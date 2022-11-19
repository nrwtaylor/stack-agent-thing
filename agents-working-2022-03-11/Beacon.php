<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Beacon extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->keyword = "beacon";
        $this->node_list = ["beacon" => ["on" => ["off"]]];
        $this->link = $this->web_prefix . "thing/" . $this->uuid . "/beacon";

    }

    function set($requested_state = null)
    {
        if ($requested_state == null) {
            return;
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

    function get()
    {
        $this->variables_thing = new Variables(
            $this->thing,
            "variables beacon " . $this->from
        );

        if (!isset($this->requested_state)) {
            if (!isset($this->state)) {
                $this->requested_state = "X";
            } else {
                $this->requested_state = $this->state;
            }
        }

        $this->previous_state = $this->variables_thing->getVariable("state");
        $this->refreshed_at = $this->variables_thing->getVariables(
            "refreshed_at"
        );

        $this->thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->previous_state
        );
        $this->thing->choice->Choose($this->requested_state);

        $this->state = $this->thing->choice->current_node;

        $this->state = $this->previous_state;

        // Bring in stuff
        $this->getPlace();
        $this->getFlag();
        $this->getHeadcode();

    }

    public function selectChoice($choice = null)
    {
        if ($choice == null) {
            return;
        }

        $this->thing->log(
            'Agent "' . ucwords($this->keyword) . '" chose "' . $choice . '".'
        );

        $this->set($choice);
    }

    public function respondResponse()
    {
        $this->makeBeacon();
        // Thing actions

        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["email"] = $this->sms_message;

        //        $this->makeMessage();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
        //        $this->makeWeb();

        $this->thing_report["help"] =
            "This is your Beacon. Try BEACON ON. BEACON OFF. PLACE IS PRIDE. FLAG IS RAINBOW.";

        //        return;
    }

    function makeChoices()
    {
        $this->node_list = ["beacon" => null];

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "beacon"
        );

        $this->choices = $this->thing->choice->makeLinks("beacon");

        $this->thing_report["choices"] = $this->choices;
    }

    function makeMessage()
    {
        switch ($this->state) {
            case "off":
                $m = "The beacon is off.";
                break;
            case "on":
                if (!isset($this->place->place_name)) {
                    $place = "NOT SET";
                } else {
                    $place = strtoupper($this->place->place_name);
                }

                $m = "The beacon is at " . strtoupper($this->place->place_name);
                $m .= " with a " . strtoupper($this->flag->state) . " flag.";
                $m .=
                    " Train " .
                    strtoupper($this->headcode->head_code) .
                    " is running.";
                break;
            default:
                $m = "The beacon is not on.";
        }

        $this->message = $m;
        $this->thing_report["message"] = $m;
    }

    function getPlace()
    {
        $this->place = new Place($this->thing, "place");

        if (
            !isset($this->place->place_name) or
            $this->place->place_name == false
        ) {
            $this->place->place_name = "X";
        }

    }

    function getHeadcode()
    {
        $this->headcode = new Headcode($this->thing, "headcode");
        if (!isset($this->headcode->head_code)) {
            $this->headcode->head_code = "X";
        }
    }

    function getFlag()
    {
        $this->flag = new Flag($this->thing, "flag");

        if (!isset($this->flag->state) or $this->flag->state == false) {
            $this->flag->state = "X";
        }

        $this->thing->log(
            $this->agent_prefix . " got a flag " . $this->flag->state . "."
        );
    }

    function makeSMS()
    {
        if ($this->state == false) {
            $text = "X";
        } else {
            $text = $this->state;
        }
        $sms_message = "BEACON IS " . strtoupper($text);

        switch ($this->state) {
            case "off":
                $sms_message .= " | The beacon is off.";
                break;
            case "on":
                if ($this->flag->state == false) {
                    $flag_state = "X";
                } else {
                    $flag_state = $this->flag->state;
                }
                $sms_message .= " | flag " . strtoupper($flag_state);
                $sms_message .=
                    " | headcode " . strtoupper($this->headcode->head_code);

                if ($this->place->place_name == false) {
                    $place_name = "X";
                } else {
                    $place_name = $this->place->place_name;
                }
                $sms_message .= " | place " . strtoupper($place_name);

                $sms_message .= " | link " . $this->link;

                break;
            default:
                $sms_message .= " | The beacon is not on.";
        }

        $sms_message .=
            " | nuuid " .
            substr($this->variables_thing->variables_thing->uuid, 0, 4);

        $sms_message .= " | TEXT HELP";

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    /**
     *
     */
    function makeWeb()
    {
        $web = "<b>Beacon Agent</b>";
        $web .= "<p>";
        $web .= "<p>";

        switch ($this->state) {
            case "on":
                $link = $this->web_prefix . "thing/" . $this->uuid . "/agent";

                $link_txt =
                    $this->web_prefix . "thing/" . $this->uuid . "/place.txt";

                $this->node_list = ["beacon" => ["beacon", "job"]];

                $web .= $this->place->html_image;
                $web .= "<br>";

                $web .= "<br>";

                $web .= '<a href="' . $this->flag->link . '">';
                $web .= $this->flag->html_image;
                $web .= "</a>";
                $web .= "<br>";

                $web .= "<br>";

                $refreshed_at = max(
                    strtotime($this->flag->refreshed_at),
                    strtotime($this->place->refreshed_at)
                );

                $ago = $this->thing->human_time(
                    strtotime($this->thing->time()) - $refreshed_at
                );

                $web .= "Last asserted about " . $ago . " ago.";

                $web .= "<br>";

                break;
            case "off":
            default:
                $web .= $this->message;
                $web .= "<br>";
                break;
        }

        $this->thing_report["web"] = $web;
    }

    function makeBeacon()
    {
        $this->flag->makePNG();
        $this->headcode->makePNG();
        $this->place->makePNG();
    }

    public function readSubject()
    {
        $this->response = null;

        $keywords = ["off", "on"];

        $input = strtolower($this->subject);

        // Because the identity is likely to be in the from address
        $haystack = $this->agent_input . " " . $this->subject;

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

        // If all else fails try the discriminator.
        $input_agent = new Input($this->thing, "input");
        $discriminators = ['on', 'off'];
        $input_agent->aliases['on'] = ['red', 'on','active','activate'];
        $input_agent->aliases['off'] = ['green', 'off','deactivate','inactive'];

        $type = $input_agent->discriminateInput($input, $discriminators);

        if ($type != false) {
            $this->requested_state = $type;
        }

        switch ($this->requested_state) {
            case "on":
                $this->selectChoice("on");
                return;
            case "off":
                $this->selectChoice("off");
                return;
        }

        // Message not understood
    }
}
