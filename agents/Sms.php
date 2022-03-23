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
        $this->body = null;
        if (is_array($input)) {
            $this->body = $input;
        }

        $this->input = $input;
        $this->cost = 50;

        $this->agent_prefix = 'Agent "SMS"';

        $this->test = "Development code";

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;

        // Example

        $this->api_key = $this->thing->container['api']['nexmo']['api_key'];
        $this->api_secret =
            $this->thing->container['api']['nexmo']['api_secret'];

        $this->numbers = [];
        if (isset($this->thing->container['stack']['sms_numbers'])) {
            $this->numbers = $this->thing->container['stack']['sms_numbers'];
        }

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        // Borrow this from iching
        $time_string = $this->thing->Read([
            "sms",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["sms", "refreshed_at"],
                $time_string
            );
        }

        $this->sms_count = $this->thing->Read(["sms", "count"]);

        if ($this->sms_count == false) {
            $this->sms_count = 0;
        }

        //$this->sendSMS();
        //$this->sendUSshortcode();

        $this->node_list = ["sms send" => ["sms send"]];

        $this->thing->log(
            '<pre> Agent "Sms" running on Thing ' . $this->uuid . ' </pre>'
        );
        $this->thing->log(
            '<pre> Agent "Sms" received this Thing "' .
                $this->subject .
                '"</pre>'
        );
        $this->sms_per_message_responses = 1;
        $this->sms_horizon = 2 * 60; //s

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

        if ($this->readSubject() == true) {
            $this->thing_report = [
                'thing' => $this->thing->thing,
                'choices' => false,
                'info' => "A cell number wasn't provided.",
                'help' => 'from needs to be a number.',
            ];

            $this->thing->log('completed without sending a message.');
            return;
        }

        $this->respond();

        $this->thing->log(
            $this->agent_prefix .
                'ran for ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.',
            "OPTIMIZE"
        );

        $this->thing_report['etime'] = number_format(
            $this->thing->elapsed_runtime()
        );

        $this->thing->log('<pre> Agent "Sms" completed</pre>');
    }

    function eventSet($input = null)
    {
        //  if ($input == null) {$input = $this->body;}

        $this->thing->db->setFrom($this->from);

        $this->thing->json->setField("message0");
        $this->thing->json->writeVariable(["sms"], $input);
    }

    public function getClient()
    {
        //devstack
        return;
        if (!isset($this->body)) {
            return;
        }

        $text = $this->body['text'];

        //$type = $this->body["type"];

        $space_name = $this->body['msisdn'];
        $user_name = $this->body['to'];

    }

    public function logSms($text) {

file_put_contents('/tmp/s.log', $text, FILE_APPEND);


    }

    // -----------------------

    private function respond()
    {
$this->logSms("merp");
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

        $received_at = strtotime($this->thing->thing->created_at);
        $time_ago = time() - $received_at;

        // Don't send a message if there isn't enough balance,
        // the number of responses per message would be exceeded, or
        // if the message would be sent 'too late'.

        if (
            $this->thing->account['stack']->balance['amount'] >= $this->cost and
            $this->sms_count < $this->sms_per_message_responses and
            $time_ago < $this->sms_horizon
        ) {
            // Dev stack Read in stack sms seperator value
            // But for now replace sms seperators and translate to \n
            $test_message = str_replace(" | ", "\n", $test_message);

            $response = $this->sendSms($to, $test_message);
//$this->logSms($response);
            if ($response === true) {
                $this->thing_report['info'] = 'did not send a SMS.';
                return;
            }

            $this->thing->account['stack']->Debit($this->cost);

            // Investigate short codes
            // $this->sendUSshortcode($to, $test_message);

            $this->thing_report['info'] =
                '<pre> Agent "Sms" sent a SMS to ' . $this->from . '.</pre>';

            $this->thing->Write(
                ["sms", "count"],
                $this->sms_count + 1
            );
        } else {
$this->logSms("Thing not sent.");

            $this->thing_report['info'] =
                'SMS not sent.  Balance of ' .
                $this->thing->account['stack']->balance['amount'] .
                " less than " .
                $this->cost;
        }

        $this->thing_report['help'] = "This is the agent that manages SMS.";
    }

    public function readSubject()
    {
        if (!is_numeric($this->from)) {
            // This isn't a textable number.
            return true;
        }

        return false;
    }

    function sendSMS($to, $text)
    {
        if (!in_array($this->to, $this->numbers)) {
            return true;
        }

        $type = "text";

        if (strlen($text) != strlen(utf8_decode($text))) {
            $type = "unicode";
        }

        $url =
            'https://rest.nexmo.com/sms/json?' .
            http_build_query([
                'api_key' => $this->api_key,
                'api_secret' => $this->api_secret,
                'type' => $type,
                'to' => $to,
                'from' => $this->to,
                'text' => $text,
            ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        //Decode the json object you retrieved when you ran the request.
        $decoded_response = json_decode($response, true);

        error_log(
            'You sent ' . $decoded_response['message-count'] . ' messages.'
        );

        foreach ($decoded_response['messages'] as $message) {
            if ($message['status'] == 0) {
                error_log("Success " . $message['message-id']);
            } else {
                error_log(
                    "Error {$message['status']} {$message['error-text']}"
                );
            }
        }
    }

    function sendUSshortcode($to, $text)
    {
        //https://rest.nexmo.com/sc/us/alert/json?api_key={$your_key}&api_secret={$your_secret}&
        // to={$to}&key1={$value1}&key2={$value2}

        $url =
            'https://rest.nexmo.com/sc/us/alert/json?' .
            http_build_query([
                'api_key' => $this->api_key,
                'api_secret' => $this->api_secret,
                'to' => $to,
                'message' => $text,
            ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        // devstack log to db

        //Decode the json object you retrieved when you ran the request.
        $decoded_response = json_decode($response, true);

        error_log(
            'You sent ' . $decoded_response['message-count'] . ' messages.'
        );

        foreach ($decoded_response['messages'] as $message) {
            if ($message['status'] == 0) {
                error_log("Success " . $message['message-id']);
            } else {
                error_log(
                    "Error {$message['status']} {$message['error-text']}"
                );
            }
        }
    }
}
