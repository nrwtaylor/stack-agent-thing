<?php
/**
 * State.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// devstack
// Build a tree of allowed states.

class State extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    public function init()
    {
        $this->test = "Development code";

        //        $this->primary_place = "roost";
        $this->primary_place = "state";

        $this->default_state = "off";

        $this->keyword = "state";

        $this->node_list = [
            "on" => ["off" => ["on"]],
            "off" => ["on" => ["off"]],
        ];

        $this->thing_report['help'] = 'This is the state agent.';
    }

    /**
     *
     */
    private function setState()
    {
        return;
        $this->state_thing->choice->Create(
            $this->primary_place,
            $this->node_list,
            $this->state
        );
        $this->state_thing->choice->Choose($this->state);
        $choices = $this->state_thing->choice->makeLinks($this->state);
    }

    function run()
    {
    }

    public function get()
    {
        $flag_variable_name = "";
        // Get the current Identities flag
        $this->state_agent = new Variables(
            $this->thing,
            "variables state" . $flag_variable_name . " " . $this->from
        );

        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->previous_state = $this->state_agent->getVariable("state");
        $this->refreshed_at = $this->state_agent->getVariable("refreshed_at");

        $this->state = $this->previous_state;
    }

    /**
     *
     */

    public function set()
    {
        $this->state_agent->setVariable("state", $this->state);
        $this->state_agent->setVariable("refreshed_at", $this->current_time);

        $this->setState();
    }

    public function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks($this->state);
        $this->choices = $choices;
        $this->thing_report['choices'] = $choices;
    }

    function respondResponse()
    {
        //        $this->thing_report['sms'] = "STATE " . "| " . $this->response;
    }

    public function makeSMS()
    {
        $sms = "STATE ";

        $sms .= strtoupper($this->state_agent->head_code) . " ";

        $sms .= strtoupper($this->state) . "";

        if ($this->previous_state != $this->state) {
            $sms .= " ";
            $sms .= "was previously " . strtoupper($this->previous_state);
        }

        $sms .= " | ";

        $sms .= $this->response;
        $choice_text = "Choices are ";

        $this->thing->choice->Create(
            $this->primary_place,
            $this->node_list,
            $this->state
        );

        $choices = $this->thing->choice->getChoices();

        foreach ($choices as $i => $choice_name) {
            $choice_text .= strtoupper($choice_name) . " / ";
        }
        $sms .= $choice_text;
        $this->thing_report['sms'] = $sms;
    }

    public function readState()
    {
    }

    public function isState($state = null)
    {
        if ($state == null) {
            return false;
        }
        $this->thing->choice->Create('state', $this->node_list);
        $message = $this->thing->choice->getChoices($state);

        if ($message == []) {
            return false;
        }
        return true;
    }

    public function chooseState($state)
    {
        $this->state = $state;
        return;

        // devstack

        $t = $this->state_thing->choice->Choose($state);
        $m = $this->state_thing->choice->load('state');
    }

    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = $this->assert($input);

        if ($filtered_input == "") {
            $this->response .= "Got state. ";
            return;
        }

        if (!$this->isState($filtered_input)) {
            $this->response .= "State not recognized. ";
            return;
        }

        $this->chooseState($filtered_input);
        return;
    }
}
