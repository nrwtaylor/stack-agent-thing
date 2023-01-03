<?php
/**
 * Kaiju.php
 *
 * @package default
 */

// An example of a custom agent.

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Kaiju extends Agent
{
    public $var = "hello";

    /**
     *
     */
    public function init()
    {
        if (isset($this->test_flag) and $this->test_flag === true) {
            $this->test();
        }
        $this->node_list = ["kaiju" => ["kaiju"]];

        //        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->unit = "Health";

        $this->default_state = "X";

        $this->max_words = 25;

        $this->getNuuid();

        $this->height = 200;
        $this->width = 300;

        $this->default_horizon = 90 * 24 * 60 * 10; // things.
        $this->horizon = $this->default_horizon;

        $this->series = ["kaiju"];
        $this->default_y_spread = 100;

        $this->default_preferred_step = 20;

        $this->parameters = [
            "kaiju_voltage" => ["text" => "voltage (V)"],
            "bilge_level" => ["text" => "bilge level (mm)"],
            "magnetic_field" => ["text" => "magnetic flux (uT)"],
            "pressure" => ["text" => "pressure (mBar)"],
            "dv_dt" => ["text" => "dV/dt graph (V/s)"],
            "vertical_acceleration" => ["text" => "Vertical acceleration (g)"],
            "kaiju_temperature" => ["text" => "Kaiju temperature (C)"],
            "roll" => ["text" => "roll (degrees)"],
            "pitch" => ["text" => "pitch (degrees)"],
            "heading" => ["text" => "heading (degrees)"],
        ];

        $this->character = new Character($this->thing, "character is Kaiju");

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->variable = new Variables(
            $this->thing,
            "variables kaiju " . $this->from
        );
    }

    /**
     *
     */
    function run()
    {
        $this->getAddress($this->thing->from);
        $this->getKaiju();
    }

    /**
     *
     * @param unknown $state (optional)
     * @return unknown
     */
    function isKaiju($state = null)
    {
        if ($state == null) {
            if (!isset($this->state)) {
                $this->state = "simple";
            }

            $state = $this->state;
        }

        if ($state == "simple" or $state == "full") {
            return false;
        }

        return true;
    }

    function calcdVdt()
    {
        if (!isset($this->points)) {
            return true;
        }

        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $this->chart_agent->chart_width = $this->chart_width;
        $this->chart_agent->chart_height = $this->chart_height;

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;

        $i = 0;
        foreach ($this->points as &$point) {
            if (!isset($refreshed_at_last)) {
                $refreshed_at_last = $point["refreshed_at"];
            }
            //$refreshed_at_last = $refreshed_at;

            $refreshed_at = $point["refreshed_at"];
            $dt = $refreshed_at_last - $refreshed_at; // Going backwards.

            if (!isset($series_1_last)) {
                $series_1_last = $point["series_1"];
            }
            $series_1 = $point["series_1"];
            $point["voltage"] = $point["series_1"];

            $dv = $series_1 - $series_1_last;

            $refreshed_at_last = $refreshed_at;
            $series_1_last = $series_1;

            if ($dt == 0) {
                $dv_dt = null;
            } else {
                $dv_dt = (float) $dv / $dt;
            }

            $point["dv"] = $dv;
            $point["dt"] = $dt;

            $point["dv_dt"] = $dv_dt;
            $i += 1;
        }
    }

    // devstack
    // move to Chart?
    // Refactor as multiple signal graph
    function drawGraph2()
    {
        if (!isset($this->points)) {
            return true;
        }

        $this->chart_agent = new Chart(
            $this->thing,
            "chart age " . $this->from
        );
        //$this->chart_agent->blankImage();

        $this->image = $this->chart_agent->image;
        $this->black = $this->chart_agent->black;
        $this->red = $this->chart_agent->red;
        $this->grey = $this->chart_agent->grey;

        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $this->chart_agent->chart_width = $this->chart_width;
        $this->chart_agent->chart_height = $this->chart_height;

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;

        $temperature_1 = $this->points[0]["temperature_1"];
        $temperature_2 = $this->points[0]["temperature_2"];
        $temperature_3 = $this->points[0]["temperature_3"];

        $refreshed_at = $this->points[0]["refreshed_at"];

        // Get min and max
        if (!isset($y_min)) {
            $y_min = min($temperature_1, $temperature_2, $temperature_3);
        }
        if (!isset($y_max)) {
            $y_max = max($temperature_1, $temperature_2, $temperature_3);
        }

        if (!isset($x_min)) {
            $x_min = $refreshed_at;
        }
        if (!isset($x_max)) {
            $x_max = $refreshed_at;
        }

        $i = 0;
        foreach ($this->points as $point) {
            $temperature_1 = $point["temperature_1"];
            $temperature_2 = $point["temperature_2"];
            $temperature_3 = $point["temperature_3"];

            //            $dv_dt = $point['dv_dt'];

            //            $queue_time = $point['series_2'];
            //            $elapsed_time = $series_1 + $series_2;

            $refreshed_at = $point["refreshed_at"];

            if ($temperature_1 == null) {
                continue;
            }

            if ($temperature_2 == null) {
                continue;
            }

            if ($temperature_3 == null) {
                continue;
            }

            if (min($temperature_1, $temperature_2, $temperature_3) < $y_min) {
                $y_min = min($temperature_1, $temperature_2, $temperature_3);
            }
            if (max($temperature_1, $temperature_2, $temperature_3) > $y_max) {
                $y_max = max($temperature_1, $temperature_2, $temperature_3);
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

        $this->y_max = $y_max;
        $this->y_min = $y_min;

        $this->x_max = $x_max;
        $this->x_min = $x_min;

        $this->chart_agent->y_min = $y_min;
        $this->chart_agent->y_max = $y_max;
        $this->chart_agent->x_min = $x_min;
        $this->chart_agent->x_max = $x_max;

        $this->drawSeries("temperature_1", "red");
        $this->drawSeries("temperature_2", "black", 1);
        $this->drawSeries("temperature_3", "grey", 1);

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

        $this->chart_agent->drawGrid($y_min, $y_max, $preferred_step);

        $this->chart_agent->image = $this->image;
        $this->chart_agent->makePNG();
        $this->image_embedded = $this->chart_agent->image_embedded;
        $this->image = $this->chart_agent->image;
        //        $this->html_image = $this->chart_agent->html_image;
    }

    function drawGraph($series_name = null)
    {
        //$series_name = 'dv_dt';

        if (!isset($this->points)) {
            return true;
        }

        $this->chart_agent = new Chart(
            $this->thing,
            "chart age " . $this->from
        );
        //$this->chart_agent->blankImage();
        $this->image = $this->chart_agent->image;
        $this->black = $this->chart_agent->black;
        $this->red = $this->chart_agent->red;
        $this->grey = $this->chart_agent->grey;

        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $this->chart_agent->chart_width = $this->chart_width;
        $this->chart_agent->chart_height = $this->chart_height;

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;
        $magnetic_field = $this->points[0][$series_name];

        $refreshed_at = $this->points[0]["refreshed_at"];

        // Get min and max
        if (!isset($y_min)) {
            $y_min = $magnetic_field;
        }
        if (!isset($y_max)) {
            $y_max = $magnetic_field;
        }

        if (!isset($x_min)) {
            $x_min = $refreshed_at;
        }
        if (!isset($x_max)) {
            $x_max = $refreshed_at;
        }

        $i = 0;
        foreach ($this->points as $point) {
            $magnetic_field = $point[$series_name];

            $refreshed_at = $point["refreshed_at"];

            if ($magnetic_field == null) {
                continue;
            }

            if ($magnetic_field < $y_min) {
                $y_min = $magnetic_field;
            }
            if ($magnetic_field > $y_max) {
                $y_max = $magnetic_field;
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

        $this->y_max = $y_max;
        $this->y_min = $y_min;

        $this->x_max = $x_max;
        $this->x_min = $x_min;

        $this->chart_agent->y_min = $y_min;
        $this->chart_agent->y_max = $y_max;
        $this->chart_agent->x_min = $x_min;
        $this->chart_agent->x_max = $x_max;

        //return true;
        $this->drawSeries($series_name);

        $allowed_steps = [
            0.0001,
            0.0002,
            0.0005,
            0.001,
            0.002,
            0.005,
            0.01,
            0.02,
            0.05,
            0.1,
            0.2,
            0.5,
            1,
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

        $preferred_step = $this->default_preferred_step;
        foreach ($allowed_steps as $key => $step) {
            $distance = abs($inc - $step);
            if ($distance < $closest_distance) {
                $closest_distance = $distance;
                $preferred_step = $step;
            }
        }
        $this->chart_agent->drawGrid($y_min, $y_max, $preferred_step);

        $this->chart_agent->image = $this->image;
        $this->chart_agent->makePNG();
        $this->image_embedded = $this->chart_agent->image_embedded;
        $this->image = $this->chart_agent->image;
        //        $this->html_image = $this->chart_agent->html_image;
    }

    public function drawSeries(
        $series_name = null,
        $colour = "red",
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

            //            $temperature_2 = $point['temperature_2'];
            ///            $temperature_3 = $point['temperature_3'];

            //            $series_1 = $point['series_1'];
            //            $series_2 = $point['series_2'];

            //            $dv_dt = $point['dv_dt'];

            //            $elapsed_time = $series_1 + $series_2;
            $refreshed_at = $point["refreshed_at"];

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
                $this->chart_agent->image,
                $x_old - $offset,
                $y_old - $offset,
                $x_old + $width / 2 + $offset,
                $y_old + $offset,
                $this->{$colour}
            );

            imagefilledrectangle(
                $this->chart_agent->image,
                $x_old + $width / 2 - $offset,
                $y_old - $offset,
                $x - $width / 2 + $offset,
                $y + $offset,
                $this->{$colour}
            );

            imagefilledrectangle(
                $this->chart_agent->image,
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
     * @param unknown $requested_state (optional)
     */
    function set($requested_state = null)
    {
        $this->thing->Write(["kaiju", "inject"], $this->inject);

        $this->refreshed_at = $this->current_time;

        $this->variable->setVariable("state", $this->state);
        $this->variable->setVariable("refreshed_at", $this->current_time);

        $this->thing->log(
            $this->agent_prefix . "set Kaiju to " . $this->state,
            "INFORMATION"
        );
    }

    /**
     *
     */
    function get()
    {
        $this->previous_state = $this->variable->getVariable("state");

        $this->refreshed_at = $this->variable->getVariable("refreshed_at");

        $this->thing->log(
            $this->agent_prefix . "got from db " . $this->previous_state,
            "INFORMATION"
        );

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

        $this->thing->log(
            $this->agent_prefix .
                "got a " .
                strtoupper($this->state) .
                " FLAG.",
            "INFORMATION"
        );

        $time_string = $this->thing->Read(["kaiju", "refreshed_at"]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(["kaiju", "refreshed_at"], $time_string);
        }

        $this->refreshed_at = strtotime($time_string);

        $this->inject = $this->thing->Read(["kaiju", "inject"]);
    }

    /**
     *
     */
    function getNuuid()
    {
        $agent = new Nuuid($this->thing, "nuuid");
        $this->nuuid_png = $agent->PNG_embed;
    }

    /**
     *
     */
    function getUuid()
    {
        $agent = new Uuid($this->thing, "uuid");
        $this->uuid_png = $agent->PNG_embed;
    }

    /**
     *
     * @param unknown $librex
     * @return unknown
     */
    public function getLibrex($librex)
    {
        // Look up the meaning in the dictionary.
        if ($librex == "" or $librex == " " or $librex == null) {
            return false;
        }

        switch ($librex) {
            case null:
            // Drop through
            case "kaiju":
                $file = $this->resource_path . "kaiju/kaiju.txt";
                break;
            default:
                $file = $this->resource_path . "kaiju/kaiju.txt";
        }
        $this->librex = file_get_contents($file);
    }

    /**
     *
     * @return unknown
     */
    function getKaiju()
    {
        if (!isset($this->kaiju_address)) {
            $this->getAddress($this->thing->from);
        }
        if (!isset($this->kaiju_address)) {
            return;
        }

        $this->kaiju_thing = new Thing(null);
        $this->kaiju_thing->Create(
            $this->kaiju_address,
            "null",
            "s/ kaiju thing"
        );

        $block_things = [];
        // See if a stack record exists.

        //$horizon_days = $this->horizon / (24 * 60 * 60);
        $this->response .= "Saw horizon is " . $this->textNumber($this->horizon) . " things. ";

        $findagent_thing = new Findagent(
            $this->kaiju_thing,
            "kaiju " . $this->horizon
        );
        $things = $findagent_thing->thing_report["things"];

        if (!is_array($things)) {
            return;
        }

        $count = count($things);

        $this->max_index = 0;

        $match = 0;

        $link_uuids = [];
        $kaiju_messages = [];

        foreach ($things as $block_thing) {
            if ($block_thing["nom_from"] != $this->kaiju_address) {
                continue;
            }

            if ($block_thing["nom_to"] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing["uuid"];
                $link_uuids[] = $block_thing["uuid"];

                $kaiju_messages[] = $block_thing;

                if ($match == $this->horizon) {
                    break;
                }
            }
        }

        $kaiju_messages_count = count($kaiju_messages);

        $this->kaiju_things = [];
        $parse_count = 0;

        foreach ($kaiju_messages as $key => $thing) {
            $parsed_thing = $this->parseThing($thing["task"]);
            if ($parsed_thing != null) {
                $parsed_thing["created_at"] = $thing["created_at"];

                $this->kaiju_things[] = $parsed_thing;

                $thing_subject = $thing["task"];

                $kaiju_array = explode("|", $thing_subject);
                $data_array = explode(" ", $kaiju_array[1]);

                $voltage = $this->parseData($data_array[2]);

                $temperature_1 = str_replace("C", "", $data_array[10]);
                $temperature_2 = str_replace("C", "", $data_array[11]);
                $temperature_3 = str_replace("C", "", $data_array[12]);

                $magnetic_field_text = $parsed_thing["magnetic_field"];
                $magnetic_field = $this->parseData($magnetic_field_text)[
                    "magnetic_field"
                ];

                $pressure_text = $parsed_thing["pressure"];
                $pressure = $this->parseData($pressure_text)["pressure"];

                $bilge_level_text = $parsed_thing["bilge_level"];
                $bilge_level = $this->parseData($bilge_level_text)["bilge"];

                $vertical_acceleration_text =
                    $parsed_thing["vertical_acceleration"];
                $vertical_acceleration = $this->parseData(
                    $vertical_acceleration_text
                )["vertical_acceleration"];

                $kaiju_temperature_text = $parsed_thing["kaiju_temperature"];
                $kaiju_temperature_text = str_replace(
                    "C",
                    "",
                    $kaiju_temperature_text
                );

                $kaiju_temperature = floatval($kaiju_temperature_text);

                $roll_text = $parsed_thing["roll"];
                $roll = $roll_text;

                $pitch_text = $parsed_thing["pitch"];
                $pitch = $pitch_text;

                $heading_text = $parsed_thing["heading"];
                $heading_text = str_replace("M", "", $heading_text);

                $heading = $heading_text;

                $point = [
                    "refreshed_at" => strtotime($parsed_thing["created_at"]),
                    "series_1" => $voltage["voltage"],
                    "series_2" => 0,
                    "temperature_1" => $temperature_1,
                    "temperature_2" => $temperature_2,
                    "temperature_3" => $temperature_3,
                    "magnetic_field" => $magnetic_field,
                    "pressure" => $pressure,
                ];

                $point["kaiju_voltage"] = $voltage["voltage"];

                $point["bilge_level"] = $bilge_level;
                $point["vertical_acceleration"] = $vertical_acceleration;
                $point["kaiju_temperature"] = $kaiju_temperature;
                $point["roll"] = $roll;
                $point["pitch"] = $pitch;
                $point["heading"] = $heading;

                $this->points[] = $point;
                if (!isset($earliest_kaiju_thing["refreshed_at"])) {
                    $earliest_kaiju_thing = $point;
                }
                if (
                    $point["refreshed_at"] <
                    $earliest_kaiju_thing["refreshed_at"]
                ) {
                    $earliest_kaiju_thing = $point;
                }

                $parse_count += 1;
            }
        }

        $this->response .=
            "Parsed " .
            $this->textNumber($parse_count) .
            " of " .
            $this->textNumber($kaiju_messages_count) .
            " kaiju messages. ";

        $epoch = $earliest_kaiju_thing["refreshed_at"];
        $datum = new \DateTime(); // convert UNIX timestamp to PHP DateTime
        $datum->setTimestamp($epoch);

        $t = $this->timestampTime($datum);

        $this->response .= "Earliest seen kaiju thing is " . trim($t) . ". ";

        $this->kaiju_thing = $this->kaiju_things[0];
        return $this->kaiju_thing;
    }

    function parseData($text)
    {
        $map = [
            "V" => "voltage",
            "Pa" => "pressure",
            "uT" => "magnetic_field",
            "g" => "vertical_acceleration",
            "mm" => "bilge",
        ];

        foreach ($map as $symbol => $name) {
            if (strpos($text, $symbol) !== false) {
                $voltage = (float) str_replace($symbol, "", $text);

                $a[$name] = $voltage;
                return $a;
            }
        }

        return null;
    }

    public function getAddress($searchfor = null)
    {
        $this->addressKaiju($searchfor);
    }
    /**
     *
     * @param unknown $searchfor (optional)
     */
    public function addressKaiju($searchfor = null)
    {
        $librex = "kaiju.txt";
        $this->getLibrex($librex);
        $contents = $this->librex;

        $this->kaijus = [];
        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            $word = $this->parseKaiju($line);
            $this->kaijus[$word["owner"]] = $word;
            // do something with $line
            $line = strtok($separator);
        }
        $kaiju_list = [];
        foreach ($this->kaijus as $kaiju_name => $arr) {
            if ($this->thing->from == $kaiju_name) {
                $kaiju_list[] = $arr["address"];
            }
        }

        $address = null;
        $this->kaiju_address = null;
        if (count($kaiju_list) == 1) {
            $this->kaiju_address = $kaiju_list[0];
            $address = $this->kaiju_address;
        }

        return $address;
    }

    /**
     *
     * @param unknown $Wtest
     * @return unknown
     */
    private function parseKaiju($test)
    {
        if (isset($this->test_string)) {
            $test = $this->test_string;
        }

        if (mb_substr($test, 0, 1) == "#") {
            $word = false;
            return $word;
        }

        $dict = explode("/", $test);

        if (!isset($dict[1]) or !isset($dict[2])) {
        }

        foreach ($dict as $index => $phrase) {
            if ($index == 0) {
                continue;
            }
            if ($phrase == "") {
                continue;
            }
            $english_phrases[] = $phrase;
        }
        $text = $dict[0];

        $dict = explode(",", $text);
        $kaiju_owner = $dict[0];
        $kaiju_address = trim($dict[1]);

        $parsed_line = ["owner" => $kaiju_owner, "address" => $kaiju_address];
        return $parsed_line;
    }

    /**
     *
     * @param unknown $test
     * @return unknown
     */
    private function parseThing($test)
    {
        if (mb_substr($test, 0, 1) == "#") {
            $word = false;
            return $word;
        }

        $dict = explode(" ", $test);
        if (!isset($dict[1]) or !isset($dict[2]) or !isset($dict[3])) {
            return null;
        }

        if (!isset($dict[4])) {
            return;
        }
        if (!isset($dict[5])) {
            return;
        }

        if (count($dict) == 12) {
            $nuuid = $dict[2];
            $kaiju_voltage = $dict[3];
            $kaiju_temperature = $dict[4];
            $pressure = $dict[5];
            $magnetic_field = $dict[6];
            $vertical_acceleration = $dict[7];
            $temperature_1 = $dict[8];
            $temperature_2 = $dict[9];
            $temperature_3 = $dict[10];
            $bilge_level = $dict[11];
            $pitch = null;
            $roll = null;
            $heading = null;
            $clock_time = null;
        }

        if (count($dict) == 13) {
            $nuuid = $dict[2];
            $kaiju_voltage = $dict[3];
            $kaiju_temperature = $dict[4];
            $pressure = $dict[5];
            $magnetic_field = $dict[6];
            $vertical_acceleration = $dict[7];
            $temperature_1 = $dict[8];
            $temperature_2 = $dict[9];
            $temperature_3 = $dict[10];
            $bilge_level = $dict[11];
            $pitch = null;
            $roll = null;
            $heading = null;
            $clock_time = $dict[12];
        }

        if (count($dict) == 14) {
            $nuuid = $dict[2];
            $kaiju_voltage = $dict[3];
            $kaiju_temperature = $dict[4];
            $pressure = $dict[5];
            $magnetic_field = $dict[6];
            $vertical_acceleration = $dict[7];
            $temperature_1 = $dict[8];
            $temperature_2 = $dict[9];
            $temperature_3 = $dict[10];
            $bilge_level = $dict[11];
            $pitch = null;
            $roll = null;
            $heading = null;
            $clock_time = $dict[12] . " " . $dict[13];
        }

        if (count($dict) == 15) {
            $nuuid = $dict[2];
            $kaiju_voltage = $dict[3];
            $kaiju_temperature = $dict[4];
            $pressure = $dict[5];
            $magnetic_field = $dict[6];
            $vertical_acceleration = $dict[7];
            $pitch = $dict[8];
            $roll = $dict[9];
            $heading = $dict[10];

            $temperature_1 = $dict[11];
            $temperature_2 = $dict[12];
            $temperature_3 = $dict[13];
            $bilge_level = $dict[14];
            $clock_time = null;
        }

        if (count($dict) == 16) {
            $nuuid = $dict[2];
            $kaiju_voltage = $dict[3];
            $kaiju_temperature = $dict[4];
            $pressure = $dict[5];
            $magnetic_field = $dict[6];
            $vertical_acceleration = $dict[7];
            $pitch = $dict[8];
            $roll = $dict[9];
            $heading = $dict[10];

            $temperature_1 = $dict[11];
            $temperature_2 = $dict[12];
            $temperature_3 = $dict[13];
            $bilge_level = $dict[14];
            $clock_time = $dict[15];
        }

        if (count($dict) == 17) {
            $nuuid = $dict[2];
            $kaiju_voltage = $dict[3];
            $kaiju_temperature = $dict[4];
            $pressure = $dict[5];
            $magnetic_field = $dict[6];
            $vertical_acceleration = $dict[7];
            $pitch = $dict[8];
            $roll = $dict[9];
            $heading = $dict[10];

            $temperature_1 = $dict[11];
            $temperature_2 = $dict[12];
            $temperature_3 = $dict[13];
            $bilge_level = $dict[14];
            $clock_time = $dict[15] . " " . $dict[16];
        }

        $parsed_line = [
            "nuuid" => $nuuid,
            "kaiju_voltage" => $kaiju_voltage,
            "kaiju_temperature" => $kaiju_temperature,
            "pressure" => $pressure,
            "magnetic_field" => $magnetic_field,
            "vertical_acceleration" => $vertical_acceleration,
            "pitch" => $pitch,
            "roll" => $roll,
            "heading" => $heading,
            "temperature_1" => $temperature_1,
            "temperature_2" => $temperature_2,
            "temperature_3" => $temperature_3,
            "bilge_level" => $bilge_level,
            "clocktime" => $clock_time,
        ];

        return $parsed_line;
    }

    function test()
    {
        $this->test_string =
            "THING | b97f 0.00V 27.4C 100060Pa 46.22uT 0.00g 25.9C 26.6C 25.8C 516mm 1564091111";
    }

    /**
     *
     */
    public function respondResponse()
    {
        $this->getResponse();

        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["info"] = "This reads a Thing message.";
        $this->thing_report["help"] = "Try KAIJU 10. Or KAIJU 4.";

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    /**
     *
     */
    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "kaiju"
        );
        $this->choices = $this->thing->choice->makeLinks("kaiju");

        $this->thing_report["choices"] = $this->choices;
    }

    /**
     *
     */
    function makeSMS()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/kaiju.pdf";

        //        $sms = "KAIJU " . $this->inject . " | " . $link . " | " . $this->response;
        $text = "Was not found.";
        if (isset($this->kaiju_thing)) {
            if (is_string($this->kaiju_thing)) {$text = $this->kaiju_thing;}
            if (is_array($this->kaiju_thing)) {
            $text = implode(" ", $this->kaiju_thing);
            }
        }
        $sms = "KAIJU THING | " . $text;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */
    function getResponse()
    {
        if (isset($this->response)) {
            return;
        }
    }

    /**
     *
     */
    function makeMessage()
    {
        if (!isset($this->sms_message)) {
            $this->makeSMS();
        }
        $message = $this->sms_message . "<br>";
        //        $uuid = $this->uuid;
        //        $message .= "<p>" . $this->web_prefix . "thing/$uuid/kaiju\n \n\n<br> ";
        $this->thing_report["message"] = $message;
    }

    /**
     *
     */
    public function makePNGs()
    {
        $this->thing_report["pngs"] = [];
        $agent = new Png($this->thing, "png");
    }

    /**
     *
     */
    function makeWeb()
    {
        $this->node_list = ["asleep" => ["awake", "moving"]];

        $link = $this->web_prefix . "thing/" . $this->uuid . "/kaiju";

        $this->drawGraph2();
        $graph2_image_embedded = $this->chart_agent->image_embedded;

        $this->makePNG();

        $this->calcDvdt();

        //$this->blankImage();

        $parameters = $this->parameters;

        $title = [];
        foreach ($parameters as $parameter => $text) {
            $this->drawGraph($parameter);
            $this->makePNG();


if (isset($graph_image_embedded[$parameter])) {
            $graph_image_embedded[$parameter] =
                $this->chart_agent->image_embedded;
}
            $title[$parameter] = $text["text"];
        }

        $web = "<b>Kaiju Agent</b>";
        $web .= "<p>";

        $web .= "<p>";

        //$web .= '<a href="' . $link . '">'. $this->html_image . "</a>";
        //$web .= "<br>";

        //$this->kaiju_thing

        $web .= $this->sms_message;
        $web .= "\n";

        $web .= "<p>";

        if ((isset($this->kaiju_thing)) and (is_array($this->kaiju_thing))) {
            $web .= "NUUID " . $this->kaiju_thing["nuuid"] . "<br>";

            $web .= "kaiju voltage " . $this->kaiju_thing["kaiju_voltage"];
            if ($this->kaiju_thing["kaiju_voltage"] < 11.5) {
                $web .= " WARN";
            }
            $web .= "<br>";

            $web .= "bilge level " . $this->kaiju_thing["bilge_level"];
            if ($this->kaiju_thing["bilge_level"] > 200) {
                $web .= " WARN";
            }
            $web .= "<br>";
        }
        $web .= "<p>";

        $ago = false;

if (isset($this->thing-thing->created_at)) {
        $ago = $this->thing->human_time(
            time() - strtotime($this->thing->thing->created_at)
        );
}

        if (isset($this->points)) {
            $txt = '<a href="' . $link . ".txt" . '">';
            $txt .= "TEXT";
            $txt .= "</a>";

            $web .= "Kaiju report here " . $txt . ".";
            $web .= "<p>";
        }

        if (isset($this->points)) {
            $web .= '<a href="' . $link . '">';
            $web .= $graph2_image_embedded;
            $web .= "</a>";
            $web .= "<br>";

            $web .= "temperature graph (C)";

            $web .= "<br><br>";

            foreach ($parameters as $parameter => $meta) {
                $web .= '<a href="' . $link . '">';
                $web .= $graph_image_embedded[$parameter];
                $web .= "</a>";
                $web .= "<br>";

                $web .= $meta["text"];

                $web .= "<br><br>";
            }
        }

        $web .= "Requested about " . $ago . " ago. ";

        $togo = $this->thing->human_time($this->time_remaining);
        $web .= "This link will expire in " . $togo . ".<br>";

        $web .= "<br>";

        $privacy = '<a href="' . $this->web_prefix . "privacy" . '">';
        $privacy .= $this->web_prefix . "privacy";
        $privacy .= "</a>";

        $web .=
            "This Kaiju thing is hosted by the " .
            ucwords($this->word) .
            " service.  Read the privacy policy at " .
            $privacy .
            ".";

        //        $web .= "This Kaiju thing is hosted by the " . ucwords($this->word) . " service.  Read the privacy policy at " . $this->web_prefix . "privacy";
        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    /**
     *
     */
    function makeTXT()
    {
        $txt = "Kaiju traffics.\n";
        $txt .= "Duplicate messages may exist. Can you de-duplicate?";
        $txt .= "\n";

        if (!isset($this->sms_message)) {
            $this->makeSMS();
        }

        $txt .= $this->sms_message;

        $txt .= "\n\n";

        $txt .= "Full log follows.\n";

        if (isset($this->kaiju_things)) {
            foreach ($this->kaiju_things as $key => $thing) {
            if (is_string($thing)) {$flat_thing = $thing;}
            if (is_array($thing)) {
            $flat_thing = implode(" ", $thing);
            }

                $txt .= $flat_thing . "\n";
            }
        }

        $txt .= "\n\n";

        $txt .= "dv/dt test.\n";

        $this->calcDvdt();
        if (isset($this->points)) {
            foreach ($this->points as $key => $point) {
                $time_text = date("H:i", $point["refreshed_at"]);
                $date_text = date("m/d/Y", $point["refreshed_at"]);

                if (!isset($date_text_last)) {
                    $date_text_last = $date_text;
                }
                if ($date_text != $date_text_last) {
                    $txt .= $date_text . "\n";
                }

                $txt .=
                    $time_text .
                    " V " .
                    number_format($point["voltage"], 2) .
                    "V dV " .
                    number_format($point["dv"], 2) .
                    "V dt " .
                    $point["dt"] .
                    "s dv/dt " .
                    number_format($point["dv_dt"], 6) .
                    "\n";

                $date_text_last = $date_text;
            }
        }

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractNuuid($input)
    {
        if (!isset($this->duplicables)) {
            $this->duplicables = [];
        }

        return $this->duplicables;
    }

    /**
     *
     */
    public function makeKaiju()
    {
    }

    /**
     *
     */
    public function readSubject()
    {
        $input = $this->input;

// dev strip out Discord address more generally
// https://stackoverflow.com/questions/2174362/remove-text-between-parentheses-php

        if (isset($this->test_flag) and $this->test_flag === true) {
            $input = $this->test_string;
        }

        $filtered_input = preg_replace("/\<[^)]+\>/","",$input); // 'ABC '

        $number_agent = new Number($this->thing, "number");
        $number = $number_agent->extractNumber($filtered_input);

        if ($number != null) {
            $horizon = $number * 24 * 60 * 4; // Every 15 minute;
            $this->response .= "Set horizon to " . $this->textNumber($number) . " things. (" . $this->textNumber($horizon) . " things.) ";

            $this->horizon = $number;
        }

        $pieces = explode(" ", strtolower($filtered_input));

        if (count($pieces) == 1) {
            if ($input == "kaiju") {
                return;
            }
        }

        $keywords = ["test", "kaiju", "simple", "full", "hey", "on", "off"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "hey":
                            return;

                        case "test":
                            $this->test_flag = true;
                            $this->test();
                            $l = $this->parseThing($this->test_string);
                            return;

                        case "on":
                        default:
                    }
                }
            }
        }

        if (!isset($this->index) or $this->index == null) {
            $this->index = 1;
        }
    }
}
