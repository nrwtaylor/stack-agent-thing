<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';
require_once '/var/www/html/stackr.ca/agents/message.php';
//require_once '/var/www/html/stackr.ca/agents/headcode.php';
require_once '/var/www/html/stackr.ca/agents/flag.php';
//require_once '/var/www/html/stackr.ca/agents/consist.php';//
require_once '/var/www/html/stackr.ca/agents/variables.php';
//require_once '/var/www/html/stackr.ca/agents/alias.php';


//require '/var/www/html/stackr.ca/public/agenthandler.php'; // until the callAgent call can be
								// factored to
								// call agent 'Agent'

ini_set("allow_url_fopen", 1);

class Wave_NWW3 
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


$this->agent_prefix = 'Agent "Wave" ';

$this->node_list = array("off"=>array("on"=>array("off")));
$this->thing->choice->load('train');


       $this->keywords = array('wave','height','hst', 'tp', 'direction', 'link', 'buoy', 'bouy', 'verbosity','mode', "period", "p");


//                'block' => array('default run_time'=>'105',
//                                'negative_time'=>'yes'),

        $this->current_time = $this->thing->json->time();


                //$this->default_run_time = $this->thing->container['api']['train']['default run_time'];
                //$this->negative_time = $this->thing->container['api']['train']['negative_time'];
                $this->default_run_time = $this->current_time;
                $this->negative_time = true;
                //$this->app_secret = $this->thing->container['api']['facebook']['app secret'];

                //$this->page_access_token = $this->thing->container['api']['facebook']['page_access_token'];

    $default_train_name = "";

        $this->variables_agent = new Variables($this->thing, "variables " . "wave" . " " . $this->from);



       // $this->current_time = $this->thing->json->time();


        // Loads in Train variables.
        $this->get(); 
//var_dump ($this->notch_height);
//exit();
// Default to Long Island

if ($this->verbosity == false) {$this->verbosity = 2;}

if ($this->notch_height == false) {$this->notch_height = 1.6;}
if ($this->notch_direction == false) {$this->notch_direction = 180;}
if ($this->notch_spread == false) {$this->notch_spread = 80;}
if ($this->notch_min_period == false) {$this->notch_min_period = 10;}
if ($this->noaa_buoy_id == false) {$this->noaa_buoy_id = 44025;}
if ($this->link == false) {$this->link = "https://magicseaweed.com/Hampton-Beach-Surf-Report/2074/";}

//echo $this->noaa_buoy_id;
//exit();


		$this->thing->log('<pre> Agent "Wave" running on Thing '. $this->thing->nuuid . '.</pre>');
		$this->thing->log('<pre> Agent "Wave" received this Thing "'.  $this->subject . '".</pre>');





		$this->readSubject();
//echo $this->noaa_buoy_id;
//exit();

