<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Mordok extends Agent
{
    public $var = 'hello';

    public function init()
    {
        $this->start_time = microtime(true);

        $this->keyword = "mordok";

        $this->agent_prefix = 'Agent "Mordok" ';

        $this->test = "Development code"; // Always

        $this->node_list = ["off" => ["on" => ["off"]]];

        //        $this->end_time = microtime(true);
        //        $this->actual_run_time = $this->end_time - $this->start_time;
        //        $milliseconds = round($this->actual_run_time * 1000);

        //        $this->thing->log( $this->agent_prefix .'ran for ' . $milliseconds . 'ms.' );

        //        $this->thing_report['log'] = $this->thing->log;

        //		return;
    }

    public function set($requested_state = null)
    {
        if ($requested_state == null) {
            return;
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

    public function get()
    {
        $this->variables_thing = new Variables(
            $this->thing,
            "variables mordok " . $this->from
        );

        $this->previous_state = $this->variables_thing->getVariable("state");
        $this->refreshed_at = $this->variables_thing->getVariables(
            "refreshed_at"
        );

        if (!isset($this->requested_state)) {
            if (isset($this->state)) {
                $this->requested_state = $this->state;
            } else {
                $this->requested_state = false;
            }
        }

        $this->thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->previous_state
        );
        $this->thing->choice->Choose($this->requested_state);

        $this->state = $this->thing->choice->current_node;

        $this->state = $this->previous_state;
    }

    function readMordok()
    {
        return $this->state;
    }

    function selectChoice($choice = null)
    {
        if ($choice == null) {
            return $this->state;

            //        $choice = 'off'; // Fail off.
        }

        $this->thing->log(
            'Agent "' . ucwords($this->keyword) . '" chose "' . $choice . '".'
        );

        $this->set($choice);

        return $this->state;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = $this->variables_thing->thing->choice->makeLinks(
            $this->state
        );
        $this->thing_report['choices'] = $choices;

        $test_message =
            'Last thing heard: "' .
            $this->subject .
            '".  Your next choices are [ ' .
            $choices['link'] .
            '].';
        $test_message .= '<br>Shift state: ' . $this->state . '<br>';

        $test_message .= '<br>' . $this->sms_message;

        $test_message .=
            '<br>Current node: ' . $this->thing->choice->current_node;

        $test_message .= '<br>Requested state: ' . $this->requested_state;

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $test_message; // NRWTaylor. Slack won't take hmtl raw. $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] =
            'This is a Mordok.  You can turn a Mordok ON and OFF.';
    }

    public function makeSMS()
    {
        if ($this->state == "inside nest" or $this->state == false) {
            $t = "NOT SET";
        } else {
            $t = $this->state;
        }

        $sms_message = "MORDOK IS " . strtoupper($t);
        //        $sms_message .= " | Previous " . strtoupper($this->previous_state);
        //        $sms_message .= " | Now " . strtoupper($this->state);
        //        $sms_message .= " | Requested " . strtoupper($this->requested_state);
        //        $sms_message .= " | Current " . strtoupper($this->base_thing->choice->current_node);
        //        $sms_message .= " | nuuid " . strtoupper($this->thing->nuuid);
        //        $sms_message .= " | base nuuid " . strtoupper($this->variables_thing->thing->nuuid);

        //        $sms_message .= " | another nuuid " . substr($this->variables_thing->uuid,0,4);
        $sms_message .=
            " | nuuid " .
            substr($this->variables_thing->variables_thing->uuid, 0, 4);

        if ($this->state == "off") {
            $sms_message .= " | TEXT MORDOK ON";
        } else {
            $sms_message .= " | TEXT ?";
        }

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    public function readSubject()
    {
        $this->response = null;

        $keywords = ['off', 'on'];

        $input = strtolower($this->subject);

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                $this->readMordok();
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
                            return;
                        case 'on':
                            $this->selectChoice('on');
                            return;
                        case 'next':

                        default:
                    }
                }
            }
        }

        // If all else fails try the discriminator.

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        // Run the discriminator.

        $input_agent = new Input($this->thing, "input");
        $discriminators = ['on', 'off'];

        $input_agent->aliases['on'] = ['red', 'on'];
        $input_agent->aliases['off'] = ['green', 'off'];

        $this->requested_state = $input_agent->discriminateInput($haystack); // Run the discriminator.

        switch ($this->requested_state) {
            case 'on':
                $this->selectChoice('on');
                return;
            case 'off':
                $this->selectChoice('off');
                return;
        }

        $this->readMordok();

        return "Message not understood";

        return false;
    }
}
