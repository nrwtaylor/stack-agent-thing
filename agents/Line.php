<?php
/**
 * Line.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

// TODO: Respond to fourth parameter. Init start angle.

class Line extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
    }

    public function set()
    {
    }

    public function parseLine($line, $field_names = null)
    {
        if ($field_names == null) {
            $field_names = $this->field_names;
        }

        $field_values = explode(",", $line);
        $i = 0;
        $arr = [];

        foreach ($field_names as $field_name) {
            if (!isset($field_values[$i])) {
                $field_values[$i] = null;
            }
            $arr[$field_name] = $field_values[$i];
            $i += 1;
        }
        return $arr;
    }


    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms = "LINEL | ";
        $sms .= $this->web_prefix . "thing/" . $this->uuid . "/line";
        $sms .= " | " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    // https://rosettacode.org/wiki/Find_the_intersection_of_two_lines#Python
    public function intersectLine($line1, $line2)
    {
        $Ax1 = $line1[0]['x'];
        $Ay1 = $line1[0]['y'];
        $Ax2 = $line1[1]['x'];
        $Ay2 = $line1[1]['y'];

        $Bx1 = $line2[0]['x'];
        $By1 = $line2[0]['y'];
        $Bx2 = $line2[1]['x'];
        $By2 = $line2[1]['y'];

        //    """ returns a (x, y) tuple or None if there is no intersection """
        $d = ($By2 - $By1) * ($Ax2 - $Ax1) - ($Bx2 - $Bx1) * ($Ay2 - $Ay1);
        if ($d != 0) {
            $uA =
                (($Bx2 - $Bx1) * ($Ay1 - $By1) -
                    ($By2 - $By1) * ($Ax1 - $Bx1)) /
                $d;
            $uB =
                (($Ax2 - $Ax1) * ($Ay1 - $By1) -
                    ($Ay2 - $Ay1) * ($Ax1 - $Bx1)) /
                $d;
        } else {
            return true;
        }

        if (!(0 <= $uA and $uA <= 1 and (0 <= $uB and $uB <= 1))) {
            return true;
        }
        $x = $Ax1 + $uA * ($Ax2 - $Ax1);
        $y = $Ay1 + $uA * ($Ay2 - $Ay1);

        return ['x' => $x, 'y' => $y];
    }

}
