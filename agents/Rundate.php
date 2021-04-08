<?php
/**
 * Rundate.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Rundate extends Agent
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
        $this->test = "Development code"; // Always iterative.

        $this->thing_report["help"] =
            "Reads text for the date of the next thing.";
    }

    /**
     *
     */
    function set()
    {
        if ($this->rundate == false) {
            return;
        }

        if (!isset($this->day)) {
            $this->day = "X";
        }
        if (!isset($this->month)) {
            $this->month = "X";
        }
        if (!isset($this->year)) {
            $this->year = "X";
        }

        $datetime = $this->day . " " . $this->month . ":" . $this->year;
        $this->datetime = date_parse($datetime);

        $this->rundate->setVariable("refreshed_at", $this->current_time);
        $this->rundate->setVariable("day", $this->day);
        $this->rundate->setVariable("month", $this->month);
        $this->rundate->setVariable("year", $this->year);

        $this->setRunat();
    }

    /**
     *
     * @param unknown $run_at (optional)
     */
    function get($run_at = null)
    {
/*
        $this->rundate = new Variables(
            $this->thing,
            "variables rundate " . $this->from
        );
*/
        $this->thing->json->setField("variables");
        $this->head_code = $this->thing->json->readVariable([
            "headcode",
            "head_code",
        ]);

        $flag_variable_name = "_" . $this->head_code;

        // Get the current Identities flag
        $this->rundate = new Variables(
            $this->thing,
            "variables rundate" . $flag_variable_name . " " . $this->from
        );


        if ($this->rundate == false) {
            return;
        }

        $day = $this->rundate->getVariable("day");
        $month = $this->rundate->getVariable("month");
        $year = $this->rundate->getVariable("year");

        if ($this->isInput($month)) {
            $this->month = $month;
        }
        if ($this->isInput($year)) {
            $this->year = $year;
        }

        if ($this->isInput($day)) {
            $this->day = $day;
        }

    }

    /**
     * Ensure consistency between Rundate and Runat for day.
     */
    function setRunat()
    {
        $date_string = $this->year . "-" . $this->month . "-" . $this->day;
        $d = strtotime($date_string);

        $day = strtoupper(date("D", $d));

        $runat = new Runat($this->thing, "runat");

        if (strtoupper($runat->day) != strtoupper($day)) {
            $command = "runat " . $day;
            $this->response .= "Changed runat day to " . $day . ". ";
            $runat = new Runat($this->thing, $command);
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
    function extractRundate($input = null)
    {
        $this->parsed_date = date_parse($input);

        $year = $this->parsed_date["year"];
        $month = $this->parsed_date["month"];
        $day = $this->parsed_date["day"];

        // See what numbers are in the input
        if (!isset($this->numbers)) {
            $this->numbers = $this->extractNumbers($input);
        }

        if ($day != false and $month != false) {
            if (
                $this->numbers[0] == $day and $this->numbers[1] == $month or
                $this->numbers[0] == $month and $this->numbers[1] == $day
            ) {
                //ok
            } else {
                if ($this->numbers[0] > 12 and $this->numbers[0] <= 31) {
                    $day = $this->numbers[0];
                }
                if ($this->numbers[0] >= 1000 and $this->numbers[0] <= 9999) {
                    $year = $this->numbers[0];
                }
            }
        }

        // What if nothing comes back from PHP's date parse.
        // But it is still a valid date.
        if ($day == false and $month == false and $year == false) {
            if (count($this->numbers) == 0) {
                return;
            }

            // Two numbers in string - month and day?
            if (isset($this->numbers[1])) {
                if (
                    $this->numbers[0] > 12 and
                    $this->numbers[0] <= 31 and
                    $this->numbers[1] >= 1 and
                    $this->numbers[1] <= 12
                ) {
                    if (
                        $this->numbers[0] >= 1000 and
                        $this->numbers[0] <= 9999
                    ) {
                        $year = $this->numbers[0];
                    }
                    if (
                        $this->numbers[1] >= 1000 and
                        $this->numbers[1] <= 9999
                    ) {
                        $year = $this->numbers[1];
                    }

                    $day = $this->numbers[0];
                    $month = $this->numbers[1];
                    //  return;
                }
            } elseif (!isset($this->numbers[1])) {
                if ($this->numbers[0] > 12 and $this->numbers[0] <= 31) {
                    $day = $this->numbers[0];
                }
                if ($this->numbers[0] >= 1000 and $this->numbers[0] <= 9999) {
                    $year = $this->numbers[0];
                }
            }
        }

        if ($day > 0) {
            $this->day = $day;
        }
        if ($month > 0) {
            $this->month = $month;
        }
        if ($year > 0) {
            $this->year = $year;
        }
    }

    /**
     *
     */
    public function makeTXT()
    {
        $txt = $this->sms_message;

        $this->thing_report["txt"] = $txt;
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

        $month = "X";
        if (isset($this->month)) {
            $month = $this->month;
        }
        if ($month == null) {
            $month = "X";
        }

        $year = "X";
        if (isset($this->year)) {
            $year = $this->year;
        }
        if ($year == null) {
            $year = "X";
        }

        $sms_message = "RUNDATE";

        $day_text = str_pad($day, 2, "0", STR_PAD_LEFT);
        $month_text = str_pad($month, 2, "0", STR_PAD_LEFT);
        $year_text = str_pad($year, 2, "0", STR_PAD_LEFT);

        $sms_message .=
            " | day " .
            $day_text .
            " month " .
            $month_text .
            " year " .
            $year_text .
            " ";
        if ((isset($this->response)) and ($this->response != "")) {
            $sms_message .= "| " . trim($this->response) . " ";
        }
        if (
            !$this->isInput($day) or
            !$this->isInput($month) or
            !$this->isInput($year)
        ) {
            $sms_message .= "| Set RUNDATE. ";
        }

        $sms_message .= "| nuuid " . strtoupper($this->rundate->nuuid);

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    /**
     *
     */
    public function respondResponse()
    {
        if ($this->response == null) {
            $this->response .= "Retrieved run at day.";
        }

        // Thing actions

        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report["info"] = $message_thing->thing_report["info"];

    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $filtered_input = $this->filterAgent();
        $this->response = null;
        $this->num_hits = 0;

        if ($filtered_input == "") {return;}

        if (strpos($filtered_input, "reset") !== false) {
            $this->day = "X";
            $this->month = "X";
            $this->year = "X";
            return;
        }

        $this->extractRundate($filtered_input);

        if (strpos($this->agent_input, "rundate") !== false) {
            return;
        }
    }
}
