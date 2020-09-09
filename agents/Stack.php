<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Stack extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->created_at = $this->thing->thing->created_at;

        $this->current_time = $this->thing->json->time();

        $this->default_state = "off";
        $this->state = $this->default_state;
//        $this->countStack();

        $this->node_list = ["stack" => ["agent", "thing"], "null" => ["stack"]];

    }

    public function run() {

        $this->countStack();

    }

    function resetStack()
    {
        $this->getStart();
    }

    function set($requested_state = null)
    {
        if ($requested_state == null) {
            if (!isset($this->requested_state)) {
                // Set default behaviour.
                // $this->requested_state = "green";
                // $this->requested_state = "red";
                $this->requested_state = "green"; // If not sure, show green.
            }
            $requested_state = $this->requested_state;
        }

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        $this->stack->setVariable("state", $this->state);

        //$this->nuuid = substr($this->variables_thing->variables_thing->uuid,0,4);
        //$this->variables_thing->setVariable("flag_id", $this->nuuid);

        $this->stack->setVariable("refreshed_at", $this->current_time);

        $this->stack->setVariable("count", $this->count);
        $this->stack->setVariable("identity", $this->identity);

        $this->thing->log(
            $this->agent_prefix . 'set Stack to ' . $this->state,
            "INFORMATION"
        );
    }

    function get()
    {
        $this->stack = new Variables(
            $this->thing,
            "variables stack " . $this->from
        );

        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->previous_state = $this->stack->getVariable("state");
        $this->refreshed_at = $this->stack->getVariable("refreshed_at");

        $identity = $this->stack->getVariable("identity");

        if (isset($identity)) {
            $this->identity = $identity;
            $this->response = "Got existing identity.";
        }

        $f = $this->getThing();
        if ($f == true) {
            $this->getStart();
            $f = $this->getThing();
            $this->response = "Got new identity.";
        }

        if ($f == true) {
            $this->thing->log("Failed to retrieve thing");
            return;
        }

        $this->thing->log(
            $this->agent_prefix . 'got from db ' . $this->previous_state,
            "INFORMATION"
        );

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isStack($this->previous_state)) {
            $this->state = $this->previous_state;
        } else {
            $this->state = $this->default_state;
        }

        //        $this->getThing();

        //        $this->thing->choice->Create($this->keyword, $this->node_list, $this->state);
        //        $check = $this->thing->choice->current_node;

        $this->thing->log(
            $this->agent_prefix .
                'got a ' .
                strtoupper($this->state) .
                ' FLAG.',
            "INFORMATION"
        );

        return;
    }

    function isStack($state = null)
    {
        // Validates whether the Flag is green or red.
        // Nothing else is allowed.

        if ($state == null) {
            if (!isset($this->state)) {
                $this->state = $this->default_state;
            }
            $state = $this->state;
        }

        if ($state == "red" or $state == "green") {
            return false;
        }

        return true;
    }

    function getStart()
    {
        // This returns an uuid

        $thing_json = @file_get_contents(
            $this->web_prefix . "api/redpanda/start"
        );

        if ($thing_json == false) {
            return true;
        }

        $thing_array = json_decode($thing_json, true);

        if ($thing_array == null) {
            return true;
            // No thing found
        }

        $this->identity = $thing_array["thing"]["uuid"];
    }

    function getThing()
    {
        $this->thing->log(
            "Loading " .
                $this->identity .
                " from " .
                str_replace("@", "", $this->mail_postfix) .
                "."
        );
        $url = $this->web_prefix . "api/redpanda/thing/" . $this->identity;

        $thing_json = @file_get_contents($url);

        if ($thing_json == false) {
            return true;
        }

        $thing_report = $this->thing->json->jsontoArray($thing_json);

        if ($thing_report['thing'] == null) {
            $this->response .= "Thing not found. ";
            return true;
            // No thing found
        }

        $thing = $thing_report['thing'];

        $this->variables = $thing["variables"];
        $this->settings = $thing['settings'];

    }

    function variablesStack()
    {
        $this->agent = $this->variables['agent'];
        $this->account = $this->variables['account'];
    }

    function printArrayList($array, $h = null, $depth = 0)
    {
        if ($array == false) {
            $h = "No data found.";
            return $h;
        }

        $depth = $depth + 1;
        if ($h = null) {
            $h = "Start";
        }
        $h .= "<ul>";

        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $h .= "<li>" . $k . "</li>";
                //$depth = $depth + 1;
                $h .= $this->printArrayList($v, $h, $depth);
                //$depth = $depth - 1;
                continue;
            }
            if ($v == null or $v == false) {
                $v = "X";
            }
            $h .= "<li>" . $k . " is " . $v . "</li>";
        }
        $h .= "</ul>";
        $depth = $depth - 1;
        return $h;
    }

    public function makeWeb()
    {
        if (!isset($this->variables)) {
            $this->getThing();
        }

        $w = "<b>Stack Agent</b><br>";
        //        $w .= "stack is " . $this->stack_uuid. "<br>";
        $w .= "state is " . strtoupper($this->state) . "<br>";

        $w .= "identity is " . $this->identity . "<br>";
        $w .= "count is " . $this->count . "<br>";
        $w .= "response is " . $this->response . "<br>";
        $w .= "message is " . $this->sms_message . "<br>";
        //  $w .= print_r($this->variables) . "<br>";
        $w .= "Variables<br>";
        $variables = [];
        if (isset($this->variables)) {
            $variables = $this->variables;
        }

        $w .= $this->printArrayList($variables) . "<br>";
        $w .= "Settings<br>";

        $settings = [];
        if (isset($this->settings)) {
            $variables = $this->settings;
        }

        $w .= $this->printArrayList($settings) . "<br>";

        $this->thing_report['web'] = $w;
    }

    public function makeChoices()
    {
        // Make buttons
        // $choices = false;

        if ($this->from == "null" . $this->mail_postfix) {
            $this->thing->choice->Create(
                $this->agent_name,
                $this->node_list,
                "null"
            );
            $choices = $this->thing->choice->makeLinks("null");
        } else {
            $this->thing->choice->Create(
                $this->agent_name,
                $this->node_list,
                "stack"
            );
            $choices = $this->thing->choice->makeLinks('stack');
        }

        $this->thing_report['choices'] = $choices;
        return;
    }

    private function getCount()
    {
        if (!isset($this->count)) {
            $this->count = $this->countSack();
        }
    }

    function countStack()
    {
        $thing_report = $this->thing->db->count();
        $this->count = $thing_report['number'];
    }

    function isNumeric($number = null)
    {
        return is_numeric($number);
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->messageStack($this->response);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['keyword'] = 'stack';
        //$this->thing_report['info'] = 'Ping agent pinged back';
        $this->thing_report['help'] = 'Useful for checking the stack.';

    }

    function messageStack($text = null)
    {
        $text = @file_get_contents(
            $this->web_prefix . "api/redpanda/" . "stack"
        );
        if ($text == false) {
            return true;
        }
    }

    function makeSMS()
    {
        $this->sms_message = "STACK";
        $this->sms_message .=
            " | count " . number_format($this->count) . " Things";
        if (!isset($this->identity)) {
            $identity = "X";
        } else {
            $identity = $this->identity;
        }
        $this->sms_message .= " | identity " . $identity;
        $this->sms_message .= " | TEXT LATENCY";

        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
    }
}
