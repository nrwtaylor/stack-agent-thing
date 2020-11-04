<?php
namespace Nrwtaylor\StackAgentThing;

class Dateline extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->at_agent = new At($this->thing, "at");

        $this->test_url = null;
        if (isset($this->thing->container['api']['dateline']['test_url'])) {
            $this->test_url =
                $this->thing->container['api']['dateline']['test_url'];
        }
        $this->url_agent = new Url($this->thing,"url");

    }

    function run()
    {
        $this->doDateline();
    }

    public function get()
    {
        //$this->test();
        //$this->extractDateline();
    }

    public function test()
    {
        if (!is_string($this->test_url)) {
            return false;
        }

        $url = $this->test_url;
        $read_agent = new Read($this->thing, $url);

        $paragraph_agent = new Paragraph($this->thing, $read_agent->contents);

        $paragraphs = $paragraph_agent->paragraphs;

        $arr = ['year', 'month', 'day', 'day_number', 'hour', 'minute'];

        foreach ($paragraphs as $i => $paragraph) {
            $dateline = $this->extractDateline($paragraph);
            if ($dateline == false) {continue;}
            echo $dateline['dateline'] . "\n" . $dateline['line'] . "\n";

        }
    }

    public function extractDateline($text = null)
    {
        //$url_agent = new Url($this->thing,"url");
        $text = $this->url_agent->stripUrls($text);
        $paragraph = $text;

        $arr = ['year', 'month', 'day', 'day_number', 'hour', 'minute'];

        if ($paragraph == "") {
            return false;
        }
        $t = $this->at_agent->extractAt($paragraph);

        $flag = false;
        $date = [];

        foreach ($arr as $component) {
            $this->{$component} = $this->at_agent->{$component};

            if ($this->{$component} !== false) {
                $flag = true;
            }
            $date[$component] = $this->{$component};
        }

        if ($flag === false) {
            // No components seen
            return false;
        }

        $dateline = $this->textDateline($date);
        $date['line'] = $paragraph;
        $date['dateline'] = $dateline;

//        echo $date['dateline'] . "\n" . $date['line'] . "\n";
        return $date;
    }

    public function textDateline($dateline)
    {
        $text = "";
        foreach ($dateline as $key => $value) {
            if ($value === false) {
                continue;
            }
            $text .= $key . " " . $value . " ";
        }

        return $text;
    }

    public function doDateline()
    {
        if ($this->agent_input == null) {
            $array = ['where are you?'];
            $k = array_rand($array);
            $v = $array[$k];

            if (isset($this->dateline['dateline'])) {
                $v = $this->dateline['dateline'];
            }

            //$response = "DATELINE | " . strtolower($v) . ".";

            $this->dateline_message = $v; // mewsage?
        } else {
            $this->dateline_message = $this->agent_input;
        }
    }

    function getNegativetime()
    {
        $agent = new  Negativetime($this->thing, "dateline");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a dateline keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = ["dateline" => ["dateline", "dog"]];

        $sms = "DATELINE | " . $this->dateline_message;

        $this->sms_message = "" . $sms;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "dateline");
        $choices = $this->thing->choice->makeLinks('dateline');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);
        if ($this->agent_input != null) {
            $input = $this->agent_input;
        }

        if ($input == "dateline") {
            return;
        }
        if ($input == "dateline test") {
            $this->test();
        }

        $this->dateline = $this->extractDateline($input);
    }
}
