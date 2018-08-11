<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Jacket 
{
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

       $this->keywords = array('weather', 'jacket', 'anorak', 'raincoat');

        $this->current_time = $this->thing->time();

        $this->variables_agent = new Variables($this->thing, "variables " . "jacket" . " " . $this->from);

        // Loads in Weather variables.
        $this->get();

        if ($this->verbosity == false) {$this->verbosity = 2;}



		$this->thing->log('<pre> Agent "Weather" running on Thing '. $this->thing->nuuid . '.</pre>');
		$this->thing->log('<pre> Agent "Weather" received this Thing "'.  $this->subject . '".</pre>');

        //$agent = new Weather($this->thing,"weather");

        //$weather_text = $agent->sms_message;



		$this->readSubject();

//        $this->getWeather();

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
/*
    function getWeather()
    {

        $data_source = $this->xml_link;


        //$data = file_get_contents($data_source, NULL, NULL, 0, 4000);

        $data = file_get_contents($data_source);

        //$this->thing_report['txt'] = $this->txt;

        if ($data == false) {
            return true;
            // Invalid weather setting.
        }

        // String html tags
        //$data = strip_tags($data);
        $data = preg_replace("/<.*?>/", " ", $data);

        $contents = $data;
        $searchfor = "Current Conditions";

        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        $pattern = "/^.*". $pattern. ".*\$/m";

        // search, and store all matching occurences in $matches
        $m = false;
        if(preg_match_all($pattern, $contents, $matches)){
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
        if(preg_match_all($pattern, $contents, $matches)){
            $m = implode("\n", $matches[0]);
            $this->matches = $matches;
        }

        // Make an array of all forecasts
        $this->conditions = $this->matches[0];
        // noting array cycles as day > night > day > night > day > night

        // Condition text
        $forecast_conditions = explode($searchfor, $this->conditions[0])[0];
        $forecast_timestamp = trim(explode($searchfor, $this->conditions[0])[1]);
 
        $this->forecast_timestamp_date = explode("PDT", $forecast_timestamp)[1];
        $this->forecast_timestamp_time = explode("PDT", $forecast_timestamp)[0];



        //$this->forecast_timestamp = strtotime($this->forecast_time_text);
        $this->forecast_conditions = trim($forecast_conditions);

        $this->refreshed_at = $this->current_time;

        return;

    }
*/
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

/*
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
*/
	private function respond()
    {

		// Thing actions
		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "jacket";

        $choices = false;
		$this->thing_report['choices'] = $choices;

        $this->makeSms();
        $this->makeMessage();

        $this->thing_report['email'] = $this->sms_message;
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
        $web = "<b>Jacket Agent</b>";
        $web .= "<p>";
        //$web .= '<iframe title="Environment Canada Weather" width="287px" height="191px" src="//weather.gc.ca/wxlink/wxlink.html?cityCode=bc-74&amp;lang=e" allowtransparency="true" frameborder="0"></iframe>';

        $web .= '<iframe title="Environment Canada Weather" width="300px" height="191px" src="//weather.gc.ca/wxlink/wxlink.html?cityCode=bc-74&amp;lang=e" allowtransparency="true" frameborder="0"></iframe>';

        $web .= "<p>";
        $web .= "current jacket is " . $this->current_jacket . "<br>";
        $web .= "forecast jacket is " . $this->forecast_jacket . "<br>";

        $web .= "data from " . $this->weather->link . "<br>";
        $web .= "data source is Environment Canada" . "<br>";



        $web .="<br>";

//        $ago = $this->thing->human_time ( time() - strtotime($this->refreshed_at) );
//        $web .= "Environment Canada feed last queried " . $ago .  " ago.<br>";

        //$this->sms_message = $sms_message;
        $this->thing_report['web'] = $web;

    }

    function getJacket()
    {
//$haystack = "Cloudy with 40 percent chance of showers. Low 16. Forecast issued 4:00 PM PDT Tuesday 31 July 2018";
//$haystack = "Thursday: Chance of showers. High 22. POP 30%";

        $this->weather = new Weather($this->thing,"weather");
        //$haystack = $this->weather->sms_message;

        // For testing
//        $this->link = "meep";
//        $this->weather->current_conditions = "Cloudy with 40 percent chance of showers. Low 16. Forecast issued 4:00 PM PDT Tuesday 31 July 2018";
//        $this->weather->forecast_conditions = "Thursday: Chance of showers. High 22. POP 30%";



//var_dump($this->weather->current_conditions);
//var_dump($this->weather->forecast_conditions);

        $current_message = "dry";
        $forecast_message = "dry";

        $this->pop_current = $this->probabilityRain($this->weather->current_conditions);

        $rain_current = $this->isRain($this->weather->current_conditions);
        if ($rain_current)  {$current_message = "rain";}
        if ((is_numeric($this->pop_current)) and ($this->pop_current != 0))  {$current_message .= " " . $this->pop_current . "%";}


        $this->pop_forecast = $this->probabilityRain($this->weather->forecast_conditions);

        $rain_forecast = $this->isRain($this->weather->forecast_conditions);
        if ($rain_forecast)  {$forecast_message = "rain";}
        if ((is_numeric($this->pop_forecast)) and ($this->pop_forecast != 0))  {$forecast_message .= " " . $this->pop_forecast . "%";}


        $this->current_jacket = $current_message;
        $this->forecast_jacket = $forecast_message;

    }

    public function makeSms()
    {
        //if (!isset($this->message)) {$this->makeMessage();}

        $sms_message = "JACKET";;


        if (!isset($this->message)) {$this->makeMessage();}
        $sms_message .= " | " . $this->raw_message;

        $sms_message .= " | " . $this->current_jacket;

        $sms_message .= " > " . $this->forecast_jacket;


        $sms_message .= " | link " . $this->weather->link;
        $sms_message .= " | data source Environment Canada";

        //$agent = new Clocktime($this->thing, $this->forecast_timestamp_time);

        //$sms_message .= " | " . str_pad($agent->hour,"0", 2) . ":" . str_pad($agent->minute, "0", 2);

        // devstack - a conditioning algorithm.  In Sms.php?
//$sms_message = str_replace("°C","C",$sms_message);

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }

    public function makeMessage()
    {
//var_dump($this->pop_current);
//var_dump($this->pop_forecast);
        $message = "Your call.";

        // % pop now
        if (($this->pop_current == null) and ($this->pop_forecast == null)) {$message = "No.";}
        if (($this->pop_current == null) and ($this->pop_forecast >= 30)) {$message = "Not for now.";}
        if (($this->pop_current == null) and ($this->pop_forecast >= 40)) {$message = "Not for now.";}


        // 30% pop now
        if (($this->pop_current >= 30) and ($this->pop_forecast == null)) {$message = "Maybe.";}
        if (($this->pop_current >= 30) and ($this->pop_forecast >= 30)) {$message = "Maybe. And keep it with you.";}
        if (($this->pop_current >= 30) and ($this->pop_forecast >= 40)) {$message = "Maybe. And getting wetter.";}

        // 40% pop now
        if (($this->pop_current >= 40) and ($this->pop_forecast == null)) {$message = "Possibly.";}
        if (($this->pop_current >= 40) and ($this->pop_forecast >= 30)) {$message = "Possibly. And keep it with you.";}
        if (($this->pop_current >= 40) and ($this->pop_forecast >= 40)) {$message = "Possibly. And getting wetter.";}

        // 60% pop now
        if (($this->pop_current >= 60) and ($this->pop_forecast == null)) {$message = "Yes.";}
        if (($this->pop_current >= 60) and ($this->pop_forecast >= 30)) {$message = "Yes.";}
        if (($this->pop_current >= 60) and ($this->pop_forecast >= 40)) {$message = "Yes.";}

        // dev refactor
        $this->raw_message = $message;

        $message .= " " . "Data courtesy of Environment Canada.";

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

    public function isRain($haystack)
    {
        $needles = array("rain","showers", "drizzle", "showers");
        //$this->weather = new Weather($this->thing,"weather");
        //$haystack = $this->weather->sms_message;

        foreach($needles as $needle){
            if (strpos($haystack, $needle) !== false) {
                //echo $haystack. " " .$needle;
                return true;
            }
        }
        return false;

    }

    public function probabilityRain($text)
    {
        // http://climate.weather.gc.ca/glossary_e.html#r
        // very light, light, moderate, heavy


        $haystack = $text;

        // Examples
        //$haystack = "Cloudy with 40 percent chance of showers. Low 16. Forecast issued 4:00 PM PDT Tuesday 31 July 2018";
        //$haystack = "Thursday: Chance of showers. High 22. POP 30%";


        $haystack = str_replace("%", " percent ", $haystack);

        $number = null;
        $pieces = explode(" ", $haystack);
        $keywords = array("percent","%");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
                        case 'percent': 
                        case "%":
                            $number = $pieces[$key-1];
                            break;
                        default:
                            // drop through
                    }
                }
            }
        }
//        echo $haystack . "\n";
//        echo $number . "\n";
//exit();

        $probability_of_precipitation = floatval($number);

        return $probability_of_precipitation;
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

        $this->getJacket();

        return;

    }
}

?>
