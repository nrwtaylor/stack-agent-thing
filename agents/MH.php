<?php
namespace Nrwtaylor\StackAgentThing;

class MH extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function run()
    {
        $this->doMH();
    }

    public function doMH()
    {
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "MH | " . strtolower($v) . ".";

            $this->mh_message = $response; // mewsage?
        } else {
            $this->mh_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is an agent to handle the MH email format.";
        $this->thing_report["help"] =
            "This mostly deals with equal signs at the end of lines.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function metaMH($text = null)
    {
        if ($text == null) {
            return;
        }

        // Test and dev.
        // Extract subject line
        // $this->subject = $this->subjectMH($text);
        $this->meta = ["subject" => $this->subject];
    }

    public function subjectMH($text = null)
    {
        if ($text !== null) {
            $datagram = $this->readEmail($text);
            return $datagram["subject"];
        }

        if (isset($this->datagram["subject"])) {
            return $this->datagram["subject"];
        }

        // Test and dev.
        // Extract subject line
        $subject = "TODO Extract subject line - see MH.php";
        return $subject;
    }

    public function bodyMH($text = null)
    {
        if ($text !== null) {
            $datagram = $this->readEmail($text);
            return $datagram["text"];
        }

        if (isset($this->datagram["text"])) {
            return $this->datagram["text"];
        }
    }

    public function textMH($text = null)
    {
        if ($text == null) {
            return;
        }

        // quoted_printable_decode removes the last ?=
        // which causes problems when reading a UTF8 encoded subject

        $text = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $text);
        //        $text = quoted_printable_decode($text);

        return $text;

        // Test and dev.

        $lines = preg_split("/\r\n|\n|\r/", $text);

        $new_lines = [];
        $temp_line = "";
        foreach ($lines as $i => $line) {
            if (substr($line, -1) === "=") {
                $filtered_line = rtrim($line, "=");
                $temp_line .= $filtered_line;
            }

            if (substr($line, -1) !== "=") {
                $new_lines[] = $temp_line . $line;
                $temp_line = "";
            }
        }

        $contents = implode("\n", $new_lines);

        return $contents;
    }

    public function readMH($text = null)
    {
        if ($text == null) {
            return;
        }
        $this->email = $this->readEmail($text);
        $this->subject = $this->subjectMH($text);
        $this->meta = $this->metaMH($text);
        $this->contents = $this->textMH($text);
    }

    function makeSMS()
    {
        $this->node_list = ["mh" => ["mh", "dog"]];
        $this->sms_message = "" . $this->mh_message;
        $this->thing_report["sms"] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("channel", $this->node_list, "mh");
        $choices = $this->thing->choice->makeLinks("mh");
        $this->thing_report["choices"] = $choices;
    }

    public function readSubject()
    {
        $input = $this->input;
        return false;
    }
}
