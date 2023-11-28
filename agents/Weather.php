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

class Weather extends Agent
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

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->keyword = "environment";
        $this->test = "Development code"; // Always
        $this->keywords = ['weather'];

        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "weather" . " " . $this->from
        );

        $this->default_state = "green";

        if ($this->verbosity == false) {
            $this->verbosity = 2;
        }

        // devstack identify place as vancouver
        $this->link = "https://weather.gc.ca/rss/city/bc-74_e.xml";
        $this->xml_link = "https://weather.gc.ca/rss/city/bc-74_e.xml";

        // https://www.weather.gc.ca/city/pages/bc-74_metric_e.html
        $link = str_replace("/rss/city/", "/city/pages/", $this->xml_link);
        $this->link = str_replace("_e.xml", "_metric_e.html", $link);

        $this->thing->refresh_at = $this->thing->time(time() + 5*60); // Refresh after 5 minutes.

    }

    public function makeLink()
    {
        $this->link = "https://weather.gc.ca/rss/city/bc-74_e.xml";
        $this->xml_link = "https://weather.gc.ca/rss/city/bc-74_e.xml";

        // https://www.weather.gc.ca/city/pages/bc-74_metric_e.html
        $link = str_replace("/rss/city/", "/city/pages/", $this->xml_link);
        $this->link = str_replace("_e.xml", "_metric_e.html", $link);
        $this->thing_report['link'] = $this->link;
    }

    /**
     *
     */
    function run()
    {
        $this->getWeather();
    }

    /**
     *
     * @param unknown $text
     */
    function doWeather($text)
    {
        $filtered_text = strtolower($text);

        $ngram_agent = new Ngram($this->thing, $filtered_text);

        foreach ($ngram_agent->ngrams as $index => $ngram) {
            switch ($ngram) {
                case "next weekend":
                case "next saturday":
                case "next sat":
                    $this->nextweekendWeather();
                    $this->response .= "Saw a request about next weekend. ";
                    break;
                case "friday":
                case "saturday":
                case "monday":
                case "weather weekend":
                case "this weekend":
                case "this saturday":
                case "this sat":
                    $this->thisweekendWeather();
                    $this->response .= "Saw a request about this weekend. ";
                    break;
                case 'verbosity':
                case 'mode':
                    $number = $this->extractNumber();
                    if (is_numeric($number)) {
                        $this->verbosity = $number;
                        $this->set();
                    }
                    break;
                //            case 'weather':
                //                $this->getWeather();
                //                $this->response .= $this->current_conditions . " > " . $this->forecast_conditions . ". ";
                //                break;

                default:
                    if (!isset($this->weather_contents)) {
                        $this->getWeather();
                    }
            }
        }
    }

    /**
     *
     */
    private function thisweekendWeather()
    {
        if (!isset($this->weather_contents)) {
            $this->getWeather();
        }
        $data = $this->weather_contents;
        //$this->weather_contents = $data;
        $contents = $data;

        $m = explode("  ", $data);

        $days = [
            "saturday",
            "sunday",
            "monday",
            "tuesday",
            "wednesday",
            "thursday",
            "friday",
        ];

        $week_index = 0;

        foreach ($m as $line) {
            foreach ($days as $day) {
                if (stripos($line, $day) !== false) {
                    $a = explode(":", $line);
                    if (stripos($line, $day) !== false) {
                        foreach ($days as $day) {
                            if (stripos($a[0], $day) !== false) {
                                $night = "day";
                                if (stripos($a[0], "night") !== false) {
                                    $night = "night";
                                }
                                $this->daily_forecast[$day][$week_index][
                                    $night
                                ] = $a[1];
                                if (
                                    strtolower($day) == "monday" and
                                    $night == "day"
                                ) {
                                    $week_index += 1;
                                }
                            }
                        }
                    }
                }
            }
        }
        $weather_text = "";

        $index = 0;
        if (!isset($this->daily_forecast['sunday'][0]['night'])) {
            $index = 1;
        }

        foreach ([$index] as $week_index) {
            foreach (["friday", "saturday", "sunday"] as $day) {
                foreach (["day", "night"] as $night) {
                    $night_text = "";
                    if ($night == "night") {
                        $night_text = $night;
                    }
                    if (
                        !isset($this->daily_forecast[$day][$week_index][$night])
                    ) {
                        continue;
                    }
                    $weather_text .=
                        " / " .
                        trim(ucwords($day) . " " . $night_text) .
                        ". " .
                        $this->daily_forecast[$day][$week_index][$night];
                }
            }
        }

        $weather_text = str_replace("\r\n", "", $weather_text);

        $weather_text = str_replace("\n", "", $weather_text);
        $weather_text = str_replace("  ", " ", $weather_text);
        $weather_text = trim($weather_text);

        if (strpos(strrev($weather_text), '.') !== 0) {
            $weather_text .= ". ";
        } else {
            $weather_text .= " ";
        }

        $this->response .= $weather_text;
    }

    /**
     *
     */
    private function nextweekendWeather()
    {
        $this->response =
            "+7 day forecast is not available. Try WEATHER THIS WEEKEND. ";
    }

    /**
     *
     */
    function set()
    {
        if (!isset($requested_state) or $requested_state == null) {
            if (!isset($this->requested_state)) {
                $this->requested_state = $this->state;
            }
            $requested_state = $this->requested_state;
        }

        $this->variables_agent->setVariable("state", $this->state);

        $this->variables_agent->setVariable("verbosity", $this->verbosity);

        $this->variables_agent->setVariable(
            "current_conditions",
            $this->current_conditions
        );
        $this->variables_agent->setVariable(
            "forecast_conditions",
            $this->forecast_conditions
        );

        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->refreshed_at = $this->current_time;
    }

    /**
     *
     */
    function get()
    {
        $this->state = $this->variables_agent->getVariable("state");
        if ($this->state == false) {
            $this->state = $this->default_state;
        }

        $this->last_current_conditions = $this->variables_agent->getVariable(
            "current_conditions"
        );
        $this->last_forecast_conditions = $this->variables_agent->getVariable(
            "forecast_conditions"
        );

        $this->last_refreshed_at = $this->variables_agent->getVariables(
            "refreshed_at"
        );

        $this->verbosity = $this->variables_agent->getVariable("verbosity");
    }

    /**
     *
     * @return unknown
     */
    function getWeather()
    {
        $data_source = $this->xml_link;

        $data = file_get_contents($data_source);

        if ($data == false) {
            return true;
            // Invalid weather setting.
        }

        // String html tags
        //$data = strip_tags($data);
        $data = preg_replace("/<.*?>/", " ", $data);
        $contents = $data;
        $this->weather_contents = $data;
        $searchfor = "Current Conditions";

        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        $pattern = "/^.*" . $pattern . ".*\$/m";

        // search, and store all matching occurences in $matches
        $m = false;
        if (preg_match_all($pattern, $contents, $matches)) {
            $m = implode("\n", $matches[0]);
            $this->matches = $matches;
        }

        // Condition text
        $this->current_conditions = str_replace(
            $searchfor,
            "",
            $this->matches[0][0]
        );

        $this->current_conditions = str_replace(
            "&#xB0;",
            "°",
            $this->current_conditions
        );
        $this->current_conditions = trim(
            str_replace(": ", "", $this->current_conditions)
        );



 $searchfor = "Observed at";

        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        $pattern = "/^.*" . $pattern . ".*\$/m";

        // search, and store all matching occurences in $matches
        $m = false;
        if (preg_match_all($pattern, $contents, $matches)) {
            $m = implode("\n", $matches[0]);
            $this->matches = $matches;
        }

        // Condition text
        $observed_at = str_replace(
            $searchfor,
            "",
            $this->matches[0][0]
        );

        $observed_at = str_replace(
            "&deg;C",
            "°C",
            $observed_at
        );
        $observed_at = trim(
            str_replace(": ", "", $observed_at)
        );
        $observed_at = trim(
            str_replace("Air Quality Health Index", "AQHI", $observed_at)
        );

        $observed_at = trim(
            str_replace(']]>', "", $observed_at)
        );


        $observed_at = trim(
            str_replace("Pressure / Tendency", "Pressure", $observed_at)
        );

$parts = explode("Condition", $observed_at);

$this->observed_at = preg_replace('!\s+!', ' ', trim($parts[0]));

$this->observed_place = explode(" ", $this->observed_at)[0];

$t = $this->extractClocktime($this->observed_at);

$this->observed_clocktime = implode(":", $t);
$this->observed_conditions = preg_replace('!\s+!', ' ', trim($parts[1]));

        $contents = $data;
        $searchfor = "Forecast issued";

        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        $pattern = "/^.*" . $pattern . ".*\$/m";

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
        $forecast_timestamp = trim(
            explode($searchfor, $this->conditions[0])[1]
        );

        $this->forecast_timestamp_date = preg_split(
            "/ (PDT|PST) /",
            $forecast_timestamp
        )[1];
        $this->forecast_timestamp_time = preg_split(
            "/ (PDT|PST) /",
            $forecast_timestamp
        )[0];

        $this->forecast_conditions = trim($forecast_conditions);

        $this->refreshed_at = $this->current_time;

        return;
    }

    /**
     *
     */
    function getTemperature()
    {
        // devstack
        if (!isset($this->conditions)) {
            $this->getWeather();
        }
        $this->current_temperature = -1;
    }

    /**
     *
     * @param unknown $needles
     * @param unknown $haystack
     * @return unknown
     */
    function match_all($needles, $haystack)
    {
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
    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();
        // Generate email response.

        //$to = $this->thing->from;
        //$from = "weather";

        $choices = false;
        $this->thing_report['choices'] = $choices;

        //$this->makeSms();
        //$this->makeMessage();

        $this->thing_report['email'] = $this->sms_message;
        //$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;
        $this->thing_report['txt'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->makeWeb();

        $this->thing_report['help'] = 'This reads a web resource.';
    }

    /**
     *
     */
    public function makeWeb()
    {
        $web = "<b>Weather Agent</b>";
        $web .= "<p>";
        //$web .= '<iframe title="Environment Canada Weather" width="287px" height="191px" src="//weather.gc.ca/wxlink/wxlink.html?cityCode=bc-74&amp;lang=e" allowtransparency="true" frameborder="0"></iframe>';

        $iframe =
            '<iframe title="Environment Canada Weather" width="300px" height="191px" src="https://weather.gc.ca/wxlink/wxlink.html?cityCode=bc-74&amp;lang=e" allowtransparency="true" frameborder="0"></iframe>';

        $web .= $iframe;

        $web .= "<p>";
        $web .= "current conditions are " . $this->current_conditions . "<br>";
        $web .=
            "forecast conditions becoming " .
            $this->forecast_conditions .
            "<br>";

        $web .= "data from " . $this->link . "<br>";
        $web .= "source is Environment Canada" . "<br>";

        $web .= "<br>";

        $ago = $this->thing->human_time(
            time() - strtotime($this->refreshed_at)
        );

        $web .= "Environment Canada feed last queried " . $ago . " ago.<br>";

        //        $this->sms_message = $sms_message;
        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    public function makeSMS()
    {
        if (!isset($this->response) or $this->response == null) {
            $this->response =
                $this->current_conditions . " > " . $this->forecast_conditions;
        }

        $sms_message = "WEATHER | ";
        $sms_message .= trim($this->response);
        $sms_message .= " | link " . $this->link;
        $sms_message .= " | source Environment Canada";

        $agent = new Clocktime($this->thing, $this->forecast_timestamp_time);

        $sms_message .=
            " " .
            str_pad($agent->hour, 2, "0", STR_PAD_LEFT) .
            ":" .
            str_pad($agent->minute, 2, "0", STR_PAD_LEFT);

        // devstack - a conditioning algorithm.  In Sms.php?
        $sms_message = str_replace("°C", "C", $sms_message);

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Weather is " . $this->current_conditions . ".";
        $message .= " " . "Courtesy of Environment Canada.";

        $this->message = $message;
        $this->thing_report['message'] = $message;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function extractNumber($input = null)
    {
        if ($input == null) {
            $input = $this->subject;
        }

        $pieces = explode(" ", strtolower($input));

        // Extract number
        $matches = 0;
        foreach ($pieces as $key => $piece) {
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
    public function readSubject()
    {
        if (!isset($this->input) or $this->input == null) {
            return;
        }
        //        $this->response .= 'Weather heard, "' . $this->input .'". ';

        $this->doWeather($this->input);

        $this->num_hits = 0;

        $keywords = $this->keywords;

        $pieces = explode(" ", strtolower($this->input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($this->input == 'weather') {
                $this->response =
                    $this->current_conditions .
                    " > " .
                    $this->forecast_conditions;
                return;
            }

            // Drop through
            // return "Request not understood";
        }

        if (count($pieces) == 1) {
            if ($this->input == 'weather') {
                $this->response =
                    $this->current_conditions .
                    " > " .
                    $this->forecast_conditions;
                return;
            }

            // Drop through
            // return "Request not understood";
        }

        if (count($pieces) == 2) {
            if ($this->input == 'observed weather') {
                $this->response = $this->observed_place . " " . $this->observed_clocktime . " " . 
                    $this->observed_conditions .' > ' .
$this->forecast_conditions;
                return;
            }

            // Drop through
            // return "Request not understood";
        }







        return "Message not understood";
        return false;
    }
}
