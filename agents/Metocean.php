<?php
/**
 * Weather.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Metocean extends Agent {
    // https://weather.gc.ca/business/index_e.html
    // WeatherLink

    // License required from Environment Canada to re-publish.

    // https://weather.gc.ca/rss/city/bc-74_e.xml
    // https://weather.gc.ca/rss/warning/bc-74_e.xml

    // https://weather.gc.ca/city/pages/bc-74_metric_e.html#printinstr

    // <!-- Begin WeatherLink Fragment -->
    // <iframe title="Environment Canada Weather" width="287px" height="191px" src="//weather.gc.ca/wxlink/wxlink.html?cityCode=bc-74&amp;lang=e" allowtransparency="true" frameborder="0"></iframe>
    // <!-- End WeatherLink Fragment -->

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init() {

        $this->keyword = "environment";
        $this->test= "Development code"; // Always
        $this->keywords = array('weather');

        $this->variables_agent = new Variables($this->thing, "variables " . "metocean" . " " . $this->from);

        $this->default_state = "green";

        if ($this->verbosity == false) {$this->verbosity = 2;}

        // devstack identify place as vancouver
        $this->link = "https://weather.gc.ca/rss/city/bc-74_e.xml";
        $this->xml_link = "https://weather.gc.ca/rss/city/bc-74_e.xml";

$this->html_link = "https://weather.gc.ca/marine/weatherConditions-currentConditions_e.html?mapID=02&siteID=14305&stationID=wvf";

        // https://www.weather.gc.ca/city/pages/bc-74_metric_e.html
        $link = str_replace("/rss/city/", "/city/pages/", $this->xml_link);
        $this->link = str_replace("_e.xml", "_metric_e.html", $link);


    }


    /**
     *
     */
    function run() {

        $this->doMetocean();

    }

function doMetocean() {

$this->current_conditions = "";
$this->forecast_conditions = "";

$this->getMetocean();

}

    /**
     *
     */
    function set() {

        if ( (!isset($requested_state)) or ($requested_state == null) ) {
            if (!isset($this->requested_state)) {$this->requested_state = $this->state;}
            $requested_state = $this->requested_state;
        }

        $this->variables_agent->setVariable("state", $this->state);

        $this->variables_agent->setVariable("verbosity", $this->verbosity);

        $this->variables_agent->setVariable("current_conditions", $this->current_conditions);
        $this->variables_agent->setVariable("forecast_conditions", $this->forecast_conditions);

        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

        $this->refreshed_at = $this->current_time;
    }


    /**
     *
     */
    function get() {
        $this->state = $this->variables_agent->getVariable("state")  ;
        if ($this->state == false) {$this->state = $this->default_state;}

        $this->last_current_conditions = $this->variables_agent->getVariable("current_conditions")  ;
        $this->last_forecast_conditions = $this->variables_agent->getVariable("forecast_conditions")  ;

        $this->last_refreshed_at = $this->variables_agent->getVariables("refreshed_at");

        $this->verbosity = $this->variables_agent->getVariable("verbosity")  ;
    }


    /**
     *
     * @return unknown
     */
    function getMetocean() {

        $data_source = $this->html_link;

        $data = file_get_contents($data_source);
        if ($data == false) {
            return true;
            // Invalid weather setting.
        }

        // String html tags
        //$data = strip_tags($data);
        $data = preg_replace("/<.*?>/", " ", $data);
        //var_dump($data);
        $contents = $data;
        $this->weather_contents = $data;

$searchfor = "Wind&nbsp; ( knots ) ";

//$t = $this->getLineAfter($contents, $searchfor);
$text = $this->getLine($contents, $searchfor, 0);

//var_dump($text);
//exit();
//$x = "Wind";
$conditions_text = explode($searchfor, $text);

$conditions = explode("Air",$conditions_text[1]);
$this->current_conditions = trim($conditions[0]);


$searchfor = "Sand Heads Lightstation ";

//$t = $this->getLineAfter($contents, $searchfor);
$text = $this->getLine($contents, $searchfor, 0);

//$x = "Wind";
$timestamp_text = explode("  ", $text);
$timestamp = $timestamp_text[1];

$conditions_timestamp = str_replace("&nbsp;", " " ,$timestamp);

//var_dump($timestamp);

//        $forecast_timestamp = trim(explode($searchfor, $this->conditions[0])[1]);

        $this->forecast_timestamp_date = preg_split( "/ (PDT|PST) /", $conditions_timestamp )[1];
        $this->forecast_timestamp_time = preg_split( "/ (PDT|PST) /", $conditions_timestamp )[0];

//        $this->forecast_conditions = trim($forecast_conditions);

        $this->refreshed_at = $this->current_time;


//exit();

$conditions = explode("Air",$conditions_text[1]);
$this->current_conditions = trim($conditions[0]);





return;

        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        $pattern = "/^.*". $pattern. ".*\$/m";

        // search, and store all matching occurences in $matches
        $m = false;
        if (preg_match_all($pattern, $contents, $matches)) {
            $m = implode("\n", $matches[0]);
            $this->matches = $matches;
        }


        // Condition text
        $this->current_conditions = str_replace($searchfor, "", $this->matches[0][0]);
        $this->current_conditions = str_replace("&#xB0;", "°", $this->current_conditions);
        $this->current_conditions = trim(str_replace(": ", "", $this->current_conditions));

        $contents = $data;
        $searchfor = "Forecast issued";

        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        $pattern = "/^.*". $pattern. ".*\$/m";


        // search, and store all matching occurences in $matches
        $m = false;
        if (preg_match_all($pattern, $contents, $matches)) {
            $m = implode("\n", $matches[0]);
            $this->matches = $matches;
        }

        // Make an array of all forecasts
        $this->conditions = $this->matches[0];
        // noting array cycles as day > night > day > night > day > night
        // Condition text
        $forecast_conditions = explode($searchfor, $this->conditions[0])[0];
        $forecast_timestamp = trim(explode($searchfor, $this->conditions[0])[1]);

        $this->forecast_timestamp_date = preg_split( "/ (PDT|PST) /", $forecast_timestamp )[1];
        $this->forecast_timestamp_time = preg_split( "/ (PDT|PST) /", $forecast_timestamp )[0];

        $this->forecast_conditions = trim($forecast_conditions);

        $this->refreshed_at = $this->current_time;

        return;

    }

