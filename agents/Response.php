<?php
namespace Nrwtaylor\StackAgentThing;

class Response extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doResponse();
    }

    public function doResponse()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "RESPONSE | " . strtolower($v) . ".";

            $this->response_message = $response; // mewsage?
        } else {
            $this->response_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeSMS()
    {
        $this->sms_message = "" . $this->response_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
        return false;
    }
}
