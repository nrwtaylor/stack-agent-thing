<?php
namespace Nrwtaylor\StackAgentThing;

// Agent resolves message disposition

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Agent
{
	function __construct(Thing $thing, $input = null)
    {
        // Start the timer
        $this->start_time = $thing->elapsed_runtime();
        //microtime(true);

		$this->agent_input = strtolower($input);
//        $this->agent_name = "Agent";
//        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';

        $this->agent_name = 'agent';

        $this->agent_name = strtolower(get_class());
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent_name) . '" ';



		// Given a "thing".  Instantiate a class to identify
		// and create the most appropriate agent to respond to it.

		$this->thing = $thing;
//        $this->thing->elapsed_runtime();
//		$this->agent_name = 'agent';

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

        //$this->thing->container->db->commit();

        $this->getMeta();

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];



//        $this->uuid = $thing->uuid;
//      	$this->to = $thing->to;
//      	$this->from = $thing->from;
//      	$this->subject = $thing->subject;

		$this->sqlresponse = null;

		$this->thing->log('running on Thing ' . $this->thing->nuuid . '.');
        $this->thing->log('read "' . $this->subject . '".');

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';
        $this->agents_path = $GLOBALS['stack_path'] . 'agents/';
        $this->agents_path = $GLOBALS['stack_path'] . 'vendor/nrwtaylor/stack-agent-thing/agents/';

        $this->current_time = $this->thing->time();

        $this->verbosity = 9;

        $this->context = null;

