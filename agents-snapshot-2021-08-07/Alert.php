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


class Alert extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
        $this->agent_name = "alert";
        $this->test= "Development code";

        $this->thing_report["info"] = "This connects to an alert statement.";
        $this->thing_report["help"] = "Get the alert. Text ALERT.";

        $this->time_zone = 'America/Vancouver';
    }

    /**
     *
     */
    private function getAlerts() {
        //if (isset($this->cave_names)) {return;}

        // Makes a one character dictionary

        $file = $this->resource_path . 'alert/alerts.txt';
        $contents = file_get_contents($file);


        $separator = "\r\n";
        $line = strtok($contents, $separator);
$this->alerts = false;
        while ($line !== false) {
//            $items = explode(",", $line);
            $this->alerts = $line;
            break;

            // do something with $line
            $line = strtok( $separator );
        }

//$this->alerts = false;

    }


    /**
     *
     */
    function makeSMS() {
        $this->node_list = array("alert"=>array("alert"));
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

    //https://stackoverflow.com/questions/11343403/php-exception-handling-on-datetime-object
    function isAlertValid($str) {

if ($str == false) {return false;}

return true;
    } 


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function doAlert($text = null) {
//        $datum = null;
$this->getAlerts();
//var_dump($this->alerts);
        $timevalue = $text;
        if (($this->agent_input == "alert") and  ($text == null)) {$timevalue = "No current alerts.";}
        if ($text == "alert") {$timevalue = "Alert not defined.";}

        if ($timevalue == null) {$timevalue = "Did not find an alert.";}

if  (!isset($this->alerts)) {

$m = "No alerts found.";

}
 //       $m =  "Unfortunately, the alert agent was not available. ";

        //try {
        //        if (true) {
if ($this->isAlertValid( $this->alerts )) {

//            $m = "Alert check from stack server ". $this->web_prefix. ". ";
$m = "Found an alert. ";
$this->flag = "red";
$m .= $this->alerts;
//            $m .= "In the timezone " . $this->time_zone . ", it is " . $datum->format('l') . " " . $datum->format('d/m/Y, H:i:s') .". ";

        } else {
$this->flag = "green";
//} catch (Throwable $t) {
$m = "No alert found.";
}



        $this->response .= $m;
        $this->time_message = $this->response;

//        $this->datum = $datum;
        return $timevalue;

    }

    public function extractAlert($text = null) {

        if (($text == null) or ($text == "")) {return true;}
$alerts_list = array("transit");
        $OptionsArray = $alerts_list;

        $matches = array();

        // Devstack. Librex.
        foreach ($OptionsArray as $i=>$timezone_id) {

            if (stripos($timezone_id, $text) !== false) {
                $matches[] = $timezone_id;
            }
        }

        $match = false;
        if ((isset($matches)) and (count($matches) == 1)) {$match = $matches[0];}
        $this->response .= "Could not resolve the alert. ";

        return $match;

    }

    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->filtered_input = $this->assert($this->input, "alert");

        if ($this->filtered_input != "") {
            $timezone = $this->extractAlert($this->filtered_input);
        }

        if ((isset($alert)) and (is_string($alert))) {$this->alert = $alert;}

        $this->doAlert();
        return false;
    }

}
