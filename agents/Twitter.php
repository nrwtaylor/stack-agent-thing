<?php
namespace Nrwtaylor\StackAgentThing;

//namespace Twitter;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

//use Abraham\TwitterOAuth\TwitterOAuth;
ini_set("allow_url_fopen", 1);

// devstack

class Twitter extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->cost = 50;

        $this->keywords = ["twitter", "channel", "tweet", "hashtag"];

        $this->test = "Development code";

        $this->thing_report["info"] = "This is a twitter message agent.";

        $this->initTwitter();
        $this->tweeted_words_location = $this->settingsAgent([
            "twitter",
            "tweeted_words_location",
        ]);

        $this->node_list = ["twitter" => ["twitter"]];
    }

    public function initTwitter()
    {
        $this->api_key = $this->thing->container["api"]["twitter"]["api_key"];
        $this->api_secret =
            $this->thing->container["api"]["twitter"]["api_secret"];

        $this->access_token =
            $this->thing->container["api"]["twitter"]["access_token"];
        $this->access_token_secret =
            $this->thing->container["api"]["twitter"]["access_token_secret"];
    }

    public function nullAction()
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["character", "action"],
            "null"
        );

        $this->message = "TWITTER | Request not understood. | TEXT SYNTAX";
        $this->sms_message = "TWITTER | Request not understood. | TEXT SYNTAX";
        $this->response = true;
        return $this->message;
    }

    public function run()
    {
        $this->heardTwitter();
        $this->affectTwitter();
        $this->countTweets();
    }

    public function countTweets() {
       if (!isset($this->tweets)) {$count = true;} else {

       $count = count($this->tweets);
       }

       $this->count_tweets = $count;
       return $count;

    }

    public function heardTwitter()
    {

        $file = $this->tweeted_words_location;
        if (!file_exists($file)) {
            $this->error .= "Tweeted words location not available. ";
            return true;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            $this->error .= "No tweeted words resource retrieved. ";
            return true;
        }

        // Get unique tokens
        $tokens = [];
        foreach ($this->tweets as $k => $v) {
            $pieces = explode(" ", strtolower($v["text"]));
            foreach ($pieces as $piece) {
                $tokens[] = $piece;
            }
        }
        $tokens = array_unique($tokens);

        // Write tokens not previously seen
        foreach ($tokens as $token) {
            if (stripos($contents, $token) !== false) {
                $t = trim($token) . "\n";
                file_put_contents($file, $t, FILE_APPEND | LOCK_EX);
            }
        }

    }

    public function makeTXT()
    {
        $txt = "";
        foreach ($this->tweets as $index => $tweet) {
            $txt .= $tweet["text"] . "\n";
            $txt .= $tweet["created_at"] . "\n";
        }

        $this->txt = $txt;
        $this->thing_report["txt"] = $txt;
    }

    public function makeSnippet()
    {
        $web = "";
        if (isset($this->channel)) {
        $web .= "Channel: ".  $this->channel;
        $web .= "<p>";
        }
        if (isset($this->tweets)) {
            $web .= "<ul>";
            foreach ($this->tweets as $index => $tweet) {
                $web .=
                    "<li><div>" . $this->restoreUrl($tweet["text"]) . "</div>";
                $web .= "<div>" . $tweet["created_at"] . "</div>";
                if (isset($tweet["affect"])) {
                    $web .=
                        "<div>" .
                        $tweet["affect"]["affect"] .
                        " (" .
                        $tweet["affect"]["pleasantness"] .
                        " " .
                        $tweet["affect"]["activation"] .
                        ")" .
                        "</div>";
                }
            }
            $web .= "</ul>";

        }
        $web .= "<p>";
        $web .= "Affect: " . $this->affect . " for this set";
        $web .= "<br>";
        $web .= "Tweets counted: " . $this->count_tweets;
        $web .= "<br>";
        $web .= "Tweet affect: " . round($this->affect / $this->count_tweets,1) . " per tweet";


        $this->snippet = $web;
        $this->thing_report["snippet"] = $web;
    }

    public function makeWeb()
    {
        $web = "";
        $web .= $this->snippet;

        $this->web = $web;
        $this->thing_report["web"] = $web;
    }

    function findWord($librex, $searchfor)
    {
        if ($librex == "" or $librex == " " or $librex == null) {
            return false;
        }

        switch ($librex) {
            case null:
            // Drop through
            case "affect":
                $file =
                    $this->resource_path . "twitter/twitter_words_affect.txt";
                $contents = file_get_contents($file);
                break;
            default:
                $file = $this->resource_path . "word/words.txt";
        }
        $pattern = "|\b($searchfor)\b|";

        // dev
        return;

        // search, and store all matching occurences in $matches

        if (preg_match_all($pattern, $contents, $matches)) {

            $m = $matches[0][0];
            return $m;
        } else {
            return false;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["choices"] = false;
        $this->thing_report["help"] = "In development.";
    }

    function makeHelp()
    {
    }

    public function readSubject()
    {
        $input = $this->input;
        $this->score = 1;
        if (stripos($input, "twitter") !== false) {
            $this->score = $this->score * 10;
        }
        if (stripos($input, "tweet") !== false) {
            $this->score = $this->score * 10;
        }

        $filtered_input = $this->assert($this->input, "twitter", false);

        if ($filtered_input == "") {
            $this->score = 20;
            $this->randomTweet();
            return;
        }

        $pieces = explode(" ", strtolower($filtered_input));

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "channel":
                            $this->score = $this->score * 10;
                            $prefix = "channel";
                            $words = preg_replace(
                                "/^" . preg_quote($prefix, "/") . "/",
                                "",
                                $input
                            );
                            $words = ltrim($words);

                            $this->channel = $words;

                            $this->randomTweet();
                            $this->response .=
                                "Saw a twitter channel request. ";
                            return;

                        default:
                    }
                }
            }
        }
        $this->channel = $filtered_input;
        $this->randomTweet();

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
        if (!isset($this->sms_message)) {
            if (!isset($this->random_tweet)) {
                $this->random_tweet = "test";
            }
            $this->sms_message = $this->random_tweet["text"];
        }
        $this->thing_report["sms"] = "TWITTER | " . $this->sms_message;
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

        $this->tweet = "#test";

        $this->connection = new \Abraham\TwitterOAuth\TwitterOAuth(
            $this->api_key,
            $this->api_secret,
            $this->access_token,
            $this->access_token_secret
        );
        $this->content = $this->connection->get("account/verify_credentials");

        $this->statuses = $this->connection->get("search/tweets", [
            "q" => "twitterapi",
        ]);

    }

    function randomTweet()
    {
        if (!isset($this->tweets)) {
            $this->getTweets();
        }
        $i = rand(0, count($this->tweets) - 1);
        $this->random_tweet = $this->tweets[$i];

        return $this->random_tweet;
    }

    function affectTweet($tweet = null)
    {
        if ($tweet === null) {
            return 0;
        }

        $a = $this->countActivations($tweet["text"]);
        $p = $this->countPleasantness($tweet["text"]);

        return [
            "activation" => $a,
            "pleasantness" => $p,
            "affect" => ($a + 1) * ($p + 1),
        ];
    }

    function affectTwitter()
    {
        $score = 0;
        foreach ($this->tweets as $i => $tweet) {
            $affect = $this->affectTweet($tweet);
            $this->tweets[$i]["affect"] = $affect;
            $score = $score + $affect['affect'];
        }
        $this->affect = $score;
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

        $this->tweet = "#test";

        $this->connection = new \Abraham\TwitterOAuth\TwitterOAuth(
            $this->api_key,
            $this->api_secret,
            $this->access_token,
            $this->access_token_secret
        );

        $this->statuses = $this->connection->get("search/tweets", [
            "q" => $channel,
        ]);

        $this->tweets = [];

        foreach ($this->statuses->statuses as $k => $v) {
            $this->tweets[] = [
                "text" => $v->text,
                "created_at" => $v->created_at,
            ];
        }

        if ($this->twitterOK() != "green") {
            return true;
        }

        return $this->tweets;
    }

    function twitterOK()
    {
        if ($this->connection->getLastHttpCode() === 200) {
            $this->response .=  "Your latest twitter request was OK. ";
            return "green";
        } else {
            $this->error .= "Error: A problem ocurred reading Twitter. ";
            return "red";
        }
    }

    // TODO
    // Create code to post tweets through Twitter API.
    // Outline code from Facebook here.

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

        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["twitter", "result"],
            $result
        );
        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable(
            ["twitter", "refreshed_at"],
            $time_string
        );
    }
}
