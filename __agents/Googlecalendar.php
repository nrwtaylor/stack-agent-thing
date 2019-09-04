<?php
namespace Nrwtaylor\StackAgentThing;
//require_once '/var/www/html/stackr.ca/vendor/autoload.php';
require_once '/var/www/stackr.test/vendor/autoload.php';


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

if (!debug_backtrace()) {

    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(-1);

    $t = new GoogleCalendar();
    $t->client();
}

class GoogleCalendar
{

	function __construct(Thing $thing, $input = null)
    {
		$this->agent_name = "Google Calendar";

        $this->thing = $thing;

        $this->thing_report = array('thing' => $this->thing->thing);
        $this->thing_report['info'] = 'This is a Google Calendar agent.';

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        $this->thing->log( '<pre> Agent "Google Calendar" running on Thing ' .  $this->uuid . ' </pre>' );
        $this->thing->log( '<pre> Agent "Google Calendar" received this Thing "' .  $this->subject . '"</pre>' );


        $this->node_list = array("start"=>array("google calendar"));


        $this->sms_message = "Test";

		// Setup Google Client
		$this->getClient();

		// First.  Find the token.  Populates access_token.  True if there is a problem.
		$this->findToken();

		if ($this->access_token === true) { // No existing access token found 

			$this->thing->log( 'Agent "Google Calendar" did not find an existing oauth token.' );

			// Check whether an authentication code has been passed to the agent.
			if ( is_array($input) ) {
				$this->thing->log( 'Agent "Google Calendar" received an array command.');


				if ( array_key_exists('code', $input) ) {
					$this->getToken($input['code']);

echo $this->access_token;

                    $this->setToken();
                    $this->thing->log('Agent "Google Calendar" got a new oauth token.');

                    $this->thing->log('Agent "Google Calendar" got an oauth token');

				} else {
					$this->requestToken();
					// No access token
					$this->respond();
					return;
				}

				// What other instructions are passed by array?	
				// Not expecting anything.

			} else {

				// No array received.  And no token found...
				$this->requestToken();
				$this->respond();
				return;
			}

		} else {
	        $this->setToken();
			$this->thing->log('Agent "Google Calendar" used an existing oauth token.');
		}


		echo "access Token found";


		// Print the next 10 events on the user's calendar.
		// $calendarId = 'primary';
		$optParams = array(
  			'maxResults' => 10,
  			'orderBy' => 'startTime',
  			'singleEvents' => TRUE,
  			'timeMin' => date('c'),
		);


        //$results = $service->events->listEvents($calendarId, $optParams);
        //$this->getEvents('primary');
        $this->getCalendarList();

        $calendar = $this->client_id = $this->thing->container['stack']['calendar'];
        $this->getEvents($calendar);

        $this->setEvent();

        $this->input = $input;
        $this->cost = 50;


        if ( $this->readSubject() == true) {
            $this->thing_report = array('thing' => $this->thing->thing, 
                'choices' => false,
                'info' => "A cell number wasn't provided.",
                'help' => 'from needs to be a number.');

            $this->thing->log( '<pre> Agent "Google Calendar" completed without sending a message</pre>' );
            return;
        }
        $this->respond();
        $this->thing->log ( 'completed.' );

        return;
	}

    public function getClient()
    {

		// https://developers.google.com/api-client-library/php/auth/web-app
        $this->client_id = $this->thing->container['api']['google_calendar']['client_id'];
        $this->client_secret = $this->thing->container['api']['google_calendar']['client_secret'];
        $this->redirect_uri = $this->thing->container['api']['google_calendar']['redirect_uris']; // May need to tweak this
        $this->access_type = 'offline';

		$this->scopes = "https://www.googleapis.com/auth/calendar";
		$this->approval_prompt = "force";

        $client = new \Google_Client();
        $client->setClientId($this->client_id);
        $client->setClientSecret($this->client_secret);
        $client->setRedirectUri($this->redirect_uri);
        $client->setScopes($this->scopes);
        $client->setApprovalPrompt($this->approval_prompt);
        $client->setAccessType($this->access_type);

        // This passes the uuid through to the redirect url
        $client->setState($this->uuid);

        $this->client = $client;

		return $this->client;
	}


	public function setToken()
    {

		$this->client->setAccessToken($this->access_token);

        // Refresh the token if it's expired.
        // if ($client->isAccessTokenExpired()) {
        //  $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        //  file_put_contents($credentialsPath, json_encode($client->getAccessToken()));

        // echo "Token expired.  Not implemented.";

        //}

		$this->service = new Google_Service_Calendar($this->client);
		return $this->service;
	}

	public function requestToken()
    {
        $this->thing->log( "authCode not found" );
        // Request authorization from the user.
        $authUrl = $this->client->createAuthUrl();

        // echo "<br><br>";
        // echo '<a href="' . $authUrl. '">Allow Stackr to access your Google Calendar</a>';

	    $this->sms_message = '<a href="' . $authUrl. '">Allow Stackr to access your Google Calendar</a>';
	    return;

	}

