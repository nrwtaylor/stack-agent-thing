<?php
/**
 * Connection.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Connection extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->test_connections = require $this->resource_path .
            "connection/connections.php";

        $this->keyword = "connection";
        $this->test = "Development code"; // Always
        $this->keywords = ["connection"];

        //         $this->variables_agent = new Variables(
        //             $this->thing,
        //             "variables " . "connection" . " " . $this->from
        //         );

        $this->default_state = "green";

        if ($this->verbosity == false) {
            $this->verbosity = 2;
        }

        $this->thing->refresh_at = $this->thing->time(time() + 5 * 60); // Refresh after 5 minutes.
    }

    public function isConnection($text)
    {
        $response = file_get_contents($text);
        if ($response === false) {
            return false;
        }
        return true;
    }

    public function checkConnections()
    {
        $test_results = [];

        foreach ($this->test_connections as $address => $modifiers) {
            foreach ($modifiers["ports"] as $port) {
                foreach ($modifiers["descriptors"] as $descriptor) {
                    $uuid = $this->thing->getUuid();
                    //echo $uuid." " . $address . " " . $port . " " .$descriptor . "\n";
                    $url =
                        $descriptor .
                        $address .
                        ($port === null ? "" : ":" . $port);
                    $test_results[$uuid] = [
                        "address" => $address,
                        "result" => $this->isConnection($url),
                    ];
                }
            }
        }

        foreach ($test_results as $uuid => $test_result) {
            $this->response .=
                $test_result["address"] .
                " " .
                ($test_result["result"] ? "OK" : "NOT OK") .
                " ";
            $test_results[$uuid]["text"] = $test_result["result"]
                ? "OK"
                : "NOT OK";
        }
        $this->response .= "Tested connections. ";
        $this->test_results = $test_results;
    }

    /**
     *
     */
    function run()
    {
        //$this->up = $this->last_up;
        //$this->test_results = $this->last_test_results;

        //if ($this->last_test_results === false) {
        //        $this->response .= "Did connection. ";
        //        $this->doConnection($this->input);
        //}
    }

    /**
     *
     * @param unknown $text
     */
    function doConnection($text)
    {
        $filtered_text = strtolower($text);

        $ngram_agent = new Ngram($this->thing, $filtered_text);

        foreach ($ngram_agent->ngrams as $index => $ngram) {
            switch ($ngram) {
                case "check":
                    $this->checkConnections();
                    $this->upConnection();
                    $this->response .=
                        "Saw a request about checking connections. ";
                    return;
                default:
                //$this->response .= "No request seen. ";
            }
        }

        // Failed.
        //$this->up = $this->variables_agent->getVariable("up");
        //$this->test_results = $this->variables_agent->getVariable("test_results");
        //$this->refreshed_at = $this->variables_agent->getVariable(
        //    "refreshed_at"
        //);
    }

    function set()
    {
        if (!isset($this->up) or !isset($this->test_results)) {
            return;
        }
        if ($this->last_up === false or $this->last_test_results === false) {
            $this->thing->Write(["connection", "up"], $this->up);
            $this->thing->Write(
                ["connection", "test_results"],
                $this->test_results
            );

            //$this->variables_agent->setVariable("up", $this->up);
            //$this->variables_agent->setVariable("test_results", $this->test_results);
            //$this->variables_agent->setVariable(
            //    "refreshed_at",
            //    $this->refreshed_at
            //);
        }
    }

    public function get()
    {
        //        $this->current_time = $this->thing->json->time();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->Read(["connection", "refreshed_at"]);
        $this->last_refreshed_at = $time_string;

        if ($time_string === false) {
            $time_string = $this->thing->json->time();
            $this->thing->Write(["connection", "refreshed_at"], $time_string);
        }

        $this->refreshed_at = strtotime($time_string);

        $this->last_up = $this->thing->Read(["connection", "up"]);
        $this->last_test_results = $this->thing->Read([
            "connection",
            "test_results",
        ]);
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["help"] = "This checks connections between Things.";
    }

    /**
     *
     */
    public function makeWeb()
    {
        $web = "<b>Connection Agent</b>";
        $web .= "<p>";

        if (isset($this->test_results)) {
            $web .= $this->htmlTable($this->test_results);
            $web .= "<br>";
        }

        if ($this->last_refreshed_at !== false) {
            $ago = $this->thing->human_time(time() - $this->refreshed_at);
            $web .= "Checked about " . $ago . " ago.";
        }

        $this->thing_report["web"] = $web;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms_message = "CONNECTION";

        if (isset($this->up)) {
            $sms_message .= " ";
            $sms_message .= $this->up ? "UP" : "DOWN";
        }
        $sms_message .= " | ";

        $response = "";
        if ($this->response != "") {
            $response = $this->response;
        }

        $sms_message .= trim($response);

        $sms_message .=
            " " . $this->web_prefix . "thing/" . $this->uuid . "/connection";

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    public function upConnection()
    {
        $ok_count = 0;
        $count = 0;
        foreach ($this->test_results as $uuid => $test_result) {
            if ($test_result["result"] === true) {
                $ok_count += 1;
            }
            $count += 1;
        }
        $up = false;
        if ($ok_count === $count) {
            $up = true;
        }
        $this->up = $up;
        return $up;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Connection state not known.";
        if (isset($this->up)) {
            $message = "Connection is " . ($this->up ? "UP" : "DOWN");
        }
        $this->message = $message;
        $this->thing_report["message"] = $message;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function extractNumber($input = null)
    {
        if ($input == null) {
            $input = $this->subject;
        }

        $pieces = explode(" ", strtolower($input));

        // Extract number
        $matches = 0;
        foreach ($pieces as $key => $piece) {
            if (is_numeric($piece)) {
                $number = $piece;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            if (is_integer($number)) {
                $this->number = intval($number);
            } else {
                $this->number = floatval($number);
            }
        } else {
            $this->number = true;
        }
        return $this->number;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        //        if (!isset($this->input) or $this->input == null) {
        //            return;
        //        }
        $input = $this->input;
        $filtered_input = $this->assert($input);

        if ($this->last_up == false or $this->last_test_results == false) {
            $this->doConnection($this->input);
        } else {
            $this->up = $this->last_up;
            $this->test_results = $this->last_test_results;
        }

        // $this->doConnection($input);
    }
}
