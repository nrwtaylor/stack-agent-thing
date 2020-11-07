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
            $datum = $this->datumTime($text);
            $this->text = $datum->format('H:i');
            $m = "Time check from stack server " . $this->web_prefix . ". ";
            $m .=
                "In the timezone " .
                $this->time_zone .
                ", it is " .
                $datum->format('l') .
                " " .
                $datum->format('d/m/Y, H:i:s') .
                ". ";
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

    function datumTime($text = null, $time_zone = "UTC")
    {

if ($text == null) {return true;}

// If not datum is provided.
// Check for a zull flug.
$zulu_flag = null;
if (strtolower(substr($text, -1)) == 'z') {
    $zulu_flag = "Z";
}

if (($zulu_flag == "Z") and ($time_zone == null)) {

$time_zone = "UTC";

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
        if ($this->agent_input == "time") {return;}
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
