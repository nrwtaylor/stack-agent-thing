<?php
/**
 * Blank.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Blank extends Agent
{
    public $var = "hello";

    /**
     *
     */
    function init()
    {
        $this->node_list = ["empty" => ["empty", "null"]];
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function isBlank($text)
    {
        if ($text === true) {return true;}
        if ($text === false) {return true;}
        if ($text === null) {return true;}

        if ($text === "") {return true;}
        if (is_array($text) and count($text) === 0) {return true;}

        return false;
    }




}