function getLine($contents, $searchfor, $relative_index = 0) {

$lines = explode("\r\n", $contents);
//    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach($lines as $i=>$line) {

if (strpos($line, $searchfor) !== false) {
    return $lines[$i+ $relative_index];
}
}
 //   if (($key = array_search($line, $lines)) !== false && isset($lines[$key + 1])) {
//        return $lines[$key + 1];
//    }
    return null;
}

    /**
     *
     * @param unknown $needles
     * @param unknown $haystack
     * @return unknown
     */
    function match_all($needles, $haystack) {
        if (empty($needles)) {
            return false;
        }

        foreach ($needles as $needle) {
            if (strpos($haystack, $needle) == false) {
                return false;
            }
        }
        return true;
    }


    /**
     *
     */
    /**
     *
     */
    public function makeWeb() {
        $web = "<b>Metocean Agent</b>";
//        $web .= "<p>";
        //$web .= '<iframe title="Environment Canada Weather" width="287px" height="191px" src="//weather.gc.ca/wxlink/wxlink.html?cityCode=bc-74&amp;lang=e" allowtransparency="true" frameborder="0"></iframe>';

//        $web .= '<iframe title="Environment Canada Weather" width="300px" height="191px" src="//weather.gc.ca/wxlink/wxlink.html?cityCode=bc-74&amp;lang=e" allowtransparency="true" frameborder="0"></iframe>';

        $web .= "<p>";
        $web .= "current conditions are " . $this->current_conditions . "<br>";
        $web .= "forecast conditions becoming " . $this->forecast_conditions . "<br>";

        $web .= "data from " . $this->html_link . "<br>";
$web .= "This report is from a summary of hourly weather conditions for the weather station or buoy. Please note that these observations might not always be representative of weather conditions over their associated marine area.<br>";
        $web .= "Source is Environment Canada." . "<br>";



        $web .="<br>";

        $ago = $this->thing->human_time ( time() - strtotime($this->refreshed_at) );

        $web .= "Environment Canada feed last queried " . $ago .  " ago.<br>";

//        $this->sms_message = $sms_message;
        $this->thing_report['web'] = $web;
    }


    /**
     *
     */
    public function makeSms() {

        if ((!isset($this->response)) or ($this->response == null)) {
//            $this->response = $this->current_conditions . " > " . $this->forecast_conditions;
            $this->response = $this->current_conditions . " ";
        }

        $sms_message = "METOCEAN SAND HEADS LIGHTHOUSE | ";
        $sms_message .= trim($this->response);
        $sms_message .= " | link " . $this->html_link;
        $sms_message .= " | source Environment Canada ";
$sms_message .= "Note that these observations might not always be representative of weather conditions over their associated marine area.";
        $agent = new Clocktime($this->thing, $this->forecast_timestamp_time);

        $sms_message .= " " . str_pad($agent->hour, 2 , "0", STR_PAD_LEFT) . ":" . str_pad($agent->minute, 2, "0", STR_PAD_LEFT);
$sms_message .= " " . $this->forecast_timestamp_date;
        // devstack - a conditioning algorithm.  In Sms.php?
//        $sms_message = str_replace("°C", "C", $sms_message);

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;

    }


    /**
     *
     */
    public function makeMessage() {
        $message = "Conditions are " . $this->current_conditions . ".";
        $message .= " " . "Courtesy of Environment Canada.";

        $this->message = $message;
        $this->thing_report['message'] = $message;
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
     * @return unknown
     */
    public function readSubject() {
        if ((!isset($this->input)) or ($this->input == null)) {return;}
        //        $this->response .= 'Weather heard, "' . $this->input .'". ';

        $this->doMetocean($this->input);

        $this->num_hits = 0;

        $keywords = $this->keywords;

        $pieces = explode(" ", strtolower($this->input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {

            if ($this->input == 'metocean') {
//                $this->response = $this->current_conditions . " > " . $this->forecast_conditions;
                $this->response = $this->current_conditions;
                return;

            }

            // Drop through
            // return "Request not understood";

        }
        return "Message not understood";
        return false;
    }


}
