<?php
namespace Nrwtaylor\StackAgentThing;

class X extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doX();
    }

    public function doX()
    {
        if ($this->agent_input == null) {
            $array = array('Specify how much you need');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "X | " . strtolower($v) . ".";

            $this->x_message = $response;
        } else {
            $this->x_message = $this->agent_input;
        }
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "x");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is the concept of scarcity. There is a limited amount.";
        $this->thing_report["help"] = "Try X 20.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("x" => array("x", "z"));
        $this->sms_message = "" . $this->x_message;
        $this->thing_report['sms'] = $this->x_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "x");
        $choices = $this->thing->choice->makeLinks('x');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
