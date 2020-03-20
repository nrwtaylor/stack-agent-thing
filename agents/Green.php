<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Green extends Agent
{

    // This is a color.

    // Red - Not available
    // Green - Slot allocated
    // Yellow - Next signal Red.
    // Double Yellow - Next signal Yellow

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {

//        $this->start_time = microtime(true);

        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->keyword = "mordok";

        $this->thing = $thing;

        $this->start_time = $this->thing->elapsed_runtime();

        $this->thing_report['thing'] = $this->thing->thing;


        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        $this->num_hits =0;

        $this->agent_prefix = 'Agent "Green" ';


        $this->keywords = array('green');

        $this->current_time = $this->thing->json->time();

        $this->thing->log('Agent "Green" running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log('Agent "Green" received this Thing "'.  $this->subject . '".');

//                $this->default_run_time = $this->current_time;
//                $this->negative_time = true;

        // Loads in Train variables.

        $this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        $this->flag_thing = new Flag($this->thing); // Pass without agent instruction to generate message.

		//$this->readSubject();
        $this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

//		$this->respond();
        //$this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );


        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

		$this->thing->log($this->agent_prefix . 'completed.');

        $this->thing_report['log'] = $this->thing->log;


    }

    function set()
    {

        // A block has some remaining amount of resource and 
        // an indication where to start.


        // This makes sure that
        if (!isset($this->train_thing)) {
            $this->train_thing = $this->thing;
        }

        if ($this->requested_state == null) {
            $this->requested_state = $this->state;
        }


        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }



        // Update calculated variables.
        $this->getAvailable();

        $this->variables_agent->setVariable("state", $requested_state);
        $this->variables_agent->setVariable("head_code", $this->head_code);

        $this->variables_agent->setVariable("alias", $this->alias);
        $this->variables_agent->setVariable("index", $this->index);

        $this->variables_agent->setVariable("run_at", $this->run_at);
        $this->variables_agent->setVariable("quantity", $this->quantity);

        $this->variables_agent->setVariable("available", $this->available);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

        $this->variables_agent->setVariable ( "route" , $this->route) ;
        $this->variables_agent->setVariable ( "consist" , $this->consist) ;
        $this->variables_agent->setVariable ( "runtime", $this->runtime) ;

        $this->thing->choice->save('train', $this->state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        return;
    }

    function nextTrain()
    {

        $this->thing->log("next train");
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


    function get($train_time = null)
    {

        // Loads current block into $this->block_thing

        $match = false;

        if ($train_time == null) {
            $train_time = $this->current_time;
        }

        $train_things = array();
        // See if a block record exists.

        $findagent_thing = new Findagent($this->thing, 'train');

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

$this->thing->log('Agent "Train" found ' . count($findagent_thing->thing_report['things']) ." Train Agent Things." );

        $this->max_index =0;

        foreach ($findagent_thing->thing_report['things'] as $train_thing) {

            $thing = new Thing($train_thing['uuid']);

            $thing->json->setField("variables");

            $thing->index = $thing->getVariable("train", "index");
            if ($thing->index > $this->max_index) {$this->max_index = $thing->index;}

            $thing->alias = $thing->getVariable("train", "alias");

            $thing->run_at = $thing->getVariable("train", "run_at");
            $thing->quantity = $thing->getVariable("train", "quantity");
            $thing->available = $thing->getVariable("train", "available");
            $thing->refreshed_at = $thing->getVariable("train", "refreshed_at");


            $thing->head_code = $thing->getVariable("train", "head_code");
            $thing->route = $thing->getVariable("train", "route");
            $thing->consist = $thing->getVariable("train", "consist");
            $thing->runtime = $thing->getVariable("train", "runtime");



            // Calculate the end time.
            if ($thing->quantity > 0) {
                $thing->end_at = $this->thing->json->time(strtotime($thing->run_at . " " . $thing->quantity . " minutes"));
            } else {
                $thing->end_at = null;
            }
            
            //// If the train time is in the run period of the train
            //// then this is a valid train to be running right now.
            if ( ( strtotime($train_time) >= strtotime($thing->run_at) ) 
                and ( strtotime($train_time) <= strtotime($thing->end_at) ) ) {

 $this->thing->log( 'Agent "Train" found ' . $this->trainTime($train_time) . ' in existing train #' . $thing->index . ' (' . $this->trainTime($thing->run_at) . " " . $thing->quantity . ').');
                //$this->block_thing->flagRed();
$match = true;  
                break; //Take first matching block.   Because this will be the last referenced train.

            } else {

 $this->thing->log($this->agent_prefix . 'train is ' . "" . $thing->head_code . "#" . $thing->index . ' ' . $this->trainTime($thing->run_at) . "+" . $thing->runtime . " =" . $this->trainTime($thing->end_at) . " )");
 //              echo "green - no existing blcok found in the db";             
                //$this->block_thing->flagGreen();
            }

        }





        // Set-up empty block variables.
        $this->flagposts = array();
        $this->trains = array();
        $this->bells = array();

        // If it drops through as Green, then no blocks matched the current time.
        if ($match != false) {

            $this->thing->log($this->agent_prefix . "found a valid train.");

            // Red Block Thing - There is a current operating block on the stack.
            // Load the block details into this Thing.

            $this->train_thing = $thing;

//$this->variables_agent = new Variables($thing, "variables train " . $this->from);

            // No nead to do this because the read agent will do.
            $this->index = $thing->index;
            $this->alias = $thing->alias;
            $this->head_code = $thing->head_code;
            $this->run_at = $thing->run_at;
            $this->quantity = $thing->quantity;
            $this->available = $thing->quantity;

            $this->route = $thing->route;
            $this->consist = $thing->consist;
            $this->runtime = $thing->runtime;

//            $this->getAvailable();
//            $this->getEndat();

//            $this->block_thing = $thing;

            $this->train_thing->json->setField("associations");
            $this->associations = $this->train_thing->json->readVariable( array("agent") );

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
            return $this->train_thing;
        }


            // No valid train found, so make a block record in current Thing
            // and set flag to Green ie accepting trains.
        $this->thing->log('Agent "Train" did not find a valid train at traintime ' . $this->trainTime($train_time) . "." );


 //       $this->max_index =0;

        $train_thing = $findagent_thing->thing_report['things'][0];

  //      var_dump($train_thing);
//exit(); 

        if (isset($train_thing)) {

            $thing = new Thing($train_thing['uuid']);

            $thing->json->setField("variables");

            $thing->index = $thing->getVariable("train", "index");
            if ($thing->index > $this->max_index) {$this->max_index = $thing->index;}

            $thing->alias = $thing->getVariable("train", "alias");

            $thing->run_at = $thing->getVariable("train", "run_at");
            $thing->quantity = $thing->getVariable("train", "quantity");
            $thing->available = $thing->getVariable("train", "available");
            $thing->refreshed_at = $thing->getVariable("train", "refreshed_at");


            $thing->head_code = $thing->getVariable("train", "head_code");
            $thing->route = $thing->getVariable("train", "route");
            $thing->consist = $thing->getVariable("train", "consist");
            $thing->runtime = $thing->getVariable("train", "runtime");



            // Calculate the end time.
            if ($thing->quantity > 0) {
                $thing->end_at = $this->thing->json->time(strtotime($thing->run_at . " " . $thing->quantity . " minutes"));
            } else {
                $thing->end_at = null;
            }


            $this->train_thing = $thing;

//            $this->index = 0;
//            $this->getHeadcode();
            //$this->head_code = "0Z10";
            //$this->start_at = $this->current_time;
            //$this->quantity = 22;
            //$this->available = 22;

//            $this->makeTrain($this->current_time, 45, 22);

 $this->thing->log( 'Agent "Train" got last train ' . $this->trainTime($train_time) . ' in existing train #' . $thing->index . ' (' . $this->trainTime($thing->run_at) . " " . $thing->quantity . ').');

            //$this->makeBlock($this->current_time, "x");

            return $this->train_thing;

        }

        $this->train_thing = $this->thing;
        $this->train_thing->index = $this->max_index + 1;
        $this->train_thing->head_code = "0Z" . rand(50,99);
        $this->run_at = $this->current_time;
        $this->quantity = 22;

        return $this->train_thing;

        return false;
    }

    function dropTrain() {
        $this->thing->log($this->agent_prefix . "was asked to drop a train.");

        //$this->get(); No need as it ran on start up.

        // If it comes back false we will pick that up with an unset block thing.

// So this is currently dropping the current Thing not the Train
// I think.
// So take it out of the command roster. 1803 12 Nov

        // Dropping a Train means to 
        // Stop running the current train.

        // And if no Train is running?
        // Is there a concept of a scheduled train?

        if (isset($this->train_thing)) {
            $this->train_thing->Forget();
            $this->train_thing = null;
        }

        $this->get();
 
       return;
    }

    function runTrain($headcode = null) {
        //$this->head_code = "0Z" . $this->index;
        $n = rand(1,49);
        $n = str_pad($n, 2, '0', STR_PAD_LEFT);

        $this->head_code = "5Z".$n;






        //if ($this->quantity == 0) {$this->quantity = 45;}
        $this->quantity = 45;
        $this->getAvailable();

        $this->makeTrain($this->current_time, $this->quantity, $this->available);

        $this->state = "running";

        //$this->makeTrain($this->current_time, $this->quantity, $this->available);

    }

    function getAlias() {

        if (isset($this->alias)) {
            return $this->alias;
        }

        $this->aliases = array("Logan's run", "Kessler Run", "Orient Express", "Pineapple Express",
            "Dahjeeling Express", "Flying Scotsman", "Crazy Train" );

        $this->alias_thing = new Alias($this->train_thing, 'alias');

        $this->alias = $this->alias_thing->alias;

        if ($this->alias == false) {$this->alias = array_rand($this->aliases);}

//           $this->alias = "Orient Express";
        return $this->alias;
    }

    function makeTrain($run_at = null, $quantity = null, $available = null) {

//echo $run_at . "<br>";
//echo $quantity . "<br>";


        // Load in Consist, Quantity and Route from Headcode
        // will also load in the headcode, if not already loaded.
 

        $this->getHeadcode();
     //$this->head_code = "0Z" . str_pad($this->index + 11,2, '0', STR_PAD_LEFT);
        //$this->alias = "Logan's run";
        $this->getAlias();
        $this->getConsist();
        $this->getQuantity(); // which is runtime
        $this->getRoute();

        $this->state = "stopped";

        if (($quantity == null) and ($this->quantity == null)) {
            $quantity = 45;
        } 

        if (($quantity !=null) and ($this->quantity != null) ) {
            //$quantity = $this->quantity;
        }

        if (($available == null) and ($this->available == null)) {
            $available = 22; 
        } elseif ($this->available != null) {
            $available = $this->available; 
        }

        if (($run_at == null) and ($this->run_at == null)) {
            $run_at = $this->current_time; 
            $this->run_at = $run_at;
        } 


        if (($this->run_at != null) and ($run_at != null)) {
            // Let run_at stand. 
        }


        if ($run_at == null) {
            $run_at = $this->current_time;
            $this->run_at = $run_at; 
        }



        $this->thing->log('Agent "Train" will make a Train with ' . $this->trainTime($run_at) . " " . $quantity . " " . $available . ".");
        // $quantity, $run_at, $available set to preferred values.


        // Check that the shift is okay for making blocks.

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

            $this->run_at = $run_at;
            $this->quantity = $quantity;
            $this->getEndat();

            if ($this->quantity > 0) {
                $this->run_at = $run_at;
                $this->quantity = $quantity;
                $this->getEndat();
            } else {
                $this->run_at = $run_at;
                $this->quantity = -$quantity;
                $this->getRunat();
            }




            $this->getAvailable();

            $this->train_thing = $this->thing;

        } else {

            $this->thing->log($this->agent_prefix . " checked the shift state: " . $shift_state . ".");
            // ... and decided there was already a shift running ...
            $this->run_at = "meep"; // We could probably find when the shift started running.
            $this->quantity = 0;
            $this->available = 0;
            $this->end_at = "meep";

        }


        // So at this point $this->start_at, $this->end_at, $this->quantity, 
        // $this->available, have all be established.

        //$this->getEndat();

        $this->set();

        //$this->block_thing = $this->thing;

        $this->thing->log('Agent "Train" found a run_at and a quantity and made a Train.');

    }


    function trainTime($input = null) {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $train_time = "x";
            return $train_time;
        }


        $t = strtotime($input_time);

        //echo $t->format("Y-m-d H:i:s");
        $this->hour = date("H",$t);
        $this->minute =  date("i",$t);

        $train_time = $this->hour . $this->minute;

        if ($input == null) {$this->train_time = $train_time;}

        return $train_time;

        //exit();


    }


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

        // See if the thing variable is found

        if (isset($this->train->$variable_name)) {
            $this->$variable_name = $this->train->$variable_name;

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




    function getEndat()
    {
        if (!isset($this->events)) {$this->extractEvents($this->subject);}


// If there is only one time, it is the run_at time
//        if (count($this->events) == 1) {
//            $this->run_at = $run_at;
//            $this->num_hits += 1;
//            return $this->run_at;
//        } 


        if (count($this->events) == 2) {
            $this->end_at = $this->events[1];
            $this->num_hits += 2;
            return $this->end_at;
        }


        if ((isset($this->run_at)) and (isset($this->quantity))) {
            if (($this->run_at != "x") and ($this->quantity != "x")) {
                $this->end_at = $this->thing->json->time(strtotime($this->run_at . " + " . $this->quantity . " minutes"));
            } else {
                $this->end_at = "x";
            }
            return $this->end_at;
        }

        $this->end_at = "x";
        return $this->end_at;
    }

    function getRunat()
    {

        if (!isset($this->events)) {$this->extractEvents($this->subject);}

        if (count($this->events) == 1) {
            $this->run_at = $this->events[0];
            $this->num_hits += 1;
            return $this->run_at;
        }

        if (count($this->events) == 2) {
            $this->run_at = $this->events[0];
            $this->num_hits += 2;
            return $this->run_at;
        }


        //if (($this->end_at != "x") and ($this->quantity != "x")) {
        //    $this->run_at = $this->thing->json->time(strtotime($this->end_at . " -" . $this->quantity));
        //} else {
        //    $this->run_at = "x";
        //}

        $this->run_at = $this->trainTime();

        return $this->run_at;
    }


    function getAvailable()
    {

        // This proto-typical block manages (available) time.
        // From start_at and current_time we can calculate elapsed_time.

        if (!isset($this->end_at)) {
            $this->getEndat();
        }

        if (!isset($this->run_at)) {
            $this->getRunat();
        }

        if (strtotime($this->current_time)  < strtotime($this->run_at)) {
            $this->available = strtotime($this->end_at) - strtotime($this->run_at);
           // $this->available = $this->quantity;
        } else {
            $this->available = strtotime($this->end_at) - strtotime($this->current_time);
        }

        $this->thing->log('Agent "Train" identified ' . $this->available . ' resource units available.');
    }


    function getQuantity()
    {
        // Because an Agent hasn't been written yet.
        // This will kind of cover Things until then.

        if (!isset($this->headcode_thing)) {
            $this->getHeadcode();
        }

        $quantity = $this->headcode_thing->quantity; //which is runtime

        // Which can be <number>, "X" or "Z".

        if (strtoupper($quantity) == "X") {
            // Train must specifiy runtime.
            if (!isset($this->quantity)) {$this->quantity = "X";}
        }

        if (strtoupper($quantity) == "Z") {
            // Train must specifiy runtime.
            $this->quantity = "Z";
        }

        if (is_numeric($quantity)) {
            // Train must specifiy runtime.
            $this->quantity = $quantity;
        }

        return $this->quantity;
    }

    function getConsist() 
    {
        if (!isset($this->headcode_thing)) {
            $this->getHeadcode();
        }

        $consist = $this->headcode_thing->consist; 

        $this->consist_thing = new Consist($this->variables_agent->thing, 'consist');
        $this->consist = $this->consist_thing->variable; 

        // $this->consist = "Nn";
        // $consist = "X";

        if (!isset($this->consist)) {
            $this->consist = $consist;
            return $this->consist; 
        }

        // First see if the planned consist appears in the headcode
        // consist.

        if (strstr($consist, $this->consist)) {
            // Then "Nn" appears in the headcode consist.
            $this->consist = $consist;
            return $this->consist;
        }

        // So "Nn" doesn't appear in the consist.

        if (strstr($consist, "Z")) {
            // Then "Z" appears in the headcode consist.
            $t = "";
            $match = false;
            foreach (str_split($consist,1) as $l) {
                if (($l == "Z") and ($match == false)) {
                    $t = $t . $this->consist . "Z";
                    $match = true;
                } else {
                    $t = $t . $l;
                }
            }
            $this->consist = $t;
            return $this->consist;
        }

        if (strstr($consist, "X")) {
            // Then "Z" appears in the headcode consist.
            $t = "";
            $match = false;
            foreach (str_split($consist,1) as $l) {
                if (($l == "X") and ($match == false)) {
                    $t = $t . $this->consist . "X";
                    $match = true;
                } else {
                    $t = $t . $l;
                }
            }
            $this->consist = $t;
            return $this->consist;
        }

        return true; // Consist is not compatable with headcode.
    }

    function getRoute() {

        if (!isset($this->headcode_thing)) {
            $this->getHeadcode();
        }

        $route = $this->headcode_thing->route; //which is runtime

//      $this->route = "Eton>Triumph";
//$route = "Eton>Gilmore>Hastings>Triumph";

        if (!isset($this->route)) {
            $this->route = $route;
            return $this->route; 
        }

        // First see if the planned consist appears in the headcode
        // consist.


        $train_places = explode(">", $this->route);
        $head_code_places = explode(">", $route);
        $valid = true;

        foreach ($train_places as $train_place) {
            $match = false;
            foreach($head_code_places as $head_code_place) {
                if ($train_place == $head_code_place) {
                    $match = true;
                }
            }
            if ($match == false) {$this->route = true; return $this->route;}
        }

        $this->route = $route;
        return $this->route;
    }

    function getHeadcode() 
    {
        // This will trigger a request from the Agent
        // to return the current Headcode.

        // Even if $this->head_code is set, it still needs to pull it by a stack call.
        // But no reason the Headcode agent can't keep track of this.
        $this->headcode_thing = new Headcode($this->variables_agent->thing, 'headcode '. $this->input);
        $this->head_code = $this->headcode_thing->head_code;

//        if ($this->head_code == false) { // Didn't return a useable headcode.
            // So assign a 'special'.
//            $this->head_code = "0Z" . str_pad($this->index + 11,2, '0', STR_PAD_LEFT);
//        }

        // Not sure about the direct variable
        // probably okay if the variable is renamed to variable.  Or if $headcode_thing
        // resolves to the variable.

        return $this->head_code;
    }

    function getFlag() 
    {
        $this->flag_thing = new Flag($this->variables_agent->thing, 'flag');
        $this->flag = $this->flag_thing->state; 

        return $this->flag;
    }

    function setFlag($colour) 
    {

        $this->flag_thing = new Flag($this->variables_agent->thing, 'flag '.$colour);
        $this->flag = $this->flag_thing->state; 

        return $this->flag;
    }

    function extractUuids($input)
    {
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
        return $this->available;
    }



    function addTrain() {
        $this->makeTrain();
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
        $this->thing->choice->Create('train', $this->node_list, 'red');
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

	public function respond() {

		// Thing actions
        // At some point this is where the 
        // Train can be set to run until concluded.
        // For now flag as Green to 

		$this->thing->flagGreen();
		// Generate email response.


		$to = $this->thing->from;
		$from = "train";

		//echo "<br>";

        if (isset($this->requested_state)) {
            $this->state = $this->requested_state;
        } else {
            $this->state = $this->previous_state;
        }

		$choices = $this->thing->choice->makeLinks($this->state);
		$this->thing_report['choices'] = $choices;



        //$interval = date_diff($datetime1, $datetime2);
        //echo $interval->format('%R%a days');

        $available = $this->thing->human_time($this->available);

        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index;
        }

        //$s = $this->block_thing->state;
        if (!isset($this->flag)) {
            $this->flag = strtoupper($this->getFlag());
        }


//        if ($this->train_thing == false) {





		$sms_message = "TRAIN ";


        if (isset($this->alias)) {
            $sms_message .= '"' . strtoupper($this->alias). '"';
        } else if (isset($this->head_code)) {
            $sms_message .= strtoupper($this->head_code);
        } else {
            $sms_message .= "IVOR";
        }

//var_dump($this->available);

        if ($this->train_thing == false) {
            $sms_message .= " | train not running";
        } else {

            if (isset($this->available)) {
                $sms_message .= " | " . round($this->available/60,0) . " minutes remaining";
            }
        }

        if (isset($this->flag)) {
            $sms_message .= " | signal " . strtoupper($this->flag);
        }


        $complexity = 1;
        if ($complexity > 2) {
//        $sms_message .= " | index " . strtoupper($this->index);

//        $sms_message .= " | $this->route;
        $route_description = $this->route . " [" . $this->consist . "] " . $this->quantity;
        $sms_message .= " | " . $route_description;

        //$sms_message .= " | " . $this->blockTime($this->start_at); 
        //$sms_message .= " | Block ends " . $this->blockTime($this->end_at);
        $sms_message .= " | " . $this->trainTime($this->run_at) . "-" . $this->trainTime($this->end_at);
        $sms_message .= " | now " . $this->trainTime();
//        $sms_message .= " | nuuid " . strtoupper($this->train_thing->nuuid);
        $sms_message .= " | nuuid " . substr($this->variables_agent->variables_thing->uuid,0,4); 
  

        $run_time = microtime(true) - $this->start_time;
        $milliseconds = round($run_time * 1000);

        $sms_message .= " | processor time " . number_format($milliseconds) . 'ms';


        $head_code = strtoupper($this->head_code);
        if (!$this->thing->isData($head_code)) {$head_code = "X";}

        $run_at = $this->trainTime($this->run_at);
        if (!$this->thing->isData($run_at)) {$run_at = "X";}

        if (!isset($this->route)) {
            $route = "X";
        } else {
            $route = $this->route;
        }

        if (!isset($this->runtime)) {
            $runtime = "X";
        } else {
            $runtime = $this->runtime;
        }

        }


        if ($this->train_thing == false) {
            $sms_message .= " | MESSAGE RUN TRAIN";
        } else {
            $sms_message .= " | MESSAGE ?";
        }


    // This below section needs to be refactored.
    // as Close Message.
    $postfix = "no";
    if ($postfix == "yes") {
    switch($this->index) {
        case null:
            $sms_message =  "TRAIN | Next scheduled Train will be.";
            $sms_message .= " | Headcode  " . $this->head_code;
            $sms_message .= " | Route " . $this->route;
            $sms_message .= " | Consist " . $this->consist;
            $sms_message .= " | Start at " . $this->run_at;
            $sms_message .= " | Runtime " . $this->quantity;
            //$sms_message .= " | nuuid " . strtoupper($this->train_thing->nuuid);
            $sms_message .= " | TEXT TRAIN ";
            if ($head_code == "X") {$sms_message .= "<head code>";}

            break;

        case '1':
          $sms_message .=  " | TEXT TRAIN <four digit clock> <1-3 digit runtime>";
            //$sms_message .=  " | TEXT ADD BLOCK";
            break;
        case '2':
            $sms_message .=  " | TEXT DROP TRAIN";
            //$sms_message .=  " | TEXT BLOCK";
            break;
        case '3':
            $sms_message .=  " | TEXT TRAIN";
            break;
        case '4':
            $sms_message .=  " | TEXT TRAIN";
            break;
        default:
            $sms_message .=  " | TEXT ?";
            break;
    }
        }


        //if (!isset(

//echo $sms_message;

		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>Train state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $sms_message;

		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

        $test_message .= '<br>run_at: ' . $this->run_at;
        $test_message .= '<br>end_at: ' . $this->end_at;


//		$test_message .= '<br>Requested state: ' . $this->requested_state;

			$this->thing_report['sms'] = $sms_message;
			$this->thing_report['email'] = $sms_message;
			$this->thing_report['message'] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;


                        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;



		//$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);

$this->thing_report['help'] = 'This is a Train. Trains have Flags.  Messaging RED will show the Red Flag.  Messaging GREEN will show the Green Flag.';


		//echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}


    function extractEvents($input)
    {
        if ($input == null) {$input = $this-subject;}

        // Extract runat signal
        $pieces = explode(" ", strtolower($input));
        $matches = 0;
        $this->events = array();
        foreach ($pieces as $key=>$piece) {

            if ((strlen($piece) == 4) and (is_numeric($piece))) {
                $event_at = $piece;
                $this->events[] = $event_at;
                $matches += 1;
            }
        }

        return $this->events;
    }


    public function readSubject() 
    {
        $this->response = null;
        $this->num_hits = 0;
        // Extract uuids into
//        $uuids_in_input

//        $headcodes_in_input





        $keywords = $this->keywords;

        if ($this->agent_input != null) {

            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);

        } else {

            $input = strtolower($this->subject);

        }

        $this->input = $input;

		$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

//		$this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

//$this->getHeadcode();
//$headcode_thing = new Headcode($this->thing, 'headcode '.$input);
//$this->head_code = $headcode_thing->head_code; // Not sure about the direct variable
// probably okay if the variable is renamed to variable.  Or if $headcode_thing
// resolves to the variable.


//echo count($head_codes);
        $this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

$uuids = $this->extractUuids($input);
//foreach ($uuids as $uuid) {

//    echo $uuid;
//}
$this->thing->log($this->agent_prefix . " counted " . count($uuids) . " uuids.");

//exit();


        $pieces = explode(" ", strtolower($input));



		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'train') {

                if (!isset($this->train_thing->head_code)) {
                    $this->train_thing->head_code = $this->getHeadcode();
                }
                $this->head_code = $this->train_thing->head_code;

                if (!isset($this->train_thing->flag)) {
                    $this->train_thing->flag = $this->getFlag();
                }
                $this->flag = $this->train_thing->flag;

                $this->available = $this->getAvailable();

                if (!isset($this->train_thing->alias)) {
                    $this->train_thing->alias = $this->getAlias();
                }



                return;
            }
        }
/*
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
        $this->thing->log('Agent "Train" found a "run at" time of "' . $this->run_at . '".');
    }
*/


    $this->getRunat();
    $this->getEndat();

//echo $this->run_at;
//echo "<br>";
//echo $this->end_at;
//exit();
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


   case 'red':
   //     //$this->thing->log("read subject nextblock");
        $this->setFlag('red');
        break;


   case 'green':
   //     //$this->thing->log("read subject nextblock");
        $this->setFlag('green');
        break;


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
        $this->thing->log("read subject nexttrain");
        $this->nextTrain();
        break;

   case 'drop':
   //     //$this->thing->log("read subject nextblock");
        $this->dropTrain();
        break;


   case 'add':
   //     //$this->thing->log("read subject nextblock");
        $this->makeTrain();
        break;

   case 'run':
   //     //$this->thing->log("read subject nextblock");
        $this->runTrain();
        break;

//   case 'red':
   //     //$this->thing->log("read subject nextblock");
//        $this->setFlag('red');
//        break;


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
    $this->makeTrain($this->run_at,$this->quantity);
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
                               //                 echo "sum";
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


                 //       echo '<pre> Agent "Train" normalized discrimators "';print_r($normalized);echo'"</pre>';


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
