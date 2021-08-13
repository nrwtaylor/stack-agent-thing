<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Messageidentifier extends Agent
{
    public $var = "hello";
    function init()
    {
        $this->test = "Development code"; // Always

        $this->identifier = $this->agent_input;

        $this->node_list = ["identity" => ["on" => ["off"]]];

        // This isn't going to help because we don't know if this
        // is the base.
        //        $this->state = "off";
        //        $this->thing->choice->load($this->keyword);

        $this->current_time = $this->thing->time();

        $this->thing_report["sms"] = "MESSAGE IDENTITY | " . $this->identifier;
    }

    function set($requested_state = null)
    {
        $this->thing->Write(
            ["message_identifier", "identifier"],
            $this->identifier
        );
    }

    function get()
    {
    }

    function readMessageidentifier($text = null)
    {
        //        return $this->state;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $to = $this->thing->from;
        $from = $this->keyword;

        $choices = false;
        $this->thing_report["choices"] = $choices;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
        $this->thing_report["help"] =
            "This is your Identity.  You can turn your Identity ON and OFF.";

        //		return;
    }

    public function readSubject()
    {
    }
}
