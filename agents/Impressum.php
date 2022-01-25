<?php
/**
 * Impressum.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Impressum extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     */
    function init()
    {
        $this->mail_regulatory =
            $this->thing->container["stack"]["mail_regulatory"];

        $this->node_list = ["start" => ["start", "opt-in"]];
        $this->impressum();
    }

    /**
     *
     */
    public function impressum()
    {
        $this->makeChannel("impressum");
    }

    /**
     *
     */
    public function makeWeb()
    {
        $file = $GLOBALS["stack_path"] . "resources/impressum/impressum.html";
        $contents = "No Impressum found.";
        if (file_exists($file)) {
            $contents = file_get_contents($file);
        }
        $this->thing_report["web"] = $contents;
    }

    /**
     *
     * @return unknown
     */
    public function makeSMS()
    {
        $text = "No SMS response available.";
        if (isset($this->thing_report["sms"])) {
            $text = $this->thing_report["sms"];
        }

        $text = $this->mail_regulatory;
        $text = str_replace('\r', ' ', $text);
        $text = str_replace('\n', ' ', $text);
        $text = preg_replace('/\s+/', " ", $text);
        $text = trim($text);

        $sms = "IMPRESSUM | " . $text;

        $this->thing_report["sms"] = $sms;
        $this->sms_message = $sms;
        return $this->sms_message;
    }

    /**
     *
     */
    public function makeEmail()
    {
        if (!isset($this->thing_report["email"])) {
            $thing = new Thing(null);
            $thing->Create(
                "terms-of-use",
                "human",
                "s/ impressum email not found"
            );
            return true;
        }

        $text = $this->thing_report["email"];
        $shortcode_agent = new Shortcode($this->thing, "shortcode");
        $text = $shortcode_agent->filterShortcode($text);

        $this->thing_report["email"] = $text;
    }

    public function testImpressum()
    {
    }

    public function pdfImpressum($pdf = null)
    {
        try {
            $pdf->SetFont("Helvetica", "", 10);
            $this->txt = "" . $this->uuid . ""; // Pure uuid.

            $link = $this->web_prefix . "thing/" . $this->uuid . "/day";

            $qr_png_embed = $this->imageQr($link);

            //      $pdf->Image($this->quick_response_png, 175, 5, 30, 30, "PNG");
            $pdf->Image($qr_png_embed, 175, 5, 30, 30, "PNG");
            //        throw new \Exception('Test.');

            $pdf->SetTextColor(0, 0, 0);

            $pdf->SetXY(15, 7);

            $line_height = 4;

            $t = $this->thing_report["sms"];

            $t = str_replace(" | ", "\n", $t);

            $pdf->MultiCell(150, $line_height, $t, 0);

            //$pdf->Link(15,7,150,10, $link);

            $y = $pdf->GetY() + 0.95;

            $pdf->SetXY(15, $y);
            $text = "v0.0.1";

            $pdf->MultiCell(
                150,
                $line_height,
                $this->agent_name . " " . $text,
                0,
                "L"
            );

            $y = $pdf->GetY() + 0.95;

            $pdf->SetXY(15, $y);
            $text =
                "Pre-printed text and graphics (c) 2020 " . $this->entity_name;
            $pdf->MultiCell(150, $line_height, $text, 0, "L");

            // Good until?

            $text = "dev";
            /*
This causes an infinite loop.
            $text = $this->timestampDay();
*/

            $pdf->SetXY(175, 35);
            $pdf->MultiCell(30, $line_height, $text, 0, "L");
        } catch (Exception $e) {
            $this->thing->console("Caught exception: ", $e->getMessage(), "\n");
        }

        return $pdf;
        /*
if ($pdf == null) {
if (isset($this->pdf)) {
$pdf = $this->pdf;
}

} 

if ($pdf == null) {

$pdf_handler = new Pdf($this->thing, "pdf");
$pdf = $pdf_handler->pdf;

}
*/
        $pdf->SetFont("Helvetica", "", 10);
        $this->txt = "" . $this->uuid . ""; // Pure uuid.

        $link = $this->web_prefix . "thing/" . $this->uuid . "/day";

        $qr_png_embed = $this->imageQr($link);

        //      $pdf->Image($this->quick_response_png, 175, 5, 30, 30, "PNG");
        $pdf->Image($qr_png_embed, 175, 5, 30, 30, "PNG");
        //        throw new \Exception('Test.');

        //$pdf->Link(175,5,30,30, $link);

        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY(15, 7);

        $line_height = 4;

        $t = $this->thing_report["sms"];

        $t = str_replace(" | ", "\n", $t);

        $pdf->MultiCell(150, $line_height, $t, 0);

        //$pdf->Link(15,7,150,10, $link);

        $y = $pdf->GetY() + 0.95;
        $pdf->SetXY(15, $y);
        $text = "v0.0.1";
        $pdf->MultiCell(
            150,
            $line_height,
            $this->agent_name . " " . $text,
            0,
            "L"
        );

        $y = $pdf->GetY() + 0.95;

        $pdf->SetXY(15, $y);
        $text = "Pre-printed text and graphics (c) 2020 " . $this->entity_name;
        $pdf->MultiCell(150, $line_height, $text, 0, "L");

        // Good until?
        $text = $this->timestampDay();
        $pdf->SetXY(175, 35);
        $pdf->MultiCell(30, $line_height, $text, 0, "L");

        return $pdf;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        return $this->thing_report;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->thing_report["request"] = "What is your Impressum?";

        return "Message not understood";
    }
}
