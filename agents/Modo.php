<?php
/**
 * Modo.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Modo extends Agent
{

    // This gets events from the Modo API.

    public $var = 'hello';


    /**
     *
     */
    function init() {
        $this->agent_name = "modo";
        $this->keyword = "modo";
        $this->test= "Development code"; // Always

        $this->keywords = array('modo', 'meet-up', 'car', 'show', 'happening');

        $this->run_time_max = 360; // 5 hours

        $this->variables_agent = new Variables($this->thing, "variables " . "modo" . " " . $this->from);

        //   $this->getModo();
        $this->thing_report['help'] = "Reads the state of the Modo co-op car fleet. Try MODO 554. Or MODO 470 GRANVILLE VANCOUVER.";
    }


    /**
     *
     */
    function run() {

        $this->make();
    }


    /**
     *
     */
    function make() {

        $this->makeMessage();
        $this->makeSms();
        $this->makeWeb();
        $this->thingreportModo();


    }


    /**
     *
     */
    function set() {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);
    }


    /**
     *
     */
    function get() {
        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        $this->thing->log('loaded ' . $this->counter . ".", "DEBUG");

        $this->counter = $this->counter + 1;
    }


    /**
     *
     * @param unknown $sort_order (optional)
     * @return unknown
     */
    function getModo($sort_order = null) {
        if ($sort_order == null) {$sort_order = "popularity";}

        $city = "vancouver";
        $c = new City($this->thing, "city");
        $city = $c->city_name;

        // "America/Vancouver" apparently
        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        //$keywords = str_replace(" ", "%20%", $keywords);

        $keywords = urlencode($keywords);


        $data_source = "https://bookit.modo.coop/api/v2/car_list";

        //$data = file_get_contents($data_source, NULL, NULL, 0, 4000);

        $data = file_get_contents($data_source);
        if ($data == false) {
            $this->response = "Could not ask Modo.";
            $this->available_cars_count = 0;
            $this->cars_count = 0;
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, TRUE);
        $total_items = $json_data['Response']['CarCount'];

        $this->thing->log('got ' . $total_items . " Modo things.");

        $this->available_cars_count = $total_items;

        $modo_cars = $json_data['Response']['Cars'];

        $this->carsModo($modo_cars); // Custom function to match Meetup API variables to Events.
        return false;
    }


    /**
     *
     * @return unknown
     */
    public function respondResponse() {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        $to = $this->thing->from;
        $from = "modo";

        //$this->makeMessage();
        //$this->makeSms();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        //$this->makeWeb();


        $this->thing_report['sms'] = $this->sms_message;

        return $this->thing_report;
    }


    /**
     *
     * @param unknown $text (optional)
     */
    function getCars($text = null) {

        //        if (!isset($this->neighbourhood)) {return;}
        if (!isset($this->cars)) {$this->getModo();}

        if (!isset($this->neighbourhood)) {return;}


        //$this->cars[$modo_id] = array("description"=>$description, "runat"=>null, "runtime"=>null, "place"=>$locations[0], "li$
        $this->matches = array();


        foreach ($this->cars as $modo_id=>$car) {


            foreach ($car['locations'] as $i => $location) {
                //                if (strtolower($location['neighbourhood']) == strtolower($text)) {


                if (strtolower($location['neighbourhood']) == strtolower($this->neighbourhood)) {


                    $this->matches[$location['description']][$modo_id] = $car;

                }
            }
        }

    }


    /**
     *
     * @param unknown $car
     */
    function printCar($car) {

        echo $this->carString($car) . "\n";

    }


    /**
     *
     * @param unknown $text (optional)
     */
    function getCar($text = null) {

        if (!isset($this->cars)) {$this->getModo();}
        //$this->cars[$modo_id] = array("description"=>$description, "runat"=>null, "runtime"=>null, "place"=>$locations[0], "link"=>null);
        $this->matches = array();
        foreach ($this->cars as $modo_id=>$car) {
            foreach ($car['locations'] as $i => $location) {
                if (strtolower($location['neighbourhood']) == strtolower($text)) {$this->matches[$modo_id] = $car;}
            }
        }
    }


    /**
     *
     */
    function doModo() {

        if ( (!isset($this->cars)) or ($this->cars == null)) {$this->getCars();}
        $available_cars_count = 0;
        $in_use_count =0;
        $x = strtotime($this->current_time);
        foreach ($this->cars as $modo_id=>$car) {

            $available_flag = true;
            foreach ($car['locations'] as $i=>$location) {

                // Go through all the locations.
                // Is it currently in use?

                $start_time = $location['start_time'];
                $end_time = $location['end_time'];

                //echo $modo_id . " / " . $x . " / " . $start_time.  " / " . $end_time . " " ;

                if (($start_time != null) and ($start_time < $x) and ($end_time == null)) {$available_flag = false;} // Not available.
                if (($start_time < $x) and ($end_time > $x)) {$available_flag = false;} // Not available.

                if ($available_flag) {
                    //echo "available";
                } else {$in_use_count += 1;

                }

                if ($available_flag == false) {break;}
            }

            if ($available_flag) {$available_cars_count += 1;}
        }
        $total_cars_count = count($this->cars);
        $utilization = $in_use_count / $total_cars_count;
        $this->response = $in_use_count . "/" . $total_cars_count . " " .number_format($utilization * 100, 0) . "%. ";

    }


    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     *
     * @param float   $latitudeFrom  Latitude of start point in [deg decimal]
     * @param float   $longitudeFrom Longitude of start point in [deg decimal]
     * @param float   $latitudeTo    Latitude of target point in [deg decimal]
     * @param float   $longitudeTo   Longitude of target point in [deg decimal]
     * @param float   $earthRadius   (optional) Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    function haversineGreatCircleDistance(
        $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }


    /**
     *
     * @param unknown $text (optional)
     */
    function nearestCar($text = null) {

        if (!isset($this->cars)) {$this->getModo();}
        if ($text == null) {return;}
        if (is_array($text)) {
            $search_longitude = $text[0];
            $search_latitude = $text[1];

            //echo $search_latitude . "  " . $search_longitude ."\n";

        }
        //$this->cars[$modo_id] = array("description"=>$description, "runat"=>null, "runtime"=>null, "place"=>$locations[0], "link"=>null);

        $min_distance = 1e99;
        $this->matches = array();
        foreach ($this->cars as $modo_id=>$car) {

            foreach ($car['locations'] as $id=>$location) {
                $latitude = $location['latitude'];
                $longitude = $location['longitude'];

                //                $distance = pow(( pow($latitude - $search_latitude, 2) + pow($longitude - $search_longitude, 2)), 0.5);

                $distance = $this->haversineGreatCircleDistance($latitude, $longitude, $search_latitude, $search_longitude);
                $car['distance'] = $distance;
                if ($distance < $min_distance) {
                    $min_distance = $distance;
                    //echo $min_distance . "\n";
                    $this->nearest_cars[$distance] = $car;
                    //                if (strtolower($location['neighbourhood']) == strtolower($text)) {$this->matches[$modo_id] = $car;}
                }
            }

        }
        $this->nearest_car = $this->nearest_cars[$min_distance];
    }


    /**
     * public function respond() {
     * $this->thing->flagGreen();
     * $to = $this->thing->from;
     * $from = "modo";
     * $this->makeSMS();
     * $this->makeChoices();
     * $this->thing_report['message'] = $this->sms_message;
     * $this->thing_report['txt'] = $this->sms_message;
     * $message_thing = new Message($this->thing, $this->thing_report);
     * $thing_report['info'] = $message_thing->thing_report['info'] ;
     * return $this->thing_report;
     * }
     *
     * @return unknown
     */


    /**
     *
     * @param unknown $modo_cars
     */
    function carsModo($modo_cars) {
        if (!isset($this->cars)) {$this->cars = array();}
        if ($modo_cars == null) {$this->cars_count = 0;return;}

        foreach ($modo_cars as $id=>$car) {

            $description = $car['Make'] . " " . $car['Model'] . " " . $car['Colour'];

            $seats = $car['Seats'];
            $locations = array();
            $modo_id = $car['ID'];
            foreach ($car['Location'] as $i=>$location) {

                $location_id = $location['LocationID'];
                $this->getLocation($location_id);
                // Pulls in $this->location;



                $arr = array(
                    "name"=>$this->location['Name'],
                    "description" => $this->location['ShortDescription'],
                    "city" => $this->location['City'],
                    "neighbourhood" => $this->location['Neighbourhood'],
                    "latitude" => $this->location['Latitude'],
                    "longitude" => $this->location['Longitude'],
                    "start_time" => $location['StartTime'],
                    "end_time" => $location['EndTime']
                );

                foreach ($locations as $i=>$l) {



                    if ( (implode(" " , $arr)) == (implode(" " , $l)) ) {break;}

                }

                $locations[] = $arr;

                //              $this->cars[$modo_id] = array("description"=>$description, "runat"=>null, "runtime"=>null, "locations"=>$locations, "link"=>null);


            }
            $this->cars_count = count($this->cars);
            //            $this->events[$meetup_id] = array("event"=>$event_name, "runat"=>$run_at, "runtime"=>$runtime, "locations"=>$venue_name, "link"=>$link);
            $this->cars[$modo_id] = array("modo_id" => $modo_id, "description"=>$description, "quantity"=>$seats, "runat"=>null, "runtime"=>null, "locations"=>$locations, "link"=>$this->modoLink($modo_id));

        }

        $this->cars_count = count($this->cars);
    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function isNeighbourhood($text = null) {
        if (!isset($this->locations)) {$this->getLocations();}
        $matches = array();
        foreach ($this->locations as $i=>$location) {

            if (strtolower($location['Neighbourhood']) == strtolower($text)) {
                //echo "Is " . $location['Neighbourhood'] . "\n";
                return true;
            }

        }

        //$this->neighbourhoods = $matches;
        //if ((isset($matches)) and (count($matches) >= 1)) {return true;}
        return false;

    }


    /**
     *
     * @param unknown $text (optional)
     */
    function matchNeighbourhoods($text = null) {
        if (!isset($this->neighbourhoods)) {$this->getNeighbourhoods($text);}
        $this->neighbourhood_matches = array();
        //echo "merp";
        if (!isset($this->locations)) {$this->getLocations();}
        //     $matches = array();
        foreach ($this->locations as $i=>$location) {
            //var_dump($location);
            if (strpos($location['Neighbourhood'], strtolower($text)) !== false) {
                //            if (strtolower($location['Neighbourhood']) == strtolower($text)) {
                //echo $location['Neighbourhood'];
                $this->neighbourhood_matches[] = $location['Neighbourhood'];
                return;
            }

        }
        //$this->neighbourhood = null;
        //$this->neighbourhoods = $matches;
        //if ((isset($matches)) and (count($matches) >= 1)) {return true;}
        return;

    }


    /**
     *
     * @param unknown $text (optional)
     */
    function getNeighbourhood($text = null) {

        if (!isset($this->locations)) {$this->getLocations();}
        $matches = array();
        foreach ($this->locations as $i=>$location) {

            if (strtolower($location['Neighbourhood']) == strtolower($text)) {
                $this->neighbourhood = $location['Neighbourhood'];
                return;
            }

        }
        $this->neighbourhood = null;
        //$this->neighbourhoods = $matches;
        //if ((isset($matches)) and (count($matches) >= 1)) {return true;}
        return;

    }


    /**
     *
     * @return unknown
     */
    function getLocations() {

        //        if ($sort_order == null) {$sort_order = "popularity";}

        $city = "vancouver";
        $c = new City($this->thing, "city");
        $city = $c->city_name;

        // "America/Vancouver" apparently
        $keywords = "";
        if (isset($this->search_words)) {$keywords = $this->search_words;}

        //$keywords = str_re\place(" ", "%20%", $keywords);

        $keywords = urlencode($keywords);

        // Let's use meetup popularity...
        //        $data_source = "https://api.meetup.com/2/open_events.xml?format=json&and_text=true&text=" . $keywords . "&t$

        $data_source = "https://bookit.modo.coop/api/v2/location_list";
        //$data = file_get_contents($data_source, NULL, NULL, 0, 4000);
        $data = file_get_contents($data_source);

        if ($data == false) {
            $this->response = "Could not ask Modo.";
            $this->available_locations_count = 0;
            $this->locations_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, TRUE);

        //        $total_items = $json_data['meta']['total_count'];

        //        $this->thing->log('got ' . $total_items . " Event things.");

        //        $this->available_cars_count = $total_items;

        $this->locations = $json_data['Response']['Locations'];

        //        $this->getLocation($modo_location); // Custom function to match Meetup API variables to Events.
        return false;


    }


    /**
     *
     * @param unknown $text (optional)
     */
    function getLocation($text = null) {

        if (!isset($this->locations)) {$this->getLocations();}
        //$text = "clark";
        if (isset($this->locations[$text])) {$this->location = $this->locations[$text]; return;}

        $min_lev = 1e99;
        foreach ($this->locations as $i=>$location) {

            $line = $location['Name'] . " " . $location['ShortDescription'];
            $lev = levenshtein($line, $text);
            if ($lev < $min_lev) {$min_lev = $lev; $best_match = $location;}

        }
        $this->location = $best_match;
        return;
    }


    /**
     *
     */
    function getNeighbourhoods() {

        if (!isset($this->locations)) {$this->getLocations();}

        //if (isset($this->locations[$text])) {$this->location = $this->locations[$text$

        $min_lev = 1e99;
        foreach ($this->locations as $i=>$location) {

            $this->neighbourhoods[$location['Neighbourhood']][] = $location;
        }
    }


    /**
     *
     * @param unknown $ref
     * @return unknown
     */
/*
    function getLink($ref) {
        // Give it the message returned from the API service

        $this->link = "https://www.google.com/search?q=" . $ref;
        return $this->link;
    }
*/

    /**
     *
     * @param unknown $event
     */
    public function makeCar($event) {
        // Need to check whether the events exists...
        // This can be post response.

        // Load as new event things onto stack
        $thing = new Thing(null);
        $thing->Create("modo@stackr.ca", "cars", "s/ cars modo " . $modo_id);

        // make sure the right fields are directly given
        //        new Event($thing, "event is ". $event['name']);
        //        new Runat($thing, "runat is ". $event['runat']);
        //        new Place($thing, "place is ". $event['place']);
        //        new Link($thing, "link is " . $event['link']);
    }


    /**
     *
     * @param unknown $car
     * @return unknown
     */
    public function carString($car) {
        if ($car == null) {return;}
        if (!is_array($car)) {return;}
        $l = "";
        foreach ($car["locations"] as $i=>$location) {

            //            $l .= $location['neighbourhood'] . " ";
            $l .= $location['description'] . " ";

        }

        //        $car_date = date_parse($event['runat']);
        $car_date = date_parse($car["locations"][0]['start_time']);

        $month_number = $car_date['month'];
        if ($month_number == "X") {$month_name = "XXX";} else {
            $month_name = date('F', mktime(0, 0, 0, $month_number, 10)); // March
        }
        $simple_date_text = $month_name . " " . $car_date['day'];

        //        $car_string = ""  . $simple_date_text;
        $car_string = "";
        $car_string .= $l . " "  . $car['description'] . " ";

        return $car_string;

    }


    /**
     *
     */
    public function makeWeb() {
        //var_dump($this->response);
        if (isset($this->html_message)) {return;}
        if (isset($this->nearest_cars)) {
            $this->thing->log("using nearest.");$this->nearestWeb(); return;
        }
        if (isset($this->matches)) {
            $this->thing->log("using matches.");
            $this->matchesWeb(); return;
        }

        $html = "<b>MODO</b>";
        $html .= "<p><b>Modo Cars</b>";

        if (!isset($this->cars)) {
            $html .= "<br>No cars found on Modo.";
        } else {
            foreach ($this->cars as $id=>$car) {
                $car_html = $this->carString($car);

                $link = "https://bookit.modo.coop/cars/" .$id;
                $html_link = '<a href="' . $link . '">';
                $html_link .= "modo";
                $html_link .= "</a>";

                $html .= "<p>" . $car_html . " " . $html_link;
            }
        }

        $this->html_message = $html;
    }


    /**
     *
     */
    public function makeSms() {

        if (isset($this->sms_message)) {return;}
        if (isset($this->nearest_cars)) {$this->nearestSms(); return;}
        if ((isset($this->matches)) and ($this->matches != array())) {$this->matchesSms(); return;}


        $sms = "MODO";
        switch ($this->cars_count) {
        case 0:
            $sms .= " | No car found.";
            break;
        case 1:
            $car = reset($this->cars);
            $car_html = $this->carString($car);
            $sms .= " | " .$car_html;


            //          if ($this->available_cars_count != $this->cars_count) {
            //            $sms .= $this->cars_count. " retrieved";
            //      }

            break;
        default:
            //            $sms .= " "  . $this->available_cars_count . ' cars ';
            if ($this->available_cars_count != $this->cars_count) {
                $sms .= $this->cars_count. " retrieved";
            }

            $car = reset($this->cars);
            //          $car_html = $this->carString($car);
            $sms .= " | " . $this->response;

            //            $sms .= " | " . $car_html;


        }

        //$this->response = str_replace("  ", " ", $this->response);

        //        $sms .= " | " . $this->response;

        $sms = str_replace("  ", " ", $sms);


        $this->sms_message = $sms;
    }


    /**
     *
     */
    public function makeMessage() {
        $message = "Modo";

        if (!isset($this->cars_count)) {$this->cars_count = null;}

        switch (true) {
        case ($this->cars_count == 0):
            $message .= " did not find any events.";
            break;
        case ($this->cars_count == 1):
            $car = reset($this->cars);
            $car_html = $this->carString($car);
            $message .= " found "  . $car_html . ".";
            break;
        default:
            $message .= " found "  . $this->available_cars_count . ' events.';
            $car = reset($this->cars);
            $car_html = $this->carString($car);
            $message .= " This was one of them. " . $car_html .".";
        }

        $this->message = $message;
    }



    /**
     *
     */
    private function thingreportModo() {

        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['web'] = $this->html_message;
        $this->thing_report['message'] = $this->message;
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function extractNumber($input = null) {
        if ($input == null) {$input = $this->subject;}

        $pieces = explode(" ", strtolower($input));

        // Extract number
        $matches = 0;
        foreach ($pieces as $key=>$piece) {
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


    /**
     *
     * @param unknown $time_text
     * @return unknown
     */
    function timeString($time_text) {

        if ($time_text == null) {return "X";}

        //$timevalue = $this->current_time;

        $this->time_zone = 'America/Vancouver';
        $m = "";
        // if no error from query_time_server
        if (true) {

            //    $tmestamp = $timevalue - 2208988800; # convert to UNIX epoch time stamp
            //$epoch = $tmestamp;
            $epoch = $time_text;
            $datum = new \DateTime("@$epoch", new \DateTimeZone("UTC"));



            $datum->setTimezone(new \DateTimeZone($this->time_zone));

            $m .= $datum->format('d/m H:i') . " ";


        }
        else {
            $m =  "Unfortunately, the time server $timeserver could not be reached at this time. ";
        }

        return $m;

    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {
        $this->response = null;

        $this->num_hits = 0;

        $keywords = $this->keywords;

        $input = $this->input;

        //var_dump($this->input);
        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {

            if ($input == 'modo') {
                //$this->search_words = null;
                $this->doModo();
                $this->response .= "Counted Modo cars in use now.";
                return;
            }
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "modo is")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("modo is"));
        } elseif (($pos = strpos(strtolower($input), "modo")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("modo"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");


        if (is_numeric($filtered_input)) {
            $this->getCars();
            if (!isset($this->cars[$filtered_input])) {$this->response = "Not found."; return;}
            $car = $this->cars[$filtered_input];
            $location = $car['locations'][0]['description'];

            $r = $car['description'] . " "  . "[".$filtered_input . "] / " ;
            $this->response = $car["link"] . " " . $location . " "   . $r;




            //echo "number";
            return;

        }



        if ($filtered_input != "") {
            $this->search_words = $filtered_input;

            if ($this->isNeighbourhood($filtered_input)) {
                $this->getNeighbourhood($filtered_input);

                $this->matchNeighbourhoods($filtered_input);
                $this->response .= "Got neighbourhood " . $this->neighbourhood.".\n";

                // Found a neighbourhood.
                // Now get the available cars in that eightbourhood.
                $this->getCars($filtered_input);

                if (count($this->matches) == 0) {
                    $this->response .= "No cars in this neighbourhood.\n";
                    return;}
                return;

            }

            // At this point the Agent has not matched the words against a neighbourhood.
            // See what best match.
            // Using the neighbourhood and street address.

            //            $this->getLocation($filtered_input);

            //          $geolocation = new Geolocation($this->thing, $filtered_input);
            //        $best_match = $geolocation->best_matches[0];


            //      $this->nearestCar($best_match['coordinates']);

            //       foreach (array_reverse($this->nearest_cars) as $i=>$nearest_car) {
            //           $this->response .= $this->carString($nearest_car) . " (" . number_format($nearest_car['distance'], 0) . "m)". " / ";
            //       }


            //            $this->getCar($filtered_input);
            //            $this->response .= "Asked Modo about " . $this->search_words . " cars. ";
            //            return false;

            // At this point the Agent has not matched the words against a neighbourhood.
            // See what best match.
            // Using the neighbourhood and street address.

            $this->getLocation($filtered_input);

            $geolocation = new Geolocation($this->thing, $filtered_input);
            $best_match = $geolocation->best_matches[0];
            $this->location_best_match = $best_match;
            if ($best_match == null) {$this->response = 'Place "' . $filtered_input . '" could not be found.'; return;}

            $this->nearestCar($best_match['coordinates']);
            /*
            foreach (array_reverse($this->nearest_cars) as $i=>$nearest_car) {
                $this->response .= $this->carString($nearest_car) . " (" . number_format($nearest_car['distance'], 0) . "m)". " / ";
            }
*/
            //$this->nearestSms();
            //$this->response .= $this->sms_message;

            $this->response .= "Asked Modo about " . $this->search_words . " cars. ";
            return false;

        }
        $this->response .= "Message not understood";
        return true;

    }


    /**
     *
     */
    function nearestSms() {
        $sms = "MODO nearest | ";
        $max_distance = 4000;
        $max_cars = 3;
        $car_count = 0;
        foreach (array_reverse($this->nearest_cars) as $i=>$nearest_car) {
            $car_count += 1;
            if ($car_count > $max_cars) {break;}
            if ($car_count == 1) {$sms .= "link ".  $nearest_car["link"] ." ";}
//            $sms .= $this->carString($nearest_car) . " (" . number_format($nearest_car['distance'], 0) . "m)". " / ";
            $sms .= $this->carString($nearest_car) . "[".$nearest_car["modo_id"] . "]".  " (" . number_format($nearest_car['distance'], 0) . "m)". " / ";


        }

        $this->sms_message = $sms;

    }


    /**
     *
     * @param unknown $modo_id
     * @return unknown
     */
    function modoLink($modo_id) {

        $link = "https://bookit.modo.coop/cars/" .$modo_id;
        return $link;

    }


    /**
     *
     */
    function matchesSms() {
        $response = "";
        $max_cars = 3;
        $car_count = 0;
        //var_dump($this->matches);
        // Then sort by the location description
        $r = "";

        foreach ($this->matches as $location_name=>$matches) {

            $arr = explode(" - ", $location_name );
            $city = $arr[0];
            $street_address = $arr[1];
            $r .= $street_address . " - " ;
            if ( (isset($car['locations'])) and (count($car['locations'])) > 1) {$r.=" / ";}
            $r2 = "";
            foreach ($matches as $modo_id=>$car) {
                $car_count += 1;
                //var_dump($car);
                //exit();
                $r2= "";
                $location_text = null;
                $flag_error = false;

                foreach ($car['locations'] as $i=>$location) {

                    if (!isset($first_link)) {$first_link = $car["link"];}

                    if (!isset($location_text)) {$location_text = $location['description'];}
                    $start_time_text = $this->timeString($location['start_time']);
                    $end_time_text = $this->timeString($location['end_time']);

                    if (($start_time_text == "X") and ($end_time_text == "X")) {
                        //$r2 .= "available " ;
                    }

                    if (($start_time_text == "X") and ($end_time_text != "X")) {
                        $r2 .= "return by " . trim($end_time_text) . " - " ;
                        break;
                    }

                    if (($start_time_text != "X") and ($end_time_text == "X")) {
                        //    $r2 .= "not available " ;
                    }

                    if (($start_time_text != "X") and ($end_time_text != "X")) {
                        //    $r2 .= "not availale ";
                    }

                }

                $r .= $r2 . $car['description'] . " "  . "[".$modo_id . "] / " ;


            }
            $old_r = $r;
            $r .= "\n";
            if (mb_strlen($r) >= 136) {$r = $old_r .= " TEXT WEB"; break;}

        }
        $car_matches_count = $this->countCars($this->matches);
        $sms = "MODO " . "" . $car_matches_count . " cars matched ";
        if ($car_count != count($this->matches)) {$sms .= $car_count . " shown ";}
        $sms .= "| " . $first_link . " "   . $r;

        $this->sms_message = $sms;

        //      $this->response .= $r;



    }


    /**
     *
     * @param unknown $matches
     * @return unknown
     */
    function countCars($matches) {
        $count =0;
        foreach ($matches as $modo_id=>$cars) {

            foreach ($cars as $i=>$c) {

                $count += 1;

            }
        }
        return $count;


    }


    /**
     *
     */
    function nearestWeb() {

        $html = "<b>MODO</b>";
        $html .= "<p><b>Available Modo Cars</b>";


        $html .= "<p>Best match location is " .  $this->location_best_match['description'].". ";
        $html .= "Best match coordinates are (" .  $this->location_best_match['coordinates'][0] . ", " . $this->location_best_match['coordinates'][1].").";
        $html .= "<p>";
        $count = 0;
        if (!isset($this->nearest_cars)) {
            $html .= "<br>No nearest cars found on Modo.";
        } else {
            foreach ($this->cars as $id=>$car) {
                $count += 1;
                if ($count > 10) {break;}
                $car_html = $this->carString($car);

                $link = "https://bookit.modo.coop/cars/" .$id;
                $html_link = '<a href="' . $link . '">';
                $html_link .= "modo";
                $html_link .= "</a>";

                $html .= "<p>" . $car_html . " " . $html_link;
            }
        }

        $this->html_message = $html;


    }


    /**
     *
     */
    function matchesWeb() {

        $html = "<b>MODO</b>";
        $html .= "<p><b>Available Modo Cars</b>";

        $html .= '<p>Matched neighbourbood "' .  $this->neighbourhood .'". ';
        $html .= "<p>";
        //var_dump($this->matches);
        if (!isset($this->matches)) {
            $html .= "<br>No matching cars found on Modo.";
        } else {
            foreach ($this->matches as $street_address=>$cars) {
                foreach ($cars as $id=>$car) {
                    $car_html = $this->carString($car);

                    $link = "https://bookit.modo.coop/cars/" .$id;
                    $html_link = '<a href="' . $link . '">';
                    $html_link .= "modo";
                    $html_link .= "</a>";

                    $html .= "<p>" . $car_html . " " . $html_link;
                }
            }
        }

        $this->html_message = $html;


    }


}
