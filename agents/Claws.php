<?php
namespace Nrwtaylor\StackAgentThing;

class Claws extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doClaws();
    }

    public function doClaws()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "CLAWS | " . strtolower($v) . ".";

            $this->claws_message = $response; // mewsage?
        } else {
            $this->claws_message = $this->agent_input;
        }
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "claws");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a claws keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("claws" => array("claws", "dog"));
        $this->sms_message = "" . $this->claws_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "claws");
        $choices = $this->thing->choice->makeLinks('claws');
        $this->thing_report['choices'] = $choices;
    }

    public function filenameClaws($text = null) {
       if ($text == null) {return true;}

       $this->filename = $text;
    }

    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = $this->assert($input);
        $this->filenameClaws($filtered_input);

var_dump($this->filename);

$text = null;
if (is_string($this->filename)) {
        $text = file_get_contents($this->filename);
}

var_dump($text);

        return false;
    }
}
