<?php
namespace Nrwtaylor\StackAgentThing;

class Events
{
    // An exercise in augemented virtual collaboration. With water.
    // But a Thing needs to know what minding the gap is.

    // Lots of work needed here.
    // Need to decide how to process events.

	public $var = 'hello';

    // This will provide a splosh - a unit of energy converted into a sploshed unit of distance.
    // This is useful for SPLOSH because it is the first step in provided the distance travelled down a path.
    // When provided with a random time interval series of energy inputs.

    // See getEnergy() for dev

    function __construct(Thing $thing, $agent_input = null)
    {
        // Precise timing
        $this->start_time = $thing->elapsed_runtime();

        $this->agent_input = $agent_input;
		$this->thing = $thing;

        $this->thing_report['thing'] = $thing;
        $this->agent_name = "events";

		$this->retain_for = 24; // Retain for at least 24 hours.

        $this->uuid = $thing->uuid;
      	$this->to = $thing->to;
       	$this->from = $thing->from;
       	$this->subject = $thing->subject;
		$this->sqlresponse = null;

        $this->state = "dev";

		$this->thing->log( 'running on Thing ' . $this->thing->nuuid . '.');
		$this->thing->log( 'received this Thing "' . $this->subject . '"');
        $this->thing->log( 'received this Agent Input "' . $this->subject . '"');

        // Get some stuff from the stack which will be helpful.
        // Until SPLOSH gets its own.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->current_time = $this->thing->time();

        $this->get(); // load in last known position variables for current player

		$this->readSubject();

        //$this->doCriticalhit();
//        $this->criticalhitDo();


		$this->respond();

        $this->set();

		$this->thing->log( 'completed.');


		return;
    }

    // Add in code for setting the current distance travelled.
    function set()
    {
        // UK Commonwealth spelling
        $this->thing->json->setField("variables");
        //$names = $this->thing->json->writeVariable( array("critical_hit", "roll"), $this->roll );

        $time_string = $this->thing->time();
        $this->thing->json->writeVariable( array("events", "refreshed_at"), $time_string );

//        $splosh_timestamp = $this->thing->microtime();
//        $this->thing->json->writeVariable( array("splosh", "timestamp"), $splosh_timestamp );

    }

    function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("events", "refreshed_at") );

        //$micro_timestamp = $this->thing->json->readVariable( array("splosh", "timestamp") );

        // Keep second level timestamp because I'm not
        // sure Stackr can deal with microtimes (yet).
        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("events", "refreshed_at"), $time_string );
        }

    }

// -----------------------

    public function getClocktime()
    {
        $this->clocktime = new Clocktime($this->thing, "clocktime");
    }

	private function respond()
    {
		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;
		$from = "events";

        $this->makeMessage();
        $this->makeSms();

        $this->thingreportEvents();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
		    $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->makeWeb();

		return $this->thing_report;
	}

    function thingreportEvents()
    {
        $this->thing_report['message'] = $this->message;
        $this->thing_report['keyword'] = $this->keyword;
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->message;
    }

    private function eventsDo()
    {
        // What does this do?

    }

    public function doEvents()
    {
        $this->earliest_event_string = "None of the events apis found anything. Which is weird, because there has to be something on.";

        // What is Events.
        $keywords = $this->search_words;

        $this->events = array();

        $this->eventful = new Eventful($this->thing, "eventful ". $keywords);
        $this->events = array_merge($this->events, $this->eventful->events);

        $this->meetup = new Meetup($this->thing, "meetup ". $keywords);
        $this->events = array_merge($this->events, $this->meetup->events);

        $this->brownpapertickets = new Brownpapertickets($this->thing, "brownpapertickets ". $keywords);
        $this->events = array_merge($this->events, $this->brownpapertickets->events);



        //$this->ticketmaster = new Ticketmaster($this->thing, "ticketmaster ". $keywords);


//var_dump($this->eventful->events);
//var_dump($this->meetup->events);
//var_dump($this->brownpapertickets->events);


//exit();

        foreach($this->events as $eventful_id=>$event) {

            $event_name = $event['event'];
            $event_time = $event['runat'];
            $event_place = $event['place']; // Doesn't presume the Rio

            $time_to_event =  strtotime($event_time) - strtotime($this->current_time) ;
            if (!isset($time_to_earliest_event)) {
               $time_to_earliest_event = $time_to_event;
               $event_string = $this->eventful->eventString($event);
               $this->earliest_event_string = $this->thing->human_time($time_to_earliest_event) . " until " . $event_string . ".";

            } else {
                $this->response = "Got the current Events.";
                if ($time_to_earliest_event > $time_to_event) {

                    $time_to_earliest_event = $time_to_event;
//                    $earliest_event_name = $event_name;
//                    $earliest_event_time = $event_time;
//                    $earliest_event_place = $event_place;
                    $event_string = $this->eventful->eventString($event);

                    $this->earliest_event_string = $this->thing->human_time($time_to_earliest_event) . " until " . $event_string . ".";


                    if ($time_to_event < 0) {
                        $this->earliest_event_string = "About to happen. Happening. Or just happened. " . $event_string . ".";
                    }

                    $this->response = "Got the next event.";

                    $this->runat = new Runat($this->thing, "runat " . $event_time);


                    if ($this->runat->isToday($event_time)) {
                        $this->response = "Got today's event.";
                    }
            }
            }
                    

        }


  //      $this->response = "Got the next Geekenders.";
    }

	public function readSubject()
    {
		$this->response = "Heard Events.";
		$this->keyword = "events";

        if ($this->agent_input != null) {
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "events is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("events is")); 
        } elseif (($pos = strpos(strtolower($input), "events")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("events")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

    if ($filtered_input != "") {
        $this->search_words = $filtered_input;
        $this->response = "Asked Events about " . $this->search_words . " events";
        //return false;
    }



        $this->doEvents();




		return $this->response;
	}

    public function makeMessage()
    {
        $message = $this->eventful->message;


        $this->message = $this->earliest_event_string; //. ".";
        $this->thing_report['message'] = $message;
    }

    public function makeSms()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/events';

        $sms = "EVENTS ";
        $sms .= " | " . $this->earliest_event_string;
        $sms .= " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/events';

        $html = "<b>EVENTS WATCHER</b>";

        $html .= '<br>Events watcher says , "';
        $html .= $this->sms_message. '"';

        $html .= "<p>";
        foreach($this->events as $id=>$event) {
            // devstack Need to move the event string code to Event.php
//var_dump($event);
//exit();
            $e = $event['event'];
            $runat = $event['runat'];
            $p = $event['place'];
            //$e = $this->eventful->eventString($event);
            $html .= "<br>" . ($e). " " . $runat . " " . $p;
        }

        $this->web_message = $html;
        $this->thing_report['web'] = $html;
    }

}

?>
