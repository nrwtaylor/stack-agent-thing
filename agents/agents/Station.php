<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//ini_set('memory_limit', '1024M');

ini_set("allow_url_fopen", 1);

class Station
{

	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {

        $this->channel = new Channel($thing, "channel");
        $this->channel_name = $this->channel->channel_name;

        // Handle stations specifically.
        // And if the context is Transit handle Translink Stops.
        $this->context = new Context($thing, "context");
        // 1 word messages don't action.

        $this->transit = new Transit($thing,"translink");


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

        $this->agent_input = $agent_input;

		$this->thing = $thing;
		$this->agent_name = 'translink';

        $this->thing_report['thing'] = $thing;

        $this->start_time = $this->thing->elapsed_runtime();


		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

		$this->api_key = $this->thing->container['api']['translink'];

		$this->retain_for = 2; // Retain for at least 2 hours.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;



		$this->sqlresponse = null;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];


		// Allow for a new state tree to be introduced here.
		$this->node_list = array("start"=>array("useful", "useful?"));

		$this->thing->log('Agent "Translink" running on Thing ' . $this->thing->nuuid . '.');
		$this->thing->log('Agent "Translink" received this Thing "' . $this->subject .  '".');

$this->max_hops = 2;

        $this->getNetworktime();
        //$this->getStations(); // Get the stops served directly by this stop.
        $this->getDestinations(); // Get the stops available from this stop.  Availability includes runat.
        $this->readSubject();
  		$this->respond();
		$this->thing->log('Agent "Station" ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');


		return;

    }
/*
    function depr_getTrains()
    {
        if (!isset($this->routes)) {$this->getRoutes();}
        foreach($this->routes as $route_id=>$route) {
            // All the trains passing through this station
            $train = $this->get("trips", array("route_id"=>$route_id));
            $trains[$route_id] = $train;
        }
        $this->trains = $trains;

    }
*/

    function getRailway()
    {
        // Running in 15s.  4 Aug 2018.
        $split_time = $this->thing->elapsed_runtime();
        $this->thing->log ( "Making railway - transit context");
        //echo $this->thing->log();
//exit();
        // stop_times is a large file
        // this looks through and identifies all the blocks.
        // From one stop to the next.

        for ($channels = $this->nextGtfs("stop_times"); $channels->valid(); $channels->next()) {

            $channel = $channels->current();

            $station_id = $channel['stop_id'];
            $train_id = $channel['trip_id'];

            $stop_sequence = $channel['stop_sequence'];
            if ($stop_sequence == 1) {unset($last_station);}

            if (isset($last_station)) {
                $this->blocks[$last_station][$station_id] = $channel;
            }
            $last_station = $station_id;
        }
        $this->railway = $this->blocks;
        $this->thing->log('Made a railway in ' . number_format($this->thing->elapsed_runtime() - $split_time) . 'ms.');

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
        $stop_id = $this->idStation($text);

        // Make the networks
        $this->getRailway();
        $station_id = $stop_id; // Work in train context

        $visible_stations[$station_id] = array("visited"=>false,"station_id"=>$station_id);

        $completed = false;
        $hops = 0;

        $this->thing->log("Looking for visible stations.");

        while($completed == false) {
            $completed = true;
            foreach ($visible_stations as $visible_station_id=>$visible_station) {
                if ($visible_station['visited'] == false) {
                    $station_id_pointer = $visible_station_id;
                    $completed = false;
                    break;
                }
            }

            if ($completed == true) {return; echo "meep";exit();}
            //echo "\n";

            // Now visiting stations up from $station_id
            $stations =  $this->railway[$station_id_pointer];

            foreach ($stations as $station_id=>$station) {
                $visible_stations[$station_id] = array("visited"=>false,"station_id"=>$station_id,"station"=>$station);
                echo $station_id . " ";
                $completed = false;
            }

            $visible_stations[$station_id_pointer]['visited'] = true;
            $hops += 1;
            if ($hops > $this->max_hops) {break;}
        }

        echo "\n";

        $this->stations = $visible_stations;
        return $this->stations;
    }

    public function getDestinations()
    {
        $this->destinations = array(); // Fair enough.
    }

    public function nullAction()
    {

        $this->thing->json->setField("variables");

        $this->message = "TRANSIT | Request not understood. | TEXT SYNTAX";
        $this->sms_message = "TRANSIT | Request not understood. | TEXT SYNTAX";
        $this->response = true;
        return $this->message;
    }

    function idStation($text = null)
    {
        // Curiously one of the harder things to do.
        // dev create a CSV file when recognize version number has changed.

        // Transit context
        // Take text and recognize the id.
        $stop_code = 51380;

        $stops = $this->get("stops", array("stop_code"=>$stop_code));

        if (isset($stops[0])) {$stop_id = $stops[0]["stop_id"];}
        $this->thing->log("Matched stop_code " . $stop_code . " to stop_id " .$stop_id . ".");

        $this->station_id = $stop_id;
        return $this->station_id;
    }

    function get($file_name, $selector_array = null)
    {
        $this->thing->log("Getting " . $file_name . ".txt.");

        $matches = array();
        $iterator = $this->nextGtfs($file_name, $selector_array);

        foreach ($iterator as $iteration) {
            $matches[] = $iteration;
        }

        return $matches;
    }

    function makeStop($iteration)
    {
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

// To handle >24 hours.  Urgh:/
// https://stackoverflow.com/questions/12708419/strtotime-function-for-hours-more-than-24
function getTimeFromString($time){
    $time = explode(':', $time);
    return mktime($time[0], $time[1], $time[2]);
}


    function echoTuple()
    {
        // Deploy
        $stop_tuple = $this->stop_tuples['51380']['9130759']["27:54:00"];

        $txt = $stop_tuple['stop_id'];
        $txt .= $stop_tuple['trip_time_elapsed'];
        $txt .= $stop_tuple['shape_dist_traveled'];
        $txt .= $stop_tuple['departure_time'];

        echo $txt;
    }

    function getStation($station_id = null)
    {
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

    function tripRoute($station_id)
    {
        if (isset($this->trip_routes[$station_id])) {return $this->trip_routes;}

        for ($routes = $this->nextGtfs("trips", array("stop_id"=>$station_id)); $routes->valid(); $routes->next()) {
            $route = $routes->current();
            $this->trip_routes[$route['trip_id']] = $route['route_id'];
        }

        return $this->trip_routes;
    }

// This is ugly.

function searchForId($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['trip_id'] === $id) {
           return $key;
       }
   }
   return null;
}

