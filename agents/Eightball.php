<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class EightBall extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function set()
    {
        $this->thing->Write(["eightball", "face"], $this->face);
    }

    function get()
    {
        //$this->thing->json->setField("variables");
        $time_string = $this->thing->Read([
            "eightball",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->Write(
                ["eightball", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        //$this->thing->json->setField("variables");
        $this->face = strtolower(
            $this->thing->Read(["eightball", "face"])
        );
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->sms_message = "8 BALL";

        $this->sms_message .= " | " . $this->message;
        $this->sms_message .= ' | TEXT ROLL d20';

        $choices = false;

        $this->thing_report["choices"] = $choices;
        $this->thing_report["info"] = "This makes a prognistication.";
        $this->thing_report["help"] = "Try EIGHTBALL.";

        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeTXT()
    {
        if (!isset($this->sms_message)) {
            $this->makeSMS();
        }
        $this->thing_report['txt'] = $this->sms_message;
    }

    function makeMessage()
    {
        switch ($this->face) {
            case 1:
                $answer = "It is certain";
                break;
            case 2:
                $answer = "It is decidely so";
                break;
            case 3:
                $answer = "Without a doubt";
                break;
            case 4:
                $answer = "Yes definitely";
                break;
            case 5:
                $answer = "You may rely on it";
                break;
            case 6:
                $answer = "As I see it, yes";
                break;
            case 7:
                $answer = "Most likely";
                break;
            case 8:
                $answer = "Outlook good";
            case 9:
                $answer = "Yes";
                break;
            case 10:
                $answer = "Signs point to yes";
                break;
            case 11:
                $answer = "Reply hazy try again";
                break;
            case 12:
                $answer = "Ask again later";
                break;
            case 13:
                $answer = "Better not tell you now";
                break;
            case 14:
                $answer = "Cannot predict now";
                break;
            case 15:
                $answer = "Concentrate and ask again";
                break;
            case 16:
                $answer = "Don't count on it";
                break;
            case 17:
                $answer = "My reply is no";
                break;
            case 18:
                $answer = "My sources say no";
            case 19:
                $answer = "Outlook not so good";
                break;
            case 20:
                $answer = "Very doubtful";
                break;
            default:
                $answer = "Broken";
                break;
        }

        $m = $answer;

        $this->message = $m;
        $this->thing_report['message'] = $m;
    }

    public function readSubject()
    {
        if (!isset($this->face) or $this->face == "") {
            $this->face = rand(1, 20);
        }
        $this->response = "Received an answer to the question.";

        return false;
    }
}
