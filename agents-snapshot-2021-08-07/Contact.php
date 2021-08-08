<?php
namespace Nrwtaylor\StackAgentThing;

class Contact extends Agent
{
    public $var = 'hello';

    function init() {
    }

    public function run() {

        if ($this->agent_input == null) {
            $array = [""];
            $k = array_rand($array);
            $v = $array[$k];

            $response = $v;

            $this->contact_message = $response;
        } else {
            $this->contact_message = $this->agent_input;
        }


}

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "contact");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["info"] =
            "This is saying you are here, when someone needs you.";
        $this->thing_report["help"] =
            "This is also about being very consistent.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = ["contact" => ["stay", "go", "game"]];
        $this->sms_message = "CONTACT | " . $this->contact_message;
        $this->sms_message .= $this->response;

        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "contact");
        $choices = $this->thing->choice->makeLinks('contact');
        $this->thing_report['choices'] = $choices;
    }

    public function readContact($text = null)
    {
        $flag = false;
        if ($text == null) {
            $text = $this->subject;
        }

        $input = strtolower($text);
        $this->contact_input = trim(str_replace("contact", "", $input));

        if ($this->contact_input === "") {
            $this->response .= "Go ahead. ";
            return $this->contact_input;
        }

        $callsign_agent = new Callsign($this->thing, "callsign");
        $t = $callsign_agent->getCallsign($this->contact_input);

        if ($t != false and stripos($input, $t['callsign']) !== false) {
            $this->response .= $t['callsign'] . ". ";
            return $this->contact_input;
        }

        $this->response .= "Noted. ";

        return $this->contact_input;
    }

    public function readSubject()
    {
        $this->readContact();
        return false;
    }
}
