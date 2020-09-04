<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Evacsim extends Agent
{
    public $var = 'hello';

    public function init()
    {
        //        $this->start_time = microtime(true);

        $this->keyword = "evacsim";

        $this->test = "Development code"; // Always

        $this->node_list = [
            "off" => [
                "on" => [
                    "off",
                    "unit knock" => ["blue", "pink", "red", "yellow", "orange"],
                    "report clear",
                ],
            ],
        ];

        $this->variables_thing = new Variables(
            $this->thing,
            "variables evacsim " . $this->from
        );
    }

    function set($requested_state = null)
    {
        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        if ($requested_state == "X") {
            return true;
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
        $this->previous_state = $this->variables_thing->getVariable("state");
        $this->refreshed_at = $this->variables_thing->getVariables(
            "refreshed_at"
        );

        if ($this->previous_state == false) {
            $this->previous_state = "X";
        }

        $this->thing->choice->Create(
            $this->keyword,
            $this->node_list,
            $this->previous_state
        );

        if (!isset($this->requested_state)) {
            $this->requested_state = "X";
        }
        $this->thing->choice->Choose($this->requested_state);

        $this->state = $this->thing->choice->current_node;

        $this->state = $this->previous_state;
    }

    function readEvacsim()
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

        //$this->thing->log('Agent "' . ucwords($this->keyword) . '" choice selected was "' . $choice . '".');

        return $this->state;
    }

    function makeSMS()
    {
        if (isset($this->sms_message)) {
            $this->thing_report['sms'] = $this->sms_message;
            return $this->sms_message;
        }

        if ($this->state == "inside nest") {
            $t = "NOT SET";
        } else {
            $t = $this->state;
        }

        $this->sms_message = "EVACSIM IS " . strtoupper($t);
        $this->sms_message .=
            " | nuuid " .
            substr($this->variables_thing->variables_thing->uuid, 0, 4);

        if ($this->state == "off") {
            $this->sms_message .= " | TEXT EVACSIM ON";
        } else {
            $this->sms_message .= " | TEXT ?";
        }

        $this->thing_report['sms'] = $this->sms_message;
        return $this->sms_message;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = $this->variables_thing->thing->choice->makeLinks(
            $this->state
        );
        //$choices = false;
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

        //$this->thing_report['sms'] = $sms_message;
        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $test_message; // NRWTaylor. Slack won't take hmtl raw. $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] =
            'This is Evacsim.  A tool developed to be supportive of NSEM.';
    }

    function eventKnock()
    {
        //        $outcomes = array("blue"=>"Not home when first canvassed-must be canvassed again.",
        //                        "pink"=>"Have been notified of an order to evacuate",
        //                        "red"=>"Notified & needs assistance to evacuate",
        //                        "yellow"=>"Verified as being evacuated",
        //                        "orange"=>"Notified & are refusing to evacuate.");

        $knocks = [
            "ring, ring.",
            "knock. knock.",
            "brring. brring.",
            "ring.",
            "Thud. Thud.",
            "Chimes.",
        ];

        $n = count($knocks);
        //var_dump($n);
        $i = rand(1, $n) - 1;

        $knock = $knocks[$i];

        $responses = [
            "1 | child refusal | A child answers the door. No one else comes to the door.",
            "1 | no response | There is no answer.  You knock twice more, and still no answer.",
            "1 | support | You hear dogs barking inside.  A person answers who tells you there are 5 people at home, one uses a walking frame.",
            "1 | refusal | It isn't clear how many people are in the unit.",
            "1 | no response | You hear people inside, but no-one comes to the door.",
            "1 | refusal | It isn't clear how many people are in the unit.",
            "1 | notified | It isn't clear how many people are in the unit.",
            "1 | notified | 4 people are in the unit.",
            "1 | notified | 3 people are in the unit.",
        ];

        $n = count($responses);
        $i = rand(1, $n) - 1;
        $response = $responses[$i];
        $pieces = explode(" | ", $response);

        $this->knock_weight = $pieces[0];
        $this->knock_response = $pieces[1];
        $this->knock_text = $pieces[2];

        $this->sms_message =
            "EVACSIM | " .
            $knock .
            " " .
            $this->knock_text .
            " | " .
            strtoupper($this->knock_response);

        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
        $this->response = null;

        $keywords = ['off', 'on', 'knock', 'unit knock', 'block clear'];

        $input = strtolower($this->subject);

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                $this->readEvacsim();
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

                        case 'knock':
                            $this->eventKnock();

                            return;

                        case 'next':

                        default:
                    }
                }
            }
        }

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

        $this->readEvacsim();

        return "Message not understood";

        return false;
    }
}
