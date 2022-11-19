<?php
namespace Nrwtaylor\StackAgentThing;

// dev not tested

class Query extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function run()
    {
    }

function parseQuery($log_command)
{
    $tokens = explode(" ", $log_command);

    $log_includes = [];
    $log_excludes = [];

    // First see if there are no + or - signs.
    // If there are not then look for the whole phrase.

    $sign_flag = false;
    foreach ($tokens as $m => $token) {
        $first_character = substr($token, 0, 1);

        if ($first_character === "+") {
            $sign_flag = true;
            break;
        }
        if ($first_character === "-") {
            $sign_flag = true;
            break;
        }
    }

    if ($sign_flag === false) {
        $log_includes[] = $log_command;
        return [$log_includes, $log_excludes];
    }

    // Saw a plus or a minus sign.
    // Parse this. Assume that tokens without a sign are include.

    foreach ($tokens as $m => $token) {
        $first_character = substr($token, 0, 1);

        if ($first_character === "+") {
            $log_includes[] = ltrim($token, "+");
            continue;
        }
        if ($first_character === "-") {
            $log_excludes[] = ltrim($token, "-");
            continue;
        }

        $log_includes[] = $token; // If no sign, treat as include.
    }
    return [$log_includes, $log_excludes];
}


    public function readQuery()
    {
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "QUERY | " . strtolower($v) . ".";

            $this->html_message = $response; // mewsage?
        } else {
            $this->html_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This agent handles queries.";
        $this->thing_report["help"] =
            "This is about recognizing and processing queries.";

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $sms = "QUERY";
        if ($this->response !== "") {
            $sms .= " | " . $this->response;
        }
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function readSubject()
    {
        $filtered_text = $this->assert($this->input, "query", false);
//        if ($this->validateRegex($filtered_text) == true) {
//            $this->response .= "Is regex. ";
//        }
        return false;
    }
}
