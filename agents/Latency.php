<?php
namespace Nrwtaylor\StackAgentThing;

//require_once '/var/www/html/stackr.ca/agents/message.php';
//echo "Watson says hi<br>";

class Latency
{
	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {

        $this->agent_input = $agent_input;
        $this->thing = $thing;
        $this->agent_name = 'latency';

        $this->queue_time = $this->thing->elapsed_runtime();
        $this->start_time = $this->thing->elapsed_runtime();

 		$this->thing_report  = array("thing"=>$this->thing->thing);

        // So I could call
        $this->test = false;
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
        // I think.
        // Instead.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        //$this->sqlresponse = null;

        $this->created_at = $thing->thing->created_at;

        $this->current_time = $this->thing->json->time();

        $this->getLatency();

        $this->node_list = array("latency"=>array("latency","tallygraph"), "null"=>array());

        $this->thing->log('<pre> Agent "Latency" running on Thing ' . $this->thing->nuuid . '.</pre>',"INFORMATION");

        // Probably an unnecessary call, but it updates $this->thing.
        // And we need the previous usermanager state.

        $this->thing->Get();

        $this->current_state = $this->thing->getState('usermanager');

		// create container and configure
		$this->api_key = $this->thing->container['api']['watson'];

		$this->readSubject();

        if ($this->agent_input == null) {		
		    $this->thing_report = $this->respond();
        }


        $this->set();
		$this->thing->log('Agent "Latency" ran for ' . number_format($this->thing->elapsed_runtime()-$this->start_time)."ms.", "OPTIMIZE");

        $this->thing_report['log'] = $this->thing->log;

        return;

    }

    function makeSMS()
    {
        $this->getQueuetime();
        $rtime = $this->thing->elapsed_runtime() - $this->start_time;

        $this->node_list = array("latency"=>array("latencygraph"));

        $this->sms_message = "LATENCY";
        $this->sms_message .= " | qtime " . number_format($this->queue_time) . "ms";
        $this->sms_message .= " | rtime " . number_format($rtime). "ms"; 
        $this->sms_message .= " | etime " . number_format($this->thing->elapsed_runtime()). "ms"; 

        $this->sms_message .= " | TEXT PING";
        $this->thing_report['sms'] = $this->sms_message;

    }

    public function makeChoices()
    {

        if ($this->from == "null@stackr.ca") {
            $this->thing->choice->Create($this->agent_name, $this->node_list, "null");
            $choices = $this->thing->choice->makeLinks("null");
        } else {
            $this->thing->choice->Create($this->agent_name, $this->node_list, "latency");
            $choices = $this->thing->choice->makeLinks('latency');
        }

        $this->thing_report['choices'] = $choices;
        return;
    }


    private function getRuntime()
    {

        //if (!isset($this->start_time)) {$this->start_time = time();}

        $this->run_time = $this->thing->elapsed_runtime() - $this->start_time;

    }

    private function getQueuetime()
    {
        if (!isset($this->queue_time)) {
            $this->queue_time = $this->start_time;
        }
    }

    private function set()
    {

        $this->current_time = $this->thing->json->time();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("latency", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("latency", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->thing->json->setField("variables");
        $queue_time = $this->thing->json->readVariable( array("latency", "queue_time") );
        $run_time = $this->thing->json->readVariable( array("latency", "run_time") );


        if (($queue_time == false) and ($run_time==false) ) {
            $this->getLatency();

            $this->readSubject();

            $this->thing->json->writeVariable( array("latency", "queue_time"), $this->queue_time );
            $this->thing->json->writeVariable( array("latency", "run_time"), $this->run_time );

        }
    }

    function getLatency()
    {
        $this->getQueuetime();
        $this->getRuntime();
        $this->elapsed_time = $this->queue_time + $this->run_time;
    }

    function isNumeric($number = null)
    {
        return is_numeric($number);
    }
// -----------------------

	private function respond()
    {
		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;
		$from = "latency";

		$subject = 's/pingback '. $this->current_state;	

		$message = 'Latency checker.';

		$received_at = strtotime($this->thing->thing->created_at);

        $ago = $this->thing->human_time ( time() - $received_at );

        $this->makeChoices();
        $this->makeSMS();

		//$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['email'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;


        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->thing_report['keyword'] = 'pingback';
        $this->thing_report['help'] = 'Latency is how long the message is in the stack queue.';

		return $this->thing_report;
	}



	public function readSubject()
    {
		return;
	}

}

?>
