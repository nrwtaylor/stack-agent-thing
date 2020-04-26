<?php
//namespace Twitter;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';
//require_once '/var/www/html/stackr.ca/agents/TwitterOAuth.php';
require_once '/var/www/html/stackr.ca/agents/message.php';


//use Abraham\TwitterOAuth\TwitterOAuth;
//exit();
ini_set("allow_url_fopen", 1);

class Devtwitter {
	

	public $var = 'hello';


    function __construct(Thing $thing, $agent_input = null) {
		$this->agent_input = $agent_input;
//echo "test";

		$this->cost = 50;
	//function __construct($arguments) {

		//echo $arguments;
		//var_dump($arguments);
//  $defaults = array(
//    'uuid' => Uuid::uuid4(),
//    'from' => NULL,
//	'to' => NULL,
//	'subject' => NULL,
//	'sqlresponse' => NULL
//  );

//  $arguments = array_merge($defaults, $arguments);

//  echo $arguments['firstName'] . ' ' . $arguments['lastName'];




		// create container and configure it
		//$settings = require '/var/www/html/stackr.ca/src/settings.php';
		//$this->container = new \Slim\Container($settings);

		// create app instance
		

		//$app = new \Slim\App($this->container);
		//$this->container = $app->getContainer();


		


		$this->test= "Development code";



//		$thingy = $thing->thing;
		$this->thing = $thing;


		$this->thing_report = array('thing' => $this->thing->thing);
		$this->thing_report['info'] = 'This is a twitter message agent.';

                // Example
                //$this->api_key = $this->thing->container['api']['translink'];


        $this->api_key = $this->thing->container['api']['twitter']['api_key'];
        $this->api_secret = $this->thing->container['api']['twitter']['api_secret'];

        $this->access_token = $this->thing->container['api']['twitter']['access_token'];
        $this->access_token_secret = $this->thing->container['api']['twitter']['access_token_secret'];


        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		$this->sqlresponse = null;

        $this->agent_name = "devtwitter";    
        $this->keyword = "devtwitter";
        $this->keywords = array($this->keyword);
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) .'" ';	

        $this->node_list = array("sms send"=>array("sms send"));

		$this->thing->log( '<pre> Agent "Devtwitter" running on Thing ' .  $this->thing->nuuid . '.</pre>' );
		$this->thing->log( '<pre> Agent "Devtwitter" received this Thing "' .  $this->subject . '".</pre>' );



//		if ( $this->readSubject() == true) {
//			$this->thing_report = array('thing' => $this->thing->thing, 
//				'choices' => false,
//				'info' => "A cell number wasn't provided.",
//				'help' => 'from needs to be a number.');
//
//		        $this->thing->log( '<pre> Agent "Twitter" completed without sending a message.</pre>' );
//			return;
//		}

        $this->readSubject();

        // Note response does not include tweeting.
		$this->respond();


// Err ... making sure the state is saved.
//$this->thing->choice->Choose($this->state);

		// Which means at this point, we have a UUID
		// whether or not the record exists is another question.

		// But we don't need to find, it because the UUID is randomly created.	
		// Chance of collision super-super-small.

		// So just return the contents of thing.  false if it doesn't exist.
		
		//return $this->getThing();

 //       echo '<pre> Agent "Sms" end state is ';
        //$this->state = $thing->choice->load('token');
        //echo $this->thing->getState('usermanager');
 //       echo $this->state;
 ///       echo'"</pre>';



		$this->thing->log ( $this->agent_prefix . ' completed.' );

        $this->thing_report['log'] = $this->thing->log;

		return;

		}




//	function createAccount(String $account_name, $amount) {

//		$scalar_account = new Account($this->uuid, 'scalar', $amount, "happiness", "Things forgotten"); // Yup.
//		$this->thing->scalar = $scalar_account;
//		return;
//	}


