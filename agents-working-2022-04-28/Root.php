<?php
namespace Nrwtaylor\StackAgentThing;

class Root extends Agent
{
    public $var = 'hello';

// Handles calls for root.
// Blankly.

    function init()
    {
    }

    function run()
    {
        $this->doRoot();
    }

    public function doRoot()
    {
            $this->message = "Hello"; // mewsage?
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This handles calls for root.";
        $this->thing_report["help"] = "No help available for this command.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("root" => array("root"));
        $this->sms_message = "" . $this->message;
        $this->thing_report['sms'] = $this->message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "root");
        $choices = $this->thing->choice->makeLinks('cat');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
