<?php

namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


ini_set("allow_url_fopen", 1);

class Block
{

    // This is a resource block.  It is a train which be run by the block scheduler.
    // It will respond to trains with a signal.
    // Red - Not available
    // Green - Slot allocated
    // Yellow - Next signal Red.
    // Double Yellow - Next signal Yellow

    // The block keeps track of the uuids of associated resources.
    // And checks to see what the block signal should be.  And pass and collect tokens.

    // This is the block manager.  They are an ex-British Rail signalperson.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) {

        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

$this->agent_prefix = 'Agent "Block" ';

 $this->node_list = array("start"=>array("stop 1"=>array("stop 2","stop 1"),"stop 3"),"stop 3");
$this->thing->choice->load('block');



//                'block' => array('default run_time'=>'105',
//                                'negative_time'=>'yes'),


                $this->default_run_time = $this->thing->container['api']['block']['default run_time'];
                $this->negative_time = $this->thing->container['api']['block']['negative_time'];

                //$this->app_secret = $this->thing->container['api']['facebook']['app secret'];

                //$this->page_access_token = $this->thing->container['api']['facebook']['page_access_token'];



        $this->current_time = $this->thing->json->time();


        // Loads in Block variables.
        $this->get(); // Updates $this->elapsed_time;


		// So created a token_generated_time field.
        //        $this->thing->json->setField("variables");
        //        $this->thing->json->writeVariable( array("stopwatch", "request_at"), $this->thing->json->time() );

//$this->thing->json->time()


		$this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

//$this->thing->choice->Create('stopwatch', $this->node_list, 'stop');

//$this->thing->choice->Choose("midden work");

		$this->thing->log('<pre> Agent "Block" running on Thing '. $this->thing->nuuid . '.</pre>');
		$this->thing->log('<pre> Agent "Block" received this Thing "'.  $this->subject . '".</pre>');


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



		$this->thing->log('<pre> Agent "Block" completed</pre>');

        $this->thing_report['log'] = $this->thing->log;



