<?php
/**
 * Spiral.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

// TODO: Respond to fourth parameter. Init start angle.

class Spiral extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->test = "Development code";

        $this->thing_report["info"] = "A SPIRAL is a repeating pattern.";
        $this->thing_report["help"] = 'Click on the image for a PDF.';

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $command_line = null;

        $this->node_list = [
            "spiral" => ["spiral", "spirals"],
        ];

        $this->current_time = $this->thing->time();

        // Get some stuff from the stack which will be helpful.
        $this->entity_name = $this->thing->container['stack']['entity_name'];

        $this->default_canvas_size_x = 2000;
        $this->default_canvas_size_y = 2000;

        $this->thing->refresh_at = $this->thing->time(
            time() + 2 * 24 * 60 * 60
        ); // Never refresh.

        $agent = new Retention($this->thing, "retention");
        $this->retain_to = $agent->retain_to;

        $agent = new Persistence($this->thing, "persistence");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        // Archimedean spiral
        // length, a, b, (start angle)
        $this->default_spirals = [200, 10, 25];

        $this->initSpiral();

        $this->draw_center = false;
        $this->draw_outline = false;

        $this->canvas_size_x = $this->default_canvas_size_x;
        $this->canvas_size_y = $this->default_canvas_size_y;

        $this->size = min(
            $this->default_canvas_size_x,
            $this->default_canvas_size_y
        );

        if (isset($this->thing->container['stack']['font'])) {
            $this->font = $this->thing->container['stack']['font'];
        }
    }

    public function set()
    {
        $this->setSpiral();
    }

    /**
     *
     * @param unknown $input
     */
    function getWhatis($input)
    {
        $whatis = "spiral";
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
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }
    }

    /**
     *
     */
    public function makeChoices()
    {
        $this->choices = false;
        $this->thing_report['choices'] = $this->choices;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms = "SPIRAL | ";
        $sms .= $this->web_prefix . "thing/" . $this->uuid . "/spiral";
        $sms .= " | " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Made a spiral for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid/spiral.png\n \n\n<br> ";
        $message .=
            '<img src="' .
            $this->web_prefix .
            'thing/' .
            $uuid .
            '/spiral.png" alt="spiral" height="92" width="92">';

        $this->thing_report['message'] = $message;
    }

    /**
     *
     */
    public function setSpiral()
    {
        $this->thing->Write(["spiral", "spirals"], $this->spirals);
    }

    /**
     *
     * @return unknown
     */
    // TODO
    public function getSpiral()
    {
        $spirals = $this->thing->Read(["spiral", "spirals"]);

        if ($spirals == false) {
            $this->thing->log(
                $this->agent_prefix . ' did not find spirals.',
                "INFORMATION"
            );
            // No spirals saved.  Return.
            return true;
        }
    }

    /**
     *
     */
    public function initSpiral()
    {
        if (!isset($this->size)) {
            $this->size = 50;
        }
    }

    public function run()
    {
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/spiral.pdf';
        $this->node_list = ["spiral" => ["week"]];
        $web = "";
        $web .= '<a href="' . $link . '">';
        $web .= $this->html_image;
        $web .= "</a>";
        $web .= "<br>";
        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    public function makeTXT()
    {
        $txt = 'This is a SPIRAL';
        $txt .= "\n";

        $this->thing_report['txt'] = $txt;
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

    // TODO: Factor out as seperate common class to multiple agents.

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
        //$this->getColours();
        //$this->image = imagecreatetruecolor(164, 164);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

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

        $this->drawSpirals();

        $this->drawSpokes();

        // Write the string at the top left
        $border = 30;
        $r = 1.165;

        $radius = ($r * ($canvas_size_x - 2 * $border)) / 3;

        // devstack add path
        $font = $this->default_font;
        $text = "A spiral in slices...";
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
            extract($bbox, EXTR_PREFIX_ALL, 'bb');
        }
        //check width of the image
        $width = imagesx($this->image);
        $height = imagesy($this->image);
        $pad = 0;
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

        $this->thing_report['png'] = $imagedata;

        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="spiral"/>';

        $this->html_image =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="spiral"/>';

        $this->PNG_embed = "data:image/png;base64," . base64_encode($imagedata);
        $this->PNG = $imagedata;

        return $response;
    }

    public function drawSpokes()
    {
        $number = 7;
        //        if ($type == "wedge") {
        $this->round_agent = new Round($this->thing, "round " . $number);
        $image = $this->round_agent->image;

        $width = imagesx($this->image);
        $height = imagesy($this->image);

        //$dest = @imagecreatefrompng('image1.png');
        //$src = @imagecreatefrompng('image2.png');

        // Copy and merge
        //imagecopymerge($this->image, $image, 0, 0, 0, 0, $width, $height, 50);
    }

    public function drawLine()
    {
    }

    public function drawSpirals()
    {
        //        $border = 100;
        //        $size = 1000 - $border;

        //        $nautilus_width = $size / (count($this->nautilii) + 1);
        //        $nautilus = 1000;

        $size_start = 0;
        $size_end = 200;
        if (isset($this->spirals[0])) {
            $size_end = $this->spirals[0];
        }

        $a = 50;
        if (isset($this->spirals[1])) {
            $a = $this->spirals[1];
        }

        $b = 10;
        if (isset($this->spirals[2])) {
            $b = $this->spirals[2];
        }

        $init_degrees = 0;
        if (isset($this->spirals[3])) {
            $init_degrees = $this->spirals[3];
        }

        $this->drawSpiral($a, $b, $init_degrees);
    }

    public function drawSpiral(
        $a = 10,
        $b = 25,
        $init_degrees = null,
        $end_degrees = null
    ) {

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

        $next_x_pt = 0;
        $next_y_pt = 0;
        $x_path_old = 0;
        $y_path_old = 0;


        $coords = [];

        $first_point = true;

        foreach (range($init_degrees, $end_degrees, 1) as $i) {

            $x_pt = $next_x_pt;
            $y_pt = $next_y_pt;

            $t = ($i * pi()) / 180;
            $next_x_pt = ($a + $b * $t) * cos($t);
            $next_y_pt = ($a + $b * $t) * sin($t);

            // Check for intersection on path
            $x_path = 0;
            $y_path = 0;
            $intersection_point = true;

            if (false) {
                foreach (array_reverse($coords) as $j => $coord) {
                    if (!isset($coords[$j - 1])) {
                        continue;
                    }
                    $x_path_old = array_reverse($coords)[$j - 1]['x'];
                    $y_path_old = array_reverse($coords)[$j - 1]['y'];

                    $x_path = $coord['x'];
                    $y_path = $coord['y'];

                    $intersection_point = $this->intersectLine(
                        [
                            ['x' => $x_path_old, 'y' => $y_path_old],
                            ['x' => $x_path, 'y' => $y_path],
                        ],
                        [
                            ['x' => 0, 'y' => 0],
                            ['x' => $next_x_pt, 'y' => $next_y_pt],
                        ]
                    );

                    // Plot the spiral path from saved coords
                    /*
                imageline(
                    $this->image,
                    $center_x + $x_path_old,
                    $center_y + $y_path_old,
                    $center_x + $x_path,
                    $center_y + $y_path,
                    $this->red
                );
*/

                    /*
                imageline(
                    $this->image,
                    $center_x + 0,
                    $center_y + 0,
                    $center_x + $next_x_pt,
                    $center_y + $next_y_pt,
                    $this->green
                );
*/

                    if ($intersection_point !== true) {
                        break;
                    }
                }
            }
            //$inner_x_pt = 0;
            //$inner_y_pt = 0;

            $coords[] = ["x" => $next_x_pt, "y" => $next_y_pt];
            //if ($i != $size_start) {

            if ($first_point === false) {
                imageline(
                    $this->image,
                    $center_x + $next_x_pt,
                    $center_y + $next_y_pt,
                    $center_x + $x_pt,
                    $center_y + $y_pt,
                    $this->black
                );
            }
            $first_point = false;
            //}

            /*
                if ($intersection_point !== true) {
                    imageline(
                        $this->image,
                        $center_x + $intersection_point['x'],
                        $center_y + $intersection_point['y'],
                        $center_x + $next_x_pt,
                        $center_y + $next_y_pt,
                        $this->black
                    );
                } else {
                    imageline(
                        $this->image,
                        $center_x + 0,
                        $center_y + 0,
                        $center_x + $next_x_pt,
                        $center_y + $next_y_pt,
                        $this->black
                    );
                }
*/
            //          }
        }
        return $this->image;
    }

    public function get()
    {
        $time_string = $this->thing->Read(["spiral", "refreshed_at"]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(["spiral", "refreshed_at"], $time_string);
        }
    }

    // https://rosettacode.org/wiki/Find_the_intersection_of_two_lines#Python
    public function intersectLine($line1, $line2)
    {
        $Ax1 = $line1[0]['x'];
        $Ay1 = $line1[0]['y'];
        $Ax2 = $line1[1]['x'];
        $Ay2 = $line1[1]['y'];

        $Bx1 = $line2[0]['x'];
        $By1 = $line2[0]['y'];
        $Bx2 = $line2[1]['x'];
        $By2 = $line2[1]['y'];

        //    """ returns a (x, y) tuple or None if there is no intersection """
        $d = ($By2 - $By1) * ($Ax2 - $Ax1) - ($Bx2 - $Bx1) * ($Ay2 - $Ay1);
        if ($d != 0) {
            $uA =
                (($Bx2 - $Bx1) * ($Ay1 - $By1) -
                    ($By2 - $By1) * ($Ax1 - $Bx1)) /
                $d;
            $uB =
                (($Ax2 - $Ax1) * ($Ay1 - $By1) -
                    ($Ay2 - $Ay1) * ($Ax1 - $Bx1)) /
                $d;
        } else {
            return true;
        }

        if (!(0 <= $uA and $uA <= 1 and (0 <= $uB and $uB <= 1))) {
            return true;
        }
        $x = $Ax1 + $uA * ($Ax2 - $Ax1);
        $y = $Ay1 + $uA * ($Ay2 - $Ay1);

        return ['x' => $x, 'y' => $y];
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
            $this->thing_report['pdf'] = false;
            return $this->thing_report['pdf'];
        }

        $this->getWhatis($this->subject);
        try {
            // initiate FPDI
            $pdf = new Fpdi\Fpdi();

            $pdf->setSourceFile($this->default_pdf_page_template);
            $pdf->SetFont('Helvetica', '', 10);

            $tplidx1 = $pdf->importPage(1, '/MediaBox');
            $s = $pdf->getTemplatesize($tplidx1);

            $pdf->addPage($s['orientation'], $s);
            $pdf->useTemplate($tplidx1);

            $pdf->Image($this->PNG_embed, 7, 30, 200, 200, 'PNG');

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(1, 1);

            $pdf->SetFont('Helvetica', '', 26);
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
                    'PNG'
                );
            }

            // Page 2
            $tplidx2 = $pdf->importPage(2);

            $pdf->addPage($s['orientation'], $s);

            $pdf->useTemplate($tplidx2, 0, 0);
            // Generate some content for page 2

            $pdf->SetFont('Helvetica', '', 10);
            $this->txt = "" . $this->uuid . ""; // Pure uuid.

            $link = $this->web_prefix . 'thing/' . $this->uuid . '/spiral';

            $pdf->SetTextColor(0, 0, 0);

            $pdf->SetXY(15, 7);

            $line_height = 4;

            $t = $this->thing_report['sms'];

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

            $image = $pdf->Output('', 'S');
            $this->thing_report['pdf'] = $image;
        } catch (Exception $e) {
            $this->error .= 'Caught exception: ' . $e->getMessage() . ". ";
        }

        return $this->thing_report['pdf'];
    }

    /**
     *
     */
    public function readSubject()
    {
        $this->type = "wedge";

        $input = $this->input;

        $number_agent = new Number($this->thing, "number");
        $t = $number_agent->extractNumbers($input);

        if ($t == []) {
            $t = $this->default_spirals;
        }

        $this->spirals = $t;
        $this->response .=
            "Saw a request to make a spiral with these parameters: " .
            implode(" ", $this->spirals) .
            ". ";

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'spiral') {
                $this->getSpiral();

                $this->size = 4;
                $this->response .= "Made a spiral pattern. ";
                return;
            }
        }

        $input_agent = new Input($this->thing, "input");
        $discriminators = ['spiral', 'spirals'];
        $input_agent->aliases['spiral'] = ['wheel'];
        $input_agent->aliases['spirals'] = ['wheels'];
        $type = $input_agent->discriminateInput($input, $discriminators);
        if ($type != false) {
            $this->type = $type;
        }

        $keywords = ["pattern"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        default:
                    }
                }
            }
        }

        $this->getSpiral();
    }
}
