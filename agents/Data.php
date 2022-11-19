<?php
/**
 * Rundate.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Data extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->keywords = ["data"];

        $this->thing_report["info"] =
            "Data on the stack is something other than true, false or null.";

        $this->thing_report["help"] =
            "Recognizes data.";
    }

    /**
     *
     * @param unknown $variable
     * @return unknown
     */
    public function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
    }
}
