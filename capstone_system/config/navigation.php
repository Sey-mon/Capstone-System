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
            'route' => 'admin.api.management',
            'icon' => 'fas fa-robot',
            'text' => 'API Management',
        ],
        [
            'route' => 'admin.knowledge-base.index',
            'icon' => 'fas fa-book-medical',
            'text' => 'Knowledge Base',
        ],
        [
            'route' => 'admin.foods.index',
            'icon' => 'fas fa-utensils',
            'text' => 'Food Database',
        ],
        [
            'route' => 'admin.food-requests.index',
            'icon' => 'fas fa-clipboard-question',
            'text' => 'Food Requests',
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
        // [
        //     'route' => 'nutritionist.meal-plans',
        //     'icon' => 'fas fa-utensils',
        //     'text' => 'Meal Plans',
        // ],
        [
            'route' => 'nutritionist.foods.index',
            'icon' => 'fas fa-database',
            'text' => 'Food Database',
        ],
        [
            'route' => 'nutritionist.food-requests.index',
            'icon' => 'fas fa-clipboard-list',
            'text' => 'My Food Requests',
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
            'text' => 'Child Assessments',
        ],
        [
            'route' => 'parent.meal-plans',
            'icon' => 'fas fa-magic',
            'text' => 'Generate Meal Plan',
        ],
        [
            'route' => 'parent.view-meal-plans',
            'icon' => 'fas fa-book-medical',
            'text' => 'My Meal Plans',
        ],
        [
            'route' => 'parent.profile',
            'icon' => 'fas fa-user-cog',
            'text' => 'Profile',
        ],
    ],
];
