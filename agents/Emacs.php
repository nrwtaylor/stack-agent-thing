<?php
namespace Nrwtaylor\StackAgentThing;

class Emacs extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function claws()
    {
    }

    function test()
    {
        if ($this->emacs_test_flag != "on") {
            return;
        }
        $this->response .= "No test performed. ";
    }

    public function respondResponse()
    {
        $this->thing_report["info"] =
            "This is a tool interacting with EMACS.";
        $this->thing_report["help"] = "No user facing actions available.";

        $this->thing_report["message"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function updateEmacs($filename = null, $text = null)
    {
        if ($text == null) {
            return true;
        }
        $filename = trim($filename, '"');

        if (!file_exists($filename)) {
            return true;
        }

        file_put_contents($filename, $text, FILE_APPEND | LOCK_EX);
    }

    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = trim($this->assert($input));

    }
}
