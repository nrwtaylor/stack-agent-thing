<?php
/**
 * Month.php
 *
 * @package default
 */

// TODO
// PDF and PNG rendering

namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Month extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->test = "Development code";

        $this->thing_report["info"] =
            "A MONTH is the period of about 28 to 31 days.";
        $this->thing_report["help"] = "Click on the image for a PDF.";

        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        $this->node_list = ["month" => ["month", "day", "year", "uuid"]];

        $this->entity_name = $this->thing->container["stack"]["entity_name"];

        $this->default_canvas_size_x = 2000;
        $this->default_canvas_size_y = 2000;

        $this->draw_center = false;
        $this->draw_outline = false; //Draw hexagon line

        $this->canvas_size_x = $this->default_canvas_size_x;
        $this->canvas_size_y = $this->default_canvas_size_y;

        $this->size = min(
            $this->default_canvas_size_x,
            $this->default_canvas_size_y
        );

        $this->month_indicators = [
            "JAN" => ["january", "jan"],
            "FEB" => ["february", "feb"],
            "MAR" => ["march", "mar"],
            "APR" => ["april", "apr"],
            "MAY" => ["may", "may"],
            "JUN" => ["june", "jun"],
            "JUL" => ["july", "jul"],
            "AUG" => ["august", "aug"],
            "SEP" => ["september", "sep"],
            "OCT" => ["october", "oct"],
            "NOV" => ["november", "nov"],
            "DEC" => ["december", "dec"],
        ];

        $this->initMonth();
    }

    public function initMonth()
    {
        $this->haab_months = [
            "Pop" => ["mat"],
            "Wo'" => ["black conjunction"],
            "Sip" => ["red conjunction"],
            "Sotz'" => ["bat"],
            "Sek" => ["death"],
            "Xul" => ["dog"],
            "Yaxk'in" => ["new sun"],
            "Mol" => ["water"],
            "Ch'en" => ["black storm"],
            "Yax" => ["green storm"],
            "Sak" => ["white storm"],
            "Keh" => ["red storm"],
            "Mak" => ["enclosed"],
            "K'ank'in" => ["yellow sun"],
            "Muwan" => ["owl"],
            "Pax" => ["planting time"],
            "K'ayab" => ["turtle"],
            "Kumk'u" => ["granary"],
            "Wayeb'" => ["five unlucky days"],
        ];
    }

    public function set()
    {
        $this->setMonth();
    }

    /**
     *
     * @param unknown $text (optional)
     */
    function getQuickresponse($text = null)
    {
        if ($text == null) {
            $text = $this->web_prefix;
        }
        $agent = new Qr($this->thing, $text);
        $this->quick_response_png = $agent->PNG_embed;
    }

    /**
     *
     */
    public function getNuuid()
    {
        $agent = new Nuuid($this->thing, "nuuid");
        $this->nuuid_png = $agent->PNG_embed;
    }

    /**
     *
     * @param unknown $input
     */
    function getWhatis($input)
    {
        $whatis = "month";
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), $whatis . " is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen($whatis . " is")
            );
        } elseif (($pos = strpos(strtolower($input), $whatis)) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen($whatis));
        }

        //$filtered_input = ltrim(strtolower($whatIWant), " ");
        $filtered_input = ltrim($whatIWant, " ");

        $this->whatis = $filtered_input;
    }

    // TODO Remove this function. Refactor as a stamp call.
    /**
     *
     * @param unknown $t (optional)
     * @return unknown
     */
    public function timestampDay($t = null)
    {
        //        $s = $this->thing->thing->created_at;

        if (!isset($this->retain_to)) {
            $text = "X";
        } else {
            $t = $this->retain_to;
            $text = "GOOD UNTIL " . strtoupper(date("Y M d D H:i", $t));
            //$text = "CLICK FOR PDF";
        }
        $this->timestamp = $text;
        return $this->timestamp;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    /**
     *
     */
    public function makeChoices()
    {
        $this->choices = false;
        $this->thing_report["choices"] = $this->choices;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms = "MONTH";

        $months = [];
        if (isset($this->months)) {
            $months = $this->months;
        }

        $month_text = "No month found.";
        if (isset($this->month)) {
            $month_text = $this->month;
            $sms .= " | " . $month_text;
        }
        $sms .= " " . $this->response;
        //$sms .= " | " . $this->message . " " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Made a month for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid/month.png\n \n\n<br> ";
        $message .=
            '<img src="' .
            $this->web_prefix .
            "thing/" .
            $uuid .
            '/month.png" alt="month" height="92" width="92">';

        $this->thing_report["message"] = $message;
    }

    /**
     *
     */
    public function setMonth()
    {
        return;
    }

    /**
     *
     * @return unknown
     */

    public function run()
    {
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/month.pdf";
        $this->node_list = ["month" => ["month"]];
        $web = "";
        $web .= '<a href="' . $link . '">';
        $web .= $this->html_image;
        $web .= "</a>";
        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    /**
     *
     */
    public function makeTXT()
    {
        $txt = "This is a MONTH";
        $txt .= "\n";

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    /**
     *
     * @param unknown $r
     * @param unknown $g
     * @param unknown $b
     */
    function rgbcolor($r, $g, $b)
    {
        $this->rgb = imagecolorallocate($this->image, $r, $g, $b);
    }

    /**
     *
     * @return unknown
     */
    public function makePNG()
    {
        if (isset($this->canvas_size_x)) {
            $canvas_size_x = $this->canvas_size_x;
            $canvas_size_y = $this->canvas_size_x;
        } else {
            $canvas_size_x = 164;
            $canvas_size_y = 164;
        }
        $this->image = imagecreatetruecolor($canvas_size_x, $canvas_size_y);
        //$this->image = imagecreatetruecolor(164, 164);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        // For Vancouver Pride 2018

        // https://en.wikipedia.org/wiki/Rainbow_flag
        // https://en.wikipedia.org/wiki/Rainbow_flag_(LGBT_movement)
        // https://www.schemecolor.com/lgbt-flag-colors.php

        $this->electric_red = imagecolorallocate($this->image, 231, 0, 0);
        $this->dark_orange = imagecolorallocate($this->image, 255, 140, 0);
        $this->canary_yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->la_salle_green = imagecolorallocate($this->image, 0, 129, 31);
        $this->blue = imagecolorallocate($this->image, 0, 68, 255);
        $this->patriarch = imagecolorallocate($this->image, 118, 0, 137);

        $this->flag_red = imagecolorallocate($this->image, 231, 0, 0);
        $this->flag_orange = imagecolorallocate($this->image, 255, 140, 0);
        $this->flag_yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->flag_green = imagecolorallocate($this->image, 0, 129, 31);
        $this->flag_blue = imagecolorallocate($this->image, 0, 68, 255);
        // Indigo https://www.rapidtables.com/web/color/purple-color.html
        $this->flag_indigo = imagecolorallocate($this->image, 75, 0, 130);
        $this->flag_violet = imagecolorallocate($this->image, 118, 0, 137);
        $this->flag_grey = $this->grey;

        $this->indigo = imagecolorallocate($this->image, 75, 0, 130);

        $this->ice_green = imagecolorallocate($this->image, 126, 217, 195);
        $this->blue_ice = imagecolorallocate($this->image, 111, 122, 159);
        $this->artic_ice = imagecolorallocate($this->image, 195, 203, 217);
        $this->ice_cold = imagecolorallocate($this->image, 165, 242, 243);
        $this->white_ice = imagecolorallocate($this->image, 225, 231, 228);

        $this->ice_color_palette = [
            $this->ice_green,
            $this->blue_ice,
            $this->artic_ice,
            $this->ice_cold,
            $this->white_ice,
        ];

        // Patriarch as a color name.
        // https://www.schemecolor.com/lgbt-flag-colors.php
        $this->color_palette = [
            $this->electric_red,
            $this->dark_orange,
            $this->canary_yellow,
            $this->la_salle_green,
            $this->blue,
            $this->patriarch,
        ];

        $this->flag_color_palette = [
            $this->flag_red,
            $this->flag_orange,
            $this->flag_yellow,
            $this->flag_green,
            $this->flag_blue,
            $this->flag_indigo,
            $this->flag_violet,
            $this->flag_grey,
        ];

        imagefilledrectangle(
            $this->image,
            0,
            0,
            $canvas_size_x,
            $canvas_size_y,
            $this->white
        );

        $textcolor = imagecolorallocate($this->image, 0, 0, 0);

        $this->drawMonth();

        // Write the string at the top left
        $border = 30;
        $r = 1.165;

        $radius = ($r * ($canvas_size_x - 2 * $border)) / 3;

        // devstack add path
        $font = $this->default_font;
        $text = "A month in slices...";
        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

        $size = $canvas_size_x - 90;
        $size = 20;
        $angle = 0;
        if (file_exists($font)) {
            $bbox = imagettfbbox($size, $angle, $font, $text);
            $bbox["left"] = 0 - min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["top"] = 0 - min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            $bbox["width"] =
                max($bbox[0], $bbox[2], $bbox[4], $bbox[6]) -
                min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["height"] =
                max($bbox[1], $bbox[3], $bbox[5], $bbox[7]) -
                min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            extract($bbox, EXTR_PREFIX_ALL, "bb");
            //check width of the image
            $width = imagesx($this->image);
            $height = imagesy($this->image);
            $pad = 0;
        }
        //        imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $this->black, $font, $text);

        //imagestring($this->image, 2, 140, 0, $this->thing->nuuid, $textcolor);

        // https://stackoverflow.com/questions/14549110/failed-to-delete-buffer-no-buffer-to-delete

        if (ob_get_contents()) {
            ob_clean();
        }

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();

        ob_end_clean();

        $this->thing_report["png"] = $imagedata;

        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="month"/>';

        $this->html_image =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="month"/>';

        $this->PNG_embed = "data:image/png;base64," . base64_encode($imagedata);

        $this->PNG = $imagedata;

        return $response;

        $this->PNG = $image;
        $this->thing_report["png"] = $image;

        return;
    }

    public function drawMonth($type = null)
    {
        if ($type == null) {
            $type = $this->type;
        }
        if ($type == "wedge") {
            $this->wedgeMonth();
            return;
        }

        $this->sliceMonth();
    }

    public function sliceMonth()
    {
        $size = null;
        if ($size == null) {
            $size = $this->size;
        }
        $border = 100;
        $size = 1000 - $border;

        if (isset($this->canvas_size_x)) {
            $canvas_size_x = $this->canvas_size_x;
            $canvas_size_y = $this->canvas_size_y;
        } else {
            $canvas_size_x = $this->default_canvas_size_x;
            $canvas_size_y = $this->default_canvas_size_y;
        }

        $width_slice = ($canvas_size_x - 2 * $border) / 24;

        // Draw out the state
        $center_x = $canvas_size_x / 2;
        $center_y = $canvas_size_y / 2;

        // devstack rotation not yet implemented
        if (!isset($this->angle)) {
            $this->angle = 0;
        }

        foreach (range(0, 24, 1) as $i) {
            imageline(
                $this->image,
                $width_slice * $i,
                0,
                $width_slice * $i,
                $canvas_size_y,
                $this->black
            );
        }
    }

    public function wedgeMonth()
    {
        $size = null;
        if ($size == null) {
            $size = $this->size;
        }
        $border = 100;
        $size = 1000 - $border;

        if (isset($this->canvas_size_x)) {
            $canvas_size_x = $this->canvas_size_x;
            $canvas_size_y = $this->canvas_size_y;
        } else {
            $canvas_size_x = $this->default_canvas_size_x;
            $canvas_size_y = $this->default_canvas_size_y;
        }

        // Draw out the state
        $center_x = $canvas_size_x / 2;
        $center_y = $canvas_size_y / 2;

        // devstack rotation not yet implemented
        if (!isset($this->angle)) {
            $this->angle = 0;
        }

        $init_angle = (-1 * pi()) / 2;
        $angle = (2 * 3.14159) / 24;
        //$x_pt =  230;
        //$y_pt = 230;

        foreach (range(0, 24 - 1, 1) as $i) {
            $x_pt = $size * cos($angle * $i + $init_angle);
            $y_pt = $size * sin($angle * $i + $init_angle);
            /*
            imageline(
                $this->image,
                $center_x + $x_pt,
                $center_y + $y_pt,
                $center_x + $x_pt,
                $center_y + $y_pt,
                $this->black
            );
*/
            imageline(
                $this->image,
                $center_x,
                $center_y,
                $center_x + $x_pt,
                $center_y + $y_pt,
                $this->black
            );
        }

        imagearc(
            $this->image,
            $center_x,
            $center_y,
            2 * $size,
            2 * $size,
            0,
            360,
            $this->black
        );
    }

    public function get()
    {
        $time_string = $this->thing->Read([
            "month",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["month", "refreshed_at"],
                $time_string
            );
        }
    }

    /**
     *
     * @return unknown
     */
    public function makePDF()
    {
        if (
            $this->default_pdf_page_template === null or
            !file_exists($this->default_pdf_page_template)
        ) {
            $this->thing_report["pdf"] = false;
            return $this->thing_report["pdf"];
        }

        $this->getWhatis($this->subject);
        try {
            // initiate FPDI
            $pdf = new Fpdi\Fpdi();

            $pdf->setSourceFile($this->default_pdf_page_template);
            $pdf->SetFont("Helvetica", "", 10);

            $tplidx1 = $pdf->importPage(1, "/MediaBox");
            $s = $pdf->getTemplatesize($tplidx1);

            $pdf->addPage($s["orientation"], $s);
            $pdf->useTemplate($tplidx1);
            /*
            if (isset($this->hextile_PNG)) {
                $top_x = -6;
                $top_y = 11;

                $pdf->Image(
                    $this->hextile_PNG,
                    $top_x,
                    $top_y,
                    -300,
                    -300,
                    'PNG'
                );
            }
*/
            $this->getNuuid();
            //$pdf->Image($this->nuuid_png, 5, 18, 20, 20, 'PNG');
            $pdf->Image($this->PNG_embed, 7, 30, 200, 200, "PNG");

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(1, 1);

            $pdf->SetFont("Helvetica", "", 26);
            $this->txt = "" . $this->whatis . ""; // Pure uuid.

            $pdf->SetXY(140, 7);
            $text = $this->whatis;
            $line_height = 20;
            $pdf->MultiCell(150, $line_height, $text, 0);

            if (isset($this->hextile_PNG)) {
                $top_x = -6;
                $top_y = 11;

                $pdf->Image(
                    $this->hextile_PNG,
                    $top_x,
                    $top_y,
                    -300,
                    -300,
                    "PNG"
                );
            }

            // Page 2
            $tplidx2 = $pdf->importPage(2);

            $pdf->addPage($s["orientation"], $s);

            $pdf->useTemplate($tplidx2, 0, 0);
            // Generate some content for page 2

            $pdf->SetFont("Helvetica", "", 10);
            $this->txt = "" . $this->uuid . ""; // Pure uuid.

            $link = $this->web_prefix . "thing/" . $this->uuid . "/month";

            $this->getQuickresponse($link);
            $pdf->Image($this->quick_response_png, 175, 5, 30, 30, "PNG");

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
            $text =
                "Pre-printed text and graphics (c) 2020 " . $this->entity_name;
            $pdf->MultiCell(150, $line_height, $text, 0, "L");

            // Good until?
            $text = $this->timestampDay();
            $pdf->SetXY(175, 35);
            $pdf->MultiCell(30, $line_height, $text, 0, "L");

            $image = $pdf->Output("", "S");
            $this->thing_report["pdf"] = $image;
        } catch (Exception $e) {
            $this->thing->console("Caught exception: ", $e->getMessage(), "\n");
        }

        return $this->thing_report["pdf"];
    }

    public function isMonth($text)
    {
        $this->parsed_date = date_parse($text);
        $month = $this->parsed_date["month"];

        if ($month !== false) {
            return $month;
        }

        foreach ($this->month_indicators as $month => $month_indicators) {
            if (stripos($text, $month) !== false) {
                return $momth;
            }

            foreach ($month_indicators as $i => $month_indicator) {
                if (stripos($text, $month_indicator) !== false) {
                    return $month_indicator;
                }
            }
        }

        return false;
    }

    // TODO
    public function extractMonths($text = null)
    {
        $months = [];

        if ($text == null or $text == "") {
            return true;
        }

        if (!isset($this->ngram_agent)) {
            $this->ngram_agent = new Ngram($this->thing, "ngram");
        }

        $tokens = [];
        foreach (range(0, 4, 1) as $i) {
            $new_grams = $this->ngram_agent->extractNgrams($text, $i);
            $tokens = array_merge($tokens, $new_grams);
        }
        foreach ($tokens as $i => $token) {
            if ($token == "") {
                continue;
            }
            $response = $this->isMonth($token);
            if ($response === false) {
                continue;
            }

            // TODO refactor
            if (is_string($response)) {
                $response = intval($response);
            }
            if (is_integer($response)) {
                // Check if a month has been mis-categorized as a year.
                $this->parsed_date = date_parse($text);
                $month = $this->parsed_date["month"];
                if ($response == $month) {
                    continue;
                }

                $month_text = strval($response);

                //$era = $this->eraDay($text);
                $month = ["month" => $month_text, "era" => $era];
                $months[] = $month;
            }
        }

        // Remove duplicates.
        // https://stackoverflow.com/questions/307674/how-to-remove-duplicate-values-from-a-multi-dimensional-array-in-php
        $serialized = array_map("serialize", $months);
        $unique = array_unique($serialized);
        $months = array_intersect_key($months, $unique);
        return $months;
    }

    function extractMonth($input = null)
    {
        $month = "X";
        $month_evidence = [];

        $months = $this->month_indicators;

        foreach ($months as $i => $month_null) {
            $month_evidence[$i] = [];
        }

        foreach ($months as $key => $month_names) {
            if (strpos(strtolower($input), strtolower($key)) !== false) {
                // $month_evidence[] = $key;
                $month = $key;
                $month_evidence[$month][] = $key;
                //break;
            }

            foreach ($month_names as $month_name) {
                if (
                    strpos(strtolower($input), strtolower($month_name)) !==
                    false
                ) {
                    if (
                        strpos(
                            strtolower($input),
                            strtolower($month_name . " ")
                        ) == false
                    ) {
                        continue;
                    }

                    if (
                        strpos(
                            strtolower($input),
                            strtolower(" " . $month_name)
                        ) == false
                    ) {
                        continue;
                    }

                    //      $month_evidence[] = $month_name;
                    $month = $key;
                    $month_evidence[$key][] = $month_name;

                    //break;
                }
            }
        }

        $this->parsed_date = date_parse($input);
        if (
            $this->parsed_date["year"] != false and
            $this->parsed_date["month"] != false and
            $this->parsed_date["day"] != false
        ) {
            $date_string =
                $this->parsed_date["year"] .
                "/" .
                $this->parsed_date["month"] .
                "/" .
                $this->parsed_date["day"];

            $unixTimestamp = strtotime($date_string);
            $p_month = strtoupper(date("M", $unixTimestamp));
            if ($month == "X") {
                $month = $p_month;
            }
            $month_evidence[$month][] = $date_string;
        }

        $unixTimestamp = strtotime($input);
        if ($unixTimestamp !== false) {
            $p_month = strtoupper(date("M", $unixTimestamp));
            $month_evidence[$p_month][] = $input;
        }
        $scores = [];
        // Process day evidence
        foreach ($month_evidence as $month => $evidence) {
            $scores[$month] = mb_strlen(implode("", $evidence));
        }

        foreach ($scores as $i => $score) {
            if ($score == 0) {
                unset($scores[$i]);
                continue;
            }

            // Allow one character date recognition if the string is 1 long.
            if ($score == 1 and mb_strlen($input) == 1) {
                continue;
            }

            // Allow two character date recognition if the string is 2 long.
            if ($score == 2 and mb_strlen($input) == 2) {
                continue;
            }

            // Now deal with lots of matching letters in a long string
            // Is there more than one line of evidence?
            if (count($month_evidence[$i]) > 1) {
                continue;
            }

            if ($score > 2) {
                continue;
            }

            // Otherwise ignore
            // TODO: Review
            unset($scores[$i]);
        }

        if (count($scores) == 0) {
            return false;
        }

//        if (count($scores) == 1) {
//            return array_key_first($scores);
//        }

        if (count($scores) == 1) {
            if (!function_exists("array_key_first")) {
                // function array_key_first(array $scores) {
                foreach ($scores as $key => $unused) {
                    return $key;
                }
                return null;
                //}
            }

            //            return array_key_first($scores);
        }



        // Leave it here for now.
        // TODO: Consider three months all with same score
        // TODO: Consider two months wth non-zero scores.
        return false;
    }

    /**
     *
     */
    public function readSubject()
    {
        $this->type = "wedge";

        $input = $this->assert($this->input, "month", false);
        if ($input == "") {
            return;
        }

        $this->months = $this->extractMonths($input);
        $month = $this->extractMonth($input);
        if ($month != false) {
            $this->month = $month;
            //$this->era = $month['era'];
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "month") {
                $this->response .= "Made a month. ";
                return;
            }
        }

        $indicators = [
            "month" => ["month"],
        ];

        $this->flagAgent($indicators, $input);

        $input_agent = new Input($this->thing, "input");
        $discriminators = [
            "wedge" => ["pizza", "wheel", "wedge"],
            "slice" => ["slice", "column", "columns"],
        ];

        $type = $input_agent->discriminateInput($input, $discriminators);
        if ($type != false) {
            $this->type = $type;
        }

        $keywords = ["month"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        default:
                    }
                }
            }
        }
    }
}