	public function getToken($authCode)
    {
 		$accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

 		if (isset($accessToken['error'])) {
                	//echo "error: ";
                        $this->thing->log( "Request for access token returned error [" .$accessToken['error']."]." );
                        return true;
        }

 		if (isset($accessToken['access_token'])) {

            $this->thing->json->setField("variables");
	        $this->thing->json->writeVariable( array("google calendar", "access_token") , json_encode($accessToken)  );

			$this->access_token = json_encode($accessToken);
			return $this->access_token;
		}

		// Dump error information.
		// var_dump( $accessToken );

		return true;

	}

    function findToken()
    {
        //$this->thing->json->setField("variables");
        //$names = $this->thing->json->writeVariable( array("google calendargroup", "action"), 'find' );

        $thingreport = $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->variableSearch(null, "google calendar", 99);

        // echo "<br>";

        $tokens = array();

        foreach ($thingreport['things'] as $thing_obj) {

            $thing = new Thing( $thing_obj['uuid'] );

            $thing->json->setField("variables");
            $token = $thing->json->readVariable( array("google calendar", "access_token") );

            if ( ($token == false) or ($token == null) ) {
            } else {
                $tokens[] = $token;
            }

//                        $thing->json->setField("variables");
//                        $refreshed_at = $thing->json->readVariable( array("google calendar", "refreshed_at") );
//                        echo "stack_time" . $refreshed_at;
//                        echo "<br>";

        }

        if ( count($tokens) == 0 ) {
            $this->sms_message .= "";
            $this->sms_message .= " | No Google Calendar token found.";

            $this->access_token = true;
            //      $group = "meep";
        } else {
             $this->access_token = $tokens[0];

             $this->sms_message .= " | This is the Google Calendar function.  Commands: TBD.";
             $this->thingreport['tokens'] = $tokens; 
        }

        return $this->access_token;
    }

	public function getEvents($calendar_name = null)
    {
		if ($calendar_name == null) {
			$calendar_name = 'primary';
		}

		$optParams = array(
			'maxResults' => 10,
			'orderBy' => 'startTime',
			'singleEvents' => TRUE,
			'timeMin' => date('c'),
		);

		$results = $this->service->events->listEvents($calendar_name, $optParams);

        // echo "<br>Start resuts ---<br>";

        if (count($results->getItems()) == 0) {
            print "No upcoming events found.\n";
        } else {
            print "Upcoming events:\n";
            foreach ($results->getItems() as $event) {
                $start = $event->start->dateTime;
                if (empty($start)) {
                    $start = $event->start->date;
                }
                printf("%s (%s)\n", $event->getSummary(), $start);
                echo "<br>";
            }
        }

        echo "<br>--- End results<br>";

        return;
	}


	public function getCalendarList ()
    {
        // https://developers.google.com/google-apps/calendar/v3/reference/calendarList#resource
		$results = $this->service->calendarList->listCalendarList();

        echo "<br>Start resuts ---<br>";

        if (count($results->getItems()) == 0) {
            print "No calendars found.\n";
        } else {
            print "Calendars:\n";
            foreach ($results->getItems() as $item) {
                //$start = $event->start->dateTime;
                //if (empty($start)) {
                //  $start = $event->start->date;
                //}

	            echo $item->getId();
	            echo " ";
	            echo $item->getSummary();
	            echo "<br>";
                //printf("%s (%s)\n", $event->getSummary(), $start);
            }
        }
        echo "<br>End calendar list<br>";
	}


	public function setEvent()
    {

        $test_event_array = array(
            'summary' => 'Test Appointment',
            'location' => '800 Howard St., San Francisco, CA 94103',
            'description' => 'A chance to hear more about Google\'s developer products.',
            'start' => array(
                'dateTime' => '2017-08-31T09:00:00-07:00',
                'timeZone' => 'America/Los_Angeles',
                ),
            'end' => array(
                'dateTime' => '2017-08-31T17:00:00-07:00',
                'timeZone' => 'America/Los_Angeles',
            ),
            'recurrence' => array(
                'RRULE:FREQ=DAILY;COUNT=2'
                ),
            'attendees' => array(
                array('email' => 'lpage@example.com'),
                array('email' => 'sbrin@example.com'),
                ),
            'reminders' => array(
            'useDefault' => FALSE,
            'overrides' => array(
                array('method' => 'email', 'minutes' => 24 * 60),
                array('method' => 'popup', 'minutes' => 10),
                ),
            ),
        );

        $event = new Google_Service_Calendar_Event($test_event_array);

        $calendarId = 'primary';
        $event = $this->service->events->insert($calendarId, $event);
        printf('Event created: %s\n', $event->htmlLink);

        //echo $createdEvent->getId();
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
