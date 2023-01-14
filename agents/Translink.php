<?php
/**
 * Translink.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Translink extends Agent
{
    public $var = "hello";

    /**
     *
     */
    function init()
    {
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

        $this->agent_name = "translink";

        // So I could call
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }

        $this->api_key = $this->thing->container["api"]["translink"];

        $this->retain_for = 2; // Retain for at least 2 hours.

        $this->sqlresponse = null;

        // Allow for a new state tree to be introduced here.
        $this->node_list = ["start" => ["useful", "useful?"]];
    }

    /**
     *
     */
    public function run()
    {
        $this->getNetworktime();
    }

    /**
     *
     * @return unknown
     */
    public function nullAction()
    {
        $names = $this->thing->Write(["character", "action"], "null");

        $this->message =
            "TRANSIT | Translink request not understood. | TEXT SYNTAX";
        //$this->sms_message = "TRANSIT | Translink request not understood. | TEXT SYNTAX";
        $this->response .= "Translink request not understood. ";
        return $this->message;
    }

    /**
     *
     */
    function deprecate_getStopid()
    {
    }

    /**
     *
     * @param unknown $file_name
     * @param unknown $selector_array (optional)
     * @return unknown
     */
    public function getTranslink($file_name, $selector_array = null)
    {
        $this->thing->console("Getting " . $file_name . "\n");

        $matches = [];
        $iterator = $this->gtfsTranslink($file_name, $selector_array);

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
    function getEvents()
    {
        // For testing
        // So this is too slow to present the full tuple map
        $stop_code = 51380;
        $this->network_time = $this->network_time_string;
        //"15:43:33";

        $stops = $this->getTranslink("stops", ["stop_code" => $stop_code]);

        if (isset($stops[0])) {
            $stop_id = $stops[0]["stop_id"];
        }
        $this->thing->console(
            "Matched stop_code " .
                $stop_code .
                " to stop_id " .
                $stop_id .
                ".\n"
        );
        $this->stop_id = $stop_id;
        // Information for this stop.  Generator/yield magic.
        $events = $this->gtfsTranslink("stop_times", ["stop_id" => $stop_id]);

        $stop_events = [];

        $trip_ids = [];
        // Information for this stop.

        // Generate the next event
        $visible = "off";
        for (
            $events = $this->gtfsTranslink("stop_times", [
                "stop_id" => $stop_id,
            ]);
            $events->valid();
            $events->next()
        ) {
            $event = $events->current();

            $event_time_delta =
                $this->getTimeFromString($event["arrival_time"]) -
                $this->getTimeFromString($this->network_time);
            $event["event_time_delta"] = $event_time_delta;

            //            if ($event_time_delta < -30) {continue;}

            // And really only looking 2 hours into the future (transit pass validity)
            //            $seconds_limit = 2 * 60 * 60;
            //            if ($event_time_delta > $seconds_limit) {continue;}

            $event_stop_id = $event["stop_id"];
            $event_trip_id = $event["trip_id"];

            $stop_events[$event_stop_id][$event_trip_id] = $event;
        }
        // Now determine which stops are visible.
        // Based on network time and whether the trip has passed the stop.
        $this->thing->console(
            "Determining tripe events for first trip on the list :/" . "\n"
        );
        //$this->network_time = "15:43:33";

        //      foreach ($this->stops as $stop) {
        //            foreach ($stop as $trips) {
        //              foreach ($trips as $event) {
        // Get all the trip events

        $event_stop_id = $event["stop_id"];
        $event_trip_id = $event["trip_id"];

        // Can't go back to previous events / stops.

        //                    $trip_events = $this->stops[$event_stop_id][$event_trip_id];

        // Generator to get the stops on the next trip
        $trip_events = $this->gtfsTranslink("stop_times", [
            "trip_id" => $event_trip_id,
        ]);
        $visible = "off"; // No events visible.
        foreach ($trip_events as $event) {
            $event_stop_id = $event["stop_id"];
            $event_trip_id = $event["trip_id"];

            $event["event_time_delta"] =
                strtotime($event["arrival_time"]) -
                strtotime($this->network_time);

            $stop_events[$event_stop_id][$event_trip_id] = $event;
        }

        $this->events = $stop_events;
        return;

        $visible = "off"; // No events visible.
        foreach ($trip_events as $trip_event) {
            if ($trip_event["stop_id"] == $stop_id) {
                $visible = "on";
            } // Until the event has happened.

            if ($visible == "on") {
                $this->visible_events[$trip_event["stop_id"]][
                    $trip_event["trip_id"]
                ] = $event;
            }
        }

        $this->stop_events = $stop_events;
        return;
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
     * @param unknown $stop_id (optional)
     */
    function getTrips($stop_id = null)
    {
        $trips = [];
        $trip_ids = [];

        // Information for this stop.
        // Generate the next event
        for (
            $trips = $this->gtfsTranslink("stop_times", [
                "stop_id" => $stop_id,
            ]);
            $trips->valid();
            $trips->next()
        ) {
            $trip = $trips->current();
            //$route_id = $trip['route_id'];
            $trip_id = $trip["trip_id"];
            $stop_id = $trip["stop_id"];

            $trip_ids[] = $trip_id;

            $trips[$trip_id][] = $stop_id;
        }
        $this->trip_stops = $trips;
    }

    /**
     *
     */
    function getRoutes()
    {
        // I think this is where this forks to station 3 August 2018

        $this->routes = [];
        $routes = $this->gtfsTranslink("routes");
        //$stop_events = array();
        $trip_ids = [];
        // Information for this stop.
        // Generate the next event
        $visible = "off";
        for (
            $routes = $this->gtfsTranslink("routes");
            $routes->valid();
            $routes->next()
        ) {
            $route = $routes->current();
            $route_id = $route["route_id"];
            $this->routes[$route_id] = [
                "route_short_name" => $route["route_short_name"],
                "route_long_name" => $route["route_long_name"],
            ];
        }
    }

    /**
     *
     */
    public function makeTXT()
    {
        return;
        //if (!isset($this->events)) {$this->getEvents();}

        $this->routes = [];
        $routes = $this->gtfsTranslink("routes");
        $stop_events = [];
        $trip_ids = [];
        // Information for this stop.
        // Generate the next event
        $visible = "off";
        for (
            $routes = $this->gtfsTranslink("routes");
            $routes->valid();
            $routes->next()
        ) {
            $route = $routes->current();
            $route_id = $route["route_id"];
            $this->routes[$route_id] = [
                "route_short_name" => $route["route_short_name"],
                "route_long_name" => $route["route_long_name"],
            ];
        }

        // this is like runs
        $this->trips = [];
        $this->trip_ids = [];

        // Information for this stop.
        // Generate the next event
        for (
            $trips = $this->gtfsTranslink("trips");
            $trips->valid();
            $trips->next()
        ) {
            $trip = $trips->current();
            $route_id = $trip["route_id"];
            $trip_id = $trip["trip_id"];
            $trip_headsign = $trip["trip_headsign"];

            $this->trip_ids[] = $trip_id;

            $this->trips[$trip_id] = [
                "route_id" => $route_id,
                "trip_headsign" => $trip_headsign,
            ];
        }

        $txt = "FUTURE EVENTS VISIBLE FROM THIS STOP " . $this->stop;
        $txt .= "\n";
        $txt .= $this->network_time;

        $txt .= "\n";

        $j = 0;
        foreach ($this->events as $trips) {
            foreach ($trips as $event) {
                $line = "";
                //                    $event = $this->events[51380][9130758];

                if ($event["event_time_delta"] < 0) {
                    continue;
                }
                if ($event["event_time_delta"] > 60 * 60 * 2) {
                    continue;
                }

                $trip_id = $event["trip_id"];
                $line .= str_pad($trip_id, 10, " ", STR_PAD_LEFT);

                $route_id = $this->trips[$event["trip_id"]]["route_id"];
                $trip_headsign =
                    $this->trips[$event["trip_id"]]["trip_headsign"];

                $line .= str_pad($route_id, 12, " ", STR_PAD_LEFT);
                $line .= str_pad($trip_headsign, 20, " ", STR_PAD_LEFT);

                $route_array = $this->routes[$route_id];

                $route_short_name =
                    $this->routes[$route_id]["route_short_name"];
                $route_long_name = $this->routes[$route_id]["route_long_name"];

                $line .= str_pad($event["stop_id"], 7, " ", STR_PAD_LEFT);

                $line .= str_pad(
                    "*" . $route_short_name . "*",
                    10,
                    " ",
                    STR_PAD_LEFT
                );
                $line .= " ";
                $line .= str_pad($route_long_name, 34, " ", STR_PAD_RIGHT);

                $line .= str_pad($event["arrival_time"], 12, " ", STR_PAD_LEFT);
                $line .= str_pad(
                    $event["departure_time"],
                    10,
                    " ",
                    STR_PAD_LEFT
                );

                $event_time_delta = $event["event_time_delta"];

                $line .= str_pad(
                    $this->thing->human_time($event["event_time_delta"]),
                    15,
                    " ",
                    STR_PAD_LEFT
                );

                $line .= "\n";

                if (!isset($lines[$event_time_delta])) {
                    $lines[$event_time_delta] = [];
                }

                $lines[$event_time_delta][] = $line;

                //still devstack here

                $trip_ids[] = $trip_id;

                $find_stops = false;
                if ($find_stops) {
                    $i = 0;
                    $j += 1;
                    for (
                        $trip_events = $this->gtfsTranslink("stop_times", [
                            "trip_id" => $trip_id,
                        ]);
                        $trip_events->valid();
                        $trip_events->next()
                    ) {
                        if ($j > 2) {
                            break;
                        }
                        $trip_event = $trip_events->current();
                        $i += 1;
                        if ($j >= 1) {
                            break;
                        }

                        if ($i >= 1) {
                            break;
                        }

                        $trip_event_time_delta =
                            $this->getTimeFromString(
                                $trip_event["arrival_time"]
                            ) - $this->getTimeFromString($this->network_time);
                        $trip_event["event_time_delta"] =
                            $trip_event_time_delta + $event_time_delta;

                        if ($trip_event["event_time_delta"] > 60 * 5) {
                            continue;
                        }
                        $stop_id = $trip_event["stop_id"];

                        $lines[$trip_event_time_delta][] =
                            "    " .
                            $stop_id .
                            " " .
                            $trip_event["event_time_delta"] .
                            "\n";

                        $this->thing->console(
                            "\n" .
                                $stop_id .
                                " " .
                                $trip_event["event_time_delta"] .
                                "\n"
                        );
                    }
                }
            }
        }
        ksort($lines);

        foreach ($lines as $line_set) {
            foreach ($line_set as $line) {
                $txt .= $line;
            }
        }

        $txt .= "\n";

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    /**
     *
     */
    function makeWeb()
    {
        $web = "meep";
        $this->thing_report["web"] = $web;
    }

    /*
        $txt = "";
        foreach ($this->stop_tuples as $stop_tuple) {
            $txt .= $stop_tuple['trip_time_elapsed']. " " . $stop_tuple['shape_dist_traveled']. " " . $stop_tuple['departure_time'];
            $txt .= "\n";
        }
*/

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

    /**
     *
     * @param unknown $file_name
     * @param unknown $selector_array (optional)
     */
    function gtfsTranslink($file_name, $selector_array = null)
    {
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

            //$line = trim(fgets($handle));
            $arr = [];
            $field_values = explode(",", $line);
            $i = 0;
            foreach ($field_names as $field_name) {
                if (!isset($field_values[$i])) {
                    $field_values[$i] = null;
                }
                $arr[$field_name] = $field_values[$i];
                $i += 1;
            }

            if ($selector_array == null) {
                yield $arr;
            }

            $match_count = 0;
            $match = true;
            foreach ($arr as $field_name => $field_value) {
                // Look for all items in the selector_array matching
                if ($selector_array == null) {
                    continue;
                }

                foreach ($selector_array as $selector_name => $selector_value) {
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

            if ($match == false) {
                continue;
            }

            yield $arr;
        }

        fclose($handle);
    }

    /**
     *
     * @param unknown $file_name
     * @param unknown $index_name (optional)
     * @return unknown
     */
    function getGtfs($file_name, $index_name = null)
    {
        $file =
            $GLOBALS["stack_path"] .
            "resources/translink/" .
            $file_name .
            ".txt";

        $handle = fopen($file, "r");

        $output_array = [];

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
    function infoTranslink()
    {
        $this->sms_message = "TRANSIT";
        $this->sms_message .= " | ";
        $this->sms_message .=
            "Live data feed provided through the TransLink Open API. | https://developer.translink.ca/ | ";
        $this->sms_message .= "TEXT HELP";

        return;
    }

    /**
     *
     */
    function helpTranslink()
    {
        $this->response .=
            'Text the five-digit stop number for live Translink stop inforation. | For example, "51380". | ';
        return;
    }

    /**
     *
     */
    function syntaxTranslink()
    {
        $this->response .= 'Syntax: "51380". | ';

        return;
    }

    /**
     *
     * @param unknown $stop
     * @return unknown
     */
    public function stopTranslink($stop)
    {
        $split_time = $this->thing->elapsed_runtime();
        //$this->thing->log('Agent "Translink". Start Translink API call. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');
        $stop_message = "";
        $this->stop = $stop;
        try {
            $file =
                "http://api.translink.ca/rttiapi/v1/stops/" .
                $stop .
                "/estimates?apikey=" .
                $this->api_key .
                "&count=3&timeframe=60";
            //   $web_input = file_get_contents('http://api.translink.ca/rttiapi/v1/stops/'.$stop .'/estimates?apikey='. $this->api_key . '&count=3&timeframe=60');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $file);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $xmldata = curl_exec($ch);
            curl_close($ch);

            $web_input = $xmldata;
            if ($web_input == "") {
                $web_input = @file_get_contents(
                    "http://api.translink.ca/rttiapi/v1/stops/" .
                        $stop .
                        "/estimates?apikey=" .
                        $this->api_key .
                        "&count=3&timeframe=60"
                );

                if ($web_input === false) {
                    $this->response .=
                        "Unable to contact Translink real-time API service. ";
                    return;
                }
            }

            $this->error = "";
        } catch (Exception $e) {
            $this->thing->console("Caught exception: ", $e->getMessage(), "\n");
            $this->error = $e;
            $web_input = false;
            $this->response .= "Request not understood. ";
            return "Request not understood";
        }

        $xml = simplexml_load_string($web_input);
        $t = $xml->NextBus;

        $json_data = json_encode($t, true);

        $response = null;

        foreach ($t as $item) {
            $response .=
                "<li>" .
                $item->Schedules->Schedule->ExpectedLeaveTime .
                " " .
                $item->RouteNo .
                " " .
                $item->RouteName .
                " " .
                "> " .
                $item->Schedules->Schedule->Destination .
                "</li>";
        }

        $message =
            "Thank you for your request for stop " .
            $stop .
            ".  The next buses are: <p><ul>" .
            ucwords(strtolower($response)) .
            "</ul>";
        $message .= "";
        $message .= "Source: Translink real-time data feed.";

        // Hacky here to be refactored.
        // Generate a special short SMS message

        //        $this->sms_message = "";
        $response = "";

        foreach ($t as $item) {
            // $response .=  $item->Schedules->Schedule->ExpectedLeaveTime . ' ' . $item->RouteNo . '> ' . $item->Schedules->Schedule->Destination . ' | ';

            $response .=
                $item->RouteNo .
                " " .
                $item->Schedules->Schedule->ExpectedLeaveTime .
                " > " .
                $item->Schedules->Schedule->Destination .
                " | ";
        }

        $stop_message = "NEXT BUS";

        if (is_array($t) and count($t) > 1) {
            //if (count($t) > 1) {
            $stop_message .= "ES";
        }

        $stop_message .= " | ";

        // Sometimes Translink return
        // a date in the time string.  Remove it.

        $input = $response;
        //$input = "Current from 2014-10-10 to 2015/05/23 and 2001.02.10";
        $output = preg_replace(
            "/(\d{4}[\.\/\-][01]\d[\.\/\-][0-3]\d)/",
            "",
            $input
        );

        if (is_array($t) and count($t) == 0) {
            // if (count($t) == 0) {
            $stop_message .=
                "No information returned for stop " . $this->stop . " | ";
        } else {
            $stop_message .= ucwords(strtolower($output));
        }

        $stop_message .= "Source: Translink | ";

        $alert_agent = new Alert($this->thing, "alert");
        if ($alert_agent->flag == "red") {
            $stop_message .= "TEXT ALERT";
        } else {
            $stop_message .= "TEXT ?";
        }

        $this->thing->log(
            'Agent "Translink". Translink API call took ' .
                number_format($this->thing->elapsed_runtime() - $split_time) .
                "ms."
        );

        $this->stop_message = $stop_message;
        $this->response .= $stop_message;

        return $message;
    }

    /**
     *
     * @param unknown $bus_id
     * @return unknown
     */
    public function busTranslink($bus_id)
    {
        try {
            $file =
                "http://api.translink.ca/rttiapi/v1/buses/" .
                $bus_id .
                "?apikey=" .
                $this->api_key;

            //http://api.translink.ca/rttiapi/v1/stops/'.$stop .'/estimates?apikey='. $this->api_key . '&count=3&timeframe=60';

            $web_input = file_get_contents($file);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $file);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $xmldata = curl_exec($ch);
            curl_close($ch);

            $web_input = $xmldata;

            $this->error = "";
        } catch (Exception $e) {
            $this->thing->console("Caught exception: ", $e->getMessage(), "\n");
            $this->error = $e;
            $web_input = false;
            $this->response .= "Bus information not yet supported. ";
            return;
            return "Bus information not yet supported";
        }

        $message = "Here is some xml information" . $web_input;
        //$this->sms_message = "TRANSIT | Bus number service not implemented.";
        $this->message =
            "A bus number was provided, but the agent cannot yet respond to this.";

        $this->response .= "Bus number service not implemented. ";

        return $message;
    }

    // -----------------------

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        //$this->thing->log('Agent "Translink". Start Respond. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');

        // Thing actions
        $this->thing->flagGreen();

        $this->thing_report["sms"] = $this->sms_message;
        $this->thing_report["choices"] = false;
        $this->thing_report["info"] = "SMS sent";

        //              $this->thing_report['email'] = array('to'=>$this->from,
        //                              'from'=>'transit',
        //                              'subject' => $this->subject,
        //                              'message' => $message,
        //                              'choices' => false);

        // Generate email response.

        //  $to = $this->thing->from;
        //  $from = "transit";

        // Need to refactor email to create a preview of the sent email in the $thing_report['email']
        // For now this attempts to send both an email and text.

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["help"] = "Connector to Translink API.";

        //        $this->makeTxt();
        //        $this->makeweb();

        return $this->thing_report;
    }

    public function makeSMS()
    {
        //$this->sms_message = "Test" . $this->subject;
$sms = $this->response;
$this->sms_message = $sms;
        $this->thing_report["sms"] = $this->sms_message;
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
        $keywords = ["stop", "bus", "route", "transit", "gtfs"];

        $input = strtolower($this->subject);

        $input = $this->assert($input);

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            //            $input = $this->subject;
           if (ctype_alpha($input[0]) == true) {
                // Strip out first letter and process remaning 4 or 5 digit number

                $input = substr($input, 1);
                if (is_numeric($input) and strlen($input) == 4) {
                    $this->busTranslink($input);
                    return;

                    return $this->busTranslink($input);
                    //return $this->response;
                }

                if (is_numeric($input) and strlen($input) == 5) {
                    //$this->response .= "foo ".$input. " bar";
                    //                    $this->busTranslink($input);
                    //                    return;

                    return $this->stopTranslink($input);

                    //return $this->response;
                }

                if (is_numeric($input) and strlen($input) == 6) {
                    $this->busTranslink($input);
                    return;

                    return $this->busTranslink($input);
                }
            }
            if (is_numeric($input) and strlen($input) == 5) {
                $this->stopTranslink($input);
                return;
            }

            if (is_numeric($input) and strlen($input) == 4) {
                $this->busTranslink($input);
                return;
            }

            $this->response .= "Request not understood. ";
            //                        return "Request not understood";
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "stop":
                            if ($key + 1 > count($pieces)) {
                                $this->stop = false;
                                return "Request not understood";
                            } else {
                                $this->stop = $this->extractNumber($input);
                                //$pieces[$key + 1];
                                $this->response .= $this->stopTranslink(
                                    $this->stop
                                );
                                return $this->response;
                            }
                            break;

                        case "bus":
                            break;

                        case "translink":
                        case "info":
                        case "information":
                            $this->infoTranslink();
                            return;

                        case "help":
                            $this->helpTranslink();
                            return;

                        case "syntax":
                            $this->syntaxTranslink();
                            return;

                        default:
                    }
                }
            }
        }
        $this->nullAction();
        return "Message not understood";
    }
}
