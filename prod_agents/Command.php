<?php
namespace Nrwtaylor\StackAgentThing;

class Command extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doCommand();
    }

    public function doCommand()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "COMMAND | " . strtolower($v) . ".";

            $this->command_message = $response; // mewsage?
        } else {
            $this->command_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a command.";
        $this->thing_report["help"] = "Words in capitals are commands.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("command" => array("command"));
        $this->sms_message = "" . $this->command_message;
        $this->thing_report['sms'] = $this->command_message;
    }

    public function extractCommands($text = null) {
        if ($text == null) {return true;}

        $tokens = explode(" ", $text);
$command = "";
$commands = array();
        foreach($tokens as $i=>$token) {
            if ($token == strtoupper($token)) {

                $command .= " " . $token;

            } elseif ($command !="") {

                $commands[] = trim($command);
                $command = "";

            }

        }
    
return $commands;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
