<?php
namespace Nrwtaylor\StackAgentThing;
require_once '/var/www/stackr.test/vendor/autoload.php';


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

if (!debug_backtrace()) {

    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(-1);
    $thing = new Thing(null);
    $thing->Create("hangouts_a","hangouts_b", "s/ dev Googlehangouts");
    $t = new GoogleHangouts($thing);
    $t->client();
}

// Okay
// So we want to do this.
// https://developers.google.com/hangouts/chat/how-tos/rest-api

// Authentication using a service account is a prerequisite for using the Hangouts Chat REST API.

class GoogleHangouts
{

	function __construct(Thing $thing, $input = null)
    {
        $this->body = $input['body'];

		$this->agent_name = "Google Hangouts";

        $this->thing = $thing;

        $this->thing_report = array('thing' => $this->thing->thing);
        $this->thing_report['info'] = 'This is a Google Hangouts agent.';

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        $this->node_list = array("start"=>array("google hangouts"));

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
        $this->respond();
        $this->thing->log ( 'completed.' );
	}

    function eventSet($input = null)
    {
      //  if ($input == null) {$input = $this->body;}

        $this->thing->db->setFrom($this->from);

        $this->thing->Write( array("google") , $input, 'message0'  );


    }

    public function getClient()
    {

		// https://developers.google.com/api-client-library/php/auth/web-app
        $key_file_location = $this->thing->container['api']['google_service']['key_file_location'];

        $this->client = new \Google_Client();
        $this->client->setApplicationName("Stan");
        $this->client->setAuthConfig($key_file_location);
        $this->client->setScopes(['https://www.googleapis.com/auth/chat.bot']);

        $hangoutschat = new \Google_Service_HangoutsChat($this->client);

        $text = $this->body["message"]["text"];

        $type = $this->body["type"];

        $space_name = $this->body["space"]["name"];

        $user_name = $this->body["user"]["name"];

        $thread_name = $this->body["message"]["thread"]["name"];


        $thing = new Thing(null);
        $thing->Create($space_name,"agent",$text);
        $agent = new Agent($thing);

        $response = $agent->thing_report['sms'];

        $t = array('name'=>$thread_name);

        $message = new \Google_Service_HangoutsChat_Message(['text'=>$response,'thread'=>$t]);
//        $message->setText($response);


        $this->sms_message = $response;
        $hangoutschat->spaces_messages->create($space_name, $message);

	}

    private function respond()
    {
        // Thing actions

        $this->thing->flagGreen();

        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;


         $message_thing = new Message($this->thing, $this->thing_report);

         $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->thing_report['choices'] = false;
        $this->thing_report['help'] = 'In development.';
        $this->thing_report['log'] = $this->thing->log;


    }


    public function readSubject()
    {

    }


}
