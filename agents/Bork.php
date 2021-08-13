<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Bork extends Agent
{
    public $var = "hello";

    public function init()
    {
        if ($this->agent_input == null) {
            $this->requested_agent = "Bork.";
        } else {
            $this->requested_agent = $agent_input;
        }

        $this->num_hits = 0;

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["start" => ["useful", "useful?"]];

        $this->thing_report["info"] = "Bork";
        $this->thing_report["help"] = "BORK";
        $this->thing_report["num_hits"] = $this->num_hits;
    }

    public function get($type = null)
    {
        $this->message = $this->requested_agent;
        $this->sms_message = $this->requested_agent;

        $names = $this->thing->Write(
            ["bork", "requested_agent"],
            $this->requested_agent
        );
        $time_string = $this->thing->time();
        $this->thing->Write(
            ["bork", "refreshed_at"],
            $time_string
        );

        return $this->message;
    }

    public function respondResponse()
    {
        if ($this->agent_input != null) {
            return;
        }

        // Thing actions
        $this->thing->flagGreen();

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "start"
        );
        $choices = $this->thing->choice->makeLinks("start");
        $this->thing_report["choices"] = $choices;

        $this->sms_message = "BORK | " . $this->sms_message . " | REPLY ?";
        $this->thing_report["sms"] = $this->sms_message;

        $this->thing_report["email"] = $this->message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing->log(
            'Agent "Bork" responded "' . $this->sms_message . '".'
        );

    }

    public function readSubject()
    {
    }
}
