<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dashboard Cache Duration
    |--------------------------------------------------------------------------
    |
    | The duration (in seconds) to cache dashboard statistics.
    | Default: 900 seconds (15 minutes)
    |
    */
    'cache_duration' => env('DASHBOARD_CACHE_MINUTES', 15) * 60,

    /*
    |--------------------------------------------------------------------------
    | Low Stock Threshold
    |--------------------------------------------------------------------------
    |
    | The quantity threshold for low stock alerts.
    | Items with quantity at or below this value will trigger alerts.
    | Default: 10 items
    |
    */
    'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 10),

    /*
    |--------------------------------------------------------------------------
    | Stock Severity Levels
    |--------------------------------------------------------------------------
    |
    | Thresholds for different severity levels of low stock:
    | - Critical: Quantity is 0 (out of stock)
    | - Warning: Quantity is between 1 and this threshold
    | - Low: Quantity is between warning threshold and low stock threshold
    |
    */
    'stock_critical_threshold' => 0,
    'stock_warning_threshold' => 5,

    /*
    |--------------------------------------------------------------------------
    | Expiring Soon Days
    |--------------------------------------------------------------------------
    |
    | Number of days to consider items as "expiring soon".
    | Items expiring within this timeframe will trigger alerts.
    | Default: 30 days
    |
    */
    'expiring_soon_days' => env('EXPIRING_SOON_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Chart Default Period
    |--------------------------------------------------------------------------
    |
    | The default time period for chart data display.
    | Options: '7days', '30days', '6months', '12months'
    | Default: 6 months
    |
    */
    'chart_default_period' => '6months',

    /*
    |--------------------------------------------------------------------------
    | Default Chart Colors
    |--------------------------------------------------------------------------
    |
    | Consistent color scheme used across all dashboard charts and stats.
    |
    */
    'colors' => [
        'sam' => '#ef4444',      // Red for Severe Acute Malnutrition
        'mam' => '#f59e0b',      // Orange for Moderate Acute Malnutrition
        'normal' => '#3b82f6',   // Blue for Normal status
        'critical' => '#ef4444', // Red for critical alerts
        'warning' => '#f59e0b',  // Orange for warnings
        'low' => '#eab308',      // Yellow for low priority
        'success' => '#10b981',  // Green for success states
    ],
];