function searchForsId($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['stop_id'] === $id) {
           return $key;
       }
   }
   return null;
}

function searchForrId($id, $array) {
   foreach ($array as $key => $val) {
       if ($val['route_id'] === $id) {
           return $key;
       }
   }
   return null;
}


    function getRoutes($station_id)
    {

        $this->tripRoute($station_id); // trip_routes (quick trip to route conversion)

        $this->split_time = $this->thing->elapsed_runtime();

        $this->thing->log('Agent "Station" is gettings routes for ' . $station_id .'.');

        // This is slow
        if (!isset($this->trips[$station_id])) {$this->getTrips($station_id);}
        if (!isset($this->routes[$station_id])) {$this->routes[$station_id] = array();}

        if (!isset($this->trips_db)) {$this->trips_db = $this->get("trips");}


        // For each trip_id get the route
        foreach($this->trips[$station_id] as $trip_id) {

            // Translate trip_id to route_id
            $route_id =  $this->trip_routes[$trip_id];

            // Have we processed it?
            if (isset($this->routes[$station_id][$route_id])) {continue;}


            $index = $this->searchForrId($route_id, $this->trips_db);
            $route = $this->trips_db[$index];

            $route_id = $route['route_id'];

            $this->routes[$station_id][$route_id] = $route;

//            echo ".";
            $this->thing->log('Got station ' . $station_id . ' and route ' . $route_id . ".");

        }

        echo "\n";
        $this->thing->log('Got stations.');

        return $this->routes[$station_id];

    }

    function getTrips($station_ids)
    {
        //echo "\n";
        $this->thing->log("Getting trips passing through " . $station_ids . ".");
        //if (isset($this->trips[$station_ids])) {return $this->trips[$station_ids];}



        // stop times is 80Mb
        $selector_array = array("stop_id"=>$station_ids);

        //foreach($this->stations as $station_id=>$station) {
        //    if (!isset($this->trips[$station_id])) {$this->trips[$station_id] = array();}
        //    $selector_array[] = array("stop_id"=>$station_id);
        //}

        for ($stops = $this->nextGtfs("stop_times", $selector_array); $stops->valid(); $stops->next()) {

            $stop = $stops->current();
//var_dump($stop);
            $trip_id = $stop['trip_id'];

            $this->trips[$station_ids][] = $stop['trip_id'];

        }
//echo "meep";
//exit();
        return $this->trips[$station_ids];
    }

    function makeSms()
    {
        //if ((isset($this->stations)) and (is_array($this->stations))) {$count = count($this->stations);} 
        $sms = "STATION | " . "A work in progress. Text TXT.";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;

    }

    function makeTxt()
    {
        if (!isset($this->stations)) {$this->getStations();}

        $txt = "FUTURE STOPS VISIBLE FROM THIS STATION " . $this->station_id;
        $txt .= "\n";
        //$txt .= $this->network_time;

        $txt .= "\n";

//        if (!isset($this->routes)) {$this->getRoutes(array("stop_id"=>$this->station_id));}

        foreach($this->stations as $station_id=>$station) {
            if ($station['visited']) {

            $txt .= "[".$station_id ."] ";
            } else {
                $txt .= $station_id ." ";

            }
        }
$j = 0;

        foreach($this->stations as $station_id=>$station) {

  //          $this->getRoutes(array("stop_id"=>$station_id));

            $this->split_time = $this->thing->elapsed_runtime();



            $stop_id =  ($station['station']['stop_id']);

            $next_stop_distance = $station['station']['shape_dist_traveled'];

            //if ($this->thing->elapsed_runtime() > (20000)) {
            //    break;
            //}

            $next_station = $this->getStation($stop_id);
            //$this->stations[$stop_id]; // functionally equivalent


            // Create text block for routes served at a specific stop
            $r = "";
            foreach ($next_station['routes'] as $route) {
                $r .= $route['trip_headsign'] ." ";
            }
            $r = "\n";

            // Create text block for static information about stop
            $next_station_id = $next_station['stop_id'];
            $next_station_code = $next_station['stop_code'];
            $next_station_desc = $next_station['stop_desc'];
            $next_station_lat = $next_station['stop_lat'];
            $next_station_long = $next_station['stop_lon'];

            //$next_trip_id = $next_station['trip_id'];

            //$this->getRoutes($station_id);
            //$this->trains
            //$this->trips

//            $station = $this->getStation($station_id);

            $line = $station_id . "  ".$next_stop_distance . " " . $next_station_id ." ". $next_station_desc . " " . $next_station_lat . " " . $next_station_long ."\n";

         $txt .= $line;
         $txt .= $r . "\n";
        $this->thing->log ($line);
        $this->thing->log ($r);

            // Get a least one station
            if ($this->thing->elapsed_runtime() > (20000)) {
                break;
            }


            //$last_station_id = $next_station_id;

        }
        $this->thing_report['txt'] = $txt;
    }

    function makeWeb()
    {
        $web = "meep";
        $this->thing_report['web'] = $web;

    }
