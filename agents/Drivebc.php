<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

/*

Query DriveBC API
Provide text interface for weather station list,
traffic report by keyword
and weather by station name.

*/

class Drivebc extends Agent
{
    // https://api.open511.gov.bc.ca/help
    public $var = "hello";

    function init()
    {
        $this->test = "Development code"; // Always

        $this->keywords = ["drive", "drivebc", "traffic", "weather"];

        $this->thing_report["help"] =
            "This provides Province of British Columbia provincial road network information.";
    }

    function run()
    {
        $this->getEvents();
        //$this->getJurisdictions();
        //$this->getAreas();
        $this->getWeather();
        $this->getRoad("Highway 99");

        $this->doDriveBC();
    }

    function doDriveBC($text = null)
    {
        $this->filtered_events = [];
        foreach ($this->events as $i => $event) {
            $haystack = implode(" ",$this->flattenArr($event));
            if (stripos($haystack, $this->search_words) !== false) {
                $this->filtered_events[] = $event;
            }
        }

        $this->filtered_weather = [];
        foreach ($this->stations as $i => $station) {
            $haystack = $station["name"] . " " . $station["event"];
            if (stripos($haystack, $this->search_words) !== false) {
                $this->filtered_weather[] = $station;
            }
        }


    }

    function getEvents()
    {
        $this->getDriveBC("events");

        if (!isset($this->request_response)) {
            return true;
        }

        $this->events = $this->request_response["events"];
        foreach ($this->request_response["events"] as $key => $event) {
        }
    }

    function getJurisdictions()
    {
        // API returns one jurisdiction (Province of British Columbia)

        $this->getDriveBC("jurisdiction");

        if (!isset($this->request_response)) {
            return true;
        }

        // devstack. Open511.
        //foreach($this->request_response['events'] as $key=>$event) {
        //}
    }

    function getWebcam()
    {
        // https://images.drivebc.ca/bchighwaycam/pub/html/dbc/562.html

        // Index of all.
        // https://images.drivebc.ca/bchighwaycam/pub/html/www/index.html
        // Index by region
        // https://images.drivebc.ca/bchighwaycam/pub/html/www/index-Northern.html
        // https://images.drivebc.ca/bchighwaycam/pub/html/www/index-SouthernInterior.html
        // https://images.drivebc.ca/bchighwaycam/pub/html/www/index-LowerMainland.html
        // https://images.drivebc.ca/bchighwaycam/pub/html/www/index-VancouverIsland.html
        // https://images.drivebc.ca/bchighwaycam/pub/html/www/index-Border.html
    }

    function getRoad($road_name = "Highway 1")
    {
        //https://api.open511.gov.bc.ca/events?road_name=Highway 1
        //$this->getDriveBC("events?road_name=" . $road_name);

        // rawurlencode adds in %20
        $this->getDriveBC("events?road_name=" . rawurlencode($road_name));

        $this->road[$road_name] = $this->request_response["events"];
    }

    function getBorder()
    {
        // http://www.th.gov.bc.ca/ATIS/
    }

    function getDMS()
    {
        // Not possible currently?
    }

    function getWeather()
    {
        // https://www.drivebc.ca/api/weather/
        //$l = "https://www.drivebc.ca/api/weather/observations/around?lat=48.443491&long=-123.343757";
        //$l = "https://www.drivebc.ca/api/weather/observations?format=json";
        $this->getDriveBC("api/weather/observations?format=json");

        if (!isset($this->request_response)) {
            return true;
        }

        foreach ($this->request_response as $key => $station) {
            $station = $station["station"];
            $this->stations[$station["id"]] = $station;
        }
    }

    function getAreas()
    {
        // API returns one jurisdiction (Province of British Columbia)

        $this->getDriveBC("areas");

        if (!isset($this->request_response)) {
            return true;
        }

        $this->areas = $this->request_response["areas"];

        $this->area_name = [];
        foreach ($this->areas as $key => $area) {
            $this->area_name[$area["name"]] = $area;
        }
    }

