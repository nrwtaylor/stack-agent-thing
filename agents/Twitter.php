<?php
namespace Nrwtaylor\StackAgentThing;

//namespace Twitter;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//use Abraham\TwitterOAuth\TwitterOAuth;
ini_set("allow_url_fopen", 1);

// devstack

class Twitter extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->cost = 50;

        $this->test = "Development code";

        $this->thing_report['info'] = 'This is a twitter message agent.';

        $this->api_key = $this->thing->container['api']['twitter']['api_key'];
        $this->api_secret =
            $this->thing->container['api']['twitter']['api_secret'];

        $this->access_token =
            $this->thing->container['api']['twitter']['access_token'];
        $this->access_token_secret =
            $this->thing->container['api']['twitter']['access_token_secret'];

        $this->node_list = ["sms send" => ["sms send"]];

    }

    public function nullAction()
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["character", "action"],
            'null'
        );

        $this->message = "TWITTER | Request not understood. | TEXT SYNTAX";
        $this->sms_message = "TWITTER | Request not understood. | TEXT SYNTAX";
        $this->response = true;
        return $this->message;
    }

    function makeTXT()
    {
        $file = '../temp/twitter_words.txt';
        $current = "-";
        file_put_contents($file, $current, FILE_APPEND | LOCK_EX);

        $current = file_get_contents($file);

        foreach ($this->tweets as $k => $v) {
            //var_dump($v);
            $pieces = explode(" ", strtolower($v['text']));
            foreach ($pieces as $piece) {
                echo $piece;

                $this->findWord('affect', $piece);
            }
        }
        exit();
        // Append a new person to the file
        $current .= "John Smith\n";
        // Write the contents back to the file
        file_put_contents($file, $current, FILE_APPEND | LOCK_EX);
    }

    function findWord($librex, $searchfor)
    {
        if ($librex == "" or $librex == " " or $librex == null) {
            return false;
        }

        switch ($librex) {
            case null:
            // Drop through
            case 'affect':
                $file = '/var/www/html/stackr.ca/temp/twitter_words.txt';
                $contents = file_get_contents($file);
                break;
            default:
                $file = '/var/www/html/stackr.ca/resources/word/words.txt';
        }
        //        header('Content-Type: text/plain');
        //        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        //  $pattern = "/^.*$pattern.*\$/m";
        $pattern = "|\b($searchfor)\b|";

        echo "foo";
        var_dump($contents);
        echo "bar";
        exit();
        // search, and store all matching occurences in $matches

        if (preg_match_all($pattern, $contents, $matches)) {
            //echo "Found matches:\n";
            //$m = implode("\n", $matches[0]);

            $m = $matches[0][0];
            return $m;
        } else {
            //echo "no found";
            return false;
            //echo "No matches found";
        }
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

//        $to = $this->from;
/*
        if ($this->agent_input != null) {
            $test_message = $this->agent_input;
        } else {
            $test_message = $this->subject;
        }
*/
//        $this->makeSMS();

        $this->thing_report['info'] =
            '<pre> Agent "Twitter" sent a twitter message to ' .
            $this->from .
            '.</pre>';

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];

            echo $this->sms_message;
        }

//        $this->makeHelp();

        $this->thing_report['choices'] = false;
        $this->thing_report['help'] = 'In development.';
        $this->thing_report['log'] = $this->thing->log;

    }

    function makeHelp()
    {
    }

    public function readSubject()
    {
        //        $this->getAffect();
        //        $this->getTweets();

        /*      No reading to be done.
 		if ( !is_numeric($this->from) ) {
			// This isn't a textable number.
			return true;
		}
*/

        $emoji_thing = new Emoji($this->thing, "emoji");
        $thing_report = $emoji_thing->thing_report;

        if (isset($emoji_thing->emojis)) {
            $input = ltrim(strtolower($emoji_thing->translated_input));
        }

        $this->response = null;

        $keywords = ['twitter'];

        //$input = strtolower($this->subject);

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            $input = $this->subject;

            if (strtolower($input) == 'twitter') {
                $this->randomTweet();
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'twitter':
                            $prefix = 'twitter';
                            $words = preg_replace(
                                '/^' . preg_quote($prefix, '/') . '/',
                                '',
                                $input
                            );
                            $words = ltrim($words);

                            $this->channel = $words;
                            //exit();

                            //                            $this->Get($words);
                            $this->randomTweet();

                            return;

                        default:

                        //echo 'default';
                    }
                }
            }
        }

        //        $this->getAffect();

        $this->nullAction();

        return "Message not understood";

        //return false;
    }

    function makeMessage($message = null)
    {
        if ($this->input != null) {
            $message = $this->input;
        } else {
            $message = $this->subject;
        }

        $sender = $this->from;
        //                "text":"'.$message.'",

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
            "image_url":"https://stackr.ca/thing/d0f11a91-cce9-4b04-b046-07cf5ead3d31/iching.png",
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
                        "url":"https://stackr.ca/thing/d0f11a91-cce9-4b04-b046-07cf5ead3d31/iching.png", 
                        "is_reusable":true
                    }
                }
            }


        }';
