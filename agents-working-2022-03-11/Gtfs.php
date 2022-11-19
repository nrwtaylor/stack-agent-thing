<?php
/**
 * Gtfs.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

//ini_set('memory_limit', '1024M');

ini_set("allow_url_fopen", 1);

class Gtfs extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->channel = new Channel($this->thing, "channel");
        $this->channel_name = $this->channel->channel_name;

        // Some notes
        // agency.txt - agency_id, agency_name - descriptions of the three agencies
        // feed_info.txt - description of the feed publisher
        // shapes.txt - shape_id - looks like all translink routes with coords (shape_id)
        // transfers.txt - from_stop_id, to_stop_id
        // calendar_dates.txt
        // stops.txt - stop_id, stop_code, stop_name - includes stop coordinates
        // trips.txt - route_id, trip_id, trip_headsign, shape_id
        // calendar.txt
        // routes.txt - route_id, route short_name, route long_name
        // stop_times.txt - trip_id, arrival_time, departure_time, stop_id

        // So to generate a list of destinations from a particular stop_id?

        // From stops translate stop_code (51380) to stop_id (11614)
        // From stop_times translate stop_id to trip_id (after found line)
        //   this gives a list of all the stops after the stop on routes served
        //   from the stop

        $this->start_time = $this->thing->elapsed_runtime();

        // So I could call
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }

        $this->retain_for = 2; // Retain for at least 2 hours.

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["start" => ["useful", "useful?"]];

        $this->thing->log("running on Thing " . $this->thing->nuuid . ".");
        $this->thing->log('received this Thing "' . $this->subject . '".');

        $this->max_hops = 10;

        $this->thing_report["help"] =
            "Asks Translink about where you are. Try GTFS MADISON HASTINGS.";

    }

    /**
     *
     * @return unknown
     */
    function railwayGtfs()
    {
        if (isset($this->railway)) {
            return $this->railway;
        }
        // Running in 15s.  4 Aug 2018.
        $split_time = $this->thing->elapsed_runtime();
        $this->thing->log("Making railway - transit context");

        // stop_times is a large file
        // this looks through and identifies all the blocks.
        // From one stop to the next.

        for (
            $channels = $this->nextGtfs("stop_times");
            $channels->valid();
            $channels->next()
        ) {
            $channel = $channels->current();

            $station_id = $channel["stop_id"];
            $train_id = $channel["trip_id"];

            //$this->thing->log ( "got " . $station_id . " " . $train_id . ".");

            $stop_sequence = $channel["stop_sequence"];
            if ($stop_sequence == 1) {
                unset($last_station);
            }

            if (isset($last_station)) {
                $this->railway[$last_station][$station_id] = $channel;
            }
            $last_station = $station_id;
        }

        $this->thing->log(
            "Made a railway in " .
                number_format($this->thing->elapsed_runtime() - $split_time) .
                "ms."
        );
    }

    /**
     *
     * @param unknown $station_id (optional)
     * @return unknown
     */
    function stopGtfs($station_id = null)
    {
        if ($station_id == null) {
            $station_id = $this->station_id;
        }

        if (isset($this->stops[$station_id])) {
            return $this->stops[$station_id];
        }
        $stop = $this->stopsGtfs($station_id);
        $this->thing->log("Got stop " . $station_id . ".");
        return $stop;
    }

    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function findStop($text = null)
    {
        if ($text == null) {
            return null;
        }

        if (!isset($this->stops_db)) {
            $this->stops_db = $this->getMatches("stops");
        }
        $match_array = $this->searchForText(strtolower($text), $this->stops_db);

        return $match_array;
    }

    /**
     *
     * @param unknown $station_id (optional)
     * @return unknown
     */
    function stopsGtfs($station_id = null)
    {
        if ($station_id == null) {
            $station_id = $this->station_id;
        }

        if (isset($this->stops[$station_id])) {
            return $this->stops[$station_id];
        }
        // Use Translink file language.  This is a station in train context.
        if (!isset($this->stops_db)) {
            $this->stops_db = $this->getMatches("stops");
        }
        $stop_count = $this->searchForsId($station_id, $this->stops_db);

        $stop = null;
        if (!($stop_count == null)) {
            $stop = $this->stops_db[$stop_count];
        }
        $this->stops[$station_id] = $stop;
        $this->thing->log(
            "got station " . $station_id . " and stop " . $stop["stop_id"] . "."
        );
        return $this->stops[$station_id];
    }

    /**
     *
     * @param unknown $station_id (optional)
     * @return unknown
     */
    function stationsGtfs($station_id = null)
    {
        if (isset($this->stations[$station_id])) {
            return $this->stations;
        }

        $this->thing->log("get stations " . $station_id . ".");

        // This needs to get a list of all the stations connected to this station (stop) by a train (trip)

        // Get the stations are connected (backwards and forwards) a stop

        // For transit context speak that looks like seeing which stops are
        // on all the routes which pass through this stop.

        // Make the networks
        $this->railwayGtfs();

        // Use Translink file language.  This is a station in train context.
        $stop = $this->stopsGtfs($station_id);

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
                    //             break;
                }
            }

            if ($completed == true) {
                return true;
            }

            // Now visiting stations up from $station_id
            $stations = $this->railway[$station_id_pointer];

            foreach ($stations as $station_id => $station) {
                $visible_stations[$station_id] = [
                    "visited" => false,
                    "station_id" => $station_id,
                    "station" => $this->stopsGtfs($station_id),
                ];
                $completed = false;
            }

            $visible_stations[$station_id_pointer]["station"] = $this->stopsGtfs(
                $station_id_pointer
            );

            $visible_stations[$station_id_pointer]["routes"] = $this->routesGtfs(
                $station_id_pointer
            );
            $visible_stations[$station_id_pointer]["visited"] = true;
            $hops += 1;
            if ($hops > $this->max_hops) {
                break;
            }
        }

        $this->stations = $visible_stations;

        return $this->stations;
    }

    /**
     *
     */
    public function getDestinations()
    {
        $this->destinations = []; // Fair enough.
    }

    /**
     *
     * @return unknown
     */
    public function nullAction()
    {
        $this->thing->json->setField("variables");

        $this->message = "TRANSIT | Request not understood. | TEXT SYNTAX";
        $this->sms_message = "TRANSIT | Request not understood. | TEXT SYNTAX";
        $this->response = true;
        return $this->message;
    }

    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function idStation($text = null)
    {
        // Curiously one of the harder things to do.
        // dev create a CSV file when recognize version number has changed.

        // Transit context
        // Take text and recognize the id.
        if ($text == null) {
            return null;
        }
        $stop_code = $text;

        $stops = $this->getMatches("stops", ["stop_code" => $stop_code]);

        if (isset($stops[0])) {
            $stop_id = $stops[0]["stop_id"];
        }
        $this->thing->log(
            "Matched stop_code " . $stop_code . " to stop_id " . $stop_id . "."
        );

        $this->station_id = $stop_id;
        return $this->station_id;
    }

    /**
     *
     * @param unknown $file_name
     * @param unknown $selector_array (optional)
     * @return unknown
     */
    function getMatches($file_name, $selector_array = null)
    {
        $matches = [];
        $iterator = $this->nextGtfs($file_name, $selector_array);

        foreach ($iterator as $iteration) {
            $matches[] = $iteration;
        }

        return $matches;
    }

    /**
     *
     * @param unknown $iteration
     * @return unknown
     */
    function makeStop($iteration)
    {
        $trip_id = $iteration["trip_id"];
        $stop_id = $iteration["stop_id"];
        $arrival_time = $iteration["arrival_time"];
        $departure_time = $iteration["departure_time"];
        $shape_dist_traveled = $iteration["shape_dist_traveled"];

        $stop = [
            "trip_id" => $trip_id,
            "stop_id" => $stop_id,
            "arrival_time" => $arrival_time,
            "departure_time" => $departure_time,
            "shape_dist_traveled" => $shape_dist_traveled,
            "elapsed_travel_time" => null,
        ];
        return $stop;
    }

    /**
     * To handle >24 hours.  Urgh:/
     * https://stackoverflow.com/questions/12708419/strtotime-function-for-hours-more-than-24
     *
     * @param unknown $time
     * @return unknown
     */
    function getTimeFromString($time)
    {
        $time = explode(":", $time);
        return mktime($time[0], $time[1], $time[2]);
    }

    /**
     *
     */
    function echoTuple()
    {
        // Deploy
        $stop_tuple = $this->stop_tuples["51380"]["9130759"]["27:54:00"];

        $txt = $stop_tuple["stop_id"];
        $txt .= $stop_tuple["trip_time_elapsed"];
        $txt .= $stop_tuple["shape_dist_traveled"];
        $txt .= $stop_tuple["departure_time"];

        $this->thing->console($txt);
    }

    /**
     *
     * @param unknown $station_id (optional)
     * @return unknown
     */
    function getStation($station_id = null)
    {
        if (isset($this->stations[$station_id]["station"])) {
            return $this->stations[$station_id];
        }
        $this->stationsGtfs($station_id);
        return $this->stations[$station_id];
    }

    /**
     *
     * @param unknown $station_id
     * @return unknown
     */
    function tripRoute($station_id)
    {
        if (isset($this->trip_routes[$station_id])) {
            return $this->trip_routes;
        }

        for (
            $routes = $this->nextGtfs("trips", [["stop_id" => $station_id]]);
            $routes->valid();
            $routes->next()
        ) {
            $route = $routes->current();
            $this->trip_routes[$route["trip_id"]] = $route["route_id"];
        }

        return $this->trip_routes;
    }

    // This is ugly.

    /**
     *
     * @param unknown $id
     * @param unknown $array
     * @return unknown
     */
    function searchForId($id, $array)
    {
        foreach ($array as $key => $val) {
            if ($val["trip_id"] === $id) {
                return $key;
            }
        }
        return null;
    }

    /**
     *
     * @param unknown $text
     * @param unknown $array
     * @return unknown
     */
    function searchForText($text, $array)
    {
        //$text = "commercial broadway";
        $text = strtolower($text);
        $pieces = explode(" ", $text);
        $match = false;
        $match_array = [];
        $num_words = count($pieces);
        foreach ($array as $key => $val) {
            $stop_name = strtolower($val["stop_name"]);

            $stop_id = strtolower($val["stop_id"]);
            $stop_code = strtolower($val["stop_code"]);

            $count = 0;

            foreach ($pieces as $piece) {
                if (preg_match("/\b$piece\b/i", $stop_name)) {
                    $count += 1;
                    $match = true;
                } else {
                    $match = false;
                    continue;
                }

                if ($count == $num_words) {
                    break;
                }
            }

            if ($count == $num_words) {
                $match_array[] = [
                    "stop_name" => $stop_name,
                    "stop_id" => $stop_id,
                    "stop_code" => $stop_code,
                ];
            }
        }
        return $match_array;
    }

    /**
     *
     * @param unknown $id
     * @param unknown $array
     * @return unknown
     */
    function searchForsId($id, $array)
    {
        foreach ($array as $key => $val) {

            if ($val["stop_id"] == $id) {
                return $key;
            }
        }
        return null;
    }

    /**
     *
     * @param unknown $id
     * @param unknown $array
     * @return unknown
     */
    function searchForrId($id, $array)
    {
        foreach ($array as $key => $val) {
            if ($val["route_id"] === $id) {
                return $key;
            }
        }
        return null;
    }

    /**
     *
     * @param unknown $station_id
     * @return unknown
     */
    function routesGtfs($station_id)
    {
        $this->tripRoute($station_id); // trip_routes (quick trip to route conversion)

        $this->split_time = $this->thing->elapsed_runtime();

        $this->thing->log("gettings routes for " . $station_id . ".");

        // This is slow
        if (!isset($this->trips[$station_id])) {
            $this->tripsGtfs($station_id);
        }

        if (!isset($this->routes[$station_id])) {
            $this->routes[$station_id] = [];
        }

        if (!isset($this->routes_db)) {
            $this->routes_db = $this->getMatches("routes");
        }

        // For each trip_id get the route
        foreach ($this->trips[$station_id] as $trip) {
            // Translate trip_id to route_id
            $trip_id = $trip["trip_id"];
            $route_id = $this->trip_routes[$trip_id];

            if (isset($this->routes[$station_id][$route_id])) {
                continue;
            }

            $index = $this->searchForrId($route_id, $this->routes_db);
            $route = $this->routes_db[$index];

            $this->routes[$station_id][$route_id] = $route;

            $this->thing->log(
                "Got station " . $station_id . " and route " . $route_id . "."
            );
        }

        $this->thing->log("Got Gtfs stops.");

        return $this->routes;
    }

    /**
     *
     * @param unknown $station_id_input
     * @return unknown
     */
    function tripsGtfs($station_id_input)
    {
        $this->thing->log(
            "Getting trips connecting through station_id " .
                $station_id_input .
                "."
        );

        // stop times is 80Mb

        if (is_array($station_id_input)) {
            // Get all the stations (which is all visible).
            // And see whether they appear in the list of served routes.
            // Travelling salesman?
            //           foreach($station_id_input as $key=>$station_id) {
            //if (!isset($this->trips[$station_id])) {$this->trips[$station_id] = array();}
            //                $selector_array[] = array($key=>$station_id);
            //            }
            $selector_array = $station_id_input;
        } else {
            $selector_array = [["stop_id" => $station_id_input]];
        }
        for (
            $stops = $this->nextGtfs("stop_times", $selector_array);
            $stops->valid();
            $stops->next()
        ) {
            $stop = $stops->current();
            $trip_id = $stop["trip_id"];
            $stop_id = $stop["stop_id"];

            if (!isset($this->trips[$stop_id])) {
                $this->trips[$stop_id] = [];
            }
            $this->trips[$stop_id][$trip_id] = $stop;
        }

        $this->thing->log(
            "Got trips connecting through station_id " . $station_id_input . "."
        );

        return $this->trips;
    }

    /**
     *
     */
    function makeSMS()
    {
        if (isset($this->message)) {
            $this->sms_message = "GTFS | " . $this->message;
            $this->thing_report["sms"] = $this->sms_message;
            return;
        }

        if (!isset($this->routes[$this->station_id])) {
            $this->routesGtfs($this->station_id);
        }
        if (!isset($this->stations[$this->station_id])) {
            $this->stationsGtfs($this->station_id);
        }

        // Produce a list of stops
        $s = "";
        $stops = $this->stationsGtfs($this->station_id);
        foreach ($stops as $stop_id => $stop) {
            $station = $stop["station"];
            $stop_text = $station["stop_desc"];
            $stop_code = $station["stop_code"];
            $stop_name = $station["stop_name"];

            $s .= $stop_name . " ";
            $s .= " [";
            $r = "";
            if (!isset($stop["routes"])) {
                $routes = $this->routesGtfs($stop_id);
                foreach ($routes[$stop_id] as $route_id => $route) {
                    $r .= $route["route_short_name"] . " ";
                }
                $s .= $r . "] ";
                continue;
            }
            $routes = $stop["routes"];
            $route_text = "";

            foreach ($routes as $station_id => $station_routes) {
                foreach ($station_routes as $route_id => $route) {
                    $route_short_name = $route["route_short_name"];
                    $route_short_name_array[$route_short_name] = true;
                }
            }

            foreach ($route_short_name_array as $route_short_name => $value) {
                $route_text .= $route_short_name . " ";
            }
            $s .= $route_text . "]";
        }

        // Produce a list of routes
        $t = "";
        foreach ($this->routes[$this->station_id] as $route_id => $route) {
            $t .= $route["route_short_name"] . " ";
        }

        $sms =
            "STATION " .
            $this->station_id .
            " | routes available here " .
            $t .
            " stops visible " .
            $s .
            " | Text TXT.";

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */
    function makeTXT()
    {
        if (true) {
            return;
        }

        if (!isset($this->stations)) {
            $this->stationsGtfs();
        }

        $txt = "FUTURE STOPS VISIBLE FROM THIS STATION " . $this->station_id;
        $txt .= "\n";

        $txt .= "\n";

        foreach ($this->stations as $station_id => $station) {
            if ($station["visited"]) {
                $txt .= "[" . $station_id . "] ";
            } else {
                $txt .= $station_id . " ";
            }
        }

        $j = 0;
        foreach ($this->stations as $station_id => $station) {
            $txt .= $station_id . "\n";

            $this->split_time = $this->thing->elapsed_runtime();

            // Visible but station building hasn't happened.
            // Costly function so don't run for reporting.

            //if (!isset($station['station'])) {$txt .= "No station information.\n";}

            $stop = $this->stopsGtfs($station_id);
            $stop_code = $stop["stop_code"];
            $stop_id = $stop["stop_id"];
            $stop_desc = $stop["stop_desc"];

            $stop_name = $stop["stop_name"];
            $stop_lat = $stop["stop_lat"];
            $stop_lon = $stop["stop_lon"];
            $zone_id = $stop["zone_id"];

            $stop_text =
                $station_id .
                " " .
                $stop_id .
                " " .
                $stop_desc .
                " " .
                $stop_name .
                " " .
                $stop_lat .
                " " .
                $stop_lon .
                " " .
                $zone_id .
                "\n";

            $route_text = "";
            if (isset($station["routes"])) {
                // Nothing textually useful from $station['station']
                // But does have a full route list indexed by stop_id and route_id
                $route_text = "";
                foreach ($station["routes"] as $routes) {
                    foreach ($routes as $route) {
                        $route_short_name = $route["route_short_name"];
                        $route_long_name = $route["route_long_name"];
                        $route_text .=
                            $route_short_name . " " . $route_long_name . " ";
                        // Other fields not used by Translink
                    }
                }
            }

            $txt .= $stop_text . $route_text . "\n\n";

            //            $stop_id =  ($station['station']['stop_id']);
            //            $next_stop_distance = $station['station']['shape_dist_traveled'];
        }
        $this->thing_report["txt"] = $txt;
    }

    /**
     *
     */
    function makeWeb()
    {
        $web = "Not implemented.";
        $this->thing_report["web"] = $web;
    }

    /**
     *
     */
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

    /**
     *
     * @param unknown $departure_time_text (optional)
     * @return unknown
     */
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

    function parseLine($line, $field_names = null)
    {
        if ($field_names == null) {
            $field_names = $this->field_names;
        }

        $field_values = explode(",", $line);
        $i = 0;
        $arr = [];

        foreach ($field_names as $field_name) {
            if (!isset($field_values[$i])) {
                $field_values[$i] = null;
            }
            $arr[$field_name] = $field_values[$i];
            $i += 1;
        }
        return $arr;
    }

    /**
     *
     * @param unknown $file_name
     * @param unknown $selector_array (optional)
     */
    function nextGtfs($file_name, $selector_array = null)
    {

        $this->thing->log("nextGtfs " . $file_name . " ");
        $split_time = $this->thing->elapsed_runtime();

        $file =
            $GLOBALS["stack_path"] .
            "resources/translink/" .
            $file_name .
            ".txt";

        $handle = fopen($file, "r");
        $line_number = 0;

        while (!feof($handle)) {
            $line = trim(fgets($handle));
            $line_number += 1;
            // Get headers
            if ($line_number == 1) {
                $i = 0;
                $field_names = explode(",", $line);

                foreach ($field_names as $field) {
                    $field_names[$i] = preg_replace(
                        '/[\x00-\x1F\x80-\xFF]/',
                        "",
                        $field
                    );
                    $i += 1;
                }
                continue;
            }

            $arr = $this->parseLine($line, $field_names);

            // If there is no selector array, just return it.
            if ($selector_array == null) {
                yield $arr;
                continue;
            }

            if (array_key_exists(0, $selector_array)) {
            } else {
                $selector_array = [$selector_array];
            }

            // Otherwise see if it matches the selector array.
            $match_count = 0;
            $match = true;
            foreach ($arr as $field_name => $field_value) {
                //if ($selector_array == null) {$matches[] = $iteration; continue;}

                // Look for all items in the selector_array matching
                if ($selector_array == null) {
                    continue;
                }

                foreach ($selector_array as $selector) {
                    foreach ($selector as $selector_name => $selector_value) {
                        if ($selector_name != $field_name) {
                            continue;
                        }

                        if ($selector_value == $field_value) {
                            $match_count += 1;
                        } else {
                            $match = false;
                            break;
                        }
                    }
                }
            }

            if ($match == false) {
                continue;
            }

            yield $arr;
        }

        fclose($handle);

        $this->thing->log(
            "nextGtfs took " .
                number_format($this->thing->elapsed_runtime() - $split_time) .
                "ms."
        );
    }

    /**
     *
     * @param unknown $file_name
     * @param unknown $index_name (optional)
     * @return unknown
     */
    function getGtfs($file_name, $index_name = null)
    {
        // Load in data files
        //       $searchfor = strtoupper($this->search_words);
        //$searchfor = "MAIN HASTINGS";

        $file =
            $GLOBALS["stack_path"] .
            "resources/translink/" .
            $file_name .
            ".txt";
        $output_array = [];
        if (!file_exists($file)) {return $output_array;}

        $handle = fopen($file, "r");

        if ($handle) {
            $line_number = 0;
            while (($line = fgets($handle)) !== false) {
                $line_number += 1;

                if ($line_number == 1) {
                    $i = 0;
                    $field_names = explode(",", $line);

                    foreach ($field_names as $field) {
                        $field_names[$i] = preg_replace(
                            '/[\x00-\x1F\x80-\xFF]/',
                            "",
                            $field
                        );
                        $i += 1;
                    }

                    $i = 0;
                    $field_index = 0;
                    foreach ($field_names as $field_name) {
                        if ($field_name == $index_name or $index_name == null) {
                            $field_index = $i;
                            break;
                        }
                        $i += 1;
                    }

                    continue;
                }

                $field_values = explode(",", $line);

                //$field_index = 0;
                $arr = [];
                $i = 0;
                foreach ($field_names as $field_name) {
                    if (!isset($field_values[$i])) {
                        $field_values[$i] = null;
                    }
                    $arr[$field_name] = $field_values[$i];
                    $i += 1;
                }

                $field_index_value = $field_values[$field_index];
                if (!isset($output_array[$field_index_value])) {
                    $output_array[$field_index_value] = [];
                }
                $output_array[$field_index_value][] = $arr;

                // process the line read.
            }

            fclose($handle);
        } else {
            // error opening the file.
        }

        return $output_array;
    }

    /**
     *
     */
    function gtfsInfo()
    {
        $this->sms_message = "GTFS";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}
        $this->sms_message .= " | ";
        $this->sms_message .=
            "Analysis of the TransLink network data files. | https://developer.translink.ca/ | ";
        $this->sms_message .= "TEXT HELP";

        return;
    }

    /**
     *
     */
    function gtfsHelp()
    {
        $this->sms_message = "GTFS";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}
        $this->sms_message .= " | ";
        $this->sms_message .=
            'Text the five-digit stop number for live Translink stop inforation. | For example, "51380". | ';
        $this->sms_message .= "TEXT <5-digit stop number>";
    }

    /**
     *
     */
    function gtfsSyntax()
    {
        $this->sms_message = "GTFS";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}
        $this->sms_message .= " | ";
        $this->sms_message .= 'Syntax: "51380". | ';
        $this->sms_message .= "TEXT HELP";
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["choices"] = false;
        $this->thing_report["info"] = "SMS sent";

        $this->thing_report["info"] =
            "This is the Station Agent responding to a request.";

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    /**
     *
     * @param unknown $phrase
     */
    private function nextWord($phrase)
    {
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {

        $keywords = ["stop", "bus", "route"];

        $input = $this->input;
        $input = $this->assert($input);
        $arr = $this->findStop($input);

        $count = 0;
        if (is_array($arr)) {
            $count = count($arr);
        }

        if ($count > 1) {
            $m = "";
            $temp_array = [];
            foreach ($arr as $stop) {
                $station_id = $stop["stop_id"];
                $this->stations[$station_id] = [
                    "visited" => false,
                    "station_id" => $station_id,
                ];

                $this->places[$stop["stop_name"]][$stop["stop_id"]] = $stop;

                $temp_array[$stop["stop_name"]][] = $stop["stop_code"];
            }

            foreach ($temp_array as $stop_desc => $stops) {
                $m .= trim(implode(" ", $stops)) . " " . $stop_desc . " / ";
            }

            $this->message = $m;
            return;
        }

        if ($arr != null and count($arr) == 1) {
            $this->station_id = $arr[0]["stop_id"];
            $m = $arr[0]["stop_code"] . " " . $arr[0]["stop_name"];
            $this->message =
                $m .
                " | " .
                "http://www.transitdb.ca/stop/" .
                $arr[0]["stop_code"] .
                "/ | TEXT WEB";
            return;
        }

        $number = new Number($this->thing, $input);

        if (isset($number->numbers[0])) {
            $transit_id = $number->numbers[0];
            $station_id = $this->idStation($transit_id);

            if ($station_id == null) {
                $this->message = "No match found.";
                $this->response = "No match found.";
                return;
            }
            $this->getStation($station_id);
            $this->station_id = $station_id;

            $this->response =
                "Collected stop information within 10 hops of this stop.";
            return;
        }

        $m = "No matching stops found.";
        $this->message = $m;

    }
}