		return;

		}





    function set()
    {

        // A block has some remaining amount of resource and 
        // an indication where to start.


        // This makes sure that
        if (!isset($this->block_thing)) {
            $this->block_thing = $this->thing;
        }



        $this->block_thing->json->setField("variables");
        $this->block_thing->json->writeVariable( array("block", "index"), $this->index );

        $this->block_thing->json->writeVariable( array("block", "start_at"), $this->start_at );
        $this->block_thing->json->writeVariable( array("block", "quantity"), $this->quantity );

        $this->getAvailable();
        $this->block_thing->json->writeVariable( array("block", "available"), $this->available );
        $this->block_thing->json->writeVariable( array("block", "refreshed_at"), $this->current_time );

//if (!isset($this->state)) {
//        $this->state = "X";
//}

        $this->block_thing->choice->save('block', $this->state);


        return;
    }

    function nextBlock() {

        $this->thing->log("next block");
        // Pull up the current block
        $this->get();

        // Find the end time of the block
        // which is $this->end_at

        // One minute into next block
        $quantity = 1;
        $next_time = $this->thing->json->time(strtotime($this->end_at . " " . $quantity . " minutes"));

        $this->get($next_time);

        // So this should create a block in the next minute.




        return $this->available;


    }


    function get($block_time = null)
    {

        // Loads current block into $this->block_thing

        $match = false;

        if ($block_time == null) {
            $block_time = $this->current_time;
        }

        $block_things = array();
        // See if a block record exists.
//        require_once '/var/www/html/stackr.ca/agents/findagent.php';
        $findagent_thing = new FindAgent($this->thing, 'block');

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

$this->thing->log('Agent "Block" found ' . count($findagent_thing->thing_report['things']) ." Block Things." );

        $this->max_index =0;

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {

            $thing = new Thing($block_thing['uuid']);


            $thing->json->setField("variables");

            $thing->index = $thing->json->readVariable( array("block", "index"))  ;
            if ($thing->index > $this->max_index) {$this->max_index = $thing->index;}

            $thing->start_at = $thing->json->readVariable( array("block", "start_at"))  ;
            $thing->quantity = $thing->json->readVariable( array("block", "quantity"))  ;
            $thing->available = $thing->json->readVariable( array("block", "available"))  ;
            $thing->refreshed_at = $thing->json->readVariable( array("block", "refreshed_at"))  ;


            if ($thing->quantity > 0) {
                $thing->end_at = $this->thing->json->time(strtotime($thing->start_at . " " . $thing->quantity . " minutes"));
            } else {
                $thing->end_at = null;
            }
            
            if ( ( strtotime($block_time) >= strtotime($thing->start_at) ) 
                and ( strtotime($block_time) <= strtotime($thing->end_at) ) ) {

 $this->thing->log( 'Agent "Block" found ' . $this->blockTime($block_time) . ' in existing block #' . $thing->index . ' (' . $this->blockTime($thing->start_at) . " " . $thing->quantity . ').');
                //$this->block_thing->flagRed();
$match = true;  
                break; //Take first matching block.

            } else {

 $this->thing->log( 'Block #' . $thing->index . ' (' . $this->blockTime($thing->start_at) . " - " . $this->blockTime($thing->end_at) . " )");
 //              echo "green - no existing blcok found in the db";             
                //$this->block_thing->flagGreen();
            }

        }





        // Set-up empty block variables.
        $this->flagposts = array();
        $this->trains = array();
        $this->bells = array();

        // If it drops through as Green, then no blocks matched the current time.
        if ($match == false) {
            // No valid block found, so make a block record in current Thing
            // and set flag to Green ie accepting trains.

            $this->block_thing = $this->thing;

            $this->index = 0;
            $this->start_at = $this->current_time;
            $this->quantity = 22;
            $this->available = 22;



            $this->thing->log('Agent "Block" did not find a valid block at blocktime ' . $this->blockTime($block_time) . "." );

            //$this->makeBlock($this->current_time, "x");

        } else {

            $this->thing->log($this->agent_prefix . "found a valid block.");

            // Red Block Thing - There is a current operating block on the stack.
            // Load the block details into this Thing.

            $this->block_thing = $thing;

            $this->index = $thing->index;
            $this->start_at = $thing->start_at;
            $this->quantity = $thing->quantity;
            $this->available = $thing->quantity;
//            $this->end_at = $temp_thing->end_at;

          //  $this->getAvailable(); //Update the availability

          //  $this->end_at = $this->thing->json->time(strtotime($this->start_at . " " . $this->quantity . " minutes"));

        }

        $this->getAvailable();
        $this->getEndat();

//            $this->block_thing = $thing;

            $this->block_thing->json->setField("associations");
            $this->associations = $this->block_thing->json->readVariable( array("agent") );

            foreach ($this->associations as $association_uuid) {

                $association_thing = new Thing($association_uuid);

                $association_thing->json->setField("variables");
                $this->flagposts[] = $association_thing->json->readVariable( array("flagpost") );

                $association_thing->json->setField("variables");
                $this->trains[] = $association_thing->json->readVariable( array("train") );

                $association_thing->json->setField("variables");
                $this->bells[] = $association_thing->json->readVariable( array("bell") );

           



            // Go through and build block
            // Flag posts

            // Slots

            


        }
//exit();
        return;
    }

    function dropBlock() {
        $this->thing->log($this->agent_prefix . "was asked to drop a block.");

        //$this->get(); No need as it ran on start up.

        // If it comes back false we will pick that up with an unset block thing.

        if (isset($this->block_thing)) {
            $this->block_thing->Forget();
            $this->block_thing = null;
        }

        $this->get();
 
       return;
    }

    function makeBlock($run_at = null, $quantity = null, $available = null) {



        if (($quantity == null) and ($this->quantity == null)) {
            $quantity = 105;
        } 

        if (($quantity !=null) and ($this->quantity != null) ) {
            //$quantity = $this->quantity;
        }

        if (($available == null) and ($this->available == null)) {
            $available = 100; 
        } elseif ($this->available != null) {
            $available = $this->available; 
        }

        if (($run_at == null) and ($this->run_at == null)) {
            $run_at = $this->current_time; 
        } 


        if (($this->run_at != null) and ($run_at != null)) {
            // Let run_at stand. 
        }


        if ($run_at == null) {
            $run_at = $this->current_time; 
        }



$this->thing->log('Agent "Block" will make a Block with ' . $this->blockTime($run_at) . " " . $quantity . " " . $available . ".");
//exit();
        // $quantity, $run_at, $available set to preferred values.


        // Check that the shift is okay for making blocks.

//        require_once '/var/www/html/stackr.ca/agents/shift.php';
//        $shift_thing = new Shift($this->thing);
//        $shift_state = strtolower($this->thing->log($shift_thing->thing_report['keyword']));

$shift_override == true;

        if ( ($shift_state == "off") or
                ($shift_state == "null") or
                ($shift_state == "") or
                ($shift_override) ){

            // Only if the shift state is off can we 
            // create blocks on the fly.

            // Otherwise we needs to make trains to run in the block.

            $this->thing->log($this->agent_prefix . "found that this is the Off shift.");

            // So we can create this block either from the variables provided to the function,
            // or leave them unchanged.


            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            $this->start_at = $run_at;
            $this->quantity = $quantity;
            $this->getEndat();
            $this->getAvailable();
           



            $this->block_thing = $this->thing;


        } else {

            $this->thing->log($this->agent_prefix . " checked the shift state: " . $shift_state . ".");
            // ... and decided there was already a shift running ...
            $this->start_at = "meep"; // We could probably find when the shift started running.
            $this->quantity = 0;
            $this->available = 0;
            $this->end_at = "meep";

        }


        // $this->start_at
        // $this->end_at
        // $this->quantity
        // $this->available
        // Have all be established.

        //$this->getEndat();

        $this->set();

        //$this->block_thing = $this->thing;

        $this->thing->log('Agent "Block" found a run_at and a quantity and made a Block.');

    }



    function blockTime($input = null) {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $block_time = "x";
            return $block_time;
        }


        $t = strtotime($input_time);

        $this->hour = date("H",$t);
        $this->minute =  date("i",$t);

        $block_time = $this->hour . $this->minute;

        if ($input == null) {$this->block_time = $block_time;}

        return $block_time;

        //exit();


    }

    function getEndat() {

        if (($this->start_at != "x") and ($this->quantity != "x")) {
            $this->end_at = $this->thing->json->time(strtotime($this->start_at . " " . $this->quantity . " minutes"));
        } else {
            $this->end_at = "x";
        }


        return $this->end_at;
    }

    function getAvailable() {

        // This proto-typical block manages (available) time.

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

//echo $this->blockTime($this->start_at) . "<br>";
//echo $this->quantity . "<br>";
//echo "<br>";
//echo $this->blockTime($this->end_at) . "<br>";
//echo $this->available . "<br>";

        //    
        //if ($this->available < 0) {$this->available = 0;}
        //
        $this->thing->log('Agent "Block" identified ' . $this->available . ' resource units available.');
 
//exit();

    }

    function extractHeadcodes($input) {

        if (!isset($this->headcodes)) {
            $this->head_codes = array();
        }

        $pattern = "|\d[A-Za-z]{1}\d{2}|";

        preg_match_all($pattern, $input, $m);
//        return $m[0];
        $arr = $m[0];
        //array_pop($arr);

        return $arr;


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

    function trains() {

        

    }

    function read()
    {
        $this->thing->log("read");

//        $this->get();
        return $this->available;
    }



 function addBlock() {
   //     //$this->thing->log("read subject nextblock");
        $this->makeBlock();
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
        $this->thing->choice->Create('block', $this->node_list, 'red');
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

//        echo "start";
//        echo $this->previous_state;

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
/*
		// Stop
		// Respond with elapsed time.
		// Start
		// Respond with elapsed time.
		// Reset
		// Set elapsed time to 0.

		switch ($this->requested_state) {
 		   case 'stop':
        $this->stop();
        break;
    case 'start':
        $this->start();
        break;
    case 'reset':
        $this->reset();
        break;

    case 'split':
        $this->split();
        break;


    default:
       $this->read();
}
*/
		// Generate email response.

		$to = $this->thing->from;
		$from = "block";

		//echo "<br>";

//		$choices = $this->thing->choice->makeLinks($this->state);
//		$this->thing_report['choices'] = $choices;
		//echo "<br>";
		//echo $html_links;
        $this->makeChoices();
//$interval = date_diff($datetime1, $datetime2);
//echo $interval->format('%R%a days');

$available = $this->thing->human_time($this->available);

if (!isset($this->index)) {
    $index = "0";
} else {
    $index = $this->index;
}


        $this->makeSMS();

        //if (!isset(

//echo $sms_message;

		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $this->choices['link'] . '].';
        if (isset($this->state)) {
		    $test_message .= '<br>Block state: ' . $this->state . '<br>';
        }
		$test_message .= '<br>' . $this->sms_message;

		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

        $test_message .= '<br>start_at: ' . $this->start_at;
        $test_message .= '<br>end_at: ' . $this->end_at;


//		$test_message .= '<br>Requested state: ' . $this->requested_state;

			$this->thing_report['email'] = $this->sms_message;
			$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;


                        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->makeWeb();


$this->thing_report['help'] = 'This is a block.';



		return;


	}

    public function makeSMS()
    {
        $s = "RED";
        $sms_message = "BLOCK " . $this->index ." | " . round($this->available/60,0) . " minutes | " . $s;
        //$sms_message .= " | " . $this->blockTime($this->start_at); 
        //$sms_message .= " | Block ends " . $this->blockTime($this->end_at);
        $sms_message .= " | from " . $this->blockTime($this->start_at) . " to " . $this->blockTime($this->end_at);
        $sms_message .= " | now " . $this->blockTime();
        $sms_message .= " | nuuid " . strtoupper($this->block_thing->nuuid);

    switch($this->index) {
        case null:
//          $sms_message =  "BLOCK | No block scheduled. | TEXT ADD BLOCK";
            $sms_message =  "BLOCK | No active block found. | TEXT BLOCK <four digit clock> <1-3 digit runtime>";
            break;

        case '1':
          $sms_message .=  " | TEXT BLOCK <four digit clock> <1-3 digit runtime>";
            //$sms_message .=  " | TEXT ADD BLOCK";
            break;
        case '2':
            $sms_message .=  " | TEXT DROP BLOCK";
            //$sms_message .=  " | TEXT BLOCK";
            break;
        case '3':
            $sms_message .=  " | TEXT BLOCK";
            break;
        case '4':
            $sms_message .=  " | TEXT BLOCK";
            break;
        default:
            $sms_message .=  " | TEXT ?";
            break;
    }

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
        return;
    }

    public function makeChoices()
    {
        if (!isset($this->state)) {
            $state = "block";
        } else {
            $state = $this->state;
        }

        $this->thing->choice->Create('channel', $this->node_list, $state);
        $this->choices = $this->thing->choice->makeLinks($state);
        $this->thing_report['choices'] = $this->choices;


    }

    public function makeWeb()
    {
        $web = "<b>Block Agent</b><br>";


    switch($this->index) {
        case null:
//          $sms_message =  "BLOCK | No block scheduled. | TEXT ADD BLOCK";
            $web .=  "No active block found.";
            break;

        default:
            $web .= "from ". $this->blockTime($this->start_at) . " ";
            $web .= "to " . $this->blockTime($this->end_at) . "<br>";

            $web .= "quantity available: " .  $this->quantity . "<br>";
            $web .= "available resource: ". $this->available . "<br>";
        }
        $this->thing_report['web'] = $web;


    }


    public function readSubject() 
    {
        $this->response = null;
        $this->num_hits = 0;
        // Extract uuids into
//        $uuids_in_input

//        $headcodes_in_input




        $keywords = array('next', 'accept', 'clear', 'drop','add');

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

$head_codes = $this->extractHeadcodes($input);


if (count($head_codes) == 1) {
       $this->head_code = $head_codes[0];
        $this->thing->log('Agent "Block" found a headcode ' . $this->head_code .'.');
}


$uuids = $this->extractUuids($input);

$this->thing->log($this->agent_prefix . " counted " . count($uuids) . " uuids.");

//exit();


        $pieces = explode(" ", strtolower($input));


		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'block') {

                //echo "readsubject block";
                $this->read();
                return;
            }
/*
                        if ( $this->thing->choice->isValidState($input) ) {

echo "valid state";
				$this->requested_state = $input;
                                $this->thing->choice->Choose($input);
                               
                                return $input;
                        }
*/




// Drop through
//                        return "Request not understood";

                }

