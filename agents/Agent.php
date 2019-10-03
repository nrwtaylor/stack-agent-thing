<?php
/*
 * Agent.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

// Agent resolves message disposition

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


class Agent {

    public $input;


    /**
     *
     * @param Thing   $thing
     * @param unknown $input (optional)
     */
    function __construct(Thing $thing, $input = null) {
        // Start the timer
        $this->start_time = $thing->elapsed_runtime();
        //microtime(true);

$this->agent_input = $input;
if (is_array($input)) {$this->agent_input = $input;}
if (is_string($input)) {$this->agent_input = strtolower($input);}

//        $this->agent_input = strtolower($input);

//        $this->agent_name = 'agent';

        $this->getName();
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent_name) . '" ';
        // Given a "thing".  Instantiate a class to identify
        // and create the most appropriate agent to respond to it.

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing;

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

        $this->getMeta();

        // Tell the thing to be quiet
        if ($this->agent_input != null) {
            //            $this->thing->silenceOn();
            //            $quiet_thing = new Quiet($this->thing,"quiet on");
        }

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->sqlresponse = null;

        $this->thing->log('running on Thing ' . $this->thing->nuuid . '.');
        $this->thing->log('read "' . $this->subject . '".');

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';
        $this->agents_path = $GLOBALS['stack_path'] . 'agents/';
        $this->agents_path = $GLOBALS['stack_path'] . 'vendor/nrwtaylor/stack-agent-thing/agents/';

        $this->current_time = $this->thing->time();

        $this->verbosity = 9;

        $this->context = null;
        $this->response = "";


if (isset($thing->container['api']['agent'])) {

if ($thing->container['api']['agent'] == "off") {return;}


}


        // First things first... see if Mordok is on.
        /* Think about how this should work and the user UX/UI
            $mordok_agent = new Mordok($this->thing);

            if ($mordok_agent->state == "on") {

        $thing_report = $this->readSubject();

        $this->respond();

} else {
// Don't

}
*/
        $this->init();

//        $this->getName();
//        $this->agent_prefix = 'Agent "' . ucfirst($this->agent_name) . '" ';


        $this->get();
        $this->read();
        $this->run();
        $this->make();


//set_error_handler(array($this,'my_exception_handler'));
try {
//            throw new \OverflowException('Insufficient space in DB record ' . $this->uuid . ".");

        $this->set();
}
catch (\OverflowException $t)
{
$this->response = "Stack variable store is full. Variables not saved. Text FORGET ALL.";
$this->thing_report['sms'] = "STACK | " . $this->response;

    $this->thing->log("caught overflow exception.");
   // Executed only in PHP 7, will not match in PHP 5
}

catch (\Throwable $t)
{
//$this->response = "STACK | Variable store is full. Text FORGET ALL.";
//$this->thing_report['sms'] = "STACK | Variable store is full. Text FORGET ALL.";

$this->thing->log("caught throwable.");
   // Executed only in PHP 7, will not match in PHP 5
}
catch (\Exception $e)
{

   $this->thing->log("caught exception");
   // Executed only in PHP 5, will not be reached in PHP 7
}

