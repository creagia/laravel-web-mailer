<?php

return [

    'route' => [
        /**
         * Prefix used by the package routes. 'mails' by default.
         */
        'prefix' => env('WEB_MAILER_ROUTE_PREFIX', 'web-inbox'),

        'middleware' => [
//            'web',
//            'auth',
        ],
    ],

    /*
     * The path where the emails will be stored
     */
    'storage_path' => storage_path('web-emails'),

    /*
     * To enable this feature, you must schedule
     *  the command: 'laravel-web-mailer:cleanup'
     */
    'delete_emails_older_than_days' => 7,
];
