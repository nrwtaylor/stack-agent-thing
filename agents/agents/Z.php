<?php
namespace Nrwtaylor\StackAgentThing;

class Z extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doZ();
    }

    public function doZ()
    {
        if ($this->agent_input == null) {
            $array = array('There is enough');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "Z | " . strtolower($v) . ".";

            $this->x_message = $response;
        } else {
            $this->x_message = $this->agent_input;
        }
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "z");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is the concept of having enough. There is enough.";
        $this->thing_report["help"] = "Try Z 20.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("z" => array("z", "x"));
        $this->sms_message = "" . $this->x_message;
        $this->thing_report['sms'] = $this->x_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "z");
        $choices = $this->thing->choice->makeLinks('z');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
