<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Png extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->text = $this->agent_name;

        $command_line = null;

        $this->node_list = ["png" => ["png", "roll"]];

        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        $this->current_time = $this->thing->time();

        // If it is an agent request for png only generate png (for speed)
        if ($this->agent_input == "png") {
            $this->makePNG();
        }
/*
 else {
            $this->respondResponse();
        }
*/
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

 //       $to = $this->thing->from;

 //       $from = "png";

        // So maybe not choices, but the message needs to be passed.
/* 
       $this->makeSMS();
        $this->makeMessage();
        $this->makePNG();

        $this->makeChoices();
        $this->makeWeb();

        $this->makeEmail();
*/
        $this->thing_report["info"] = "This makes a PNG.";

        if (!isset($this->thing_report["help"])) {
            $this->thing_report["help"] = "No help available.";
        }

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

//        $this->makeTXT();

//        return $this->thing_report;
    }

    function makeTXT()
    {
        $txt = 'A PNG which says, "' . $this->text . '".';

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    function makeChoices()
    {
return;
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "png"
        );

        $choices = $this->thing->choice->makeLinks("png");
        $this->thing_report["choices"] = $choices;
    }

    function makeEmail()
    {
        if (!isset($this->html_image)) {
            $this->makePNG();
        }

        $link = $this->web_prefix . "thing/" . $this->uuid . "/png";

        $this->node_list = ["email" => ["png"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "email"
        );
        //$choices = $this->thing->choice->makeLinks("email");

        $web = '<a href="' . $link . '">' . $this->html_image . "</a>";
        $web .= "<br>";

        $web .= "<br>";

        $this->message = $web;

        $makeemail_agent = new Makeemail($this->thing, $this->message);

        $this->email_message = $makeemail_agent->email_message;
        $this->thing_report["email"] = $makeemail_agent->email_message;
    }

    function makeWeb()
    {
        if (!isset($this->image_html)) {
            $this->makePNG();
        }

//        $this->makePNG();
        $link = $this->web_prefix . "thing/" . $this->uuid . "/agent";

        $this->node_list = ["web" => ["png"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "web"
        );
        $this->choices = $this->thing->choice->makeLinks("web");

        $web = '<a href="' . $link . '">' . $this->html_image . "</a>";
        $web .= "<br>";
        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    function makeSMS()
    {
        $sms = "PNG | " . $this->text;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeMessage()
    {
        if (!isset($this->image_html)) {
            $this->makePNG();
        }

        $message = "Stackr made a PNG for you.<br>";
        $message .= $this->html_image;
        $message .= "<br>";

        $this->thing_report["message"] = $message;
    }

    public function makeImage()
    {
        if (isset($this->image)) {
            return;
        }

        $image = imagecreatetruecolor(125, 125);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $grey = imagecolorallocate($image, 128, 128, 128);

        imagefilledrectangle($image, 0, 0, 125, 125, $white);
        $textcolor = imagecolorallocate($image, 0, 0, 0);

        //check width of the image
        $width = imagesx($image);
        $height = imagesy($image);

        $this->width = $width;
        $this->height = $height;

        $font = $this->default_font;

        if (file_exists($font)) {
            $text = $this->text;

            // Add some shadow to the text
            //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

            $size = 24;
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
            extract($bbox, EXTR_PREFIX_ALL, "bb");
            /*
        //check width of the image 
        $width = imagesx($image); 
        $height = imagesy($image);

        $this->width = $width;
        $this->height = $height;
*/
            $pad = 0;
            imagettftext(
                $image,
                $size,
                $angle,
                $width / 2 - $bb_width / 2,
                $height / 2 + $bb_height / 2,
                $grey,
                $font,
                $text
            );
        }

        imagestring($image, 2, 100, 0, $this->thing->nuuid, $textcolor);

        $this->image = $image;
    }

    public function makePNG($image = null)
    {
        if (isset($this->PNG) and $image == null) {
            return true;
        }

        if ($image == null) {
            $this->makeImage();
        } else {
            $this->image = $image;
            $this->width = imagesx($image) * 0.85;
            $this->height = imagesy($image) * 0.85;
        }

        /*
        if (ob_get_contents()) ob_clean();
        ob_start();
        imagepng($image);
        $imagedata = ob_get_contents();
        ob_clean();
        ob_end_clean();
*/

        // Now set the image.

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();
        ob_clean();
        ob_end_clean();

        $this->image_string = base64_encode($imagedata);

        $this->PNG_embed = "data:image/png;base64," . $this->image_string;
        $this->PNG = $imagedata;

        $this->thing_report["png"] = $imagedata;

        if (isset($this->result[1]["roll"])) {
            $alt_text =
                "Rolled " .
                $this->roll .
                " and got " .
                $this->result[1]["roll"] .
                ".";
        } else {
            $alt_text = "Roll result not available";
        }
        /*
        $html = '<img src="data:image/png;base64,'.base64_encode($imagedata). '"
                width="' . $this->width .'" height="' . $this->height . '" 
                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/png.txt">';
*/
        /*
        $html = '<img src="data:image/png;base64,'. $this->image_string . '"
                width="' . $this->width .'" height="' . $this->height . '" 
                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/png.txt">';
*/
        // Removing height fixes problem with image squashing on mobile devices
        // Prodstack css
        $html =
            '<img src="data:image/png;base64,' .
            $this->image_string .
            '"
                width="' .
            $this->width .
            '"  
                alt="' .
            $alt_text .
            '" longdesc = "' .
            $this->web_prefix .
            "thing/" .
            $this->uuid .
            '/png.txt">';

        $this->html_image = $html;

        return $this->thing_report["png"];
    }

    function is_base64_encoded($data)
    {
        // https://stackoverflow.com/questions/2556345/detect-base64-encoding-in-php
        if (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data)) {
            return true;
        } else {
            return false;
        }
    }

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

    function drawTriangle()
    {
        $pta = [0, 0];
        $ptb = [sqrt(20), 1];
        $ptc = [20, 0];

        imageline($image, 20, 20, 280, 280, $black);
        imageline($image, 20, 20, 20, 280, $black);
        imageline($image, 20, 280, 280, 280, $black);
    }

/*
    function read($variable = null)
    {
        $this->get();
    }
*/
    public function readSubject()
    {
    }
}
