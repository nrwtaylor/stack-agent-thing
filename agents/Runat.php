<?php
/**
 * Runat.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Runat extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->keywords = ["next", "accept", "clear", "drop", "add", "new"];
        $this->test = "Development code";
    }

    /**
     *
     */
    function set()
    {
        if ($this->runat == false) {
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

        $this->runat->setVariable("refreshed_at", $this->current_time);
        $this->runat->setVariable("day", $this->day);
        $this->runat->setVariable("hour", $this->hour);
        $this->runat->setVariable("minute", $this->minute);

        $this->thing->log(
            $this->agent_prefix .
                " saved " .
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
/*
        $this->runat = new Variables(
            $this->thing,
            "variables runat " . $this->from
        );
*/
        $this->head_code = $this->thing->Read([
            "headcode",
            "head_code",
        ]);

 //       $flag_variable_name = "_" . $this->head_code;
        $flag_variable_name = "";
        // Get the current Identities flag
        $this->runat = new Variables(
            $this->thing,
            "variables runat" . $flag_variable_name . " " . $this->from
        );

/*
        $headcode_agent = new Headcode($this->thing, "headcode");
        $this->head_code = $headcode_agent->head_code;
*/
        if ($this->runat == false) {
            return;
        }

        $day = $this->runat->getVariable("day");
        $hour = $this->runat->getVariable("hour");
        $minute = $this->runat->getVariable("minute");

        $this->refreshed_at = $this->runat->getVariable("refreshed_at");

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
    }

    function getRunat()
    {
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
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractRunat($input = null)
    {
        $this->parsed_date = date_parse($input);

        $minute = $this->parsed_date["minute"];
        $hour = $this->parsed_date["hour"];

        $day = $this->extractDay($input);

        // See what numbers are in the input
        if (!isset($this->numbers)) {
            $this->numbers = $this->extractNumbers($input);
        }
        $this->extractNumbers($input);

        if (isset($this->numbers) and count($this->numbers) == 0) {
            $this->response .= "Did not see any numbers. ";
        } elseif (count($this->numbers) == 1) {
            $this->response .= "Saw one number. ";

            if (isset($this->numbers[0]) and $this->numbers[0] == "0000") {
                $this->response .= "Saw midnight. ";
                $this->hour = 0;
                $this->minute = 0;
            } elseif (strlen($this->numbers[0]) == 4) {
                $this->response .= "Saw one four digit number. ";

                if ($minute == 0 and $hour == 0) {
                    $this->response .=
                        "Saw a four digit number. Using this for the time. ";
                    $minute = substr($this->numbers[0], 2, 2);
                    $hour = substr($this->numbers[0], 0, 2);
                }
            }
        } elseif (count($this->numbers) == 2) {
            $this->response .= "Saw two numbers. ";

            if ($minute == 0 and $hour == 0) {
                // 0 0 extracted by date parse.
                // Sign that date parse could not recognize a time.
                // But we see two numbers.

                // Deal with edge case(s)
                if (isset($this->numbers[0]) and $this->numbers[0] == "0000") {
                    $this->response .= "Saw midnight. ";
                    $this->hour = 0;
                    $this->minute = 0;
                } elseif (
                    $this->numbers[0] == "00" and
                    (isset($this->numbers[1]) and $this->numbers[1] == "00")
                ) {
                    $this->hour = $hour;
                    $this->minute = $minute;
                } else {
                    $this->hour = $this->numbers[0];
                    $this->minute = $this->numbers[1];
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
            $this->day = $day;
            $this->minute = $minute;
            $this->hour = $hour;
        }

        if ($this->isInput($day)) {
            $this->day = $day;
        }
        if ($day == "X" and (isset($this->day) and $this->day == "X")) {
            $this->day = $day;
        }
    }

    public function timeRunat($text = null)
    {
        $time_string = $text;
        if ($text == null) {
            $time_string = $this->day . " " . $this->hour . ":" . $this->minute;
        }

        $this->time = strtotime($time_string);

        return $this->time;
    }

    /**
     *
     */
    function makeTXT()
    {
        $txt = $this->sms_message;

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    public function textRunat() {

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

        $minute = "X";
        if (isset($this->minute)) {
            $minute = $this->minute;
        }

        $hour_text = str_pad($hour, 2, "0", STR_PAD_LEFT);
        $minute_text = str_pad($minute, 2, "0", STR_PAD_LEFT);
        $day_text = $day;

        $text =
            " day " .
            $day_text .
            " hour " .
            $hour_text .
            " minute " .
            $minute_text;

        return $text;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms_message = "RUNAT";

        $sms_message .= " " . strtoupper($this->head_code) . " ";
        $sms_message .=
            "|" . $this->textRunat() . ". ";

        $sms_message .= $this->response;

        if (
            !$this->isInput($this->day) or
            !$this->isInput($this->hour) or
            !$this->isInput($this->minute)
        ) {
            $sms_message .= " | Set RUNAT. ";
        }

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    public function makeWeb()
    {
        $stamp = "";
        if (!isset($this->response)) {
            $this->response = "Made web page. ";
        }

        $m = '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';

        $m .= $this->textRunat();

        $this->web_message = $m;
        $this->thing_report['web'] = $m;
    }


    /**
     *
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] =
            "Try RUNAT NOW. RUNAT MON 10:40. Or RUNAT RESET.";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->input;

        if ($input == "runat") {
            return;
        }

        $filtered_input = $this->assert($input);

        $keywords = $this->keywords;
        if (strpos($filtered_input, "reset") !== false) {
            $this->hour = "X";
            $this->minute = "X";
            $this->day = "X";
            return;
        }
        if (strpos($filtered_input, "now") !== false) {
            $this->extractRunat($this->humanTime());
            return;
        }

        $this->extractRunat($filtered_input);
    }
}
