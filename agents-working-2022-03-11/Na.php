<?php
namespace Nrwtaylor\StackAgentThing;

class Na extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doNa();
    }

    public function doNa()
    {
        if ($this->agent_input == null) {

            $response = "N/A.";

            $this->na_message = $response; // mewsage?
        } else {
            $this->na_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a na end-point.";
        $this->thing_report["help"] = "This is about nothing.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeSMS()
    {
        $this->node_list = array("na" => array("na"));
        $sms = "NA | " . $this->na_message;
        $this->sms_message = "" . $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "na");
        $choices = $this->thing->choice->makeLinks('na');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
    }
}
