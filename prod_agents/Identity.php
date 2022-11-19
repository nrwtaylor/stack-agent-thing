<?php /**
 * Identity.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Identity extends Agent
{
    public $var = "hello";

    /**
     */
    function init()
    {
        $this->thing_report["help"] =
            "This is your Identity. You can turn your Identity ON and OFF.";

        $this->keyword = "identity";

        $this->node_list = ["identity" => ["on" => ["off"]]];

        $this->variables_thing = new Variables(
            $this->thing,
            "variables identity " . $this->from
        );
    }

    /**
     *
     */
    function run()
    {
        $this->makeChoices();
    }

    /**
     *
     * @param unknown $requested_state (optional)
     */
    function set($requested_state = null)
    {
        if ($requested_state == null) {
            if (
                !isset($this->requested_state) or
                $this->requested_state == null
            ) {
                $this->requested_state = "X";
            }
            $requested_state = $this->requested_state;
        }

        $this->variables_thing->setVariable("state", $requested_state);

        $this->variables_thing->setVariable(
            "refreshed_at",
            $this->current_time
        );

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
        $this->previous_state = $this->variables_thing->getVariable("state");

        $this->refreshed_at = $this->variables_thing->getVariables(
            "refreshed_at"
        );

        $this->thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->previous_state
        );

        $this->state = $this->thing->choice->current_node;

        $this->state = $this->previous_state;
        $this->requested_state = $this->state;
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

            //        $choice = 'off'; // Fail off.
        }

        $this->thing->log('"chose "' . $choice . '".');

        //        $this->set($choice);
        $this->requested_state = $choice;
        $this->state = $choice;

        return $this->state;
    }

    /**
     *
     */
    function makeChoices()
    {
        $choices = $this->variables_thing->thing->choice->makeLinks(
            $this->state
        );
        $this->thing_report["choices"] = $choices;
    }

    /**
     *
     */
    function makeSMS()
    {
        if ($this->state == false) {
            $t = "X";
        } else {
            $t = $this->state;
        }
        $sms_message = "IDENTITY IS " . strtoupper($t);

        if ($this->state == "on") {
            $sms_message .= " | identity " . strtoupper($this->from);
        }

        $sms_message .=
            " | nuuid " .
            substr($this->variables_thing->variables_thing->uuid, 0, 4);

        if ($this->state == "off") {
            $sms_message .= " | TEXT IDENTITY ON";
        } else {
            $sms_message .= " | TEXT ?";
        }

        $this->thing_report["sms"] = $sms_message;
    }

    /**
     *
     */
    function makeWeb()
    {
        $this->thing_report["web"] =
            "Web identity is not available in this channel.";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->response = null;

        $keywords = ["off", "on"];

        $input = strtolower($this->subject);

        // Because the identity is likely to be in the from address
        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        //  $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                //                $this->read();
                return;
            }
            //return "Request not understood";
            // Drop through to piece scanner
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "off":
                            $this->thing->log("switch off");
                            $this->selectChoice("off");
                            return;
                        case "on":
                            $this->selectChoice("on");
                            return;
                        case "next":
                        default:
                    }
                }
            }
        }

        $discriminators = [
            "on" => ["red", "on"],
            "off" => ["green", "off"],
        ];

        $type = $this->discriminateInput($input, $discriminators);
        if ($type != false) {
            $this->requested_state = $type;
        }

        // If all else fails try the discriminator.
        switch ($this->requested_state) {
            case "on":
                $this->selectChoice("on");
                return;
            case "off":
                $this->selectChoice("off");
                return;
        }

        // Don't read anything else.
        //        $this->read();

        return "Message not understood";
    }
}
