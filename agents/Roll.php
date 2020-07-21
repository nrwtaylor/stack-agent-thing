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

class Roll extends Agent
{
    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init() {

        $this->width = 125;
        $this->height = $this->width;

        //test
//        $this->drawD20(2);

        $this->node_list = array("roll" => array("roll", "card"));
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->thing_report["info"] = "This rolls a dice.  See
                                https:\\codegolf.stackexchange.com/questions/25416/roll-dungeons-and-dragons-dice";
        $this->thing_report['help'] = 
                'This is about dice with more than 6 sides.  Try "Roll d20". Or "Roll 3d20+17. Or "Card"';


    }


    /**
     *
     */
    public function get() {

        $this->current_time = $this->thing->json->time();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable(array(
                "roll",
                "refreshed_at"
            ));

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                array("roll", "refreshed_at"),
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->thing->json->setField("variables");
        $this->last_roll = strtolower(
            $this->thing->json->readVariable(array("roll", "roll"))
        );
        $this->last_result = $this->thing->json->readVariable(array(
                "roll",
                "result"
            ));


    }

    /**
     *
     * @return unknown
     */
    public function respondResponse() {
        $this->thing->flagGreen();

        $this->makeChoices();


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


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "roll"
        );

        $choices = $this->thing->choice->makeLinks('roll');
        $this->thing_report['choices'] = $choices;
    }


    /**
     *
     */
    function makeEmail() {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/roll';

        $this->node_list = array("roll" => array("roll", "card"));
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "roll"
        );
        $choices = $this->thing->choice->makeLinks('roll');

        $web = '<a href="' . $link . '">';
        //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/roll.png" jpg"
        //                width="100" height="100"
        //                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/roll.tx$

        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';
        /*
        if (!isset($this->html_image)) {
            $this->makePNG();
        }

        $web .= $this->html_image;
*/
        $web .= '<div class="imageset">';
        foreach ($this->thing_report['pngs'] as $name=>$image_string) {

            $alt_text = $this->images[$name]['alt_text'];


            $html = '<img src="data:image/png;base64,'. $image_string . '"
                width="' . $this->width .'"
                alt="' . $alt_text . '" longdesc="' . $this->web_prefix . 'thing/' .$this->uuid . '/png.txt">';

            $web .=   '<div class="image">' .$html . '</div>';
        }

        $web = '</div>';


        $web .= "</a>";
        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time(time() - $this->refreshed_at);
        $web .= "Rolled about " . $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['email'] = $web;
    }


    /**
     *
     */
    function makeWeb() {
        $web = "";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = array("roll" => array("roll", "card"));
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "web"
        );
        $choices = $this->thing->choice->makeLinks('web');
