<?php
/**
 * Snowflake.php
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

class Snowflake extends Agent
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

        $this->thing_report["info"] =
            "A SNOWFLAKE is a semi-unique pattern with an associated UUID.";
        $this->thing_report["help"] = 'Try "UUID SNOWFLAKE".';

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $command_line = null;

        $this->node_list = ["snowflake" => ["snowflake", "uuid"]];

        $this->current_time = $this->thing->json->time();

        // Get some stuff from the stack which will be helpful.
        $this->entity_name = $this->thing->container['stack']['entity_name'];

        $this->default_canvas_size_x = 164;
        $this->default_canvas_size_y = 164;

        $agent = new Retention($this->thing, "retention");
        $this->retain_to = $agent->retain_to;

        $agent = new Persistence($this->thing, "persistence");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->initSnowflake();

        $this->draw_center = false;
        $this->draw_outline = false; //Draw hexagon line

        if (
            isset($this->thing->container['stack']['font'])
        ) {
            $this->font =
                $this->thing->container['stack']['font'];
        }


        $this->thing->log(
            $this->agent_prefix .
                'completed getSnowflake. Timestamp = ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.',
            "OPTIMIZE"
        );
    }

    public function set()
    {
        $this->setSnowflake();
    }

    // https://www.math.ucdavis.edu/~gravner/RFG/hsud.pdf

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
    function whatisSnowflake($input)
    {
        $filtered_input = $this->assert($input);
        /*
        $whatis = "snowflake";
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
*/

        if (is_string($filtered_input) and $filtered_input != "") {
            $this->response .= 'Is "' . $filtered_input . '". ';
        }
        $this->whatis = $filtered_input;
    }

    /**
     *
     * @param unknown $t (optional)
     * @return unknown
     */
    public function timestampSnowflake($t = null)
    {
        // $s = $this->thing->thing->created_at;
        $s = $this->created_at;
        if (!isset($this->retain_to)) {
            $text = "X";
        } else {
            $t = $this->retain_to;
            $text = "GOOD UNTIL " . strtoupper(date('Y M d D H:i', $t));
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
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        return $this->thing_report;
    }

    /**
     *
     */
    public function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "snowflake"
        );

        $this->choices = $this->thing->choice->makeLinks('snowflake');
        $this->thing->log(
            $this->agent_prefix .
                'completed makeLinks. Timestamp = ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.',
            "OPTIMIZE"
        );

        $this->thing_report['choices'] = $this->choices;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $cell = $this->lattice[0][0][0];
        //$sms = "SNOWFLAKE | cell (0,0,0) state ". strtoupper($cell['state']);
        $sms = "SNOWFLAKE | ";
        //$sms .= $this->web_prefix . "thing/" . $this->uuid . "/snowflake";
        $sms .= "" . $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Stackr made a snowflake for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid/snowflake.png\n \n\n<br> ";
        $message .=
            '<img src="' .
            $this->web_prefix .
            'thing/' .
            $uuid .
            '/snowflake.png" alt="snowflake" height="92" width="92">';

        $this->thing_report['message'] = $message;
    }

    public function latticeSnowflake()
    {
        $lattice_agent = new Lattice($this->thing, "lattice");

        $lattice_agent->initLattice();

        $lattice_agent->font_size = 16;

        $lattice_agent->max = 12;
        $lattice_agent->size = 120;
        $lattice_agent->lattice_size = 40;
        $lattice_agent->angle = -pi() / 2;

        $lattice_agent->center_x = 400;
        $lattice_agent->center_y = 550;

        //$lattice_agent->angle = 0;

        $lattice_agent->canvas_size_x = 2550;
        $lattice_agent->canvas_size_y = 2860;

        //        $lattice_agent->initLattice();

        $lattice_agent->q_centre = 8;
        $lattice_agent->r_centre = 8;
        $lattice_agent->s_centre = 8;

        //$lattice_agent->drawLattice(10,10,10,25,0);

        //$lattice_agent->makeImage();

        $lattice_agent->makePNG();

        $this->hextile_PNG = $lattice_agent->PNG_embed;
    }

    /**
     *
     */
    public function setSnowflake()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["snowflake", "decimal"],
            $this->decimal_snowflake
        );

        $this->thing->log(
            $this->agent_prefix .
                ' saved decimal snowflake ' .
                $this->decimal_snowflake .
                '.',
            "INFORMATION"
        );
    }

    /**
     *
     * @return unknown
     */
    public function getSnowflake()
    {
        $this->thing->json->setField("variables");
        $this->decimal_snowflake = $this->thing->json->readVariable([
            "snowflake",
            "decimal",
        ]);

        if ($this->decimal_snowflake == false) {
            $this->thing->log(
                $this->agent_prefix . ' did not find a decimal snowflake.',
                "INFORMATION"
            );
            // No snowflake saved.  Return.
            return true;
        }

        //        $this->max = 12;
        //        $this->size = 3.7;
        //        $this->lattice_size = 15;

        //        $this->binarySnowflake($this->decimal_snowflake);

        $this->thing->log(
            'loaded decimal snowflake ' . $this->decimal_snowflake . '.',
            "INFORMATION"
        );
    }

    /**
     *
     */
    public function initSnowflake()
    {
        if (!isset($this->max)) {
            $this->max = 12;
        }
        if (!isset($this->size)) {
            $this->size = 3.7;
        }
        if (!isset($this->lattice_size)) {
            $this->lattice_size = 15;
        }

        $this->initLattice($this->max);
        $this->initSegment();

        $this->setProbability();
        $this->setRules();
    }

    public function run()
    {
        $this->binarySnowflake($this->decimal_snowflake);

        $this->snowflake_points = [];
        $index = 0;

        foreach ($this->point_list as $point) {
            list($q, $r, $s) = $point;
            $value = rand(0, 1);

            if ($this->binary_snowflake[$index] == 1) {
                $value = ["name" => null, "state" => 'on', "value" => 1];
            } else {
                $value = ["name" => null, "state" => 'off', "value" => 0];
            }

            $this->lattice[$q][$r][$s] = $value;
            $this->snowflake_points[] = $this->binary_snowflake[$index];
            $index += 1;
            if ($index >= strlen($this->binary_snowflake)) {
                break;
            }
        }
    }

    /**
     *
     */
    /*
    public function decimalUuid()
    {
        $uuid_agent = new Uuid($this->thing, "uuid");
        $this->decimal_snowflake = $uuid->decimalUuid($this->uuid);
return;
        $hex = str_replace("-", "", $this->uuid);

        $dec = 0;
        $len = strlen($hex);
        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd(
                $dec,
                bcmul(
                    strval(hexdec($hex[$i - 1])),
                    bcpow('16', strval($len - $i))
                )
            );
        }

        $this->decimal_snowflake = $dec;
    }
*/
    /**
     *
     */
    public function binaryUuid()
    {
        $hex = str_replace("-", "", $this->uuid);

        $bin = 0;
        $len = strlen($hex);
        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd(
                $dec,
                bcmul(
                    strval(hex2bin($hex[$i - 1])),
                    bcpow('16', strval($len - $i))
                )
            );
        }

        $this->thing->log(
            ' loaded decimal snowflake ' . $this->decimal_snowflake . '.',
            "INFORMATION"
        );
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.pdf';
        $this->node_list = ["snowflake" => ["snowflake"]];
        //$web = "<b>Snowflake Agent</b><br><p>";
        $web = "";
        $web .= '<a href="' . $link . '">';
        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';
        $web .= $this->html_image;
        $web .= "</a>";
        $web .= "<br>";

        //$web .= $this->selector_hex . "<br>";
        //$web .= $this->selector_dec . "<br>";
        //$web .= $this->angle . "<br>";
        //$web .= $this->selector . "<br>";

        $this->timestampSnowflake($this->retain_to);
        $web .= ucwords($this->timestamp);

        //$web .= "<p>";

        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    public function makeTXT()
    {
        $txt = 'This is a SNOWFLAKE';
        $txt .= "\n";
        $txt .= count($this->lattice) . ' cells retrieved.';

        $txt .= "\n";
        $txt .= str_pad("COORD (Q,R,S)", 15, ' ', STR_PAD_LEFT);
        $txt .= " " . str_pad("NAME", 10, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("STATE", 10, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("VALUE", 10, " ", STR_PAD_LEFT);

        $txt .= " " . str_pad("COORD (X,Y)", 6, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        // Centre framed on 0,0,0
        $q_array = [-2, -1, 0, 1, 2];
        $r_array = [-2, -1, 0, 1, 2];
        $s_array = [-2, -1, 0, 1, 2];

        // Run the lattice update/display loops
        foreach ($this->point_list as $point) {
            //    foreach($r_array as $r){
            //        foreach($s_array as $s){
            list($q, $r, $s) = $point;

            //$cell = $this->lattice[$q][$r][$s];
            $cell = $this->getCell($q, $r, $s);

            $txt .=
                " " .
                str_pad(
                    "(" . $q . "," . $r . "," . $s . ")",
                    15,
                    " ",
                    STR_PAD_LEFT
                );

            $txt .= " " . str_pad($cell['name'], 10, ' ', STR_PAD_LEFT);
            $txt .= " " . str_pad($cell['state'], 10, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($cell['value'], 10, " ", STR_PAD_RIGHT);

            //$txt .= " " . str_pad($cell['neighbours'], 10, ' ', STR_PAD_LEFT);
            //$txt .= " " . str_pad($cell['p_melt'], 10, " ", STR_PAD_LEFT);
            //$txt .= " " . str_pad($cell['p_freeze'], 10, " " , STR_PAD_RIGHT);

            $txt .= "\n";

            //}
            // }
        }

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

        imagecolortransparent($this->image, $this->white);

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
        //if (isset($this->text_color)) {
        //    $textcolor = $this->text_color;
        //}

        //        $this->drawSnowflake($canvas_size_x/2, $canvas_size_y/2);
        $this->drawSnowflake();

        // Write the string at the top left
        $border = 30;
        $r = 1.165;

        $radius = ($r * ($canvas_size_x - 2 * $border)) / 3;

        // devstack add path
        $font = $this->font;
        //$font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';
        $text = "test";
        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

        $size = $canvas_size_x - 90;
        $angle = 0;

        //check width of the image
        $width = imagesx($this->image);
        $height = imagesy($this->image);

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
            //check width of the image
            //        $width = imagesx($this->image);
            //        $height = imagesy($this->image);
            $pad = 0;
            //imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $grey, $font, $number);
        }
        imagestring($this->image, 2, 140, 0, $this->thing->nuuid, $textcolor);

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
            '"alt="snowflake"/>';

        $this->html_image =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="snowflake"/>';

        $this->PNG_embed = "data:image/png;base64," . base64_encode($imagedata);

        //        $this->thing_report['png'] = $image;

        //        $this->PNG = $this->image;
        $this->PNG = $imagedata;
        //imagedestroy($this->image);
        //        $this->thing_report['png'] = $imagedata;

        //        $this->PNG_data = "data:image/png;base64,'.base64_encode($imagedata).'";

        return $response;

        $this->PNG = $image;
        $this->thing_report['png'] = $image;

        return;
    }

    /**
     *            $this->agent_prefix .
     */
    public function drawTriangle()
    {
        $pta = [0, 0];
        $ptb = [sqrt(20), 1];
        $ptc = [20, 0];

        imageline($image, 20, 20, 280, 280, $black);
        imageline($image, 20, 20, 20, 280, $black);
        imageline($image, 20, 280, 280, 280, $black);
    }

    /**
     *
     * @param unknown $center_x
     * @param unknown $center_y
     * @param unknown $x
     * @param unknown $y
     * @param unknown $i
     * @return unknown
     */
    public function hex_corner($center_x, $center_y, $x, $y, $i)
    {
        // So this takes a centre co-ordinate
        // and projects a point $size away from it at angle $i.

        $PI = 3.14159;
        $angle_deg = 60 * $i + 30;
        $angle_rad = ($PI / 180) * $angle_deg;

        return [
            $center_x + $x * cos($angle_rad) - sin($angle_rad) * $y,
            $center_y + $x * sin($angle_rad) + cos($angle_rad) * $y,
        ];
    }

    /**
     *
     * @param unknown $r
     * @param unknown $g
     * @param unknown $b
     * @param unknown $s
     * @return unknown
     */
    public function hextopixel($r, $g, $b, $s)
    {
        if ($r + $g + $b != 0) {
            return;
        }

        $y = (3 / 2) * $s * $b;
        // $b = 2/3 * $y / $s
        $x = sqrt(3) * $s * ($b / 2 + $r);
        //$x = - sqrt(3) * $s * ( $b/2 + $g )
        //$r = (sqrt(3)/3 * $x - $y/3 ) / $s
        //$g = -(sqrt(3)/3 * $x + $y/3 ) / $s

        return [$x, $y];
    }

    /**
     *
     * @param unknown $q
     * @param unknown $r
     * @param unknown $s
     * @param unknown $center_x
     * @param unknown $center_y
     * @param unknown $angle
     * @param unknown $size
     * @param unknown $color    (optional)
     */
    public function drawHexagon(
        $q,
        $r,
        $s,
        $center_x,
        $center_y,
        $angle,
        $size,
        $color = null,
        $quantity = null
    ) {
        //if ($color == null) {
        //    $color = $this->white;
        //    $color_outline = $this->grey;
        //}

        list($x_pt, $y_pt) = $this->hextopixel($q, $r, $s, $size);

        if ($this->draw_center == true) {
            // Draw centre points of hexagons
            imageline(
                $this->image,
                $center_x + $x_pt,
                $center_y + $y_pt,
                $center_x + $x_pt,
                $center_y + $y_pt,
                $this->black
            );
        }

        $arr = [0, 1, 2, 3, 4, 5];
        list($x_old, $y_old) = $this->hex_corner(
            $x_pt,
            $y_pt,
            $size,
            $angle,
            count($arr) - 1
        );
        $point_array = [];
        foreach ($arr as &$value) {
            list($x, $y) = $this->hex_corner(
                $x_pt,
                $y_pt,
                $size,
                $angle,
                $value
            );

            $point_array[] = $x + $center_x;
            $point_array[] = $y + $center_y;
            //imageline($this->image, $x+60, $y+60, $x_old+60, $y_old+60, $this->black);

            $x_old = $x;
            $y_old = $y;
        }

        if ($this->draw_outline == true) {
            imagepolygon(
                $this->image,
                $point_array,
                count($point_array) / 2,
                $this->black
            );
        }
        /*
        if ($color == null) {
            $color_outline = $this->grey;
            $r = 155;
            $g = 183;
            $b = 217;
            $this->rgbcolor(rand($r-20,$r+10),rand($g-10,$g+10),rand($b-40, $b+20));
            // Need consistency from image to image
            $this->rgbcolor(155,183,217);
            $color = $this->rgb;
        }
*/

        if ($color != null) {
            // Because this determines what is frozen and not frozen

            //        if ($color != $this->white) {

            $this->rgbcolor(100, 100, 217);
            $color_outline = $this->rgb;

            $r = 155;
            $g = 183;
            $b = 217;
            $this->rgbcolor(
                rand($r - 20, $r + 10),
                rand($g - 10, $g + 10),
                rand($b - 40, $b + 20)
            );
            // Need consistency from image to image
            $this->rgbcolor(155, 183, 217);
            $color = $this->rgb;

            //            $color_outline = $color;

            // $color = $this->rgb;

            // Flag
            if (isset($this->flag->state)) {
                //                $color= $this->{$this->flag->state};

                // Draw a white rectangle
                if (!isset($this->flag->state) or $this->flag->state == false) {
                    $color = $this->grey;
                } else {
                    if (isset($this->{$this->flag->state})) {
                        $color = $this->{$this->flag->state};
                    } elseif (isset($this->{'flag_' . $this->flag->state})) {
                        $color = $this->{'flag_' . $this->flag->state};
                    }
                    // $color_outline = $color;
                }
                $color_outline = $color;
            }

            if (!isset($this->events[0])) {
                $this->event = false;
            } else {
                $this->event = $this->events[0];
            }

            if ($quantity == null) {
                $quantity = 1;
            }
            $color = $this->ice_color_palette[$quantity];

            // Vancouver Pride 2018
            if (
                isset($this->event) and
                    $this->event != false and
                    $this->event->event_name == "vancouver pride 2018" or
                isset($this->flag->state) and $this->flag->state == "rainbow"
            ) {
                $this->selector_hex = substr($this->uuid, 0, 1); // A random number from 0 to 9.
                $this->selector_dec = $this->hextodec($this->selector_hex);

                $this->selector = $this->selector_dec % 3;
                $index_array = [
                    ($q + 10.5) * 0.28,
                    ($q + 11) * 0.28,
                    ($q + 11) * 0.265,
                ];
                $color_index = $index_array[$this->selector];
                $color = $this->green;

                if (isset($this->color_palette[$color_index])) {
                    $color = $this->color_palette[$color_index];
                    $color_outline = $this->rgb;
                }

                if ($color_index < 0 or $color_index >= 6) {
                    $color = $this->rgb;
                    $color_outline = $this->white;
                }
            }

            imagefilledpolygon(
                $this->image,
                $point_array,
                count($point_array) / 2,
                $color
            );
            //imagefilledpolygon($this->image, $point_array, count($point_array)/2, $this->rgb);

            imagepolygon(
                $this->image,
                $point_array,
                count($point_array) / 2,
                $color_outline
            );
            /*
            if ((isset($this->event)) and ($this->event->event_name == "vancouver pride 2018")) {
                imagepolygon($this->image, $point_array, count($point_array)/2, $color_outline);
            } else {
                $this->rgbcolor(100,100,217);
                imagepolygon($this->image, $point_array, count($point_array)/2, $this->rgb);
            }
*/
        }
    }

    /**
     *
     */
    public function setProbability()
    {
        $type = 'preset';

        $this->thing->log(
            $this->agent_prefix .
                'using probability set "' .
                strtoupper($type) .
                '".',
            "DEBUG"
        );

        switch ($type) {
            case 'preset':
                $this->p_freeze = [
                    1,
                    0.2,
                    0.1,
                    0,
                    0.2,
                    0.1,
                    0.1,
                    0,
                    0.1,
                    0.1,
                    1,
                    1,
                    0,
                ];
                $this->p_melt = [
                    0,
                    0.7,
                    0.5,
                    0.5,
                    0,
                    0,
                    0,
                    0.3,
                    0.5,
                    0,
                    0.2,
                    0.1,
                    0,
                ];
                break;
            case 'random':
                $this->p_melt = [];
                $this->p_freeze = [];
                foreach (range(1, 13) as $t) {
                    $this->p_melt[$t] = rand(0, 1000) / 1000;
                    $this->p_freeze[$t] = rand(0, 1000) / 1000;
                }
                break;
            case 'uuid':
                $s = $this->uuid;
                $s = strtolower(str_replace("-", "", $s));

                foreach (range(0, strlen($s), 2) as $i) {
                    $melt = $this->hextodec($s[$i]);
                    $freeze = $this->hextodec($s[$i + 1]);
                    $this->p_melt[$i / 2] = $melt / 15;
                    $this->p_freeze[$i / 2] = $freeze / 15;
                }
                break;
        }
    }

    /**
     *
     * @param unknown $value
     * @return unknown
     */
    public function hextodec($value)
    {
        $n = $value;

        if ($value == 'a') {
            $n = 10;
        }
        if ($value == 'b') {
            $n = 11;
        }
        if ($value == 'c') {
            $n = 12;
        }
        if ($value == 'd') {
            $n = 13;
        }
        if ($value == 'e') {
            $n = 14;
        }
        if ($value == 'f') {
            $n = 15;
        }

        return $n;
    }

    /**
     *
     */
    public function setRules()
    {
        $this->rules = [];
        $this->rules[0][0][0][0][0][1] = 1;
        $this->rules[0][0][0][0][1][1] = 2;
        $this->rules[0][0][0][1][0][1] = 3;
        $this->rules[0][0][0][1][1][1] = 4;
        $this->rules[0][0][1][0][0][1] = 5;
        $this->rules[0][0][1][0][1][1] = 6;
        $this->rules[0][0][1][1][0][1] = 7;
        $this->rules[0][0][1][1][1][1] = 8;
        $this->rules[0][1][0][1][0][1] = 9;
        $this->rules[0][1][0][1][1][1] = 10;
        $this->rules[0][1][1][0][1][1] = 11;
        $this->rules[0][1][1][1][1][1] = 12;
        $this->rules[1][1][1][1][1][1] = 13;
    }

    /**
     *
     * @param unknown $s
     * @return unknown
     */
    public function getProb($s)
    {
        foreach (range(0, 5) as $i) {
            $a = $i % 6;
            $b = ($i + 1) % 6;
            $c = ($i + 2) % 6;
            $d = ($i + 3) % 6;
            $e = ($i + 4) % 6;
            $f = ($i + 5) % 6;

            if (
                isset(
                    $this->rules[$s[$a]][$s[$b]][$s[$c]][$s[$d]][$s[$e]][$s[$f]]
                ) or
                isset(
                    $this->rules[$s[$f]][$s[$e]][$s[$d]][$s[$c]][$s[$b]][$s[$a]]
                )
            ) {
                $n =
                    $this->rules[$s[$a]][$s[$b]][$s[$c]][$s[$d]][$s[$e]][
                        $s[$f]
                    ];
                break;
            } else {
                $n = rand(3, 8);
                //                $n = 13;
            }
        }
        //echo " p = " .$n

        // So we are supposed to use rule N for
        // finding the probability of melting
        // and freezing to the cell.

        $p_melt = $this->p_melt[$n - 1];
        $p_freeze = $this->p_freeze[$n - 1];

        return [$n, $p_melt, $p_freeze];
    }

    /**
     *
     */
    public function initLattice()
    {
        $this->thing->log(
            $this->agent_prefix . 'initialized the lattice.',
            "INFORMATION"
        );

        //        $this->lattice_size = $n;
        $n = $this->lattice_size;
        //$this->lattice_size = $n;

        $this->lattice = [];

        $value = ["name" => null, "state" => null, "value" => 0];

        foreach (range(-$n, $n) as $q) {
            foreach (range(-$n, $n) as $r) {
                foreach (range(-$n, $n) as $s) {
                    //foreach($point_list as $point) {
                    //    list($q,$r,$s) = $point;
                    $this->lattice[$q][$r][$s] = $value;
                    //array($q=>array($r=>array($s=>$value)));
                }
            }
        }

        //$this->lattice[-1][0][0] = array("name"=>"seed", "state"=>"on", "value"=>.5);
        $this->lattice[0][0][0] = [
            "name" => "seed",
            "state" => "on",
            "value" => 0.5,
        ];
        //$this->lattice[1][-2][1] = array("name"=>"seed", "state"=>"on", "value"=>.5);
    }

    /**
     *
     * @param unknown $q
     * @param unknown $r
     * @param unknown $s
     * @return unknown
     */
    public function getCell($q, $r, $s)
    {
        // $cell = true;

        if (
            $q > $this->lattice_size or
            $q < -$this->lattice_size or
            $r > $this->lattice_size or
            $r < -$this->lattice_size or
            $s > $this->lattice_size or
            $s < -$this->lattice_size
        ) {
            $cell = ['name' => 'boundary', 'state' => 'off', 'value' => 0]; // red?
        } else {
            if (isset($this->lattice[$q][$r][$s])) {
                $cell = $this->lattice[$q][$r][$s];
            } else {
                // Flag an error;
                $cell = ['name' => "bork", 'state' => 'off', 'value' => true];
            }
        }

        return $cell;
    }

    /**
     *
     * @param unknown $q
     * @param unknown $r
     * @param unknown $s
     */
    public function updateCell($q, $r, $s)
    {
        // Process the cell;
        // Because CA is 3D spreadsheets.
        //$q_array= array(-1,1);
        //$r_array= array(-1,1);
        //$s_array= array(-1,1);

        //$cell_value = 0;

        // Build a list of the state of the surrounding cells.

        $cell = $this->getCell($q, $r, $s);

        $states = [];
        $i = 0;
        foreach (range(-1, 1, 2) as $q_offset) {
            foreach (range(-1, 1, 2) as $r_offset) {
                foreach (range(-1, 1, 2) as $s_offset) {
                    $neighbour_cell = $this->getCell(
                        $q + $q_offset,
                        $r + $r_offset,
                        $s + $s_offset
                    );

                    if ($neighbour_cell['state'] == 'on') {
                        $states[$i] = 1;
                    } else {
                        $states[$i] = 0;
                    }
                    $i += 1;
                }
            }
        }

        // Perform some calculation here on $states,
        // to determine what state the current cell should be in.
        list($n, $p_melt, $p_freeze) = $this->getProb($states);

        $cell['neighbours'] =
            $states[0] .
            ' ' .
            $states[1] .
            ' ' .
            $states[2] .
            ' ' .
            $states[3] .
            $states[4] .
            ' ' .
            $states[5];

        $cell['p_melt'] = $p_melt;
        $cell['p_frozen'] = $p_freeze;

        if ($p_melt < $p_freeze) {
            $cell['state'] = 'on';
        } else {
            $cell['state'] = 'off';
        }

        //if (rand(0,10)/10 > .3) {
        //    $cell['state'] ='off';
        //} else {
        //    $cell['state'] = 'on';
        //}

        // Then set lattice value
        $this->lattice[$q][$r][$s] = $cell;
    }

    /**
     *
     * @return unknown
     */
    public function decimalSnowflake()
    {
        $s = "";
        foreach ($this->snowflake_points as $point) {
            $s .= $point;
        }
        $this->decimal_snowflake = bindec($s);
        return $this->decimal_snowflake;
    }

    /**
     *
     * @param unknown $dec
     * @return unknown
     */
    public function binarySnowflake($dec)
    {
        if ($dec == null) {
            $dec = $this->decimal_snowflake;
        }

        $Input = $dec;
        $Output = '';
        if (preg_match("/^\d+$/", $Input)) {
            while ($Input != '0') {
                $s = strval($Input);

                $Output .= chr(48 + ($s[strlen($s) - 1] % 2));
                $Input = bcdiv($Input, '2');
            }
            $Output = strrev($Output);
        }

        $this->binary_snowflake = $Output;
        //      $this->binary_snowflake= decbin($dec);
        return $this->binary_snowflake;
    }

    /**
     *
     */
    public function initSegment()
    {
        $this->thing->log(
            $this->agent_prefix . 'initialized the segment.',
            "INFORMATION"
        );

        $this->point_list = [];

        foreach (range(0, $this->max) as $a) {
            foreach (range(0, $a - 3) as $b) {
                if (!($a - $b > $a)) {
                    //echo $a-$b . " " .-$a . " " .$b . "---" . ( ($a-$b) > $a) . "<br>";
                    $this->point_list[] = [$a - $b, -$a, $b];
                }
            }
        }
    }

    /**
     *
     */
    public function updateSnowflake()
    {
        $this->thing->log(
            $this->agent_prefix . 'updated the snowflake.',
            "INFORMATION"
        );

        foreach ($this->point_list as $point) {
            list($q, $r, $s) = $point;
            $this->updateCell($q, $r, $s);
        }
    }

    /**
     *
     * @param unknown $q     (optional)
     * @param unknown $r     (optional)
     * @param unknown $s     (optional)
     * @param unknown $size  (optional)
     * @param unknown $index (optional)
     */
    public function drawSnowflake(
        $q = null,
        $r = null,
        $s = null,
        $size = null,
        $index = 0
    ) {
        $this->split_time = $this->thing->elapsed_runtime();

        $index += 1; // Track for recursion
        if ($index >= 2) {
            return;
        }

        if ($q == null) {
            $q = 0;
        }
        if ($r == null) {
            $r = 0;
        }
        if ($s == null) {
            $s = 0;
        }

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

        $this->snowflake_points = [];
        foreach ($this->point_list as $point) {
            list($q, $r, $s) = $point;

            // Gives any cell value
            $cell = $this->lattice[$q][$r][$s];

            $color = $this->black;
            if ($cell['state'] == 'on') {
                $color = $this->grey;

                $this->selector_hex = substr($this->uuid, 0, 1); // A random number from 0 to 9.
                $this->selector_dec = $this->hextodec($this->selector_hex);

                //            $this->angle = ($this->selector_dec % 6) * 30;

                switch (true) {
                    case isset($this->event) and
                        $this->event != false and
                        $this->event->event_name == "vancouver pride 2018":
                        $this->angle = ($this->selector_dec % 6) * 60;
                        $distance =
                            (1 / 4) *
                            sqrt(pow($q, 2) + pow($r, 2) + pow($s, 2));
                        $key = $distance % 5;
                        $color = $this->color_palette[$key];
                        break;
                    case isset($this->flag->state):
                        $color = $this->red;
                        break;
                    default:
                        $color = $this->grey;
                }

                $this->snowflake_points[] = 1;
            } else {
                $this->snowflake_points[] = 0;
            }

            //if ($cell['name'] == 'boundary') {$color = $this->black;}

            if ($index == 2) {
                $color = $this->white;
            }

            // Draw out the state
            $center_x = $canvas_size_x / 2;
            $center_y = $canvas_size_y / 2;

            // devstack rotation not yet implemented
            if (!isset($this->angle)) {
                $this->angle = 0;
            }
            $angle = ($this->angle / 180) * 3.14159;
            $angle = 0; // override devstack

            $cell = $this->lattice[$q][$r][$s];

            $n = 0;
            if (isset($cell['p_melt'])) {
                $n = $cell['p_melt'];
            }
            if (isset($cell['p_frozen'])) {
                $n = $cell['p_frozen'];
            }
            //$n = $cell['neighbours'];
            //$quantity = intval($n * 10 - 5);
            //$quantity = random_int(0,4);
            //$quantity = intval($n*5);
            $quantity = 2;

            // Draw an individual hexagon (q,r,s) centred at at an angle and distance from (x,y)
            $this->drawHexagon(
                $q,
                $r,
                $s,
                $center_x,
                $center_y,
                $angle,
                $size,
                $color,
                $quantity
            );
            $this->drawHexagon(
                -1 * $r,
                -1 * $s,
                -1 * $q,
                $center_x,
                $center_y,
                $angle,
                $size,
                $color,
                $quantity
            );

            $this->drawHexagon(
                $r,
                $q,
                $s,
                $center_x,
                $center_y,
                $angle,
                $size,
                $color,
                $quantity
            );
            $this->drawHexagon(
                -1 * $s,
                -1 * $r,
                -1 * $q,
                $center_x,
                $center_y,
                $angle,
                $size,
                $color,
                $quantity
            );

            $this->drawHexagon(
                $q,
                $s,
                $r,
                $center_x,
                $center_y,
                $angle,
                $size,
                $color,
                $quantity
            );
            $this->drawHexagon(
                -$q,
                -$r,
                -$s,
                $center_x,
                $center_y,
                $angle,
                $size,
                $color,
                $quantity
            );

            $this->drawHexagon(
                -1 * $s,
                -1 * $q,
                -1 * $r,
                $center_x,
                $center_y,
                $angle,
                $size,
                $color,
                $quantity
            );
            $this->drawHexagon(
                -1 * $q,
                -1 * $s,
                -1 * $r,
                $center_x,
                $center_y,
                $angle,
                $size,
                $color,
                $quantity
            );

            $this->drawHexagon(
                $s,
                $r,
                $q,
                $center_x,
                $center_y,
                $angle,
                $size,
                $color,
                $quantity
            );
            $this->drawHexagon(
                $r,
                $s,
                $q,
                $center_x,
                $center_y,
                $angle,
                $size,
                $color,
                $quantity
            );

            $this->drawHexagon(
                -$r,
                -$q,
                -$s,
                $center_x,
                $center_y,
                $angle,
                $size,
                $color,
                $quantity
            );
            $this->drawHexagon(
                $s,
                $q,
                $r,
                $center_x,
                $center_y,
                $angle,
                $size,
                $color,
                $quantity
            );

            // devstack
            //                   $this->drawSnowflake($q,$r,$s,$size/10,$index);
            //                    // Which eventually becomes recursively $this->drawSnowflake(...)
            //                  // but not there yet.
        }

        $this->decimalSnowflake();

        $this->thing->log(
            $this->agent_prefix .
                'drew a snowflake in ' .
                number_format(
                    $this->thing->elapsed_runtime() - $this->split_time
                ) .
                'ms.',
            "OPTIMIZE"
        );
        $this->thing->log('drew an snowflake.', "INFORMATION");
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "snowflake",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["snowflake", "refreshed_at"],
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
        $this->whatisSnowflake($this->input);
        try {
            // initiate FPDI
            $pdf = new Fpdi\Fpdi();

            $pdf->SetFont('Helvetica', '', 10);

            $file = $this->resource_path . 'snowflake/bubble.pdf';
            if (file_exists($file)) {
                $pdf->setSourceFile($file);

                //            $pdf->SetFont('Helvetica', '', 10);

                $tplidx1 = $pdf->importPage(1, '/MediaBox');
                $s = $pdf->getTemplatesize($tplidx1);

                $pdf->addPage($s['orientation'], $s);
                $pdf->useTemplate($tplidx1);
            } else {
                $pdf->addPage();
            }
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
            //            $pdf->Image($this->nuuid_png, 5, 18, 20, 20, 'PNG');

            //            $pdf->SetFont('Helvetica', '', 10);
            //            $pdf->Image($this->PNG_embed, 5, 5, 20, 20, 'PNG');

            switch (true) {
                case isset($this->hextile_PNG):
                    $pdf->Image($this->nuuid_png, 20, 13, 20, 20, 'PNG');

                    break;
                default:
                    $pdf->Image($this->nuuid_png, 5, 18, 20, 20, 'PNG');
            }

            $pdf->SetFont('Helvetica', '', 10);
            $pdf->Image($this->PNG_embed, 5, 5, 20, 20, 'PNG');

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(1, 1);

            $tokens = explode(" ", $this->whatis);

            $pdf->SetFont('Helvetica', '', 26);
            $this->txt = "" . $this->whatis . ""; // Pure uuid.


            $i = 0;
            $dev = false;
            if ($dev == true) {
                //$i = 0;
                $j = 0;
                $word_agent = new Word($this->thing, "word");
                foreach ($tokens as $token) {
                    $png_image = $word_agent->imageWord($token);
                    $pdf->Image($png_image, 5 + $i, 5 + $j, 20, 20, 'PNG');
                    $i += 10;
                    $j += 10;
                }
            }

            $pdf->SetXY(140 + $i * 10, 7);
            $text = $this->whatis;
            //$text = $token;
            $line_height = 20;
            $pdf->MultiCell(150, $line_height, $text, 0);
            //$i += 1;
            //}

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
            if (file_exists($file)) {
                $tplidx2 = $pdf->importPage(2);

                $pdf->addPage($s['orientation'], $s);

                $pdf->useTemplate($tplidx2, 0, 0);
                // Generate some content for page 2
            } else {
                $pdf->addPage();
            }

            $pdf->SetFont('Helvetica', '', 10);
            $this->txt = "" . $this->uuid . ""; // Pure uuid.

            $link = $this->web_prefix . 'thing/' . $this->uuid . '/snowflake';

            $this->getQuickresponse($link);
            $pdf->Image($this->quick_response_png, 175, 5, 30, 30, 'PNG');

            //$pdf->Link(175,5,30,30, $link);

            $pdf->SetTextColor(0, 0, 0);

            $pdf->SetXY(15, 7);

            $line_height = 4;

            $t = $this->thing_report['sms'];

            $t = str_replace(" | ", "\n", $t);

            $pdf->MultiCell(150, $line_height, $t, 0);

            //$pdf->Link(15,7,150,10, $link);

            $y = $pdf->GetY() + 0.95;
            $pdf->SetXY(15, $y);
            $text = "v0.0.4";
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
            $text = $this->timestampSnowflake();
            $pdf->SetXY(175, 35);
            $pdf->MultiCell(30, $line_height, $text, 0, "L");

            //$pdfTitle = 'snowflake_'.$this->thing->nuuid.'.pdf';
            //$image = $pdf->Output('S', $pdfTitle);
            //          $pdf->SetXY(50, 50);

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
        //        $input = strtolower($this->subject);
        $input = strtolower($this->input);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'snowflake') {
                $this->getSnowflake();

                if (
                    !isset($this->decimal_snowflake) or
                    $this->decimal_snowflake == null
                ) {
                    $this->decimal_snowflake = rand(1, rand(1, 10) * 1e11);
                }

                $this->binarySnowflake($this->decimal_snowflake);
                $p = strlen($this->binary_snowflake);

                $this->max = 13;
                $this->size = 4;
                $this->lattice_size = 40;
                $this->response .= "Made a snowflake. Which will melt. ";
                return;
            }
        }

        $keywords = ["uuid", "iterate", "pride", "flag", "hex"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'pride':
                            $this->node_list = [
                                "snowflake" => ["pride", "snowflake", "uuid"],
                            ];

                            // Mock-up multi-event
                            $this->events = [];

                            $event = new Event(
                                $this->thing,
                                "vancouver pride 2018"
                            );
                            $this->events[] = $event;

                            $this->flag = new Flag($this->thing, "flag");

                            $this->getSnowflake();

                            if (
                                !isset($this->decimal_snowflake) or
                                $this->decimal_snowflake == null
                            ) {
                                $this->decimal_snowflake = rand(
                                    1,
                                    rand(1, 10) * 1e11
                                );
                            }

                            $this->binarySnowflake($this->decimal_snowflake);
                            $p = strlen($this->binary_snowflake);

                            $this->max = 13;
                            $this->size = 6.5;
                            $this->lattice_size = 40;

                            $this->canvas_size_x = 164 * 1.5;
                            $this->canvas_size_y = 164 * 1.5;

                            $this->response .=
                                "Made a vancouver pride 2018 snowflake.  It is going to melt.";
                            return;

                        case 'flag':
                            $this->node_list = [
                                "snowflake" => ["flag", "snowflake", "uuid"],
                            ];

                            $this->getSnowflake();

                            $this->flag = new Flag($this->thing, "flag");

                            if (
                                !isset($this->decimal_snowflake) or
                                $this->decimal_snowflake == null
                            ) {
                                $this->decimal_snowflake = rand(
                                    1,
                                    rand(1, 10) * 1e11
                                );
                            }

                            $this->binarySnowflake($this->decimal_snowflake);
                            $p = strlen($this->binary_snowflake);

                            $this->max = 13;
                            $this->size = 4;
                            $this->lattice_size = 40;

                            $this->canvas_size_x = 164 * 1;
                            $this->canvas_size_y = 164 * 1;

                            $this->response .=
                                "Made a " . $this->flag->state . " snowflake. ";
                            return;

                        case 'uuid':
                            $this->max = sqrt(128) + 6;
                            //$this->max = 24;

                            $this->size = 2.5;
                            $this->lattice_size = 40;

                            $uuid_agent = new Uuid($this->thing, "uuid");
                            $this->decimal_snowflake = $uuid_agent->decimalUuid(
                                $this->uuid
                            );

                            //                            $this->decimalUuid();
                            $this->response .=
                                "Saw request for a UUID snowflake. ";
                            return;

                        case 'hex':
                            $this->latticeSnowflake();
                            $this->response .= "Saw request for hexes. ";
                            break;

                        case 'iterate':
                            $this->thing->log(
                                $this->agent_prefix .
                                    'received a command to update the snowflake.',
                                "INFORMATION"
                            );
                            $this->updateSnowflake();
                            $this->response .=
                                "Saw request to iterate the snowflake. ";
                            return;

                        case 'on':
                            //$this->setFlag('green');
                            //break;
                            $this->response .= "Heard on. ";

                        default:
                    }
                }
            }
        }

        $this->getSnowflake();

        if (
            !isset($this->decimal_snowflake) or
            $this->decimal_snowflake == null
        ) {
            $this->decimal_snowflake = rand(1, rand(1, 10) * 1e11);
        }

        $this->binarySnowflake($this->decimal_snowflake);
        $p = strlen($this->binary_snowflake);

        $this->max = 13;
        $this->size = 4;
        $this->lattice_size = 40;

        $this->response .= "Made a binary snowflake. ";
        return;

        if (strpos($input, 'uuid') !== false) {
            //    $this->uuidSnowflake();
        }

        if ($this->agent_input == "snowflake iterate") {
            $this->thing->log(
                $this->agent_prefix .
                    'received a command to update the snowflake.',
                "INFORMATION"
            );
            $this->updateSnowflake();
            return;
        }
    }
}
