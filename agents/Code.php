<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Code extends Agent
{
    function init()
    {
        if ($this->thing->thing != true) {
            $this->thing->log(
                'Agent "Code" ran on a null Thing ' . $this->uuid . "."
            );
            $this->thing_report["info"] = "Tried to run Code on a null Thing.";
            $this->thing_report["help"] = "That isn't going to work";

            return $this->thing_report;
        }

        // So I could call
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }

        //        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        $this->node_list = [
            "code" => ["privacy"],
            "start a" => ["useful", "useful?"],
            "start b" => ["helpful", "helpful?"],
        ];

        // If readSubject is true then it has been responded to.
        $this->getLink();
    }
    public function get()
    {
    }

    public function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["code", "received_at"],
            gmdate("Y-m-d\TH:i:s\Z", time())
        );
    }

    public function respondResponse()
    {
        $this->makeChoices();

        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is the code agent.";
        $this->thing_report["help"] =
            "This agent takes an UUID and shows the code of the agent run on it.";

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    function makeSMS()
    {
        $this->getLink();
        $this->sms_message = "CODE | No php found.";
        if (strtolower($this->prior_agent) == "php") {
            $this->sms_message = "CODE | No php available.";
        } else {
            $this->sms_message =
                "CODE | " .
                $this->web_prefix .
                "" .
                $this->link_uuid .
                "/" .
                strtolower($this->prior_agent) .
                ".php";
        }

        $this->sms_message .= " | TEXT INFO";
        $this->thing_report["sms"] = $this->sms_message;
    }

    function getLink($ref = null)
    {
        $block_things = [];
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, "thing");

        $this->max_index = 0;

        $match = 0;
        $things = $findagent_thing->thing_report["things"];
        $this->prior_agent = null;
        if (is_array($things)) {
            foreach ($things as $block_thing) {
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

            $previous_thing = new Thing($block_thing["uuid"]);

            if (!isset($previous_thing->json->array_data["message"]["agent"])) {
                $this->prior_agent = "php";
            } else {
                $this->prior_agent =
                    $previous_thing->json->array_data["message"]["agent"];
            }
        }
        $this->link_uuid = null;
        return $this->link_uuid;
    }

    public function readSubject()
    {
    }

    function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "php"
        );
        $choices = $this->thing->choice->makeLinks("php");

        $this->thing_report["choices"] = $choices;
    }

    public function makePDF()
    {
        $this->thing->report["pdf"] = false;
        return;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "web/" . $this->uuid . "/thing";

        $this->node_list = ["web" => ["iching", "roll"]];

        $web = '<a href="' . $link . '">';
        $web .=
            '<img src= "' .
            $this->web_prefix .
            "thing/" .
            $this->link_uuid .
            '/receipt.png">';
        $web .= "</a>";
        //$web .= "<br>";
        //$web .= '<img src= "https://stackr.ca/thing/' . $this->link_uuid . '/flag.png">';

        $web .= "<br>";
        $web .= "<b>" . ucwords($this->prior_agent) . " Agent</b><br>";

        //$web .= 'The last agent to run was the ' . ucwords($this->prior_agent) . ' Agent.<br>';

        $web .= 'This Thing said it heard, "' . $this->subject . '".<br>';
        $web .= $this->sms_message . "<br>";
        //$web .= 'About '. $this->thing->created_at;

        $received_at = strtotime($this->thing->created_at);
        $ago = $this->thing->human_time(time() - $received_at);
        $web .= "About " . $ago . " ago.";

        $web .= "<br>";
        $this->thing_report["web"] = $web;
    }
}
