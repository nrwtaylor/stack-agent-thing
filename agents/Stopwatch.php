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
        $this->node_list = ["stopwatch" => ["stopwatch", "nonsense"]];

        $this->number = null;
        $this->unit = "";

        $this->default_state = "easy";
        $this->default_mode = "relay";

        $this->setMode($this->default_mode);

        $this->thingStopwatch();

        if (!isset($this->stopwatch_thing)) {
            $this->stopwatch_thing = $this->thing;
        }
        $this->stopwatch_thing->choice->Choose("running");

        $this->stopwatch_thing->choice->Choose("stopped");

        // Get the remaining persistence of the message.
        $agent = new Persistence(
            $this->stopwatch_thing,
            "persistence 60 minutes"
        );
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

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
                $this->state = "easy";
            }

            $state = $this->state;
        }
        if ($state == "easy" or $state == "hard") {
            return true;
        }

        return false;
    }

    function set($requested_state = null)
    {
        $this->stopwatch_thing->json->writeVariable(
            ["stopwatch", "inject"],
            $this->inject
        );

        $this->refreshed_at = $this->current_time;

        $this->stopwatch->setVariable("state", $this->state);
        $this->stopwatch->setVariable("mode", $this->mode);

        $this->stopwatch->setVariable("refreshed_at", $this->current_time);

        if (isset($this->prior_thing)) {
            $this->prior_thing->json->writeVariable(
                ["stopwatch", "response_time"],
                $this->response_time
            );
        }
    }

    function get()
    {
        $this->previous_state = $this->stopwatch->getVariable("state");
        $this->previous_mode = $this->stopwatch->getVariable("mode");
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

        if ($this->previous_mode == false) {
            $this->previous_mode = $this->default_mode;
        }

        $this->mode = $this->previous_mode;

        $this->stopwatch_thing->json->setField("variables");
        $time_string = $this->stopwatch_thing->json->readVariable([
            "stopwatch",
            "refreshed_at",
        ]);
        if ($time_string == false) {
            $this->stopwatch_thing->json->setField("variables");
            $time_string = $this->stopwatch_thing->json->time();
            $this->stopwatch_thing->json->writeVariable(
                ["stopwatch", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->inject = $this->stopwatch_thing->json->readVariable([
            "stopwatch",
            "inject",
        ]);

        $this->last_response_time = $this->stopwatch_thing->json->readVariable([
            "stopwatch",
            "response_time",
        ]);

        $this->microtime_agent = new Microtime(
            $this->stopwatch_thing,
            "microtime"
        );
        $this->timestamp = $this->microtime_agent->timestamp;
        $this->getLink();
        $stopwatch = $this->priorStopwatch();
        $microtime_agent = new Microtime($this->prior_thing, "microtime");
        $this->last_timestamp = $microtime_agent->timestamp;
    }

    function thingStopwatch()
    {
        // Read the elapsed time ie 'look at stopwatch'.

        // See if a stopwatch record exists.

        $things = $this->getThings("stopwatch");

        if ($things === null) {
            return;
        }

        foreach (
            // array_reverse($findagent_thing->thing_report['things'])
            array_reverse($things)
            as $uuid => $thing
        ) {
            //            $uuid = $thing['uuid'];
            //     $variables_json = $thing['variables'];
            //   $variables = $this->thing->json->jsontoArray($variables_json);

            if (!isset($variables["stopwatch"])) {
                continue;
            }
            if (!isset($variables["stopwatch"]["elapsed"])) {
                continue;
            }

            $thing->refreshed_at = $variables["stopwatch"]["refreshed_at"];
            $thing->elapsed_time = $variables["stopwatch"]["elapsed"];

            if (
                $thing->refreshed_at == false or
                $thing->elapsed_time == false
            ) {
                continue;
            } else {
                break;
            }
        }

        if (!isset($thing->refreshed_at) or !isset($thing->elapsed_time)) {
            // Nothing found.

            // Make a stopwatch. Thing.

            $this->stopwatch_thing = $this->thing;

            $this->thing->json->writeVariable(
                ["stopwatch", "refreshed_at"],
                $this->current_time
            );
            $this->elapsed_time = 0;
            $this->refreshed_at = $this->current_time;
            $this->state = "stop";
            $this->previous_state = "start";
        } else {
            $this->stopwatch_thing = $thing;

            $this->stopwatch_thing->json->setField("variables");
            $this->elapsed_time = $this->stopwatch_thing->json->readVariable([
                "stopwatch",
                "elapsed",
            ]);

            $this->refreshed_at = $this->stopwatch_thing->json->readVariable([
                "stopwatch",
                "refreshed_at",
            ]);

            // devstack here

            $this->previous_state =
                $this->stopwatch_thing->wchoice->current_node;

            $this->state = $this->previous_state;

            //$this->state = $thing->flagGet();
            //$this->previous_state = $this->state;
        }
    }

    function setState($state)
    {
        $this->state = "easy";
    }

    public function priorStopwatch()
    {
        $things = $this->getThings("stopwatch");

        if ($things === null) {
            $this->prior_thing = new Thing(null);
            return;
        }

        foreach (array_reverse($things) as $uuid => $thing) {
            if ($uuid == $this->uuid) {
                continue;
            }
            $this->prior_thing = new Thing($uuid);
            //$this->response .= "Got prior thing. ";
            break;
        }
    }

    function getState()
    {
        if (!isset($this->state)) {
            $this->state = "easy";
        }
        return $this->state;
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

        $sms .= trim($this->short_message) . "\n";

        $this->sms_message = $sms;
        $this->thing_report["txt"] = $sms;
    }

    public function run()
    {
        $this->calcStopwatch();
    }

    public function calcStopwatch()
    {
        if (
            $this->microtime_agent->epochtimeMicrotime($this->timestamp) <
            $this->microtime_agent->epochtimeMicrotime($this->last_timestamp)
        ) {
            $this->response_time = "X";
            return;
        }

        $age =
            $this->microtime_agent->epochtimeMicrotime($this->timestamp) -
            $this->microtime_agent->epochtimeMicrotime($this->last_timestamp);
        $this->response_time = $age;
    }

    public function statisticsStopwatch()
    {
        return;
        $statistics_agent = new Statistics(
            $this->stopwatch_thing,
            "statistics stopwatch response_time"
        );
        //$this->response .= $statistics_agent->response;

        $this->statistics_text = "";
        if (
            isset($statistics_agent->minimum) and
            isset($statistics_agent->mean) and
            isset($statistics_agent->maximum) and
            isset($statistics_agent->count) and
            isset($statistics_agent->number)
        ) {
            $this->statistics_text =
                $statistics_agent->number .
                "s" .
                " " .
                "[" .
                $statistics_agent->minimum .
                " (" .
                $statistics_agent->mean .
                ") " .
                $statistics_agent->maximum .
                "] " .
                "N=" .
                $statistics_agent->count;
        }
    }

    function makeSMS()
    {
        $sms = "STOPWATCH " . "\n";
        if (is_numeric($this->response_time)) {
            $sms .= $this->response_time . "s\n";
        }

        if (isset($this->statistics_text)) {
            $sms .= $this->statistics_text . "\n";
        }

        $short_message_text = "No message available.";
        if (isset($this->short_message)) {
            $short_message_text = $this->short_message;
        }

        $sms .= trim($short_message_text) . "\n";

        if (is_string($this->response) and $this->response != "") {
            $sms .= $this->response . "\n";
        }

        $sms .= "TEXT WEB";
        // $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeMessage()
    {
        $short_message_text = "No message available.";
        if (isset($this->short_message)) {
            $short_message_text = $this->short_message;
        }

        $message = $short_message_text . "<br>";
        $uuid = $this->uuid;
        $message .=
            "<p>" . $this->web_prefix . "thing/$uuid/stopwatch\n \n\n<br> ";
        $this->thing_report["message"] = $message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/stopwatch";

        //        if (!isset($this->html_image)) {
        //            $this->makePNG();
        //        }

        $web = "<b>Stopwatch Agent</b>";
        $web .= "<p>";

        //            $web = '<a href="' . $link . '">';
        $web .= $this->image_embedded;
        //            $web .= "</a>";

        if (isset($this->text)) {
            $web .= "" . $this->text;
        }

        $web .= "<p>";

        if (isset($this->response_time) and $this->response_time != false) {
            $web .= "Response time is ";
            $web .= "" . $this->response_time . " seconds";
            $web .= "<br>";
        }

        if (
            isset($this->last_response_time) and
            $this->last_response_time != false
        ) {
            $web .= "Last response time is ";
            $web .= "" . $this->last_response_time;
            $web .= "<br>";
        }

        $web .= "<p>";

        $web .= "Message Metadata - ";
        //        $web .= "<p>";

        $created_at_text = "X";
        if (isset($this->stopwatch_thing->thing->created_at)) {
            $created_at_text = $this->stopwatch_thing->thing->created_at;
        }

        $web .=
            $this->inject .
            " - " .
            $this->stopwatch_thing->nuuid .
            " - " .
            $created_at_text;

        $togo = $this->stopwatch_thing->human_time($this->time_remaining);
        $web .= " - " . $togo . " remaining.<br>";

        $web .= "<br>";

        $link = $this->web_prefix . "privacy";
        $privacy_link = '<a href="' . $link . '">' . $link . "</a>";

        $ago = $this->stopwatch_thing->human_time(
            time() - strtotime($this->stopwatch_thing->thing->created_at)
        );
        $web .= "Stopwatch question was created about " . $ago . " ago. ";

        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    public function readSubject()
    {
        var_dump($this->stopwatch_thing->choice->current_node);
        $keywords = ["stop", "start", "lap", "reset"];

        $input = strtolower($this->subject);

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        //      $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == "stopwatch") {
                $this->readStopwatch();
                return;
            }

            // return "Request not understood";
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "start":
                            $this->start();
                            break;
                        case "stop":
                            $this->stop();
                            break;
                        case "reset":
                            $this->reset();
                            break;
                        case "split":
                            $this->split();
                            break;

                        default:
                        //$this->read();                                                    //echo 'default';
                    }
                }
            }
        }

        // If all else fails try the discriminator.

        $input_agent = new Input($this->stopwatch_thing, "input");
        //$input_agent->discriminateInput($discriminators);

        $discriminators = ["start", "stop", "reset", "lap"];
        $input_agent->aliases["start"] = [
            "start",
            "sttr",
            "stat",
            "st",
            "strt",
        ];
        $input_agent->aliases["stop"] = ["stop", "stp"];
        $input_agent->aliases["reset"] = ["rst", "reset", "rest"];
        $input_agent->aliases["lap"] = ["lap", "laps", "lp"];

        $this->requested_state = $input_agent->discriminateInput(
            $haystack,
            $discriminators
        );

        switch ($this->requested_state) {
            case "start":
                $this->start();
                break;
            case "stop":
                $this->stop();
                break;
            case "reset":
                $this->reset();
                break;
            case "split":
                $this->split();
                break;
        }

        $this->readStopwatch();

        return "Message not understood";

        return false;
    }

    function setMode($mode = null)
    {
        if ($mode == null) {
            return;
        }
        $this->mode = $mode;
    }

    function getMode()
    {
        if (!isset($this->mode)) {
            $this->mode = $this->default_mode;
        }
        return $this->mode;
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
                if (isset($variables["stopwatch"]["response_time"])) {
                    $response_time = $variables["stopwatch"]["response_time"];
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
                "response_time" => $response_time,
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

    function start()
    {
        $this->stopwatch_thing->log("start");

        //$this->get();

        switch ($this->stopwatch_thing->choice->current_node) {
            case "running":
                $t =
                    strtotime($this->current_time) -
                    strtotime($this->refreshed_at);

                $this->elapsed_time = $t + strtotime($this->elapsed_time);
                $this->set();
                $this->stopwatch_thing->elapsed_time = $this->elapsed_time;
                $this->response .= "Saw it already running. ";
                return;
            case false:
            case "stopped":
                $this->stopwatch_thing->choice->Choose("running");

                $this->stopwatch_thing->flagSet("red");

                //$this->state = 'running';
                $this->set();

                $this->response .= "Started the clock. ";

                return;
        }

        //        throw 'not running and stopped.';
    }

    function split()
    {
    }

    function stop()
    {
        $this->stopwatch_thing->log("stop");

        $this->get();

        if ($this->stopwatch_thing->choice->current_node == "stopped") {
            // Do nothing.
            $this->response .= "Clock is stopped. ";
        }

        if ($this->stopwatch_thing->choice->current_node == "running") {
            $this->stopwatch_thing->choice->Choose("stopped");

            $t =
                strtotime($this->current_time) - strtotime($this->refreshed_at);

            $this->elapsed_time = $t + strtotime($this->elapsed_time);
            $this->stopwatch_thing->elapsed_time = $this->elapsed_time;

            $this->response .= "Stopped the clock. ";
        }

        $this->stopwatch_thing->flagSet("green");
        $this->set();

        //                $this->elapsed_time = time() - strtotime($time_string>
        return $this->elapsed_time;
    }

    function reset()
    {
        $this->stopwatch_thing->log("reset");

        $this->get();
        // Set elapsed time as 0 and state as stopped.
        $this->elapsed_time = 0;
        $this->stopwatch_thing->choice->Create(
            "stopwatch",
            $this->node_list,
            "stop"
        );

        $this->stopwatch_thing->choice->Choose("stop");

        $this->set();

        return $this->elapsed_time;
    }

    function readStopwatch($variable = null)
    {
        if (!isset($this->response_time)) {
            $this->response .= "No response time available. ";
            return;
        }

        $this->response .= "Looked at stopwatch. " . $this->response_time;

        return;
        $this->stopwatch_thing->log("read");

        $this->get();
        return $this->elapsed_time;
    }
}
