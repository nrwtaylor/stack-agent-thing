<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Messageidentifier extends Agent
{

    public $var = 'hello';
    function init() {
//$this->identity = "test";
//        $this->start_time = microtime(true);

//        if ($agent_input == null) {$agent_input = "";}

//        $this->agent_input = $agent_input;

//        $this->keyword = "identity";

//        $this->agent_prefix = 'Agent "Identity" ';

//        $this->thing = $thing;
//        $this->thing_report['thing'] = $thing;

        $this->test= "Development code"; // Always

//        $this->uuid = $thing->uuid;
//        $this->to = $thing->to;
//        $this->from = $thing->from;
//        $this->subject = $thing->subject;
//        $this->sqlresponse = null;
$this->identifier = $this->agent_input;

        $this->node_list = array("identity"=>array("on"=>array("off")));

// This isn't going to help because we don't know if this
// is the base.
//        $this->state = "off";
//        $this->thing->choice->load($this->keyword);

        $this->current_time = $this->thing->json->time();

$this->thing_report['sms'] = "MESSAGE IDENTITY | " . $this->identifier;

		}


    function set($requested_state = null)
    {
//        if ($time_string == false) {
            $this->thing->json->setField("variables");
//            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("message_identifier", "identifier"), $this->identifier );
//        }

    }


    function get()
    {
    }

    function read()
    {
//        return $this->state;
    }

	public function respond()
    {

		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = $this->keyword;


        $choices = false;
		$this->thing_report['choices'] = $choices;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }
        $this->thing_report['help'] = 'This is your Identity.  You can turn your Identity ON and OFF.';

		return;


	}


    public function readSubject() 
    {
	}


}

