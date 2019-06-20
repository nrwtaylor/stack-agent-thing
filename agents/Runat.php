<?php
/**
 * Runat.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Runat extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init() {
        $this->keywords = array('next', 'accept', 'clear', 'drop', 'add', 'new');
        $this->test= "Development code"; // Always iterative.
        $this->runat = new Variables($this->thing, "variables runat " . $this->from);
    }


    /**
     *
     */
    function set() {

        if ($this->runat == false) {return;}

        if (!isset($this->day)) {$this->day = "X";}
        if (!isset($this->hour)) {$this->hour = "X";}
        if (!isset($this->minute)) {$this->minute = "X";}

        $datetime = $this->day ." " . $this->hour.":".$this->minute;
        $this->datetime = date_parse($datetime);



        $this->runat->setVariable("refreshed_at", $this->current_time);
        $this->runat->setVariable("day", $this->day);
        $this->runat->setVariable("hour", $this->hour);
        $this->runat->setVariable("minute", $this->minute);

$this->printRunat("set");


        $this->thing->log( $this->agent_prefix .' saved ' . $this->day . " " . $this->hour . " " . $this->minute . ".", "DEBUG" );

    }

    /**
     *
     * @param unknown $run_at (optional)
     */
    function get($run_at = null) {

        if ($this->runat == false) {return;}

        $day = $this->runat->getVariable("day");
        $hour = $this->runat->getVariable("hour");
        $minute = $this->runat->getVariable("minute");

        if ($this->isInput($day)) {$this->day = $day;}
        if ($this->isInput($hour)) {$this->hour = $hour;}
        if ($this->isInput($minute)) {$this->minute = $minute;}


$this->printRunat("get");
    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function isToday($text = null) {

        $unixTimestamp = strtotime($this->current_time);
        $day = date("D", $unixTimestamp);

        if (strtoupper($this->day) == strtoupper($day)) {
            return true;
        } else {
            return false;
        }

        // true = yes, false = no
    }


    /**
     *
     * @param unknown $input (optional)
     */
    function extractNumbers($input =  null) {

if ($input == null) {$input = $this->input;}

        $this->numbers = array();

        $agent = new Number($this->thing, "number " . $input);
//$agent->extractNumber($input);
        $numbers = $agent->numbers;
        if (count($numbers) > 0) {
            $this->numbers = $numbers;
        }
    }


    /**
     *
     * @param unknown $input (optional)
     */
    function extractNumber($input =  null) {
        $this->number = "X";

        if (!isset($this->numbers)) {$this->extractNumbers($input);}
        if (count($this->numbers) == 1) {$this->number = $this->numbers[0];}

    }

    function isInput($input) {
       if ($input === false) {return false;}
       if (strtolower($input) == strtolower("X")) {return false;}

       if (is_numeric($input)) {return true;}
       if ($input == 0) {return true;}

       return true;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractRunat($input = null) {
        $this->parsed_date = date_parse($input);

        $minute = $this->parsed_date['minute'];
        $hour = $this->parsed_date['hour'];
        $day = $this->extractDay($input);

        // See what numbers are in the input
        if (!isset($this->numbers)) {$this->extractNumbers($input);}

//var_dump($this->numbers);
$this->extractNumbers($input);
if ( (isset($this->numbers)) and (count($this->numbers) ==0) ) {

} else {

        if (($minute == 0) and ($hour == 0)) {

            // Deal with edge case(s)
            if ((isset($this->numbers[0])) and ($this->numbers[0] = "0000")) {
               $this->hour = 0;
               $this->minute = 0;
            }
            if (($this->numbers[0] = "00") and
               ((isset($this->numbers[1])) and ($this->numbers[1] == "00") )) {

               $this->hour = $hour;
               $this->minute = $minute;

            }


        } else {

            if ($this->isInput($minute)) {$this->minute = $minute;}
            if ($this->isInput($hour)) {$this->hour = $hour%24;}
        }

}
        if ($this->isInput($day)) {$this->day = $day;}
        if (($day == "X") and (isset($this->day) and ($this->day == "X"))) {$this->day = $day;}

//        return array($this->day, $this->hour, $this->minute);
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractMeridian($input = null) {

        if (!isset($this->number)) {$this->extractNumber($input);}

        if (count($this->numbers) == 2) {
            if (($this->numbers[0] <= 12) and ($this->numbers[0]>=1)) {
                $this->hour = $this->numbers[0];
            }
            if (($this->numbers[1] >=1) and ($this->numbers[1]<=59)) {
                $this->minute = $this->numbers[1];
            }
        }

        if (count($this->numbers) == 1) {
            if (($this->numbers[0] <= 12) and ($this->numbers[0]>=1)) {
                $this->hour = $this->numbers[0];
            }
        }



        $pieces = explode(strtolower($input), " ");

        $keywords = array("am", "pm", "morning", "evening", "late", "early");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {

                    switch ($piece) {
                    case 'stop':

                        if ($key + 1 > count($pieces)) {
                            //echo "last word is stop";
                            $this->stop = false;
                            return "Request not understood";
                        } else {
                            $this->stop = $pieces[$key+1];
                            $this->response = $this->stopTranslink($this->stop);
                            return $this->response;
                        }
                        break;

                    case 'am':
                        break;

                    case 'pm':
                        $this->hour = $this->hour + 12;
                        break;

                    default:
                    }

                }
            }
        }



    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractDay($input = null) {
        $day = "X";

        $days = array("MON"=>array("mon", "monday", "M"),
            "TUE"=>array("tue", "tuesday", "Tu"),
            "WED"=>array("wed", "wednesday", "W"),
            "THU"=>array("thur", "thursday", "Th"),
            "FRI"=>array("fri", "friday", "F", "Fr"),
            "SAT"=>array("sat", "saturday", "Sa"),
            "SUN"=>array("sun", "sunday", "Su"));

        foreach ($days as $key=>$day_names) {


            if (strpos(strtolower($input), strtolower($key)) !== false) {
                $day = $key;
                break;
            }

            foreach ($day_names as $day_name) {

                if (strpos(strtolower($input), strtolower($day_name)) !== false) {
                    $day = $key;
                    break;
                }
            }
        }

        $this->parsed_date = date_parse($input);

        if (($this->parsed_date['year'] != false) and ($this->parsed_date['month'] != false) and ($this->parsed_date['day'] != false)) {

            $date_string = $this->parsed_date['year'] ."/".$this->parsed_date['month']. "/" . $this->parsed_date['day'];

            $unixTimestamp = strtotime($date_string);
            $p_day = date("D", $unixTimestamp);

            if ($day == "X") {$day = $p_day;}
        }
        $this->thing->log("found day " . $day . ".");

        return $day;
    }


    /**
     *
     */
    function makeTXT() {
        $txt = $this->sms_message;

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }


    /**
     *
     */
    private function makeSMS() {

        $sms_message = "RUNAT";

        $hour_text = str_pad($this->hour, 2, "0", STR_PAD_LEFT);
        $minute_text = str_pad($this->minute, 2, "0", STR_PAD_LEFT);

$day_text = $this->day;
        $sms_message .= " | day " . $day_text . " hour " . $hour_text . " minute " . $minute_text . " ";


if ( (!$this->isInput($this->day)) or
     (!$this->isInput($this->hour)) or
     (!$this->isInput($this->minute)) ) {

//if (($this->hour == "X") or ($this->day == "X") or ($this->minute == "X")) {

$sms_message .= " | Set RUNAT. ";

}

        $sms_message .= "| nuuid " . strtoupper($this->runat->nuuid);
//        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }


    /**
     *
     */
    public function respond() {

        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $to = $this->thing->from;
        $from = "runat";


        //$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
        $this->thing_report['choices'] = $choices;

        //$this->makeTXT();

        $this->makeSMS();

        $test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';

        $test_message .= '<br>' . $this->sms_message;

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;




        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        } else {
            $this->thing_report['info'] = 'Agent input was "' . $this->agent_input . '".' ;
        }

        $this->makeTXT();

        $this->thing_report['help'] = 'This is a headcode.';
    }


    /**
     *
     * @param unknown $variable
     * @return unknown
     */
    function isData($variable) {
        if (
            ($variable !== false) and
            ($variable !== true) and
            ($variable != null) ) {

            return true;

        } else {
            return false;
        }
    }

    function printRunat($text = null) {
return;
        echo $text . "\n";

        if (!isset($this->day)) {$day = "X";} else {$day = $this->day;}
        if (!isset($this->hour)) {$hour = "X";} else {$hour = $this->hour;}
        if (!isset($this->minute)) {$minute = "X";} else {$minute = $this->minute;}


        echo $day . " "  .$hour . " " . $minute ."\n";

    }

    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->response = null;
        $this->num_hits = 0;

        $keywords = $this->keywords;
        if (strpos($this->input, "reset") !== false) {
            $this->hour = "X";
            $this->minute = "X";
            $this->day = "X";
            return;
        }
//        $this->extractRunat($this->input);

        if ($this->agent_input == "runat")  {

            return;
        }

        if (strpos($this->agent_input, "runat") !== false) {
        $this->extractRunat($this->agent_input);

            return;
        }
        $this->extractRunat($this->input);

    }


}
