<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// https://github.com/gonzalo123/gam-sms/blob/master/Sms.php

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
//$message = str_replace("smsmodem", "", $this->subject);

//$number_agent = Number($this->thing, "extract");

//var_dump($number_thing->number);

$this->test_to = "+17787920847";

//$this->serial_agent = new Serial($this->thing);
//$this->modemOpen();

//$this->sendSMS($to,$message);


//$t = $this->readInbox();
//var_dump($t);


//$this->serial_agent->serial->deviceClose();
//$this->modemClose();

//$this->sendSMS("17787920847",$message);
//exit();

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


        $this->node_list = array("sms modem send"=>array("sms modem send"));

		$this->thing->log( '<pre> Agent "Sms modem" running on Thing ' .  $this->uuid . ' </pre>' );
		$this->thing->log( '<pre> Agent "Sms modem" received this Thing "' .  $this->subject . '"</pre>' );
        $this->sms_modem_per_message_responses = 1;
        $this->sms_modem_horizon = 2 *60; //s

        $this->readSubject();

/*
		if ( $this->readSubject() == true) {
			$this->thing_report = array('thing' => $this->thing->thing, 
				'choices' => false,
				'info' => "A cell number wasn't provided.",
				'help' => 'from needs to be a number.');

		        $this->thing->log( '<pre> Agent "Sms Modem" completed without sending a SMS</pre>' );
			return;
		}
*/
		$this->respond();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());

		$this->thing->log ( '<pre> Agent "Sms Modem" completed</pre>' );

		return;

	}

// -----------------------

    public function readInbox($mode="ALL")
    {
        echo "Attempt to read inbox\n";
//$serial_agent = new Serial($this->thing);

        $this->pinOK = true;

        $inbox = $return = array();
        if ($this->pinOK) {
            $this->modemOpen();
            $this->serial_agent->sendSerial("AT+CMGF=1\r");
            sleep(2);
            $out = $this->serial_agent->readPort();
echo "set mode\n";
var_dump($out);
            //if ($out == 'OK') {
                $this->serial_agent->sendSerial("AT+CMGL=\"{$mode}\"\r");
                sleep(2);
                $inbox = $this->serial_agent->readPort(true);
echo "inbox\n";
var_dump($inbox);
            //}
            $this->modemClose();
//echo "meep";
//exit();

            if (count($inbox) > 2) {
                array_pop($inbox);
                array_pop($inbox);
                $arr = explode("+CMGL:", implode("\n", $inbox));

                for ($i = 1; $i < count($arr); $i++) {
                    $arrItem = explode("\n", $arr[$i], 2);
                    // Header
                    $headArr = explode(",", $arrItem[0]);
                    $fromTlfn = str_replace('"', null, $headArr[2]);
                    $id = $headArr[0];
                    $date = $headArr[4];
                    $hour = $headArr[5];
                    // txt
                    $txt = $arrItem[1];
                    $return[] = array('id' => $id, 'tlfn' => $fromTlfn, 'msg' => $txt, 'date' => $date, 'hour' => $hour);
                }
            }
            return $return;
        } else {
            throw new Exception("Please insert the PIN", self::EXCEPTION_NO_PIN);
        }
    }

	private function respond() {

		// Thing actions
		$this->thing->flagGreen();

		// Generate email response.
		$to = $this->from;

		//if ($this->input != null) {
		//	$test_message = $this->input;
		//} else {
		//	$test_message = $this->subject;
		//}

//$message = str_replace("smsmodem", "", $this->subject);


        $this->thing_report['sms'] = "SMS MODEM | " . $this->response;

        $received_at = strtotime($this->thing->thing->created_at);
        $time_ago = time() - $received_at;

        // Don't send a message if there isn't enough balance,
        // the number of responses per message would be exceeded, or
        // if the message would be sent 'too late'.
		//if (($this->thing->account['stack']->balance['amount'] >= $this->cost ) and
        //    ($this->sms_modem_count < $this->sms_modem_per_message_responses) and
        //    ($time_ago < $this->sms_modem_horizon )) {
//			$this->sendSMS($to, $test_message);
		//	$this->thing->account['stack']->Debit($this->cost);


//$this->modemOpen();

//$this->sendSMS($to,$message);
//$t = $this->readInbox();
//var_dump($t);


//$this->serial_agent->serial->deviceClose();
//$this->modemClose();



			$this->thing_report['info'] = '<pre> Agent "Sms Modem" sent a SMS to ' . $this->from . '.</pre>';

            $this->thing->json->writeVariable( array("sms_modem", "count"), $this->sms_modem_count + 1 );



		//} else {

		//	$this->thing_report['info'] = 'SMS not sent.  Balance of ' . $this->thing->account['stack']->balance['amount'] . " less than " . $this->cost ;
		//}


        $this->thing_report['help'] = "This is the agent that manages SMS Modem.";

		return;
	}

    function modemClose()
    {
        //$this->serial_agent->serial->deviceClose();
        $this->serial_agent->deviceClose();
    }

    function modemOpen()
    {
        $this->serial_agent = new Serial($this->thing);
        $this->serial_agent->deviceOpen("/dev/ttyUSB0", 115200);
    }

    function extractSms()
    {



    }

	public function readSubject()
    {
        
		//if ( !is_numeric($this->from) ) {
		//	// This isn't a textable number.
		//	return true;
		//}

        var_dump($this->subject);

        if (strtolower($this->subject) == "smsmodem") {
            echo "smsmodem read received/n";
            $this->readInbox();
            echo "read inbox/n";
            $this->response = "Read inbox";
            return;
        }

        echo "extracting number/n";

        $number_agent = new Number($this->thing);

        var_dump($number_agent->numbers);

        $message = str_replace("smsmodem", "", $this->subject);

        $this->modemOpen();
        $this->sendSMS($this->test_to,$message);
        $this->modemClose();

        $this->response = "Sent SMS message.";

		return;
	}



    function sendSMS($to, $message)
    {
        echo "Prepare to send\n";

        $this->serial_agent->serial->sendMessage("AT+CMGF=1\r",1);
        $text = $this->serial_agent->serial->readPort();

        var_dump($text);

        $this->serial_agent->serial->sendMessage("AT+CMGS=\"$to\"\r",1);

        $this->serial_agent->serial->sendMessage($message . chr(26),1);

        echo "Should have sent\n";

        $this->modemClose();

        return;
    }





}

?>