*/
        $this->json_message = $jsonData;
    }

    function makeSMS()
    {
        //echo "meep";
        //        $this->sms_message = "test";
        if (!isset($this->sms_message)) {
            if (!isset($this->random_tweet)) {
                $this->random_tweet = "test";
            }
            $this->sms_message = $this->random_tweet['text'];
        }
        $this->thing_report['sms'] = "TWITTER | " . $this->sms_message;
        //echo $this->sms_message;
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

    function Tweet($to, $text)
    {
        if (!isset($this->tweet)) {
            // Build tweet from $to and $text.
        }

        //$to = "id";
        $this->tweet = "#test";

        $this->connection = new \Abraham\TwitterOAuth\TwitterOAuth(
            $this->api_key,
            $this->api_secret,
            $this->access_token,
            $this->access_token_secret
        );
        $this->content = $this->connection->get('account/verify_credentials');

        $this->statuses = $this->connection->get("search/tweets", [
            "q" => "twitterapi",
        ]);

        echo "<br>";
        //var_dump($this->content);
        var_dump($this->statuses);
        var_dump(count($this->statuses));
        echo "<br>";

        var_dump($this->twitterOK());
        exit();

        //                        $connection->post('statuses/update', array('status' => $this->tweet));
        if ($connection->getLastHttpCode() === 200) {
            echo '<p><strong>Your latest tweet:</strong> ' .
                $tweet .
                '</p>' .
                PHP_EOL;
        } else {
            echo '<p><strong>Error:</strong> A problem ocurred. You filled your Twitter credentials correctly? Or walk abusing the Twitter API?</p>' .
                PHP_EOL;
        }
    }

    function randomTweet()
    {
        if (!isset($this->tweets)) {
            $this->getTweets();
        }
        //echo count($this->tweets);
        //exit();

        $i = rand(1, count($this->tweets));
        $this->random_tweet = $this->tweets[$i];

        var_dump($this->random_tweet);
    }

    function getPleasantness()
    {
        // The free dictionary
        // 1. Giving or affording pleasure or enjoyment; agreeable:
        // a pleasant scene; pleasant sensations.
        // 2. Pleasing in manner, behavior, or appearance.

        // Going to focus on the first.

        if (!isset($this->tweets)) {
            $this->getTweets();
        }

        foreach ($this->tweets as $tweet) {
            echo $tweet['text'];
        }
    }

    function getActivation()
    {
        if (!isset($this->tweets)) {
            $this->getTweets();
        }

        foreach ($this->tweets as $tweet) {
            echo $tweet['created_at'];
            echo "<br>";
        }
    }

    function getAffect()
    {
        $this->getPleasantness(); // Negative to Positive computed ranges
        $this->getActivation(); // Negative to Positive computed ranges
    }

    function getTweets($channel = null)
    {
        if ($channel == null) {
            if (isset($this->channel)) {
                $channel = $this->channel;
            } else {
                $channel = "#vancouver #yvr";
            }
        }

        //$to = "id";
        $this->tweet = "#test";

        //require_once '/var/www/html/stackr.ca/vendor/abraham/twitteroauth/src/TwitterOAuth.php';

        $this->connection = new \Abraham\TwitterOAuth\TwitterOAuth(
            $this->api_key,
            $this->api_secret,
            $this->access_token,
            $this->access_token_secret
        );
        //                        $this->content = $this->connection->get('account/verify_credentials');
        //var_dump($channel);
        //exit();
        $this->statuses = $this->connection->get("search/tweets", [
            "q" => $channel,
        ]);

        echo "<br>";
        $this->tweets = [];
        //var_dump($this->content);
        foreach ($this->statuses->statuses as $k => $v) {
            //var_dump($v->text);

            $this->tweets[] = [
                "text" => $v->text,
                "created_at" => $v->created_at,
            ];
            //echo "<br>---<br>";
        }
        //echo "<br>";

        if ($this->twitterOK() != 'green') {
            return true;
        }

        return $this->tweets;
    }

    function twitterOK()
    {
        if ($this->connection->getLastHttpCode() === 200) {
            echo '<p><strong>Your latest twitter request was OK.</p>' . PHP_EOL;
            return 'green';
        } else {
            echo '<p><strong>Error:</strong> A problem ocurred. You filled your Twitter credentials correctly? Or walk abusing the Twitter API?</p>' .
                PHP_EOL;
            return 'red';
        }
    }

    function sendMessage($to, $text)
    {
        //                        $this->connection->post('statuses/update', array('status' => $this->tweet));

        //        $this->Tweet($to, $text);
        return;

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
        //                   "url":"https://stackr.ca/thing/7f0ef3d0-54e4-400c-b3cc-a537a2e358b6/uuid.png"
        //                   }
        //               }
        //               }
        //           }';

        //$attachment = '"attachment":{}';

        // above is not working
        //$attachment = "";

        //API Url
        $url =
            'https://graph.facebook.com/v2.6/me/messages?access_token=' .
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
            'Content-Type: application/json',
        ]);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        //Execute the request
        if (!empty($message_to_reply)) {
            $result = curl_exec($ch);
        }

        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["facebook", "result"],
            $result
        );
        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable(
            ["facebook", "refreshed_at"],
            $time_string
        );

        return;
    }
}
