<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Check if locale is set in the session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } 
        // Check if locale is set in the browser
        else if ($request->hasHeader('Accept-Language')) {
            $locale = substr($request->header('Accept-Language'), 0, 2);
        }
        // Default to configured locale
        else {
            $locale = config('app.locale');
        }

        // Ensure the locale exists in our available locales
        if (!array_key_exists($locale, config('app.available_locales'))) {
            $locale = config('app.fallback_locale');
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }
} 