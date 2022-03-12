<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Calendardate extends Agent
{
    public $var = 'hello';

    public function init()
    {
        $this->keywords = [
            'now',
            'next',
            'accept',
            'clear',
            'drop',
            'add',
            'new',
        ];

        // You will probably see these a lot.
        // Unless you learn headcodes after typing SYNTAX.

        $this->test = "Development code"; // Always iterative.
        $this->state = null; // to avoid error messages

        $this->calendardate = new Variables(
            $this->thing,
            "variables calendardate " . $this->from
        );
    }

    function makeCalendardate($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if (strtoupper($input) == "X") {
            $this->calendar_date = "X";
            return $this->calendar_date;
        }

        $t = strtotime($input_time);

        $this->day = date("d", $t);
        $this->month = date("m", $t);
        $this->year = date("Y", $t);

        $this->calendar_date =
            $this->year . "-" . $this->month . "-" . $this->day;

        return $this->calendar_date;
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
            $this->extractCalendardate($line);

            $line .
                "<br>" .
                "year " .
                $this->year .
                " month " .
                $this->month .
                " day " .
                $this->day .
                "<br>" .
                "<br>";
        }
    }

    function set()
    {
        //$this->head_code = "0Z15";

        if (!isset($this->refreshed_at)) {
            $this->refreshed_at = $this->thing->time();
        }

        $this->calendardate->setVariable("refreshed_at", $this->refreshed_at);
        $this->calendardate->setVariable("year", $this->year);
        $this->calendardate->setVariable("month", $this->month);
        $this->calendardate->setVariable("day", $this->day);

        $this->thing->log(
            'saved ' .
                $this->year .
                " " .
                $this->month .
                " " .
                $this->day .
                ".",
            "DEBUG"
        );

    }

    function getRunat()
    {
        if (!isset($this->calendardate)) {
            if (isset($calendardate)) {
                $this->calendardate = $calendardate;
            } else {
                $this->calendardate = "Meep";
            }
        }
        return $this->calendardate;
    }

    function get($run_at = null)
    {
        $this->last_refreshed_at = $this->calendardate->getVariable(
            'refreshed_at'
        );

        $this->day = $this->calendardate->getVariable("day");
        $this->month = $this->calendardate->getVariable("month");
        $this->year = $this->calendardate->getVariable("year");

        return;
    }

    function extractCalendardate($input = null)
    {
        if (is_numeric($input)) {
            // See if we received a unix timestamp number
            $input = date('Y-m-d H:i:s', $input);
        }

        $this->parsed_date = date_parse($input);

        $this->year = $this->parsed_date['year'];
        $this->month = $this->parsed_date['month'];
        $this->day = $this->parsed_date['day'];

        if (
            $this->year == false and
            $this->month == false and
            $this->day == false
        ) {
            // Start here
            $this->year = "X";
            $this->month = "X";
            $this->day = "X";

            if (
                $this->year == "X" and
                $this->month == "X" and
                $this->day == "X"
            ) {
                return null;
            }
        }

        return [$this->year, $this->month, $this->day];
    }

    function readCalendardate()
    {
    }

    function makeTXT()
    {
        $txt = $this->sms_message;

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    public function makeWeb()
    {
        if (!isset($this->response)) {
            $this->response = "meep";
        }

        $m = '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';

        $m .=
            "year " .
            $this->year .
            "month " .
            $this->month .
            " day " .
            $this->day .
            "<br>";

        $m .= $this->response;

        $this->web_message = $m;
        $this->thing_report['web'] = $m;
    }

    public function makeSMS()
    {
        $sms_message = "CALENDARDATE";
        $sms_message .=
            " | year " .
            $this->year .
            " month " .
            $this->month .
            " day " .
            $this->day;

        if (isset($this->response)) {
            $sms_message .= " | " . $this->response;
        }

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    public function respondResponse()
    {

        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report['choices'] = $choices;

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        } else {
            $this->thing_report['info'] =
                'Agent input was "' . $this->agent_input . '".';
        }

        $this->thing_report['help'] =
            'This is a calendardate.  Extracting clock times from strings.';
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
        if ($this->agent_input == "test") {
            $this->test();
            return;
        }

        $this->num_hits = 0;

        $keywords = $this->keywords;

        $input = $this->input;

        $prior_uuid = null;

        // Is there a clocktime in the provided datagram
        $this->extractCalendardate($input);
        if ($this->agent_input == "extract") {
            $this->response = "Extracted a calendardate.";
            return;
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'calendardate') {
                $this->get();

                $this->refreshed_at = $this->last_refreshed_at;

                $this->response = "Last 'calendardate' retrieved.";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'now':
                            $this->thing->log("read subject nextheadcode");
                            $t = $this->thing->time();
                            $this->extractCalendardate($t);
                            $this->response = "Got server date.";

                            return;
                    }
                }
            }
        }

        //        if (($this->minute == "X") and ($this->hour == "X")) {
        if ($this->year == "X" and $this->month == "X" and $this->day == "X") {
            $this->get();
            $this->response = "Last calendardate retrieved.";
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