//echo "meepmeep";

    // Extract runat signal
    $matches = 0;
    foreach ($pieces as $key=>$piece) {

        if ((strlen($piece) == 4) and (is_numeric($piece))) {
            $run_at = $piece;
            $matches += 1;
        }
    }

    if ($matches == 1) {
        $this->run_at = $run_at;
        $this->num_hits += 1;
        $this->thing->log('Agent "Block" found a "run at" time of "' . $this->run_at . '".');
    }

    // Extract runtime signal
    $matches = 0;
    foreach ($pieces as $key=>$piece) {

        if (($piece == 'x') or ($piece == 'z')) {
            $this->quantity = $piece;
            $matches += 1;
            continue;
        }

        if (($piece == '5') or ($piece == '10')
            or ($piece == '15')
            or ($piece == '20')
            or ($piece == '25')
            or ($piece == '30')
            or ($piece == '45')
            or ($piece == '55')
            or ($piece == '60')
            or ($piece == '75')
            or ($piece == '90')

            ) {

            $this->quantity = $piece;
            $matches += 1;
            continue;
        }

        if ((strlen($piece) == 3) and (is_numeric($piece))) {
            $this->quantity = $piece; //3 digits is a good indicator of a runtime in minutes
            $matches += 1;
            continue;
        }

        if ((strlen($piece) == 2) and (is_numeric($piece))) {
            $this->quantity = $piece;
            $matches += 1;
            continue;
        }

        if ((strlen($piece) == 1) and (is_numeric($piece))) {
            $this->quantity = $piece;
            $matches += 1;
            continue;
        }


    }

    if ($matches == 1) {
        $this->quantity = $piece;
        $this->num_hits += 1;
        //$this->thing->log('Agent "Block" found a "run time" of ' . $this->quantity .'.');
    }


