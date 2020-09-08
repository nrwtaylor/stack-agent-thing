<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

class Satoshi extends Agent
{
    public function init()
    {
        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }

        $this->node_list = [];
    }

    public function get()
    {
        // Load in address generating algorithm.
        require_once $this->resource_path . "satoshi/PHPCoinAddress.php";
        // CoinAddress::set_debug(true);      // optional - show debugging messages
        // CoinAddress::set_reuse_keys(true); // optional - use same key for all addresses
        $coin = \CoinAddress::bitcoin();

        //print 'public (base58): ' . $coin['public'] . "<br>";
        //print 'public (Hex)   : ' . $coin['public_hex'] . "<br>";
        //print 'private (WIF)  : ' . $coin['private'] . "<br>";
        //print 'private (Hex)  : ' . $coin['private_hex'] . "<br>";

        $this->thing->json->setField("variables");

        $public = $this->thing->json->readVariable(["satoshi", "public"]);
        $secret = $this->thing->json->readVariable(["satoshi", "secret"]);

        if ($public == false and $secret == false) {
            $public_key = $coin['public'];
            $this->public_key = $public_key;

            $secret_key = $coin['private'];
            $this->secret_key = $secret_key;

            $this->thing->json->writeVariable(
                ["satoshi", "public"],
                $this->public_key
            );

            $this->thing->json->writeVariable(
                ["satoshi", "secret"],
                $this->secret_key
            );
        } else {
            $this->public_key = $public;
            $this->secret_key = $secret;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    public function makeSMS()
    {
        $this->sms_message = "SATOSHI | ";
        $this->sms_message .= "public key " . $this->public_key . ' ';
        $this->sms_message .= "secret key " . $this->secret_key;
        $this->sms_message .= ' | TEXT SATOSHI';

        // While we work on this
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
    }

    public function makePDF()
    {
        $pdf = new Fpdi\Fpdi();

        $pdf->setSourceFile($this->resource_path . 'satoshi/satoshi.pdf');

        $tplidx1 = $pdf->importPage(1, '/MediaBox');
        $pdf->addPage();
        $pdf->useTemplate($tplidx1, 0, 0, 215);

        $this->getQuickresponse($this->secret_key);
        $pdf->Image($this->quick_response_png, 168, 125, 40, 40, 'PNG');

        $this->getQuickresponse($this->public_key);
        $pdf->Image($this->quick_response_png, 115, 125, 40, 40, 'PNG');

        $codeText = $this->public_key;

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(255, 0, 0);

        // Generate some content for page 1

        $tplidx2 = $pdf->importPage(2);

        $pdf->addPage();
        $pdf->useTemplate($tplidx2, 0, 0);
        // Generate some content for page 2

        $pdf->SetFont('Helvetica', '', 6);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY(5, 240);
        $t = "Agent 'Satoshi' processed Thing ";
        $t .= $this->uuid . ' on ';
        $t .= date("Y-m-d H:i:s") . ' with';

        $pdf->Write(0, $t);

        $pdf->SetXY(5, 243);
        $t = 'PUBLIC KEY ' . $this->public_key;
        $pdf->Write(0, $t);

        $pdf->SetXY(5, 246);
        $t = 'and SECRET KEY ' . $this->secret_key . ".";
        $pdf->Write(0, $t);

        $pdf->SetXY(5, 249);

        $t =
            "Bitcoin pair algorithm used was PHPCoinAddress (https://github.com/zamgo/PHPCoinAddress).";

        $pdf->Write(0, $t);

        ob_start();
        $image = $pdf->Output('', 'I');
        $image = ob_get_contents();
        ob_clean();

        $this->thing_report['pdf'] = $image;
        return $this->thing_report['pdf'];
    }

    function getQuickresponse($text = null)
    {
        if ($text == null) {
            $text = $this->web_prefix;
        }
        $agent = new Qr($this->thing, $text);
        $this->quick_response_png = $agent->PNG_embed;
        return $agent->PNG_embed;
    }
}
