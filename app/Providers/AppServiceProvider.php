<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\ShreeMarutiSeries;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Cache key for the admin-wide Maruti series warning.
     */
    public const MARUTI_SERIES_WARNING_CACHE_KEY = 'maruti_series_warning_banner';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Surface the low Maruti series warning in the header of every admin page.
        View::composer('layouts.admin', function ($view) {
            $view->with('marutiSeriesWarning', $this->marutiSeriesWarning());
        });
    }

    /**
     * Warning text when the current year is at or below the notify threshold.
     */
    protected function marutiSeriesWarning(): ?string
    {
        try {
            return Cache::remember(self::MARUTI_SERIES_WARNING_CACHE_KEY, now()->addMinute(), function () {
                if (!Setting::get('shree_maruti_enabled', false)) {
                    return null;
                }

                $threshold = (int) Setting::get('shree_maruti_notify_threshold', 0);

                if ($threshold <= 0) {
                    return null;
                }

                $currentYear = (int) now()->year;
                $available = ShreeMarutiSeries::where('is_used', false)->forYear($currentYear)->count();

                if ($available > $threshold) {
                    return null;
                }

                return "Only {$available} Maruti tracking numbers left for {$currentYear}. "
                    . 'Add a new series to avoid label generation failures.';
            });
        } catch (\Throwable $e) {
            // Never break page rendering over a warning banner.
            return null;
        }
    }
}
