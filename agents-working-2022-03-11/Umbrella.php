<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Umbrella extends Agent
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

    public $var = "hello";

    function init()
    {
        $this->test = "Development code"; // Always

        $this->keywords = ["weather"];

        $this->current_conditions = null;
        $this->forecast_conditions = null;

        if ($this->verbosity == false) {
            $this->verbosity = 2;
        }
    }

    function set()
    {
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

    function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "umbrella" . " " . $this->from
        );

        $this->state = $this->variables_agent->getVariable("state");

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

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["help"] = "This reads a web resource.";
    }

    public function makeWeb()
    {
        $web = "<b>Umbrella Agent</b>";
        $web .= "<p>";

        $web .=
            '<iframe title="Environment Canada Weather" width="300px" height="191px" src="//weather.gc.ca/wxlink/wxlink.html?cityCode=bc-74&amp;lang=e" allowtransparency="true" frameborder="0"></iframe>';

        $web .= "<p>";
        $web .= "current umbrellas is " . $this->current_umbrella . "<br>";
        $web .= "forecast umbrella is " . $this->forecast_umbrella . "<br>";

        $web .= "data from " . $this->weather->link . "<br>";
        $web .= "data source is Environment Canada" . "<br>";

        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    function getUmbrella()
    {
        $this->weather = new Weather($this->thing, "weather");

        $current_message = "dry";
        $forecast_message = "dry";

        $this->pop_current = $this->probabilityRain(
            $this->weather->current_conditions
        );

        $rain_current = $this->isRain($this->weather->current_conditions);
        if ($rain_current) {
            $current_message = "rain";
        }
        if (is_numeric($this->pop_current) and $this->pop_current != 0) {
            $current_message .= " " . $this->pop_current . "%";
        }

        $this->pop_forecast = $this->probabilityRain(
            $this->weather->forecast_conditions
        );

        $rain_forecast = $this->isRain($this->weather->forecast_conditions);
        if ($rain_forecast) {
            $forecast_message = "rain";
        }
        if (is_numeric($this->pop_forecast) and $this->pop_forecast != 0) {
            $forecast_message .= " " . $this->pop_forecast . "%";
        }

        $this->current_umbrella = $current_message;
        $this->forecast_umbrella = $forecast_message;
    }

    public function makeSMS()
    {
        $sms_message = "UMBRELLA";

        if (!isset($this->message)) {
            $this->makeMessage();
        }
        $sms_message .= " | " . $this->raw_message;

        $sms_message .= " | " . $this->current_umbrella;

        $sms_message .= " > " . $this->forecast_umbrella;

        $sms_message .= " | link " . $this->weather->link;
        $sms_message .= " | data source Environment Canada";

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    public function makeMessage()
    {
        $message = "Your call.";

        // % pop now
        if ($this->pop_current == null and $this->pop_forecast == null) {
            $message = "No.";
        }
        if ($this->pop_current == null and $this->pop_forecast >= 30) {
            $message = "Not for now.";
        }
        if ($this->pop_current == null and $this->pop_forecast >= 40) {
            $message = "Not for now.";
        }

        // 30% pop now
        if ($this->pop_current >= 30 and $this->pop_forecast == null) {
            $message = "Maybe.";
        }
        if ($this->pop_current >= 30 and $this->pop_forecast >= 30) {
            $message = "Maybe. And keep it with you.";
        }
        if ($this->pop_current >= 30 and $this->pop_forecast >= 40) {
            $message = "Maybe. And getting wetter.";
        }

        // 40% pop now
        if ($this->pop_current >= 40 and $this->pop_forecast == null) {
            $message = "Possibly.";
        }
        if ($this->pop_current >= 40 and $this->pop_forecast >= 30) {
            $message = "Possibly. And keep it with you.";
        }
        if ($this->pop_current >= 40 and $this->pop_forecast >= 40) {
            $message = "Possibly. And getting wetter.";
        }

        // 60% pop now
        if ($this->pop_current >= 60 and $this->pop_forecast == null) {
            $message = "Yes.";
        }
        if ($this->pop_current >= 60 and $this->pop_forecast >= 30) {
            $message = "Yes.";
        }
        if ($this->pop_current >= 60 and $this->pop_forecast >= 40) {
            $message = "Yes.";
        }

        // dev refactor
        $this->raw_message = $message;

        //$message = "Weather is " . $this->current_umbrella . ".";
        $message .= " " . "Data courtesy of Environment Canada.";

        $this->message = $message;
        $this->thing_report["message"] = $message;
    }

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

    public function isRain($haystack)
    {
        $needles = ["rain", "showers", "drizzle", "showers"];

        foreach ($needles as $needle) {
            if (strpos($haystack, $needle) !== false) {
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

        $haystack = str_replace("%", " percent ", $haystack);

        $number = null;
        $pieces = explode(" ", $haystack);
        $keywords = ["percent", "%"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "percent":
                        case "%":
                            $number = $pieces[$key - 1];
                            break;
                        default:
                        // drop through
                    }
                }
            }
        }

        $probability_of_precipitation = floatval($number);

        return $probability_of_precipitation;
    }

    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;

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

        $this->getUmbrella();

        return;
    }
}
