<?php
/**
 * Nautilus.php
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

class Nautilus extends Agent
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

        $this->thing_report["info"] = "A NAUTILUS is a repeating pattern.";
        $this->thing_report["help"] = 'Click on the image for a PDF.';

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $command_line = null;

        $this->node_list = [
            "nautilus" => ["nautilus", "nautilii"],
        ];

        $this->current_time = $this->thing->json->time();

        // Get some stuff from the stack which will be helpful.
        $this->entity_name = $this->thing->container['stack']['entity_name'];

        $this->default_canvas_size_x = 2000;
        $this->default_canvas_size_y = 2000;

        $this->thing->refresh_at = $this->thing->time(time() + 2*24*60*60); // Never refresh.

        $agent = new Retention($this->thing, "retention");
        $this->retain_to = $agent->retain_to;

        $agent = new Persistence($this->thing, "persistence");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        // length, segments, growth angle, (start angle)
        $this->default_nautilii = [38, 200, 5];

        $this->initNautilus();

        $this->draw_center = false;
        $this->draw_outline = false;

        $this->canvas_size_x = $this->default_canvas_size_x;
        $this->canvas_size_y = $this->default_canvas_size_y;

        $this->size = min(
            $this->default_canvas_size_x,
            $this->default_canvas_size_y
        );

        if (
            isset($this->thing->container['stack']['font'])
        ) {
            $this->font =
                $this->thing->container['stack']['font'];
        }


    }

    public function set()
    {
        $this->setNautilus();
    }

    /**
     *
     * @param unknown $input
     */
    function getWhatis($input)
    {
        $whatis = "nautilus";
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
        $sms = "NAUTILUS | ";
        $sms .= $this->web_prefix . "thing/" . $this->uuid . "/nautilus";
        $sms .= " | " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Made a nautilus for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid/nautilus.png\n \n\n<br> ";
        $message .=
            '<img src="' .
            $this->web_prefix .
            'thing/' .
            $uuid .
            '/nautilus.png" alt="nautilus" height="92" width="92">';

        $this->thing_report['message'] = $message;
    }

    /**
     *
     */
    public function setNautilus()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["nautilus", "nautilii"],
            $this->nautilii
        );
    }

    /**
     *
     * @return unknown
     */
    // TODO
    public function getNautilus()
    {
        $this->thing->json->setField("variables");
        $nautilii = $this->thing->json->readVariable(["nautilus", "nautilii"]);

        if ($nautilii == false) {
            $this->thing->log(
                $this->agent_prefix . ' did not find nautilii.',
                "INFORMATION"
            );
            // No nautilii saved.  Return.
            return true;
        }
    }

    /**
     *
     */
    public function initNautilus()
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
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/nautilus.pdf';
        $this->node_list = ["nautilus" => ["week"]];
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
        $txt = 'This is a NAUTILUS';
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

        $this->drawNautilii();

        // Write the string at the top left
        $border = 30;
        $r = 1.165;

        $radius = ($r * ($canvas_size_x - 2 * $border)) / 3;

        // devstack add path
        $font = $this->default_font;
        $text = "A nautilus in slices...";
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

        //echo '<img src="data:image/png;base64,'.base64_encode($imagedata).'"/>';
        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="nautilus"/>';

        $this->html_image =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="nautilus"/>';

        $this->PNG_embed = "data:image/png;base64," . base64_encode($imagedata);
        $this->PNG = $imagedata;

        return $response;
    }

    public function drawNautilii()
    {
//        $border = 100;
//        $size = 1000 - $border;

//        $nautilus_width = $size / (count($this->nautilii) + 1);
//        $nautilus = 1000;

        $size = 50;
        if (isset($this->nautilii[0])) {
            $size = $this->nautilii[0];
        }

        $segments = 50;
        if (isset($this->nautilii[1])) {
            $segments = $this->nautilii[1];
        }

        $mu_degrees = 0;
        if (isset($this->nautilii[2])) {
            $mu_degrees = $this->nautilii[2];
        }

        $init_degrees = 0;
        if (isset($this->nautilii[3])) {
            $init_degrees = $this->nautilii[3];
        }

        $this->drawNautilus($segments, $size, $mu_degrees, $init_degrees);
    }

    public function drawNautilus(
        $n = 7,
        $size = null,
        $mu_degrees = null,
        $init_degrees = null
    ) {
        if ($mu_degrees == null) {
            $mu_degrees = 0;
        }
        $mu_radians = ($mu_degrees / 360) * 2 * pi();

        if ($init_degrees == null) {
            $init_degrees = 0;
        }
        $init_radians = ($init_degrees / 360) * 2 * pi();
        //var_dump($init_radians);
        if ($size == null) {
            $size = $this->size;
        }

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

        //$size = 50;
        $next_x_pt = 0;
        $next_y_pt = $size;
        $x_path_old = 0;
        $y_path_old = 0;

        if ($n > 1) {
            //            $init_angle = (-1 * pi()) / 2;
            //            $angle = (2 * 3.14159) / $n;

            $coords = [];
            foreach (range(0, $n, 1) as $i) {
                if ($next_x_pt - 0 == 0) {
                    $radial_angle = 0;
                } else {
                    $radial_angle = atan(($next_y_pt - 0) / ($next_x_pt - 0));
                }

                //if ($i == 0) {$radial_angle = $init_radians;}

                $radial_angle = $radial_angle + $mu_radians;

                $sign = 1;
                if ($next_x_pt < 0 and $next_y_pt < 0) {
                    $sign = +1;
                }
                if ($next_x_pt < 0 and $next_y_pt > 0) {
                    $sign = +1;
                }
                if ($next_x_pt > 0 and $next_y_pt < 0) {
                    $sign = -1;
                }
                if ($next_x_pt > 0 and $next_y_pt > 0) {
                    $sign = -1;
                }

                /*
                imageline(
                    $this->image,
                    $center_x ,
                    $center_y ,
                    $center_x + 1000,
                    $center_y + 1000,
                    $this->black
                );
*/

                $x_pt = $next_x_pt;
                $y_pt = $next_y_pt;

                $next_x_pt =
                    $x_pt + $size * cos($radial_angle + $sign * (pi() / 2));
                $next_y_pt =
                    $y_pt + $size * sin($radial_angle + $sign * (pi() / 2));

                // Check for intersection on path
                $x_path = 0;
                $y_path = 0;
                $intersection_point = true;
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

                //$inner_x_pt = 0;
                //$inner_y_pt = 0;

                $coords[] = ["x" => $next_x_pt, "y" => $next_y_pt];

                imageline(
                    $this->image,
                    $center_x + $next_x_pt,
                    $center_y + $next_y_pt,
                    $center_x + $x_pt,
                    $center_y + $y_pt,
                    $this->black
                );

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
            }
        }
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "nautilus",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["nautilus", "refreshed_at"],
                $time_string
            );
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
        if (($this->default_pdf_page_template === null) or (!file_exists($this->default_pdf_page_template))) {
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

            $link = $this->web_prefix . 'thing/' . $this->uuid . '/nautilus';

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

            // Good until?
            //$text = $this->timestampNautilus();
            //$pdf->SetXY(175, 35);
            //$pdf->MultiCell(30, $line_height, $text, 0, "L");

            $image = $pdf->Output('', 'S');
            $this->thing_report['pdf'] = $image;
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
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
            $t = $this->default_nautilii;
        }

        $this->nautilii = $t;
        $this->response .=
            "Saw a request to make a nautilus with these parameters: " .
            implode(" ", $this->nautilii) .
            ". ";

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'nautilus') {
                $this->getNautilus();

                $this->size = 4;
                $this->response .= "Made a nautilus pattern. ";
                return;
            }
        }

        $input_agent = new Input($this->thing, "input");
        $discriminators = ['nautilus', 'nautilii'];
        $input_agent->aliases['nautilus'] = ['spiral', 'wheel'];
        $input_agent->aliases['nautilii'] = ['spirals', 'wheels', 'nautiluses'];
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

        $this->getNautilus();
    }
}
