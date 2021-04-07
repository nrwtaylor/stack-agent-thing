<?php
/**
 * Input.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Run extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */

    /**
     */
    function init()
    {
        $this->test = "Development code";
        $this->route_to_agent = null;
    }

    function assertIs($input)
    {
        $agent_name = "run";
        $whatIWant = $input;
        if (
            ($pos = strpos(strtolower($input), $agent_name . " is")) !== false
        ) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen($agent_name . " is")
            );
        } elseif (($pos = strpos(strtolower($input), $agent_name)) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen($agent_name));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $input = $filtered_input;

        if ($input) {
            $this->agent_text = $input;

            return;
        }
        $this->agent_text = null;
    }

    /**
     *
     * @param unknown $text (optional)
     */
    function doRun($text = null)
    {
        $filtered_text = strtolower($text);
        $ngram_agent = new Ngram($this->thing, $filtered_text);

        foreach ($ngram_agent->ngrams as $index => $ngram) {
            switch ($ngram) {
                case "input":
                    $this->agent_text = "input";
                    $response_text = $this->agent_text . ". ";

                    $this->response .= $response_text;
                    return;

                case "off":
                case "break":

                    $t = new Input($this->thing, "break");
                    $this->agent_text = "off";
                    $this->response = "Break. ";
                    return;

                case "run":
                    $this->agent_text = "on";
                    $response_text = $this->agent_text . ". ";

                    $this->response .= $response_text;
                    return;

                default:
            }
        }

        $this->assertIs($this->input);
        $this->response .= "Said that the agent is now running. ";

    }

    /**
     *
     */
    public function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "run " . $this->from
        );

        $this->agent_text = $this->variables_agent->getVariable("text");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );
    }

    /**
     *
     * @param unknown $input_flag (optional)
     */
    function set($agent_text = null)
    {
        $this->respond();
        if ($agent_text == null) {
            $agent_text = $this->agent_text;
        }
        if (!isset($this->variables_agent)) {
            $this->get();
        }

        $this->variables_agent->setVariable("text", $agent_text);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    /**
     *
     */
    function makeSMS()
    {
        $sms = "RUN | " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = false;

        $this->thing_report["choices"] = $choices;
        $this->thing_report["info"] = "This makes an run thing.";
        $this->thing_report["help"] = "This is about run variables.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->doRun($this->input);
        return false;
    }

    /**
     *
     * @return unknown
     */
    function getRun()
    {
        $block_things = [];
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, "thing");

        $things = $findagent_thing->thing_report["things"];

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        //$this->thing->log('Agent "Block" found ' . count($findagent_thing->thing_report['things']) ." Block Things.");

        $this->max_index = 0;

        $match = 0;

        foreach ($things as $block_thing) {
            $this->thing->log(
                $block_thing["task"] .
                    " " .
                    $block_thing["nom_to"] .
                    " " .
                    $block_thing["nom_from"]
            );

            if ($block_thing["nom_to"] != "usermanager") {
                $match += 1;

                $this->link_uuid = $block_thing["uuid"];

                $thing = new Thing($this->link_uuid);
                $variables = $thing->account["stack"]->json->array_data;
                //                $input_uuid = null;

                if (isset($variables["run"]) and $match == 2) {
                    break;
                }

            }
        }


        $input_uuid = $variables["run"]["uuid"];

        if ($input_uuid == null) {
            // This is input
            $this->variables_agent = new Variables(
                $thing,
                "variables " . "run " . $this->from
            );
            $this->variables_agent->setVariable("text", $this->agent_text);

            $this->state = false;
        } else {
            $this->state = true;
            // This isn't input
        }

        return $this->link_uuid;
    }
}
