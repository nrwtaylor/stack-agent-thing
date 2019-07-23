<?php
/**
 * VE7RVFCat.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class VE7RVF extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
        $this->agent_name = "ve7rvf";
        $this->test= "Development code";
        $this->thing_report["info"] = "This is a repeater operated by VECTOR in the Amateur Radio Service.";
        $this->thing_report["help"] = "This is about helping you communicate in this channel. Try BEAR. OR RANGER.";

    }

    /**
     *
     * @return unknown
     */

    public function respond() {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "ve7rvf";

        $this->makeSMS();

        $this->thing_report["info"] = "This is a repeater operated by VECTOR in the Amateur Radio Service.";
        $this->thing_report["help"] = "This is about helping you communicate in this channel. Try BEAR. OR RANGER.";

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
        $this->node_list = array("ve7rvf"=>array("bear", "ranger"));
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }

    function doRepeater($text = null) {
        // Yawn.

        if ($this->agent_input == null) {
            $array = array('VE7RVF 145.450 offset -0.600 TX freq 144.850 TX tone 100 TSQL output tone X note VE7RVF (location: North)');
            $k = array_rand($array);
            $v = $array[$k];

            $this->response = $v;
            $this->message = $this->response;
        } else {
            $this->message = $this->agent_input;
        }

    }

    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->doRepeater($this->input);
        return false;
    }


}
