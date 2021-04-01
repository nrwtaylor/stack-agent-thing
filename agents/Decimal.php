<?php
/**
 * Runtime.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Decimal extends Agent
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
    function isDecimal($val)
    {
        // Use is_numeric (inbuily PHP number type)
        return is_numeric($val) && floor($val) != $val;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
    }
}
