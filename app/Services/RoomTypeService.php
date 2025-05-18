<?php

namespace App\Services;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RoomTypeService
{
    public function getAllTypes(): Collection
    {
        return Cache::remember('room_types:all', now()->addDay(), function () {
            return RoomType::with('rooms')->get();
        });
    }

    public function getActiveTypes(): Collection
    {
        return Cache::remember('room_types:active', now()->addHours(1), function () {
            return RoomType::active()->with('rooms')->get();
        });
    }

    public function getType(int $typeId): ?RoomType
    {
        $cacheKey = "room_type:{$typeId}";
        
        return Cache::remember($cacheKey, now()->addDay(), function () use ($typeId) {
            return RoomType::with('rooms')->find($typeId);
        });
    }

    public function createType(array $data): RoomType
    {
        return DB::transaction(function () use ($data) {
            $amenities = $data['amenities'] ?? [];
            unset($data['amenities']);
            
            $type = RoomType::create($data);
            
            if (!empty($amenities)) {
                $this->syncAmenities($type, $amenities);
            }
            
            $this->clearTypeCache();
            
            return $type;
        });
    }

    public function updateType(RoomType $type, array $data): RoomType
    {
        return DB::transaction(function () use ($type, $data) {
            $amenities = $data['amenities'] ?? null;
            unset($data['amenities']);
            
            $type->update($data);
            
            if ($amenities !== null) {
                $this->syncAmenities($type, $amenities);
            }
            
            $this->clearTypeCache();
            $this->updateTypeCache($type);
            
            return $type;
        });
    }

    public function deleteType(RoomType $type): bool
    {
        return DB::transaction(function () use ($type) {
            $deleted = $type->delete();
            
            if ($deleted) {
                $this->clearTypeCache();
            }
            
            return $deleted;
        });
    }

    public function getTypeAvailability(RoomType $type, string $checkIn, string $checkOut): array
    {
        $cacheKey = "room_type_availability:{$type->id}:{$checkIn}:{$checkOut}";
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($type, $checkIn, $checkOut) {
            $availableRooms = $type->getAvailableRooms()
                ->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
                    $query->where(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkOut)
                            ->where('check_out', '>=', $checkIn);
                    })->whereIn('status', ['confirmed', 'checked_in']);
                })->get();

            return [
                'type' => $type,
                'available' => $availableRooms->isNotEmpty(),
                'available_rooms' => $availableRooms,
                'total_available' => $availableRooms->count(),
                'base_price' => $type->base_price,
                'amenities' => $type->amenities,
            ];
        });
    }

    public function getTypePricing(RoomType $type, string $checkIn, string $checkOut): array
    {
        $cacheKey = "room_type_pricing:{$type->id}:{$checkIn}:{$checkOut}";
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($type, $checkIn, $checkOut) {
            $startDate = now()->parse($checkIn);
            $endDate = now()->parse($checkOut);
            $nights = $startDate->diffInDays($endDate);
            
            $basePrice = $type->base_price;
            $totalPrice = 0;
            $priceBreakdown = [];
            
            $current = $startDate->copy();
            while ($current < $endDate) {
                $dailyPrice = $this->calculateDailyPrice($type, $current);
                $totalPrice += $dailyPrice;
                
                $priceBreakdown[] = [
                    'date' => $current->format('Y-m-d'),
                    'base_price' => $basePrice,
                    'final_price' => $dailyPrice,
                    'multipliers' => [
                        'seasonal' => $this->getSeasonalMultiplier($current),
                        'day_of_week' => $this->getDayOfWeekMultiplier($current),
                        'occupancy' => $this->getOccupancyMultiplier($current),
                    ],
                ];
                
                $current->addDay();
            }
            
            return [
                'total_nights' => $nights,
                'base_price_per_night' => $basePrice,
                'total_price' => $totalPrice,
                'average_price_per_night' => $nights > 0 ? ($totalPrice / $nights) : $basePrice,
                'price_breakdown' => $priceBreakdown,
            ];
        });
    }

    protected function calculateDailyPrice(RoomType $type, $date): float
    {
        $basePrice = $type->base_price;
        
        // Apply multipliers
        $price = $basePrice;
        $price *= $this->getSeasonalMultiplier($date);
        $price *= $this->getDayOfWeekMultiplier($date);
        $price *= $this->getOccupancyMultiplier($date);
        
        return round($price, 2);
    }

    protected function getSeasonalMultiplier($date): float
    {
        // Reuse the logic from RoomService
        return app(RoomService::class)->getSeasonalMultiplier($date);
    }

    protected function getDayOfWeekMultiplier($date): float
    {
        return app(RoomService::class)->getDayOfWeekMultiplier($date);
    }

    protected function getOccupancyMultiplier($date): float
    {
        return app(RoomService::class)->getOccupancyMultiplier($date);
    }

    protected function syncAmenities(RoomType $type, array $amenities): void
    {
        $type->amenities = array_unique($amenities);
        $type->save();
    }

    protected function updateTypeCache(RoomType $type): void
    {
        $cacheKey = "room_type:{$type->id}";
        Cache::put($cacheKey, $type->load('rooms'), now()->addDay());
    }

    protected function clearTypeCache(): void
    {
        Cache::forget('room_types:all');
        Cache::forget('room_types:active');
    }
} 