<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Destination extends Agent
{
    public $var = "hello";

    // Not finished.  Or really started.
    // This will look up a destination by cross street
    // Find the trips servicing it.

    public function init()
    {
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }

        $this->node_list = ["start" => ["useful", "useful?"]];
    }

    public function makeDestination($text)
    {
        $columns = explode(",", $text);

        $arr = [
            "a" => $columns[0],
            "stop_number" => $columns[1],
            "stop_name" => $columns[2],
            "stop_description" => $columns[3],
            "stop_latitude" => $columns[4],
            "stop_longitude" => $columns[5],
            "stop_zone" => $columns[6],
        ];

        return $arr;
    }

    public function getDestinations()
    {
        $this->destination_list = [];

        if (!isset($this->gtfs->places)) {
            return true;
        }
        $places = $this->gtfs->places;

        foreach ($places as $stop_desc => $stops) {
            foreach ($stops as $stop) {
                $this->destination_list[] = [
                    "stop_desc" => $stop["stop_desc"],
                    "stop_code" => $stop["stop_code"],
                ];
            }
        }
    }

    public function getDestination()
    {
        if (!isset($this->destination_list)) {
            $this->getDestinations();
        }

        $this->destination = false;
        if (
            is_array($this->destination_list) and
            count($this->destination_list) == 1
        ) {
            $this->destination = $this->destination_list[0];
        }
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->thing_report["choices"] = false;
        $this->thing_report["info"] = "SMS sent";

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["help"] =
            "Text DESTINATION followed by a recognized place name.";
    }

    public function makeSMS()
    {
        if (!isset($this->destination_list)) {
            $this->getDestinations();
        }

        $message = "DESTINATION";
        $route_list_text = "NOT SET";
        if (isset($this->route_list_text)) {
            $route_list_text = $this->route_list_text;
        }
        $message .= " > " . $route_list_text;

        $this->sms_message = $message;
        $this->thing_report["sms"] = $message;
    }

    public function makeWeb()
    {
        if (!isset($this->destination_list)) {
            $this->getDestinations();
        }

        $message = "DESTINATION<br>";

        foreach ($this->destination_list as $key => $destination) {
            $stop_name = $destination["stop_desc"];
            $stop_number = $destination["stop_code"];

            $message .= $stop_number . " | " . $stop_name . "<br>";
        }
        $this->web_message = $message;
        $this->thing_report["web"] = $message;
    }

    function assertDestination($input)
    {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "destination is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("destination is")
            );
        } elseif (
            ($pos = strpos(strtolower($input), "destination")) !== false
        ) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("destination")
            );
        }

        $this->destination_input = $whatIWant;
    }

    public function readSubject()
    {
        $this->response = null;

        $keywords = ["destination"];

        $filtered_input = $this->assert($this->input, "destination", false);
        if ($filtered_input == "") {
            $this->response .= "No destination provided. ";
            return;
        }
        $this->gtfs = new Gtfs($this->thing, strtolower($filtered_input));

        if (isset($this->gtfs->stations)) {
            foreach ($this->gtfs->stations as $station) {
                $station_id = $station["station_id"];
                $this->gtfs->getRoutes($station_id);
                foreach (
                    $this->gtfs->routes[$station_id]
                    as $route_id => $route
                ) {
                    $route_list[$route["route_short_name"]] = true;
                }
            }

            $route_text = "";
            foreach ($route_list as $route_number => $value) {
                $route_text .= $route_number . " ";
            }

            $this->route_list_text = $route_text;
        }

        $this->response .= "Got routes serving " . $input . ". ";
    }
}
