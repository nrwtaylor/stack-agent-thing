<?php
namespace Nrwtaylor\StackAgentThing;

class Latitude extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->default_latitude = false;

        if (isset($this->thing->container['stack']['latitude'])) {
                $this->default_latitude =
                    $this->thing->container['stack']['latitude'];
        }

    }

    function get() {

        $this->latitude_agent = new Variables($this->thing, "variables latitude " . $this->from);

        $latitude = $this->latitude_agent->getVariable("latitude");

        if (is_numeric($latitude)) {
            $this->latitude = $latitude;
        } else {
            $this->latitude = $this->default_latitude;
        }

        $this->refreshed_at = $this->latitude_agent->getVariable("refreshed_at");
    }

    function set() {

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
            $array = array('observation', 'polaris', 'sun');
            $k = array_rand($array);
            $v = $array[$k];

            if (!is_numeric($this->latitude)) {
                $response = "No latitude available. ";
            }

//            if ($this->latitude !== false) {
//                $response = "Latitude is " . $this->latitude .". ";
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
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        //return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("latitude" => array("longitude", "time"));

        $latitude_text = "";
        if (is_numeric($this->latitude)) {$latitude_text = $this->latitude . " ";}

        $sms = "LATITUDE ". $latitude_text . "| " . $this->message . " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    public function extractLatitude($text = null) {

        if ($text == null) {return true;}

        $tokens = explode(" ",trim($text));
        $latitudes = [];

        foreach ($tokens as $i=>$token) {
            $sign = +1;
            $last_character = strtolower(substr(trim($text), -1));
            $text_token = $token;
            if (($last_character == "n") or ($last_character == "s")) {

                if ($last_character == "n") {$sign = +1;}
                if ($last_character == "s") {$sign = -1;}
                $text_token = mb_substr($token, 0, -1);
            }

            if (is_numeric($text_token)) {$latitudes[] = $sign * $text_token;}

        }
        if (count($latitudes) == 1) {$this->latitude = $latitudes[0];}

    }


    public function readSubject()
    {
        $input = $this->input;
        $this->extractLatitude($input);
        return false;
    }
}
