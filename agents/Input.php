<?php
/**
 * Input.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Input extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */

    /**
     * function __construct(Thing $thing, $text = null) {
     */
    function init()
    {
        $this->test = "Development code";
        $this->input_agent = null;
    }

    // -----------------------

    function assertIs($input)
    {
        $this->input_agent = null;
        $agent_name = "input";
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
        $this->input_agent = $filtered_input;
    }

    /**
     *
     * @param unknown $text (optional)
     */
    function doInput($text = null)
    {
        $filtered_text = strtolower($text);
        $ngram_agent = new Ngram($this->thing, $filtered_text);

        foreach ($ngram_agent->ngrams as $index => $ngram) {
            switch ($ngram) {
                case "break":
                    $this->input_agent = null;
                    $this->response = "Break. ";
                    return;

                case "input":
                    $input_agent = $this->input_agent;
                    $this->input_agent = null;
                    //$this->assertIs($this->input);
                    $input_agent_text = $input_agent . " is expecting input. ";

                    if ($input_agent == false) {
                        $input_agent_text = "No input expected. ";
                    }
                    $this->input_agent = $input_agent;
                    $this->response .= $input_agent_text;
                    return;

                default:
            }
        }

        $this->assertIs($this->input);
        $this->response .=
            "Said that input response is expected to the current agent. ";

    }


    /**
     *
     */
    public function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "input " . $this->from
        );

        //        $input = new Variables($this->thing, "variables basket " . $this->from);

        $this->input_agent = $this->variables_agent->getVariable("agent");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

    }

    /**
     *
     * @param unknown $input_flag (optional)
     */
    function set($input_agent = null)
    {
        $this->respond();
        if ($input_agent == null) {
            $input_agent = $this->input_agent;
        }
        if (!isset($this->variables_agent)) {
            $this->get();
        }
        //$this->variables_agent->setVariable("value_destroyed", $this->value_destroyed);

        //$this->variables_agent->setVariable("things_destroyed", $this->things_destroyed);

        //$this->thing->setVariable("damage_cost", $this->damage_cost);

        $this->variables_agent->setVariable("agent", $input_agent);
        $this->variables_agent->setVariable(
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

    /**
     *
     */
    function makeSMS()
    {
        //    if ($this->state == true) {
        //        $sms = "INPUT | ?";
        //    }

        //    if ($this->state == false) {
        //        $sms = "INPUT | " . $this->subject;

        //    }
        $sms = "INPUT | " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     * @return unknown
     */
    public function respond()
    {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "input";

        $this->makeSMS();

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
        return $this->thing_report;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->doInput($this->input);
        //$input = strtolower($this->subject);
        //$this->getInput();

        return false;
    }

    /**
     *
     * @return unknown
     */
    //public function readInstruction() {

    //$input = strtolower($this->subject);
    //    $this->getInput();

    //    return false;
    //}

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
            $this->variables_agent = new Variables(
                $thing,
                "variables " . "input " . $this->from
            );
            $this->variables_agent->setVariable("uuid", $this->uuid);

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
//var_dump($discriminator);
if (!isset($aliases[$discriminator])) {continue;}
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
}
