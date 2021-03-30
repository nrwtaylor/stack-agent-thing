<?php
namespace Nrwtaylor\StackAgentThing;


ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Petal extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->node_list = ["petal" => ["petal"]];

        $this->haystack =
            $this->uuid .
            $this->to .
            $this->subject .
            $this->agent_input;

        $this->current_time = $this->thing->json->time();
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "petal",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["petal", "refreshed_at"],
                $time_string
            );
        }

        $this->thing->json->setField("variables");
        $this->roll = $this->thing->json->readVariable(["petal", "roll"]);
        $this->result = $this->thing->json->readVariable(["petal", "result"]);
    }

    public function set()
    {
        $this->thing->json->writeVariable(["petal", "roll"], $this->roll);
        $this->thing->json->writeVariable(["petal", "result"], $this->result);

    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        //$this->sms_message = "ROLL | ";

        $choices = false;
        $this->makeChoices();

        $this->thing_report["info"] = "This will draw a petal. One day.";

        $this->thing_report["help"] =
            "This is about evenutally drawing a flower.";

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "petal"
        );

        $choices = $this->thing->choice->makeLinks("petal");
        $this->thing_report["choices"] = $choices;
    }

    function makeSMS()
    {
        $temp_sms_message = "";

        if (
            !isset($this->result) or
            $this->result == "Invalid input" or
            $this->result == null
        ) {
            $sms = "PETAL | Request not processed. Check syntax.";
        } else {
            $sms = "PETAL | ";
            //var_dump($this->result);
            foreach ($this->result as $k => $v) {
                foreach ($v as $key => $value) {
                    if ($key == "roll") {
                        //   $message .= '<br>Total roll is ' . $value . '<br>';
                        //$temp_sms_message .= 'Total roll = ' . $value;
                        $roll = $value;
                    } else {
                        //   $message .= $key . ' giving ' . $value . '<br>';
                        $temp_sms_message .= $key . "=" . $value . " ";
                    }
                }
            }

            $sms = "PETAL = " . $roll . " | ";
            $sms .= $temp_sms_message;
            $sms .= "| TEXT ?";
        }

        $this->thing_report["sms"] = $sms;
    }

    function makeMessage()
    {
        $message = "Stackr rolled the following for you.<br>";

        foreach ($this->result as $k => $v) {
            foreach ($v as $key => $value) {
                if ($key == "roll") {
                    $message .= "<br>Total roll is " . $value . "<br>";
                    $roll = $value;
                } else {
                    $message .= $key . " giving " . $value . "<br>";
                }
            }
        }

        $this->thing_report["message"] = $message;

        return;
    }

    public function makePNG()
    {
        if (count($this->result) != 2) {
            return;
        }

        $number = $this->result[1]["roll"];

        $this->image = imagecreatetruecolor(125, 125);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        imagefilledrectangle($this->image, 0, 0, 125, 125, $this->white);

        $textcolor = imagecolorallocate($this->image, 0, 0, 0);

        $this->drawPetal(56, 64);

        $number = $this->result[0]["d6"];
        // Create a 55x30 image
        //$image = imagecreatetruecolor(125, 125);

        // Draw a white rectangle

        //imagefilledrectangle($image, 0, 0, 200, 125, ${$this->state});

        //imagefilledrectangle($image, 0, 0, 125, 125, $white);

        //$textcolor = imagecolorallocate($image, 0, 0, 0);

        //}
        // Write the string at the top left
        $border = 30;
        $radius = (1.165 * (125 - 2 * $border)) / 3;

        //$number = 6;

        if ($number > 99) {
            return;
        }

        $font = $this->default_font;

        $text = $number;
        // Add some shadow to the text

        $size = 72;
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

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();
        ob_end_clean();

        $this->thing_report["png"] = $imagedata;

        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="hexagram"/>';

        imagedestroy($this->image);

        return $response;

        $this->PNG = $image;
        $this->thing_report["png"] = $image;

        return;
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

    function hex_corner($center_x, $center_y, $size, $i)
    {
        $PI = 3.14159;
        $angle_deg = 60 * $i + 30;
        $angle_rad = ($PI / 180) * $angle_deg;
        return [
            $center_x + $size * cos($angle_rad),
            $center_y + $size * sin($angle_rad),
        ];
    }

    function drawPetal($n, $p)
    {
        $this->step_length = 20;
        $this->points = [
            [0, 0],
            [0, $this->step_length],
            [0, 2 * $this->step_length],
            [0, 3 * $this->step_length],
        ];

        $arr = [0, 1, 2, 3, 4, 5];
        foreach ($arr as &$value) {
            list($x, $y) = $this->hex_corner(60, 60, 30, $value);

            if (isset($x_new)) {
                imageline($this->image, 60, 60, $x, $y, $this->black);
            }
            $x_new = $x;
            $y_new = $y;
        }
    }

    function iteratePetal()
    {
        $step_length = 20;
        echo "<pre>";
        $drive_vector = [0, 1];
        $i = 0;
        foreach ($this->points as &$point) {
            //  var_dump(rand(0,360)/360);

            $disturbance_vector = [rand(0, 100) / 100, rand(0, 100) / 100];
            $growth_vector = [
                $drive_vector[0] + $disturbance_vector[0],
                $drive_vector[1] + $disturbance_vector[1],
            ];

            $i += 1;
            $this->points[$i][0] =
                $point[0] + $growth_vector[0] * $this->step_length;
            $this->points[$i][1] =
                $point[1] + $growth_vector[1] * $this->step_length;
        }
    }

    function getPetal($input)
    {
        if (!isset($this->rolls)) {
            $this->rolls = $this->extractRolls($input);
        }
        //var_dump($this->rolls);

        if (count($this->rolls) == 1) {
            $this->roll = $this->rolls[0];
            return $this->roll;
        }

        if (count($this->rolls) == 0) {
            $this->roll = "d6";
            return $this->roll;
        }

        $this->roll = false;

        return false;
    }

    function extractRolls($input)
    {
        if (!isset($this->rolls)) {
            $this->rolls = [];
        }

        //Why not combine them into one character class? /^[0-9+#-]*$/ (for matching) and /([0-9+#-]+)/ for capturing ?
        $pattern = "|^(\\d)?d(\\d)(\\+\\d)?$|";
        //$pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";
        $pattern = "/([0-9d+]+)/";
        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);
        $this->rolls = $arr;


        return $this->rolls;
    }

    function dieRoll($die_N = 6, $modifier = 0)
    {
        $d = rand(1, $die_N);
        $roll = $d + $modifier;

        return $roll;
    }

    public function readSubject()
    {
$input = $this->assert($this->input, "petal", false);
        // Translate from emoji
        $temp_thing = new Emoji($this->thing, "emoji");
        $input = $temp_thing->translated_input;

        $n = substr_count($input, "roll");

        //$input=preg_replace('/\b(\S+)(?:\s+\1\b)+/i', '$1', $input);
        $input = preg_replace(
            '/\b(\S+)(?:\s+\1\b)+/i',
            "roll " . $n . "d6",
            $input
        );

        $this->getPetal($input);

        if ($this->roll == false) {
            $this->roll = "d6";
        }

        $result = [];

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
                $result[] = ["modifier" => $modifier];
            } else {
                if (is_numeric($elements[0]) and is_numeric($elements[1])) {
                    $N_rolls = $elements[0];
                    $die_N = $elements[1];
                } elseif ($die[0] == "d" and is_numeric($elements[1])) {
                    $N_rolls = 1;
                    $die_N = $elements[1];
                } else {
                    // Roll a d6 if unclear
                    //$N_rolls = 1;
                    //$die_N = 6;
                    //return;

                    //					return "Invalid input";
                }

                for ($i = 1; $i <= $N_rolls; $i++) {
                    $d = rand(1, $die_N);
                    $result[] = ["d" . $die_N => $d];

                    $roll = $roll + $d;
                }
            }
        }

        $result[] = ["roll" => $roll];

        $this->result = $result;
        $this->sum = $result;

        return $result;
    }
}
