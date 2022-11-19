<?php
namespace Nrwtaylor\StackAgentThing;

class Count extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doCount();
    }

    public function doCount()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "COUNT | " . strtolower($v) . ".";

            $this->count_message = $response; // mewsage?
        } else {
            $this->count_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is counting stuff.";
        $this->thing_report["help"] = "This does things that help with counting.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

    }

    function makeSMS()
    {
        $this->node_list = array("count" => array("count", "dog"));
        $this->sms_message = "" . $this->count_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

// Refactor to someplace better
public function falsesCount($array) {

$falses = 0;
foreach((array)$array as $arr) {
if ($arr === false) {$falses +=1;}
}
return ($falses);

}



    function makeChoices()
    {
     //   $this->thing->choice->Create('channel', $this->node_list, "count");
     //  $choices = $this->thing->choice->makeLinks('cat');
     //   $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
