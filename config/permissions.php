<?php

return [
    'definitions' => [
        'users.manage' => [
            'label' => 'Manage users',
            'roles' => ['admin'],
        ],
        'backup.manage' => [
            'label' => 'Backup & restore',
            'roles' => ['admin'],
        ],
        'audit.view' => [
            'label' => 'View audit logs',
            'roles' => ['admin'],
        ],
        'results.entry' => [
            'label' => 'Enter / edit scores',
            'roles' => ['admin', 'teacher'],
        ],
        'results.review' => [
            'label' => 'Approve / reject score submissions',
            'roles' => ['admin'],
        ],
        'results.publish' => [
            'label' => 'Publish / unpublish results',
            'roles' => ['admin'],
        ],
        'results.broadsheet' => [
            'label' => 'View broadsheet',
            'roles' => ['admin', 'teacher'],
        ],
        'billing.transactions' => [
            'label' => 'Manage transactions',
            'roles' => ['admin', 'bursar'],
        ],
        'billing.void' => [
            'label' => 'Void transactions',
            'roles' => ['admin', 'bursar'],
        ],
        'billing.export' => [
            'label' => 'Export transactions',
            'roles' => ['admin', 'bursar'],
        ],
        'fees.manage' => [
            'label' => 'Manage fee structures',
            'roles' => ['admin', 'bursar'],
        ],
        'announcements.manage' => [
            'label' => 'Manage announcements',
            'roles' => ['admin'],
        ],
        'messages.access' => [
            'label' => 'Access messaging',
            'roles' => ['admin', 'teacher', 'bursar'],
        ],
        'data_collection.submit' => [
            'label' => 'Submit weekly data collection',
            'roles' => ['admin', 'teacher'],
        ],
        'data_collection.review' => [
            'label' => 'Review data collection submissions',
            'roles' => ['admin'],
        ],
        'analytics.view' => [
            'label' => 'View Analytics Dashboard',
            'roles' => ['admin', 'teacher'],
        ],
    ],
];
