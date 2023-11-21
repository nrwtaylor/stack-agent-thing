<?php
namespace Nrwtaylor\StackAgentThing;

class Noon extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doNoon();
    }

    public function doNoon()
    {
        if ($this->agent_input == null) {
    //        $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
     //       $k = array_rand($array);
     //       $v = $array[$k];

       //     $response = "CAT | " . strtolower($v) . ".";
$response = "NOON";
            $this->noon_message = $response; // mewsage?
        } else {
            $this->noon_message = $this->agent_input;
        }
    }


    // -----------------------
/*
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }
*/
    function makeSMS()
    {
        $this->node_list = array("noon" => array("noon"));
        $this->sms_message = "" . $this->noon_message;
        $this->thing_report['sms'] = $this->noon_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "noon");
        $choices = $this->thing->choice->makeLinks('cat');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