//restore_error_handler();
        if (($this->agent_input == null) or ($this->agent_input == "")) {
            $this->respond();
        }

        if (!isset($this->response)) {$this->response = "No response found.";}
        $this->thing_report['response'] = $this->response;

        $this->thing->log('ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;
    }


    /**
     *
     */
    public function init() {
    }


    /**
     *
     */
    public function get() {
    }


    /**
     *
     */
    public function set() {
    }


    /**
     *
     */
    public function make() {
        $this->makeResponse();
        $this->makeMessage();
        $this->makeImage();
        $this->makePNG();
        $this->makeSMS();
        $this->makeWeb();
        $this->makeSnippet();

$this->makeEmail();
//$this->makeMessage();
$this->makeTXT();

        $this->makePDF();
    }


    /**
     *
     */
    public function run() {
    }


    /**
     *
     * @return unknown
     */
    public function kill() {
        // No messing about.
        return $this->thing->Forget();
    }


    /**
     *
     */
    public function test() {
        // See if it can run an agent request
        $agent_thing = new Agent($this->thing, "agent");
        // No result for now
        $this->test = null;
    }


    /**
     *
     */
    public function getName() {
        $this->agent_name =   explode( "\\", strtolower(get_class($this)) )[2] ;

    }


    /**
     *
     * @param unknown $thing (optional)
     */
    public function getMeta($thing = null) {
        if ($thing == null) {$thing = $this->thing;}

        // Non-nominal
        $this->uuid = $thing->uuid;
        if (!isset($thing->to)) {$this->to = null;} else {$this->to = $thing->to;}

        // Potentially nominal
        if (!isset($thing->subject)) {$this->subject = null;} else {$this->subject = $thing->subject;}

        // Treat as nomina
        if (!isset($thing->from)) {$this->from = null;} else {$this->from = $thing->from;}
    }


    /**
     *
     * @return unknown
     */
    function getLink() {

        $block_things = array();
        // See if a block record exists.
        $findagent_thing = new FindAgent($this->thing, 'thing');

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        //$this->thing->log('Agent "Block" found ' . count($findagent_thing->thing_report$

        $this->max_index =0;

        $match = 0;

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {

            //       $this->thing->log($block_thing['task'] . " " . $block_thing['nom_to'] . " " .$

            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                if ($match == 2) {break;}
            }
        }


        $previous_thing = new Thing($block_thing['uuid']);
        $this->prior_thing = $previous_thing;
        if (!isset($previous_thing->json->array_data['message']['agent'])) {
            $this->prior_agent = "help";
        } else {
            $this->prior_agent = $previous_thing->json->array_data['message']['agent'];
        }

        return $this->link_uuid;
    }


    /**
     *
     * @return unknown
     */
    function getTask() {

        $block_things = array();
        // See if a stack record exists.
        $findagent_thing = new Findagent($this->thing, 'thing');

        $this->max_index =0;
        $match = 0;
        $link_uuids = array();

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {

            $this->thing->log($block_thing['task'] . " " . $block_thing['nom_to'] . " " . $block_thing['nom_from']);
            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_task = $block_thing['task'];
                $link_tasks[] = $block_thing['task'];
                // if ($match == 2) {break;}
                // Get upto 10 matches
                if ($match == 10) {break;}
            }
        }
        $this->prior_agent = "web";
        foreach ($link_tasks as $key=>$link_task) {

            if (isset($link_task)) {

                if (in_array(strtolower($link_task), array('web', 'pdf', 'txt', 'log', 'php', 'syllables', 'brilltagger'))) {
                    continue;
                }

                $this->link_task = $link_task;
                break;
            }
        }

        $this->web_exists = true;
        if (!isset($agent_thing->thing_report['web'] )) {$this->web_exists = false;}

        return $this->link_task;
    }


    /**
     *
     * @param unknown $variable_name (optional)
     * @param unknown $variable      (optional)
     * @return unknown
     */
    function getVariable($variable_name = null, $variable = null) {

        // This function does a minor kind of magic
        // to resolve between $variable, $this->variable,
        // and $this->default_variable.

        if ($variable != null) {
            // Local variable found.
            // Local variable takes precedence.
            return $variable;
        }

        if (isset($this->$variable_name)) {
            // Class variable found.
            // Class variable follows in precedence.
            return $this->$variable_name;
        }

        // Neither a local or class variable was found.
        // So see if the default variable is set.
        if (isset( $this->{"default_" . $variable_name} )) {

            // Default variable was found.
            // Default variable follows in precedence.
            return $this->{"default_" . $variable_name};
        }

        // Return false ie (false/null) when variable
        // setting is found.
        return false;

    }



    /**
     *
     */
    public function respond() {
        $this->respondResponse();
        $this->thing->flagGreen();
//        return $this->thing_report;
    }


    /**
     *
     */
    public function respondResponse() {
        $agent_flag = true;
if ($this->agent_name == "agent") {return;}


        if ($agent_flag == true) {
            //        if ($this->agent_input == null) {
            //          $this->respond();
            //      }


            if (!isset($this->thing_report['sms'])) {
                $this->thing_report['sms'] = "AGENT | Standby.";
            }

            $this->thing_report['message'] = $this->thing_report['sms'];

            if (($this->agent_input == null) or ($this->agent_input == "")) { 
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
            }
        }

    }


    /**
     *
     */
    private function makeResponse() {

        if (isset($this->response)) {return;}

        //$this->response = "Standby.";
        $this->response = "";

    }


    /**
     *
     */
    public function makeWeb() {
    }

    public function makePDF() {
    }

    public function makeImage() {
    }

    public function makeSnippet() {
       $this->thing_report['snippet'] = "<b>Empty.</b>";
    }


    public function makeTXT() {
//if (!isset($this->thing_report['sms'])) {$this->makeSMS();}
//        $this->thing_report['txt'] = $this->thing_report['sms'];
    }

    public function makeMessage() {
//if (!isset($this->thing_report['sms'])) {$this->makeSMS();}

//        $this->thing_report['message'] = $this->thing_report['sms'];
    }

