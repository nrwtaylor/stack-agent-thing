<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from Tick

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Thought {

    function __construct(Thing $thing, $agent_input = null)
    {

        if ($agent_input == null) {
            $this->agent_input = $agent_input;
        }

        $this->thing = $thing;
        $this->agent_name = 'thought';
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';

        $this->thing_report['thing'] = $this->thing->thing;

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
        // I think.
        // Instead.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		//$this->sqlresponse = null;

		$this->node_list = array("snow"=>array("stop","snow"));

        $this->thing->log( 'Agent "Thought" running on Thing '. $this->thing->nuuid . '.');

        //$this->variables_agent = new Variables($this->thing, "variables snow " . $this->from);
        $this->current_time = $this->thing->json->time();

        $this->get();
		$this->readSubject();

        // frame

        $this->variable = 10; //s
        $this->thought();

        // frame

        $this->set();
        if ($this->agent_input == null) {
 		    $this->respond();
        }

		$this->thing->flagGreen();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;

    	return;
	}



    function set()
    {
        //$this->variables_agent->setVariable("counter", $this->counter);
        //$this->thing->setVariable("refreshed_at", $this->current_time);

//        $this->thing->choice->save('usermanager', $this->state);

        return;
    }


    function get()
    {
        //$this->counter = $this->variables_agent->getVariable("counter");
        //$this->refreshed_at = $this->thing->getVariable("refreshed_at");

        //$this->thing->log($this->agent_prefix .  'loaded ' . $this->counter . ".");

        //$this->counter = $this->counter + 1;

        return;
    }

    private function makeSMS() {

        switch (rand(1,3)) {
            case 1:
                $sms = "THOUGHT";
                break;
            case 2:
                $sms = "THOUGHT";
                break;

            case null;

            default:
                $sms = "THOUGHT";

        }


            $this->sms_message = $sms;
            $this->thing_report['sms'] = $sms;

    }


    private function makeEmail() {

        switch (rand(1,3)) {
            case 1:
                $subject = "Thought request received";
                $message = "Thought.\n\n";
                break;

            case null;

            default:
               $subject = "Thought request received";
               $message = "Thought.\n\n";
        }

            $this->message = $message;
            $this->thing_report['email'] = $message;

    }


    private function makeChoices()
    {

            $choices = $this->thing->choice->makeLinks('thought');

            $this->choices = $choices;
            $this->thing_report['choices'] = $choices;

    }



	public function respond() {

		// Thing actions
		$this->thing->flagGreen();

		// Get the current user-state.
        $this->makeSMS();
        $this->makeEmail();
        $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['sms'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] = $this->agent_prefix . 'responding to the word snow';


		return;
	}



	public function readSubject() 
    {
        // Ignore subject.
		return;
	}


	function thought() {

        // Call the Usermanager agent and update the state
        // Stochastically call snow.
        $thingreport = $this->thing->db->reminder($this->from, array('s/', 'stack record'), array('ant', 'email', 'transit' , 'translink'));
                $things = $thingreport['thing'];
        
        $thing = $things[array_rand($things)];
        $uuid = $thing['uuid'];

        // 
        $this->thought =  $thing['task'];

        sleep(rand(1, $this->variable));



        $this->thing->log( $this->agent_prefix .' says, "Thought."' );


		return;
	}













}









?>
