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

    // https://stackoverflow.com/questions/2957609/how-can-i-give-a-color-to-imagecolorallocate
    function allocatehexColor($im, $hex)
    {
        $hex = ltrim($hex, "#");
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return imagecolorallocate($im, $r, $g, $b);
    }

    public function hexColour($text)
    {
        $pattern = "/\#[A-Za-z0-9]{6}/i";

        preg_match_all($pattern, $text, $match);
        if (!isset($colours)) {
            $colours = [];
        }

        $colours = array_merge($colours, $match[0]);
        $colours = array_unique($colours);

        if (count($colours) === 1) {
            return $colours[0];
        }
        if (count($colours) > 1) {
            return true;
        }
        return false;
    }

    public function textColour($text)
    {
        if (!isset($this->colour_names)) {
            $this->colour_names = $this->loadColours();
        }

        foreach ($this->colour_names as $slug => $colour_array) {
            if (strtolower($text) === strtolower($colour_array["name"])) {
                return $colour_array;
            }
        }
        return false;
    }

    public function texthexColour($text)
    {
        $hex = $this->textColour($text);
        if (isset($hex["hex"])) {
            return $hex["hex"];
        }
        return false;
    }
}
