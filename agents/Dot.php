<?php
/**
 * Dot.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Dot extends Agent
{
    public $var = "hello";

    // Not sure about this pattern.
    // But I need a dot to represent a day.
    public function drawDot($image,
        $text,
        $angle,
        $radius,
        $size,
        $offset = 0,
        $colour = null
    ) {
        if ($colour == null) {
            $colour = $this->colours_agent->black;
        }
        // angle in degrees
        //imagesetthickness($this->image, 5);
        //$init_angle = (-1 * pi()) / 2;
        //$angle = (2 * 3.14159) / 24;
        //$x_pt =  230;
        //$y_pt = 230;

        $angle_radians = ($angle / 180) * pi();

        //foreach (range(0, 24 - 1, 1) as $i) {
        $x_dot = ($radius + $offset) * cos($angle_radians + $this->init_angle);
        $y_dot = ($radius + $offset) * sin($angle_radians + $this->init_angle);

        imagearc(
            $image,
            intval($this->center_x + $x_dot),
            intval($this->center_y + $y_dot),
            intval(2 * $size),
            intval(2 * $size),
            0,
            360,
            $colour
        );

        return $image;

    }
}
