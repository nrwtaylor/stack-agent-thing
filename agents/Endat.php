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

class Endat extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->keywords = ['next', 'accept', 'clear', 'drop', 'add', 'new'];
        $this->test = "Development code"; // Always iterative.
        $this->endat = new Variables(
            $this->thing,
            "variables endat " . $this->from
        );
    }

    /**
     *
     */
    function set()
    {
        if ($this->endat == false) {
            return;
        }

        if (!isset($this->day)) {
            $this->day = "X";
        }
        if (!isset($this->hour)) {
            $this->hour = "X";
        }
        if (!isset($this->minute)) {
            $this->minute = "X";
        }

        $datetime = $this->day . " " . $this->hour . ":" . $this->minute;
        $this->datetime = date_parse($datetime);

        $this->endat->setVariable("refreshed_at", $this->current_time);
        $this->endat->setVariable("day", $this->day);
        $this->endat->setVariable("hour", $this->hour);
        $this->endat->setVariable("minute", $this->minute);

        $this->printEndat("set");

        $this->thing->log(
            $this->agent_prefix .
                ' saved ' .
                $this->day .
                " " .
                $this->hour .
                " " .
                $this->minute .
                ".",
            "DEBUG"
        );
    }

    /**
     *
     * @param unknown $run_at (optional)
     */
    function get($run_at = null)
    {
$headcode_agent = new Headcode($this->thing, "headcode");
$this->head_code = $headcode_agent->head_code;

$at_agent = new At($this->thing, "headcode");

        if ($this->endat == false) {
            return;
        }

        $day = $this->endat->getVariable("day");
        $hour = $this->endat->getVariable("hour");
        $minute = $this->endat->getVariable("minute");

        $this->refreshed_at = $this->endat->getVariable("refreshed_at");

        $this->day = "X";
        if ($this->isInput($day)) {
            $this->day = $day;
        }
        $this->hour = "X";
        if ($this->isInput($hour)) {
            $this->hour = $hour;
        }

        $this->minute = "X";
        if ($this->isInput($minute)) {
            $this->minute = $minute;
        }

        $this->printEndat("get");
    }

    function getEndat()
    {
        //var_dump($this->hour);
        //var_dump($this->minute);

        if (!isset($this->end_at) and !isset($this->runtime)) {
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
            case strtoupper($this->end_at) != "X" and
                strtoupper($this->end_at) != "Z":
                $this->run_at = strtotime(
                    $this->end_at . "-" . $this->runtime->minutes . "minutes"
                );
                break;
            default:
                $this->run_at = $this->trainTime();
        }

        return $this->run_at;
    }

    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function isToday($text = null)
    {
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
    function extractNumbers($input = null)
    {
        if ($input == null) {
            $input = $this->input;
        }

        $this->numbers = [];

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
    function extractNumber($input = null)
    {
        $this->number = "X";

        if (!isset($this->numbers)) {
            $this->extractNumbers($input);
        }
        if (count($this->numbers) == 1) {
            $this->number = $this->numbers[0];
        }
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function isInput($input)
    {
        if ($input === false) {
            return false;
        }
        if (strtolower($input) == strtolower("X")) {
            return false;
        }

        if (is_numeric($input)) {
            return true;
        }
        if ($input == 0) {
            return true;
        }

        return true;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractEndat($input = null)
    {
        $this->parsed_date = date_parse($input);

        $minute = $this->parsed_date['minute'];
        $hour = $this->parsed_date['hour'];
        $day = $this->extractDay($input);

        $this->response .= "Got " . $day . " " . $hour . " " . $minute . ". ";

        //var_dump($minute);
        //var_dump($hour);
        //var_dump($day);
        // See what numbers are in the input
        if (!isset($this->numbers)) {
            $this->extractNumbers($input);
        }
        $this->extractNumbers($input);

        //var_dump($this->numbers);
        if (isset($this->numbers) and count($this->numbers) == 0) {
            $this->response .= "Did not see any numbers. ";
        } elseif (count($this->numbers) == 1) {
            $this->response .= "Saw one number. ";

            if (strlen($this->numbers[0]) == 4) {
                $this->response .= "Saw one four digit number. ";

                //var_dump($minute);
                //var_dump($hour);

                if ($minute == 0 and $hour == 0) {
                    $this->response .=
                        "Saw a four digit number. Using this for the time. ";
                    $minute = substr($this->numbers[0], 2, 2);
                    $hour = substr($this->numbers[0], 0, 2);

                    //                if ($this->isInput($minute)) {$this->minute = $minute;}
                    //                if ($this->isInput($hour)) {$this->hour = $hour%24;}
                    //$this->minute = $minute;
                    //$this->hour = $hour;
                }
            }
        } elseif (count($this->numbers) == 2) {
            $this->response .= "Saw two numbers. ";

            if ($minute == 0 and $hour == 0) {
                $this->response .= "And saw minutes and hours are both zero. ";

                // Deal with edge case(s)
                if (isset($this->numbers[0]) and ($this->numbers[0] = "0000")) {
                    $this->response .= "Saw midnight. ";
                    $this->hour = 0;
                    $this->minute = 0;
                }
                if (
                    ($this->numbers[0] = "00") and
                    (isset($this->numbers[1]) and $this->numbers[1] == "00")
                ) {
                    $this->hour = $hour;
                    $this->minute = $minute;
                }
            } else {
                if ($this->isInput($minute)) {
                    $this->minute = $minute;
                }
                if ($this->isInput($hour)) {
                    $this->hour = $hour % 24;
                }
            }
        } else {
        }

        $this->minute = $minute;
        $this->hour = $hour;

        if ($this->isInput($day)) {
            $this->day = $day;
        }
        if ($day == "X" and (isset($this->day) and $this->day == "X")) {
            $this->day = $day;
        }

        //echo "extract hour " .$this->hour;
        //echo "extract minute " . $this->minute;

        //        return array($this->day, $this->hour, $this->minute);
    }

    public function timeEndat($text = null) {

       $time_string = $text;
       if ($text == null) {
          $time_string = $this->day . " " . $this->hour .":". $this->minute;
       }

       $this->time = strtotime($time_string);

       return $this->time;

    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractMeridian($input = null)
    {
        if (!isset($this->number)) {
            $this->extractNumber($input);
        }

        if (count($this->numbers) == 2) {
            if ($this->numbers[0] <= 12 and $this->numbers[0] >= 1) {
                $this->hour = $this->numbers[0];
            }
            if ($this->numbers[1] >= 1 and $this->numbers[1] <= 59) {
                $this->minute = $this->numbers[1];
            }
        }

        if (count($this->numbers) == 1) {
            if ($this->numbers[0] <= 12 and $this->numbers[0] >= 1) {
                $this->hour = $this->numbers[0];
            }
        }

        $pieces = explode(strtolower($input), " ");

        $keywords = ["am", "pm", "morning", "evening", "late", "early"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'stop':
                            if ($key + 1 > count($pieces)) {
                                //echo "last word is stop";
                                $this->stop = false;
                                return "Request not understood";
                            } else {
                                $this->stop = $pieces[$key + 1];
                                $this->response .= $this->stopTranslink(
                                    $this->stop
                                );
                                return;
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
    function extractDay($input = null)
    {
        $day = "X";

        $days = [
            "MON" => ["mon", "monday", "M"],
            "TUE" => ["tue", "tuesday", "Tu"],
            "WED" => ["wed", "wednesday", "W", "wday"],
            "THU" => ["thur", "thursday", "Th", "Thu"],
            "FRI" => ["fri", "friday", "F", "Fr"],
            "SAT" => ["sat", "saturday", "Sa"],
            "SUN" => ["sun", "sunday", "Su"],
        ];

        foreach ($days as $key => $day_names) {
            if (strpos(strtolower($input), strtolower($key)) !== false) {
                $day = $key;
                break;
            }

            foreach ($day_names as $day_name) {
                if (
                    strpos(strtolower($input), strtolower($day_name)) !== false
                ) {
                    $day = $key;
                    break;
                }
            }
        }

        $this->parsed_date = date_parse($input);

        if (
            $this->parsed_date['year'] != false and
            $this->parsed_date['month'] != false and
            $this->parsed_date['day'] != false
        ) {
            $date_string =
                $this->parsed_date['year'] .
                "/" .
                $this->parsed_date['month'] .
                "/" .
                $this->parsed_date['day'];

            $unixTimestamp = strtotime($date_string);
            $p_day = date("D", $unixTimestamp);

            if ($day == "X") {
                $day = $p_day;
            }
        }
        $this->thing->log("found day " . $day . ".");

        return $day;
    }

    /**
     *
     */
    function makeTXT()
    {
        $txt = $this->sms_message;

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $day = "X";
        if (isset($this->day)) {
            $day = $this->day;
        }
        if ($day == null) {
            $day = "X";
        }

        $hour = "X";
        if (isset($this->hour)) {
            $hour = $this->hour;
        }
        //if ($hour == null) {$hour = "X";}

        $minute = "X";
        if (isset($this->minute)) {
            $minute = $this->minute;
        }

        //if ($minute == null) {$minute = "X";}

        //var_dump($this->hour);
        //var_dump($this->minute);

        $sms_message = "ENDAT";

        $hour_text = str_pad($hour, 2, "0", STR_PAD_LEFT);
        //if ($hour == 'X') {$hour_text = "X";}

        $minute_text = str_pad($minute, 2, "0", STR_PAD_LEFT);
        //if ($minute == 'X') {$minute_text = "X";}

        $day_text = $day;
$sms_message .= " " .$this->head_code .  " ";
        $sms_message .=
            " | day " .
            $day_text .
            " hour " .
            $hour_text .
            " minute " .
            $minute_text .
            " ";
        $sms_message .= $this->response;

        if (
            !$this->isInput($day) or
            !$this->isInput($hour) or
            !$this->isInput($minute)
        ) {
            //if (($this->hour == "X") or ($this->day == "X") or ($this->minute == "X")) {

            $sms_message .= " | Set ENDAT. ";
        }

        $sms_message .= "| nuuid " . strtoupper($this->endat->nuuid);
        //        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        //$to = $this->thing->from;
        //$from = "runat";

        //$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
        $this->thing_report['choices'] = $choices;

        //$this->makeTXT();

        //$this->makeSMS();

        $test_message =
            'Last thing heard: "' .
            $this->subject .
            '".  Your next choices are [ ' .
            $choices['link'] .
            '].';

        $test_message .= '<br>' . $this->sms_message;

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report['info'] = $message_thing->thing_report['info'];
        } else {
            $this->thing_report['info'] =
                'Agent input was "' . $this->agent_input . '".';
        }

        //$this->makeTXT();

        $this->thing_report['help'] = 'This is a headcode.';
    }

    /**
     *
     * @param unknown $variable
     * @return unknown
     */
    function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param unknown $text (optional)
     */
    function printEndat($text = null)
    {
        return;
        echo $text . "\n";

        if (!isset($this->day)) {
            $day = "X";
        } else {
            $day = $this->day;
        }
        if (!isset($this->hour)) {
            $hour = "X";
        } else {
            $hour = $this->hour;
        }
        if (!isset($this->minute)) {
            $minute = "X";
        } else {
            $minute = $this->minute;
        }

        echo $day . " " . $hour . " " . $minute . "\n";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->response .= 'Hello. ';
        //$this->response = null;
        $this->num_hits = 0;

        $input = $this->input;
        $filtered_input = $this->assert($input);
        $this->response .= "Reading " . $filtered_input . ". ";

        $keywords = $this->keywords;
        if (strpos($filtered_input, "reset") !== false) {
            $this->hour = "X";
            $this->minute = "X";
            $this->day = "X";
            return;
        }

        if (strpos($filtered_input, "now") !== false) {
            $this->extractEndat($this->current_time);
return;
        }

        //        $this->extractRunat($this->input);
        if ($this->input == "endat") {
//            $this->extractRunat($filtered_input);
            return;
        }



        //        if (strpos($this->agent_input, "runat") !== false) {

        //            $this->extractRunat($this->agent_input);

        //            return;
        //        }
        $this->extractEndat($filtered_input);
    }
}
