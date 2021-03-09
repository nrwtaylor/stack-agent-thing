<?php
namespace Nrwtaylor\StackAgentThing;

class Cat extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doCat();
    }

    public function doCat()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "CAT | " . strtolower($v) . ".";

            $this->cat_message = $response; // mewsage?
        } else {
            $this->cat_message = $this->agent_input;
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
            "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeSMS()
    {
        $this->node_list = array("cat" => array("cat", "dog"));
        $this->sms_message = "" . $this->cat_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "cat");
        $choices = $this->thing->choice->makeLinks('cat');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