public function makeEmail() {



}



    /**
     *
     */
    public function makeSMS() {
        //$this->makeResponse();
        // So this is the response if nothing else has responded.

        if (!isset($this->thing_report['sms'])) {
            if (isset($this->sms_message)) {$this->thing_report['sms'] = $this->sms_message;}

            if (!isset($this->thing_report['sms'])) {
                $sms = strtoupper($this->agent_name);

                if ($this->response == "") {$sms .= " >";} else {
                    $sms .= " | " . $this->response;
                }


                $this->thing_report['sms'] = $sms;
                $this->thing_report['sms'] = null;

            }

            if (!isset($this->sms_message)) {
                $this->sms_message = $this->thing_report['sms'];
            }


        }

    }


    /**
     *
     */
    public function getPrior() {
        // See if the previous subject line is relevant
        $this->thing->db->setUser($this->from);
        $prior_thing_report = $this->thing->db->priorGet();
        $this->prior_thing = $prior_thing_report;

        $task = $prior_thing_report['thing']->task ;
        $nom_to = $prior_thing_report['thing']->nom_to ;

        $temp_haystack = $nom_to . ' ' . $task;
    }


    /**
     *
     * @param unknown $input
     * @param unknown $n     (optional)
     * @return unknown
     */
    private function getNgrams($input, $n = 3) {
        $words = explode(' ', $input);
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


    /**
     *
     * @param unknown $time_limit (optional)
     * @param unknown $input      (optional)
     * @return unknown
     */
    function timeout($time_limit = null, $input = null) {
        if ($time_limit == null) {
            $time_limit = 10000;
        }

        if ($input == null) {
            $input = "No matching agent found. ";
        }

        // Timecheck

        switch (strtolower($this->context)) {
        case 'place':
            $array = array('place', 'mornington crescent');
            break;
        case 'group':
            $array = array('group', 'say hello', 'listen', 'join');
            break;
        case 'train':
            $array = array('train', 'run train', 'red', 'green', 'flag');
            break;
        case 'headcode':
            $array = array('headcode');
            break;
        case 'identity':
            $array = array('headcode', 'mordok', 'jarvis', 'watson');
            break;
        default:
            $array = array('link', 'roll d20', 'roll', 'iching', 'bible', 'wave', 'eightball', 'read', 'group', 'flag', 'tally', 'emoji', 'red', 'green', 'balance', 'age', 'mordok', 'pain', 'receipt', 'key', 'uuid', 'remember', 'reminder', 'watson', 'jarvis', 'whatis', 'privacy', '?');
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


    /**
     *
     * @param unknown $text (optional)
     */
    private function read($text = null) {

        if ($text == null) {$text = $this->subject;} // Always.
        if (isset($this->filtered_input)) {$text = $this->filtered_input;}
        if (isset($this->translated_input)) {$text = $this->translated_input;}

        switch (true) {
        case (isset($this->input)) :
            break;

        case ($this->agent_input == null):
        case (strtolower($this->agent_input) == "extract"):
        case (strtolower($this->agent_input) == strtolower($this->agent_name)) :
            $this->input = strtolower($text);
            break;
        default:
            $this->input = strtolower($this->agent_input);
        }



        $whatIWant = $this->input;
        if (($pos = strpos(strtolower($this->input), "@ednabot")) !== FALSE) {
            $whatIWant = substr(strtolower($this->input), $pos+strlen("@ednabot"));
        } elseif (($pos = strpos(strtolower($this->input), "@ednabot")) !== FALSE) {
            $whatIWant = substr(strtolower($this->input), $pos+strlen("@ednabot"));
        }
        $this->input = trim($whatIWant);
        $this->readSubject();
    }


    /**
     *
     * @param unknown $agent_class_name (optional)
     * @param unknown $agent_input      (optional)
     * @return unknown
     */
    public function getAgent($agent_class_name = null, $agent_input = null) {

        try {
            $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;

            $this->thing->log( 'trying Agent "' . $agent_class_name . '".', "INFORMATION" );

            if (isset($this->input)) {$this->thing->subject = $this->input;}

            $agent = new $agent_namespace_name($this->thing);

            // If the agent returns true it states it's response is not to be used.
            if ((isset($agent->response)) and ($agent->response === true)) {
                throw new Exception("Flagged true.");
            }
            $this->thing_report = $agent->thing_report;
            $this->agent = $agent;

            //        } catch (Throwable $ex) { // Error is the base class for all internal PHP error exceptions.
        } catch (\Throwable $t) {
            $this->thing->log( 'caught throwable.' , "WARNING" );

        } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptions.
            $this->thing->log( 'caught error. Could not load "' . $agent_class_name . '".' , "WARNING" );
            $message = $ex->getMessage();
            // $code = $ex->getCode();
            $file = $ex->getFile();
            $line = $ex->getLine();

            $input = $message . '  ' . $file . ' line:' . $line;
            $this->thing->log($input , "WARNING" );

            // This is an error in the Place, so Bork and move onto the next context.
            // $bork_agent = new Bork($this->thing, $input);
            //continue;
            return false;
        }
        return true;

    }


    /**
     *
     * @param unknown $agent_class_name (optional)
     * @return unknown
     */
    public function isAgent($agent_class_name = null) {
        if ($agent_class_name == null) {
            $agent_class_name = strtolower($this->agent_name);
        }
        try {

            $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;

            $this->thing->log( 'trying Agent "' . $agent_class_name . '".', "INFORMATION" );
            $agent = new $agent_namespace_name($this->thing);

            return true;

            // If the agent returns true it states it's response is not to be used.
            if ((isset($agent->response)) and ($agent->response === true)) {
                throw new Exception("Flagged true.");
            }

            $this->thing_report = $agent->thing_report;

            $this->agent = $agent;
            return true;

} catch (\Throwable $t) {
            $this->thing->log( 'caught throwable.' , "WARNING" );

        } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptions.
            $this->thing->log( 'caught error. Could not load "' . $agent_class_name . '".' , "WARNING" );
            $message = $ex->getMessage();
            // $code = $ex->getCode();
            $file = $ex->getFile();
            $line = $ex->getLine();

//            $input = $message . '  ' . $file . ' line:' . $line;
            $this->thing->log($input , "WARNING" );

            // This is an error in the Place, so Bork and move onto the next context.
            // $bork_agent = new Bork($this->thing, $input);
            //continue;
            return false;
        }

    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {
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

        // Basically if the agent input directly matches an agent name
        // Then run it.

        // So look hear to generalize that.
        //$agents = new Agents($this->thing, "agents");
        //foreach ($agents->agents as $agent_class_name=>$agent_name) {
        $text = urldecode($this->agent_input);
        $text = strtolower($text);
        //if ( $text == $this->agent_input) {
        //}

        $arr = explode(' ', trim($text));

        $arr = explode('\%20', trim($text));

        $agents = array();

        $onegrams = $this->getNgrams($text, $n = 1);
        $bigrams = $this->getNgrams($text, $n = 2);
        $trigrams = $this->getNgrams($text, $n = 3);

        $arr = array_merge($arr, $onegrams);
        $arr = array_merge($arr, $bigrams);
        $arr = array_merge($arr, $trigrams);

        usort($arr, function($a, $b) {
                return strlen($b) <=> strlen($a);
            });
        $matches = array();

        foreach ($arr as $i=>$ngram) {
            $ngram = ucfirst($ngram);
            if ($ngram == "Thing") {
                continue;
            }

            // Exclude incoming web links asking for buttons
            if ($ngram == "Button") {
                continue;
            }

            if ($ngram == "Agent") {
                continue;
            }

            if ($ngram == "") {
                continue;
            }


            if ($this->isAgent($ngram)) {
                $matches[] = $ngram;
                //                return $this->thing_report;
            }
        }
        if (count($matches) == 1) {
            $this->getAgent($matches[0]);

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

            $this->thing->log( 'Agent "Agent" created a Similar agent looking for incoming message repeats.', "DEBUG" );

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
                        . number_format(($burst_age_limit - $burst_age)/ 60) ." minutes");

                } elseif ($this->verbosity >=7) {
                    $t = new Hashmessage($this->thing, "MESSAGE | The stack is handling a burst of similar requests. | Wait "
                        . number_format(($burst_age_limit - $burst_age)/ 60) ." minutes and then retry.");

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
        // expand out chinese characters
        // Added to stack 29 July 2019 NRW Taylor
        $chinese_thing = new Chinese($this->thing, $input);
        $this->thing_report = $chinese_thing->thing_report;
        if ((isset($chinese_thing->chineses)) and isset($chinese_thing->translated_input)) {
            $input = $chinese_thing->translated_input;
        }

        // And then compress
        // devstack - replace this with a fast general character
        // character recognizer of concepts.
        $compression_thing = new Compression($this->thing, $input);
        if (isset($compression_thing->filtered_input)) {
            // Compressions found.
            $input = $compression_thing->filtered_input;
        }

        $input = trim($input);
        $this->input = $input;

        // Check if it is a command (starts with s slash)
        if ( strtolower( substr($input, 0, 2)) != "s/") {

            // Okay here check for input

            if ( strtolower($this->subject) == "break" ) {

                $input_thing = new Input($this->thing, "break");
                $this->thing_report = $input_thing->thing_report;
                return $this->thing_report;

            }

            // Where is input routed to?
            $input_thing = new Input($this->thing, "input");
            if (($input_thing->input_agent != null) and ($input_thing->input_agent != $input)) {
            }

        }


        $this->thing->log('processed haystack "' .  $input . '".', "DEBUG");

        // Now pick up obvious cases where the keywords are embedded
        // in the $input string.

        if (strtolower($input) == 'agent') {
            $this->thing->log( 'created a Usermanager agent.' );
            //            $usermanager_thing = new Usermanager($this->thing);
            //$link = $this->web_prefix . "thing/" . $this->uuid . "/agent";
            $this->getLink();
            //$link = $this->link_uuid;
            $link = $this->web_prefix . "agent/" . $this->link_uuid . "/" . strtolower($this->prior_agent);

            $this->thing_report['sms'] = "AGENT | " . $link . " Made an agent link.";
            return $this->thing_report;
        }

        $this->thing->log('<pre> Agent "Agent" looking for optin/optout.</pre>');
        //    $usermanager_thing = new Usermanager($this->thing,'usermanager');

        if (strpos($input, 'optin') !== false) {
            $this->thing->log( 'created a Usermanager agent.' );
            $usermanager_thing = new Usermanager($this->thing);
            $this->thing_report = $usermanager_thing->thing_report;
            return $this->thing_report;
        }

        if (strpos($input, 'optout') !== false) {
            $this->thing->log( 'created a Usermanager agent.' );
            $usermanager_thing = new Optout($this->thing);
            $this->thing_report = $usermanager_thing->thing_report;
            return $this->thing_report;
        }

        if (strpos($input, 'opt-in') !== false) {
            $this->thing->log( 'Agent created a Usermanager agent.' );
            $usermanager_thing = new Optin($this->thing);
            $this->thing_report = $usermanager_thing->thing_report;
            return $this->thing_report;
        }

        if (strpos($input, 'opt-out') !== false) {
            $this->thing->log( 'Agent created a Usermanager agent.' );
            $usermanager_thing = new Optout($this->thing);
            $this->thing_report = $usermanager_thing->thing_report;
            return $this->thing_report;
        }

        // Then look for messages sent to UUIDS
        $this->thing->log('looking for UUID in address.', 'INFORMATION');

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
        // Remove references to named chatbot agents
        //        $chatbot = new Chatbot($this->thing,"chatbot");
        //        $input =  $chatbot->filtered_input;

        $headcode = new Headcode($this->thing, "extract");
        $headcode->extractHeadcodes($input);


        if ($headcode->response === true) {
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
        $arr = explode(' ', trim($input));

        $agents = array();

        $bigrams = $this->getNgrams($input, $n = 2);
        $trigrams = $this->getNgrams($input, $n = 3);

        $arr = array_merge($arr, $bigrams);
        $arr = array_merge($arr, $trigrams);
        // Added this March 6, 2018.  Testing.
        if ($this->agent_input == null) {
            $arr[] = $this->to;
        } else {
            $arr = explode(' ' , $this->agent_input);
        }
        set_error_handler(array($this, 'warning_handler'), E_WARNING);
        //set_error_handler("warning_handler", E_WARNING);
        $this->thing->log('looking for keyword matches with available agents.', "INFORMATION");

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

            // 2nd way
            $agent_class_name = strtolower($keyword);

            // Can probably do this quickly by loading path list into a variable
            // and looping, or a direct namespace check.
            $filename = $this->agents_path .  $agent_class_name . ".php";
            if (file_exists($filename)) {
                $agents[] = $agent_class_name;
            }

            // 3rd way
            $agent_class_name = strtoupper($keyword);

            // Can probably do this quickly by loading path list into a variable
            // and looping, or a direct namespace check.
            $filename = $this->agents_path .  $agent_class_name . ".php";
            if (file_exists($filename)) {
                $agents[] = $agent_class_name;
            }
        }

        restore_error_handler();

        // What effect would this have?
        //$agents = array_reverse($agents);

        $this->input = $input;
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

            if ($this->getAgent($agent_class_name)) {
                //            if ($this->getAgent($agent_class_name, $input)) {
                return $this->thing_report;
            }
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

        $this->thing->log( 'now looking at Transit Context.' );

        $transit_thing = new Transit($this->thing, "extract");
        $this->thing_report = $transit_thing->thing_report;

        if ((isset($transit_thing->stop)) and ($transit_thing->stop != false) ) {

            $translink_thing = new Translink($this->thing);
            $this->thing_report = $translink_thing->thing_report;
            return $this->thing_report;

        }

        $this->thing->log( 'now looking at Place Context.' );
        //$place_thing = new Place($this->thing, "extract");
        $place_thing = new Place($this->thing, "place");
        //        $this->thing_report = $place_thing->thing_report;

        if (!$place_thing->isPlace($input)) {
            //        if (!$place_thing->isPlace($this->subject)) {
            //if (($place_thing->place_code == null) and ($place_thing->place_name == null) ) {
        } else {
            // place found
            $place_thing = new Place($this->thing);
            $this->thing_report = $place_thing->thing_report;
            return $this->thing_report;
        }



        // Here are some other places

        $frequency_thing = new Frequency($this->thing, "extract");
        $this->thing_report = $frequency_thing->thing_report;

        if ($frequency_thing->hasFrequency($input)) {
            //            $ars_thing = new Amateurradioservice($this->thing, $input);
            //        $frequency_thing = new Frequency($this->thing, "extract");
            $frequency_thing = new Frequency($this->thing);

            //            $ars_thing = new Amateurradioservice($this->thing);
            $this->thing_report = $frequency_thing->thing_report;
            return $this->thing_report;

        }

        $repeater_thing = new Repeater($this->thing, "extract");
        $this->thing_report = $repeater_thing->thing_report;

        if ($repeater_thing->hasRepeater($input)) {

            $ars_thing = new Amateurradioservice($this->thing, $input);
            if ($ars_thing->response == false) {

                $ars_thing = new Callsign($this->thing);
                $this->thing_report = $ars_thing->thing_report;
                return $this->thing_report;

            } else {
                $ars_thing = new Amateurradioservice($this->thing);
                $this->thing_report = $ars_thing->thing_report;
                return $this->thing_report;
            }
        }



        $this->thing->log( 'now looking at Nest Context.  Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        if (strtolower($this->from) != "null@stackr.ca") {

            $entity_list = array("Crow", "Wumpus", "Ant");
            //$agent_name = "entity";
            foreach ($entity_list as $key=>$entity_name) {

                $findagent_agent = new FindAgent($this->thing, $entity_name);
                $things = $findagent_agent->thing_report['things'];
                $uuid = ($things[0]['uuid']);

                $thing = new Thing($uuid);

                if ($thing == false) {continue;}
                if (!isset($thing->account)) {continue;}
                if (!isset($thing->account['stack'])) {continue;}

                $variables = $thing->account['stack']->json->array_data;

                // Check
                if (!isset($variables[strtolower($entity_name)])) {continue;}

                $last_heard[strtolower($entity_name)] = strtotime( $variables[strtolower($entity_name)]['refreshed_at']);


                if (!isset($last_heard['entity'])) {
                    $last_heard['entity'] = $last_heard[strtolower($entity_name)];
                    $agent_name = $entity_name;
                }

                if ($last_heard['entity'] < $last_heard[strtolower($entity_name)]) {
                    $last_heard['entity'] = $last_heard[strtolower($entity_name)];
                    $agent_name = $entity_name;
                }
            }


            if (!isset($agent_name)) {$agent_name = "Ant";}



            $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'. $agent_name;

            if (strpos($input, 'nest maintenance') !== false) {
                $ant_thing = new $agent_namespace_name($this->thing);
                $this->thing_report = $ant_thing->thing_report;
                return $this->thing_report;
            }

            if (strpos($input, 'patrolling') !== false) {
                $ant_thing = new $agent_namespace_name($this->thing);
                $this->thing_report = $ant_thing->thing_report;
                return $this->thing_report;
            }

            if (strpos($input, 'foraging') !== false) {
                $ant_thing = new $agent_namespace_name($this->thing);
                $this->thing_report = $ant_thing->thing_report;
                return $this->thing_report;
            }

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
            $character_thing = new Character($this->thing, 'character');
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
            $place_thing = new Place($this->thing, 'place');
            $this->place_code = $place_thing->place_code;

            if ($this->place_code != null) {
                // Headcode was recognized.
                // Assign to Train manager.

                ///                    $place_thing = new Place($this->thing);
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
        $alias_thing = new Alias($this->thing, 'extract');
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

        if ((isset($chinese_thing->chineses)) and ($chinese_thing->chineses != array())) {
            $c = new Chinese($this->thing, "chinese");
            $this->thing_report = $c->thing_report;
            //            $this->thing_report['sms'] = "AGENT | " . "Heard " . $input .".";
            return $this->thing_report;
            //exit();

        }

        return $this->thing_report;

        if ((isset($chinese_thing->chineses)) or (isset($emoji_thing->emojis))) {
            $this->thing_report['sms'] = "AGENT | " . "Heard " . $input .".";
            return $this->thing_report;
        }


        // If a chatbot name is seen, respond.
        //        if ((is_array($chatbot->chatbot_names)) and (count($chatbot->chatbot_names) > 0)) {
        //            $this->thing_report = $chatbot->thing_report;
        //            return $this->thing_report;
        //        }

        $this->thing->log( '<pre> Agent "Agent" created a Redpanda agent.</pre>', "WARNING" );
        $redpanda_thing = new Redpanda($this->thing);

        $this->thing_report = $redpanda_thing->thing_report;

        return $this->thing_report;
    }

    function filterAgent($text = null) {

//$input = strtolower($this->subject);
$input = $this->input;
if ($text != null) {$input = $text;}

            $strip_words = array(
                $this->agent_name, 
                strtolower($this->agent_name),
                ucwords($this->agent_name),
                strtoupper($this->agent_name)
            );
            foreach ($strip_words as $i=>$strip_word) {
            $strip_words[] = str_replace(" ", "", $strip_word);
            }

                foreach($strip_words as $i=>$strip_word){

//                    $strip_word = $strip_word['words'];

                    $whatIWant = $input;
                    if (($pos = strpos(strtolower($input), $strip_word. " is")) !== FALSE) {
                        $whatIWant = substr(strtolower($input), $pos+strlen($strip_word . " is"));
                    } elseif (($pos = strpos(strtolower($input), $strip_word)) !== FALSE) {
                        $whatIWant = substr(strtolower($input), $pos+strlen($strip_word));
                    }

                    $input = $whatIWant;
                }
            $input = trim($input);

$this->input = $input;
return $input;
    }


    /**
     *
     */
    public function makePNG() {
/*
        $this->html_image = null;
        $this->image = null;
        $this->PNG = null;
        $this->thing_report['png'] = null;



        if (!isset($this->image)) {return;}
try {
        $agent = new Png($this->thing, "png");
//try{ 
//        $this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;

        //$this->thing_report['png'] = $agent->PNG;
        $this->thing_report['png'] = $agent->image_string;
} catch (\Throwable $t) {

$this->thing_report['png'] = null; 

}
*/
    }


    /**
     *
     * @param unknown $errno
     * @param unknown $errstr
     */
    function warning_handler($errno, $errstr) {
        //throw new \Exception('Class not found.');
        //trigger_error("Fatal error", E_USER_ERROR);
        $this->thing->log( $errno );
        $this->thing->log( $errstr );

        // do something

    }

function my_exception_handler($e) {
$this->thing_report['sms'] = "Test";
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
restore_exception_handler();
$this->thing->log( "fatal exception" );
//$this->thing_report['sms'] = "Merp.";
$this->thing->log($e);
    // do some erorr handling here, such as logging, emailing errors
    // to the webmaster, showing the user an error page etc
}


}
