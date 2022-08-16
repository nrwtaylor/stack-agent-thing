<?php
namespace Nrwtaylor\StackAgentThing;

class Transducers extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doTransducers();
    }

    public function doTransducers()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "TRANSDUCERS | " . strtolower($v) . ".";

            $this->transducers_message = $response; // mewsage?
        } else {
            $this->transducers_message = $this->agent_input;
        }
    }

    function textTransducers($transducers = null)
    {
$text = "";
foreach($transducers as $i=>$transducer) {

$text .= implode(" ", $transducer) . "\n";

}
return $text;

    }

    function makeSMS()
    {
        $this->node_list = array("transducers" => array("transducer"));
        $this->sms_message = "" . $this->transducers_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
//        $this->thing->choice->Create('channel', $this->node_list, "cat");
//        $choices = $this->thing->choice->makeLinks('cat');
//        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
