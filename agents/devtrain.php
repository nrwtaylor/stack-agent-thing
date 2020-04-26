<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';
require_once '/var/www/html/stackr.ca/agents/message.php';
require_once '/var/www/html/stackr.ca/agents/headcode.php';
require_once '/var/www/html/stackr.ca/agents/flag.php';
require_once '/var/www/html/stackr.ca/agents/consist.php';
require_once '/var/www/html/stackr.ca/agents/variables.php';
//require_once '/var/www/html/stackr.ca/agents/alias.php';



ini_set("allow_url_fopen", 1);

class Devtrain
{

    // This is a Train.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) {

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

        $this->agent_prefix = 'Agent "Train" ';

 $this->node_list = array("start"=>array("stop 1"=>array("stop 2","stop 1"),"stop 3"),"stop 3");
$this->thing->choice->load('train');


       $this->keywords = array('run','change','next', 'accept', 'clear', 'drop','add','run','red','green');


//                'block' => array('default run_time'=>'105',
//                                'negative_time'=>'yes'),

        $this->current_time = $this->thing->json->time();


                //$this->default_run_time = $this->thing->container['api']['train']['default run_time'];
                //$this->negative_time = $this->thing->container['api']['train']['negative_time'];
                $this->default_run_time = $this->current_time;
                $this->negative_time = true;
                //$this->app_secret = $this->thing->container['api']['facebook']['app secret'];

                //$this->page_access_token = $this->thing->container['api']['facebook']['page_access_token'];


    $this->min_runtime = 22;
    $default_train_name = "train";

        $this->variables_agent = new Variables($this->thing, "variables " . $default_train_name . " " . $this->from);


        // Loads in Train variables.
        $this->get(); 



		$this->thing->log('<pre> Agent "Train" running on Thing '. $this->thing->nuuid . '.</pre>');
		$this->thing->log('<pre> Agent "Train" received this Thing "'.  $this->subject . '".</pre>');





		$this->readSubject();
		$this->respond();



        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

		$this->thing->log($this->agent_prefix . 'completed.');

        $this->thing_report['log'] = $this->thing->log;



