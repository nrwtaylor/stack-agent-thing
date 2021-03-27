<?php
/**
 * Log.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Log extends Agent
{
    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     * @return unknown
     */
    function init()
    {
        if ($this->thing != true) {
            $this->thing->log("ran on a null Thing " . $thing->uuid . ".");
            $this->thing_report["info"] = "Tried to run Log on a null Thing.";
            $this->thing_report["help"] = "That isn't going to work";

            return $this->thing_report;
        }

        // So I could call
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }

        $this->state = "X";
        if (isset($this->thing->container["api"]["log"]["state"])) {
            $this->state = $this->thing->container["api"]["log"]["state"];
        }

        $this->node_list = [
            "log" => ["privacy"],
            "code" => ["web", "log"],
            "uuid" => ["snowflake", "optin"],
        ];
    }

    public function run()
    {
        if ($this->state == "on") {
            $this->getLink();
            $web_thing = new Thing(null);
            $web_thing->Create(
                $this->from,
                $this->agent_name,
                "s/ record web view"
            );
        }
    }

    public function set()
    {
        if ($this->state == "on") {
            $this->thing->json->setField("variables");
            $this->thing->json->writeVariable(
                ["log", "received_at"],
                gmdate("Y-m-d\TH:i:s\Z", time())
            );
        }
    }

    function filterLog($log_text, $log_includes = null, $log_excludes = null)
    {
        $response = "";
        $lines = preg_split("/<br[^>]*>/i", $log_text);

        foreach ($lines as $i => $line) {
            if (is_array($log_excludes)) {
                foreach ($log_excludes as $j => $log_exclude) {
                    if (stripos($line, $log_exclude) !== false) {
                        continue 2;
                    }
                }
            }

            if (is_array($log_includes)) {
                if (count($log_includes) == 0) {
                    $response .= trim($line) . "\n";
                    continue;
                }

                foreach ($log_includes as $j => $log_include) {
                    if (stripos($line, $log_include) !== false) {
                        $response .= trim($line) . "\n";
                        continue 2;
                    }
                }
            }
        }
        if ($response === "") {
            return true;
        }
        return $response;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is the log agent.";
        $this->thing_report["help"] =
            "This agent shows the log file, and explains it.";

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

    }

    function makeSnippet()
    {
        $gap_time_max = 0;
        $lines = explode("<br>", $this->thing->log);
        $log = [];
        foreach ($lines as $i => $line) {
            $line = trim($line);
            $tokens = explode(" ", $line);
            $millisecond_text = $tokens[0];
            $milliseconds = intval(
                trim(str_replace(["ms", ","], "", $millisecond_text))
            );

            $gap_time = 0;
            if (isset($last_milliseconds)) {
                $gap_time = $milliseconds - $last_milliseconds;
            }

            if ($gap_time > $gap_time_max) {
                $gap_time_max = $gap_time;
                $log_index_max = $i;
            }
            $t = [
                "number" => $i,
                "line" => $line,
                "elapsed_time" => $milliseconds,
                "gap_time" => $gap_time,
            ];

            $log[] = $t;
            $last_milliseconds = $milliseconds;
        }

        // Find top-10 time gaps
        $gap_time_sorted_log = $log;
        $gap_time = [];
        foreach ($gap_time_sorted_log as $key => $row) {
            $gap_time[$key] = $row["gap_time"];
        }
        array_multisort($gap_time, SORT_DESC, $gap_time_sorted_log);

        $max_entries = 10;
        $count = 0;
        $highlight = [];
        foreach ($gap_time_sorted_log as $i => $log_entry) {
            $count += 1;
            $highlight[] = $log_entry["number"];
            if ($count >= $max_entries) {
                break;
            }
        }

        $snippet = "";
        foreach ($log as $i => $log_entry) {
            $line = $log_entry["line"];
            //if ($i == $log_index_max) { $line = '<b>' . $log_entry['line'] . '</b>';}

            foreach ($highlight as $k => $highlight_index) {
                if ($highlight_index == $i) {
                    $line = "<b>" . $log_entry["line"] . "</b>";
                }
            }

            $snippet .= $line . "<br>";
        }
        $this->thing_report["snippet"] = $snippet;
    }

    /**
     *
     */
    function makeSMS()
    {
        if (!isset($this->link_uuid) or !isset($this->prior_agent)) {
            $link_text = "No log available.";
        } else {
            $link_text =
                $this->web_prefix .
                "" .
                $this->link_uuid .
                "/" .
                strtolower($this->prior_agent) .
                ".log";

            if (strtolower($this->prior_agent) == "php") {
                $link_text = "No log available.";
            }
        }
        $this->sms_message = "LOG | " . $link_text;

        $this->sms_message .= " | TEXT INFO";
        $this->thing_report["sms"] = $this->sms_message;
    }

    /**
     *
     * @return unknown
     */

    function getLink($variable = null)
    {
        $block_things = [];
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, "thing");

        $this->max_index = 0;

        $match = 0;
        $things = $findagent_thing->thing_report["things"];

        if ($things === true) {
            return true;
        }
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

        return $this->link_uuid;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = $this->assert("search", $input);

        $this->defaultButtons();
    }

    /**
     *
     */
    function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "log"
        );
        $choices = $this->thing->choice->makeLinks("log");

        $this->thing_report["choices"] = $choices;
    }

    /**
     *
     */
    public function makePDF()
    {
        $this->thing->report["pdf"] = false;
    }

    /**
     *
     */
    function makeWeb()
    {
        $link = $this->web_prefix . "web/" . $this->uuid . "/thing";

        $this->node_list = ["web" => ["iching", "roll"]];

        $web = "";
        $agent_text = "No";
        if (isset($this->prior_agent)) {
            $agent_text = ucwords($this->prior_agent);
        }
        $web .= "<b>" . $agent_text . " Agent</b>";

        $web .= '<br>This Thing said it heard, "' . $this->subject . '".';

        $web .=
            "<br>This will provide a full log description of what the code did with datagram.";

        $web .= "<br>" . $this->sms_message . "<br>";

        $received_at = strtotime($this->thing->created_at);
        $ago = $this->thing->human_time(time() - $received_at);
        $web .= "About " . $ago . " ago.";

        $web .= "<br>";
        $this->thing_report["web"] = $web;
    }

    /**
     *
     */
    function defaultButtons()
    {
        if (rand(1, 6) <= 3) {
            $this->thing->choice->Create("log", $this->node_list, "log");
        } else {
            $this->thing->choice->Create("log", $this->node_list, "code");
        }

        $this->thing->flagGreen();
    }
}
