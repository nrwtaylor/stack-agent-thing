<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Usermanager extends Agent
{
function init() {
//	function __construct(Thing $thing, $agent_input = null)
//    {

//		$this->thing = $thing;
  //      $this->start_time = $this->thing->elapsed_runtime();
    //    $this->thing_report['thing'] = $thing;

//		$this->agent_name = 'usermanager';
   //     $this->agent_prefix = 'Agent "Usermanager"';
   //     $this->agent_input = $agent_input;

		// So I could call
		$this->test = false;
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

        // Load in some characterizations.
        $this->short_name = $this->thing->container['stack']['short_name'];
        $this->web_prefix = $this->thing->container['stack']['web_prefix'];
        $this->mail_prefix = $this->thing->container['stack']['mail_prefix'];
        $this->mail_postfix = $this->thing->container['stack']['mail_postfix'];
        $this->sms_seperator = $this->thing->container['stack']['sms_separator']; // |

        // Load in time quantums
        $this->cron_period = $this->thing->container['stack']['cron_period']; // 60s
        $this->thing_resolution = $this->thing->container['stack']['thing_resolution']; // 1ms

        // Load in a pointer to the stack record.
        $this->stack_uuid = $this->thing->container['stack']['uuid']; // 60s

        $this->verbosity_log = 7;

                $this->thing_report['help'] = 'Agent "Usermanager" figuring the user of this Thing out';


  //      $this->uuid = $thing->uuid;
  //      $this->to = $thing->to;
  //      $this->from = $thing->from;
  //      $this->subject = $thing->subject;
		//$this->sqlresponse = null;

		$this->node_list = array("start"=>array("new user"=>array("opt-in"=>
			array("opt-out"=>array("opt-in","delete")))));

        $this->current_time = $this->thing->time();

//		$this->thing->log( 'Agent "Usermanager" running on Thing '. $this->thing->nuuid . '.', "INFORMATION");

//        // 279ms 303ms 306ms
//        $split_time = $this->thing->elapsed_runtime();
//
//        $this->variables_agent = new Variables($this->thing, "variables usermanager " . $this->from);

//        $this->thing->log( $this->agent_prefix .' created variables agent in ' . number_format($this->thing->elapsed_runtime() - $split_time) . 'ms.', "OPTIMIZE" );
        //4ms 4ms 3ms
//        $this->get();

//        if ($this->agent_input != null) {
//            $this->readInstruction();
//	    } else {
//            $this->getSubject();
//        }

//        $this->set();

//        if ($this->agent_input == null) {
//		    $this->setSignals(); /// Don't send any messages
//        }
        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;

		return;
	}

function run() {

        if ($this->agent_input != null) {
            $this->readInstruction();
            } else {
            $this->getSubject();
        }

}


    function readInstruction()
    {
        $this->thing->log( $this->agent_prefix .' read instruction "' . $this->agent_input.'".', "INFORMATION" );

        if ($this->agent_input == "usermanager optin") {
            $this->previous_state = $this->state;
            $this->state = "opt-in";

            if ($this->verbosity_log >=8) {
                $this->thing->log( $this->agent_prefix .'updated the state to ' . $this->state . ".", "INFORMATION" );
            }
        }

        if ($this->agent_input == "usermanager optout") {
            $this->previous_state = $this->state;
            $this->state = "opt-out";
        }

        if ($this->agent_input == "usermanager delete") {
            $this->previous_state = $this->state;
            $this->state = "delete";
        }

        if ($this->agent_input == "usermanager start") {
            $this->thing->log( $this->agent_prefix .' set internal state to START.', "INFORMATION" );

            $this->previous_state = $this->state;
            $this->state = "start";
        }

        if ($this->agent_input == "usermanager unsubscribe") {
            $this->previous_state = $this->state;
            $this->state = "unsubscribe";
        }

        if ($this->agent_input == "usermanager stop") {
            $this->previous_state = $this->state;
            $this->state = "stop";
        }

        if ($this->agent_input == "usermanager") {
            $this->previous_state = $this->state;
        }
    }

    function set()
    {
        $this->variables_agent->setVariable("state", $this->state);
        $this->variables_agent->setVariable("counter", $this->counter);

        $this->previous_state = $this->state;
        $this->variables_agent->setVariable("previous_state", $this->previous_state);

        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

        $this->thing->choice->save('usermanager', $this->state);
        return;
    }


    function get()
    {

        // 279ms 303ms 306ms
        $split_time = $this->thing->elapsed_runtime();

        $this->variables_agent = new Variables($this->thing, "variables usermanager " . $this->from);

        $this->thing->log( $this->agent_prefix .' created variables agent in ' . number_format($this->thing->elapsed_runtime() - $split_time) . 'ms.', "OPTIMIZE" );


        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->state = $this->variables_agent->getVariable("state");
        $this->previous_state = $this->variables_agent->getVariable("previous_state");

        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        if ($this->verbosity_log >= 8) {
            $this->thing->log( 'Agent "Usermanager" loaded ' . $this->state . " " . $this->previous_state . ".", "DEBUG");
        }

        $this->counter = $this->variables_agent->getVariable("counter");

        if ($this->verbosity_log >= 8) {
            $this->thing->log( 'Agent "Usermanager" loaded ' . $this->counter . ".", "DEBUG");
        }

        $this->counter = $this->counter + 1;

        if ($this->state == false) {
            $this->state = "start";
            $this->previous_state = "X";
        }

        if (($this->state == null) or ($this->state == true)) {
  //          $this->state = "start";
  //          $this->previous_state = "start";
        }

        $this->thing->log($this->agent_prefix . ' retrieved a ' . strtoupper($this->state) . ' state.', "INFORMATION");

        return;
    }


    function makeSMS()
    {
        switch($this->counter) {
            case 0:
                // drop throught
            case 1: 

                $sms = "USERMANAGER | " . "state " . strtoupper($this->state) . " previous_state " . strtoupper($this->previous_state);
                break;
            default:
                $sms = "USERMANAGER | " . "state " . strtoupper($this->state) . " previous_state " . strtoupper($this->previous_state);
                break;
        }

        $sms .= " counter " .$this->counter;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
        return;
    }

	public function respond()
    {
		// Develop the various messages for each channel.

		$this->thing->flagGreen(); 

        $this->makeSMS();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['sms'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
        // I think this is the place to report the state.

		$this->thing_report['keyword'] = $this->state;
		$this->thing_report['help'] = 'Agent "Usermanager" figuring the user of this Thing out';

		return $this->thing_report;
	}

	public function getSubject()
    {
		// What do we know at this point?
		// We know the nom_from.
		// We have the message.
		// And we know this was directed towards usermanager (or close).

		// So starting with nom_from.
		// Two conditions, we either know the nom_from, or we don't.

		//$status = false;
		$this->state_change = false;

        // $input = strtolower($this->to . " " .$this->subject);

        if ($this->agent_input != null) {

            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);

        } else {
            $input = strtolower($this->to . " " . $this->subject);
        }

        $keywords = array('usermanager','optin','opt-in','optout','opt-out','start','delete','new');
        $pieces = explode(" ", strtolower($input));

        if ($this->agent_input == "usermanager") {return;}


        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'usermanager') {return;}
        }

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
                        case 'optin':   
                        case 'opt-in': 
                            $this->optin();
                            return;

                        case 'optout':   
                        case 'opt-out': 
                            $this->optout();
                            return;

                        case 'start':   
                            $this->start();
                            return;
                        case 'new user':
                        case 'newuser':
                        case 'new':
                            $this->newuser();
                            return;

                        default:
                            // Could not recognize a command.
                            // Drop through

                    }

                }
            }

        }

        $this->requested_state = $this->discriminateInput($input, array('opt-in', 'opt-out'));

		switch ($this->state) {
			case 'opt-out':
                // We are in a state of opt-out.
                // Only respond to an Opt-in message

                if ($this->requested_state == "opt-in") {
                    $this->optin();
                    return;
                }

                // Otherwise ignore
                return;

			case 'opt-in':
                // In the opt-in state.
                if ($this->requested_state == "opt-out") {
                    $this->optout();
                    return;
                }
                // Otherwise ignore
                return;

			case 'new user':
                if ($this->requested_state == "opt-in") {
                    $this->optin();
                    return;
                }

                if ($this->requested_state == "opt-out") {
                    $this->optout();
                    return;
                }
			    return;

			case 'start';
                $this->newuser();
				break;

			case 'delete';
				//$this->state_change = true;
                //$this->thing->choice->Choose("new");
                // Do nothing remain deleted.
                // Make so must text "start"

                // Was deleted but now continuing to have conversation
                // Loop through to start
                $this->state_change = true;
                $this->thing->choice->Choose("start");
                $this->state = "start";

                break;

			default:
                $this->start();
				$this->state_change = true;
				$this->thing->choice->Choose("start");

			}

		return;
	}



	function newuser()
    {
        $this->thing->log( $this->agent_prefix .' chose NEWUSER.', "INFORMATION" );

        $this->thing->choice->Choose("new user");
        $this->previous_state = $this->state;
        $this->state = "new user";

        $agent = new Newuser($this->thing);

		return;
		// Make a record of the new user request

		$newuser_thing = new Thing(null);
		$newuser_thing->Create($this->from, 'usermanager', "s/ newuser (usermanager)");

//		$node_list = array("new user"=>array("opt-in"=>array("opt-out"=>"opt-in")));

		$newuser_thing->choice->Create($this->agent_name, $this->node_list, "new user");

		$newuser_thing->flagRed();

		return;
	}


    function start()
    {
        $this->thing->log( $this->agent_prefix .' chose START.', "INFORMATION" );

        $this->thing->choice->Choose("start");

        $this->thing->log( $this->agent_prefix .' choice call completed.', "INFORMATION" );

        $this->previous_state = $this->state;
        $this->state = "start";

        $agent = new Start($this->thing);
        return;

    }


    function optout()
    {
        $this->thing->log( $this->agent_prefix .' chose OPTOUT.', "INFORMATION" );


        // Send to the Optin agent to handle response
        $this->thing->choice->Choose("opt-in");
        $this->previous_state = $this->state;
        $this->state = "opt-in";

        $agent = new Optin($this->thing);
        return;
    }

    function optin()
    {
        $this->thing->log( $this->agent_prefix .' chose OPTIN.', "INFORMATION" );

        // Send to the Optin agent to handle response

        $this->thing->choice->Choose("opt-in");
        $this->previous_state = $this->state;
        $this->state = "opt-in";

        $agent = new Optin($this->thing);
        return;
    }

	function discriminateInput($input, $discriminators = null)
    {
		$default_discriminator_thresholds = array(2=>0.3, 3=>0.3, 4=>0.3);

		if (count($discriminators) > 4) {
			$minimum_discrimination = $default_discriminator_thresholds[4];
		} else {
			$minimum_discrimination = $default_discriminator_thresholds[count($discriminators)];
		}

		//$input = "optout opt-out opt-out";

		if ($discriminators == null) {
			$discriminators = array('opt-in', 'opt-out');
		}	

		$aliases = array();

		$aliases['opt-in'] = array('optin','accept','okay','yes', 'sure');
		$aliases['opt-out'] = array('optout','leave','unsubscribe','no','quit');

		$words = explode(" ", $input);

		$count = array();

		$total_count = 0;
		// Set counts to 1.  Bayes thing...	
		foreach ($discriminators as $discriminator) {
			$count[$discriminator] = 1;
			$total_count = $total_count + 1;
		}
		// ...and the total count.



		foreach ($words as $word) {

			foreach ($discriminators as $discriminator) {

				if ($word == $discriminator) {
					$count[$discriminator] = $count[$discriminator] + 1;
					$total_count = $total_count + 1;
						//$this->thing->log("sum");
				}

				foreach ($aliases[$discriminator] as $alias) {

					if ($word == $alias) {
						$count[$discriminator] = $count[$discriminator] + 1;
						$total_count = $total_count + 1;
						//echo "sum";

					}
				}
			}

		}

		$this->thing->log('Agent "Usermanager" total count is ' . $total_count, "DEBUG");
		// Set total sum of all values to 1.

		$normalized = array();
		foreach ($discriminators as $discriminator) {
			$normalized[$discriminator] = $count[$discriminator] / $total_count;		
		}


		// Is there good discrimination
		arsort($normalized);

		// Now see what the delta is between position 0 and 1
        $t= null;
		foreach ($normalized as $key=>$value) {
			//echo $key, $value;
            $t .= $key ." " . $value;

			if ( isset($max) ) {$delta = $max-$value; break;}
			if ( !isset($max) ) {$max = $value;$selected_discriminator = $key; }
            $t .= " ";
		}

        $this->thing->log('Agent "Usermanager" normalized discrimators "' .  $t . '".', "DEBUG");

		if ($delta >= $minimum_discrimination) {
			//echo "discriminator" . $discriminator;
			return $selected_discriminator;
		} else {
			return false; // No discriminator found.
		}

		return true;
	}
}

?>
