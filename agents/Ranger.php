<?php
/**
 * Cat.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Ranger extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
        $this->agent_name = "ranger";
        $this->test= "Development code";
        $this->thing_report["info"] = "This is a ranger who keeps an eye open for picnickers. And bears.";
        $this->thing_report["help"] = "Find the bears. However you can. Text BEAR.";

        $this->game_name = "pic-a-nic";

        $this->contact = "VE7RVF control";
        $this->primary_channel = "146.580";

        if ($this->game_name == "pic-a-nic") {
            $this->contact = "146.580 CONTROL";
            $this->primary_channel = "146.565";
        }


    }

    /**
     *
     */
    private function getNegativetime() {

        // And example of using another agent to get information the cat needs.
        $agent = new Negativetime($this->thing, "ranger");
        $this->negative_time = $agent->negative_time; //negative time is asking

    }


    /**
     *
     * @return unknown
     */

    public function respond() {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "ranger";

        $this->makeSMS();
        $this->makeChoices();

        $this->thing_report["info"] = "This is a ranger in a park.";
        $this->thing_report["help"] = "This is about finding bears and picnics. First. Text BEAR.";

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
        $this->node_list = array("ranger"=>array("ranger", "bear"));
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create('channel', $this->node_list, "cat");
        $choices = $this->thing->choice->makeLinks('cat');
        $this->thing_report['choices'] = $choices;
    }

    function doCat($text = null) {
        // Yawn.

        $this->getNegativeTime();

        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $this->response = strtolower($v);
            $this->cat_message = $this->response;
        } else {
            $this->cat_message = $this->agent_input;
        }

    }

    function doRanger($text = null) {
        // Yawn.

//        $this->getNegativeTime();

        if ($this->agent_input == null) {
            $array = array('Find the picanic(s) before the bears do and warn the campers. Or try catch a bear. Contact ' . $this->contact .'. Monitor ' . $this->primary_channel .'.');
            $k = array_rand($array);
            $v = $array[$k];

            $this->response = $v;
            $this->ranger_message = $this->response;
        } else {
            $this->ranger_message = $this->agent_input;
        }

    }



    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->doRanger($this->input);
        return false;
    }


}
