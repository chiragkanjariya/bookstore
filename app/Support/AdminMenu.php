<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;

/**
 * Single source of truth for the admin sidebar menus and the permission keys
 * that guard them. Routes, the sidebar and the role editor all read from here,
 * so a menu only has to be described once.
 */
class AdminMenu
{
    /**
     * The dashboard is implicitly available to every admin user.
     */
    public const DASHBOARD = 'dashboard';

    /**
     * All menu groups with the items they contain.
     *
     * Each item supports:
     *  - key:     permission key used by the `menu` middleware and role editor
     *  - label:   sidebar label
     *  - route:   route name to link to
     *  - pattern: route pattern used to highlight the active item
     *  - icon:    array of SVG path "d" attributes
     *  - badge:   optional closure returning a count to show next to the label
     *
     * @return array<int, array{label: string, items: array<int, array<string, mixed>>}>
     */
    public static function groups(): array
    {
        return [
            [
                'label' => 'Inventory',
                'items' => [
                    [
                        'key' => 'books',
                        'label' => 'Books',
                        'route' => 'admin.books.index',
                        'pattern' => 'admin.books.*',
                        'icon' => ['M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                        'badge' => fn () => Book::count(),
                    ],
                    [
                        'key' => 'categories',
                        'label' => 'Categories',
                        'route' => 'admin.categories.index',
                        'pattern' => 'admin.categories.*',
                        'icon' => ['M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                        'badge' => fn () => Category::count(),
                    ],
                ],
            ],
            [
                'label' => 'Users',
                'items' => [
                    [
                        'key' => 'users',
                        'label' => 'Manage Users',
                        'route' => 'admin.users.index',
                        'pattern' => 'admin.users.*',
                        'icon' => ['M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'],
                        'badge' => fn () => User::where('role', 'user')->count(),
                    ],
                    [
                        'key' => 'admin-users',
                        'label' => 'Admin Users',
                        'route' => 'admin.admin-users.index',
                        'pattern' => 'admin.admin-users.*',
                        'icon' => ['M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                        'badge' => fn () => User::where('role', 'admin')->count(),
                    ],
                    [
                        'key' => 'roles',
                        'label' => 'Roles & Permissions',
                        'route' => 'admin.roles.index',
                        'pattern' => 'admin.roles.*',
                        'icon' => ['M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                    ],
                ],
            ],
            [
                'label' => 'Sales',
                'items' => [
                    [
                        'key' => 'orders',
                        'label' => 'Orders',
                        'route' => 'admin.orders.index',
                        'pattern' => 'admin.orders.*',
                        'icon' => ['M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                        'badge' => fn () => Order::marutiOrders()->shippingPartnerStatus(Order::SHIPPING_PARTNER_PENDING)->count(),
                    ],
                    [
                        'key' => 'manual-orders',
                        'label' => 'Manual Orders',
                        'route' => 'admin.manual-shipping.index',
                        'pattern' => 'admin.manual-shipping.*',
                        'icon' => ['M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                        'badge' => fn () => Order::manualOrders()->shippingPartnerStatus(Order::SHIPPING_PARTNER_PENDING)->count(),
                    ],
                    [
                        'key' => 'bulk-orders',
                        'label' => 'Bulk Orders',
                        'route' => 'admin.bulk-orders.index',
                        'pattern' => 'admin.bulk-orders.*',
                        'icon' => ['M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                        'badge' => fn () => Order::bulkOrders()->shippingPartnerStatus(Order::SHIPPING_PARTNER_PENDING)->count(),
                    ],
                ],
            ],
            [
                'label' => 'Reports',
                'items' => [
                    [
                        'key' => 'account-reports',
                        'label' => 'Account Reports',
                        'route' => 'admin.reports.accounts.index',
                        'pattern' => 'admin.reports.accounts.*',
                        'icon' => ['M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ],
                ],
            ],
            [
                'label' => 'System',
                'items' => [
                    [
                        'key' => 'settings',
                        'label' => 'Settings',
                        'route' => 'admin.settings.index',
                        'pattern' => 'admin.settings.*',
                        'icon' => [
                            'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                            'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                        ],
                    ],
                    [
                        'key' => 'maruti-series',
                        'label' => 'Maruti Series',
                        'route' => 'admin.maruti-series.index',
                        'pattern' => 'admin.maruti-series.*',
                        'icon' => ['M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                    ],
                    [
                        'key' => 'manual-couriers',
                        'label' => 'Manual Courier',
                        'route' => 'admin.manual-couriers.index',
                        'pattern' => 'admin.manual-couriers.*',
                        'icon' => ['M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'],
                    ],
                    [
                        'key' => 'logs',
                        'label' => 'Application Logs',
                        'route' => 'admin.logs.index',
                        'pattern' => 'admin.logs.*',
                        'icon' => ['M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ],
                ],
            ],
        ];
    }

    /**
     * All menu items keyed by their permission key.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function items(): array
    {
        $items = [];

        foreach (self::groups() as $group) {
            foreach ($group['items'] as $item) {
                $items[$item['key']] = $item + ['group' => $group['label']];
            }
        }

        return $items;
    }

    /**
     * Every assignable permission key.
     *
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(self::items());
    }

    /**
     * Human readable label for a permission key.
     */
    public static function label(string $key): string
    {
        return self::items()[$key]['label'] ?? ucwords(str_replace('-', ' ', $key));
    }

    /**
     * Drop keys that are not real menus (e.g. stale keys on an old role).
     *
     * @param  array<int, mixed>  $keys
     * @return array<int, string>
     */
    public static function filterKeys(array $keys): array
    {
        return array_values(array_intersect(self::keys(), array_map('strval', $keys)));
    }
}
