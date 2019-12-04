<?php
/**
 * Kaiju.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Kaiju extends Chart
{
    public $var = 'hello';

    /**
     *
     */
    public function init() {

        if ((isset($this->test_flag)) and ($this->test_flag === true)) {$this->test();}
        $this->node_list = array("kaiju"=>array("kaiju"));

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->unit = "Health";

        $this->default_state = "X";

        $this->max_words = 25;

        $this->getNuuid();

        $this->height = 200;
        $this->width = 300;

        $this->horizon = 10*24*60/15;

        $this->series = array('kaiju');


        $this->character = new Character($this->thing, "character is Kaiju");

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->variable = new Variables($this->thing, "variables kaiju " . $this->from);
    }


    /**
     *
     */
    function run() {
        $this->getAddress($this->thing->from);
        $this->getKaiju();
        /*
$this->blankImage();
        $this->drawGraph();
$this->makePNG();
*/
        //exit();
    }


    /**
     *
     * @param unknown $state (optional)
     * @return unknown
     */
    function isKaiju($state = null) {

        if ($state == null) {
            if (!isset($this->state)) {$this->state = "easy";}

            $state = $this->state;
        }

        if (($state == "easy") or ($state == "hard")
        ) {return false;}

        return true;
    }


    /**
     *
     * @return unknown
     */
    function calcdVdt() {

        if (!isset($this->points)) {return true;}

        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;


        $i = 0;
        foreach ($this->points as &$point) {


            if (!isset($refreshed_at_last)) {$refreshed_at_last = $point['refreshed_at'];}
            //$refreshed_at_last = $refreshed_at;

            $refreshed_at = $point['refreshed_at'];
            $dt = $refreshed_at_last - $refreshed_at; // Going backwards.


            if (!isset($series_1_last)) {$series_1_last = $point['series_1'];}
            $series_1 = $point['series_1'];
            $point['voltage'] = $point['series_1'];

            $dv = $series_1 - $series_1_last;



            $refreshed_at_last = $refreshed_at;
            $series_1_last = $series_1;




            if ($dt == 0) {
                $dv_dt = null;} else {
                $dv_dt = (float) $dv/$dt;
            }

            $point['dv'] = $dv;
            $point['dt'] = $dt;

            $point['dv_dt'] = $dv_dt;
            $i += 1;
        }

    }


    /**
     *
     * @return unknown
     */
    function drawGraph1() {

        if (!isset($this->points)) {return true;}

        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;

        $series_1 = $this->points[0]['series_1'];
        $series_2 = $this->points[0]['series_2'];


        $refreshed_at = $this->points[0]['refreshed_at'];

        // Get min and max
        if (!isset($y_min)) { $y_min = $series_1 + $series_2; }
        if (!isset($y_max)) {$y_max = $series_1 + $series_2;}

        if (!isset($x_min)) { $x_min = $refreshed_at; }
        if (!isset($x_max)) { $x_max = $refreshed_at; }

        $i = 0;
        foreach ($this->points as $point) {

            $series_1 = $point['series_1'];
            $queue_time = $point['series_2'];
            $elapsed_time = $series_1 + $series_2;

            $refreshed_at = $point['refreshed_at'];

            if (($elapsed_time == null) or ($elapsed_time == 0 )) {
                continue;
            }

            if ($elapsed_time < $y_min) {$y_min = $elapsed_time;}
            if ($elapsed_time > $y_max) {$y_max = $elapsed_time;}

            if ($refreshed_at < $x_min) {$x_min = $refreshed_at;}
            if ($refreshed_at > $x_max) {$x_max = $refreshed_at;}


            $i += 1;
        }

        $x_max = strtotime($this->current_time);

        $i = 0;

        foreach ($this->points as $point) {

            $series_1 = $point['series_1'];
            $series_2 = $point['series_2'];
            $elapsed_time = $series_1 + $series_2;
            $refreshed_at = $point['refreshed_at'];

            $y_spread = $y_max - $y_min;
            if ($y_spread == 0) {$y_spread = 100;$this->y_spread = $y_spread;}

            $y = 10 + $this->chart_height - ($elapsed_time - $y_min) / ($y_spread) * $this->chart_height;
            $x = 10 + ($refreshed_at - $x_min) / ($x_max - $x_min) * $this->chart_width;

            if (!isset($x_old)) {$x_old = $x;}
            if (!isset($y_old)) {$y_old = $y;}

            // +1 to overlap bars
            $width = $x - $x_old;

            $offset = 1.5;

            imagefilledrectangle($this->image,
                $x_old - $offset , $y_old - $offset,
                $x_old + $width / 2 + $offset, $y_old + $offset,
                $this->red);

            imagefilledrectangle($this->image,
                $x_old + $width / 2 - $offset, $y_old - $offset,
                $x - $width / 2 + $offset, $y + $offset ,
                $this->red);

            imagefilledrectangle($this->image,
                $x - $width / 2 - $offset , $y - $offset,
                $x + $offset, $y + $offset ,
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

        $this->drawGrid($y_min, $y_max, $preferred_step);
        return $this->image;
    }


    /**
     *
     * @return unknown
     */
    function drawGraph2() {

        if (!isset($this->points)) {return true;}

        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;

        $temperature_1 = $this->points[0]['temperature_1'];
        $temperature_2 = $this->points[0]['temperature_2'];
        $temperature_3 = $this->points[0]['temperature_3'];


        $refreshed_at = $this->points[0]['refreshed_at'];

        // Get min and max
        if (!isset($y_min)) { $y_min = min($temperature_1, $temperature_2, $temperature_3); }
        if (!isset($y_max)) {$y_max = max($temperature_1, $temperature_2, $temperature_3);}

        if (!isset($x_min)) { $x_min = $refreshed_at; }
        if (!isset($x_max)) { $x_max = $refreshed_at; }



        $i = 0;
        foreach ($this->points as $point) {

            $temperature_1 = $point['temperature_1'];
            $temperature_2 = $point['temperature_2'];
            $temperature_3 = $point['temperature_3'];


            //            $dv_dt = $point['dv_dt'];

            //            $queue_time = $point['series_2'];
            //            $elapsed_time = $series_1 + $series_2;

            $refreshed_at = $point['refreshed_at'];

            if ($temperature_1 == null) {
                continue;
            }

            if ($temperature_2 == null) {
                continue;
            }

            if ($temperature_3 == null) {
                continue;
            }


            if (min($temperature_1, $temperature_2, $temperature_3) < $y_min) {$y_min = min($temperature_1, $temperature_2, $temperature_3);}
            if (max($temperature_1, $temperature_2, $temperature_3) > $y_max) {$y_max = max($temperature_1, $temperature_2, $temperature_3);}

            if ($refreshed_at < $x_min) {$x_min = $refreshed_at;}
            if ($refreshed_at > $x_max) {$x_max = $refreshed_at;}



            $i += 1;
        }

        $x_max = strtotime($this->current_time);

        $this->y_max = $y_max;
        $this->y_min = $y_min;

        $this->x_max = $x_max;
        $this->x_min = $x_min;


        $this->drawSeries('temperature_1', 'red');
        $this->drawSeries('temperature_2', 'black', 1);
        $this->drawSeries('temperature_3', 'grey', 1);


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

        $this->drawGrid($y_min, $y_max, $preferred_step);
    }


    /**
     *
     * @return unknown
     */
    function drawGraph3() {

        if (!isset($this->points)) {return true;}

        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;
        $magnetic_field = $this->points[0]['magnetic_field'];


        $refreshed_at = $this->points[0]['refreshed_at'];

        // Get min and max
        if (!isset($y_min)) { $y_min = $magnetic_field; }
        if (!isset($y_max)) {$y_max = $magnetic_field;}

        if (!isset($x_min)) { $x_min = $refreshed_at; }
        if (!isset($x_max)) { $x_max = $refreshed_at; }



        $i = 0;
        foreach ($this->points as $point) {
            $magnetic_field = $point['magnetic_field'];
            $refreshed_at = $point['refreshed_at'];

            if ($magnetic_field == null) {
                continue;
            }



            if ($magnetic_field < $y_min) {$y_min = $magnetic_field;}
            if ($magnetic_field > $y_max) {$y_max = $magnetic_field;}

            if ($refreshed_at < $x_min) {$x_min = $refreshed_at;}
            if ($refreshed_at > $x_max) {$x_max = $refreshed_at;}


            $i += 1;
        }

        $x_max = strtotime($this->current_time);

        $this->y_max = $y_max;
        $this->y_min = $y_min;

        $this->x_max = $x_max;
        $this->x_min = $x_min;

        //return true;
        $this->drawSeries('magnetic_field');

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
        $this->drawGrid($y_min, $y_max, $preferred_step);

    }


    /**
     *
     * @return unknown
     */
    function drawGraph4() {

        $series_name = 'pressure';

        if (!isset($this->points)) {return true;}

        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;
        $magnetic_field = $this->points[0][$series_name];


        $refreshed_at = $this->points[0]['refreshed_at'];

        // Get min and max
        if (!isset($y_min)) { $y_min = $magnetic_field; }
        if (!isset($y_max)) {$y_max = $magnetic_field;}

        if (!isset($x_min)) { $x_min = $refreshed_at; }
        if (!isset($x_max)) { $x_max = $refreshed_at; }



        $i = 0;
        foreach ($this->points as $point) {
            $magnetic_field = $point[$series_name];
            $refreshed_at = $point['refreshed_at'];

            if ($magnetic_field == null) {
                continue;
            }



            if ($magnetic_field < $y_min) {$y_min = $magnetic_field;}
            if ($magnetic_field > $y_max) {$y_max = $magnetic_field;}

            if ($refreshed_at < $x_min) {$x_min = $refreshed_at;}
            if ($refreshed_at > $x_max) {$x_max = $refreshed_at;}


            $i += 1;
        }

        $x_max = strtotime($this->current_time);

        $this->y_max = $y_max;
        $this->y_min = $y_min;

        $this->x_max = $x_max;
        $this->x_min = $x_min;

        //return true;
        $this->drawSeries($series_name);

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
        $this->drawGrid($y_min, $y_max, $preferred_step);

    }



    /**
     *
     * @param unknown $series_name (optional)
     * @return unknown
     */
    function drawGraph($series_name = null) {

        //$series_name = 'dv_dt';

        if (!isset($this->points)) {return true;}

        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;
        $magnetic_field = $this->points[0][$series_name];


        $refreshed_at = $this->points[0]['refreshed_at'];

        // Get min and max
        if (!isset($y_min)) { $y_min = $magnetic_field; }
        if (!isset($y_max)) {$y_max = $magnetic_field;}

        if (!isset($x_min)) { $x_min = $refreshed_at; }
        if (!isset($x_max)) { $x_max = $refreshed_at; }



        $i = 0;
        foreach ($this->points as $point) {
            $magnetic_field = $point[$series_name];
            $refreshed_at = $point['refreshed_at'];

            if ($magnetic_field == null) {
                continue;
            }



            if ($magnetic_field < $y_min) {$y_min = $magnetic_field;}
            if ($magnetic_field > $y_max) {$y_max = $magnetic_field;}

            if ($refreshed_at < $x_min) {$x_min = $refreshed_at;}
            if ($refreshed_at > $x_max) {$x_max = $refreshed_at;}


            $i += 1;
        }

        $x_max = strtotime($this->current_time);

        $this->y_max = $y_max;
        $this->y_min = $y_min;

        $this->x_max = $x_max;
        $this->x_min = $x_min;

        //return true;
        $this->drawSeries($series_name);

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
        $this->drawGrid($y_min, $y_max, $preferred_step);

    }


    /**
     *
     * @param unknown $series_name (optional)
     * @param unknown $colour      (optional)
     * @param unknown $line_width  (optional)
     * @return unknown
     */
    public function drawSeries($series_name = null, $colour = 'red', $line_width = 1.5) {
        if ($series_name == null) {return true;}


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

            //            $temperature_2 = $point['temperature_2'];
            ///            $temperature_3 = $point['temperature_3'];


            //            $series_1 = $point['series_1'];
            //            $series_2 = $point['series_2'];

            //            $dv_dt = $point['dv_dt'];

            //            $elapsed_time = $series_1 + $series_2;
            $refreshed_at = $point['refreshed_at'];

            $y_spread = $y_max - $y_min;
            if ($y_spread == 0) {$y_spread = 100;$this->y_spread = $y_spread;}

            $y = 10 + $this->chart_height - ($series - $y_min) / ($y_spread) * $this->chart_height;
            $x = 10 + ($refreshed_at - $x_min) / ($x_max - $x_min) * $this->chart_width;

            if (!isset($x_old)) {$x_old = $x;}
            if (!isset($y_old)) {$y_old = $y;}

            // +1 to overlap bars
            $width = $x - $x_old;

            $offset = $line_width;

            imagefilledrectangle($this->image,
                $x_old - $offset , $y_old - $offset,
                $x_old + $width / 2 + $offset, $y_old + $offset,
                $this->{$colour});

            imagefilledrectangle($this->image,
                $x_old + $width / 2 - $offset, $y_old - $offset,
                $x - $width / 2 + $offset, $y + $offset ,
                $this->{$colour});

            imagefilledrectangle($this->image,
                $x - $width / 2 - $offset , $y - $offset,
                $x + $offset, $y + $offset ,
                $this->{$colour});


            $y_old = $y;
            $x_old = $x;

            $i += 1;
        }


    }


    /**
     *
     * @param unknown $requested_state (optional)
     */
    function set($requested_state = null) {
        $this->thing->json->writeVariable( array("kaiju", "inject"), $this->inject );

        $this->refreshed_at = $this->current_time;

        $this->variable->setVariable("state", $this->state);
        $this->variable->setVariable("refreshed_at", $this->current_time);

        $this->thing->log($this->agent_prefix . 'set Kaiju to ' . $this->state, "INFORMATION");
    }


    /**
     *
     */
    function get() {
        $this->previous_state = $this->variable->getVariable("state");

        $this->refreshed_at = $this->variable->getVariable("refreshed_at");

        $this->thing->log($this->agent_prefix . 'got from db ' . $this->previous_state, "INFORMATION");

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isKaiju($this->previous_state)) {
            $this->state = $this->previous_state;
        } else {
            $this->state = $this->default_state;
        }

        if ($this->state == false) {
            $this->state = $this->default_state;
        }

        $this->thing->log($this->agent_prefix . 'got a ' . strtoupper($this->state) . ' FLAG.' , "INFORMATION");

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("kaiju", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("kaiju", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->inject = $this->thing->json->readVariable( array("kaiju", "inject") );
    }


    /**
     *
     */
    function getNuuid() {
        $agent = new Nuuid($this->thing, "nuuid");
        $this->nuuid_png = $agent->PNG_embed;
    }


    /**
     *
     */
    function getUuid() {
        $agent = new Uuid($this->thing, "uuid");
        $this->uuid_png = $agent->PNG_embed;
    }


    /**
     *
     * @param unknown $text (optional)
     */
    function getQuickresponse($text = null) {
        if ($text == null) {$text = $this->web_prefix;}
        $agent = new Qr($this->thing, $text);
        $this->quick_response_png = $agent->PNG_embed;
    }


    /**
     *
     * @param unknown $state
     */
    function setState($state) {
        $this->state = "easy";
        if ((strtolower($state) == "hard") or (strtolower($state) == "easy")) {
            $this->state = $state;
        }
    }


    /**
     *
     * @return unknown
     */
    function getState() {
        if (!isset($this->state)) {$this->state = "easy";}
        return $this->state;

    }


    /**
     *
     * @param unknown $bank (optional)
     */
    function setBank($bank = null) {
        //if (($bank == "easy") or ($bank == null)) {
        //    $this->bank = "easy-a03";
        //}

        //if ($bank == "hard") {
        //    $this->bank = "hard-a05";
        //}

        //if ($bank == "16ln") {
        $this->bank = "16ln-a00";
        //}

    }


    /**
     *
     * @param unknown $librex
     * @return unknown
     */
    public function getLibrex($librex) {
        // Look up the meaning in the dictionary.
        if (($librex == "") or ($librex == " ") or ($librex == null)) {return false;}

        switch ($librex) {
        case null:
            // Drop through
        case 'kaiju':
            $file = $this->resource_path .'kaiju/kaiju.txt';
            break;
        default:
            $file = $this->resource_path . 'kaiju/kaiju.txt';
        }
        $this->librex = file_get_contents($file);


    }


    /**
     *
     * @return unknown
     */
    function getKaiju() {
        if (!isset($this->kaiju_address)) {$this->getAddress($this->thing->from);}
        if (!isset($this->kaiju_address)) {return;}

        //var_dump($this->kaiju_address);
        //exit();

        $this->kaiju_thing = new Thing(null);
        $this->kaiju_thing->Create($this->kaiju_address, "null", "s/ kaiju thing");

        $block_things = array();
        // See if a stack record exists.
        $findagent_thing = new Findagent($this->kaiju_thing, 'thing '. $this->horizon);
        $this->max_index =0;

        $match = 0;

        $link_uuids = array();
        $kaiju_messages = array();

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {

            //            $this->thing->log($block_thing['task'] . " " . $block_thing['nom_to'] . " " . $block_thing['nom_from']);
            //echo $block_thing['task'] . " " . $block_thing['nom_to'] . " " . $block_thing['nom_from'] . "\n";
            if ($block_thing['nom_from'] != $this->kaiju_address) {continue;}

            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                $link_uuids[] = $block_thing['uuid'];
                //                $kaiju_messages[] = $block_thing['task'];
                $kaiju_messages[] = $block_thing;
                //var_dump($block_thing['task']);
                // if ($match == 2) {break;}
                // Get upto 10 matches
                if ($match == $this->horizon) {break;}


            }
        }

        $this->kaiju_things = array();
        foreach ($kaiju_messages as $key=>$thing) {
            $parsed_thing = $this->parseThing($thing['task']);
            if ($parsed_thing != null) {

                $parsed_thing['created_at'] = $thing['created_at'];

                //                $parsed_thing['created_at'] = strtotime($thing['created_at']);
                $this->kaiju_things[] = $parsed_thing;


                $thing_subject= $thing['task'];

                $kaiju_array = explode("|" , $thing_subject);
                $data_array = explode(" " , $kaiju_array[1]);
                //$voltage = (float)str_replace("V","",$data_array[2]);

                $voltage = $this->parseData($data_array[2]);

                $temperature_1 = str_replace("C", "", $data_array[10]);
                $temperature_2 = str_replace("C", "", $data_array[11]);
                $temperature_3 = str_replace("C", "", $data_array[12]);


                //var_dump($parsed_thing);
                $magnetic_field_text = $parsed_thing['magnetic_field'];
                $magnetic_field = $this->parseData($magnetic_field_text)['magnetic_field'];

                $pressure_text = $parsed_thing['pressure'];
                $pressure = $this->parseData($pressure_text)['pressure'];


                //$array = array();
                //$array["refreshed_at"] = $parsed_thing['created_at'];
                //$array["series_1"] = $voltage;
                //$array["series_2"] = 0;
                //var_dump($array);


                //var_dump($data_array[2]);
                $this->points[] = array("refreshed_at"=>strtotime($parsed_thing['created_at']),
                    "series_1"=>$voltage["voltage"],
                    "series_2"=>0,
                    "temperature_1"=>$temperature_1,
                    "temperature_2"=>$temperature_2,
                    "temperature_3"=>$temperature_3,
                    "magnetic_field"=>$magnetic_field,
                    "pressure"=>$pressure,

                );
                //$this->points[] = array("refreshed_at"=>$parsed_thing['created_at'], $voltage, "series_2"=>0);

                //         $this->points[] = array("refreshed_at"=>$created_at, "run_time"=>$run_time, "queue_time"=>$queue_time);



            }

        }

        $this->kaiju_thing = $this->kaiju_things[0];
        return $this->kaiju_thing;
    }


    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function parseData($text) {

        $map = array("V" => "voltage", "Pa"=>"pressure", "uT"=>"magnetic_field", "g"=>"acceleration",
            "mm"=>"bilge");

        foreach ($map as $symbol=>$name) {

            if (strpos($text, $symbol) !== false) {
                $voltage = (float)str_replace($symbol, "", $text);
                //echo $symbol . " " . $name ." " . $voltage.  "\n";

                $a[$name] = $voltage;
                return $a;

            }
        }

        return null;

    }


    /**
     *
     * @param unknown $searchfor (optional)
     */
    function getAddress($searchfor = null) {
        $librex = "kaiju.txt";
        $this->getLibrex($librex);
        $contents = $this->librex;


        $this->kaijus = array();
        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {

            $word = $this->parseKaiju($line);
            $this->kaijus[$word['owner']] = $word;
            // do something with $line
            $line = strtok( $separator );
        }
        $kaiju_list = array();
        foreach ($this->kaijus as $kaiju_name=>$arr) {

            if ($this->thing->from == $kaiju_name) {
                $kaiju_list[] = $arr['address'];
            }
        }


        //        if ($searchfor == null) {return null;}


        $this->kaiju_address = null;
        if (count($kaiju_list) == 1) {
            $this->kaiju_address = $kaiju_list[0];
        }
        //$this->getKaiju();
        //        $this->kaiju_thing = new Thing(null);

        //        $agent_sms = new Sms($thing,"sms");

        //        $agent_sms->sendSMS($kaiju_address, "thing"

    }


    //    function getKaiju()
    //    {

    //        $agent_sms = new Sms($this->thing,"sms");

    // $agent_sms->sendSMS("XXXXXXXXXX", "thing");

    //    }

    /**
     *
     * @param unknown $test
     * @return unknown
     */
    private function parseKaiju($test) {

        if (isset($this->test_string)) {$test = $this->test_string;}

        if (mb_substr($test, 0, 1) == "#") {$word = false; return $word;}

        $dict = explode("/", $test);

        if ( (!isset($dict[1])) or (!isset($dict[2])) ) {
        }

        foreach ($dict as $index=>$phrase) {
            if ($index == 0) {continue;}
            if ($phrase == "") {continue;}
            $english_phrases[] = $phrase;
        }
        $text =  $dict[0];

        $dict = explode(",", $text);
        $kaiju_owner = $dict[0];
        $kaiju_address = trim($dict[1]);

        $parsed_line = array("owner"=>$kaiju_owner, "address"=>$kaiju_address);
        return $parsed_line;
    }


    /**
     *
     * @param unknown $test
     * @return unknown
     */
    private function parseThing($test) {
        if (mb_substr($test, 0, 1) == "#") {$word = false; return $word;}

        $dict = explode(" ", $test);
        if ( (!isset($dict[1])) or (!isset($dict[2])) or (!isset($dict[3])) ) {
            return null;
        }

        if (!isset($dict[4])) {return;}
        if (!isset($dict[5])) {return;}

        //var_dump($dict);
        //var_dump(count($dict));

        //foreach($dict as $index=>$phrase) {
        //    if ($index == 0) {continue;}
        //    if ($phrase == "") {continue;}
        //    $english_phrases[] = $phrase;
        //}
        if (count($dict) == 12) {
            $nuuid =  $dict[2];
            $kaiju_voltage =  $dict[3];
            $kaiju_temperature =  $dict[4];
            $pressure = $dict[5];
            $magnetic_field =  $dict[6];
            $vertical_acceleration =  $dict[7];
            $temperature_1 =  $dict[8];
            $temperature_2 =  $dict[9];
            $temperature_3 =  $dict[10];
            $bilge_level =  $dict[11];
            $pitch = null;
            $roll = null;
            $heading = null;
            $clock_time = null;
        }

        if (count($dict) == 13) {
            $nuuid =  $dict[2];
            $kaiju_voltage =  $dict[3];
            $kaiju_temperature =  $dict[4];
            $pressure = $dict[5];
            $magnetic_field =  $dict[6];
            $vertical_acceleration =  $dict[7];
            $temperature_1 =  $dict[8];
            $temperature_2 =  $dict[9];
            $temperature_3 =  $dict[10];
            $bilge_level =  $dict[11];
            $pitch = null;
            $roll = null;
            $heading = null;
            $clock_time = $dict[12];
        }

        if (count($dict) == 14) {
            $nuuid =  $dict[2];
            $kaiju_voltage =  $dict[3];
            $kaiju_temperature =  $dict[4];
            $pressure = $dict[5];
            $magnetic_field =  $dict[6];
            $vertical_acceleration =  $dict[7];
            $temperature_1 =  $dict[8];
            $temperature_2 =  $dict[9];
            $temperature_3 =  $dict[10];
            $bilge_level =  $dict[11];
            $pitch = null;
            $roll = null;
            $heading = null;
            $clock_time = $dict[12] . " " . $dict[13];
        }

        if (count($dict) == 15) {

            $nuuid =  $dict[2];
            $kaiju_voltage =  $dict[3];
            $kaiju_temperature =  $dict[4];
            $pressure =  $dict[5];
            $magnetic_field =  $dict[6];
            $vertical_acceleration =  $dict[7];
            $pitch =  $dict[8];
            $roll =  $dict[9];
            $heading =  $dict[10];

            $temperature_1 =  $dict[11];
            $temperature_2 =  $dict[12];
            $temperature_3 =  $dict[13];
            $bilge_level =  $dict[14];
            $clock_time =  null;
        }


        if (count($dict) == 16) {

            $nuuid =  $dict[2];
            $kaiju_voltage =  $dict[3];
            $kaiju_temperature =  $dict[4];
            $pressure =  $dict[5];
            $magnetic_field =  $dict[6];
            $vertical_acceleration =  $dict[7];
            $pitch =  $dict[8];
            $roll =  $dict[9];
            $heading =  $dict[10];

            $temperature_1 =  $dict[11];
            $temperature_2 =  $dict[12];
            $temperature_3 =  $dict[13];
            $bilge_level =  $dict[14];
            $clock_time =  $dict[15];
        }

        if (count($dict) == 17) {

            $nuuid =  $dict[2];
            $kaiju_voltage =  $dict[3];
            $kaiju_temperature =  $dict[4];
            $pressure =  $dict[5];
            $magnetic_field =  $dict[6];
            $vertical_acceleration =  $dict[7];
            $pitch =  $dict[8];
            $roll =  $dict[9];
            $heading =  $dict[10];

            $temperature_1 =  $dict[11];
            $temperature_2 =  $dict[12];
            $temperature_3 =  $dict[13];
            $bilge_level =  $dict[14];
            $clock_time =  $dict[15]. " " . $dict[16];
        }

        //        $dict = explode(",",$text);
        //        $kaiju_owner = $dict[0];
        //        $kaiju_address = trim($dict[1]);

        if (!isset($nuuid)) {var_dump($dict);}

        $parsed_line = array(
            "nuuid" =>  $nuuid,
            "kaiju_voltage" =>  $kaiju_voltage,
            "kaiju_temperature" =>  $kaiju_temperature,
            "pressure" =>  $pressure,
            "magnetic_field" =>  $magnetic_field,
            "vertical_acceleration" =>  $vertical_acceleration,
            "pitch" =>  $pitch,
            "roll" =>  $roll,
            "heading" =>  $heading,

            "temperature_1" =>  $temperature_1,
            "temperature_2" =>  $temperature_2,
            "temperature_3" =>  $temperature_3,
            "bilge_level" =>  $bilge_level,
            "clocktime" =>  $clock_time
        );

        return $parsed_line;
    }


    /**
     *
     */
    function test() {

        $this->test_string = "THING | b97f 0.00V 27.4C 100060Pa 46.22uT 0.00g 25.9C 26.6C 25.8C 516mm 1564091111";

    }


    /**
     *
     * @return unknown
     */
    function getBank() {
        if (!isset($this->bank)) {
            $this->bank = "16ln-a00";
        }

        if (isset($this->inject) and ($this->inject != false)) {
            $arr = explode("-", $this->inject);
            $this->bank = $arr[0] . "-" . $arr[1];
        }
        return $this->bank;
    }


    //    public function makeImage() {
    //       $this->image = null;

    //    }

    /**
     *
     */
    public function respond() {

        //$this->getAddress($this->thing->from);
        //$this->getKaiju();

        $this->getResponse();

        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "kaiju";


        $this->makeSMS();

        $this->makeMessage();
        // $this->makeTXT();
        $this->makeChoices();

        $this->thing_report["info"] = "This creates an exercise message.";
        $this->thing_report["help"] = 'Try CHARLEY. Or NONSENSE.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        $this->makeWeb();

        $this->makeTXT();
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create($this->agent_name, $this->node_list, "kaiju");
        $this->choices = $this->thing->choice->makeLinks('kaiju');

        $this->thing_report['choices'] = $this->choices;
    }


    /**
     *
     */
    function makeSMS() {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/kaiju.pdf';

        //        $sms = "KAIJU " . $this->inject . " | " . $link . " | " . $this->response;
        $text = "Was not found.";
        if (isset($this->kaiju_thing)) {$text = implode(" " , $this->kaiju_thing);}
        $sms = "KAIJU THING | " . $text;


        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }


    /**
     *
     */
    function makeACP125G() {
        $this->acp125g = new ACP125G($this->thing, "acp125g");
        $this->acp125g->makeACP125G($this->message);
    }


    /**
     *
     */
    function getResponse() {
        if (isset($this->response)) {return;}
    }


    /**
     *
     */
    function makeMessage() {


        if (!isset($this->sms_message)) {$this->makeSMS();}
        $message = $this->sms_message . "<br>";
        //        $uuid = $this->uuid;
        //        $message .= "<p>" . $this->web_prefix . "thing/$uuid/kaiju\n \n\n<br> ";
        $this->thing_report['message'] = $message;
    }


    /**
     *
     */
    function getBar() {
        $this->bar = new Bar($this->thing, "display");
    }


    /**
     *
     */
    function setInject() {
    }


    /**
     *
     */
    public function makePNGs() {
        //if (!isset($this->image)) {$this->makeImage();}
        $this->thing_report['pngs'] = array();
        //return;
        $agent = new Png($this->thing, "png");
        /*
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
*/
    }


    /**
     *
     */
    function makeWeb() {

        $this->node_list = array("asleep"=>array("awake", "moving"));


        $link = $this->web_prefix . 'thing/' . $this->uuid . '/kaiju';

        $this->blankImage();
        $this->drawGraph1();
        if (!isset($this->html_image)) {$this->makePNG();}
        $graph1_image_embedded = $this->image_embedded;

        $this->image = null;
        $this->makePNG();

        $this->blankImage();
        $this->drawGraph2();
        if (!isset($this->html_image)) {$this->makePNG();}
        $graph2_image_embedded = $this->image_embedded;

        $this->makePNG();


        $this->blankImage();
        $this->drawGraph3();
        //        $this->drawGraph('magnetic');

        $this->makePNG();
        //        if (!isset($this->html_image)) {$this->makePNG();}
        $graph3_image_embedded = $this->image_embedded;

        //$graph3_image_embedded = $graph2_image_embedded;

        //$this->makePNG();


        $this->blankImage();
        //        $this->drawGraph4();
        $this->drawGraph('pressure');

        $this->makePNG();
        //        if (!isset($this->html_image)) {$this->makePNG();}
        $graph4_image_embedded = $this->image_embedded;


        $this->calcDvdt();

        $this->blankImage();
        $this->drawGraph('dv_dt');
        $this->makePNG();
        //        if (!isset($this->html_image)) {$this->makePNG();}
        $graph5_image_embedded = $this->image_embedded;


        $web = "<b>Kaiju Agent</b>";
        $web .= "<p>";

        $web .= "<p>";


        //$web .= '<a href="' . $link . '">'. $this->html_image . "</a>";
        //$web .= "<br>";

        //$this->kaiju_thing

        $web .= $this->sms_message;
        $web .= "\n";

        $web .= "<p>";

        if (isset($this->kaiju_thing)) {

            $web .= "NUUID " . $this->kaiju_thing['nuuid'] . "<br>";

            $web .= "kaiju voltage " . $this->kaiju_thing['kaiju_voltage'];
            if ($this->kaiju_thing['kaiju_voltage'] < 11.50) {$web .= " WARN";}
            $web.= "<br>";

            $web .= "bilge level " . $this->kaiju_thing['bilge_level'];
            if ($this->kaiju_thing['bilge_level'] >200) {$web .= " WARN";}
            $web.= "<br>";

        }
        $web .= "<p>";


        $ago = $this->thing->human_time ( time() - strtotime( $this->thing->thing->created_at ) );

        if (isset($this->points)) {

            $txt = '<a href="' . $link . ".txt" . '">';
            $txt .= 'TEXT';
            $txt .= "</a>";

            $web .= "Kaiju report here " . $txt .".";
            $web .= "<p>";
        }

        if (isset($this->points)) {

            $web .= '<a href="' . $link . '">';
            //        $web .= $this->image_embedded;
            $web .= $graph1_image_embedded;
            $web .= "</a>";
            $web .= "<br>";

            $web .= "voltage graph";

            $web .= "<br><br>";


            $web .= '<a href="' . $link . '">';
            //        $web .= $this->image_embedded;
            $web .= $graph2_image_embedded;
            $web .= "</a>";
            $web .= "<br>";

            $web .= "temperature graph";

            $web .= "<br><br>";



            $web .= '<a href="' . $link . '">';
            //        $web .= $this->image_embedded;
            $web .= $graph3_image_embedded;
            $web .= "</a>";
            $web .= "<br>";

            $web .= "magnetic flux graph";

            $web .= "<br><br>";

            $web .= '<a href="' . $link . '">';
            //        $web .= $this->image_embedded;
            $web .= $graph4_image_embedded;
            $web .= "</a>";
            $web .= "<br>";

            $web .= "pressure graph";

            $web .= "<br><br>";


            $web .= '<a href="' . $link . '">';
            //        $web .= $this->image_embedded;
            $web .= $graph5_image_embedded;
            $web .= "</a>";
            $web .= "<br>";

            $web .= "dV/dt graph";

            $web .= "<br><br>";


        }

        $web .= "Requested about ". $ago . " ago.";
        //        $web .= "<p>";
        //        $web .= "Inject " . $this->thing->nuuid . " generated at " . $this->thing->thing->created_at. "\n";

        $togo = $this->thing->human_time($this->time_remaining);
        $web .= "This link will expire in " . $togo. ".<br>";

        $web .= "<br>";

        $privacy = '<a href="' . $this->web_prefix . "privacy" . '">';
        $privacy .= $this->web_prefix . 'privacy';
        $privacy .= "</a>";

        $web .= "This Kaiju thing is hosted by the " . ucwords($this->word) . " service.  Read the privacy policy at " . $privacy .".";

        //        $web .= "This Kaiju thing is hosted by the " . ucwords($this->word) . " service.  Read the privacy policy at " . $this->web_prefix . "privacy";
        $web .= "<br>";

        $this->thing_report['web'] = $web;


    }


    /**
     *
     */
    function makeTXT() {
        $txt = "Kaiju traffics.\n";
        $txt .= 'Duplicate messages may exist. Can you de-duplicate?';
        $txt .= "\n";

        if (!isset($this->sms_message)) {$this->makeSMS();}

        $txt .= $this->sms_message;

        $txt .= "\n\n";

        $txt .= "Full log follows.\n";

        if (isset($this->kaiju_things)) {

            foreach ($this->kaiju_things as $key=>$thing) {

                $flat_thing = implode($thing, " ");
                //            if ($parsed_thing != null) {
                //                $txt .= $parsed_thing['created_at'] . "\n";
                $txt .=  $flat_thing . "\n";
                //            }

            }

        }

        $txt .= "\n\n";

        $txt .= "dv/dt test.\n";

        $this->calcDvdt();
        if (isset($this->points)) {

            foreach ($this->points as $key=>$point) {

                //$time_text = echo date('m/d/Y H:i', $point['refreshed_at']);
                $time_text = date('H:i', $point['refreshed_at']);
                $date_text = date('m/d/Y', $point['refreshed_at']);

                if (!isset($date_text_last)) {$date_text_last = $date_text;}
                if ($date_text != $date_text_last) {$txt .= $date_text . "\n";}

                $txt .=  $time_text ." V " . number_format($point['voltage'], 2)  . "V dV " . number_format($point['dv'], 2) . "V dt " . $point['dt']. "s dv/dt " .number_format($point['dv_dt'], 6) . "\n";

                $date_text_last = $date_text;

            }

        }



        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractNuuid($input) {
        if (!isset($this->duplicables)) {
            $this->duplicables = array();
        }

        return $this->duplicables;
    }


    /**
     *
     */
    public function makeKaiju() {
        //        $this->makePDF();
        //        $this->thing_report['percs'] = $this->thing_report['pdf'];
    }



    /**
     *
     */
    public function readSubject() {



        //        if (!$this->getMember()) {$this->response = "Generated an inject.";}

        $input = strtolower($this->subject);
        if ((isset($this->test_flag)) and ($this->test_flag === true)) {$input = $this->test_string;}

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            if ($input == 'kaiju') {

                //              $this->getMessage();

                //                if ((!isset($this->index)) or
                //                    ($this->index == null)) {
                //                    $this->index = 1;
                //                }
                return;
            }
        }

        $keywords = array("test", "kaiju", "hard", "easy", "hey", "on", "off");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {

                    case 'hard':
                    case 'easy':
                        $this->setState($piece);
                        $this->setBank($piece);

                        //                        $this->getMessage();
                        $this->response .= " Set messages to " . strtoupper($this->state) .".";

                        return;

                    case 'hey':

                        return;

                    case 'test':
                        $this->test_flag = true;
                        $this->test();
                        $l = $this->parseThing($this->test_string);
                        var_dump($l);
                        return;

                    case 'on':
                    default:
                    }
                }
            }
        }

        //        $this->getMessage();

        if ((!isset($this->index)) or
            ($this->index == null)) {
            $this->index = 1;
        }
    }


}
