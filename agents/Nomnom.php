<?php
namespace Nrwtaylor\StackAgentThing;

class Nomnom extends Agent
{
    function init()
    {
        $this->keywords = [];

        $time_string = $this->thing->Read([
            "nomnom",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["nomnom", "refreshed_at"],
                $time_string
            );
        }
    }

    public function nomnom($text = null)
    {
        $nonnom_agent = new Nonnom($this->thing, "nonnom");
        $t = $nonnom_agent->nonnomify($text);
        $this->response .= $t . " ";
    }

    public function respondResponse()
    {
        $this->cost = 100;

        // Thing stuff

        $this->thing->flagGreen();

        $this->thing_report["message"] = $this->sms_message;

        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $sms = "NOMNOM | " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeEmail()
    {
        $this->email_message = "NOMNOM | ";
    }

    public function readSubject()
    {
        $input = strtolower($this->input);

        $keywords = ["nomnom"];
        $pieces = explode(" ", strtolower($input));

        $text = $this->nomnom($input);

        $status = true;
    }
}
