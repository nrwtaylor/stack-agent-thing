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

    public function doResponse()
    {
        if ($this->agent_input == null) {
            $array = array('Hmmmm.', 'Is that so.', 'Right.');
            $k = array_rand($array);
            $v = $array[$k];

            $response = strtolower($v) . ".";

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

$parts = explode('. ', $thing_report['sms']);
$thing_report['sms'] = $parts[0] . ".";


$thing_report['sms'] = "[dev apply limit] " . $thing_report['sms'];

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
        // Don't respond to responses.
        if ($this->agent_name == "response") {
            return;
        }

// conditionResponse.
//$this->response = $this->conditionResponse($this->response);

        if ($agent_flag == true) {
            if (!isset($this->thing_report["sms"])) {
                $this->thing_report["sms"] = "AGENT | Standby.";
            }
            $this->thing_report['sms'] .= " response added " . $this->agent_name;

            $this->thing_report["message"] = $this->thing_report["sms"];
            if ($this->agent_input == null or $this->agent_input == "" or $this->agent_input == 'response') {
                $message_thing = new Message($this->thing, $this->thing_report);
                $this->thing_report["info"] =
                    $message_thing->thing_report["info"];
            }
        }
    }

    public function makeResponse($text = null) {
       if ($text == null) {return true;}
       if (isset($this->meta_string)) {$this->response = $this->meta_string . " - " . $this->response;} 
       if (!isset($this->meta_string)) {$this->response = "no meta" . " - " . $this->response;}

       $this->thing_report['response'] = $this->response;

       return $this->response;
    }

    public function metaResponse() {
        $this->getMeta();
        $t = "";
        $t .= $this->meta;
        $t .= " " . $this->meta_string;
        $t .= " " . $this->agent_name;
       return $t;
    }

    public function makeSMS()
    {
        $this->sms_message = "RESPONSE | " . "" . $this->response_message . " " . $this->response;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
if ($this->hasText($this->input, "meta")) {

$this->response .= $this->metaResponse() ." ";
}

        return false;
    }
}
