<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Slack
{
    // dev stack sockets

    //    \Ratchet\Client\connect('ws://echo.socketo.me:9000')->then(function($conn) {
    //        $conn->on('message', function($msg) use ($conn) {
    //            echo "Received: {$msg}\n";
    //            $conn->close();
    //        });

    //        $conn->send('Hello World!');
    //    }, function ($e) {
    //        echo "Could not connect: {$e->getMessage()}\n";
    //    });

    public $var = "hello";

    function __construct(Thing $thing, $input = null)
    {
        $this->cost = 50;

        $this->test = "Development code";

        $this->thing = $thing;

        $this->thing_report = ["thing" => $this->thing->thing];
        $this->thing_report["info"] = 'This is the "Slack" agent.';

        $this->client_secret =
            $this->thing->container["api"]["slack"]["client secret"];
        $this->client_id = $this->thing->container["api"]["slack"]["client ID"];
        $this->verification_token =
            $this->thing->container["api"]["slack"]["verification token"];
        $this->bot_user_oauth_access_token =
            $this->thing->container["api"]["slack"][
                "bot user oauth access token"
            ];

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        if ($this->from == "null@stackr.ca") {
            return;
        }

        $this->node_list = ["sms send" => ["sms send"]];

        $this->thing->log(
            '<pre> Agent "Slack" running on Thing ' .
                $this->thing->nuuid .
                ".</pre>"
        );
        $this->thing->log(
            '<pre> Agent "Slack" received this Thing "' .
                $this->subject .
                '".</pre>'
        );

        // Read the input array.
        // for testing
        /*
$input = 
'{
        "token": "XXYYZZ",
        "team_id": "TXXXXXXXX",
        "api_app_id": "AXXXXXXXXX",
        "event": {
                "type": "name_of_event",
                "event_ts": "1234567890.123456",
                "user": "UXXXXXXX1"
        },
        "type": "event_callback",
        "authed_users": [
                "UXXXXXXX1",
                "UXXXXXXX2"
        ],
        "event_id": "Ev08MFMKH6",
        "event_time": 1234567890
}';
*/
        //echo "foobar";
        //echo $input;

        $this->set();

        $this->eventGet();

        if ($this->to == "slackhandler") {
            $this->Connect();
            $this->getEvents();
            return;
        }

        ob_start();
        $test = ob_get_clean();

        $this->message = "devstack test";

        if (is_array($input)) {
            if (isset($input["payload"])) {
                echo "payload found";
                $input = json_decode($input["payload"], true);
            }

            if (isset($input["token"])) {
                echo "token found";
                $this->body = $input;
                $this->eventSet();
                $this->variablesGet();
                $this->thing->flagRed();
                echo "stored event";
                return false; // Return having set the Thing's slack variables.
                // Next time it is called from the agent handler it won't have an array payload
            }

            if (isset($input["thing"]) or isset($input["things"])) {
                if (isset($input["choices"])) {
                    $this->choices = $input["choices"];
                } else {
                    $this->choices = false;
                }

                if (isset($input["sms"])) {
                    $this->message = $input["sms"];
                }
                /*
        		if (isset ($input['message'])) {
		            $this->message = $input['message'];
                }
*/
                if (!isset($this->message)) {
                    $this->message = "Message not set";
                }
            }
        }

        $this->input = $input;

        /* Websocket experimentation.  Might not be needed.

	// Now run a websocket connect to Slack
		$this->Connect();

echo "<br>----<br>";

if (!isset($this->error_message)) {
	$this->getEvents();

	//$this->sendEvent();

} else {
	return true;
}

// Test message send
//$this->makeMessage(); 
//$this->sendMessage();

*/

        if ($this->readSubject() == true) {
            $this->thing_report = [
                "thing" => $this->thing->thing,
                "choices" => false,
                "info" => "A cell number wasn't provided.",
                "help" => "from needs to be a number.",
            ];

            $this->thing->log(
                '<pre> Agent "Slack" completed without sending a message</pre>'
            );
            return;
        }

        if ($this->isEcho()) {
            // do nothing
        } else {
            $this->respond();
        }

        $this->thing->log('Agent "Slack" completed.');

        return;
    }

    public function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["slack", "refreshed_at"],
            $this->thing->json->time()
        );
        //        $this->thing->json->writeVariable(array("slack",
        //            "name"),  $this->channel_name
        //            );
    }

    function eventSet()
    {
        $this->thing->log('<pre> Agent "Slack" called eventSet()');

        $this->thing->db->setFrom($this->from);

        $this->thing->json->setField("message0");
        $this->thing->json->writeVariable(["slack"], $this->body);

    }

    function eventGet()
    {
        $this->thing->log('<pre> Agent "Slack" called eventGet()</pre>');

        $this->thing->db->setFrom($this->from);

        $this->thing->json->setField("message0");
        $this->body = $this->thing->json->readVariable(["slack"]);

        $this->variablesGet();

        return $this->body;
    }

    function variablesGet()
    {
        $this->channel_id = $this->getChannel();
        $this->user = $this->getUser();
        $this->text = $this->getText();
        $this->response_url = null;
        if (isset($this->body["response_url"])) {
            $this->response_url = $this->body["response_url"];
        }

    }

    private function respond()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        //if ( $this->isCommand() ) {
        $to = $this->getChannel();
        //}

        if ($this->input != null) {
            $test_message = $this->input;
        } else {
            $test_message = $this->subject;
        }

        $test_message = $this->makeMessage("command");

        $this->thing->json->setField("message0");
        $this->response_url = $this->thing->json->readVariable([
            "slack",
            "response_url",
        ]);

        if ($this->response_url != false) {
            $this->chat_webhookResponse(null, $test_message);

            $this->thing->log("Slack message sent");

            $this->thing_report["info"] =
                'Agent "Slack" sent a Slack webhook response.';

            $this->thing_report["choices"] = false;
            $this->thing_report["help"] = "In development.";
            $this->thing_report["log"] = $this->thing->log;
            $this->thing->flagGreen();
            return $this->thing_report;
        }

        $test_message = $this->makeMessage("event");

        if (!isset($test_message)) {
            $test_message = "null message";
        } else {
        }

        $this->chat_postMessage($this->channel_id, $test_message);

        $this->thing->log("Slack message sent to " . $this->channel_id);
        $this->thing_report["info"] =
            '<pre> Agent "Slack" sent a Slack message to channel ' .
            $this->channel_id .
            "</pre>";

        $this->thing_report["choices"] = false;
        $this->thing_report["help"] = "In development.";
        $this->thing_report["log"] = $this->thing->log;

        $this->thing->flagGreen();
        return $this->thing_report;

        //		if ($this->thing->account['stack']->balance['amount'] >= $this->cost ) {
        //			$this->sendMessage($to, $test_message);

        //			$this->thing->account['stack']->Debit($this->cost);

        //                        $message_thing = new Message($this->thing, $this->thing_report);
        //                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        //
        //		} else {
        //
        //			$this->thing_report['info'] = 'SMS not sent.  Balance of ' . $this->thing->account['stack']->balance['amount'] . " less than " . $this->cost ;
        //		}

        //		return;
    }

    public function readSubject()
    {
        return false;
    }

    function Connect()
    {
        /* Expected response style
{
    "ok": true,
    "url": "wss:\/\/ms9.slack-msgs.com\/websocket\/2I5yBpcvk",
    "team": {
        "id": "T654321",
        "name": "Librarian Society of Soledad",
        "domain": "libsocos",
        "enterprise_id": "E234567",
        "enterprise_name": "Intercontinental Librarian Society"
    },
    "self": {
        "id": "W123456",
        "name": "brautigan"
    }
}

*/

        $token = $this->bot_user_oauth_access_token;

        //API url
        $url = "https://slack.com/api/rtm.connect?token=" . $token;

        //Initiate cURL.
        $ch = curl_init($url);

        //The JSON data.
        //$jsonData = '{"token":"' . $token . '"}';
        $jsonData = "";

        //Encode the array into JSON.
        $jsonDataEncoded = $jsonData;

        //Tell cURL that we want to send a POST request.
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
        ]);

        //Execute the request
        echo "<br>";
        $result = curl_exec($ch);
        echo $result;
        $response = json_decode($result);

        if ($response->ok) {
            $this->resource_id = $ch;
            $this->websocket_url = $response->url;
            $this->team = $response->team;
            $this->self = $response->self;

            echo "<br>Connected and found,";
            echo $this->self->name . "'s ID is " . $this->self->id . ".";
            // Valid response
        } else {
            echo "Unable to connect";
            $this->error_message = $response->error;
            echo $this->error_message;
            // Invalid respsone
        }

    }

    function chat_webhookResponse($to, $message)
    {
        //https://api.slack.com/methods/chat.postMessage
        $token = $this->bot_user_oauth_access_token;

        // API url
        $url = $this->response_url;

        //Initiate cURL.
        //$slack_call = curl_init($url);
        //The JSON data.
        //$jsonData = '{"token":"' . $token . '"}';
        //                $data = array(
        //			"channel" => $to,
        //			"text" => $message
        //			);

        /* Testing
                $message = array(
                        "text" => "merp"
                        );
*/

        /*
$data = $message;
*/

        // Encode the array into JSON.
        $data = "payload=" . json_encode($message);
        // Not
        // $data = array('payload' => json_encode($message));

        $message = "hello world from curl";
        $room = "random";
        $icon = ":smile:";

        /*
    $data = "payload=" . json_encode(array(         
        "channel"       =>  "#{$room}",
        "text"          =>  $message,
        "icon_emoji"    =>  $icon
    ));
*/

        $slack_call = curl_init();
        curl_setopt($slack_call, CURLOPT_URL, $url);
        curl_setopt($slack_call, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($slack_call, CURLOPT_POSTFIELDS, $data);
        curl_setopt($slack_call, CURLOPT_CRLF, true);
        curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($slack_call, CURLOPT_SSL_VERIFYPEER, false);

        /*
curl_setopt($slack_call, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json",
    "Content-Length: " . strlen($data))
);
*/
        /*
curl_setopt($slack_call, CURLOPT_HTTPHEADER, array(
    "Content-Type: application/json")
);
*/

        //Execute the request
        $result = curl_exec($slack_call);
        $response = json_decode($result);

        if ($result == "ok") {
            //$this->resource_id = $slack_call;
            //$this->websocket_url = $response->url;
            //$this->team = $response->team;
            //$this->self = $response->self;
            $this->thing_report["info"] = $result;
            return false;
            //echo "<br>Connected and found,";
            //echo $this->self->name. "'s ID is " . $this->self->id . ".";
            // Valid response
        } else {
            return true;
            //echo "Unable to connect";
            //        $this->error_message = $response->error;
            //        echo $this->error_message;
            // Invalid respsone
        }
        return;
    }

    function chat_postMessage($to, $message = null)
    {
        //$this->thing->json->setField("message1");
        //$this->thing->json->writeVariable( array("test") , $to . $message  );

        //echo "chat_postMessage";
        //https://api.slack.com/methods/chat.postMessage
        $token = $this->bot_user_oauth_access_token;

        if ($message == null) {
            $message = "postMessage null message";
        }

        if (!is_array($message)) {
            $message = "postMessage not an array";
            return true;
        }

        $data = [
            "token" => $token,
            "channel" => $this->getChannel(),
            "text" => "merp",
        ];

        /*
$arr = array(
        "token" => $token,
        "channel" => $this->getChannel(), //"#mychannel",
        "text" => "bleep", //"Hello, Foo-Bar channel message.",
        "username" => "Mordok2",
    );
*/

        //$arr = $message;

        $data = http_build_query($message);

        //$data = '{payload = ' . json_encode($message) . '}';
        //$data = '{payload= ' . json_encode($data) . '}';


        /*

    $data = http_build_query([
        "token" => $token,
    	"channel" => $this->getChannel(), //"#mychannel",
    	"text" => $message, //"Hello, Foo-Bar channel message.",
    	"username" => "Mordok2",
    ]);

*/

        /*
//$data = http_build_query($message);
//$arr = json_decode($message);
$data = http_build_query($message);
*/

        // API url
        // $url = 'https://slack.com/api/chat.postMessage?token=' . $token . '&channel=' . $to . '&text=' . $message;
        $url = "https://slack.com/api/chat.postMessage";

        //Initiate cURL.
        $ch = curl_init($url);
        //The JSON data.
        //$jsonData = '{"token":"' . $token . '"}';
        $jsonData = "";

        // Encode the array into JSON.
        $jsonDataEncoded = $jsonData;
        //Tell cURL that we want to send a POST request.
        curl_setopt($ch, CURLOPT_POST, 1);

        // Encode the array into JSON.
        // $json_string = json_encode($data);

        //curl_setopt($ch, CURLOPT_POSTFIELDS, $json_string);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
        ]);

        //Execute the request
        $result = curl_exec($ch);
        $response = json_decode($result);

        ob_start();
        $test = ob_get_clean();

        $this->thing->json->setField("message1");
        $this->thing->json->writeVariable(
            ["debug"],
            "char_postMessage" . " " . $to . " " . $test . " " . $url
        );

        if ($response->ok) {
            $this->resource_id = $ch;
            $this->websocket_url = $response->url;
            $this->team = $response->team;
            $this->self = $response->self;

            return false;
            // echo "<br>Connected and found,";
            // echo $this->self->name. "'s ID is " . $this->self->id . ".";
            // Valid response
        } else {
            return true;
            // echo "Unable to connect";
            // $this->error_message = $response->error;
            // echo $this->error_message;
            // Invalid respsone
        }
        return;
    }

    function isEvent()
    {
        if (isset($this->body["command"])) {
            return true;
        } else {
            return false;
        }
    }

    function isCommand()
    {
        if (
            isset($this->body["type"]) and
            $this->body["type"] == "event_callback"
        ) {
            return true;
        } else {
            return false;
        }
    }

    function isEcho()
    {
        // Checks if the message is an echo.
        // Well actually checks if the message is a bot for now.
        // Probably handy not to respond to bots.

        if (isset($this->body["event"]["bot_id"])) {
            return true;
        } else {
            return false;
        }
    }

    function getChannel()
    {
        if (isset($this->body["channel_id"])) {
            $this->channel_id = $this->body["channel_id"];
            return $this->channel_id;
        }

        if (isset($this->body["event"]["channel"])) {
            $this->channel_id = $this->body["event"]["channel"];
            return $this->channel_id;
        }

        return true;
    }

    function getUser()
    {
        if (isset($this->body["user_id"])) {
            $this->user = $this->body["user_id"];
            return $this->user;
        }

        if (isset($this->body["event"]["user"])) {
            $this->user = $this->body["event"]["user"];
            return $this->user;
        }

        return true;
    }

    function getText()
    {
        if (isset($this->body["text"])) {
            $this->text = $this->body["text"];
            return $this->text;
        }

        if (isset($this->body["event"]["text"])) {
            $this->text = $this->body["event"]["text"];
            return $this->text;
        }

        return true;
    }

    function getTeam()
    {
        if (isset($this->body["team_id"])) {
            $this->team = $this->body["team_id"];
            return $this->team;
        }

        //if (!isset( $this->user )) {
        //        $this->user = $this->body['event']['user'];
        //}

        return true;
    }

    function getToken()
    {
        $this->token = $this->body["token"];

        //if (!isset( $this->user )) {
        //        $this->user = $this->body['event']['user'];
        //}

        return $this->token;
    }

    function getEvents()
    {
        $start_time = time();
        echo "<br>Get events<br>";

        /* Message format
{
    "type": "message",
    "ts": "1358878749.000002",
    "user": "U023BECGF",
    "text": "Hello"
}

*/

        $wsUrl = $this->websocket_url;

        echo "websocket_url" . $wsUrl . "<br>";

        $loop = React\EventLoop\Factory::create();
        $connector = new Ratchet\Client\Connector($loop);

        $connector($wsUrl)->then(
            function (Ratchet\Client\WebSocket $conn) {
                echo "<br>open<br>";
                $conn->on("open", function (
                    \Ratchet\RFC6455\Messaging\MessageInterface $msg
                ) use ($conn) {
                    echo "<br>";
                    echo "Received: {$msg}\n";
                    echo "<br>";
                    // $conn->close();
                });

                echo "<br>messag<br>";

                $conn->on("message", function (
                    \Ratchet\RFC6455\Messaging\MessageInterface $msg
                ) use ($conn) {
                    echo "<br>";
                    echo "Received: {$msg}\n";
                    echo "<br>";
                    // $conn->close();
                });

                echo "<br>close<br>";

                $conn->on("close", function ($code = null, $reason = null) {
                    echo "<br>";
                    echo "Connection closed ({$code} - {$reason})\n";
                });

                $t = "";
                $t = '{"text":"Hello"}';

                //if (isset($this->message)) {
                $conn->send($t);
                //}
            },
            function (\Exception $e) use ($loop) {
                echo "<br>";
                echo "Could not connect: {$e->getMessage()}\n";
                $loop->stop();
            }
        );

        // $loop->run();

        echo "<br>getEvents completed<br>";
        return;
    }

    function sendMessage()
    {
        /* Incomming Message format
{
    "type": "message",
    "ts": "1234567890.123456",
    "user": "UXXXXXXX1",
    "text": "Hello"
}
*/
        //$this->Connect();

        $wsUrl = $this->websocket_url;

        $loop = React\EventLoop\Factory::create();
        $connector = new Ratchet\Client\Connector($loop);

        $connector($wsUrl)->then(
            function (Ratchet\Client\WebSocket $conn) {
                $conn->on("message", function (
                    \Ratchet\RFC6455\Messaging\MessageInterface $msg
                ) use ($conn) {
                    echo "Received: {$msg}\n";
                    echo "<br>";
                    // $conn->close();
                });

                $conn->on("close", function ($code = null, $reason = null) {
                    echo "Connection closed ({$code} - {$reason})\n";
                });

                $conn->send($this->message);
            },
            function (\Exception $e) use ($loop) {
                echo "Could not connect: {$e->getMessage()}\n";
                $loop->stop();
            }
        );

        $loop->run();
        return;
    }

    function makeMessage($type = null)
    {
        //$type = null;

        if (!isset($this->message) or $this->message == null) {
            $this->message = "No message";
        }

        // This didn't work here
        $string = $this->message;

        $spaceString = str_replace("<", " <", $string);
        $doubleSpace = strip_tags($spaceString);
        $this->message = str_replace("  ", " ", $doubleSpace);

        ob_start();
        $test = ob_get_clean();

        /*
$array = 
'{
        "token": "XXYYZZ",
        "team_id": "TXXXXXXXX",
        "api_app_id": "AXXXXXXXXX",
        "event": {
                "type": "name_of_event",
                "event_ts": "1234567890.123456",
                "user": "UXXXXXXX1",
                ...
        },
        "type": "event_callback",
        "authed_users": [
                "UXXXXXXX1",
                "UXXXXXXX2"
        ],
        "event_id": "Ev08MFMKH6",
        "event_time": 1234567890
}';
*/

        //Options
        //$token    = $this->bot_user_oauth_access_token;
        $token = $this->bot_user_oauth_access_token;

        $domain = "mordok";
        $channel = $this->getChannel();
        $bot_name = "mordok";
        $icon = ":alien:";
        $message = "Test message";
        $attachments = [
            [
                "fallback" => "Lorem ipsum",
                "pretext" => "Lorem ipsum",
                "color" => "#ff6600",
                "fields" => [
                    [
                        "title" => "Title",
                        "value" => "Lorem ipsum",
                        "short" => true,
                    ],
                    [
                        "title" => "Notes",
                        "value" => "Lorem ipsum",
                        "short" => true,
                    ],
                ],
            ],
        ];

        $data = [
            "token" => $token,
            "channel" => $this->getChannel(),
            "username" => $bot_name,
            "text" => $message,
            "icon_emoji" => $icon /*,
             'attachments' => $attachments */,
        ];

        // Prepare choice buttons
        $button = true;
        $max_buttons = 1;
        $buttons_count = 0;
        if (
            isset($this->choices) and
            isset($this->choices["words"]) and
            $button == true
        ) {
            $actions = null;

            foreach ($this->choices["words"] as $word) {
                if (strtolower($word) == "forget") {
                    continue;
                }
                $buttons_count += 1;
                if ($buttons_count > $max_buttons) {
                    break;
                }
                $action = [
                    "name" => $word,
                    "text" => $word,
                    "type" => "button",
                    "value" => $word,
                ];

                $actions[] = $action;
                $buttons_count += 1;
            }

            //} else {
            //$actions = array($action,$action);
            //}

            $attachments = [
                [
                    "text" => null,
                    "fallback" =>
                        "TEXT [ " .
                        implode(" | ", $this->choices["words"]) .
                        " ]",
                    "callback_id" => "slack_button_" . $this->uuid,
                    "color" => "#719e40",
                    "attachment_type" => "default",
                    "actions" => $actions,
                ],
            ];

            if ($type == null or $type == "event") {
                $json_attachments = json_encode($attachments);
            } elseif ($type == "command") {
                $json_attachments = $attachments;
            }

            //$this->message = "test";

            $data = [
                "token" => $token,
                "channel" => $this->getChannel(), //"#mychannel",
                "text" => $this->message, //"Hello, Foo-Bar channel message.",
                "icon_emoji" => $icon,
                "username" => "Mordok",
                "attachments" => $json_attachments,
            ];
            /*
            echo "<pre>";
            //echo "data buttons";
            print_r(json_encode($data));
            echo "</pre>";
*/
            // $data_string = json_encode($data);
            $data_string = $data;

            return $data_string;
        }
    }
}

?>
