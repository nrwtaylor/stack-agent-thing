<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Alias
{

    // This is the alias manager.  It assigns an alias coding to 
    // N-grams which are the same idea gram.

    // User case: 'Madison and Hastings' is aliased to '51380'
    // This is Place context.

    // Usage:
    // Alias - returns current alias
    // Slowly.

    // It needs to return the latest alias record for the current context.
    // So first find the context.
    // Then find the latest alias record in that context.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) {

        if ($agent_input == null) {$agent_input = "";}
        $this->agent_input = $agent_input;
        $this->keyword = "alias";

        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();
        $this->thing_report['thing'] = $this->thing->thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        $this->agent_name = "alias";
        $this->agent_prefix = 'Agent "Alias" ';

// $this->node_list = array("green"=>array("red"=>array("green","red"),"red"),"green3");
//$this->thing->choice->load('alias');
        $this->node_list = array("off"=>array("on"=>array("off")));

// This isn't going to help because we don't know if this
// is the base.
//        $this->state = "off";
//        $this->thing->choice->load($this->keyword);

        $this->current_time = $this->thing->json->time();

        $this->variables_agent = new Variables($this->thing, "variables alias " . $this->from);

        $this->keywords = array('alias','is');


        $this->context = null;
        $this->alias = null;
        $this->alias_id = null;

        $this->current_time = $this->thing->json->time();

        $default_alias_name = "alias";

//        $this->variables_agent = new Variables($this->thing, "variables " . $default_alias_name . " " . $this->from);

        $this->thing->log($this->agent_prefix . '. ' . $this->thing->elapsed_runtime() . 'ms.', "OPTIMIZE");

        // Loads in Block variables.
        //$this->get(); // Updates $this->elapsed_time; // And calls the variables-agent

        $this->thing->log($this->agent_prefix . '. ' . $this->thing->elapsed_runtime() . 'ms.', "OPTIMIZE");

//echo $this->thing->log;

		// So created a token_generated_time field.
        //        $this->thing->json->setField("variables");
        //        $this->thing->json->writeVariable( array("stopwatch", "request_at"), $this->thing->json->time() );

//$this->thing->json->time()



//$this->thing->choice->Create('stopwatch', $this->node_list, 'stop');
//$this->thing->choice->Choose("midden work");

		$this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.',"INFORMATION");
		$this->thing->log($this->agent_prefix . 'received this Thing "'.  $this->subject . '".', "INFORMATION");


		$this->readSubject();

        $this->set();
        if ($this->agent_input == null) {
         
		    $this->respond();
        }


        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime()-$this->start_time) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;
        //$this->thing_report['txt'] = "Meep";


		return;

    }



    function getState() {

        if (!isset($this->state)) {$this->state = "X";}

        return $this->state;

    }

    function set()
    {

        // A block has some remaining amount of resource and 
        // an indication where to start.


        // This makes sure that
        if (!isset($this->alias_thing)) {
            $this->alias_thing = $this->thing;
        }

//        if (!isset($this->requested_state)) {
//            $this->requested_state = $this->getState();
//        }


//        if ( (!isset($request_state)) or ($requested_state == null) ) {
//            $requested_state = $this->requested_state;
//        }

//        $this->extractAlias();

        // Update calculated variables.
//w        $this->alias_id = $this->context_id;

//        $this->variables_agent->setVariable("state", $requested_state);
        $this->variables_agent->setVariable("alias", $this->alias);

        $this->variables_agent->setVariable("context", $this->context);
        $this->variables_agent->setVariable("alias_id", $this->alias_id); // exactly same as context id

        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

//        $this->thing->choice->save('alias', $this->state);

        
//        $this->thing->json->writeVariable( array("alias", "state"), $requested_state );
        $this->thing->json->writeVariable( array("alias", "alias"), $this->alias );

        $this->thing->json->writeVariable( array("alias", "context"), $this->context );
        $this->thing->json->writeVariable( array("alias", "alias_id"), $this->alias_id ); // exactly same as context_id

        $this->thing->json->writeVariable( array("alias", "refreshed_at"), $this->current_time );



  $this->thing->log($this->agent_prefix . ' thought '.  $this->alias . " " . $this->context . " " . $this->alias_id . ".");




//        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        return;
    }


    function extractContext($input = null)
    {
        $this->context_agent = new Context($this->thing, "context " . $input);

        $this->context =  $this->context_agent->context;
        $this->context_id = $this->context_agent->context_id;

        $this->thing->log($this->agent_prefix . ' got context '.  $this->context . " " . $this->context_id . ". ", "DEBUG");

        return $this->context;
    }

    function getAliases() {

        $this->aliases_list = array();

        $findagent_thing = new FindAgent($this->thing, 'alias');

        $this->thing->log('Agent "Alias" found ' . count($findagent_thing->thing_report['things']) ." Alias Agent Things." );
        $this->thing->log('Agent "Alias". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        foreach ($findagent_thing->thing_report['things'] as $thing_object) {
            // While timing is an issue of concern

            $uuid = $thing_object['uuid'];

//echo $thing_object['task'];

            if ($thing_object['nom_to'] != "usermanager") {

                $thing= new Thing($uuid);
                $variables = $thing->account['stack']->json->array_data;

                if ( (isset($variables['alias'])) and (isset($variables['alias']['alias'])) ) {
                    // prod

               //     (isset($variables['alias'])) {
                    $alias = $variables['alias']['alias'];

                    $variables['alias'][] = $thing_object['task'];
                    $this->aliases_list[] = $variables['alias'];

                }
            }
        }

        return $this->aliases_list;
    }


    function extractAliases($input = null)
    {
        // Get the list of aliases
        if (!isset($this->aliases_list)) {$this->getAliases();}
        //$search_array = array_combine(array_map('strtolower', $this->aliases), $this->aliases);

        $search_array = null;
        if ($input == null) {$input = strtolower($this->subject);}

        $this->aliases = array();

        $pieces = explode(" ", strtolower($input));
        foreach ($pieces as $key=>$piece) {
            foreach ($this->aliases_list as $key=>$alias_arr) {

        //        $search_array = array_combine(array_map('strtolower', $this->aliases), $this->aliases);

                $alias = $alias_arr['alias'];

                if (isset($search_array[strtolower($piece)])) {

                } else {
                    $alphanum_alias = preg_replace("/[^A-Z]+/", "", $alias);
                    $this->aliases[] = $alphanum_alias;
                    $search_array = array_combine(array_map('strtolower', $this->aliases), $this->aliases);
                }

            }
        }
        return $this->aliases;
    }

    function get($train_time = null)
    {
        // Loads current alias into $this->alias_thing

        $this->get_start_time = $this->thing->elapsed_runtime();

        $this->thing->log('Agent "Alias". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        $this->variables_agent = new Variables($this->thing, "variables alias " . $this->from);
        $this->variables_agent->getVariables();

        $this->thing->log('Agent "Alias". Timestamp ' . $this->thing->elapsed_runtime() . 'ms.' );

        // So if no alias records are returned, then this is the first
        // record to be set. A null call to set() will start things off.


       // if ($this->variables_agent->alias != null) {
        // Otherwise, we know we have at least a handful of 
        // existing aliases to check.


        // Filter by context_id
        $this->getAliases();
        $aliases = array();
        foreach ($this->aliases_list as $key=>$alias) {
//            echo "alias " .$alias['alias'] . " alias_id " . $alias['alias_id'] . " context " . $alias['context'] . " is " . $alias['context'];
//            echo "<br>";
            if ($alias['alias_id'] == $this->context_id) {
                $aliases[] = $alias;
            }
        }

        if (count($aliases) == 0) {

            $this->alias = "Random";

        } else {

            $this->alias = $aliases[0]['alias'];
            $this->alias_id = $aliases[0]['alias_id'];

//echo "<br>";echo "alias  " . $this->alias . " " . $this->alias_id;
//exit();
//echo "<br>";echo "alias id " . $this->alias_id;
//echo "<br>";echo "context " . $this->context;
//echo "<br>";
//exit();

        }

//        $this->set();

//        $this->thing->log('Agent "Alias". Get() function too ' . number_format($this->thing->elapsed_runtime() - $this->get_start_time) . 'ms.' );


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


    function makeAlias($alias = null)
    {



        $this->thing->log($this->agent_prefix . 'will make an Alias with ' . $this->alias . ".");

        // Check that the shift is okay for making aliases.

//        $shift_thing = new Shift($this->thing);
//        $shift_state = strtolower($this->thing->log($shift_thing->thing_report['keyword']));

        $allow_create_alias = true;

        if ($allow_create_alias) {

            // Only if the shift state is off can we 
            // create blocks on the fly.

            // Otherwise we needs to make trains to run in the block.

            $this->thing->log($this->agent_prefix . "found that this is the Off shift.");

            $this->thing->log($this->agent_prefix . 'found an alias ' . $this->alias . 'and made a Alias entry' . $this->alias_id . '.');

            //$this->set();

        } else {

        $this->thing->log($this->agent_prefix . 'was not allowed to make a Alias entry.');

            // ... and decided there was already a shift running ...
//            $this->alias = "meep";

        }

  //      $this->set();


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

        $input = strtolower($this->subject);

        $keywords = array('is');
        $pieces = explode(" is ", strtolower($input));


//        $this->max_ngram = 10;
        if (count($pieces) == 2) {
            // A left and a right pairing and nothing else.
            // So we can substitute the word and pass it to Alias.

            $this->left_grams = $pieces[0];
            $this->right_grams = $pieces[1];

            $left_num_words = count(explode(" ", $this->left_grams));
            $right_num_words = count(explode(" ", $this->right_grams));

            if ($left_num_words < $right_num_words) {
                $this->alias_id = $this->left_grams;
                $this->alias = $this->right_grams;
            } else {
                $this->alias_id = $this->right_grams;
                $this->alias = $this->left_grams;
            }

//            if ($left_num_words <= $this->max_ngram) {

                // Could call this as a Gearman worker.
                // Pass it to Alias which handles is/alias as the same word.
                //$instruction = $left_grams . " alias " . $right_grams; 

            if ($this->alias == "place") {
                // Okay straight to Place
                $place_agent = new Place($this->thing);
                return;

            }


            return;


        }
}
        $alias = "X";
        return $alias;
    }

/*
    function extractAlias($input = null) 
    {
        if (!isset($this->aliases)) {$this->extractAliases($input);}

        if (count($this->aliases) == 1) {
            $this->alias = $this->aliases[0];
            return $this->alias;
        }

        if (count($this->aliases) == 0) {
            $this->alias = "meep";
            return $this->alias;
        }

        $this->alias = $this->aliases[0];
        return $this->alias;
    }
*/

/*
    function getHeadcode() 
    {

        if ( (isset($this->head_code)) and (isset($this->headcode_thing)) ) { return $this->head_code;}

        $this->headcode_thing = new Headcode($this->variables_agent->thing, 'headcode '. $this->input);
        $this->head_code = $this->headcode_thing->head_code; 

        return $this->head_code;
    }
*/


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



   function makeTXT()
    {

        if (!isset($this->aliases_list)) {$this->get();}


        $txt = 'These are ALIASES for RAILWAY ' . $this->variables_agent->nuuid . '. ';
        $txt .= "\n";
        $txt .= count($this->aliases_list). ' Aliases retrieved.';

        $txt .= "\n";


            //$txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
//            $txt .= " " . str_pad("HEAD", 4, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad("ALIAS", 24, " " , STR_PAD_RIGHT);
//            $txt .= " " . str_pad("DAY", 4, " ", STR_PAD_LEFT);

//            $txt .= " " . str_pad("RUNAT", 6, " ", STR_PAD_LEFT);
//            $txt .= " " . str_pad("ENDAT", 6, " ", STR_PAD_LEFT);

            $txt .= " " . str_pad("ALIAS_ID", 8, " ", STR_PAD_LEFT);

     $txt .= " " . str_pad("CONTEXT", 6, " ", STR_PAD_LEFT);
//     $txt .= " " . str_pad("QUANTITY", 9, " ", STR_PAD_LEFT);
//     $txt .= " " . str_pad("CONSIST", 6, " ", STR_PAD_LEFT);/
//     $txt .= " " . str_pad("ROUTE", 6, " ", STR_PAD_LEFT);


        $txt .= "\n";
        $txt .= "\n";

        foreach($this->aliases_list as $key=>$alias) {

            //$txt .= implode(" ", $train);
     //       $txt .= str_pad($train['index'], 7, '0', STR_PAD_LEFT);
       //     $txt .= " " . str_pad(strtoupper($train['head_code']), 4, "X", STR_PAD_LEFT);
            $txt .= " " . str_pad($alias['alias'], 24, " " , STR_PAD_RIGHT);
    
    //        $day = strtoupper(substr($this->trainDay($train['run_at']),0,3));
    //        $txt .= " " . str_pad($day, 4, " ", STR_PAD_LEFT);

    //        $txt .= " " . str_pad($this->trainTime($train['run_at']), 6, " ", STR_PAD_LEFT);
    //        $txt .= " " . str_pad($this->trainTime($train['end_at']), 6, " ", STR_PAD_LEFT);

if (!isset($alias['alias_id'])) {$alias['alias_id'] = "X";}
if (!isset($alias['context'])) {$alias['context'] = "X";}

            $txt .= " " . str_pad($alias['alias_id'], 8, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($alias['context'], 6, " ", STR_PAD_LEFT);
       //     $txt .= " " . str_pad($train['quantity'], 9, " ", STR_PAD_LEFT);
        //    $txt .= " " . str_pad($train['consist'], 6, " ", STR_PAD_LEFT);
        //    $txt .= " " . str_pad($train['route'], 6, " ", STR_PAD_LEFT);


            $txt .= "\n";

        }

        $txt .= "\n";
        $txt .= "---\n";

        $txt .= "alias is " . $this->alias . "\n";
        $txt .= "context is " . $this->context . "\n";
        $txt .= "alias_id is " . $this->alias_id . "\n";

        $txt .= "---";

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

        return $txt;
    }



	private function respond()
    {
		// Thing actions
		$this->thing->flagGreen();

		// Generate email response.
		$to = $this->thing->from;
		$from = "alias";

        $this->makeTXT();

//exit();
//		$choices = $this->thing->choice->makeLinks($this->state);
//		$this->thing_report['choices'] = $choices;
        $this->thing_report['choices'] = false;

        //$interval = date_diff($datetime1, $datetime2);
        //echo $interval->format('%R%a days');

//        $available = $this->thing->human_time($this->available);

        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index;
        }


        $this->makeSMS();
        $this->makeChoices();
        $this->thing_report['email'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->thing_report['help'] = 'This is the Aliasing manager.';

		return;
	}

    private function makeChoices()
    {

        if (!isset($this->choices)) {
            $this->thing->choice->Create($this->agent_name, $this->node_list, "alias");
            $this->choices = $this->thing->choice->makeLinks('alias');
        }
        $this->thing_report['choices'] = $this->choices;


    }

    private function makeSMS() 
    {
        if (!isset($this->sms_messages)) {
            $this->sms_messages = array();
        }

        $this->sms_messages[] = "ALIAS | Could not find an agent to respond to your message.";
        $this->node_list = array("alias"=>array("agent","message"));

        $sms_message = "ALIAS " . strtoupper($this->alias_id);
        $sms_message .= " | alias " . strtoupper($this->alias);

        $sms_message .= " | nuuid " . substr($this->variables_agent->uuid,0,4); 
        $sms_message .= " | nuuid " . substr($this->alias_thing->uuid,0,4); 

        $sms_message .= " | context " . $this->context;
        $sms_message .= " | alias id " . $this->alias_id;

        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime()) . "ms";

        $this->sms_messages[] = $sms_message;

        $this->sms_message = $this->sms_messages[0];
        $this->thing_report['sms'] = $this->sms_messages[0];
        return;
    }

    function isPlace($input)
    {
        // recognize a place on the alias list

        foreach ($this->aliases_list as $key=>$alias_list) {
            $alias = $alias_list['alias'];
            $alias_id = $alias_list['alias_id'];
            $alias_timestamp = $alias_list['refreshed_at'];
            $context = $alias_list['context'];

            if($alias == null){continue; echo "meep";}

            // building this for two people
            if (strpos($input, strtolower($alias)) !== false) {
                // never like these double-ifs, but it's kind of clear
                // that we are check both the alias first.
                //        echo 'found alias';
                return "green";
            }

            if (strpos($input, $alias_id) !== false) {
                // alias found the word in it's list of alias_ids
                // possibly tells us the alias_id generator is
                // quite liberal with it's identifiers.
                //        echo 'found alias_id';
                return "green";
            }

            // run - see if it works.  delete comment.  keep going.
        }

        return "red";

    }

    public function readSubject() 
    {

        $this->response = null;
        $this->num_hits = 0;

        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        $this->extractAlias($input);

        $this->getAliases();


        // Bail at this point if
        // only extract wanted.
        if ($this->agent_input == 'extract') {

            // Added return here March 17 2018
            return;
            if ($this->alias != false) {return;}
        }

        $this->extractContext();

        $this->input = $input;
		$haystack = $this->agent_input . " "  . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword

        if (count($pieces) == 1) {

            if ($this->input == 'alias') {
                $this->get();   
                //$this->set();
                $this->num_hits += 1;
                return;
            }
        }


    foreach ($pieces as $key=>$piece) {
        foreach ($this->keywords as $command) {
            if (strpos(strtolower($piece),$command) !== false) {

                switch($piece) {


   case 'drop':
        $this->dropAlias();
        break;


   case 'add':
        $this->makeAlias();
        break;


   case 'is':
        //$this->alias = $this->input;
        //$this->alias = $this->extractAlias($input);

        $this->makeAlias($this->alias);
        //$this->set();
        return;

    default:
        //$this->read();                                                    //echo 'default';

                                        }

                                }
                                      }
//exit();
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

// Guess we check if it's a Place then?

if ($this->isPlace($input)) {
    $place_thing = new Place($this->thing); // no agent because this now takes message priority
    $this->thing_report['info'] = 'Agent "Alias" sent the datagram to Place';
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
