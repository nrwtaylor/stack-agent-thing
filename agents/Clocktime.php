<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Clocktime extends Agent
{
    public $var = "hello";
    public $info;
    public $link;
    public $txt;
    
    public $clock_time;
    public $hour;
    public $minute;

    public $clocktime;

    function init()
    {
        $this->thing->log(
            $this->agent_prefix .
                "running on Thing " .
                $this->thing->nuuid .
                ".",
            "INFORMATION"
        );

        $this->keywords = [
            "now",
            "next",
            "accept",
            "clear",
            "drop",
            "add",
            "new",
        ];

        // You will probably see these a lot.
        // Unless you learn headcodes after typing SYNTAX.

        $this->current_time = $this->thing->time();

        // Agent variables
        $this->sqlresponse = null; // True - error. (Null or False) - no response. Text - response

        $this->state = null; // to avoid error messages

        $this->clocktime = new Variables(
            $this->thing,
            "variables clocktime " . $this->from
        );
    }

    function makeClocktime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if (($input !== null) and (strtoupper($input) == "X")) {
            $this->clock_time = "X";
            return $this->clock_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $this->clock_time = $this->hour . $this->minute;

        //if ($input == null) {$this->clocktime = $train_time;}
        return $this->clock_time;
    }

    function test()
    {
        $test_corpus = file_get_contents(
            $this->resource_path . "clocktime/test.txt"
        );
        $test_corpus = explode("\n", $test_corpus);

        $this->response = "";
        foreach ($test_corpus as $key => $line) {
            if ($line == "-") {
                break;
            }
            $this->extractClocktime($line);

            $line .
                "<br>" .
                "hour " .
                $this->hour .
                " minute " .
                $this->minute .
                "<br>" .
                "<br>";
        }
    }

    function set()
    {
        $this->clocktime->setVariable("refreshed_at", $this->current_time);
        $this->clocktime->setVariable("hour", $this->hour);
        $this->clocktime->setVariable("minute", $this->minute);

        $this->thing->log(
            $this->agent_prefix .
                " saved " .
                $this->hour .
                " " .
                $this->minute .
                ".",
            "DEBUG"
        );
    }

    function getRunat()
    {
        if (!isset($this->clocktime)) {
            if (isset($clocktime)) {
                $this->clocktime = $clocktime;
            } else {
                $this->clocktime = "Meep";
            }
        }
        return $this->clocktime;
    }

    function get($run_at = null)
    {
        $this->hour = $this->clocktime->getVariable("hour");
        $this->minute = $this->clocktime->getVariable("minute");

        $this->thing->log(
            "got hour " . $this->hour . " minute " . $this->minute . "."
        );
    }

    function extractClocktime($input = null)
    {
        if (is_numeric($input)) {
            // See if we received a unix timestamp number
            $input = date("Y-m-d H:i:s", $input);
        }

        $this->parsed_date = date_parse($input);

        $this->minute = $this->parsed_date["minute"];
        $this->hour = $this->parsed_date["hour"];

        if ($this->minute == false and $this->hour == false) {
            // Start here
            $this->minute = "X";
            $this->hour = "X";

            // Test for non-recognized edge case
            if (preg_match("(o'clock|oclock)", $input) === 1) {
                $number_agent = new Number($this->thing, "number " . $input);
                if (count($number_agent->numbers) == 1) {
                    $this->hour = $number_agent->numbers[0];
                    if ($this->hour > 12) {
                        $this->hour = "X";
                    }
                }
            }

            $pattern = "/([0-1]?[0-9]|2[0-3])[:][0-5][0-9]/";

            // TODO Recognize non-colon seperator
            // TODO Recognize seconds

            preg_match_all($pattern, $input, $m);

            if (count($m[0]) != 0) {
                // TODO Recognize multiple times in a string

                $t = explode(":", $m[0][0]);
                $this->minute = $t[1];
                $this->hour = $t[0];
            }

            // Test for non-recognized edge case
            if (strpos($input, "0000") !== false) {
                $this->minute = 0;
                $this->hour = 0;
            }

            if ($this->hour == "X" and $this->minute == "X") {
                return null;
            }
        }

        return [$this->hour, $this->minute];
    }

    function makeTXT()
    {
        $txt = $this->sms_message;

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    public function makeWeb()
    {
        if (!isset($this->response)) {
            $this->response = "meep";
        }

        $m = "<b>" . ucwords($this->agent_name) . " Agent</b><br>";

        //$m .= "CLOCKTIME<br>";
        $m .= "hour " . $this->hour . " minute " . $this->minute . "<br>";

        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $m .= $this->response;

        $this->web_message = $m;
        $this->thing_report["web"] = $m;
    }

    public function makeSMS()
    {
        $sms_message = "CLOCKTIME";
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " | hour " . $this->hour . " minute " . $this->minute;

        if ((isset($this->response)) and ($this->response != "")) {
            $sms_message .= " | " . $this->response;
        }

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        //$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        } else {
            $this->thing_report["info"] =
                'Agent input was "' . $this->agent_input . '".';
        }

        $this->thing_report["help"] =
            "This is a clocktime.  Extracting clock times from strings.";
    }

    function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);
        if ($this->agent_input != null) {
            $input = $this->agent_input;
        }

        if ($input == "clocktime") {
            return;
        }
        if ($input == "clocktime test") {
            $this->test();
        }

        //if ($this->agent_input == "test") {$this->test(); return;}

        $this->num_hits = 0;

        $keywords = $this->keywords;

        $prior_uuid = null;

        // Is there a clocktime in the provided datagram
        $this->extractClocktime($input);
        if ($this->agent_input == "extract") {
            $this->response = "Extracted a clocktime.";
            return;
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "clocktime") {
                //                $this->get();
                $this->response = "Last 'clocktime' retrieved.";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "now":
                            $this->thing->log("read subject nextheadcode");
                            $t = $this->thing->time();
                            $this->extractClocktime($t);
                            $this->response = "Got server time.";

                            return;
                    }
                }
            }
        }

        if ($this->minute == "X" and $this->hour == "X") {
            //            $this->get();
            $this->response = "Last clocktime retrieved.";
        }
        /*
        // Added in test 2018 Jul 26
        if (($this->minute == false) and ($this->hour == false)) {

            $t = $this->thing->time();
            $this->extractClocktime($t);
            $this->response = "Got server time.";
        }
*/
        return "Message not understood";
    }
}
