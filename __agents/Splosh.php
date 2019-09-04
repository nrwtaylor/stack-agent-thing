<?php
namespace Nrwtaylor\StackAgentThing;

class Splosh
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
        $this->agent_name = "splosh";

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

        $this->doSplosh();

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
        $names = $this->thing->json->writeVariable( array("splosh", "distance"), $this->distance );

        $time_string = $this->thing->time();
        $this->thing->json->writeVariable( array("splosh", "refreshed_at"), $time_string );

        $splosh_timestamp = $this->thing->microtime();
        $this->thing->json->writeVariable( array("splosh", "timestamp"), $splosh_timestamp );
    }

    function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("splosh", "refreshed_at") );

        $micro_timestamp = $this->thing->json->readVariable( array("splosh", "timestamp") );

        // Keep second level timestamp because I'm not
        // sure Stackr can deal with microtimes (yet).
        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("splosh", "refreshed_at"), $time_string );
        }

        // And in microtime code for Splosh.
        if ($micro_timestamp == false) {
            $micro_timestamp = $this->thing->microtime();
            $this->thing->json->writeVariable( array("splosh", "timestamp"), $micro_timestamp );
        }

        // If it has already been processed ...
        $this->last_timestamp = $micro_timestamp;

        $this->distance = $this->thing->json->readVariable( array("splosh", "distance") );
        $this->response = "Loaded Splosh distance and timestamp.";
    }

// -----------------------

	private function respond()
    {
		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;
		$from = "splosh";

        $this->makeSms();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
		    $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->makeWeb();

        $this->thing_report['sms'] = $this->sms_message;

		return $this->thing_report;
	}

    private function sploshDo()
    {
        // What does splosh do. doSplosh is doing a Splosh.

        // Splosh is a tool to connect your Concept2 to our world.  Welcome.

        // What open source means is that everybody can read and see our code.
        // Because you need to share.  So do we.

        // Splosh is a project of Stackr Interactive Ltd based in Burnaby, BC.  No.  Not EA.

        // And our code is what SPLOSH does.  Hello Robot.



        // A splosh is a unified step of work measured from your Concept2 device.
        // A splosh is a unit of energy.  It happens to be exactly the same as a metric calorie.
        // Weird.



        // And then Splosh figures out how for that energy would have got you along your path.

        // Welcome to <Insert Robot Name Here>.  The Robot Coxswain.

        // Yeah we want to steer the path too.  But this is Splosh.  
        // For Sploshers who don't want to row bikes.
        // But also those who want to ride boats.
        // In human crews.

        // If you don't want to worry about where you are going, don't worry about <Insert Robot Name Here>.

        // Code?

        // Sure.

        $this->getEnergy();

        

    }

    public function doSplosh()
    {
        // What is to do a Splosh.

        // This will be the full cycle of a Splosh.

        // Get the last 12 dof coordinate and microtimestamp.  (Or minimum acceptable dofs.)
        // Project next 12 dof coordinate

        // Get the other players 12dof coordinate.
        // Publish current players 12 dof coordinate.

        // Calculate information of interest from player-player coordinate pairs.
        // Respond with information of interest.

        // Splosh.

        // For now take unit of energy and convert it into distance travelled.
        $distance_travelled = $this->sploshEnergy($this->energy->number);

        $this->distance_travelled = $distance_travelled;

        $this->getDistance();
        $this->getTime();

        // I think a Splosh is not the catching oars.  It's the rhythm tick interval.

    }

    private function sploshEnergy($energy_text = null)
    {
        if ($energy_text == null) {$energy_text = $this->energy->number;}

        $scalar = 0;
        $this->velocity = 0;
        $this->velocity = -1; // m/s test current
        $this->acceleration = 0;

        // Realworld elapsed time
        //$tick_time = 534 / 1000; // ms > s

//var_dump($this->getTime());
//exit();
        $tick_time = $this->getTime(); //s but test

        // Imagining a scale of 1 to 10.
        // Hoping most values are around 7.
        // But not expecting beflow 6 or over 8.
        $sploshiness = $scalar + (7 + rand(-1*100, 1*100) / 100);

        // https://www.mansfieldct.org/Schools/MMS/staff/hand/work=fxd.htm
        //  A force F applied through distance D = energy work W 

        // Our energy gets converted sploshly into force.
        // I think this comes straight out of the Concept2.
        $this->force = $energy_text * $sploshiness;

        //$distance = $force / $energy_work;
        // But you can just do this if coding a game.
        // For now.
        $distance = $energy_text * $sploshiness;

        $this->distance_acceleration = ($this->acceleration * $tick_time * $tick_time) /2;
        $this->distance_velocity = ($this->velocity * $tick_time);


//        $distance_energy = ($acceleration * $tick_time * $tick_time) /2
 //                            + ($velocity * $tick_time)
 //                               + $distance; 
        echo "time " . $tick_time . "\n";
        echo "a" . $this->distance_acceleration . "\n";
        echo "v" . $this->distance_velocity . "\n";
        echo "s" . $distance;

        $distance_energy = $this->distance_acceleration + $this->distance_velocity + $distance;

        // 1/2at^2 + vt + d

        // Is this distance sploshy enough?
        

        return $distance_energy;

    }

    private function getVelocity()
    {

        // https://www.ncbi.nlm.nih.gov/pubmed/7421474
        // http://eodg.atm.ox.ac.uk/user/dudhia/rowing/physics/basics.html

    }

    private function getDistance()
    {
        // Distance down the path.  Only number that matters.
        // For now.

        if ((!isset($this->distance)) or ($this->distance == false)) {$this->distance = 0;}

        $this->distance += $this->distance_travelled;

    }

    public function getTime()
    {
        if (isset($this->elapsed_clock)) {return $this->elapsed_clock;}
        // Only do this once.
        // Can't have calculations based on different timestamps.
        if (!isset($this->current_timestamp)) {$this->current_timestamp = $this->thing->microtime();}

        //$this->current_timestamp = $this->thing->microtime();
        $this->elapsed_clock = $this->microtime_diff($this->last_timestamp, $this->current_timestamp);
        return $this->elapsed_clock;
    }

