<?php
/**
 * Date.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Date extends Agent
{
    /**
     *
     * @param unknown $x
     * @return unknown
     */
    function isDate($x)
    {
        $date_array = date_parse($x);

        if (
            $date_array['day'] != false and
            $date_array['month'] != false and
            $date_array['year'] != false
        ) {
            return true;
        }
        return false;
    }

}