/*
    if ((isset($this->run_time)) and (isset($this->run_at))) {
        // Good chance with both these set that asking for a new
        // block to be created, or to override existing block.
        $this->thing->log('Agent "Block" found a run time.');

        $this->nextBlock();
        return;
    }
*/
    foreach ($pieces as $key=>$piece) {
        foreach ($keywords as $command) {
            if (strpos(strtolower($piece),$command) !== false) {

                switch($piece) {
/*
                                                case 'stopwatch':    

                                                        if ($key + 1 > count($pieces)) {
                                                                //echo "last word is stop";
                                                                $this->stop = false;
                                                                return "Request not understood";
                                                        } else {
                                                                //echo "next word is:";
                                                                //var_dump($pieces[$index+1]);
                                                                $command = $pieces[$key+1];

								if ( $this->thing->choice->isValidState($command) ) {
                                                                	return $command;
								}
                                                        }
                                                        break;
*/
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
        $this->thing->log("read subject nextblock");
        $this->nextBlock();
        break;

   case 'drop':
   //     //$this->thing->log("read subject nextblock");
        $this->dropBlock();
        break;


   case 'add':
   //     //$this->thing->log("read subject nextblock");
        $this->makeBlock();
        break;


    default:
        //$this->read();                                                    //echo 'default';

                                        }

                                }
                        }

                }


