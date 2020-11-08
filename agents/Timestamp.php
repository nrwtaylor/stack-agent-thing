<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Timestamp extends Agent
{
    public $var = 'hello';
    function init()
    {
        //$this->keywords = ['next', 'accept', 'clear', 'drop', 'add', 'new'];
        $this->keywords = [
            'millis',
            'milli',
            'milliseconds',
            'ms',
            'microtime',
            'micros',
            'micro',
            'microseconds',
            'microseconds',
        ];
        $this->current_time = $this->thing->json->microtime();

        // Default is not to show end user microtime.
        $this->micro_time_flag = false;

        $this->test = "Development code"; // Always iterative.

        $this->state = null; // to avoid error messages

        $this->timestamp_prefix = "";

        //        $this->makeTimestamp();
    }

    function timezoneTimestamp($text = null)
    {
    }

    function run()
    {
if (strtolower($this->input) == "zulu") {
        $this->zuluTimestamp();
        return;
}
    $this->utcTimestamp();
    }

function zuluTimestamp($input = null) {

        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if (strtoupper($input) == "X") {
            $this->timestamp = "X";
            return $this->timestamp;
        }

        $t = strtotime($input_time);

        $this->timestamp = $this->current_time;

        $time_agent = new Time($this->thing, "time");

$time_zone = "UTC";
        if ($time_zone !== false and $time_zone !== true) {
            $time_agent->time_zone = $time_zone;
        }
        $this->response .=
            "Returns the current " . $time_agent->time_zone . " timestamp.";

        $time_agent->doTime();

        $this->default_time_zone = $time_agent->default_time_zone;
        $this->time_zone = $time_zone;
        $timestamp = $time_agent->timestampTime();
$timestamp = substr_replace($timestamp,"T",10,1);
$timestamp = substr_replace($timestamp,"Z",19,1);

$this->timestamp = $timestamp;
        return $this->timestamp;


}

    function utcTimestamp($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if (strtoupper($input) == "X") {
            $this->timestamp = "X";
            return $this->timestamp;
        }

        $t = strtotime($input_time);

        $this->timestamp = $this->current_time;

        $time_agent = new Time($this->thing, "time");

        $time_zone = $time_agent->extractTimezone($this->input);

        if ($time_zone !== false and $time_zone !== true) {
            $time_agent->time_zone = $time_zone;
        }
        $this->response .=
            "Returns the current " . $time_agent->time_zone . " timestamp.";

        $time_agent->doTime();

        $this->default_time_zone = $time_agent->default_time_zone;
        $this->time_zone = $time_zone;
        $this->timestamp = $time_agent->timestampTime();

        return $this->timestamp;
    }

    function test()
    {
        $file = $this->resource_path . "timestamp/test.txt";
        if (!file_exists($file)) {
            return true;
        }

        $test_corpus = @file_get_contents($file);
        if ($test_corpus === false) {
            $this->response .= "Could not load test corpus. ";
            return true;
        }
        $test_corpus = explode("\n", $test_corpus);

        $this->response = "";
        foreach ($test_corpus as $key => $line) {
            if ($line == "-") {
                break;
            }
            $this->extractTimestamp($line);

            $line . "<br>" . "timestamp " . $this->timestamp . "<br>" . "<br>";
        }
    }

    function set()
    {
    }

    function get($run_at = null)
    {
    }

    function extractTimestamp($input = null)
    {
        // Get the clock time.
        // Then date
        $this->timestamp = "X";
        return $this->timestamp;
    }

    function readTimestamp($variable = null)
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

        $parts = explode(" ", $this->timestamp);

        $timestamp = $parts[0] . " " . $parts[1];
        if ($this->micro_time_flag === true) {
            $timestamp = $this->timestamp;
        }

        //        $m .= $timestamp;

        if ($this->default_time_zone != $this->time_zone) {
            $timestamp .= " " . $this->time_zone;
        }
        $timestamp .= "<br>";

        $m .= $timestamp;

        $this->web_message = $m;
        $this->thing_report['web'] = $m;
    }

    public function makeSMS()
    {
        $sms_message = "TIMESTAMP";

        $timestamp = trim($this->timestamp);

        if ($this->micro_time_flag === true) {
            $timestamp = $this->timestamp;
        }

        if (($this->default_time_zone != $this->time_zone) and 
            (strtolower($this->input) != "zulu")) {
            $timestamp .= " " . $this->time_zone;
        }

        $sms_message .= " | " . $timestamp;

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

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
            'This returns the current timestamp. Now. Try MICROTIME. Or TIME.';
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
        //$this->response .= "Returns the current timestamp.";

        if ($this->agent_input == "test") {
            $this->test();
            return;
        }

        $input = $this->agent_input;
        if ($this->agent_input == null or $this->agent_input == "") {
            $input = $this->subject;
        }

        if ($this->agent_input == "timestamp") {
            $input = $this->subject;
            //} else {
            //    $input = $this->agent_input;
        }

        $this->input = $input;
        $this->num_hits = 0;
        $keywords = $this->keywords;

        $prior_uuid = null;

        if ($this->agent_input == "extract") {
            return;
        }

        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'milli':
                        case 'micro':
                        case 'ms':
                            $this->micro_time_flag = true;
                            break;
                        default:
                    }
                }
            }
        }

        return "Message not understood";
    }
}
