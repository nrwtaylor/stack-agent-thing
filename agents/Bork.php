w<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Bork {

	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;
		if ($agent_input == null) {
			$this->requested_agent = "Bork.";
		} else {
			$this->requested_agent = $agent_input;
		}


		$this->thing = $thing;
        $this->thing_report['thing'] = $thing;
		$this->agent_name = 'bork';

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

		$this->api_key = $this->thing->container['api']['translink'];

		$this->retain_for = 4; // Retain for at least 4 hours.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        $this->num_hits = 0;

		$this->sqlresponse = null;

		// Allow for a new state tree to be introduced here.
		$this->node_list = array("start"=>array("useful", "useful?"));

		$this->thing->log( 'Agent "Bork" running on Thing ' . $this->thing->nuuid . '' );
		$this->thing->log( 'Agent "Bork" received this Thing "' . $this->subject . '"');

        $this->thing->log( 'Agent "Bork" received this Agent Input "' . $agent_input . '"');


        $this->startBork();


		$this->readSubject();

		if ($this->agent_input == null) {
			$this->thing->log( '<pre> Agent "Bork" respond() </pre>' );

			$this->respond();
		}

        $this->thing_report['info'] = 'Bork';
      	$this->thing_report['help'] = 'BORK';
      	$this->thing_report['num_hits'] = $this->num_hits;

		$this->thing->log( 'Agent "Bork" completed.' );

        $this->thing_report['log'] = $this->thing->log;


		return;

    }

    public function startBork($type = null)
    {

        $this->message = $this->requested_agent;
		$this->sms_message = $this->requested_agent;

        $this->thing->json->setField("variables");
  	    $names = $this->thing->json->writeVariable( array("bork", "requested_agent"), $this->requested_agent );
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable( array("bork", "refreshed_at"), $time_string );

        return $this->message;
    }

// -----------------------

	private function respond()
    {

		// Thing actions
		$this->thing->flagGreen();

		// Generate email response.
		$to = $this->thing->from;
		$from = "bork";

		$this->thing->choice->Create($this->agent_name, $this->node_list, "start");
		$choices = $this->thing->choice->makeLinks('start');
        $this->thing_report['choices'] = $choices;

		$this->sms_message = "BORK | " . $this->sms_message . " | REPLY ?";
		$this->thing_report['sms'] = $this->sms_message;

		$this->thing_report['email'] = $this->message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->thing->log( 'Agent "Bork" responded "' . $this->sms_message . '".' );

		return $this->thing_report;
	}


	public function readSubject()
    {
		$this->response = null;
		return;
	}

}

?>
