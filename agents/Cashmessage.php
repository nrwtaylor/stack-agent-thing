<?php
namespace Nrwtaylor\StackAgentThing;

class Cashmessage extends Agent
{
    public $var = "hello";

    public function init()
    {
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->cash_message = '$cashmessage';

        $this->sms_message = "" . $this->cash_message;

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["info"] = "This creates a cashtag message.";
        $this->thing_report["help"] =
            "This is about paidfor message injection.";

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
