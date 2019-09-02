<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Stop {

	function __construct(Thing $thing) {

		$this->thing = $thing;
		$this->agent_name = 'stop';
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

		$this->node_list = array("stop"=>array("start", "opt-in"));

        $this->thing->log( $this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.</pre>');

        $this->variables_agent = new Variables($this->thing, "variables stop " . $this->from);
        $this->current_time = $this->thing->json->time();

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];



		//echo "construct email responser";

		// If readSubject is true then it has been responded to.
		// Forget thing.

//		$this->previous_state = $this->thing->getState('usermanager');

		//echo '<pre> Agent "Optin" previous usermanager state: ';echo $this->previous_state;echo'</pre>';

        $this->get();
		$this->readSubject();

        $this->set();
 		$this->respond();

		//$this->thing_report = $thing_report;

		$this->thing->flagGreen();

//		echo '<pre> Agent "Optin" completed</pre>';

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;


    	return;
	}



    function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

//        $this->thing->choice->save('usermanager', $this->state);

        return;
    }


    function get()
    {
        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        $this->thing->log( $this->agent_prefix .  'loaded ' . $this->counter . ".");

        $this->counter = $this->counter + 1;

        return;
    }

    public function makeSMS() {

        switch ($this->counter) {
            case 1:
                $sms = "STOP | Stackr stopped.  Text START.";
                break;

            case null;

            default:
                $sms = "STOP | Stopped.";

        }

            $sms .= " | counter " . $this->counter;

            $this->sms_message = $sms;
            $this->thing_report['sms'] = $sms;

    }


    public function makeEmail() {

        switch ($this->counter) {
            case 1:

                $subject = "Stop Stackr?";

                $message = "So an action you took (or someone else took) stopped Stackr.
                    <br>
                    ";
                break;

            case null;

            default:
                $message = "STOP | Acknowledged. " . $this->web_prefix . "privacy";

        }

            $this->message = $message;
            $this->thing_report['email'] = $message;

    }


    public function makeChoices()
    {

            $choices = $this->thing->choice->makeLinks('stop');

            $this->choices = $choices;
            $this->thing_report['choices'] = $choices;

    }



	public function respond() {

		// Thing actions

		// New user is triggered when there is no nom_from in the db.
		// If this is the case, then Stackr should send out a response
		// which explains what stackr is and asks either
		// for a reply to the email, or to send an email to opt-in@<email postfix>.


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

        $this->thing_report['help'] = $this->agent_prefix  .'responding to an instruction to opt-in.';


		return;
	}



	public function readSubject() {
        $this->stop();
//		$this->thing->choice->Choose("new user");
		return;		

	}


	function stop() {

        // Call the Usermanager agent and update the state
        $agent = new Usermanager($this->thing, "usermanager stop");
        $this->thing->log( $this->agent_prefix .'called the Usermanager to update user state to stop.' );


		return;
	}













}









?>
