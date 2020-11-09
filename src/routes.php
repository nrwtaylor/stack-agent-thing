<?php
/**
 * routes.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
// (c) 2020 Stackr Interactive Ltd

// whitefox 16 September 2020

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//ini_set("max_execution_time",1 ); //s
//ini_set("max_input_time", 2); //s
//set_time_limit(2);

// API group
$app->group('/api', function () use ($app) {
    // This is the whitefox API.  Accessible at api/whitefox/
    $app->group('/whitefox', function () use ($app) {
if (isset($app->getContainer()->get('settings')['api']['stripe'][
                'webhook'
            ])) {
        $app->post($app->getContainer()->get('settings')['api']['stripe'][
                'webhook'
            ],

            function ($request, $response, $args) {

            $credential_set = $this->get('settings')['api']['stripe']['credential_set'];

       $secret_key =
            $this->get('settings')['api']['stripe'][$credential_set]['secret_key'];

            \Stripe\Stripe::setApiKey($secret_key);

            $thing = new Thing(null);
            $thing->Create("stripe", "routes", "s/ web stripe");

                $body = $request->getParsedBody();
                $getParam = $request->getQueryParams();
                //$threadKey = $getParam('threadKey');
                $params_json = $getParam;
                $args_json = $args;

                //$uri = $request->getUri();
                //$method = $uri->getQuery();
                //$threadKey = $app->request()->params('threadKey');
                //$threadKey = $request->params('');
                //$data = json_decode($request->getBody()) ?: $request->params();
                //$data = json_decode( $app->request->getBody() ) ?: $app->request->p>
                $data = [
                    "params" => $params_json,
                    "args" => $args_json,
                    "body" => $body,
                    "merp"=>"merp"
                ];

//$data = ["test"=>"merp"];
//$data = "stripe";
            $stripe_agent = new Stripe($thing, $data);
            $session = $stripe_agent->checkoutStripe();

            return $response->withJson([ 'id' => $session->id ])->withStatus(200);


        });
}
    });

    // Introducing redpanda
    $app->group('/redpanda', function () use ($app) {
        // Developmental google calendar end-point
        $app->get('/googlecalendar', function ($request, $response, $args) {
            // Allows direct creation of Things from the URI line
            // created as <from> web@<mail_postfix>.

            $getParam = $request->getQueryParams();
            $code = $getParam['code']; // is equal to http://stackoverflow.com

            $state = $getParam['state']; // is equal to http://stackoverflow.com

            $thing = new Thing($state); // State contains uuid
            //$thing->Create("google@<mail_postfix>", "null", $code);

            $google_agent = new GoogleCalendar($thing, [
                'code' => $code,
                'state' => $state,
            ]);

            return $this->renderer->render(
                $response,
                'thing.phtml',
                $google_agent->thing_report
            );
        });

        $app->post(
            $app->getContainer()->get('settings')['api']['google'][
                'google hangouts'
            ]['webhook'],
            function ($request, $response, $args) {
                $body = $request->getParsedBody();
                $getParam = $request->getQueryParams();
                //$threadKey = $getParam('threadKey');
                $params_json = $getParam;
                $args_json = $args;

                //$uri = $request->getUri();
                //$method = $uri->getQuery();
                //$threadKey = $app->request()->params('threadKey');
                //$threadKey = $request->params('');
                //$data = json_decode($request->getBody()) ?: $request->params();
                //$data = json_decode( $app->request->getBody() ) ?: $app->request->params();
                $data = [
                    "params" => $params_json,
                    "args" => $arg_json,
                    "body" => $body,
                ];
                //$data = $body;
                ignore_user_abort(true);
                set_time_limit(0);

                ob_start();

                $prod = true;
                if ($prod == true) {
                    $serverProtocol = filter_input(
                        INPUT_SERVER,
                        'SERVER_PROTOCOL',
                        FILTER_SANITIZE_STRING
                    );
                    header($serverProtocol . ' 200 OK');
                    // Disable compression (in case content length is compressed).
                    header('Content-Encoding: none');
                    header('Content-Length: ' . ob_get_length());

                    // Close the connection.
                    header('Connection: close');
                } else {
                    header('HTTP/1.0 200 OK');
                    header("Content-Type: application/json");
                    header('Content-Length: ' . ob_get_length());
                }

                ob_end_flush();
                ob_flush();
                flush();

                $input = json_encode($body);
                $thing = new Thing(null);

                $nom_to = "null";
                $sender_id = "null";
                $test_text = "s/ google hangouts";

                $thing->Create($sender_id, $nom_to, $test_text);

                $channel = new Channel($thing, "hangoutschat");
                $agent = new Googlehangouts($thing, $data);

                //                $agent = new Googlehangouts($thing, array($body, $threadKey));
                //                $agent = new Agent($thing);

                $thing->flagRed();
                $response_text = "foo";
                return $this->response->write($response_text)->withStatus(200);
                //  }
                //            }

                return;
            }
        );

        // Non-operational SMS end-point.
        $app->post('/sms', function ($request, $response, $args) {
            return $response->withHeader('HTTP/1.0 200 OK')->withStatus(200);
        });

        // Null end-point.  For testing against.
        $app->post('/null', function ($request, $response, $args) {
            return $response->withHeader('HTTP/1.0 200 OK')->withStatus(200);
        });

        // Operational end-point for GEARMAN
if (isset($app->getContainer()->get('settings')['api']['stripe'][
                'webhook'
            ])) {

        $app->get(
            $app->getContainer()->get('settings')['api']['gearman']['webhook'],
            function ($request, $response, $args) {
                $body = $request->getParsedBody();

                //echo "meep";
                //                $arr = json_encode(array("to"=>"web@stackr.ca", "from"=>"routes", "subject"=>"gearman webhook"));
                $arr = json_encode([
                    "to" => "web@stackr.ca",
                    "from" => "snowflake",
                    "subject" => "snowflake",
                ]);

                $client = new \GearmanClient();
                $client->addServer();

                $client->doHighBackground("call_agent", $arr);

                return;
                // $response->withHeader('HTTP/1.0 200 OK')
                //                ->withStatus(200);
            }
        );
}
        // Operational end-point for Microsoft
        $app->post(
            $app->getContainer()->get('settings')['api']['microsoft'][
                'webhook'
            ],
            function ($request, $response, $args) {
                //        $app->post('/webhook_microsoft_fn5yozm', function ($request, $response, $args)  {
                $body = $request->getParsedBody();

                //$body = $response->getBody();

                ignore_user_abort(true);
                set_time_limit(0);

                ob_start();

                $prod = true;
                if ($prod == true) {
                    $serverProtocol = filter_input(
                        INPUT_SERVER,
                        'SERVER_PROTOCOL',
                        FILTER_SANITIZE_STRING
                    );
                    header($serverProtocol . ' 200 OK');
                    // Disable compression (in case content length is compressed).
                    header('Content-Encoding: none');
                    header('Content-Length: ' . ob_get_length());

                    // Close the connection.
                    header('Connection: close');
                } else {
                    header('HTTP/1.0 200 OK');
                    header("Content-Type: application/json");
                    header('Content-Length: ' . ob_get_length());
                }

                ob_end_flush();
                ob_flush();
                flush();

                //            $microsoft_thing = new Thing(null);
                //            $microsoft_thing->Create( $body['conversation']['id'], $body['from']['id'], $body['text'] );

                //        $microsoft_thing->db->setFrom($microsoft_thing->from);

                //        $microsoft_thing->json->setField("message0");
                //        $microsoft_thing->json->writeVariable( array("edna") , $body  );

                //            return $response->withHeader('HTTP/1.0 200 OK')
                //                ->withStatus(200);

                $queue = false;
                // Flag Red so that the agent handler picks it up.
                if ($queue) {
                    $to = $body['conversation']['id'];

                    $arr = json_encode([
                        "to" => $to,
                        "from" => $body['from']['id'],
                        "subject" => $body['text'],
                    ]);

                    $client = new \GearmanClient();
                    $client->addServer();

                    $client->doNormal("call_agent", $arr);
                    //$client->doHighBackground("call_agent", $arr);
                } else {
                    $microsoft_thing = new Thing(null);
                    $microsoft_thing->Create(
                        $body['conversation']['id'],
                        $body['from']['id'],
                        $body['text']
                    );

                    $m = new Microsoft($microsoft_thing, $body);

                    $agent = new Agent($microsoft_thing);
                }

                $message = "Stackr received a message from ";
                $message .= $body['msisdn'] . " to " . $body['to'];
                $message .= " which said " . $body['text'] . ".";

                return $response
                    ->withHeader('HTTP/1.0 200 OK')
                    ->withStatus(200);
            }
        );

        // Operational end-point for NEXMO
        $app->post(
            $app->getContainer()->get('settings')['api']['nexmo']['webhook'],
            function ($request, $response, $args) {
                $body = $request->getParsedBody();

                ignore_user_abort(true);
                set_time_limit(0);

                ob_start();

                $prod = true;
                if ($prod == true) {
                    $serverProtocol = filter_input(
                        INPUT_SERVER,
                        'SERVER_PROTOCOL',
                        FILTER_SANITIZE_STRING
                    );
                    header($serverProtocol . ' 200 OK');
                    // Disable compression (in case content length is compressed).
                    header('Content-Encoding: none');
                    header('Content-Length: ' . ob_get_length());

                    // Close the connection.
                    header('Connection: close');
                } else {
                    header('HTTP/1.0 200 OK');
                    header("Content-Type: application/json");
                    header('Content-Length: ' . ob_get_length());
                }

                ob_end_flush();
                ob_flush();
                flush();

                $message_id = $body['messageId'];

                $inject_text = "";

                $queue = true;

                // Flag Red so that the agent handler picks it up.
                if ($queue) {
                    $arr = json_encode([
                        "to" => $body['msisdn'],
                        "from" => $body['to'],
                        "subject" => $body['text'] . $inject_text,
                        "body" => $body,
                    ]);

                    $client = new \GearmanClient();
                    $client->addServer();
                    $client->doNormal("call_agent", $arr);
                    //$client->doHighBackground("call_agent", $arr);
                } else {
                    $sms_thing = new Thing(null);
                    $sms_thing->Create(
                        $body['msisdn'],
                        $body['to'],
                        $body['text']
                    );

                    $channel = new Channel($sms_thing, "sms");

                    $agent = new Agent($sms_thing);
                }

                $message = "Stackr received a message from ";
                $message .= $body['msisdn'] . " to " . $body['to'];
                $message .= " which said " . $body['text'] . ".";

                return $response
                    ->withHeader('HTTP/1.0 200 OK')
                    ->withStatus(200);
            }
        );

        $app->post(
            $app->getContainer()->get('settings')['api']['slack']['webhook'],
            function ($request, $response, $args) {
                ob_start();

                $prod = true;
                if ($prod == true) {
                    $serverProtocol = filter_input(
                        INPUT_SERVER,
                        'SERVER_PROTOCOL',
                        FILTER_SANITIZE_STRING
                    );
                    header($serverProtocol . ' 200 OK');
                    // Disable compression (in case content length is compressed).
                    header('Content-Encoding: none');
                    header('Content-Length: ' . ob_get_length());

                    // Close the connection.
                    header('Connection: close');
                } else {
                    header('HTTP/1.0 200 OK');
                    header("Content-Type: application/json");
                    header('Content-Length: ' . ob_get_length());
                }

                ob_end_flush();
                ob_flush();
                flush();

                // Create an empty Thing
                $slack_thing = new Thing(null);
                //$channel = new Channel($slack_thing,"slack");

                //$channel = new Channel($slack_thing, "slack");
                // Retrieve the body of the request
                $body = $request->getParsedBody();

                //                $channel = new Channel($slack_thing,"slack");

                // Check if this is a webhook verification
                //https://api.slack.com/events/url_verification
                if (
                    isset($body['type']) and
                    $body['type'] == 'url_verification'
                ) {
                    $challenge = $body['challenge'];
                    $slack_thing->Create($sender_id, $page_id, $body['type']);

                    $body = $response->getBody();
                    $body->write($challenge);

                    return $response
                        ->withHeader('HTTP/1.0 200 OK')
                        ->withStatus(200);
                }

                $verify_token = 'hellomordok';

                // https://gist.github.com/stefanzweifel/04be27486517cd7d3422
                $query = $request->getQueryParams();
                //$body = $response->getBody();

                $input = json_decode(file_get_contents("php://input"), true);

                echo "Slack says it's good to remind you that the button is doing something. ";

                if (isset($body['event'])) {
                    echo "An event was received";
                    if ($body['event']['type'] == "message") {
                        echo "and identified as a message";
                        $nom_to = $body['api_app_id'];
                        $sender_id = $body['event']['user'];

                        $test_text = $body['event']['text'];

                        $test_text = ltrim(
                            str_replace("<@U6N5VCYDT>", "", $test_text)
                        );
                        $slack_thing->Create($sender_id, $nom_to, $test_text);

                        $channel = new Channel($slack_thing, "slack");

                        $slack_agent = new Slack($slack_thing, $body);

                        $slack_agent = new Agent($slack_thing);

                        $slack_thing->flagRed();
                        $response_text = "foo";
                        return $this->response
                            ->write($response_text)
                            ->withStatus(200);
                    }
                }

                // So it is not a message event?
                // Perhaps it is a command.
                if (isset($body['command'])) {
                    echo "Command accepted. ";
                    //$sender_id = $body['user_id'] . "-" . $body['channel_id'];
                    $sender_id = $body['user_id'];

                    $page_id = "mordok";
                    $text = $body['text'];

                    $slack_thing->Create($sender_id, $page_id, $text);

                    $slack_agent = new Slack($slack_thing, $body);
                    $channel = new Channel($slack_thing, "slack");
                    //$slack_agent = new Agent($slack_thing, $body);

                    //$slack_agent = new Slack($slack_thing);
                    $slack_agent = new Agent($slack_thing);

                    $slack_thing->flagRed();
                    $response_text = "";

                    return $this->response
                        ->write($response_text)
                        ->withStatus(200);
                }

                if (isset($body['payload'])) {
                    echo "Slack datagram transmitted.";
                    //$sender_id = $body['user_id'];
                    $sender_id = "not extracted";
                    $page_id = "mordok";
                    $text = "s/ button press"; //$body['text'];
                    $slack_thing->Create($sender_id, $page_id, $text);

                    $slack_agent = new Slack($slack_thing, $body);
                    $slack_thing->flagRed();

                    $response_text = "";

                    return $this->response
                        ->write($response_text)
                        ->withStatus(200);
                }

                foreach ($body as $key => $value) {
                    $t = $t . " " . $key;
                }

                $test_text = $t;
                //$test_text = $body['event']['text'];
                ob_start();
                var_dump($body);
                $test_text = ob_get_clean();

                //$test_text="ht";
                $sender_id = "not extracted";
                $page_id = "not extracted";

                $slack_thing->Create($sender_id, $page_id, $test_text);
            }
        );

        $app->post(
            $app->getContainer()->get('settings')['api']['facebook']['webhook'],
            function ($request, $response, $args) {
                $body = $request->getParsedBody();

                $verify_token = '<private>'; //

                // Page access token
                // Access Token

                $query = $request->getQueryParams();
                $body = $response->getBody();
                if (
                    isset($query['hub_verify_token']) &&
                    isset($query['hub_challenge']) &&
                    $query['hub_verify_token'] === $verify_token
                ) {
                    $body->write($query['hub_challenge']);
                }

                $raw_input = file_get_contents("php://input");
                $message = json_decode($raw_input, true);
                $input = $message;

                // Page ID of Facebook page which is sending message
                $page_id = $message['entry'][0]['id'];

                // User Scope ID of sender.
                $sender_id =
                    $message['entry'][0]['messaging'][0]['sender']['id'];
                // Get Message text if available
                $text = isset(
                    $message['entry'][0]['messaging'][0]['message']['text']
                )
                    ? $message['entry'][0]['messaging'][0]['message']['text']
                    : '';
                // Get Postback payload if available
                $postback = isset(
                    $message['entry'][0]['messaging'][0]['postback']['payload']
                )
                    ? $message['entry'][0]['messaging'][0]['postback'][
                        'payload'
                    ]
                    : '';

                if ($text) {
                    // Return a 200 OK to facebook as quickly as possible.
                    // If not messenger resends the same message.
                    ignore_user_abort(true);
                    set_time_limit(0);
                    ob_start();
                    header('HTTP/1.0 200 OK');
                    header("Content-Type: application/json");
                    header('Content-Length: ' . ob_get_length());
                    ob_end_flush();
                    ob_flush();
                    flush();

                    $queue = true;
                    if ($queue) {
                        $arr = json_encode([
                            "to" => $sender_id,
                            "from" => $page_id,
                            "subject" => $text,
                        ]);
                        $client = new \GearmanClient();
                        $client->addServer();
                        //$client->addServer("10.0.0.24");
                        //$client->addServer("10.0.0.25");
                        //$client->doNormal("call_agent", $arr);
                        $client->doHighBackground("call_agent", $arr);
                    } else {
                        $fb_thing->flagGreen(); // Avoid the que handler picking it up.

                        // So when this is turned on it ends up receiving multipl
                        // FB messages and creating multiple similar responses
                        // to reply to.
                        // Need to understand what FB is doing.

                        $agent = new Agent($fb_thing);
                    }
                }

                return $response
                    ->withHeader('HTTP/1.0 200 OK')
                    ->withStatus(200);
            }
        );

        // api/redpanda
        $app->get('/to/{to}/subject/{subject}', function (
            $request,
            $response,
            $args
        ) {
            // Allows direct creation of Things from the URI line
            // created as <from> web@<mail_postfix>.

            $to = $args['to'];
            $subject = $args['subject'];

            $thing = new Thing(null);
            $thing->Create("web@stackr.ca", $to, $subject);

            // Create with a stack value to reflect user generated/provided input.
            $thingreport = [
                'thing' => $thing->thing,
                'info' => 'This is a GET connector to create new Things.  It does not extract any
				other information from the datagram.  And only uses the text in the URI provided.',
                'help' =>
                    'Also try ' .
                    'thing/<34 char>/to/<agent name>/subject/<998 characters>',
                'whatisthis' =>
                    'This is the API creation endpoint for submitting to-subject pairs to this stack',
            ];

            return $this->response->withJson($thingreport);
        });

        // api/redpanda
        $app->group('/thing/{uuid}', function () use ($app) {
            $app->get('', function ($request, $response, $args) {
                $uuid = $args['uuid'];
                $thing = new Thing($uuid);
                //$j = json_decode ($thing->thing);

                if ($thing->thing != false) {
                    $t = $thing->thing;

                    // NOMINAL INFORMATION RELEASE
                    // Remove nom_from

                    //$identity_agent = new Identity($thing);
                    $t->nom_from = "Z";

                    $t->associations = false;
                    $t->message0 = false;
                    $t->message1 = false;
                    $t->message2 = false;

                    $t->message3 = false;
                    $t->message4 = false;
                    $t->message5 = false;
                    $t->message6 = false;
                    $t->message7 = false;

                    $t->settings = false;
                    $t->variables = $thing->json->array_data;
                } else {
                    $t = false;
                }
                // Just display the thing.

                $web_prefix =
                    (isset($_SERVER['HTTPS']) ? "https" : "http") .
                    "://$_SERVER[HTTP_HOST]/";
                //$web_prefix = "";

                $thingreport = [
                    'thing' => $t,
                    'info' =>
                        'Turns out Stackr has an imperfect and forgetful memory - ' .
                        $web_prefix .
                        'privacy.',
                    'help' => 'Check your junk/spam folder.',
                    'devstack' => 'Expect false when no record.',
                    'whatisthis' =>
                        'This is the redpanda API thing JSON end-point.',
                ];

                return $this->response->withJson($thingreport);
            });

            // api/redpanda
            $app->get('/forget', function ($request, $response, $args) {
                $uuid = $args['uuid'];
                $thing = new Thing($uuid);
                $thing->Forget();

                $thingreport = [
                    'thing' => false,
                    'info' => 'That thing was forgotten.',
                    'help' => 'The SQL record was deleted',
                    'devstack' => 'true to indicate an error',
                    'whatisthis' =>
                        'You have just deleted the db record for this Thing',
                ];

                return $this->response->withJson($thingreport);
            });
        });

        $app->group('/{subject}', function () use ($app) {
            $app->get('', function ($request, $response, $args) {
                // Allows direct creation of Things from the URI line
                // created as <from> web@stackr.ca.
                $to = "agent";
                $subject = $args['subject'];

                $thing = new Thing(null);
                $thing->Create("web@stackr.ca", $to, $subject);

                // devstack implement identity toggle
                // $identity = new Identity($thing); // run quiet
                // For now just show nom_from as 'available'
                $thing->thing->nom_from = "Z";

                $thingreport = [
                    'thing' => $thing->thing,
                    'info' => 'This is a GET connector to message Agent.  It does not extract any
                    other information from the datagram.  And only uses the text in the URI provided.',
                    'help' => 'api/redpanda/<998 characters>',
                    'whatisthis' =>
                        'This is the API endpoint for submitting a message to Agent.',
                ];

                return $this->response->withJson($thingreport);
            });
        });
    });
});

// Route handler for everything after the /
$app->get('[/{params:.*}]', function ($request, $response, $args) {

    //    ini_set("max_input_time", 2); //s
    //set_time_limit(2);

    //ini_set("max_execution_time",1 ); //s
    //$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $actual_link = $_SERVER['REQUEST_URI'];

    $datagram = [];

    $params = $actual_link;

    //  $params = $args['params'];

    $thing_report['start_time'] = microtime(true);

    // Add any post ? mark text to the command
    $input_array = $request->getParams();
    $input = implode(' ', $input_array);
    if ($input != "") {
        $input = " " . $input;
    }

    // $params_array = explode("/",$args['params']);
    $params_array = explode("/", $actual_link);

    $command = str_replace("/", " ", $params) . $input;
    $command = ltrim($command, " ");
    $command = rtrim($command, " ");

    //var_dump($command);
    //exit();
    if (substr($command, 0, 15) == 'googleauthorize') {
        $thing = new Thing(null);
        $thing->Create("web", "routes", "s/ web googleauthorize");
        $googleauthorize_agent = new Googleauthorize($thing);

        $thing_report = $googleauthorize_agent->thing_report;
        $thing_report['requested_channel'] = 'thing';

        $thing_report['etime'] = number_format($thing->elapsed_runtime());
        $thing_report['request'] = $thing->subject;

        $thing->flagGreen();

        $bleep = [];
        $bleep['thing_report'] = $thing_report;

        return $this->renderer->render($response, 'thing.phtml', $bleep);
    }
    // extract uuid
    // See if there is a UUID in the web address, and
    // extract it to $uuid.   null if not found.
    if (!isset($this->uuids)) {
        $this->uuids = [];
    }

    $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";
    preg_match_all($pattern, $command, $m);

    $arr = $m[0];
    //array_pop($arr);
    $this->uuids = $arr;

    if (count($this->uuids) == 1) {
        $uuid = $this->uuids[0];
    } else {
        $uuid = null;
    }

    // Last item is going to be the command
    $last = $params_array[count($params_array) - 1];

    switch (true) {
        case $command == null:
        case $command == "stackr.ca": //prod

        case $command == "/":
            $thing_report['choices'] = ["Privacy", "Thing"];

            $datagram['thing_report'] = $thing_report;
            $datagram['args'] = $args;

            return $this->renderer->render($response, 'index.phtml', $datagram);
            break;

        case $command == "privacy":
        case $command == "privacy-policy":
            $thing = new Thing($uuid);
            $thing->Create("web", "routes", "s/ web privacy");

            $privacy_agent = new Privacy($thing);

            $thing_report = $privacy_agent->thing_report;
            $thing_report['requested_channel'] = 'thing';

            $thing_report['etime'] = number_format($thing->elapsed_runtime());
            $thing_report['request'] = $thing->subject;

            $thing->flagGreen();

            $bleep = [];
            $bleep['thing_report'] = $thing_report;

            return $this->renderer->render($response, 'thing.phtml', $bleep);
            break;

        case $command == "robots.txt":
            $thing = new Thing($uuid);
            $thing->Create("robot", "routes", "s/ web robots");

            $robot_agent = new Robot($thing);

            $content = $robot_agent->thing_report['txt'];
            $response->write($content);
            return $response->withHeader('Content-Type', 'text/plain');
            break;
        case $command == "termsofuse":
        case $command == "terms-of-use":
            $thing = new Thing($uuid);
            $thing->Create("web", "routes", "s/ web terms of use");

            $agent = new Termsofuse($thing);

            $thing_report = $agent->thing_report;
            $thing_report['requested_channel'] = 'thing';

            $thing_report['etime'] = number_format($thing->elapsed_runtime());
            $thing_report['request'] = $thing->subject;

            $thing->flagGreen();

            $bleep = [];
            $bleep['thing_report'] = $thing_report;

            return $this->renderer->render($response, 'thing.phtml', $bleep);
            break;

        case strpos($last, ".") !== false:

            // File request of some sort

            // Unless it is a number and a number

            $t = explode(".", $last);
            $is_number = false;
            // Unless it is a number and a number
            if (is_numeric($t[0]) and is_numeric($t[1])) {
                $is_number = true;
            }
            if (!$is_number) {
                $agent_name = $t[0];
                $ext_name = $t[1];
                $agent_class_name = 'Make' . strtolower($ext_name);

                $web_thing = new Thing($uuid);

                $content_types = [
                    "pdf" => 'application/pdf',
                    "png" => 'image/png',
                    "txt" => 'text/plain',
                    "php" => 'text/plain',
                    "log" => 'text/plain',
                    "json" => 'application/json',
                    "ics" => 'text/calendar',
                ];
/*
                if ($uuid == null) {

                    $response->write(false);

                    if (isset($content_types[$ext_name])) {
                        return $response->withHeader(
                            'Content-Type',
                            $content_types[$ext_name]
                        );
                    }
                }
*/
                // See if the extension name is one of these.

                $found = false;
                foreach ($content_types as $key => $value) {
                    if ($key == $ext_name) {
                        $found = true;
                    }
                }

                if ($found === false) {
                    return $response->withStatus(404);
                }

    // OK - Done all we can.
    // So now need to create a Thing.

        $web_thing->db = new Database($web_thing->uuid,"null");

                try {
                    $agent_namespace_name =
                        '\\Nrwtaylor\\StackAgentThing\\' . $agent_class_name;

                    $agent = new $agent_namespace_name($web_thing, $agent_name);

                } catch (Exception $e) {
                    //echo 'Caught exception: ',  $e->getMessage(), "\n";
                    return $response->withStatus(404);
                }
                ob_clean();
                if (!isset($agent->thing_report[strtolower($ext_name)])) {
                    //var_dump($ext_name);
                    //echo "meep";
// TODO: TEST
//                    exit();
                    return $response->withStatus(404);

                }

                $content = $agent->thing_report[strtolower($ext_name)];
                if (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $content)) {
                    //   //return TRUE;
                    //} else {
                    $content = base64_decode($content);
                }
                //return FALSE;
                //}

                // Assume that image is base64 encoded.
                // Non base64 encoding doesn't pass through Gearman
                //$content = base64_decode($content);

                if ($content === false or $content == null) {
                    $response->write(false);
                    return $response->withHeader(
                        'Content-Type',
                        $content_types[$ext_name]
                    );
                }
                $response->write($content);
                return $response->withHeader(
                    'Content-Type',
                    $content_types[$ext_name]
                );
            }
        case true:
            // Everything else
            $thing = new Thing($uuid);

            $channel = new Channel($thing, "web");

            // Check if this is no thing.
            // Don't respond to web requests without a UUID
            // to a thing which doesn't exist on the stack.
            //echo $thing->uuid;

            if ($thing->thing == false) {
                if ($uuid != null) {
                    $datagram = [];
                    $datagram['thing'] = false;
                    $datagram['thing_report'] = false;

                    return $this->renderer->render(
                        $response,
                        'thing.phtml',
                        $datagram
                    );
                }
            }

            $slug = new Slug($thing, $command);

            // So the uuid is null
            if ($uuid == null) {
                if (isset($slug->state) and $slug->state == "off") {
                    $datagram = [];
                    $datagram['thing'] = false;
                    $datagram['thing_report'] = false;

                    return $this->renderer->render(
                        $response,
                        'thing.phtml',
                        $datagram
                    );
                }

                if (isset($slug) and !$slug->isSlug($command)) {
                    $datagram = [];
                    $datagram['thing'] = false;
                    $datagram['thing_report'] = false;

                    return $this->renderer->render(
                        $response,
                        'thing.phtml',
                        $datagram
                    );
                }
            }

            // Unaddressed web request to a Thing existing on the stack.
            // Enter into stack as coming from web and addressed to stack agent.
            ///if ($thing->thing == false) {
            if ($uuid == null) {
                $thing->Create("web@stackr.ca", "agent", $command);
            }

            $filtered_command = $command;
            if (!is_numeric($command)) {
                $filtered_command = str_replace("-", " ", $slug->slug);
            }

            $compression_thing = new Compression($thing, $filtered_command);
            if (isset($compression_thing->filtered_input)) {
                // Compressions found.
                $filtered_command = $compression_thing->filtered_input;
            }

            $agent = new Agent($thing, $filtered_command);

            $thing_report = $agent->thing_report;

            $thing_report['filename'] = $last;
            $thing_report['request'] = $thing->subject;

            $arr = explode(' ', trim($command));
            $channel = $arr[0];

            switch ($channel) {
                case "thing":
                    //    if ($channel == "thing") {
                    if (isset($thing_report['web'])) {
                        $channel = "web";
                    } elseif (isset($thing_report['sms'])) {
                        $channel = "sms";
                    } else {
                        $channel = "sms";
                    }
                    //    }
                    break;
                case "email":
                    $makeemail_agent = new Makeemail($thing, $thing_report);

                    //                $makeemail_agent = new Makeemail($thing, $thing_report['message']);
                    $this->email_message = $makeemail_agent->email_message;
                    $thing_report['email'] = $makeemail_agent->email_message;
                    break;
                default:
                    //var_dump($channel);
                    //var_dump($thing_report['sms']);
                    if (isset($thing_report['web'])) {
                        $channel = "web";
                    } elseif (isset($thing_report['sms'])) {
                        $channel = "sms";
                    } else {
                        $channel = "sms";
                    }
            }

            $thing_report['requested_channel'] = $channel;

            // Recognize this as a Thing being channeled to agent.
            // and describe as a Thing request

            if (
                strpos($command, "agent") !== false and
                strpos($command, "thing") !== false
            ) {
                $thing_report['requested_channel'] = "thing";

                $datagram = [];
                $datagram['thing'] = false;
                $datagram['thing_report'] = false;
                return $this->renderer->render(
                    $response,
                    'thing.phtml',
                    $datagram
                );
            }

            // Flag the Thing Green.
            // Stackr can't accept Red flagged Things from the Internet.
            // Unless ID validated.
            $thing->flagGreen();

            // Report overflow
            if ($thing->json->size_overflow > 0) {
                $thing_report['response'] =
                    "Thing overflow: " .
                    $thing->json->size_overflow .
                    " characters not saved.";
            }
            //if ($thing->json->size_overflow != false) {echo "Stack write failed.";}

            // We have to give a response.  Bleep.
            // So give the full thing report.
            // Which is available via the API at
            // <web_prefix>api/redpanda/<uuid>
            //ie <web_prefix>api/redpanda/839887dd-0772-4ee3-aa53-82b40da89ac0
            $datagram = [];
            $datagram['thing_report'] = $thing_report;
            return $this->renderer->render($response, 'thing.phtml', $datagram);
    }
});
