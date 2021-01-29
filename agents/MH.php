<?php
namespace Nrwtaylor\StackAgentThing;

class MH extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doMH();
    }

    public function doMH()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "MH | " . strtolower($v) . ".";

            $this->mh_message = $response; // mewsage?
        } else {
            $this->mh_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is an agent to handle the MH email format.";
        $this->thing_report["help"] = "This mostly deals with equal signs at the end of lines.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    public function metaMH($text = null) {

       // Test and dev.
       // Extract subject line

       return "TODO Extract subject line - see MH.php";

    }

    public function textMH($text = null) {

       if ($text == null) {return;}

       // Test and dev.

       $lines = preg_split("/\r\n|\n|\r/", $text);
       $new_lines = [];
       foreach($lines as $i=>$line) {
           $new_line = trim(" =");
           $new_lines[] = $new_line;

       }

       $contents = implode("\n", $new_lines);
       return $contents;
    }

    function makeSMS()
    {
        $this->node_list = array("mh" => array("mh", "dog"));
        $this->sms_message = "" . $this->mh_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "mh");
        $choices = $this->thing->choice->makeLinks('mh');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
