<?php
/**
 * Eiyu.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Eiyu {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function __construct(Thing $thing, $text = null) {


        $this->agent_name = 'control';
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
        $this->test= "Development code";

        $this->agent_input = $text;

        $this->thing = $thing;

        $this->thing_report  = array("thing"=>$this->thing->thing);
        $this->start_time = $this->thing->elapsed_runtime();



        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;


        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log($this->agent_prefix . "received this Thing ".  $this->subject . '".');


        $this->readSubject();

        //$this->getNegativetime();

        $this->thing_report = $this->respond();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format( $this->thing->elapsed_runtime() - $this->start_time ) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;

        return;

    }


    /**
     *
     */
    function getNegativetime() {

        $agent = new Negativetime($this->thing, "control");
        $this->negative_time = $agent->negative_time; //negative time is asking

    }


    // -----------------------

    /**
     *
     * @return unknown
     */
    private function respond() {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "control";


        if ($this->agent_input == null) {
            $array = array('Wumpus rounds start 1pm 22 June 2018 QEP. Check-in on VE7RVF. Rendevous at CERV. Ask where is the Wumpus.');

            $k = array_rand($array);
            $v = $array[$k];

            $response = "CONTROL | " . $v . "";


            $this->control_message = $response;
        } else {
            $this->control_message = $this->agent_input;
        }

        $this->makeSMS();
        $this->makeChoices();

        $this->thing_report["info"] = "This is control telling folk about a game.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }


    /**
     *
     */
    function makeSMS() {
        $this->node_list = array("control"=>array("control"));
        $this->sms_message = "" . $this->control_message;
        //if ($this->negative_time < 0) {
        //    $this->sms_message .= " " .$this->thing->human_time($this->negative_time/-1) . ".";
        //}
        $this->thing_report['sms'] = $this->sms_message;

    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create('channel', $this->node_list, "control");
        $choices = $this->thing->choice->makeLinks('control');
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



return;
?>
