<?php
// https://medium.com/async-php/multi-process-php-94a4e5a4be05
//use AsyncPHP\Doorman\Manager\ProcessManager;
//use AsyncPHP\Doorman\Task\ProcessCallbackTask;

//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//var_dump($argv);
//exit();

ini_set("allow_url_fopen", 1);

//require '/home/wildtay3/public_html/stackr/vendor/autoload.php';

require '/var/www/html/stackr.ca/vendor/autoload.php';

require_once '/var/www/html/stackr.ca/src/thing.php';
require_once '/var/www/html/stackr.ca/agents/agent.php';

//echo "meep";
//exit();

//if (!debug_backtrace()) {

	//public $console = true;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
//        echo "Running callagent.php directly.";

//var_dump($argv);
//exit();

//$uuid = $argv[1];
//echo $uuid;
//        $thing = new Thing($uuid);
//        $agent = new Agent($thing);

//        $t = new Callagent();
//        $t->Apply();



//} else {
//	$this->console = false;
//}

// Thread not installed on server.


class Callagent {

	public function __construct($thing, $agent_input = null) {

        $this->agent_instruction = $agent_input;

        //$this->arg = $arg;

//        $this->thing = new Thing(null);
        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->thing_report['thing'] = $this->thing->thing;


//var_dump($argv);
//exit();

        if ($agent_input == "getagent") {$this->callAgent($this->thing->uuid);}
        $this->thing_report['log'] = $this->thing->log;

	}


    public function run() {

            $this->callAgent($uuid, 'receipt');
    }


    function callAgent($uuid, $to = null){

	// dev note: rename $to to $agent_requested

	// I feel like this is hidden down here.  Perhaps that's a good thing.

	// So this is the agenthandler's first effort at call the right Agent for 
	// this Thing.

	// [and needed for Agents like tokenlimiter which limit access].

	// Get uuid out of this.  And instantiate a thing.
	//$uuid = $thingreport['thing']->uuid;

	// All agenthandler knows is that a uuid has been Red flagged and 
	// requires agent attention.

	// We need to pass the full Thing object to the agent.
	// This is good because inside the thing is the thing's array.

	    $thing = new Thing($uuid);

//        echo 'callagent (full report)/n';
//        echo "<pre>";
//        print_r($thing->thing);echo'</pre>';
//        echo "/n";


// Clearly I am still troubled by red or green.
// Red flag is what a Thing waves when it needs attention.
// Traffic light analogy is 'green to pass'.

// So either here green flagged Things pass a green traffic light.
// nrwtaylor 17 May

// Changing this from isRed to isGreen 27 Apr.  For integration testing.
// Seems to be that was what is needed.
// Ignore any green items sent to the agent handler?  Or 


// Shouldn't do this with callagent.
	    if ($thing->isGreen() == true) {
          
		    //echo 'callagent called Thing ' . $thing->uuid . 'has a Green flag.  callagent ignoring./n';
		    return;
	    }

// That shouldn't have happened.  But wanted to pick it up for in case
// it does.

// Assume all nom_to has been washed of non-stack nominal.
// dev: Use bayes to flag likelihood of non-stack nominal.

// Then we decide what agent to run, based on the nom_to.  Which is some form
// of stackr specific address.

// For testing.
// Remove in production.
//$thing->from = "redpanda.stack@gmail.com";

//var_dump($to);
//var_dump($thing->to);
	    if ($to == null) {
		    $to = $thing->to;
	    }
//echo "to". $to;

	    // Hacky here.  If there is an @ sign and it isn't @stackr. then delete
	    $arr = explode("@", $to, 2);
	    $to = $arr[0];

	    // Hexagram 51 震 zhèn Shake
	    // Shake things up.
	    // If you want to succeed stop reacting and make the necessary changes.

        //echo '<pre> agenthandler.php callAgent() $thing->thing: '; print_r($thing->thing); echo '</pre>';

	    // The right thing to do too late, is the best thing if it is the only thing.

//echo "to ". $to;

	    $agent_class_name = ucfirst($to);
	    echo "Agent name: ",$agent_class_name;

	    try {
		    include_once '/var/www/html/stackr.ca/agents/' . strtolower($agent_class_name) . '.php';
	    } catch (Exception $e) {
    		echo 'Caught exception: ',  $e->getMessage(), "\n";
	    }

	    if (class_exists($agent_class_name)) {

		    goto skip;

		    echo '<pre> Agent\'s file found: '; print_r($agent_class_name); echo '</pre>';	

		    // Hoping this does not include the names of the agent classes.

		    $declared_classes = get_declared_classes();
		    //echo '<pre> Agent\'s file matching against: '; print_r($declared_classes); echo '</pre>';

		    //Return Everything after iChing.  

		    $white_list = array();

		    $match = false;
		    foreach($declared_classes as $key=>$declared_class) {
			    if (ucfirst($declared_class) == ucfirst('iChing')) {$match = true;}
			    if (ucfirst($declared_class) == 'Monolog\Logger') {$match = false;}

			    if ($match == true) {
				    //echo '<pre> ' . $match . 'added to whitelist.</pre>';
				    $white_list[$key] = ucfirst($declared_class);

			    }
		    }

		    echo '<pre> Agent\'s file matching against generated whitelist: '; print_r($white_list); echo '</pre>';
		    $found = false;

		    foreach($white_list as $key=>$agent_name_whitelist) {
			    if (ucfirst($agent_name_whitelist) == ucfirst($agent_class_name)) {
				    echo '<pre> Agent\'s file matched against whitelist.</pre>';
				    $found = true;
				    continue;
			    }
		    }

		    if ($found == false) {
			    // Agent not found in whitelist
			    echo '<pre> Agent\'s not found in whitelist.  Re-assign to redpanda</pre>';
			    $agent_class_name = ucfirst('redpanda');
		    } else {
			    // do nothing
		    }

		    skip:

		    $agent = new $agent_class_name($thing, $this->agent_instruction);

	    } else {

		    echo '<pre> Agent\'s file not found: '; print_r($agent_class_name); echo '</pre>';	
		    require_once '/var/www/html/stackr.ca/agents/agent.php';
		    // If class doesn't exist then call standard agent.

            //        try {
            //register_shutdown_function('shutDownFunction');
            try {
                $agent = new Agent($thing, $this->agent_instruction);
            } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptions.

                require_once '/var/www/html/stackr.ca/agents/bork.php';

                $message = $ex->getMessage();
                $code = $ex->getCode();
                $file = $ex->getFile();
                $line = $ex->getLine();

                $input = $message . ' / ' . $code . ' / ' . $file . ' / ' . $line;

                $agent = new Bork($thing, 'agenthandler/' . $input );

            }

  //      } catch (Exception $e) {
  //              echo 'Caught exception: ',  $e->getMessage(), "\n";
  //      }


	    }
        // Added Mar 17 2018
        $this->thing_report = $agent->thing_report;

    }


}

//function shutDownFunction() { 
//    $error = error_get_last();
    // fatal error, E_ERROR === 1
//    if ($error['type'] === E_ERROR) { 
        //do your stuff     
//    } 
//}

//register_shutdown_function('shutDownFunction');

// Thinking here about destruction of the SQL record thing as well as the 
// instatiated class.  
// The SQL record should remain, and by tidied up by the db scavengers.  So,
// Don't do this.  Unless you won't the forget_time to be zero.

		// and then forget the thing
		//$thing->Forget();








?>
