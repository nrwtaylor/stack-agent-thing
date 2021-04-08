<?php
namespace Nrwtaylor\StackAgentThing;

class Cykz extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    public function readCykz($text = null)
    {
        if ($this->agent_input == null) {
            $array = [
                "We will be at Sunset and Roundhouse from 1100. Expect traffic on VE7RVF and 146.580 from 1330 to 1600.",
            ];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "CYKZ | " . $v;

            $this->cykz_message = $response;
        } else {
            $this->cykz_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["info"] =
            "This is saying you are here, when someone needs you.";
        $this->thing_report["help"] = "This is about being very consistent.";

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];

    }

    function makeSMS()
    {
        $this->node_list = ["cykz" => ["stay", "go", "game"]];
        $this->sms_message = "" . $this->cykz_message;

        $this->thing_report["sms"] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("channel", $this->node_list, "cykz");
        $choices = $this->thing->choice->makeLinks("cykz");
        $this->thing_report["choices"] = $choices;
    }

    public function readSubject()
    {
        $this->readCykz();
    }
}
