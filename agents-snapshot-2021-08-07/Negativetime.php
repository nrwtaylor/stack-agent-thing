<?php
/**
 * Negativetime.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Negativetime extends Agent {

    public $var = 'hello';


    /**
     *
     */
    function init() {

        $this->agent_name = 'negative time';
        $this->test= "Development code";

        $this->current_time = $this->thing->json->time();
    }


    /**
     *
     */
    function run() {
        $this->getNegativetime();
    }

    /**
     *
     */
    function getNegativetime() {
        $this->endat = new Endat($this->thing, "endat"); // get runat for the currently focused event
        $enddate = new Enddate($this->thing, "enddate");



        if ( ($enddate->year == "X") or
            ($enddate->month == "X") or
            ($enddate->day == "X") or
            ($this->endat->day == "X") or
            (($this->endat->hour == "X") and ($this->endat->hour != 0)) or
            (($this->endat->minute == "X") and ($this->endat->minute != 0))  ) {


            $this->negative_time = null;
            return;
        }

        $date_text = $enddate->year . "-" . $enddate->month . "-" . $enddate->day ." " . $this->endat->hour . ":" . $this->endat->minute;
        $end_time = strtotime($date_text);
var_dump($date_text);
        $now = (strtotime($this->current_time));
var_dump($this->current_time);
        $negative_time = $end_time - $now;


        if ($negative_time > 0) {
            //var_dump($runat->datetime);
            $this->negative_time = $end_time - $now;
        } else {
            $this->negative_time = null;
        }


    }


    /**
     *
     * @return unknown
     */
    public function respond() {


        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "negativetime";





        //        $response = $input . "Try " . strtoupper($v) . ".";


        $this->makeSMS();
        $this->makeChoices();

        $this->thing_report["info"] = "This is about negative time.";
        $this->thing_report["help"] = "Negative time is the time after a Bell.  It is a measure of the total advance on the bell.";

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
            $array = array('Negative time is the time after a Bell.  It is a measure of the total delay to the next bell.');
            $k = array_rand($array);
            $pos = $array[$k];

            $array = array('Negative time is the time after a Bell.  It is a measure of the total advance on the bell.');
            $k = array_rand($array);
            $neg = $array[$k];
            if ($this->negative_time < 0) {
                $response = "NEGATIVE TIME | " . "-" .$this->thing->human_time( $this->negative_time / -1)." since ";
            } else {
                $response = "NEGATIVE TIME | " . "+".$this->thing->human_time( $this->negative_time)." until ";
            }
            $response .= "" . $this->endat->day . " " . str_pad("0", 2, $this->endat->hour, STR_PAD_LEFT) . ":" . str_pad("0", 2, $this->endat->minute, STR_PAD_LEFT) .".";

            if ($this->negative_time == null) {$response = "NEGATIVE TIME | Event not set. Set ENDDATE and/or ENDAT.";}

            $this->cat_message = $response;
        } else {
            $this->cat_message = $this->agent_input;
        }


        $this->node_list = array("cat"=>array("cat", "negative time"));
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
