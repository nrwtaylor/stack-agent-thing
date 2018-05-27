<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Nominal 
{

    // This is a Nominal.  You will probably want to read up about
    // the locomotive Nominals used by British Rail.
    //
    // A Nominal takes the form (or did in the 1960s),
    // of NANN.  Where N is a digit from 0-9, and A is an uppercase character from A-Z.
    //
    // This implementation is recognizes lowercase and uppercase characters as the same.

    // The Nominal is used by the Train agent to create the proto-train.

    // A Nominal must have a route. Route is a text string.  Examples of route are:
    //  Gilmore > Hastings > Place
    //  >> Gilmore >>
    //  > Hastings

    // A Nominal may have a consist. (Z - indicates train may fill consist. 
    // X - indicates train should specify the consist. (devstack: "Input" agent)
    // NnXZ is therefore a valid consist. As is "X" or "Z".  
    // A consist must always resolve to a locomotive.  Specified as uppercase letter.
    // The locomotive closest to the first character is the engine.  And gives 
    // commands to following locomotives to follow.

    // This is the Nominal manager.  This person is pretty special.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) {

        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

$this->agent_prefix = 'Agent "Nominal" ';

 $this->node_list = array("start"=>array("stop 1"=>array("stop 2","stop 1"),"stop 3"),"stop 3");
$this->thing->choice->load('Nominal');

        $this->keywords = array('next', 'accept', 'clear', 'drop','add','new');




//                'Nominal' => array('default run_time'=>'105',
//                                'negative_time'=>'yes'),


                //$this->default_run_time = $this->thing->container['api']['Nominal']['default run_time'];
                //$this->negative_time = $this->thing->container['api']['Nominal']['negative_time'];

        // You will probably see these a lot.
        // Unless you learn Nominals after typing SYNTAX.


        $this->current_time = $this->thing->json->time();

        // Loads in Nominal variables.
        // This will attempt to find the latest head_code
//        $this->get(); // Updates $this->elapsed_time as well as pulling in the current Nominal


		// So created a token_generated_time field.
        //        $this->thing->json->setField("variables");
        //        $this->thing->json->writeVariable( array("stopwatch", "request_at"), $this->thing->json->time() );

        //$this->thing->json->time()


		$this->test= "Development code"; // Always iterative.

        // Non-nominal
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;

        // Potentially nominal
        $this->subject = $thing->subject;

        // Nominal
        $this->from = $thing->from;
        $this->subject = $thing->subject;


        // Agent variables
        $this->sqlresponse = null; // True - error. (Null or False) - no response. Text - response


		$this->thing->log('<pre> Agent "Nominal" running on Thing '. $this->thing->nuuid . '.</pre>');
		$this->thing->log('<pre> Agent "Nominal" received this Thing "'.  $this->subject . '".</pre>');


        // Stuff here which might be handy for devstack

	// Read the elapsed time.  Or start.

 //               $this->current_time = $this->thing->json->time();

//	echo $this->read(); // Updates $this->elapsed_time;

		// Read the subject as passed to this class.

//	echo '<pre> Agent "Stopwatch" start state is ';
/*
	$this->state = $thing->choice->load('stopwatch'); //this might cause problems
	//echo $this->thing->getState('usermanager');
	echo $this->state;
	echo'"</pre>';
*/

		//$balance = array('amount'=>0, 'attribute'=>'transferable', 'unit'=>'tokens');
       		//$t = $this->thing->newAccount($this->uuid, 'token', $balance); //This might be a problem

		//$this->thing->account['token']->Credit(1);




		$this->readSubject();

		$this->respond();



		$this->thing->log('<pre> Agent "Nominal" completed</pre>');

        $this->thing_report['log'] = $this->thing->log;



		return;

		}





    function set()
    {

        // A Nominal has some remaining amount of resource and 
        // an indication where to start.


        // This makes sure that
        if (!isset($this->nominal_thing)) {
            $this->nominal_thing = $this->thing;
        }



        $this->nominal_thing->json->setField("variables");
        $this->nominal_thing->json->writeVariable( array("nominal", "state"), $this->state );

        $this->nominal_thing->json->writeVariable( array("nominal", "refreshed_at"), $this->current_time );

//if (!isset($this->state)) {
//        $this->state = "X";
//}

        $this->Nominal_thing->choice->save('nominal', $this->state);


        return;
    }


    function getVariable($variable_name = null, $variable = null) {

        if ($variable != null) {
            //echo "Local variable found";
            // Local variable takes precedence.
            return $variable;
        }

        if (isset($this->$variable_name)) {
            //echo "Class variable found";
          // Default variable is set.
            return $this->$variable_name;
        }


        // Neither a local or class variable found
        // See if the default variable is set.
        if (isset( $this->{"default_" . $variable_name} )) {

            //echo "Default variable found";
            // Default variable is set.
            return $this->{"default_" . $variable_name};
        }

        // Return no variable found
        return false;
    }

    function get($variable = null)
    {

        // Loads current Nominal into $this->Nominal_thing

        $match = false;

        $variable = $this->getVariable('nominal',$variable);


        $Nominal_things = array();
        // See if a Nominal record exists.
        $findagent_thing = new FindAgent($this->thing, 'nominal');

        // This pulls up a list of other Nominal Things.
        // We need the newest Nominal as that is most likely to be relevant to
        // what we are doing.

        $this->thing->log('Agent "Nominal" found ' . count($findagent_thing->thing_report['things']) ." Nominal Things." );

        $this->max_index =0;
        $this->current_variable = null;


        // Set the Nominal thing as the current latest.
        // Not working.
            if (!isset($this->nominal_thing)) {

                $nominal_things = $findagent_thing->thing_report['things'];
                $nominal_thing = $nominal_things[0];
                $thing = new Thing($nominal_thing['uuid']);
                $latest_variable = $thing->json->readVariable( array("nominal", "state") );


                //$this->Nominal_thing = $thing;
                //$this->current_head_code = $thing->head_code;
            }


            if ( strtolower($variable) == strtolower($this->default_variable) ) { 
                // This means we are being asked to pull the default head code
                // which should be the latest if it exists.
                if (isset($latest_variable)) {
                    $variable = $latest_variable;
                }
                // Otherwise drop through with default head code loaded in construct

            }






        foreach (array_reverse($findagent_thing->thing_report['things']) as $nominal_thing) {
//        foreach ($findagent_thing->thing_report['things'] as $Nominal_thing) {

            $thing = new Thing($nominal_thing['uuid']);

            $thing->json->setField("variables");

            // Load requird val
            $thing->index = $thing->json->readVariable( array("nominal", "index"))  ;
            $thing->state = $thing->json->readVariable( array("nominal", "state") );

            $thing->refreshed_at = $thing->json->readVariable( array("Nominal", "refreshed_at"))  ;


/*
            if (!isset($this->Nominal_thing)) {
                $this->Nominal_thing = $thing;
                $this->current_head_code = $thing->head_code;
            }
*/


            if ($thing->index > $this->max_index) {$this->max_index = $thing->index;}



            // If the search is for the default Nominal of 0Z10...
            // then pull up the current Nominal.  Anything
            // is better than 0Z10.
/*
            if ( strtolower($head_code) == strtolower($this->default_head_code) ) { 
                // This means the default head code is still in the latest
                // 99 (?) top Nominals
                $match = true;
                $Nominal_thing = $thing;  
                break; //Take first matching Nominal.

            }
*/


            // If the input Nominal matches...
            if ( strtolower($variable) == strtolower($thing->variable) ) { 

 $this->thing->log( 'Agent "Nominal" found ' . $thing->variable . ' in existing Nominal #' . $thing->index . '.');
                //$this->Nominal_thing->flagRed();
$match = true;  
//$Nominal_thing = $thing;
                break; //Take first matching Nominal.

            } else {

 $this->thing->log( 'Nominal #' . $thing->index . ' (' . $thing->variable. ").");
 //              echo "green - no existing blcok found in the db";             
                //$this->Nominal_thing->flagGreen();
            }

        }




echo "This current head_code" . $this->current_variable . ".<br>";


        // Set-up empty Nominal variables.
        $this->flagposts = array();
        $this->trains = array();
        $this->bells = array();

        // If it drops through as Green, then no Nominals matched the current time.
        if ($match == false) {
            // No valid Nominal found, so make a Nominal record in current Thing
            // and set flag to Green ie accepting trains.

            $this->Nominal_thing = $this->thing;

            $this->index = 0;

            $this->start_at = $this->current_time;
            $this->quantity = 22;
            $this->available = 22;



            $this->thing->log('Agent "Nominal" did not find a Nominal for ' . $variable . "." );

            // So if a Nominal was not found we need to make one.
            // This will start creating the identities head code space.

            // It will start with 0Z10.
            // 0. light engine with or without break vans.
            // Z. Always has been a special.
            // 10. Because starting at the beginning is probably a mistake. 
            // if you need 0Z00 ... you really need it.

            $recurse = true;

            //if (($recurse != false) and (strtolower($head_code) == "0z10")) {
            if ($recurse != false) {
                $this->variable = $variable;

                $this->makeNominal();

            }

            //$this->makeNominal($this->current_time, "x");

        } else {

            $this->thing->log($this->agent_prefix . "found a matching Nominal.");

            // Red Nominal Thing - There is a current operating Nominal on the stack.
            // Load the Nominal details into this Thing.

            $this->useNominal($thing);
        }

        //$this->getAvailable();
        //$this->getEndat();

/* Useful to develop train.
            $this->Nominal_thing->json->setField("associations");
            $this->associations = $this->Nominal_thing->json->readVariable( array("agent") );

            foreach ($this->associations as $association_uuid) {

                $association_thing = new Thing($association_uuid);

                $association_thing->json->setField("variables");
                $this->flagposts[] = $association_thing->json->readVariable( array("flagpost") );

                $association_thing->json->setField("variables");
                $this->trains[] = $association_thing->json->readVariable( array("train") );

                $association_thing->json->setField("variables");
                $this->bells[] = $association_thing->json->readVariable( array("bell") );

           



            // Go through and build Nominal
            // Flag posts

            // Slots

            


        }
//exit();
*/
        return;
    }




    function dropNominal() {
        $this->thing->log($this->agent_prefix . "was asked to drop a Nominal.");

        //$this->get(); No need as it ran on start up.

        // If it comes back false we will pick that up with an unset Nominal thing.

        if (isset($this->Nominal_thing)) {
            $this->Nominal_thing->Forget();
            $this->Nominal_thing = null;
        }

        $this->get();
 
       return;
    }

    function useNominal($thing) {

        $this->Nominal_thing = $thing;



        $this->variable = $thing->variable;
        $this->route = ">";
        $this->consist = "Z";

        $this->index = $thing->index;
        $this->start_at = $thing->start_at;
        $this->quantity = $thing->quantity;
        $this->available = $thing->quantity;

        return false;

    }

    function makeNominal($variable = null) {

        $variable = $this->getVariable('nominal', $variable);

        $this->thing->log('Agent "Nominal" will make a Nominal for ' . $variable . ".");

        // Check that the shift is okay for making Nominals.

//        $shift_thing = new Shift($this->thing);
//        $shift_state = strtolower($this->thing->log($shift_thing->thing_report['keyword']));

        $ad_hoc = true;

        if ( ($ad_hoc != false) ) {

            // Ad-hoc Nominals allows creation of Nominals on the fly.

            $quantity = "Z";

            // Otherwise we needs to make trains to run in the Nominal.

            $this->thing->log($this->agent_prefix . "found that this is the Off shift.");

            // So we can create this Nominal either from the variables provided to the function,
            // or leave them unchanged.


            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            $this->current_variable = $variable;
            $this->variable = $variable;

            $this->start_at = $run_at;
            $this->quantity = $quantity; // which is run_time
            $this->getEndat();
            $this->getAvailable();

            $this->nominal_thing = $this->thing;

        }


        $this->set();

        //$this->Nominal_thing = $this->thing;

        $this->thing->log('Agent "Nominal" found a Nominal and made a Nominal.');

    }




    function getEndat() {

        if (($this->start_at != "x") and ($this->quantity != "x")) {
            $this->end_at = $this->thing->json->time(strtotime($this->start_at . " " . $this->quantity . " minutes"));
        } else {
            $this->end_at = "x";
        }


//echo $this->end_at;
//exit();
        return $this->end_at;
    }

    function getAvailable() {

        // This proto-typical Nominal manages (available) time.

        // From start_at and current_time we can calculate elapsed_time.

        if (!isset($this->end_at)) {
            $this->getEndat();
        }


        //if ($this->current_time  < $this->start_at) {
        if (strtotime($this->current_time)  < strtotime($this->start_at)) {
            $this->available = strtotime($this->end_at) - strtotime($this->start_at);
           // $this->available = $this->quantity;
        } else {
            $this->available = strtotime($this->end_at) - strtotime($this->current_time);
        }

echo $this->NominalTime($this->start_at) . "<br>";
echo $this->quantity . "<br>";
echo "<br>";
echo $this->NominalTime($this->end_at) . "<br>";
echo $this->available . "<br>";

        //    
        //if ($this->available < 0) {$this->available = 0;}
        //
        $this->thing->log('Agent "Nominal" identified ' . $this->available . ' resource units available.');
 
//exit();

    }




    function extractNominals($input) {

        if (!isset($this->nominals)) {
            $this->nominals = array();
        }

        $pattern = "|\[A-Za-z]|";

        preg_match_all($pattern, $input, $m);
//        return $m[0];
        $this->nominalss = $m[0];
        //array_pop($arr);

        return $this->nominals;
    }
    
    function getNominal($input) {
        
        $nominals = $this->extractNominals($input);

        if (count($nominals) == 1) {
            $this->nominal = $nominals[0];
            $this->thing->log('Agent "Nominal" found a Nominal (' . $this->nominal . ') in the text.');
            return $this->nominal;
        }

        if (count($nominals == 0)) {return false;}

        if (count($nominals > 1)) {return true;}



        return true;

    }