$this->getWave();

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

        // A block has some remaining amount of resource and 
        // an indication where to start.

        // This makes sure that
        if (!isset($this->wave_thing)) {
            $this->wave_thing = $this->thing;
        }

        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        $this->variables_agent->setVariable("state", $requested_state);

        $this->variables_agent->setVariable("verbosity", $this->verbosity);

        $this->variables_agent->setVariable("notch_height", $this->notch_height);
        $this->variables_agent->setVariable("notch_min_period", $this->notch_min_period);
        $this->variables_agent->setVariable("notch_direction", $this->notch_direction);
        $this->variables_agent->setVariable("notch_spread", $this->notch_spread);
        $this->variables_agent->setVariable("link", $this->link);
        $this->variables_agent->setVariable("noaa_buoy_id", $this->noaa_buoy_id);


        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

        $this->thing->choice->save('wave', $this->state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        return;
    }

    function get()
    {

        //$this->variables_thing->getVariables();



        $this->notch_height = $this->variables_agent->getVariable("notch_height")  ;
        $this->notch_min_period = $this->variables_agent->getVariable("notch_min_period")  ;
        $this->notch_direction = $this->variables_agent->getVariable("notch_direction")  ;
        $this->notch_spread = $this->variables_agent->getVariable("notch_spread")  ;
        $this->link = $this->variables_agent->getVariable("link")  ;


        $this->noaa_buoy_id = $this->variables_agent->getVariable("noaa_buoy_id")  ;
        $this->refreshed_at = $this->variables_agent->getVariables("refreshed_at");

        $this->verbosity = $this->variables_agent->getVariable("verbosity")  ;


        $this->thing->choice->Create($this->keyword, $this->node_list, $this->previous_state);
        $this->thing->choice->Choose($this->requested_state);

        $this->state = $this->thing->choice->current_node;


        $this->state = $this->previous_state;
//echo $this->state;
//exit();

        return;

    }



    function dropTrain() {
        $this->thing->log($this->agent_prefix . "was asked to drop a train.");

        //$this->get(); No need as it ran on start up.

        // If it comes back false we will pick that up with an unset block thing.

        if (isset($this->wave_thing)) {
            $this->wave_thing->Forget();
            $this->wave_thing = null;
        }

        $this->get();
 
       return;
    }

    function runTrain($headcode = null) {
        //$this->head_code = "0Z" . $this->index;

        //$this->head_code = "1Z10";
        //if ($this->quantity == 0) {$this->quantity = 45;}
        $this->quantity = 45;
        $this->getAvailable();

        $this->makeTrain($this->current_time, $this->quantity, $this->available);

        $this->state = "running";

        //$this->makeTrain($this->current_time, $this->quantity, $this->available);

    }

    function getWave() {
//        $this->alias = "Logan's run";
//        require_once '/var/www/html/stackr.ca/agents/alias.php';
//        $this->alias_thing = new Alias($this->variables_agent->thing, 'alias');
//        $this->alias = $this->alias_thing->alias;


$buoy = $this->noaa_buoy_id;
//"44025";
$data_source = "http://polar.ncep.noaa.gov/waves/WEB/multi_1.latest_run/plots/multi_1.OPCA02.bull";
$data_source = "http://polar.ncep.noaa.gov/waves/WEB/multi_1.latest_run/plots/multi_1." . $buoy . ".bull";


//$data = file_get_contents($data_source, NULL, NULL, 0, 4000);

$data = file_get_contents($data_source);

//var_dump($data);
//exit();
if ($data == false) {
    return true;
    // Invalid buoy setting.
}

$lines = explode("\n", $data);
//var_dump($lines[7]);
//echo "<br>";
// Seventh line has first line of forecast.  Forecast is updated 4 times daily.
// So eventually retrieve first six lines and extract one closest
// to current time.

// For now pull first line and extract
// wave parameters.
//$sections = explode(" | ", $lines[7]);

$i =0;
$forecast = array();
foreach($lines as $line) {
    $i += 1;
    if ($i>= 8){
        //echo $line . "<br>";
        $forecast[] = $line;
        $fields = explode(" | ", $line);
//var_dump($fields);
        //$time_info = explode("  ", $line[1]);
        $time_info = preg_split('/\s+/', $fields[1]);
//var_dump($time_info);
        $day = intval($time_info[1]);
        $hour = intval($time_info[2]);

        //echo "day and hour " . $day . " " . $hour . "<br>";

// PULL IN ALL 5 or 6 WAVE FIELDS HERE.  AND NOTCH FILTER FOR
// THE SAME WAVE GUIDE.
        $wave_field_num = 0;
        $waves = array();
        while ($wave_field_num <= count($fields)){
            $wave_field_num += 1;
            if ($wave_field_num >= 3) {

                if (isset($fields[$wave_field_num])) {
                $wave_info = preg_split('/\s+/', $fields[$wave_field_num]);
                if (count($wave_info)>=4) {;
                $height =  floatval($wave_info[1]);
                $period =  floatval($wave_info[2]);
                $direction =  intval($wave_info[3]);

        //echo "height, period, direction " . $height . " " . $period . " " . $direction .  "<br>";



                $waves[] = array("height"=>$height, "period"=>$period, "direction"=>$direction);
                } else {
                    $waves[] = false;
                }
                } else {
                $waves[] = false;
                }
            }
        }
        $wave_spectra[] = array("day"=>$day, "hour"=>$hour, "waves"=>$waves);
    }
}



$utc_time = gmdate('d.m.Y H:i', strtotime($this->current_time));

$at_hour = intval(date('H', strtotime($utc_time)));
$at_day = intval(date('j', strtotime($utc_time)));
$at_minute = intval(date('i', strtotime($utc_time)));

//echo $at_hour . "<br>";
//echo $at_day . "<br>";
//echo $at_minute . "<br>";

$this->hour = $at_hour;
$this->day = $at_day;
//exit();

//echo "current time " . $this->current_time;
//echo "current hour" . $at_hour . "current day" . $at_day;

//echo "<br>";
$i = 0;
foreach($wave_spectra as $key=>$spectra) {
//    echo "hour " . $spectra['hour']. " " .$at_hour . "<br>";
//    echo "day " . $spectra['day']. " " .$at_day . "<br>";

     $i += 1;
    if (($spectra["hour"] == $at_hour) and ($spectra["day"] == $at_day)) {
//        echo "meep" . $at_day . " ". $at_hour . "<br>";
        //$this->day = $at_day;
        $waves =  $spectra['waves'];
        //exit();
         break;
    }

}

$next_wave_set = $wave_spectra[$i + 1]['waves'];
$wave_set_interpolated = array();
$i = 0;
foreach ($waves as $wave) {
//echo "----" . "<br>";
//echo "height " . $wave['height'] . " " . $next_wave_set[$i]['height'] . "<br>";
//echo "direction " . $wave['direction'] . " " . $next_wave_set[$i]['direction'] . "<br>";
//echo "period ". $wave['period'] . " " . $next_wave_set[$i]['period'] . "<br>";

//echo "minute " . $at_minute . "<br>";

if (isset($next_wave_set[$i]['height'])) {;
//echo "<br>";
        $height = round($wave['height'] + (($next_wave_set[$i]['height'] - $wave['height']))* $at_minute / 60,2);
        $direction = intval($wave['direction'] + ($next_wave_set[$i]['direction'] - $wave['direction']) * ($at_minute/60));
        $period = round($wave['period'] + ($next_wave_set[$i]['period'] - $wave['period']) * $at_minute / 60,1);
} else {
        $height = round($wave['height'],2);
        $direction = intval($wave['direction']);
        $period = round($wave['period'],1);
}

//echo "height " . $height. " direction " . $direction . " period " . $period . "<br>";

        $wave_set_interpolated[] = array("height"=>$height,"direction"=>$direction,"period"=>$period);

       $i += 1;
}

// now we have the index we can get the previous, now, and next wave parameters.
//        $this->height = $wave_spectra[$i]["height"];
//        $this->direction = $wave_spectra[$i]["direction"];
//        $this->period = $wave_spectra[$i]["period"];

//echo $this->height, $this->direction, $this->period;


//foreach($waves as $wave) {



//https://www.surfertoday.com/surfing/9116-the-importance-of-swell-period-in-surfing
//period > 10.


// So this looks through all the wave fields to find
// either the notch passing dominant wave.
// Or if no waves passing the notch,
// the dominant wave.
$spread = 80;
$this->height = 0;
$this->dominant_height = 0;
foreach($wave_set_interpolated as $wave) {

    $num_matching_wave_fields = 0;


    if ($this->dominant_height < $wave['height']) {
        $this->dominant_height = $wave['height'];
        $this->dominant_direction = $wave['direction'];
        $this->dominant_period = $wave['period'];
    }

//$this->notch_height = 1.6;
//$this->notch_direction = 180;
//$this->notch_spread = 80;
//$this->notch_min_period = 10;

    if (($wave['height'] >= $this->notch_height) and 
        ($wave['direction'] > ($this->notch_direction - $this->notch_spread)) and 
        ($wave['direction'] < ($this->notch_direction + $this->notch_spread)) and
        ($wave['period'] > $this->notch_min_period)) {

    if ($this->height < $wave['height']) {
        // New dominant wave
        $this->height = $wave['height'];
        $this->direction = $wave['direction'];
        $this->period = $wave['period'];
    }
    $num_matching_wave_fields += 1;

    }
}

if ($num_matching_wave_fields >= 1) {
    // Use the wave passing the notch.
    $this->setFlag("red");
} else {
    $this->setFlag("green");
    // Report the dominant wave.
    $this->height = $this->dominant_height;
    $this->direction = $this->dominant_direction;
    $this->period = $this->dominant_period;

}

        return $this->height;
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
		$from = "wave";

		//echo "<br>";

		$choices = $this->thing->choice->makeLinks($this->state);
		$this->thing_report['choices'] = $choices;



        //$interval = date_diff($datetime1, $datetime2);
        //echo $interval->format('%R%a days');
        //$available = $this->thing->human_time($this->available);


        //$s = $this->block_thing->state;
        if (!isset($this->flag)) {
            $this->flag = strtoupper($this->getFlag());
        }
//$this->link = "https://magicseaweed.com/Hampton-Beach-Surf-Report/2074/";

        if (strtolower($this->flag) == "red") {
		$sms_message = "WAVE = SURF'S UP";
        } else {
            $sms_message = "WAVE";
        }

        if ($this->verbosity >= 2) {
            $sms_message .= " | flag " . strtoupper($this->flag);
            $sms_message .= " | direction " . strtoupper($this->direction) . "";
            $sms_message .= " | height " . strtoupper($this->height). "m";
            $sms_message .= " | period " . strtoupper($this->period). "s";
            $sms_message .= " | source NOAA Wavewatch III ";
        }

        if ($this->verbosity >=9) {$sms_message .= " | nowcast " . $this->day . " " . $this->hour;}

        if ($this->verbosity >=5) {
            $sms_message .= " | notch " . $this->notch_height . "m " . $this->notch_direction . " " . $this->notch_spread . " " . $this->notch_min_period . "s";
        }

        if ($this->verbosity >=2) {
            $sms_message .= " | buoy " . $this->noaa_buoy_id;
        }


        $sms_message .= " | link " . $this->link;

        if ($this->verbosity >=9) {
        $sms_message .= " | nuuid " . substr($this->variables_agent->thing->uuid,0,4); 


        $run_time = microtime(true) - $this->start_time;
        $milliseconds = round($run_time * 1000);

        $sms_message .= " | processor time " . number_format($milliseconds) . 'ms';
        }



    switch($this->index) {
        case null:
            $sms_message .= " | TEXT WAVE ";

            break;

        case '1':
          $sms_message .=  " | TEXT WAVE";
            //$sms_message .=  " | TEXT ADD BLOCK";
            break;
        case '2':
            $sms_message .=  " | TEXT WAVE";
            //$sms_message .=  " | TEXT BLOCK";
            break;
        case '3':
            $sms_message .=  " | TEXT WAVE";
            break;
        case '4':
            $sms_message .=  " | TEXT WAVE";
            break;
        default:
            $sms_message .=  " | TEXT ?";
            break;
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

$this->thing_report['help'] = 'This triggers based on specific NOAA buoy data parameters.';


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

            if ($input == 'wave') {

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
        $this->thing->log('Agent "Train" found a "run at" time of "' . $this->run_at . '".');
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

