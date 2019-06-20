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

class Runtime extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init() {

        $this->keywords = array('next', 'accept', 'clear', 'drop', 'add', 'new');
        $this->test= "Development code"; // Always iterative.

//        $this->runtime = false;

    }


    /**
     *
     */
    function set() {

        if ($this->runtime == false) {return;}

        $this->runtime->setVariable("refreshed_at", $this->current_time);
        $this->runtime->setVariable("minutes", $this->minutes);

        $this->thing->log( $this->agent_prefix .' saved ' . $this->minutes . ".", "DEBUG" );

        //      $this->thing->json->writeVariable( array("run_at", "day"), $this->day );
        //      $this->thing->json->writeVariable( array("run_at", "hour"), $this->hour );
        //      $this->thing->json->writeVariable( array("run_at", "minute"), $this->minute );
        //      $this->thing->json->writeVariable( array("run_at", "refreshed_at"), $this->current_time );

    }


    /**
     *
     * @return unknown
     */
    function getRuntime() {
        if (!isset($this->run_time)) {
            if (isset($run_time)) {
                $this->run_time = $run_time;
            } else {
                $this->run_at = "Meep";
            }
        }
        return $this->run_time;
    }


    /**
     *
     * @param unknown $run_at (optional)
     */
    function get($run_at = null) {
        $this->runtime = new Variables($this->thing, "variables runtime " . $this->from);

        if ($this->runtime == false) {return;}

        $this->minutes = $this->runtime->getVariable("minutes");
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractRuntime($input = null) {

        $this->minutes = "X";
        $periods = array(1440=>array("d", "days", "dys", "dys", "dy", "day"),
            60=>array("h", "hours", "hrs", "hs", "hr"),
            1=>array("minutes", "m", "mins", "min", "mn"));


        $pieces = explode(" ", $input);
        $previous_piece = null;

        $list = array();

        foreach ($pieces as $key=>$piece) {

            foreach ($periods as $multiplier=>$period) {

                //echo $piece . " " . $period;
                //echo "<br>";

                foreach ($period as $period_name) {

                    if (($period_name == $piece) and (is_numeric($previous_piece))) {

                        $list[] = $previous_piece * $multiplier;
                    } elseif (is_numeric($piece)) {
                        // skip

                    } elseif (is_numeric(str_replace($period_name, "", $piece))) {
                        $list[] = str_replace($period_name, "", $piece) * $multiplier;

                    }
                }

            }

            $previous_piece = $piece;
        }

        // If nothing found assume a lone number represents minutes
        if (count($list) == 0) {
            foreach ($pieces as $key=>$piece) {

                if ($this->is_decimal($piece)) {
                    // Assue this is hours
                    $list[] = $piece * 60;
                } elseif (is_numeric($piece)) {

                    $list[] = $piece;

                }

            }
        }

        if (count($list) == 1) { $this->minutes = $list[0];}

        return $this->minutes;
    }


    /**
     *
     * @param unknown $val
     * @return unknown
     */
    function is_decimal( $val ) {
        return is_numeric( $val ) && floor( $val ) != $val;
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractTime($input = null) {
        $this->minutes = "X";
        $days = array(22=>array("default"),
            15=>array("quarter hour", "quarter", "1/4", "0.25"),
            30=>array("half hour", "half hour", "half", "0.5"),
            60=>array("hour", "hr"),
            1440=>array("day"));

        foreach ($days as $key=>$day_names) {


            if (strpos(strtolower($input), strtolower($key)) !== false) {
                $this->minutes = $key;
                break;
            }

            foreach ($day_names as $day_name) {

                if (strpos(strtolower($input), strtolower($day_name)) !== false) {
                    $this->minutes = $key;
                    break;
                }
            }
        }


        return $this->minutes;
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
        $sms_message = "RUNTIME";

        $sms_message .= $this->response;

        if ($this->minutes == "X") {
            $sms_message .= " Set RUNTIME.";
        }

        $sms_message .= " | nuuid " . strtoupper($this->runtime->nuuid);
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

        $response_text = "Please set RUNTIME.";
        if ($this->minutes != false) {
            $response_text = "" . $this->minutes . " minutes.";
        }
        $this->response .= " | " . $response_text;

        // Generate email response.

        $to = $this->thing->from;
        $from = "runtime";

        //$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
        $this->thing_report['choices'] = $choices;

        //$this->makeTXT();

        $this->makeSMS();

        $test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';

        $test_message .= '<br>' . $this->sms_message;

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;




        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        } else {
            $this->thing_report['info'] = 'Agent input was "' . $this->agent_input . '".' ;
        }

        $this->makeTXT();

        $this->thing_report['help'] = 'This is the runtime manager.';

        return;
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
     * @return unknown
     */
    public function readSubject() {
        $this->response = null;
        $this->num_hits = 0;

//        $keywords = $this->keywords;

        if (strpos($this->agent_input, "runtime") !== false) {
            return;
        }

        if (strpos($this->input, "reset") !== false) {
            $this->minutes = "X";
	
            return;
        }


        $this->extractRuntime($this->input);

        if ($this->minutes == "X") {
            $this->extractTime($this->input);
        }



        if ($this->agent_input == "extract") {return;}


        $pieces = explode(" ", strtolower($this->input));

        if ($this->minutes == "X") {

            $this->get();
            return;
        }

    }

}
