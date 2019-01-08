<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//ini_set('memory_limit', '1024M');

ini_set("allow_url_fopen", 1);

class Translink
{

	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
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

		// Allow for a new state tree to be introduced here.
		$this->node_list = array("start"=>array("useful", "useful?"));

		$this->thing->log('Agent "Translink" running on Thing ' . $this->thing->nuuid . '.');
		$this->thing->log('Agent "Translink" received this Thing "' . $this->subject .  '".');

//		$this->readSubject(); // No need to read subject 'translink' is pretty clear.
        //$this->thing->log('Agent "Translink". Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');


// Use this to create routes from a given stop
// devstack NRWTaylor 28 July 2018
        //$this->getEvents();

        $this->getNetworktime();
//echo $this->network_time_string;
//exit();
        $this->readSubject();

        $this->getStop($this->stop);



        $this->gtfs = new Gtfs($this->thing, "gtfs");
        $stop_id = $this->gtfs->idStation(51380);
        $stop = $this->gtfs->getStop($stop_id);
        $this->stop_name = $stop['stop_name'];


  		$this->respond();

		$this->thing->log('Agent "Translink" ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');
		return;

    }

    public function nullAction()
    {

        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable( array("character", "action"), 'null' );


        $this->message = "TRANSIT | Request not understood. | TEXT SYNTAX";
        $this->sms_message = "TRANSIT | Request not understood. | TEXT SYNTAX";
        $this->response = true;
        return $this->message;
    }

    function getStopid()
    {


    }

    function getStop($text)
    {
        $this->gtfs = new Gtfs($this->thing, "gtfs");
        $stop_id = $this->gtfs->idStation($text);
        $stop = $this->gtfs->getStop($stop_id);
        $this->stop_name = $stop['stop_name'];
    }

    function get($file_name, $selector_array = null)
    {
/*
        echo "Getting " . $file_name . "\n";

        $matches = array();
        $iterator = $this->nextGtfs($file_name, $selector_array);

        foreach ($iterator as $iteration) {
            $matches[] = $iteration;
        }
*/
/*
        foreach ($iterator as $iteration) {
            //echo $iteration;

            //var_dump($iteration);


            foreach ($iteration as $field_name=>$field_value) {

                if ($selector_array == null) {$matches[] = $iteration; continue;}

                foreach ($selector_array as $selector_name=>$selector_value) {
                    //echo $selector_name ." " . $selector_value . "\n";
                    //echo $field_name ." " . $field_value . "\n";
  
                    if (($selector_name == $field_name) and ($selector_value == $field_value)) {
                        $matches[] = $iteration;
                    }
              }

            $matches[] = $iteration;
//                    echo $field_name ." " . $field_value . "\n";

            }
//            $arr = explode(",",$iteration);
//            foreach ($arr as $key=>$value) {
//                echo $key ." ".$value . "\n";
//            }
            
        }
*/
//        var_dump($matches);
//exit();

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

    function getEvents()
    {
        // For testing
        // So this is too slow to present the full tuple map
        $stop_code =51380;
        $this->network_time = $this->network_time_string;
        //"15:43:33";

        $stops = $this->get("stops", array("stop_code"=>$stop_code));

        if (isset($stops[0])) {$stop_id = $stops[0]["stop_id"];}
        echo "Matched stop_code " . $stop_code . " to stop_id " .$stop_id . ".\n";
        $this->stop_id = $stop_id;
        // Information for this stop.  Generator/yield magic.
        $events = $this->nextGtfs("stop_times", array("stop_id"=>$stop_id));


        $stop_events = array();

        $trip_ids = array();
        // Information for this stop.


        // Generate the next event
        $visible = "off";
        for ($events = $this->nextGtfs("stop_times",array("stop_id"=>$stop_id)); $events->valid(); $events->next()) {

            $event = $events->current();

            $event_time_delta = $this->getTimeFromString($event['arrival_time']) - $this->getTimeFromString($this->network_time);
            $event['event_time_delta'] = $event_time_delta;

//            if ($event_time_delta < -30) {continue;}

            // And really only looking 2 hours into the future (transit pass validity)
//            $seconds_limit = 2 * 60 * 60;
//            if ($event_time_delta > $seconds_limit) {continue;}


            $event_stop_id = $event['stop_id'];
            $event_trip_id = $event['trip_id'];

 //           $event['event_time_delta'] = $event_time_delta;


//var_dump($event);
//exit();
            $stop_events[$event_stop_id][$event_trip_id] = $event;
//echo "[".$event_stop_id."]" . "[" . $event_trip_id ."]" . " --- ".implode(" " , $event) . "\n";
//var_dump($stop_events);
//exit();

/*
            $trip_events = $this->nextGtfs("stop_times", array("trip_id"=>$event_trip_id));
            $trip_event = $trip_events->current();


            $trip_event_stop_id = $trip_event['stop_id'];

            if ($trip_event_stop_id == $stop_id) {$visible = "on";}
//echo "foo";
//exit();

            if ($visible = "off") {continue;}
echo "foo";
exit();
                $arrival_time = $trip_event['arrival_time']; // Limit of one event per second, per trip, per stop.  Make more stops.

                // Calculate event variables
                if (!isset($shape_dist_traveled)) {$shape_dist_traveled = 0;}
                $shape_dist_traveled += floatval( $trip_event['shape_dist_traveled'] );

                if ((!isset($last_departure_time)) 
                    or ($last_departure_time == null)
                    or ($last_event_trip_id != $event_trip_id)) {
                    $trip_time_elapsed = 0;
                } else {
                    $trip_time_elapsed += strtotime($event['departure_time']) - strtotime($last_departure_time);
                }

                $trip_event['trip_time_elapsed'] = $trip_time_elapsed;
                $trip_event['shape_dist_traveled'] = $shape_dist_traveled;

                $last_event_trip_id = $event_trip_id;
                $last_departure_time = $trip_event['departure_time'];
                // Discrete
                $this->stops[strval($trip_event_stop_id)][strval($event_trip_id)][strval($arrival_time)] = $trip_event;
echo $event_stop_id . " " . $event_trip_id ." " . $arrival_time ." --- ".implode(" " , $trip_event) . "\n";
               // Now get the stops with can be reached from this stop.
                // Stops on trips which are after now.




echo "foo";
exit();

/*
            $trip_events = $this->nextGtfs("stop_times", array("trip_id"=>$event_trip_id));

            $visible = "off";
            for ($trip_events = $this->nextGtfs("stop_times",array("trip_id"=>$event_trip_id)); $trip_events->valid(); $trip_events->next()) {




                $trip_event = $trip_events->current();
//                $trip_event_stop_id = $trip_event['stop_id'];
                $trip_event_stop_id = $trip_event['stop_id'];

                if ($trip_event_stop_id == $stop_id) {$visible = "on";}
                if ($visible = "off") {continue;}

                $arrival_time = $trip_event['arrival_time']; // Limit of one event per second, per trip, per stop.  Make more stops.

                // Calculate event variables
                if (!isset($shape_dist_traveled)) {$shape_dist_traveled = 0;}
                $shape_dist_traveled += floatval( $trip_event['shape_dist_traveled'] );

                if ((!isset($last_departure_time)) 
                    or ($last_departure_time == null)
                    or ($last_event_trip_id != $event_trip_id)) {
                    $trip_time_elapsed = 0;
                } else {
                    $trip_time_elapsed += strtotime($event['departure_time']) - strtotime($last_departure_time);
                }

                $trip_event['trip_time_elapsed'] = $trip_time_elapsed;
                $trip_event['shape_dist_traveled'] = $shape_dist_traveled;

                $last_event_trip_id = $event_trip_id;
                $last_departure_time = $trip_event['departure_time'];
                // Discrete
                $this->stops[strval($trip_event_stop_id)][strval($event_trip_id)][strval($arrival_time)] = $trip_event;
echo $event_stop_id . " " . $event_trip_id ." " . $arrival_time ." --- ".implode(" " , $trip_event) . "\n";
               // Now get the stops with can be reached from this stop.
                // Stops on trips which are after now.

            }
*/
        }
//var_dump($stop_events);
//exit();
        // Now determine which stops are visible.
        // Based on network time and whether the trip has passed the stop.
        echo "Determining tripe events for first trip on the list :/" . "\n";
        //$this->network_time = "15:43:33";

  //      foreach ($this->stops as $stop) {
//            foreach ($stop as $trips) {
  //              foreach ($trips as $event) {
                    // Get all the trip events






                    $event_stop_id = $event['stop_id'];
                    $event_trip_id = $event['trip_id'];



                    // Can't go back to previous events / stops.

//                    $trip_events = $this->stops[$event_stop_id][$event_trip_id];


            // Generator to get the stops on the next trip
            $trip_events = $this->nextGtfs("stop_times", array("trip_id"=>$event_trip_id));
            $visible = "off"; // No events visible.
            foreach ($trip_events as $event) {

                $event_stop_id = $event['stop_id'];
                $event_trip_id = $event['trip_id'];

                $event['event_time_delta'] = (strtotime($event['arrival_time']) - strtotime($this->network_time));


                $stop_events[$event_stop_id][$event_trip_id] = $event;


            }

$this->events = $stop_events;
    return;
var_dump($stop_events);
exit();
//            $trip_events = $this->nextGtfs("stop_times", array("trip_id"=>$event_trip_id));


                    $visible = "off"; // No events visible.
                    foreach ($trip_events as $trip_event) {

                        if ($trip_event['stop_id'] == $stop_id) {$visible = "on";} // Until the event has happened.
/*
                        $event_time_delta = $this->getTimeFromString($event['arrival_time']) - $this->getTimeFromString($this->network_time);
                        // Because this doesn't work with > 24:
                        //$event_time_delta = (strtotime($event['arrival_time']) - strtotime($this->network_time));
                        //echo $event_time_delta;

                        switch (true) {
                            case ($event_time_delta < -60):
                                $tense = "past";
                                $visible = "off";
                                break;
                            case ($event_time_delta > +60):
                                $tense = "future";
                                break;
                            default:
                                $tense =  "now";
                       }
*/
                       if ($visible == "on") {
                           //echo $trip_event['arrival_time'] . $event_time_delta . " " . $tense .  "\n";
                           $this->visible_events[$trip_event['stop_id']][$trip_event['trip_id']] = $event;

                       }
                    }
      //          }
    //        }
    //    }

        $this->stop_events = $stop_events;
//var_dump($this->stop_events);
//exit();
        return;
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

    function getTrips($stop_id = null)
    {
        $trips = array();
        $trip_ids = array();

        //$trips = $this->nextGtfs("trips");
        // Information for this stop.
        // Generate the next event
        for ($trips = $this->nextGtfs("stop_times", array("stop_id"=>$stop_id)); $trips->valid(); $trips->next()) {

            $trip = $trips->current();
            //$route_id = $trip['route_id'];
            $trip_id = $trip['trip_id'];
            $stop_id = $trip['stop_id'];

            //$trip_headsign = $trip['trip_headsign'];

            $trip_ids[] = $trip_id;

            $trips[$trip_id][] = $stop_id;
        }
        $this->trip_stops = $trips;

    }

    function getRoutes()
    {
// I think this is where this forks to station 3 August 2018    

        $this->routes = array();
        $routes = $this->nextGtfs("routes");
        //$stop_events = array();
        $trip_ids = array();
        // Information for this stop.
        // Generate the next event
        $visible = "off";
        for ($routes = $this->nextGtfs("routes"); $routes->valid(); $routes->next()) {

            $route = $routes->current();
            $route_id = $route['route_id'];
            $this->routes[$route_id] = array("route_short_name"=>$route['route_short_name'],
                                    "route_long_name"=>$route['route_long_name']);
        }



    }

    function makeTxt()
    {
        if (!isset($this->events)) {$this->getEvents();}




$this->routes = array();
        $routes = $this->nextGtfs("routes");
        $stop_events = array();
        $trip_ids = array();
        // Information for this stop.
        // Generate the next event
        $visible = "off";
        for ($routes = $this->nextGtfs("routes"); $routes->valid(); $routes->next()) {

            $route = $routes->current();
            $route_id = $route['route_id'];
            $this->routes[$route_id] = array("route_short_name"=>$route['route_short_name'],
                                    "route_long_name"=>$route['route_long_name']);
        }

/* Get trips which pass through this stop.
        $this->getTrips($this->stop_id);
var_dump($this->trip_stops);
exit();
*/

// this is like runs
$this->trips = array();
$this->trip_ids = array();

        //$trips = $this->nextGtfs("trips");
        // Information for this stop.
        // Generate the next event
        for ($trips = $this->nextGtfs("trips"); $trips->valid(); $trips->next()) {

            $trip = $trips->current();
            $route_id = $trip['route_id'];
            $trip_id = $trip['trip_id'];
            $trip_headsign = $trip['trip_headsign'];

            $this->trip_ids[] = $trip_id;

            $this->trips[$trip_id] = array("route_id"=>$route_id,
                                    "trip_headsign"=>$trip_headsign);
        }
/*
var_dump($this->trip_ids);
exit();
*/
/*
        for ($events = $this->nextGtfs("stop_times"); $events->valid(); $events->next()) {

            $event = $events->current();

            $event_trip_id = $event['trip_id'];
        }
*/

//var_dump($this->routes);
//exit();
/*
function cmp($a, $b)
{
    return strcmp($a["event_time_delta"], $b["event_time_delta"]);
}
usort($this->events, "cmp");
*/


        $txt = "FUTURE EVENTS VISIBLE FROM THIS STOP " . $this->stop;
        $txt .= "\n";
        $txt .= $this->network_time;

        $txt .= "\n";



        //foreach($this->stops as $visible_stop) {
            //foreach($this->events as $event) {
                    //$txt .= $event['stop_code'] ;
$j = 0;
        foreach($this->events as $trips) {
            foreach ($trips as $event) {
                    $line = "";
//                    $event = $this->events[51380][9130758];

                    if ($event['event_time_delta'] < 0) {continue;}
                    if ($event['event_time_delta'] > 60 * 60 * 2) {continue;}

$trip_id = $event['trip_id'];
                    $line .= str_pad($trip_id, 10, " ", STR_PAD_LEFT) ;

$route_id = $this->trips[$event['trip_id']]['route_id'];
$trip_headsign = $this->trips[$event['trip_id']]['trip_headsign'];

                    $line .= str_pad($route_id, 12, " ", STR_PAD_LEFT) ;
                    $line .= str_pad($trip_headsign, 20, " ", STR_PAD_LEFT) ;


$route_array = $this->routes[$route_id];

            $route_short_name = $this->routes[$route_id]["route_short_name"];
            $route_long_name = $this->routes[$route_id]["route_long_name"];


//var_dump($route_id);
//var_dump($route_short_name);
//var_dump($route_long_name);


//exit();


                    $line .= str_pad($event['stop_id'], 7, " ", STR_PAD_LEFT) ;

                    $line .= str_pad("*".$route_short_name."*", 10, " ", STR_PAD_LEFT) ;
                    $line .= " ";
                    $line .= str_pad($route_long_name, 34, " ", STR_PAD_RIGHT) ;


                    $line .= str_pad($event['arrival_time'], 12, " ", STR_PAD_LEFT) ;
                    $line .= str_pad($event['departure_time'], 10, " ", STR_PAD_LEFT) ;

$event_time_delta = $event['event_time_delta'];

                    $line .= str_pad($this->thing->human_time($event['event_time_delta']), 15, " ", STR_PAD_LEFT) ;

                    //$txt .= str_pad($event['trip_time_elapsed'], 15, " ", STR_PAD_LEFT) ;
                    //$txt .= str_pad($event['shape_dist_traveled'], 15, " ", STR_PAD_LEFT) ; 
                    $line .= "\n";

                    if (!isset($lines[$event_time_delta])) {$lines[$event_time_delta] = array();}

                    $lines[$event_time_delta][] = $line;
//still devstack here

                    $trip_ids[] = $trip_id;

$find_stops = false;
if ($find_stops) {
$i = 0;
$j+=1;
       //$trip_events = $this->nextGtfs("stop_times", array("trip_id"=>$trip_id));
       for ($trip_events = $this->nextGtfs("stop_times",array("trip_id"=>$trip_id)); $trip_events->valid(); $trip_events->next()) {
if ($j>2) {break;}
            $trip_event = $trip_events->current();
$i+=1;
if ($j>=1) {break;}

if ($i>=1) {break;}

            $trip_event_time_delta = $this->getTimeFromString($trip_event['arrival_time']) - $this->getTimeFromString($this->network_time);
            $trip_event['event_time_delta'] = $trip_event_time_delta + $event_time_delta;

if ($trip_event['event_time_delta'] > 60 *5) {continue;}
            $stop_id = $trip_event['stop_id'];

            $lines[$trip_event_time_delta][] = "    " . $stop_id . " " . $trip_event['event_time_delta'] ."\n";

 echo "\n".$stop_id . " " . $trip_event['event_time_delta']. "\n";

       }
}
                }
//exit();
            }
ksort($lines);
//var_dump($lines);
//exit();

            foreach ($lines as $line_set) {
                foreach ($line_set as $line) {
                    $txt .= $line;




                }
            }

                    $txt .= "\n";
            //}
//        }


//        $txt .= "Test";

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;


    }

    function makeWeb()
    {
        $web = "<b>" . $this->stop . " " . ucwords($this->stop_name). "</b>";
        //$web .= "<br>" . $this->network_time_string . "<br>";

/*
        $stop_id = $this->gtfs->idStation($this->stop);
        $this->gtfs->getRoutes($stop_id);

        foreach (        $this->gtfs->routes[$stop_id] as $route_id=>$route) {
            $web .= "<br>" . $route['route_short_name'] . " " . $route['route_long_name'] . "";
        }

        //var_dump($this->gtfs->getStop($stop_id));

        if (!isset($this->gtfs->trips[$stop_id])) {$this->gtfs->getTrips($stop_id);}
        $trips =  $this->gtfs->trips[$stop_id];
*/


        $web .= "<p><br>" . $this->sms_message;
        $this->thing_report['web'] = $web;



    }
/*
        $txt = "";
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
//var_dump($file);
//exit();

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

$this->getStop($this->stop);

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

//            $this->sms_message = ucwords($this->stop_name);

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



    public function busTranslink($bus_id)
    {
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
        $this->thing_report['choices'] = false;
        $this->thing_report['info'] = 'SMS sent';




  //              $this->thing_report['email'] = array('to'=>$this->from,
  //                              'from'=>'transit',
  //                              'subject' => $this->subject,
  //                              'message' => $message, 
  //                              'choices' => false);




		// Generate email response.

		$to = $this->thing->from;
		$from = "transit";

		//$message = $this->readSubject();

		//$message = "Thank you for your request.<p><ul>" . ucwords(strtolower($response)) . '</ul>' . $this->error . " <br>";

// This is running at 20s...
//		$this->thing->choice->Create($this->agent_name, $this->node_list, "start");
//		$choices = $this->thing->choice->makeLinks('start');
//		$this->thing_report['choices'] = $choices;


		// Need to refactor email to create a preview of the sent email in the $thing_report['email']
		// For now this attempts to send both an email and text.

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

// And then at this point if Mordok is on?
// Run an hour train.
//$thing = new Mordok($this->thing);
//If Mordok is on.  Then allow starting of a train automatically.
//        if (strtolower($thing->state) == "on") {

//            $thing = new Transit($this->thing, "transit " . $this->stop);
//        }

//	$this->thing_report['info'] = 'This is the translink agent responding to a request.';
	    $this->thing_report['help'] = 'Connector to Translink API.';

        //$this->thing->log('Agent "Translink". End Respond. Timestamp ' . number_format($this->thing->elapsed_runtime()) . 'ms.');
        //$this->makeTxt();
        $this->makeweb();

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

