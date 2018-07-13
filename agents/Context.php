<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Context 
{

    // This is the Context manager.

    // Usage:
    // Context - return the current users context.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) {

        //if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->keyword = "context";

        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();
        $this->thing_report['thing'] = $this->thing->thing;


        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;


        $this->agent_prefix = 'Agent "Context" ';

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log($this->agent_prefix . 'received this Thing "'.  $this->subject . '".');

        $this->contexts = array('identity'=> 'uuid',
                            'headcode'=>'head_code',
                            'train'=>'head_code',
                            'transit'=>'transit_id',
                            'circus'=>null,
                            'event'=>null,
                            'place'=>null,
                            'group'=>'group_id');

// $this->node_list = array("green"=>array("red"=>array("green","red"),"red"),"green3");
//$this->thing->choice->load('alias');
        $this->node_list = array("off"=>array("on"=>array("off")));

// This isn't going to help because we don't know if this
// is the base.
//        $this->state = "off";
//        $this->thing->choice->load($this->keyword);

        $this->current_time = $this->thing->json->time();

    //    $this->variables_agent = new Variables($this->thing, "variables alias " . $this->from);

        $this->keywords = array('context');


//                'block' => array('default run_time'=>'105',
//                                'negative_time'=>'yes'),

        $this->current_time = $this->thing->json->time();

        //$default_alias_name = "alias";

//        $this->variables_agent = new Variables($this->thing, "variables " . $default_alias_name . " " . $this->from);
    $this->verbosity = 9;

    $this->requested_state = false;
    $this->index = 0;

        // Loads in Block variables.
        $this->get(); // Updates $this->elapsed_time; // And calls the variables-agent

        $this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );



		// So created a token_generated_time field.
        //        $this->thing->json->setField("variables");
        //        $this->thing->json->writeVariable( array("stopwatch", "request_at"), $this->thing->json->time() );

//$this->thing->json->time()







		$this->readSubject();
        $this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