// https://gist.github.com/hadl/5721816
function microtime_diff($start, $end)
{

    // Lots of testing needed on this :/

    list($start_date, $start_clock, $start_usec) = explode(" ", $start);
    list($end_date, $end_clock, $end_usec) = explode(" ", $end);

    $diff_date = strtotime($end_date) - strtotime($start_date);

    $diff_clock = strtotime($end_clock) - strtotime($start_clock);

    $diff_usec = floatval($end_usec) - floatval($start_usec);

    return floatval($diff_date) + floatval($diff_clock) + $diff_usec;
}

    private function getEnergy()
    {
        // Insert code to talk to Concept2.  
        $text = "Nonsense text string, json string, or other text representation of 1 energy units";

        // Going to be a call from the unit for a Splosh.  So it should be provided.
        // So retrieve the Concept2 energy last posted.
        // Call that a custom Splosh function to read and write the energy to a splosh variable.
        $this->energy = new Number($this->thing, "energy " . $text); // test uniqueness?

        // Which means we now have 
        // var_dump($this->energy->number);
    }

	public function readSubject()
    {
		$this->response = "Sploshed.";
		//$this->sms_message = "SPLOSH | https://dictionary.cambridge.org/dictionary/english/splosh";
		$this->message = "https://dictionary.cambridge.org/dictionary/english/splosh";
		$this->keyword = "splosh";

		$this->thing_report['keyword'] = $this->keyword;
//		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['email'] = $this->message;


        $this->sploshDo();

		return $this->response;
	}

    public function makeSms()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/splosh';

        if ($this->distance != 0) {
            $sms = "SPLOSHED " . $this->distance ."m";
        } else {
            $sms = "SPLOSH " . $this->distance_travelled . "m";
        }


        //$sms .= " | https://dictionary.cambridge.org/dictionary/english/splosh";
        $sms .= " | " . $link . " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function makeWeb()
    {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/splosh';


        $html = "<b>SPLOSH</b>";
        $html .= "<p><b>Splosh Variables</b>";
        //$html .= '<br>state ' . $this->state . '';

        $html .= "<br>Distance just sploshed is " . $this->distance_travelled . $this->distance_unit_name;
        $html .= "<br>Total distance sploshed " . $this->distance . $this->distance_unit_name;
        $html .= "<br>Elapsed time between splash " . $this->elapsed_clock * 1e3 . $this->time_unit_name;

// . $this->time_unit_name;



        $html .= "<br>Last splosh time " . $this->last_timestamp;

        // You can hardcode you Splosh page here
        $html .= "<p><b>Splosh splosher link</b>";
        $html .= "<br>";

        $html .= '<a href="' . $link . '">';
//        $web .= $this->html_image;
        $html .= $link;

        $html .= "</a>";
        $html .= "<br>";
        $html .= "<br>";
        $html .= 'Splosh says, "';
        $html .= $this->sms_message. '"';





        $this->web_message = $html;
        $this->thing_report['web'] = $this->web_message;
    }





}

?>
