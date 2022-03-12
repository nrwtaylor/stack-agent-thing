<?php
namespace Nrwtaylor\StackAgentThing;

class Speed extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->default_speed = false;
        $this->default_units = "metres";

        if (isset($this->thing->container["stack"]["speed"])) {
            $this->default_speed =
                $this->thing->container["stack"]["speed"]["speed"];
            $this->default_units =
                $this->thing->container["stack"]["speed"]["units"];
        }
    }

    function get()
    {
        $this->speed_agent = new Variables(
            $this->thing,
            "variables speed " . $this->from
        );

        $speed = $this->speed_agent->getVariable("speed");

        if (is_numeric($speed)) {
            $this->speed = $speed;
        } else {
            $this->speed = $this->default_speed;
        }

        $this->refreshed_at = $this->speed_agent->getVariable("refreshed_at");
    }

    function set()
    {
        $this->speed_agent->setVariable("speed", $this->speed);
        $this->speed_agent->setVariable("refreshed_at", $this->current_time);
    }

    function run()
    {
        $this->doSpeed();
    }

    public function doSpeed()
    {
        if ($this->agent_input == null) {
            $array = ["observation", "polaris", "sun"];
            $k = array_rand($array);
            $v = $array[$k];

            if (!is_numeric($this->speed)) {
                $response = "No speed available. ";
            }

            //            if ($this->speed !== false) {
            //                $response = "Speed is " . $this->speed .". ";
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
        $this->node_list = ["speed" => ["longitude", "time"]];

        $speed_text = "";
        if (is_numeric($this->speed)) {
            $speed_text = $this->speed . " ";
        }

        $sms =
            "SPEED " .
            $speed_text .
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

    public function extractSpeed($text = null)
    {
        if ($text == null) {
            return true;
        }
        if ($text == "null") {
            return true;
        }

        $tokens = explode(" ", trim($text));
        $speed = false;
        $speeds = [];

        // dev todo
        // Recognize text speeds
        /*
        foreach ($tokens as $i=>$token) {
            $sign = +1;
            $last_character = strtolower(substr(trim($text), -1));
            $text_token = $token;
            if (($last_character == "n") or ($last_character == "s")) {

                if ($last_character == "n") {$sign = +1;}
                if ($last_character == "s") {$sign = -1;}
                $text_token = mb_substr($token, 0, -1);
            }

            if (is_numeric($text_token)) {$speeds[] = $sign * $text_token;}

        }
*/

        $nmea_response = $this->readNMEA($text);

        if (isset($nmea_response["speed_in_knots"])) {
            $speed = $nmea_response["speed_in_knots"];
            $speeds[] = $speed;
        }

        if (count($speeds) == 1) {
            $speed = $speeds[0];
        }

        return $speed;
    }

    public function readSubject()
    {
        $input = $this->input;
        $this->extractSpeed($input);
        return false;
    }
}
