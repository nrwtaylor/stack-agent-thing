<?php
namespace Nrwtaylor\StackAgentThing;

class Criticalhit
{
    // An exercise in augemented virtual collaboration. With water.
    // But a Thing needs to know what minding the gap is.

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
        $this->agent_name = "critical hit";

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

        $this->distance_unit_name = "m";
        $this->time_unit_name = "ms";

        $this->get(); // load in last known position variables for current player

		$this->readSubject();

        //$this->doCriticalhit();
//        $this->criticalhitDo();


		$this->respond();

        $this->set();

		$this->thing->log( 'completed.');

        //echo $this->thing->elapsed_runtime() - $this->start_time;


		return;
    }

    // Add in code for setting the current distance travelled.
    function set()
    {
        // UK Commonwealth spelling
        $this->thing->json->setField("variables");
        //$names = $this->thing->json->writeVariable( array("critical_hit", "roll"), $this->roll );

        $time_string = $this->thing->time();
        $this->thing->json->writeVariable( array("critical_hit", "refreshed_at"), $time_string );

//        $splosh_timestamp = $this->thing->microtime();
//        $this->thing->json->writeVariable( array("splosh", "timestamp"), $splosh_timestamp );

    }

    function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("critical_hit", "refreshed_at") );

        //$micro_timestamp = $this->thing->json->readVariable( array("splosh", "timestamp") );

        // Keep second level timestamp because I'm not
        // sure Stackr can deal with microtimes (yet).
        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("critical_hit", "refreshed_at"), $time_string );
        }

        // And in microtime code for Splosh.
        //if ($micro_timestamp == false) {
        //    $micro_timestamp = $this->thing->microtime();
        //    $this->thing->json->writeVariable( array("splosh", "timestamp"), $micro_timestamp );
        //}


        // If it has already been processed ...
        //$this->last_timestamp = $micro_timestamp;


        //$this->roll = $this->thing->json->readVariable( array("splosh", "distance") );
        //$this->response = "Loaded Splosh distance and timestamp.";
    }

// -----------------------

	private function respond() {

		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;
		$from = "criticalhit";

        $this->makeSms();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
		    $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->makeWeb();

        $this->thing_report['sms'] = $this->sms_message;

		return $this->thing_report;
	}

    private function criticalhitDo()
    {
        // What does this do?

        $this->getRoll();

        

    }

    public function doCriticalhit()
    {
        // What is a Critical Hit.
        $this->eventful = new Eventful($this->thing, "eventful critical%20%hit");
        $this->response = "Rolled a " . $this->die_roll. ".";
    }

    public function doMiss()
    {

        // What is a Critical Hit.

        //$this->eventful = new Eventful($this->thing, "eventful critical%20%hit");

        //var_dump($eventful->message);

        // I think a Splosh is not the catching oars.  It's the rhythm tick interval.
        $this->response = "Didn't roll a 20.";
    }


    private function criticalhitRoll($text = null)
    {
        if ($text == null) {$text = $this->die_roll;}

        $this->outcome = "merp";
        if ($text == "20") {
            $this->outcome = "critical hit";
            $this->doCriticalHit();
        } else {
            $this->doMiss();
        }

        //$this->outcome = "merp";

        return $this->outcome;

    }

    private function getRoll()
    {
        // Insert code to talk to Concept2.  
        $text = "d20";

        // Going to be a call from the unit for a Splosh.  So it should be provided.
        // So retrieve the Concept2 energy last posted.
        // Call that a custom Splosh function to read and write the energy to a splosh variable.
        $this->roll = new Roll($this->thing, "" . $text); // test uniqueness?

        $this->die_roll = $this->roll->result[1]['roll'];

        $this->criticalhitRoll($this->die_roll);

    }

	public function readSubject()
    {
		$this->response = "Rolled.";
		//$this->sms_message = "SPLOSH | https://dictionary.cambridge.org/dictionary/english/splosh";
		$this->message = "http://riotheatre.ca/event/the-critical-hit-show-a-live-dd-comedy-experience/";
		$this->keyword = "critical";

		$this->thing_report['keyword'] = $this->keyword;
//		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['email'] = $this->message;


        $this->criticalhitDo();

		return $this->response;
	}

    public function makeSms()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/criticalhit';

        if ($this->outcome == "critical hit") {
            $sms = "CRITICAL HIT " . $this->roll->roll ." " . $this->die_roll;
            $sms .= " | " . $this->eventful->message;

        } else {
            $sms = "MERP " . $this->roll->roll . " " . $this->die_roll;
            $sms .= " | " . $this->message;
        }

        //$sms .= " | https://dictionary.cambridge.org/dictionary/english/splosh";
        $sms .= " | " . $this->response;


        $this->sms_message = $sms;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function makeWeb()
    {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/splosh';


        $html = "<b>Critical Hit</b>";
        $html .= "<p><b>Splosh Variables</b>";
        //$html .= '<br>state ' . $this->state . '';

        $html .= "<br>Rolled a " . $this->roll->roll;

// . $this->time_unit_name;



//        $html .= "<br>Last critical hit time " . $this->last_timestamp;

        // You can hardcode you Splosh page here
        //$html .= "<p><b>Splosh splosher link</b>";
        //$html .= "<br>";

        //$html .= '<a href="' . $link . '">';
//        $web .= $this->html_image;
        //$html .= $link;

        //$html .= "</a>";
        //$html .= "<br>";
        //$html .= "<br>";
        $html .= 'Critical Hit says, "';
        $html .= $this->sms_message. '"';





        $this->web_message = $html;
        $this->thing_report['web'] = $this->web_message;
    }





}

?>