// -----------------------

    public function nullAction()
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable( array("character", "action"), 'null' );

        $this->message = strtoupper($this->agent_name) .  " | Request not understood. | TEXT SYNTAX";
        $this->sms_message = strtoupper($this->agent_name) . " | Request not understood. | TEXT SYNTAX";
        $this->response = true;

        return $this->message;
    }

    function makeTXT()
    {
//        $txt = strtoupper($this->agent_name) . "\n";
//        $txt .= 'Searched twitter for "' . $this->channel .'".\n';

        if (!isset($this->tweets)) {$this->getTweets();}
        if (!isset($this->tweets[0]['activation'])) {$this->getAffect();} 


        $txt = strtoupper($this->agent_name) . "\n";
        $txt .= 'Searched twitter for "' . $this->channel .'".';
        $txt .= "\n\n";


        $sample_words_added = 0;
        $i =0;
        foreach ($this->tweets as &$tweet) {
            $i += 1;
            if (!is_array($tweet)) {continue;}

            $wrapped_tweet_text = wordwrap($tweet['text'],74, "\n", true);

            //$text = preg_replace('\n', '\n    ', $wrapped_tweet_txt); // 2 or more \n
            $indented_wrapped_tweet_text = $wrapped_tweet_text;

            $pieces = explode(" ", strtolower($tweet['text']));
            $l = null;
            $words_added =0;
            foreach ($pieces as $piece) {

                $piece = preg_replace("#[[:punct:]]#", "", $piece);
                $dict1 = $this->findWord('affect',$piece);

                if ($dict1 == false)  {
//echo "not found in twitter dict";

                    $dict2 = $this->findWord('pool',$piece);
//echo $dict2;
                    if ($dict2 == false) {
//echo "not found in pool dict";

                        // Not found in pool either
                        $l .= '"' .$piece .'",';
                        $words_added += 1;
                        $file = '/var/www/html/stackr.ca/temp/twitter_words.txt';
                        file_put_contents($file, $piece . "\n",      FILE_APPEND | LOCK_EX);


//$file = "/var/www/html/stackr.ca/temp/twitter_words.txt";
//$file = escapeshellarg($file); // for the security concious (should be everyone!)
//$line = `tail -n 1 $file`;

//echo "<br>";
//echo $line;

//exit();
                    }
                } else {
                    //if ($dict1 != false) {echo 'Found "' . $piece . '" in affect dictionary. ';}
                    //if ($dict2 != false) {echo 'Found "' . $piece . '" in pool dictionary.';}
                    //var_dump($dict1);
                }
//                if ($this->words_added > 0) {echo "added to lexicon<br>";}
            }

//            $txt .= $indented_wrapped_tweet_text . "\n";


            if ($words_added > 0) {

//                $txt .= "--- Tweet " . $i . " --- " . $words_added . " words added to lexicon ---\n";
//                $txt .= $l . "\n";

                $sample_words_added += $words_added;

            }

            $pleasant = $tweet['pleasant_count'];
            $unpleasant = $tweet['unpleasant_count'];

            $active = $tweet['active_count'];
            $passive = $tweet['passive_count'];

            $vivid = $tweet['vivid_count'];

            $twitter_activation = $tweet['twitter_activation'];


//            $txt = strtoupper($this->agent_name) . "\n";
//            $txt .= 'Searched twitter for "' . $this->channel .'".\n';


            $tweet['new_words_count'] = $words_added;


            $txt .= "--- Tweet " . $i . " --- " . $words_added . " words added to lexicon ---\n";
  //          $txt .= $l . "\n";

            if ($l != null) {$txt .= "Words added are " . $l . "\n";}

            $txt .=  "pleasant " . $pleasant . " unpleasant " . $unpleasant . " active " . $active . " passive " . $passive . " vivid " . $vivid . " twitter activation " . $twitter_activation;
            $txt .= "\n";
            $txt .=  "pleasantness " . $tweet['pleasantness_score'] . " activation " . $tweet['activation_score'];
            $txt .= "\n";
            $txt .= $indented_wrapped_tweet_text . "\n";





            $txt .= "\n";

        }
        $txt .= "\n--- Sample metrics ---\n";    
        $txt .= "Sample affect is (pleasantness, activation) = (".$this->pleasantness_score . ",". $this->activation_score . ").";
        $txt .= "\n";

        $txt .= $this->tweets['twitter_activation'];

     

//echo "\n";
        //var_dump($this->affect);

//exit();
    // Append a new person to the file
   // $current .= "John Smith\n";
    // Write the contents back to the file
   // file_put_contents($file, $current, FILE_APPEND | LOCK_EX);
        $this->thing_report['txt'] = $txt;

    }

    function findWord($librex, $searchfor)
    {
        if (($librex == "") or ($librex == " ") or ($librex == null)) {return false;}

        switch ($librex) {
            case null:
                // Drop through
         //   case 'affect':
         //       $file = '/var/www/html/stackr.ca/temp/twitter_words.txt';
         //       $contents = file_get_contents($file);
         //       break;
            case 'affect':
                $file = '/var/www/html/stackr.ca/resources/twitter/twitter_words_affect.txt';
                $contents = file_get_contents($file);
                break;
            case 'pool':
                $file = '/var/www/html/stackr.ca/temp/twitter_words.txt';
                $contents = file_get_contents($file);
                break;

            default:
                $file = '/var/www/html/stackr.ca/resources/word/words.txt';

        }

        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        $pattern = "/^.*$pattern.*\$/m";
        // search, and store all matching occurences in $matches
//
//var_dump(preg_match_all($pattern,$contents,$matches));
//exit();
        if(preg_match_all($pattern, $contents, $matches)){

            //echo "Found matches:\n";
            //$m = implode("\n", $matches[0]);

            $m = $matches[0][0];

//echo "match";
//var_dump($m);
//exit();
//echo "<br>";
            return $m;
        } else {
            //echo "no found";            
            return false;
            //echo "No matches found";
        }

    }

	private function respond() {

		// Thing actions


		$this->thing->flagGreen();


		// Generate email response.

		$to = $this->from;
//		$from = $this->to;

		//echo "<br>";



//		$choices = $this->thing->choice->makeLinks($this->state);
		//echo "<br>";
		//echo $html_links;

		if ($this->agent_input != null) {
			$test_message = $this->agent_input;
		} else {
			$test_message = $this->subject;
		}

$this->makeTXT();
$this->makeSMS();
//$this->makeTXT();
//var_dump($test_message);
//		if ($this->thing->account['stack']->balance['amount'] >= $this->cost ) {

// !!!!!
//			$this->sendMessage($to, $test_message);

//			$this->thing->account['stack']->Debit($this->cost);
//			$this->thing->log("FB message sent");

			$this->thing_report['info'] = '<pre> Agent "Twitter" sent a twitter message to ' . $this->from . '.</pre>';
//
//		} else {
//
//			$this->thing_report['info'] = 'SMS not sent.  Balance of ' . $this->thing->account['stack']->balance['amount'] . " less than " . $this->cost ;
//		}/
//exit();


        if ($this->agent_input == null) { 
           $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;

//echo $this->sms_message;
        }

        //$this->thing_report['help'] = 'This Flag is either RED or GREEN. RED means busy.';
        $this->makeHelp();



        $this->thing_report['choices'] = false;
//$this->thing_report['info'] = 'This is a facebook message agent.';
        $this->thing_report['help'] = 'In development.';
        $this->thing_report['log'] = $this->thing->log;

		//echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}

    function makeHelp() {

    }

	public function readSubject() {

       $this->getAffect();
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

//        $keywords = array('twitter');
$keywords = $this->keywords;
        //$input = strtolower($this->subject);

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));


        if (count($pieces) == 1) {

            $input = $this->subject;

            if (strtolower($input) == $this->keyword) {

               $this->randomTweet();
                return;
            }
        }




        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {


                        case $this->keyword:   

                            $prefix = $this->keyword;
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);

