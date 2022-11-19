<?php
namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
//use Endroid\QrCode\Writer\PngWriter;
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
                $this->web_prefix . "thing/" . $this->uuid . "" . "/qr";
        } else {
            $this->quick_response = $this->agent_input;
        }

        $this->width = 200;

        $this->thing_report['help'] = "Try QR.";
        $this->thing_report['info'] =
            "Creates a scannable Quick Response (QR) code.";
    }

    public function get()
    {
        $time_string = $this->thing->Read(["qr", "refreshed_at"]);

        if ($time_string == false) {
            $time_string = $this->thing->time();

            $this->thing->Write(
                ["qr", "refreshed_at"],
                $time_string
            );

        }

        $this->refreshed_at = strtotime($time_string);
    }

    function getQr()
    {
        $this->qr_agent = new Qr($this->thing, $this->link);
        $this->quick_response_png = $this->qr_agent->PNG_embed;
        $this->html_image = $this->qr_agent->html_image;
    }

    /*
    function imageQr()
    {
        return $this->quick_response_png;
      //  $this->qr_agent = new Qr($this->thing, $this->link);
      //  $this->quick_response_png = $this->qr_agent->PNG_embed;
      //  $this->html_image = $this->qr_agent->html_image;
    }
*/

    function extractQr($input) {
        return extractQuickresponse($input);

    }
    function extractQuickresponse($input)
    {
        if (!isset($this->quick_responses)) {
            $this->quick_responses = [];
        }

        $this->quick_responses[] = $input;
        return $this->quick_responses;
    }

    function makeLink()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/qr';

        $this->link = $link;
        $this->thing_report['link'] = $link;
    }

    public function set()
    {
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
        $timestamp = $this->timestampAgent($this->refreshed_at);

        $web .= "Thing created at " . $timestamp . ". ";
        $web .= "<br>";
        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

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

        //$pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        if ($this->hasUuid($this->to)) {
            $this->response .= "Saw and ignored a UUID in the message. ";
            $this->thing->log('found a QR in address.');
        }
    }

    function makeSMS()
    {
        $this->sms_message = "QR | ";
        $this->sms_message .= $this->quick_response;

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
        if ($this->agent_input == null) {
            $codeText = $this->quick_response;
            //$codeText = $this->web_prefix . "thing/".$this->uuid . "/qr";
        } else {
            $codeText = $this->agent_input;
        }

        $this->imageQr($codeText);
    }

    public function imageQr($codeText = null)
    {
      //  if (isset($this->PNG)) {
      //  return $this->PNG_embed;
      //  }
        if ($codeText == null) {
            $codeText = $this->web_prefix;
        }
        /*
        if ($this->agent_input == null) {
            $codeText = $this->quick_response;
            //$codeText = $this->web_prefix . "thing/".$this->uuid . "/qr";
        } else {
            $codeText = $this->agent_input;
        }
*/
        $this->thing->log("start qrcode");

        $qrCode = QrCode::create($codeText)
            //          ->setEncoding(new Encoding('UTF-8'))
            //          ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(300)
            ->setMargin(10);
        //         ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
        //          ->setForegroundColor(new Color(0, 0, 0))
        //          ->setBackgroundColor(new Color(255, 255, 255));

        //    $svgWriter = new SvgWriter();
        //    $result_svg = $svgWriter->write($qrCode, $logo);

        $pngWriter = new PngWriter();
        $image = $pngWriter->write($qrCode)->getString();

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
return $this->PNG_embed;
        return $this->thing_report['png'];
    }
}
