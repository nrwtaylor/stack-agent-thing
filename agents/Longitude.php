<?php
namespace Nrwtaylor\StackAgentThing;

class Longitude extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->default_longitude = false;


        if (isset($this->thing->container['stack']['longitude'])) {
                $this->default_longitude =
                    $this->thing->container['stack']['longitude'];
        }
    }

    function run()
    {
        $this->doLongitude();
    }

    public function doLongitude()
    {
        if ($this->agent_input == null) {
            $array = array('board', 'longitude', 'meridian');
            $k = array_rand($array);
            $v = $array[$k];

            if (!is_numeric($this->longitude)) {
                $response = "No longitude available. ";
            }

//            if ($this->longitude !== false) {
//                $response = "Longitude is " . $this->longitude .". ";
//            }

            $this->message = $response; // mewsage?
        } else {
            $this->message = $this->agent_input;
        }
    }

    function get() {

        $this->longitude_agent = new Variables($this->thing, "variables longitude " . $this->from);

        $longitude = $this->longitude_agent->getVariable("longitude");

        if (is_numeric($longitude)) {
            $this->longitude = $longitude;
        } else {
            $this->longitude = $this->default_longitude;
        }

        $this->refreshed_at = $this->longitude_agent->getVariable("refreshed_at");
    }

    function set() {

        $this->longitude_agent->setVariable("longitude", $this->longitude);
        $this->longitude_agent->setVariable("refreshed_at", $this->current_time);

    }


    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        //return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("longitude" => array("latitude", "time"));

        $longitude_text = "";
        if (is_numeric($this->longitude)) {$longitude_text = $this->longitude . " ";}
        $sms = "LONGITUDE ". $longitude_text . "| " . $this->message . " " . $this->response;
;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    public function extractLongitude($text = null) {

	if ($text == null) {return true;}

        $tokens = explode(" ",trim($text));
        $longitudes = [];

        foreach ($tokens as $i=>$token) {
            $sign = +1;
            $last_character = strtolower(substr(trim($text), -1));
            $text_token = $token;
            if (($last_character == "w") or ($last_character == "e")) {

                if ($last_character == "w") {$sign = -1;}
                if ($last_character == "e") {$sign = +1;}
                $text_token = mb_substr($token, 0, -1);
            }

            if (is_numeric($text_token)) {$longitudes[] = $sign * $text_token;}

        }
        if (count($longitudes) == 1) {$this->longitude = $longitudes[0];}

    }

    public function readSubject()
    {
        $input = $this->input;
        $this->extractLongitude($input);
        return false;
    }
}
