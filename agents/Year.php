<?php
/**
 * Year.php
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

class Year extends Agent
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
            "A YEAR is a repeating pattern of seasons.";
        $this->thing_report["help"] = 'Click on the image for a PDF.';

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $command_line = null;

        $this->node_list = ["year" => ["year", "uuid"]];

        $this->current_time = $this->thing->json->time();

        // Get some stuff from the stack which will be helpful.
        $this->entity_name = $this->thing->container['stack']['entity_name'];

        $this->default_canvas_size_x = 2000;
        $this->default_canvas_size_y = 2000;

        $agent = new Retention($this->thing, "retention");
        $this->retain_to = $agent->retain_to;

        $agent = new Persistence($this->thing, "persistence");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->initYear();

        $this->draw_center = false;
        $this->draw_outline = false; //Draw hexagon line

        $this->canvas_size_x = $this->default_canvas_size_x;
        $this->canvas_size_y = $this->default_canvas_size_y;

        $this->size = min(
            $this->default_canvas_size_x,
            $this->default_canvas_size_y
        );
    }

    public function set()
    {
        $this->setYear();
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
        $whatis = "year";
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
     * @param unknown $t (optional)
     * @return unknown
     */
    public function timestampYear($t = null)
    {
        $s = $this->thing->thing->created_at;

        if (!isset($this->retain_to)) {
            $text = "X";
        } else {
            $t = $this->retain_to;
            $text = "GOOD UNTIL " . strtoupper(date('Y M d D H:i', $t));
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
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        return $this->thing_report;
    }

    /**
     *
     */
    public function makeChoices()
    {
/*
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "year"
        );

        $this->choices = $this->thing->choice->makeLinks('year');
        $this->thing->log(
            $this->agent_prefix .
                'completed makeLinks. Timestamp = ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.',
            "OPTIMIZE"
        );
*/
        $this->choices = false;
        $this->thing_report['choices'] = $this->choices;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $cell = $this->lattice[0][0][0];
        $sms = "YEAR | ";

        foreach ($this->years as $i => $year) {
            $year_text .= $year['year'] . " " . $year['era'] . " ";
        }
        $sms .= $year_text;
        $sms .= $this->web_prefix . "thing/" . $this->uuid . "/year";
        $sms .= " | " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Stackr made a year for you.<br>";

        $uuid = $this->uuid;

        $message .=
            "Keep on stacking.\n\n<p>" .
            $this->web_prefix .
            "thing/$uuid/year.png\n \n\n<br> ";
        $message .=
            '<img src="' .
            $this->web_prefix .
            'thing/' .
            $uuid .
            '/year.png" alt="year" height="92" width="92">';

        $this->thing_report['message'] = $message;
    }

    public function deprecate_latticeYear()
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

        $lattice_agent->makePNG();

        $this->hextile_PNG = $lattice_agent->PNG_embed;
    }

    /**
     *
     */
    public function setYear()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["year", "decimal"],
            $this->decimal_year
        );

        $this->thing->log(
            $this->agent_prefix .
                ' saved decimal year ' .
                $this->decimal_year .
                '.',
            "INFORMATION"
        );
    }

    /**
     *
     * @return unknown
     */
    public function getYear()
    {
        $this->thing->json->setField("variables");
        $this->decimal_year = $this->thing->json->readVariable([
            "year",
            "decimal",
        ]);

        if ($this->decimal_year == false) {
            $this->thing->log(
                $this->agent_prefix . ' did not find a decimal year.',
                "INFORMATION"
            );
            // No year saved.  Return.
            return true;
        }

        $this->thing->log(
            'loaded decimal year ' . $this->decimal_year . '.',
            "INFORMATION"
        );
    }

    /**
     *
     */
    public function initYear()
    {
        $this->number_agent = new Number($this->thing, "number");
/*
        if (!isset($this->max)) {
            $this->max = 12;
        }
        if (!isset($this->size)) {
            $this->size = 3.7;
        }
        if (!isset($this->lattice_size)) {
            $this->lattice_size = 15;
        }
*/
        //$this->initLattice($this->max);
        //$this->initSegment();

        //$this->setProbability();
        //$this->setRules();
    }

    public function run()
    {
/*
        $this->binaryYear($this->decimal_year);

        $this->year_points = [];
        $index = 0;

        foreach ($this->point_list as $point) {
            list($q, $r, $s) = $point;
            $value = rand(0, 1);

            if ($this->binary_year[$index] == 1) {
                $value = ["name" => null, "state" => 'on', "value" => 1];
            } else {
                $value = ["name" => null, "state" => 'off', "value" => 0];
            }

            $this->lattice[$q][$r][$s] = $value;
            $this->year_points[] = $this->binary_year[$index];
            $index += 1;
            if ($index >= strlen($this->binary_year)) {
                break;
            }
        }
*/
    }

    /**
     *
     */
    public function deprecate_decimalUuid()
    {
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

        $this->decimal_year = $dec;

        return;
    }

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
            ' loaded decimal year ' . $this->decimal_year . '.',
            "INFORMATION"
        );
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/year.pdf';
        $this->node_list = ["year" => ["year"]];
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
        $txt = 'This is a YEAR';
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

        $this->drawYear();

        // Write the string at the top left
        $border = 30;
        $r = 1.165;

        $radius = ($r * ($canvas_size_x - 2 * $border)) / 3;

        // devstack add path
        $font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';
        $text = "A year in slices...";
        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

        $size = $canvas_size_x - 90;
        $size = 20;
        $angle = 0;

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
            '"alt="year"/>';

        $this->html_image =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="year"/>';

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
    public function deprecate_initLattice()
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
    public function decimalYear()
    {
        $s = "";
        foreach ($this->year_points as $point) {
            $s .= $point;
        }
        $this->decimal_year = bindec($s);
        return $this->decimal_year;
    }

    /**
     *
     * @param unknown $dec
     * @return unknown
     */
    public function binaryYear($dec)
    {
        if ($dec == null) {
            $dec = $this->decimal_year;
        }

        $Input = $dec;
        $Output = '';
        if (preg_match("/^\d+$/", $Input)) {
            while ($Input != '0') {
                $Output .= chr(48 + ($Input[strlen($Input) - 1] % 2));
                $Input = bcdiv($Input, '2');
            }
            $Output = strrev($Output);
        }

        $this->binary_year = $Output;
        return $this->binary_year;
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
    public function updateYear()
    {
        $this->thing->log(
            $this->agent_prefix . 'updated the year.',
            "INFORMATION"
        );

        foreach ($this->point_list as $point) {
            list($q, $r, $s) = $point;
            $this->updateCell($q, $r, $s);
        }
    }

    public function drawYear($type = null)
    {
        if ($type == null) {
            $type = $this->type;
        }
        if ($type == "wedge") {
            $this->wedgeYear();
            return;
        }

        $this->sliceYear();
    }

    public function sliceYear()
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

        $width_slice = ($canvas_size_x - 2 * $border) / 12;

        // Draw out the state
        $center_x = $canvas_size_x / 2;
        $center_y = $canvas_size_y / 2;

        // devstack rotation not yet implemented
        if (!isset($this->angle)) {
            $this->angle = 0;
        }

        foreach (range(0, 12, 1) as $i) {
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

    public function wedgeYear()
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
        $angle = (2 * 3.14159) / 12;
        //$x_pt =  230;
        //$y_pt = 230;

        foreach (range(0, 11, 1) as $i) {
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
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "year",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["year", "refreshed_at"],
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
        $this->getWhatis($this->subject);
        try {
            // initiate FPDI
            $pdf = new Fpdi\Fpdi();

            $pdf->setSourceFile($this->resource_path . 'snowflake/bubble.pdf');
            $pdf->SetFont('Helvetica', '', 10);

            $tplidx1 = $pdf->importPage(1, '/MediaBox');
            $s = $pdf->getTemplatesize($tplidx1);

            $pdf->addPage($s['orientation'], $s);
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

            $link = $this->web_prefix . 'thing/' . $this->uuid . '/year';

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
            $text = $this->timestampYear();
            $pdf->SetXY(175, 35);
            $pdf->MultiCell(30, $line_height, $text, 0, "L");

            $image = $pdf->Output('', 'S');
            $this->thing_report['pdf'] = $image;
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        return $this->thing_report['pdf'];
    }

    public function isYear($text)
    {
        // 1, 2, three or 4 number string.
        // Possibly followed by a string CE, BC, AD, Common Era etc

        // Apologies to the future.
        // You'll need to fix this.

        $this->parsed_date = date_parse($text);
        $year = $this->parsed_date['year'];

        if ($year !== false) {
            return $year;
        }

        // Any number less than 9999 could be a year.
        if (is_numeric($text) and $text < 9999) {
            return $text;
        }

        // https://blog.esllibrary.com/2015/11/05/abbreviations-bc-ad-bce-ce/
        // BC, BCE, CE come after the year
        // AD comes before the year (but recognize common practice)
        $year_indicators = [
            'bc',
            'b.c.',
            'ad',
            'a.d.',
            'ce',
            'c.e.',
            'anno domini',
            'bce',
            'b.c.e',
            'julian',
            'carbon',
            'year of our lord',
        ];

        //$number_agent = new Number($this->thing, "number");

        foreach ($year_indicators as $year_indicator) {
            if (stripos($text, $year_indicator) !== false) {
                $number = $this->number_agent->extractNumber($text);
                return $number;
            }
        }

        return false;
    }

    public function eraYear($text)
    {
        $eras = [
            'bce' => ['bce', 'b.c.e'],
            'bc' => ['bc', 'b.c.'],
            'ad' => ['a.d.', 'anno domini', 'year of our lord'],
            'ce' => ['ce', 'c.e.'],
            'julian' => null,
            'carbon' => null,
        ];

        $matched_eras = [];
        foreach ($eras as $era => $error_indicators) {
            if (stripos($text, $era) !== false) {
                $matched_eras[] = $era;
                continue;
                //return $era;
            }

            foreach ($era_indicators as $era_indicator) {
                if (stripos($text, $era_indicator) !== false) {
                    $matched_eras[] = $era;
                    continue;
                    //return $era;
                }
            }
        }

        // Filter out era which are part of another era.
        // For now textually.
        $era_list = $matched_eras;
        foreach ($matched_eras as $i => $era1) {
            foreach ($matched_eras as $j => $era2) {
                if ($era1 == $era2) {
                    continue;
                }

                if (stripos($era1, $era2) !== false) {
                    unset($era_list[$j]);
                }
            }
        }
        $matched_eras = $era_list;

        if (count($matched_eras) == 1) {
            return $matched_eras[0];
        }

        return false;
    }

    public function extractYears($text = null)
    {
        $years = [];

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
            if ($token == "") {continue;}
            $response = $this->isYear($token);
            if ($response === false) {continue;}

            if (is_numeric($response)) {
                // Check if a day has been mis-categorized as a year.
                $this->parsed_date = date_parse($text);
                $day = $this->parsed_date['day'];
                if ($response == $day) {
                    continue;
                }

                $era = $this->eraYear($text);
                $year = ['year' => $response, "era" => $era];
                $years[] = $year;
            }
        }
        // Remove duplicates.
        // https://stackoverflow.com/questions/307674/how-to-remove-duplicate-values-from-a-multi-dimensional-array-in-php
        $serialized = array_map('serialize', $years);
        $unique = array_unique($serialized);
        $years = array_intersect_key($years, $unique);
        return $years;
    }

    public function extractYear($text = null) {
        if ($text == null) {
            return true;
        }
        $year = false;

        if (!isset($this->years)) {$years = $this->extractYears($text);}
        if (count($years) == 1) {$year = $years[0];}
        return $year;

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

        if ($this->agent_input == "year") {
            return;
        }


        $this->years = $this->extractYears($input);
        $year = $this->extractYear($input);
        if ($year != false) {$this->year = $year['year'];$this->era = $year['era'];}

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'year') {
                $this->getYear();

                if (
                    !isset($this->decimal_year) or
                    $this->decimal_year == null
                ) {
                    $this->decimal_year = rand(1, rand(1, 10) * 1e11);
                }

                $this->binaryYear($this->decimal_year);
                $p = strlen($this->binary_year);

                $this->max = 13;
                $this->size = 4;
                $this->lattice_size = 40;
                $this->response .= "Made a year. ";
                return;
            }
        }

        $input_agent = new Input($this->thing, "input");
        $discriminators = ['wedge', 'slice'];
        $input_agent->aliases['wedge'] = ['pizza', 'wheel', 'wedge'];
        $input_agent->aliases['slice'] = ['slice', 'column', 'columns'];
        $type = $input_agent->discriminateInput($input, $discriminators);
        if ($type != false) {
            $this->type = $type;
        }

        $keywords = ["uuid", "iterate", "pride", "flag", "hex"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'pride':
                            $this->node_list = [
                                "year" => ["pride", "year"],
                            ];

                            // Mock-up multi-event
                            $this->events = [];

                            $event = new Event(
                                $this->thing,
                                "vancouver pride 2018"
                            );
                            $this->events[] = $event;

                            $this->flag = new Flag($this->thing, "flag");

                            $this->getYear();

                            if (
                                !isset($this->decimal_year) or
                                $this->decimal_year == null
                            ) {
                                $this->decimal_year = rand(
                                    1,
                                    rand(1, 10) * 1e11
                                );
                            }

                            $this->binaryYear($this->decimal_year);
                            $p = strlen($this->binary_year);

                            $this->max = 13;
                            $this->size = 6.5;
                            $this->lattice_size = 40;

                            $this->canvas_size_x = 164 * 1.5;
                            $this->canvas_size_y = 164 * 1.5;

                            $this->response .= "Made a vancouver pride 2018.";
                            return;

                        case 'flag':
                            $this->node_list = [
                                "year" => ["flag", "year"],
                            ];

                            $this->getYear();

                            $this->flag = new Flag($this->thing, "flag");

                            if (
                                !isset($this->decimal_year) or
                                $this->decimal_year == null
                            ) {
                                $this->decimal_year = rand(
                                    1,
                                    rand(1, 10) * 1e11
                                );
                            }

                            $this->binaryYear($this->decimal_year);
                            $p = strlen($this->binary_year);

                            $this->max = 13;
                            $this->size = 4;
                            $this->lattice_size = 40;

                            $this->canvas_size_x = 164 * 1;
                            $this->canvas_size_y = 164 * 1;

                            $this->response .=
                                "Made a " . $this->flag->state . " year. ";
                            return;

                        case 'uuid':
                            $this->max = sqrt(128) + 6;
                            //$this->max = 24;

                            $this->size = 2.5;
                            $this->lattice_size = 40;
                            $this->decimalUuid();
                            $this->response .= "Saw request for a UUID year. ";
                            return;

                        case 'hex':
                            $this->latticeYear();
                            $this->response .= "Saw request for hexes. ";
                            break;

                        case 'iterate':
                            $this->thing->log(
                                $this->agent_prefix .
                                    'received a command to update the year.',
                                "INFORMATION"
                            );
                            $this->updateYear();
                            $this->response .=
                                "Saw request to iterate the year. ";
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

        $this->getYear();

        if (!isset($this->decimal_year) or $this->decimal_year == null) {
            $this->decimal_year = rand(1, rand(1, 10) * 1e11);
        }

        $this->binaryYear($this->decimal_year);
        $p = strlen($this->binary_year);

        $this->max = 13;
        $this->size = 4;
        $this->lattice_size = 40;

        $this->response .= "Made a binary year. ";
        return;

        if (strpos($input, 'uuid') !== false) {
            //    $this->uuidYear();
        }

        if ($this->agent_input == "year iterate") {
            $this->thing->log(
                'received a command to update the year.',
                "INFORMATION"
            );
            $this->updateYear();
            return;
        }
    }
}
