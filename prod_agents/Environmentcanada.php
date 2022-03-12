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

class Environmentcanada extends Agent
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
        $this->keywords = ['weather','environment canada'];

        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "environment_canada" . " " . $this->from
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
        $this->getEnvironmentCanada();
    }

    /**
     *
     * @param unknown $text
     */
    function doEnvironmentCanada($text)
    {
        $filtered_text = strtolower($text);

        $ngram_agent = new Ngram($this->thing, $filtered_text);

        foreach ($ngram_agent->ngrams as $index => $ngram) {
            switch ($ngram) {
                case "next weekend":
                case "next saturday":
                case "next sat":
                    $this->nextweekendEnvironmentCanada();
                    $this->response .= "Saw a request about next weekend. ";
                    break;
                case "friday":
                case "saturday":
                case "monday":
                case "environoment canada weekend":
                case "this weekend":
                case "this saturday":
                case "this sat":
                    $this->thisweekendEnvironmentCanada();
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
                    if (!isset($this->environent_canada_contents)) {
                        $this->getEnvironmentCanada();
                    }
            }
        }
    }

    /**
     *
     */
    private function thisweekendEnvironmentCanada()
    {
        if (!isset($this->environment_canada__contents)) {
            $this->getEnvironmentCanada();
        }
        $data = $this->environment_canada_contents;
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
        $environment_canada_text = "";

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
                    $environment_canada_text .=
                        " / " .
                        trim(ucwords($day) . " " . $night_text) .
                        ". " .
                        $this->daily_forecast[$day][$week_index][$night];
                }
            }
        }

        $environment_canada_text = str_replace("\r\n", "", $environment_canada_text);

        $environment_canada_text = str_replace("\n", "", $environment_canada_text);
        $environment_canada_text = str_replace("  ", " ", $environment_canada_text);
        $environment_canada_text = trim($environment_canada_text);

        if (strpos(strrev($environment_canada_text), '.') !== 0) {
            $environment_canada_text .= ". ";
        } else {
            $environment_canada_text .= " ";
        }

        $this->response .= $environment_canada_text;
    }

    /**
     *
     */
    private function nextweekendEnvironmentCanada()
    {
        $this->response =
            "+7 day forecast is not available. Try ENVIRONMENT CANADA THIS WEEKEND. ";
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
    function getEnvironmentCanada()
    {
        $data_source = $this->xml_link;
try {
        $data = @file_get_contents($data_source);
        } catch (\Throwable $t) {
return true;
        } catch (\Exception $e) {
return true;
        }
var_dump($data);
        if ($data == false) {
            return true;
            // Invalid weather setting.
        }

        // String html tags
        //$data = strip_tags($data);
        $data = preg_replace("/<.*?>/", " ", $data);
        $contents = $data;
        $this->environment_canada_contents = $data;
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
            $this->getEnvironmentCanada();
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
        $web = "<b>Environment Canada Agent</b>";
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

        $sms_message = "ENVIRONMENT CANADA | ";
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

    public function lightstationEnvironmentcanada($text = null) {

// https://weather.gc.ca/marine/weatherConditions-lightstation_e.html?mapID=02&siteID=15300&stationID=46131
// https://www.ndbc.noaa.gov/station_page.php?station=46131
// https://www.ndbc.noaa.gov/radial_search.php?lat1=49.910N&lon1=124.980W&uom=E&dist=250

    }

    public function cityEnvironmentcanada($text) {
// dev
// find city code from text.

    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
//        if (!isset($this->input) or $this->input == null) {
//            return;
//        }
        //        $this->response .= 'Weather heard, "' . $this->input .'". ';

//        $this->doEnvironmentCanada($this->input);

$input = $this->input;
$filtered_input = $this->assert($input);

        $this->num_hits = 0;

        $keywords = $this->keywords;

        $pieces = explode(" ", strtolower($this->input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 2) {
            if ($this->input == 'environment canada') {
                $this->response =
                    $this->current_conditions .
                    " > " .
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
