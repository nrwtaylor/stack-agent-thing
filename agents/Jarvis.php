<?php
/**
 * Jarvis.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Jarvis extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {

        if ($this->agent_input == null) {
            $this->requested_agent = "Jarvis.";
        } else {
            $this->requested_agent = $this->agent_input;
        }

        $this->retain_for = 4; // Retain for at least 4 hours.

        $this->num_hits = 0;
        $this->node_list = ["start" => ["useful", "useful?"]];

        $this->thing_report["info"] = "Hey";
        $this->thing_report["num_hits"] = $this->num_hits;
    }

    function run()
    {
    }

    public function get()
    {
    }

    public function set()
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["jarvis", "requested_agent"],
            $this->requested_agent
        );

        $this->thing->json->setField("variables");
        $time_string = $this->thing->time();
        $this->thing->json->writeVariable(
            ["jarvis", "refreshed_at"],
            $time_string
        );
    }

    /**
     *
     * @param unknown $type (optional)
     * @return unknown
     */
    public function readJarvis($type = null)
    {
        $litany = [
            "Hello Ironman.",
            "Good morning. It's 7 A.M. The weather in Malibu is 72 degrees with scattered clouds. The surf conditions are fair with waist to shoulder highlines, high tide will be at 10:52 a.m.",
            "As always sir, a great pleasure watching you work.",
            "Sir, take a deep breath.",
            "Working on it, sir. This is a prototype.",
            "Oh, hello sir.",
            "Yes, sir.",
            "All wrapped up here, sir. Will there be anything else?",
            'Sir, received "' . $this->subject . '"',
        ];

        $key = array_rand($litany);
        $value = $litany[$key];

        $this->message = $value;
        $this->sms_message = $value;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "start"
        );
        $choices = $this->thing->choice->makeLinks("start");
        $this->thing_report["choices"] = $choices;

        $this->sms_message = "JARVIS | " . $this->sms_message . " | REPLY HELP";
        $this->thing_report["sms"] = $this->sms_message;

        $this->thing_report["email"] = $this->message;
        $this->thing_report["message"] = $this->message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["help"] =
            "This is Just a Rather Very Intelligent System.";
    }

    /**
     *
     * @return unknown
     */
    public function test()
    {
        $this->test = false; // good
        return "green";
    }

    /**
     *
     */
    public function readSubject()
    {
        $this->readJarvis();
    }
}
