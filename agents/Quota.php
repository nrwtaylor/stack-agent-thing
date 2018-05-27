<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Quota {

	function __construct(Thing $thing, $agent_input = null) {

        $this->agent_input = $agent_input;

		$this->thing = $thing;
		$this->agent_name = 'quota';
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

        $quotas_list = array("railway", "block", "train", "daily", "hourly", "minute", "second");
        $quotas_list = array("daily");

        //message_perminute_limit

        $this->quota_name = "message_perminute";
        $quota_period = 60;

        $this->period_limit = $this->thing->container['api']['quota'][$this->quota_name . '_limit'];
        //$this->quota_daily = $this->thing->container['api']['quota'][$this->quota_name . '_limit'];
        //$this->quota_hourly = $this->thing->container['api']['quota']['this->message_hourly_limit'];

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

		$this->node_list = array("quota"=>array("opt-in"));

        $this->thing->log( $this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.</pre>', "INFORMATION");

        //foreach ($quotas_list as $key=>$value) {
            // Pattern $this->{"default_" . $variable_name};
            $this->variables = new Variables($this->thing, "variables quota_" . $this->quota_name . " " . $this->from);
       // }

        $this->current_time = $this->thing->json->time();


        // Check whether quota should be reset

        $this->get();

        // Check whether quota should be reset
        $t = strtotime($this->current_time) - strtotime($this->reset_at);
        //var_dump($t);
        if ($t > $quota_period) { $this->quotaReset();}

		$this->readSubject();

        $this->setFlag();
        $this->set();
        if ($agent_input == null) {
 		    $this->respond();
        }


		$this->thing->flagGreen();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;

    	return;
	}



    function set()
    {
//echo "set";

//var_dump($this->counter);
        $this->variables->setVariable("counter", $this->counter);
        $this->variables->setVariable("reset_at", $this->reset_at);
        $this->variables->setVariable("refreshed_at", $this->current_time);


        return;
    }


    function get()
    {
        $this->counter = $this->variables->getVariable("counter");
        $this->reset_at = $this->variables->getVariable("reset_at");
        $this->refreshed_at = $this->variables->getVariable("refreshed_at");
//echo "get";
//var_dump($this->counter);

        if ($this->counter == null) {$this->counter = 1;}
        if ($this->reset_at == null) {$this->reset_at = $this->current_time;}

        //$this->counter_daily = $this->counter; // Factor out.
//        $this->counter_daily += 1;

        $this->thing->log( $this->agent_prefix .  'loaded ' . $this->counter . ".", "DEBUG");


        return;
    }

    function setFlag($colour = null)
    {

        if ($colour == null) {
            $colour = "red";
        }

        if ($this->counter <= $this->period_limit) {
            $colour = "green";
        } else {
            $colour = "red";
        }

        $this->flag_thing = new Flag($this->thing, 'flag_quota_'. $this->quota_name . " " . $colour);
        $this->flag = $this->flag_thing->state;

        // Is this do our set?,  Setting signals.
    }

    private function makeSMS()
    {
        $sms = "QUOTA | ";

        $sms .= "flag " . strtoupper($this->flag_thing->state) . " ";
        $sms .= $this->quota_name . " " . $this->counter . " of " . $this->period_limit . "";

        switch (true) {
            case ($this->counter > $this->period_limit):
                $sms .= " | ";
                $sms .= 'Quota exceeded. | ';
                $sms .= "Text QUOTA RESET";
                break;
            case ($this->counter > 0.5 * $this->period_limit):
                $sms .= ' | ';
                $sms .= "Text QUOTA RESET";
                break;


            case true:
            default:
        }

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;

    }


    private function makeEmail() {

        switch ($this->counter) {
            case 1:

                $subject = "Start Stackr?";

                $message = "So an action you took (or someone else took) opted you into 
                    Stackr.
                    <br>
                    There is always that little element of uncertainity.  So we clearly think
                    this is a good thing and are excited to start
                    making associations from your emails that (which?) we know will be
                    helpful or useful to you.
                    <br>
                    So thanks for that and be sure to keep an eye on your stack balance. Which
                    will be maintained at least until you opt-out.  
                    <br>
                    Keep on stacking.

                    ";
                break;
            case 2:
                $subject = "Opt-in request accepted";

                $message = "Thank you for your opt-in request.  'optin' has 
                    added ".$this->from." to the accepted list of Stackr emails.
                    You can now use Stackr.  Keep on stacking.\n\n";

                break;

            case null;

            default:
                $message = "START | Acknowledged. " . $this->web_prefix . "privacy";

        }

            $this->message = $message;
            $this->thing_report['email'] = $message;

    }


    public function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "start");
        $choices = $this->thing->choice->makeLinks('start');
        // $choices = false;
        $this->thing_report['choices'] = $choices;
        return;
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

        $this->thing_report['help'] = $this->agent_prefix  .'manages stack quotas.';

		return;
	}



	public function readSubject() {

        // Process as User>Agent or as Thing>Agent?
        if ($this->agent_input == null) {
            $piece = strtolower($this->from . " " . $this->subject);
        } else {
            $piece = $this->agent_input;
        }

        // Check for other ideas in the message
        switch (true) {
            case (strpos(strtolower($piece),"reset") !== false):
               // Match phrase within phrase
                $this->quotaReset();
                break;
            case (strpos(strtolower($piece),"use") !== false):
               // Match phrase within phrase
                $this->quotaUse();
                break;

            case true;
                $this->quota();
            default:
        }

//        $this->quota();
		return;
	}

	function quota()
    {

        if ($this->counter >= 10000) {
            $this->state = "Meep";
        }

		return;
	}

    function quotaReset() 
    {
        $this->reset_at = $this->current_time;
        $this->counter = 1;
        return;
    }

    function quotaUse() 
    {
        $this->counter += 1;
        return;
    }


}

?>
