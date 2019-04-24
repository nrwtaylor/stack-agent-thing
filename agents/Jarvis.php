<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Jarvis {

	public $var = 'hello';


    function __construct(Thing $thing, $agent_input = null) {

        $this->agent_input = $agent_input;

		if ($agent_input == null) {
			$this->requested_agent = "Jarvis.";
		} else {
			$this->requested_agent = $agent_input;

			echo $this->requested_agent;
			

		}


		$this->thing = $thing;
		$this->agent_name = 'hey';
	        $thing_report['thing'] = $this->thing->thing;

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

		$this->thing->log( '<pre> Agent "Jarvis" running on Thing ' . $this->uuid . '</pre>' );
		$this->thing->log( '<pre> Agent "Jarvis" received this Thing "' . $this->subject . '"</pre>');


		$this->thing->log( 'Agent "Jarvis" startJarvis()' );
        $this->startJarvis();

		$this->readSubject();

        $this->thing_report = $this->respond();

        $this->thing_report['info'] = 'Hey';
      	$this->thing_report['num_hits'] = $this->num_hits;

		$this->thing->log( 'Agent "Jarvis" completed' );

		return;

		}




    public function startJarvis($type = null)
    {
        $litany = array("Hello Ironman.",
            "Good morning. It's 7 A.M. The weather in Malibu is 72 degrees with scattered clouds. The surf conditions are fair with waist to shoulder highlines, high tide will be at 10:52 a.m.",
            "As always sir, a great pleasure watching you work.",
            "Sir, take a deep breath.",
            "Working on it, sir. This is a prototype.", 
            "Oh, hello sir.",
            "Yes, sir.",
            "All wrapped up here, sir. Will there be anything else?",
            'Sir, received "'. $this->subject. '"');

        $key = array_rand($litany);
        $value = $litany[$key];

        $this->message = $value;
        $this->sms_message = $value;

	    $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable( array("jarvis", "requested_agent"), $this->requested_agent );

        $this->thing->json->setField("variables");
        $time_string = $this->thing->time();
         $this->thing->json->writeVariable( array("jarvis", "refreshed_at"), $time_string );

        return $this->message;
    }

// -----------------------

	private function respond()
    {
		// Thing actions
		$this->thing->flagGreen();

		// Generate email response.
		$to = $this->thing->from;
		$from = "jarvis";

		$this->thing->choice->Create($this->agent_name, $this->node_list, "start");
		$choices = $this->thing->choice->makeLinks('start');
	        $this->thing_report['choices'] = $choices;


		$this->sms_message = "JARVIS | " . $this->sms_message . " | REPLY HELP";
		$this->thing_report['sms'] = $this->sms_message;

		$this->thing_report['email'] = $this->message;
        $this->thing_report['message'] = $this->message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = "This is Just a Rather Very Intelligent System.";


		return $this->thing_report;
	}

    public function test()
    {
echo "merp";
        $this->test = false; // good
        return "green";
    }

	public function readSubject()
    {
		$this->response = null;
		return;
	}
}
