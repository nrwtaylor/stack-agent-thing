<?php
/**
 * Time.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Timezone extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init()
    {
        $this->initTimezone();
    }

    function initTimezone()
    {
        $this->default_time_zone = "America/Vancouver";
        if (isset($this->thing->container["api"]["time"])) {
            if (
                isset(
                    $this->thing->container["api"]["time"]["default_time_zone"]
                )
            ) {
                $this->default_time_zone =
                    $this->thing->container["api"]["time"]["default_time_zone"];
            }
        }

        $this->time_zone = $this->default_time_zone;
        /*

        $allowed_endpoints = [];
        if (file_exists($this->resource_path .
            $this->allowed_slugs_resource)) {

        $allowed_endpoints = require $this->resource_path .
            $this->allowed_slugs_resource;
        }


*/

        if (file_exists($this->resource_path . "timezone/timezone.php")) {
            $this->supplemental_timezone_identifiers_list = require $this->resource_path .
                "timezone/timezone.php";
        }
    }

    public function get()
    {
    }

    public function set()
    {
    }

    public function readSubject()
    {
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
                stripos($text, $timezone_id) !== false or
                stripos(str_replace(" ", "_", $text),  $timezone_id) !== false
            ) {
                $matches[] = $timezone_id;
            }
        }


        $match = false;
        if (isset($matches) and count($matches) == 1) {
            $match = $matches[0];
            return $match;
        }

        if (!isset($this->supplemental_timezone_identifiers_list)) {
           return true;
        }

        $OptionsArray = $this->supplemental_timezone_identifiers_list;
        $matches = [];

        // Devstack. Librex.
        foreach ($OptionsArray as $timezone_id => $descriptors) {
            foreach ($descriptors as $descriptor) {
                if (
                    stripos($text, $descriptor) !== false or
                    stripos(str_replace(" ", "_", $text), $descriptor) !== false
                ) {
                    $matches[] = $timezone_id;
                }
            }
        }
        $matches = array_unique($matches);
        $match = false;
        if (isset($matches) and count($matches) == 1) {
            $match = $matches[0];
            return $match;
        }
        return true;
    }
}
