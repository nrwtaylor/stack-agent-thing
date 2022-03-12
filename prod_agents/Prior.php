<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Prior extends Agent
{
    function init()
    {
    }

    public function set()
    {
        $hash_nom_from = hash($this->stack_hash_algorithm, $this->from);

        $prior_uuid = $this->setMemory($hash_nom_from, $this->uuid);
        $this->response .=
            "Wrote key:value " . $hash_nom_from . " " . $this->uuid . ". ";
    }

    public function get()
    {
        $hash_nom_from = hash($this->stack_hash_algorithm, $this->from);
        $this->prior_uuid = $this->getMemory($hash_nom_from);
        $this->response .= "Retrieved from memory. ";
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["message"] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function readSubject()
    {
        $filtered_input = $this->assert($this->input, "prior");
    }

    function makeSMS()
    {
        $sms = "PRIOR | " . $this->prior_uuid . " " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }
}
