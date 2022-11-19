<?php
namespace Nrwtaylor\StackAgentThing;

class Error extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
       $this->runError();
    }

    public function runError()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "ERROR | " . strtolower($v) . ".";

            $this->error_message = $response; // mewsage?
        } else {
            $this->error_message = $this->agent_input;
        }
    }

    public function thingError($t) {

            $web_thing = new Thing(null);
            $web_thing->Create(
                $this->from,
                "error",
                "Throwable: Set failed. " . $this->textError($t) .
                    " " .
                    $t->getTraceAsString()
            );



    }

    public function textError($t) {

      $text = $t->getMessage() . " " . $t->getLine() . " " . $t->getFile();
      return $text;

    }

    function makeSMS()
    {
        $this->node_list = array("error" => array("exception"));
        $this->sms_message = "" . $this->error_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
        return false;
    }
}
