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
		    'short_name' => '<not set>',
            'entity_name' => '<not set',
            'server_location' => '<NOT SET>',
            'hashmessage' => '#devstack',
		    'state' => 'test',
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
		    'web_prefix' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/",
		    'sms_separator' => '|',
		    'sms_address' => '<not set>',
		    'retain_for' => array('amount'=> 8, 'attribute'=>'time', 'unit'=>'hours'),
            'persist_for' => array('amount'=> 2, 'attribute'=>'time', 'unit'=>'days'),
        ],

	    'api' => [
		    'translink' => '<private>',
            'nexmo' => [
                "api_key"=>'<private>',
                "api_secret"=>'<private>',
            ],

            'biblesearch' => '<private>',
            '1forge' => '<private>',
		    'google' => array('API key'=>'<private>',
				'client ID'=>'<private>',
				'client secret'=>'<private>'),
            'google calender' => [
                "client_id"=>"<private>",
                "project_id"=>"<private>",
                "auth_uri"=>"<private>",
                "token_uri"=>"<private>",
                "auth_provider_x509_cert_url"=>"<private>",
                "client_secret"=>"<private>",
                "redirect_uris"=>(isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/",
 . "api/redpanda/googlecalendar"
            ],
            'telegram' => [
                "api_id"=>"<private>",
                "api_hash"=>"<private>",
                "mtproto_server_test"=>"149.154.167.40:443",
                "mtproto_server_prod"=>"149.154.167.50:443",
                "public_key"=>"<private or at least up to you>"
            ],
        ],
        'twitter' => [
                "api_key"=>"<private>",
                "api_secret"=>"<private>",
                "access_token"=>"<private>",
                "access_token_secret"=>"<private>"
         ],

         'facebook' => array('app token'=>'<private>',
                'app ID'=>'<private>',
                'app secret'=>'<private>',
				'page_access_token'=>'<private>'),

         'slack' => array('verification token'=>'<private>',
                'client ID'=>'<private>',
                'client secret'=>'<private>',
				'bot user oauth access token' => '<private>'),

         'block' => array('default run_time'=>'105',
                                'negative_time'=>'yes'),
         'quota' => array('quota_daily'=>'1',
                                'quota_hourly'=>'1',
                                'message_perminute_limit'=>'1',
                                'message_hourly_limit'=>'1',
                                'message_daily_limit'=>'1'),

        'place' => array('default_place_name'=>'Mornington Crescent',
                                'default_place_code'=>'090003'),

		'watson' => false,
		'clerk' => array('scalar'=>1001, '<not set>', '<not set>'),
        'robot' => array('user_agent'=>'<not set>'),

		'watson weather' => false,
        
    	'agent' => [
	    	'clerk' => array('scalar'=>100, '<not set>', '<not set>'),
        ],

	    'thing' => [
            'is' => 'a stack of things',
            'callsign' => 'XXXX',
            'sms' => 'X XXX XXX XXXX',
            'email' => 'null@stackr.ca',
            'place' => 'mornington crescent',
            'stack_account' => array('account_name'=>'stack', 'balance'=>array('amount'=>100.00, 'attribute'=>'X', 'unit'=>'X')),
            'thing_account' => array('account_name'=>'thing', 'balance'=>array('amount'=>-100.0, 'attribute'=>'X', 'unit'=>'X')),
        ],
    ],
];


