<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Who extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->verbosity = 9;

        $this->nominal = $this->thing->container["stack"]["nominal"];
        $this->mail_regulatory =
            $this->thing->container["stack"]["mail_regulatory"];

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["who" => ["privacy", "weather"]];
    }

    public function who()
    {
        $mail_regulatory = str_replace("\r", "", $this->mail_regulatory);
        $mail_regulatory = str_replace("\n", " ", $mail_regulatory);

        $this->sms_message =
            "WHO | " .
            ucwords($this->nominal) .
            " | " .
            $this->email .
            " | " .
            ltrim($mail_regulatory);

        $this->message = $this->sms_message;

        return $this->message;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->makeChoice();

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["email"] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["help"] = "Who manager";
    }

    public function makeMessage()
    {
    }

    public function makeSMS()
    {
        if (!isset($this->sms_message)) {
            $this->sms_message = "WHO | Message not understood.";
        }
        $this->thing_report["sms"] = $this->sms_message;
    }

    public function makeChoice()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "who"
        );
        $choices = $this->thing->choice->makeLinks("who");
        $this->thing_report["choices"] = $choices;
    }

    public function readWho($text = null)
    {
        if ($text == null) {
            return;
        }
        return "No Who found.";
    }

    public function readSubject()
    {
        $keywords = ["?"];
//        $input = strtolower($this->subject);
$input = $this->input;

$this->hasPerson($input);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            $input = $this->subject;
            if ($input == "who") {
                $this->response .= "Single word who received";
                $this->thing->log("got a single keyword.");
                $this->who();
                return;
            }

            $this->who();
            $this->response .= "Provided contact details.";
            return;
        }

        $input = $this->assert($this->input, "who", false);
        $this->readWho($input);
    }
}
