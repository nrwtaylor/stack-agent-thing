<?php
/**
 * Age.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);
ini_set("display_errors", 1);

class Age extends Chart
{
    /**
     *
     * @param Thing   $thing
     */
    //    function __construct(Thing $thing)
    function init()
    {
        $this->height = 200;
        $this->width = 300;

        $this->series = ["age"];

        $this->node_list = ["start"];

        $this->thing->log(
            '<pre> Agent "Age" running on Thing ' . $this->uuid . " </pre>"
        );

        $this->mail_postfix = $this->thing->container["stack"]["mail_postfix"];
        $this->web_prefix = $this->thing->container["stack"]["web_prefix"];

        $this->current_time = $this->thing->json->time();

        $this->tubs_max = 8;
        $this->y_origin = 10;
        $this->margin_bottom = 10;
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "age",
            "refreshed_at",
        ]);

    }
    public function set()
    {
    }

    public function countAge()
    {
        $this->thing->db->setFrom($this->from);
        $thing_report = $this->thing->db->agentSearch("age", 3);
        $things = $thing_report["things"];

        $this->sms_message = "";
        $reset = false;

        if ($things == false) {
            // No age information store found.
            $this->resetCounts();
        } else {
            foreach ($things as $thing) {
                $uuid = $thing["uuid"];

                $variables_json = $thing["variables"];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if (isset($variables["age"]["mean"])) {
                    $this->age = $variables["age"]["mean"];
                }
                if (isset($variables["age"]["count"])) {
                    $this->count = $variables["age"]["count"];
                }
                if (isset($variables["age"]["sum"])) {
                    $this->sum = floatval($variables["age"]["sum"]);
                }
                if (isset($variables["age"]["sum_squared"])) {
                    $this->sum_squared = floatval(
                        $variables["age"]["sum_squared"]
                    );
                }
                if (isset($variables["age"]["sum_squared_difference"])) {
                    $this->sum_squared_difference = floatval(
                        $variables["age"]["sum_squared_difference"]
                    );
                }

                if (isset($variables["age"]["earliest"])) {
                    $this->earliest_known = strtotime(
                        $variables["age"]["earliest"]
                    );
                }

                if (
                    $this->age == false or
                    $this->count == false or
                    $this->sum == false or
                    $this->sum_squared == false or
                    $this->sum_squared_difference == false
                ) {
                    //$this->resetCounts();
                } else {
                    // Successfully loaded an age Thing

                    $this->age_thing = new Thing($uuid);

                    //$this->age_thing = $thing;
                    break;
                }

                $this->resetCounts();
            }
        }
    }

    /**
     *
     */
    function drawGraph()
    {
        $chart_agent = new Chart($this->thing, "chart age " . $this->from);
        $this->image = $chart_agent->image;
        $this->white = $chart_agent->white;
        $this->black = $chart_agent->black;
        $this->red = $chart_agent->red;
        $this->grey = $chart_agent->grey;

        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $num_points = count($this->tubs);
        $column_width = 1;
        if ($num_points !== 0) {
            $column_width = $this->width / $num_points;
        }
        $i = 0;

        $x_min = 0;
        $x_max = 1;
        $y_min = 0;
        $y_max = 1;

        foreach ($this->tubs as $x => $y) {
            if ($y == null or $y == 0) {
                continue;
            }

            // Get min and max
            if (!isset($y_min)) {
                $y_min = $y;
            }
            if (!isset($y_max)) {
                $y_max = $y;
            }

            if (!isset($x_min)) {
                $x_min = $x;
            }
            if (!isset($x_max)) {
                $x_max = $x;
            }

            if ($y < $y_min) {
                $y_min = $y;
            }
            if ($y > $y_max) {
                $y_max = $y;
            }
            $x_min = 0;

            if ($x > $x_max) {
                $x_max = $x;
            }

            $i += 1;
        }

        $this->y_max = $y_max;
        $this->y_min = $y_min;
        $this->y_min = 0; // Force 0
        $this->x_max = $x_max;
        $this->x_min = 0;

        if (!is_numeric($this->x_max) or !is_numeric($this->x_min)) {
            //$this->x_spread = 60*60*24*10;
        } else {
            $this->x_spread = $this->x_max - $this->x_min;
        }

        $this->y_spread = $this->y_max - $this->y_min;

        $i = 0;

        $i_max = $this->tubs_max;

        foreach ($this->tubs as $tub_name => $tub_quantity) {
            if ($i > $this->tubs_max) {
                break;
            }

            $elapsed_time = $tub_quantity;
            $refreshed_at = $this->tub_boundaries[$tub_name];

            $y =
                $this->y_origin +
                $this->chart_height -
                (($elapsed_time - $this->y_min) / $this->y_spread) *
                    $this->chart_height;

            $j = $this->tubs_max - $i;
            $x = 50 + ($j / $this->tubs_max) * ($this->chart_width - 50);

            $x = $this->chart_width - $x + 50;

            if (!isset($x_old)) {
                $x_old = $x;
            }
            if (!isset($y_old)) {
                $y_old = $y;
            }

            // +1 to overlap bars
            //$width = $x - $x_old;

            $width = 20;

            $offset = 1.5;

            imagefilledrectangle(
                $this->image,
                $x - $width / 2 - $offset,
                $this->height - $this->y_origin,
                $x + $width / 2 + $offset,
                $y,
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
        $inc = ($this->y_max - $this->y_min) / 5;
        $closest_distance = $this->y_max;

        foreach ($allowed_steps as $key => $step) {
            $distance = abs($inc - $step);
            if ($distance < $closest_distance) {
                $closest_distance = $distance;
                $preferred_step = $step;
            }
        }
        $this->preferred_step = $preferred_step;

        $this->drawGrid($this->y_min, $this->y_max, $preferred_step);
        $this->drawLabels();

        $chart_agent->image = $this->image;
        $chart_agent->makePNG();
        $this->image_embedded = $chart_agent->image_embedded;
    }

    public function drawLabels($tubs = null)
    {

        $y_min = 0;
        $x_min = 0;
        $i_max = $this->tubs_max;
        $i = 0;

        foreach ($this->tubs as $m => $n) {
            if ($i > $this->tubs_max) {
                break;
            }

            $j = $this->tubs_max - $i;

            $plot_x = 50 + ($j / $this->tubs_max) * ($this->chart_width - 50);
            $plot_x = $this->chart_width - $plot_x + 50;

            $x_label_offset = 5;
            $plot_x = $plot_x + $x_label_offset;
            $y_label_offset = 16;
            $plot_y = $this->height - $y_label_offset;

            $font = $this->default_font;

            $text = "x";

            $size = 10;
            $angle = 90;
            $pad = 0;

            $colour = $this->black;

            foreach ([-1, 0, 1] as $i1 => $x0) {
                foreach ([-1, 0, 1] as $j1 => $y0) {
                    if (file_exists($font)) {
                        imagettftext(
                            $this->image,
                            $size,
                            $angle,
                            $plot_x + $x0,
                            $plot_y + $y0,
                            $this->white,
                            $font,
                            $m
                        );
                    }
                }
            }

            if (file_exists($font)) {
                imagettftext(
                    $this->image,
                    $size,
                    $angle,
                    $plot_x,
                    $plot_y,
                    $colour,
                    $font,
                    $m
                );
            }
            $i = $i + 1;
        }
    }

    /**
     *
     */
    function getBalance()
    {
    }

    /**
     *
     */
    public function getData()
    {
        // Get all users records
        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(""); // Designed to accept null as $this->uuid.

        $things = $thingreport["thing"];

        if (!isset($this->points)) {
            $this->points = [];
        }
        $this->earliest_seen_population = false;

        if ($things === false) {
            return;
        }

        // Get the earliest from the current data set
        foreach ($things as $thing) {
            $created_at = strtotime($thing["created_at"]);
            $bin_sum = 0;

            $this->points[] = ["age" => $created_at, "bin_sum" => $bin_sum];
        }
    }

    /**
     *
     */
    function resetCounts()
    {
        $this->age = null;
        $this->count = 0;
        $this->sum = 0;
        $this->sum_squared = 0;
        $this->sum_squared_difference = 0;

        $this->age_thing = new Thing(null);
        $this->age_thing->Create($this->from, "age", "s/ user age");
        $this->age_thing->flagGreen();
    }

    /**
     *
     */
    function tubAge()
    {
        if (isset($this->tubs)) {
            return;
        }

        $count = null;
        if (is_array($this->points)) {
            $count = count($this->points);
        }

        $this->num_tubs = [];
        $dimension[0] = "age";
        $dimension[1] = "bin_sum";

        $this->tub_boundaries = [
            "second" => 0,
            "seconds" => 1,
            "minute" => 60,
            "minutes" => 2 * 60,
            "hour" => 60 * 60,
            "hours" => 2 * 60 * 60,
            "day" => 60 * 60 * 24,
            "days" => 2 * 60 * 60 * 24,
            "week" => 60 * 24 * 7,
            "weeks" => 60 * 60 * 24 * 7,
            "month" => 60 * 60 * 24 * 7 * 6,
            "months" => 2 * 60 * 60 * 24 * 7 * 6,
            "year" => 60 * 60 * 60 * 24 * 365,
            "years" => 2 * 60 * 60 * 60 * 24 * 365,
            "decades" => 60 * 60 * 60 * 24 * 365,
            "centuries" => 60 * 60 * 60 * 24 * 365 * 100,
        ];

        if ($this->points == []) {
            $this->x_max = 1;
            $this->x_min = 0;

            $this->y_max = 1;
            $this->y_min = 0;

            $this->y_spread = $this->y_max - $this->y_min;
            $this->x_spread = $this->x_max - $this->x_min;

            $this->num_tubs = 1;

            // Clear array
            $this->tubs = [];
        }

        foreach ($this->points as $key => $point) {
            if (!isset($x_min)) {
                $x_min = $point["age"];
            }
            if (!isset($x_max)) {
                $x_max = $point["age"];
            }

            if ($point["age"] < $x_min) {
                $x_min = $point["age"];
            }
            if ($point["age"] > $x_max) {
                $x_max = $point["age"];
            }

            if (!isset($y_min)) {
                $y_min = $point["bin_sum"];
            }
            if (!isset($y_max)) {
                $y_max = $point["bin_sum"];
            }

            if ($point["bin_sum"] < $y_min) {
                $y_min = $point["bin_sum"];
            }
            if ($point["bin_sum"] > $y_max) {
                $y_max = $point["bin_sum"];
            }
        }

        $this->x_max = $x_max;
        $this->x_min = $x_min;

        $this->y_max = $y_max;
        $this->y_min = $y_min;

        $this->y_spread = $this->y_max - $this->y_min;
        $this->x_spread = $this->x_max - $this->x_min;

        $this->num_tubs = 9;

        // Clear array
        $this->tubs = [];

        foreach ($this->points as $key => $point) {

            $x = time() - $point["age"];

            $tub_boundary_name = explode(" ", $this->thing->human_time($x))[1];

            if (!isset($this->tubs[$tub_boundary_name])) {
                $this->tubs[$tub_boundary_name] = 1;
                continue;
            }
            $this->tubs[$tub_boundary_name] += 1;
        }
        foreach ($this->tubs as $tub_name => $quantity) {
        }
    }

    /**
     *
     * @return unknown
     */
    function stackAge()
    {
        // Calculate streamed adhoc sample statistics
        // Like calculating stream statistics.
        // Keep track of counts.  And sums.  And squares of sums.
        // And sums of differences of squares.

        // Get all users records

        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(""); // Designed to accept null as $this->uuid.

        $things = $thingreport["thing"];
        $this->mean = false;
        $this->age_oldest = false;
        $this->total_things = 0;
        $this->sum = false;
        $this->sample_count = false;
        if ($things === false) {
            return;
        }

        // Get the earliest from the current data set

        $this->earliest_seen_population = false;
        foreach ($things as $thing) {
            $created_at = strtotime($thing["created_at"]);
            if (
                $created_at < $this->earliest_seen_population or
                $this->earliest_seen_population == false
            ) {
                $this->earliest_seen_population = $created_at;
            }
        }

        $this->earliest_known = $this->earliest_seen_population;

        $this->total_things = count($things);
        $this->sum = $this->sum;

        $this->sample_count = 0;
        $this->count = $this->count;

        $start_time = time();

        $variables = [];

        $this->earliest_seen_sample = false;

        shuffle($things);
        while ($this->total_things > 0) {
            $thing = array_pop($things);
            $created_at = strtotime($thing["created_at"]);

            if (
                $created_at < $this->earliest_seen_sample or
                $this->earliest_seen_sample == false
            ) {
                $this->earliest_seen_sample = $created_at;
            }

            $time_now = time();

            $variable = $time_now - $created_at; //age
            $variables[] = $variable;

            // Not because this is an age sample ignore 0 age.

            if ($variable == 0) {
                //echo "age = 0";
                continue;
            }

            if (time() - $start_time > 2) {
                $this->thing->log("Sampled for more than 2s");
                // timed out
                break;
            }

            if ($this->sample_count > $this->total_things / 4) {
                //echo " Sampled 1 in 4";
                // 20% should be enough for sampling
                break;
            }

            $this->sample_count += 1;

            $this->count += 1;
            $this->sum += $variable;
            $this->sum_squared += $variable * $variable;
        }

        // Calculate the mean
        $this->mean = $this->sum / $this->count;

        // Calculate the sum squared difference
        $this->sum_squared_difference = $this->sum_squared_difference;

        foreach ($variables as $variable) {
            $squared_difference =
                ($variable - $this->mean) * ($variable - $this->mean);
            $this->sum_squared_difference += $squared_difference;
        }

        // Calculate the variance.  Precursor to standard deviation.
        $this->variance = $this->sum_squared_difference / $this->count;

        // Calculation the standard deviation.
        $this->standard_deviation = sqrt($this->variance);

        $end_time = time();
        $this->calc_time = $end_time - $start_time;

        $this->age_oldest = time() - $this->earliest_seen_population;

        // Store counts
        $this->age_thing->db->setFrom($this->from);

        $this->age_thing->json->setField("variables");
        $this->age_thing->json->writeVariable(["age", "mean"], $this->mean);
        $this->age_thing->json->writeVariable(["age", "count"], $this->count);
        $this->age_thing->json->writeVariable(["age", "sum"], $this->sum);
        $this->age_thing->json->writeVariable(
            ["age", "sum_squared"],
            floatval($this->sum_squared)
        );
        $this->age_thing->json->writeVariable(
            ["age", "sum_squared_difference"],
            floatval($this->sum_squared_difference)
        );

        $this->age_thing->json->writeVariable(
            ["age", "earliest"],
            $this->earliest_known
        );

        $this->age_thing->flagGreen();

        return $this->mean;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        // Develop the various messages for each channel.
        $this->thing->flagGreen();

        $this->thing->json->setField("variables");

//        $this->thing_report["thing"] = $this->thing->thing;
        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
    }

    /**
     *
     */
    function makeTXT()
    {
        $txt = "This is a CHART. ";
        $txt .= "\n";

        foreach ($this->tubs as $x => $y) {
            $txt .= str_pad($x, 7, " ", STR_PAD_LEFT);
            $txt .= " ";

            $txt .= str_pad($y, 7, " ", STR_PAD_LEFT);
            $txt .= "\n";
        }

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $this->sms_message =
            "AGE = " .
            $this->thing->human_time($this->mean) .
            " to " .
            $this->thing->human_time($this->age_oldest) .
            " | " .
            $this->sms_message;

        $this->sms_message .=
            "This is the Mean to Oldest age of the Things you have deposited. | ";

        if (false) {
            $this->sms_message .=
                "OLDEST " .
                $this->thing->human_time($this->age_oldest) .
                " to " .
                $this->thing->human_time(
                    time() - $this->earliest_seen_population
                ) .
                " | ";
        }

        $this->sms_message .=
            "COUNT " . number_format($this->total_things) . " | ";

        if (false) {
            $this->sms_message .= "SUM " . number_format($this->sum) . " | ";
            $this->sms_message .=
                "SUM SQUARED " . number_format($this->sum_squared) . " | ";
            $this->sms_message .=
                "SUM SQUARED DIFFERENCE " .
                number_format($this->sum_squared_difference) .
                " | ";
        }

        $this->sms_message .= "TEXT BALANCE";

        $this->thing_report["thing"] = $this->thing->thing;
        $this->thing_report["sms"] = $this->sms_message;
    }

    /**
     *
     */
    public function readSubject()
    {
        // This is a stack generated image
        // Eventually responsive to perspective context
        $this->countAge();
        $this->stackAge();
        $this->getData();
        $this->tubAge();
    }

    /**
     *
     */
    public function makeWeb()
    {
        //$this->getData();
        $this->drawGraph();
        $link = $this->web_prefix . "thing/" . $this->uuid . "/age";

        $head = '
            <td>
            <table border="0" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF; border-bottom:0; border-radius:10px">
            <tr>
            <td align="center" valign="top">
            <div padding: 5px; text-align: center">';

        $foot = "</td></div></td></tr></tbody></table></td></tr>";

        $web = "";
        //$web = '<a href="' . $link . '">';
        $web .= $this->image_embedded;
        //$web .= "</a>";
        $web .= "<br>";
        $web .= "<p>";

        $web .= "<b>Agent Age</b>";

        $web .= "<p>";
        $web .= "<table>";
        $web .= "<th>" . "age" . "</th><th>" . "Things" . "</th>";
        foreach ($this->tubs as $tub_name => $tub_quantity) {
            $web .= "<tr>";
            $web .= "<th>" . $tub_name . "</th><th>" . $tub_quantity . "</th>";
            $web .= "</tr>";
        }

        $web .= "<th>" . "Total" . "</th><th>" . $this->total_things . "</th>";

        $web .= "</table>";
        $web .= "<p>";

        $web .= "This shows the age spread of the ";
        $web .= number_format($this->total_things) . " Things ";
        $web .= "you have deposited using the current text channel. ";
        $web .=
            'You can send the text command "FORGETALL" to forget all these Things. ';

        $web .=
            "The oldest thing is " .
            $this->thing->human_time($this->age_oldest) .
            " old. ";

        $web .= "<p>";
        $web .=
            'You can send the text command "FORGET TODAY". Or "FORGET MONTH". Or "FORGET MINUTES". This will forget Things of the specified age.';
        $web .= "<p>";
        $web .= "The privacy engine continually removes Things by algorithm.";

        $web .= "<br><br>";

        $this->thing_report["web"] = $web;
    }
}
