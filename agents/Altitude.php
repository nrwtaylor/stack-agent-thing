<?php
namespace Nrwtaylor\StackAgentThing;

class Altitude extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->default_altitude = false;
        $this->default_units = "metres";

        if (isset($this->thing->container["stack"]["altitude"])) {
            $this->default_altitude =
                $this->thing->container["stack"]["altitude"]["altitude"];
            $this->default_units =
                $this->thing->container["stack"]["altitude"]["units"];
        }
    }

    function get()
    {
        $this->altitude_agent = new Variables(
            $this->thing,
            "variables altitude " . $this->from
        );

        $altitude = $this->altitude_agent->getVariable("altitude");

        if (is_numeric($altitude)) {
            $this->altitude = $altitude;
        } else {
            $this->altitude = $this->default_altitude;
        }

        $this->refreshed_at = $this->altitude_agent->getVariable(
            "refreshed_at"
        );
    }

    function set()
    {
        $this->altitude_agent->setVariable("altitude", $this->altitude);
        $this->altitude_agent->setVariable("refreshed_at", $this->current_time);
    }

    function run()
    {
        $this->doAltitude();
    }

    public function doAltitude()
    {
        if ($this->agent_input == null) {
            $array = ["observation", "polaris", "sun"];
            $k = array_rand($array);
            $v = $array[$k];

            if (!is_numeric($this->altitude)) {
                $response = "No altitude available. ";
            }

            //            if ($this->altitude !== false) {
            //                $response = "Altitude is " . $this->altitude .". ";
            //            }

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
        $this->node_list = ["altitude" => ["longitude", "time"]];

        $altitude_text = "";
        if (is_numeric($this->altitude)) {
            $altitude_text = $this->altitude . " ";
        }

        $sms =
            "ALTITUDE " .
            $altitude_text .
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

    public function extractAltitude($text = null)
    {
        if ($text == null) {
            return true;
        }
        if ($text == "null") {
            return true;
        }

        $tokens = explode(" ", trim($text));
        $altitude = false;
        $altitudes = [];

        foreach ($tokens as $i => $token) {
            $sign = +1;
            $last_character = strtolower(substr(trim($text), -1));
            $text_token = $token;
            if ($last_character == "n" or $last_character == "s") {
                if ($last_character == "n") {
                    $sign = +1;
                }
                if ($last_character == "s") {
                    $sign = -1;
                }
                $text_token = mb_substr($token, 0, -1);
            }

            if (is_numeric($text_token)) {
                $altitudes[] = $sign * $text_token;
            }
        }

        $nmea_response = $this->readNMEA($text);

        if (isset($nmea_response["altitude"])) {
            $altitude = $nmea_response["altitude"];
            $altitudes[] = $altitude;
        }

        if (count($altitudes) == 1) {
            $altitude = $altitudes[0];
        }

        return $altitude;
    }

    public function readSubject()
    {
        $input = $this->input;
        $this->extractAltitude($input);
        return false;
    }
}
