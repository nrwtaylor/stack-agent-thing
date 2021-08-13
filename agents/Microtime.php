<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Microtime extends Agent
{
    public $var = 'hello';

    public function init()
    {
        $this->node_list = ["microtime" => ["microtime"]];
        $this->time_agent = new Time($this->thing, "time");
        $this->time_zone = $this->time_agent->time_zone;

    }

    public function deprecate_textMicrotime($timestamp)
    {
        $t = explode(".", $timestamp);

        $n = str_pad($t[1], 4, "0") / 10000;
        $text = $n . " " . $t[0];

        //$text = $this->thing->microtime($text);

        return $text;
    }

    public function epochtimeMicrotime($text = null)
    {
        if (is_numeric($text)) {
            return true;
        }
        if ($text == null) {
            $text = $this->getMicrotime();
            //$text = $this->thing->microtime();
        }

        $t = explode('.', $text);

        $non_micro = $t[0];
        $micro = $t[1];

        $non_micro_text = strtotime($non_micro);
        $timestamp = $non_micro_text .".". substr(str_replace("0.","",$micro),0,6);
        return $timestamp;
    }

    public function timestampMicrotime($text) {
        return text;
    }

    public function isMicrotime($state = null)
    {
        return true;
    }

    public function set($requested_state = null)
    {
        $this->refreshed_at = $this->current_time;

        $this->thing->Write(
            ["microtime", "refreshed_at"],
            $this->current_time
        );

    }

    public function get()
    {
        // If it is a valid previous_state, then
        // load it into the current state variable.


        $time_string = $this->thing->Read([
            "microtime",
            "refreshed_at",
        ]);
        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["microtime", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $timestamp_string = $this->thing->Read([
            "microtime",
            "timestamp",
        ]);

        if ($timestamp_string == false) {
            $timestamp_string = $this->getMicrotime();
            $this->thing->Write(
                ["microtime", "timestamp"],
                $timestamp_string
            );
        }

        $this->timestamp = $timestamp_string;
    }

    public function getMicrotime() {
        // Get the microtime signal from the Thing.
        // Process it to the desired microtime timestring.

        $timestamp_string = $this->thing->microtime();

        $t = explode(' ', $timestamp_string);

        $non_micro = $t[0] . " " . $t[1];
        $micro = $t[2];

// TODO Make timezone aware.
// See Time.
//        $this->datum = $this->time_agent->doTime($non_micro); //non_micro
//        $non_micro_text = $this->time_agent->timestampTime($this->datum);
        $non_micro_text = $non_micro;

        $timestamp_string = trim($non_micro_text) .".". substr(str_replace("0.","",$micro),0,6);

        return $timestamp_string;
    }

    public function respondResponse()
    {
        //$this->makeChoices();
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This agent handles microtime.";
        $this->thing_report["help"] = 'Try MICROTIME.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeTXT()
    {
        $sms = "MICROTIME " . "\n";

        $sms .= trim($this->short_message) . "\n";

        $this->sms_message = $sms;
        $this->thing_report['txt'] = $sms;
    }

    function makeSMS()
    {
        $sms = "MICROTIME " . "\n";

        // Parsers may not recognize
        // 2020-11-05 18:45:49.70520700
        // So display as with six digits of precision.
        // 2020-11-05 18:45:49 0.705207

        $sms .= $this->timestamp . "\n";
        $sms .= $this->epochtimeMicrotime($this->timestamp) . "\n";
//        $sms .= trim($this->short_message) . "\n";

//        $sms .= "TEXT WEB";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function getMessage()
    {
        $this->message['text'] = "Hello";

        $this->text = trim($this->message['text'], "//");

        $this->short_message = "" . $this->text . "\n";

        if ($this->text == "X") {
            $this->response = "No message to pass.";
        }
    }

    function makeMessage()
    {
        $message = $this->short_message . "<br>";
        $uuid = $this->uuid;
        $message .=
            "<p>" . $this->web_prefix . "thing/$uuid/microtime\n \n\n<br> ";
        $this->thing_report['message'] = $message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/microtime';

        $web = "<b>Microtime Agent</b>";
        $web .= "<p>";

        if (isset($this->timestamp)) {
            $web .= "" . $this->timestamp;
        }

        $link = $this->web_prefix . "privacy";
        $privacy_link = '<a href="' . $link . '">' . $link . "</a>";

        if (isset($this->thing->thing->created_at)) {
            $ago = $this->thing->human_time(
                time() - strtotime($this->thing->thing->created_at)
            );
        $web .= "Microtime timestamp was created about " . $ago . " ago. ";
        $web .= "<br>";
        }
        $this->thing_report['web'] = $web;
    }

    public function readSubject()
    {
        $input = strtolower($this->input);

        $pieces = explode(" ", strtolower($input));

        $this->getMessage();
    }
}