//[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}

    function extractUuids($input) {

        if (!isset($this->uuids)) {
            $this->uuids = array();
        }

        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);

        return $arr;


    }


    function read()
    {
        $this->thing->log("read");

//        $this->get();
        return $this->available;
    }



 function addNominal() {
   //     //$this->thing->log("read subject nextNominal");
        $this->makeNominal();
        $this->get();
        return;
}


    function setState($input) {

        switch ($input) {
            case "red":
                if (($this->state == "green") 
                    or ($this->state == "yellow")
                    or ($this->state == "yellow yellow")
                    or ($this->state == "X"))  {
                    $this->state = "red";
                }
                break;


            case "green";

                if (($this->state == "red") 
                    or ($this->state == "X"))  {
                    $this->state = "green";
                }

                break;
        }
               
        return;
    }

    function reset()
    {
        $this->thing->log("reset");

        $this->get();
        // Set elapsed time as 0 and state as stopped.
        $this->elapsed_time = 0;
        $this->thing->choice->Create('Nominal', $this->node_list, 'red');
/*
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("stopwatch", "refreshed_at"), $this->current_time);
        $this->thing->json->writeVariable( array("stopwatch", "elapsed"), $this->elapsed_time);
*/
        $this->thing->choice->Choose('start');

        $this->set();

        return $this->quantity_available;
    }

    function stop()
    {
        $this->thing->log("stop");
        $this->get();
        $this->thing->choice->Choose('red');
        $this->set();
//                $this->elapsed_time = time() - strtotime($time_string);
        return $this->quantity_available;
	}

    function start() 
    {
        $this->thing->log("start");

        $this->get();

        //echo "start";
        //echo $this->previous_state;

		if ($this->previous_state == 'stop') {
            $this->thing->choice->Choose('start');
            $this->state = 'start';
            $this->set();
            return;
		}

		if ($this->previous_state == 'start') {

            //echo $this->current_time;
            //ech
            $t = strtotime($this->current_time) - strtotime($this->refreshed_at);

			$this->elapsed_time = $t + strtotime($this->elapsed_time);
            $this->set();
            return;
		}

        $this->thing->choice->Choose('start');
        $this->state = 'start';
        $this->set();
        return;


 //       return null;
    }

	private function respond() {

		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = "nominal";

		//echo "<br>";

		$choices = $this->thing->choice->makeLinks($this->state);
		$this->thing_report['choices'] = $choices;
		//echo "<br>";
		//echo $html_links;

//$interval = date_diff($datetime1, $datetime2);
//echo $interval->format('%R%a days');

$available = $this->thing->human_time($this->available);

if (!isset($this->index)) {
    $index = "0";
} else {
    $index = $this->index;
}

//$s = $this->Nominal_thing->state;
$s = "GREEN";
		$sms_message = "Nominal " . strtoupper($this->nominal) ." | " . $s;
        //$sms_message .= " | " . $this->NominalTime($this->start_at);
        $sms_message .= " | ";

        $sms_message .= $this->route . " [" . $this->consist . "] " . $this->quantity;
 
        $sms_message .= " | index " . $this->index;
        //$sms_message .= " | from " . $this->NominalTime($this->start_at) . " to " . $this->NominalTime($this->end_at);
        //$sms_message .= " | now " . $this->NominalTime();
        $sms_message .= " | nuuid " . strtoupper($this->Nominal_thing->nuuid);
  

//if (!isset($this->index)) {
//    $sms_message .=  " | TEXT ADD Nominal";
//}


        if ($this->Nominal_thing->index == $this->max_index) {
          $sms_message .=  " | Changed Nominal";
//            $sms_message =  "Nominal " . strtoupper($this->head_code) | New active Nominal set. | TEXT Nominal";
//            break;
        }



    switch($this->index) {
//        case $this->max_index:
//          $sms_message =  "Nominal | No Nominal scheduled. | TEXT ADD Nominal";
//            $sms_message =  "Nominal " . strtoupper($this->head_code) | New active Nominal set. | TEXT Nominal";
//            break;

        case null:
//          $sms_message =  "Nominal | No Nominal scheduled. | TEXT ADD Nominal";
            $sms_message =  "Nominal | No active Nominal found. | TEXT Nominal <four digit clock> <1-3 digit runtime>";
            break;

        case '1':
          $sms_message .=  " | TEXT Nominal <four digit clock> <1-3 digit runtime>";
            //$sms_message .=  " | TEXT ADD Nominal";
            break;
        case '2':
            $sms_message .=  " | TEXT DROP Nominal";
            //$sms_message .=  " | TEXT Nominal";
            break;
        case '3':
            $sms_message .=  " | TEXT Nominal";
            break;
        case '4':
            $sms_message .=  " | TEXT Nominal";
            break;
        default:
            $sms_message .=  " | TEXT ?";
            break;
    }


//        if ($this->index == $this->max_index) {
//          $sms_message =  "Nominal | No Nominal scheduled. | TEXT ADD Nominal";
//            $sms_message =  "Nominal " . strtoupper($this->head_code) | New active Nominal set. | TEXT Nominal";
//            break;
//        }


        //if (!isset(

//echo $sms_message;

		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>Nominal state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $sms_message;

		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

        $test_message .= '<br>start_at: ' . $this->start_at;
        $test_message .= '<br>end_at: ' . $this->end_at;


//		$test_message .= '<br>Requested state: ' . $this->requested_state;

			$this->thing_report['sms'] = $sms_message;
			$this->thing_report['email'] = $sms_message;
			$this->thing_report['message'] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;




if (!$this->thing->isData($this->agent_input)) {
                        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
} else {
    $this->thing_report['info'] = 'Agent input was "' . $this->agent_input . '".' ;
}



		//$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);

$this->thing_report['help'] = 'This is a Nominal.';


		//echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}

    function isData($variable) {
        if ( 
            ($variable !== false) and
            ($variable !== true) and
            ($variable != null) ) {
 
            return true;

        } else {
            return false;
        }
    }

    public function readSubject() 
    {
        $this->response = null;
        $this->num_hits = 0;
        // Extract uuids into
//        $uuids_in_input

//        $Nominals_in_input



        $keywords = $this->keywords;
      //  $keywords = array('next', 'accept', 'clear', 'drop','add','new');

        if ($this->agent_input != null) {

            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);

        } else {

            $input = strtolower($this->subject);

        }


		$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

//		$this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;
/*
//echo "head cdoes";
$head_codes = $this->extractNominals($input);
//foreach ($head_codes as $head_code) {

//    echo "x". $head_code ."x";
//}

if (count($head_codes) == 1) {
       $this->head_code = $head_codes[0];
        $this->thing->log('Agent "Nominal" found a Nominal ' . $this->head_code .'.');
}
*/

    // Updated $this->head_code
    $this->getNominal($input);


//$uuids = $this->extractUuids($input);
//$this->thing->log($this->agent_prefix . " counted " . count($uuids) . " uuids.");



        $pieces = explode(" ", strtolower($input));


		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'nominal') {

                //echo "readsubject Nominal";
                $this->read();
                return;
            }

            // Drop through
        }

