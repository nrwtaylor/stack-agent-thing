<?php
/**
 * Queue.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Queue extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {

        $this->agent_name = "queue";
        $this->test= "Development code";
        $this->thing_report["info"] = "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";
    }

    function run() {

$this->doQueue();

$this->thing_report['sms'] = "Merp";

    }

// This is a place to work on associating things together.

    function newQueue() {}

    function doQueue($text = null) {

        $filtered_text = strtolower($text);
        $ngram_agent = new Ngram($this->thing,$filtered_text);

        foreach ($ngram_agent->ngrams as $index=>$ngram) {

            switch ($ngram) {
            case "tag":
            case "tag crow":
                echo "tagged " . $this->crow_thing->nuuid;
                $this->response .= "Tagged Crow. ";
                break;

            case "next":
            case "next crow":
            case "crow next":
                //echo "spawn";
                $this->nextCrow();
                $this->response .= "Got the next Crow. ";
                break;


            default:
                //$this->getCrow();

                //$this->response .= "No state change. ";
                // echo "not found => spawn()";
                //$this->spawn();
            }

        }

$queue_uuid = "23c9a180-28cf-4ca5-aefe-8ba3a5a88cd8";

// Get the queue
$this->getQueue($queue_uuid);

$this->setQueue();

    }

    function getQueue($queue_uuid = null) {

// Get the queue
if ($queue_uuid == null) return true;
//$queue_uuid = "23c9a180-28cf-4ca5-aefe-8ba3a5a88cd8";

$queue = new Thing($queue_uuid);

// And inventory the contents
//var_dump($queue->thing->associations);
$items = json_decode($queue->thing->associations);

var_dump($items);


// Try some other things.

// Last thing?  Is that helpful. Or useful.
$last_thing = $this->thing->db->priorGet();
//var_dump($last_thing);

$search_text = "";
$associated_things = $this->thing->db->associationSearch($search_text, $max = null);

$agent_search = "queue";
$max = 10000;
$a = $this->thing->db->agentSearch($agent_search, $max);
//var_dump($a);


    }

    function putinQueue() {

        // Make something and put it in the queue
        $picanic_thing = new Thing(null);
        $picanic_thing->Create("picanic", "queue" , "s/ jelly sandwich");

        $picnic_uuid = $picanic_thing->uuid;

        $this->queue->json->setField("associations");
        $this->queue->json->pushStream($picnic_uuid);

    }

    function setQueue() {

// Get the queue

$queue_uuid = "23c9a180-28cf-4ca5-aefe-8ba3a5a88cd8";
$this->queue = new Thing($queue_uuid);

// Make something and put it in the queue
$this->putinQueue();
//$picanic_thing = new Thing(null);
//$picanic_thing->Create("picanic", "queue" , "s/ jelly sandwich");


//$picnic_uuid = $picanic_thing->uuid;



//$this->queue->json->setField("associations");
//$this->queue->json->pushStream($picnic_uuid);

// And inventory the contents
//var_dump($queue->thing->associations);
//$items = json_decode($queue->thing->associations);

//var_dump($items);

$pos = -1; // Last added
$pos = 0; // First added

$this->queue->json->popStream($pos);

}

// What stack variables do we need to get to make this work?

    /**
     *
     */
    private function getNegativetime() {

        // And example of using another agent to get information the cat needs.
        $agent = new Negativetime($this->thing, "cat");
        $this->negative_time = $agent->negative_time; //negative time is asking

    }

    /**
     *
     * @return unknown
     */
    public function respond() {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "cat";

        $this->makeSMS();
        $this->makeChoices();

        $this->thing_report["info"] = "This is a cat keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

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
        $this->node_list = array("cat"=>array("cat", "dog"));
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

    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->doQueue($this->input);
        return false;
    }


}
