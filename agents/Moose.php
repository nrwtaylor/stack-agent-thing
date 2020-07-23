<?php
namespace Nrwtaylor\StackAgentThing;

class Moose extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doMoose();
    }

    public function doMoose()
    {
        if ($this->agent_input == null) {
            $array = array('subtle, throaty, airy grunting');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "MOOSE | " . strtolower($v) . ".";

            $this->moose_message = $response;
        } else {
            $this->moose_message = $this->agent_input;
        }
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "cat");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a moose. It is just a moose.";
        $this->thing_report["help"] = "This is about different animals.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("moose" => array("moose", "rocky", "cat", "dog"));
        $this->sms_message = "" . $this->moose_message;
        $this->thing_report['sms'] = $this->moose_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "moose");
        $choices = $this->thing->choice->makeLinks('moose');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