/*
        $txt = "";
echo "meep";
        foreach ($this->stop_tuples as $stop_tuple) {
            $txt .= $stop_tuple['trip_time_elapsed']. " " . $stop_tuple['shape_dist_traveled']. " " . $stop_tuple['departure_time'];
            $txt .= "\n"; 
        }
*/

    function getNetworktime()
    {
        $agent = new Clocktime($this->thing,"now");
        $this->network_time_string = str_pad($agent->hour, 2, "0",STR_PAD_LEFT).":".str_pad($agent->minute,2,"0",STR_PAD_LEFT).":"."00";

    }

    function departuretimeNetwork($departure_time_text = null)
    {
        //$this->network_time_string = "20:07:00";
        if (!isset($this->network_time_string)) {$this->network_time_string = "16:01:00";}
        $network_time = strtotime($this->network_time_string);
        $departure_time = strtotime($departure_time_text);
        if ($departure_time < $network_time) {$departure_time = false;}

        return $departure_time;
        // RED - Trip hasn't been seen at the stop yet.

    }

    function nextGtfs($file_name, $selector_array = null)
    {
        $file = $GLOBALS['stack_path'] . 'resources/translink/' . $file_name . '.txt';

        $handle = fopen($file, "r");
        $line_number = 0;

if ($handle == false) {return true;}

        while(!feof($handle)) {
            $line = trim(fgets($handle));
            $line_number += 1;

            // Get headers
            if ($line_number == 1) {
                $i = 0;
                $field_names = explode(",",$line);

                foreach ($field_names as $field){ 
                    $field_names[$i] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $field);
                    $i += 1;
                }
                continue;
            }


            //$line = trim(fgets($handle));
            $arr = array();
            $field_values = explode(",",$line);
            $i = 0;
            foreach($field_names as $field_name) {
                if (!isset($field_values[$i])) {$field_values[$i] = null;}
                $arr[$field_name] = $field_values[$i];
                $i += 1;
            }


/*
            //$field_index = 0;
            $arr = array();
            $i = 0;
            foreach ($field_names as $field_name) {
                if (!isset($field_values[$i])) {$field_values[$i] = null;}
                    $arr[$field_name] = $field_values[$i];
                    $i += 1;
                }

//                $field_index_value = $field_values[$field_index];
//                if (!isset($output_array[$field_index_value])) {$output_array[$field_index_value] = array();}
//                $output_array[$field_index_value][] = $arr;
            }
*/

            if ($selector_array == null) {yield $arr;}


            $match_count = 0;
            $match = true;
            foreach ($arr as $field_name=>$field_value) {

                //if ($selector_array == null) {$matches[] = $iteration; continue;}

                // Look for all items in the selector_array matching
                //$match_count = 0;
                //$match = true;
                if ($selector_array == null) {continue;}

                foreach ($selector_array as $selector_name=>$selector_value) {

                    if ($selector_name != $field_name) {continue;}

//                    echo $selector_name ." " . $selector_value . "\n";
//                    echo $field_name ." " . $field_value . "\n";
//                    echo "\n";

                    if ($selector_value == $field_value) {

                      $match_count += 1;
                    } else {
                        $match = false; 
                        break;
                    }

                }

            }

            if ($match == false) {continue;}

            yield $arr;

        }

        fclose($handle);
    }


    function getGtfs($file_name, $index_name = null)
    {


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
                    $field_names = explode(",",$line);

                    foreach ($field_names as $field){ 
                        $field_names[$i] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $field);
                        $i += 1;
                    }

                    $i = 0;
                    $field_index = 0;
                    foreach($field_names as $field_name) {
                        if (($field_name == $index_name) or ($index_name == null)) {$field_index = $i;break;}
                        $i += 1;
                    }

                    continue;
                }

                $field_values = explode(",",$line);

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

    function translinkInfo()
    {


                        $this->sms_message = "TRANSIT";
//                      if (count($t) > 1) {$this->sms_message .= "ES";}
                        $this->sms_message .= " | ";
                        $this->sms_message .= 'Live data feed provided through the TransLink Open API. | https://developer.translink.ca/ | ';
                        $this->sms_message .= "TEXT HELP";

                return;


        }

        function translinkHelp() {

                        $this->sms_message = "TRANSIT";
//                      if (count($t) > 1) {$this->sms_message .= "ES";}
                        $this->sms_message .= " | ";
                        $this->sms_message .= 'Text the five-digit stop number for live Translink stop inforation. | For example, "51380". | ';
                        $this->sms_message .= "TEXT <5-digit stop number>";
                return;


        }

    function translinkSyntax()
    {

        $this->sms_message = "TRANSIT";
//                      if (count($t) > 1) {$this->sms_message .= "ES";}
        $this->sms_message .= " | ";
        $this->sms_message .= 'Syntax: "51380". | ';
        $this->sms_message .= "TEXT HELP";

        return;
    }


	public function stopTranslink($stop)
    {
        $split_time = $this->thing->elapsed_runtime();
        //$this->thing->log('Agent "Translink". Start Translink API call. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');


		$this->stop = $stop;
		try {

			$file = 'http://api.translink.ca/rttiapi/v1/stops/'.$stop .'/estimates?apikey='. $this->api_key . '&count=3&timeframe=60';

			$web_input = file_get_contents('http://api.translink.ca/rttiapi/v1/stops/'.$stop .'/estimates?apikey='. $this->api_key . '&count=3&timeframe=60');


			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $file);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$xmldata = curl_exec($ch);
			curl_close($ch);

			$web_input = $xmldata;

			$this->error = "";

		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			$this->error = $e;
			$web_input = false;
            $this->sms_message = "Request not understood: " . $this->error;

			return "Request not understood";
		}

		//echo $web_input;


                $xml = simplexml_load_string($web_input);  
                $t = $xml->NextBus;

                //var_dump($xml);
                $json_data = json_encode($t,true);
                //echo $json_data;

                $response = null;

                foreach($t as $item) {
  $response .= '<li>' . $item->Schedules->Schedule->ExpectedLeaveTime . ' ' . $item->RouteNo . ' ' . $item->RouteName . ' ' . '> ' . $item->Schedules->Schedule->Destination . '</li>';
                }

                $message = "Thank you for your request for stop " . $stop .".  The next buses are: <p><ul>" . ucwords(strtolower($response)) . '</ul>';
		$message .= "";
		$message .= "Source: Translink real-time data feed.";


