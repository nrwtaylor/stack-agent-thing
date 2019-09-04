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

class Geolocation extends Agent
{

    // This gets events from the Modo API.

    public $var = 'hello';
    public $country = "Canada only.";


    /**
     *
     */
    function init() {

//        $this->agent_name = "Geolocation";
        $this->keyword = "geolocation";
        $this->test= "Development code"; // Always

        $this->keywords = array('geolocation');

        $this->run_time_max = 360; // 5 hours

        $this->variables_agent = new Variables($this->thing, "variables " . "geolocation" . " " . $this->from);

        //        $this->getGeolocation();

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

        $this->thing->log( $this->agent_prefix .  'loaded ' . $this->counter . ".", "DEBUG");

        $this->counter = $this->counter + 1;
    }


    /**
     *
     * @param unknown $lookup_text (optional)
     * @return unknown
     */
    function getGeolocation($lookup_text = null) {
        if ($lookup_text == null) {return;}
        //        if ($sort_order == null) {$sort_order = "popularity";}

        $city = "vancouver";
        $c = new City($this->thing, "city");
        $city = $c->city_name;

        // "America/Vancouver" apparently
        //        $keywords = "";
        //        if (isset($this->search_words)) {$keywords = $this->search_words;}

        //$keywords = str_replace(" ", "%20%", $keywords);

        //        $keywords = urlencode($keywords);

        // Let's use meetup popularity...
        //        $data_source = "https://api.meetup.com/2/open_events.xml?format=json&and_text=true&text=" . $keywords . "&time=,1w&key=". $this->api_key;
        //$lookup_text = "470 Granville, Vancouver";

        $data_source = "http://geogratis.gc.ca/services/geolocation/en/locate?q=". urlencode($lookup_text);
        //        $data_source = "http://geogratis.gc.ca/services/geolocation/en/suggest?q=". urlencode($lookup_text);

        //        $time = "&time=,1w";
        //        $time = ""; // turn time paramaters off

        //        $format = "format=json"; // json or xml
        // $data_source = "https://api.meetup.com/2/open_events.xml?format=json&country=ca&city=vancouver&and_text=true&text=" . $keywords . "&time=,1w&key=". $this->api_key;
        // $data_source = "https://api.meetup.com/2/open_events.xml?format=json&country=ca&city=vancouver&and_text=true&text=" . $keywords . $time . "&key=". $this->api_key;

        //        $data_source = "https://api.meetup.com/2/open_events.xml?" . $format . "&country=ca&city=vancouver&and_text=true&text=" . $keywords . $time . "&key=". $this->api_key;

        //$data = file_get_contents($data_source, NULL, NULL, 0, 4000);

        $data = file_get_contents($data_source);
        //var_dump($data_source);
        //var_dump($data);
        //exit();
        if ($data == false) {
            $this->response = "Could not ask Geolocation (NRCAN).";
            $this->available_places_count = 0;
            $this->places_count = 0;
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, TRUE);

        //var_dump($json_data);
        //exit();
        $total_items = count($json_data);

        $this->thing->log('got ' . $total_items . " Geolocated things.");

        $this->available_cars_count = $total_items;

        $geolocation_places = $json_data;

        $this->placesGeolocation($geolocation_places); // Custom function to match Meetup API variables to Events.
        return false;
    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function getPlaces($text = null) {
        //var_dump($text);
        //        if (!isset($this->neighbourhood)) {return;}
        if (!isset($this->places)) {$this->getGeolocation($text);}
        return $this->places;

    }


    /**
     *
     * @param unknown $text (optional)
     */
    function bestPlaces($text = null) {
        if (!isset($this->places)) {$this->getPlaces($text);}

        if ($this->places == null) {$this->best_matches = null;return;}

        $min_lev = 1e99;
        $this->matches = array();
        foreach ($this->places as $geolocation_id=>$place) {

            $lev = levenshtein($place['description'], $text);
            $this->matches[$lev][] = $place;
            if ($lev < $min_lev) {$min_lev = $lev;
                //echo $min_lev. "\n";
            }
        }
        $this->best_matches = $this->matches[$min_lev];

    }


    /**
     *
     * @param unknown $place
     */
    function printGeolocation($place) {

        echo $this->geolocationString($place) . "\n";

    }


    /**
     *
     * @param unknown $text (optional)
     */
    function getPlace($text = null) {

        if (!isset($this->places)) {$this->getGeolocation();}
        //$this->cars[$modo_id] = array("description"=>$description, "runat"=>null, "runtime"=>null, "place"=>$locations[0], "link"=>null);
        $this->matches = array();
        foreach ($this->places as $geolocation_id=>$place) {
            //            foreach ($place['locations'] as $i => $location) {
            if (strtolower($place['description']) == strtolower($text)) {$this->matches[] = $place;}
            //            }
        }



    }


    /**
     *
     * @param unknown $geolocation_places
     */
    function placesGeolocation($geolocation_places) {
        if (!isset($this->places)) {$this->places = array();}
        if ($geolocation_places == null) {$this->places_count = 0;return;}

        foreach ($geolocation_places as $id=>$place) {

            //            $locations = $car['Location'];
            //            foreach ($locations as $i=>$location) {
            //                $location_matches[] = $this->getLocation($location['LocationID']);
            //            }

            //var_dump($car);
            //exit();
            //            $description = $car['Make'] . " " . $car['Model'] . " " . $car['Year'] . " " . $car['Colour'];
            $description = $place['title'];

            $coordinates = $place['geometry']['coordinates'];

            $geolocation_id = null;

            $this->places_count = count($this->places);
            //            $this->events[$meetup_id] = array("event"=>$event_name, "runat"=>$run_at, "runtime"=>$runtime, "locations"=>$venue_name, "link"=>$link);
            $this->places[$geolocation_id] = array("description"=>$description, "coordinates"=>$coordinates);

            $this->places[] = array("description"=>$description, "coordinates"=>$coordinates);


        }

        $this->places_count = count($this->places);

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
     * function getNeighbourhood($text = null) {
     * if (!isset($this->locations)) {$this->getLocations();}
     * $matches = array();
     * foreach ($this->locations as $i=>$location) {
     * if (strtolower($location['Neighbourhood']) == strtolower($text)) {
     * $this->neighbourhood = $location['Neighbourhood'];
     * return;
     * }
     * }
     * $this->neighbourhood = null;
     * //$this->neighbourhoods = $matches;
     * //if ((isset($matches)) and (count($matches) >= 1)) {return true;}
     * return;
     * }
     *
     * @param unknown $text (optional)
     */

    /**
     * function getLink($ref) {
     * // Give it the message returned from the API service
     * $this->link = "https://www.google.com/search?q=" . $ref;
     * return $this->link;
     * }
     *
     * @param unknown $ref
     * @return unknown
     */

    /**
     *
     * @param unknown $place
     */
    public function makePlace($place) {
        // Need to check whether the events exists...
        // This can be post response.

        // Load as new event things onto stack
        $thing = new Thing(null);
        $thing->Create("geolocation@stackr.ca", "places", "s/ places geolocation " . $place);

        // make sure the right fields are directly given
        //        new Event($thing, "event is ". $event['name']);
        //        new Runat($thing, "runat is ". $event['runat']);
        //        new Place($thing, "place is ". $event['place']);
        //        new Link($thing, "link is " . $event['link']);
    }


    /**
     *
     * @param unknown $place
     * @return unknown
     */
    public function placeString($place) {
        if (!is_array($place)) {return;}
        //var_dump($place);
        $place_string = $place['description'] . " " . $place['coordinates'][0] . " " . $place['coordinates'][1];
        //var_dump($place_string);
        return $place_string;

    }


    /**
     *
     */
    public function makeWeb() {
return;
        $html = "<b>Geolocation Agent</b>";
        $html .= "<p><b>Geolocation Cars</b>";

        if (!isset($this->events)) {
            $html .= "<br>No places found on Geolocation.";
        } else {
            foreach ($this->events as $id=>$event) {
                $event_html = $this->eventString($event);

                $link = $event['link'];
                $html_link = '<a href="' . $link . '">';
                $html_link .= "geolocation";
                $html_link .= "</a>";

                $html .= "<p>" . $event_html . " " . $html_link;
            }
        }

        $this->html_message = $html;
    }


    /**
     *
     */
    public function makeSMS() {
        $sms = "GEOLOCATION" . " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }


    /**
     *
     */
    public function makeMessage() {
        $message = "Geolocation";

        switch ($this->cars_count) {
        case 0:
            $message .= " did not find any events.";
            break;
        case 1:
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
    private function thingreportGeolocation() {
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
            //    $datum = date("Y-m-d H:i:s",$tmestamp - date("Z",$tmestamp)); /* incl time zone offset */
            //var_dump($epoch);
            //    $d = date("Y-m-d H:i:s",$tmestamp - date("Z",$tmestamp)); /* incl time zone offset */

            //$datum = $dt = new \DateTime($tmestamp, new \DateTimeZone("UTC"));
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
    public function respondResponse() {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        $to = $this->thing->from;
        $from = "geolocation";

        //$this->makeMessage();
        $this->makeSms();

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
     * @return unknown
     */
    public function readSubject() {
        $this->response = null;

        $this->num_hits = 0;

        $keywords = $this->keywords;
        /*
        if ($this->agent_input != null) {

            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);

        } else {
            $input = strtolower($this->subject);
        }

        $this->input = $input;
*/

        $input = $this->input;

        //$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;
        //$prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {

            if ($input == 'geolocation') {
                //$this->search_words = null;
                $this->response = "Asked Geolocation about places.";
                return;
            }

        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "geolocation is")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("geolocation is"));
        } elseif (($pos = strpos(strtolower($input), "geolocation")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("geolocation"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        //echo "foo";
        if ($filtered_input != "") {
            //echo "bar";
            $this->search_words = $filtered_input;

            //                $this->response .= "Got neighbourhood " . $this->neighbourhood.".\n";

            // Found a neighbourhood.
            // Now get the available cars in that eightbourhood.
            $this->getPlaces($filtered_input);

            $this->bestPlaces($filtered_input);
            //var_dump($this->best_matches);
            $r = "";
            if ($this->best_matches == null) {$this->response = "No places matched."; return;}
            foreach ($this->best_matches as $index=>$match) {

                //echo $this->placeString($match);
                //exit();
                $r = $this->placeString($match) . " / ";

                $this->response .= $r;
            }
            return;


            // At this point the Agent has not matched the words against a neighbourhood.
            // See what best match.
            // Using the neighbourhood and street address.

            //            $this->getCar($filtered_input);


            $this->response .= "Asked Geolocation about " . $this->search_words . " cars. ";
            return false;
        }
        $this->response .= "Message not understood";
        return true;

    }


}
