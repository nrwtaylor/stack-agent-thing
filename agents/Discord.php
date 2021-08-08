<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// devstack
// develop and test

// status needs a lot of work

class Discord extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->bot_name = "Edna";
        $this->server_name = "Edna";

        $this->bot_name = $this->settingsAgent([
            "discord",
            "bots",
            $this->bot_name,
            "name",
        ]);
        $this->bot_text = $this->settingsAgent([
            "discord",
            "bots",
            $this->bot_name,
            "text",
        ]);
        $this->bot_url = $this->settingsAgent([
            "discord",
            "bots",
            $this->bot_name,
            "url",
        ]);

        $this->server_name = $this->settingsAgent([
            "discord",
            "servers",
            $this->server_name,
            "name",
        ]);
        $this->server_text = $this->settingsAgent([
            "discord",
            "servers",
            $this->server_name,
            "text",
        ]);
        $this->server_url = $this->settingsAgent([
            "discord",
            "servers",
            $this->server_name,
            "url",
        ]);

        $this->test = "Development code";

        $this->thing_report["info"] = "This is an agent to manage Discord.";

        $this->credential_set = $this->settingsAgent([
            "discord",
            "credential_set",
        ]);

        $this->client_id = $this->settingsAgent([
            "discord",
            "credential_set",
            $this->credential_set,
            "client_id",
        ]);
        $this->client_secret = $this->settingsAgent([
            "discord",
            "credential_set",
            $this->credential_set,
            "client_secret",
        ]);
        $this->token = $this->settingsAgent([
            "discord",
            "credential_set",
            $this->credential_set,
            "token",
        ]);
        $this->channel_id = $this->settingsAgent([
            "discord",
            "credential_set",
            $this->credential_set,
            "channel_id",
        ]);
        $this->permissions_integer = $this->settingsAgent([
            "discord",
            "credential_set",
            $this->credential_set,
            "permissions_integer",
        ]);

        $this->node_list = ["sms send" => ["sms send"]];

        $channel = new Channel($this->thing, "discord");
    }

    function getDiscord()
    {
    }

    function eventSet($input = null)
    {
        if ($input == null) {
            $input = $this->body;
        }

        $this->thing->db->setFrom($this->from);

        $this->thing->json->setField("message0");
        $this->thing->json->writeVariable(["discord"], $input);
    }

    function getResponseurl()
    {
        if (isset($this->body["channelData"]["clientActivityId"])) {
            $this->activity_id = $this->body["channelData"]["clientActivityId"];
            return $this->activity_id;
        }

        return true;
    }

    function activityDiscord()
    {
        if (isset($this->body["channelData"]["clientActivityId"])) {
            $this->activity_id = $this->body["channelData"]["clientActivityId"];
            return $this->activity_id;
        }

        return true;
    }

    // dev test
    function channelidDiscord()
    {
        if (isset($this->body["conversation"]["id"])) {
            $this->channel_id = $this->body["conversation"]["id"];
            return $this->channel_id;
        }

        return true;
    }

    // dev test
    function userDiscord()
    {
        if (isset($this->body["from"]["id"])) {
            $this->user = $this->body["from"]["id"];
            return $this->user;
        }

        return true;
    }

    // dev test
    function textDiscord()
    {
        if (isset($this->body["text"])) {
            $this->text = $this->body["text"];
            return $this->text;
        }

        return true;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function readSubject()
    {
        // A Discord thing will have an array in agent_input.
        // dev Add a test of the array structure to verify it is a Discord structure.
        if (is_array($this->agent_input)) {
            $this->response .= "Processed datagram. ";
            $this->eventSet($this->agent_input);
            return;
        }

        $input = $this->input;
        $filtered_input = $this->assert($input);

        if ($filtered_input == "bot") {
            $this->response .=
                $this->bot_name . " link " . $this->bot_url . ". ";
        }

        if ($filtered_input == "server") {
            $this->response .=
                $this->server_name . " link " . $this->server_url . ". ";
        }

        //$message_reply_id = $this->agent_input;
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["discord", "reply_id"],
            null
        );
    }

    public function makeSMS()
    {
        $response = "No response seen. ";
        if ($this->response != "") {
            $response = $this->response;
        }

        $sms = "DISCORD | " . $response;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeWeb()
    {
        $web = "";

        if (isset($this->thing_report["pdf"])) {
            $link = $this->web_prefix . "thing/" . $this->uuid . "/discord.pdf";
            $this->node_list = ["zoom" => ["zoom"]];
            $web = "";
        }

        if (isset($this->html_image)) {
            $web .= '<a href="' . $link . '">';
            $web .= $this->html_image;
            $web .= "</a>";
        }

        $web .= $this->restoreUrl(
            "Use this URL to join our Discord server " .
                $this->server_name .
                " " .
                $this->server_url .
                "."
        );

        $web .= "<br>";

        $web .= $this->restoreUrl(
            "Use this URL to add our Discord bot " .
                $this->bot_name .
                " " .
                $this->bot_url .
                "."
        );

        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    function eventGet()
    {
        $bodies = json_decode($this->thing->thing->message0, true);
        $this->body = $bodies["discord"];

        $this->variablesGet();
        return $this->body;
    }

    function variablesGet()
    {
        $this->channel_id = $this->channelidDiscord();
        $this->user = $this->userDiscord();
        $this->text = $this->textDiscord();
        $this->activity_id = $this->activityDiscord();
        $this->service_url = $this->body["serviceUrl"];
    }

    // dev deprecated?
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

    function authorize()
    {
        $this->permissions_integer = 0;
        $url =
            "https://discordapp.com/api/oauth2/authorize?client_id=" .
            $this->client_id .
            "&scope=bot&permissions=" .
            $this->permissions_integer;

        $url =
            "https://discordapp.com/oauth2/authorize?client_id=" .
            $this->client_id .
            "&scope=bot&permissions=0";

        echo "\n";
        echo "Posting" . $url;
        echo "\n";
        //Initiate cURL.
        $ch = curl_init($url);

        $result = curl_exec($ch);
    }
}
