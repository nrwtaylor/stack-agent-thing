<?php
/**
 * Rundate.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Rundate extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init() {

        $this->keywords = array('next', 'accept', 'clear', 'drop', 'add', 'new');
        $this->test= "Development code"; // Always iterative.
        $this->rundate = new Variables($this->thing, "variables rundate " . $this->from);

        $this->thing_report['help'] = "Reads text for the date of the next thing.";

    }


    /**
     *
     */
    function set() {

        if ($this->rundate == false) {return;}

        if (!isset($this->day)) {$this->day = "X";}
        if (!isset($this->month)) {$this->month = "X";}
        if (!isset($this->year)) {$this->year = "X";}

        $datetime = $this->day ." " . $this->month.":".$this->year;
        $this->datetime = date_parse($datetime);



        $this->rundate->setVariable("refreshed_at", $this->current_time);
        $this->rundate->setVariable("day", $this->day);
        $this->rundate->setVariable("month", $this->month);
        $this->rundate->setVariable("year", $this->year);

        $this->printRundate("set");


        $this->thing->log( $this->agent_prefix .' saved ' . $this->day . " " . $this->month . " " . $this->year . ".", "DEBUG" );

        $this->setRunat();

    }


    /**
     *
     * @param unknown $run_at (optional)
     */
    function get($run_at = null) {

        if ($this->rundate == false) {return;}

        $day = $this->rundate->getVariable("day");
        $month = $this->rundate->getVariable("month");
        $year = $this->rundate->getVariable("year");

        if ($this->isInput($month)) {$this->month = $month;}
        if ($this->isInput($year)) {$this->year = $year;}

        if ($this->isInput($day)) {$this->day = $day;}
        // Check the day in Runat.
        //$this->getRunat();


        //        if ($this->isInput($month)) {$this->month = $month;}
        //        if ($this->isInput($year)) {$this->year = $year;}


        $this->printRundate("get");
    }


    /**
     *
     */
    function setRunat() {
        // $runat = new Runat($this->thing, "runat");
        $date_string = $this->year ."-" . $this->month ."-" . $this->day;
        $d = strtotime($date_string);

        $day = strtoupper(date("D", $d));

        $runat = new Runat($this->thing, "runat");

        if (strtoupper($runat->day) != strtoupper($day)) {

            $command = "runat " . $day;
            $this->response .= "Changed runat day to " . $day. ".";
            $runat = new Runat($this->thing, $command);
        }
    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function isToday($text = null) {

        $unixTimestamp = strtotime($this->current_time);
        $day = date("D", $unixTimestamp);

        if (strtoupper($this->day) == strtoupper($day)) {
            return true;
        } else {
            return false;
        }

        // true = yes, false = no
    }


    /**
     *
     * @param unknown $input (optional)
     */
    function extractNumbers($input =  null) {
        $this->numbers = array();

        $agent = new Number($this->thing, "number");
        $numbers = $agent->numbers;
        if (count($numbers) > 0) {
            $this->numbers = $numbers;
        }

    }


    /**
     *
     * @param unknown $input (optional)
     */
    function extractNumber($input =  null) {
        $this->number = "X";

        if (!isset($this->numbers)) {$this->extractNumbers($input);}
        if (count($this->numbers) == 1) {$this->number = $this->numbers[0];}

    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function isInput($input) {
        if ($input === false) {return false;}
        if (strtolower($input) == strtolower("X")) {return false;}

        if (is_numeric($input)) {return true;}
        if ($input == 0) {return true;}

        return true;
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractRundate($input = null) {
        $this->parsed_date = date_parse($input);

        $year = $this->parsed_date['year'];
        $month = $this->parsed_date['month'];
        $day = $this->parsed_date['day'];

        // See what numbers are in the input
        if (!isset($this->numbers)) {$this->extractNumbers($input);}

if (($day != false) and ($month != false)) {

if ((($this->numbers[0] == $day) and ($this->numbers[1] == $month)) or 
(($this->numbers[0] == $month) and ($this->numbers[1] == $day))) {
 //ok
} else {

 if (($this->numbers[0] >12) and ($this->numbers[0] <= 31)) {$day = $this->numbers[0];}
 if (($this->numbers[0] >= 1000) and ($this->numbers[0] <= 9999)) {$year = $this->numbers[0];}


}

}

if (($day == false) and ($month == false) and ($year == false)) {



// Two numbers in string - month and day?
if (isset($this->numbers[1])) {

 if (($this->numbers[0] >12) and ($this->numbers[0] <= 31) and
    ($this->numbers[1] >= 1) and ($this->numbers[1] <= 12)) {

  if (($this->numbers[0] >= 1000) and ($this->numbers[0] <= 9999)) {$year = $this->numbers[0];}
  if (($this->numbers[1] >= 1000) and ($this->numbers[1] <= 9999)) {$year = $this->numbers[1];}

  $day = $this->numbers[0];
  $month = $this->numbers[1];
//  return;
 }
} elseif (!isset($this->numbers[1])) {

 if (($this->numbers[0] >12) and ($this->numbers[0] <= 31)) {$day = $this->numbers[0];}
 if (($this->numbers[0] >= 1000) and ($this->numbers[0] <= 9999)) {$year = $this->numbers[0];}
// return;

}
}


        if ($day > 0) {$this->day = $day;}
        if ($month > 0) {$this->month = $month;}
        if ($year > 0) {$this->year = $year;}


    }




    /**
     *
     */
    function makeTXT() {
        $txt = $this->sms_message;

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }


    /**
     *
     */
    private function makeSMS() {

        $sms_message = "RUNDATE";

        $day_text = str_pad($this->day, 2, "0", STR_PAD_LEFT);
        $month_text = str_pad($this->month, 2, "0", STR_PAD_LEFT);
        $year_text = str_pad($this->year, 2, "0", STR_PAD_LEFT);

        $day_text = $this->day;
        $month_text = $this->month;
        $year_text = $this->year;
        $sms_message .= " | day " . $day_text . " month " . $month_text . " year " . $year_text . " ";
        if (isset($this->response)) {
            $sms_message .= "| " . trim($this->response) . " ";
        }
        if ( (!$this->isInput($this->day)) or
            (!$this->isInput($this->month)) or
            (!$this->isInput($this->year)) ) {

            //if (($this->hour == "X") or ($this->day == "X") or ($this->minute == "X")) {

            $sms_message .= "| Set RUNDATE. ";

        }

        $sms_message .= "| nuuid " . strtoupper($this->rundate->nuuid);
        //        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }


    /**
     *
     */
    public function respond() {

        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $to = $this->thing->from;
        $from = "rundate";


        $choices = false;
        $this->thing_report['choices'] = $choices;

        //$this->makeTXT();

        $this->makeSMS();

        //        $test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';

        //        $test_message .= '<br>' . $this->sms_message;

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;




        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        } else {
            $this->thing_report['info'] = 'Agent input was "' . $this->agent_input . '".' ;
        }

        $this->makeTXT();

    }


    /**
     *
     * @param unknown $variable
     * @return unknown
     */
    function isData($variable) {
        if (
            ($variable !== false) and
            ($variable !== true) and
            ($variable != null) ) {

            return true;

        } else {
            return false;
        }
    }


    /**
     *
     * @param unknown $text (optional)
     */
    function printRundate($text = null) {
        //return;

        if (!isset($this->day)) {$day = "X";} else {$day = $this->day;}
        if (!isset($this->month)) {$month = "X";} else {$month = $this->month;}
        if (!isset($this->year)) {$year = "X";} else {$year = $this->year;}



    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->response = null;
        $this->num_hits = 0;

        $keywords = $this->keywords;
        if (strpos($this->input, "reset") !== false) {
            $this->day = "X";
            $this->month = "X";
            $this->year = "X";
            return;
        }

        if (strpos($this->agent_input, "rundate") !== false) {
            return;
        }


        $this->extractRundate($this->input);

    }


}
