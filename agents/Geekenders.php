<?php
namespace Nrwtaylor\StackAgentThing;

class Geekenders
{
    // https://www.facebook.com/Geekenders/

	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        // Precise timing
        $this->start_time = $thing->elapsed_runtime();

        $this->agent_input = $agent_input;
		$this->thing = $thing;

        $this->thing_report['thing'] = $thing;
        $this->agent_name = "geekenders";

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
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->current_time = $this->thing->time();

        $this->get(); // load in last known position variables for current player

		$this->readSubject();

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

        $time_string = $this->thing->time();
        $this->thing->json->writeVariable( array("geekenders", "refreshed_at"), $time_string );
    }

    function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("geekenders", "refreshed_at") );

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("geekenders", "refreshed_at"), $time_string );
        }
    }

    // -----------------------

    public function getClocktime()
    {
        $this->clocktime = new Clocktime($this->thing, "clocktime");
    }

	private function respond() {

		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;
		$from = "criticalhit";

        $this->makeMessage();
        $this->makeSms();

        $this->thingreportGeekenders();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
		    $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->makeWeb();

		return $this->thing_report;
	}

    function thingreportGeekenders()
    {
        $this->thing_report['message'] = $this->message;
        $this->thing_report['keyword'] = $this->keyword;
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->message;
    }

    private function geekendersDo()
    {
        // What does this do?
    }

    public function doGeekenders()
    {
        $this->earliest_event_string = "Didn't find anything. Which is weird, because there has to be a geekenders show. Check https://www.facebook.com/Geekenders/";

        // What is a Geekenders.
        $this->eventful = new Eventful($this->thing, "eventful geekenders");

        foreach($this->eventful->events as $eventful_id=>$event) {

            $event_name = $event['event'];
            $event_time = $event['runat'];
            $event_place = $event['place']; // Doesn't presume the Rio

            $time_to_event =  strtotime($event_time) - strtotime($this->current_time) ;
            if (!isset($time_to_earliest_event)) {
               $time_to_earliest_event = $time_to_event;
               $event_string = $this->eventful->eventString($event);
               $this->earliest_event_string = $this->thing->human_time($time_to_earliest_event) . " until " . $event_string . ".";

            } else {
                $this->response = "Got the current Geekenders.";
                if ($time_to_earliest_event > $time_to_event) {

                    $time_to_earliest_event = $time_to_event;
                    $event_string = $this->eventful->eventString($event);

                    $this->earliest_event_string = $this->thing->human_time($time_to_earliest_event) . " until " . $event_string . ".";

                    if ($time_to_event < 0) {
                        $this->earliest_event_string = "About to happen. Happening. Or just happened. " . $event_string . ".";
                    }

                    $this->response = "Got the next Geekenders.";

                    $this->runat = new Runat($this->thing, "runat " . $event_time);


                    if ($this->runat->isToday($event_time)) {
                        $this->response = "Got today's Geekenders.";
                    }
                }
            }
        }
    }

	public function readSubject()
    {
		$this->response = "Heard Geekenders.";
		$this->keyword = "geekenders";

        $this->doGeekenders();

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
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/geekenders';

        $sms = "GEEKENDERS ";
        $sms .= " | " . $this->earliest_event_string;
        $sms .= " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function makeWeb()
    {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/geekenders';


        $html = "<b>GEEKENDERS WATCHER</b>";

        $html .= '<br>Geekenders watcher says , "';
        $html .= $this->sms_message. '"';

        $html .= "<p>";
        foreach($this->eventful->events as $id=>$event) {
            $e = $this->eventful->eventString($event);
            $html .= "<br>" . ($e);
        }



        $this->web_message = $html;
        $this->thing_report['web'] = $html;
    }

}

?>
