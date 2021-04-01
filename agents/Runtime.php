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

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->keywords = ["runtime", "run"];
        $this->test = "Development code"; // Always iterative.

        $this->units = "minutes";

        $this->default_runtime = "X";
    }

    function set($requested_runtime = null)
    {
        if ($requested_runtime == null) {
            if (!isset($this->requested_runtime)) {
                $this->requested_runtime = "X"; // If not sure, show X.

                if (isset($this->runtime)) {
                    $this->requested_runtime = $this->runtime;
                }
            }

            $requested_runtime = $this->requested_runtime;
        }

        $this->runtime = $requested_runtime;
        $this->refreshed_at = $this->current_time;

        $this->variables->setVariable("runtime", $this->runtime);
        $this->variables->setVariable("refreshed_at", $this->current_time);
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
                //$this->run_at = "Meep";
            }
        }
        return $this->run_time;
    }

    public function get()
    {
        $flag_variable_name = "";
        // Get the current Identities flag

        $this->variables = new Variables(
            $this->thing,
            "variables runtime" . $flag_variable_name . " " . $this->from
        );

        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->previous_runtime = $this->variables->getVariable("runtime");
        $this->refreshed_at = $this->variables->getVariable("refreshed_at");

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if ($this->isRuntime($this->previous_runtime)) {
            $this->runtime = $this->previous_runtime;
        } else {
            $this->runtime = $this->default_runtime;
        }
    }

    public function isRuntime($runtime = null)
    {
        // Validates whether the Flag is green or red.
        // Nothing else is allowed.

        if ($runtime == null) {
            if (!isset($this->runtime)) {
                $this->runtime = "X";
            }

            $runtime = $this->runtime;
        }

        if (is_numeric($runtime)) {
            return true;
        }

        if (strtolower($runtime) == "x") {
            return true;
        }
        if (strtolower($runtime) == "z") {
            return true;
        }

        return false;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractRuntime($input = null)
    {
        $periods = [
            1440 => ["d", "days", "dys", "dys", "dy", "day"],
            60 => ["h", "hours", "hrs", "hs", "hr"],
            1 => ["minutes", "m", "mins", "min", "mn"],
        ];

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

        if (count($list) == 1) {
            $this->runtime = $list[0];
        }
        return $this->runtime;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractTime($input = null)
    {
        $this->runtime = "X";
        $days = [
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
            60 => ["hour", "hr"],
            1440 => ["day"],
        ];

        foreach ($days as $key => $day_names) {
            if (strpos(strtolower($input), strtolower($key)) !== false) {
                $this->runtime = $key;
                break;
            }

            foreach ($day_names as $day_name) {
                if (
                    strpos(strtolower($input), strtolower($day_name)) !== false
                ) {
                    $this->runtime = $key;
                    break;
                }
            }
        }

        return $this->runtime;
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
        if (isset($this->variables->head_code)) {
            $sms_message .= " " . strtoupper($this->variables->head_code);
        }

        $sms_message .= " " . $this->runtime . " ". $this->units ." ";
        $sms_message .= $this->response;

        if ($this->runtime == "X") {
            $sms_message .= " Set RUNTIME.";
        }

        $sms_message .= " | nuuid " . strtoupper($this->variables->nuuid);

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
        if ($this->runtime != false) {
            $response_text = "" . $this->runtime . " minutes.";
        }
        $this->response .= " | " . $response_text;

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] = "This is the runtime manager.";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;

        if (strpos($this->agent_input, "runtime") !== false) {
            return;
        }

        if (strpos($this->input, "reset") !== false) {
            $this->runtime = "X";

            return;
        }

        $this->extractRuntime($this->input);

        if ($this->runtime == "X") {
            $this->extractTime($this->input);
        }

        $this->requested_runtime = $this->runtime;
        if ($this->agent_input == "extract") {
            return;
        }
    }
}
