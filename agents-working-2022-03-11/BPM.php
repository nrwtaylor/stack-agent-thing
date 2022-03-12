<?php
/**
 * BPM.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

// Recognizes and handles UUIDS.
// Does not generate them.  That is a Thing function.

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class BPM extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->agent_name = "bpm";

        $this->stack_state = $this->thing->container["stack"]["state"];
        $this->short_name = $this->thing->container["stack"]["short_name"];

        //        $this->bpm = "X";
        $this->node_list = ["bpm" => ["bar", "tick", "bpm"]];

        $this->variables_agent = new Variables(
            $this->thing,
            "variables bpm " . $this->from
        );

        $this->thing_report["help"] = "Recognizes text with a bpm in it. ";
    }

    public function extractBPM($input = null)
    {
        $this->bpm = $this->last_bpm;
        if (!isset($this->bpms)) {
            $this->extractBPMs($input);
        }

        if (isset($this->bpms[0])) {
            $this->bpm = $this->bpms[0];
        }
        return $this->bpm;
    }

    function extractBPMs($input = null)
    {
        if (is_array($input)) {
            return true;
        }

        $number_agent = new Number($this->thing, "number");
        $this->bpms = $number_agent->numbers;

        return $this->bpms;
    }

    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function hasBPM($text)
    {
        $this->extractBPMs($text);
        if (isset($this->bpms) and count($this->bpms) > 0) {
            return true;
        }
        return false;
    }

    public function set()
    {
        $this->variables_agent->setVariable("bpm", $this->bpm);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    public function get()
    {
        $this->last_bpm = $this->variables_agent->getVariable("bpm");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->response .= "Last BPM was " . $this->last_bpm . ". ";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        // Test
        // $this->input = "200 bpm";
        $this->extractBPM($this->input);
        if (isset($this->bpm) and $this->bpm != null) {
            $this->response .= "Retrieved channel BPM. ";
            return;
        }

        $input = $this->input;

        $strip_words = ["bpm", "beats per minute"];

        foreach ($strip_words as $i => $strip_word) {
            $whatIWant = $input;
            if (
                ($pos = strpos(strtolower($input), $strip_word . " is")) !==
                false
            ) {
                $whatIWant = substr(
                    strtolower($input),
                    $pos + strlen($strip_word . " is")
                );
            } elseif (
                ($pos = strpos(strtolower($input), $strip_word)) !== false
            ) {
                $whatIWant = substr(
                    strtolower($input),
                    $pos + strlen($strip_word)
                );
            }

            $input = $whatIWant;
        }

        $filtered_input = ltrim(strtolower($input), " ");

        $this->bpm = "X";

        return false;
    }

    /**
     *
     */
    function makeSMS()
    {
        $sms_message = strtoupper($this->agent_name) . " | ";

        if (isset($this->bpm)) {
            $sms_message .= $this->bpm . "bpm. ";
        }

        $sms_message .= $this->response;
        $sms_message .= "| TEXT BAR";

        $this->sms_message = $sms_message;

        $this->thing_report["sms"] = $sms_message;
    }

    /**
     *
     */
    /*
    function makeChoices() {
    }
*/

    /**
     *
     */
    function makeImage()
    {
        $this->image = null;
    }
}
