<?php
namespace Nrwtaylor\StackAgentThing;
// bounty
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Train
{

    // A Train is a headcode with a alias (name).
    // It will respond to trains with a signal.

    // Red - Not available
    // Green - Slot allocated
    // Yellow - Next signal Red.
    // Double Yellow - Next signal Yellow

    // The Block keeps track of the Uuids of associated Resources.
    // And checks to see what the block signal should be.  And pass and collect tokens.

    // This is the train driver 1969.  They are an ex-British Rail signalperson.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = microtime(true);

        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->keyword = "train";

        $this->thing = $thing;

        $this->start_time = $this->thing->elapsed_runtime();

        $this->thing_report['thing'] = $thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        $this->num_hits =0;

        $this->agent_prefix = 'Agent "Train" ';

        $this->node_list = array("red"=>array("green"=>array("red")));
        $this->thing->choice->load('train');

        //exit();

        $this->keywords = array('train','run','change','next', 'accept', 'clear', 'drop','add','run','red','green');

        $this->verbosity = 2;

//                'block' => array('default run_time'=>'105',
//                                'negative_time'=>'yes'),

        $this->current_time = $this->thing->json->time();

        $this->thing->log('<pre> Agent "Train" running on Thing '. $this->thing->nuuid . '.</pre>');
        $this->thing->log('<pre> Agent "Train" received this Thing "'.  $this->subject . '".</pre>');

        //$this->default_run_time = $this->thing->container['api']['train']['default run_time'];
        //$this->negative_time = $this->thing->container['api']['train']['negative_time'];
        $this->default_runtime = $this->current_time;
        $this->negative_time = true;

        $default_train_name = "train";

        $this->variables_agent = new Variables($this->thing, "variables " . $default_train_name . " " . $this->from);

        $this->current_time = $this->thing->json->time();

        // Loads in Train variables.
        $this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.', 'OPTIMIZE' );


        // So first thing this does is pull
        // up a list of the running trains.
        // Or if there are no trains running,
        // then the last 99 trains.
        $this->get();

        $this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.', 'OPTIMIZE' );

		$this->readSubject();

        $this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.', 'OPTIMIZE' );

//		$this->respond();
//        if ($this->agent_input == null) {$this->Respond();}
$this->respondResponse();
        $this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.', 'OPTIMIZE' );
        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', 'OPTIMIZE' );
		$this->thing->log($this->agent_prefix . 'completed.', 'INFORMATION');

        $this->thing_report['log'] = $this->thing->log;
        $this->thing_report['response'] = $this->response;
        //var_dump($this->available);

		return;

    }


    function idTrain($text = null)
    {

        if ($text == null) {return null;}
        //$stop_code = $text;

        //$stops = $this->get("stops", array("stop_code"=>$stop_code));

        if (!isset($this->previous_trains)) {$this->getTrains();}

        $matches = array();

        foreach ($this->previous_trains as $train) {
            if ( (strtolower($text) == strtolower($train['head_code'])) or 
                 (strtolower($text) == strtolower($train['alias'])) ) {

                $head_code = $train['head_code'];
                $alias = $train['alias'];

                $matches[$alias] = $head_code;


                //break; //on first find
            }
        }

        if ($matches == array()) {return false;}

        if (count($matches) == 1) {return array($alias,$head_code);}
        return true;

        $this->thing->log("Matched head_code " . $head_code . " to alias " .$alias . ".");


        //$this->station_id = $stop_id;
        return $head_code;
    }




    function set()
    {

        // A block has some remaining amount of resource and 
        // an indication where to start.


        // This makes sure that
        if (!isset($this->train_thing)) {
            $this->train_thing = $this->thing;
        }

        if ((!isset($this->requested_state)) or ($this->requested_state == null)) {
            $this->requested_state = $this->state;
        }


        if ((!isset($requested_state)) or ($requested_state == null)) {
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
        $this->variables_agent->setVariable ( "runtime", $this->runtime->minutes) ;

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
        $runtime = 1;
        $next_time = $this->thing->json->time(strtotime($this->end_at . "+" . runtime . " minutes"));

        $this->get($next_time);

        // So this should create a block in the next minute.

        return $this->available;

    }

    function getTrain() {

        // Given closest train in $this->train_thing.

        if ($this->train_thing == false) {

            if (isset($this->variables_agent->head_code)) {

                // Load in headcode and associates variables
                // Look for X and Z variables and replace with variables
                // from ->variables_agent

            }


        }

//var_dump($this->train_thing);
//exit();

                $this->train_thing->index = $this->train_thing->getVariable("train", "index");
                if ($this->train_thing->index > $this->max_index) {$this->max_index = $this->train_thing->index;}

                $this->train_thing->head_code = $this->train_thing->getVariable("train", "head_code");
                $this->train_thing->alias = $this->train_thing->getVariable("train", "alias");

                $this->train_thing->run_at = $this->train_thing->getVariable("train", "run_at");
                $this->train_thing->quantity = $this->train_thing->getVariable("train", "quantity");
                $this->train_thing->available = $this->train_thing->getVariable("train", "available");
                $this->train_thing->refreshed_at = $this->train_thing->getVariable("train", "refreshed_at");

                $this->train_thing->route = $this->train_thing->getVariable("train", "route");
                $this->train_thing->consist = $this->train_thing->getVariable("train", "consist");
                $this->train_thing->runtime = $this->train_thing->getVariable("train", "runtime");
    }

    function get($train_time = null)
    {

        // Loads current block into $this->block_thing

        $match = false;

        if ($train_time == null) {
            $train_time = $this->current_time;
        }

        $train_things = array();

        // Get recent train tags.
        // This will include simple 'train'
        // requests too.
        // Think about that.
        //require_once '/var/www/html/stackr.ca/agents/findagent.php';
        $findagent_thing = new Findagent($this->thing, 'train');

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        $this->thing->log('Agent "Train" found ' . count($findagent_thing->thing_report['things']) ." Train Agent Things." );

        $this->max_index = 0;
        $this->previous_trains = array();

        foreach ($findagent_thing->thing_report['things'] as $train_thing) {

            $thing = new Thing($train_thing['uuid']);

            $variables_json= $train_thing['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);

            $thing->json->setField("variables");

            $thing->index = $thing->getVariable("train", "index");

            // Find the maximumum index in the last 99 things.
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
            if ($thing->runtime > 0) {
                $thing->end_at = $this->thing->json->time(strtotime($thing->run_at . " " . $thing->runtime . " minutes"));
            } else {
                $thing->end_at = null;
            }

            $this->previous_trains[] = array("index"=>$thing->index, "head_code"=>$thing->head_code, 
                "run_at"=>$thing->run_at,"end_at"=>$thing->end_at,"runtime"=>$thing->runtime, "alias"=>$thing->alias,
                "available"=>$thing->available, "quantity"=>$thing->quantity,
                "route"=>$thing->route, "consist"=>$thing->consist, "refreshed_at"=>$thing->refreshed_at

                );

/*
            //// If the train time is in the run period of the train
            //// then this is a valid train to be running right now.
            if ( ( strtotime($train_time) >= strtotime($thing->run_at) ) 
                and ( strtotime($train_time) <= strtotime($thing->end_at) ) ) {

                $this->thing->log( 'Agent "Train" found ' . $this->trainTime($train_time) . ' in existing train #' . $thing->index . ' (' . $this->trainTime($thing->run_at) . " " . $thing->runtime . ').');
                $match = true;
                break; //Take first matching block.   Because this will be the last referenced train.

            }
*/
        }

        // First check to see if the provided head_code is in the list.
$train = null;
        if (isset($this->head_code)) {
            foreach ($this->previous_trains as $train) {
                if ($train['head_code'] == $this->head_code) {
                    //$this->train_thing = $train;
                    $match = true;
                    break;
                }
            }
        }
        if ($match != true) {
        foreach ($this->previous_trains as $train) {
                //// If the train time is in the run period of the train
                //// then this is a valid train to be running right now.
                if ( ( strtotime($train_time) >= strtotime($thing->run_at) ) 
                    and ( strtotime($train_time) <= strtotime($thing->end_at) ) ) {

                    $this->thing->log( 'Agent "Train" found ' . $this->trainTime($train_time) . ' in existing train #' . $thing->index . ' (' . $this->trainTime($thing->run_at) . " " . $thing->runtime . ').');
                    $match = true;
                    break; //Take first matching block.   Because this will be the last referenced train.
                }
            }
        }


        switch (true) {
            case ($match != false):

                $this->thing->log($this->agent_prefix . "found a valid train.");
                $this->info = "current train retrieved";
                $this->response = "Retrieved the current train.";
                // Load the Train into this Thing.
                //$this->train_thing = $trea;

                // No nead to do this because the read agent will do.
                $this->index = $train['index'];
                $this->alias = $train['alias'];
                $this->head_code = $train['head_code'];
                $this->run_at = $train['run_at'];

                $this->runtime = $train['runtime'];
                $this->quantity = $train['quantity'];

                $this->route = $train['route'];
                $this->consist = $train['consist'];

                $this->available = $this->getAvailable();
                $this->end_at = $this->getEndat();

                $this->train_thing = $this->thing;
                //$this->train_thing = true;
                break;

            case ($match == false):

                // Recent train.  Perhaps running late?
                $train_thing = $findagent_thing->thing_report['things'][0];
                $this->info = "last train retrieved";
                $this->response = "Retrieved the last train.";
                // No valid train found, so make a block record in current Thing
                // and set flag to Green ie accepting trains.
                $this->thing->log('Agent "Train" did not find a valid train at traintime ' . $this->trainTime($train_time) . "." );

                $thing = new Thing($train_thing['uuid']);
                $this->train_thing = $thing;

                $thing->json->setField("variables");

                $this->index = $thing->getVariable("train", "index");
                if ($this->index > $this->max_index) {$this->max_index = $this->index;}

                $this->head_code = $thing->getVariable("train", "head_code");
                $this->alias = $thing->getVariable("train", "alias");

                $this->run_at = $thing->getVariable("train", "run_at");
                $this->quantity = $thing->getVariable("train", "quantity");
                $this->available = $thing->getVariable("train", "available");
                $this->refreshed_at = $thing->getVariable("train", "refreshed_at");


                $this->route = $thing->getVariable("train", "route");
                $this->consist = $thing->getVariable("train", "consist");
                $this->runtime = $thing->getVariable("train", "runtime");

                $this->available = $this->getAvailable();
                $this->end_at = $this->getEndat();

//                $this->train_thing = $thing;
                $this->thing->log( 'got last train ' . $this->trainTime($train_time) . ' in existing train #' . $this->index .  ' (' . $this->trainTime($this->run_at) . " " . $this->runtime->minutes . ').');

                break;
            case (false) :
                $this->info = "special created";
                $this->response = "Created a special train.";
                $this->train_thing = $this->thing;
                $this->train_thing->index = $this->max_index + 1;
                $this->head_code = "2Z" . rand(20,29);
                $this->run_at = $this->current_time;
                $this->runtime = 22;
                break;

            default:
                $this->info = "bork";
                $this->train_thing = $this->thing;
                $this->head_code = "BORK";
        }

/*

        // Set-up empty block variables.
        $this->flagposts = array();
        $this->trains = array();
        $this->bells = array();

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

            }


*/

        return $this->train_thing;

    }

    function dropTrain()
    {
        $this->thing->log("was asked to drop a train.");

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
        //$n = rand(1,49);
        //$n = str_pad($n, 2, '0', STR_PAD_LEFT);

        //$this->head_code = "5Z".$n;

        //if ($this->quantity == 0) {$this->quantity = 45;}
        //$this->runtime = 22;
        //$this->getAvailable();

        //if (!isset($this->head_code)) {
            $n = rand(1,49);
            $n = str_pad($n, 2, '0', STR_PAD_LEFT);
            $this->head_code = "5Z".$n;
        //    $this->getHeadcode();
        //}

        if (!isset($this->run_at)) {
            // get and extract neither found anything
            //$this->getRunat();
            $this->run_at = $this->current_time;
        }

        if (!isset($this->runtime)) {
            // get and extract neither found anything
            //$this->getRuntime();
            $this->runtime = 22;
        }

        $this->makeTrain($this->head_code,$this->alias, $this->current_time, $this->runtime);

        $this->state = "running";

        //$this->makeTrain($this->current_time, $this->quantity, $this->available);

    }

    function getAlias() {

        $this->alias = "";
        return $this->alias;

        if ( (isset($this->alias)) and ($this->alias != false)) {
            return $this->alias;
        }

        $this->aliases = array("Logans run", "Kessler Run", "Orient Express", "Pineapple Express",
            "Dahjeeling Express", "Flying Scotsman", "Gilmore Special", "Rocky Mountaineer",
            "Atlantic","Alouette","The Ambassador","Atlantic Express","Atlantic Limited");

        //require_once '/var/www/html/stackr.ca/agents/alias.php';
        $this->alias_thing = new \Nrwtaylor\StackAgentThing\Alias($this->train_thing, 'alias');

        $this->alias = $this->alias_thing->alias;

        // If it is still false assign an alias.
        if ($this->alias == false) {
            $k = array_rand($this->aliases);
            $this->alias = $this->aliases[$k];
        $this->alias_thing = new Alias($this->train_thing, 'alias is ' . $this->alias);

        }

//           $this->alias = "Orient Express";
        return $this->alias;
    }

    function assertTrain($input)
    {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "train is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("train is")); 
        } elseif (($pos = strpos(strtolower($input), "train")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("train")); 
        }
        $filtered_input = ltrim(strtolower($whatIWant), " ");

        list($head_code,$alias) = $this->idTrain($filtered_input);


        if ($head_code == null) {
            $this->makeTrain(null, $filtered_input);
        } else {
            $this->head_code = $head_code;
            $this->getTrain();
        }
    }


    function makeTrain($head_code, $alias = null, $run_at = null, $runtime = null)
    {

//        if ($alias == null) {$alias = "X";}

        // See if the code or name already exists
        foreach ($this->previous_trains as $train) {
            if (($head_code == $train['head_code']) or ($alias == $train['alias'])) {
                $this->alias = $train['alias'];
                $head_code =$train['head_code'];
                $this->last_refreshed_at = $train['refreshed_at'];
            }
        }


        if ($alias == null) {
            $this->getAlias();
            $alias = $this->alias;
        }
//var_dump($alias);
//exit();
        if ($head_code == null) {
            $this->getHeadcode();
            $head_code = $this->head_code;
        }

        if ($run_at == null) {
            $this->getRunat(); // which is runtime
            if ($this->run_at == "X") {
                $this->run_at = $this->current_time;
            } 

            $run_at = $this->run_at;
        }

        $runtime_minutes = "X";
        if ($runtime == null) {
            $this->getRuntime(); // which is runtime
            if ( (!isset($this->runtime->minutes)) 
                or (strtoupper($this->runtime->minutes) == "X") 
                ) {

                $this->runtime->minutes = 22;
            }
            $runtime_minutes = $this->runtime->minutes;
        }

//        $this->getAlias();

        if ($this->verbosity > 2) {
            $this->getRoute();
            $this->getConsist();
        }

        $this->state = "stopped";

        if ($runtime_minutes == "X") {
            $runtime_minutes = 45;
        }

        if ($run_at == "X") {
            $run_at = $this->current_time; 
        }

        $this->getAvailable();

        $this->thing->log('will make a Train with ' . $this->trainTime($run_at) . " " . $runtime_minutes . " " . $this->runtime->minutes . ".");

        $shift_override = true;
        $shift_state = "off";
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
            $this->runtime->minutes = $runtime_minutes;
            $this->alias = $alias;

//            $this->getEndat();
//            $this->getAvailable();

        } else {

            $this->thing->log($this->agent_prefix . " checked the shift state: " . $shift_state . ".");
            // ... and decided there was already a shift running ...
            $this->run_at = "meep"; // We could probably find when the shift started running.
            $this->runtime->minutes = "X";
            $this->available = "X";
            $this->end_at = "X";

            $this->alias = "MERP";

        }

        // So at this point $this->start_at, $this->end_at, $this->quantity, 
        // $this->available, have all be established.

        //$this->getEndat();

        $this->getAvailable();
        $this->getEndat();

        $this->set();

        $this->thing->log('found a run_at and a runtime and made a Train.');

    }

    function trainTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if (strtoupper($input) == "X") {
            $train_time = "X";
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

    function trainDay($input = null) {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if (strtoupper($input) == "X") {
            $train_day = "X";
            return $train_day;
        }


        $t = strtotime($input_time);





        //$train_day = "MON";

$date = $input_time;
$day  = 1;
$days = array('SUN', 'MON', 'TUE', 'WED','THU','FRI', 'SAT');
$this->day =  date('l', strtotime($input_time));
$train_day = $this->day;


        if ($input == null) {$this->train_day = $train_day;}

        return $train_day;

        //exit();


    }

    function getVariable($variable_name = null, $variable = null)
    {
        // This function does a minor kind of magic
        // to resolve between $variable, $this->variable,
        // and $this->default_variable.

        if (!isset($variable)) {
            // Local variable found.
            // Local variable takes precedence.
            return "X";
        }


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


    function extractEndat()
    {
        if (!isset($this->events)) {$this->extractEvents($this->subject);}

        // If there is only one time, it is the run_at time

        if ( (is_array($this->events)) and (count($this->events) == 2) ) {
        //if (count($this->events) == 2) {
            $this->end_at = $this->events[1];
            $this->num_hits += 2;
            return $this->end_at;
        }

        $this->end_at = "X";
        return $this->end_at;
    }



    function getEndat()
    {

        // Avoid ping pong if no variables set.
        if ( (!isset($this->run_at)) and (!isset($this->runtime)) ) {
            $this->end_at = "X";
            return $this->end_at;
        }

        if (!isset($this->run_at)) {
            $this->getRunat();
        }

        if (!isset($this->runtime->minutes)) {
            $this->getRuntime();
        }

        switch (true) {
            case (strtoupper($this->run_at) == "X"):
                // No runat available.  So endtime is X
                $this->end_at = "X";
                break;
            //case ($this->runtime == false):
            //    $this->end_at = "X";
            //    break;
            case (strtoupper($this->runtime->minutes) == "X"):
                // No runat available.  So endtime is X
                $this->end_at = "X";
                break;

            case (strtoupper($this->runtime->minutes) == "Z"):
                // No runat available.  So endtime is X
                $this->end_at = "X";
                break;

            default:
//var_dump($this->run_at);

//if ((!isset($this->runtime)) or ($this->runtime == false)) {$this->runtime = new Runtime($this->thing, "runtime 0");}

// Trying to get this empty class creation fixed.

               $this->end_at = $this->thing->json->time(strtotime($this->run_at . " + " . $this->runtime->minutes . " minutes"));
        }

//        $this->end_at = "X";
        return $this->end_at;
    }

    function extractRunat()
    {
        if (!isset($this->events)) {$this->extractEvents($this->subject);}

      if ( (is_array($this->events)) and (count($this->events) == 1) ) {
//        if (count($this->events) == 1) {
            $this->run_at = $this->events[0];
            $this->num_hits += 1;
            return $this->run_at;
        }

       if ( (is_array($this->events)) and (count($this->events) == 2) ) {
//        if (count($this->events) == 2) {
            $this->run_at = $this->events[0];
            $this->num_hits += 2;
            return $this->run_at;
        }

        $this->run_at = "X";

        return $this->run_at;
    }


    function getRunat()
    {

        if ( (!isset($this->end_at)) and (!isset($this->runtime)) ) {
            if (!isset($this->run_at)) {
                $this->run_at = "X";
            }
            return $this->run_at;
        }

        if (!isset($this->end_at)) {
            $this->getEndat();
        }

        if (!isset($this->runtime)) {
            $this->getRuntime();
        }

        switch (true) {
            case ( (strtoupper($this->end_at) != "X") and (strtoupper($this->end_at) != "Z")) :
                $this->run_at = strtotime( $this->end_at . "-" . $this->runtime->minutes. "minutes");
                break;
            default:
                $this->run_at = $this->trainTime();
        }

        return $this->run_at;
    }


    function getAvailable()
    {
        // Calculate the amount of time remaining for the train

        if ( (!isset($this->run_at)) and (!isset($this->end_at)) ) {
            if (!isset($this->available)) {
                $this->available = "X";
            }
        }

        if (!isset($this->run_at)) {
            $this->getRunat();
        }

        if (!isset($this->runtime)) {
            $this->getRuntime();
        }

        if (!isset($this->end_at)) {
            $this->getEndat();
        }


        if (($this->runtime == "X") or ($this->run_at == "X")) {
            $this->available = "Z";
            return $this->available;
        }


//var_dump($this->run_at);
//var_dump($this->current_time);
//var_dump($this->end_at);


        switch (true) {
            case (strtoupper($this->run_at) == "X"):
                // No runtime available.  So what
                // is available, is what there is...
                $this->available = "Z";
                break;
            case (strtotime($this->current_time)  < strtotime($this->run_at)):
                // Current time is before the run at time.
                // So the full amount of time is available.
               $this->available = strtotime($this->end_at) - strtotime($this->run_at);
                break;
            case (strtotime($this->current_time)  > strtotime($this->run_at)):
                // Current time is after the run time.
                // Return the number of minutes until
                // the end time.
                // Negative is how late the train is.
                $this->available = strtotime($this->end_at) - strtotime($this->current_time);

                break;
            default:
                $this->available = "X";
        }

        $this->thing->log('Agent "Train" identified ' . $this->available . ' resource units available.');

        return $this->available;

    }


    function getRuntime()
    {

        // Because an Agent hasn't been written yet.
        // This will kind of cover Things until then.

        if (!isset($this->headcode_thing)) {
            $this->getHeadcode();
        }

        $this->runtime = new Runtime($this->thing,"runtime " . $this->head_code);

        //$runtime = $this->headcode_thing->runtime; //which is runtime

//        $runtime = $this->runtime->minutes;
        $runtime = $this->runtime;


        // Which can be <number>, "X" or "Z".
        if (strtoupper($runtime->minutes) == "X") {
            // Train must specifiy runtime.
            if (!isset($this->runtime->minutes)) {
                $this->runtime->minutes = "X";
            }
        }

        if (strtoupper($runtime->minutes) == "Z") {
            // Train must specifiy runtime.
            $this->runtime->minutes = "Z";
        }

        if (is_numeric($runtime->minutes)) {
            // Train must specifiy runtime.
            $this->runtime->minutes = $runtime->minutes;
        }
        return $this->runtime;
    }



    function getQuantity()
    {
        $this->runtime = $this->getRuntime();
        return $this->quantity;

    }

    function getConsist() 
    {
        $this->consist = "X";
        return $this->consist;

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

    function getRoute()
    {
        $this->route = "X";
        return $this->route;


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

    function extractHeadcode()
    {
        //if (!isset($this->head_code)) {
        //    $n = rand(50,99);
        //    $this->head_code = "1Z" . $n;
        //}

        $this->headcode_thing = new Headcode($this->thing, 'extract');
        $this->head_code = $this->headcode_thing->head_code;

        return $this->head_code;
    }

    function getHeadcode()
    {
        // This will trigger a request from the Agent
        // to return the current Headcode.

        //if (!isset($this->head_code)) {
        //    $n = rand(50,99);
        //    $this->head_code = "1Z" . $n;
        //}  

        $this->headcode_thing = new Headcode($this->variables_agent->thing, 'headcode');
        $this->head_code = $this->headcode_thing->head_code;
        return $this->head_code;
    }

    function nextHeadcode()
    {
        // This will trigger a request from the Agent
        // to return the current Headcode.

        if (!isset($this->head_code)) {
            $n = rand(50,99);
            $this->head_code = "1Z" . $n;
        }  

        $this->headcode_thing = new Headcode($this->variables_agent->thing, 'headcode '. $this->head_code);

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
        $this->flag = $this->flag_thing->state;             // No headcode found


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
        $this->makeTrain(null);
        $this->get();
        return;
    }

    function setState($input)
    {

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


		if ($this->previous_state == 'stop') {
            $this->thing->choice->Choose('start');
            $this->state = 'start';
            $this->set();
            return;
		}

		if ($this->previous_state == 'start') {

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

    function makeTXT()
    {
        $txt = 'This is a TRAIN for RAILWAY ' . $this->variables_agent->nuuid . '. ';
        $txt .= "\n";

        $count = 0;
        if (is_array($this->previous_trains)) {
          $count =  count($this->events);
        }

        $txt .= $count . ' Trains retrieved.';

        $txt .= "\n";
            $txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
            $txt .= " " . str_pad("HEAD", 4, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad("ALIAS", 10, " " , STR_PAD_RIGHT);
            $txt .= " " . str_pad("DAY", 4, " ", STR_PAD_LEFT);

            $txt .= " " . str_pad("RUNAT", 6, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad("ENDAT", 6, " ", STR_PAD_LEFT);

            $txt .= " " . str_pad("RUNTIME", 8, " ", STR_PAD_LEFT);

     $txt .= " " . str_pad("AVAILABLE", 6, " ", STR_PAD_LEFT);
     $txt .= " " . str_pad("QUANTITY", 9, " ", STR_PAD_LEFT);
     $txt .= " " . str_pad("CONSIST", 6, " ", STR_PAD_LEFT);
     $txt .= " " . str_pad("ROUTE", 6, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        foreach($this->previous_trains as $key=>$train) {
            //$txt .= implode(" ", $train);
            $txt .= str_pad($train['index'], 7, '0', STR_PAD_LEFT);
            $txt .= " " . str_pad(strtoupper($train['head_code']), 4, "X", STR_PAD_LEFT);
            $txt .= " " . str_pad($train['alias'], 10, " " , STR_PAD_RIGHT);
    
            $day = strtoupper(substr($this->trainDay($train['run_at']),0,3));
            $txt .= " " . str_pad($day, 4, " ", STR_PAD_LEFT);

            $txt .= " " . str_pad($this->trainTime($train['run_at']), 6, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($this->trainTime($train['end_at']), 6, " ", STR_PAD_LEFT);

            $txt .= " " . str_pad($train['runtime'], 8, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($train['available'], 6, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($train['quantity'], 9, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($train['consist'], 6, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($train['route'], 6, " ", STR_PAD_LEFT);


            $txt .= "\n";
        }
//exit();
        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;


    }


	public function respondResponse()
    {

        $this->makeTXT();

		// Thing actions
        // At some point this is where the 
        // Train can be set to run until concluded.
        // For now flag as Green to 

		$this->thing->flagGreen();
		// Generate email response.


		$to = $this->thing->from;
		$from = "train";


        if (isset($this->requested_state)) {
            $this->state = $this->requested_state;
        } else {
            if (!isset($this->previous_state)) {
                $this->previous_state = "train";
            }

            $this->state = $this->previous_state;
        }

		$choices = $this->thing->choice->makeLinks($this->state);
		$this->thing_report['choices'] = $choices;

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

        $this->makeSMS();

//            $sms_message = "testtesttest train";
//            $this->thing_report['sms'] = $sms_message;
//            $this->sms_message = $sms_message;

        $this->makeEmail();

        $this->message = $this->sms_message;
        // $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 18 Feb 2018 - testing if this works for email;

        //$this->thing_report['message'] = "test";

        $this->thing_report['info'] = "Took a look at the train.";
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->makeWeb();

        $this->thing_report['help'] = 'This is a Train. Trains have Flags.  Messaging RED will show the Red Flag.  Messaging GREEN will show the Green Flag.';

		return;
	}

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "train");
        $choices = $this->thing->choice->makeLinks('train');
        $this->choices = $choices;
        $this->thing_report['choices'] = $choices;
    }



    function makeWeb() {

        if (!isset($this->choices)) {
            $this->makeChoices();
        }

        $test_message = '<b>TRAIN ' . strtoupper($this->head_code);
        if ($this->alias != null) {
            $test_message .= ' "' . strtoupper($this->alias) . '"';
        }

        $test_message .= ' #' . $this->index . "</b>";
        $test_message .= "<p>";


        $test_message .= '<p><b>Railway Time</b>';
        $test_message .= '<br>' .$this->trainTime();

        if (isset($this->refreshed_at)) {
            $test_message .= '<br>refreshed at ' . $this->trainTime($this->refreshed_at);
        }

        $test_message .= "<p><b>Train Variables</b>";
        $test_message .= '<br>state ' . $this->state . '';
        $test_message .= '<br>flag ' . strtoupper($this->flag) . '';
$this->getRoute();
        $test_message .= '<br>route ' . $this->route;
$this->getConsist();
        $test_message .= '<br>consist ' . $this->consist;

        if (isset($this->jobs)) {
            $test_message .= '<br>jobs ' . $this->jobs;
        }

        $test_message .= "<p>";
        $test_message .= "<b>Schedule</b>";
        $test_message .= '<br>run_at ' . $this->trainTime($this->run_at);
        $test_message .= '<br>end_at ' . $this->trainTime($this->end_at);
        $test_message .= '<br>runtime ' . $this->runtime->minutes;


        if (!isset($this->sms_message)) {$this->makeSMS;}
        $test_message .= '<p>';
        $test_message .= '<b>SMS Text</b>';
        $test_message .= '<br>' . $this->sms_message;

        $test_message .= '<p><b>Resources</b>';
        $test_message .= '<br>quantity ' . $this->quantity;
        $test_message .= '<br>available ' . $this->available;

        $test_message .= "<p><b>Agents</b>";
        $test_message .= "<br>" .$this->choices['link'];
        //$test_message .= '<br>current_node ' . $this->thing->choice->current_node;
        //$test_message .= "<br>" . $this->thing_report['choices']['button']; //words link button

        $test_message .= '<p>Agent "Train" is responding to your web view of data gram subject "' . $this->subject . '", ';
        $test_message .= "which was received " . $this->thing->human_time($this->thing->elapsed_runtime()) . " ago.";

        $this->web = $test_message;
        $this->thing_report['web'] = $test_message;

    }


    function makeEmail() {

        if (!isset($this->choices)) {
            $this->makeChoices();
        }

        $test_message = 'Agent "Train" is responding to your email, subject line "' . $this->subject . '", ';

        $test_message .= "which was received " . $this->thing->human_time($this->thing->elapsed_runtime()) . " ago.";

        $test_message .= '<p>';
        $test_message .= '<br>TRAIN ' . strtoupper($this->head_code);
        $test_message .= ' ' . strtoupper($this->alias);
        $test_message .= ' ' . $this->index;

        $test_message .= "<br>";

        $test_message .= '<p>';
        $test_message .= "<br>Train Variables";
        $test_message .= '<br>state ' . $this->state . '';
        $test_message .= '<br>flag ' . strtoupper($this->flag) . '';
        $test_message .= '<br>route ' . $this->route;
        $test_message .= '<br>consist ' . $this->consist;

        if (isset($this->jobs)) {
            $test_message .= '<br>jobs ' . $this->jobs;
        }

        $test_message .= "<p>";
        $test_message .= "Schedule";
        $test_message .= '<br>run_at ' . $this->run_at;
        $test_message .= '<br>end_at ' . $this->end_at;
        $test_message .= '<br>runtime ' . $this->runtime->minutes;


        if (!isset($this->sms_message)) {$this->makeSMS;}
        $test_message .= '<p>';
        $test_message .= 'SMS Text';
        $test_message .= '<br>' . $this->sms_message;

        $test_message .= '<p>';
        $test_message .= '<br>Resources';        
        $test_message .= '<br>quantity ' . $this->quantity;
        $test_message .= '<br>available ' . $this->available;

        if (isset($this->refreshed_at)) {
            $test_message .= '<br>refreshed_at ' . $this->refreshed_at;
        }

        $test_message .= '<p>';
        $test_message .= "<br>Choices";
        $test_message .= "<br>choices link " . $this->choices['link'];
        $test_message .= '<br>current_node ' . $this->thing->choice->current_node;
        $test_message .= "<br>" . $this->thing_report['choices']['button']; //words link button

        $test_message .= "<p>";
        $test_message .= 'End of Report';

        $this->email_message = $test_message;
        $this->thing_report['email'] = $test_message;

    }

    public function makeSMS()
    {

        $this->node_list = array("train");

        $sms_message = "TRAIN ";
        $sms_message .= strtoupper($this->head_code);

//      This line is not being accepted by FB Messenger !?
//        $sms_message .= ' "' . strtoupper($this->alias). '"';
        //$this->getAlias();

        $sms_message .= " " . strtoupper($this->alias) ;

        //$this->getAvailable();
        if ($this->r_type == 'instruction') {
            //$sms_message .= " false";


        if ($this->train_thing == false) {
            $sms_message .= " | train not running";
        } else {

            $sms_message .= " | ";

                if ($this->verbosity >= 2) {
                    $run_at = $this->trainTime($this->run_at);
                    //if (!$this->thing->isData($run_at)) {$run_at = "X";}
                    $sms_message .= "" ."run at " . $this->trainTime($this->run_at);
                    $sms_message .= " " ."runtime " . $this->runtime->minutes;

                }

                if ($this->verbosity > 5) {
                    $sms_message .= " " ."end at " . $this->trainTime($this->end_at);
                    $sms_message .= " " ."now " . $this->trainTime();
                }



        }


            } else {
                $sms_message .= " | " . round($this->available/60,0) . " minutes remaining";
            }



        if ($this->verbosity >= 1) {
            //$this->train_thing->flag = $this->getFlag();
            //$this->flag = $this->train_thing->flag;

            if (isset($this->flag)) {
                $sms_message .= " | flag " . strtoupper($this->flag);
            }
        }

        if ($this->verbosity >= 1) {
            if (isset($this->response)) {
                $sms_message .= " | " . $this->response;
            }
        }
        

        if ($this->verbosity > 2) {

            if (!isset($this->route)) {
                $route = "X";
            } else {
                $route = $this->route;
            }

            if (!isset($this->consist)) {
                $route = "Z";
            } else {
                $route = $this->consist;
            }


            $route_description = $route . " [" . $this->consist . "] " . $this->runtime->minutes;
            $sms_message .= " | " . $route_description;
            $sms_message .= " | nuuid " . substr($this->variables_agent->variables_thing->uuid,0,4); 
        }

        if ($this->verbosity > 5) {

            $sms_message .= " | rtime " . number_format($this->thing->elapsed_runtime())."ms"; 
        }


        if ($this->verbosity > 3) {
            if ($this->train_thing == false) {
                $sms_message .= " | MESSAGE RUN TRAIN";
            } else {
                $sms_message .= " | MESSAGE ?";
            }
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


            $this->thing_report['sms'] = $sms_message;
            $this->sms_message = $sms_message;
            return $this->sms_message;


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


    function extractRuntime($input)
    {

        $pieces = explode(" ", strtolower($input));

        // Extract runtime signal
        $matches = 0;
        foreach ($pieces as $key=>$piece) {

            if (($piece == 'x') or ($piece == 'z')) {
                $this->runtime->minutes = $piece;
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

            $this->runtime->minutes = $piece;
            $matches += 1;
            continue;
        }

        if ((strlen($piece) == 3) and (is_numeric($piece))) {
            $this->runtime->minutes = $piece; //3 digits is a good indicator of a runtime in minutes
            $matches += 1;
            continue;
        }

        if ((strlen($piece) == 2) and (is_numeric($piece))) {
            $this->runtime->minutes = $piece;
            $matches += 1;
            continue;
        }

        if ((strlen($piece) == 1) and (is_numeric($piece))) {
            $this->runtime->minutes = $piece;
            $matches += 1;
            continue;
        }

    }

    if ($matches == 1) {
        return $this->runtime->minutes;
        $this->runtime->minutes = $piece;
        $this->num_hits += 1;
        //$this->thing->log('Agent "Block" found a "run time" of ' . $this->quantity .'.');
    }

        return true;

    }

    public function readSubject() 
    {
        // To get it working.
        $this->r_type = "keyword";

        // At this point the previous train will be loaded.

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

//$this->getHeadcode();
//$headcode_thing = new Headcode($this->thing, 'headcode '.$input);
//$this->head_code = $headcode_thing->head_code; // Not sure about the direct variable
// probably okay if the variable is renamed to variable.  Or if $headcode_thing
// resolves to the variable.


        $this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        $uuids = $this->extractUuids($input);
        $this->thing->log($this->agent_prefix . " counted " . count($uuids) . " uuids.");

        $pieces = explode(" ", strtolower($input));

        $this->extractRunat($haystack);
        $this->extractEndat($haystack);
        $this->extractRuntime($haystack);
        $this->extractHeadcode();

        if ($this->agent_input == "extract") {return;}

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'train') {
                $this->getHeadcode();

                $this->getTrain();
                $this->response = "Current headcode train retrieved.";
                return;

            }
        }

//    $this->getRunat();
//    $this->getEndat();
//    $this->getRuntime();
//    $this->extractRunat();
//    $this->extractEndat();
//    $this->extractRuntime();

    foreach ($pieces as $key=>$piece) {
        foreach ($keywords as $command) {
            if (strpos(strtolower($piece),$command) !== false) {

                switch($piece) {

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


   case 'make':
   case 'new':
   case 'train':
   case 'create':
   case 'add':
        $this->assertTrain(strtolower($input));

        if (empty($this->alias)) {$this->alias = "X";}

        $this->response = 'Asserted Train and found ' . strtoupper($this->alias) .".";
        return;
        break;




   case 'add':
   //     //$this->thing->log("read subject nextblock");
        $this->makeTrain(null);
        break;

   case 'run':
        $this->r_type = "instruction";
   //     //$this->thing->log("read subject nextblock");
        $this->runTrain(null);
        break;

//   case 'red':
   //     //$this->thing->log("read subject nextblock");
//        $this->setFlag('red');
//        break;


    default:
        //$this->read();

                                        }

                                }
                        }

                }


// Check whether Block saw a run_at and/or run_time
// Intent at this point is less clear.  But Block
// might have extracted information in these variables.

// $uuids, $head_codes, $this->run_at, $this->run_time

if ( (count($uuids) == 1) and (count($head_codes) == 1) and (isset($this->run_at)) and (isset($this->runtime->minutes)) ) {

    // Likely matching a head_code to a uuid.

}


if ( (isset($this->run_at)) and (isset($this->runtime->minutes)) ) {
    $this->r_type = "instruction";
//$this->thing->log('Agent "Block" found a run_at and a run_time and made a Block.');
    // Likely matching a head_code to a uuid.
    $this->makeTrain($this->head_code, $this->alias, $this->run_at,$this->runtime->minutes);
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
                                }

                                foreach ($aliases[$discriminator] as $alias) {

                                        if ($word == $alias) {
                                                $count[$discriminator] = $count[$discriminator] + 1;
                                                $total_count = $total_count + 1;
                                        }
                                }
                        }

                }

                // Set total sum of all values to 1.

                $normalized = array();
                foreach ($discriminators as $discriminator) {
                        $normalized[$discriminator] = $count[$discriminator] / $total_count;            
                }


                // Is there good discrimination
                arsort($normalized);


                // Now see what the delta is between position 0 and 1

                foreach ($normalized as $key=>$value) {

                    if ( isset($max) ) {$delta = $max-$value; break;}
                        if ( !isset($max) ) {$max = $value;$selected_discriminator = $key; }
                }




                if ($delta >= $minimum_discrimination) {
                        //echo "discriminator" . $discriminator;
                        return $selected_discriminator;
                } else {
                        return false; // No discriminator found.
                } 

                return true;
        }

}
