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

        $this->thing_report["info"] = $this->settingsAgent([
            "discord",
            "bots",
            $this->bot_name,
            "info",
        ]);

        $this->thing_report["help"] = $this->settingsAgent([
            "discord",
            "bots",
            $this->bot_name,
            "help",
        ]);

/*
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
*/
        $this->test = "Development code";

        //$this->thing_report["info"] = "This is an agent to manage Discord.";

        $channel = new Channel($this->thing, "discord");
    }

    function getDiscord()
    {
    }

    public function sendDiscord($text, $to)
    {
        $bot_webhook = $this->settingsAgent([
            "discord",
            "servers",
            $to,
            "webhook",
        ]);

        $datagram = ["to" => $bot_webhook, "from" => $to, "subject" => $text];

        $this->webhookDiscord($datagram);
    }
    // https://github.com/agorlov/discordmsg
    public function webhookDiscord($datagram = null)
    {
        if ($datagram == null) {
            return true;
        }

        $url = $datagram["to"];
        $from = $datagram["from"];
        $msg = $datagram["subject"];
        $avatar = null;

        $curl = curl_init();
        //timeouts - 5 seconds
        curl_setopt($curl, CURLOPT_TIMEOUT, 5); // 5 seconds
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5); // 5 seconds

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            json_encode([
                "content" => $msg,
                "username" => $from,
                "avatar_url" => $avatar,
            ])
        );

        $output = json_decode(curl_exec($curl), true);

        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 204) {
            curl_close($curl);
            $this->response .=
                "Could not send message: " . $output["message"] . ". ";
            return true;
            //throw new Exception("Something went wrong to send a discord message: " . $output['message']);
        }

        curl_close($curl);
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
        $names = $this->thing->Write(["discord", "reply_id"], null);
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
/*
       $web .= "<br>";
        $web .= $this->restoreUrl(
            "Use this URL to add our Discord bot " .
                $this->bot_name .
                " " .
                $this->bot_url .
                "."
        );
        $web .= "<br>";
*/
$web .= $this->bot_text;

        $button_text = 'Add Edna to your Discord server';
$link_begin = '<a href="'.                 $this->bot_url .'">';
$link_end = '</a>';
        $web .=
            $link_begin .
            '<div class="payment-button" id="checkout-button"><b>' .
            $button_text .
            '</b></div>'. $link_end;


        $this->thing_report["web"] = $web;
    }

    public function helpDiscord() {

        $web .= "See if the operators of Edna are around. Chat with us live, message us, and test out Edna commands with support in Edna's Discord server.";
        $web .= "<br>";
       $web .= "<br>";
        $web .= $this->restoreUrl(
            "Use this URL to join our Discord server " .
                $this->server_name .
                " " .
                $this->server_url .
                "."
        );

        $web .= "<br>";
$this->thing_report['help'] = "bananas";

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
