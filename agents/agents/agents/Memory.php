<?php
namespace Nrwtaylor\StackAgentThing;

class Memory extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doMemory();
    }

    public function doMemory()
    {
        if ($this->agent_input == null) {
            $array = array('hmmm');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "MEMORY | " . strtolower($v) . ".";

            $this->memory_message = $response; // mewsage?
        } else {
            $this->memory_message = $this->agent_input;
        }
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a memory.";
        $this->thing_report["help"] = "This is about ... hmmm.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("memory" => array("memory"));
        $this->sms_message = "" . $this->memory_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "memory");
        $choices = $this->thing->choice->makeLinks('memory');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
