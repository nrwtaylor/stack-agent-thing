<?php
namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;
use Endroid\QrCode\QrCode;

//QR_Code::png('Hello World');

// Recognizes and handles UUIDS.  Does not generate.  That is a Thing function.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Qr extends Agent
{
    function init()
    {
        $this->node_list = ["qr" => ["qr", "uuid", "snowflake"]];

        $this->aliases = ["learning" => ["good job"]];

        if ($this->agent_input == null) {
            $this->quick_response =
                $this->web_prefix . "" . $this->uuid . "" . "/qr";
        } else {
            $this->quick_response = $this->agent_input;
        }

        $this->width = 200;

        $this->thing_report['help'] = "Try QR.";
        $this->thing_report['info'] =
            "Creates a scannable Quick Response (QR) code.";
    }

    function extractQuickresponse($input)
    {
        if (!isset($this->quick_responses)) {
            $this->quick_responses = [];
        }

        $this->quick_responses[] = $input;
        return $this->quick_responses;
    }

    public function set()
    {
        $this->thing->json->setField("settings");
        $this->thing->json->writeVariable(
            ["qr", "received_at"],
            $this->thing->json->time()
        );
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/qr';

        $this->node_list = ["qr" => ["qr", "uuid"]];
        // Make buttons
//        $this->thing->choice->Create($this->agent_name, $this->node_list, "qr");
//        $choices = $this->thing->choice->makeLinks('qr');

        $alt_text = "QR code with the uuid " . $this->uuid;

        $web = '<a href="' . $link . '" ' . 'alt="' . $alt_text . '" >';

        $web .= $this->html_image;

        $web .= "</a>";

        $web .= "<br>";

        $web .= 'Code will scan as "' . $this->quick_response . '". ';
        $web .= '<p>';

        $timestamp = $this->timestampAgent();

        $web .= "Thing created at " . $timestamp . ". ";
        $web .= "<br>";
        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $from = $this->from;
        $to = $this->to;

        $subject = $this->subject;

        // Now passed by Thing object
        $quick_response = $this->quick_response;
        $sqlresponse = "yes";

        $message =
            "Thank you $from here is a QR (Quick Response).<p>" .
            $this->web_prefix .
            "thing/$quick_response\n$sqlresponse \n\n<br> ";
        $message .=
            '<img src="' .
            $this->web_prefix .
            'thing/' .
            $quick_response .
            '/receipt.png" alt="thing:' .
            $quick_response .
            '" height="92" width="92">';

        $this->thing_report['email'] = $this->thing_report['sms'];

        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    public function readSubject()
    {
        // If the to line is a UUID, then it needs
        // to be sent a receipt.

        // Then look for messages sent to UUIDS
        $this->thing->log('looking for QR in address.');
        //    $uuid_thing = new Uuid($this->thing, 'uuid');

        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        if (preg_match($pattern, $this->to)) {
            $this->response .= "Saw and ignored a UUID in the message. ";
            $this->thing->log('found a QR in address.');
        }
    }

    function makeSMS()
    {
        $this->sms_message = "QR | ";
        $this->sms_message .= $this->quick_response;
        //$this->sms_message .= ' | TEXT ?';

        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("uuid", $this->node_list, "qr");

        $choices = $this->thing->choice->makeLinks("qr");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }

    public function makePNG()
    {
        if (isset($this->PNG)) {
            return;
        }

        if ($this->agent_input == null) {
            $codeText = $this->quick_response;
            //$codeText = $this->web_prefix . "thing/".$this->uuid . "/qr";
        } else {
            $codeText = $this->agent_input;
        }

        $this->thing->log("start qrcode");
        $qrCode = new QrCode($codeText);
        $image = $qrCode->writeString();
        $this->thing->log("completed qrcode write string");
        $this->PNG_embed = "data:image/png;base64," . base64_encode($image);

        $this->PNG = $image;

        //        $this->width = 100;
        $alt_text = $this->uuid;

        $html =
            '<img src="data:image/png;base64,' .
            base64_encode($image) .
            '"
                width="' .
            $this->width .
            '"  
                alt="' .
            $alt_text .
            '" longdesc = "' .
            $this->web_prefix .
            'thing/' .
            $this->uuid .
            '/qr.txt">';

        $this->html_image = $html;

        // Can't get this text editor working yet 10 June 2017

        //$textcolor = imagecolorallocate($image, 0, 0, 255);
        // Write the string at the top left
        //imagestring($image, 5, 0, 0, 'Hello world!', $textcolor);

        $this->thing_report['png'] = $image;

        return $this->thing_report['png'];
    }
}
