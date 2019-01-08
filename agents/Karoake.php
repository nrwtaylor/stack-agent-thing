<?php
namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);



//require '/var/www/html/stackr.ca/public/agenthandler.php'; // until the callAgent call can be
								// factored to
								// call agent 'Agent'

ini_set("allow_url_fopen", 1);

class Karoake 
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


$this->agent_prefix = 'Agent "Read" ';

//$this->node_list = array("off"=>array("on"=>array("off")));
//$this->thing->choice->load('train');


       $this->keywords = array('read', 'link', 'date', 'wordlist');


//                'block' => array('default run_time'=>'105',
//                                'negative_time'=>'yes'),

        $this->current_time = $this->thing->json->time();



    $default_train_name = "";

        $this->variables_agent = new Variables($this->thing, "variables " . "read" . " " . $this->from);



       // $this->current_time = $this->thing->json->time();


        // Loads in Train variables.
        $this->get(); 

// Default to Long Island

if ($this->verbosity == false) {$this->verbosity = 2;}

//if ($this->notch_height == false) {$this->notch_height = 1.6;}
//if ($this->notch_direction == false) {$this->notch_direction = 180;}
//if ($this->notch_spread == false) {$this->notch_spread = 80;}
if ($this->wordlist == false) {$this->wordlist = "estate sale";}
//if ($this->noaa_buoy_id == false) {$this->noaa_buoy_id = 44025;}
//if ($this->link == false) {$this->link = "http://www.27east.com/hamptons-classifieds/yard-sales.cfm";}

if ($this->link == false) {$this->link = "http://rockgod.ca/rock-god-karaoke-schedule-canada-british-columbia-vancouver-000001.html";}


$this->robot = new Robot($this->thing, $this->link);
// Pending copyright notice confirmation.

exit();
//var_dump($this->robot->is_allowed);
//exit();

//echo $this->noaa_buoy_id;
//exit();


		$this->thing->log('<pre> Agent "Read" running on Thing '. $this->thing->nuuid . '.</pre>');
		$this->thing->log('<pre> Agent "Read" received this Thing "'.  $this->subject . '".</pre>');





		$this->readSubject();


        $this->getWave();

		$this->respond();



        $this->thing->log( $this->agent_prefix .'ran for ' . $this->thing->elapsed_runtime() . 'ms.' );

		$this->thing->log($this->agent_prefix . 'completed.');

        $this->thing_report['log'] = $this->thing->log;



		return;

		}





    function set()
    {

        // A block has some remaining amount of resource and 
        // an indication where to start.

        // This makes sure that
        if (!isset($this->wave_thing)) {
            $this->wave_thing = $this->thing;
        }

        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        $this->variables_agent->setVariable("state", $this->state);

        $this->variables_agent->setVariable("verbosity", $this->verbosity);

        $this->variables_agent->setVariable("wordlist", $this->wordlist);

        $this->variables_agent->setVariable("link", $this->link);


        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

        $this->refreshed_at = $this->current_time;

        return;
    }

    function get()
    {


        $this->state = $this->variables_agent->getVariable("state")  ;

        $this->wordlist = $this->variables_agent->getVariable("wordlist")  ;
        $this->link = $this->variables_agent->getVariable("link")  ;


        $this->refreshed_at = $this->variables_agent->getVariables("refreshed_at");

        $this->verbosity = $this->variables_agent->getVariable("verbosity")  ;



        return;

    }



    function getWave()
    {

        $data_source = $this->link;

        if (!($this->robot->is_allowed)) {return true;}

        //$data = file_get_contents($data_source, NULL, NULL, 0, 4000);

        $data = file_get_contents($data_source);


        //$this->thing_report['txt'] = $this->txt;


        if ($data == false) {
            return true;
            // Invalid buoy setting.
        }
//var_dump($data);
        $this->extractEvents_2($data,"");
/*
        //$data = strip_tags($data);

        $data = preg_replace("/<.*?>/", " ", $data);
        $sections = explode("ADD TO YOUR YARD SALE LIST", $data);

        $this->addresses = array();
        $this->yard_sales = array();

        array_shift($sections);
        array_pop($sections);

        $lines = explode("\n", $data);

        $count = 0;

        foreach ($sections as $section) {

        $words = explode(" ", strtolower($this->wordlist));

        $ch = implode("|",$words);
        $pattern = '['.$ch.']';

        if ($this->match_all($words, strtolower($section))) {
            //if(preg_match('[weber|grills]', strtolower($section))) { 

            $this->yard_sales[] = $section;
            $count += 1;

            $lines = explode("\n", trim($section));
            $this->addresses[] = array_pop($lines);
        }
    }

    $utc_time = gmdate('d.m.Y H:i', strtotime($this->current_time));

    $at_hour = intval(date('H', strtotime($utc_time)));
    $at_day = intval(date('j', strtotime($utc_time)));
    $at_minute = intval(date('i', strtotime($utc_time)));

    $this->hour = $at_hour;
    $this->day = $at_day;

        return $this->addresses;
*/  
  }

