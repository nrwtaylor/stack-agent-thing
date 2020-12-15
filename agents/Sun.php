<?php
/**
 * Sun.php
 *
 * @package default
 */

// TODO Extract equinox.

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Sun extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init()
    {
        $this->test = "Development code";

        $this->thing_report["info"] =
            "This connects to an authorative time server.";
        $this->thing_report["help"] = "Get the time. Text CLOCKTIME.";

        $this->initSun();
    }

    function initSun()
    {
        // Solar observations are dependant on where you are.
        // Get the time.
        $this->time_agent = new Time($this->thing, "time");
        //$this->default_time_zone = 'America/Vancouver';
        $this->time_agent->time_zone = 'America/Vancouver';
        $this->time_agent->doTime();

        $this->longitude_agent = new Longitude($this->thing, "longitude");
        $this->latitude_agent = new Latitude($this->thing, "latitude");

        $this->sun_resource = 'sun/sun.php';
        if (isset($this->thing->container['api']['sun']['sun_resource'])) {
            $this->sun_resource =
                $this->thing->container['api']['sun']['sun_resource'];
        }

        if (file_exists($this->resource_path . $this->sun_resource)) {
            $sun_resource = require $this->resource_path . $this->sun_resource;
        }

        $this->sun_resource = $sun_resource;
    }

    /**
     *
     */
    function makeSMS()
    {
        $this->node_list = ["sun" => ["sun", "moon", "venus"]];
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }

    /**
     *
     */
    function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    public function textSun($text)
    {
        return "The Sun will rise tomorrow";
    }

    function doSun($text = null)
    {
        $day_seconds = 24 * 60 * 60;

        // Zulu time. Now.
        $t = $this->time_agent->getTime();
        $t = $this->current_time;

        $minimums = [];
        $maximums = [];
        $equinoxes = [];

        $day_lengths = [];
        foreach (range(-500, 500, 1) as $n) {
            $epoch_time = strtotime($t) + $n * $day_seconds;

            $arr = $this->predictSun($epoch_time);

            $day_lengths[$n] = $arr['sunset'] - $arr['sunrise'];

            if ((isset($day_lengths[$n-2])) and (isset($day_lengths[$n-1]))) {
                if (
                    $day_lengths[$n - 2] <= $day_lengths[$n - 1] and
                        $day_lengths[$n - 1] > $day_lengths[$n - 0] or
                    $day_lengths[$n - 2] < $day_lengths[$n - 1] and
                        $day_lengths[$n - 1] >= $day_lengths[$n - 0]
                ) {
                    $this->thing->log("found maximum");
                    $maximums[] = [
                        'day' => $n - 1,
                        'day_length' => $day_lengths[$n - 1],
                    ];
                }

                if (
                    $day_lengths[$n - 2] >= $day_lengths[$n - 1] and
                        $day_lengths[$n - 1] < $day_lengths[$n - 0] or
                    $day_lengths[$n - 2] > $day_lengths[$n - 1] and
                        $day_lengths[$n - 1] <= $day_lengths[$n - 0]
                ) {
                    $this->thing->log("found minimum");
                    $minimums[] = [
                        'day' => $n - 1,
                        'day_length' => $day_lengths[$n - 1],
                    ];
                }
            }

            if (
                abs($day_lengths[$n] - (12 * 60 * 60) ) < 200
            ) {

                $this->thing->log("found equinoxes");
                $equinox = [
                    'day' => $n,
                    'day_length' => $day_lengths[$n],
                ];

                if ( ($last_equinox['day']+1) == $equinox['day'] ) { 

                    if (
                        ( abs($last_equinox['day_length'] - (12 * 60 * 60) )) >
                        ( abs($equinox['day_length'] - (12 * 60 * 60))) 
                    ) {
                        continue;
                    }

                } else {
                    $equinoxes[] = $equinox;
                    $last_equinox = $equinox;
                }
            //$day_length_delta = $day_lengths[$n] - $day_lengths[$n-1];
            }
        }

        //var_dump($equinoxes);

        // Minimum is winter solstice (northern hemisphere).
        // Maximum is summer solstice (northern hemisphere).
        $this->sun_message = $this->response;

        return $text;
    }

    // TODO: Refactor lmtTime
    // Consider 
    public function predictSun($text = null)
    {
        // So. This function exists.
        // https://www.php.net/manual/en/function.date-sun-info.php

        // Given a Unix timestamp (epoch time)
        // Convert that to a Local Meridian Time.
        // If you know the latitude and longitude in degrees.

        // TODO: Test

        //$longitude_agent = new Longitude($this->thing, "longitude");

        // Cannot calculate local time without knowing longitude.
        $longitude = $this->longitude_agent->longitude;
        if ($longitude === false) {
            return true;
        }

        //$latitude_agent = new Latitude($this->thing, "latitude");
        $latitude = $this->latitude_agent->latitude;
        if ($latitude === false) {
            return true;
        }

        $timestamp_epoch = time();
        if ($text != null) {
            $timestamp_epoch = $text;
            if (!is_numeric($text)) {
                $timestamp_epoch = strtotime($text);
            }
        }

        $solar_array = date_sun_info($timestamp_epoch, $latitude, $longitude);

        $transit_epoch = $solar_array['transit'];

        $offset = $timestamp_epoch - $transit_epoch; // seconds

        // So at the specific provided epoch time.
        // Which was now.
        // Noon offset in decimal hours.
        $x = 12 * 60 * 60 + $offset;

        // Use gmdate to get an hour minute seconds text stamp.
        $t = gmdate("H:i:s", $x); // How many H:i:s solar noon was ago.
        $solar_array['solar_clock_time'] = $t;
        return $solar_array;
    }

    public function linksSun()
    {
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        if ($this->agent_input == "sun") {
            return;
        }
        $this->filtered_input = $this->assert($this->input, "sun");

        $this->doSun();
        return false;
    }
}
