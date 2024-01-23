<?php
namespace Nrwtaylor\StackAgentThing;

class Watch extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->bell_handler = new Bells($this->thing, "bells");
        $this->bell_handler->agent_input = null;
        $this->bell_handler->doBells();

        $this->bells = $this->bell_handler->bells;
        $this->bell = $this->bell_handler->bell;

        $julian_day = $this->julianDay();

        if ($this->isOdd(intval($julian_day))) {
            $this->dog_watch = "A";
        } else {
            $this->dog_watch = "B";
        }
    }

    function run()
    {
        $this->doWatch();
    }

    public function doWatch()
    {
        if ($this->agent_input == null) {
            $array = ["A", "B"];
            $k = array_rand($array);
            $v = $array[$k];
            $response = "WATCH | " . "dog watch " . $this->dog_watch . ".";
            $this->watch_message = $response; // mewsage?
        } else {
            $this->watch_message = $this->agent_input;
        }
    }

    function makeSMS()
    {
        $this->node_list = ["watch" => ["watch"]];
        $this->sms_message = "" . $this->watch_message;
        $this->thing_report["sms"] = $this->watch_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("channel", $this->node_list, "watch");
        $choices = $this->thing->choice->makeLinks("watch");
        $this->thing_report["choices"] = $choices;
    }

    public function readSubject()
    {
        return false;
    }
}