/*
        if (!isset($this->html_image)) {
            $this->makePNG($this->image);
        }
*/
        //        $web = '<a href="' . $link . '">' . $this->html_image . "</a>";
        //        $web .= "<br>";

        //var_dump($this->thing_report['pngs']);
        //exit();

        $text = "off";
        if ($text == "on") {

            if (isset($this->thing_report['pngs'])) {

                foreach ($this->thing_report['pngs'] as $name=>$image_string) {
                    $alt_text = $this->images[$name]['alt_text'];

                    $web .= $alt_text .  '<br>';
                }

            }

        }

        foreach ($this->thing_report['pngs'] as $name=>$image_string) {

            $alt_text = $this->images[$name]['alt_text'];


            $html = '<img src="data:image/png;base64,'. $image_string . '"
                width="' . $this->width .'"
                alt="' . $alt_text . '" longdesc="' . $this->web_prefix . 'thing/' .$this->uuid . '/'. $name .'.txt" >';

            //        $html = '<br><img src="data:image/png;base64,'. $image_string . '" alt="test" /><br>';

            //$html ="";
            $web .= $html;
            //break;
        }
        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time(time() - $this->refreshed_at);
        $web .= "Rolled about " . $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }


    /**
     *
     */
    function makeSMS() {
        $temp_sms_message = "";

        if (
            !isset($this->result) or
            $this->result == 'Invalid input' or
            $this->result == null
        ) {
            $sms = "ROLL | Request not processed. Check syntax.";
        } elseif ($this->roll == "d6") {
            $sms = "ROLL | " . $this->result[1]['roll'];
        } else {
            $sms = "ROLL | ";
            foreach ($this->result as $k => $v) {
                foreach ($v as $key => $value) {
                    if ($key == 'roll') {
                        $roll = $value;
                    } else {
                        $temp_sms_message .= $key . '=' . $value . ' ';
                    }
                }
            }

            $sms = "ROLL = " . $roll . " | ";
            $sms .= $temp_sms_message;
            $sms .= '| TEXT ?';
        }

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }


    /**
     *
     */
    function makeMessage() {
        $message = "Stackr rolled the following for you.<br>";

        foreach ($this->result as $k => $v) {
            foreach ($v as $key => $value) {
                if ($key == 'roll') {
                    $message .= '<br>Total roll is ' . $value . '<br>';
                    $roll = $value;
                } else {
                    $message .= $key . ' giving ' . $value . '<br>';
                }
            }
        }

        $this->thing_report['message'] = $message;
    }


    /*
    function extractRoll($input) {

//echo $input;
//exit();

preg_match('/^(\\d)?d(\\d)(\\+\\d)?$/',$input,$matches);

print_r($matches);

$t = preg_filter('/^(\\d)?d(\\d)(\\+\\d)?$/',
                '$a="$1"? : 1;for(; $i++<$a; $s+=rand(1,$2) );echo$s$3;',
                $input)?:'echo"Invalid input";';


    }
*/

    /**
     *
     * @param unknown $number (optional)
     * @param unknown $die    (optional)
     * @return unknown
     */
    public function makeImage($number = null, $die = null) {
        //var_dump($number);
        //var_dump($die);
        //if (isset($this->image)) {return;}
        // here DB request or some processing

        if (($die == null) and ($number == null)) {
            if (isset($this->result[2])) {return true;} // Can't figure this out.

            //var_dump($this->result);
            //            $die = "d6";
            //if (isset($this->roll)) {$die = $this->roll;}
            //            $number = $this->result[0]['d6'];
            $die_array = $this->result[0];

            reset($die_array);
            //echo key($die_array) . ' = ' . current($die_array);
            $die = key($die_array);
            $number = current($die_array);


        }
        if ($die == "roll") {return true;}
        //echo "number " . $number . " die " . $die. "<br>";
        /*
if ($number == null) {
    $number = $this->result[count($this->result) - 1]['roll'];

//        if ($die == "d6") {
//            $number = $this->result[0]['d6'];
//        }
}


if ($die == null) {
    $die = $this->roll;
}
*/
        //var_dump($die);
        //var_dump($number);
        //echo $die ." " . $number ."<br>";

        $image = imagecreatetruecolor($this->width, $this->height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $grey = imagecolorallocate($image, 128, 128, 128);

        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $white);

        $textcolor = imagecolorallocate($image, 0, 0, 0);
        /*
        if (count($this->result) != 2) {
            $this->image = $image;
            return $image;
        }
*/
        if ($die == "d6") {
            $image = $this->drawD6($number);
            return $image;
        } else {
            if ($number > 99) {
                $this->image = $image;
                return $image;
            }

            if (false) {
                // draws triangle lines based on the rules of math
                $size = 100;
                list($pta_x, $pta_y) = array(0, 0);
                list($ptb_x, $ptb_y) = array($size / 2, ($size * sqrt(3)) / 2);
                list($ptc_x, $ptc_y) = array($size, 0);

                imageline($image, $pta_x, $pta_y, $ptb_x, $ptb_y, $black);
                imageline($image, $ptb_x, $ptb_y, $ptc_x, $ptc_y, $black);
                imageline($image, $ptc_x, $ptc_y, $pta_x, $pta_y, $black);
            }

            //$font = $GLOBALS['stack'] . 'vendor/nrwtaylor/stack-agent-thing/resources/roll/KeepCalm-Medium.ttf';
            $font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';

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
            $width = imagesx($image);
            $height = imagesy($image);
            $pad = 0;
            imagettftext(
                $image,
                $size,
                $angle,
                $width / 2 - $bb_width / 2,
                $height / 2 + $bb_height / 2,
                $grey,
                $font,
                $number
            );

            //var_dump ($width);
            imagestring($image, 2, 100, 0, $die, $textcolor);
        }

        $this->image = $image;
        return $image;
    }


    /**
     *
     * @param unknown $number
     */
    public function drawD20($number) {

        // devstack

        $r = 1;
        $phi = (1+5^0.5)/2;
        $vertice = array(0, 1, $phi);

        $vertices[0] = array(+1 * $r/sqrt(3), +1 * $r/sqrt(3), +1 * $r/sqrt(3));
        $vertices[1] = array(-1 * $r/sqrt(3), +1 * $r/sqrt(3), +1 * $r/sqrt(3));
        $vertices[2] = array(+1 * $r/sqrt(3), -1 * $r/sqrt(3), +1 * $r/sqrt(3));
        $vertices[3] = array(-1 * $r/sqrt(3), -1 * $r/sqrt(3), +1 * $r/sqrt(3));
        $vertices[4] = array(+1 * $r/sqrt(3), +1 * $r/sqrt(3), -1 * $r/sqrt(3));
        $vertices[5] = array(-1 * $r/sqrt(3), +1 * $r/sqrt(3), -1 * $r/sqrt(3));
        $vertices[6] = array(+1 * $r/sqrt(3), -1 * $r/sqrt(3), -1 * $r/sqrt(3));
        $vertices[7] = array(-1 * $r/sqrt(3), -1 * $r/sqrt(3), -1 * $r/sqrt(3));

        $vertices[8] = array(0, +1 * $r/(sqrt(3) * $phi), +1 * ($r * $phi)/sqrt(3));
        $vertices[9] = array(0, +1 * $r/(sqrt(3) * $phi), -1 * ($r * $phi)/sqrt(3));
        $vertices[10] = array(0, -1 * $r/(sqrt(3) * $phi), +1 * ($r * $phi)/sqrt(3));
        $vertices[11] = array(0, -1 * $r/(sqrt(3) * $phi), -1 * ($r * $phi)/sqrt(3));

        $vertices[12] = array(+1 * $r/(sqrt(3) * $phi), +1 * ($r * $phi)/sqrt(3), 0);
        $vertices[13] = array(+1 * $r/(sqrt(3) * $phi), -1 * ($r * $phi)/sqrt(3), 0);
        $vertices[14] = array(-1 * $r/(sqrt(3) * $phi), +1 * ($r * $phi)/sqrt(3), 0);
        $vertices[15] = array(-1 * $r/(sqrt(3) * $phi), -1 * ($r * $phi)/sqrt(3), 0);

        $vertices[16] = array(+1 * ($r * $phi)/sqrt(3), 0 , +1 * $r/(sqrt(3) * $phi));
        $vertices[17] = array(+1 * ($r * $phi)/sqrt(3), 0 , -1 * $r/(sqrt(3) * $phi));
        $vertices[18] = array(-1 * ($r * $phi)/sqrt(3), 0 , +1 * $r/(sqrt(3) * $phi));
        $vertices[19] = array(-1 * ($r * $phi)/sqrt(3), 0 , -1 * $r/(sqrt(3) * $phi));



    }

    public function drawD6($number) {

       $padding = 2;

       $height = $this->height;
       $width = $this->width;

       $border = 30;

       $image = imagecreatetruecolor($this->width, $this->height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $grey = imagecolorallocate($image, 128, 128, 128);

        imagefilledrectangle($image, 0, 0, $this->width, $this->height, $white);

        $textcolor = imagecolorallocate($image, 0, 0, 0);

           $this->ImageRectangleWithRoundedCorners(
                $image,
                0 + $padding,
                0 + $padding,
                $width - $padding,
                $height - $padding,
                12,
                $black
            );
            $this->ImageRectangleWithRoundedCorners(
                $image,
                6 + $padding,
                6 + $padding,
                $width - 6 - $padding,
                $height - 6 - $padding,
                12 - 6,
                $white
            );

            //            $number = $this->result[0]['d6'];

            // Build pip array
            $pips = array();
            $pips[1] = array(array(1, 1));
            $pips[2] = array(array(0, 0), array(2, 2));
            $pips[3] = array(array(0, 0), array(1, 1), array(2, 2));
            $pips[4] = array(
                array(0, 0),
                array(0, 2),
                array(2, 0),
                array(2, 2)
            );
            $pips[5] = array(
                array(0, 0),
                array(0, 2),
                array(1, 1),
                array(2, 0),
                array(2, 2)
            );
            $pips[6] = array(
                array(0, 0),
                array(0, 1),
                array(0, 2),
                array(2, 0),
                array(2, 1),
                array(2, 2)
            );

            // Write the string at the top left
//            $border = 30;
            $radius = (1.165 * ($width - 2 * $border - 2 * $padding)) / 3;

            foreach ($pips[$number] as $key => $value) {
                list($x, $y) = $value;

                $die_x = (($width - 2 * $border - 2 * $padding) / 2) * $x + $border + $padding;
                $die_y = (($height - 2 * $border - 2 * $padding) / 2) * $y + $border + $padding;

                imagefilledellipse(
                    $image,
                    $die_x,
                    $die_y,
                    $radius,
                    $radius,
                    $black
                );
            }

return $image;
    }

    /**
     *
     * @param unknown $image (optional)
     * @return unknown
     */
    public function makePNG($image = null) {
        if ($image = null) {
            $image = $this->image;
        }
        if ($image == true) {return true;}

        //if (!isset($this->image)) {$this->makeImage();}

        $agent = new Png($this->thing, "png");
        $image = $this->makeImage();

        if ($image === true) {return true;}

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
    public function makePNGs() {
        //if (!isset($this->image)) {$this->makeImage();}
        $this->thing_report['pngs'] = array();
        //return;
        $agent = new Png($this->thing, "png");

        foreach ($this->result as $index=>$die_array) {
            reset($die_array);
            //echo key($die_array) . ' = ' . current($die_array);
            $die = key($die_array);
            $number = current($die_array);

            $image =      $this->makeImage($number, $die);
            if ($image === true) {continue;}

            $agent->makePNG($image);

            //        $this->html_image = $agent->html_image;
            //        $this->image = $agent->image;
            //        $this->PNG = $agent->PNG;

            $alt_text = "Image of a " .$die . " die with a roll of " . $number . ".";


            $this->images[$this->agent_name .'-'.$index] = array("image"=>$agent->image,
                "html_image"=> $agent->html_image,
                "image_string"=> $agent->image_string,
                "alt_text" => $alt_text);


            $this->thing_report['pngs'][$this->agent_name . '-'.$index] = $agent->image_string;


        }
        /*
        $this->image_string = base64_encode($imagedata);

        $this->PNG_embed = "data:image/png;base64,".$this->image_string;
        $this->PNG = $imagedata;

        $this->thing_report['png'] = $imagedata;

        if (isset($this->result[1]['roll'])) {
            $alt_text = "Rolled " . $this->roll . " and got " . $this->result[1]['roll'] . ".";
        } else {
            $alt_text = "Roll result not available";
        }
        // Removing height fixes problem with image squashing on mobile devices
        // Prodstack css
        $html = '<img src="data:image/png;base64,'. $this->image_string . '"
                width="' . $this->width .'"
                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/png.txt">';
*/

        //        $this->makeImage();

        //        $agent->makePNG($this->image);

        //        $this->html_image = $agent->html_image;
        //        $this->image = $agent->image;
        //        $this->PNG = $agent->PNG;

        //$this->thing_report['png'] = $agent->PNG;
        //        $this->thing_report['pngs'] = array('dice-1'=>$agent->image_string);
    }


    /**
     *
     * @param unknown $im     (reference)
     * @param unknown $x1
     * @param unknown $y1
     * @param unknown $x2
     * @param unknown $y2
     * @param unknown $radius
     * @param unknown $color
     */
    function ImageRectangleWithRoundedCorners(
        &$im,
        $x1,
        $y1,
        $x2,
        $y2,
        $radius,
        $color
    ) {
        // draw rectangle without corners
        imagefilledrectangle(
            $im,
            $x1 + $radius,
            $y1,
            $x2 - $radius,
            $y2,
            $color
        );
        imagefilledrectangle(
            $im,
            $x1,
            $y1 + $radius,
            $x2,
            $y2 - $radius,
            $color
        );

        // draw circled corners
        imagefilledellipse(
            $im,
            $x1 + $radius,
            $y1 + $radius,
            $radius * 2,
            $radius * 2,
            $color
        );
        imagefilledellipse(
            $im,
            $x2 - $radius,
            $y1 + $radius,
            $radius * 2,
            $radius * 2,
            $color
        );
        imagefilledellipse(
            $im,
            $x1 + $radius,
            $y2 - $radius,
            $radius * 2,
            $radius * 2,
            $color
        );
        imagefilledellipse(
            $im,
            $x2 - $radius,
            $y2 - $radius,
            $radius * 2,
            $radius * 2,
            $color
        );
    }


    /**
     *
     */
    function drawTriangle() {
        $pta = array(0, 0);
        $ptb = array(sqrt(20), 1);
        $ptc = array(20, 0);

        imageline($image, 20, 20, 280, 280, $black);
        imageline($image, 20, 20, 20, 280, $black);
        imageline($image, 20, 280, 280, 280, $black);
    }


    /**
     *
     */
    function set() {

        if ($this->last_roll == false or $this->last_result == false) {
            //            $this->readSubject();

            $this->thing->json->writeVariable(
                array("roll", "roll"),
                $this->roll
            );
            $this->thing->json->writeVariable(
                array("roll", "result"),
                $this->result
            );

            $this->thing->log(
                $this->agent_prefix . ' completed read.',
                "OPTIMIZE"
            );
        }


    }



    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function getRoll($input) {
        if (!isset($this->rolls)) {
            $this->rolls = $this->extractRolls($input);
        }

        if (count($this->rolls) == 1) {
            $this->roll = strtolower($this->rolls[0]);
            return $this->roll;
        }

        if (count($this->rolls) == 0) {
            $this->roll = "d6";
            return $this->roll;
        }

        $this->roll = false;
        //array_pop($arr);
        return false;
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractRolls($input) {
        if (!isset($this->rolls)) {
            $this->rolls = array();
        }

        //Why not combine them into one character class? /^[0-9+#-]*$/ (for matching) and /([0-9+#-]+)/ for capturing ?
        $pattern = "|^(\\d)?d(\\d)(\\+\\d)?$|";
        //$pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";
        $pattern = '/([0-9d+]+)/';
        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);
        $this->rolls = $arr;

        return $this->rolls;
    }


    /**
     *
     * @param unknown $die_N    (optional)
     * @param unknown $modifier (optional)
     * @return unknown
     */
    function dieRoll($die_N = 6, $modifier = 0) {
        $d = rand(1, $die_N);
        $roll = $d + $modifier;

        return $roll;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {
        if (($this->last_roll == false) or ($this->last_result == false)) {
            //        $input = '2d20+5+d100';
            if ($this->agent_input != null) {
                $input = strtolower($this->agent_input);
            } else {
                $input = strtolower($this->subject);

                $temp_thing = new Emoji($this->thing, "emoji");
                $input = $temp_thing->translated_input;
            }

            $n = substr_count($input, "roll");

            //$input=preg_replace('/\b(\S+)(?:\s+\1\b)+/i', '$1', $input);
            $input = preg_replace(
                '/\b(\S+)(?:\s+\1\b)+/i',
                "roll " . $n . "d6",
                $input
            );

            $this->getRoll($input);

            //        $words = explode(" ", $input);

            //        if ((count($words) ==1) and ($words[0] == $this->agent_name)) {
            //            $input = "d6";
            //        }

            //        if ($words[0] == $this->agent_name) {
            //         array_shift($words);
            //            if (count($words) == 0) {
            $input = "d6";
            //            } else {
            //             $input = implode(" ", $words);
            //                $input = $this->roll;
            //            }
            //        }

            if ($this->roll == false) {
                $this->roll = "d6";
            }

            $result = array();

            $roll = 0;

            $dies = explode("+", $this->roll);

            if (count($dies) == 0) {
                //$dies[0] = "d6";
                //return;
                return "Invalid input";
            }

            foreach ($dies as $die) {
                //echo $die;

                $elements = explode("d", $die, 2);

                if (count($elements) == 1 and is_numeric($elements[0])) {
                    $modifier = $elements[0];
                    $roll = $roll + $modifier;
                    $result[] = array('modifier' => $modifier);
                } else {
                    if (is_numeric($elements[0]) and is_numeric($elements[1])) {
                        $N_rolls = $elements[0];
                        $die_N = $elements[1];
                    } elseif ($die[0] == 'd' and is_numeric($elements[1])) {
                        $N_rolls = 1;
                        $die_N = $elements[1];
                    } else {
                        // Roll a d6 if unclear
                        $N_rolls = 1;
                        $die_N = 6;
                        //return;

                        //     return "Invalid input";
                    }

                    for ($i = 1; $i <= $N_rolls; $i++) {
                        $d = rand(1, $die_N);
                        $result[] = array('d' . $die_N => $d);

                        $roll = $roll + $d;
                    }
                }
            }

            $result[] = array('roll' => $roll);

            $this->result = $result;
            $this->sum = $roll;

            return $result;
        } else {

            $this->roll = $this->last_roll;
            $this->result = $this->last_result;


        }
    }


}

