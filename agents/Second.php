<?php
/**
 * Second.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Second extends Agent
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
            "A SECOND is a measurement of elapsed time.";
        $this->thing_report["help"] = "Click on the image for a PDF.";

        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        $command_line = null;

        $this->node_list = [
            "second" => ["leap second", "minut", "year", "uuid"],
        ];

        $this->current_time = $this->thing->json->time();

        // Get some stuff from the stack which will be helpful.
        $this->entity_name = $this->thing->container["stack"]["entity_name"];

        $this->default_canvas_size_x = 2000;
        $this->default_canvas_size_y = 2000;

        $agent = new Retention($this->thing, "retention");
        $this->retain_to = $agent->retain_to;

        $agent = new Persistence($this->thing, "persistence");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->initSecond();

        $this->draw_center = false;
        $this->draw_outline = false; //Draw hexagon line

        $this->canvas_size_x = $this->default_canvas_size_x;
        $this->canvas_size_y = $this->default_canvas_size_y;

        $this->size = min(
            $this->default_canvas_size_x,
            $this->default_canvas_size_y
        );

        if (isset($this->thing->container["stack"]["font"])) {
            $this->font = $this->thing->container["stack"]["font"];
        }
    }

    public function set()
    {
        $this->setSecond();
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
        $whatis = "second";
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), $whatis . " is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen($whatis . " is")
            );
        } elseif (($pos = strpos(strtolower($input), $whatis)) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen($whatis));
        }

        $filtered_input = ltrim($whatIWant, " ");

        $this->whatis = $filtered_input;
    }

    /**
     *
     * @param unknown $t (optional)
     * @return unknown
     */
    public function timestampSecond($t = null)
    {
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
        $sms = "SECOND | ";

        $seconds = [];
        if (isset($this->seconds)) {
            $seconds = $this->seconds;
        }

        $second_text = "";
        foreach ($seconds as $i => $second) {
            $second_text .= $second["second"] . " ";
        }
        $sms .= $second_text;


        $sms .= "" . $this->response;

        $sms .= $this->web_prefix . "thing/" . $this->uuid . "/second";
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Made a second for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid/second.png\n \n\n<br> ";
        $message .=
            '<img src="' .
            $this->web_prefix .
            "thing/" .
            $uuid .
            '/second.png" alt="second" height="92" width="92">';

        $this->thing_report["message"] = $message;
    }

    /**
     *
     */
    public function setSecond()
    {
        return;
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["second", "decimal"],
            $this->decimal_second
        );

        $this->thing->log(
            $this->agent_prefix .
                " saved decimal second " .
                $this->decimal_second .
                ".",
            "INFORMATION"
        );
    }

    /**
     *
     * @return unknown
     */
    public function getSecond()
    {
    }

    /**
     *
     */
    public function initSecond()
    {
    }

    public function run()
    {
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/second.pdf";
        $this->node_list = ["second" => ["minute" => ["year"]]];
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
        $txt = "This is a SECOND";
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

        $this->drawSecond();

        // Write the string at the top left
        $border = 30;
        $r = 1.165;

        $radius = ($r * ($canvas_size_x - 2 * $border)) / 3;

        // devstack add path
        $font = $this->default_font;
        $text = "A second in slices...";
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
        }
        //check width of the image
        $width = imagesx($this->image);
        $height = imagesy($this->image);
        $pad = 0;

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
            '"alt="second"/>';

        $this->html_image =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="second"/>';

        $this->PNG_embed = "data:image/png;base64," . base64_encode($imagedata);

        $this->PNG = $imagedata;

        return $response;
    }

    public function drawSecond($type = null)
    {
        if ($type == null) {
            $type = $this->type;
        }

        $number_of_months = 12;
        if ($this->calendar_type = "13 month") {
            $number_of_months = 13;
        }

        if ($type == "wedge") {
            $this->round_agent = new Round(
                $this->thing,
                "round " . $number_of_months
            );
            $this->image = $this->round_agent->image;
            return;
        }

        $this->slice_agent = new Slice(
            $this->thing,
            "slice " . $number_of_months
        );
        $this->image = $this->slice_agent->image;
    }

    public function sliceSecond()
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

        $number_of_months = 12;
        if ($this->calendar_type = "13 month") {
            $number_of_months = 13;
        }

        $width_slice = ($canvas_size_x - 2 * $border) / $number_of_months;

        // Draw out the state
        $center_x = $canvas_size_x / 2;
        $center_y = $canvas_size_y / 2;

        // devstack rotation not yet implemented
        if (!isset($this->angle)) {
            $this->angle = 0;
        }

        foreach (range(0, $number_of_months, 1) as $i) {
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

    public function wedgeSecond()
    {
        $number_of_months = 12;
        if ($this->calendar_type = "13 month") {
            $number_of_months = 13;
        }

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
        $angle = (2 * 3.14159) / $number_of_months;
        //$x_pt =  230;
        //$y_pt = 230;

        foreach (range(0, $number_of_months - 1, 1) as $i) {
            $x_pt = $size * cos($angle * $i + $init_angle);
            $y_pt = $size * sin($angle * $i + $init_angle);

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
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "second",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["second", "refreshed_at"],
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
        if ($this->default_pdf_page_template === null) {
            $this->thing_report["pdf"] = null;
            return;
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

            $this->getNuuid();
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

            $link = $this->web_prefix . "thing/" . $this->uuid . "/second";

            $this->getQuickresponse($link);
            $pdf->Image($this->quick_response_png, 175, 5, 30, 30, "PNG");

            $pdf->SetTextColor(0, 0, 0);

            $pdf->SetXY(15, 7);

            $line_height = 4;

            $t = $this->thing_report["sms"];

            $t = str_replace(" | ", "\n", $t);

            $pdf->MultiCell(150, $line_height, $t, 0);

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
            $text = $this->timestampSecond();
            $pdf->SetXY(175, 35);
            $pdf->MultiCell(30, $line_height, $text, 0, "L");

            $image = $pdf->Output("", "S");
            $this->thing_report["pdf"] = $image;
        } catch (Exception $e) {
            $this->error .= "Caught exception: " . $e->getMessage() . ". ";
        }

        return $this->thing_report["pdf"];
    }

    public function isSecond($text)
    {
        if (is_float($text)) {
            return false;
        }

        // 1, 2, three or 4 number string.
        // Possibly followed by a string sec, secs, seconds etc

        // Apologies to the future.

        // Any number might be a a count of seconds.
        //        if (mb_strlen($text) == 4) {
        if (ctype_digit($text)) {
            $second = $text;
            return $second;
        }
        //        }

        // TODO ?
        // 23 may 1 is recognized by date_parse as 23 may 2001.
        // dev?

        $this->parsed_date = date_parse($text);
        $second = $this->parsed_date["second"];

        if ($second !== false) {
            // Test if parsed_date has extracted a second from a larger number.
            $stripped_text = str_replace($second, "", $second);
            if (mb_strlen($stripped_text) == 0) {
                return $second;
            }

            // Look for the year in the format 2021/06/05 or 2021-06-05.
            /*
            $variants = [];
            $variants[] = " " . $year . "-";
            $variants[] = " " . $year . "/";
            foreach ($variants as $variant) {
                if (stripos($text, $variant) !== false) {
                    return $year;
                }
            }

            $variants = [];
            $variants[] = "" . $year . "-";
            $variants[] = "" . $year . "/";
            foreach ($variants as $variant) {
                if (substr($text, 0, mb_strlen($variant)) == $variant) {
                    return $year;
                }
            }
*/
            //            return $year;
        }

        // Any number less than 60 could be a second.
        // Rely on the number catch at the start of this function for any number.
        if (is_integer($text) and $text < 60) {
            return $text;
        }

        // https://blog.esllibrary.com/2015/11/05/abbreviations-bc-ad-bce-ce/
        // BC, BCE, CE come after the year
        // AD comes before the year (but recognize common practice)
        $second_indicators = [
            "s",
            "sec",
            "secs",
            "second",
            "seconds",
            "s.",
            "sec.",
            "secs.",
            "segundos",
        ];

        foreach ($second_indicators as $second_indicator) {
            if (stripos($text, $second_indicator) !== false) {
                //$number = $this->number_agent->extractNumber($text);
                $number = $this->extractNumber($text);
                if ($number == null) {
                    continue;
                }

                // Test against a few ways these might appear.
                $variants = [];
                $variants[] = $second_indicator . " " . $number;
                $variants[] = $second_indicator . "" . $number;
                $variants[] = $number . " " . $second_indicator;
                $variants[] = $number . "" . $second_indicator;
                $variants[] = $number . "(" . $second_indicator . ")";
                $variants[] = $number . "[" . $second_indicator . "]";

                foreach ($variants as $variant) {
                    if (stripos($text, $variant) !== false) {
                        return $number;
                    }
                }
            }
        }

        return false;
    }

    public function extractSeconds($text = null)
    {
        $seconds = [];

        if ($text == null or $text == "") {
            return true;
        }

        $tokens = [];
        foreach (range(0, 4, 1) as $i) {
            $new_grams = $this->extractNgrams($text, $i);
            $tokens = array_merge($tokens, $new_grams);
        }
        foreach ($tokens as $i => $token) {
            if ($token == "") {
                continue;
            }
            $response = $this->isSecond($token);
            if ($response === false) {
                continue;
            }
            // TODO refactor
            if (is_string($response)) {
                $response = intval($response);
            }
            if (is_integer($response)) {
                // Check if a day has been mis-categorized as a second.
                $this->parsed_date = date_parse($text);
                $day = $this->parsed_date["day"];
                if ($response == $day) {
                    continue;
                }
                // It is a number.
                // But is it a number inside of a telephone number.
                $t = $this->extractTelephonenumbers($text);

                // Or a 3 3 4 pattern.

                //        $pattern = "/\b\d{3} \d{4} \d{4}\b/i";

                //        preg_match_all($pattern, $text, $match);
                //        $t = array_merge($t, $match[0]);

                foreach ($t as $j => $telephone_number) {
                    if (
                        stripos($telephone_number, strval($response)) !== false
                    ) {
                        continue 2;
                    }
                }
                $second_text = strval($response);

                //$era = $this->eraYear($text);
                $second = ["second" => $second_text];
                $seconds[] = $second;
            }
        }

        // Remove duplicates.
        // https://stackoverflow.com/questions/307674/how-to-remove-duplicate-values-from-a-multi-dimensional-array-in-php
        $serialized = array_map("serialize", $seconds);
        $unique = array_unique($serialized);

        $seconds = array_intersect_key($seconds, $unique);

        if (count($seconds) === 1) {
            return $seconds;
        }

        // Check if the year appears as a distinct token.
        $filtered_seconds = [];
        foreach ($tokens as $i => $token) {
            foreach ($seconds as $j => $second) {
                if ($second["second"] === $token) {
                    $filtered_seconds[] = $second;
                }
            }
        }

        // Remove duplicates.
        // https://stackoverflow.com/questions/307674/how-to-remove-duplicate-value>
        $serialized = array_map("serialize", $filtered_seconds);
        $unique = array_unique($serialized);

        $filtered_seconds = array_intersect_key($filtered_seconds, $unique);
        return $filtered_seconds;
    }

    public function extractSecond($text = null)
    {
        if ($text == null) {
            return true;
        }
        $second = false;

        if (isset($this->seconds) and $this->seconds == []) {
            return false;
        }

        $seconds = [];
        if (!isset($this->seconds)) {
            $seconds = $this->extractSeconds($text);
        }

        if (count($seconds) == 1) {
            $second = $seconds[0];
        }
        return $second;
    }

    /**
     *
     */
    public function readSubject()
    {
        $this->type = "wedge";
        //$input = $this->agent_input;
        $input = $this->agent_input;
        if ($this->agent_input == "" or $this->agent_input == null) {
            $input = $this->subject;
        }

        if ($input == "second") {
            return;
        }

        $this->seconds = $this->extractSeconds($input);
        $second = $this->extractSecond($input);
        if ($second != false) {
            $this->second = $second["second"];
        }

        $pieces = explode(" ", strtolower($input));

        $this->calendar_type = null;
        if (
            stripos($input, "13") !== false or
            stripos($input, "thirteen month") !== false
        ) {
            $this->calendar_type = "13 month";
            $this->response .= "Saw a request for a thirteen month calendar. ";
        }

        if (count($pieces) == 1) {
            if ($input == "second") {
                $this->getSecond();

                if (
                    !isset($this->decimal_second) or
                    $this->decimal_second == null
                ) {
                    $this->decimal_second = rand(1, rand(1, 10) * 1e11);
                }

                $this->binarySecond($this->decimal_second);
                $p = strlen($this->binary_second);

                $this->max = 13;
                $this->size = 4;
                $this->lattice_size = 40;
                $this->response .= "Made a second. ";
                return;
            }
        }

        $input_agent = new Input($this->thing, "input");
        $discriminators = ["wedge", "slice"];
        $input_agent->aliases["wedge"] = ["pizza", "wheel", "wedge"];

        $input_agent->aliases["slice"] = ["slice", "column", "columns"];
        $type = $input_agent->discriminateInput($input, $discriminators);
        if ($type != false) {
            $this->type = $type;
        }

        $keywords = ["second"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        default:
                    }
                }
            }
        }

        $this->getSecond();
    }
}