<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from Tick

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Index extends Agent
{
    public function init()
    {

        $this->pad_length = 4;
        $this->node_list = ["index" => ["index"]];

    }

    public function set()
    {
        $this->padIndex();

        $this->variables_agent->setVariable("index", $this->index);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    public function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables index " . $this->from
        );

        $this->index = $this->variables_agent->getVariable("index");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log($this->agent_prefix . "loaded " . $this->index . ".");
    }

    function padIndex()
    {
        $this->index_padded = str_pad(
            $this->index,
            $this->pad_length,
            "0",
            STR_PAD_LEFT
        );
    }

    public function assertIndex($n)
    {
        if (!isset($n)) {
            $this->get();
            $n = $this->index;
        }

        $this->index = $n;
        $this->padIndex();
    }

    public function resetIndex()
    {
        $this->index = 1;
    }

    public function incrementIndex()
    {
        if (!isset($this->index)) {
            $this->get();
        }

        $this->index += 1;
    }

    public function makeSMS()
    {
        $sms = "INDEX | ";
        switch ($this->index) {
            case 1:
                $sms .= "Index is one. ";
                break;
            case 2:
                $sms .= "Index is two. ";
                break;
            case null:
            default:
        }

        $this->padIndex();
        $sms .= "" . $this->index_padded;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeEmail()
    {
        switch ($this->index) {
            case 1:
                $subject = "Index request received";
                $message = "Index is " . $this->index . ".\n\n";

                break;

            case null:

            default:
                $subject = "Index request received";
                $message = "Index is " . $this->index . ".\n\n";
        }

        $this->message = $message;
        $this->thing_report["email"] = $message;
    }

    public function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks("index");

        $this->choices = $choices;
        $this->thing_report["choices"] = $choices;
    }

    public function makePNG()
    {
        $agent = new Png($this->thing, "png"); // long run

        $this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;
        $this->thing_report["png"] = $agent->image_string;
    }

    public function makeImage()
    {
        $text = strtoupper($this->index);
        $text = $this->index;
        $image_height = 125;
        $image_width = 125;

        $image = imagecreatetruecolor($image_width, $image_height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $grey = imagecolorallocate($image, 128, 128, 128);

        imagefilledrectangle($image, 0, 0, $image_width, $image_height, $white);
        $textcolor = imagecolorallocate($image, 0, 0, 0);

        //        $this->ImageRectangleWithRoundedCorners($image, 0,0, $image_width, $image_height, 12, $black);
        //        $this->ImageRectangleWithRoundedCorners($image, 6,6, $image_width-6, $image_height-6, 12-6, $white);

        $font = $this->default_font;

        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);
        $sizes_allowed = [72, 36, 24, 18, 12, 6];

        if (file_exists($font)) {
            foreach ($sizes_allowed as $size) {
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

                //check width of the image
                $width = imagesx($image);
                $height = imagesy($image);
                if ($bbox["width"] < $image_width - 10) {
                    break;
                }
            }

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

        imagestring(
            $image,
            2,
            $image_width - 35,
            10,
            $this->thing->nuuid,
            $textcolor
        );

        $this->image = $image;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->makePNG();

        $this->makeChoices();

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["email"] = $this->sms_message;

        // While we work on this
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
        $this->thing_report["help"] =
            $this->agent_prefix . "providing the current index.";
    }

    public function readSubject()
    {

        $keywords = ["index", "next", "last", "+", "plus", "reset"];

        $input = $this->assert($this->input, "index", false);
        $pieces = explode(" ", strtolower($input));

        // See if there is just one number provided
        $number_agent = new Number($this->thing, $input);
        // devstack number
        if ($number_agent->number != false) {
            $this->assertIndex($number_agent->number);
            return;
        }

        // So this is really the 'sms' section
        // Keyword
        $pieces = explode(" ", strtolower($input));
        if (count($pieces) == 1) {
            if ($input == "index") {
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "next":
                        case "increment":
                        case "+":
                            $this->incrementIndex();
                            return;
                        case "reset":
                            $this->resetIndex();
                            return;
                        default:
                        // Could not recognize a command.
                        // Drop through
                    }
                }
            }
        }

        // Ignore subject.
        return;
    }

    public function index()
    {
        $this->thing->log(
            $this->agent_prefix . ' says, "Keeping an index\n\n"'
        );
    }
}
