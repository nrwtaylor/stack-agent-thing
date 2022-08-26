<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Oneforge 
{

    // This gets Forex from an API.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) {
// deprecated
return;
        $this->start_time = microtime(true);

        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->keyword = "mordok";

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;


        $this->agent_prefix = 'Agent "Forex" ';

        //$this->node_list = array("off"=>array("on"=>array("off")));

        $this->keywords = array('forex');

        $this->current_time = $this->thing->time();


        $this->api_key = $this->thing->container['api']['1forge'];

        $this->variables_agent = new Variables($this->thing, "variables " . "forex" . " " . $this->from);

        // Loads in variables.
        $this->get(); 

        if ($this->verbosity == false) {$this->verbosity = 2;}

        if ($this->currency_pair == false) {$this->currency_pair = "USDCAD";}

		$this->thing->log('<pre> Agent "Forex" running on Thing '. $this->thing->nuuid . '.</pre>');
		$this->thing->log('<pre> Agent "Forex" received this Thing "'.  $this->subject . '".</pre>');

		$this->readSubject();

        $this->getOneforge();

		$this->respond();

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( $this->agent_prefix .'ran for ' . $milliseconds . 'ms.' );

		$this->thing->log($this->agent_prefix . 'completed.');

        $this->thing_report['log'] = $this->thing->log;

		return;

	}





    function set()
    {

        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        $this->variables_agent->setVariable("state", $requested_state);

        $this->variables_agent->setVariable("verbosity", $this->verbosity);

        $this->variables_agent->setVariable("currency_pair", $this->currency_pair);
        $this->variables_agent->setVariable("bid", $this->bid);
        $this->variables_agent->setVariable("ask", $this->ask);
        $this->variables_agent->setVariable("price", $this->price);


        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

        $this->thing->choice->save('wave', $this->state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        return;
    }

    function get()
    {

        //$this->variables_thing->getVariables();

        $this->currency_pair = $this->variables_agent->getVariable("currency_pair")  ;
        $this->bid = $this->variables_agent->getVariable("bid")  ;
        $this->ask = $this->variables_agent->getVariable("ask")  ;
        $this->price = $this->variables_agent->getVariable("price")  ;

        $this->verbosity = $this->variables_agent->getVariable("verbosity")  ;

        return;

    }


    function getOneforge()
    {

        $this->getLink($this->currency_pair);
        $data_source = "https://forex.1forge.com/1.0.3/quotes?pairs=" . $this->currency_pair . "&api_key=" .$this->api_key;

        //$data = file_get_contents($data_source, NULL, NULL, 0, 4000);

        $data = file_get_contents($data_source);

        if ($data == false) {
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, TRUE);


        $this->bid = $json_data[0]['bid'];
        $this->price = $json_data[0]['price'];
        $this->ask = $json_data[0]['ask'];

        return $this->price;
    }


    function getLink($ref)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.google.com/search?q=" . $ref; 
        return $this->link;
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



	private function respond() {

		// Thing actions

		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "forex";

		//echo "<br>";

		//$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
		$this->thing_report['choices'] = $choices;



        //$interval = date_diff($datetime1, $datetime2);
        //echo $interval->format('%R%a days');
        //$available = $this->thing->human_time($this->available);


        $this->flag = "green";
        if (strtolower($this->flag) == "red") {
		    $sms_message = "FOREX = SURF'S UP";
        } else {
            $sms_message = "FOREX " . $this->currency_pair;
        }


        if ($this->verbosity >= 2) {
            $sms_message .= " | flag " . strtoupper($this->flag);
            $sms_message .= " | price " . $this->price . " ";
            $sms_message .= " | bid " . $this->bid. " ";
            $sms_message .= " | ask " . $this->ask. " ";
            $sms_message .= " | source 1forge ";
        }

        $sms_message .= " | curated link " . $this->link;

        if ($this->verbosity >=9) {
            $sms_message .= " | nuuid " . substr($this->variables_agent->thing->uuid,0,4); 


            $run_time = microtime(true) - $this->start_time;
            $milliseconds = round($run_time * 1000);

            $sms_message .= " | rtime " . number_format($milliseconds) . 'ms';
        }

        $sms_message .=  " | TEXT ?";


		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';

		$test_message .= '<br>' . $sms_message;


        $this->thing_report['sms'] = $sms_message;
        $this->thing_report['email'] = $sms_message;
        $this->thing_report['message'] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;


        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->thing_report['help'] = 'This triggers provides currency prices using the 1forge API.';

		return;
	}

    public function extractNumber($input = null)
    {
        if ($input == null) {$input = $this->subject;}

        $pieces = explode(" ", strtolower($input));

        // Extract number
        $matches = 0;
        foreach ($pieces as $key=>$piece) {

            if (is_numeric($piece)) {
                $number = $piece;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            if (is_integer($number)) {
                $this->number = intval($number);
            } else {
                $this->number = floatval($number);
            }
        } else {
            $this->number = true;
        }

        return $this->number;
    }

    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;
        // Extract uuids into

        //$this->number = extractNumber();

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

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'forex') {

                return;
            }

        }

    // Extract runat signal
    $matches = 0;

    $currencies = array();

    foreach ($pieces as $key=>$piece) {

        if ((strlen($piece) == 3) and (ctype_alpha($piece))) {
            $currencies[] =strtoupper( $piece);
            //$run_at = $piece;
            $matches += 1;
        }
    }

    if ($matches == 1) {
        $this->currency_pair = 'USD' . $currencies[0];
    }


    if ($matches == 2) {
        $this->currency_pair = $currencies[0] . $currencies[1];
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

   case 'buoy':
   case 'bouy';
        $number = $this->extractNumber();
        if (is_numeric($number)) {
            $this->noaa_buoy_id = $number;
            $this->set();
        }
       return;

   case 'direction':
   case 'dir';
        $number = $this->extractNumber();
        if (is_numeric($number)) {
            $this->notch_direction = $number;
            $this->set();
        }
       return;

   case 'spread':
        $number = $this->extractNumber();
        if (is_numeric($number)) {
            $this->notch_spread = $number;
            $this->set();
        }
       return;


   case 'period':
        $number = $this->extractNumber();
        if (is_numeric($number)) {
            $this->notch_min_period = $number;
            $this->set();
        }
       return;

   case 'height':
        $number = $this->extractNumber();
        if (is_numeric($number)) {
            $this->notch_height = $number;
            $this->set();
        }
       return;


   case 'verbosity':
    case 'mode':
        $number = $this->extractNumber();
        if (is_numeric($number)) {
            $this->verbosity = $number;
            $this->set();
        }
       return;



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

//if ( (count($uuids) == 1) and (count($head_codes) == 1) and (isset($this->run_at)) and (isset($this->quantity)) ) {

    // Likely matching a head_code to a uuid.

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

//    $this->read();




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


                        //echo '<pre> Agent "Train" normalized discrimators "';print_r($normalized);echo'"</pre>';


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