// Check whether Block saw a run_at and/or run_time
// Intent at this point is less clear.  But Block
// might have extracted information in these variables.

// $uuids, $head_codes, $this->run_at, $this->run_time

if ( (count($uuids) == 1) and (count($head_codes) == 1) and (isset($this->run_at)) and (isset($this->quantity)) ) {

    // Likely matching a head_code to a uuid.

}


if ( (isset($this->run_at)) and (isset($this->quantity)) ) {

//$this->thing->log('Agent "Block" found a run_at and a run_time and made a Block.');
    // Likely matching a head_code to a uuid.
    $this->makeBlock($this->run_at,$this->quantity);
    return;
}

//    if ((isset($this->run_time)) and (isset($this->run_at))) {
        // Good chance with both these set that asking for a new
        // block to be created, or to override existing block.
//        $this->thing->log('Agent "Block" found a run time.');

//        $this->nextBlock();
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
                                                //echo "sum";
                                }

                                foreach ($aliases[$discriminator] as $alias) {

                                        if ($word == $alias) {
                                                $count[$discriminator] = $count[$discriminator] + 1;
                                                $total_count = $total_count + 1;
                                                //echo "sum";
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


                        //$this->thing->log('Agent "Block" normalized discrimators "' .  $normalized . '"');


                if ($delta >= $minimum_discrimination) {
                        //echo "discriminator" . $discriminator;
                        return $selected_discriminator;
                } else {
                        return false; // No discriminator found.
                } 

                return true;
        }

}

?>

