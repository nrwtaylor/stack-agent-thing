<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


class Message {

	function __construct(Thing $thing, $agent_input = null) {

        // $timestamp =  new Timestamp($thing, "timestamp");

        //$this->start_time = $thing->elapsed_runtime();

        $this->thing = $thing;

        // Set this but overwrite it later if agent_input received
        // $this->thing_report['thing'] = $thing;
        //$this->start_time = microtime(true);
        $this->start_time = $this->thing->elapsed_runtime();
		// First check that the $agent_input is an array

		// If it null, then it is a non-agent calling the Message function.
		// So address that first


		if ($agent_input == null) {
			$this->thing_report = false;
		}

		if ( is_array($agent_input) ) {
			$this->thing_report = $agent_input;
			$this->setMessages();
		}

        $this->previous_agent = $this->get_calling_class();

        $this->agent_prefix = 'Agent "Message" ';

		// Given a "thing".  Instantiate a class to identify and create the
		// most appropriate agent to respond to it.
		//$this->thing = $thing;

		$this->thing_report['thing'] = $this->thing->thing;



		// Get some stuff from the stack which will be helpful.
		$this->web_prefix = $thing->container['stack']['web_prefix'];
		$this->stack_state = $thing->container['stack']['state'];
		$this->short_name = $thing->container['stack']['short_name'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];


		// Create some short-cuts.
		$this->uuid = $thing->uuid;
     	$this->to = $thing->to;
      	$this->from = $thing->from;
       	$this->subject = $thing->subject;


        $this->thing->log($this->agent_prefix . 'started running on Thing ' . $this->thing->nuuid . '.</pre>', "INFORMATION");


		$this->node_list = array("start"=>
						array("useful","useful?"));

		$this->aliases = array("message"=>array("communicate"));

//		$this->readSubject();
		$this->respond();


        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("message",
            "outcome"),  $this->thing_report['info']
            );

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;

        $timestamp =  new Timestamp($thing, "timestamp");