    function set()
    {
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "drivebc" . " " . $this->from
        );
    }

    function getDriveBC($resource = "events")
    {
        $this->getLink();

        $data_source = "https://api.open511.gov.bc.ca";

        // Different endpoint for weather conditions in the network.
        // https://www.drivebc.ca/api/weather/
        if (strpos($resource, "weather") !== false) {
            $data_source = "https://www.drivebc.ca";
        }

        $command = "/" . $resource;
        $l = $data_source . $command;
        $data = file_get_contents($l);
        if ($data == false) {
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, true);

        $this->request_response = $json_data;

        return $this->request_response;
    }

    public function getLink($ref = null)
    {
        // Give it the message returned from the API service

        $this->link = "http://www.drivebc.ca/";
        return $this->link;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();
        // Generate email response.

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function makeSMS()
    {
        $sms = "DRIVE BC ";
        //day_twilight_flag

        if (isset($this->drivebc_weather_flag)) {
            if (
                isset($this->filtered_weather) and
                count($this->filtered_weather) !== 0
            ) {
                $filtered_weather = $this->filtered_weather[0];
                $filtered_weather_text = $this->weatherDrivebc(
                    $filtered_weather
                )["text"];
                $sms .= $filtered_weather_text . " ";
            } else {
                $sms .= "No weather station found. ";
            }
        } else {
            if (
                isset($this->filtered_events) and
                count($this->filtered_events) !== 0
            ) {
                $filtered_event = $this->filtered_events[0];
                $filtered_event_text = $this->trafficDrivebc($filtered_event)[
                    "text"
                ];

                $sms .= $filtered_event_text . " ";
            } else {
                $sms .= " | curated link " . $this->link;
            }
        }

        //        $sms_message .= " | TEXT ?";
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function weatherDrivebc($weather_report)
    {
        $text =
            $weather_report["name"] .
            " " .
            $weather_report["date"] .
            " " .
            "airTemp " .
            $weather_report["airTemp"] .
            " " .
            "windMean " .
            $weather_report["windMean"] .
            " " .
            "windMax " .
            $weather_report["windMax"] .
            " " .
            "windDir " .
            $weather_report["windDir"] .
            " " .
            "roadTemp " .
            $weather_report["roadTemp"] .
            " " .
            "snowSince " .
            $weather_report["snowSince"] .
            " " .
            "snowEnd " .
            $weather_report["snowEnd"] .
            " " .
            "snowDepth " .
            $weather_report["snowDepth"] .
            " " .
            "precipLastHr " .
            $weather_report["precipLastHr"] .
            " " .
            "precip " .
            $weather_report["precip"];

        return ["text" => $text];
    }

    public function trafficDrivebc($traffic_event)
    {
        $text =
            $traffic_event["headline"] .
            " " .
            $traffic_event["description"] .
            " " .
            " " .
            $traffic_event["severity"] .
            " ";

        if (isset($traffic_event["schedule"]["intervals"])) {
        foreach ($traffic_event["schedule"]["intervals"] as $i => $interval) {
            $text .= $interval . " ";
        }
        }

        foreach ($traffic_event["roads"] as $i => $road) {
            $text .=
                $road["name"] .
                " " .
                $road["from"] .
                " to " .
                (isset($road["to"]) ? $road['to'] : "") .
                " " .
                ($road["direction"] !== "NONE" ? $road["direction"] : "") .
                " ";
        }

        return ["text" => $text];
    }

    public function makeSnippet()
    {
        $web = "";

        if (isset($this->search_words)) {
            $web .= $this->search_words;
            $web .= "<p>";
        }

        if (
            isset($this->filtered_events) and
            count($this->filtered_events) !== 0
        ) {
            $web .= "<b>Network Events</b>";
            $web .= "<p>";

            $web .= "<ul>";
            foreach ($this->filtered_events as $index => $event) {
                $web .=
                    "<li><div>" .
                $this->trafficDrivebc(
                    $event
                )["text"] .  $this->restoreUrl($event['url']) .
                    "</div>";
            }
            $web .= "</ul>";
            $web .= "<p>";
        }

        if (
            isset($this->filtered_weather) and
            count($this->filtered_weather) !== 0
        ) {
            $web .= "<b>Weather Reports</b>";
            $web .= "<p>";
            $web .= "<ul>";
            foreach ($this->filtered_weather as $index => $weather) {
                $web .= "<li><div>" . $weather["name"] . "</div>";
                $web .=
                    "<div>" 

                . $filtered_event_text = $this->weatherDrivebc($weather)[
                    "text"
                ] . "</div>";
            }
            $web .= "</ul>";
            $web .= "<p>";
        }


        if (
            isset($this->drivebc_stations_flag)
        ) {
            $web .= "<b>Weather Station List</b>";
            $web .= "<p>";
            $web .= "<ul>";
            foreach ($this->stations as $index => $station) {
                $web .= "<li><div>" . $station["name"] . " " . $station['description'] .  "</div>";
            }
            $web .= "</ul>";
            $web .= "<p>";
        }



        $this->snippet = $web;
        $this->thing_report["snippet"] = $web;
    }

    public function makeWeb()
    {
        $web = "";
        $web .= $this->snippet;

        $this->web = $web;
        $this->thing_report["web"] = $web;
    }

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

    public function readSubject()
    {

        $input = $this->input;
        $filtered_input = $this->filterAgent($this->input, [
            "drivebc",
            "drive",
            "weather",
            "traffic",
            "stations",
            "list",
        ]);

        // Did we see anything?
        if (mb_strlen($input) !== mb_strlen($filtered_input)) {
            $this->score = 10;
        }

        $indicators = [
            "weather" => ["weather", "rain", "snow", "wet"],
            "traffic" => ["traffic"],
            "stations" => ['stations','list'],
        ];

        $this->flagAgent($indicators, strtolower($input));

        if ($filtered_input == "") {
            return;
        }

        $this->search_words = $filtered_input;

    }
}
