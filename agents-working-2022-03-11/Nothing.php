<?php
namespace Nrwtaylor\StackAgentThing;

class Nothing extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doNothing();
    }

    public function doNothing()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "NOTHING | " . strtolower($v) . ".";

            $this->nothing_message = $response; // mewsage?
        } else {
            $this->nothing_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
    }

    function makeSMS()
    {
        $this->node_list = array("nothing" => array("nothing"));
        $this->sms_message = "" . $this->nothing_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
        return false;
    }
}