$this->channel = $words;
//exit();

//                            $this->Get($words);
                            $this->randomTweet();

                            return;

                        case "cat":   

                            //$prefix = $this->keyword;
                            //$words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            //$words = ltrim($words);

                            $this->channel = "cat";

//$this->channel = "southampton";
//exit();

//                            $this->Get($words);
                            $this->randomTweet();
//echo $this->pleasantness;
//echo $this->activation;
// Then run it through the affect rater.

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

    function makeMessage($message = null) {

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
    "id":"'. $sender.'"
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
            "subtitle":"' . $message . '"
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

    function makeSMS() {
//echo "meep";
//        $this->sms_message = "test";
        if (!isset($this->sms_message)) {
            if (!isset($this->random_tweet)) {
                $this->sms_message = "DEVTWITTER | " . "Bork.";
                $this->thing_report['sms'] = $this->sms_message;
                return;

            }

            $this->sms_message = "DEVTWITTER | " . $this->random_tweet['text'];
            $this->sms_message .= " | new words " . $this->random_tweet['new_words_count'];
            $this->sms_message .= " | score " . $this->random_tweet['pleasantness_score'] ." " . $this->random_tweet['activation_score'];
            $this->thing_report['sms'] = $this->sms_message;
            return;
        }

 //$tweet['new_words_count']
        $this->thing_report['sms'] = $this->sms_message;
        return;
    }

    function makeBasicMessage($message = null) {

        if ($this->input != null) {
            $message = $this->input;
        } else {
            $message = $this->subject;
        }


        $sender = $this->from;

        $jsonData = '{
            "recipient":{
               "id":"'. $sender.'"
            },
            "message":{
                "text":"'.$message.'"
            }
        }';

        $this->json_message = $jsonData;

    }

    function Tweet($to, $text) {


                        if (!isset($this->tweet)) {

                            // Build tweet from $to and $text.

                        }

                        //$to = "id";
                        $this->tweet = "#test";

                        $this->connection = new \Abraham\TwitterOAuth\TwitterOAuth($this->api_key, $this->api_secret, $this->access_token, $this->access_token_secret);
                        $this->content = $this->connection->get('account/verify_credentials');

$this->statuses = $this->connection->get("search/tweets", ["q" => "twitterapi"]);

//echo "<br>";
//var_dump($this->content);
//var_dump($this->statuses);
//var_dump(count($this->statuses));
//echo "<br>";

//var_dump($this->twitterOK());
//exit();

//                        $connection->post('statuses/update', array('status' => $this->tweet));
                        if ($connection->getLastHttpCode() === 200) {
                            $this->thing->log('<p><strong>Your latest tweet:</strong> '. $tweet .'</p>'.PHP_EOL);
                        } else {
                            $this->thing->log('<p><strong>Error:</strong> A problem ocurred. You filled your Twitter credentials correctly? Or walk abusing the Twitter API?</p>'.PHP_EOL);
                        }




    }


    function randomTweet()
    {
        if (!isset($this->tweets)) {$this->getTweets();}
//echo count($this->tweets);
//exit();

        $i = array_rand($this->tweets);
        $this->random_tweet = $this->tweets[$i];

//var_dump($this->random_tweet);
    }


    function getVividness()
    {

        if (!isset($this->tweets)) {$this->getTweets();}

        $this->tweets['vivid_count'] = 0;
        foreach ($this->tweets as &$tweet) {

            if (!is_array($tweet)) {continue;}
            $pieces = explode(" ", strtolower($tweet['text']));
            $tweet['vivid_count'] = 0;

            foreach ($pieces as $piece) {

                $t = $this->findWord('affect',$piece);
                if ($t != false) {
                    $c = substr_count($t, 'vivid');
                    $tweet['vivid_count'] += $c;
                }

                $tweet['vividness_score'] = $tweet['vivid_count'];
                $this->tweets['vivid_count'] += $tweet['vivid_count'];
            }
        }

        $this->vividness_score = $this->tweets['vivid_count'];
        return $this->vividness_score;
    }


    function getPleasantness() {

        // The free dictionary
        // 1. Giving or affording pleasure or enjoyment; agreeable:
        // a pleasant scene; pleasant sensations. 
        // 2. Pleasing in manner, behavior, or appearance.

        // Going to focus on the first.

        if (!isset($this->tweets)) {$this->getTweets();}

        $this->tweets['pleasant_count'] = 0;
        $this->tweets['unpleasant_count'] = 0;            
//        $this->tweets['vivid_count'] = 0;


        foreach ($this->tweets as &$tweet) {

            $pieces = explode(" ", strtolower($tweet['text']));
            if (!is_array($tweet)) {continue;}

            $tweet['pleasant_count'] = 0;
            $tweet['unpleasant_count'] = 0;            
            $tweet['pleasantness_score'] = 0;

            foreach ($pieces as $piece) {

                $t = $this->findWord('affect',$piece);
                if ($t != false) {

                    $c = substr_count($t, 'pleasant');
                    $tweet['pleasant_count'] += $c;

                    $c = substr_count($t, 'unpleasant');
                    $tweet['unpleasant_count'] += $c;
                }

                //$tweet['pleasantness_score'] = $twe$pleasant_count;
                //$tweet['unpleasant_count'] += $unpleasant_count;
                //$tweet['vivid_count'] += $vivid_count;

            }

        $tweet['pleasantness_score'] = ($tweet['pleasant_count'] - $tweet['unpleasant_count']);


        $this->tweets['pleasant_count'] += $tweet['pleasant_count'];
        $this->tweets['unpleasant_count'] += $tweet['unpleasant_count']; 
   //     $this->tweets['vivid_count'] += $tweet['vivid_count'];

        }


        //echo "pleasant " .$this->tweets['pleasant_count'];
        //echo " unpleasant " .$this->tweets['unpleasant_count'];            
        //echo " vivid " .$this->tweets['vivid_count'];
        //echo "<br>";

        $this->pleasantness_score = $this->tweets['pleasant_count'] -
                                    $this->tweets['unpleasant_count'];

        //echo "pleasantness score " . $this->pleasantness_score;

        return $this->pleasantness_score;
    }

    function getActivation() {

        if (!isset($this->tweets)) {$this->getTweets();}

        $this->tweets['active_count'] = 0;
        $this->tweets['passive_count'] = 0;            
        //$this->tweets['vivid_count'] = 0;

        $this->tweets['quote_count'] = 0;
        $this->tweets['reply_count'] = 0;            
        $this->tweets['retweet_count'] = 0;
        $this->tweets['favorite_count'] = 0;

        $this->tweets['twitter_activation'] = 0;
        $this->tweets['activation_score'] = 0;


        foreach ($this->tweets as &$tweet) {

            if (!is_array($tweet)) {continue;}

                $pieces = explode(" ", strtolower($tweet['text']));

                $tweet['active_count'] = 0;
                $tweet['passive_count'] = 0;            
          //      $tweet['vivid_count'] = 0;

                foreach ($pieces as $piece) {

                    $t = $this->findWord('affect',$piece);

                    if ($t != false) {

                        // Count instances of active or passive and vivid
                        $c = substr_count($t, 'active');
                        $tweet['active_count'] += $c;

                        $c = substr_count($t, 'passive');
                        $tweet['passive_count'] += $c;
    
            //            $c = substr_count($t, 'vivid');
              //          $tweet['vivid_count'] += $c;
                }

        $tweet['twitter_activation'] = $tweet['quote_count'] + $tweet['reply_count'] + $tweet['retweet_count'] + $tweet['favorite_count'];


switch ($tweet['twitter_activation']) {
    case $tweet['twitter_activation'] > 100:
        $twit = 4;
        break;
    case $tweet['twitter_activation']> 10:
        $twit = 3;
        break;
    case $tweet['twitter_activation']> 0:
        $twit = 2;
        break;
    default:
        $twit = 1;
}



        $tweet['activation_score'] = $twit + $tweet['active_count'] - $tweet['passive_count'];



                $this->tweets['active_count'] += $tweet['active_count'];
                $this->tweets['passive_count'] += $tweet['passive_count'];            

                $this->tweets['quote_count'] += $tweet['quote_count'];
                $this->tweets['reply_count'] += $tweet['reply_count'];            
                $this->tweets['retweet_count'] += $tweet['retweet_count'];
                $this->tweets['favorite_count'] += $tweet['favorite_count'];

                $this->tweets['twitter_activation'] += $tweet['twitter_activation'];
             $this->tweets['activation_score'] += $tweet['activation_score'];


            }
        }


        //echo "active " .$this->tweets['active_count'];
        //echo "passive " .$this->tweets['passive_count'];            
        //echo "vivid " .$this->tweets['vivid_count'];


        $this->activation_score = $this->tweets['activation_score'];
/*$this->tweets['quote_count'] + 
                                    $this->tweets['reply_count'] +
                                    $this->tweets['retweet_count'] +
                                    $this->tweets['favorite_count'] +
                                    $this->tweets['active_count'] -
                                    $this->tweets['passive_count']; 
  */                                  

//        echo "activation score " . $this->activation_score;

        return $this->activation_score;

    }


    function getAffect() {

       $this->getPleasantness(); // Negative to Positive computed ranges
       $this->getActivation(); // Negative to Positive computed ranges

       $this->getVividness();

       $this->affect = array("pleasantness"=>$this->pleasantness_score, "activation"=>$this->activation_score);


    }


    function getTweets($channel = null) {

        if ($channel == null) {
            if (isset($this->channel)) {$channel = $this->channel;} else {$channel = "#vancouver #yvr";}
        }

                        //$to = "id";
                        $this->tweet = "#test";

                        require_once '/var/www/html/stackr.ca/vendor/abraham/twitteroauth/src/TwitterOAuth.php';

                        $this->connection = new \Abraham\TwitterOAuth\TwitterOAuth($this->api_key, $this->api_secret, $this->access_token, $this->access_token_secret);
//                        $this->content = $this->connection->get('account/verify_credentials');
//var_dump($channel);
//exit();
$channel = "cat";
$this->statuses = $this->connection->get("search/tweets", ["q" => $channel, "count" => 20, "result_type"=>'recent']);

//echo "<br>";
$this->tweets = array();
//var_dump($this->content);
foreach ($this->statuses->statuses as $k=>$v) {
//var_dump($v->text);

if (isset($v->quote_count)) {$quote_count = $v->quote_count;} else {$quote_count = null;}
if (isset($v->reply_count)) {$reply_count = $v->reply_count;} else {$reply_count = null;}


$this->tweets[] = array("text"=>$v->text, 
            "created_at"=>$v->created_at,
            "quote_count"=>$quote_count,
            "reply_count"=>$reply_count,
            "retweet_count"=>$v->retweet_count,
            "favorite_count"=>$v->favorite_count);
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
                            $this->thing->log( '<p><strong>Your latest twitter request was OK.</p>'.PHP_EOL);
                            return 'green';
                        } else {
                            $this->thing->log( '<p><strong>Error:</strong> A problem ocurred. You filled your Twitter credentials correctly? Or walk abusing the Twitter API?</p>'.PHP_EOL);
                            return 'red';
                        }

    }

    function sendMessage($to, $text) {

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
        $url = 'https://graph.facebook.com/v2.6/me/messages?access_token='.$this->page_access_token;

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
        $jsonData = '{
            "recipient":{
               "id":"'. $sender.'"
            },
            "message":{
                "text":"'.$message_to_reply.'"
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        //Execute the request
        if( !empty($message_to_reply) ){
            $result = curl_exec($ch);
        }


                    $this->thing->json->setField("variables");
                    $names = $this->thing->json->writeVariable( array("facebook", "result"), $result );
                        $time_string = $this->thing->json->time();
                        $this->thing->json->writeVariable( array("facebook", "refreshed_at"), $time_string );




        return;
    }


}

?>

