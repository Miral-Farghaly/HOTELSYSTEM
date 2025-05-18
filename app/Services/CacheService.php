<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class CacheService
{
    /**
     * Get room availability from cache or compute it
     */
    public function getRoomAvailability(int $roomId, Carbon $startDate, Carbon $endDate, callable $callback)
    {
        $key = "room_availability:{$roomId}:{$startDate->format('Y-m-d')}:{$endDate->format('Y-m-d')}";
        $ttl = Config::get('cache.ttl.room_availability');

        return Cache::tags([Config::get('cache.tags.rooms'), 'availability'])
            ->remember($key, $ttl, $callback);
    }

    /**
     * Cache room price calculations
     */
    public function getRoomPrice(int $roomId, Carbon $startDate, Carbon $endDate, array $options, callable $callback)
    {
        $optionsKey = md5(json_encode($options));
        $key = "room_price:{$roomId}:{$startDate->format('Y-m-d')}:{$endDate->format('Y-m-d')}:{$optionsKey}";
        $ttl = Config::get('cache.ttl.price_calculation');

        return Cache::tags([Config::get('cache.tags.prices'), 'calculations'])
            ->remember($key, $ttl, $callback);
    }

    /**
     * Cache common database queries
     */
    public function getCommonQuery(string $identifier, array $params = [], callable $callback)
    {
        $paramsKey = md5(json_encode($params));
        $key = "query:{$identifier}:{$paramsKey}";
        $ttl = Config::get('cache.ttl.common_queries');

        return Cache::tags(['queries', $identifier])
            ->remember($key, $ttl, $callback);
    }

    /**
     * Clear room availability cache
     */
    public function clearRoomAvailability(int $roomId = null)
    {
        if ($roomId) {
            Cache::tags([Config::get('cache.tags.rooms'), 'availability'])
                ->forget("room_availability:{$roomId}:*");
        } else {
            Cache::tags([Config::get('cache.tags.rooms'), 'availability'])->flush();
        }
    }

    /**
     * Clear price calculation cache
     */
    public function clearPriceCalculations(int $roomId = null)
    {
        if ($roomId) {
            Cache::tags([Config::get('cache.tags.prices'), 'calculations'])
                ->forget("room_price:{$roomId}:*");
        } else {
            Cache::tags([Config::get('cache.tags.prices'), 'calculations'])->flush();
        }
    }

    /**
     * Clear specific query cache
     */
    public function clearQueryCache(string $identifier)
    {
        Cache::tags(['queries', $identifier])->flush();
    }

    /**
     * Clear all hotel related caches
     */
    public function clearAllCaches()
    {
        $tags = Config::get('cache.tags');
        foreach ($tags as $tag) {
            Cache::tags($tag)->flush();
        }
    }

    /**
     * Cache hotel settings
     */
    public function getHotelSettings(callable $callback)
    {
        return Cache::tags([Config::get('cache.tags.settings')])
            ->remember('hotel_settings', Config::get('cache.ttl.hotel_settings'), $callback);
    }

    /**
     * Cache user preferences
     */
    public function getUserPreferences(int $userId, callable $callback)
    {
        return Cache::tags([Config::get('cache.tags.users')])
            ->remember("user_preferences:{$userId}", Config::get('cache.ttl.user_preferences'), $callback);
    }

    /**
     * Warm up commonly accessed caches
     */
    public function warmUpCache()
    {
        // Warm up room availability for next 30 days
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);
        
        // Example of warming up cache for room types
        $this->getCommonQuery('room_types', [], function () {
            return \App\Models\RoomType::with('amenities')->get();
        });

        // Example of warming up hotel settings
        $this->getHotelSettings(function () {
            return \App\Models\Configuration::all()
                ->keyBy('key')
                ->map(fn($item) => $item->value);
        });
    }
} 