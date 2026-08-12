<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\ShreeMarutiSeries;
use App\Support\AdminMenu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
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
            $view->with('adminMenuGroups', $this->adminMenuGroups());
        });

        $this->registerMenuPermissions();
    }

    /**
     * Menu level permission checks for policies and Blade templates.
     */
    protected function registerMenuPermissions(): void
    {
        Gate::define('menu', function ($user, string $key) {
            return $user->canAccessMenu($key);
        });

        // @menu('orders') ... @endmenu
        Blade::if('menu', function (string $key) {
            return Auth::check() && Auth::user()->canAccessMenu($key);
        });
    }

    /**
     * Sidebar menu groups the signed in admin is allowed to see.
     *
     * @return array<int, array{label: string, items: array<int, array<string, mixed>>}>
     */
    protected function adminMenuGroups(): array
    {
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        $groups = [];

        foreach (AdminMenu::groups() as $group) {
            $items = array_values(array_filter(
                $group['items'],
                fn ($item) => $user->canAccessMenu($item['key'])
            ));

            if ($items) {
                $groups[] = ['label' => $group['label'], 'items' => $items];
            }
        }

        return $groups;
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
