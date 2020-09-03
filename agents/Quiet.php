<?php
/**
 * Quiet.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Quiet extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->start_time = microtime(true);

        $this->keyword = "quiet";

        $this->test = "Development code"; // Always

        $this->save_to_stack = true;

        if ($this->agent_input != null) {
            $this->response .= "Stack save off. ";
            $this->save_to_stack = false;
        }

        $this->node_list = ["off" => ["on" => ["off"]]];

        //        $this->current_time = $this->thing->json->time();

        $this->default_state = 'on';

        if ($this->save_to_stack == true) {
            $this->response .= "Stack save on. ";

            $this->variables_thing = new Variables(
                $this->thing,
                "variables quiet " . $this->from
            );
        }

        $this->thing_report['help'] =
            "This tells a Thing to be quiet. And not make messages for you.";
    }

    /**
     *
     * @param unknown $requested_state (optional)
     */
    function set($requested_state = null)
    {
        if ($requested_state == null) {
            return true;
        }
        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        if ($this->save_to_stack == true) {
            $this->variables_thing->setVariable("state", $requested_state);
            $this->variables_thing->setVariable(
                "refreshed_at",
                $this->current_time
            );
        }
        $this->thing->choice->Choose($requested_state);

        $this->thing->choice->save($this->keyword, $requested_state);

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;
    }

    /**
     *
     */
    function get()
    {
        if ($this->save_to_stack == true) {
            $this->previous_state = $this->variables_thing->getVariable(
                "state"
            );
            $this->refreshed_at = $this->variables_thing->getVariables(
                "refreshed_at"
            );
            $this->response .= "Got previous state. ";
        }

        //$this->response .= $this->previous_state . ". ";

        if (!isset($this->previous_state)) {
            $this->previous_state = "off";
        }

        if ($this->previous_state == false) {
            $this->previous_state = $this->default_state;
        }

        if (!isset($this->requested_state)) {
            if (isset($this->state)) {
                $this->requested_state = $this->state;
            } else {
                $this->requested_state = $this->previous_state;
            }
        }

        //        if (!isset($this->previous_state)) {$this->previous_state = "off";}

        $this->thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->previous_state
        );
        $this->thing->choice->Choose($this->requested_state);

        $this->state = $this->thing->choice->current_node;

        $this->state = $this->previous_state;
    }

    /**
     *
     * @return unknown
     */
    function readQuiet()
    {
        //$this->thing->log("read");

        //        $this->get();
        return $this->state;
    }

    /**
     *
     * @param unknown $choice (optional)
     * @return unknown
     */
    function selectChoice($choice = null)
    {
        if ($choice == null) {
            return $this->state;
        }

        $this->thing->log(
            'Agent "' . ucwords($this->keyword) . '" chose "' . $choice . '".'
        );

        $this->set($choice);

        return $this->state;
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $choices = false;
        if ($this->save_to_stack == true) {
            $choices = $this->variables_thing->thing->choice->makeLinks(
                $this->state
            );
        }
        $this->thing_report['choices'] = $choices;

        //$this->makeSms();
        $sms_message = $this->sms_message;
        $test_message =
            'Last thing heard: "' .
            $this->subject .
            '".  Your next choices are [ ' .
            $choices['link'] .
            '].';
        $test_message .= '<br>Shift state: ' . $this->state . '<br>';

        $test_message .= '<br>' . $sms_message;

        $test_message .=
            '<br>Current node: ' . $this->thing->choice->current_node;

        $test_message .= '<br>Requested state: ' . $this->requested_state;
        //        $this->thing_report['sms'] = $sms_message;
        $this->thing_report['email'] = $sms_message;
        $this->thing_report['message'] = $test_message; // NRWTaylor. Slack won't take hmtl raw. $test_message;

        $this->thing_report['info'] = "Heard quiet.";
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->thing_report['help'] =
            'This is Quiet.  You can tell a Thing to be Quiet. Or not.';

        //      return;
    }

    public function makeSMS()
    {
        if ($this->state == "inside nest" or $this->state == false) {
            $t = "NOT SET";
        } else {
            $t = $this->state;
        }

        $sms_message = "QUIET IS " . strtoupper($t);
        if ($this->save_to_stack) {
            $sms_message .=
                " | nuuid " .
                substr($this->variables_thing->variables_thing->uuid, 0, 4);
        }
        $sms_message .= " ";
        $sms_message .= $this->response;
        $state_response = "TEXT QUIET ON";
        if ($this->state == "off") {
            $state_response = "TEXT QUIET ON";
        }
        if ($this->state == "on") {
            $state_response = "TEXT QUIET OFF";
        }
        $sms_message .= " | " . $state_response;

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        //$this->response = null;

        $keywords = ['off', 'on'];
        /*
        if ($this->agent_input != null) {
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }
*/
        $input = strtolower($this->input);

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        //  $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                $this->readQuiet();
                return;
            }
            //return "Request not understood";
            // Drop through to piece scanner
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'off':
                            $this->thing->log('switch off');
                            $this->selectChoice('off');
                            //$this->requested_state = "on";
                            return;
                        case 'on':
                            $this->selectChoice('on');
                            //$this->requested_state = "off";
                            return;
                        case 'next':

                        default:
                    }
                }
            }
        }

        // If all else fails try the discriminator.

        $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.
        switch ($this->requested_state) {
            case 'on':
                $this->selectChoice('on');
                return;
            case 'off':
                $this->selectChoice('off');
                return;
        }

        $this->readQuiet();

        return "Message not understood";

        return false;
    }

    /**
     *
     * @param unknown $input
     * @param unknown $discriminators (optional)
     * @return unknown
     */
    function discriminateInput($input, $discriminators = null)
    {
        //$input = "optout opt-out opt-out";

        if ($discriminators == null) {
            $discriminators = ['on', 'off'];
        }

        $default_discriminator_thresholds = [2 => 0.3, 3 => 0.3, 4 => 0.3];

        if (count($discriminators) > 4) {
            $minimum_discrimination = $default_discriminator_thresholds[4];
        } else {
            $minimum_discrimination =
                $default_discriminator_thresholds[count($discriminators)];
        }

        $aliases = [];

        $aliases['on'] = ['red', 'on'];
        $aliases['off'] = ['green', 'off'];
        //$aliases['reset'] = array('rst','reset','rest');
        //$aliases['lap'] = array('lap','laps','lp');

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

                foreach ($aliases[$discriminator] as $alias) {
                    if ($word == $alias) {
                        $count[$discriminator] = $count[$discriminator] + 1;
                        $total_count = $total_count + 1;
                    }
                }
            }
        }

        $this->thing->log(
            'Agent "Flag" has a total count of ' . $total_count . '.'
        );
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
            return $selected_discriminator;
        } else {
            return false; // No discriminator found.
        }

        return true;
    }
}
