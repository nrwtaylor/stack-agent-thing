<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Microsoft
{
    public $var = "hello";

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;
        $this->cost = 50;

        $this->test = "Development code";

        $this->thing = $thing;

        $this->thing_report = ["thing" => $this->thing->thing];
        $this->thing_report["info"] = "This is a microsoft agent.";

        //        $this->app_token = $this->thing->container['api']['microsoft']['app token'];
        $this->app_id =
            $this->thing->container["api"]["microsoft"]["edna"]["appid"];
        $this->app_secret =
            $this->thing->container["api"]["microsoft"]["edna"]["appsecret"];
        //        $this->page_access_token = $this->thing->container['api']['facebook']['page_access_token'];

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        $this->agent_prefix = 'Agent "Microsoft" ';

        $this->node_list = ["sms send" => ["sms send"]];

        $this->thing->log(
            'Agent "Microsoft" running on Thing ' . $this->thing->nuuid . "."
        );
        $this->thing->log(
            'Agent "Microsoft" received this Thing "' . $this->subject . '".'
        );

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container["stack"]["web_prefix"];
        $this->mail_postfix = $thing->container["stack"]["mail_postfix"];
        $this->word = $thing->container["stack"]["word"];
        $this->email = $thing->container["stack"]["email"];

        $this->eventGet();

        $channel = new Channel($this->thing, "microsoft");

        if ($this->readSubject() == true) {
            $this->thing_report = [
                "thing" => $this->thing->thing,
                "choices" => false,
                "info" => "A Microsoft ID wasn't provided.",
                "help" => "from needs to be a number.",
            ];

            $this->thing->log(
                'Agent "Microsoft" completed without sending a message.'
            );
            return;
        }
        $this->respond();

        $this->thing->log('Agent "Microsoft" completed.');

        return;
    }

    function eventSet($input = null)
    {
        if ($input == null) {
            $input = $this->body;
        }

        $this->thing->log('<pre> Agent "Slack" called eventSet()');

        $this->thing->db->setFrom($this->from);

        $this->thing->json->setField("message0");
        $this->thing->json->writeVariable(["microsoft"], $input);

        //$this->thing->flagGreen();

        return;
    }

    function getResponseurl()
    {
        //$activity = ($this->body['channelData']['clientActivityId']);
        if (isset($this->body["channelData"]["clientActivityId"])) {
            $this->activity_id = $this->body["channelData"]["clientActivityId"];
            return $this->activity_id;
        }

        //if ( isset( $this->body['event']['channel'] )) {
        //    $this->channel_id = $this->body['event']['channel'];
        //    return $this->channel_id;
        //}

        return true;
    }

    function getActivity()
    {
        //$activity = ($this->body['channelData']['clientActivityId']);

        if (isset($this->body["channelData"]["clientActivityId"])) {
            $this->activity_id = $this->body["channelData"]["clientActivityId"];
            return $this->activity_id;
        }

        //if ( isset( $this->body['event']['channel'] )) {
        //    $this->channel_id = $this->body['event']['channel'];
        //    return $this->channel_id;
        //}

        return true;
    }

    function getChannel()
    {
        if (isset($this->body["conversation"]["id"])) {
            $this->channel_id = $this->body["conversation"]["id"];
            return $this->channel_id;
        }

        //if ( isset( $this->body['event']['channel'] )) {
        //    $this->channel_id = $this->body['event']['channel'];
        //    return $this->channel_id;
        //}

        return true;
    }

    function getUser()
    {
        if (isset($this->body["from"]["id"])) {
            $this->user = $this->body["from"]["id"];
            return $this->user;
        }

        //if ( isset($this->body['event']['user']) ) {
        //    $this->user = $this->body['event']['user'];
        //    return $this->user;
        //}

        return true;
    }

    function getText()
    {
        if (isset($this->body["text"])) {
            $this->text = $this->body["text"];
            return $this->text;
        }

        //if ( isset( $this->body['event']['text'] )) {
        //   $this->text = $this->body['event']['text'];
        //    return $this->text;
        //}

        return true;
    }

    // -----------------------

    private function respond()
    {
        // Thing actions
        $this->thing->flagGreen();

        // Generate email response.

        $to = $this->from;
        //		$from = $this->to;

        //		if ($this->input != null) {
        //			$test_message = $this->input;
        //		} else {
        //			$test_message = $this->subject;
        //		}

        //        if ($this->input != null) {
        //            $test_message = $this->input;
        //        } else {
        //            $test_message = $this->subject;
        //        }
        $test_message = null;

        //		if ($this->thing->account['stack']->balance['amount'] >= $this->cost ) {
        $this->sendMessage($to, $test_message);
        //			$this->thing->account['stack']->Debit($this->cost);
        //			$this->thing->log("FB message sent");

        $this->thing_report["info"] =
            '<pre> Agent "Microsoft" sent a message to ' .
            $this->from .
            ".</pre>";

        $this->thing_report["choices"] = false;
        //$this->thing_report['info'] = 'This is a facebook message agent.';
        $this->thing_report["help"] = "In development.";
        $this->thing_report["log"] = $this->thing->log;
    }

    public function readSubject()
    {
        if (is_array($this->agent_input)) {
            $this->response = "Processed datagram.";
            $this->eventSet($this->agent_input);
            return;
        }

        if (is_string($this->agent_input)) {
            $this->response = "Sent message.";
            $this->message = $this->agent_input;
            //$this->eventSet($this->agent_input);
            return;
        }

        //$message_reply_id = $this->agent_input;
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["microsoft", "reply_id"],
            null
        );

        //"channelData":{"clientActivityId":"1536536110650.9566644201124537.16"}}

        // Nothing to read.
        return false;
    }

    function eventGet()
    {
        $this->thing->log('<pre> Agent "Slack" called eventGet()</pre>');

        $bodies = json_decode($this->thing->thing->message0, true);
        $this->body = $bodies["microsoft"];

        $this->variablesGet();
        return $this->body;
    }

    function variablesGet()
    {
        $this->channel_id = $this->getChannel();
        $this->user = $this->getUser();
        $this->text = $this->getText();
        $this->activity_id = $this->getActivity();
        $this->service_url = $this->body["serviceUrl"];

        return;
    }

    function makeMessage($message = null)
    {
        if ($this->input != null) {
            $message = $this->input;
        } else {
            $message = $this->subject;
        }

        $sender = $this->from;
        // "text":"'.$message.'",

        $jsonData =
            '{
  "recipient":{
    "id":"' .
            $sender .
            '"
  },
  "message":{
    "attachment":{
      "type":"template",
      "payload":{
        "template_type":"generic",
        "elements":[
           {
            "title":"ICHING",
            "image_url":"https://<web_prefix>/thing/d0f11a91-cce9-4b04-b046-07cf5ead3d31/iching.png",
            "subtitle":"' .
            $message .
            '"
          }
        ]
      }
    }
  }
}';
        /*
 '{
            "recipient":{
               "id":"'. $sender.'"
            },
            "message":{

                "attachment":{
                    "type":"image", 
                    "payload":{
                        "url":"https://<web_prefix>/thing/d0f11a91-cce9-4b04-b046-07cf5ead3d31/iching.png", 
                        "is_reusable":true
                    }
                }
            }


        }';
*/
        $this->json_message = $jsonData;
    }

    function makeBasicMessage($message = null)
    {
        //        if ($this->input != null) {
        //            $message = $this->input;
        //        } else {
        //            $message = $this->subject;
        //        }

        $sender = $this->from;
        /*
        $jsonData = '{
            "recipient":{
               "id":"'. $sender.'"
            },
            "message":{
                "text":"'.$message.'"
            }
        }';
*/

        //                    $this->thing->json->setField("message0");
        //                    $names = $this->thing->json-readVariable( array("microsoft") );

        if (!isset($this->message)) {
            $this->json_message = "No message provided.";
            return;
        }

        $jsonData =
            '{"type": "message",
    "from": {
        "id": "' .
            $this->body["recipient"]["id"] .
            '",
        "name": "' .
            $this->body["recipient"]["name"] .
            '"
    },
    "conversation": {
        "id": "' .
            $this->body["conversation"]["id"] .
            '"
    },
   "recipient": {
        "id": "' .
            $this->body["from"]["id"] .
            '",
        "name": "' .
            $this->body["from"]["name"] .
            '"
    },
    "text": "I have several times available on Saturday!"
}';

        $jsonData =
            '{"type": "message",
    "from": {
        "id": "' .
            $this->body["recipient"]["id"] .
            '",
        "name": "' .
            $this->body["recipient"]["name"] .
            '"
    },
    "conversation": {
        "id": "' .
            $this->body["conversation"]["id"] .
            '"
    },
   "recipient": {
        "id": "' .
            $this->body["from"]["id"] .
            '",
        "name": "' .
            $this->body["from"]["name"] .
            '"
    },
    "text": "' .
            $this->message .
            '"
}';

        $this->json_message = $jsonData;
    }

    function authorizeMessage()
    {
        $url =
            "https://login.microsoftonline.com/botframework.com/oauth2/v2.0/token";

        //Initiate cURL.
        $ch = curl_init($url);

        //$jsonDataEncoded = "grant_type=client_credentials&client_id=" . $this->app_id . "&client_secret=" . $this->app_secret . "&scope=https%3A%2F%2Fgraph.microsoft.com%2F.default";
        $jsonDataEncoded =
            "grant_type=client_credentials&client_id=" .
            $this->app_id .
            "&client_secret=" .
            $this->app_secret .
            "&scope=https%3A%2F%2Fapi.botframework.com%2F.default";

        //Tell cURL that we want to send a POST request.
        curl_setopt($ch, CURLOPT_POST, 1);

        //Attach our encoded JSON string to the POST fields.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);

        //Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //Execute the request
        //if( !empty($message_to_reply) ){
        $result = curl_exec($ch);
        //}

        $result_json = json_decode($result, true);
        $this->access_token = $result_json["access_token"];

        return;
    }

    function sendMessage($to, $text)
    {
        // Get access token each time
        $this->authorizeMessage();

        // Respond with https://docs.microsoft.com/en-us/azure/bot-service/rest-api/bot-framework-rest-connector-api-reference?view=azure-bot-service-3.0
        /*
{
    "type": "message",
    "from": {
        "id": "12345678",
        "name": "bot's name"
    },
    "conversation": {
        "id": "abcd1234",
        "name": "conversation's name"
    },
   "recipient": {
        "id": "1234abcd",
        "name": "user's name"
    },
    "text": "I have several times available on Saturday!",
    "replyToId": "bf3cc9a2f5de..."
}

POST https://smba.trafficmanager.net/apis/v3/conversations/abcd1234/activities/bf3cc9a2f5de... 
Authorization: Bearer eyJhbGciOiJIUzI1Ni...
Content-Type: application/json

*/
        $sender = $to;
        $message_to_reply = $text;

        //API Url
        ///v3/conversations/{conversationId}/activities/{activityId}

        $conversation_id = $this->channel_id;
        $activity_id = $this->activity_id;
        // https://docs.microsoft.com/en-us/microsoftteams/platform/concepts/bots/bot-conversations/bots-conversations#sending-replies-to-messages
        $endpoint =
            "conversations/" . $conversation_id . "/activities/" . $activity_id;

        $url = $this->service_url . "v3/" . $endpoint;

        //Initiate cURL.
        $ch = curl_init($url);

        //The JSON data.

        $this->makeBasicMessage();
        $jsonData = $this->json_message;
        //Encode the array into JSON.
        //        $jsonDataEncoded = $jsonData;

        //        $j = json_encode($jsonDataEncoded);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //Set the content type to application/json
        //        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        //        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->access_token));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->access_token,
        ]);

        //Execute the request
        //      if( !empty($message_to_reply) ){
        $result = curl_exec($ch);
        //      }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["microsoft", "result"],
            $result
        );
        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable(
            ["microsoft", "refreshed_at"],
            $time_string
        );

        return;
    }
}

?>