// Hacky here to be refactored.
// Generate a special short SMS message

$this->sms_message = "";
$response ="";

                foreach($t as $item) {
 // $response .=  $item->Schedules->Schedule->ExpectedLeaveTime . ' ' . $item->RouteNo . '> ' . $item->Schedules->Schedule->Destination . ' | ';

  $response .=  $item->RouteNo . ' ' . $item->Schedules->Schedule->ExpectedLeaveTime . ' > ' . $item->Schedules->Schedule->Destination . ' | ';

                }



                	$this->sms_message = "NEXT BUS";
			if (count($t) > 1) {$this->sms_message .= "ES";}

			$this->sms_message .= " | ";


			// Sometimes Translink return 
			// a date in the time string.  Remove it.

			$input = $response;
			//$input = "Current from 2014-10-10 to 2015/05/23 and 2001.02.10";
			$output = preg_replace('/(\d{4}[\.\/\-][01]\d[\.\/\-][0-3]\d)/', '', $input);

			//echo $output;

			if (count($t) == 0) {
				$this->sms_message .= "No information returned for stop " . $this->stop . ' | ';
			} else {
				$this->sms_message .= ucwords(strtolower($output))  ;
			}

            $this->sms_message .= "Source: Translink | ";

			$this->sms_message .= "TEXT ?";

        $this->thing->log('Agent "Translink". Translink API call took ' . number_format($this->thing->elapsed_runtime() - $split_time) . 'ms.');


		return $message;
	}



        public function busTranslink($bus_id) {

                try {

                        $file = 'http://api.translink.ca/rttiapi/v1/buses/' . $bus_id . '?apikey=' . $this->api_key;

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
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
                        $this->error = $e;
                        $web_input = false;
			return "Bus information not yet supported";
                }

		$message = "Here is some xml information" . $web_input;
		$this->sms_message = "TRANSIT | Bus number service not implemented.";
		$this->message = "A bus number was provided, but the agent cannot yet respond to this.";
                //echo $web_input;
                return $message;
        }





