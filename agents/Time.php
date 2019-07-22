<?php
/**
 * Cat.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Time extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
        $this->agent_name = "time";
        $this->test= "Development code";
        $this->thing_report["info"] = "This connects to an authorative time server.";
        $this->thing_report["help"] = "Get the time. Text CLOCKTIME.";
    }


    /**
     *
     * @return unknown
     */

    public function respond() {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "time";

        $this->makeSMS();
        $this->makeChoices();

        //$this->thing_report["info"] = "This is a ntp in a park.";
        //$this->thing_report["help"] = "This is finding picnics. And getting your friends to join you. Text RANGER.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }


    /**
     *
     */
    function makeSMS() {
        $this->node_list = array("time"=>array("time"));
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }

// function query_time_server

    /**
     *
     */
    function makeChoices() {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    function doTime($text = null) {

        // If we didn't receive the command NTP ...

if (strtolower($this->input) != "time") {
    $this->time_message = $this->time_response;
$this->response = $this->time_response;
return;
}

        if ($this->agent_input == null) {

$timevalue = $this->current_time;

$this->time_zone = 'America/Vancouver';

// if no error from query_time_server
if(true) {

$datum = new \DateTime("$timevalue", new \DateTimeZone("UTC"));


                $datum->setTimezone(new \DateTimeZone($this->time_zone));

    $m = "Time check from stack server ". $this->web_prefix. ". ";
$m .= "In the timezone " . $this->time_zone . ", it is " . $datum->format('l') . " " . $datum->format('d/m/Y, H:i:s') .". ";

}
else
{
    $m =  "Unfortunately, the time server $timeserver could not be reached at this time. ";
}

            $this->response = $m;
            $this->time_message = $this->response;

        } else {
            $this->time_message = $this->agent_input;
        }

    }



    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->doTime($this->input);

        return false;
    }


}