		return;

	}

    function get_calling_class()
    {

        //get the trace
        $trace = debug_backtrace();

        // Get the class that is asking for who awoke it
        $class = $trace[1]['class'];

        // +1 to i cos we have to account for calling this function
        for ( $i=1; $i<count( $trace ); $i++ ) {
            if ( isset( $trace[$i] ) ) // is it set?
                 if ( $class != $trace[$i]['class'] ) // is it a different class
                     return $trace[$i]['class'];
        }
    }

    function quotaMessage() {

        $this->quota = new Quota($this->thing, 'quota');
        $this->quota_flag = $this->quota->flag; 

        if ($this->quota_flag == "red") {
            $this->thing_report['info'] = 'Agent "Message" daily message quota exceeded. ' . $this->quota->counter_daily . ' of ' . $this->quota->quota_daily . ' messages sent.';
            return $this->thing_report;
        }

    }

    function tallyMessage() {

        $command = "tally 10000 message tally" . $this->mail_postfix;
        $tally_thing = new Tally($this->thing, $command);

        // Tally message counts up successfully sent messages.
        // So this is a good place to check if the same message has been
        // sent 3 times.


    }


	function setMessages() 
    {

		// 'message' must be set always.  If not fall back to sms_message
		if ( !isset($this->thing_report['message'] ) ) {
			$this->message = "Message not set";
			if ( isset ($this->thing_report['sms'] ) ) {
				$this->message = $this->thing_report['sms'];
	        	//		$this->message = "test";
			} else { 
				$this->message = "Message not set and no sms message available";
			}
		} else {
			if ( ($this->thing_report['message'] == null) or ( empty( $this->thing_report['message'] ) ) ) {
				$this->message = "Blank message received";
			} else {
				$this->message = $this->thing_report['message'];
				//$this->message = "Message text received: [" . $this->message . "]";
			}
		}

        if ( !isset($this->thing_report['thing'] ) ) {
            $this->from = null;
            $this->to = null;
        } else {
//			$this->from = $this->thing_report['thing']->nom_from;
//			$this->to = $this->thing_report['thing']->nom_to;



        }


		// As must 'thing'

        foreach ($this->thing_report as $key=>$value) {
			switch ($key) {
                case 'keyword':
                    $this->keyword = $this->thing_report['keyword'];
					continue;
    			case 'sms':
					$this->sms_message = $this->thing_report['sms'];
					continue;
                case 'choices':
                    $this->choices = $this->thing_report['choices'];
					continue;


/*
                                case 'message':
					if ( !isset($this->thing_report['message']) ) {
                                        	$this->message = $this->sms_message;
					} else {
					        $this->message = $this->thing_report['message'];
					}
*/
    				case 'email':
//					if (isset($this->thing_report['email']) ) {
//						$this->message = $this->thing_report['message'];
						$this->email = $this->thing_report['email'];
//					}
        				//echo "email";
        				//break;
					continue;
  				case 'web':

//$this->thing->log('<pre> Agent "Message" started running on Thing ' . date("Y-m-d H:i:s") . '</pre>');

        				//$this->thing->log( "web channel sent to message - no action" );
        				//break;
					continue;
    				default:
       					//
					continue;
			}
		}
		return;
	}

    function isOpen()
    {
        // See if the channel is open.
        $u = new Usermanager($this->thing, "usermanager");

        if (($u->state == "opt-in") or ($u->state == "start") or ($u->state == "new user")) {

            $this->messaging = "on";
        } else {
            $this->messaging = "off";
        }

        return $this->messaging;
    }

    function checkFacebook($searchfor)
    {
        // Check address against the beta list

        $file = '/var/www/stackr.test/resources/facebook/fbid.txt';
        $contents = file_get_contents($file);

        $pattern = "|\b($searchfor)\b|";

        // search, and store all matching occurences in $matches

        if(preg_match_all($pattern, $contents, $matches)){

            $m = $matches[0][0];
            return $m;
        } else {
            return false;
        }

        return;
    }

    function checkSlack($searchfor)
    {
        // Check address against the beta list

        $file = '/var/www/stackr.test/resources/slack/id.txt';
        $contents = file_get_contents($file);

        $pattern = "|\b($searchfor)\b|";

        // search, and store all matching occurences in $matches

        if(preg_match_all($pattern, $contents, $matches)){

            $m = $matches[0][0];
            return $m;
        } else {
            return false;
        }

        return;
    }


	public function respond()
    {

        if ($this->isOpen() == "off") {
            $this->thing->log( $this->agent_prefix . ' messaging is off.' , "WARNING");
            $this->thing_report['info'] = 'Agent "Message" says user messaging is OFF.';

//            return; Do not implement until usermanager is defaulting to start
            return;
        }


		$this->thing_report['info'] = 'Agent "Message" processing response';

		// Thing actions

		$this->thing->json->setField("variables");
		$this->thing->json->writeVariable(array("message",
			"received_at"),  $this->thing->json->time()
			);



        $this->thing->json->writeVariable(array("message",
            "agent"), $this->previous_agent
            );



		$this->thing->flagGreen();

        if ( $this->thing_report == false) {
			$this->thing_report['info'] = 'Agent "Message" did not receive a Thing report';
			return $this->thing_report;
		} else {

		}

        if ( substr( $this->subject, 0, 3 ) === "s/ " ) {
            $this->thing_report['info'] = 'Agent "Message" did not send a stack message.';
            return $this->thing_report;
        } else {

        }


        $from = $this->from;
        $to = $this->to;

		// Now passed by Thing object
		$uuid = $this->uuid;
		$sqlresponse = "yes";

        $message = "Thank you $from this was MESSAGE";

        // Recognize and then handle Facebook messenger chat.
        if ( $this->checkFacebook($to)  ) { // The FB number of Mordok the Magnificent

            $this->channel = 'facebook';

            // See what sorry state Mordok is in.
            //$mordok_thing = new Mordok($this->thing);

            //$this->thing->log( '<pre> ' . $mordok_thing->thing_report['info'] . '</pre>' );

            // Cost is handled by sms.php
            // So here we should pull in the token limiter and proof
            // it's capacity to token limit outgoing SMS

            $token_thing = new Tokenlimiter($this->thing, 'facebook');

            $dev_overide = null;
            if ( ($token_thing->thing_report['token'] == 'facebook' ) or ($dev_overide == true) ) {

                $fb_thing = new Facebook($this->thing, $this->sms_message);

                $thing_report['info'] = $fb_thing->thing_report['info'];

                $this->thing_report['channel'] = 'facebook'; // one of sms, email, keyword etc
                $this->thing_report['info'] = 'Agent "Message" sent a Facebook message.'; 

                $this->thing->log( '<pre> ' . $this->thing_report['info'] . '</pre>', "INFORMATION" );

                $this->tallyMessage();

            } else {

                $this->thing_report['channel'] = 'facebook'; // one of sms, email, keyword etc
                $this->thing_report['info'] = "You were sent this link through " . $this->thing_report['channel'] . '.'; 
            }

           $this->thing->log( $this->agent_prefix . ' said, "' . $this->thing_report['info'] .'"', "WARNING" );

            return $this->thing_report;
        }


        if ( $this->checkSlack($to) ) { // The Slack app of Mordok the Magnificent

            $this->thing->log('<pre> Agent "Message" responding via Slack.</pre>');


                        // Cost is handled by sms.php
                        // So here we should pull in the token limiter and proof 
                        // it's capacity to token limit outgoing SMS

                       $token_thing = new Tokenlimiter($this->thing, 'slack');


$this->thing->log('Agent "Message" received a ' . $token_thing->thing_report['token'] . " Token.", "INFORMATION");
$dev_overide = null;
                        if ( ($token_thing->thing_report['token'] == 'slack' ) or ($dev_overide == true) ) {

                       $slack_thing = new Slack($this->thing, $this->thing_report);

// $slack_thing = new Slack($this->thing, $this->sms_message);


                       $thing_report['info'] = 'Slack message sent.';

                       $this->thing_report['channel'] = 'slack'; // one of sms, email, keyword etc
                       $this->thing_report['info'] = 'Agent "Message" sent a Slack message'; 


                        $this->thing->log( '<pre> ' . $this->thing_report['info'] . '</pre>' , "INFORMATION");

$this->tallyMessage();


                        } else {

                       $this->thing_report['channel'] = 'slack'; // one of sms, email, keyword etc
                       $this->thing_report['info'] = 'Agent "Message" did not get a Slack token.'; 
                        }

                        $this->thing->log( '<pre> ' . $this->thing_report['info'] . '</pre>', "WARNING" );

                        return $this->thing_report;
                }



        if ( is_numeric($from) and isset($this->sms_message) ) {

            $this->thing_report['channel'] = 'sms'; // one of sms, email, keyword etc

    		// Cost is handled by sms.php

            // Check both a thing token and a stack quota.
            $token_thing = new Tokenlimiter($this->thing, 'sms');
            $quota = new Quota($this->thing, 'quota');

            //$this->thing->log( $this->agent_prefix . " Token is " . $token_thing->thing_report['token'] . ".");

            //$dev_overide = null; //uncomment to stop sms messaging


            switch (true) {
                case ($token_thing->thing_report['token'] != 'sms' ):
                    $this->thing_report['info'] = 'Agent "Message" did not get SMS token.';
                    break;

                // Need to review this
                case ($quota->counter > 5):
//                case ($quota->flag == 'red'):
                    $this->thing_report['info'] = 'Agent "Message" has exceeded the daily message quota.';
                    break;


//            case (isset($dev_overide)):
                case (true):

                   $sms_thing = new Sms($this->thing, $this->sms_message);

                   $this->thing_report['info'] = 'Agent "Message" sent a SMS.'; 
                   $this->thing->log( '<pre> ' . $this->thing_report['info'] . '</pre>', "INFORMATION" );

                   $this->tallyMessage();
                   $quota = new Quota($this->thing, 'quota use');

                   break;

                default:
            }

            $this->thing->log( '<pre> ' . $this->thing_report['info'] . '</pre>', "WARNING" );


			return $this->thing_report;
        }


//		$this->thing_report['message'] = null; // one of sms, email, keyword etc


        // Recognize and respond to email messages,
        // IF there is a formatted email message.

        if ( filter_var($from, FILTER_VALIDATE_EMAIL) and isset($this->message) ) {

            // Cost is handled by sms.php
            // So here we should pull in the token limiter and proof 
            // it's capacity to token limit outgoing email

            $token_thing = new Tokenlimiter($this->thing, 'email');

            $quota = new Quota($this->thing, 'quota');



            $this->thing->log( 'Agent "Message" received a ' . $token_thing->thing_report['token'] . " Token.", "INFORMATION");
                $makeemail_agent = new makeEmail($this->thing, $this->thing_report);
                $this->thing_report['email'] = $makeemail_agent->email_message;

            switch (true) {
                case (strpos($this->from, $this->mail_postfix) !== false):
                    $this->thing_report['info'] = 'Agent "Message" did not send an Email to an internal address.';
                    break;


                case ($token_thing->thing_report['token'] != 'email' ):

                    $this->thing_report['info'] = 'Agent "Message" did not get Email token.';

                    break;

                case ($quota->flag == 'red'):
                    $this->thing_report['info'] = 'Agent "Message" has exceeded the daily message quota.';

                    break;
//            case (isset($dev_overide)):
                case (true):

//                if($quota->counter >= $quota->period_limit) {
//                    $this->sms_message = "!dailymessagequota";
//                }

                $sms_thing = new Email($this->thing, $this->thing_report);


//                   $sms_thing = new Sms($this->thing, $this->sms_message);

                //$this->thing_report['email'] = $sms_thing->email_message;

                   $this->thing_report['info'] = 'Agent "Message" sent an Email.'; 
                   $this->thing->log( '<pre> ' . $this->thing_report['info'] . '</pre>', "INFORMATION" );

                   $this->tallyMessage();
                   $quota = new Quota($this->thing, 'quota use');

                   break;

                default:
            }


            $this->thing->log( '<pre> ' . $this->thing_report['info'] . '</pre>', "WARNING" );

            return $this->thing_report;
        }

        $this->thing_report['info'] = 'Agent "Message" did not send a message.';

		return $this->thing_report;
	}



	public function readSubject() {


		$status = true;
	return $status;		
	}





}

?>
