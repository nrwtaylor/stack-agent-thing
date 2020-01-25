<?php


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Similar extends Agent 
{

    public $var = 'hello';

  //  function __construct(Thing $thing, $agent_input = null) {
function init() {
//        $this->start_time = microtime(true);
//        $this->agent_instruction = $agent_input;


        //if ($agent_input == null) {$agent_input = "";}

//        $this->agent_input = $agent_input;
        $this->keyword = "similar";
  //      $this->agent_prefix = 'Agent "' . ucwords($this->keyword) . '" ';

    //    $this->thing = $thing;
   //     $this->thing_report['thing'] = $this->thing->thing;

        $this->verbosity = 1;

if (isset($this->settings['verbosity'])) {$this->verbosity= $this->settings['verbosity'];}

        if ($this->verbosity >= 2) {
            $this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid . ".");
        }

        $this->start_time = $this->thing->elapsed_runtime();

        $this->test= "Development code"; // Always

   //     $this->uuid = $thing->uuid;
   //     $this->to = $thing->to;
   //     $this->from = $thing->from;
   //     $this->subject = $thing->subject;
  //      $this->sqlresponse = null;

        if ($this->verbosity >= 2) {
            $this->thing->log($this->agent_prefix . 'received this Thing, "' . $this->subject .  '".') ;
        }

        // Set up default flag settings
//        $this->verbosity = 1;
        $this->requested_flag = null;
        $this->default_flag = "green";

        $this->requested_thing_name = 'thing';
        $this->horizon = 15; // Sounds about right.
        // Allows for manual overloading of input.  Gets boring.
        // Allows a 15 day horizong (14+1) which is a good default
        // for repeating daily scheduling patterns.

        $this->node_list = array("green"=>array("red"=>array("green")));


        $this->current_time = $this->thing->json->time();

        // Get the current Identities flag
//        $this->flag = new Variables($this->thing, "variables flag " . $this->from);

        //$this->nuuid = substr($this->variables_thing->variables_thing->uuid,0,4); 

        if ($this->verbosity >= 2) {
            $this->thing->log($this->agent_prefix . ' got flag variables. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.') ;
        }

        // At this point the flag object
        // has the current flag variables loaded.


//        $this->readInstruction();

//		$this->readSubject();

//        $this->getSimilar(); 

       //$this->thing->log($this->agent_prefix . ' completed read. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.') ;

//        if ($this->agent_input == null) {$this->Respond();}
        //$this->thing->log($this->agent_prefix . ' set response. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.') ;


//        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

//        $this->thing_report['log'] = $this->thing->log;


//		return;

		}

public function read() {

        $this->readInstruction();
                $this->readSubject();

}