// -----------------------

	private function respond()
    {
        //$this->thing->log('Agent "Translink". Start Respond. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');
		// Thing actions
		$this->thing->flagGreen();


        $this->thing_report['sms'] = $this->sms_message;

        $this->makeSms();

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

//        if (strtolower($thing->state) == "on") {
//            $thing = new Transit($this->thing, "transit " . $this->stop);
//        }

	    $this->thing_report['help'] = 'This agent is developmental (and slow ~160,000ms).  See what you think.  Let me know at ' . $this->email . ".";

        $this->makeWeb();

//var_dump($this->channel->channel_name);
//exit();
//        if ($this->channel->channel_name == "web") {return;}

        $this->makeTxt(); // Do last because this needs some processing.

		return $this->thing_report;
	}

	private function nextWord($phrase)
    {


	}

	public function readSubject()
    {

		$this->response = null;

		$keywords = array('stop', 'bus', 'route');

		$input = strtolower($this->subject);

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
				if (strpos(strtolower($piece),$command) !== false) {

					switch($piece) {
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





/*
        $this->matched_lines = array();
        foreach ($lines as $line) {
            // if(preg_match('(MAIN|HASTINGS)', $line) === 1) { // echo $line; // 
            //$matches[] = $line; // $count += 1; // }

            //$needles = array('MAIN','HASTINGS');
            $needles = explode(" ",$searchfor);

            $regex='/(?=.*?'.implode(')(?=.*?', $needles).')/s';
            if (preg_match($regex,$line)===1) {
                //  echo 'true';
                $this->matched_lines[] = $line;
                //echo $line;
                //echo "<br>";
            }
        }
*/

?>
