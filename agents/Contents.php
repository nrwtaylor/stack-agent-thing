<?php
namespace Nrwtaylor\StackAgentThing;

// Take a URI (URI or file reference) and get contents.

class Contents extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test_url = null;
        if (isset($this->thing->container['api']['dateline']['test_url'])) {
            $this->test_url =
                $this->thing->container['api']['dateline']['test_url'];
        }
        $this->url_agent = new Url($this->thing, "url");
    }

    function run()
    {
        $this->runContents();
    }

    public function get()
    {
    }

    public function test()
    {
        if (!is_string($this->test_url)) {
            return false;
        }

        $url = $this->test_url;
        $read_agent = new Read($this->thing, $url);

        $this->contents = $read_agent->contents;
    }

    public function getContents($text = null)
    {
        if ($this->instruction == "") {
            return false;
        }

        $url = $this->instruction;
        $read_agent = new Read($this->thing, $url);
        $this->contents = $read_agent->contents;
    }

    public function isContents($contents = null)
    {
        if ($contents === false) {
            return false;
        }

        if ($contents === null) {
            return false;
        }

        if ($contents === true) {
            return false;
        }

        if (!is_string($contents)) {
            return false;
        }

        return true;
    }

    public function runContents()
    {
        if ($this->agent_input == null) {
            $v = 'Contents.';

            $this->contents_message = $v; // mewsage?
        } else {
            $this->contents_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This gets the contents of a Uniform Resource Locator (URI).";
        $this->thing_report["help"] =
            "This is about getting the contents of a pointer.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = ["contents" => ["contents", "uri", "read"]];

        $sms = "CONTENTS ";
        // . $this->dateline_message;

        $sms .= $this->contents_message . " ";
        $sms .= $this->response;

        $this->sms_message = "" . $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        $input = $this->input;

        $filtered_input = $this->assert($input);

        $this->instruction = $filtered_input;
        if ($input == "contents test") {
            $this->test();
            return;
        }
        $this->getContents();
    }
}
