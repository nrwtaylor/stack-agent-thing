<?php
namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Listener extends Agent
{
    public $var = "hello";

    public function run()
    {
        $this->startListener();
    }

    public function init()
    {
        if ($this->agent_input == null) {
            $this->requested_agent = "Listener";
        } else {
            $this->requested_agent = $this->agent_input;
        }

        $this->retain_for = 4; // Retain for at least 4 hours.

        $this->num_hits = 0;

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["listener" => ["person", "listener", "watcher"]];

        $this->thing_report["info"] = "Listener";
        $this->thing_report["help"] = "An agent to flag an Listener.";
    }

    public function startListener($type = null)
    {
        $litany = ["Hello.", "Hello Listener.", "OK.", "Good to know.", "Somebody is listening."];
        $key = array_rand($litany);
        $value = $litany[$key];

        $this->message = $value;
        $this->sms_message = $value;

        $names = $this->thing->Write(["listener", "listener"], null);

        //if ($time_string == false) {
        $time_string = $this->thing->time();
        $this->thing->Write(["listener", "refreshed_at"], $time_string);

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
            "listener"
        );
        $choices = $this->thing->choice->makeLinks("listener");
        $this->thing_report["choices"] = $choices;

        $this->sms_message = "LISTENER | " . $this->sms_message . "";
        $this->thing_report["sms"] = $this->sms_message;

        $this->thing_report["email"] = $this->message;
        $this->thing_report["message"] = $this->message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function hasListener($text = null) {
return $this->hasText($text, "listener");
       // Does the text indicate this is an listener.
       

    }

    public function assertListener() {
        if (!isset($this->listener)) {
        $this->listener = null;
        }
        $this->assertPerson();
        $this->assertObserver();

    }

    public function makeWeb()
    {
        $html = "<b>LISTENER</b>";

        $html .= "<p><br>" . "This is a developmental tool. Hello Listener.";

        $this->thing_report["web"] = $html;
    }

    public function readSubject()
    {
        $this->response .= "Heard there might be a listener. ";

        $input = $this->input;

        $this->assertFlag($input);

    }
}
