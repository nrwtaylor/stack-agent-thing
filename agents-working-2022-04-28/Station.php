<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// Stations can see other stations on the forward path.
// And hear stations on the backward path.


// TODO Lots to do.
// Entity to keep track of stations forward and behind.

// Not functional.

class Station extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->channel = new Channel($this->thing, "channel");
        $this->channel_name = $this->channel->channel_name;

        // Handle stations specifically.
        // And if the context is Transit handle Translink Stops.
        $this->context = new Context($this->thing, "context");
        $this->transit = new Transit($this->thing, "transit");

        $this->node_list = ["station" => ["station"]];

        $this->max_hops = 2;

        $this->networkStations();
        $this->getNetworktime();
        $this->destinationsStation(); // Get the stops available from this stop.  Availability includes runat.
    }

    public function nextStation($parameter, $selector = null)
    {
        $station_list = [
            [
                "station_id" => "Mornington Crescent",
                "trip_id" => "x",
                "stop_sequence" => 6,
                "shape_dist_traveled" => null,
                "stop_code" => 51380,
                "stop_desc" => null,
                "stop_lat" => null,
                "stop_lon" => null,
            ],
            [
                "station_id" => "Euston Station",
                "trip_id" => "c",
                "stop_sequence" => 10,
                "shape_dist_traveled" => null,
                "stop_code" => 51381,
                "stop_desc" => null,
                "stop_lat" => null,
                "stop_lon" => null,
            ],
            [
                "station_id" => "Kings Cross Station",
                "trip_id" => "b",
                "stop_sequence" => 8,
                "shape_dist_traveled" => null,
                "stop_code" => 51382,
                "stop_desc" => null,
                "stop_lat" => null,
                "stop_lon" => null,
            ],
        ];

        $count = 0;
        while ($count < count($station_list)) {
            yield $station_list[$count];
            $count += 1;
        }
    }

    public function networkStations()
    {
        // Running in 15s.  4 Aug 2018.
        $split_time = $this->thing->elapsed_runtime();
        $this->thing->log("Making railway - transit context");

        for (
            $channels = $this->nextStation("station_times");
            $channels->valid();
            $channels->next()
        ) {
            $channel = $channels->current();
            $station_id = $channel["station_id"];
            $train_id = $channel["trip_id"];

            $stop_sequence = $channel["stop_sequence"];
            if ($stop_sequence == 1) {
                unset($last_station);
            }
            if (isset($last_station)) {
                $this->blocks[$last_station][$station_id] = $channel;
            }
            $last_station = $station_id;
        }
        $this->stations_network = $this->blocks;
    }

    function getStations($text = null)
    {
        // This needs to get a list of all the stations connected to this station (stop) by a train (trip)

        // Get the stations are connected (backwards and forwards) a stop

        // For transit context speak that looks like seeing which stops are
        // on all the routes which pass through this stop.

        // And this should do that.  Let's check.
        //$stop_code = $this->idStation($text);
        $stop_code = 51380;
        $station_id = $this->idStation($text);

        // Make the networks
        //        $this->networkStations();
        $station_id = $station_id; // Work in train context

        $visible_stations[$station_id] = [
            "visited" => false,
            "station_id" => $station_id,
        ];

        $completed = false;
        $hops = 0;

        $this->thing->log("Looking for visible stations.");

        while ($completed == false) {
            $completed = true;
            foreach (
                $visible_stations
                as $visible_station_id => $visible_station
            ) {
                if ($visible_station["visited"] == false) {
                    $station_id_pointer = $visible_station_id;
                    $completed = false;
                    break;
                }
            }

            if ($completed == true) {
                return;
            }
            // Now visiting stations up from $station_id

            if (isset($this->stations_network[$station_id_pointer])) {
                $stations = $this->stations_network[$station_id_pointer];

                foreach ($stations as $station_id => $station) {
                    $visible_stations[$station_id] = [
                        "visited" => false,
                        "station_id" => $station_id,
                        "station" => $station,
                    ];
                    $completed = false;
                }

                $visible_stations[$station_id_pointer]["visited"] = true;
            }
            $hops += 1;
            if ($hops > $this->max_hops) {
                break;
            }
        }

        $this->stations_visible = $visible_stations;
        return $this->stations_visible;
    }

    public function destinationsStation()
    {
        $this->destinations = []; // Fair enough.
    }

    public function originsStation()
    {
        $this->origins = []; // Fair enough.
    }

    public function nullStation()
    {
        $this->message = "STATION | Request not understood. | TEXT SYNTAX";
        $this->sms_message = "STATION | Request not understood. | TEXT SYNTAX";
        $this->response .= "Null station. ";
        return $this->message;
    }

    function idStation($text = null)
    {
        // Curiously one of the harder things to do.
        // dev create a CSV file when recognize version number has changed.

        // Transit context
        // Take text and recognize the id.
        $stop_code = "51382";
        $station = $this->stopcodeStation($stop_code);

        $this->station_id = $station["station_id"];
        return $this->station_id;
    }

    public function stopsStation($stop_code = null)
    {
        if ($stop_code === null) {
            return true;
        }
        // TODO build a look up of stops at each station.
        // Placeholder for now.
        $a = $this->stopcodeStation($stop_code);
        //    $a = $this->searchForStopCode($stop_code, $this->stations_network);
        $stops[0] = $this->stations_network[$stop_code];

        return $stops;
    }

    function stopcodeStation($stop_code)
    {
        foreach ($this->stations_network as $ststation_id => $stations) {
            foreach ($stations as $station) {
                if ($station["stop_code"] == $stop_code) {
                    return $station;
                }
                break;
            }
        }

        return true;
    }

    function makeStation($station)
    {
        $trip_id = $station["trip_id"];
        $station_id = $station["station_id"];
        $arrival_time = $station["arrival_time"];
        $departure_time = $station["departure_time"];
        $shape_dist_traveled = $station["shape_dist_traveled"];

        $stop = [
            "trip_id" => $trip_id,
            "station_id" => $station_id,
            "arrival_time" => $arrival_time,
            "departure_time" => $departure_time,
            "shape_dist_traveled" => $shape_dist_traveled,
            "elapsed_travel_time" => null,
        ];
        return $stop;
    }

    // To handle >24 hours.  Urgh:/
    // https://stackoverflow.com/questions/12708419/strtotime-function-for-hours-more-than-24
    function getTimeFromString($time)
    {
        $time = explode(":", $time);
        return mktime($time[0], $time[1], $time[2]);
    }

    function getStation($station_id = null)
    {
        if (isset($this->routes[$station_id])) {
            return $this->routes[$station_id];
        }
        if ($station_id == null) {
            $station_id = $this->station_id;
        }

        // This is tricky, because there is no existing file that maps
        // station_id to route.

        // Question is can it be done quick enough not
        // to worry about building another table.

        // Currently taking 15s.  4 August 2018.

        if (!isset($this->stations_db)) {
            $this->stations_db = $this->stopsStation([$station_id]);
        }

        $station_count = $this->searchForsId($station_id, $this->stations_db);

        $station = $this->stations_db[$station_count];

        if (!isset($this->routes[$station_id])) {
            $this->routesStation($station_id);
        }

        // trip_times.txt maps trip_id <> route_id
        // routes gets route info

        $station["routes"] = $this->routes[$station_id];

        return $station;
    }

    function tripRoute($station_id)
    {
        if (isset($this->trip_routes[$station_id])) {
            return $this->trip_routes;
        }

        for (
            $routes = $this->nextStation("trips", [
                "ststation_id" => $station_id,
            ]);
            $routes->valid();
            $routes->next()
        ) {
            $route = $routes->current();
            $this->trip_routes[$route["trip_id"]] = $route["route_id"];
        }

        return $this->trip_routes;
    }

    // This is ugly.

    function searchForId($id, $array)
    {
        if ($array === null) {
            return true;
        }

        foreach ($array as $key => $val) {
            if ($val["trip_id"] === $id) {
                return $key;
            }
        }
        return null;
    }

    function searchForsId($id, $array)
    {
        if ($array === null) {
            return true;
        }
        foreach ($array as $key => $val) {
            if ($val["station_id"] === $id) {
                return $key;
            }
        }
        return null;
    }

    function searchForrId($id, $array)
    {
        if ($array === null) {
            return true;
        }

        foreach ($array as $key => $val) {
            if ($val["route_id"] === $id) {
                return $key;
            }
        }
        return null;
    }

    function searchForStopCode($id, $array)
    {
        if ($array === null) {
            return true;
        }
        foreach ($array as $key => $val) {
            if ($val["stop_code"] === $id) {
                return $key;
            }
        }
        return null;
    }

    function routesStation($station_id)
    {
        $this->tripRoute($station_id); // trip_routes (quick trip to route conversion)

        $this->split_time = $this->thing->elapsed_runtime();

        $this->thing->log(
            'Agent "Station" is gettings routes for ' . $station_id . "."
        );

        // This is slow
        if (!isset($this->trips[$station_id])) {
            $this->tripsStation($station_id);
        }
        if (!isset($this->routes[$station_id])) {
            $this->routes[$station_id] = [];
        }

        if (!isset($this->trips_db)) {
            $this->trips_db = $this->tripsStation([$station_id]);
        }

        // For each trip_id get the route
        foreach ($this->trips[$station_id] as $trip_id) {
            // Translate trip_id to route_id
            $route_id = $this->trip_routes[$trip_id];

            // Have we processed it?
            if (isset($this->routes[$station_id][$route_id])) {
                continue;
            }

            $index = $this->searchForrId($route_id, $this->trips_db);
            $route = $this->trips_db[$index];

            $route_id = $route["route_id"];

            $this->routes[$station_id][$route_id] = $route;

            $this->thing->log(
                "Got station " . $station_id . " and route " . $route_id . "."
            );
        }

        $this->thing->log("Got stations.");

        return $this->routes[$station_id];
    }

    function tripsStation($station_ids)
    {
        $this->thing->log(
            "Getting trips passing through " . $station_ids . "."
        );
        //if (isset($this->trips[$station_ids])) {return $this->trips[$station_ids];}

        // stop times is 80Mb
        $selector_array = ["station_id" => $station_ids];

        for (
            $stops = $this->nextStation("station_times", $selector_array);
            $stops->valid();
            $stops->next()
        ) {
            $stop = $stops->current();
            $trip_id = $stop["trip_id"];

            $this->trips[$station_ids][] = $stop["trip_id"];
        }
        return $this->trips[$station_ids];
    }

    function makeSMS()
    {
        //if ((isset($this->stations)) and (is_array($this->stations))) {$count = count($this->stations);}
        $sms = "STATION | " . "A work in progress. Text TXT.";

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeTXT()
    {
        if (!isset($this->stations_visible)) {
            $this->getStations();
        }

        $txt = "FUTURE STOPS VISIBLE FROM THIS STATION " . $this->station_id;
        $txt .= "\n";
        //$txt .= $this->network_time;

        $txt .= "\n";

        foreach ($this->stations_visible as $station_id => $station) {
            if ($station["visited"]) {
                $txt .= "[" . $station_id . "] ";
            } else {
                $txt .= $station_id . " ";
            }
        }
        $j = 0;

        foreach ($this->stations_visible as $station_id => $station) {
            $this->split_time = $this->thing->elapsed_runtime();
            if (!isset($station["station"]["station_id"])) {
                continue;
            }
            $station_id = $station["station"]["station_id"];

            $next_stop_distance = $station["station"]["shape_dist_traveled"];

            $next_station = $this->getStation($station_id);

            // Create text block for routes served at a specific stop
            $r = "";
            foreach ($next_station["routes"] as $route) {
                $r .= $route["trip_headsign"] . " ";
            }
            $r = "\n";

            // Create text block for static information about stop
            $next_station_id = $next_station["station_id"];
            $next_station_code = $next_station["stop_code"];
            $next_station_desc = $next_station["stop_desc"];
            $next_station_lat = $next_station["stop_lat"];
            $next_station_long = $next_station["stop_lon"];

            $line =
                $station_id .
                "  " .
                $next_stop_distance .
                " " .
                $next_station_id .
                " " .
                $next_station_desc .
                " " .
                $next_station_lat .
                " " .
                $next_station_long .
                "\n";

            $txt .= $line;
            $txt .= $r . "\n";
            $this->thing->log($line);
            $this->thing->log($r);

            // Get a least one station
            if ($this->thing->elapsed_runtime() > 20000) {
                break;
            }

            //$last_station_id = $next_station_id;
        }
        $this->thing_report["txt"] = $txt;
    }

    function makeWeb()
    {
        $web = "meep";
        $this->thing_report["web"] = $web;
    }

    function getNetworktime()
    {
        $agent = new Clocktime($this->thing, "now");
        $this->network_time_string =
            str_pad($agent->hour, 2, "0", STR_PAD_LEFT) .
            ":" .
            str_pad($agent->minute, 2, "0", STR_PAD_LEFT) .
            ":" .
            "00";
    }

    function departuretimeNetwork($departure_time_text = null)
    {
        //$this->network_time_string = "20:07:00";
        if (!isset($this->network_time_string)) {
            $this->network_time_string = "16:01:00";
        }
        $network_time = strtotime($this->network_time_string);
        $departure_time = strtotime($departure_time_text);
        if ($departure_time < $network_time) {
            $departure_time = false;
        }

        return $departure_time;
        // RED - Trip hasn't been seen at the stop yet.
    }

    function infoStation()
    {
        $this->sms_message = "STATION";
        $this->sms_message .= " | ";
        $this->sms_message .=
            "Live data feed provided through the TransLink Open API. | https://developer.translink.ca/ | ";
        $this->sms_message .= "TEXT HELP";
    }

    function helpStation()
    {
        $this->sms_message = "STATION";
        $this->sms_message .= " | ";
        $this->sms_message .=
            'Text the five-digit stop number for live Translink stop inforation. | For example, "51380". | ';
        $this->sms_message .= "TEXT <5-digit stop number>";
    }

    function syntaxStation()
    {
        $this->sms_message = "STATION";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}
        $this->sms_message .= " | ";
        $this->sms_message .= 'Syntax: "51380". | ';
        $this->sms_message .= "TEXT HELP";
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->thing_report["choices"] = false;
        $this->thing_report["info"] = "SMS sent";

        // Generate email response.

        $this->thing_report["info"] =
            "This is the Station Agent responding to a request.";

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["help"] =
            "This agent is developmental (and slow ~160,000ms).  See what you think.  Let me know at " .
            $this->email .
            ".";
    }

    public function readSubject()
    {
        $this->keywords = ["stop", "bus", "route"];
        $input = $this->input;

        $filtered_input = $this->assert($input, "station");

        $pieces = explode(" ", strtolower($filtered_input));

        if (count($pieces) == 1) {
            //        $input = $this->subject;

            if (is_numeric($filtered_input) and strlen($filtered_input) == 5) {
                return $this->stopcodeStation($filtered_input);
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "stop":
                            if ($key + 1 > count($pieces)) {
                                $this->stop = false;
                                return "Request not understood";
                            } else {
                                $this->stop = $pieces[$key + 1];
                                $this->response .= $this->stopStation(
                                    $this->stop
                                );
                                return;
                            }
                            break;

                        case "bus":
                            break;

                        case "translink":
                        case "info":
                        case "information":
                            $this->infoStation();
                            return;

                        case "help":
                            $this->helpStation();
                            return;

                        case "syntax":
                            $this->syntaxStation();
                            return;

                        default:
                    }
                }
            }
        }
        $this->nullStation();
    }
}
