<?php
namespace Nrwtaylor\StackAgentThing;

class Emacs extends Agent
{
    public $var = "hello";

    function init()
    {
       $this->emacs_default_buffer = $this->settingsAgent(['emacs','default_buffer']);
    }

    function claws()
    {
    }

    function test()
    {
        if ($this->emacs_test_flag != "on") {
            return;
        }
        $this->response .= "No test performed. ";
    }

    public function respondResponse()
    {
        $this->thing_report["info"] =
            "This is a tool interacting with EMACS.";
        $this->thing_report["help"] = "No user facing actions available.";

        $this->thing_report["message"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function updateEmacs($text = null, $filename = null)
    {
        if ($text == null) {
            return true;
        }

        if ($filename == null) {
           if ($this->emacs_default_buffer == null) {return true;}
           $filename = $this->emacs_default_buffer;
        }
        $filename = trim($filename, '"');

        // Check if the text is already in the buffer.
        if (file_exists($filename)) {

            $contents = file_get_contents($filename);

            if (strpos($contents, $text) !== false) {
                $this->response .= 'Text already in buffer. ';
                return true;
            }
        }

        file_put_contents($filename, $text, FILE_APPEND | LOCK_EX);
    }

    public function textEmacs($event) {

            $text = "";
            $dateline = $this->timestampDateline($event["dateline"]);
            $date_text = substr($dateline, 0, 10);
            $day_text = ucwords(strtolower($event["dateline"]["day"]));
            $time_text = substr($dateline, 11, 5);
            $timestamp_text = $date_text . " " . $day_text . " " . $time_text;

            $text .= "**" . " " . $event["subject"] . "\n";

            $call = $event["call"];

            $text .= "   " . "SCHEDULED: <" . $timestamp_text . ">\n";
            $text .= "   " . $call["url"] . "\n";
            $text .=
                "   " . "password " . $call["password"] . " ";
            $text .= ($call['access_code'] ? "/ access code " . $call["access_code"] : null); 
            $text .= "\n";

            $text .= "   " . "stack " . $call["service"];
            $text .= "\n";
            return $text;

    }

    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = trim($this->assert($input));

    }
}
