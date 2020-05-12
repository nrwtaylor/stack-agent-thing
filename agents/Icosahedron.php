<?php
/**
 * Roll.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Icosahedron extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->width = 125;
        $this->height = $this->width;

        //test
        //        $this->drawD20(2);

        $this->node_list = ["roll" => ["roll", "card"]];
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->thing_report["info"] = "This rolls a dice.  See
                                https:\\codegolf.stackexchange.com/questions/25416/roll-dungeons-and-dragons-dice";
        $this->thing_report['help'] =
            'This is about dice with more than 6 sides.  Try "Roll d20". Or "Roll 3d20+17. Or "Card"';

        /*
        $this->image = imagecreatetruecolor($this->width, $this->height);

        $white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $red = imagecolorallocate($this->image, 255, 0, 0);
        $green = imagecolorallocate($this->image, 0, 255, 0);
        $grey = imagecolorallocate($this->image, 128, 128, 128);


    $camx = 20;
    $camy = 20;
    $camz = 1;

    $yaw = 10;
    $pitch = 20;

$t = new Stl($this->thing, "stl");
foreach($t->triangles as $i=>$triangle) {

foreach($triangle as $j=>$point) {

$x = $point[0] - $camx; $y = $point[1] - $camy; $z = $point[2] - $camz;

$sy = sin(-$yaw); $cy = cos(-$yaw); $sp = sin(-$pitch); $cp = cos(-$pitch);

$rot = $this->RotatePoint($sy,$cy,$x,$y);
$x = $rot[0];
$y = $rot[1];

$rot = $this->RotatePoint($sp,$cp,$z,$y);
$z = $rot[0];
$y = $rot[1];

echo $x ." " . $y ." " . $z ."\n";

imageline($this->image, $x, $y, $x, $y, $this->black);


}

}

exit();
*/
    }

    public function RotatePoint($sin, $cos, $x, $y)
    {
        return [$x * $cos - $y * $sin, $y * $cos + $x * $sin];
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        //        $choices = false;

        $this->thing_report["info"] = "This rolls a dice.  See
				https:\\codegolf.stackexchange.com/questions/25416/roll-dungeons-and-dragons-dice";
        if (!isset($this->thing_report['help'])) {
            $this->thing_report["help"] =
                'This is about dice with more than 6 sides.  Try "Roll d20". Or "Roll 3d20+17. Or "Card"';
        }

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        return $this->thing_report;
    }

    /*
     *
     * @param unknown $number (optional)
     * @param unknown $die    (optional)
     * @return unknown
     */
    public function makeImage($number = null, $die = null)
    {
        $number = 5;
        $this->width = 800;
        $this->height = 800;
        $this->image = imagecreatetruecolor($this->width, $this->height);

        $white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $red = imagecolorallocate($this->image, 255, 0, 0);
        $green = imagecolorallocate($this->image, 0, 255, 0);
        $grey = imagecolorallocate($this->image, 128, 128, 128);

        imagefilledrectangle(
            $this->image,
            0,
            0,
            $this->width,
            $this->height,
            $white
        );

        $camx = 0;
        $camy = 0;
        $camz = 100;

        $yaw = deg2rad(72);
        $pitch = deg2rad(36);

        //$yaw = atan2($camz, $camx);
        //$pitch = atan2($camy, pow((pow($camx,2) + pow($camz,2)),0.5) );

        echo "yaw " . $yaw . " " . "pitch " . $pitch . "<br>";

        $x_camera_offset = $this->width / 2;
        $y_camera_offset = $this->height / 2;
        //$z_offset = $this->height/2;
        $scale = 500;

        $t = new Stl($this->thing, "stl");
        foreach ($t->triangles as $i => $triangle) {
            $normal = $triangle['normal'];
            $normal[2] = $normal[2] * -1;

            echo "face normal " .
                $normal[0] .
                " - " .
                $normal[1] .
                " - " .
                $normal[2] .
                " ";

            $x = $normal[0];
            $y = $normal[1];
            $z = $normal[2];

            $sy = sin(-$yaw);
            $cy = cos(-$yaw);
            $sp = sin(-$pitch);
            $cp = cos(-$pitch);

            $rot = $this->RotatePoint($sy, $cy, $x, $y);
            $x = $rot[0];
            $y = $rot[1];

            $rot = $this->RotatePoint($sp, $cp, $z, $y);
            $z = $rot[0];
            $y = $rot[1];

            $dot_product = $camx * $x + $camy * $y + $camz * $z;

            echo "dot product" . $dot_product . "<br>";

            // Is face away from camera?
            if ($dot_product < 0) {
                //continue;
            }

            $x_2d = null;
            $x_old = null;
            $y_2d = null;
            $y_old = null;

            $lengths = [];
            $x_old = null;
            $y_old = null;
            $z_old = null;

            foreach ($triangle['vertices'] as $k => $point) {
                //var_dump($point);
                if ($k == 0) {
                    $x_old = $point[0];
                    $y_old = $point[1];
                    $z_old = $point[2];
                    continue;
                }

                $lengths[$k] = pow(
                    pow($point[0] - $x_old, 2) +
                        pow($point[1] - $y_old, 2) +
                        pow($point[2] - $z_old, 2),
                    0.5
                );

                if ($k == 2) {
                    break;
                }

                $x_old = $point[0];
                $y_old = $point[1];
                $z_old = $point[2];
            }

            $lengths[0] = pow(
                pow($point[0] - $x_old, 2) +
                    pow($point[1] - $y_old, 2) +
                    pow($point[2] - $z_old, 2),
                0.5
            );

            $min_length = 1e99;
            $max_length = 0;
            foreach ($lengths as $n => $length) {
                if ($length < $min_length) {
                    $min_length = $length;
                }
                if ($length > $max_length) {
                    $max_length = $length;
                }

                echo "length " . $length . "<br>";
            }

            // Skip pointy triangles;
            echo "length ratio " . $max_length / $min_length . "<br>";

            $half_perimeter = ($lengths[0] + $lengths[1] + $lengths[2]) / 2;

            $area = pow(
                $half_perimeter *
                    ($half_perimeter - $lengths[0]) *
                    ($half_perimeter - $lengths[1]) *
                    ($half_perimeter - $lengths[2]),
                0.5
            );

            echo "area " . $area . "<br>";

            if ($area < 10) {
                continue;
            }

            if ($max_length / $min_length > 3) {
                continue;
            }

            foreach ($triangle['vertices'] as $j => $point) {
                $point[2] = $point[2] * -1;

                echo "3D " .
                    $point[0] .
                    " " .
                    $point[1] .
                    " " .
                    $point[2] .
                    "<br>";

                /*
$x = $point[0] - $camx; $y = $point[1] - $camy; $z = $point[2] - $camz;
*/

                $x = $point[0];
                $y = $point[1];
                $z = $point[2];

                $sy = sin(-$yaw);
                $cy = cos(-$yaw);
                $sp = sin(-$pitch);
                $cp = cos(-$pitch);

                $rot = $this->RotatePoint($sy, $cy, $x, $y);
                $x = $rot[0];
                $y = $rot[1];

                $rot = $this->RotatePoint($sp, $cp, $z, $y);
                $z = $rot[0];
                $y = $rot[1];

                $distance = 100;

                $x_2d = ($scale * $x) / ($z + $distance);
                $y_2d = ($scale * $y) / ($z + $distance);

                if ($x_old == null) {
                    $x_old = $x_2d;
                }
                if ($y_old == null) {
                    $y_old = $y_2d;
                }

                // Is point behind camera?
                //if ($y <= 0) {continue;}

                //echo $x ." " . $y ." " . $z ."\n";

                echo "2D (" .
                    $x_old .
                    "," .
                    $y_old .
                    ") > (" .
                    $x_2d .
                    "," .
                    $y_2d .
                    ")<br>";

                imageline(
                    $this->image,
                    $x_old + $x_camera_offset,
                    $y_old + $y_camera_offset,
                    $x_2d + $x_camera_offset,
                    $y_2d + $y_camera_offset,
                    $this->black
                );
                $x_old = $x_2d;
                $y_old = $y_2d;
            }
        }

        return $this->image;

        return;
        imagefilledrectangle(
            $this->image,
            0,
            0,
            $this->width,
            $this->height,
            $white
        );

        $textcolor = imagecolorallocate($this->image, 0, 0, 0);

        if (false) {
            // draws triangle lines based on the rules of math
            $size = 100;
            list($pta_x, $pta_y) = [0, 0];
            list($ptb_x, $ptb_y) = [$size / 2, ($size * sqrt(3)) / 2];
            list($ptc_x, $ptc_y) = [$size, 0];

            imageline(
                $this->image,
                $pta_x,
                $pta_y,
                $ptb_x,
                $ptb_y,
                $this->black
            );
            imageline(
                $this->image,
                $ptb_x,
                $ptb_y,
                $ptc_x,
                $ptc_y,
                $this->black
            );
            imageline(
                $this->image,
                $ptc_x,
                $ptc_y,
                $pta_x,
                $pta_y,
                $this->black
            );
        }

        //$font = $GLOBALS['stack'] . 'vendor/nrwtaylor/stack-agent-thing/resources/roll/KeepCalm-Medium.ttf';
        $font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';

        $this->drawIcosahedron();

        $text = $number;

        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

        $size = 72;
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
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
        $pad = 0;
        imagettftext(
            $this->image,
            $size,
            $angle,
            $this->width / 2 - $bb_width / 2,
            $this->height / 2 + $bb_height / 2,
            $grey,
            $font,
            $number
        );

        //var_dump ($width);
        imagestring($this->image, 2, 100, 0, $die, $textcolor);

        return $this->image;
    }

    public function getFaces()
    {
        $faces[0] = [0, 1, 2];
        $faces[1] = [1, 2, 3];
        $faces[2] = [2, 3, 4];
        $faces[3] = [3, 4, 5];
        $faces[4] = [6, 7, 8];
        $faces[5] = [8, 9, 10];
        $faces[6] = [10, 11, 12];
        $faces[7] = [12, 13, 14];
        $faces[8] = [14, 15, 16];
        $faces[9] = [17, 18, 19];
        $faces[10] = [19, 0, 1];
        $faces[11] = [0, 2, 3];
        $faces[12] = [0, 1, 2];
        $faces[13] = [0, 1, 2];
        $faces[14] = [0, 1, 2];
        $faces[15] = [0, 1, 2];
        $faces[16] = [0, 1, 2];
        $faces[17] = [0, 1, 2];
        $faces[18] = [0, 1, 2];
        $faces[19] = [0, 1, 2];

        return $faces;
    }

    /**
     *
     * @param unknown $number
     */
    public function getVertices()
    {
        // devstack

        $r = 1;
        $phi = ((1 + 5) ^ 0.5) / 2;
        $vertice = [0, 1, $phi];

        $vertices[0] = [
            (+1 * $r) / sqrt(3),
            (+1 * $r) / sqrt(3),
            (+1 * $r) / sqrt(3),
        ];
        $vertices[1] = [
            (-1 * $r) / sqrt(3),
            (+1 * $r) / sqrt(3),
            (+1 * $r) / sqrt(3),
        ];
        $vertices[2] = [
            (+1 * $r) / sqrt(3),
            (-1 * $r) / sqrt(3),
            (+1 * $r) / sqrt(3),
        ];
        $vertices[3] = [
            (-1 * $r) / sqrt(3),
            (-1 * $r) / sqrt(3),
            (+1 * $r) / sqrt(3),
        ];
        $vertices[4] = [
            (+1 * $r) / sqrt(3),
            (+1 * $r) / sqrt(3),
            (-1 * $r) / sqrt(3),
        ];
        $vertices[5] = [
            (-1 * $r) / sqrt(3),
            (+1 * $r) / sqrt(3),
            (-1 * $r) / sqrt(3),
        ];
        $vertices[6] = [
            (+1 * $r) / sqrt(3),
            (-1 * $r) / sqrt(3),
            (-1 * $r) / sqrt(3),
        ];
        $vertices[7] = [
            (-1 * $r) / sqrt(3),
            (-1 * $r) / sqrt(3),
            (-1 * $r) / sqrt(3),
        ];

        $vertices[8] = [
            0,
            (+1 * $r) / (sqrt(3) * $phi),
            (+1 * ($r * $phi)) / sqrt(3),
        ];
        $vertices[9] = [
            0,
            (+1 * $r) / (sqrt(3) * $phi),
            (-1 * ($r * $phi)) / sqrt(3),
        ];
        $vertices[10] = [
            0,
            (-1 * $r) / (sqrt(3) * $phi),
            (+1 * ($r * $phi)) / sqrt(3),
        ];
        $vertices[11] = [
            0,
            (-1 * $r) / (sqrt(3) * $phi),
            (-1 * ($r * $phi)) / sqrt(3),
        ];

        $vertices[12] = [
            (+1 * $r) / (sqrt(3) * $phi),
            (+1 * ($r * $phi)) / sqrt(3),
            0,
        ];
        $vertices[13] = [
            (+1 * $r) / (sqrt(3) * $phi),
            (-1 * ($r * $phi)) / sqrt(3),
            0,
        ];
        $vertices[14] = [
            (-1 * $r) / (sqrt(3) * $phi),
            (+1 * ($r * $phi)) / sqrt(3),
            0,
        ];
        $vertices[15] = [
            (-1 * $r) / (sqrt(3) * $phi),
            (-1 * ($r * $phi)) / sqrt(3),
            0,
        ];

        $vertices[16] = [
            (+1 * ($r * $phi)) / sqrt(3),
            0,
            (+1 * $r) / (sqrt(3) * $phi),
        ];
        $vertices[17] = [
            (+1 * ($r * $phi)) / sqrt(3),
            0,
            (-1 * $r) / (sqrt(3) * $phi),
        ];
        $vertices[18] = [
            (-1 * ($r * $phi)) / sqrt(3),
            0,
            (+1 * $r) / (sqrt(3) * $phi),
        ];
        $vertices[19] = [
            (-1 * ($r * $phi)) / sqrt(3),
            0,
            (-1 * $r) / (sqrt(3) * $phi),
        ];

        return $vertices;
    }

    /**
     *
     * @param unknown $image (optional)
     * @return unknown
     */
    public function makePNG($image = null)
    {
        if ($image = null) {
            $image = $this->image;
        }
        if ($image == true) {
            return true;
        }

        //if (!isset($this->image)) {$this->makeImage();}

        $agent = new Png($this->thing, "png");
        $image = $this->makeImage();

        if ($image === true) {
            return true;
        }

        $agent->makePNG($image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;

        //$this->thing_report['png'] = $agent->PNG;
        $this->thing_report['png'] = $agent->image_string;
    }

    /**
     *
     */
    function drawTriangle($face)
    {
        $pta = $face[0];
        $ptb = $face[1];
        $ptc = $face[2];

        $this->imageline($this->image, 20, 20, 280, 280, $this->black);
        imageline($this->image, 20, 20, 20, 280, $this->black);
        imageline($this->image, 20, 280, 280, 280, $this->black);
    }

    public function makeWeb()
    {
        $web = "";
        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    function drawIcosahedron()
    {
        $this->vertices = $this->getVertices();
        $this->faces = $this->getFaces();

        foreach ($this->faces as $i => $face) {
            $this->drawTriangle($face);

            //$x = $vertice[0] * 20;
            //$y = $vertice[1] * 20;

            //$x_center = $this->width / 2;
            //$y_center = $this->height / 2;

            //        imageline($this->image, $x_center+0, $y_center +0, $x_center + $x, $y_center +$y, $this->black);
        }
        return;
        $pta = [0, 0];
        $ptb = [sqrt(20), 1];
        $ptc = [20, 0];

        imageline($this->image, 20, 20, 280, 280, $black);
        imageline($this->image, 20, 20, 20, 280, $black);
        imageline($this->image, 20, 280, 280, 280, $black);
    }

    /*
     *
     * @return unknown
     */
    public function readSubject()
    {
    }
}
