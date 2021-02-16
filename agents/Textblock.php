<?php
namespace Nrwtaylor\StackAgentThing;

// dev
// TODO integrate with dateline reader

class Textblock extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function run()
    {
        $this->doTextblock();
    }

    public function get()
    {
    }

    public function test()
    {
    }

    public function blocksTextblock($contents)
    {
        $lines = explode("\n", $contents);

        //$paragraphs = $this->extractParagraphs($contents);
        return $lines;
    }

    public function textTextblock($textblock)
    {
        $text = implode(" ", $textblock);

        return $text;
    }

    public function doTextblock()
    {
        if ($this->agent_input == null) {
            $array = ["where are you?"];
            $k = array_rand($array);
            $v = $array[$k];

            $this->textblock_message = $v; // mewsage?
        } else {
            $this->textblock_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a text block. A group of text lines.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $this->node_list = ["textblock" => ["textblock", "dog"]];

        $sms = "TEXTBLOCK ";

        if (isset($this->textblock)) {
        }
        $sms .= $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeChoices()
    {
        $this->thing_report["choices"] = false;
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);
        if ($this->agent_input != null) {
            $input = $this->agent_input;
        }
        if ($input == "textblock") {
            $this->response .= "Saw agent command. ";
            return;
        }

        if ($input == "textblock test") {
            $this->test();
            return;
        }

        $this->textblock = $this->extractTextblock($input);
    }
}
