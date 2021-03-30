<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Info extends Agent
{
    public function init()
    {
        if ($this->thing != true) {
            $this->thing->log(
                'Agent "Info" ran on a null Thing ' . $thing->uuid . ""
            );
            $this->thing_report["info"] = "Tried to run Info on a null Thing.";
            $this->thing_report["help"] = "That isn't going to work";

            return $this->thing_report;
        }

        // So I could call
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = [
            "info" => ["privacy", "whatis"],
            "start a" => ["useful", "useful?"],
            "start b" => ["helpful", "helpful?"],
        ];

        $this->getLink();

        $this->thing_report["etime"] = number_format(
            $this->thing->elapsed_runtime()
        );
        $this->thing_report["log"] = $this->thing->log;

        $this->thing_report["info"] = "meep";
    }

    public function getInfo()
    {
        if (strtolower($this->prior_agent) == "info") {
            $this->info =
                "This provides information from the agents called by this agent.";
            $this->help = "Asks the Agent for information.";
            $this->thing_report["info"] = "Asks the Agent for info.";
            return;
        }

        try {
            $this->thing->log(
                $this->agent_prefix .
                    'trying Agent "' .
                    $this->prior_agent .
                    '".',
                "INFORMATION"
            );
            $agent_class_name = ucwords(strtolower($this->prior_agent));
            $agent_namespace_name =
                "\\Nrwtaylor\\StackAgentThing\\" . $agent_class_name;

            $agent = new $agent_namespace_name($this->prior_thing);
            $this->info = $agent->thing_report["info"];
            //$thing_report = $agent->thing_report;
        } catch (\Error $ex) {
            // Error is the base class for all internal PHP error exceptions.
            $this->thing->log(
                $this->agent_prefix . 'borked on "' . $agent_class_name . '".',
                "WARNING"
            );
            $message = $ex->getMessage();
            //$code = $ex->getCode();
            $file = $ex->getFile();
            $line = $ex->getLine();

            $input = $message . "  " . $file . " line:" . $line;

            // This is an error in the Place, so Bork and move onto the next context.
            $agent = new Bork($this->thing, $input);
            $this->info = $input;
        }

        $this->thing_report["info"] = $this->info;
    }

    public function makeSMS()
    {
        if (!isset($this->info)) {
            $this->getInfo();
        }

        $this->sms_message =
            "INFO | " . ucwords($this->prior_agent) . " | " . $this->info;
        $this->sms_message .= " | TEXT WHATIS";
        $this->thing_report["sms"] = $this->sms_message;
    }

    public function respondRespose()
    {

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["info", "received_at"],
            gmdate("Y-m-d\TH:i:s\Z", time())
        );

        $this->makeChoices();

        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is the info agent.";
        $this->thing_report["help"] =
            "This agent takes a Thing and runs the Info agent on it.";

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

    }

    function getLink($ref = null)
    {
        $block_things = [];
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, "thing");
        $things = $findagent_thing->thing_report["things"];
        if ($things == true) {
            $this->prior_agent = "info";
            return true;
        }
        $this->max_index = 0;

        $match = 0;

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
        $this->prior_thing = $previous_thing;
        if (!isset($previous_thing->json->array_data["message"]["agent"])) {
            $this->prior_agent = "info";
        } else {
            $this->prior_agent =
                $previous_thing->json->array_data["message"]["agent"];
        }

        return $this->link_uuid;
    }

    public function readSubject()
    {
        $this->getInfo();
        $status = true;
        return $status;
    }

    function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "info"
        );
        $choices = $this->thing->choice->makeLinks("info");

        $this->thing_report["choices"] = $choices;
    }

    function makeWeb()
    {
        $this->node_list = ["web" => ["iching", "roll"]];
        $web = "";

        $link = $this->web_prefix . "web/" . $this->uuid . "/thing";

        if (isset($this->link_uuid)) {
            $link = $this->link_uuid;
        }

        $web .= '<a href="' . $link . '">';
        $web .=
            '<img src= "' .
            $this->web_prefix .
            "thing/" .
            $link .
            '/receipt.png">';
        $web .= "</a>";

        $web .= "<br>";

        $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br>";
        $web .=
            "The last agent to run was the " .
            ucwords($this->prior_agent) .
            " Agent.<br>";

        $web .= "<br>";

        $web .= $this->info . "<br>";

        $this->thing_report["web"] = $web;
    }
}
