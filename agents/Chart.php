<?php
/**
 * Chart.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);
ini_set('display_errors', 1);

class Chart extends Agent
{
    // Latencygraph shows the stack latency history.

    /**
     * refactor
     */
    function init()
    {
        $agent_command = $this->agent_input; //
        $this->agent_command = $agent_command;

        $this->nom_input =
            $agent_command . " " . $this->from . " " . $this->subject;

        $this->ignore_empty = true;

        $this->height = 200;
        $this->width = 300;

        $this->default_y_spread = 100;

        $this->initChart();
        $this->getColours();
        $this->node_list = ["chart"];

        $this->y_max_limit = null;
        $this->y_min_limit = null;
    }

    /**
     *
     */
    function run()
    {
    }

    /**
     *
     */
    function set()
    {
    }

    /**
     *
     */
    public function get()
    {
        //       $this->getColours();
        $this->getData();
    }

    /**
     *
     */
    function getColours()
    {
        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);
    }

    /**
     *
     */
    function getData()
    {
        $split_time = $this->thing->elapsed_runtime();

        $agent_name = "age";
        $tock_series = "age";

        $this->identity = "null" . $this->mail_postfix;
        // We will probably want a getThings at some point.
        $things = false;
        if (isset($this->thing->db)) {

            $this->thing->db->setFrom($this->identity);
            $thing_report = $this->thing->db->agentSearch($agent_name, 99);

            $things = $thing_report['things'];
        }
        if ($things == false) {
            return;
        }

        $this->points = [];
        foreach ($things as $thing) {
            $variables_json = $thing['variables'];

            $variables = $this->thing->json->jsontoArray($variables_json);

            if (!isset($variables[$agent_name])) {
                continue;
            }

            ${$agent_name} = $variables[$agent_name];

            ${$dimension[0]} = $agent_name[$dimension[0]];
            ${$dimension[1]} = $agent_name[$dimension[1]];
            ${$tock_series} = strtotime($agent_name[$tock_series]);

            $elapsed_time = $run_time + $queue_time;

            if (
                ($dimension[0] == null or $dimension[0] == 0) and
                $this->ignore_empty
            ) {
                continue;
            }
            if (
                ($dimension[1] == null or $dimension[1] == 0) and
                $this->ignore_empty
            ) {
                continue;
            }
            if (
                ($tock_series == null or $tock_series == 0) and
                $this->ignore_empty
            ) {
                continue;
            }

            $this->points[] = [
                $tock_series => ${$tock_series},
                $dimension[0] => ${$dimension[0]},
                $dimension[1] => ${$dimension[1]},
            ];
        }

        $this->thing->log(
            'Agent "Chart" getData ran for ' .
                number_format($this->thing->elapsed_runtime() - $split_time) .
                "ms.",
            "OPTIMIZE"
        );
    }

    /**
     *
     */
    function makeSMS()
    {
        $this->sms_message =
            "CHART  | " . $this->web_prefix . "chart/" . $this->uuid;

        if (isset($this->function_message)) {
            $this->sms_message .= " | " . $this->function_message;
        }
        $this->sms_message .= ' | TEXT ?';

        $this->thing_report['sms'] = $this->sms_message;
    }

    /**
     *
     * @return unknown
     */
    function drawGraph()
    {
        if (!isset($this->x_min)) {
            return true;
        }
        if (!isset($this->x_max)) {
            return true;
        }
        if (!isset($this->y_min)) {
            return true;
        }
        if (!isset($this->y_max)) {
            return true;
        }

        if (!isset($this->y_spread)) {
            $this->y_spread = $this->y_max - $this->y_min;
        }
        if (!isset($this->x_spread)) {
            $this->x_spread = $this->x_max - $this->x_min;
        }

        if ($this->x_spread == 0) {
            return true;
        }

        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $num_points = count($this->points);
        if ($num_points == 0) {
            return true;
        }

        $column_width = $this->width / $num_points;

        $i = 0;

        foreach ($this->points as $x => $y) {
            $common_variable = $y;

            $y =
                10 +
                $this->chart_height -
                (($common_variable - $this->y_min) / $this->y_spread) *
                    $this->chart_height;
            $x =
                10 +
                (($x - $this->x_min) / $this->x_spread) * $this->chart_width;
            if (is_nan($y)) {
                return true;
            }

            if (!isset($x_old)) {
                $x_old = $x;
            }
            if (!isset($y_old)) {
                $y_old = $y;
            }

            // +1 to overlap bars
            $width = $x - $x_old;

            $offset = 1.5;
            // dev for very large numbers
            /*
if ( ($y_old - $offset) > $this->chart_height) {continue;}
if ( (-1 * ($y_old - $offset)) > $this->chart_height)  {continue;}

if ( ($y_old + $offset) > $this->chart_height) {continue;}
if ( (-1 * ($y_old + $offset)) > $this->chart_height)  {continue;}

if ( ($x_old - $offset) > $this->chart_width) {continue;}
if ( (-1 * ($x_old - $offset)) > $this->chart_width)  {continue;}

if ( ($x_old + $offset) > $this->chart_width) {continue;}
if ( (-1 * ($x_old + $offset)) > $this->chart_width)  {continue;}
*/
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

        $preferred_step = 10;
        $allowed_step_multiples = [1, 2, 5];

        $range = $this->y_max - $this->y_min;

        $inc = ($this->y_max - $this->y_min) / 5;

        $digits = $range !== 0 ? floor(log10($range) + 1) : 1;

        $digits = $digits - 2;
        $allowed_steps = [
            1 * pow(10, $digits),
            2 * pow(10, $digits),
            5 * pow(10, $digits),
            10 * pow(10, $digits),
        ];

        $closest_distance = $this->y_max;

        foreach ($allowed_steps as $key => $step) {
            $distance = abs($inc - $step);
            if ($distance < $closest_distance) {
                $closest_distance = $distance;
                $preferred_step = $step;
            }
        }

        $this->drawGrid($this->y_min, $this->y_max, $preferred_step);
    }

    /**
     *
     * @param unknown $series_name (optional)
     * @param unknown $colour      (optional)
     * @param unknown $line_width  (optional)
     * @return unknown
     */
    public function drawSeries(
        $series_name = null,
        $colour = 'red',
        $line_width = 1.5
    ) {
        if ($series_name == null) {
            return true;
        }

        $y_max = $this->y_max;
        $x_max = $this->x_max;

        $y_min = $this->y_min;
        $x_min = $this->x_min;

        //$series_name = 'temperature_1';
        $x_max = strtotime($this->current_time);

        $i = 0;
        foreach ($this->points as $point) {
            //$y = array();

            $series = $point[$series_name];

            $refreshed_at = $point['refreshed_at'];

            $y_spread = $y_max - $y_min;
            if ($y_spread == 0) {
                $y_spread = $this->default_y_spread;
                $this->y_spread = $y_spread;
            }

            $y =
                10 +
                $this->chart_height -
                (($series - $y_min) / $y_spread) * $this->chart_height;
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

            $offset = $line_width;

            imagefilledrectangle(
                $this->image,
                $x_old - $offset,
                $y_old - $offset,
                $x_old + $width / 2 + $offset,
                $y_old + $offset,
                $this->{$colour}
            );

            imagefilledrectangle(
                $this->image,
                $x_old + $width / 2 - $offset,
                $y_old - $offset,
                $x - $width / 2 + $offset,
                $y + $offset,
                $this->{$colour}
            );

            imagefilledrectangle(
                $this->image,
                $x - $width / 2 - $offset,
                $y - $offset,
                $x + $offset,
                $y + $offset,
                $this->{$colour}
            );

            $y_old = $y;
            $x_old = $x;

            $i += 1;
        }
    }

    /**
     *
     * @param unknown $y_min
     * @param unknown $y_max
     * @param unknown $inc
     */
    public function drawGrid($y_min, $y_max, $inc)
    {
        $y = $this->roundUpToAny($y_min, $inc);

        $y_spread = $y_max - $y_min;

        if ($y_spread == 0) {
            $y_spread = $this->default_y_spread;
            $this->y_spread = $y_spread;
        }

        while ($y <= $y_max) {
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

            $font = $this->font;

            $text = $y;

            $size = 6;
            $angle = 0;
            $pad = 0;

            if (file_exists($font)) {
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
            }

            $y = $y + $inc;
        }

        $plot_y =
            10 +
            $this->chart_height -
            ((0 - $y_min) / $y_spread) * $this->chart_height;

        imageline($this->image, 10, $plot_y, 300 - 10, $plot_y, $this->black);
    }

    /**
     *
     * @param unknown $n
     * @param unknown $x (optional)
     * @return unknown
     */
    function roundUpToAny($n, $x = null)
    {
        if ($x == null) {
            $x = 5;
        }

        return round(($n + $x / 2) / $x) * $x;
    }

    /**
     *
     */
    private function drawBar()
    {
    }

    /**
     *
     * @return unknown
     */
    public function initChart()
    {
        if (!isset($this->width) or !isset($this->height)) {
            return true;
        }

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
    }

    /**
     *
     * @return unknown
     */
    public function makePNG()
    {
        if (!isset($this->image)) {
            return true;
        }

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();
        ob_end_clean();

        $this->thing_report['png'] = $imagedata;

        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="chart"/>';
        $this->image_embedded = $response;

        return $response;
    }

    /**
     *
     */
    function makeWeb()
    {
        if (!isset($this->image)) {
            $this->thing_report['web'] = "No chart available.";

            return;
        }

        $link = $this->web_prefix . 'chart/' . $this->uuid . '/agent';

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

        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    function makeTXT()
    {
        return true;

        if (!isset($this->points)) {
            $this->thing_report['txt'] = "No data available.";
            return;
        }

        $txt = 'This is a CHART. ';
        $txt .= "\n";

        $count = null;
        if (is_array($this->points)) {
            $count = count($this->points);
        }

        $txt .= $count . '' . ' Points retrieved.\n';

        $tubs = [];
        $dimension[0] = "age";
        $dimension[1] = "bin_sum";

        foreach ($this->points as $key => $point) {
            if (!isset($x_min)) {
                $x_min = $point['age'];
            }
            if (!isset($x_max)) {
                $x_max = $point['age'];
            }

            if ($point['age'] < $x_min) {
                $x_min = $point['age'];
            }
            if ($point['age'] > $x_max) {
                $x_max = $point['age'];
            }

            if (!isset($y_min)) {
                $y_min = $point['bin_sum'];
            }
            if (!isset($y_max)) {
                $y_max = $point['bin_sum'];
            }

            if ($point['bin_sum'] < $y_min) {
                $y_min = $point['bin_sum'];
            }
            if ($point['bin_sum'] > $y_max) {
                $y_max = $point['bin_sum'];
            }
        }

        $this->x_max = $x_max;
        $this->x_min = $x_min;

        $this->y_max = $y_max;
        $this->y_min = $y_min;

        $this->x_spread = $this->x_max - $this->x_min;
        $txt .= "";
        $txt .= "Dimension[0] tock_series spread is " . $this->x_spread . "\n";

        $num_tubs = 3;

        foreach ($this->points as $key => $point) {
            //$spread = the distance between youngest and oldest age
            $tub_index =
                intval(
                    (($num_tubs - 1) * ($x_max - $point['age'])) /
                        $this->x_spread
                ) + 1;

            if (!isset($tubs[$tub_index])) {
                $tubs[$tub_index] = 1;
                continue;
            }
            $tubs[$tub_index] += 1;
        }

        foreach ($tubs as $x => $y) {
            $txt .= str_pad($x, 7, ' ', STR_PAD_LEFT);
            $txt .= " ";
            $txt .= str_pad($y, 7, ' ', STR_PAD_LEFT);
            $txt .= "\n";
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    /**
     *
     */
    public function defaultCommand()
    {
        $this->agent = "chart";
        $this->name = "thing";
        $this->identity = $this->from;
    }

    /**
     *
     */
    public function readInstruction()
    {
        if ($this->agent_command == null) {
            $this->defaultCommand();
        }

        $pieces = explode(" ", strtolower($this->nom_input));

        $this->agent = $pieces[0];
        $this->name = $pieces[1];
        $this->identity = $pieces[2];
    }

    /**
     *
     */
    public function readText()
    {
        // No need to read text.  Any identity input to Tally
        // increments the tally.
    }

    /**
     *
     */
    public function readSubject()
    {
        //if ($this->agent_input == "chart") {return null;}
        $this->readInstruction();
        $this->readText();
    }

    public function historyChart(
        $variables_history = null,
        $variables_set = null
    ) {
        if ($variables_history == null) {
            return true;
        }
        if ($variables_set == null) {
            return true;
        }

        if (is_array($variables_set)) {
            $variable_name = key($variables_set);
        }

        if (is_string($variables_set)) {
            $variable_name = $variables_set;
        }

        //        $t = "NUMBER CHART\n";
        $points = [];

        // Defaults needed.
        $x_min = 1e99;
        $x_max = -1e99;

        $y_min = 1e99;
        $y_max = -1e99;

        foreach ($variables_history as $i => $number_object) {
            $created_at = $number_object['created_at'];
            $number = $number_object[$variable_name];
            $points[$created_at] = $number;

            if (!isset($x_min)) {
                $x_min = $created_at;
            }
            if (!isset($x_max)) {
                $x_max = $created_at;
            }

            if ($created_at < $x_min) {
                $x_min = $created_at;
            }
            if ($created_at > $x_max) {
                $x_max = $created_at;
            }

            if (!isset($y_min)) {
                $y_min = $number;
            }
            if (!isset($y_max)) {
                $y_max = $number;
            }

            if ($number < $y_min) {
                $y_min = $number;
            }
            if ($number > $y_max) {
                $y_max = $number;
            }
        }

        $temp_chart_agent = new Chart(
            $this->thing,
            "chart " . $variable_name . " " . $this->from
        );
        $temp_chart_agent->points = $points;

        $temp_chart_agent->x_min = $x_min;
        $temp_chart_agent->x_max = $x_max;
        $temp_chart_agent->x_max = strtotime($this->thing->time);

        if ($this->y_min_limit != false or $this->y_min_limit != null) {
            $y_min = $this->y_min_limit;
        }
        $temp_chart_agent->y_min = $y_min;

        if ($this->y_max_limit != false or $this->y_max_limit != null) {
            $y_max = $this->y_max_limit;
        }
        $temp_chart_agent->y_max = $y_max;

        $y_spread = 100;
        if (
            $temp_chart_agent->y_min == false and
            $temp_chart_agent->y_max === false
        ) {
            //
        } elseif (
            $temp_chart_agent->y_min == false and
            is_numeric($temp_chart_agent->y_max)
        ) {
            $y_spread = $y_max;
        } elseif (
            $temp_chart_agent->y_max == false and
            is_numeric($temp_chart_agent->y_min)
        ) {
            // test stack
            $y_spread = abs($temp_chart_agent->y_min);
        } else {
            $y_spread = $temp_chart_agent->y_max - $temp_chart_agent->y_min;
            //            if ($y_spread == 0) {$y_spread = 100;}
        }
        if ($y_spread == 0) {
            $y_spread = 100;
        }

        $temp_chart_agent->y_spread = $y_spread;
        $temp_chart_agent->drawGraph();

        $temp_chart_agent->makePNG();
        return $temp_chart_agent->image_embedded;
    }
}
