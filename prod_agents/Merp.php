<?php
namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Merp extends Agent
{
    public $var = "hello";

    public function run()
    {
        $this->startMerp();
    }

    public function init()
    {
        if ($this->agent_input == null) {
            $this->requested_agent = "Merp";
        } else {
            $this->requested_agent = $this->agent_input;
        }

        $this->retain_for = 4; // Retain for at least 4 hours.

        $this->num_hits = 0;

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["start" => ["useful", "useful?"]];

        $this->thing_report["info"] = "Merp";
        $this->thing_report["help"] =
            "An agent which says, 'Merp'. Type 'Web' on the next line.";
    }

    public function startMerp($type = null)
    {
        $litany = [
            "Meh.",
            "Hhhhhh.",
            "Hi",
            'Received "' . $this->subject . '"',
        ];
        $key = array_rand($litany);
        $value = $litany[$key];

        $this->message = $value;
        $this->sms_message = $value;
        $this->max_nod_time = 30;

        if ($this->nod->time_travelled > $this->max_nod_time) {
            $this->sms_message =
                "Last nod was over " .
                $this->thing->human_time($this->max_nod_time) .
                " ago.";
        }

        $names = $this->thing->Write(
            ["merp", "requested_agent"],
            $this->requested_agent
        );

        //if ($time_string == false) {
        $time_string = $this->thing->time();
        $this->thing->Write(
            ["merp", "refreshed_at"],
            $time_string
        );
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
            "start"
        );
        $choices = $this->thing->choice->makeLinks("start");
        $this->thing_report["choices"] = $choices;

        $this->sms_message = "MERP | " . $this->sms_message . "";
        $this->thing_report["sms"] = $this->sms_message;

        $this->thing_report["email"] = $this->message;
        $this->thing_report["message"] = $this->message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function makeWeb()
    {
        $html = "<b>MERP</b>";

        $html .= "<br>Last nod html " . $this->nod->last_timestamp;

        if (isset($this->nod->last_created_at)) {
            $html .= "<br>Last nod sms " . $this->nod->last_created_at;

            $timestamp = $this->nod->last_timestamp;
            $t = explode(" ", $timestamp);
            $timestamp = $t[0] . " " . $t[1];

            $t1 = strtotime($timestamp);
            $t2 = strtotime($this->nod->last_created_at);

            $html_time = strtotime($this->current_time) - $t1;
            $sms_time = strtotime($this->current_time) - $t2;

            $nearest_time = min($html_time, $sms_time);

            $html .=
                "<br>Last nod was " .
                $this->thing->human_time($nearest_time) .
                " ago.";
        }

        $warranty = new Warranty($this->thing, "warranty");

        $html .=
            "<p><br>" .
            "This is a developmental tool. Sometimes it might not work. If you have resources, we hope you can make it more reliable.";

        $html .= "<p><br>" . $warranty->message;

        $this->thing_report["web"] = $html;
    }

    public function readSubject()
    {
        $this->nod = new Nod($this->thing, "nod");

        $this->response = null;
    }
}
