<?php
/**
 * Runtime.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Runtime extends Agent
{
    public $var = "hello";
    /*
Save runtime as seconds.
Along with a variable to indicate the preferred
presentation units ie seconds = 60, units = minutes.
Show 1 minute.
*/

// TODO Recognize "hours" or "minutes" without a number as request to set units.
// Tests
// runtime 5s
// runtime 5 s
// runtime 5 seconds
// runtime 1 hour
// runtime hour
// runtime 50
// runtime half an hour

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->keywords = ["runtime", "run", "seconds", "minutes"];
        $this->test = "Development code"; // Always iterative.

        $this->periods = [
            86400 => ["days", "d", "day", "dys", "dys", "dy", "day"],
            3600 => ["hours", "h", "hour", "hrs", "hs", "hr"],
            60 => ["minutes", "minute", "m", "mins", "min", "mn"],
            1 => ["seconds", "seconds", "s", "sec", "secs"],
            0.001 => ["milliseconds", "millisec", "ms", "msec", "millisecond"],
            0.000001 => ["microseconds", "microsecond", "microsec"],
            0.000000001 => ["nanoseconds", "nanosecond", "nanosec", "ns"],
        ];

        $this->default_units = "minutes";
        $this->units = $this->default_units;
        $this->default_seconds = "X";
    }

    public function set($requested_seconds = null)
    {
        if ($requested_seconds == null) {
            if (!isset($this->requested_seconds)) {
                $this->requested_seconds = "X"; // If not sure, show X.

                if (isset($this->seconds)) {
                    $this->requested_seconds = $this->seconds;
                }
            }

            $requested_seconds = $this->requested_seconds;
        }
        $this->seconds = $requested_seconds;

        $this->refreshed_at = $this->current_time;

        $this->runtime->setVariable("seconds", $this->seconds);
        $this->runtime->setVariable("units", $this->units);
        $this->runtime->setVariable("refreshed_at", $this->current_time);
    }

    /**
     *
     * @return unknown
     */
    function getRuntime()
    {
        if (!isset($this->run_time)) {
            if (isset($run_time)) {
                $this->run_time = $run_time;
            } else {
                return true;
            }
        }
        return $this->run_time;
    }

    public function get()
    {
        $flag_variable_name = "";
        $this->thing->json->setField("variables");
        $this->head_code = $this->thing->json->readVariable([
            "headcode",
            "head_code",
        ]);

        $flag_variable_name = "_" . $this->head_code;

        // Get the current Identities flag

        $this->runtime = new Variables(
            $this->thing,
            "variables runtime" . $flag_variable_name . " " . $this->from
        );

        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->previous_seconds = $this->runtime->getVariable("seconds");
        $this->previous_units = $this->runtime->getVariable("units");
        $this->refreshed_at = $this->runtime->getVariable("refreshed_at");

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if ($this->isRuntime($this->previous_seconds)) {
            $this->seconds = floatval($this->previous_seconds);
        } else {
            $this->seconds = $this->default_seconds;
        }

        if ($this->previous_units !== false) {
            $this->units = $this->previous_units;
        } else {
            $this->units = $this->default_units;
        }
    }

    public function isRuntime($text = null)
    {
        // Validates whether the Flag is green or red.
        // Nothing else is allowed.
        if ($text == null) {
            if (!isset($this->seconds)) {
                $this->seconds = "X";
            }

            $text = $this->seconds;
        }

        if (is_numeric($text)) {
            return true;
        }

        if (strtolower($text) == "x") {
            return true;
        }
        if (strtolower($text) == "z") {
            return true;
        }

        return false;
    }

    public function unitsRuntime($input = null)
    {
        foreach ($this->periods as $i => $period_array) {
            foreach ($period_array as $j => $period_name) {
                if ($input == $period_name) {
                    return $period_array[0];
                }
            }
        }
        return false;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function secondsRuntime($input = null)
    {
        $periods = $this->periods;

        $pieces = explode(" ", $input);
        $previous_piece = null;

        $list = [];

        foreach ($pieces as $key => $piece) {
            foreach ($periods as $multiplier => $period) {
                foreach ($period as $period_name) {
                    if (
                        $period_name == $piece and
                        is_numeric($previous_piece)
                    ) {
                        $list[] = $previous_piece * $multiplier;
                    } elseif (is_numeric($piece)) {
                        // skip
                    } elseif (
                        is_numeric(str_replace($period_name, "", $piece))
                    ) {
                        $list[] =
                            str_replace($period_name, "", $piece) * $multiplier;
                    }
                }
            }

            $previous_piece = $piece;
        }

        // If nothing found assume a lone number represents minutes
        if (count($list) == 0) {
            foreach ($pieces as $key => $piece) {
                if ($this->isDecimal($piece)) {
                    $this->response .= "Saw a decimal and read as hours. ";
                    // Assue this is hours
                    $list[] = $piece * 60;
                } elseif (is_numeric($piece)) {
                    $list[] = $piece;
                }
            }
        }
        $list = array_unique($list);
        if (count($list) == 1) {
            $seconds = $list[0];
        }
        return $seconds;
    }

    public function textRuntime($amount, $units)
    {
        foreach ($this->periods as $multiplier => $period_array) {
            if ($period_array[0] === $units) {
                $text = $amount / $multiplier . " " . $units;
                return $text;
            }
        }

        return $amount . " " . "seconds";
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractRuntime($input = null)
    {
        // rename days to seconds
        //$seconds = "X";
        $minutes = [
            22 => ["default"],
            15 => ["quarter hour", "quarter", "1/4", "0.25"],
            30 => [
                "half hour",
                "half hour",
                "halfhour",
                "half an hour",
                "half",
                "0.5",
            ],
            45 => ["0.75", "three quarters", "3/4"],
            60 => ["hour", "hr"],
            1440 => ["day"],
        ];

        foreach ($minutes as $key => $minute_names) {
            if (strpos(strtolower($input), strtolower($key)) !== false) {
                $seconds = $key;
                break;
            }

            foreach ($minute_names as $minute_name) {
                if (
                    strpos(strtolower($input), strtolower($minute_name)) !==
                    false
                ) {
                    $seconds = $key;
                    break;
                }
            }
        }
        if (!isset($seconds)) {
            return false;
        }
        // Fix this.
        $seconds = $seconds * 60;
        return $seconds;
    }

    function selectRuntime($text = null, $text2 = null)
    {
        // Process the amount
        if ($text == null) {
            if (!isset($this->seconds)) {
                $this->response .= "Did not find an existing runtime. ";
                $this->seconds = $this->default_seconds;
            }
            $text = $this->seconds;
        }

        if (!isset($this->seconds)) {
            $this->seconds = "X";
        }
        $this->previous_seconds = $this->seconds;
        $this->seconds = floatval($text);

        // Process the units.

        if ($text2 == null) {
            if (!isset($this->units)) {
                $this->response .= "Did not find an existing units. ";
                $this->units = $this->default_units;
            }
            $text2 = $this->units;
        }

        if (!isset($this->units)) {
            $this->units = "X";
        }
        $this->previous_units = $this->units;
        $this->units = $text2;

        $this->response .=
            "Selected a " .
            $this->textRuntime($this->seconds, $this->units) .
            " runtime. ";
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

    /**
     *
     */
    public function makeSMS()
    {
        $sms_message = "RUNTIME";
        if (isset($this->head_code)) {
            $sms_message .= " " . strtoupper($this->head_code);
        }

        $sms_message .=
            " " . $this->textRuntime($this->seconds, $this->units) . " ";
        $sms_message .= $this->response;

        if ($this->seconds == "X") {
            $sms_message .= " Set RUNTIME.";
        }

        // $sms_message .= " | nuuid " . strtoupper($this->runtime->nuuid);

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $response_text = "Please set RUNTIME.";
        if ($this->seconds != false) {
            $response_text = "" . $this->seconds . " " . $this->units . ".";
        }
        $this->response .= " | " . $response_text . " ";

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
        $this->thing_report["help"] = "This is the runtime manager.";
    }

    public function readRuntime()
    {
        $seconds_text = "X";
        if (isset($this->seconds)) {
            $seconds_text = $this->seconds;
        }
        /*
        $this->response .=
            "Saw a " .
            $this->textRuntime($this->seconds, $this->units) .
            " runtime. ";
*/
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->input;

        if (strtolower($input) === "runtime") {
            $this->response .= "Got the runtime. ";
            return;
        }

        $filtered_input = $this->assert($input, "runtime");

        if (strpos($this->agent_input, "runtime") !== false) {
            return;
        }

        if (strpos($this->input, "reset") !== false) {
            $this->selectRuntime("X");
            return;
        }
        $measurement = $this->extractMeasurement($filtered_input);

        if ($measurement !== false) {
            $amount = $measurement["amount"];
            $units = $measurement["units"];

            $units = $this->unitsRuntime($units);
            $seconds = $this->secondsRuntime($amount . " " . $units);
        } else {
            $seconds = $this->extractRuntime($filtered_input);
        }

        if ($seconds !== false) {
            if (!isset($units)) {
                $units = "X";
            }
            $this->selectRuntime($seconds, $units);
        }
    }
}
