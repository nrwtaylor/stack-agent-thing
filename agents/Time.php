<?php
/**
 * Time.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Time extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init()
    {
        $this->agent_name = "time";
        $this->test = "Development code";

        $this->thing_report["info"] =
            "This connects to an authorative time server.";
        $this->thing_report["help"] = "Get the time. Text CLOCKTIME.";

        $this->initTime();

        //$this->time = $this->thing->time();
        //$this->time = time();
        //$this->time_zone = 'America/Vancouver';
    }

    function initTime()
    {
        $this->default_time_zone = 'America/Vancouver';
        if (isset($this->thing->container['api']['time'])) {
            if (
                isset(
                    $this->thing->container['api']['time']['default_time_zone']
                )
            ) {
                $this->default_time_zone =
                    $this->thing->container['api']['time']['default_time_zone'];
            }
        }

        $this->time_zone = $this->default_time_zone;
    }

    /**
     *
     */
    function makeSMS()
    {
        $this->node_list = ["time" => ["time"]];
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
     * https://stackoverflow.com/questions/11343403/php-exception-handling-on-datetime-object
     *
     * @param unknown $str
     * @return unknown
     */
    function isDateValid($str)
    {
        if (!is_string($str)) {
            return false;
        }

        $stamp = strtotime($str);

        if (!is_numeric($stamp)) {
            return false;
        }

        if (
            checkdate(date('m', $stamp), date('d', $stamp), date('Y', $stamp))
        ) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    public function textTime($text)
    {
        $timestamp = strtotime($text);
        $new_date_format = date('m/d H:i', $timestamp);
        return $new_date_format;
    }

    // TODO Replace with call to datumTime() ?
    public function timestampTime(
        $datum = null,
        $time_zone = null,
        $flag_timezone = false
    ) {
        if ($datum == null) {
            if (!isset($this->datum)) {
                return true;
            }
            $datum = $this->datum;
        }

        if ($time_zone == null) {
            $time_zone = $this->time_zone;
        }

        $datum->setTimezone(new \DateTimeZone($time_zone));

        $tz = $datum->getTimezone();
        $timezone = $tz->getName();

        $time_stamp = $datum->format('Y-m-d H:i:s') . " ";
        if ($flag_timezone === true) {
            $timestamp .= $timezone;
        }
        return $time_stamp;
    }

    function doTime($text = null)
    {
        if ($text == null) {
            $text = $this->getTime();
        }
        $m = "Could not get a time.";
        if ($this->isDateValid($text)) {
            $m = "Time check from stack server " . $this->web_prefix . ". ";

            $datum = $this->datumTime($text);
            if ($datum !== false) {
                $this->text = $datum->format('H:i');
                $m .=
                    "In the timezone " .
                    $this->time_zone .
                    ", it is " .
                    $datum->format('l') .
                    " " .
                    $datum->format('d/m/Y, H:i:s') .
                    ". ";
            }

            if ($datum === false) {
                $m .= "The local meridian/mean/solar(?) time is ";
                $m .= $this->lmtTime();
                $m .= ". This is a developmental stack service. Validate before use.";
            }
        }

        $this->response .= $m;
        $this->time_message = $this->response;

        return $datum;
    }

    function getTime($text = null)
    {
        $timevalue = $text;
        if ($this->agent_input == "time" and $text == null) {
            $timevalue = $this->current_time;
        }
        if ($text == "time") {
            $timevalue = $this->current_time;
        }

        if ($timevalue == null) {
            $timevalue = $this->current_time;
        }
        return $timevalue;
    }

    public function lmtTime($text = null)
    {
        // So. This function exists.
        // https://www.php.net/manual/en/function.date-sun-info.php

        // Given a Unix timestamp (epoch time)
        // Convert that to a Local Meridian Time.
        // If you know the latitude and longitude in degrees.

        // TODO: Test

        $longitude_agent = new Longitude($this->thing, "longitude");
        $longitude = $longitude_agent->longitude;

        $latitude_agent = new Latitude($this->thing, "latitude");
        $latitude = $latitude_agent->latitude;

        //$latitude = 49.2827;

        $timestamp_epoch = time();
        if ($text != null) {
            $timestamp_epoch = strtotime($text);
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

        // So local meridian time would be.

        $text = "XXXX-XX-XXT" . $t . " LMT";

        // Really. So we need to engage with latitude and longitude?

        // The latitude and longitude is a function of the current position.
        // Of the vessel.

        // They are independantly observed on a spere(oid/ish). Geoid?
        // One is a function of the number of minutes offset you are from a meridian.

        // And longitude is an observation of the inclination.
        // Of the spinny axis against the solar(/local galaxy)?

        // So. An observation of latitude tells you how many minutes.
        // You are ahead or behind the meridian.

        // 123.1207° W

        // This tells me I am ahead/behind the meridian by 123.1207 minutes
        // Of longitude. Appearently there is a factor of four.

        // Which gets me to 492.8428 minutes of time.

        // 8.20804667 hours of time.

        return $text;

        // TODO

        // And then I look up in the sky and measure the inclination.
        // Of something obvious. Polaris?

        // And do that in a lot of fixed places.

        // Look for the convergence. Measure against that.

        // Recognize is wobbles (a bit/a lot)?

        // 49.2827° N

        // So channel based observation and storage of latitude and longitude.
        // https://www.google.com/search?&q=latitude+vancouver

        // Easy search for a human.
        // Trickier for a robot. Maybe.
    }

    function datumTime($text = null, $time_zone = "UTC")
    {
        // Recognize and understand local meridian.
        // PHP documentation for timezones does not look to recognize this.

        // So set a datum false.

        if (strtolower($time_zone) == "lmt") {
            $this->lmtTime($text);

            $this->datum = false; // Signal no external datum found.
            return $this->datum;
        }

        if ($text == null) {
            return true;
        }

        // If not datum is provided.
        // Check for a zull flug.
        $zulu_flag = null;
        if (strtolower(substr($text, -1)) == 'z') {
            $zulu_flag = "Z";
        }

        if ($zulu_flag == "Z" and $time_zone == null) {
            $time_zone = "UTC";
        }

        if ($this->time_zone == "lmt") {
            $this->datum = false;
            return $this->datum;
        }

        $datum = null;
        $timevalue = $text;

        if ($this->isDateValid($timevalue)) {
            $datum = new \DateTime($timevalue, new \DateTimeZone($time_zone));

            $datum->setTimezone(new \DateTimeZone($this->time_zone));
        }

        $this->datum = $datum;
        return $datum;
    }

    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    public function extractTimezone($text = null)
    {
        if ($text == null or $text == "") {
            return true;
        }

        if (stripos($text, "lmt") !== false) {
            return "lmt";
        }

        $text = str_replace("time", "", $text);
        $text = trim(str_replace("stamp", "", $text));

        $OptionsArray = timezone_identifiers_list();

        $matches = [];

        // Devstack. Librex.
        foreach ($OptionsArray as $i => $timezone_id) {
            if (
                stripos($timezone_id, $text) !== false or
                stripos($timezone_id, str_replace(" ", "_", $text)) !== false
            ) {
                $matches[] = $timezone_id;
            }
        }
        $match = false;
        if (isset($matches) and count($matches) == 1) {
            $match = $matches[0];
        } else {
            $this->response .= "Could not resolve the timezone. ";
        }
        return $match;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        //$input = $this->input;
        //if (stripos($input, "lmt") !== false) {$this->timezone="lmt";}

        if ($this->agent_input == "time") {
            return;
        }
        $this->filtered_input = $this->assert($this->input, "time");

        if ($this->filtered_input != "") {
            $timezone = $this->extractTimezone($this->filtered_input);
        }

        if (isset($timezone) and is_string($timezone)) {
            $this->time_zone = $timezone;
        }

        $this->doTime();
        return false;
    }
}
