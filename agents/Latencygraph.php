<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);
ini_set("display_errors", 1);

class Latencygraph extends Agent
{
    // Latencygraph shows the stack latency history.

    public function init()
    {
        $this->nom_input =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $this->ignore_empty = true;

        $this->height = 200;
        $this->width = 300;

        // So I could call
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->current_time = $this->thing->json->time();

        $this->node_list = ["latencygraph"];
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
        $split_time = $this->thing->elapsed_runtime();

        $this->identity = "null" . $this->mail_postfix;
        // We will probably want a getThings at some point.
        $this->thing->db->setFrom($this->identity);
        $thing_report = $this->thing->db->agentSearch("latency", 99);

        $things = $thing_report["things"];

        if ($things == false) {
            return;
        }

        $this->points = [];
        foreach ($things as $thing) {
            $variables_json = $thing["variables"];

            $variables = $this->thing->json->jsontoArray($variables_json);

            if (!isset($variables["latency"])) {
                continue;
            }

            $latency = $variables["latency"];

            // Check each of the three Things.
            //$this->variables_thing = new Thing($thing['uuid']);

            //$thing = new Thing($thing['uuid']);
            //$thing->json->setField("variables");

            //$run_time = $thing->getVariable("latency", "run_time");
            //$queue_time = $thing->getVariable("latency", "queue_time");
            //$refreshed_at = strtotime($thing->getVariable("latency", "refreshed_at"));

            $run_time = $latency["run_time"];
            $queue_time = $latency["queue_time"];
            $refreshed_at = strtotime($latency["refreshed_at"]);

            $elapsed_time = $run_time + $queue_time;

            if (
                ($queue_time == null or $queue_time == 0) and
                $this->ignore_empty
            ) {
                continue;
            }
            if (($run_time == null or $run_time == 0) and $this->ignore_empty) {
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
                "run_time" => $run_time,
                "queue_time" => $queue_time,
            ];
        }

        $this->thing->log(
            'Agent "Latencygraph" getData ran for ' .
                number_format($this->thing->elapsed_runtime() - $split_time) .
                "ms.",
            "OPTIMIZE"
        );
    }

    public function respondResponse()
    {
        // Develop the various messages for each channel.

        // Thing actions
        // Because we are making a decision and moving on.  This Thing
        // can be left alone until called on next.
        $this->thing->flagGreen();

        $this->makeSMS();
        $this->thing_report["thing"] = $this->thing->thing;

        $this->makePNG();

        // While we work on this
        $this->thing_report["email"] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);

        $this->makeWeb();

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->sms_message =
            "LATENCY GRAPH  | " .
            $this->web_prefix .
            "latencygraph/" .
            $this->uuid;

        if (isset($this->function_message)) {
            $this->sms_message .= " | " . $this->function_message;
        }
        $this->sms_message .= " | TEXT ?";

        $this->thing_report["sms"] = $this->sms_message;
    }

    function drawGraph()
    {
        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        if (!isset($this->points)) {
            return true;
        }

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;

        $run_time = $this->points[0]["run_time"];
        $queue_time = $this->points[0]["queue_time"];

        $refreshed_at = $this->points[0]["refreshed_at"];

        // Get min and max
        if (!isset($y_min)) {
            $y_min = $run_time + $queue_time;
        }
        if (!isset($y_max)) {
            $y_max = $run_time + $queue_time;
        }

        if (!isset($x_min)) {
            $x_min = $refreshed_at;
        }
        if (!isset($x_max)) {
            $x_max = $refreshed_at;
        }

        $i = 0;
        foreach ($this->points as $point) {
            $run_time = $point["run_time"];
            $queue_time = $point["queue_time"];
            $elapsed_time = $run_time + $queue_time;
            $refreshed_at = $point["refreshed_at"];

            if ($elapsed_time == null or $elapsed_time == 0) {
                continue;
            }

            if ($elapsed_time < $y_min) {
                $y_min = $elapsed_time;
            }
            if ($elapsed_time > $y_max) {
                $y_max = $elapsed_time;
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
            $run_time = $point["run_time"];
            $queue_time = $point["queue_time"];
            $elapsed_time = $run_time + $queue_time;
            $refreshed_at = $point["refreshed_at"];

            $y_spread = $y_max - $y_min;
            if ($y_spread == 0) {
                $y_spread = 100;
            }

            $y =
                10 +
                $this->chart_height -
                (($elapsed_time - $y_min) / $y_spread) * $this->chart_height;
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

            // +1 to overlap bars
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

            $y_old = $y;
            $x_old = $x;

            $i += 1;
        }

        $allowed_steps = [
            0.02,
            0.05,
            0.2,
            0.5,
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
            20000,
            25000,
            100000,
            200000,
            250000,
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

            $text = $y;

            $size = 6;
            $angle = 0;
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
        extract($bbox, EXTR_PREFIX_ALL, "bb");

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
            '"alt="latencygraph"/>';
        $this->image_embedded = $response;

        imagedestroy($this->image);

        return $response;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "latencygraph/" . $this->uuid . "/agent";

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

        $web .= "latency graph";

        $web .= "<br><br>";

        $this->thing_report["web"] = $web;
    }

    public function defaultCommand()
    {
        $this->agent = "latencygraph";
        $this->name = "thing";
        $this->identity = $this->from;
    }

    public function readInstruction()
    {
        if ($this->agent_input == null) {
            $this->defaultCommand();
            return;
        }

        $pieces = explode(" ", strtolower($this->nom_input));

        $this->agent = $pieces[0];
        $this->name = $pieces[1];
        $this->identity = $pieces[2];
    }

    public function readText()
    {
        // No need to read text.  Any identity input to Tally
        // increments the tally.
    }

    public function readInput()
    {
        $this->readInstruction();
        $this->readText();
    }

    public function readSubject()
    {
        $this->readInput();
    }
}
