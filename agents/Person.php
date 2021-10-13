<?php
namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Person extends Agent
{
    public $var = "hello";

    public function run()
    {
        $this->startPerson();
    }

    public function init()
    {
        if ($this->agent_input == null) {
            $this->requested_agent = "Person";
        } else {
            $this->requested_agent = $this->agent_input;
        }

        $this->retain_for = 4; // Retain for at least 4 hours.

        $this->num_hits = 0;

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["person" => ["observer", "listener", "watcher"]];

        $this->thing_report["info"] = "Person";
        $this->thing_report["help"] = "An agent to call a person.";
    }

    public function startPerson($type = null)
    {
        $litany = ["Hello.", "OK.", "Good to know.", "Tell me a story."];
        $key = array_rand($litany);
        $value = $litany[$key];

        $this->message = $value;
        $this->sms_message = $value;

        $names = $this->thing->Write(["person", "person"], null);

        //if ($time_string == false) {
        $time_string = $this->thing->time();
        $this->thing->Write(["person", "refreshed_at"], $time_string);

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
            "person"
        );
        $choices = $this->thing->choice->makeLinks("person");
        $this->thing_report["choices"] = $choices;

        $this->sms_message = "PERSON | " . $this->sms_message . "";
        $this->thing_report["sms"] = $this->sms_message;

        $this->thing_report["email"] = $this->message;
        $this->thing_report["message"] = $this->message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function hasPerson($text = null) {
return $this->hasText($text, "person");
       // Does the text indicate this is a person.
       

    }

    public function assertPerson() {
        $this->person = null;
    }

    public function makeWeb()
    {
        $html = "<b>PERSON</b>";

        $html .= "<p><br>" . "This is a developmental tool. Hello Person.";

        $this->thing_report["web"] = $html;
    }

    public function readSubject()
    {
        $this->response .= "Heard a person. ";
    }
}
