<?php
namespace Nrwtaylor\StackAgentThing;

class Latitude extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->default_latitude = false;

        if (isset($this->thing->container["stack"]["latitude"])) {
            $this->default_latitude =
                $this->thing->container["stack"]["latitude"];
        }
    }

    function get()
    {
        // Can we get the latitude from Kplex?
        //$this->kplex_agent = new Kplex($this->thing, "kplex");
        //var_dump($this->kplex_agent);
        //exit();
        //$this->listenKplex();
        //var_dump($this->snapshot);
        //exit();
        //if (!isset($this->snapshot)) {

        //$this->listenKplex();

        //}
        //var_dump($this->snapshot);
        $this->latitude_agent = new Variables(
            $this->thing,
            "variables latitude " . $this->from
        );

        $latitude = $this->latitude_agent->getVariable("latitude");
        if (is_numeric($latitude)) {
            $this->latitude = $latitude;
        } else {
            $this->latitude = $this->default_latitude;
        }

        $this->refreshed_at = $this->latitude_agent->getVariable(
            "refreshed_at"
        );
    }

    public function formatLatitude($text = null, $pattern = null)
    {
        if ($text == null) {
            return null;
        }

        $sign = "N";
        if ($text > 0) {
            $sign = "N";
        } else {
            $sign = "S";
            $text = abs($text);
        }
        //$arr = $this->dmsDegree($text);
        if (is_numeric($text)) {
            return $text . $sign;
        }

        return $text;
    }

    function set()
    {
        $this->latitude_agent->setVariable("latitude", $this->latitude);
        $this->latitude_agent->setVariable("refreshed_at", $this->current_time);
    }

    function run()
    {
        $this->doLatitude();
    }

    public function doLatitude()
    {
        if ($this->agent_input == null) {

            $response = "Got longitude. ";

            if (!is_numeric($this->latitude)) {
                $response = "No latitude available. ";
            }

            $this->message = $response; // mewsage?
        } else {
            $this->message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];

        //return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = ["latitude" => ["longitude", "time"]];

        $latitude_text = "";
        if (is_numeric($this->latitude)) {
            $latitude_text = $this->latitude . " ";
        }

        $sms =
            "LATITUDE " .
            $latitude_text .
            "| " .
            $this->message .
            " " .
            $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
    }

    public function extractLatitude($text = null)
    {
        if ($text == null) {
            return true;
        }
        if ($text == "null") {
            return true;
        }

        $tokens = explode(" ", trim($text));
        $latitude = false;
        $latitudes = [];

        foreach ($tokens as $i => $token) {
            $sign = +1;
            $last_character = strtolower(substr(trim($token), -1));
            $text_token = $token;
            if ($last_character == "n" or $last_character == "s") {
                if ($last_character == "n") {
                    $sign = +1;
                }
                if ($last_character == "s") {
                    $sign = -1;
                }
                $text_token = mb_substr($token, 0, -1);
           // }

            if (is_numeric($text_token)) {
                $latitudes[] = $sign * $text_token;
            }
            }
        }

        $nmea_response = $this->readNMEA($text);

        if (
            isset($nmea_response["current_latitude"]) and
            isset($nmea_response["current_latitude_north_south"])
        ) {
            if (
                strtolower($nmea_response["current_latitude_north_south"]) ==
                "e"
            ) {
                $sign = +1;
            }
            if (
                strtolower($nmea_response["current_latitude_north_south"]) ==
                "w"
            ) {
                $sign = -1;
            }
            $latitude = $nmea_response["current_latitude"] * $sign;
            $latitudes[] = $latitude;
        }

        if (
            isset($nmea_response["latitude"]) and
            isset($nmea_response["latitude_north_south"])
        ) {
            if (strtolower($nmea_response["latitude_north_south"]) == "e") {
                $sign = +1;
            }
            if (strtolower($nmea_response["latitude_north_south"]) == "w") {
                $sign = -1;
            }
            $latitude = $nmea_response["latitude"] * $sign;
            $latitudes[] = $latitude;
        }
        if (count($latitudes) == 1) {
            $latitude = $latitudes[0];
        }

        return $latitude;
    }

    public function readSubject()
    {
        $input = $this->input;
        $latitude = $this->extractLatitude($input);
        if ($latitude !== false) {
            $this->latitude = $latitude;
        }
        return false;
    }
}
