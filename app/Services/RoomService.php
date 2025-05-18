<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Reservation;
use App\Models\RoomSpecialPrice;
use App\Models\RoomBlock;
use App\Models\RoomWaitlist;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RoomService
{
    public function getAllRooms(array $filters = []): Collection
    {
        $query = Room::with(['type', 'maintenanceLogs']);

        if (isset($filters['type_id'])) {
            $query->where('type_id', $filters['type_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['floor'])) {
            $query->where('floor', $filters['floor']);
        }

        if (isset($filters['capacity'])) {
            $query->where('capacity', '>=', $filters['capacity']);
        }

        return $query->get();
    }

    public function getRoom(int $roomId): ?Room
    {
        $cacheKey = "room:{$roomId}";
        
        return Cache::remember($cacheKey, now()->addDay(), function () use ($roomId) {
            return Room::with(['type', 'maintenanceLogs'])
                ->find($roomId);
        });
    }

    public function createRoom(array $data): Room
    {
        return DB::transaction(function () use ($data) {
            $amenities = $data['amenities'] ?? [];
            unset($data['amenities']);
            
            $room = Room::create($data);
            
            if (!empty($amenities)) {
                $this->syncAmenities($room, $amenities);
            }
            
            // Initialize cache for this room
            $this->updateRoomCache($room);
            
            return $room;
        });
    }

    public function updateRoom(Room $room, array $data): Room
    {
        return DB::transaction(function () use ($room, $data) {
            $amenities = $data['amenities'] ?? null;
            unset($data['amenities']);
            
            $room->update($data);
            
            if ($amenities !== null) {
                $this->syncAmenities($room, $amenities);
            }
            
            // Update room cache
            $this->updateRoomCache($room);
            
            return $room;
        });
    }

    public function deleteRoom(Room $room): bool
    {
        return DB::transaction(function () use ($room) {
            // Clear room cache
            $this->clearRoomCache($room);
            
            return $room->delete();
        });
    }

    public function bulkUpdateRooms(array $roomIds, array $data): int
    {
        return DB::transaction(function () use ($roomIds, $data) {
            $count = Room::whereIn('id', $roomIds)->update($data);
            
            // Clear cache for all updated rooms
            foreach ($roomIds as $roomId) {
                Cache::forget("room:{$roomId}");
            }
            
            return $count;
        });
    }

    public function checkAvailability(string $checkIn, string $checkOut, ?int $roomTypeId = null): array
    {
        $checkInDate = Carbon::parse($checkIn)->startOfDay();
        $checkOutDate = Carbon::parse($checkOut)->endOfDay();

        $cacheKey = "room_availability:{$checkInDate->format('Y-m-d')}:{$checkOutDate->format('Y-m-d')}:{$roomTypeId}";

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($checkInDate, $checkOutDate, $roomTypeId) {
            $query = Room::available();

            if ($roomTypeId) {
                $query->where('type_id', $roomTypeId);
            }

            // Get all rooms that might be available
            $potentialRooms = $query->get();

            $availableRooms = $potentialRooms->filter(function ($room) use ($checkInDate, $checkOutDate) {
                return !$room->reservations()
                    ->where(function ($query) use ($checkInDate, $checkOutDate) {
                        $query->where(function ($q) use ($checkInDate, $checkOutDate) {
                            $q->where('check_in', '<=', $checkOutDate)
                                ->where('check_out', '>=', $checkInDate);
                        });
                    })
                    ->whereIn('status', ['confirmed', 'checked_in'])
                    ->exists();
            });

            return [
                'available' => $availableRooms->count() > 0,
                'rooms' => $availableRooms,
                'total_available' => $availableRooms->count(),
                'check_in' => $checkInDate->format('Y-m-d'),
                'check_out' => $checkOutDate->format('Y-m-d'),
            ];
        });
    }

    public function toggleMaintenance(Room $room): Room
    {
        return DB::transaction(function () use ($room) {
            if ($room->is_maintenance) {
                $room->markAsAvailable();
            } else {
                $room->markForMaintenance();
            }
            
            // Update room cache
            $this->updateRoomCache($room);
            
            return $room;
        });
    }

    public function getRoomsByType(int $typeId): Collection
    {
        return Room::where('type_id', $typeId)
            ->where('status', 'active')
            ->where('is_maintenance', false)
            ->get();
    }

    public function getRoomAvailabilityCalendar(Room $room, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $cacheKey = "room_calendar:{$room->id}:{$start->format('Y-m-d')}:{$end->format('Y-m-d')}";

        return Cache::remember($cacheKey, now()->addHours(1), function () use ($room, $start, $end) {
            $reservations = $room->reservations()
                ->whereBetween('check_in', [$start, $end])
                ->orWhereBetween('check_out', [$start, $end])
                ->get();

            $calendar = [];
            $current = $start->copy();

            while ($current <= $end) {
                $date = $current->format('Y-m-d');
                $calendar[$date] = [
                    'available' => true,
                    'reservation_id' => null,
                    'price' => $this->calculateRoomPrice($room, $current),
                    'status' => $room->status,
                ];

                foreach ($reservations as $reservation) {
                    if ($current->between($reservation->check_in, $reservation->check_out)) {
                        $calendar[$date] = [
                            'available' => false,
                            'reservation_id' => $reservation->id,
                            'price' => $this->calculateRoomPrice($room, $current),
                            'status' => 'booked',
                        ];
                        break;
                    }
                }

                $current->addDay();
            }

            return $calendar;
        });
    }

    public function calculateRoomPrice(Room $room, Carbon $date): float
    {
        $basePrice = $room->getCurrentPrice($date);
        
        // Apply seasonal multiplier
        $price = $basePrice * $this->getSeasonalMultiplier($date);
        
        // Apply day of week multiplier
        $price *= $this->getDayOfWeekMultiplier($date);
        
        // Apply occupancy-based multiplier
        $price *= $this->getOccupancyMultiplier($date);
        
        return round($price, 2);
    }

    public function updateRoomPrice(Room $room, float $newPrice, ?string $reason = null): Room
    {
        return DB::transaction(function () use ($room, $newPrice, $reason) {
            $oldPrice = $room->price_per_night;
            
            $room->recordPriceChange($oldPrice, $newPrice, $reason);
            $room->update(['price_per_night' => $newPrice]);
            
            // Update room cache
            $this->updateRoomCache($room);
            
            return $room;
        });
    }

    public function addSpecialPrice(Room $room, array $data): RoomSpecialPrice
    {
        return DB::transaction(function () use ($room, $data) {
            // Check for overlapping special prices
            $hasOverlap = $room->specialPrices()
                ->overlapping(
                    Carbon::parse($data['start_date']),
                    Carbon::parse($data['end_date'])
                )
                ->exists();
                
            if ($hasOverlap) {
                throw new \Exception('Special price period overlaps with existing special prices');
            }
            
            $data['created_by'] = auth()->id();
            $specialPrice = $room->specialPrices()->create($data);
            
            // Clear availability cache for the affected period
            $this->clearAvailabilityCache(
                $room,
                Carbon::parse($data['start_date']),
                Carbon::parse($data['end_date'])
            );
            
            return $specialPrice;
        });
    }

    public function removeSpecialPrice(RoomSpecialPrice $specialPrice): bool
    {
        return DB::transaction(function () use ($specialPrice) {
            $room = $specialPrice->room;
            $startDate = $specialPrice->start_date;
            $endDate = $specialPrice->end_date;
            
            $result = $specialPrice->delete();
            
            // Clear availability cache for the affected period
            $this->clearAvailabilityCache($room, $startDate, $endDate);
            
            return $result;
        });
    }

    public function bulkUpdatePrices(array $roomIds, float $priceAdjustment, string $adjustmentType = 'fixed', ?string $reason = null): int
    {
        return DB::transaction(function () use ($roomIds, $priceAdjustment, $adjustmentType, $reason) {
            $rooms = Room::whereIn('id', $roomIds)->get();
            $count = 0;
            
            foreach ($rooms as $room) {
                $oldPrice = $room->price_per_night;
                $newPrice = $adjustmentType === 'fixed' 
                    ? $priceAdjustment 
                    : $oldPrice * (1 + $priceAdjustment / 100);
                
                $room->recordPriceChange($oldPrice, $newPrice, $reason);
                $room->update(['price_per_night' => round($newPrice, 2)]);
                
                // Update room cache
                $this->updateRoomCache($room);
                
                $count++;
            }
            
            return $count;
        });
    }

    public function blockRoom(Room $room, array $data): RoomBlock
    {
        return DB::transaction(function () use ($room, $data) {
            // Check for existing reservations in the block period
            $hasConflicts = $room->reservations()
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->where(function ($q) use ($data) {
                    $q->whereBetween('check_in', [$data['start_date'], $data['end_date'] ?? $data['start_date']])
                        ->orWhereBetween('check_out', [$data['start_date'], $data['end_date'] ?? $data['start_date']])
                        ->orWhere(function ($q) use ($data) {
                            $q->where('check_in', '<=', $data['start_date'])
                                ->where('check_out', '>=', $data['end_date'] ?? $data['start_date']);
                        });
                })
                ->exists();

            if ($hasConflicts) {
                throw new \Exception('Cannot block room due to existing reservations in the specified period');
            }

            $data['blocked_by'] = auth()->id();
            $block = $room->blockings()->create($data);

            // Update room status if immediate block
            if (Carbon::parse($data['start_date'])->lte(now())) {
                $room->block(
                    $data['reason'],
                    isset($data['end_date']) ? Carbon::parse($data['end_date']) : null
                );
            }

            // Clear availability cache
            $this->clearAvailabilityCache(
                $room,
                Carbon::parse($data['start_date']),
                isset($data['end_date']) ? Carbon::parse($data['end_date']) : null
            );

            return $block;
        });
    }

    public function removeBlock(RoomBlock $block): bool
    {
        return DB::transaction(function () use ($block) {
            $room = $block->room;
            $startDate = $block->start_date;
            $endDate = $block->end_date;

            $result = $block->delete();

            if ($result && $room->isBlocked()) {
                $room->unblock();
            }

            // Clear availability cache
            $this->clearAvailabilityCache($room, $startDate, $endDate);

            return $result;
        });
    }

    public function addToWaitlist(Room $room, array $data): RoomWaitlist
    {
        return DB::transaction(function () use ($room, $data) {
            if (!$room->allow_waitlist) {
                throw new \Exception('Waitlist is not enabled for this room');
            }

            $data['user_id'] = $data['user_id'] ?? auth()->id();
            return $room->addToWaitlist($data);
        });
    }

    public function processWaitlist(Room $room, Carbon $date): void
    {
        $waitlistEntries = $room->waitlistEntries()
            ->active()
            ->forDateRange($date, $date)
            ->orderBy('created_at')
            ->get();

        foreach ($waitlistEntries as $entry) {
            // Check if room became available
            $isAvailable = !$this->checkAvailability(
                $entry->check_in->format('Y-m-d'),
                $entry->check_out->format('Y-m-d'),
                $room->type_id
            )['available'];

            if ($isAvailable) {
                // Notify user about availability
                // TODO: Implement notification logic
                $entry->markAsNotified();
            }
        }
    }

    public function handleOverbooking(Room $room, array $reservationData): bool
    {
        if (!$room->canOverbook()) {
            return false;
        }

        // Check if regular booking is possible
        $availability = $this->checkAvailability(
            $reservationData['check_in'],
            $reservationData['check_out'],
            $room->type_id
        );

        if ($availability['available']) {
            return true;
        }

        // Check overbooking limits
        $currentOverbookings = $room->reservations()
            ->where('is_overbooked', true)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->whereBetween('check_in', [$reservationData['check_in'], $reservationData['check_out']])
            ->orWhereBetween('check_out', [$reservationData['check_in'], $reservationData['check_out']])
            ->count();

        return $currentOverbookings < $room->max_overbooking;
    }

    protected function getSeasonalMultiplier(Carbon $date): float
    {
        // Cache seasonal rates for a day
        $cacheKey = "seasonal_rate:{$date->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, now()->addDay(), function () use ($date) {
            // Example seasonal rates
            $seasons = [
                // High season (summer)
                ['start' => '06-01', 'end' => '08-31', 'multiplier' => 1.5],
                // Holiday season
                ['start' => '12-15', 'end' => '01-05', 'multiplier' => 2.0],
                // Low season
                ['start' => '01-06', 'end' => '05-31', 'multiplier' => 0.8],
                ['start' => '09-01', 'end' => '12-14', 'multiplier' => 0.8],
            ];

            foreach ($seasons as $season) {
                $seasonStart = Carbon::createFromFormat('m-d', $season['start'])->year($date->year);
                $seasonEnd = Carbon::createFromFormat('m-d', $season['end'])->year($date->year);
                
                if ($date->between($seasonStart, $seasonEnd)) {
                    return $season['multiplier'];
                }
            }

            return 1.0; // Default multiplier
        });
    }

    protected function getDayOfWeekMultiplier(Carbon $date): float
    {
        // Weekend rates (Friday and Saturday)
        if ($date->isWeekend()) {
            return 1.2;
        }
        
        return 1.0;
    }

    protected function getOccupancyMultiplier(Carbon $date): float
    {
        $cacheKey = "occupancy_rate:{$date->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($date) {
            $totalRooms = Room::count();
            $occupiedRooms = Reservation::whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>=', $date)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->count();
            
            $occupancyRate = $totalRooms > 0 ? ($occupiedRooms / $totalRooms) : 0;
            
            // Dynamic pricing based on occupancy
            if ($occupancyRate >= 0.9) {
                return 1.3; // High demand
            } elseif ($occupancyRate >= 0.7) {
                return 1.1; // Medium demand
            } elseif ($occupancyRate <= 0.3) {
                return 0.9; // Low demand
            }
            
            return 1.0; // Normal demand
        });
    }

    protected function syncAmenities(Room $room, array $amenities): void
    {
        $room->amenities = array_unique($amenities);
        $room->save();
    }

    protected function updateRoomCache(Room $room): void
    {
        $cacheKey = "room:{$room->id}";
        Cache::put($cacheKey, $room->load(['type', 'maintenanceLogs']), now()->addDay());
    }

    protected function clearRoomCache(Room $room): void
    {
        Cache::forget("room:{$room->id}");
    }

    protected function clearAvailabilityCache(Room $room, Carbon $startDate, Carbon $endDate): void
    {
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            Cache::forget("room_calendar:{$room->id}:{$current->format('Y-m-d')}:{$current->format('Y-m-d')}");
            $current->addDay();
        }
    }
} 