public function run() {

        $this->getSimilar(); 

}



    function set($requested_flag = null)
    {
        if ($requested_flag == null) {
            if (!isset($this->requested_flag)) {
                // Set default behaviour.
                // $this->requested_state = "green";
                // $this->requested_state = "red";
                $this->requested_flag = "green"; // If not sure, show green.
            }
            $requested_flag = $this->requested_flag;
        }


        $this->flag = $requested_flag;
        $this->refreshed_at = $this->current_time;

//        $this->thing->setVariable("state", $this->state);
//        $this->thing->setVariable("refreshed_at", $this->current_time);

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("similar",
                        "flag"),  $this->flag );
        $this->thing->json->writeVariable(array("similar",
                        "refreshed_at"),  $this->current_time );
        $this->thing->json->writeVariable(array("similar",
                        "similarness"),  $this->similarness );
        $this->thing->json->writeVariable(array("similar",
                        "similarity"),  $this->similarity );

        if ($this->verbosity >= 2) {
            $this->thing->log($this->agent_prefix . 'set Flag to ' . $this->flag);
        }        
        return;
    }

    function isFlag($flag = null)
    {
        // Validates whether the Flag is green or red.
        // Nothing else is allowed.

        if ($flag == null) {
            if (!isset($this->flag)) {$this->flag = "X";}
            $flag = $this->flag;
        }

        if (($flag == "red") or ($flag == "green")) {return false;}

        return true;
    }



    function getSimilar() {
        $train_things = array();

        // Get recent train tags.
        // This will include simple 'train'
        // requests too.
        // Think about that.
      
        $findagent_thing = new Findagent($this->thing, $this->requested_thing_name . ' ' . $this->horizon);

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.
        if ($this->verbosity >= 2) {
            $this->thing->log($this->agent_prefix . 'found ' . count($findagent_thing->thing_report['things']) ." " . ucwords($this->requested_thing_name) . " Agent Things." );
        }

//        foreach ($findagent_thing->thing_report['things'] as $thing) {

//echo $thing['task'] . " " . $thing['nom_to'] ." " . $thing['nom_from'] . "<br>";

//}
//echo "merp";
//var_dump($findagent_thing->thing_report['things']);
        $this->max_index = 0;
        $this->previous_trains = array();
        $this->similarness = 0;
        $this->similarity = 0;
        $this->flag = "green";

        $this->matches = array();

        if ((isset($findagent_thing->thing_report['things'])) and (count($findagent_thing->thing_report['things']) > 1)) {


      //  if ($findagent_thing->thing_report['things'] != true) {
//echo "Calculating<br>";
        foreach ($findagent_thing->thing_report['things'] as $thing) {
        foreach ($findagent_thing->thing_report['things'] as $thing2) {

            if ($thing['uuid'] == $thing2['uuid']) {continue;}

            $subject = $thing['task'];
            $subject2 = $thing2['task'];
            //$l = levenshtein($subject, $this->subject);
            $l = levenshtein($subject, $subject2);


            if ($l == 0) {
                $this->similarity += 1;
                $this->matches[] = $subject;
            }

            $this->similarness += $l;

            if ($this->verbosity == 9) {
                $this->thing->log($this->agent_prefix . ' ' . $subject . ' ' . $l . '.', "DEBUG");
            }
        }

//echo $thing['task'] . " " . $this->similiarity .  " " . $this->similarness . "<br>";

        }
        }

        if ($this->similarity >= 2) {
            $this->flag = "red";
        }

        if ($this->verbosity >= 2) {
            $this->thing->log($this->agent_prefix . 'calculated similarness =  ' . $this->similarness . '.', "DEBUG");
            $this->thing->log($this->agent_prefix . 'calculated similarity =  ' . $this->similarity . '.', "DEBUG");
        }




    }

    function get()
    {
        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->previous_flag = $this->thing->getVariable("similar","flag");
        $this->refreshed_at = $this->thing->getVariable("similar","refreshed_at");

        //$this->thing->log($this->agent_prefix . 'got similar flag from db ' . strtoupper($this->previous_flag));


        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isFlag($this->previous_flag)) {
            $this->flag = $this->previous_flag;
        } else {
            $this->flag = $this->default_flag;
        }

//        $this->thing->choice->Create($this->keyword, $this->node_list, $this->state);
//        $check = $this->thing->choice->current_node;


        if ($this->verbosity >= 2) {
            $this->thing->log($this->agent_prefix . 'got a ' . strtoupper($this->flag) . ' FLAG.');
        }

        return;


    }

