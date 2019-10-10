<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Drivebc extends Agent 
{

    public $var = 'hello';

    function init() {

        $this->test= "Development code"; // Always

        //$this->node_list = array("off"=>array("on"=>array("off")));

        $this->keywords = array('drive','drivebc','traffic','weather');

        $this->thing_report['help'] = 'This provides Province of British Columbia provincial road network information.';

	}

function run() {

$this->getEvents();
//$this->getJurisdictions();
//$this->getAreas();
$this->getWeather();
$this->getRoad("Highway 99");

$this->doDriveBC();

}

function doDriveBC($text = null) {

foreach($this->events as $i=>$event) {
//echo $event['description'] . "\n";

}

}

function getEvents() {

        $this->getDriveBC("events");

if (!isset($this->response)) {return true;}

$this->events = $this->response['events'];
//foreach($this->response['events'] as $key=>$event) {
//var_dump($event);
//}

}

function getJurisdictions() {
    // API returns one jurisdiction (Province of British Columbia)

        $this->getDriveBC("jurisdiction");

if (!isset($this->response)) {return true;}

// devstack. Open511.
//var_dump($this->response);
//foreach($this->response['events'] as $key=>$event) {
//var_dump($event);
//}

}

function getWebcam() {
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

function getRoad($road_name = "Highway 1") {

//https://api.open511.gov.bc.ca/events?road_name=Highway 1
//$this->getDriveBC("events?road_name=" . $road_name);

// rawurlencode adds in %20
$this->getDriveBC("events?road_name=" . rawurlencode($road_name));

//var_dump($this->response['events']);
$this->road[$road_name] = $this->response['events'];

}

function getBorder() {
// http://www.th.gov.bc.ca/ATIS/
}

function getDMS() {
// Not possible currently?
}

function getWeather() {

// https://www.drivebc.ca/api/weather/
//$l = "https://www.drivebc.ca/api/weather/observations/around?lat=48.443491&long=-123.343757";
//$l = "https://www.drivebc.ca/api/weather/observations?format=json";
        $this->getDriveBC("api/weather/observations?format=json");

if (!isset($this->response)) {return true;}

foreach($this->response as $key=>$station) {
$station = $station['station'];
$this->stations[$station['id']] = $station;
}
}


function getAreas() {
    // API returns one jurisdiction (Province of British Columbia)

        $this->getDriveBC("areas");

if (!isset($this->response)) {return true;}

$this->areas = $this->response['areas'];

$this->area_name = array();
foreach($this->areas as $key=>$area) {
$this->area_name[$area['name']]= $area;
}

}


    function set()
    {
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);
    }

    function get()
    {
        $this->variables_agent = new Variables($this->thing, "variables " . "drivebc" . " " . $this->from);
    }


    function getDriveBC($resource = "events")
    {

        $this->getLink();

        $data_source = "https://api.open511.gov.bc.ca";

        // Different endpoint for weather conditions in the network.
        // https://www.drivebc.ca/api/weather/
        if (strpos($resource, 'weather') !== false) {
            $data_source = "https://www.drivebc.ca";
        }

        $command = "/" . $resource;
        $l = $data_source . $command;
        $data = file_get_contents($l);
        if ($data == false) {
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, TRUE);


        $this->response = $json_data;

        return $this->response;
    }


    public function getLink($ref = null)
    {
        // Give it the message returned from the API service

        $this->link = "http://www.drivebc.ca/"; 
        return $this->link;
    }

	public function respond() {

		// Thing actions

		$this->thing->flagGreen();
		// Generate email response.

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

	}

    public function makeSMS() {
        $sms_message = "DRIVE BC ";

        $sms_message .= " | curated link " . $this->link;

        $sms_message .=  " | TEXT ?";
        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;

    }

    public function extractNumber($input = null)
    {
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

    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;
        // Extract uuids into

        //$this->number = extractNumber();

        $keywords = $this->keywords;

        if ($this->agent_input != null) {

            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);

        } else {

            $input = strtolower($this->subject);

        }

        $this->input = $input;

		$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'drivebc') {

                return;
            }

            if ($input == 'drive') {

                return;
            }


        }

    // Extract runat signal
    $matches = 0;

    $currencies = array();

    foreach ($pieces as $key=>$piece) {

        if ((strlen($piece) == 3) and (ctype_alpha($piece))) {
            $currencies[] =strtoupper( $piece);
            //$run_at = $piece;
            $matches += 1;
        }
    }

    if ($matches == 1) {
        $this->currency_pair = 'USD' . $currencies[0];
    }


    if ($matches == 2) {
        $this->currency_pair = $currencies[0] . $currencies[1];
    }

    foreach ($pieces as $key=>$piece) {
        foreach ($keywords as $command) {
            if (strpos(strtolower($piece),$command) !== false) {

                switch($piece) {

   case 'verbosity':
    case 'mode':
        $number = $this->extractNumber();
        if (is_numeric($number)) {
            $this->verbosity = $number;
            $this->set();
        }
       return;

    default:

                                        }

                                }
                        }

                }

		return true;

	}

}

