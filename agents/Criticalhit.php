<?php
namespace Nrwtaylor\StackAgentThing;

class Criticalhit extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->distance_unit_name = "m";
        $this->time_unit_name = "ms";
    }

    function set()
    {
        // UK Commonwealth spelling
        $time_string = $this->thing->time();
        $this->thing->Write(
            ["critical_hit", "refreshed_at"],
            $time_string
        );

    }

    function get()
    {
        $time_string = $this->thing->Read([
            "critical_hit",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["critical_hit", "refreshed_at"],
                $time_string
            );
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["sms"] = $this->sms_message;
    }

    private function criticalhitDo()
    {
        // What does this do?
        $this->getRoll();
    }

    public function doCriticalhit()
    {
        // What is a Critical Hit.
        $this->eventful = new Eventful(
            $this->thing,
            "eventful critical%20%hit"
        );
        $this->response .= "Rolled a " . $this->die_roll . ". ";
    }

    public function doMiss()
    {
        // What is a Critical Hit.
        $this->response .= "Didn't roll a 20. ";
    }

    private function criticalhitRoll($text = null)
    {
        if ($text == null) {
            $text = $this->die_roll;
        }

        $this->outcome = "merp";
        if ($text == "20") {
            $this->outcome = "critical hit";
            $this->doCriticalHit();
        } else {
            $this->doMiss();
        }

        return $this->outcome;
    }

    private function getRoll()
    {
        // Insert code to talk to Concept2.
        $text = "d20";

        $this->roll = new Roll($this->thing, "" . $text); // test uniqueness?
        $this->die_roll = $this->roll->result[1]["roll"];
        $this->criticalhitRoll($this->die_roll);
    }

    public function readSubject()
    {
        $this->response .= "Rolled. ";
        $this->message =
            "http://riotheatre.ca/event/the-critical-hit-show-a-live-dd-comedy-experience/";
        $this->keyword = "critical";

        $this->thing_report["keyword"] = $this->keyword;
        //		$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["email"] = $this->message;

        $this->criticalhitDo();
    }

    public function makeSMS()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/criticalhit";

        if ($this->outcome == "critical hit") {
            $sms = "CRITICAL HIT " . $this->roll->roll . " " . $this->die_roll;
            $sms .= " | " . $this->eventful->message;
        } else {
            $sms = "MERP " . $this->roll->roll . " " . $this->die_roll;
            $sms .= " | " . $this->message;
        }

        $sms .= " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $this->sms_message;
    }

    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/splosh";

        $html = "<b>Critical Hit</b>";
        $html .= "<p><b>Splosh Variables</b>";
        //$html .= '<br>state ' . $this->state . '';

        $html .= "<br>Rolled a " . $this->roll->roll;

        $html .= 'Critical Hit says, "';
        $html .= $this->sms_message . '"';

        $this->web_message = $html;
        $this->thing_report["web"] = $this->web_message;
    }
}
