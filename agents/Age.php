<?php
/**
 * Age.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);ini_set('display_errors', 1);

class Age extends Chart
{


    /**
     *
     * @param Thing   $thing
     */
    function __construct(Thing $thing) {
        $this->thing = $thing;

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
        // I think.
        // Instead.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        $this->height = 200;
        $this->width = 300;

        $this->series = array('age');


        //$this->sqlresponse = null;

        $this->node_list = array("start");

        $this->thing->log( '<pre> Agent "Age" running on Thing ' .  $this->uuid .  ' </pre>' );

        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->web_prefix = $thing->container['stack']['web_prefix'];

        $this->current_time = $this->thing->json->time();


        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("age", "refreshed_at") );

        if ($time_string == false) {
            // Then this Thing has no group information
            //$this->thing->json->setField("variables");
            //$time_string = $this->thing->json->time();
            //$this->thing->json->writeVariable( array("group", "refreshed_at"), $time_string );
        }

        $this->thing->db->setFrom($this->from);
        $thing_report = $this->thing->db->agentSearch('age', 3);
        $things = $thing_report['things'];

        $this->sms_message = "";
        $reset = false;

        if ( $things == false  ) {
            // No age information store found.
            $this->resetCounts();
        } else {
            foreach ($things as $thing) {
                $uuid = $thing['uuid'];

                $variables_json= $thing['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if (isset($variables['age']['mean'])) {$this->age = $variables['age']['mean'];}
                if (isset($variables['age']['count'])) {$this->count = $variables['age']['count'];}
                if (isset($variables['age']['sum'])) {$this->sum = floatval($variables['age']['sum']);}
                if (isset($variables['age']['sum_squared'])) {$this->sum_squared = floatval($variables['age']['sum_squared']);}
                if (isset($variables['age']['sum_squared_difference'])) {$this->sum_squared_difference = floatval($variables['age']['sum_squared_difference']);}

                if (isset($variables['age']['earliest'])) {$this->earliest_known = strtotime($variables['age']['earliest']);}

                if ( ($this->age == false) or
                    ($this->count == false) or
                    ($this->sum == false) or
                    ($this->sum_squared == false) or
                    ($this->sum_squared_difference == false) ) {

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

        $this->readSubject();
        $this->respond();
    }


    /**
     *
     */
    function drawGraph() {

        $chart_agent = new Chart($this->thing, "chart age " . $this->from);
        $this->image = $chart_agent->image;
        $this->black = $chart_agent->black;
        $this->red = $chart_agent->red;
        $this->grey = $chart_agent->grey;



        $this->tubAge();


        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $num_points = count($this->tubs);
        $column_width = $this->width / $num_points;

        //        $run_time = $this->points[0]['bin_sum'];
        //$queue_time = $this->points[0]['bin_sum'];

        //$variable = $run_time;

        //        $refreshed_at = $this->points[0]['age'];

        $i = 0;

        foreach ($this->tubs as $x=>$y) {
//var_dump($x);
//var_dump($y);
            if (($y == null) or ($y == 0 )) {
                continue;
            }

            // Get min and max
            if (!isset($y_min)) {$y_min = $y; }
            if (!isset($y_max)) {$y_max = $y;}

            if (!isset($x_min)) { $x_min = $x; }
            if (!isset($x_max)) { $x_max = $x; }


            if ($y < $y_min) {$y_min = $y;}
            if ($y > $y_max) {$y_max = $y;}

            if ($x < $x_min) {$x_min = $x;}
            if ($x > $x_max) {$x_max = $x;}

            $i += 1;
        }

//$x = $this->tub_boundaries[$x];
// devstack
//var_dump($x);

        $this->y_max = $y_max;
        $this->y_min = $y_min;
        $this->y_min = 0; // Force 0
        $this->x_max = $x_max;
        $this->x_min = $x_min;

        $this->x_spread = $this->x_max - $this->x_min;
        $this->y_spread = $this->y_max - $this->y_min;

        //$x_max = strtotime($this->current_time);

        $i = 0;

        //    $this->tubAge();

        foreach ($this->tubs as $index=>$tub_quantity) {

            //echo $x_min. " " . $x_max . " " . $y_min . " " . $y_max . " " . $index . " " . $tub_quantity . " ";
            //$run_time = $point['bin_sum'];
            //$queue_time = $point['queue_time'];
            $elapsed_time = $tub_quantity;
            $refreshed_at = $index;

            $y_spread = $y_max - $y_min;
            if ($y_spread == 0) {$y_spread = 100;}

            $y_origin = 10;

            $y = $y_origin + $this->chart_height - ($elapsed_time - $y_min) / ($y_spread) * $this->chart_height;


            //$x = 50 + ($refreshed_at - $x_min) / ($x_max - $x_min) * ($this->chart_width - 50);
            $x = 50 + ($refreshed_at - $x_min) / ($x_max - $x_min) * ($this->chart_width - 50);

            $x = $this->chart_width - $x + 50;

            if (!isset($x_old)) {$x_old = $x;}
            if (!isset($y_old)) {$y_old = $y;}

            // +1 to overlap bars
            //$width = $x - $x_old;

            $width = 20;

            $offset = 1.5;

            imagefilledrectangle($this->image,
                $x - $width / 2 - $offset , $this->height - $y_origin,
                $x + $width / 2 + $offset, $y ,
                $this->red);

            $y_old = $y;
            $x_old = $x;

            $i += 1;
        }

        $allowed_steps = array(0.02, 0.05, 0.2, 0.5, 2, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000, 2000, 2500, 10000, 20000, 25000, 100000, 200000, 250000);
        $inc = ($y_max - $y_min)/ 5;

        $closest_distance = $y_max;

        foreach ($allowed_steps as $key=>$step) {

            $distance = abs($inc - $step);
            if ($distance < $closest_distance) {
                $closest_distance = $distance;
                $preferred_step = $step;
            }
        }

        $this->drawGrid($this->y_min, $this->y_max, $preferred_step);

        $chart_agent->image = $this->image;
        $chart_agent->makePNG();
        $this->image_embedded = $chart_agent->image_embedded;

    }


    /**
     *
     */
    function getBalance() {
    }


    /**
     *
     */
    public function getData() {
        // Get all users records
        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

        $things = $thingreport['thing'];

        // Get the earliest from the current data set
        $this->earliest_seen_population = false;
        foreach ($things as $thing) {

            $created_at = strtotime( $thing['created_at'] );
            $bin_sum = 0;

            $this->points[] = array("age"=>$created_at, "bin_sum"=>$bin_sum);
        }
    }


    /**
     *
     */
    function resetCounts() {
        $this->count = 0;
        $this->sum = 0;
        $this->sum_squared = 0;
        $this->sum_squared_difference = 0;

        $this->age_thing = new Thing(null);
        $this->age_thing->Create($this->from , 'age', 's/ user age');
        $this->age_thing->flagGreen();
    }


    /**
     *
     */
    function tubAge() {
        if (isset($this->tubs)) {return;}
        $count = null;
        if (is_array($this->points)) {
            $count =  count($this->points);
        }

        $this->num_tubs = array();
        $dimension[0] = "age";
        $dimension[1] = "bin_sum";

$this->tub_boundaries = array("seconds"=>60,
"minutes"=>60*60,
"days"=>60*60*24,
"weeks"=>60*60*24*7,
"months"=>60*60*24*7*6,
"years"=>60*60*60*24*365,
"decades"=>60*60*60*24*365,
"centuries"=>60*60*60*24*365*100);



        foreach ($this->points as $key=>$point) {

            if (!isset($x_min)) {$x_min = $point['age'];}
            if (!isset($x_max)) {$x_max = $point['age'];}

            if ($point['age'] < $x_min) {$x_min = $point['age'];}
            if ($point['age'] > $x_max) {$x_max = $point['age'];}

            if (!isset($y_min)) {$y_min = $point['bin_sum'];}
            if (!isset($y_max)) {$y_max = $point['bin_sum'];}

            if ($point['bin_sum'] < $y_min) {$y_min = $point['bin_sum'];}
            if ($point['bin_sum'] > $y_max) {$y_max = $point['bin_sum'];}

        }

        $this->x_max = $x_max;
        $this->x_min = $x_min;

        $this->y_max = $y_max;
        $this->y_min = $y_min;

        $this->y_spread = $this->y_max - $this->y_min;
        $this->x_spread = $this->x_max - $this->x_min;

        $this->num_tubs = 9;

        // Clear array
        $this->tubs = array();

        foreach ($this->points as $key=>$point) {

            //$spread = the distance between youngest and oldest age


foreach ($this->tub_boundaries as $tub_name=>$tub_boundary) {


if ($this->x_max > $tub_boundary) {
$tub_boundary_name = $tub_name;
}

}
//var_dump($tub_boundary_name);

//$tub_index = $tub_boundary_name;
            $tub_index = intval(($this->num_tubs - 1) * ($this->x_max - $point['age']) / $this->x_spread) + 1;

            if (!isset($this->tubs[$tub_index])) {$this->tubs[$tub_index] = 1; continue;}
            $this->tubs[$tub_index] += 1;

        }

        foreach ($this->tubs as $index=>$quantity) {
        }

    }


    /**
     *
     * @return unknown
     */
    function stackAge() {
        // Calculate streamed adhoc sample statistics
        // Like calculating stream statistics.
        // Keep track of counts.  And sums.  And squares of sums.
        // And sums of differences of squares.

        // Get all users records
        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

        $things = $thingreport['thing'];

        // Get the earliest from the current data set

        $this->earliest_seen_population = false;
        foreach ($things as $thing) {
            $created_at = strtotime( $thing['created_at'] );
            if ( ($created_at < $this->earliest_seen_population  ) or ($this->earliest_seen_population == false)  ) {

                $this->earliest_seen_population = $created_at;

            }

        }

        $this->earliest_known = $this->earliest_seen_population;

        $this->total_things = count($things);
        $this->sum = $this->sum;

        $this->sample_count = 0;
        $this->count = $this->count;

        $start_time = time();

        $variables = array();


        $this->earliest_seen_sample = false;

        shuffle($things);
        while ($this->total_things > 0) {

            $thing = array_pop($things);
            $created_at = strtotime( $thing['created_at'] );

            if ( ($created_at < $this->earliest_seen_sample  ) or ($this->earliest_seen_sample == false) ) {
                $this->earliest_seen_sample = $created_at;
            }

            $time_now = time();

            $variable = $time_now - $created_at; //age
            $variables[] = $variable;


            // Not because this is an age sample ignore 0 age.

            if ( $variable == 0 ) {
                //echo "age = 0";
                continue;
            }


            if ( (time() - $start_time) > 2) {
                $this->thing->log( "Sampled for more than 2s");
                // timed out
                break;
            }

            if ($this->sample_count > $this->total_things  / 4) {
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

            $squared_difference = ($variable -$this->mean) * ($variable - $this->mean);
            $this->sum_squared_difference += $squared_difference;

        }

        // Calculate the variance.  Precursor to standard deviation.
        $this->variance = $this->sum_squared_difference / $this->count;

        // Calculation the standard deviation.
        $this->standard_deviation = sqrt( $this->variance );

        $end_time = time();
        $this->calc_time =  $end_time-$start_time;


        $this->age_oldest = time() - $this->earliest_seen_population;

        // Store counts
        $this->age_thing->db->setFrom($this->from);

        $this->age_thing->json->setField("variables");
        $this->age_thing->json->writeVariable( array("age", "mean") , $this->mean  );
        $this->age_thing->json->writeVariable( array("age", "count") , $this->count  );
        $this->age_thing->json->writeVariable( array("age", "sum") , $this->sum );
        $this->age_thing->json->writeVariable( array("age", "sum_squared") , floatval( $this->sum_squared ) );
        $this->age_thing->json->writeVariable( array("age", "sum_squared_difference") , floatval( $this->sum_squared_difference ) );

        $this->age_thing->json->writeVariable( array("age", "earliest"), $this->earliest_known   );

        $this->age_thing->flagGreen();

        return $this->mean;
    }


    /**
     *
     * @return unknown
     */
    public function respond() {
        // Develop the various messages for each channel.
        $this->thing->flagGreen();

        $this->thing->json->setField("variables");

        $this->makePNG();

        $this->thing_report['thing'] = $this->thing->thing;
        $this->makeSms();
        $this->makeWeb();

        $this->makeTXT();

        // While we work on this
        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        return $this->thing_report;
    }


    /**
     *
     */
    function makeTXT() {
        $txt = 'This is a CHART. ';
        $txt .= "\n";

        $this->tubAge();

        foreach ($this->tubs as $x=>$y) {
//var_dump($x);
//var_dump($y);
            $txt .= str_pad($x, 7, ' ', STR_PAD_LEFT);
            $txt .= " ";

            $txt .= str_pad($y, 7, ' ', STR_PAD_LEFT);
            $txt .= "\n";
        }


        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

//exit();

    }


    /**
     *
     */
    public function makeSms() {
        $this->sms_message = "AGE = " . $this->thing->human_time ($this->mean) . " to " .
            $this->thing->human_time ( $this->age_oldest ) .  " | " . $this->sms_message;

        $this->sms_message .= "This is the Mean to Oldest age of the Things you have deposited. | ";


        if (false) {
            $this->sms_message .= "OLDEST " . $this->thing->human_time( $this->age_oldest )
                . " to " . $this->thing->human_time ( time() - $this->earliest_seen_population )  . " | ";
        }

        //$this->sms_message .= "SD " . number_format ($this->standard_deviation) . " | ";
        //$this->sms_message .= number_format( $this->sample_count ) . " Things sampled from " . number_format( $this->total_things ) . " in " . $this->calc_time . "s | ";
        $this->sms_message .= "COUNT " . number_format( $this->total_things ) . " | ";

        if (false) {
            $this->sms_message .= "SUM " . number_format( $this->sum ) . " | ";
            $this->sms_message .= "SUM SQUARED " . number_format( $this->sum_squared ) . " | ";
            $this->sms_message .= "SUM SQUARED DIFFERENCE " . number_format( $this->sum_squared_difference ) . " | ";
        }

        $this->sms_message .= 'TEXT BALANCE';

        $this->thing_report['thing'] = $this->thing->thing;
        $this->thing_report['sms'] = $this->sms_message;


    }


    /**
     *
     */
    public function readSubject() {
        // This is a stack generated image
        // Eventually responsive to perspective context
        $this->stackAge();
        $this->getData();
    }


    /**
     *
     */
    public function makeWeb() {
        //$this->getData();
        $this->drawGraph();
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/age';

        $head= '
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

        $web .= "This shows the age spread of the ";
        $web .= number_format( $this->total_things ) . " Things ";
        $web .= "you have deposited using the current text channel. ";
        $web .= 'You can send the text command "FORGETALL" to forget all these Things. ';

        $web .= "The oldest thing is " . $this->thing->human_time ( $this->age_oldest) . " old. ";

        $web .= "The privacy engine continually removes Things by algorithm.";

        $web .= "<br><br>";

        $this->thing_report['web'] = $web;
    }



}
