<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Facebook
{
    public $var = "hello";

    function __construct(Thing $thing, $input = null)
    {
        $this->input = $input;
        $this->cost = 50;

        $this->test = "Development code";

        $this->thing = $thing;

        $this->thing_report = ["thing" => $this->thing->thing];
        $this->thing_report["info"] = "This is a facebook message agent.";

        $this->app_token =
            $this->thing->container["api"]["facebook"]["app token"];
        $this->app_id = $this->thing->container["api"]["facebook"]["app ID"];
        $this->app_secret =
            $this->thing->container["api"]["facebook"]["app secret"];
        $this->page_access_token =
            $this->thing->container["api"]["facebook"]["page_access_token"];

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        $this->agent_prefix = 'Agent "Facebook" ';

        $this->node_list = ["sms send" => ["sms send"]];

        $this->thing->log(
            'Agent "Facebook" running on Thing ' . $this->thing->nuuid . "."
        );
        $this->thing->log(
            'Agent "Facebook" received this Thing "' . $this->subject . '".'
        );

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container["stack"]["web_prefix"];
        $this->mail_postfix = $thing->container["stack"]["mail_postfix"];
        $this->word = $thing->container["stack"]["word"];
        $this->email = $thing->container["stack"]["email"];

        if ($this->readSubject() == true) {
            $this->thing_report = [
                "thing" => $this->thing->thing,
                "choices" => false,
                "info" => "A Facebook ID wasn't provided.",
                "help" => "from needs to be a number.",
            ];

            $this->thing->log(
                'Agent "Facebook" completed without sending a message.'
            );
            return;
        }
        $this->respond();

        $this->thing->log('Agent "Facebook" completed.');

        return;
    }

    // -----------------------

    private function respond()
    {
        // Thing actions
        $this->thing->flagGreen();

        // Generate email response.

        $to = $this->from;
        //		$from = $this->to;

        if ($this->input != null) {
            $test_message = $this->input;
        } else {
            $test_message = $this->subject;
        }

        $this->sendMessage($to, $test_message);

        $this->thing_report["info"] =
            '<pre> Agent "Facebook Messenger" sent a fb message to ' .
            $this->from .
            ".</pre>";

        $this->thing_report["choices"] = false;
        $this->thing_report["help"] = "In development.";
        $this->thing_report["log"] = $this->thing->log;
    }

    public function readSubject()
    {
        // Nothing to read.
        return false;
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
        if ($this->input != null) {
            $message = $this->input;
        } else {
            $message = $this->subject;
        }

        $sender = $this->from;

        $jsonData =
            '{
            "recipient":{
               "id":"' .
            $sender .
            '"
            },
            "message":{
                "text":"' .
            $message .
            '"
            }
        }';

        $this->json_message = $jsonData;
    }

    function sendMessage($to, $text)
    {
        // http://blog.adnansiddiqi.me/develop-your-first-facebook-messenger-bot-in-php/

        //$fb_person = (string) $to; // Just make sure its a string.  Seems to be a 2016 FB to avoid.

        //  $reply = 'Message received: ' . $text;
        // $responseJSON = '{
        //  "recipient":{
        //    "id":"'. $to .'"
        //  },
        //  "message": {
        //          "text":"'. $reply .'"
        //      }
        //  }';

        $sender = $to;
        $message_to_reply = $text;
        //       $attachment = '{
        //           "message": {
        //               "attachments": {
        //                "type":"image",
        //                "payload":{
        //                   "url":"https://<web_prefix>/thing/7f0ef3d0-54e4-400c-b3cc-a537a2e358b6/uuid.png"
        //                   }
        //               }
        //               }
        //           }';

        //$attachment = '"attachment":{}';

        // above is not working
        //$attachment = "";

        //API Url
        $url =
            "https://graph.facebook.com/v2.6/me/messages?access_token=" .
            $this->page_access_token;

        //Initiate cURL.
        $ch = curl_init($url);

        //The JSON data.
        /*        $jsonData = '{
            "recipient":{
               "id":"'. $sender.'"
            },
            "message":{
                "text":"'.$message_to_reply.'"
            }
        }';
*/
        $jsonData =
            '{
            "recipient":{
               "id":"' .
            $sender .
            '"
            },
            "message":{
                "text":"' .
            $message_to_reply .
            '"
            }
        }';

        $this->makeBasicMessage();
        $jsonData = $this->json_message;
        //Encode the array into JSON.
        $jsonDataEncoded = $jsonData;

        //Tell cURL that we want to send a POST request.
        curl_setopt($ch, CURLOPT_POST, 1);

        //Attach our encoded JSON string to the POST fields.
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);

        //Set the content type to application/json
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        //Execute the request
        if (!empty($message_to_reply)) {
            $result = curl_exec($ch);
        }

        $names = $this->thing->json->Write(
            ["facebook", "result"],
            $result
        );
        $time_string = $this->thing->time();
        $this->thing->json->Write(
            ["facebook", "refreshed_at"],
            $time_string
        );

        return;
    }
}