//echo "meepmeep";



/*
    if ((isset($this->run_time)) and (isset($this->run_at))) {
        // Good chance with both these set that asking for a new
        // Nominal to be created, or to override existing Nominal.
        $this->thing->log('Agent "Nominal" found a run time.');

        $this->nextNominal();
        return;
    }
*/
    foreach ($pieces as $key=>$piece) {
        foreach ($keywords as $command) {
            if (strpos(strtolower($piece),$command) !== false) {

                switch($piece) {
    case 'accept':
        $this->acceptThing();
        break;

    case 'clear':
        $this->clearThing();
        break;


    case 'start':
        $this->start();
        break;
    case 'stop':
        $this->stop();
        break;
    case 'reset':
        $this->reset();
        break;
    case 'split':
        $this->split();
        break;

    case 'next':
        $this->thing->log("read subject nextNominal");
        $this->nextNominal();
        break;

   case 'drop':
   //     //$this->thing->log("read subject nextNominal");
        $this->dropNominal();
        break;


   case 'add':
   //     //$this->thing->log("read subject nextNominal");
        $this->makeNominal();
        break;


    default:
        //$this->read();                                                    //echo 'default';

                                        }

                                }
                        }

                }


// Check whether Nominal saw a run_at and/or run_time
// Intent at this point is less clear.  But Nominal
// might have extracted information in these variables.

