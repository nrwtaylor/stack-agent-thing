<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);
ini_set('display_errors', 1);

class Stackgraph extends Agent
{
    // Stackgraph shows how much is on the stack.

    public function init()
    {
        // Setup Agent
        $this->agent = strtolower(get_class());

        $this->agent_command = $this->agent_input;

        $this->nom_input =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $this->ignore_empty = true;

        $this->height = 200;
        $this->width = 300;

//        $this->readInput();


        $this->node_list = ["stackgraph"];

    }

    function readSubject() {

        $this->readInput();


    }

    function set()
    {
    }

    function get()
    {
        $this->getData();
    }

    function getData()
    {
        $this->identity = "null" . $this->mail_postfix;

        // We will probably want a getThings at some point.
        $this->thing->db->setFrom($this->identity);
        //$thing_report = $this->thing->db->agentSearch("stack", 99);
        $thing_report = $this->thing->db->getStack();

        $things = $thing_report['things'];

        if ($things == false) {
            return;
        }

        $this->points = [];
        foreach ($things as $thing) {

            $variables_json = $thing['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);

            if (!isset($variables['stack'])) {
                continue;
            }

            $stack = $variables['stack'];

            if (!isset($stack['count'])) {
                continue;
            }

            $count = $stack['count'];
            $refreshed_at = strtotime($stack['refreshed_at']);

            if (($count == null or $count == 0) and $this->ignore_empty) {
                continue;
            }

            if (
                ($refreshed_at == null or $refreshed_at == 0) and
                $this->ignore_empty
            ) {
                continue;
            }

            $this->points[] = [
                "refreshed_at" => $refreshed_at,
                "series_0" => $count,
                "series_1" => null,
            ];
        }
    }

    function startVariables()
    {
        $this->thing->log('started a count.');

        if (!isset($this->variables_thing)) {
            $this->variables_thing = $this->thing;
        }

        $this->setVariable("variable", 0);
        $this->setVariable("name", $this->name);
    }

    function setVariable($variable = null, $value = null)
    {
        // Take a variable in the variables_thing and save
        // into the database.  Probably end
        // up coding setVariables, to
        // speed things up, but it isn't needed from
        // a logic perspective.

        if ($variable == null) {
            $variable = 'variable';
        }
        //        if (!isset($this->variables_thing)) { $this->variables_thing = $this->thing;}

        $this->variables_thing->$variable = $value;

        $this->variables_thing->db->setFrom($this->identity);
        $this->variables_thing->Write(
            [$this->variables_agent, $variable],
            $value
        );

        //        $this->$variable = $value;
        //        $this->variables_thing->flagGreen();

        return $this->variables_thing->$variable;
    }

    public function respondResponse()
    {
        // Develop the various messages for each channel.

        // Thing actions
        // Because we are making a decision and moving on.  This Thing
        // can be left alone until called on next.
        $this->thing->flagGreen();

        //        $this->makePNG();

        // While we work on this
        $this->thing_report['email'] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);

        //        $this->makeWeb();

        //		return $this->thing_report;
    }

    public function makeSMS()
    {
        $sms =
            "STACK GRAPH  | " . $this->web_prefix . "stackgraph/" . $this->uuid;

        if (isset($this->function_message)) {
            $sms .= " | " . $this->function_message;
        }
        $sms .= ' | TEXT ?';

        //$this->thing_report['thing'] = $this->thing->thing;
        $this->thing_report['sms'] = $sms;
        $this->sms_message = $sms;
    }

    function drawGraph()
    {
        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;

        $series_0 = $this->points[0]['series_0'];
        $series_1 = $this->points[0]['series_1'];

        $refreshed_at = $this->points[0]['refreshed_at'];

        // Get min and max
        if (!isset($y_min)) {
            $y_min = $series_0 + $series_1;
        }
        if (!isset($y_max)) {
            $y_max = $series_0 + $series_1;
        }

        if (!isset($x_min)) {
            $x_min = $refreshed_at;
        }
        if (!isset($x_max)) {
            $x_max = $refreshed_at;
        }

        $i = 0;
        foreach ($this->points as $point) {
            $series_0 = $point['series_0'];
            $series_1 = $point['series_1'];

            $combined_series = $series_0 + $series_1;
            $refreshed_at = $point['refreshed_at'];

            if ($combined_series == null or $combined_series == 0) {
                continue;
            }

            if ($combined_series < $y_min) {
                $y_min = $combined_series;
            }
            if ($combined_series > $y_max) {
                $y_max = $combined_series;
            }

            if ($refreshed_at < $x_min) {
                $x_min = $refreshed_at;
            }
            if ($refreshed_at > $x_max) {
                $x_max = $refreshed_at;
            }

            $i += 1;
        }

        $x_max = strtotime($this->current_time);
        $i = 0;

        foreach ($this->points as $point) {
            //       if (($point['variable'] == null) or ($point['variable'] == 0)) {
            //           continue;
            //       }

            $series_0 = $point['series_0'];
            $series_1 = $point['series_1'];
            $combined_series = $series_0 + $series_1;
            $refreshed_at = $point['refreshed_at'];

            $y_spread = $y_max - $y_min;
            if ($y_spread == 0) {
                $y_spread = 100;
            }

            $y =
                10 +
                $this->chart_height -
                (($combined_series - $y_min) / $y_spread) * $this->chart_height;
            $x =
                10 +
                (($refreshed_at - $x_min) / ($x_max - $x_min)) *
                    $this->chart_width;

            if (!isset($x_old)) {
                $x_old = $x;
            }
            if (!isset($y_old)) {
                $y_old = $y;
            }

            $width = $x - $x_old;

            $offset = 1.5;

            imagefilledrectangle(
                $this->image,
                $x_old - $offset,
                $y_old - $offset,
                $x_old + $width / 2 + $offset,
                $y_old + $offset,
                $this->red
            );

            imagefilledrectangle(
                $this->image,
                $x_old + $width / 2 - $offset,
                $y_old - $offset,
                $x - $width / 2 + $offset,
                $y + $offset,
                $this->red
            );

            imagefilledrectangle(
                $this->image,
                $x - $width / 2 - $offset,
                $y - $offset,
                $x + $offset,
                $y + $offset,
                $this->red
            );

            //}

            $y_old = $y;
            $x_old = $x;

            $i += 1;
            //if ($i = 10) {break;}
        }
        $allowed_steps = [
            2,
            5,
            10,
            20,
            25,
            50,
            100,
            200,
            250,
            500,
            1000,
            2000,
            2500,
            10000,
            25000,
            50000,
            100000,
        ];
        $inc = ($y_max - $y_min) / 5;

        $closest_distance = $y_max;
        foreach ($allowed_steps as $key => $step) {
            $distance = abs($inc - $step);
            if ($distance < $closest_distance) {
                $closest_distance = $distance;
                $preferred_step = $step;
            }
        }

        $this->drawGrid($y_min, $y_max, $preferred_step);
    }

