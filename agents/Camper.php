<?php
/**
 * Cat.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Camper extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
        $this->agent_name = "camper";
        $this->test= "Development code";

       $this->thing_report["info"] = "This is a camper in a park with a picnic basket.";
        $this->thing_report["help"] = "This is finding picnics. And getting your friends to join you. Text BEAR. Or RANGER. Or PARK TRIVIA.";


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
        $agent = new Negativetime($this->thing, "camper");
        $this->negative_time = $agent->negative_time; //negative time is asking

    }


    /**
     *
     * @return unknown
     */

    public function respond() {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "camper";

        $this->makeSMS();
        $this->makeChoices();

//        $this->thing_report["info"] = "This is a camper in a park with a picnic basket.";
//        $this->thing_report["help"] = "This is finding picnics. And getting your friends to join you. Text BEAR. Or RANGER.";

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
        $this->node_list = array("camper"=>array("camper", "bear", "ranger"));
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create('channel', $this->node_list, "camper");
        $choices = $this->thing->choice->makeLinks('bear');
        $this->thing_report['choices'] = $choices;
    }

    function doBear($text = null) {
        // Yawn.

//        $this->getNegativeTime();

        if ($this->agent_input == null) {
            $array = array('Get a pic-a-nic basket. Go to a numbered place on the map. Broadcast a repeating beacon on ' . $this->primary_channel . '. Contact ' . $this->contact .'.');
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
