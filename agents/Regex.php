<?php
namespace Nrwtaylor\StackAgentThing;

// dev not tested

class Regex extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function run()
    {
    }

    public function readRegex()
    {
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "HTML | " . strtolower($v) . ".";

            $this->html_message = $response; // mewsage?
        } else {
            $this->html_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This agent handles regex.";
        $this->thing_report["help"] =
            "This is about recognizing and processing regex.";

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $this->sms_message = "REGEX";
        $this->thing_report["sms"] = $this->sms_message;
    }

    function validateRegex($text)
    {
        set_error_handler([$this,"warning_handler"], E_WARNING);
        try {
            $test = preg_match($text, "");
        } catch (Exception $e) {
            return false;
            // ...
        }

        restore_error_handler();
        return true;
    }

    function warning_handler($errno, $errstr, $errfile, $errline)
    {
        $err_text = $errno . " " . $errstr . " " . $errfile . " " . $errline;
        $regex_error = "MERP";
        throw new \Exception($err_text);
    }

    public function readSubject()
    {
        $filtered_text = $this->assert($this->input, "regex", false);
var_dump($this->validateRegex($filtered_text));
        return false;
    }
}
