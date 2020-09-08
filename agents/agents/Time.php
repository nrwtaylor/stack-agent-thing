<?php
/**
 * Time.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);


class Time extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
        $this->agent_name = "time";
        $this->test= "Development code";

        $this->thing_report["info"] = "This connects to an authorative time server.";
        $this->thing_report["help"] = "Get the time. Text CLOCKTIME.";

        $this->time_zone = 'America/Vancouver';
    }


    /**
     *
     */
    function makeSMS() {
        $this->node_list = array("time"=>array("time"));
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }


    /**
     *
     */
    function makeChoices() {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }


    /**
     * https://stackoverflow.com/questions/11343403/php-exception-handling-on-datetime-object
     *
     * @param unknown $str
     * @return unknown
     */
    function isDateValid($str) {

        if (!is_string($str)) {
            return false;
        }

        $stamp = strtotime($str);

        if (!is_numeric($stamp)) {
            return false;
        }

        if ( checkdate(date('m', $stamp), date('d', $stamp), date('Y', $stamp)) ) {
            return true;
        }
        return false;
    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function doTime($text = null) {
        $datum = null;

        $timevalue = $text;
        if (($this->agent_input == "time") and  ($text == null)) {$timevalue = $this->current_time;}
        if ($text == "time") {$timevalue = $this->current_time;}

        if ($timevalue == null) {$timevalue = $this->current_time;}

        $m =  "Unfortunately, the time server was not available. ";

        //try {
        //        if (true) {
        if ($this->isDateValid( $timevalue )) {
            $datum = new \DateTime($timevalue, new \DateTimeZone("UTC"));

            $datum->setTimezone(new \DateTimeZone($this->time_zone));

            $m = "Time check from stack server ". $this->web_prefix. ". ";
            $m .= "In the timezone " . $this->time_zone . ", it is " . $datum->format('l') . " " . $datum->format('d/m/Y, H:i:s') .". ";

            $this->text =  $datum->format('H:i');

        } else {

            //} catch (Throwable $t) {
            $m = "Could not get a time.";
        }



        $this->response .= $m;
        $this->time_message = $this->response;

        $this->datum = $datum;
        return $datum;

    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    public function extractTimezone($text = null) {

        if (($text == null) or ($text == "")) {return true;}
        $OptionsArray = timezone_identifiers_list();

        $matches = array();

        // Devstack. Librex.
        foreach ($OptionsArray as $i=>$timezone_id) {

            if ( (stripos($timezone_id, $text) !== false) or (stripos($timezone_id, str_replace(" ","_",$text)) !== false) ) {
                $matches[] = $timezone_id;
            }
        }

        $match = false;
        if ((isset($matches)) and (count($matches) == 1)) {$match = $matches[0];} else {
            $this->response .= "Could not resolve the timezone. ";}

        return $match;

    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->filtered_input = $this->assert($this->input, "time");

        if ($this->filtered_input != "") {
            $timezone = $this->extractTimezone($this->filtered_input);
        }

        if ((isset($timezone)) and (is_string($timezone))) {$this->time_zone = $timezone;}

        $this->doTime();
        return false;
    }


}
