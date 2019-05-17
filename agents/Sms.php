<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Sms
{

	public $var = 'hello';

    function __construct(Thing $thing, $input = null) 
    {

            $this->body = $input;
		$this->input = $input;
		$this->cost = 50;

        $this->agent_prefix = 'Agent "SMS"';

		$this->test= "Development code";

		$this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;


        // Example

        $this->api_key = $this->thing->container['api']['nexmo']['api_key'];
        $this->api_secret = $this->thing->container['api']['nexmo']['api_secret'];


        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		$this->sqlresponse = null;

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("sms", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("sms", "refreshed_at"), $time_string );
        }

        $this->thing->json->setField("variables");
        $this->sms_count = $this->thing->json->readVariable( array("sms", "count") );

        if ($this->sms_count == false) {$this->sms_count = 0;}

        //$this->sendSMS();
        //$this->sendUSshortcode();

        $this->node_list = array("sms send"=>array("sms send"));

		$this->thing->log( '<pre> Agent "Sms" running on Thing ' .  $this->uuid . ' </pre>' );
		$this->thing->log( '<pre> Agent "Sms" received this Thing "' .  $this->subject . '"</pre>' );
        $this->sms_per_message_responses = 1;
        $this->sms_horizon = 2 *60; //s

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

/*
		if ( $this->readSubject() == true) {
			$this->thing_report = array('thing' => $this->thing->thing, 
				'choices' => false,
				'info' => "A cell number wasn't provided.",
				'help' => 'from needs to be a number.');

		        $this->thing->log( '<pre> Agent "Sms" completed without sending a SMS</pre>' );
			return;
		}
*/

        $this->eventSet($input);

        // Setup Google Client

        $this->getClient();

        if ( $this->readSubject() == true) {
            $this->thing_report = array('thing' => $this->thing->thing, 
                'choices' => false,
                'info' => "A cell number wasn't provided.",
                'help' => 'from needs to be a number.');

            $this->thing->log( 'completed without sending a message.' );
            return;
        }
//        $this->respond();
//        $this->thing->log ( 'completed.' );



		$this->respond();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());

		$this->thing->log ( '<pre> Agent "Sms" completed</pre>' );

		return;

	}

    function eventSet($input = null)
    {
      //  if ($input == null) {$input = $this->body;}

        $this->thing->db->setFrom($this->from);

        $this->thing->json->setField("message0");
        $this->thing->json->writeVariable( array("sms") , $input  );
    }


    public function getClient()
    {

        // https://developers.google.com/api-client-library/php/auth/web-app
//        $key_file_location = $this->thing->container['api']['google_service']['key_file_location'];

//        $this->client = new \Google_Client();
//        $this->client->setApplicationName("Stan");
//        $this->client->setAuthConfig($key_file_location);
//        $this->client->setScopes(['https://www.googleapis.com/auth/chat.bot']);

//        $hangoutschat = new \Google_Service_HangoutsChat($this->client);

//        $message = new \Google_Service_HangoutsChat_Message();

        $text = $this->body['text'];

        //$type = $this->body["type"];

        $space_name = $this->body['msisdn'];

        $user_name = $this->body['to'];


//                $arr = json_encode(array("to"=>$body['msisdn'], "from"=>$body['to'], "subject"=>$body['text']));


        $thing = new Thing(null);
        $thing->Create($space_name,$user_name,$text);
        $agent = new Agent($thing);

        $response = $agent->thing_report['sms'];

        //$message->setText($response);
        $this->sms_message = $response;
        //$hangoutschat->spaces_messages->create($space_name, $message);

        $this->sendSMS($user_name, $response);

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

        $this->thing_report['sms'] = "SMS | " . $test_message;

        //$test_message = str_replace(" | ", "\n", $test_message);

        $received_at = strtotime($this->thing->thing->created_at);
        $time_ago = time() - $received_at;

        // Don't send a message if there isn't enough balance,
        // the number of responses per message would be exceeded, or
        // if the message would be sent 'too late'.
		if (($this->thing->account['stack']->balance['amount'] >= $this->cost ) and
            ($this->sms_count < $this->sms_per_message_responses) and
            ($time_ago < $this->sms_horizon )) {
            // Dev stack Read in stack sms seperator value
            // But for now replace sms seperators and translate to \n
            $test_message = str_replace(" | ", "\n", $test_message);

			$this->sendSms($to, $test_message);
			$this->thing->account['stack']->Debit($this->cost);

// Investigate short codes
// $this->sendUSshortcode($to, $test_message);

			$this->thing_report['info'] = '<pre> Agent "Sms" sent a SMS to ' . $this->from . '.</pre>';

            $this->thing->json->writeVariable( array("sms", "count"), $this->sms_count + 1 );



		} else {

			$this->thing_report['info'] = 'SMS not sent.  Balance of ' . $this->thing->account['stack']->balance['amount'] . " less than " . $this->cost ;
		}


        $this->thing_report['help'] = "This is the agent that manages SMS.";

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



    function sendSMS($to, $text)
    {
$type = "text";
if (strlen($text) != strlen(utf8_decode($text)))
{
    $type = "unicode";
}

        $url = 'https://rest.nexmo.com/sms/json?' . http_build_query(
            [
                'api_key' =>  $this->api_key,
                'api_secret' => $this->api_secret,
'type'=>$type,
                'to' => $to,
                'from' => $this->to,
                'text' => $text
            ]
        );

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



    function sendUSshortcode($to, $text)
    {

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
