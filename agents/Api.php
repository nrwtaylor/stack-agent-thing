<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Api extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->thing_report = ["thing" => $this->thing->thing];

        $this->node_list = [
            "start a" => ["useful", "useful?"],
            "start b" => ["helpful", "helpful?"],
        ];

        $this->getLink();
    }

    public function respondResponse()
    {
        $web_thing = new Thing(null);
        $web_thing->Create(
            $this->from,
            $this->agent_name,
            "s/ record web view"
        );

        $link = "<not set>";
        if (isset($this->link_uuid)) {
            $link =
                $this->web_prefix . "api/redpanda/thing/" . $this->link_uuid;
        }
        $this->sms_message = "API | " . $link;

        $this->sms_message .= " | TEXT WHATIS";
        $this->thing_report["sms"] = $this->sms_message;

        $this->thing->Write(
            ["api", "received_at"],
            gmdate("Y-m-d\TH:i:s\Z", time())
        );

        $this->thing->flagGreen();

        //$choices = $this->thing->choice->makeLinks('start a');
        // Account for web views.
        // A Credit to Things account
        // And a debit from the Stack account.  Withdrawal.
        //$this->thing->account['thing']->Credit(25);
        //$this->thing->account['stack']->Debit(25);

        //        $choices = $this->thing->choice->makeLinks('start a');

        $choices = false;

        $this->thing_report["choices"] = $choices;
        $this->thing_report["info"] = "This is the api agent.";
        $this->thing_report["help"] =
            "This agent creates a web link to the API.";

        $amount_text = "not available.";
        if (isset($this->thing->account)) {
            $amount_text = $this->thing->account["thing"]->balance["amount"];
        }
        $this->thing->log(
            '<pre> Agent "Web" credited 25 to the Thing account.  Balance is now ' .
                $amount_text .
                "</pre>"
        );

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->makeWeb();

        $this->thing_report["info"] = $message_thing->thing_report["info"];

        return $this->thing_report;
    }
    function getLink($variable = null)
    {
        $block_things = [];
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, "thing");

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        //$this->thing->log('Agent "Block" found ' . count($findagent_thing->thing_report['things']) ." Block Things.");

        $this->max_index = 0;

        $match = 0;

        $things = $findagent_thing->thing_report["things"];
        if ($things == true) {
            return;
        }

        foreach ($findagent_thing->thing_report["things"] as $block_thing) {
            $this->thing->log(
                $block_thing["task"] .
                    " " .
                    $block_thing["nom_to"] .
                    " " .
                    $block_thing["nom_from"]
            );

            if ($block_thing["nom_to"] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing["uuid"];
                if ($match == 2) {
                    break;
                }
            }
        }

        $variables_json = $block_thing["variables"];
        $variables = $this->thing->json->jsontoArray($variables_json);

        if (!isset($variables["message"]["agent"])) {
            $this->prior_agent = "api";
        } else {
            $this->prior_agent = $variables["message"]["agent"];
        }

        return $this->link_uuid;
    }

    public function readSubject()
    {
        $this->defaultButtons();

        $status = true;
        return $status;
    }

    function makeWeb()
    {
        $link = null;
        if (isset($this->link_uuid)) {
            $link =
                $this->web_prefix . "api/redpanda/thing/" . $this->link_uuid;
        }
        $this->node_list = ["api" => ["iching", "roll"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "api"
        );
        $choices = $this->thing->choice->makeLinks("api");
        $web = "";
        $web .= "<br>";

        $web .= $this->subject;
        $web .= "<br>";
        $web .= $this->sms_message;

        $web .= "<br><br>";

        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    function defaultButtons()
    {
        if (rand(1, 6) <= 3) {
            $this->thing->choice->Create("web", $this->node_list, "start a");
        } else {
            $this->thing->choice->Create("web", $this->node_list, "start b");
        }

        $this->thing->flagGreen();
    }
}
