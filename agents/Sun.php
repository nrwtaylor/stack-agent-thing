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
        $this->agent_name = "sun";
        $this->test = "Development code";

        $this->thing_report["info"] =
            "This provides awareness of sun position and solar time.";
        $this->thing_report["help"] = "Try SUN. Then WEB.";
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

    function makePNG()
    {
        if ($image = null) {
            $image = $this->image;
        }
        if ($image == true) {
            return true;
        }

        $agent = new Png($this->thing, "png");

        $jpgs = $this->sun_resource['jpgs'];

        foreach ($jpgs as $jpg_link => $jpg_meta) {
            $image = imagecreatefromstring(file_get_contents($jpg_link));

            if ($image === true) {
                return true;
            }

            $agent->makePNG($image);

            $this->html_image = $agent->html_image;
            $this->image = $agent->image;
            $this->PNG = $agent->PNG;

$this->thing_report['png'] = $agent->>thing_report["png"];
           //>thing_report["png"] $this->thing_report['png'] = $agent->image_string;
        }
/*
        $this->thing_report['png'] = $imagedata;

        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="snowflake"/>';

        $this->html_image =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="snowflake"/>';

        $this->PNG_embed = "data:image/png;base64," . base64_encode($imagedata);

        //        $this->thing_report['png'] = $image;

        //        $this->PNG = $this->image;
        $this->PNG = $imagedata;
*/


    }

    public function makePNGs()
    {
        return;
        $this->thing_report['pngs'] = [];
        //return;
        $agent = new Png($this->thing, "png");

        $jpgs = $this->sun_resource['jpgs'];

        foreach ($this->result as $index => $die_array) {
            reset($die_array);
            $die = key($die_array);
            $number = current($die_array);

            $image = $this->makeImage($number, $die);
            if ($image === true) {
                continue;
            }

            $agent->makePNG($image);

            $alt_text =
                "Image of a " . $die . " die with a roll of " . $number . ".";

            $this->images[$this->agent_name . '-' . $index] = [
                "image" => $agent->image,
                "html_image" => $agent->html_image,
                "image_string" => $agent->image_string,
                "alt_text" => $alt_text,
            ];

            $this->thing_report['pngs'][$this->agent_name . '-' . $index] =
                $agent->image_string;
        }
    }

    /**
     *
     */
    function makeSMS()
    {
        $day_agent = new Day($this->thing, "day");

        $day_time_text = "Above the horizon. ";
        if ($day_agent->day_time != 'day') {
            $day_time_text = "Below the horizon. ";
        }

        $this->linksSun();
        $this->node_list = ["sun" => ["sun", "moon", "venus"]];
        $m =
            strtoupper($this->agent_name) .
            " | " .
            $day_time_text .
            " " .
            $this->response;
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

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["info"] = "This rolls a dice.  See
                https:\\codegolf.stackexchange.com/questions/25416/roll-dungeons-and-dragons-dice";
        if (!isset($this->thing_report['help'])) {
            $this->thing_report["help"] =
                'This is about dice with more than 6 sides.  Try "Roll d20". Or "Roll 3d20+17. Or "Card"';
        }

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        return $this->thing_report;
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

    function dateEpoch($epochtime)
    {
        // Time returned in GMT.
        $t = gmdate("Y-m-d\TH:i:s\Z", $epochtime); // How many H:i:s solar noon was ago.
        return $t;
    }

    function doSun($text = null)
    {
        // dev
        // TODO

        // Get definition of solstice

        /*
https://en.wikipedia.org/wiki/Equinox
An equinox is the instant of time when the plane of Earth's
equator passes through the geometric center of the Sun's disk.[3][4]
This occurs twice each year, around 20 March and 23 September.
In other words, it is the moment at which the center of the visible Sun
is directly above the equator. 

A solstice is an event that occurs when the Sun appears to reach its most northerly or southerly excursion relative to the celestial equator
on the celestial sphere. 
Two solstices occur annually, around June 21 and December 21.
In many countries, the seasons of the year are determined by reference
to the solstices and the equinoxes. 

*/

        // Calculate longest and shortest days at stack lat and long

        $day_seconds = 24 * 60 * 60;

        // Zulu time. Now.
        $t = $this->time_agent->getTime();
        $t = $this->current_time;

        $minimums = [];
        $maximums = [];
        $equinoxes = [];
        $equal_night_days = [];

        $day_lengths = [];
        foreach (range(-500, 500, 1) as $n) {
            $epoch_time = strtotime($t) + $n * $day_seconds;

            $arr = $this->predictSun($epoch_time);

            $day_lengths[$n] = $arr['sunset'] - $arr['sunrise'];

            if (isset($day_lengths[$n - 2]) and isset($day_lengths[$n - 1])) {
                if (
                    $day_lengths[$n - 2] <= $day_lengths[$n - 1] and
                        $day_lengths[$n - 1] > $day_lengths[$n - 0] or
                    $day_lengths[$n - 2] < $day_lengths[$n - 1] and
                        $day_lengths[$n - 1] >= $day_lengths[$n - 0]
                ) {
                    $this->thing->log("found maximum");
                    $maximums[] = [
                        'description' => 'longest day',
                        'day' => $n - 1,
                        'transit' => $this->dateEpoch($arr['transit']),
                        'day_length' => $day_lengths[$n - 1],
                        'timestamp' => $this->dateEpoch(
                            strtotime($t) + ($n - 1) * $day_seconds
                        ),
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
                        'description' => 'shortest day',
                        'day' => $n - 1,
                        'transit' => $this->dateEpoch($arr['transit']),
                        'day_length' => $day_lengths[$n - 1],
                        'timestamp' => $this->dateEpoch(
                            strtotime($t) + ($n - 1) * $day_seconds
                        ),
                    ];
                }
            }

            if (abs($day_lengths[$n] - 12 * 60 * 60) < 200) {
                $this->thing->log("found equinoxes");
                $equinox = [
                    'description' => '12 hour day',
                    'day' => $n,
                    'day_length' => $day_lengths[$n],
                    'timestamp' => $this->dateEpoch(
                        strtotime($t) + $n * $day_seconds
                    ),
                ];

                if (
                    isset($last_equinox['day']) and
                    $last_equinox['day'] + 1 == $equinox['day']
                ) {
                    if (
                        abs($last_equinox['day_length'] - 12 * 60 * 60) >
                        abs($equinox['day_length'] - 12 * 60 * 60)
                    ) {
                        continue;
                    }
                } else {
                    $equinoxes[] = $equinox;
                    $equal_night_days[] = $equinox;
                    $last_equinox = $equinox;
                }
                //$day_length_delta = $day_lengths[$n] - $day_lengths[$n-1];
            }
        }

        // Minimum is winter solstice (northern hemisphere).
        // Maximum is summer solstice  (northern hemisphere).
        //$this->winter_solstices = $maximums;
        //$this->summer_solstices = $minimums;

        $this->longest_days = $maximums;
        $this->shortest_days = $minimums;
        $this->equal_night_days = $equal_night_days;

        //        $this->equinoxes = $equinoxes;
        $this->sun_message = $this->response;
        $events = [];
        $events = array_merge($events, $this->longest_days);
        $events = array_merge($events, $this->shortest_days);
        $events = array_merge($events, $this->equal_night_days);

        //        $events = array_merge($events, $this->equinoxes);

        usort($events, function ($first, $second) {
            return strtotime($first['timestamp']) -
                strtotime($second['timestamp']);
        });

        //PHP 7 Spaceship operator
        //usort($events, function($a, $b) {
        //  return new \DateTime($a['timestamp']) <=> new \DateTime($b['timestamp']);
        //});
        $this->events = $events;

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

    public function makeSnippet()
    {
    }

    public function makeWeb()
    {
        $web = "";

        $web .= "<b>EVENTS</b><br>";
        foreach ($this->events as $key => $value) {
            $day_length_text = intval($value['day_length'] / 60) . " minutes";
            $web .=
                $value['timestamp'] .
                " " .
                $value['description'] .
                " " .
                $day_length_text .
                "<br>";
        }

        $this->sun_resource['jpgs'][0]['alt_text'] = 'test';
        $alt_text = $this->sun_resource['jpgs'][0]['alt_text'];
        $width = 100;
        $html_width = 'width=' . $width . ' ';
        $html_width = "";

        $image_string = $this->thing_report['png'];
        $html =
            '<img src="data:image/png;base64,' .
            $image_string .
            '" ' .
            $html_width .
            '
                alt="' .
            $alt_text .
            '" longdesc="' .
            $this->web_prefix .
            'thing/' .
            $this->uuid .
            '/' .
            $this->agent_name .
            '.txt" >';

        $web .= $html;
        //$link_agent = new Link($this->thing, "link");
        foreach ($this->sun_resource['urls'] as $key => $value) {
            $link = '<a href="' . $key . '">';
            $link .= $value['text'];
            $link .= "</a>";
            $web .= $link . "<br>";
        }

        $this->thing_report['web'] = $web;
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
