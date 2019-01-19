<?php
namespace Nrwtaylor\StackAgentThing;

//include 'PhpSerial.php';

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Serial
{

	public $var = 'hello';

    function __construct(Thing $thing, $input = null) 
    {
        $this->input = $input;
        $this->cost = 50;

        $this->agent_prefix = 'Agent "Serial"';

        $this->test= "Development code";

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;


// Let's start the class
$this->serial = new \PhpSerial;

$this->serial->deviceSet("/dev/ttyUSB0");
$this->serial->confBaudRate(115200);

return;
echo "Prepare to send\n";
$this->serial->sendMessage("AT+CMGF=1\r",1);
$text = $this->serial->readPort();
var_dump($this->subject);
$message = str_replace("smsmodem", "", $this->subject);
$this->serial->sendMessage("AT+CMGS=\"+17787920847\"\r",1);
sleep(2);
$this->serial->sendMessage($message . chr(26),1);
echo "Should have sent\n";

$this->serial->deviceClose();
exit();
//$serial->sendMessage("AT",1);
        $start_time = $this->thing->elapsed_runtime();
$elapsed = 0;

            //$this->thing->json->writeVariable( array("nod", "timestamp"), $micro_timestamp );
$serial_string = "";

while ($elapsed < 5000) {
$text = $this->serial->readPort();
if ($text != "") {
//var_dump($text);
$serial_string .= $text;
$elapsed = $this->thing->elapsed_runtime() - $start_time;

//echo "ELAPSED | " . $elapsed;
}
echo $serial_string;
}

$clocktime = new Clocktime($this->thing);
var_dump( $clocktime->extractClocktime($serial_string) );

//            $micro_elapsed = $this->thing->time() - $micro_timestamp;
//echo $micro_elapsed;
            //$this->thing->json->writeVariable( array("nod", "timestamp"), $micro_timestamp );

//$serial->sendMessage("AT+CMGF=1",1);
//var_dump($serial->readPort());

//$serial->sendMessage("AT+CSMP",1);
//var_dump($serial->readPort());

//$serial->sendMessage("AT+CSMP=17,16,0,16",1);
//var_dump($serial->readPort());


//$serial->sendMessage("TEST TEST TEST",1);
//var_dump($serial->readPort());



//$serial->sendMessage("AT+CMGF=1\n\r",1);
//var_dump($serial->readPort());
//$serial->sendMessage("AT+CMGL=\"ALL\"\n\r",2);
//var_dump($serial->readPort());
//var_dump($serial->readPort());
//var_dump($serial->readPort());
//var_dump($serial->readPort());


// If you want to change the configuration, the device must be closed
$serial->deviceClose();

exit();

$serial->sendMessage("ATI",1);



$theResult = '';
    $read = $serial->readPort();
    if ($read != '') {
        $theResult .= $read;
        $read = '';
    }
echo $theResult;

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		$this->sqlresponse = null;

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("serial", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("serial", "refreshed_at"), $time_string );
        }

        $this->thing->json->setField("variables");
        $this->serial_count = $this->thing->json->readVariable( array("serial", "count") );

        if ($this->serial_count == false) {$this->serial_count = 0;}

        //$this->sendSMS();
        //$this->sendUSshortcode();

        $this->node_list = array("serial send"=>array("serial send"));

		$this->thing->log( '<pre> Agent "Serial" running on Thing ' .  $this->uuid . ' </pre>' );
		$this->thing->log( '<pre> Agent "Serial" received this Thing "' .  $this->subject . '"</pre>' );
        $this->serial_per_message_responses = 1;
        $this->serial_horizon = 2 *60; //s

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

		        $this->thing->log( '<pre> Agent "Serial" completed without sending a Serial message</pre>' );
			return;
		}
		$this->respond();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());

		$this->thing->log ( '<pre> Agent "Serial" completed</pre>' );

		return;

	}

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


// -----------------------
private function sendSerial($text) {
    //$serial->sendMessage("ATI",1);
    echo "Prepare to send " . $text ."\n";
    $this->serial->sendMessage($text,1);
    $response_text = $this->serial->readPort();
}

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

        $this->thing_report['serial'] = "SERIAL | " . $test_message;

        $received_at = strtotime($this->thing->thing->created_at);
        $time_ago = time() - $received_at;

        // Don't send a message if there isn't enough balance,
        // the number of responses per message would be exceeded, or
        // if the message would be sent 'too late'.
		if (($this->thing->account['stack']->balance['amount'] >= $this->cost ) and
            ($this->serial_count < $this->serial_per_message_responses) and
            ($time_ago < $this->serial_horizon )) {

			$this->sendSerial($to, $test_message);
			$this->thing->account['stack']->Debit($this->cost);

// Investigate short codes
// $this->sendUSshortcode($to, $test_message);

			$this->thing_report['info'] = '<pre> Agent "Serial" sent a Serial message to ' . $this->from . '.</pre>';

            $this->thing->json->writeVariable( array("serial", "count"), $this->serial_count + 1 );



		} else {

			$this->thing_report['info'] = 'Serial message not sent.  Balance of ' . $this->thing->account['stack']->balance['amount'] . " less than " . $this->cost ;
		}


        $this->thing_report['help'] = "This is the agent that manages Serial.";

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


/*
    function sendSerial($to, $text)
    {
return;
$type = "text";
if (strlen($text) != strlen(utf8_decode($text)))
{
    $type = "unicode";
}

        //$url = 'https://rest.nexmo.com/sms/json?' . http_build_query(
//            [
//                'api_key' =>  $this->api_key,
//                'api_secret' => $this->api_secret,
//'type'=>$type,
//                'to' => $to,
//                'from' => $this->to,
//                'text' => $text
//            ]
//        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        echo $response;

        //Decode the json object you retrieved when you ran the request.
        $decoded_response = json_decode($response, true);

        error_log('You sent ' . $decoded_response['message-count'] . ' messages.');

        foreach ( $decoded_response['messages'] as $message ) {
            if ($message['status'] == 0) {
                error_log("Success " . $message['message-id']);
            } else {
                error_log("Error {$message['status']} {$message['error-text']}");
            }
        }

    return;
    }
*/


    function sendUSshortcode($to, $text)
    {
return;
        //https://rest.nexmo.com/sc/us/alert/json?api_key={$your_key}&api_secret={$your_secret}&
        // to={$to}&key1={$value1}&key2={$value2}

        $url = 'https://rest.nexmo.com/sc/us/alert/json?' . http_build_query(
            [
      'api_key' =>  $this->api_key,
      'api_secret' => $this->api_secret,
      'to' => $to,
	'message' => $text
    ]
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

// devstack log to db
//echo $response;


  //Decode the json object you retrieved when you ran the request.
  $decoded_response = json_decode($response, true);
//echo $decoded_response;

  error_log('You sent ' . $decoded_response['message-count'] . ' messages.');

  foreach ( $decoded_response['messages'] as $message ) {
      if ($message['status'] == 0) {
          error_log("Success " . $message['message-id']);
      } else {
          error_log("Error {$message['status']} {$message['error-text']}");
      }
  }
return;
}


}

?>
