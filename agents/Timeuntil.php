<?php
/**
 * Timeuntil.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Timeuntil extends Agent
{
    public $var = "hello";

    /**
     *
     */
    function init()
    {
        $this->agent_name = "time until";
        $this->test = "Development code";

        $this->thing_report["info"] = "This is about time until.";
        $this->thing_report["help"] =
            "Time until is the time to go until the Bell.";
    }

    /**
     *
     */
    function run()
    {
        $this->getTimeuntil();
    }

    /**
     *
     */
    function getTimeuntil()
    {
        // Read the subject line in $this->thing to get the currently focused date.

        $this->runat = new Runat($this->thing, "runat"); // get hour minute seconds for the currently focused event
        $rundate = new Rundate($this->thing, "rundate"); // get day month year for the currently focused event
        $this->rundate = $rundate;

        if (
            $rundate->year == "X" or
            $rundate->month == "X" or
            $rundate->day == "X" or
            $this->runat->day == "X" or
            $this->runat->hour == "X" and $this->runat->hour != 0 or
            $this->runat->minute == "X" and $this->runat->minute != 0
        ) {
            $this->time_until = null;
            return;
        }

        $date_text =
            $rundate->year .
            "-" .
            $rundate->month .
            "-" .
            $rundate->day .
            " " .
            $this->runat->hour .
            ":" .
            $this->runat->minute;
        $run_time = strtotime($date_text);

        $this->current_time = $this->thing->time();
        $now = strtotime($this->current_time);

        $time_until = $run_time - $now;

        // Is the event in the future?
        if ($time_until > 0) {
            // Yes? Calculate the time from "now".
            $this->time_until = $run_time - $now;
        } else {
            // No? Then there is no time until.
            $this->time_until = null;
        }
    }

    /**
     *
     */
    function makeSMS()
    {
        $response = "";

        if ($this->time_until < 0) {
            $response =
                "TIME UNTIL | " .
                $this->thing->human_time($this->time_until / -1) .
                "";
        } else {
            $response =
                "TIME UNTIL | " .
                $this->thing->human_time($this->time_until) .
                "";

            $days_to_go = intval($this->time_until / (24 * 60 * 60));
            $hours_to_go = intval(
                $this->time_until / (60 * 60) - $days_to_go * 24
            );
            $minutes_to_go = intval(
                $this->time_until / 60 -
                    ($days_to_go * 24 * 60 + $hours_to_go * 60)
            );
            $response .=
                " [" .
                $days_to_go .
                " days " .
                $hours_to_go .
                "hrs " .
                $minutes_to_go .
                "mins]";
        }

        $response .= " until";

        $date_string =
            $this->rundate->year .
            "/" .
            $this->rundate->month .
            "/" .
            $this->rundate->day;

        $time = new Time($this->thing, "time");
        $time->doTime($date_string);

        if (isset($time->datum) and $time->datum != null) {
            $response .=
                " " .
                $time->datum->format("l") .
                " " .
                $time->datum->format("d/m/Y, H:i:s") .
                "";
            $response .= " in " . $time->time_zone;
        }
        $response .= ".";

        if ($this->time_until == null) {
            $response = "TIME UNTIL | No response.";
        }
        $this->cat_message = $response;

        $this->response = $this->cat_message;
        $this->node_list = ["cat" => ["cat", "time until"]];
        $this->sms_message = "" . $this->cat_message;
        $this->thing_report["sms"] = $this->sms_message;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->filterAgent();
        return false;
    }
}
