<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
//require '/var/www/html/stackr.ca/vendor/autoload.php';
//require_once '/var/www/html/stackr.ca/agents/message.php';


ini_set("allow_url_fopen", 1);

class Hey {

	public $var = 'hello';


    function __construct(Thing $thing, $input = null) {

		if ($input == null) {
			$this->requested_agent = "Hey";
		} else {
			$this->requested_agent = $input;

			//echo $this->requested_agent;
			

			//echo $input;
			//exit();
		}


		$this->thing = $thing;
		$this->agent_name = 'hey';
//	        $thing_report['thing'] = $this->thing->thing;

                $this->thing_report['thing'] = $this->thing->thing;

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

		$this->thing->log( '<pre> Agent "Hey" running on Thing ' . $this->thing->nuuid . '.</pre>' );
		$this->thing->log( '<pre> Agent "Hey" received this Thing "' . $this->subject . '".</pre>');


		$this->thing->log( '<pre> Agent "Hey" startHey() </pre>' );
                $this->startHey();


		$this->readSubject();

		//if ($input != 'screen') {
    	$this->thing->log( '<pre> Agent "Hey" respond() </pre>' );

		$this->respond();

        $this->thing_report['info'] = 'Hey';
        $this->thing_report['help'] = 'HEY';
        $this->thing_report['num_hits'] = $this->num_hits;


		$this->thing->log( '<pre> Agent "Hey" completed.</pre>' );

        $this->thing_report['log'] = $this->thing->log;

		return;

		}

    public function startHey($type = null)
    {

        $litany = array("Meh.", "Hhhhhh.", "Hi", 'Received "'. $this->subject. '"');
        $key = array_rand($litany);
        $value = $litany[$key];

		$this->message = $value;
		$this->sms_message = $value;

	    $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable( array("hey", "requested_agent"), $this->requested_agent );

        //if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("hey", "refreshed_at"), $time_string );
        //}

        return $this->message;
    }


// -----------------------

	private function respond()
    {

		// Thing actions
		$this->thing->flagGreen();

		// Generate email response.
		$to = $this->thing->from;

		$from = "hey";

		$this->thing->choice->Create($this->agent_name, $this->node_list, "start");
		$choices = $this->thing->choice->makeLinks('start');
	        $this->thing_report['choices'] = $choices;


		$this->sms_message = "HEY | " . $this->sms_message . " | REPLY HELP";
		$this->thing_report['sms'] = $this->sms_message;

		$this->thing_report['email'] = $this->message;
        $this->thing_report['message'] = $this->message;


        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

		return $this->thing_report;
	}

	public function readSubject()
    {
		$this->response = null;
		return;
	}

}




?>
