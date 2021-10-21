<?php
namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Watcher extends Agent
{
    public $var = "hello";

    public function run()
    {
        $this->startWatcher();
    }

    public function init()
    {
        if ($this->agent_input == null) {
            $this->requested_agent = "Watcher";
        } else {
            $this->requested_agent = $this->agent_input;
        }

        $this->retain_for = 4; // Retain for at least 4 hours.

        $this->num_hits = 0;

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["watcher" => ["person", "watcher", "watcher"]];

        $this->thing_report["info"] = "Watcher";
        $this->thing_report["help"] = "An agent to flag an Watcher.";
    }

    public function startWatcher($type = null)
    {
        $litany = ["Hello.", "Hello Watcher.", "OK.", "Good to know.", "Somebody is listening."];
        $key = array_rand($litany);
        $value = $litany[$key];

        $this->message = $value;
        $this->sms_message = $value;

        $names = $this->thing->Write(["watcher", "watcher"], null);

        //if ($time_string == false) {
        $time_string = $this->thing->time();
        $this->thing->Write(["watcher", "refreshed_at"], $time_string);

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
            "watcher"
        );
        $choices = $this->thing->choice->makeLinks("watcher");
        $this->thing_report["choices"] = $choices;

        $this->sms_message = "WATCHER | " . $this->sms_message . "";
        $this->thing_report["sms"] = $this->sms_message;

        $this->thing_report["email"] = $this->message;
        $this->thing_report["message"] = $this->message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function hasWatcher($text = null) {
return $this->hasText($text, "watcher");
       // Does the text indicate this is an watcher.
       

    }

    public function assertWatcher() {
        if (!isset($this->watcher)) {
        $this->watcher = null;
        }
        $this->assertPerson();
        $this->assertObserver();

    }

    public function makeWeb()
    {
        $html = "<b>WATCHER</b>";

        $html .= "<p><br>" . "This is a developmental tool. Hello Watcher.";

        $this->thing_report["web"] = $html;
    }

    public function readSubject()
    {
        $this->response .= "Heard there is a watcher. ";

        $input = $this->input;

        $this->assertFlag($input);

    }
}
