<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';
require_once '/var/www/html/stackr.ca/agents/message.php';
//require_once '/var/www/html/stackr.ca/agents/headcode.php';
//require_once '/var/www/html/stackr.ca/agents/flag.php';
//require_once '/var/www/html/stackr.ca/agents/consist.php';
require_once '/var/www/html/stackr.ca/agents/variables.php';


//require '/var/www/html/stackr.ca/public/agenthandler.php'; // until the callAgent call can be
								// factored to
								// call agent 'Agent'

ini_set("allow_url_fopen", 1);

class Alias 
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

        $this->agent_prefix = 'Agent "Alias" ';

// $this->node_list = array("green"=>array("red"=>array("green","red"),"red"),"green3");
//$this->thing->choice->load('alias');
        $this->node_list = array("off"=>array("on"=>array("off")));

// This isn't going to help because we don't know if this
// is the base.
//        $this->state = "off";
//        $this->thing->choice->load($this->keyword);

        $this->current_time = $this->thing->json->time();

    //    $this->variables_agent = new Variables($this->thing, "variables alias " . $this->from);

        $this->keywords = array('alias','is');


//                'block' => array('default run_time'=>'105',
//                                'negative_time'=>'yes'),

        $this->current_time = $this->thing->json->time();

        $default_alias_name = "alias";

//        $this->variables_agent = new Variables($this->thing, "variables " . $default_alias_name . " " . $this->from);

$this->thing->log($this->agent_prefix . '. ' . $this->thing->elapsed_runtime() . 'ms.');

        // Loads in Block variables.
        $this->get(); // Updates $this->elapsed_time; // And calls the variables-agent

$this->thing->log($this->agent_prefix . '. ' . $this->thing->elapsed_runtime() . 'ms.');

//echo $this->thing->log;

		// So created a token_generated_time field.
        //        $this->thing->json->setField("variables");
        //        $this->thing->json->writeVariable( array("stopwatch", "request_at"), $this->thing->json->time() );

//$this->thing->json->time()



//$this->thing->choice->Create('stopwatch', $this->node_list, 'stop');
//$this->thing->choice->Choose("midden work");

		$this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.</pre>');
		$this->thing->log($this->agent_prefix . 'received this Thing "'.  $this->subject . '".</pre>');


	// Read the elapsed time.  Or start.

 //               $this->current_time = $this->thing->json->time();


		// Read the subject as passed to this class.


		//$balance = array('amount'=>0, 'attribute'=>'transferable', 'unit'=>'tokens');
       		//$t = $this->thing->newAccount($this->uuid, 'token', $balance); //This might be a problem

		//$this->thing->account['token']->Credit(1);




		$this->readSubject();



        if ($this->agent_input == null) {
		    $this->respond();
        }


//        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );
        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime()-$this->start_time) . 'ms.' );

