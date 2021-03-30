<?php
namespace Nrwtaylor\StackAgentThing;

use QR_Code\QR_Code;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Receipt extends Agent
{
    function init()
    {
        $this->stack_state = $this->thing->container["stack"]["state"];
        $this->short_name = $this->thing->container["stack"]["short_name"];

        $this->node_list = [
            "receipt management" => [
                "learning",
                "communicating" => ["more", "less"],
                "channeling" => ["narrowing", "broadening"],
            ],
            "receipt start" => [
                "more" => "receipt management",
                "less" => "receipt management",
            ],
        ];

        $this->aliases = ["learning" => ["good job"]];
    }

    public function set()
    {
        $this->setReceipt();
    }

    function getQuickresponse()
    {
        $agent = new Qr($this->thing, "qr");
        $this->quick_response_png = $agent->PNG_embed;
    }

    function setReceipt()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["receipt", "refreshed_at"],
            $this->thing->json->time()
        );
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $choices = false;

        $subject = $this->subject;

        // Now passed by Thing object
        $uuid = $this->uuid;
        $sqlresponse = "yes";

        $message =
            "Thank you $from your message to agent '$to' has been accepted by " .
            $this->short_name .
            ".  Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid\n$sqlresponse \n\n<br> ";
        $message .=
            '<img src="' .
            $this->web_prefix .
            "thing/" .
            $uuid .
            '/receipt.png" alt="thing:' .
            $uuid .
            '" height="92" width="92">';

        $this->thing_report["email"] = $message;
        $this->thing_report["message"] = $message; // NRWTaylor. Slack won't take hmtl raw. $test_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $this->verbosity = 1;

        $this->sms_message = "RECEIPT";

        if ($this->verbosity > 5) {
            $this->sms_message .= " | thing " . $this->uuid . "";
            $this->sms_message .= " created " . $this->thing->created_at;
            $this->sms_message .= " by " . strtoupper($this->from);
        }

        if ($this->verbosity >= 1) {
            $this->sms_message .=
                " | datagram " .
                $this->uuid .
                " received " .
                $this->thing->thing->created_at .
                ".";
        }

        //$this->sms_message .= ' | TEXT ?';

        $this->thing_report["sms"] = $this->sms_message;

        return $this->sms_message;
    }

    public function readSubject()
    {
    }

    public function PNG()
    {
        // A historical note.

        // Thx https://stackoverflow.com/questions/24019077/how-to-define-the-result-of-qrcodepng-as-a-variable

        //I just lost about 4 hours on a really stupid problem. My images on the local server were somehow broken and therefore did not display in the browsers. After much looking around and testing, including re-installing apache on my computer a couple of times, I traced the problem to an included file.
        //No the problem was not a whitespace, but the UTF BOM encoding character at the begining of one of my included files...
        //So beware of your included files!
        //Make sure they are not encoded in UTF or otherwise in UTF without BOM.
        //Hope it save someone's time.

        //http://php.net/manual/en/function.imagepng.php

        // here DB request or some processing
        $codeText = "thing:" . $this->uuid;

        $agent = new Qr($this->thing, $codeText);
        $this->thing_report["png"] = $agent->PNG;

        return $this->thing_report["png"];
    }
}
