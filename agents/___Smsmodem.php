<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Smsmodem
{

	public $var = 'hello';

    function __construct(Thing $thing, $input = null) 
    {
		$this->input = $input;
		$this->cost = 50;

        $this->agent_prefix = 'Agent "SMS Modem"';

		$this->test= "Development code";

		$this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;


        // Example

//        $this->api_key = $this->thing->container['api']['nexmo']['api_key'];
//        $this->api_secret = $this->thing->container['api']['nexmo']['api_secret'];


        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		$this->sqlresponse = null;


//var_dump($this->subject);

//$number->agent = Number($this->thing);
//var_dump($number->number);
$message = str_replace("smsmodem", "", $this->subject);

//$number_agent = Number($this->thing, "extract");

//var_dump($number_thing->number);

$to = "+17787920847";

//$this->receiveSMS();
//exit();
$this->sendSMS($to,$message);


exit();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("sms_modem", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("sms_modem", "refreshed_at"), $time_string );
        }

        $this->thing->json->setField("variables");
        $this->sms_modem_count = $this->thing->json->readVariable( array("sms_modem", "count") );

        if ($this->sms_modem_count == false) {$this->sms_modem_count = 0;}

        //$this->sendSMS();
        //$this->sendUSshortcode();

        $this->node_list = array("sms modem send"=>array("sms modem send"));

		$this->thing->log( '<pre> Agent "Sms modem" running on Thing ' .  $this->uuid . ' </pre>' );
		$this->thing->log( '<pre> Agent "Sms modem" received this Thing "' .  $this->subject . '"</pre>' );
        $this->sms_modem_per_message_responses = 1;
        $this->sms_modem_horizon = 2 *60; //s

/*
        if ( $this->sms_count >= $per_message_responses) {
            $this->thing_report = array('thing' => $this->thing->thing, 
                'choices' => false,
                'info' => "This thing has sent it's limit of SMS messages.",
                'help' => 'from needs to be a number.');

                $this->thing->log( '<pre> Agent "Sms" completed without sending a SMS</pre>' );
            return;
        }
*/

		if ( $this->readSubject() == true) {
			$this->thing_report = array('thing' => $this->thing->thing, 
				'choices' => false,
				'info' => "A cell number wasn't provided.",
				'help' => 'from needs to be a number.');

		        $this->thing->log( '<pre> Agent "Sms Modem" completed without sending a SMS</pre>' );
			return;
		}

		$this->respond();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());

		$this->thing->log ( '<pre> Agent "Sms Modem" completed</pre>' );

		return;

	}

// -----------------------

	private function respond() {

		// Thing actions
		$this->thing->flagGreen();

		// Generate email response.
		$to = $this->from;

		if ($this->input != null) {
			$test_message = $this->input;
		} else {
			$test_message = $this->subject;
		}

        $this->thing_report['sms modem'] = "SMS MODEM | " . $test_message;

        $received_at = strtotime($this->thing->thing->created_at);
        $time_ago = time() - $received_at;

        // Don't send a message if there isn't enough balance,
        // the number of responses per message would be exceeded, or
        // if the message would be sent 'too late'.
		if (($this->thing->account['stack']->balance['amount'] >= $this->cost ) and
            ($this->sms_modem_count < $this->sms_modem_per_message_responses) and
            ($time_ago < $this->sms_modem_horizon )) {
			$this->sendSms($to, $test_message);
			$this->thing->account['stack']->Debit($this->cost);

// Investigate short codes
// $this->sendUSshortcode($to, $test_message);

			$this->thing_report['info'] = '<pre> Agent "Sms Modem" sent a SMS to ' . $this->from . '.</pre>';

            $this->thing->json->writeVariable( array("sms_modem", "count"), $this->sms_mode_count + 1 );



		} else {

			$this->thing_report['info'] = 'SMS not sent.  Balance of ' . $this->thing->account['stack']->balance['amount'] . " less than " . $this->cost ;
		}


        $this->thing_report['help'] = "This is the agent that manages SMS Modem.";

		return;
	}


	public function readSubject()
    {
		if ( !is_numeric($this->from) ) {
			// This isn't a textable number.
			return true;
		}

		return false;
	}

    function receiveSMS()
    {

        $serial_agent = new Serial($this->thing);

        echo "Prepare to receive\n";
        $serial_agent->serial->sendMessage("AT+CMGF=1\r",1);
        $text = $this->readPort(true);
        var_dump($text);
        //$message = str_replace("smsmodem", "", $this->subject);

        echo "Set text mode";

        $mode = "ALL";
        $serial_agent->serial->sendMessage("AT+CMGL=\"{$mode}\"\r",1);
        $serial_agent->serial->sendMessage("AT+CMGL=\"{$mode}\"\r");
        $text = $this->readPort(true);
        var_dump($text);


//        $serial_agent->serial->sendMessage("AT+CMGS=\"$to\"\r",1);

        //$serial_agent->serial->sendMessage("AT+CMGS=\"+17787920847\"\r",1);


        //sleep(2);
//        $serial_agent->serial->sendMessage($message . chr(26),1);
//        echo "Should have sent\n";

        $serial_agent->serial->deviceClose();
        return;
    }
/*
    private function readPort($returnBufffer = false)
    {
        $out = null;
        list($last, $buffer) = $this->serial->readPort();
        if ($returnBufffer) {
            $out = $buffer;
        } else {
            $out = strtoupper($last);
        }
        if ($this->_debug == true) {
            echo $out . "\n";
        }
        return $out;
    }
*/
    function sendSMS($to, $message)
    {

        $serial_agent = new Serial($this->thing);

        echo "Prepare to send\n";
        $serial_agent->serial->sendMessage("AT+CMGF=1\r",1);
        //$text = $serial_agent->serial->readPort();
        //var_dump($text);
        //$message = str_replace("smsmodem", "", $this->subject);

        $serial_agent->serial->sendMessage("AT+CMGS=\"$to\"\r",1);

        //$serial_agent->serial->sendMessage("AT+CMGS=\"+17787920847\"\r",1);


        //sleep(2);
        $serial_agent->serial->sendMessage($message . chr(26),1);
        echo "Should have sent\n";

        $serial_agent->serial->deviceClose();

        return;
    }




}

?>

