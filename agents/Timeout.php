<?php
namespace Nrwtaylor\StackAgentThing;

class Timeout extends Agent
{
    public $var = "hello";

    public function init()
    {
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $timeout = false;

        if ($timeout) {
            $this->sms_message = "TIMEOUT";

            if ($this->agent_input != null) {
                $this->sms_message .= " | " . $this->agent_input;
            }

            $this->sms_message .=
                " | " . number_format($this->thing->elapsed_runtime()) . "ms.";

            $choices = false;

            $this->thing_report["choices"] = $choices;
            $this->thing_report["info"] =
                "This stops a query from running too long.";
            $this->thing_report["help"] =
                "This is about processor resource management.";

            $this->thing_report["sms"] = $this->sms_message;
            $this->thing_report["message"] = $this->sms_message;
            $this->thing_report["txt"] = $this->sms_message;
        } else {
            $d = rand(1, 20);

            switch (true) {
                case $d <= 2:
                    $cashmessage_thing = new Cashmessage($this->thing);
                    $this->thing_report = $cashmessage_thing->thing_report;
                    break;
                case $d <= 4:
                    $hashmessage_thing = new Hashmessage($this->thing);
                    $this->thing_report = $hashmessage_thing->thing_report;
                    break;

                case $d <= 20:
                    //$message_thing = new Message($this->thing, $this->thing_report);
                    $this->thing_report["info"] = "No message sent.";
                    break;
                default:
            }
        }
    }

    public function readSubject()
    {
    }
}
