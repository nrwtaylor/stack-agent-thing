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

        $this->aliases = $this->settingsAgent(["discord", "aliases"]);

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

        //$this->thing_report["info"] = "This is an agent to manage Discord.";

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

    //   function getDiscord()
    //   {
    //   }

public function makeDiscord() {
//if (!isset($this->sms_message)) {return true;}
if (!isset($this->sms_message)) {$this->makeSMS();}

$d = $this->sms_message;

$d .= "x";

$this->discord_message = $d;
$this->thing_report['discord'] = $d;


}

    public function testDiscord()
    {
$this->sendDiscord("merp",'edna:#general@edna.discord');

    }

    public function sendDiscord($text, $to, $other = null)
    {
$image_url = null;
if (isset($other['image_url'])) {$image_url = $other['image_url'];}

$png = null;
if (isset($other['png'])) {$png = $other['png'];}


        //$to = "kokopelli:#general@kaiju.discord"; // for testing
        $bot_name = $to;

        $parts = explode(":", $to);

        if (count($parts) == 1) {
            if (isset($this->aliases[$parts[0]])) {
                $alias = $this->aliases[$parts[0]];
                $to = $alias[0];
                $bot_name = $alias[0];
            }
        }

        if (count($parts) == 2) {
            $to = $parts[1];
            $bot_name = ucwords($parts[0]);
        }

        $bot_webhook = $this->settingsAgent([
            "discord",
            "servers",
            $bot_name,
            "webhook",
        ]);

///

///
        $datagram = [
            "to" => $bot_webhook,
            "from" => $bot_name,
            "subject" => $text,
            "image_url"=>$image_url,
            "png"=>$png,
        ];

        $this->webhookDiscord($datagram);
    }

    // https://github.com/agorlov/discordmsg
    public function webhookDiscord($datagram = null)
    {
        if ($datagram == null) {
            return true;
        }

        // Okay what if it is hashed?

        // Dehash against known webhooks.

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

// https://stackoverflow.com/questions/69576744/how-can-i-post-base64-image-data-to-a-discord-webhook-without-using-discord-js

$m = [
                "content" => $msg,
                "username" => $from,
                "avatar_url" => $avatar,
            ];

// This works. But as an embed.
//$m['embeds'] = [["image"=>["url"=>"https://stackr.ca/pixel_sml.png"]]];
//$m['embeds'] = [["image"=>["url"=>$datagram['image_url']]]];
// This doesn't
//$m['files'] =["https://stackr.ca/pixel_sml.png"];

// This doesn't either.
//$m['file'] => curl_file_create($this->PNG_embed, 'image/png');
//$m['file'] => curl_file_create($datagram['png'], 'image/png');

//PNG_embed
/*
        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            json_encode([
                "content" => $msg,
                "username" => $from,
                "avatar_url" => $avatar,
"embeds"=>[["image"=>["url"=>"https://stackr.ca/pixel_sml.png"]]]
            ])
        );
*/
        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            json_encode($m)
        );


        $output = json_decode(curl_exec($curl), true);

        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 204) {
            curl_close($curl);


            $this->thing->Write(
                ["discord", "response_message"],
                $output["message"]
            );


            $this->thing_report["info"] =
                "Could not send message: " . $output["message"] . ". ";
            $this->response .=
                "Could not send message to Discord. [" .
                $output["message"] .
                "]. ";


            return true;
            //throw new Exception("Something went wrong to send a discord message: " . $output['message']);
        }
        $this->thing_report["info"] = "Message sent to Discord. ";
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

        if ($filtered_input == "test") {
            $this->testDiscord();
            $this->response .=
                $this->bot_name . " test " . $this->bot_url . ". ";
        }

        //$message_reply_id = $this->agent_input;
        //        $this->thing->json->setField("variables");
        //       $names = $this->thing->json->writeVariable(
        //           ["discord", "reply_id"],
        //           null
        //       );
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

        $button_text = "Add Edna to your Discord server";
        $link_begin = '<a href="' . $this->bot_url . '">';
        $link_end = "</a>";
        $web .=
            $link_begin .
            '<div class="payment-button" id="checkout-button"><b>' .
            $button_text .
            "</b></div>" .
            $link_end;

        $this->thing_report["web"] = $web;
    }

    public function helpDiscord()
    {
        $web .=
            "See if the operators of Edna are around. Chat with us live, message us, and test out Edna commands with support in Edna's Discord server.";
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
        $this->thing_report["help"] = "bananas";
    }

    function get()
    {
        $this->getDiscord();
    }


    // Not tested.
    function getDiscord()
    {

if ($this->thing->thing === false) {
//$this->body = null;
return;
}

        $bodies = json_decode($this->thing->thing->message0, true);
if ($bodies == null) {
$this->body = null;
return null;
}
if (isset($bodies['msg'])) {
//       $this->body = $bodies["discord"];
       $this->body = $bodies["msg"];

}
//        return $this->body;
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