    private function drawGrid($y_min, $y_max, $inc)
    {
        $y = $this->roundUpToAny($y_min, $inc);

        while ($y <= $y_max) {

            $y_spread = $y_max - $y_min;
            if ($y_spread == 0) {
                $y_spread = 100;
            }

            $plot_y =
                10 +
                $this->chart_height -
                (($y - $y_min) / $y_spread) * $this->chart_height;

            imageline(
                $this->image,
                10,
                $plot_y,
                300 - 10,
                $plot_y,
                $this->black
            );

            $font = $this->default_font;

            $text = number_format($y);
            // Add some shadow to the text
            //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

            $size = 6;
            $angle = 0;
            //$bbox = imagettfbbox ($size, $angle, $font, $text);
            //$bbox["left"] = 0- min($bbox[0],$bbox[2],$bbox[4],$bbox[6]);
            //$bbox["top"] = 0- min($bbox[1],$bbox[3],$bbox[5],$bbox[7]);
            //$bbox["width"] = max($bbox[0],$bbox[2],$bbox[4],$bbox[6]) - min($bbox[0],$bbox[2],$bbox[4],$bbox[6]);
            //$bbox["height"] = max($bbox[1],$bbox[3],$bbox[5],$bbox[7]) - min($bbox[1],$bbox[3],$bbox[5],$bbox[7]);
            //extract ($bbox, EXTR_PREFIX_ALL, 'bb');
            //check width of the image
            //$width = imagesx($this->image);
            //$height = imagesy($this->image);
            $pad = 0;

            imagettftext(
                $this->image,
                $size,
                $angle,
                10,
                $plot_y - 1,
                $this->grey,
                $font,
                $text
            );

            $y = $y + $inc;
        }
    }

    function roundUpToAny($n, $x = 5)
    {
        return round(($n + $x / 2) / $x) * $x;
    }

    private function drawBar()
    {
    }

    public function makePNG()
    {
        //    $this->height = 200;
        //    $this->width = 300;

        $this->image = imagecreatetruecolor($this->width, $this->height);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        imagefilledrectangle(
            $this->image,
            0,
            0,
            $this->width,
            $this->height,
            $this->white
        );

        $textcolor = imagecolorallocate($this->image, 0, 0, 0);

        $this->drawGraph();

        // Write the string at the top left
        $border = 30;
        $radius = (1.165 * (125 - 2 * $border)) / 3;

        $font = $this->default_font;

        $text = "test";
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
        $width = imagesx($this->image);
        $height = imagesy($this->image);
        $pad = 0;
        //imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $grey, $font, $number);

        //     imagestring($this->image, 2, 100, 0, $this->thing->nuuid, $textcolor);

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();
        ob_end_clean();

        $this->thing_report['png'] = $imagedata;

        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="stackgraph"/>';
        $this->image_embedded = $response;

        imagedestroy($this->image);

        return $response;

        $this->PNG = $image;
        $this->thing_report['png'] = $image;

        return;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'stackgraph/' . $this->uuid . '/agent';

        $head = '
<td>
<table border="0" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF; border-bottom:0; border-radius:10px">
<tr>
<td align="center" valign="top">
<div padding: 5px; text-align: center">';

        $foot = "</td></div></td></tr></tbody></table></td></tr>";

        $web = '<a href="' . $link . '">';
        $web .= $this->image_embedded;
        $web .= "</a>";
        $web .= "<br>";

        $web .= "stack graph";

        $web .= "<br><br>";

        $this->thing_report['web'] = $web;
    }

    public function defaultCommand()
    {
        $this->agent = "tallycounter";
        //$this->limit = 5;
        $this->name = "thing";
        $this->identity = $this->from;
        return;
    }

    public function readInstruction()
    {
        if ($this->agent_command == null) {
            $this->defaultCommand();
            return;
        }

        $pieces = explode(" ", strtolower($this->nom_input));

        $this->agent = $pieces[0];
        $this->name = $pieces[1];
        $this->identity = $pieces[2];

        //        $this->thing->log( 'Agent "Tally" read the instruction and got ' . $this->agent . ' ' . $this->limit . ' ' . $this->name . ' ' . $this->identity . "." );

        return;
    }

    public function readText()
    {
        // No need to read text.  Any identity input to Tally
        // increments the tally.

        return;
    }

    public function readInput()
    {
        $this->readInstruction();
        $this->readText();
        return;
    }
}
