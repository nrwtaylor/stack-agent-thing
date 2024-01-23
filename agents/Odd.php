<?php
namespace Nrwtaylor\StackAgentThing;

class Odd extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function run()
    {
        $this->doOdd();
    }

    public function isOdd($integer_number)
    {
        if (!is_numeric($integer_number)) {
            return true;
        }
        if (!is_integer($integer_number)) {
            return true;
        }

        if ($integer_number % 2 == 0) {
            return true;
        }

        return false;
    }

    public function doOdd()
    {
        if ($this->agent_input == null) {
            $array = [$this->agent_name];
            $k = array_rand($array);
            $v = $array[$k];
            $response =
                strtoupper($this->agent_name) . " | " . strtolower($v) . ".";

            $this->{$this->agent_name . "_message"} = $response; // mewsage?
        } else {
            $this->{$this->agent_name . "_message"} = $this->agent_input;
        }
    }

    function makeSMS()
    {
        $this->node_list = [$this->agent_name => [$this->agent_name]];
        $this->sms_message = "" . $this->odd_message;
        $this->thing_report["sms"] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            "channel",
            $this->node_list,
            $this->agent_name
        );
        $choices = $this->thing->choice->makeLinks($this->agent_name);
        $this->thing_report["choices"] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