//        $this->set();

        if ($this->agent_input == null) {
		    $this->respond();
        }


		//$this->thing->log($this->agent_prefix . 'completed.');


        $this->thing->log( $this->agent_prefix .'deduced ' . $this->context . " " . $this->context_id . '.' );

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;

    	return;

    }





    function set()
    {

        // A block has some remaining amount of resource and 
        // an indication where to start.


        // This makes sure that
        if (!isset($this->context_thing)) {
            $this->context_thing = $this->thing;
        }

//        if ($requested_state == null) {
            $requested_state = $this->requested_state;
//        }

        // Update calculated variables.

//        $this->context_thing->setVariable("state", $requested_state);
//        $this->variables_agent->setVariable("context", $this->context);
//        $this->variables_agent->setVariable("context_id", $this->context_id);

        $this->context_thing->json->writeVariable( array("context", "state"), $requested_state );

        $this->context_thing->json->writeVariable( array("context", "context"), $this->context );
        $this->context_thing->json->writeVariable( array("context", "context_id"), $this->context_id );

        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

//!        $this->thing->choice->save('context', $this->state);

        $this->thing->json->writeVariable( array("context", "state"), $requested_state );

        $this->thing->json->writeVariable( array("context", "context"), $this->context );
        $this->thing->json->writeVariable( array("context", "context_id"), $this->context_id );
        $this->thing->json->writeVariable( array("context", "refreshed_at"), $this->current_time );

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        return;
    }


    function getContexts()
    {
        $context_things = array();
        $this->previous_contexts = array();

        // See if a context record exists.
//        require_once '/var/www/html/stackr.ca/agents/findagent.php';
        $findagent_thing = new FindAgent($this->thing, 'thing');

        $this->max_index =0;
        $match = 0;

/*
        $this->contexts = array('identity'=>'uuid',
                            'headcode'=>'head_code',
                            'train'=>'head_code',
                            'transit'=>'transit_id',
                            'circus'=>null,
                            'event'=>null,
                            'place'=>null,
                            'group'=>'group_id');
*/
/*
        foreach ($findagent_thing->thing_report['things'] as $thing_object) {
            $ref_time = microtime(true);
            $uuid = $thing_object['uuid'];

            $thing= new Thing($uuid);

            foreach ($this->contexts as $context=>$context_id) {
                //echo $context; echo $context_id;
                //echo "<br>";                
//exit();

                $variables = $thing->account['stack']->json->array_data;

                if (isset($variables[$context])) {


                    $this->context = $context;
                    $this->context_id = $variables[$context][$context_id];
                    //break;

                    $this->previous_contexts[] = array("context"=>$this->context, "id"=>$this->context_id, "task"=>$thing_object['task']);;
                }

            }
        $run_time = microtime(true) - $ref_time;
        $milliseconds = round($run_time * 1000);
        if ($this->verbosity == 9) {
        $this->thing->log( $this->agent_prefix .' context get forloop ' . $milliseconds . 'ms.' );
        }

        }

*/

        foreach ($findagent_thing->thing_report['things'] as $thing_object) {
            $ref_time = microtime(true);
//            $this->thing->log($thing_object['task'] . " " . $thing_object['nom_to'] . " " . $thing_object['nom_from']);
            $uuid = $thing_object['uuid'];

            if ($thing_object['nom_to'] != "usermanager") {
                $match += 1;

               //x $thing= new Thing($uuid);

            $variables_json= $thing_object['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);

//                $this->variables_agent = new Variables($thing, "variables context " . $this->from);
//                $this->variables_agent->getVariables();

               //x $variables = $thing->account['stack']->json->array_data;

                if (isset($variables['train']['head_code'])) {
                    $this->context = "train";
                    $this->context_id = $variables['train']['head_code'];
                    break;
                }

                if (isset($variables['headcode']['head_code'])) {
                    $this->context = "headcode";
                    $this->context_id = $variables['headcode']['head_code'];
                    break;
                }

                if (isset($variables['group']['group_id'])) {
                    $this->context = "group";
                    $this->context_id = $variables['group']['group_id'];
                    break;
                }

                if (isset($variables['transit']['transit_id'])) {
                    $this->context = "transit";
                    $this->context_id = $variables['group']['transit_id'];
                    break;
                }

        $run_time = microtime(true) - $ref_time;
        $milliseconds = round($run_time * 1000);
        if ($this->verbosity == 9) {
        $this->thing->log( $this->agent_prefix .' context get forloop ' . $milliseconds . 'ms.' );
        }

                if ($match >= 20) {
                    $this->context = null;
                    $this->context_id = null;
                    break;
                }
            }
        }

                $thing= new Thing($uuid);

                $this->variables_agent = new Variables($thing, "variables context " . $this->from);
                $this->variables_agent->getVariables();



 $this->thing->log($this->agent_prefix . "looked at " . $match . " Things before finding " . $this->context_id . " one with " . $this->context . " Context.");

        return $this->context;
    }



    function get($train_time = null)
    {
        $this->getContexts();

        $this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

        $this->set();
        $this->thing->log( $this->agent_prefix .'. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

        return;
    }

    function makeContext($context = null)
    {
        //$this->alias = "Logan's run";

//        $this->getAlias();

        if ($context == null) {
            $this->context = "identity";
        }
        //$this->state = "stopped";



$this->thing->log($this->agent_prefix . 'will make an Context with ' . $this->context . ".", "INFORMATION");

        // Check that the shift is okay for making aliases.

//        require_once '/var/www/html/stackr.ca/agents/shift.php';
//        $shift_thing = new Shift($this->thing);
//        $shift_state = strtolower($this->thing->log($shift_thing->thing_report['keyword']));

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

            $this->context = $context;


//?
            $this->variables_thing = $this->thing;


        } else {

            $this->thing->log($this->agent_prefix . " checked the shift state: " . $shift_state . ".");
            // ... and decided there was already a shift running ...
            $this->context = "meep";

        }

        $this->set();

        //$this->block_thing = $this->thing;

        $this->thing->log($this->agent_prefix . 'found an context and made a Context entry.');

    }

    function extractContext()
    {
//        set_error_handler(array($this, '\Nrwtaylor\StackAgentThing\warning_handler'), E_WARNING);
//        set_error_handler('\Nrwtaylor\StackAgentThing\warning_handler', E_WARNING);
        set_error_handler(array($this, 'warning_handler'), E_WARNING);

//        set_error_handler("warning_handler", E_WARNING);

        foreach ($this->contexts as $context=>$context_id) {


            $agent_class_name = ucfirst($context);
                $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;


            try {
                //include_once __DIR__ ."/". $agent_class_name . '.php';
                //$agent = new $agent_class_name($this->thing, $context);
                //$agent = new $agent_class_name($this->thing, "extract");
                $agent = new $agent_namespace_name($this->thing, "extract");

                if (isset($agent->{$context_id})) {

                    //echo $context; echo "<br>";
                    //echo $context_id; echo "<br>";
                    //echo $agent->{$context_id}; echo "<p>";
                    $success = true;
                    break;
                }

            } catch (\Exception $e) {
                $success = false;
            }

        }

        restore_error_handler();
//var_dump($this->thing->thing);
        $this->context = $context;
        $this->context_id = $agent->{$context_id};

$this->thing->log($this->agent_prefix . 'extracted ' . $this->context . ' ' . $this->context_id . ".","INFORMATION");

        return;

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



    function getContext() 
    {
        $this->getHeadcode();
        $head_code = $this->headcode_thing->head_code;


        if ($head_code == null) { 
            $this->context = "train";
            $this->context_id = "ivor";
            $this->alias_id = "ivor";
        } else {
            $this->context = "train";
            $this->context_id = $head_code;
            $this->alias_id = $this->context_id;
        }


        return $this->context;
    }

    function getHeadcode()
    {
        if ( (isset($this->head_code)) and (isset($this->headcode_thing)) ) { return $this->head_code;}

        //$this->headcode_thing = new Headcode($this->variables_agent->thing, 'headcode '. $this->input);
        $this->headcode_thing = new Headcode($this->variables_agent->thing, 'extract');

        $this->head_code = $this->headcode_thing->head_code; 

        return $this->head_code;
    }

    function addContext() {
        $this->makeContext();
        $this->get();
        return;
    }

    private function makeSMS()
    {
        $sms_message = "CONTEXT IS " . strtoupper($this->context);

        $sms_message .= " | context id " . $this->context_id; 
        $sms_message .= " | nuuid " . substr($this->variables_agent->variables_thing->uuid,0,4); 
        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime()) . 'ms';
        $sms_message .=  " | TEXT ?";


        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
        return;
    }


    private function makeTXT()
    {
     //   if (!isset($this->previous_contexts)) {
     //       $this->getContexts();
     //   }

        $txt = 'This is the CONTEXT for RAILWAY ' . $this->variables_agent->nuuid . '. ';
        $txt .= "\n";
        $txt .= count($this->previous_contexts). ' Contexts retrieved.';
        $txt .= "\n";

        $txt .= 'Context is ' . $this->context . ' ' . $this->context_id . ".";

        $txt .= "\n";
            $txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
            $txt .= " " . str_pad("CONTEXT", 10, " ", STR_PAD_RIGHT);
            $txt .= " " . str_pad("CONTEXT ID", 10, " " , STR_PAD_RIGHT);
            $txt .= " " . str_pad("TASK", 53, " ", STR_PAD_RIGHT);



        $txt .= "\n";
        $txt .= "\n";

        foreach($this->previous_contexts as $key=>$context) {
            //$txt .= implode(" ", $train);

            $txt .= str_pad($train['index'], 7, '0', STR_PAD_LEFT);
            $txt .= " " . str_pad(strtoupper($context['context']), 10, " ", STR_PAD_RIGHT);
            $txt .= " " . str_pad(strtoupper($context['id']), 10, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad(strtoupper($context['task']), 53, " ", STR_PAD_RIGHT);

            $txt .= "\n";
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;


    }





	private function respond() {

		// Thing actions

		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "context";

		//echo "<br>";

		//!$choices = $this->thing->choice->makeLinks($this->state);
		//!$this->thing_report['choices'] = $choices;

        $this->thing_report['choices'] = false;
        $this->makeSMS();
        $this->makeTXT();


//		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
      $test_message = 'Last thing heard: "' . $this->subject . '"';

		$test_message .= '<br>Train state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $this->sms_message;

//		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

//        $test_message .= '<br>run_at: ' . $this->run_at;
//        $test_message .= '<br>end_at: ' . $this->end_at;


//		$test_message .= '<br>Requested state: ' . $this->requested_state;

//			$this->thing_report['sms'] = $sms_message;
			$this->thing_report['email'] = $this->sms_message;
			$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;


                        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->thing_report['help'] = 'This is the context extractor.';

		return;


	}


    public function readSubject() 
    {
        $this->response = null;
        $this->num_hits = 0;

        $this->extractContext();
        if ($this->context_id != null) {return;}

        if (strtolower($this->agent_input) == 'extract') {
            $this->getContexts();
            return;
        }


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

		$haystack = $this->agent_input . " "  . $this->from . " " . $this->subject;

//		$this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));


		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($this->input == 'context') {

                $this->set();
                return;
            }
        }


    // Look here if there is a problem with Context. NRWTAYLOR 6 Dec 2017  
    //if ($matches == 1) {
    //    $this->context = $piece;
    //    $this->num_hits += 1;
    //}


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



    case 'next':
        $this->thing->log("read subject next Context");
        $this->nextContext();
        break;

   case 'drop':
   //     //$this->thing->log("read subject nextblock");
        $this->dropContext();
        break;


   case 'add':
   //     //$this->thing->log("read subject nextblock");
        $this->makeContext();
        break;

   case 'run':
   //     //$this->thing->log("read subject nextblock");
        $this->runContext();
        break;

   case 'is':
   //     //$this->thing->log("read subject nextblock");
        $this->context = $this->input;
        $this->makeContext($this->context);
        $this->set();
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
/*
if ( (count($uuids) == 1) and (count($head_codes) == 1) and (isset($this->context)) ) {

    // Likely matching a head_code to a uuid.

}
*/
// So we know we don't just have a keyword.

if ($pieces[0] == "context") {
    $this->makeContext($this->input);
    $this->set();
    //$this->alias = "meepmeep"; 
    return;
}


if  (isset($this->context)) {

//$this->thing->log('Agent "Block" found a run_at and a run_time and made a Block.');
    // Likely matching a head_code to a uuid.
    $this->makeContext($this->context);
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

//    $this->read();
$this->set();



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



                $contexts = array();

                $contexts['accept'] = array('accept','add','+');
                $contexts['clear'] = array('clear','drop', 'clr', '-');



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

                                foreach ($contexts[$discriminator] as $context) {

                                        if ($word == $context) {
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


                        //echo '<pre> Agent "Train" normalized discrimators "';print_r($normalized);echo'"</pre>';


                if ($delta >= $minimum_discrimination) {
                        //echo "discriminator" . $discriminator;
                        return $selected_discriminator;
                } else {
                        return false; // No discriminator found.
                } 

                return true;
        }

function warning_handler($errno, $errstr) { 
 
   //throw new \Exception('Class not found.');

    //trigger_error("Fatal error", E_USER_ERROR);

    //echo $errno;
    //echo $errstr;
    // do something
}


}

?>