// $uuids, $head_codes, $this->run_at, $this->run_time

if ( (count($uuids) == 1) and (count($nominals) == 1) and (isset($this->run_at)) and (isset($this->quantity)) ) {

    // Likely matching a head_code to a uuid.

}


//if ( (isset($this->run_at)) and (isset($this->quantity)) ) {
//echo $this->head_code;
//var_dump( ($this->head_code !== true) );
//exit();
//$this->head_code = true;
    if ($this->isData($this->variable)) {
        $this->makeNominal($this->variable);
        return;
    }
//exit();
//    if ((isset($this->run_time)) and (isset($this->run_at))) {
        // Good chance with both these set that asking for a new
        // Nominal to be created, or to override existing Nominal.
//        $this->thing->log('Agent "Nominal" found a run time.');

//        $this->nextNominal();
//        return;
//    }


// If all else fails try the discriminator.

    $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.
    switch($this->requested_state) {
        case 'start':
            $this->start();
            break;
        case 'stop':
            $this->stop();
            break;
        case 'reset':
            $this->reset();
            break;
        case 'split':
            $this->split();
            break;
    }

    $this->read();




                return "Message not understood";




		return false;

	
	}






	function kill() {
		// No messing about.
		return $this->thing->Forget();
	}

       function discriminateInput($input, $discriminators = null) {


                //$input = "optout opt-out opt-out";

                if ($discriminators == null) {
                        $discriminators = array('accept', 'clear');
                }       



                $default_discriminator_thresholds = array(2=>0.3, 3=>0.3, 4=>0.3);

                if (count($discriminators) > 4) {
                        $minimum_discrimination = $default_discriminator_thresholds[4];
                } else {
                        $minimum_discrimination = $default_discriminator_thresholds[count($discriminators)];
                }



                $aliases = array();

                $aliases['accept'] = array('accept','add','+');
                $aliases['clear'] = array('clear','drop', 'clr', '-');



                $words = explode(" ", $input);

                $count = array();

                $total_count = 0;
                // Set counts to 1.  Bayes thing...     
                foreach ($discriminators as $discriminator) {
                        $count[$discriminator] = 1;

                       $total_count = $total_count + 1;
                }
                // ...and the total count.



                foreach ($words as $word) {

                        foreach ($discriminators as $discriminator) {

                                if ($word == $discriminator) {
                                        $count[$discriminator] = $count[$discriminator] + 1;
                                        $total_count = $total_count + 1;
                                                echo "sum";
                                }

                                foreach ($aliases[$discriminator] as $alias) {

                                        if ($word == $alias) {
                                                $count[$discriminator] = $count[$discriminator] + 1;
                                                $total_count = $total_count + 1;
                                                echo "sum";
                                        }
                                }
                        }

                }

                //echo "total count"; $total_count;
                // Set total sum of all values to 1.

                $normalized = array();
                foreach ($discriminators as $discriminator) {
                        $normalized[$discriminator] = $count[$discriminator] / $total_count;            
                }


                // Is there good discrimination
                arsort($normalized);


                // Now see what the delta is between position 0 and 1

                foreach ($normalized as $key=>$value) {
                    //echo $key, $value;

                    if ( isset($max) ) {$delta = $max-$value; break;}
                        if ( !isset($max) ) {$max = $value;$selected_discriminator = $key; }
                }


//                        echo '<pre> Agent "Nominal" normalized discrimators "';print_r($normalized);echo'"</pre>';


                if ($delta >= $minimum_discrimination) {
                        //echo "discriminator" . $discriminator;
                        return $selected_discriminator;
                } else {
                        return false; // No discriminator found.
                } 

                return true;
        }

}


    function loadNominals() {
// Problem is probably best addressed by keeping a SQL database of nominals.
// Starting here.  5494 first-names and 88799 last-names
// http://www.quietaffiliate.com/free-first-name-and-last-name-databases-csv-and-sql/

//Top 1000 Baby Boy Names:

// Below is a crappy problematic approach.

$male_names = "
    Noah
    Liam
    Mason
    Jacob
    William
    Ethan
    James
    Alexander
    Michael
    Benjamin
    Elijah
    Daniel
    Aiden
    Logan
    Matthew
    Lucas
    Jackson
    David
    Oliver
    Jayden
    Joseph
    Gabriel
    Samuel
    Carter
    Anthony
    John
    Dylan
    Luke
    Henry
    Andrew
    Isaac
    Christopher
    Joshua
    Wyatt
    Sebastian
    Owen
    Caleb
    Nathan
    Ryan
    Jack
    Hunter
    Levi
    Christian
    Jaxon
    Julian
    Landon
    Grayson
    Jonathan
    Isaiah
    Charles
    Thomas
    Aaron
    Eli
    Connor
    Jeremiah
    Cameron
    Josiah
    Adrian
    Colton
    Jordan
    Brayden
    Nicholas
    Robert
    Angel
    Hudson
    Lincoln
    Evan
    Dominic
    Austin
    Gavin
    Nolan
    Parker
    Adam
    Chase
    Jace
    Ian
    Cooper
    Easton
    Kevin
    Jose
    Tyler
    Brandon
    Asher
    Jaxson
    Mateo
    Jason
    Ayden
    Zachary
    Carson
    Xavier
    Leo
    Ezra
    Bentley
    Sawyer
    Kayden
    Blake
    Nathaniel
    Ryder
    Theodore
    Elias
    Tristan
    Roman
    Leonardo
    Camden
    Brody
    Luis
    Miles
    Micah
    Vincent
    Justin
    Greyson
    Declan
    Maxwell
    Juan
    Cole
    Damian
    Carlos
    Max
    Harrison
    Weston
    Brantley
    Braxton
    Axel
    Diego
    Abel
    Wesley
    Santiago
    Jesus
    Silas
    Giovanni
    Bryce
    Jayce
    Bryson
    Alex
    Everett
    George
    Eric
    Ivan
    Emmett
    Kaiden
    Ashton
    Kingston
    Jonah
    Jameson
    Kai
    Maddox
    Timothy
    Ezekiel
    Ryker
    Emmanuel
    Hayden
    Antonio
    Bennett
    Steven
    Richard
    Jude
    Luca
    Edward
    Joel
    Victor
    Miguel
    Malachi
    King
    Patrick
    Kaleb
    Bryan
    Alan
    Marcus
    Preston
    Abraham
    Calvin
    Colin
    Bradley
    Jeremy
    Kyle
    Graham
    Grant
    Jesse
    Kaden
    Alejandro
    Oscar
    Jase
    Karter
    Maverick
    Aidan
    Tucker
    Avery
    Amir
    Brian
    Iker
    Matteo
    Caden
    Zayden
    Riley
    August
    Mark
    Maximus
    Brady
    Kenneth
    Paul
    Jaden
    Nicolas
    Beau
    Dean
    Jake
    Peter
    Xander
    Elliot
    Finn
    Derek
    Sean
    Cayden
    Elliott
    Jax
    Jasper
    Lorenzo
    Omar
    Beckett
    Rowan
    Gael
    Corbin
    Waylon
    Myles
    Tanner
    Jorge
    Javier
    Zion
    Andres
    Charlie
    Paxton
    Emiliano
    Brooks
    Zane
    Simon
    Judah
    Griffin
    Cody
    Gunner
    Dawson
    Israel
    Rylan
    Gage
    Messiah
    River
    Kameron
    Stephen
    Francisco
    Clayton
    Zander
    Chance
    Eduardo
    Spencer
    Lukas
    Damien
    Dallas
    Conner
    Travis
    Knox
    Raymond
    Peyton
    Devin
    Felix
    Jayceon
    Collin
    Amari
    Erick
    Cash
    Jaiden
    Fernando
    Cristian
    Josue
    Keegan
    Garrett
    Rhett
    Ricardo
    Martin
    Reid
    Seth
    Andre
    Cesar
    Titus
    Donovan
    Manuel
    Mario
    Caiden
    Adriel
    Kyler
    Milo
    Archer
    Jeffrey
    Holden
    Arthur
    Karson
    Rafael
    Shane
    Lane
    Louis
    Angelo
    Remington
    Troy
    Emerson
    Maximiliano
    Hector
    Emilio
    Anderson
    Trevor
    Phoenix
    Walter
    Johnathan
    Johnny
    Edwin
    Julius
    Barrett
    Leon
    Tyson
    Tobias
    Edgar
    Dominick
    Marshall
    Marco
    Joaquin
    Dante
    Andy
    Cruz
    Ali
    Finley
    Dalton
    Gideon
    Reed
    Enzo
    Sergio
    Jett
    Thiago
    Kyrie
    Ronan
    Cohen
    Colt
    Erik
    Trenton
    Jared
    Walker
    Landen
    Alexis
    Nash
    Jaylen
    Gregory
    Emanuel
    Killian
    Allen
    Atticus
    Desmond
    Shawn
    Grady
    Quinn
    Frank
    Fabian
    Dakota
    Roberto
    Beckham
    Major
    Skyler
    Nehemiah
    Drew
    Cade
    Muhammad
    Kendrick
    Pedro
    Orion
    Aden
    Kamden
    Ruben
    Zaiden
    Clark
    Noel
    Porter
    Solomon
    Romeo
    Rory
    Malik
    Daxton
    Leland
    Kash
    Abram
    Derrick
    Kade
    Gunnar
    Prince
    Brendan
    Leonel
    Kason
    Braylon
    Legend
    Pablo
    Jay
    Adan
    Jensen
    Esteban
    Kellan
    Drake
    Warren
    Ismael
    Ari
    Russell
    Bruce
    Finnegan
    Marcos
    Jayson
    Theo
    Jaxton
    Phillip
    Dexter
    Braylen
    Armando
    Braden
    Corey
    Kolton
    Gerardo
    Ace
    Ellis
    Malcolm
    Tate
    Zachariah
    Chandler
    Milan
    Keith
    Danny
    Damon
    Enrique
    Jonas
    Kane
    Princeton
    Hugo
    Ronald
    Philip
    Ibrahim
    Kayson
    Maximilian
    Lawson
    Harvey
    Albert
    Donald
    Raul
    Franklin
    Hendrix
    Odin
    Brennan
    Jamison
    Dillon
    Brock
    Landyn
    Mohamed
    Brycen
    Deacon
    Colby
    Alec
    Julio
    Scott
    Matias
    Sullivan
    Rodrigo
    Cason
    Taylor
    Rocco
    Nico
    Royal
    Pierce
    Augustus
    Raiden
    Kasen
    Benson
    Moses
    Cyrus
    Raylan
    Davis
    Khalil
    Moises
    Conor
    Nikolai
    Alijah
    Mathew
    Keaton
    Francis
    Quentin
    Ty
    Jaime
    Ronin
    Kian
    Lennox
    Malakai
    Atlas
    Jerry
    Ryland
    Ahmed
    Saul
    Sterling
    Dennis
    Lawrence
    Zayne
    Bodhi
    Arjun
    Darius
    Arlo
    Eden
    Tony
    Dustin
    Kellen
    Chris
    Mohammed
    Nasir
    Omari
    Kieran
    Nixon
    Rhys
    Armani
    Arturo
    Bowen
    Frederick
    Callen
    Leonidas
    Remy
    Wade
    Luka
    Jakob
    Winston
    Justice
    Alonzo
    Curtis
    Aarav
    Gustavo
    Royce
    Asa
    Gannon
    Kyson
    Hank
    Izaiah
    Roy
    Raphael
    Luciano
    Hayes
    Case
    Darren
    Mohammad
    Otto
    Layton
    Isaias
    Alberto
    Jamari
    Colten
    Dax
    Marvin
    Casey
    Moshe
    Johan
    Sam
    Matthias
    Larry
    Trey
    Devon
    Trent
    Mauricio
    Mathias
    Issac
    Dorian
    Gianni
    Ahmad
    Nikolas
    Oakley
    Uriel
    Lewis
    Randy
    Cullen
    Braydon
    Ezequiel
    Reece
    Jimmy
    Crosby
    Soren
    Uriah
    Roger
    Nathanael
    Emmitt
    Gary
    Rayan
    Ricky
    Mitchell
    Roland
    Alfredo
    Cannon
    Jalen
    Tatum
    Kobe
    Yusuf
    Quinton
    Korbin
    Brayan
    Joe
    Byron
    Ariel
    Quincy
    Carl
    Kristopher
    Alvin
    Duke
    Lance
    London
    Jasiah
    Boston
    Santino
    Lennon
    Deandre
    Madden
    Talon
    Sylas
    Orlando
    Hamza
    Bo
    Aldo
    Douglas
    Tristen
    Wilson
    Maurice
    Samson
    Cayson
    Bryant
    Conrad
    Dane
    Julien
    Sincere
    Noe
    Salvador
    Nelson
    Edison
    Ramon
    Lucian
    Mekhi
    Niko
    Ayaan
    Vihaan
    Neil
    Titan
    Ernesto
    Brentley
    Lionel
    Zayn
    Dominik
    Cassius
    Rowen
    Blaine
    Sage
    Kelvin
    Jaxen
    Memphis
    Leonard
    Abdullah
    Jacoby
    Allan
    Jagger
    Yahir
    Forrest
    Guillermo
    Mack
    Zechariah
    Harley
    Terry
    Kylan
    Fletcher
    Rohan
    Eddie
    Bronson
    Jefferson
    Rayden
    Terrance
    Marc
    Morgan
    Valentino
    Demetrius
    Kristian
    Hezekiah
    Lee
    Alessandro
    Makai
    Rex
    Callum
    Kamari
    Casen
    Tripp
    Callan
    Stanley
    Toby
    Elian
    Langston
    Melvin
    Payton
    Flynn
    Jamir
    Kyree
    Aryan
    Axton
    Azariah
    Branson
    Reese
    Adonis
    Thaddeus
    Zeke
    Tommy
    Blaze
    Carmelo
    Skylar
    Arian
    Bruno
    Kaysen
    Layne
    Ray
    Zain
    Crew
    Jedidiah
    Rodney
    Clay
    Tomas
    Alden
    Jadiel
    Harper
    Ares
    Cory
    Brecken
    Chaim
    Nickolas
    Kareem
    Xzavier
    Kaison
    Alonso
    Amos
    Vicente
    Samir
    Yosef
    Jamal
    Jon
    Bobby
    Aron
    Ben
    Ford
    Brodie
    Cain
    Finnley
    Briggs
    Davion
    Kingsley
    Brett
    Wayne
    Zackary
    Apollo
    Emery
    Joziah
    Lucca
    Bentlee
    Hassan
    Westin
    Joey
    Vance
    Marcelo
    Axl
    Jermaine
    Chad
    Gerald
    Kole
    Dash
    Dayton
    Lachlan
    Shaun
    Kody
    Ronnie
    Kolten
    Marcel
    Stetson
    Willie
    Jeffery
    Brantlee
    Elisha
    Maxim
    Kendall
    Harry
    Leandro
    Aaden
    Channing
    Kohen
    Yousef
    Darian
    Enoch
    Mayson
    Neymar
    Giovani
    Alfonso
    Duncan
    Anders
    Braeden
    Dwayne
    Keagan
    Felipe
    Fisher
    Stefan
    Trace
    Aydin
    Anson
    Clyde
    Blaise
    Canaan
    Maxton
    Alexzander
    Billy
    Harold
    Baylor
    Gordon
    Rene
    Terrence
    Vincenzo
    Kamdyn
    Marlon
    Castiel
    Lamar
    Augustine
    Jamie
    Eugene
    Harlan
    Kase
    Miller
    Van
    Kolby
    Sonny
    Emory
    Junior
    Graysen
    Heath
    Rogelio
    Will
    Amare
    Ameer
    Camdyn
    Jerome
    Maison
    Micheal
    Cristiano
    Giancarlo
    Henrik
    Lochlan
    Bode
    Camron
    Houston
    Otis
    Hugh
    Kannon
    Konnor
    Emmet
    Kamryn
    Maximo
    Adrien
    Cedric
    Dariel
    Landry
    Leighton
    Magnus
    Draven
    Javon
    Marley
    Zavier
    Markus
    Justus
    Reyansh
    Rudy
    Santana
    Misael
    Abdiel
    Davian
    Zaire
    Jordy
    Reginald
    Benton
    Darwin
    Franco
    Jairo
    Jonathon
    Reuben
    Urijah
    Vivaan
    Brent
    Gauge
    Vaughn
    Coleman
    Zaid
    Terrell
    Kenny
    Brice
    Lyric
    Judson
    Shiloh
    Damari
    Kalel
    Braiden
    Brenden
    Coen
    Denver
    Javion
    Thatcher
    Rey
    Dilan
    Dimitri
    Immanuel
    Mustafa
    Ulises
    Alvaro
    Dominique
    Eliseo
    Anakin
    Craig
    Dario
    Santos
    Grey
    Ishaan
    Jessie
    Jonael
    Alfred
    Tyrone
    Valentin
    Jadon
    Turner
    Ignacio
    Riaan
    Rocky
    Ephraim
    Marquis
    Musa
    Keenan
    Ridge
    Chace
    Kymani
    Rodolfo
    Darrell
    Steve
    Agustin
    Jaziel
    Boone
    Cairo
    Kashton
    Rashad
    Gibson
    Jabari
    Avi
    Quintin
    Seamus
    Rolando
    Sutton
    Camilo
    Triston
    Yehuda
    Cristopher
    Davin
    Ernest
    Jamarion
    Kamren
    Salvatore
    Anton
    Aydan
    Huxley
    Jovani
    Wilder
    Bodie
    Jordyn
    Louie
    Achilles
    Kaeden
    Kamron
    Aarush
    Deangelo
    Robin
    Yadiel
    Yahya
    Boden
    Ean
    Kye
    Kylen
    Todd
    Truman
    Chevy
    Gilbert
    Haiden
    Brixton
    Dangelo
    Juelz
    Osvaldo
    Bishop
    Freddy
    Reagan
    Frankie
    Malaki
    Camren
    Deshawn
    Jayvion
    Leroy
    Briar
    Jaydon
    Antoine";

$female_names = "

    Emma
    Olivia
    Sophia
    Ava
    Isabella
    Mia
    Abigail
    Emily
    Charlotte
    Harper
    Madison
    Amelia
    Elizabeth
    Sofia
    Evelyn
    Avery
    Chloe
    Ella
    Grace
    Victoria
    Aubrey
    Scarlett
    Zoey
    Addison
    Lily
    Lillian
    Natalie
    Hannah
    Aria
    Layla
    Brooklyn
    Alexa
    Zoe
    Penelope
    Riley
    Leah
    Audrey
    Savannah
    Allison
    Samantha
    Nora
    Skylar
    Camila
    Anna
    Paisley
    Ariana
    Ellie
    Aaliyah
    Claire
    Violet
    Stella
    Sadie
    Mila
    Gabriella
    Lucy
    Arianna
    Kennedy
    Sarah
    Madelyn
    Eleanor
    Kaylee
    Caroline
    Hazel
    Hailey
    Genesis
    Kylie
    Autumn
    Piper
    Maya
    Nevaeh
    Serenity
    Peyton
    Mackenzie
    Bella
    Eva
    Taylor
    Naomi
    Aubree
    Aurora
    Melanie
    Lydia
    Brianna
    Ruby
    Katherine
    Ashley
    Alexis
    Alice
    Cora
    Julia
    Madeline
    Faith
    Annabelle
    Alyssa
    Isabelle
    Vivian
    Gianna
    Related Post
    Bellissima! 14 Beautiful Italian Girl Names
    Quinn
    Clara
    Reagan
    Khloe
    Alexandra
    Hadley
    Eliana
    Sophie
    London
    Elena
    Kimberly
    Bailey
    Maria
    Luna
    Willow
    Jasmine
    Kinsley
    Valentina
    Kayla
    Delilah
    Andrea
    Natalia
    Lauren
    Morgan
    Rylee
    Sydney
    Adalynn
    Mary
    Ximena
    Jade
    Liliana
    Brielle
    Ivy
    Trinity
    Josephine
    Adalyn
    Jocelyn
    Emery
    Adeline
    Jordyn
    Ariel
    Everly
    Lilly
    Paige
    Isla
    Lyla
    Makayla
    Molly
    Emilia
    Mya
    Kendall
    Melody
    Isabel
    Brooke
    Mckenzie
    Nicole
    Payton
    Margaret
    Mariah
    Eden
    Athena
    Amy
    Norah
    Londyn
    Valeria
    Sara
    Aliyah
    Angelina
    Gracie
    Rose
    Rachel
    Juliana
    Laila
    Brooklynn
    Valerie
    Alina
    Reese
    Elise
    Eliza
    Alaina
    Raelynn
    Leilani
    Catherine
    Emerson
    Cecilia
    Genevieve
    Daisy
    Harmony
    Vanessa
    Adriana
    Presley
    Rebecca
    Destiny
    Hayden
    Julianna
    Michelle
    Adelyn
    Arabella
    Summer
    Callie
    Kaitlyn
    Ryleigh
    Lila
    Daniela
    Arya
    Alana
    Esther
    Finley
    Gabrielle
    Jessica
    Charlie
    Stephanie
    Tessa
    Makenzie
    Ana
    Amaya
    Alexandria
    Alivia
    Nova
    Anastasia
    Iris
    Marley
    Fiona
    Angela
    Giselle
    Kate
    Alayna
    Lola
    Lucia
    Juliette
    Parker
    Teagan
    Sienna
    Georgia
    Hope
    Cali
    Vivienne
    Izabella
    Kinley
    Daleyza
    Kylee
    Jayla
    Katelyn
    Juliet
    Maggie
    Dakota
    Delaney
    Brynlee
    Keira
    Camille
    Leila
    Mckenna
    Aniyah
    Noelle
    Josie
    Jennifer
    Melissa
    Gabriela
    Allie
    Eloise
    Cassidy
    Jacqueline
    Brynn
    Sawyer
    Evangeline
    Jordan
    Paris
    Olive
    Ayla
    Rosalie
    Kali
    Maci
    Gemma
    Lilliana
    Raegan
    Lena
    Adelaide
    Journey
    Adelynn
    Alessandra
    Kenzie
    Miranda
    Haley
    June
    Harley
    Charlee
    Lucille
    Talia
    Skyler
    Makenna
    Phoebe
    Jane
    Lyric
    Angel
    Elaina
    Adrianna
    Ruth
    Miriam
    Diana
    Mariana
    Danielle
    Jenna
    Shelby
    Nina
    Madeleine
    Elliana
    Amina
    Amiyah
    Chelsea
    Joanna
    Jada
    Lexi
    Katie
    Maddison
    Fatima
    Vera
    Malia
    Lilah
    Madilyn
    Amanda
    Daniella
    Alexia
    Kathryn
    Paislee
    Selena
    Laura
    Annie
    Nyla
    Catalina
    Kayleigh
    Sloane
    Kamila
    Lia
    Haven
    Rowan
    Ashlyn
    Christina
    Amber
    Myla
    Addilyn
    Erin
    Alison
    Ainsley
    Raelyn
    Cadence
    Kendra
    Heidi
    Kelsey
    Nadia
    Alondra
    Cheyenne
    Kaydence
    Mikayla
    River
    Heaven
    Arielle
    Lana
    Blakely
    Sabrina
    Kyla
    Ada
    Gracelyn
    Allyson
    Felicity
    Kira
    Briella
    Kamryn
    Adaline
    Alicia
    Ember
    Aylin
    Veronica
    Esmeralda
    Sage
    Leslie
    Aspen
    Gia
    Camilla
    Ashlynn
    Scarlet
    Journee
    Daphne
    Bianca
    Mckinley
    Amira
    Carmen
    Kyleigh
    Megan
    Skye
    Elsie
    Kennedi
    Averie
    Carly
    Rylie
    Gracelynn
    Mallory
    Emersyn
    Logan
    Camryn
    Annabella
    Dylan
    Elle
    Kiara
    Yaretzi
    Ariella
    Zara
    April
    Gwendolyn
    Anaya
    Baylee
    Brinley
    Sierra
    Annalise
    Tatum
    Serena
    Dahlia
    Macy
    Miracle
    Madelynn
    Briana
    Freya
    Macie
    Helen
    Bethany
    Leia
    Harlow
    Blake
    Jayleen
    Angelica
    Marilyn
    Viviana
    Francesca
    Juniper
    Carolina
    Jazmin
    Emely
    Maliyah
    Cataleya
    Jillian
    Joy
    Abby
    Malaysia
    Nylah
    Sarai
    Evelynn
    Nia
    Zuri
    Addyson
    Aleah
    Kaia
    Bristol
    Lorelei
    Jazmine
    Maeve
    Alejandra
    Justice
    Julie
    Marlee
    Phoenix
    Jimena
    Emmalyn
    Nayeli
    Aleena
    Brittany
    Amara
    Karina
    Giuliana
    Thea
    Braelynn
    Kassidy
    Braelyn
    Luciana
    Aubrie
    Janelle
    Madisyn
    Brylee
    Leighton
    Ryan
    Amari
    Eve
    Millie
    Kelly
    Selah
    Lacey
    Willa
    Haylee
    Jaylah
    Sylvia
    Melany
    Elisa
    Elsa
    Hattie
    Raven
    Holly
    Aisha
    Itzel
    Kyra
    Tiffany
    Jayda
    Michaela
    Madilynn
    Jamie
    Celeste
    Lilian
    Remi
    Priscilla
    Jazlyn
    Karen
    Savanna
    Zariah
    Lauryn
    Alanna
    Kara
    Karla
    Cassandra
    Ariah
    Evie
    Frances
    Aileen
    Lennon
    Charley
    Rosemary
    Danna
    Regina
    Kaelyn
    Virginia
    Hanna
    Rebekah
    Alani
    Edith
    Liana
    Charleigh
    Gloria
    Cameron
    Colette
    Kailey
    Carter
    Helena
    Matilda
    Imani
    Bridget
    Cynthia
    Janiyah
    Marissa
    Johanna
    Sasha
    Kaliyah
    Cecelia
    Adelina
    Jessa
    Hayley
    Julissa
    Winter
    Crystal
    Kaylie
    Bailee
    Charli
    Henley
    Anya
    Maia
    Skyla
    Liberty
    Fernanda
    Monica
    Braylee
    Dallas
    Mariam
    Marie
    Beatrice
    Hallie
    Maryam
    Angelique
    Anne
    Madalyn
    Alayah
    Annika
    Greta
    Lilyana
    Kadence
    Coraline
    Lainey
    Mabel
    Lillie
    Anika
    Azalea
    Dayana
    Jaliyah
    Addisyn
    Emilee
    Mira
    Angie
    Lilith
    Mae
    Meredith
    Guadalupe
    Emelia
    Margot
    Melina
    Aniya
    Alena
    Myra
    Elianna
    Caitlyn
    Jaelynn
    Jaelyn
    Demi
    Mikaela
    Tiana
    Blair
    Shiloh
    Ariyah
    Saylor
    Caitlin
    Lindsey
    Oakley
    Alia
    Everleigh
    Ivanna
    Miah
    Emmy
    Jessie
    Anahi
    Kaylin
    Ansley
    Annabel
    Remington
    Kora
    Maisie
    Nathalie
    Emory
    Karsyn
    Pearl
    Irene
    Kimber
    Rosa
    Lylah
    Magnolia
    Samara
    Elliot
    Renata
    Galilea
    Kensley
    Kiera
    Whitney
    Amelie
    Siena
    Bria
    Laney
    Perla
    Tatiana
    Zelda
    Jaycee
    Kori
    Montserrat
    Lorelai
    Adele
    Elyse
    Katelynn
    Kynlee
    Marina
    Jayden
    Kailyn
    Avah
    Kenley
    Aviana
    Armani
    Dulce
    Alaia
    Teresa
    Natasha
    Milani
    Amirah
    Breanna
    Linda
    Tenley
    Sutton
    Elaine
    Elliott
    Aliza
    Kenna
    Meadow
    Alyson
    Rory
    Milana
    Erica
    Esme
    Leona
    Joselyn
    Madalynn
    Alma
    Chanel
    Myah
    Karter
    Zahra
    Audrina
    Ariya
    Jemma
    Eileen
    Kallie
    Milan
    Emmalynn
    Lailah
    Sloan
    Clarissa
    Karlee
    Laylah
    Amiya
    Collins
    Ellen
    Hadassah
    Danica
    Jaylene
    Averi
    Reyna
    Saige
    Wren
    Lexie
    Dorothy
    Lilianna
    Monroe
    Aryanna
    Elisabeth
    Ivory
    Liv
    Janessa
    Jaylynn
    Livia
    Rayna
    Alaya
    Malaya
    Cara
    Erika
    Amani
    Clare
    Addilynn
    Roselyn
    Corinne
    Paola
    Jolene
    Anabelle
    Aliana
    Lea
    Mara
    Lennox
    Claudia
    Kristina
    Jaylee
    Kaylynn
    Zariyah
    Gwen
    Kinslee
    Avianna
    Lisa
    Raquel
    Jolie
    Carolyn
    Courtney
    Penny
    Royal
    Alannah
    Ciara
    Chaya
    Kassandra
    Milena
    Mina
    Noa
    Leanna
    Zoie
    Ariadne
    Monserrat
    Nola
    Carlee
    Isabela
    Jazlynn
    Kairi
    Laurel
    Sky
    Rosie
    Arely
    Aubrielle
    Kenia
    Noemi
    Scarlette
    Farrah
    Leyla
    Amia
    Bryanna
    Naya
    Wynter
    Hunter
    Katalina
    Taliyah
    Amaris
    Emerie
    Martha
    Thalia
    Christine
    Estrella
    Brenna
    Milania
    Salma
    Lillianna
    Marjorie
    Shayla
    Zendaya
    Aurelia
    Brenda
    Julieta
    Adilynn
    Deborah
    Keyla
    Patricia
    Emmeline
    Hadlee
    Giovanna
    Kailee
    Desiree
    Casey
    Karlie
    Khaleesi
    Lara
    Tori
    Clementine
    Nancy
    Simone
    Ayleen
    Estelle
    Celine
    Madyson
    Zaniyah
    Adley
    Amalia
    Paityn
    Kathleen
    Sandra
    Lizbeth
    Maleah
    Micah
    Aryana
    Hailee
    Aiyana
    Joyce
    Ryann
    Caylee
    Kalani
    Marisol
    Nathaly
    Briar
    Holland
    Lindsay
    Remy
    Adrienne
    Azariah
    Harlee
    Dana
    Frida
    Marianna
    Yamileth
    Chana
    Kaya
    Lina
    Celia
    Analia
    Hana
    Jayde
    Joslyn
    Romina
    Anabella
    Barbara
    Bryleigh
    Emilie
    Nathalia
    Ally
    Evalyn
    Bonnie
    Zaria
    Carla
    Estella
    Kailani
    Rivka
    Rylan
    Paulina
    Kayden
    Giana
    Yareli
    Kaiya
    Sariah
    Avalynn
    Jasmin
    Aya
    Jewel
    Kristen
    Paula
    Astrid
    Jordynn
    Kenya
    Ann
    Annalee
    Kai
    Kiley
    Marleigh
    Julianne
    Zion
    Emmaline
    Nataly
    Aminah
    Amya
    Iliana
    Jaida
    Paloma
    Asia
    Louisa
    Sarahi
    Tara
    Andi
    Arden
    Dalary
    Aimee
    Alisson
    Halle
    Aitana
    Landry
    Alisha
    Elin
    Maliah
    Belen
    Briley
    Raina
    Vienna
    Esperanza
    Judith
    Faye
    Susan
    Aliya
    Aranza
    Yasmin
    Jaylin
    Kyndall
    Saniyah
    Wendy
    Yaritza
    Azaria
    Kaelynn
    Neriah
    Zainab
    Alissa
    Cherish
    Dixie
    Veda
    Nala
    Tabitha
    Cordelia
    Ellison
    Meilani
    Angeline
    Reina
    Tegan
    Hadleigh
    Harmoni
    Kimora
    Ingrid
    Lilia
    Luz
    Aislinn
    America
    Ellis
    Elora
    Heather
    Natalee
    Miya
    Heavenly
    Jenny
    Aubriella
    Emmalee
    Kensington
    Kiana
    Lilyanna
    Tinley
    Ophelia
    Moriah
    Sharon
    Charlize
    Abril
    Avalyn
    Mariyah
    Taya
    Ireland
    Lyra
    Noor
    Sariyah
    Giavanna
    Stevie
    Rhea
    Zaylee
    Denise
    Frankie
    Janiya
    Jocelynn
    Libby
    Aubrianna
    Kaitlynn
    Princess
    Sidney
    Alianna
";


    return;
    }




?>