/*
    function read()
    {
        //$this->thing->log("read");

        $this->get();
        return $this->flag;
    }
*/


    function selectChoice($choice = null)
    {

        if ($choice == null) {
            if (!isset($this->flag)) {
                $this->flag = $this->default_flag;
            }
            $choice = $this->flag;
        }

        if (!isset($this->flag)) {
            $this->previous_flag = false;
        } else {
            $this->previous_flag = $this->flag;
        }


//        $this->previous_flag = $this->flag;
        $this->flag = $choice;

        //$this->thing->choice->Choose($this->state);
        //$this->thing->choice->save($this->keyword, $this->state);


        $this->thing->log('Agent "' . ucwords($this->keyword) . '" chose "' . $this->flag . '".');

        return $this->flag;
    }

    function makeChoices () {

//        $this->thing->choice->Choose($this->state);
//        $this->thing->choice->save($this->keyword, $this->state);

        $this->thing->choice->Create($this->keyword, $this->node_list, $this->flag);

        $choices = $this->flag->thing->choice->makeLinks($this->flag);
        $this->thing_report['choices'] = $choices;

    }


	public function respond() {

        if ($this->agent_input != null) {return true;}


        // At this point state is set
        $this->set($this->flag);

		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = $this->keyword;

        $this->makeSMS();
        $this->makeMessage();

		$this->thing_report['email'] = $this->message;

//        $this->makePNG();
//        $this->makeChoices(); // Turn off because it is too slow.

//        $this->makeTXT();



        //$respond = "all";
        //if (($this->flag == "red") or ($respond == "all")) {
        if ($this->agent_input == null) { 
           $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        //$this->thing_report['help'] = 'This Flag is either RED or GREEN. RED means busy.';
        $this->makeHelp();
        $this->makeTxt();

		return;
	}

    function makeHelp()
    {
        if ($this->flag == "green") {
            $this->thing_report['help'] = 'FLAG GREEN. No recent similarness has been seen.';
        }

        if ($this->flag == "red") {
            $this->thing_report['help'] = 'FLAG RED. Recent similarness has been seen.';
        }
    }

    function makeTXT()
    {
        $txt = 'This is SIMILAR /n';
        //$txt .= 'There is a '. strtoupper($this->flag) . " FLAG. ";
        if ($this->verbosity >= 5) {
            $txt .= 'It was last refreshed at ' . $this->current_time . ' (UTC).';
        }


        $txt .= '/r';

//var_dump($this->matches);
//exit();

        foreach ($this->matches as $t) {

            $txt .= $t;
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    function makeSMS()
    {

        $sms_message = "SIMILAR ";


        if ($this->verbosity >= 7) {
            $sms_message .= " | similarity " . strtoupper($this->similarity);
        }

        if ($this->verbosity >= 7) {
            $sms_message .= " | similarness " . strtoupper($this->similarness);
        }

        if ($this->verbosity >= 6) {
            $sms_message .= " | previous flag " . strtoupper($this->previous_flag);
            //$sms_message .= " requested flag " . strtoupper($this->requested_flag);
            //$sms_message .= " current node " . strtoupper($this->base_thing->choice->current_node);
        }

        if ($this->verbosity >= 1) {
            $sms_message .= " | flag " . strtoupper($this->flag);
        }


        if ($this->verbosity >= 5) {
            $sms_message .= " | nuuid " . strtoupper($this->thing->nuuid);
        }


        if ($this->verbosity >= 2) {
            if ($this->flag == "red") {
                $sms_message .= " | MESSAGE Latency";
            }


            if ($this->flag == "green") {
//                $sms_message .= ' | MESSAGE &ltinput&gt';
                $sms_message .= ' | MESSAGE Input';
            }
        }



        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;



    }


    function makeMessage()
    {

        $message = 'This is a SIMILARITY detector. ';

        if ($this->flag == 'red') {
            $message .= 'MORDOK has seen similarity with the previous ' . $this->horizon . ' messages. ';
        }

        if ($this->flag == 'green') {
            $message .= 'MORDOK is likely operating with low LATENCY. ';
        }

        $message .= 'The flag is a  ' . strtoupper($this->flag) . " FLAG. ";



        $this->message = $message;
        $this->thing_report['message'] = $message; // NRWTaylor. Slack won't take hmtl raw. $test_message;


    }

    public function readInstruction() 
    {
        if($this->agent_instruction == null) {
            //$this->defaultCommand();
            return;
        }

        $pieces = explode(" ", strtolower($this->agent_instruction));

        if (isset($pieces[0])) {$this->requested_thing_name = $pieces[0];}
        if (isset($pieces[1])) {$this->horizon = $pieces[1];}
        //$this->identity = $pieces[2];


//        $this->thing->log( 'Agent "Tally" read the instruction and got ' . $this->agen$

        return;

    }



    public function readSubject() 
    {
        $this->response = null;

if ($this->agent_input == "similar") {return null;}

if ($this->agent_input != null) {
        $this->requested_thing_name = $this->agent_input; 
}



        $keywords = array('flag', 'red', 'green');

        $input = strtolower($this->subject);

		$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

//		$this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));


		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == $this->keyword) {
                $this->get();
                return;
            }
                        //return "Request not understood";
                        // Drop through to piece scanner
        }


        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) 
                    {

                        case 'red':
                            $this->thing->log($this->agent_prefix . 'received request for RED FLAG.');
                            $this->selectChoice('red');
                            return;
                        case 'green':
                            $this->selectChoice('green');
                            return;
                        case 'next':


                        default:

                    }

                }
            }

        }


        // If all else fails try the discriminator.

        $this->requested_flag = $this->discriminateInput($haystack); // Run the discriminator.
        switch($this->requested_flag)
        {
            case 'green':
                $this->selectChoice('green');
                return;
            case 'red':
                $this->selectChoice('red');
                return;
        }

        $this->get();




        return "Message not understood";

		return false;

	
	}






	function kill()
    {
		// No messing about.
		return $this->thing->Forget();
	}

    function discriminateInput($input, $discriminators = null)
    {


                //$input = "optout opt-out opt-out";

                if ($discriminators == null) {
                        $discriminators = array('red', 'green');
                }       



                $default_discriminator_thresholds = array(2=>0.3, 3=>0.3, 4=>0.3);

                if (count($discriminators) > 4) {
                        $minimum_discrimination = $default_discriminator_thresholds[4];
                } else {
                        $minimum_discrimination = $default_discriminator_thresholds[count($discriminators)];
                }



                $aliases = array();

                $aliases['red'] = array('r', 'red','on');
                $aliases['green'] = array('g','grn','gren','green', 'gem', 'off');
                //$aliases['reset'] = array('rst','reset','rest');
                //$aliases['lap'] = array('lap','laps','lp');



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

                $this->thing->log('Agent "Flag" has a total count of ' . $total_count . '.');
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
                        return $selected_discriminator;
                } else {
                        return false; // No discriminator found.
                } 

                return true;
        }

}
