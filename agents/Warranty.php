<?php
// Start by picking a random thing and seeing what needs to be done.
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Warranty
{

	function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;
		$this->thing = $thing;
		$this->agent_name = 'warranty';
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';

        $this->thing_report['thing'] = $thing;

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];
        $this->sms_address = $thing->container['stack']['sms_address'];


		$this->node_list = array("new user"=>array("opt-in"));

        $this->thing->log( $this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.</pre>', "INFORMATION");

        $this->variables_agent = new Variables($this->thing, "variables warranty " . $this->from);

        $this->current_time = $this->thing->json->time();

        $this->get();
		$this->readSubject();

        $this->set();
 		$this->respond();

		$this->thing->flagGreen();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

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

        $this->thing->log( $this->agent_prefix .  'loaded ' . $this->counter . ".", "DEBUG");

        $this->counter = $this->counter + 1;

        return;
    }

    private function makeSMS() {

        switch ($this->counter) {
            case 1:
                $sms = "WARRANTY | This service is provided in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.";  
                $sms .= " | Text OPT-IN";
                break;
            case 2:
                $sms = "WARRANTY | This service is provided in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.";
                $sms .= " | Text OPT-IN"; 
               break;
            case null;

            default:
                $sms = "WARRANTY | This service is provided in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.";

        }

        $sms .= " | counter " . $this->counter;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function makeMessage()
    {
//var_dump(        $this->usermanager->state );

        switch ($this->usermanager->state) {
            case 'start':

                $message = "'warranty' 
                    saw that you haven't yet Opted-in. 
                    If you wish to Opt-in, please text optin or opt-in 
                    to " . $this->sms_address . ".\n\n
                    This service is provided in the hope that it will be useful, but 
                    WITHOUT ANY WARRANTY; without even the implied warranty of 
                    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. We hope you are okay.\n\n";

                break;

            case 'opt-in':

                $message = "This service is provided in the hope that it will be useful, but 
                    WITHOUT ANY WARRANTY; without even the implied warranty of 
                    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. We hope you are okay.\n\n";

                break;


            default:

                $subject = "Review and accept Stackr's Terms and Conditions of Use";

                $message = "Thank you for your recent email to Stackr.  'warranty' 
                    saw that you haven't yet accepted the Stackr Terms and 
                    Conditions of use.  If you wish to opt in to using Stackr, please either reply $
                    this email or send an email to opt-in@" . $this->mail_postfix . ".\n\n
                    This service is provided in the hope that it will be useful, but 
                    WITHOUT ANY WARRANTY; without even the implied warranty of 
                    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.\n\n
                    Stackr provides a range of email based tools to get things done.\n\n
                    Stackr will ignore further e-mails from this address.";

                break;

        }

            $this->message = $message;
            $this->thing_report['message'] = $message;



    }

    private function makeEmail() {

        switch ($this->counter) {

            default:

                $subject = "Review and accept Stackr's Terms and Conditions of Use";

                $message = "Thank you for your recent email to Stackr.  'warranty' 
                    saw that you haven't yet accepted the Stackr Terms and 
                    Conditions of use.  If you wish to opt in to using Stackr, please either reply to
                    this email or send an email to opt-in@" . $this->mail_postfix . ".\n\n
                    This service is provided in the hope that it will be useful, but 
                    WITHOUT ANY WARRANTY; without even the implied warranty of 
                    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.\n\n
                    Stackr provides a range of email based tools to get things done.\n\n
                    Stackr will ignore further e-mails from this address.";

                break;

        }

            $this->message = $message;
            $this->thing_report['email'] = $message;

    }


    public function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "warranty");
        $choices = $this->thing->choice->makeLinks('warranty');
        // $choices = false;
        $this->thing_report['choices'] = $choices;
        return;
    }



	public function respond() {

		// Thing actions

		// New user is triggered when there is no nom_from in the db.
		// If this is the case, then Stackr should send out a response
		// which explains what stackr is and asks either
		// for a reply to the email, or to send an email to opt-in@web_postfix.


		$this->thing->flagGreen();

		// Get the current user-state.



        $this->makeSMS();
        $this->makeEmail();
        $this->makeChoices();

        //$this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['sms'] = $this->sms_message;

        // While we work on this
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->makeMessage();

        $this->thing_report['help'] = $this->agent_prefix  .'responding to an instruction to new user.';


		return;
	}

    public function getUsermanager()
    {
        
        $this->usermanager = new Usermanager($this->thing, "usermanager");

        $this->response = "Read the user state.";
    }


	public function readSubject() {

        $this->getUsermanager();
		return;		
	}


	function newuser() {

        // Call the Usermanager agent and update the state
//        $agent = new Usermanager($this->thing, "usermanager newuser");
//        $this->thing->log( $this->agent_prefix .'called the Usermanager to update user state to new user.', "INFORMATION" );


		return;
	}

}

?>
