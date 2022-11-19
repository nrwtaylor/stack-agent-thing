<?php
namespace Nrwtaylor\StackAgentThing;

class Hashmessage extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $this->hash_message = "#hashmessage";
        } else {
            $this->hash_message = $this->agent_input;
        }

        $this->sms_message = "" . $this->hash_message;

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["info"] = "This creates a hashtag message.";
        $this->thing_report["help"] =
            "This is about informational message injection.";

        $this->thing_report["sms"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function readSubject()
    {
    }
}