function extractEvents_2($data, $delimiter) 
{
        $delimiter = '<li itemscope itemtype="http://schema.org/MusicEvent"';

        //$data = strip_tags($data);
        $data = preg_replace("/<.*?>/", " ", $data);
var_dump($data);
       $sections = explode($delimiter, $data);

       $this->addresses = array();
       $this->yard_sales = array();
        array_shift($sections);
        array_pop($sections);

        $lines = explode("\n", $data);

        $count = 0;

        foreach ($sections as $section) {

            $words = explode(" ", strtolower($this->wordlist));

            $ch = implode("|",$words);
            $pattern = '['.$ch.']';

        if ($this->match_all($words, strtolower($section))) {
            //if(preg_match('[weber|grills]', strtolower($section))) { 

            $this->yard_sales[] = $section;
            $count += 1;

            $lines = explode("\n", trim($section));
            $this->addresses[] = array_pop($lines);
        }
    }

    $utc_time = gmdate('d.m.Y H:i', strtotime($this->current_time));

    $at_hour = intval(date('H', strtotime($utc_time)));
    $at_day = intval(date('j', strtotime($utc_time)));
    $at_minute = intval(date('i', strtotime($utc_time)));

    $this->hour = $at_hour;
    $this->day = $at_day;

    return $this->addresses;
}



function extractEvents_1($data)
{
        //$data = strip_tags($data);
        $data = preg_replace("/<.*?>/", " ", $data);
        $sections = explode("ADD TO YOUR YARD SALE LIST", $data);

        $this->addresses = array();
        $this->yard_sales = array();

        array_shift($sections);
        array_pop($sections);

        $lines = explode("\n", $data);

        $count = 0;

        foreach ($sections as $section) {

        $words = explode(" ", strtolower($this->wordlist));

        $ch = implode("|",$words);
        $pattern = '['.$ch.']';

        if ($this->match_all($words, strtolower($section))) {
            //if(preg_match('[weber|grills]', strtolower($section))) { 

            $this->yard_sales[] = $section;
            $count += 1;

            $lines = explode("\n", trim($section));
            $this->addresses[] = array_pop($lines);
        }
    }

    $utc_time = gmdate('d.m.Y H:i', strtotime($this->current_time));

    $at_hour = intval(date('H', strtotime($utc_time)));
    $at_day = intval(date('j', strtotime($utc_time)));
    $at_minute = intval(date('i', strtotime($utc_time)));

    $this->hour = $at_hour;
    $this->day = $at_day;

    return $this->addresses;
}



function match_all($needles, $haystack)
{
    if(empty($needles)){
        return false;
    }

    foreach($needles as $needle) {
        if (strpos($haystack, $needle) == false) {
            return false;
        }
    }
    return true;
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






	private function respond() {

		// Thing actions

		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "read";

		//echo "<br>";

		//$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
		$this->thing_report['choices'] = $choices;


        $this->thing_report['txt'] = implode( "/n",$this->yard_sales);

        //$interval = date_diff($datetime1, $datetime2);
        //echo $interval->format('%R%a days');
        //$available = $this->thing->human_time($this->available);


        //$s = $this->block_thing->state;
    //    if (!isset($this->flag)) {
    //        $this->flag = strtoupper($this->getFlag());
    //    }
//$this->link = "https://magicseaweed.com/Hampton-Beach-Surf-Report/2074/";

        if (strtolower($this->flag) == "red") {
		$sms_message = "READ DEV = ESTATE FOUND";
        } else {
            $sms_message = "READ DEV";
        }

        if ($this->verbosity >= 2) {
        //    $sms_message .= " | flag " . strtoupper($this->flag);
            //$sms_message .= " | direction " . strtoupper($this->direction) . "";
            //$sms_message .= " | height " . strtoupper($this->height). "m";
            //$sms_message .= " | period " . strtoupper($this->period). "s";
            //$sms_message .= " | source NOAA Wavewatch III ";
        }

        //if ($this->verbosity >=9) {$sms_message .= " | nowcast " . $this->day . " " . $this->hour;}

        $a = implode(" | ", $this->addresses);

//var_dump($a);
//exit();

//        $addresses = preg_replace("/[^A-Za-z0-9 ]/", '', $a);
$addresses = $a;
//$addresses = "test string";

        $sms_message .= " | " . $addresses;

        if ($this->verbosity >=5) {
            $sms_message .= " | wordlist " . $this->wordlist;
        }

        //if ($this->verbosity >=2) {
        //    $sms_message .= " | buoy " . $this->noaa_buoy_id;
        //}


        $sms_message .= " | link " . $this->link;

        if ($this->verbosity >=9) {
            $sms_message .= " | nuuid " . substr($this->variables_agent->thing->uuid,0,4); 
            $sms_message .= " | rtime " . number_format($this->thing->elapsed_runtime()) . 'ms';
        }



            $sms_message .=  " | TEXT ?";

//$sms_message = "testtest";

        //if (!isset(

//echo $sms_message;

		$test_message = 'Last thing heard: "' . $this->subject . '"';

		$test_message .= '<br>Train state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $sms_message;

//		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;



//		$test_message .= '<br>Requested state: ' . $this->requested_state;

			$this->thing_report['sms'] = $sms_message;
			$this->thing_report['email'] = $sms_message;
			$this->thing_report['message'] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;


                        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;



		//$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);

$this->thing_report['help'] = 'This reads a web resource.';


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
//        $uuids_in_input

//        $headcodes_in_input



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

//		$this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

//        $this->getWave();


        $pieces = explode(" ", strtolower($input));



		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'read') {

                //echo "readsubject block";
                //$this->read();
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


   case 'verbosity':
    case 'mode':
        $number = $this->extractNumber();
        if (is_numeric($number)) {
            $this->verbosity = $number;
            $this->set();
        }
       return;





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



    default:
        //$this->read();                                                    //echo 'default';

                                        }

                                }
                        }

                }



                return "Message not understood";




		return false;

	
	}






	function kill() {
		// No messing about.
		return $this->thing->Forget();
	}


}

?>