// First things first... see if Mordok is on.
/* Think about how this should work 
and the user UX/UI
            $mordok_agent = new Mordok($this->thing);
    
            if ($mordok_agent->state == "on") {

		$thing_report = $this->readSubject();

		$this->respond();

} else {
// Don't

}
*/
        $this->init();

        $this->get();

        $this->readSubject();

        $this->run();

        $this->set();

        if ($this->agent_input == null) {
            $this->respond();
        }

        // Following line stops Gearman error, but turns off images obvs Jul 11, 2018 
        //$this->thing_report['png'] = null;

		//$this->thing_report = $thing_report;

        $this->thing->log('ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;

		return;
	}

    public function init()
    {
    }

    public function get()
    {
    }

    public function set()
    {
    }

    public function run()
    {
    }

    public function getMeta($thing = null)
    {

        if ($thing == null) {$thing = $this->thing;}

        $this->uuid = $thing->uuid;
//        $this->to = $thing->to;
//        $this->from = $thing->from;
//        $this->subject = $thing->subject;

        if (!isset($thing->to)) {$this->to = null;} else {$this->to = $thing->to;}
        if (!isset($thing->from)) {$this->from = null;} else {$this->from = $thing->from;}
        if (!isset($thing->subject)) {$this->subject = null;} else {$this->subject = $thing->subject;}
    }

	public function respond()
    {
		$this->thing->flagGreen();
		return;
	}

    private function getPrior()
    {
        // See if the previous subject line is relevant
        $this->thing->db->setUser($this->from);
        $prior_thing_report = $this->thing->db->priorGet();

        $task = $prior_thing_report['thing']->task ;
        $nom_to = $prior_thing_report['thing']->nom_to ;

        $temp_haystack = $nom_to . ' ' . $task;
    }

    private function getNgrams($input, $n = 3)
    {
        $words = explode(' ',$input);
        $ngrams = array();

        foreach ($words as $key=>$value) {

            if ($key < count($words) - ($n - 1)) {
                $ngram = "";
                for ($i = 0; $i < $n; $i++) {
                    $ngram .= $words[$key + $i];
                }
                $ngrams[] = $ngram;
            }
        }
        return $ngrams;
    }

    function timeout($time_limit = null, $input = null)
    {
        if ($time_limit == null) {
            $time_limit = 10000;
        }

        if ($input == null) {
            $input = "No matching agent found. ";
        }

        // Timecheck

        switch (strtolower($this->context)) {
            case 'place':
                $array = array('place','mornington crescent');
                break;
            case 'group':
                $array = array('group', 'say hello', 'listen','join');
                break;
            case 'train':
                $array = array('train', 'run train', 'red', 'green', 'flag');
                break;
            case 'headcode':
                $array = array('headcode');
                break;
            case 'identity':
                $array = array('headcode','mordok','jarvis','watson');
                break;
            default:
                $array = array('link','roll d20', 'roll','iching', 'bible', 'wave', 'eightball', 'read','group','flag','tally','emoji','red','green','balance','age','mordok','pain','receipt','key','uuid','remember','reminder','watson','jarvis','whatis','privacy','?');
        }

        $k = array_rand($array);
        $v = $array[$k];

        $response = $input . "Try " . strtoupper($v) . ".";

        if ($this->thing->elapsed_runtime() > $time_limit) {

            $this->thing->log( 'Agent "Agent" timeout triggered. Timestamp ' . number_format($this->thing->elapsed_runtime()) );

            $timeout_thing = new Timeout($this->thing, $response);
            $this->thing_report = $timeout_thing->thing_report;

            return $this->thing_report;
        }

        return false;

    }

	public function readSubject()
    {
		$status = false;
		$this->response = false;
        // Because we need to be able to respond to calls
        // to specific Identities.

		$input = strtolower($this->agent_input . " " . $this->to . " " .$this->subject);
        if ($this->agent_input == null) {
            $input = strtolower($this->to . " " . $this->subject);
        } else {
            $input = strtolower($this->agent_input);
        }

        if (strpos($this->agent_input, 'receipt') !== false) {
            $this->thing->log( 'Agent created a Receipt agent' );
            $receipt_thing = new Receipt($this->thing);
            $this->thing_report = $receipt_thing->thing_report;

            return $this->thing_report;
        }

        if (strpos($this->agent_input, 'flag') !== false) {
            $this->thing->log( '<pre> Agent created a Flag agent</pre>' );
            $flag_thing = new Flag($this->thing);
            $this->thing_report = $flag_thing->thing_report;

            return $this->thing_report;
        }

        if (strpos($this->agent_input, 'satoshi') !== false) {

            $this->thing->log( '<pre> Agent created a Satoshi agent</pre>' );
            $satoshi_thing = new Satoshi($this->thing);
            $this->thing_report = $satoshi_thing->thing_report;

            return $this->thing_report;

        }

        if (strpos($this->agent_input, 'iching') !== false) {

            $this->thing->log( '<pre> Agent created a iChing agent</pre>' );
            $iching_thing = new Iching($this->thing);
            $this->thing_report = $iching_thing->thing_report;
            return $this->thing_report;
        }

        if (strpos($this->agent_input, 'whatis') !== false) {

            $this->thing->log( '<pre> Agent created a Whatis agent</pre>' );
            $whatis_thing = new Whatis($this->thing);
            $this->thing_report = $whatis_thing->thing_report;
            return $this->thing_report;
        }

        if (strpos($this->agent_input, 'train') !== false) {
            $this->thing->log( '<pre> Agent created a Train agent</pre>' );
            $train_thing = new Train($this->thing);
            $this->thing_report = $train_thing->thing_report;
            return $this->thing_report;
        }

        if (strpos($this->agent_input, 'snowflake') !== false) {
            $this->thing->log( '<pre> Agent created a Snowflake agent</pre>' );
            $snowflake_thing = new Snowflake($this->thing);
            $this->thing_report = $snowflake_thing->thing_report;
            return $this->thing_report;
        }

        // First things first.  Special instructions to ignore.
        if (strpos($input, 'cronhandler run') !== false) {
            $this->thing->log( 'Agent "Agent" ignored "cronhandler run".' );
            $this->thing->flagGreen();
            //$thing_report['thing'] = $this->thing;
            $this->thing_report['thing'] = $this->thing->thing;
            $this->thing_report['info'] = 'Mordok ignored a "cronhandler run" request.';
            //$usermanager_thing = new Optout($this->thing);
            //$thing_report = $usermanager_thing->thing_report;
            return $this->thing_report;
        }

        // Second.  Ignore web view flags for now.
        if (strpos($input, 'web view') !== false) {
        $this->thing->log( 'Agent "Agent" ignored "web view".' );
            $this->thing->flagGreen();
            $this->thing_report['thing'] = $this->thing->thing;
            $this->thing_report['info'] = 'Mordok ignored a "web view" request.';
            return $this->thing_report;
        }

        // Third.  Forget.
        if (strpos($input, 'forget') !== false) {
        //if (strtolower($input) == 'forget') {

        if (strpos($input, 'all') !== false) {
            // pass through
        } else {
            $this->thing->log( 'Agent "Agent" did not ignore a forget".' );
            //$this->thing->flagGreen();
            $this->thing->Forget();
            $this->thing_report = false;
            $this->thing_report['info'] = 'Agent did not ignore a "forget" request.';
            $this->thing_report['sms'] = "FORGET | That Thing has been forgotten.";
            return $this->thing_report;
        }
    }

       //if (strpos($input, 'flag') !== false) {
        $check_beetlejuice = false;
        if ($check_beetlejuice) {
              $this->thing->log( 'Agent "Agent" created a Beetlejuice agent looking for incoming message repeats.' );
                        $beetlejuice_thing = new Beetlejuice($this->thing);

            if ($beetlejuice_thing->flag == "red") {
                $this->thing->log( 'Agent "Agent" has heard this three times.' );
            }

            $this->thing_report = $beetlejuice_thing->thing_report;
            //return $thing_report;
         }

        $burst_check = true; // Runs in about 3s.  So need something much faster.
        $burst_limit = 8;

        $burst_age_limit = 900; //s
        $similarness_limit = 100;
        $similiarities_limit = 500; //
        $burstiness_limit = 750;
        $bursts_limit = 1;

        if ($burst_check) {
            $this->thing->log( 'Agent "Agent" created a Burst agent looking for burstiness.', "DEBUG" );
            $burst = new Burst($this->thing, 'read');

            $this->thing->log( 'Agent "Agent" created a Similar agent looking for incoming message repeats.',"DEBUG" );

            $similar = new Similar($this->thing, 'read');

            $similarness = $similar->similarness;
            $bursts = $burst->burst;

            $burstiness = $burst->burstiness;
            $similarities = $similar->similarity;

            $elapsed = $this->thing->elapsed_runtime();

            $burst_age_limit = 900; //s
            $similiarness_limit = 90;

            $burst_age = strtotime($this->current_time) - strtotime($burst->burst_time);
            if ($burst_age < 0) {$burst_age = 0;}


            if ( ($bursts >= $bursts_limit) and
                ($burstiness < $burstiness_limit) and
                ($similarities >= $similiarities_limit) and
              ($similarness < $similarness_limit) and
                ($burst_age < $burst_age_limit) ) {
                // Don't respond
                $this->thing->log( 'Agent "Agent" heard similarities, similarness, with bursts and burstiness.', "WARNING" );

                if ($this->verbosity >= 9) {
                    $t = new Hashmessage($this->thing, "#channelbursts ". $bursts . "/" .$bursts_limit .
                                                     " #channelburstiness ". $burstiness ."/".$burstiness_limit .
                                                     " #channelsimilarities ". $similarities ."/".$similiarities_limit .
                                                     " #channelsimilarness ". $similarness ."/".$similiarness_limit . 
                                                     " #thingelapsedruntime ". $elapsed . 
                                                    " #burstage ". $burst_age
                                                    );
            } elseif ($this->verbosity >=8) {
                                    $t = new Hashmessage($this->thing, "MESSAGE | #stackoverage | wait " 
. number_Format(($burst_age_limit - $burst_age)/ 60) ." minutes");

                 } elseif ($this->verbosity >=7) {
                                    $t = new Hashmessage($this->thing, "MESSAGE | The stack is handling a burst of similar requests. | Wait " 
. number_Format(($burst_age_limit - $burst_age)/ 60) ." minutes and then retry.");

           } else {
                    $t = new Hashmessage($this->thing, "#testtesttest 15m timeout"
                                                    );
                }


                $this->thing_report = $t->thing_report;
                return $this->thing_report;

            }

        $this->thing->log( 'Agent "Agent" noted burstiness ' . $burstiness . ' and similarness ' . $similarness . '.' );

        }






        // Based on burstiness and similiary decide if this message is okay.
      //  if ($burstiness

//        $this->thing->log( 'Agent "Agent" noted burstiness ' . $burstiness . ' and similarness ' . $similarness . '.' );
/*

                if (($burstiness < 1000) and ($similarness < 100)) {
                    $t = new Hashmessage($this->thing, "#burstiness". $burstiness. "similarness" . $similarness);
                    $thing_report = $t->thing_report ;

                    return $thing_report;
                }
*/

        // Expand out emoji early
        // devstack - replace this with a fast general character
        // character recognizer of concepts.
        $emoji_thing = new Emoji($this->thing, "emoji");
        $this->thing_report = $emoji_thing->thing_report;


        if (isset($emoji_thing->emojis)) {
            // Emoji found.
            $input = $emoji_thing->translated_input;
        }

		$this->thing->log('<pre> Agent "Agent" processed haystack "' .  $input . '".</pre>', "DEBUG");

		// Now pick up obvious cases where the keywords are embedded
		// in the $input string.

		$this->thing->log('<pre> Agent "Agent" looking for optin/optout.</pre>');
        //    $usermanager_thing = new Usermanager($this->thing,'usermanager');

		if (strpos($input, 'optin') !== false) {
		$this->thing->log( '<pre> Agent created a Usermanager agent</pre>' );
			$usermanager_thing = new Usermanager($this->thing);
			$this->thing_report = $usermanager_thing->thing_report;
			return $this->thing_report;
		}

		if (strpos($input, 'optout') !== false) {
		$this->thing->log( '<pre> Agent created a Usermanager agent</pre>' );
			$usermanager_thing = new Optout($this->thing);
			$this->thing_report = $usermanager_thing->thing_report;
			return $this->thing_report;
		}

		if (strpos($input, 'opt-in') !== false) {
			$this->thing->log( '<pre> Agent created a Usermanager agent</pre>' );
			$usermanager_thing = new Optin($this->thing);
			$this->thing_report = $usermanager_thing->thing_report;
			return $this->thing_report;
		}

		if (strpos($input, 'opt-out') !== false) {
		$this->thing->log( '<pre> Agent created a Usermanager agent</pre>' );
			$usermanager_thing = new Optout($this->thing);
			$this->thing_report = $usermanager_thing->thing_report;
			return $this->thing_report;
		}

        // Then look for messages sent to UUIDS
        $this->thing->log('Agent "Agent" looking for UUID in address.');

        // Is Identity Context?

        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";
        if (preg_match($pattern, $this->to)) {
            $this->thing->log('Agent "Agent" found a  UUID in address.', "INFORMATION");

            $uuid_thing = new Uuid($this->thing);

            $this->thing_report = $uuid_thing->thing_report;
            return $this->thing_report;
        }


        $this->thing->log('Agent "Agent" looking for UUID in input.');

        // Is Identity Context?
        $uuid = new Uuid($this->thing, "extract");
        $uuid->extractUuids($input);


        if ((isset($uuid->uuids)) and (count($uuid->uuids) > 0)) {
            $this->thing->log('Agent "Agent" found a  UUID in input.', "INFORMATION");
            // $this->thing_report = $uuid->thing_report;
            // And then ignored it.
        }

        $headcode = new Headcode($this->thing, "extract");
        $headcode->extractHeadcodes($input);

        if ($headcode->response === true) {
            // pass echo "not a headcode...";
        } else {
            //if ( is_string($headcode->head_code)) { 

            if ( (is_array($headcode->head_codes) and (count($headcode->head_codes) > 0))) { 
                $this->thing->log('Agent "Agent" found a headcode in address.', "INFORMATION");
                $headcode_thing = new Headcode($this->thing);
                $this->thing_report = $headcode_thing->thing_report;
                return $this->thing_report;
            }
        }

        // Temporarily alias robots
        if (strpos($input, 'robots') !== false) {
        $this->thing->log( '<pre> Agent created a Robot agent</pre>', "INFORMATION" );
            $robot_thing = new Robot($this->thing);
            $this->thing_report = $robot_thing->thing_report;
            return $this->thing_report;
        }

        $this->thing->log( 'now looking at Words (and Places and Characters).  Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

		// See if there is an agent with the first workd
		$arr = explode(' ',trim($input));

		$agents = array();

        $bigrams = $this->getNgrams($input, $n = 2);
        $trigrams = $this->getNgrams($input, $n = 3);

        $arr = array_merge($arr, $bigrams);
        $arr = array_merge($arr, $trigrams);

        // Added this March 6, 2018.  Testing.
        if ($this->agent_input == null) {
            $arr[] = $this->to;
        } else {
            $arr = explode(' ' ,$this->agent_input);
        }

        set_error_handler(array($this, 'warning_handler'), E_WARNING);
		//set_error_handler("warning_handler", E_WARNING);

		$this->thing->log('Agent "Agent" looking for keyword matches with available agents.', "INFORMATION");

		foreach ($arr as $keyword) {
            // Don't allow agent to be recognized
            if (strtolower($keyword) == 'agent') {continue;}

        	$agent_class_name = ucfirst(strtolower($keyword));

            // Can probably do this quickly by loading path list into a variable
            // and looping, or a direct namespace check.
            $filename = $this->agents_path .  $agent_class_name . ".php";
            if (file_exists($filename)) {
                $agents[] = $agent_class_name;  
            }

		}

		//set_error_handler("warning_handler", E_WARNING); //dns_get_record(...) 
		restore_error_handler();

        // What effect would this have?
        //$agents = array_reverse($agents);


        // Prefer longer agent names
        usort($agents, function($a, $b) {
            return strlen($b) <=> strlen($a);
        });

		foreach ($agents as $agent_class_name) {
            //$agent_class_name = '\Nrwtaylor\Stackr\' . $agent_class_name;
			// Allow for doing something smarter here with 
			// word position and Bayes.  Agent scoring
			// But for now call the first agent found and
            // see where that consistency takes this.

            // Ignore Things for now 19 May 2018 NRWTaylor
            if ($agent_class_name == "Thing") {
                continue;
            }

            // And Email ... because email\uuid\roll otherwise goes to email
            if ((count($agents) > 1) and ($agent_class_name == "Email")) {
                continue;
            }

			try {

                $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;

                $this->thing->log( 'trying Agent "' . $agent_class_name . '".', "INFORMATION" );
                $agent = new $agent_namespace_name($this->thing);

                // If the agent returns true it states it's response is not to be used.
                if ((isset($agent->response)) and ($agent->response === true)) {
                    throw new Exception("Flagged true.");
                }

				$this->thing_report = $agent->thing_report;

			} catch (\Error $ex) { // Error is the base class for all internal PHP error exceptions.
                $this->thing->log( 'could not load "' . $agent_class_name . '".' , "WARNING" );
                // echo $ex;
    			$message = $ex->getMessage();
	    		// $code = $ex->getCode();
		    	$file = $ex->getFile();
			    $line = $ex->getLine();

    			$input = $message . '  ' . $file . ' line:' . $line;
                $this->thing->log($input , "WARNING" );

                // This is an error in the Place, so Bork and move onto the next context.
        		// $bork_agent = new Bork($this->thing, $input);
	    		continue;

			}

			return $this->thing_report;
		}

        $this->thing->log( 'did not find an Ngram agent to run.', "INFORMATION" );

        //$run_time = microtime(true) - $this->start_time;
        //$milliseconds = round($run_time * 1000);

        $this->thing->log( 'now looking at Group Context.' );

        // So no agent ran.

        // Which means that Mordok doesn't have a concept for any
        // emoji which were included.

        // Treat a single emoji as a request
        // for information on the emoji.

        if ( (isset($emoji_thing->emojis)) and (count($emoji_thing->emojis)>0) ) {
            $emoji_thing = new Emoji($this->thing);
            $this->thing_report = $emoji_thing->thing_report;

            return $this->thing_report;
        }

/*
        $this->thing->log( $this->agent_prefix .'now looking at Place Context.  Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );



        $place_thing = new Place($this->thing, $this->agent_input);
        $thing_report = $place_thing->thing_report;

var_dump($this->subject);
var_dump($place_thing->place_code);
var_dump($place_thing->place_name);

        if ((isset($place_thing->place_code)) and ($place_thing->place_code != false) ) {

            $place_thing = new Place($this->thing, $this->place_thing->place_code);
            $this->thing_report = $place_thing->thing_report;
            return $this->thing_report;

        }
*/

        $this->thing->log( 'now looking at Transit Context.' );

        $transit_thing = new Transit($this->thing, "extract");
        $this->thing_report = $transit_thing->thing_report;

        if ((isset($transit_thing->stop)) and ($transit_thing->stop != false) ) {

            $translink_thing = new Translink($this->thing);
            $this->thing_report = $translink_thing->thing_report;
            return $this->thing_report;

        }

        $this->thing->log( 'now looking at Place Context.' );
        $place_thing = new Place($this->thing, "extract");
        $this->thing_report = $place_thing->thing_report;

        if (($place_thing->place_code == null) and ($place_thing->place_name == null) ) {
            //echo "place not found";
        } else {
            //echo "place found";
            $place_thing = new Place($this->thing);
            $this->thing_report = $place_thing->thing_report;
            return $this->thing_report;
        }

/*
        // This would allow web based agent to update state
        // devstack think 
        // Now check for any place agent input
        $this->thing->log( $this->agent_prefix .'now looking at Place Context.' );
        $place_thing = new Place($this->thing, $this->agent_input);
        $this->thing_report = $place_thing->thing_report;

        if (($place_thing->place_code == null) and ($place_thing->place_name == null) ) {
echo "place not found";
        } else {
echo "place found";
            $place_thing = new Place($this->thing, $this->agent_input);
            $this->thing_report = $place_thing->thing_report;
            return $this->thing_report;
        }
*/


        $this->thing->log( 'now looking at Nest Context.  Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );


		if (strpos($input, 'nest maintenance') !== false) {

			$ant_thing = new Ant($this->thing);
			$this->thing_report = $ant_thing->thing_report;
			return $this->thing_report;
		}

		if (strpos($input, 'patrolling') !== false) {
			$ant_thing = new Ant($this->thing);
			$this->thing_report = $ant_thing->thing_report;
			return $this->thing_report;
		}

		if (strpos($input, 'foraging') !== false) {
			$ant_thing = new Ant($this->thing);
			$this->thing_report = $ant_thing->thing_report;
			return $this->thing_report;
		}

        $pattern = '/\?/';

        if (preg_match($pattern, $input)) { // returns true with ? mark
            $this->thing->log( '<pre> Agent found a question mark and created a Question agent</pre>', "INFORMATION" );
            $question_thing = new Question($this->thing);
            $this->thing_report = $question_thing->thing_report;
            return $this->thing_report;

        }
        // Timecheck
        $this->thing_report = $this->timeout(15000);
        if ($this->thing_report != false) {return $this->thing_report;}


        // Now pull in the context
        // This allows us to be more focused
        // with the remaining time.

        $split_time = $this->thing->elapsed_runtime();

        $context_thing = new Context($this->thing, "extract");
        $this->context = $context_thing->context;
        $this->context_id = $context_thing->context_id;


        $this->thing->log( 'ran Context ' . number_format($this->thing->elapsed_runtime()- $split_time) . 'ms.' );



        // Timecheck
        if ($this->context != null) {
            $r = "Context is " . strtoupper($this->context);
            $r .= " " . $this->context_id . ". ";
        } else {
            $r = null;
        }


        $this->thing_report = $this->timeout(15000, $r);
        if ($this->thing_report != false) {return $this->thing_report;}

        switch (strtolower($this->context)) {
            case 'group':

                // Now if it is a head_code, it might also be a train...
                $group_thing = new Group($this->thing, 'extract');
                $this->groups= $group_thing->groups;

                if ($this->groups != null) {
                    // Group was recognized.
                    // Assign to Group manager.

                    // devstack Should check here for four letter
                    // words ie ivor dave help

                    $group_thing = new Group($this->thing);
                    $this->thing_report = $group_thing->thing_report;

                    return $this->thing_report;
                }

                //Timecheck
                $this->thing_report = $this->timeout(45000, "No matching groups found. ");
                if ($this->thing_report != false) {return $this->thing_report;}

                break;

            case 'headcode':

                // Now if it is a head_code, it might also be a train...
                //$train_thing = new Train($this->thing, $this->head_code);
                $headcode_thing = new Headcode($this->thing, 'extract');
                $this->head_codes = $headcode_thing->head_codes;

                if ($this->head_codes != null) {
                    // Headcode was recognized.
                    // Assign to Train manager.

                    $headcode_thing = new Headcode($this->thing);
                    $this->thing_report = $headcode_thing->thing_report;

                    return $this->thing_report;
                }

                //Timecheck
                $this->thing_report = $this->timeout(45000, "No matching headcodes found. ");
                if ($this->thing_report != false) {return $this->thing_report;}

                break;
            case 'train':
                // Now if it is a head_code, it might also be a train...
                $train_thing = new Train($this->thing, 'extract');
                //$headcode_thing = new Headcode($this->thing, 'extract');
                $this->headcodes = $train_thing->head_codes;

                if ($this->head_codes != null) {
                    // Headcode was recognized.
                    // Assign to Train manager.

                    $train_thing = new Train($this->thing);
                    $this->thing_report = $train_thing->thing_report;

                    return $this->thing_report;
                }

                //Timecheck
                $this->thing_report = $this->timeout(45000, "No matching train headcodes found. ");
                if ($this->thing_report != false) {return $this->thing_report;}

                break;

            case 'character':

                // Character recognition should be replaceable by alias
                // by refactoring character to use the aliasing engine.
                $character_thing = new Character($this->thing,'character');
                $this->name = $character_thing->name;

                if ($this->name != null) {
                    // Headcode was recognized.
                    // Assign to Train manager.

                    $character_thing = new Character($this->thing);
                    $this->thing_report = $character_thing->thing_report;

                    return $this->thing_report;
                }

                $this->thing_report = $this->timeout(45000, "No matching characters found. ");
                if ($this->thing_report != false) {return $this->thing_report;}


                break;


            case 'place':

                // Character recognition should be replaceable by alias
                // by refactoring character to use the aliasing engine.
                $place_thing = new Place($this->thing,'place');
                $this->place_code = $place_thing->place_code;

                if ($this->place_code != null) {
                    // Headcode was recognized.
                    // Assign to Train manager.

                    $place_thing = new Place($this->thing);
                    $this->thing_report = $place_thing->thing_report;

                    return $this->thing_report;
                }

                $this->thing_report = $this->timeout(45000, "No matching places found. ");
                if ($this->thing_report != false) {return $this->thing_report;}


                break;


            default:
                $this->thing_report = $this->timeout(45000, "No matching context found. ");
                if ($this->thing_report != false) {return $this->thing_report;}

        }

        // So if it falls through to here ... then we are really struggling.

        // This is going to be the most generic form of matching.
        // And probably thre most common...
        // It needs to be here to pick up four letter
        // aliases ie Ivor.
       $alias_thing = new Alias($this->thing,'extract');
       $this->alias = $alias_thing->alias;

       if ($this->alias != null) {
            // Alias was recognized.
           $alias_thing = new Alias($this->thing);
           $this->thing_report = $alias_thing->thing_report;

           return $this->thing_report;
       }

        //Timecheck
        $this->thing_report = $this->timeout(45000, "No matching aliases found. ");
        if ($this->thing_report != false) {return $this->thing_report;}

        $this->thing->log( 'now looking at Identity Context.', "OPTIMIZE" );

        // Is this a request for a specific named agent?
        //$this->thing->log( $input . " " . $this->from );

        if (strpos($input, 'mordok') !== false) {
            $mordok_thing = new Mordok($this->thing);
            $this->thing_report = $mordok_thing->thing_report;
            return $this->thing_report;
        }

        if ($this->from == "1327328917385978") { // Facebook Messenger Mordok
            $mordok_thing = new Mordok($this->thing);
            $this->thing_report = $mordok_thing->thing_report;
            return $this->thing_report;
        }

        // Expand out emoji early
        // devstack - replace this with a fast general character
        // character recognizer of concepts.

        // Temptation here is to create a Thing agent.  Bad idea.

        // This will see if the word is in the Chinese dictionary.
        // Because that makes it probably a Thing worth noting.

        $chinese_thing = new Chinese($this->thing);

        if ($chinese_thing->word != null) {
            // So it is something worth noting.
            $is_thing = new Is($this->thing);
            $this->thing_report = $is_thing->thing_report;
            return $this->thing_report;
        }

		$this->thing->log( '<pre> Agent "Agent" created a Redpanda agent.</pre>', "WARNING" );
		$redpanda_thing = new Redpanda($this->thing);

		$this->thing_report = $redpanda_thing->thing_report;

    	return $this->thing_report;
	}

    function warning_handler($errno, $errstr) {
        //throw new \Exception('Class not found.');

        //trigger_error("Fatal error", E_USER_ERROR);

        //echo $errno;
        //echo $errstr;
        // do something
    }
}




/*
function warning_handler($errno, $errstr) { 
    throw new Exception('Class not found.');

    //trigger_error("Fatal error", E_USER_ERROR);

    //echo $errno;
    //echo $errstr;
    // do something
}
*/

