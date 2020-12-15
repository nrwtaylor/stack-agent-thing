<?php
/**
 * Input.php
 *
 * @package default
 */

// TODO
//

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Input extends Agent
{
    public $var = 'hello';

    /**
     * function __construct(Thing $thing, $text = null) {
     */
    function init()
    {
        $this->test = "Development code";
        $this->input_agent = null;
    }

    /**
     *
     * @param unknown $text (optional)
     */
    function readInput($text = null)
    {
        $filtered_text = strtolower($text);
        $ngram_agent = new Ngram($this->thing, $filtered_text);

        foreach ($ngram_agent->ngrams as $index => $ngram) {
            switch ($ngram) {
                case "break":
                    //    $this->input_agent = null;
                    $this->dropInput();
                    $this->response = "Break. ";
                    return;

                case "input":
                    $this->addInput($text);
                    $input_agent = $this->input_agent;
                    $input_agent_text = $input_agent . " is expecting input. ";

                    $this->response .= $input_agent_text;
                    return null;

                default:
            }
        }
        $filtered_text = $this->assert($text);
        //if ($filtered_text == null) {return;}

        //        $this->input = $filtered_text;
        return $filtered_text;

        $this->response .=
            "Said that input response is expected to the current agent. ";
    }

    /**
     *
     */
    public function get()
    {
        $this->variables_input = new Variables(
            $this->thing,
            "variables " . "input " . $this->from
        );

        $input_agent = $this->variables_input->getVariable("agent");
        if ($input_agent !== false) {
            $this->input_agent = $input_agent;
        }

        $this->input_state = $this->variables_input->getVariable("state");
        $this->input_options = $this->variables_input->getVariable("options");
        $this->input_uuid = $this->variables_input->getVariable("uuid");

        $this->refreshed_at = $this->variables_input->getVariable(
            "refreshed_at"
        );
    }

    /**
     *
     * @param unknown $input_flag (optional)
     */
    function set()
    {
        $this->variables_input->setVariable("agent", $this->input_agent);
        $this->variables_input->setVariable("state", $this->input_state);
        $this->variables_input->setVariable("options", $this->input_options);
        $this->variables_input->setVariable("uuid", $this->input_uuid);

        $this->variables_input->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    public function makeWeb()
    {
        $web = '<p><b>Agent Input</b></p>
<form>
Input: <input type="text" onkeyup="callAgent(this.value)">
</form>
<p>&gt <span id="agent-smsmessage"></span></p>';

        $this->web = $web;
        $this->thing_report['web'] = $web;
    }

    public function makeMessage()
    {
        $message = "input agent " . $this->input_agent . " ";
        $message .= "input state " . $this->input_state . " ";
        $message .= "input_options " . $this->input_options . " ";
        $message .= "uuid " . $this->input_uuid . " ";
        $message .= "input " . $this->input;
        $this->message = $message;
    }

    /**
     *
     */
    function makeSMS()
    {
        $sms = "INPUT | " . $this->message . " " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
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
        $this->thing_report["info"] = "This makes an input thing.";
        $this->thing_report["help"] = "This is about input variables.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = $this->readInput($input);
        if ($filtered_input == null) {
            return;
        }

        if ($this->agent_input !== "input") {
            $this->agentInput($this->agent_input);
        }
        $this->addInput($this->subject);
        return false;
    }

    /**
     *
     * @return unknown
     */
    function getInput()
    {
        $block_things = [];
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, 'thing');

        // This pulls up a list of other Things.

        $this->max_index = 0;

        $match = 0;

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {
            $this->thing->log(
                $block_thing['task'] .
                    " " .
                    $block_thing['nom_to'] .
                    " " .
                    $block_thing['nom_from']
            );

            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;

                $this->link_uuid = $block_thing['uuid'];

                $thing = new Thing($this->link_uuid);
                $variables = $thing->account['stack']->json->array_data;
                //                $input_uuid = null;

                if (isset($variables['input']) and $match == 2) {
                    //                    if (!isset($input_uuid = $variables['input']['uuid'])) {
                    break;
                    //                    }
                }

                //if ($match == 2) {break;}
            }
        }

        $input_uuid = $variables['input']['uuid'];

        if ($input_uuid == null) {
            // This is input
            //    $this->variables_input = new Variables(
            //        $thing,
            //        "variables " . "input " . $this->from
            //    );
            //    $this->variables_input->setVariable("uuid", $this->uuid);

            $this->state = false;
        } else {
            $this->state = true;
            // This isn't input
        }

        return $this->link_uuid;
    }

    function discriminateInput($input, $discriminators = null)
    {
        $default_discriminator_thresholds = [2 => 0.3, 3 => 0.3, 4 => 0.3];

        if (count($discriminators) > 4) {
            $minimum_discrimination = $default_discriminator_thresholds[4];
        } else {
            $minimum_discrimination =
                $default_discriminator_thresholds[count($discriminators)];
        }

        //$input = "optout opt-out opt-out";

        if ($discriminators == null) {
            $discriminators = ['minutes', 'hours'];
        }

        $aliases = [];

        $aliases['minutes'] = ['m', 'mins', 'mns', 'minits'];
        $aliases['hours'] = ['hours', 'h', 'hr', 'hrs', 'hsr'];

        if (isset($this->aliases)) {
            $aliases = $this->aliases;
        }

        $words = explode(" ", $input);

        $count = [];

        $total_count = 0;
        // Set counts to 1.  Bayes thing...
        foreach ($discriminators as $discriminator) {
            $count[$discriminator] = 1;
            $total_count = $total_count + 1;
        }
        // ...and the total count.

        foreach ($words as $word) {
            foreach ($discriminators as $discriminator) {
                if ($word == $discriminator) {
                    $count[$discriminator] = $count[$discriminator] + 1;
                    $total_count = $total_count + 1;
                }

                if (!isset($aliases[$discriminator])) {
                    continue;
                }
                foreach ($aliases[$discriminator] as $alias) {
                    if ($word == $alias) {
                        $count[$discriminator] = $count[$discriminator] + 1;
                        $total_count = $total_count + 1;
                    }
                }
            }
        }

        // Set total sum of all values to 1.

        $normalized = [];
        foreach ($discriminators as $discriminator) {
            $normalized[$discriminator] = $count[$discriminator] / $total_count;
        }

        // Is there good discrimination
        arsort($normalized);

        // Now see what the delta is between position 0 and 1

        foreach ($normalized as $key => $value) {
            if (isset($max)) {
                $delta = $max - $value;
                break;
            }
            if (!isset($max)) {
                $max = $value;
                $selected_discriminator = $key;
            }
        }

        if ($delta >= $minimum_discrimination) {
            //echo "discriminator" . $discriminator;
            return $selected_discriminator;
        } else {
            return false; // No discriminator found.
        }

        return true;
    }

    // TODO

    public function textInput($text = null)
    {
        if ($text != null) {
            $this->input_text = $text;
        }
        $this->response .= "input_text " . $this->input_text . ". ";
        return $this->input_text;
    }

    public function agentInput($text = null)
    {
        if ($text != null) {
            $this->input_agent = $text;
        }
        $this->response .= "input_agent " . $this->input_agent . ". ";
        return $this->input_agent;
    }

    public function stateInput($text = null)
    {
        if ($text != null) {
            $this->input_state = $text;
        }

        $this->response .= "input_state " . $this->input_state . ". ";

        return $this->input_state;
    }

    public function uuidInput($text = null)
    {
        if ($text != null) {
            $this->uuid_input = $text;
        }
        $this->response .= "input_uuid " . $this->uuid_input . ". ";

        //      $this->variables_input->setVariable("uuid", $this->uuid);
        return $this->uuid_input;
    }

    public function addInput($text = null, $agent = null)
    {
        $this->textInput($text);
        if ($agent != null) {
            $this->agentInput($agent);
        }
        $this->stateInput("anticipate");
        $this->set();
    }

    public function dropInput($text = null)
    {
        $this->textInput(null);
        $this->agentInput(null);
        $this->stateInput("default");
        $this->set();
    }
}
