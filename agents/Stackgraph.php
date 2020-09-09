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

        $this->readInput();

        $this->thing->log(
            $this->agent_prefix .
                'settings are: ' .
                $this->agent .
                ' ' .
                $this->name .
                ' ' .
                $this->identity .
                "."
        );

        $this->node_list = ["stackgraph"];

    }

    function set()
    {
        $this->thing->json->setField("variables");
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
            // Check each of the three Things.
            /*
            $this->variables_thing = new Thing($thing['uuid']);

            $thing = new Thing($thing['uuid']);
            $thing->json->setField("variables");

            $count = $thing->getVariable("stack", "count");
            $refreshed_at = strtotime($thing->getVariable("stack", "refreshed_at"));
*/

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

    /*
    function getAgent() 
    {
    }
*/

    function getVariables($agent = null)
    {
        return;
        if ($agent == null) {
            $agent = $this->agent;
        }

        $this->variables_horizon = 99;
        $this->variables_agent = $agent; // Allows getVariables to pull in a different agents variables.
        // Here we only need to save the count.
        // But need to inspect Tally

        //        $this->variables_agent = $agent;

        // So this returns the last 3 tally Things.
        // which should be enough.  One should be enough.
        // But this just provides some resiliency.

        $this->thing->log('requested the variables.', 'DEBUG');

        // We will probably want a getThings at some point.
        $this->thing->db->setFrom($this->identity);
        $thing_report = $this->thing->db->agentSearch(
            $this->variables_agent,
            $this->variables_horizon
        );
        $things = $thing_report['things'];

        if ($things == false) {
            $this->startVariables();
            return;
        }

        $this->thing->log(
            'got ' . count($things) . ' recent Tally Things.',
            'INFORMATION'
        );

        $this->counter_uuids = [];

        foreach ($things as $thing) {
            // Check each of the three Things.
            $this->variables_thing = new Thing($thing['uuid']);

            $uuid = $thing['uuid'];
            $variable = $this->getVariable('variable');
            $name = $this->getVariable('name');
            $next_uuid = $this->getVariable('next_uuid');

            if ($this->name == $name) {
                $this->counter_uuids[] = $uuid;
                break;
            }
        }

        $match_uuid = $next_uuid;

        $split_time = $this->thing->elapsed_runtime();
        $index = 0;

        while (true) {
            foreach ($things as $thing) {
                // Check each of the three Things.
                $this->variables_thing = new Thing($thing['uuid']);

                $uuid = $thing['uuid'];
                $variable = $this->getVariable('counter');
                //$name = $this->getVariable('name');
                $next_uuid = $this->getVariable('next_uuid');

                if ($name == $match_uuid) {
                    $this->counter_uuids[] = $uuid;
                    break;
                }
            }

            $match_uuid = $next_uuid;

            $index += 1;

            $max_time = 1000 * 10; //ms
            if ($this->thing->elapsed_runtime() - $split_time > $max_time) {
                break;
            }
        }

        return;
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

    function getVariable($variable = null)
    {
        // Pulls variable from the database
        // and sets variables thing on the current record.
        // so shouldn't need to adjust the $this-> set
        // of variables and can refactor that out.

        // All variables should be callable by
        // $this->variables_thing.

        // The only Thing variable of use is $this->from
        // which is used to set the identity for
        // self-tallies.  (Thing and Agent are the
        // only two role descriptions.)

        if ($variable == null) {
            $variable = 'variable';
        }

        $this->variables_thing->db->setFrom($this->identity);
        $this->variables_thing->json->setField("variables");

        $this->variables_agent = "tallycounter";

        $this->variables_thing->$variable = $this->variables_thing->json->readVariable(
            [$this->variables_agent, $variable]
        );

        // And then load it into the thing
        //        $this->$variable = $this->variables_thing->$variable;
        //        $this->variables_thing->flagGreen();

        return $this->variables_thing->$variable;
    }

    function setVariable($variable = null, $value)
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
        $this->variables_thing->json->setField("variables");
        $this->variables_thing->json->writeVariable(
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

            //echo $x . " " . $y;
            //echo "<br>";

            //            imagefilledrectangle($this->image,
            //                    $i * $column_width, $this->height,
            //                    $i * $column_width + $column_width, $p,
            //                    $this->black);
            /*
            imagefilledrectangle($this->image,
                    $i * $column_width, 200,
                    $i * $column_width + $column_width, $y,
                    $this->black);

            imagerectangle($this->image,
                    $i * $column_width, 200,
                    $i * $column_width + $column_width, $y,
                    $this->white);
*/

            //foreach(range(-1,1,1) as $key=>$offset) {
            $width = $x - $x_old;

            //            imageline($this->image,
            //                    $x_old + $offset , $y_old + $offset,
            //                    $x, $y,
            //                    $this->green);

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
        //echo "inc" . $inc . "\n";
        $closest_distance = $y_max;
        foreach ($allowed_steps as $key => $step) {
            $distance = abs($inc - $step);
            //echo $distance . "\n";
            if ($distance < $closest_distance) {
                $closest_distance = $distance;
                $preferred_step = $step;
            }
        }
        //echo $closest_distance;
        //echo "<br>";
        //$inc = $closest_distance;

        $this->drawGrid($y_min, $y_max, $preferred_step);
    }

    private function drawGrid($y_min, $y_max, $inc)
    {
        $y = $this->roundUpToAny($y_min, $inc);

        //echo $y . " ". $y_max;
        //exit();
        while ($y <= $y_max) {
            //    echo $i++;  /* the printed value would be
            //                   $i before the increment
            //                   (post-increment) */

            //echo $y;
            //exit();
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

            $font =
                $GLOBALS['stack_path'] . 'resources/roll/KeepCalm-Medium.ttf';

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

        //$font = '/var/www/html/stackr.ca/resources/roll/KeepCalm-Medium.ttf';
        $font = $GLOBALS['stack_path'] . 'resources/roll/KeepCalm-Medium.ttf';

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

        //echo '<img src="data:image/png;base64,'.base64_encode($imagedata).'"/>';
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
