<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Waterlevels 
{
    // SOAP needs enabling in PHP.ini

    // https://www.waterlevels.gc.ca/docs/Specifications%20-%20Spine%20observation%20and%20predictions%202.0.3(en).pdf

    // https://www.waterlevels.gc.ca/eng/info/Webservices
    // Canadian Hydrographic Service

    // License required from Canadian Hydrographic Service to re-publish.
    // https://www.waterlevels.gc.ca/eng/info/Licence

    // https://weather.gc.ca/rss/city/bc-74_e.xml
    // https://weather.gc.ca/rss/warning/bc-74_e.xml

    // https://weather.gc.ca/city/pages/bc-74_metric_e.html#printinstr

    // <!-- Begin WeatherLink Fragment -->
    // <iframe title="Environment Canada Weather" width="287px" height="191px" src="//weather.gc.ca/wxlink/wxlink.html?cityCode=bc-74&amp;lang=e" allowtransparency="true" frameborder="0"></iframe>
    // <!-- End WeatherLink Fragment -->

    //  “This product has been produced by or for
    // [insert User's corporate name] and includes data and
    // services provided by the Canadian Hydrographic Service
    // of the Department of Fisheries and Oceans. The
    // incorporation of data sourced from the Canadian
    //  Hydrographic Service of the Department of Fisheries
    // and Oceans within this product does NOT constitute an
    // endorsement by the Canadian Hydrographic Service or
    // the Department of Fisheries and Oceans of this product.”

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {

        $this->agent_input = $agent_input;
        $this->agent_input = $agent_input;

        $this->keyword = "environment";

        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->thing_report['thing'] = $thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;


        $this->agent_prefix = 'Agent "Weather" ';

       $this->keywords = array('water', 'level', 'tide', 'tides',
            'height', 'prediction');

        $this->current_time = $this->thing->time();

        $this->variables_agent = new Variables($this->thing, "variables " . "weather" . " " . $this->from);

        // Loads in Weather variables.
        $this->get();

        if ($this->verbosity == false) {$this->verbosity = 2;}



$devKey        = ""; 
$password    = ""; 
$accountId    = ""; 

// Create the SoapClient instance 
$url         = "https://ws-shc.qc.dfo-mpo.gc.ca/predictions" . "?wsdl"; 
$this->client     = new \SoapClient($url, array("trace" => 1, "exception" => 0));
/*
echo "foo";

// Create the header 
//$auth         = new ChannelAdvisorAuth($devKey, $password); 
//$header     = new SoapHeader("http://www.example.com/webservices/", "APICredentials", $auth, false); 



//$m = $client->getBoundarySpatial();

$date = "2018-03-30";
$lat = 48.30;
$long = -123.03;

$size = 1;

//$m = $client->search("hilo", 47.5, 47.7, -61.6, -61.4, 0.0, 0.0, $date . " ". "00:00:00", $date . " " . "23:59:59", 1, 100, true, "", "asc");

$m = $client->search("hilo", $lat - $size, $lat + $size, $long - $size, $long + $size, 0.0, 0.0, $date . " ". "00:00:00", $date . " " . "23:59:59", 1, 100, true, "", "asc");

//var_dump($m->data);
foreach ($m->data as $key=>$value) {

echo "station" . $value->metadata[0]->value . " ";
echo $value->metadata[1]->value . "\n";


var_dump($value->boundaryDate->min);
var_dump($value->boundaryDate->max);
echo $value->value . "\n";


}
*/



        // devstack identify place as vancouver
//        $this->link = "https://weather.gc.ca/rss/city/bc-74_e.xml";
//        $this->xml_link = "https://weather.gc.ca/rss/city/bc-74_e.xml";

        // https://www.weather.gc.ca/city/pages/bc-74_metric_e.html
//        $link = str_replace("/rss/city/","/city/pages/", $this->xml_link);
//        $this->link = str_replace("_e.xml","_metric_e.html", $link);


		$this->thing->log('<pre> Agent "Weather" running on Thing '. $this->thing->nuuid . '.</pre>');
		$this->thing->log('<pre> Agent "Weather" received this Thing "'.  $this->subject . '".</pre>');

		$this->readSubject();

        $this->getWeather();

		$this->respond();

        $this->thing->log( $this->agent_prefix .'ran for ' . $this->thing->elapsed_runtime() . 'ms.' );

		$this->thing->log($this->agent_prefix . 'completed.');
        $this->thing_report['log'] = $this->thing->log;

        return;
    }

    function set()
    {

        //if (!isset($this->wave_thing)) {
        //    $this->wave_thing = $this->thing;
        //}

        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        $this->variables_agent->setVariable("state", $this->state);

        $this->variables_agent->setVariable("verbosity", $this->verbosity);

        $this->variables_agent->setVariable("current_conditions", $this->current_conditions);
        $this->variables_agent->setVariable("forecast_conditions", $this->forecast_conditions);

        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

        $this->refreshed_at = $this->current_time;

        return;
    }

    function get()
    {
        $this->state = $this->variables_agent->getVariable("state")  ;

        $this->last_current_conditions = $this->variables_agent->getVariable("current_conditions")  ;
        $this->last_forecast_conditions = $this->variables_agent->getVariable("forecast_conditions")  ;

        $this->last_refreshed_at = $this->variables_agent->getVariables("refreshed_at");

        $this->verbosity = $this->variables_agent->getVariable("verbosity")  ;

        return;
    }

    function getWeather()
    {


$date = "2018-03-30";
$lat = 48.30;
$long = -123.03;

$size = 1;

//$m = $client->search("hilo", 47.5, 47.7, -61.6, -61.4, 0.0, 0.0, $date . " ". "00:00:00", $date . " " . "23:59:59", 1, 100, true, "", "asc");

$m = $this->client->search("hilo", $lat - $size, $lat + $size, $long - $size, $long + $size, 0.0, 0.0, $date . " ". "00:00:00", $date . " " . "23:59:59", 1, 100, true, "", "asc");

//var_dump($m->data);
foreach ($m->data as $key=>$value) {

//echo "station" . $value->metadata[0]->value . " ";
//echo $value->metadata[1]->value . "";


$date_min = $value->boundaryDate->min;
$date_max = $value->boundaryDate->max;

if ($date_min == $date_max) {
    // expected
    $date = $date_min;
} else {
    $date = true;
}

echo $date . " ";
echo $value->metadata[0]->value . " ";
echo $value->metadata[1]->value . " ";

echo $value->value . "\n";
}

        $this->refreshed_at = $this->current_time;

        return;

    }

    function getTemperature()
    {
        // devstack not finished
        if (!isset($this->conditions)) {$this->getWeather();}
        $this->current_temperature = -1;

    }

    function match_all($needles, $haystack)
    {
        if(empty($needles)){
            return false;
        }

        foreach($needles as $needle) {
            if (strpos($haystack, $needle) == false) {
                return false;
            }
        }
        return true;
    }


    function getVariable($variable_name = null, $variable = null) {

        // This function does a minor kind of magic
        // to resolve between $variable, $this->variable,
        // and $this->default_variable.

        if ($variable != null) {
            // Local variable found.
            // Local variable takes precedence.
            return $variable;
        }

        if (isset($this->$variable_name)) {
            // Class variable found.
            // Class variable follows in precedence.
            return $this->$variable_name;
        }

        // Neither a local or class variable was found.
        // So see if the default variable is set.
        if (isset( $this->{"default_" . $variable_name} )) {

            // Default variable was found.
            // Default variable follows in precedence.
            return $this->{"default_" . $variable_name};
        }

        // Return false ie (false/null) when variable
        // setting is found.
        return false;
    }

	private function respond()
    {

		// Thing actions
		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "weather";

        $choices = false;
		$this->thing_report['choices'] = $choices;

        $this->makeSms();
        $this->makeMessage();

        $this->thing_report['email'] = $this->sms_message;
        //$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;
        $this->thing_report['txt'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->makeWeb();

        $this->thing_report['help'] = 'This reads a web resource.';
		return;
	}

    public function makeWeb()
    {
        $web = "<b>Weather Agent</b>";
        $web .= "<p>";
        //$web .= '<iframe title="Environment Canada Weather" width="287px" height="191px" src="//weather.gc.ca/wxlink/wxlink.html?cityCode=bc-74&amp;lang=e" allowtransparency="true" frameborder="0"></iframe>';

        $web .= '<iframe title="Environment Canada Weather" width="300px" height="191px" src="//weather.gc.ca/wxlink/wxlink.html?cityCode=bc-74&amp;lang=e" allowtransparency="true" frameborder="0"></iframe>';

        $web .= "<p>";
        $web .= "current conditions are " . $this->current_conditions . "<br>";
        $web .= "forecast conditions becoming " . $this->forecast_conditions . "<br>";

        $web .= "data from " . $this->link . "<br>";
        $web .= "source is Environment Canada" . "<br>";



        $web .="<br>";

        $ago = $this->thing->human_time ( time() - strtotime($this->refreshed_at) );

        $web .= "Environment Canada feed last queried " . $ago .  " ago.<br>";

        //$this->sms_message = $sms_message;
        $this->thing_report['web'] = $web;

    }

    public function makeSms()
    {

        $sms_message = "TIDES | " . null;
//        $sms_message .= " > " . $this->forecast_conditions;
        $sms_message .= " | link " . $this->link;
        $sms_message .= " | source CHS";

//$agent = new Clocktime($this->thing, $this->forecast_timestamp_time);

//        $sms_message .= " " . str_pad($agent->hour, 2 ,"0",STR_PAD_LEFT) . ":" . str_pad($agent->minute, 2, "0",STR_PAD_LEFT);

        // devstack - a conditioning algorithm.  In Sms.php?
//$sms_message = str_replace("°C","C",$sms_message);

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }

    public function makeMessage()
    {
        $message = "Tides are " . null . ".";
        $message .= " " . "Courtesy of Environment Canada.";

        $this->message = $message;
        $this->thing_report['message'] = $message;
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

//		$this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

//        $this->getWave();

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'weather') {

                //echo "readsubject block";
                //$this->read();
                $this->response = "Did nothing.";
                return;

            }

            // Drop through
            // return "Request not understood";

        }

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
/*
                                                case 'stopwatch':    

                                                        if ($key + 1 > count($pieces)) {
                                                                //echo "last word is stop";
                                                                $this->stop = false;
                                                                return "Request not understood";
                                                        } else {
                                                                //echo "next word is:";
                                                                //var_dump($pieces[$index+1]);
                                                                $command = $pieces[$key+1];

								if ( $this->thing->choice->isValidState($command) ) {
                                                                	return $command;
								}
                                                        }
                                                        break;
*/


                        case 'verbosity':
                        case 'mode':
                            $number = $this->extractNumber();
                            if (is_numeric($number)) {
                                $this->verbosity = $number;
                                $this->set();
                            }
                            return;

                        default:
                            //$this->read();
                           //echo 'default';

                    }
                }
            }
        }
        return "Message not understood";
		return false;
	}
}

?>
