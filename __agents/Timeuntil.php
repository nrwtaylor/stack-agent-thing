<?php
/**
 * Timeuntil.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Timeuntil extends Agent {

    public $var = 'hello';


    /**
     *
     */
    function init() {

        $this->agent_name = 'time until';
        $this->test= "Development code";

        $this->current_time = $this->thing->json->time();
    }


    /**
     *
     */
    function run() {
        $this->getTimeuntil();
    }

    /**
     *
     */
    function getTimeuntil() {
        $this->runat = new Runat($this->thing, "runat"); // get runat for the currently focused event
        $rundate = new Rundate($this->thing, "rundate");



        if ( ($rundate->year == "X") or
            ($rundate->month == "X") or
            ($rundate->day == "X") or
            ($this->runat->day == "X") or
            (($this->runat->hour == "X") and ($this->runat->hour != 0)) or
            (($this->runat->minute == "X") and ($this->runat->minute != 0))  ) {


            $this->time_until = null;
            return;
        }

        $date_text = $rundate->year . "-" . $rundate->month . "-" . $rundate->day ." " . $this->runat->hour . ":" . $this->runat->minute;
        $run_time = strtotime($date_text);

        $now = (strtotime($this->current_time));

        $time_until = $run_time - $now;


        if ($time_until > 0) {
            //var_dump($runat->datetime);
            $this->time_until = $run_time - $now;
        } else {
            $this->time_until = null;
        }


    }


    /**
     *
     * @return unknown
     */
    public function respond() {


        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "timeuntil";





        //        $response = $input . "Try " . strtoupper($v) . ".";


        $this->makeSMS();
        $this->makeChoices();

        $this->thing_report["info"] = "This is about time until.";
        $this->thing_report["help"] = "Time until is the time to go until the Bell.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;


        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        return $this->thing_report;


    }


    /**
     *
     */
    function makeSMS() {

        if ($this->agent_input == null) {
            $array = array('Time until is the time until a Bell.');
            $k = array_rand($array);
            $pos = $array[$k];

            $array = array('Time until is the time until a Bell.');
            $k = array_rand($array);
            $neg = $array[$k];

            if ($this->time_until < 0) {
                $response = "TIME UNTIL | " . $this->thing->human_time( $this->time_until / -1)."";
            } else {
                $response = "TIME UNTIL | " . $this->thing->human_time( $this->time_until )."";
            }
            $response .= " until " . $this->runat->day . " " . str_pad("0", 2, $this->runat->hour, STR_PAD_LEFT) . ":" . str_pad("0", 2, $this->runat->minute, STR_PAD_LEFT) .".";

            if ($this->time_until == null) {$response = "TIME UNTIL | Event not set. Set RUNDATE and/or RUNAT.";}

            $this->cat_message = $response;
        } else {
            $this->cat_message = $this->agent_input;
        }


        $this->node_list = array("cat"=>array("cat", "time until"));
        $this->sms_message = "" . $this->cat_message;
        $this->thing_report['sms'] = $this->sms_message;

    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create('channel', $this->node_list, "cat");
        $choices = $this->thing->choice->makeLinks('cat');
        $this->thing_report['choices'] = $choices;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {


        //$input = strtolower($this->subject);


        return false;
    }


}
