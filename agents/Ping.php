<?php
namespace Nrwtaylor\StackAgentThing;

class Ping
{
	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;
        $this->thing = $thing;
        $this->agent_name = 'ping';

 		$this->thing_report['thing']  = $thing;

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

        $this->node_list = array("ping"=>array("pong"));

        $this->thing->log('Agent "Ping" running on Thing ' . $this->thing->nuuid . '.', "INFORMATION");

		// create container and configure
		$this->api_key = $this->thing->container['api']['watson'];

		$this->readSubject();
		$this->respond();

		$this->thing->log('Agent "Ping" completed.', "INFORMATION");

        $this->thing_report['log'] = $this->thing->log;
        $this->thing_report['response'] = $this->response;

		return;

    }


// -----------------------

	private function respond()
    {
		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.
		$to = $this->thing->from;
		$from = "ping";

        $this->makeSms();
        $this->makeMessage();
		$this->thing_report['email'] = $this->sms_message;

		//$this->thing_report['choices'] = false; 

        if ($this->agent_input == null) {
                $message_thing = new Message($this->thing, $this->thing_report);
                $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['keyword'] = 'pingback';
        $this->thing_report['help'] = 'Useful for checking the stack.';

		return $this->thing_report;

	}

    public function makeSms()
    {

        $received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( time() - $received_at );

        $this->sms_message = "PING | A message from this Identity pinged us.";
        $this->sms_message .= " | Received " . $ago . " ago.";

        $this->sms_message .= " | TEXT WATSON";
        $this->thing_report['sms'] = $this->sms_message;

    }

    public function makeMessage()
    {

        $received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( time() - $received_at );


        $message = "A message from this Identity pinged us.";
        $message .= " Received " . $ago . " ago.";

        $this->sms_message = $message;
        $this->thing_report['message'] = $message;

    }


	public function readSubject()
    {
        $this->response = "Responded to a ping.";
		return;
	}

}
