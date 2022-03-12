<?php
namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Observer extends Agent
{
    public $var = "hello";

    public function run()
    {
        $this->startObserver();
    }

    public function init()
    {
        if ($this->agent_input == null) {
            $this->requested_agent = "Observer";
        } else {
            $this->requested_agent = $this->agent_input;
        }

        $this->retain_for = 4; // Retain for at least 4 hours.

        $this->num_hits = 0;

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["observer" => ["person", "listener", "watcher"]];

        $this->thing_report["info"] = "Observer";
        $this->thing_report["help"] = "An agent to flag an observer.";
    }

    public function startObserver($type = null)
    {
        $litany = ["Hello.", "OK.", "Good to know.", "Somebody is watching."];
        $key = array_rand($litany);
        $value = $litany[$key];

        $this->message = $value;
        $this->sms_message = $value;

        $names = $this->thing->Write(["observer", "observer"], null);

        //if ($time_string == false) {
        $time_string = $this->thing->time();
        $this->thing->Write(["observer", "refreshed_at"], $time_string);

        //}

        return $this->message;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "observer"
        );
        $choices = $this->thing->choice->makeLinks("observer");
        $this->thing_report["choices"] = $choices;

        $this->sms_message = "OBSERVER | " . $this->sms_message . "";
        $this->thing_report["sms"] = $this->sms_message;

        $this->thing_report["email"] = $this->message;
        $this->thing_report["message"] = $this->message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function hasObserver($text = null) {
return $this->hasText($text, "observer");
       // Does the text indicate this is an observer.
       

    }

    public function assertObserver() {
        if (!isset($this->observer)) {
        $this->observer = null;
        }
        $this->assertPerson();
    }

    public function makeWeb()
    {
        $html = "<b>OBSERVER</b>";

        $html .= "<p><br>" . "This is a developmental tool. Hello Observer.";

        $this->thing_report["web"] = $html;
    }

    public function readSubject()
    {
        $this->response .= "Heard there is an observer. ";

        $input = $this->input;

        $this->assertFlag($input);

    }
}
