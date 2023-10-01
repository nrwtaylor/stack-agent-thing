<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Key extends Agent
{
    function init()
    {
        $this->keyword = "key";
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->Write(
            ["key", "received_at"],
            gmdate("Y-m-d\TH:i:s\Z", time())
        );

        $this->thing->flagGreen();

        $from = $this->from;
        $to = $this->to;

        $subject = $this->subject;

        $choices = $this->thing->choice->makeLinks();
        $this->thing_report['choices'] = $choices;

        $this->thing_report['email'] = $this->thing_report['message'];
        $this->thing_report['choices'] = $choices;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    public function makeSMS()
    {
        $uuid = $this->uuid;

        $this->sms_message =
            "KEY | " .
            $this->web_prefix .
            "thing/$uuid/agent | TEXT [ FORGETALL | SHUFFLE ]";

        $this->thing_report['sms'] = $this->sms_message;
    }

    public function makeMessage()
    {
        $subject = $this->subject;

        // Now passed by Thing object
        $uuid = $this->uuid;

        $message =
            "'keymanager' decided it was about time that you had a new
key to access " .
            $this->short_name .
            ". Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid\n\n\n<br> ";
        $message .=
            '<img src="' .
            $this->web_prefix .
            'thing/' .
            $uuid .
            '/receipt.png" alt="thing:' .
            $uuid .
            '" height="92" width="92">';

        $this->thing_report['message'] = $message;
    }

    public function readSubject()
    {
        $status = true;
        return $status;
    }

    public function sendKey()
    {
    }
}
