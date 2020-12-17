<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        //      Don't record.
        //        Turn logging off
        //        Monolog settings
        //        'logger' => [
        //            'name' => 'slim-app',
        //            'path' => __DIR__ . '/../logs/app.log',
        //            'level' => \Monolog\Logger::DEBUG,
        //        ],

        // Database connection settings
        "db" => [
            "host" => "<private>",
            "dbname" => "<private>",
            "user" => "<private>",
            "pass" => "<private>",
        ],

        'core' => [
            'connection' => '1',
        ],

        'stack' => [
            'latitude' => '<not set>',
            'longitude' => '<not set>',
            'word' => '<not set>',
            'short_name' => '<not set>',
            'entity_name' => '<not set',
            'server_location' => '<NOT SET>',
            'hashmessage' => '#devstack',
            'state' => 'prod',
            'engine_state' => 'dev',
            'uuid' => '<private>',
            'email' => '<not set>',
            'nominal' => '<not set>',
            'associate_prior' => false,
            'associate_posterior' => true,
            'mail_prefix' => "[TEST]",
            'mail_regulatory' => "\r\n<not set>\r\n",
            'char_max' => 4000,
            'mail_postfix' => "@<private>",
            'cron_period' => 60,
            'thing_resolution' => 1,
            'max_db_connections' => 100,
            'web_prefix' => '<not set>',
            'sms_separator' => '|',
            'sms_address' => '<not set>',
            'retain_for' => [
                'amount' => 8,
                'attribute' => 'time',
                'unit' => 'hours',
            ],
            'persist_for' => [
                'amount' => 2,
                'attribute' => 'time',
                'unit' => 'days',
            ],
            'path' => "/var/www/stackr.test/",
            'name' => 'nostromo',
            'hash' => 'on',
            'hash_algorithm' => 'sha256',
            'hashtag' => '#devstack',
        ],

        'api' => [
            'translink' => '<private>',
            'nexmo' => [
                "api_key" => '<private>',
                "api_secret" => '<private>',
            ],

            'biblesearch' => '<private>',
            '1forge' => '<private>',
            'google' => [
                'API key' => '<private>',
                'client ID' => '<private>',
                'client secret' => '<private>',
            ],
            'google calender' => [
                "client_id" => "<private>",
                "project_id" => "<private>",
                "auth_uri" => "<private>",
                "token_uri" => "<private>",
                "auth_provider_x509_cert_url" => "<private>",
                "client_secret" => "<private>",
                "redirect_uris" => "<example>",
            ],
            'telegram' => [
                "api_id" => "<private>",
                "api_hash" => "<private>",
                "mtproto_server_test" => "149.154.167.40:443",
                "mtproto_server_prod" => "149.154.167.50:443",
                "public_key" => "<private or at least up to you>",
            ],
        ],
        'twitter' => [
            "api_key" => "<private>",
            "api_secret" => "<private>",
            "access_token" => "<private>",
            "access_token_secret" => "<private>",
        ],

        'facebook' => [
            'app token' => '<private>',
            'app ID' => '<private>',
            'app secret' => '<private>',
            'page_access_token' => '<private>',
        ],

        'slack' => [
            'verification token' => '<private>',
            'client ID' => '<private>',
            'client secret' => '<private>',
            'bot user oauth access token' => '<private>',
        ],

        'block' => ['default run_time' => '105', 'negative_time' => 'yes'],
        'quota' => [
            'quota_daily' => '1',
            'quota_hourly' => '1',
            'message_perminute_limit' => '1',
            'message_hourly_limit' => '1',
            'message_daily_limit' => '1',
        ],

        'place' => [
            'default_place_name' => 'Mornington Crescent',
            'default_place_code' => '090003',
        ],

        'watson' => false,
        'clerk' => ['scalar' => 1001, '<not set>', '<not set>'],
        'robot' => ['user_agent' => '<not set>'],

        'watson weather' => false,

        'slug' => [
            "state" => 'on', // turn slug responses on and off
            'allowed_slugs_resource' => 'slug/slugs.php',
        ],

        'job' => [
            "state" => 'off', // turn job responses on and off
        ],

        'pheromone' => [
            "velocity_factor" => -1e-9,
            "acceleration_factor" => -1e-6, // adjust pheromone
        ],

        'bar' => ["default_max_bar_count" => 20],
        'tick' => ["default_max_tick_count" => 4],

        'agent' => [
            'clerk' => ['scalar' => 100, '<not set>', '<not set>'],
        ],

        'thing' => [
            'is' => 'a stack of things',
            'callsign' => 'XXXX',
            'sms' => 'X XXX XXX XXXX',
            'email' => 'null@stackr.ca',
            'place' => 'mornington crescent',
            'stack_account' => [
                'account_name' => 'stack',
                'balance' => [
                    'amount' => 100.0,
                    'attribute' => 'X',
                    'unit' => 'X',
                ],
            ],
            'thing_account' => [
                'account_name' => 'thing',
                'balance' => [
                    'amount' => -100.0,
                    'attribute' => 'X',
                    'unit' => 'X',
                ],
            ],
        ],
    ],
];
