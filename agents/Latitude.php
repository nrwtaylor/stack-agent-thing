<?php
namespace Nrwtaylor\StackAgentThing;

class Latitude extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->default_latitude = 0;


        if (isset($this->thing->container['stack']['latitude'])) {
                $this->default_latitude =
                    $this->thing->container['stack']['latitude'];
        }

        $this->latitude = $this->default_latitude;



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

            $response = strtolower($v) . ".";

            $this->message = $response; // mewsage?
        } else {
            $this->message = $this->agent_input;
        }
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "cat");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

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
        $this->sms_message = "LATITUDE | " . $this->message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
