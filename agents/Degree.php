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


    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
    }
}
