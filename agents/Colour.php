<?php
/**
 * Colour.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Colour extends Agent
{
    public $var = "hello";

    /**
     *
     */
    function init()
    {
        $this->node_list = ["address" => ["n-gram", "address"]];
        $this->colour_indicators = ["red", "green", "blue", "yellow"];
        // TODO develop file of colour names.
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function isColour($text)
    {
        foreach ($this->colour_indicators as $indicator) {
            $variants = [];
            $variants[] = " " . $indicator . " ";
            $variants[] = " " . $indicator . ".";
            $variants[] = " " . $indicator . ",";
            $variants[] = "," . $indicator . ",";
            $variants[] = "(" . $indicator . ")";
            $variants[] = "{" . $indicator . "}";
            $variants[] = "[" . $indicator . "]";

            foreach ($variants as $variant) {
                if (stripos($text, $variant) !== false) {
                    return true;
                }
            }
        }

        $colours = $this->extractColours($text);
        if (count($colours) > 0) {
            return true;
        }

        return false;
    }
}
