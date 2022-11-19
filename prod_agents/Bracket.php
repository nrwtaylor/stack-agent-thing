<?php
namespace Nrwtaylor\StackAgentThing;

// Take a string and parse brackets

class Bracket extends Agent
{
    public $var = 'hello';

    function init()
    {
$this->recognized_brackets = [['(',')'],['[',']'],['{','}']];

    }

    function run()
    {
        $this->doBracket();
    }

    public function doBracket()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "BRACKET | " . strtolower($v) . ".";

            $this->bracket_message = $response; // mewsage?
        } else {
            $this->bracket_message = $this->agent_input;
        }
    }

    function parseBrackets($text)
    {
foreach($this->recognized_brackets as $i=>$bracket) {
$parts = explode($bracket[0], $text);
$parts_b = explode($bracket[1], $parts[0]);
var_dump($parts_b);

}


    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is an agent for dealing with brackets.";
        $this->thing_report["help"] = "This is about BODMAS and order of operations.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeSMS()
    {
$response = "No response.";
if ($this->response != "") {$response = $this->response;}
        $this->sms_message = "" . $this->bracket_message . $this->response;;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
$input = $this->input;
$filtered_input = $this->assert($input);

$this->parseBrackets($filtered_input);

        return false;
    }
}