		return;

		}





    function set()
    {



        // This makes sure that
//        if (!isset($this->train_thing)) {
//            $this->train_thing = $this->thing;
//        }

        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        // Update calculated variables.
        $this->getAvailable();

        $this->variables_agent->setVariable("state", $this->train_thing->state);
        $this->variables_agent->setVariable("head_code", $this->train_thing->head_code);

        $this->variables_agent->setVariable("alias", $this->train_thing->alias);
        $this->variables_agent->setVariable("index", $this->train_thing->index);

        $this->variables_agent->setVariable("run_at", $this->train_thing->run_at);
        $this->variables_agent->setVariable("quantity", $this->train_thing->quantity);

        $this->variables_agent->setVariable("available", $this->train_thing->available);
        $this->variables_agent->setVariable("refreshed_at", $this->train_thing->current_time);

        $this->variables_agent->setVariable ( "route" , $this->train_thing->route) ;
        $this->variables_agent->setVariable ( "consist" , $this->train_thing->consist) ;
        $this->variables_agent->setVariable ( "runtime", $this->train_thing->runtime) ;

//        $this->thing->choice->save('train', $this->state);

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


    function closestTrain() {

        // Get the current train time (4 digits)
        if (!isset($this->train_time)) {
            $this->train_time = $this->trainTime();
        }

        if (!isset($this->trains_list)) {$this->getTrains();}

        $this->closest_train = array();
        $count = 0;
        foreach ($this->trains_list as $key=>$train) {

            if ( (strtotime($this->train_time) >= strtotime($train['run_at'])) and 
                (strtotime($this->train_time) <= strtotime($train['end_at'])) ) {
                
                return $train['thing'];
            }

            if ( ( strtotime($this->train_time) >= strtotime($train['run_at'])) and 
                (strtotime($this->train_time) <= strtotime($train['run_at'] + '45 minutes')) ) {

                return $train['thing'];
            }

            // Look 22 minutes ago and see if there is a match

            if ( ( strtotime($this->train_time - "22 minutes") >= strtotime($train['run_at'])) and 
                (strtotime($this->train_time - "22 minutes") <= strtotime($train['end_at'])) ) {

                return $train['thing'];
            }




            if ( ( strtotime($this->train_time - "22 minutes") >= strtotime($train['run_at']) ) and 
                (strtotime($this->train_time - "22 minutes") <= strtotime($train['run_at'] + '45 minutes')) ) {

                return $train['thing'];
            }


        }
        return false;

//exit();

    }

    function getTrains()
    {
        // This function pulls in the current <99> Trains
        // Apparently we are currently running a "5Z03".
        $this->trains_list = array();

        // See if a block record exists.
        require_once '/var/www/html/stackr.ca/agents/findagent.php';
        $findagent_thing = new FindAgent($this->thing, 'train');

        // This pulls up a list of other Trains Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        $this->max_index =0;

        foreach ($findagent_thing->thing_report['things'] as $train_thing) {

            $thing = new Thing($train_thing['uuid']);
            $variables = $thing->account['stack']->json->array_data;

            if (isset($variables['train']['head_code'])) {

                $index = $variables['train']['index'];
                $alias = $variables['train']['alias'];

                $run_at = $variables['train']['run_at'];
                $quantity = $variables['train']['quantity'];
                $available = $variables['train']['available'];
                $refreshed_at = $variables['train']['refreshed_at'];

                $head_code = $variables['train']['head_code'];

                $route = $variables['train']['route'];
                $consist = $variables['train']['consist'];
                $runtime = $variables['train']['runtime']; // Which is easy to confuse for run_railwaytime

                if ($runtime == null) {$runtime = "x";} // Null suggests Train should specify.

                if ($quantity > 0) {
                    $end_at = $this->thing->json->time(strtotime($run_at . " " . $quantity . " minutes"));
                } else {
                    $end_at = "x";
                }

                // Lots of variables to load in.
                $this->trains_list[] = array("index"=>$index,
                                            "alias"=>$alias,
                                            "run_at"=>$run_at,
                                            "quantity"=>$quantity,
                                            "available"=>$available,
                                            "refreshed_at"=>$refreshed_at,
                                            "head_code"=>$head_code,
                                            "route"=>$route,
                                            "consist"=>$consist,
                                            "runtime"=>$runtime,
                                            "end_at"=>$end_at,
                                            "thing"=>$thing);
                if ($index > $this->max_index) {$this->max_index = $index;}
            }
echo $head_code. " | " . $alias . " | " .$run_at . " | " . $runtime;
echo "<br>";
        }


//exit();
        return $this->trains_list;
    }

    function get($train_time = null)
    {

        // Loads current block into $this->block_thing

        $match = false;

        
        $this->train_thing = $this->closestTrain();

        // Set-up empty block variables.
        $this->flagposts = array();
        $this->trains = array();
        $this->bells = array();

        // If it drops through as Green, then no blocks matched the current time.
        if ($this->train_thing == false) {

            $this->thing->log($this->agent_prefix . "did not find a valid train.");
            $this->train_thing = false;

            $this->index = $this->max_index + 1;
            $this->getTrain();


            $this->makeTrain($this->head_code, $this->current_time, 45, 22);

            $this->thing->log('Agent "Train" did not find a valid train at traintime ' . $this->trainTime($train_time) . "." );
*/

        } else {

            $this->thing->log($this->agent_prefix . "found a valid train.");

            $this->train_thing = $train['thing'];

            $this->train_thing->index = $train['index'];
            $this->train_thing->alias = $train['alias'];
            $this->train_thing->run_at = $train['run_at'];

            $this->train_thing->quantity = $train['quantity'];
//            $this->available = $train['quantity'];

            $this->head_code = $train['head_code'];

            $this->train_thing->route = $train['route'];
            $this->train_thing->consist = $train['consist'];
            $this->train_thing->runtime = $train['runtime'];

//            $this->train_thing = $train['thing'];
//echo "<pre>";
//print_r($this->train_thing->train->alias);
//echo "</pre>";
//exit();

            $this->thing->log($this->agent_prefix . " " . $this->train_thing->index . " " . $this->train_thing->alias . " " .
                    $this->train_thing->run_at . " " . $this->train_thing->quantity . " " . $this->train_thing->head_code . " " .
                    $this->train_thing->route . " " . $this->train_thing->consist . " " . $this->train_thing->runtime . ".");

        }

        //$this->getAvailable();
        //$this->getEndat();


        return;
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

        $this->makeTrain($this->head_code, $this->current_time, $this->quantity, $this->available);

        $this->state = "running";

        //$this->makeTrain($this->current_time, $this->quantity, $this->available);

    }

    function getAlias() {

        if (isset($this->alias)) {return $this->alias;}

        $this->aliases = array("Logan's run", "Kessler Run", "Orient Express", "Pineapple Express", "Dahjeeling Express");

        require_once '/var/www/html/stackr.ca/agents/alias.php';
        $this->alias_thing = new Alias($this->variables_agent->thing, 'alias');
        $this->alias = $this->alias_thing->alias;

//           $this->alias = "Orient Express";
        return $this->alias;
    }

    function makeTrain($head_code = null, $run_at = null, $quantity = null, $available = null) {


        // Load in Consist, Quantity and Route from Headcode
        // will also load in the headcode, if not already loaded.
 
        if ($head_code == null) {$this->getHeadcode();}

        if ($this->head_code == null) {
            $this->head_code = "0Z" . str_pad($this->index + 11,2, '0', STR_PAD_LEFT);
        }
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
//exit();
        // $quantity, $run_at, $available set to preferred values.


        // Check that the shift is okay for making blocks.

//        require_once '/var/www/html/stackr.ca/agents/shift.php';
//        $shift_thing = new Shift($this->thing);
//        $shift_state = strtolower($this->thing->log($shift_thing->thing_report['keyword']));

$shift_state = 'off';
$shift_override == true;

        if ( ($shift_state == "off") or
                ($shift_state == "null") or
                ($shift_state == "") or
                ($shift_override) ){

            // Only if the shift state is off can we 
            // create Trains on the fly.

            // Otherwise we needs to make trains to run in the block.

            $this->thing->log($this->agent_prefix . "found that this is the Off shift.");

            // So we can create this block either from the variables provided to the function,
            // or leave them unchanged.


            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            $this->run_at = $run_at;
            $this->quantity = $quantity;
            $this->getEndat();
/*
            if ($this->quantity > 0) {
                $this->run_at = $run_at;
                $this->quantity = $quantity;
                $this->getEndat();
            } else {
                $this->run_at = $run_at;
                $this->quantity = -$quantity;
                $this->getRunat();
            }

*/


            $this->getAvailable();

  //          $this->train_thing = $this->thing;

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


    function getRuntime()
    {
        // Runtime is the total length of time the Train
        // will need to run.
        // 'X' - train must specifiy amount
        // 'Z' - as much as it needs
        // N - Quantity

        // So default to minimum runtime.
        $this->runtime = $this->min_runtime;



        // devstack: pull in from headcode


        return $this->runtime;
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



        if (($this->run_at != "x") and ($this->quantity != "x")) {
            $this->end_at = $this->thing->json->time(strtotime($this->run_at . " " . $this->quantity . " minutes"));
        } else {
            $this->end_at = "x";
        }

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

        $this->run_at = "x";

//        if (isset($this->end_at)) and (isset($this->quantity))


//        if (($this->end_at != "x") and ($this->quantity != "x")) {
//            $this->run_at = $this->thing->json->time(strtotime($this->end_at . " -" . $this->quantity));
//        } else {
//            $this->run_at = "x";
//        }

        return $this->run_at;
    }


    function getAvailable()
    {

        // This proto-typical block manages (available) time.
        // From start_at and current_time we can calculate elapsed_time.

        if (!isset($this->end_at)) {
            $this->getEndat();
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
        // 

        //if (isset($this->head_code)) {return $this->head_code;}
        // Make sure we have pulled in the latest headcode thing.
        if (isset($this->headcode_thing)) {return $this->head_code;}


        // This will trigger a request from the Agent
        // to return the current Headcode.

        // Even if $this->head_code is set, it still needs to pull it by a stack call.
        // But no reason the Headcode agent can't keep track of this.
        $this->headcode_thing = new Headcode($this->variables_agent->thing, 'headcode '. $this->input);
        $this->head_code = $this->headcode_thing->head_code;


        if ($this->head_code == false) {
            $headcode = "0Z".rand(50,99);
            $this->headcode_thing = new Headcode($this->variables_agent->thing, 'headcode '. $headcode);
            $this->head_code = $this->headcode_thing->head_code;
        }

var_dump($this->head_code);



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


    function getTrain()
    {
        // Pull in Train from headcode

        if (!isset($this->headcode_thing)) {$this->getHeadcode();}

        $this->getQuantity();
        $this->getConsist();
        $this->getRoute();
        $this->getFlag();

        $this->getRunat();
        $this->getEndat();
        $this->getRuntime();

        $this->getAvailable();

        $this->train_thing->head_code = $this->head_code;
        $this->train_thing->quantity = $this->quantity;
        $this->train_thing->consist = $this->consist;
        $this->train_thing->route = $this->route;
        $this->train_thing->flag = $this->flag;
        $this->train_thing->run_at = $this->run_at;
        $this->train_thing->end_at = $this->end_at;

        $this->train_thing->runtime = $this->runtime;

        $this->train_thing->available = $this->available;

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

        echo "start";
        echo $this->previous_state;

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
		$from = "train";

		//echo "<br>";

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

		$sms_message = "TRAIN " . strtoupper($this->train_thing->head_code);
        $sms_message .= " | " . round($this->train_thing->available/60,0) . " minutes remaining";
        $sms_message .= " | flag " . $this->train_thing->flag;

        $sms_message .= " | alias " . strtoupper($this->train_thing->alias);

//        $sms_message .= " | index " . strtoupper($this->index);

//        $sms_message .= " | $this->route;
        $route_description = $this->train_thing->route . " [" . $this->train_thing->consist . "] " . $this->train_thing->quantity;
        $sms_message .= " | " . $route_description;

        //$sms_message .= " | " . $this->blockTime($this->start_at); 
        //$sms_message .= " | Block ends " . $this->blockTime($this->end_at);
        $sms_message .= " | " . $this->trainTime($this->train_thing->run_at) . "-" . $this->trainTime($this->train_thing->end_at);
        $sms_message .= " | now " . $this->trainTime();
        $sms_message .= " | nuuid " . strtoupper($this->train_thing->nuuid);
        $sms_message .= " | nuuid " . substr($this->variables_agent->variables_thing->uuid,0,4); 
  

        $sms_message .= " | runtime " . number_format($this->thing->elapsed_runtime()) . 'ms';



    switch($this->index) {
        case null:
            $sms_message .=  " | TEXT TRAIN";

            break;

        case '1':
            $sms_message .=  " | TEXT TRAIN";
            break;
        case '2':
            $sms_message .=  " | TEXT TRAIN";
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




		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>Train state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $sms_message;

		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

        $test_message .= '<br>run_at: ' . $this->run_at;
        $test_message .= '<br>end_at: ' . $this->end_at;



    	$this->thing_report['sms'] = $sms_message;
	    $this->thing_report['email'] = $sms_message;
		$this->thing_report['message'] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;


        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        $this->thing_report['help'] = 'This is a train.';


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
echo "extractEvents";
var_dump($this->events);

        return $this->events;
    }


    function extractRuntime($input = null)
    {
        if ($input == null) {$input = $this-subject;}



        $pieces = explode(" ", strtolower($input));


    // Extract runtime signal
        $matches = 0;
        foreach ($pieces as $key=>$piece) {

            if (($piece == 'x') or ($piece == 'z')) {
                $this->runtime = $piece;
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

                $this->runtime = $piece;
                $matches += 1;
                continue;
            }

            if ((strlen($piece) == 3) and (is_numeric($piece))) {
                $this->runtime = $piece; //3 digits is a good indicator of a runtime in minutes
                $matches += 1;
                continue;
            }

            if ((strlen($piece) == 2) and (is_numeric($piece))) {
                $this->runtime = $piece;
                $matches += 1;
                continue;
            }

            if ((strlen($piece) == 1) and (is_numeric($piece))) {
                $this->runtime = $piece;
                $matches += 1;
                continue;
            }
        }

        return $this->runtime;
    }


    public function readSubject() 
    {
        $this->response = null;
        $this->num_hits = 0;


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


        // Get the current headcode
        // And determine whether it is a recent update.
        $this->getHeadcode();
        echo (strtotime($this->headcode_thing->refreshed_at) - strtotime($this->current_time));
//exit();
        if ($this->train_thing == false) {
echo "foo";
            //$this->getHeadcode();
            $this->getTrain();
        }

        if (strtolower($this->train_thing->head_code) != strtolower($this->head_code)) {
echo "bar";
            $this->getTrain();
        }

  //  $this->getEndat();
  //  $this->train_thing->end_at = $this->end_at;

//$this->getHeadcode();
//$headcode_thing = new Headcode($this->thing, 'headcode '.$input);
//$this->head_code = $headcode_thing->head_code; // Not sure about the direct variable
// probably okay if the variable is renamed to variable.  Or if $headcode_thing
// resolves to the variable.


        $pieces = explode(" ", strtolower($input));



		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'train') {
                // Save the 
                //echo "readsubject block";
$this->getTrain();
//echo "merple";
//exit();

                $this->set();
                return;
            }
        }


    //$this->getRunat();
    //$this->getEndat();


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
    $this->makeTrain($this->head_code, $this->run_at,$this->quantity);
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




                return "Message not understood";




		return false;

	
	}






	function kill() {
		// No messing about.
		return $this->thing->Forget();
	}


}

?>

