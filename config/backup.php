<?php

return [

    'backup' => [

        'name' => env('APP_NAME', 'AcademyHub'),

        'source' => [

            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    storage_path('logs'),
                    storage_path('framework/cache'),
                    storage_path('framework/sessions'),
                    storage_path('framework/views'),
                    base_path('.git'),
                ],
                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => base_path(),
            ],

            'databases' => [
                'mysql',
            ],
        ],

        'database_dump_compressor' => null,
        'database_dump_file_extension' => '',

        'destination' => [
            'filename_prefix' => 'academyhub-backup-',
            'disks' => [
                'local',
            ],
        ],

        'temporary_directory' => storage_path('app/backup-temp'),

        'password' => env('BACKUP_ARCHIVE_PASSWORD'),
        'encryption' => 'default',
    ],

    'notifications' => [

        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class         => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class     => [],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class   => [],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class        => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class    => [],
        ],

        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => env('BACKUP_NOTIFICATION_EMAIL', 'admin@school.com'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'noreply@school.com'),
                'name'    => env('MAIL_FROM_NAME', 'AcademyHub Backups'),
            ],
        ],
    ],

    'monitor_backups' => [
        [
            'name'          => env('APP_NAME', 'AcademyHub'),
            'disks'         => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class          => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class  => 5000,
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days'                            => 7,
            'keep_daily_backups_for_days'                          => 30,
            'keep_weekly_backups_for_weeks'                        => 8,
            'keep_monthly_backups_for_months'                      => 4,
            'keep_yearly_backups_for_years'                        => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],

];
