<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Stopwatch extends Agent
{
    // devstack

    public $var = "hello";

    public function init()
    {
        $this->node_list = ["stopwatch" => ["stopwatch"]];

        $this->keywords = [
            "stopwatch",
            "timer",
            "stop",
            "start",
            "split",
            "lap",
            "reset",
        ];

        $this->default_state = "stopped"; // stopped, running, split
        $this->default_reading = 0;

        $this->thingStopwatch();
        if (!isset($this->stopwatch_thing)) {
            $this->stopwatch_thing = $this->thing;
        }
        $this->stopwatch_thing->choice->Choose("running");
        $this->stopwatch_thing->choice->Choose("stopped");

        $this->stopwatch = new Variables(
            $this->stopwatch_thing,
            "variables stopwatch " . $this->from
        );
        $this->event_horizon = 60 * 60 * 24;
        $this->y_max_limit = null;
        $this->y_min_limit = null;
    }

    function isStopwatch($state = null)
    {
        if ($state == null) {
            if (!isset($this->state)) {
                $this->state = "stopped";
            }

            $state = $this->state;
        }
        if ($state == "stopped" or $state == "running" or $state == "split") {
            return true;
        }

        return false;
    }

    function set($requested_state = null)
    {
        $this->refreshed_at = $this->current_time;

        $this->stopwatch->setVariable("state", $this->state);
        $this->stopwatch->setVariable("reading", $this->reading);

        if (isset($this->reading_split)) {
            $this->stopwatch->setVariable(
                "reading_split",
                $this->reading_split
            );
        }

        if (isset($this->microtime)) {
            $this->stopwatch->setVariable("microtime", $this->microtime);
        }

        if (isset($this->microtime_split)) {
            $this->stopwatch->setVariable(
                "microtime_split",
                $this->microtime_split
            );
        }

        $this->stopwatch->setVariable("refreshed_at", $this->current_time);
    }

    function get()
    {
        $this->previous_state = $this->stopwatch->getVariable("state");

        $this->previous_reading = $this->stopwatch->getVariable("reading");
        $this->previous_reading_split = $this->stopwatch->getVariable(
            "reading_split"
        );

        $this->previous_microtime = $this->stopwatch->getVariable("microtime");
        $this->previous_microtime_split = $this->stopwatch->getVariable(
            "microtime_split"
        );

        $this->refreshed_at = $this->stopwatch->getVariable("refreshed_at");

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if ($this->isStopwatch($this->previous_state)) {
            $this->state = $this->previous_state;
        } else {
            $this->state = $this->default_state;
        }

        if ($this->state == false) {
            $this->state = $this->default_state;
        }

        if ($this->previous_reading == false) {
            $this->previous_reading = $this->default_reading;
        }

        if ($this->previous_reading_split == false) {
            $this->previous_reading_split = $this->default_reading;
        }

        $this->updateStopwatch();
    }

    function thingStopwatch()
    {
        // Read the elapsed time ie 'look at stopwatch'.

        // See if a stopwatch record exists.

        $things = $this->getThings("stopwatch");

        if ($things === null) {
            return;
        }

        foreach (array_reverse($things) as $uuid => $thing) {
            //            $uuid = $thing['uuid'];
               $variables_json = $thing['variables'];
               $variables = $this->thing->json->jsontoArray($variables_json);

            if (!isset($variables["stopwatch"])) {
                continue;
            }
            if (!isset($variables["stopwatch"]["reading"])) {
                continue;
            }

            $thing->refreshed_at = $variables["stopwatch"]["refreshed_at"];

            if ($thing->refreshed_at == false) {
                continue;
            } else {
                break;
            }
        }

        if (!isset($thing->refreshed_at) or !isset($thing->elapsed_time)) {
            $this->stopwatch_thing = $this->thing;
        } else {
            $this->stopwatch_thing = $thing;
        }
    }

    public function respondResponse()
    {
        $this->makeChoices();
        $this->stopwatch_thing->flagGreen();

        $this->thing_report["info"] = "This creates a question.";
        $this->thing_report["help"] = "Try STOPWATCH.";

        $message_thing = new Message(
            $this->stopwatch_thing,
            $this->thing_report
        );
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeChoices()
    {
        $this->stopwatch_thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "stopwatch"
        );
        $this->choices = $this->stopwatch_thing->choice->makeLinks("stopwatch");

        $this->thing_report["choices"] = $this->choices;
    }

    function makeTXT()
    {
        $sms = "STOPWATCH " . "\n";

        $sms .= trim($this->message) . "\n";

        $this->sms_message = $sms;
        $this->thing_report["txt"] = $sms;
    }

    public function run()
    {
    }

    public function updateStopwatch()
    {
        $elapsed_time = 0;
        if ($this->state === "running") {
            $this->current_microtime = microtime(true);
        }

        if (
            $this->previous_state === "split" or
            $this->previous_state === "running"
        ) {
            $elapsed_time =
                $this->current_microtime - $this->previous_microtime;
        }

        if (
            $this->previous_state === "split" or
            $this->previous_state === "running"
        ) {
            $elapsed_time_split =
                $this->current_microtime - $this->previous_microtime_split;
        }

        if ($this->previous_state === "stopped") {
            $this->previous_reading = 0;
        }

        $this->reading = $elapsed_time;

        if (isset($elapsed_time_split)) {
            $this->reading_split = $elapsed_time_split;
        }
    }

    function makeSMS()
    {
        $sms = "STOPWATCH " . "";

        $sms .= trim($this->message) . "";
        $sms .= " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeMessage()
    {
        $message = "";
        if (isset($this->state)) {
            $message .= "" . strtoupper($this->state);
        }

        if (isset($this->reading)) {
            $message .= " TIMER " . $this->humanRuntime($this->reading);
        }

        if (isset($this->reading_split)) {
            $message .= " SPLIT " . $this->humanRuntime($this->reading_split);
        }

        $this->message = $message;
        $this->thing_report["message"] = $message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/stopwatch";

        $web = "<b>Stopwatch Agent</b>";
        $web .= "<p>";

        //$web .= $this->image_embedded;

        if (isset($this->reading) and $this->reading != false) {
            $web .= "Reading is ";
            $web .= "" . $this->reading;
            $web .= "<br>";
        }

        if (isset($this->reading_split) and $this->reading_split != false) {
            $web .= "Split Reading is ";
            $web .= "" . $this->reading_split;
            $web .= "<br>";
        }

        $this->thing_report["web"] = $web;
    }

    public function readSubject()
    {
        $input = $this->input;

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "stopwatch") {
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "start":
                            $this->startStopwatch();
                            break;
                        case "stop":
                            $this->stopStopwatch();
                            break;
                        case "reset":
                            $this->resetStopwatch();
                            break;
                        case "split":
                            $this->splitStopwatch();
                            break;

                        default:
                    }
                }
            }
        }
    }

    public function makeImage()
    {
        $this->image = $this->chart_agent->image;
    }

    function historyStopwatch()
    {
        // See if a stack record exists.
        //$findagent_thing = new Findagent($this->thing, 'number '. $this->horizon);
        $things = $this->getThings("stopwatch");

        $this->stopwatches_history = [];

        if ($things === true) {
            return;
        }
        if ($things === null) {
            return;
        }

        foreach ($things as $uuid => $thing) {
            //     $variables_json= $thing_object['variables'];
            //     $variables = $this->thing->json->jsontoArray($variables_json);
            $variables = $thing->variables;
            if (isset($variables["stopwatch"])) {
                $response_time = "X";
                $refreshed_at = "X";

                if (isset($variables["stopwatch"]["refreshed_at"])) {
                    $refreshed_at = $variables["stopwatch"]["refreshed_at"];
                }
                if (isset($variables["stopwatch"]["reading"])) {
                    $reading = $variables["stopwatch"]["response_time"];
                }
            }

            $age = strtotime($this->current_time) - strtotime($refreshed_at);
            if ($age > $this->event_horizon) {
                continue;
            }

            if (!is_numeric($response_time)) {
                continue;
            }

            $this->stopwatches_history[] = [
                "timestamp" => $refreshed_at,
                "reading" => $reading,
            ];
        }

        $refreshed_at = [];
        foreach ($this->stopwatches_history as $key => $row) {
            $refreshed_at[$key] = $row["timestamp"];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->stopwatches_history);
    }

    public function makeChart()
    {
        if (!isset($this->stopwatches_history)) {
            $this->historyStopwatch();
        }
        $t = "NUMBER CHART\n";
        $points = [];

        // Defaults needed.
        $x_min = 1e99;
        $x_max = -1e99;

        $y_min = 1e99;
        $y_max = -1e99;

        foreach ($this->stopwatches_history as $i => $number_object) {
            $created_at = strtotime($number_object["timestamp"]);
            $number = $number_object["response_time"];

            $points[$created_at] = $number;

            if (!isset($x_min)) {
                $x_min = $created_at;
            }
            if (!isset($x_max)) {
                $x_max = $created_at;
            }

            if ($created_at < $x_min) {
                $x_min = $created_at;
            }
            if ($created_at > $x_max) {
                $x_max = $created_at;
            }

            if (!isset($y_min)) {
                $y_min = $number;
            }
            if (!isset($y_max)) {
                $y_max = $number;
            }

            if ($number < $y_min) {
                $y_min = $number;
            }
            if ($number > $y_max) {
                $y_max = $number;
            }
        }

        $this->chart_agent = new Chart(
            $this->stopwatch_thing,
            "chart number " . $this->from
        );
        $this->chart_agent->points = $points;

        $this->chart_agent->x_min = $x_min;
        $this->chart_agent->x_max = $x_max;
        $this->chart_agent->x_max = strtotime($this->stopwatch_thing->time);

        if ($this->y_min_limit != false or $this->y_min_limit != null) {
            $y_min = $this->y_min_limit;
        }

        $this->chart_agent->y_min = $y_min;

        if ($this->y_max_limit != false or $this->y_max_limit != null) {
            $y_max = $this->y_max_limit;
        }
        $this->chart_agent->y_max = $y_max;

        $y_spread = 100;
        if (
            $this->chart_agent->y_min == false and
            $this->chart_agent->y_max === false
        ) {
            //
        } elseif (
            $this->chart_agent->y_min == false and
            is_numeric($this->chart_agent->y_max)
        ) {
            $y_spread = $y_max;
        } elseif (
            $this->chart_agent->y_max == false and
            is_numeric($this->chart_agent->y_min)
        ) {
            // test stack
            $y_spread = abs($this->chart_agent->y_min);
        } else {
            $y_spread = $this->chart_agent->y_max - $this->chart_agent->y_min;
            //            if ($y_spread == 0) {$y_spread = 100;}
        }
        if ($y_spread == 0) {
            $y_spread = 100;
        }

        $this->chart_agent->y_spread = $y_spread;
        $this->chart_agent->drawGraph();
    }

    public function makePNG()
    {
        if (!isset($this->image)) {
            return true;
        }
        $this->chart_agent->makePNG();
        $this->image_embedded = $this->chart_agent->image_embedded;
        $this->thing_report["png"] = $this->chart_agent->thing_report["png"];
    }

    function startStopwatch()
    {
        switch ($this->previous_state) {
            case "running":
                break;
            case "stopped":
                $this->previous_state = $this->state;
                $this->state = "running";
                $this->updateStopwatch();
                break;
        }

        $this->response .= "Started stopwatch. ";

        $this->microtime = microtime(true);
        $this->microtime_split = microtime(true);
    }

    function splitStopwatch()
    {
        $this->microtime_split = microtime(true);
        $this->response .= "Set split. ";
    }

    function stopStopwatch()
    {
        switch ($this->previous_state) {
            case "running":
                $this->previous_state = $this->state;
                $this->state = "stopped";
                $this->response .= "Stopped stopwatch. ";
                break;
            case "stopped":
                break;
        }
    }

    function resetStopwatch()
    {
        $this->microtime = microtime(true);
        $this->microtime_split = microtime(true);

        $this->state = "running";
        $this->response .= "Reset stopwatch. ";
    }

    function readStopwatch($variable = null)
    {
        if (!isset($this->reading)) {
            $this->response .= "No reading available. ";
            return;
        }
    }
}