//        $this->thing->log($this->agent_prefix . 'completed.');

        $this->thing_report['log'] = $this->thing->log;


		return;

		}





    function set()
    {

        // A block has some remaining amount of resource and 
        // an indication where to start.


        // This makes sure that
        if (!isset($this->alias_thing)) {
            $this->alias_thing = $this->thing;
        }

        if (!isset($this->requested_state)) {
            $this->requested_state = $this->state;
        }


        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        // Update calculated variables.
        $this->alias_id = $this->context_id;

        $this->variables_agent->setVariable("state", $requested_state);
        $this->variables_agent->setVariable("alias", $this->alias);

        $this->variables_agent->setVariable("context", $this->context);
        $this->variables_agent->setVariable("alias_id", $this->alias_id); // exactly same as context id

        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

//        $this->thing->choice->save('alias', $this->state);


        $this->thing->json->writeVariable( array("alias", "state"), $requested_state );
        $this->thing->json->writeVariable( array("alias", "alias"), $this->alias );

        $this->thing->json->writeVariable( array("alias", "context"), $this->context );
        $this->thing->json->writeVariable( array("alias", "alias_id"), $this->alias_id ); // exactly same as context_id

        $this->thing->json->writeVariable( array("alias", "refreshed_at"), $this->current_time );






        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        return;
    }


    function getContext()
    {
        require_once '/var/www/html/stackr.ca/agents/context.php';
        $this->context_agent = new Context($this->thing, "context");
        $this->context =  $this->context_agent->context;
        $this->context_id = $this->context_agent->context_id;
        return $this->context;
    }

    function getAliases() {

        $this->aliases_list = array();

        require_once '/var/www/html/stackr.ca/agents/findagent.php';
        $findagent_thing = new FindAgent($this->thing, 'alias');

        $this->thing->log('Agent "Alias" found ' . count($findagent_thing->thing_report['things']) ." Alias Agent Things." );

        $this->thing->log('Agent "Alias". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );



        foreach ($findagent_thing->thing_report['things'] as $thing_object) {
            // While timing is an issue of concern

            $uuid = $thing_object['uuid'];

            if ($thing_object['nom_to'] != "usermanager") {

                $thing= new Thing($uuid);
                $variables = $thing->account['stack']->json->array_data;

                if (isset($variables['alias'])) {
                    $alias = $variables['alias']['alias'];
                    $this->aliases_list[] = $alias;
                }
            }
        }
        return $this->aliases_list;
    }


    function extractAliases($input = null)
    {
        if (!isset($this->aliases_list)) {$this->getAliases();}
         //$search_array = array_combine(array_map('strtolower', $this->aliases), $this->aliases);
            $search_array = null;
        if ($input == null) {$input = strtolower($this->subject);}

        $this->aliases = array();

        $pieces = explode(" ", strtolower($input));
        foreach ($pieces as $key=>$piece) {
            foreach ($this->aliases_list as $key=>$alias) {


        //        $search_array = array_combine(array_map('strtolower', $this->aliases), $this->aliases);


                if (isset($search_array[strtolower($piece)])) {


                } else {
                       $this->aliases[] = $alias;
                $search_array = array_combine(array_map('strtolower', $this->aliases), $this->aliases);
                }
//var_dump($this->aliases);

            }
        }
        return $this->aliases;
    }

    function get($train_time = null)
    {
        // Loads current alias into $this->alias_thing

        $this->get_start_time = $this->thing->elapsed_runtime();

        $this->thing->log('Agent "Alias". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        // First get context.
//require_once '/var/www/html/stackr.ca/agents/context.php';
//        $this->context_agent = new Context($this->thing, "context");
//        $this->context =  $this->context_agent->context;
//        $this->context_id = $this->context_agent->context_id;
//        $this->alias_id = $this->context_id;


        // This pairing takes 9s.
        $this->getContext();
        $this->alias_id = $this->context_id;



        // At this point we can either pull all matching alias, or
        // all matching context.

        // Pull alias will return the current (99) aliases regardless
        // of context. Pulling context will probably be a quicker 
        // search to the most recent matching alias.  Really it is 
        // about how to get to the right alias in the minimum possible
        // milliseconds.

        // And that is measured in 100s of thousands as of 15 Nov 2017 NT
 
        // With this pairing taking 6s 
        // This loads up a container for the current alias variables
        // This is needed for a basis "Alias" only text.

        $this->variables_agent = new Variables($this->thing, "variables alias " . $this->from);
        $this->variables_agent->getVariables();

        $this->thing->log('Agent "Alias".' . $this->thing->elapsed_runtime() . 'ms.' );

        // So if no alias records are returned, then this is the first
        // record to be set. A null call to set() will start things off.
        if ($this->variables_agent->alias != null) {
        $this->thing->log('Agent "Alias" did not find any existing Alias Things at all.  Setting up a new Alias Thing.' );
            $this->alias_thing = $this->thing;
            $this->set();
            return;
        }

        // Otherwise, we know we have at least a handful of 
        // existing aliases to check.

        $match = false;

        if ($train_time == null) {
            $train_time = $this->current_time;
        }


        // Pull in 99 most recent Aliases.
        // Each has an alias_id, contract_id and alias string
        $train_things = array();
        require_once '/var/www/html/stackr.ca/agents/findagent.php';
        $findagent_thing = new FindAgent($this->thing, 'alias');

        $this->thing->log('Agent "Alias" found ' . count($findagent_thing->thing_report['things']) ." Alias Agent Things." );

        $this->thing->log('Agent "Alias". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );


        // Now access which of these Aliases is closest.  To what?
        // To the four digit code for the current context

        $this->min_distance = 1e99;
        $this->found_thing = false;

        $index = 0;

        foreach ($findagent_thing->thing_report['things'] as $thing_object) {
            // While timing is an issue of concern
            if ($index>10) {break;}
            

            $index += 1;



            $uuid = $thing_object['uuid'];

            if ($thing_object['nom_to'] != "usermanager") {
                $match += 1;

                $thing= new Thing($uuid);
                $variables = $thing->account['stack']->json->array_data;

                if (isset($variables['alias'])) {
                    $alias = $variables['alias']['alias'];
                    $alias_id = $variables['alias']['alias_id'];
                    $context = $variables['alias']['context'];

                    $this->thing->log('Agent "Alias" got from stack - ' . $alias . " " . $alias_id . " " . $context . ".");

                } else {
                    // No alias variable set
                    // Try the next one.
                    break;
                }
            }

            // Code in selection criteria here.
            // Closed in time to the most current record.
            //$distance= levenshtein($this->subject, $alias);


            if  ( strtolower($this->alias_id) == strtolower($alias_id)  )
            {
                $this->thing->log('Agent "Alias" found closest Alias and Context match ' . $alias_id . " " . $context . ".");

                $this->found_thing = $thing;
                break;
            } else {
//                $thing->end_at = null;
            }
            $this->thing->log('Agent "Alias". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        }

        $this->thing->log('Agent "Alias". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );


        // If it drops through as Green, then no Aliases matched the current (???).
        if ($this->found_thing == false) {
            // No valid block found, so make a block record in current Thing
            // and set flag to Green ie accepting trains.

            $this->alias_thing = $this->thing;

            $this->alias = "Ivor";

            $this->makeAlias($this->alias);

            $this->thing->log('Agent "Alias" did not find a valid alias.' );

            //$this->makeBlock($this->current_time, "x");

        } else {

            $this->thing->log($this->agent_prefix . "found a valid alias.");

            // Red Block Thing - There is a current operating block on the stack.
            // Load the block details into this Thing.

            $this->alias_thing = $this->found_thing;


                    $this->alias = $alias;
                    $this->alias_id = $alias_id;
                    $this->context = $context;



echo "meep";
echo "<br>";echo "alias  " . $this->alias;
echo "<br>";echo "alias id " . $this->alias_id;
echo "<br>";echo "context " . $this->context;
echo "<br>";
//exit();

        }

        $this->set();
//echo $this->thing->log;
//exit();

        $this->thing->log('Agent "Alias". Get() function too ' . number_format($this->thing->elapsed_runtime() - $this->get_start_time) . 'ms.' );


        return;
    }

    function dropAlias() {
        $this->thing->log($this->agent_prefix . "was asked to drop an alias.");

        //$this->get(); No need as it ran on start up.

        // If it comes back false we will pick that up with an unset block thing.

        if (isset($this->alias_thing)) {
            $this->alias_thing->Forget();
            $this->alias_thing = null;
        }

        $this->get();
 
       return;
    }

    function runAlias() {

        $this->makeAlias($this->alias);

        $this->state = "running";

    }


    function makeAlias($alias = null) {


        //$this->alias = "Logan's run";

//        $this->getAlias();

        if ($alias == null) {
            $this->alias = "Logan's run";
        }
        $this->state = "stopped";



$this->thing->log($this->agent_prefix . 'will make an Alias with ' . $this->alias . ".");

        // Check that the shift is okay for making aliases.

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

            $this->alias = $alias;


//?
            $this->variables_thing = $this->thing;


        } else {

            $this->thing->log($this->agent_prefix . " checked the shift state: " . $shift_state . ".");
            // ... and decided there was already a shift running ...
            $this->alias = "meep";

        }

        $this->set();

        //$this->block_thing = $this->thing;

        $this->thing->log($this->agent_prefix . 'found an alias and made a Alias entry.');

    }


    function getVariable($variable_name = null, $variable = null) {

        // This function does a minor kind of magic
        // to resolve between $variable, $this->variable,
        // and $this->default_variable.

        // Doesn't yet do it's magic with...
        // identity_variable
        // thing_variable
        // stack_variable

        // Prefer closest...
        // Or prefer furthest ...


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




    function extractAlias($input = null) 
    {
        // Extract everything to the right
        // of the first is or =
        $pieces = explode(" ", strtolower($input));

        if ($input == null) { 
            $alias = "X";
            return $alias;
        } else {
            $keywords = $this->keywords;
            foreach ($pieces as $key=>$piece) {

                switch($piece) {
                    case '=':
                    case 'is':
                        $key += 1;
                        $t = "";
                        while ($key  < count($pieces)) {
                            //$key = $key +1;
                            $t .= $pieces[$key] . " ";
                            $key += 1;
                        }
                        $alias = $t;
                        return $alias;
                }


            }
        }

        $alias = "X";
        return $alias;
    }


    function getAlias($input = null) 
    {
        if (!isset($this->aliases)) {$this->extractAliases($input);}

        if (count($this->aliases) == 1) {
            $this->alias = $this->aliases[0];
            return $this->alias;
        }
        $this->alias = null;

        return $this->alias;
    }



    function getHeadcode() 
    {

        if ( (isset($this->head_code)) and (isset($this->headcode_thing)) ) { return $this->head_code;}

        $this->headcode_thing = new Headcode($this->variables_agent->thing, 'headcode '. $this->input);
        $this->head_code = $this->headcode_thing->head_code; 

        //if ($this->head_code == false) { // Didn't return a useable headcode.
        //    // So assign a 'special'.
        //    //$this->head_code = "0Z" . str_pad($this->index + 11,2, '0', STR_PAD_LEFT);
        //    $this->head_code = "2Z99";
        //}

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


    function read()
    {
        $this->thing->log("read");
        return $this->available;
    }



    function addAlias() {
        $this->makeAlias();
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
//        $this->thing->choice->Create('alias', $this->node_list, 'red');
/*
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("stopwatch", "refreshed_at"), $this->current_time);
        $this->thing->json->writeVariable( array("stopwatch", "elapsed"), $this->elapsed_time);
*/
//        $this->thing->choice->Choose('start');

        $this->set();

        return $this->quantity_available;
    }


	private function respond() {

		// Thing actions

		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "alias";

		//echo "<br>";

//		$choices = $this->thing->choice->makeLinks($this->state);
//		$this->thing_report['choices'] = $choices;
        $this->thing_report['choices'] = false;



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

		$sms_message = "ALIAS " . strtoupper($this->head_code);
        $sms_message .= " | flag " . $this->flag;
        $sms_message .= " | alias " . strtoupper($this->alias);

        $sms_message .= " | nuuid " . substr($this->variables_agent->uuid,0,4); 
        $sms_message .= " | nuuid " . substr($this->alias_thing->uuid,0,4); 


        $sms_message .= " | context " . $this->context;
        $sms_message .= " | alias id " . $this->alias_id; 

        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime()) . "ms";



        $head_code = strtoupper($this->head_code);
        if (!$this->thing->isData($head_code)) {$head_code = "X";}

//        $run_at = $this->trainTime($this->run_at);
//        if (!$this->thing->isData($run_at)) {$run_at = "X";}

//        if (!isset($this->route)) {
//            $route = "X";
//        } else {
//            $route = $this->route;
//        }

//        if (!isset($this->runtime)) {
//            $runtime = "X";
//        } else {
//            $runtime = $this->runtime;
//        }



    switch($this->index) {
        case null:
            $sms_message .=  " | TEXT ?";

            break;

        case '1':
          $sms_message .=  " | TEXT ALIAS";
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



        //if (!isset(


		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>Train state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $sms_message;

//		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

//        $test_message .= '<br>run_at: ' . $this->run_at;
 //       $test_message .= '<br>end_at: ' . $this->end_at;


//		$test_message .= '<br>Requested state: ' . $this->requested_state;

			$this->thing_report['sms'] = $sms_message;
			$this->thing_report['email'] = $sms_message;
			$this->thing_report['message'] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;


                        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;



		//$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);

$this->thing_report['help'] = 'This is the Aliasing manager.';



		return;


	}


    public function readSubject() 
    {
        $this->response = null;
        $this->num_hits = 0;




        $keywords = $this->keywords;

        $this->getAlias();

        // Bail at this point if
        // only extract wanted.
        if ($this->agent_input == 'extract') {
            if ($this->alias != false) {return;}
        }


        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        $this->input = $input;
		$haystack = $this->agent_input . " "  . $this->from . " " . $this->subject;

        $prior_uuid = null;

//        $this->getAlias($input);


        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($this->input == 'alias') {

                $this->get();   
                $this->set();
                return;
            }
        }

    if ($matches == 1) {
        //$this->alias = $piece;
        $this->num_hits += 1;
    }


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



    case 'next':
        $this->thing->log("read subject next Alias");
        $this->nextAlias();
        break;

   case 'drop':
        $this->dropAlias();
        break;


   case 'add':
        $this->makeAlias();
        break;

   case 'run':
        $this->runAlias();
        break;

   case 'is':
        //$this->alias = $this->input;
        $this->alias = $this->extractAlias($input);
        $this->makeAlias($this->alias);
        $this->set();
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

if ( (count($uuids) == 1) and (count($head_codes) == 1) and (isset($this->alias)) ) {

    // Likely matching a head_code to a uuid.

}

// So we know we don't just have a keyword.

if  (isset($this->alias)) {

//$this->thing->log('Agent "Block" found a run_at and a run_time and made a Block.'$
    // Likely matching a head_code to a uuid.
    $this->makeAlias($this->alias);
    return;
}


if ($pieces[0] == "alias") {
    $this->makeAlias($this->input);
    $this->set();
    //$this->alias = "meepmeep"; 
    return;
}


//if  (isset($this->alias)) {

//$this->thing->log('Agent "Block" found a run_at and a run_time and made a Block.');
    // Likely matching a head_code to a uuid.
//    $this->makeAlias($this->alias);
//    return;/
//}




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


                      //  echo '<pre> Agent "Train" normalized discrimators "';print_r($normalized);echo'"</pre>';


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

