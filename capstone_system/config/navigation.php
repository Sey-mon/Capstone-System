<?php

// In config/navigation.php, just add to the admin array:

return [
    'admin' => [
        [
            'route' => 'admin.dashboard',
            'icon' => 'fas fa-chart-pie',
            'text' => 'Dashboard',
        ],
        [
            'route' => 'admin.users',
            'icon' => 'fas fa-users',
            'text' => 'Users',
        ],
        [
            'route' => 'admin.patients',
            'icon' => 'fas fa-user-injured',
            'text' => 'Patients',
        ],
        [
            'route' => 'admin.inventory',
            'icon' => 'fas fa-boxes',
            'text' => 'Inventory',
        ],
        [
            'route' => 'admin.system.management',
            'icon' => 'fas fa-cogs',
            'text' => 'System Management',
        ],
        [
            'route' => 'admin.reports',
            'icon' => 'fas fa-chart-bar',
            'text' => 'Reports',
        ],
        [
            'route' => 'admin.audit.logs',
            'icon' => 'fas fa-clipboard-check',
            'text' => 'Audit Logs',
        ],
    ],
    
    'nutritionist' => [
        [
            'route' => 'nutritionist.dashboard',
            'icon' => 'fas fa-chart-pie',
            'text' => 'Dashboard',
        ],
        [
            'route' => 'nutritionist.patients',
            'icon' => 'fas fa-user-injured',
            'text' => 'Patients',
        ],
        [
            'route' => 'nutritionist.assessments',
            'icon' => 'fas fa-clipboard-list',
            'text' => 'Assessments',
        ],
        [
            'route' => 'nutritionist.profile',
            'icon' => 'fas fa-user-cog',
            'text' => 'Profile',
        ],
    ],
    
    'parent' => [
        [
            'route' => 'parent.dashboard',
            'icon' => 'fas fa-chart-pie',
            'text' => 'Dashboard',
        ],
        [
            'route' => 'parent.children',
            'icon' => 'fas fa-child',
            'text' => 'Children',
        ],
        [
            'route' => 'parent.assessments',
            'icon' => 'fas fa-clipboard-list',
            'text' => 'Assessments',
        ],
        [
            'route' => 'parent.profile',
            'icon' => 'fas fa-user-cog',
            'text' => 'Profile',
        ],
    ],
];
