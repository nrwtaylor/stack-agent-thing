<?php
/**
 * Cat.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Bear extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
        $this->agent_name = "bear";
        $this->test= "Development code";
        $this->thing_report["info"] = "This is a ranger who keeps an eye open for picnickers. And bears.";
        $this->thing_report["help"] = "Find the bears. However you can. Text BEAR.";
    }

    /**
     *
     */
    private function getNegativetime() {

        // And example of using another agent to get information the cat needs.
        $agent = new Negativetime($this->thing, "bear");
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

        $this->thing_report["info"] = "This is a bear in a park.";
        $this->thing_report["help"] = "This is finding picnics. And getting your friends to join you. Text RANGER.";

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
        $this->node_list = array("bear"=>array("bear", "ranger"));
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create('channel', $this->node_list, "bear");
        $choices = $this->thing->choice->makeLinks('bear');
        $this->thing_report['choices'] = $choices;
    }

    function doBear($text = null) {
        // Yawn.

//        $this->getNegativeTime();

        if ($this->agent_input == null) {
            $array = array('Find the picanic(s). There is at least one picnic basket broadcasting on 146.580. Contact VE7RVF control for help and support.');
            $k = array_rand($array);
            $v = $array[$k];

            $this->response = $v;
            $this->bear_message = $this->response;
        } else {
            $this->bear_message = $this->agent_input;
        }

    }



    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->doBear($this->input);
        return false;
    }


}

