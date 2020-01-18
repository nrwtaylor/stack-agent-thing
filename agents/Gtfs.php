<?php
/**
 * Gtfs.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//ini_set('memory_limit', '1024M');

ini_set("allow_url_fopen", 1);

class Gtfs extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init() {
        $this->channel = new Channel($this->thing, "channel");
        $this->channel_name = $this->channel->channel_name;

        // Handle stations specifically.
        // And if the context is Transit handle Translink Stops.
        //        $this->context = new Context($thing, "context");
        // 1 word messages don't action.

        //       $this->transit = new Transit($thing,"translink");
        //$this->findStop("Madison and Hastings");
        //exit();


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
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

        $this->api_key = $this->thing->container['api']['translink'];

        $this->retain_for = 2; // Retain for at least 2 hours.

        $this->sqlresponse = null;

        // Allow for a new state tree to be introduced here.
        $this->node_list = array("start"=>array("useful", "useful?"));

        $this->thing->log('running on Thing ' . $this->thing->nuuid . '.');
        $this->thing->log('received this Thing "' . $this->subject .  '".');

        $this->max_hops = 10;


        //        $this->getNetworktime();

        //        $this->getDestinations(); // Get the stops available from this stop.  Availability includes runat.

        $this->thing->log('ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

        $this->thing_report['help'] = 'Asks Translink about where you are. Try GTFS MADISON HASTINGS.';

        $this->thing_report['response'] = $this->response;

    }

    /**
     *
     * @return unknown
     */
    function getRailway() {
        if (isset($this->railway)) {return $this->railway;}
        //echo "making railway\n";
        // Running in 15s.  4 Aug 2018.
        $split_time = $this->thing->elapsed_runtime();
        $this->thing->log ( "Making railway - transit context");

        // stop_times is a large file
        // this looks through and identifies all the blocks.
        // From one stop to the next.

        for ($channels = $this->nextGtfs("stop_times"); $channels->valid(); $channels->next()) {

            $channel = $channels->current();
            //var_dump($channel);
            $station_id = $channel['stop_id'];
            $train_id = $channel['trip_id'];

            //$this->thing->log ( "got " . $station_id . " " . $train_id . ".");

            $stop_sequence = $channel['stop_sequence'];
            if ($stop_sequence == 1) {unset($last_station);}

            if (isset($last_station)) {
                $this->railway[$last_station][$station_id] = $channel;
            }
            $last_station = $station_id;
        }

        $this->thing->log('Made a railway in ' . number_format($this->thing->elapsed_runtime() - $split_time) . 'ms.');

    }


    /**
     *
     * @param unknown $station_id (optional)
     * @return unknown
     */
    function getStop($station_id = null) {
        //echo "get stop " . $station_id . "\n";
        if ($station_id == null) {$station_id = $this->station_id;}

        if (isset($this->stops[$station_id])) {return $this->stops[$station_id];}
        $stop = $this->getStops($station_id);
        $this->thing->log("Got stop ".$station_id .".");
        return $stop;
        /*
        // Use Translink file language.  This is a station in train context.
        if (!isset($this->stops_db)) {$this->stops_db = $this->get("stops");}
        $stop_count = $this->searchForsId($station_id, $this->stops_db);

        $stop = null;
        if (!($stop_count == null)) {$stop = $this->stops_db[$stop_count];}
        $this->stop = $stop;

        return $stop;
*/
    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function findStop($text = null) {
        if ($text == null) {return null;}

        if (!isset($this->stops_db)) {$this->stops_db = $this->getMatches("stops");}
        $match_array = $this->searchForText(strtolower($text), $this->stops_db);

        return $match_array;
    }


    /**
     *
     * @param unknown $station_id (optional)
     * @return unknown
     */
    function getStops($station_id = null) {
        if ($station_id == null) {$station_id = $this->station_id;}

        if (isset($this->stops[$station_id])) {return $this->stops[$station_id];}
        // Use Translink file language.  This is a station in train context.
        if (!isset($this->stops_db)) {$this->stops_db = $this->getMatches("stops");}
        $stop_count = $this->searchForsId($station_id, $this->stops_db);

        $stop = null;
        if (!($stop_count == null)) {$stop = $this->stops_db[$stop_count];}
        $this->stops[$station_id] = $stop;
        $this->thing->log("got station " . $station_id . " and stop " . $stop['stop_id']. ".");
        return $this->stops[$station_id];

    }


    /**
     *
     * @param unknown $station_id (optional)
     * @return unknown
     */
    function getStations($station_id = null) {

        if (isset($this->stations[$station_id])) {return $this->stations;}

        $this->thing->log("get stations " . $station_id . ".");

        // This needs to get a list of all the stations connected to this station (stop) by a train (trip)

        // Get the stations are connected (backwards and forwards) a stop

        // For transit context speak that looks like seeing which stops are
        // on all the routes which pass through this stop.

        // And this should do that.  Let's check.
        //$stop_code = $this->idStation($text);
        //$text = 51380;
        //        $stop_id = $this->idStation($text);

        // Make the networks
        $this->getRailway();
        //        $station_id = $stop_id; // Work in train context


        // Use Translink file language.  This is a station in train context.
        //        if (!isset($this->stops_db)) {$this->stops_db = $this->get("stops");}
        //        $stop_count = $this->searchForsId($station_id, $this->stops_db);
        //        $stop = $this->stops_db[$stop_count];

        $stop = $this->getStops($station_id);


        $visible_stations[$station_id] = array("visited"=>false, "station_id"=>$station_id);

        $completed = false;
        $hops = 0;

        $this->thing->log("Looking for visible stations.");

        while ($completed == false) {
            $completed = true;
            foreach ($visible_stations as $visible_station_id=>$visible_station) {
                if ($visible_station['visited'] == false) {
                    $station_id_pointer = $visible_station_id;
                    $completed = false;
                    //             break;
                }
            }

            if ($completed == true) {var_dump($visible_stations);echo "meep";exit();}
            //echo "\n";

            // Now visiting stations up from $station_id
            $stations =  $this->railway[$station_id_pointer];


            foreach ($stations as $station_id=>$station) {
                $visible_stations[$station_id] = array("visited"=>false, "station_id"=>$station_id, "station"=>$this->getStops($station_id));
                //echo $station_id . " ";
                $completed = false;
            }

            $visible_stations[$station_id_pointer]['station'] = $this->getStops($station_id_pointer);

            $visible_stations[$station_id_pointer]['routes'] = $this->getRoutes($station_id_pointer);
            $visible_stations[$station_id_pointer]['visited'] = true;
            $hops += 1;
            if ($hops > $this->max_hops) {break;}
        }


        //echo "\n";

        $this->stations = $visible_stations;
        //var_dump($this->stations);
        //exit();

        return $this->stations;
    }


    /**
     *
     */
    public function getDestinations() {
        $this->destinations = array(); // Fair enough.
    }


    /**
     *
     * @return unknown
     */
    public function nullAction() {

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
    function idStation($text = null) {
        // Curiously one of the harder things to do.
        // dev create a CSV file when recognize version number has changed.

        // Transit context
        // Take text and recognize the id.
        if ($text == null) {return null;}
        $stop_code = $text;

        $stops = $this->getMatches("stops", array("stop_code"=>$stop_code));

        if (isset($stops[0])) {$stop_id = $stops[0]["stop_id"];}
        $this->thing->log("Matched stop_code " . $stop_code . " to stop_id " .$stop_id . ".");

        $this->station_id = $stop_id;
        return $this->station_id;
    }


    /**
     *
     * @param unknown $file_name
     * @param unknown $selector_array (optional)
     * @return unknown
     */
    function getMatches($file_name, $selector_array = null) {

/*
        if (!isset($this->mem_cached)) {$this->getMemcached();

           $matches = $this->mem_cached->get('gtfs-translink');
//var_dump($matches);
//cdexit();


        }
*/
/*
        if (!isset($mem_var)) {
           $mem_var = new \Memcached; //point 2.
           $mem_var->addServer("127.0.0.1", 11211); 
        }

        $temp = $mem_var->get('gtfs-translink'); //point 3
        if (is_array($temp)) {$matches = $temp; return $matches;}
*/
        //$this->thing->log("Getting " . $file_name . ".txt.");

        $matches = array();
        $iterator = $this->nextGtfs($file_name, $selector_array);

        foreach ($iterator as $iteration) {
            $matches[] = $iteration;
        }

        if (!isset($this->mem_cached)) {$this->getMemcached();}
        $this->mem_cached->set('gtfs-translink',  $matches); //point 3


        return $matches;
    }


    /**
     *
     * @param unknown $iteration
     * @return unknown
     */
    function makeStop($iteration) {
        $trip_id =  $iteration['trip_id'];
        $stop_id =  $iteration['stop_id'];
        $arrival_time =  $iteration['arrival_time'];
        $departure_time =  $iteration['departure_time'];
        $shape_dist_traveled =  $iteration['shape_dist_traveled'];

        $stop = array("trip_id"=>$trip_id,
            "stop_id"=>$stop_id,
            "arrival_time"=>$arrival_time,
            "departure_time"=>$departure_time,
            "shape_dist_traveled"=>$shape_dist_traveled,
            "elapsed_travel_time"=>null);
        return $stop;

    }


    /**
     * To handle >24 hours.  Urgh:/
     * https://stackoverflow.com/questions/12708419/strtotime-function-for-hours-more-than-24
     *
     * @param unknown $time
     * @return unknown
     */
    function getTimeFromString($time) {
        $time = explode(':', $time);
        return mktime($time[0], $time[1], $time[2]);
    }


    /**
     *
     */
    function echoTuple() {
        // Deploy
        $stop_tuple = $this->stop_tuples['51380']['9130759']["27:54:00"];

        $txt = $stop_tuple['stop_id'];
        $txt .= $stop_tuple['trip_time_elapsed'];
        $txt .= $stop_tuple['shape_dist_traveled'];
        $txt .= $stop_tuple['departure_time'];

        echo $txt;
    }


    /**
     *
     * @param unknown $station_id (optional)
     * @return unknown
     */
    function getStation($station_id = null) {

        if (isset($this->stations[$station_id]['station'])) {return $this->stations[$station_id];}
        $this->getStations($station_id);
        return $this->stations[$station_id];
    }


    /*
        if (isset($this->routes[$station_id])) {return $this->routes[$station_id];}
        if ($station_id == null) {$station_id = $this->station_id;}

        // This is tricky, because there is no existing file that maps
        // station_id to route.

        // Question is can it be done quick enough not
        // to worry about building another table.

        // Currently taking 15s.  4 August 2018.


//        $selector_array = array("stop_id"=>$station_id); // Because this is a Station

        if (!isset($this->stations_db)) {$this->stations_db = $this->get("stops");}

        $station_count = $this->searchForsId($station_id, $this->stations_db);
        $station = $this->stations_db[$station_count];

        // stop_times.txt maps stop_id <> trip_id


        if(!isset($this->routes[$station_id])) {$this->getRoutes($station_id);}

        // trip_times.txt maps trip_id <> route_id
        // routes gets route info

        $station['routes'] = $this->routes[$station_id];

        return $station;

    }
*/


    /**
     *
     * @param unknown $station_id
     * @return unknown
     */
    function tripRoute($station_id) {
        if (isset($this->trip_routes[$station_id])) {return $this->trip_routes;}

        for ($routes = $this->nextGtfs("trips", array(array("stop_id"=>$station_id))); $routes->valid(); $routes->next()) {
            $route = $routes->current();
            $this->trip_routes[$route['trip_id']] = $route['route_id'];
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
    function searchForId($id, $array) {
        foreach ($array as $key => $val) {
            if ($val['trip_id'] === $id) {
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
    function searchForText($text, $array) {
        //$text = "commercial broadway";
        $text = strtolower($text);
        $pieces = explode(" ", $text);
        $match = false;
        $match_array = array();
        $num_words = count($pieces);
        foreach ($array as $key => $val) {

            $stop_desc = strtolower($val['stop_desc']);
            $stop_id = strtolower($val['stop_id']);
            $stop_code = strtolower($val['stop_code']);

            $count = 0;
            foreach ($pieces as $piece) {


                if (preg_match("/\b$piece\b/i", $stop_desc)) {
                    $count += 1;
                    $match = true;
                } else {
                    $match = false;
                    continue;
                }


                if ($count == $num_words) {break;}

            }
            if ($count == $num_words) {$match_array[] = array("stop_desc"=>$stop_desc,
                    "stop_id"=>$stop_id,
                    "stop_code"=>$stop_code);}
        }

        return $match_array;
    }


    /**
     *
     * @param unknown $id
     * @param unknown $array
     * @return unknown
     */
    function searchForsId($id, $array) {
        foreach ($array as $key => $val) {
            //echo $val['stop_id'] . " " . $id. "\n";
            if ($val['stop_id'] == $id) {
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
    function searchForrId($id, $array) {
        foreach ($array as $key => $val) {
            if ($val['route_id'] === $id) {
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
    function getRoutes($station_id) {

        $this->tripRoute($station_id); // trip_routes (quick trip to route conversion)

        // $this->getTrips($station_id);

        $this->split_time = $this->thing->elapsed_runtime();

        $this->thing->log('gettings routes for ' . $station_id .'.');

        // This is slow
        if (!isset($this->trips[$station_id])) {$this->getTrips($station_id);}

        if (!isset($this->routes[$station_id])) {$this->routes[$station_id] = array();}

        //        if (!isset($this->trips_db)) {$this->trips_db = $this->get("trips");}
        if (!isset($this->routes_db)) {$this->routes_db = $this->getMatches("routes");}



        // For each trip_id get the route
        foreach ($this->trips[$station_id] as $trip) {
            // Translate trip_id to route_id
            //  $route_id =  $this->trip_routes[$trip_id];
            // Have we processed it?

            //var_dump($trip);
            $trip_id = $trip['trip_id'];
            $route_id = $this->trip_routes[$trip_id];

            if (isset($this->routes[$station_id][$route_id])) {continue;}

            $index = $this->searchForrId($route_id, $this->routes_db);
            $route = $this->routes_db[$index];

            //    $route_id = $route['route_id'];

            $this->routes[$station_id][$route_id] = $route;

            //            echo ".";
            $this->thing->log('Got station ' . $station_id . ' and route ' . $route_id . ".");
        }

        //echo "\n";
        $this->thing->log('Got Gtfs stops.');

        return $this->routes;
    }


    /**
     *
     * @param unknown $station_id_input
     * @return unknown
     */
    function getTrips($station_id_input) {
        //echo "\n";
        $this->thing->log("Getting trips connecting through station_id " . $station_id_input . ".");
        //if (isset($this->trips[$station_ids])) {return $this->trips[$station_ids];}



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
            $selector_array = array(array("stop_id"=>$station_id_input));
        }
        for ($stops = $this->nextGtfs("stop_times", $selector_array); $stops->valid(); $stops->next()) {

            $stop = $stops->current();
            $trip_id = $stop['trip_id'];
            $stop_id = $stop['stop_id'];

            if (!isset($this->trips[$stop_id])) {$this->trips[$stop_id] = array();}
            $this->trips[$stop_id][$trip_id] = $stop;

        }

        $this->thing->log("Got trips connecting through station_id " . $station_id_input . ".");


        return $this->trips;
    }


    /**
     *
     */
    function makeSMS() {
        if (isset($this->message)) {

            $this->sms_message = "GTFS | " . $this->message;
            $this->thing_report['sms'] = $this->sms_message;
            return;
        }

        if (!isset($this->routes[$this->station_id])) {$this->getRoutes($this->station_id);}
        if (!isset($this->stations[$this->station_id])) {$this->getStations($this->station_id);}


        // Produce a list of stops
        $s ="";
        $stops = $this->getStations($this->station_id);
        foreach ($stops as $stop_id=>$stop) {
//var_dump($stop);
            $station =  ($stop['station']);
            $stop_text = $station['stop_desc'];
            $stop_code= $station['stop_code'];
            $stop_name= $station['stop_name'];

            $s .= $stop_name ." ";
            $s .= " [";
            $r = "";
            if (!isset($stop['routes'])) {
                $routes = $this->getRoutes($stop_id);
                foreach ($routes[$stop_id] as $route_id=>$route) {
                    $r .= $route['route_short_name'] . " ";
                }
                $s .= $r . "] ";
                continue;

            }
            $routes = $stop['routes'];
            $route_text = "";

            foreach ($routes as $station_id=>$station_routes) {
                foreach ($station_routes as $route_id=>$route) {
                    $route_short_name = $route['route_short_name'];
                    $route_short_name_array[$route_short_name] = true;
                }
            }

            foreach ($route_short_name_array as $route_short_name=>$value) {
                $route_text .= $route_short_name ." ";
            }
            $s .= $route_text . "]";

        }

        // Produce a list of routes
        $t="";
        foreach ( ($this->routes[$this->station_id]) as $route_id=>$route) {
            $t .= $route['route_short_name'] . " " ;
        }

        $sms = "STATION " . $this->station_id . " | routes available here " . $t . " stops visible " . $s . " | Text TXT.";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }


    /**
     *
     */
    function makeTXT() {
        if (true) {return;}

        if (!isset($this->stations)) {$this->getStations();}

        $txt = "FUTURE STOPS VISIBLE FROM THIS STATION " . $this->station_id;
        $txt .= "\n";

        $txt .= "\n";

        foreach ($this->stations as $station_id=>$station) {
            if ($station['visited']) {

                $txt .= "[".$station_id ."] ";
            } else {
                $txt .= $station_id ." ";

            }
        }


        $j = 0;
        foreach ($this->stations as $station_id=>$station) {
            $txt .= $station_id . "\n";

            $this->split_time = $this->thing->elapsed_runtime();

            // Visible but station building hasn't happened.
            // Costly function so don't run for reporting.

            //if (!isset($station['station'])) {$txt .= "No station information.\n";}

            //var_dump($station);
            $stop = $this->getStops($station_id);
            $stop_code = $stop['stop_code'];
            $stop_id = $stop['stop_id'];
            $stop_desc = $stop['stop_desc'];

            $stop_name = $stop['stop_name'];
            $stop_lat = $stop['stop_lat'];
            $stop_lon = $stop['stop_lon'];
            $zone_id = $stop['zone_id'];

            $stop_text = $station_id . " " . $stop_id ." ". $stop_desc . " " . $stop_name . " " . $stop_lat . " " . $stop_lon ." " . $zone_id ."\n";

            $route_text = "";
            if (isset($station['routes'])) {

                // Nothing textually useful from $station['station']
                // But does have a full route list indexed by stop_id and route_id
                $route_text = "";
                foreach ($station['routes'] as $routes) {
                    foreach ($routes as $route) {
                        //var_dump($route);
                        //exit();
                        $route_short_name = $route['route_short_name'];
                        $route_long_name = $route['route_long_name'];
                        $route_text .= $route_short_name ." " .$route_long_name . " ";
                        // Other fields not used by Translink
                    }

                }
            }

            $txt .= $stop_text .  $route_text . "\n\n";

            //            $stop_id =  ($station['station']['stop_id']);
            //            $next_stop_distance = $station['station']['shape_dist_traveled'];


        }
        $this->thing_report['txt'] = $txt;
    }


    /**
     *
     */
    function makeWeb() {
        $web = "Not implemented.";
        $this->thing_report['web'] = $web;

    }


    /**
     *
     */
    function getNetworktime() {
        $agent = new Clocktime($this->thing, "now");
        $this->network_time_string = str_pad($agent->hour, 2, "0", STR_PAD_LEFT).":".str_pad($agent->minute, 2, "0", STR_PAD_LEFT).":"."00";

    }


    /**
     *
     * @param unknown $departure_time_text (optional)
     * @return unknown
     */
    function departuretimeNetwork($departure_time_text = null) {
        //$this->network_time_string = "20:07:00";
        if (!isset($this->network_time_string)) {$this->network_time_string = "16:01:00";}
        $network_time = strtotime($this->network_time_string);
        $departure_time = strtotime($departure_time_text);
        if ($departure_time < $network_time) {$departure_time = false;}

        return $departure_time;
        // RED - Trip hasn't been seen at the stop yet.

    }


    /**
     *
     * @param unknown $file_name
     * @param unknown $selector_array (optional)
     */
    function nextGtfs($file_name, $selector_array = null) {


        if (!isset($this->mem_cached)) {$this->getMemcached();
           $matches = $this->mem_cached->get('gtfs-translink');
           $this->thing->log("found memcached gtfs-translink store.");
        }



        $this->thing->log("nextGtfs " . $file_name . " ");
        $split_time = $this->thing->elapsed_runtime();

        $file = $GLOBALS['stack_path'] . 'resources/translink/' . $file_name . '.txt';

        $handle = fopen($file, "r");
        $line_number = 0;

        while (!feof($handle)) {
            $line = trim(fgets($handle));
            $line_number += 1;
            //echo ".";
            // Get headers
            if ($line_number == 1) {
                $i = 0;
                $field_names = explode(",", $line);

                foreach ($field_names as $field) {
                    $field_names[$i] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $field);
                    $i += 1;
                }
                continue;
            }


            //$line = trim(fgets($handle));
            $arr = array();
            $field_values = explode(",", $line);
            $i = 0;
            foreach ($field_names as $field_name) {
                if (!isset($field_values[$i])) {$field_values[$i] = null;}
                $arr[$field_name] = $field_values[$i];
                $i += 1;
            }

            // If there is no selector array, just return it.
            if ($selector_array == null) {yield $arr;continue;}

            if (array_key_exists(0, $selector_array)) {
            } else {
                $selector_array = array($selector_array);
            }

            // Otherwise see if it matches the selector array.
            $match_count = 0;
            $match = true;
            foreach ($arr as $field_name=>$field_value) {

                //if ($selector_array == null) {$matches[] = $iteration; continue;}

                // Look for all items in the selector_array matching
                if ($selector_array == null) {continue;}

                foreach ($selector_array as $selector) {
                    //var_dump($selector_array);

                    foreach ($selector as $selector_name=>$selector_value) {

                        if ($selector_name != $field_name) {continue;}

                        if ($selector_value == $field_value) {

                            $match_count += 1;
                        } else {
                            $match = false;
                            break;
                        }
                    }
                }

            }

            if ($match == false) {continue;}

            yield $arr;

        }

        fclose($handle);

        $this->thing->log('nextGtfs took ' . number_format($this->thing->elapsed_runtime() - $split_time) . 'ms.');



    }


    /**
     *
     * @param unknown $selector_array (optional)
     */
    function isMatch($selector_array = null) {



    }


    /**
     *
     * @param unknown $file_name
     * @param unknown $index_name (optional)
     * @return unknown
     */
    function getGtfs($file_name, $index_name = null) {
        //$file_name = "stops.txt";
        // Load in data files
        //       $searchfor = strtoupper($this->search_words);
        //$searchfor = "MAIN HASTINGS";
        $file = $GLOBALS['stack_path'] . 'resources/translink/' . $file_name . '.txt';

        /*
        $contents = file_get_contents($file);
        $lines = explode("\n", $contents); // this is your array of words $line

        $field_names = explode(",",$lines[0]);

        // Tidy up headers
        // https://gist.github.com/josephspurrier/8780545
        $i = 0;
        foreach ($field_names as $field){
            $field_names[$i] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $field);
            $i += 1;

        }
*/

        $handle = fopen($file, "r");

        //$this->{$file_name} = array();
        $output_array = array();

        if ($handle) {
            $line_number = 0;
            while (($line = fgets($handle)) !== false) {
                $line_number += 1;

                if ($line_number == 1) {
                    $i = 0;
                    $field_names = explode(",", $line);

                    foreach ($field_names as $field) {
                        $field_names[$i] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $field);
                        $i += 1;
                    }

                    $i = 0;
                    $field_index = 0;
                    foreach ($field_names as $field_name) {
                        if (($field_name == $index_name) or ($index_name == null)) {$field_index = $i;break;}
                        $i += 1;
                    }

                    continue;
                }

                $field_values = explode(",", $line);

                //$field_index = 0;
                $arr = array();
                $i = 0;
                foreach ($field_names as $field_name) {
                    if (!isset($field_values[$i])) {$field_values[$i] = null;}
                    $arr[$field_name] = $field_values[$i];
                    $i += 1;
                }

                $field_index_value = $field_values[$field_index];
                if (!isset($output_array[$field_index_value])) {$output_array[$field_index_value] = array();}
                $output_array[$field_index_value][] = $arr;

                //if ($file_name == "stop_times") {var_dump($field_index_value);var_dump($arr);echo "foo";exit();}

                //$line_number += 1;
                // process the line read.
            }

            fclose($handle);

        } else {
            // error opening the file.
        }

        //if ($file_name == "stop_times") {var_dump($field_index_value);var_dump($arr);echo "foo";exit();}

        return $output_array;
    }


    /**
     *
     */
    function gtfsInfo() {
        $this->sms_message = "GTFS";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}
        $this->sms_message .= " | ";
        $this->sms_message .= 'Analysis of the TransLink network data files. | https://developer.translink.ca/ | ';
        $this->sms_message .= "TEXT HELP";

        return;
    }


    /**
     *
     */
    function translinkHelp() {

        $this->sms_message = "TRANSIT";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}
        $this->sms_message .= " | ";
        $this->sms_message .= 'Text the five-digit stop number for live Translink stop inforation. | For example, "51380". | ';
        $this->sms_message .= "TEXT <5-digit stop number>";

    }


    /**
     *
     */
    function translinkSyntax() {

        $this->sms_message = "GTFS";
        //                      if (count($t) > 1) {$this->sms_message .= "ES";}
        $this->sms_message .= " | ";
        $this->sms_message .= 'Syntax: "51380". | ';
        $this->sms_message .= "TEXT HELP";

    }

    // -----------------------

    /**
     *
     * @return unknown
     */
    public function respond() {
        //$this->thing->log('Agent "Translink". Start Respond. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');
        // Thing actions
        $this->thing->flagGreen();


        $this->thing_report['choices'] = false;
        $this->thing_report['info'] = 'SMS sent';

        // Generate email response.

        $to = $this->thing->from;
        $from = "station";

        $this->thing_report['info'] = 'This is the Station Agent responding to a request.';


        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        return $this->thing_report;
    }


    /**
     *
     * @param unknown $phrase
     */
    private function nextWord($phrase) {


    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {
/*
$mem_var = new \Memcached; //point 2.
$mem_var->addServer("127.0.0.1", 11211); 
$mem_var->set('gtfs-translink',  0.5); //point 3
//later:
$num = $mem_var->get('gtfs-translink'); //point 4.

var_dump($num);

exit();


echo "merp";
exit();
*/
        $this->thing->log("reading subject.");

        $this->response = null;


        $keywords = array('stop', 'bus', 'route');

        if ($this->agent_input == null) {
            $input = strtolower($this->subject);
        } else {
            $input = strtolower($this->agent_input);
        }
        //var_dump($input);
        //exit();

        $input = str_replace("gtfs " , "", $input);
        $arr =  $this->findStop($input);

        $count = 0;
        if (is_array($arr)) {$count = count($arr);}

        if ($count > 1) {
            $m = "";
            $temp_array = array();
            foreach ($arr as $stop) {

                $station_id = $stop['stop_id'];
                $this->stations[$station_id] = array("visited"=>false, "station_id"=>$station_id);
                //var_dump($stop);
                $this->places[$stop['stop_desc']][$stop['stop_id']] = $stop;

                $temp_array[$stop['stop_desc']][] = $stop['stop_code'];
            }

            foreach ($temp_array as $stop_desc=>$stops) {
                $m .= trim(implode(" " , $stops)) . " " .$stop_desc . " / " ;
            }

            //$this->places = $temp_array;

            /*
            foreach ($arr as $stop) {
                $m .= $stop['stop_code'] . " " . $stop['stop_desc'] . " /  ";
            }
*/
            $this->message = $m;
            return;
        }
        //var_dump($arr);
        if (($arr != null) and (count($arr) == 1)) {
            $this->station_id = $arr[0]['stop_id'];
            $m = $arr[0]['stop_code'] . " " . $arr[0]['stop_desc'];
            $this->message = $m . " | " . "http://www.transitdb.ca/stop/" . $arr[0]['stop_code'] ."/ | TEXT WEB";
            return;
        }


        $number = new Number($this->thing, $input);

        if (isset($number->numbers[0])) {

            $transit_id = $number->numbers[0];
            $station_id =  $this->idStation($transit_id);

            if ($station_id == null) {
                $this->message = "No match found.";
                $this->response = "No match found.";
                return;
            }

            $this->getStation($station_id);
            $this->station_id = $station_id;

            $this->response = "Collected stop information within 10 hops of this stop.";
            return;
        }


        $m = "No matching stops found.";
        $this->message = $m;
        return;

        ////        $stop_id = $this->idStation($text);


        return;
        //exit();

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));


        if (count($pieces) == 1) {

            $input = $this->subject;

            if (ctype_alpha($this->subject[0]) == true) {
                // Strip out first letter and process remaning 4 or 5 digit number
                $input = substr($input, 1);
                if (is_numeric($input) and strlen($input) == 4 ) {
                    return $this->busTranslink($input);
                    //return $this->response;
                }

                if (is_numeric($input) and strlen($input) == 5 ) {
                    return $this->busTranslink($input);
                    //return $this->response;
                }


                if (is_numeric($input) and strlen($input) == 6 ) {
                    return $this->busTranslink($input);
                    //return $this->response;
                }



            }

            if (is_numeric($this->subject) and strlen($input) == 5 ) {
                return $this->stopTranslink($input);
                //return $this->response;
            }

            if (is_numeric($this->subject) and strlen($input) == 4 ) {
                return $this->busTranslink($input);
                //return $this->response;
            }



            //                        return "Request not understood";

        }


        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {

                    switch ($piece) {
                    case 'stop':

                        if ($key + 1 > count($pieces)) {
                            //echo "last word is stop";
                            $this->stop = false;
                            return "Request not understood";
                        } else {
                            //echo "next word is:";
                            //var_dump($pieces[$index+1]);
                            $this->stop = $pieces[$key+1];
                            $this->response = $this->stopTranslink($this->stop);
                            return $this->response;
                        }
                        break;

                    case 'bus':

                        //echo 'bus';
                        break;

                    case 'translink':
                        $this->translinkInfo();
                        return;

                    case 'info':
                        $this->translinkInfo();
                        return;

                    case 'information':
                        $this->translinkInfo();
                        return;

                    case 'help':
                        $this->translinkHelp();
                        return;

                    case 'syntax':
                        $this->translinkSyntax();
                        return;


                    default:

                        //echo 'default';

                    }

                }
            }

        }
        $this->nullAction();
        return "Message not understood";
    }



}

