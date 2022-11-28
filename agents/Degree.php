<?php
/**
 * Degree.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// Sexigesimal - 60 * 60.
class Degree extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->keywords = ['decimal', 'dec'];
    }

    /**
     *
     * @param unknown $val
     * @return unknown
     */
    function isDegree($text)
    {
        $number = $this->extractNumber($text);
        if ($this->isBlank($number) === false) {return false;}

        // A degree can be any positive or negative number.
        // But likely to be 0 to 360.
        return true;
    }

    public function extractDegree($text) {
       $number = $this->extractNumber($text);
       return $number;
    }

    function scoreDegree($text)
    {
        $number = $this->extractNumber($text);
        if ($this->isBlank($number) === false) {return 0;}

        // A degree can be any positive or negative number.
        // But likely to be 0 to 360.
        if ($number > -360 and $number < 360) {return 10;}

        return 1;
    }

public function decimalToDegree($text)
{

// Converts decimal longitude / latitude to DMS
// ( Degrees / minutes / seconds ) 

// This is the piece of code which may appear to 
// be inefficient, but to avoid issues with floating
// point math we extract the integer part and the float
// part by using a string function.

    $dec = floatval($text);

    $vars = explode(".",$dec);
    $deg = $vars[0];
    $tempma = "0.".$vars[1];

    $tempma = $tempma * 3600;
    $min = floor($tempma / 60);
    $sec = $tempma - ($min*60);

    $degrees_text = $deg . "°". $min. "'". round($sec,1) . '"';
    return $degrees_text;
}


    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
    }
}
