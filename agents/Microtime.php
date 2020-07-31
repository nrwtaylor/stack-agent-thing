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
    }

public function textMicrotime($timestamp) {

$t = explode(".", $timestamp);


$n = str_pad($t[1],4,"0") / 10000;
$text =$n . " " . $t[0];

//$text = $this->thing->microtime($text);

return $text;

}

public function epochtimeMicrotime($text = null) {
if (is_numeric($text)) {return true;}

if ($text == null) {$text = $this->thing->microtime();}

$t = explode(' ',$text);
//var_dump($t);
$non_micro = $t[0] . " " . $t[1];
$micro = $t[2];
        $timestamp = (float) strtotime($non_micro) + $micro;


return $timestamp;

}


    function isMicrotime($state = null)
    {
        return true;
    }

    function set($requested_state = null)
    {
        $this->refreshed_at = $this->current_time;

        $this->thing->setVariable("refreshed_at", $this->current_time);
//        $this->thing->setVariable("timestamp", $this->timestamp);


    }

    function get()
    {
//        $this->refreshed_at = $this->thing->getVariable("refreshed_at");
//echo "merp";
///home/nick/codebase/stack-agent-thing/agents/Microtime.phpexit();/home/nick/codebase/stack-agent-thing/agents/Microtime.php
        // If it is a valid previous_state, then
        // load it into the current state variable.

        $this->thing->json->setField("variables");
//

        $time_string = $this->thing->json->readVariable([
            "microtime",
            "refreshed_at",
        ]);
        if ($time_string == false) {
//            $this->thing->json->setField("variables");
            $time_string = $this->thing->time();
            $this->thing->json->writeVariable(
                ["microtime", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);


        $timestamp_string = $this->thing->json->readVariable([
            "microtime",
            "timestamp",
        ]);

        if ($timestamp_string == false) {
  
          $this->thing->json->setField("variables");
            $timestamp_string = $this->thing->microtime();
            $this->thing->json->writeVariable(
                ["microtime", "timestamp"],
                $timestamp_string
            );
        }

//$this->timestamp = $this->timestampMicrotime($timestamp_string);
$this->timestamp = $timestamp_string;

//$this->getPrior();

//    function microtime($microtime = null)
//$this->last_timestamp = $this->timestampMicrotime($timestamp_string);

    }

    public function respondResponse()
    {
//$this->makeChoices();
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This agent handles microtime.";
        $this->thing_report["help"] =
            'Try MICROTIME.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

    }

    function makeTXT()
    {
        $sms = "MICROTIME ". "\n";

        $sms .= trim($this->short_message) . "\n";


        $this->sms_message = $sms;
        $this->thing_report['txt'] = $sms;
    }


    function makeSMS()
    {

        $sms = "MICROTIME ". "\n";

$sms .= $this->timestamp . "\n";
$sms .= $this->epochtimeMicrotime($this->timestamp) ."\n";
        $sms .= trim($this->short_message) . "\n";

        $sms .= "TEXT WEB";
        // $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function getMessage()
    {

        $this->message['text'] = "Hello";

        $this->text = trim($this->message['text'], "//");



        $this->short_message =
            "" .
            $this->text .
            "\n";

        if (
            $this->text == "X"
        ) {
            //$this->response = $this->number . " " . $this->unit . ".";
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

        $web .= "<p>";


        $web .= "Message Metadata - ";

        $web .=
            $this->thing->nuuid .
            " - " .
            $this->thing->thing->created_at;

        $web .= "<br>";

        $link = $this->web_prefix . "privacy";
        $privacy_link = '<a href="' . $link . '">' . $link . "</a>";

        $ago = $this->thing->human_time(
            time() - strtotime($this->thing->thing->created_at)
        );
        $web .= "Microtime timestamp was created about " . $ago . " ago. ";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }


    public function readSubject()
    {
        $input = strtolower($this->input);

        $pieces = explode(" ", strtolower($input));

$this->getMessage();


    }

}
