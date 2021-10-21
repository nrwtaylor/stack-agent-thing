<?php
namespace Nrwtaylor\StackAgentThing;

class Response extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doResponse();
    }

    public function set() {
       $this->thing->Write(['response'], $this->response);
    }

    public function doResponse()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "RESPONSE | " . strtolower($v) . ".";

            $this->response_message = $response; // mewsage?
        } else {
            $this->response_message = $this->agent_input;
        }
    }

    public function limitResponse($thing_report = null) {
if ($thing_report == null) {
    $thing_report = $this->thing_report;
}

// dev placeholder for calling shorten.
// Needs to work through the whole thing_report.
$thing_report['sms'] = "[apply limit] " . $thing_report['sms'];

return $thing_report;
    }

    /**
     *
     */
    public function respondResponse()
    {
$t = $this->tokensLimit();
if ($t != null and in_array('response', $t)) {

$this->response .= "Saw limit response token. ";
$this->thing_report = $this->limitResponse();

}

        $agent_flag = true;
        if ($this->agent_name == "agent") {
            return;
        }

        if ($agent_flag == true) {
            if (!isset($this->thing_report["sms"])) {
                $this->thing_report["sms"] = "AGENT | Standby.";
            }

            $this->thing_report["message"] = $this->thing_report["sms"];
            if ($this->agent_input == null or $this->agent_input == "") {
                $message_thing = new Message($this->thing, $this->thing_report);
                $this->thing_report["info"] =
                    $message_thing->thing_report["info"];
            }
        }
    }

    function makeSMS()
    {
        $this->sms_message = "" . $this->response_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
        return false;
    }
}
