<?php
/**
 * Dog.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Dog extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
//    function __construct(Thing $thing, $text = null) {
    function init() {

        $this->thing_report["help"] = "An agent which pretends to be a dog. Often asleep. Try CAT.";

        $this->negative_time = null;

        $this->getNegativetime();
        $this->getFlag();

    }

    function run() {
        $this->makeResponse();
        $this->makeSms();
    }

    // -----------------------


    /**
     *
     */
    function getNegativetime() {

        $agent = new Negativetime($this->thing, "dog");
        $this->negative_time = $agent->negative_time; //negative time is asking

        //$this->time_remaining = -1 * $this->negative_time;

    }


    /**
     *
     */
    function getFlag() {

        $agent = new Flag($this->thing, "flag");
        $this->flag = $agent->state; //negative time is asking

        //var_dump($this->state);
    }


    function makeResponse() {

        if ($this->agent_input == null) {
            //var_dump($this->negative_time);
            switch (true) {
            case (($this->negative_time <= 0) and ($this->flag == "red")):
                $array = array('Bark', 'Woof');
                $k = array_rand($array);
                $v = $array[$k];
                $response = "DOG | " . $v . ". Check on the cat.";

                // Bark like crazy.  We're late.
                break;
            case ($this->negative_time > 150):
                $response = "DOG | Zzzzz.";
                break;
            case ($this->negative_time > 120):
                $array = array('ready?');
                $k = array_rand($array);
                $v = $array[$k];
                $response = "DOG | " . strtolower($v) . ". " . $this->thing->human_time($this->negative_time ) .".";

                break;

            case ($this->negative_time > 0):
                // https://www.psychologytoday.com/us/blog/canine-corner/201211/how-dogs-bark-in-different-languages
                $array = array('bark', 'woof', 'grrr', 'ruff-ruff', 'woof-woof', 'bow-wow', 'yap-yap', 'yip-yip');
                $k = array_rand($array);
                $v = $array[$k];

                $response = "DOG | " . strtolower($v) . ". " . $this->thing->human_time($this->negative_time ) .".";
                break;


            default:
                $response =  "DOG | Zzzzzzzz.";
            }


            $this->dog_message = $response;
        } else {
            $this->dog_message = $this->agent_input;
        }

    }

    /**
     *
     * @return unknown
     */

    public function respond() {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "dog";

        $this->makeSMS();
        $this->makeChoices();

//        $this->thing_report["info"] = "This is a cat keeping an eye on how late this Thing is.";
//        $this->thing_report["help"] = "This is about being inscrutable.";

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

        if (!isset($this->dog_message)) {$this->makeResponse();}

        $this->node_list = array("dog"=>array("dog", "cat"));
        $this->sms_message = "" . $this->dog_message;
        $this->thing_report['sms'] = $this->sms_message;

    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create('channel', $this->node_list, "dog");
        $choices = $this->thing->choice->makeLinks('dog');
        $this->thing_report['choices'] = $choices;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject() {
        return false;
    }


}
