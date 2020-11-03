<?php
namespace Nrwtaylor\StackAgentThing;

class Success extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doSuccess();
    }

    public function doSuccess()
    {
        if ($this->agent_input == null) {
            $array = array('Awesome', 'Wow');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "SUCCESS | " . strtolower($v) . ".";

            $this->success_message = $response; // mewsage?
        } else {
            $this->success_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is what success looks like.";
        $this->thing_report["help"] = "This is about saying you have been successful.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("success" => array("success"));
        $sms = "SUCCESS | " . $this->success_message;

        $this->sms_message = "" . $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "success");
        $choices = $this->thing->choice->makeLinks('cat');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
