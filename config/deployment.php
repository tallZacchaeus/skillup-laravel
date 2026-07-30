<?php

return [
    'backups' => [
        'disk' => env('BACKUP_DISK', ''),
        'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
    ],

    'queue' => [
        'workers' => (int) env('QUEUE_WORKERS', 2),
        'tries' => (int) env('QUEUE_WORKER_TRIES', 3),
        'timeout' => (int) env('QUEUE_WORKER_TIMEOUT', 120),
    ],

    'cutover' => [
        'legacy_wordpress_url' => env('LEGACY_WORDPRESS_URL', ''),
        'legacy_moodle_url' => env('LEGACY_MOODLE_URL', ''),
    ],

    'monitoring' => [
        'support_alert_email' => env('SUPPORT_ALERT_EMAIL', ''),
    ],

    'smoke' => [
        'paths' => [
            '/healthz',
            '/readyz',
            '/',
            '/courses',
            '/checkout/failed',
        ],
    ],
];
