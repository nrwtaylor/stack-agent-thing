<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


ini_set("allow_url_fopen", 1);

class LimitedBeta {


	public $var = 'hello';


    function __construct(Thing $thing)
    {
        $this->agent_prefix = 'Agent "Limited Beta" ';

        $this->thing = $thing;
        $this->thing->elapsed_runtime();

        $thing_report['thing'] = $this->thing->thing;

		$this->agent_name = 'question';

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

		$this->api_key = $this->thing->container['api']['translink'];

        $this->word = $this->thing->container['stack']['word'];
        $this->email = $this->thing->container['stack']['email'];


        $this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid . '.');

        $this->current_time = $this->thing->json->time();
        $this->verbosity = 9;


        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;


        //$this->thing_report['message'] = "Echo " . $this->subject . ".";

		$this->sqlresponse = null;

		// Allow for a new state tree to be introduced here.
		$this->node_list = array("start"=>array("transit", "opt-in"));


		$this->readSubject();
        $this->respond();


        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());

        $this->thing_report['log'] = $this->thing->log;


		return;

		}

	public function limitedbeta()
    {


        $this->sms_message = 'LIMITED BETA | Your address has been forwarded to the development team.';
        $this->message = $this->word . ' is in limited beta. Your address has been forwarded to the development team.';




        $message = 'The stack received a limited beta request from ' . $this->from .'.';

        $thing = new Thing(null);

        $to = $this->email;

        $thing->Create($to, $thing->uuid , 's/ limited beta ' . $this->from);
        $thing->flagGreen();

        $thing_report['thing'] = $thing;
        $thing_report['message'] = $message;
        $thing_report['sms'] = $message;
        $thing_report['email'] = $message;

        $message_thing = new Message($thing, $thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

		return $this->message;
	}



	private function respond()
    {

		// Thing actions
		$this->thing->flagGreen();

		// Generate email response.
		$to = $this->thing->from;
		$from = "usermanager";


		//$this->thing->choice->Create($this->agent_name, $this->node_list, "start");
		//$choices = $this->thing->choice->makeLinks('start');


		$this->thing_report['thing'] = $this->thing->thing;
		$this->thing_report['choices'] = false;

        $this->thing_report['message'] = $this->sms_message;


//		$this->thing_report['sms'] = "QUESTION | " . $this->message . " | FORGET";
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->thing_report['message'];

//var_dump($this->thing->from);
//exit();

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;
		$this->thing_report['help'] = 'Beta program manager';

		return $this->thing_report;


	}

	private function nextWord($phrase) {


	}

	public function readSubject()
    {

        $this->limitedbeta();
	}



}




?>
