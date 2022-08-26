<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Microsoft extends Agent
{
    public $var = "hello";
    function init()
    {
        $this->cost = 50;

        $this->thing_report["info"] = "This is a microsoft agent.";

        $this->app_id =
            $this->thing->container["api"]["microsoft"]["edna"]["appid"];
        $this->app_secret =
            $this->thing->container["api"]["microsoft"]["edna"]["appsecret"];

        new Channel($this->thing, 'microsoft');

        $this->node_list = ["sms send" => ["sms send"]];
    }

    public function get()
    {
        $this->eventGet();
    }

    function eventSet($input = null)
    {
        if ($input == null) {
            $input = $this->body;
        }

        $this->thing->db->setFrom($this->from);

        $this->thing->Write(["microsoft"], $input, 'message0');


    }

    function getResponseurl()
    {
        if (isset($this->body["channelData"]["clientActivityId"])) {
            $this->activity_id = $this->body["channelData"]["clientActivityId"];
            return $this->activity_id;
        }
        return true;
    }

    function getActivity()
    {
        if (isset($this->body["channelData"]["clientActivityId"])) {
            $this->activity_id = $this->body["channelData"]["clientActivityId"];
            return $this->activity_id;
        }

        return true;
    }

    function getChannel()
    {
        if (isset($this->body["conversation"]["id"])) {
            $this->channel_id = $this->body["conversation"]["id"];
            return $this->channel_id;
        }

        return true;
    }

    function getUser()
    {
        if (isset($this->body["from"]["id"])) {
            $this->user = $this->body["from"]["id"];
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

        return true;
    }

    public function readSubject()
    {
        if (is_array($this->agent_input)) {
            $this->response .= "Processed datagram.";
            $this->eventSet($this->agent_input);
            $this->body = $this->agent_input;
            return;
        }

        if (is_string($this->agent_input)) {
            $this->response .= "Sent message.";
            $this->message = $this->agent_input;
            //$this->eventSet($this->agent_input);
            return;
        }
        //$message_reply_id = $this->agent_input;
        $names = $this->thing->Write(["microsoft", "reply_id"], null);

        //"channelData":{"clientActivityId":"1536536110650.9566644201124537.16"}}

        // Nothing to read.
        return false;
    }

    function eventGet()
    {
        if (!isset($this->body)) {
            $this->thing->log('<pre> Agent "Slack" called eventGet()</pre>');

            $bodies = json_decode($this->thing->thing->message0, true);
            $this->body = $bodies["microsoft"];
        }
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

        //if ($message == null) {return;}
        /*
        if (!isset($this->message)) {
$this->message = $message;
          //  $this->json_message = "No message provided.";
          //  return;
        }
*/
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
            $message .
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
    }

    public function sendMicrosoft($to, $text)
    {
        $this->sendMessage($to, $text);
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
        //$text = "roo";
        $this->makeBasicMessage($text);
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
        $e = null;
        if ($result === false) {
            $e = curl_error($ch);
        }

        // Check HTTP return code, too; might be something else than 200
        $httpReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $this->thing->Write(["microsoft", "http_code"], $httpCode);

        $this->thing->Write(["microsoft", "url"], $url);

        $this->thing->Write(["microsoft", "error"], $e);

        $names = $this->thing->Write(["microsoft", "result"], $result);
        $time_string = $this->thing->time();
        $this->thing->Write(["microsoft", "refreshed_at"], $time_string);

    }
}
