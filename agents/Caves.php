<?php
namespace Nrwtaylor\StackAgentThing;
use RecursiveIteratorIterator;
use RecursiveArrayIterator;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Caves extends Agent
{
	public $var = 'hello';

    // Lots of work needed here.
    // Currently has persistent coordinate movement (north, east, south, west).
    // State selection is dev.

    // Add a place array. Base it off a 20-node shape.
    // Get path selecting throught the array for Wumpus and Player(s) working.

    function init()
    {
		$this->test= "Development code";

        $this->state = null;

		// Load in some characterizations.
		$this->short_name = $this->thing->container['stack']['short_name'];

        $this->sms_seperator = $this->thing->container['stack']['sms_separator'];
		$this->sms_address = $this->thing->container['stack']['sms_address'];

        // Get some stuff from the stack which will be helpful.
        $this->word = $this->thing->container['stack']['word'];
        $this->email = $this->thing->container['stack']['email'];

		// Load in time quantums
        $this->cron_period = $this->thing->container['stack']['cron_period']; // 60s
		$this->thing_resolution = $this->thing->container['stack']['thing_resolution']; // 1ms

		// Load in a pointer to the stack record.
		$this->stack_uuid = $this->thing->container['stack']['uuid'];

        $this->primary_place = "caves";

        $this->created_at = $this->thing->thing->created_at;

		$this->sqlresponse = null;


        $this->node_list = array("start"=>array("inside nest"=>array("nest maintenance"=>array("patrolling"=>"foraging","foraging")),"midden work"=>"foraging"));

$this->caves = array("1"=>array("2", "3","4"),
   			 "2"=>array("1", "5", "6"),
			 "3"=>array("1", "7", "8"),
			 "4"=>array("1", "9", "10"),
"5"=>array("2", "9", "11"),
"6"=>array("2", "7", "12"),
"7"=>array("3", "6", "13"),
"8"=>array("3", "10", "14"),
"9"=>array("4", "5", "15"),
"10"=>array("4", "8", "16"),
"11"=>array("5", "12", "17"),
"12"=>array("6", "11", "18"),
"13"=>array("7", "14", "18"),
"14"=>array("8", "13", "19"),
"15"=>array("9", "16", "17"),
"16"=>array("10", "15", "19"),
"17"=>array("11", "20", "15"),
"18"=>array("12", "13", "20"),
"19"=>array("14", "16", "20"),
"20"=>array("17", "18", "19"));

$this->node_list = $this->caves;
var_dump($this->caves);


        $info = 'The "Caves" agent creates and manages some caves.';
		$info .= 'You can explore Caves by going BACK, LEFT or RIGHT.';

//        $this->cave = $this->thing->choice->load($this->primary_place);
        //$this->thing->choice->Choose($this->state);
        //$this->state = $this->thing->choice->load('hive');
        //$choices = $this->thing->choice->makeLinks($this->state);
        //$this->choices = $choices;
        //$this->thing_report['choices'] = $choices ;
//var_dump($this->cave);
//        $this->thing->choice->Create($this->primary_place, $this->caves, $this->cave);
//        $this->thing->choice->Choose($this->cave);

//        $choices = $this->thing->choice->makeLinks($this->cave);
//var_dump ($choices);
        $this->getCave();
    }

    public function run()
    {
        $this->getCave();
        $this->getClocktime();
        $this->getBar();
        $this->getCoordinate();

        $this->getTick();
    }

    public function set()
    {
        $this->thing->json->writeVariable( array($this->agent_name, "name"), $this->cave_name );



        $this->number_agent = 3;
        $this->number_agent->set();




        //$this->thing->choice->Choose($this->state);

        //$this->state = $this->thing->choice->load($this->primary_place);
    }

    public function get($crow_code = null)
    {
        $this->current_time = $this->thing->json->time();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $this->time_string = $this->thing->json->readVariable( array($this->agent_name, "refreshed_at") );


        if ($crow_code == null) {$crow_code = $this->uuid;}

        if ($this->time_string == false) {
            $this->thing->json->setField("variables");
            $this->time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array($this->agent_name, "refreshed_at"), $this->time_string );
        }

        $this->refreshed_at = strtotime($this->time_string);

        $this->thing->json->setField("variables");
        $this->cave_name = $this->thing->json->readVariable( array($this->agent_name, "name") );

        if( ($this->cave_name == false)) {
            $this->spawn();
            //$this->cave = random_int(1,20);
        }

        //if( ($this->left_count == false) or ($this->left_count = "")) {$this->left_count = 0;$this->right_count = 0;}
        //if( ($this->right_count == false) or ($this->right_count = "")) {$this->left_count = 0;$this->right_count = 0;}

        // For the Crow
//        $this->created_at = $this->thing->thing->created_at;

    //    $this->cave_name = $this->thing->choice->load($this->primary_place);
    //    $this->entity = new Entity ($this->thing, $this->primary_place);
}

    function alphanumeric($input) {
        $value = preg_replace("/[^a-zA-Z0-9]+/", "", $input);
        $value = substr($value, 0, 34);
        return $value;
        }


    private function recursiveFind(array $array, $needle)
    {

        // Generalized needle in haystack with RecursiveArrayIterator
        // by others.

        $iterator  = new RecursiveArrayIterator($array);
        $recursive = new RecursiveIteratorIterator(
            $iterator,
            RecursiveIteratorIterator::SELF_FIRST
        );

        if (is_string($needle)) {
            $needle = $this->alphanumeric($needle);
        }

        foreach ($recursive as $key => $value) {
            if ($this->alphanumeric($key) === $needle) {
                $choices = array();
                if (is_array($value)) {
                    foreach($value as $child_key=>$child_value) {
                        if (is_numeric($child_key)) {
                            $choices[] = $child_value;
                        } else {
                            $choices[] = $child_key;
                        }
                    }
                    return $choices;
                }

                if (is_string($value)) {return array($value);}
            }
            
            if ($value === $needle) {return array();}
        }
    }


/*
    public function run()
    {
//        $this->thing->choice->Choose($this->state);
//        $this->thing->log('state is "' . $this->state . '".');

      //  $this->getWumpus();
 //       $this->getClocktime();
   //     $this->getBar();
     //   $this->getCoordinate();

     //   $this->getTick();


    }
*/
    public function loop()
    {

    }

    private function getCave($requested_nuuid = null)
    {

        //if ($requested_nuuid == null) {$requested_nuuid = $this->entity->id;}

        //$entity = new Entity($this->thing, "wumpus");
        //$this->thing = $entity->thing;

        //return;

        //if ($requested_nuuid == null) {$requested_nuuid = $this->id;}

        $entity = new Entity($this->thing, $this->primary_place);

        $this->thing = $entity->thing;

        //$this->cave_name = $this->thing->choice->load($this->primary_place);

        $this->uuid = $this->thing->uuid;
        $this->nuuid = $this->thing->nuuid;

        // But not this ... use the provided input
//        $this->subject = $this->thing->subject;

//        $this->choices = $this->thing->choice->makeLinks($this->state);
        //$this->choices = $this->thing->choice->makeLinks($this->cave_name);

    }

    private function getNumber()
    {
        $this->number_agent = new Number($this->thing, $this->primary_place);
    }


    private function getClocktime()
    {
        $this->clocktime_agent = new Clocktime($this->thing, "clocktime");
    }

    private function getCoordinate()
    {
        $this->coordinate = new Coordinate($this->thing, "coordinate");

        $this->x = $this->coordinate->coordinates[0]['coordinate'][0];
        $this->y = $this->coordinate->coordinates[0]['coordinate'][1];

    }

    private function getBar()
    {
        $this->thing->bar = new Bar($this->thing, "bar stack");
    }

    private function getTick()
    {
        $this->thing->tick = new Tick($this->thing, "tick");
    }

	public function respond()
    {
		// Thing actions
		$this->thing->flagGreen();

		// Generate SMS response

        $this->whatisthis = array('inside nest'=>'Each time the ' . $this->short_name . ' service is accessed, Stackr creates a uniquely identifable Thing.
				This one is ' . $this->uuid . '.
				This message from the "Caves" ai which was been tasked with mediating web access to this Thing. 
				Manage Things on ' . $this->short_name . ' using the [ NEST MAINTENANCE ] command.  
				If Caves\'s are bothing you, you can either use the [ FORGET ] command
				to stop receiving notifications for the Thing, or you can turn [ CAVES OFF ].
				"Caves" is how ' . $this->short_name . ' manages interactions with your Things by other identities.
				[CAVES OFF] will stop any "Caves" agent responding.  You can say [ NEST MAINTENANCE ] later if you change your mind.',
        'nest maintenance'=>'A Things of yours was displayed again, perhaps by yourself.  This Cave is doing some nest maintenance.',
        'patrolling'=>"A Thing associated with " . "this identity" ." was displayed (or requested by) a device.  That's twice now.  This Wumpus is patrolling.",
        'foraging'=>"This wumpus is on it's last legs.  It has gone foraging for stack information about you to forget.",
        'midden work'=>'One of your records was displayed, perhaps by yourself.  A Wumpus spawned and is doing midden work.',
        'start'=>"Start. Not normally means that you displayed a record, let's see if we get any more Wumpus messages."
);

		// Generate email response.

		$to = $this->thing->from;
		$from = "caves";


        $this->makeChoices();
        $this->makeMessage();
        $this->makeSMS();
        //$this->makeWeb();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = 'This is the "Caves" Agent. It stumbles around Things.' ;

		return;
	}

    public function makeWeb()
    {
        // No web response for now.
        return;
        $test_message = "<b>CAVES " . strtoupper($this->thing->nuuid) . "</b>" . '<br>';
        $test_message .= "<p>";
        $test_message .= '<p><b>Caves State</b>';

        $test_message .= '<br>Last thing heard: "' . $this->subject . '"<br>' . 'The next Caves choices are [ ' . $this->choices['link'] . '].';
        $test_message .= '<br>Lair state: ' . $this->state;
        $test_message .= '<br>left_count is ' . $this->left_count;
        $test_message .= '<br>right count is ' . $this->right_count;

        $test_message .= '<br>' .$this->behaviour[$this->state] . '<br>';


        $test_message .= "<p>";
        $test_message .= '<p><b>Thing Information</b>';
        $test_message .= '<br>subject: ' . $this->subject . '<br>';
        $test_message .= 'created_at: ' . $this->created_at . '<br>';
        $test_message .= 'from: ' . $this->from . '<br>';
        $test_message .= 'to: ' . $this->to . '<br>';
        $test_message .= '<br>' .$this->thing_behaviour[$this->state] . '<br>';


        $test_message .= "<p>";
        $test_message .= '<p><b>Narratives</b>';
        $test_message .= '<br>' .$this->litany[$this->state] . '<br>';
        $test_message .= '<br>' .$this->narrative[$this->state] . '<br>';

       // $test_message .= '<p>Agent "Ant" is responding to your web view of datagram subject "' . $this->subject . '", ';
       // $test_message .= "which was received " . $this->thing->human_time($this->thing->elapsed_runtime()) . " ago.";

        $refreshed_at = max($this->created_at, $this->created_at);
        $test_message .= "<p>";
        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($refreshed_at) );
        $test_message .= "<br>Thing happened about ". $ago . " ago.";

        //$test_message .= '<br>' .$this->whatisthis[$this->state] . '<br>';

        //$this->thing_report['sms'] = $this->message['sms'];
        $this->thing_report['web'] = $test_message;
    }

    public function makeChoices()
    {

        $this->cave_name = $this->thing->choice->load($this->primary_place);

        if ($this->cave_name == false) {
            // Hopefully the first run
            $this->cave_name = strval(random_int(1,20));
        }

        $this->choices = $this->caves[$this->cave_name];

    //$choices_text = implode(" ",$choices['words']);
//        $sms .= "AVAILABLE CHOICES ARE [ NORTH EAST SOUTH WEST ] ";

      //  $sms .= "AVAILABLE CHOICES ARE [" . $choices_text."] ";


        //$this->choices_text = $this->thing->choice->current_node; 
        $this->choices_text = "";
        if ($this->choices != null) {
            $this->choices_text = strtoupper(implode("  " ,$this->choices));
        }


        //$choices = $this->thing->choice->makeLinks();
        //$this->choices = $choices;
        $this->thing_report['choices'] = $this->choices ;
    }

    public function makeMessage()
    {
        if (isset($this->response)) {$m = $this->response;} else {$m = "No response.";};
        $this->message = $m;
        $this->thing_report['message'] = $m;
    }

    public function makeSMS()
    {

        //$this->makeChoices();

        //$this->choices_text = $this->thing->choice->current_node; 
   //     if ($this->choices['words'] != null) {
   //         $this->choices_text = strtoupper(implode(" / " ,$this->choices['words']));
   //     }

        // Generate SMS response
$this->litany = array('inside nest'=>'One of your records was displayed, perhaps by yourself.  A Caves spawned and is waiting in the nest.',
    'nest maintenance'=>'A record of yours was displayed again, perhaps by yourself.  This Wumpus is doing some nest maintenance.',
    'patrolling'=>"A record of yours was displayed.  That's twice now.  This Wumpus is patrolling.",
    'foraging'=>"This wumpus is on it's last legs.  It has gone foraging for stack information about you to forget.",
    'midden work'=>'One of your records was displayed, perhaps by yourself.  A Wumpus spawned and is doing midden work.',
    'start'=>"Start.  Not normally means that you displayed a record, let's see if we get any more Wumpus messages."
);

$this->thing_behaviour = array('inside nest'=>'A Thing was instantiated.',
    'nest maintenance'=>'A Thing was instantiated again.',
    'patrolling'=>"A Thing was instantiated twice.",
    'foraging'=>"A Thing is searching the stack.",
    'midden work'=>'A Thing is doing stack work.',
    'start'=>"Start. A Thing started."
);

// Behaviour
$this->behaviour = array('inside nest'=>'Wumpus spawned and is waiting in the lair. For you.',
    'nest maintenance'=>'Wumpus is doing some work on the lair.',
    'patrolling'=>"That's twice the Wumpus heard you. Now the Wumpus is patrolling.",
    'foraging'=>"The Wumpus has gone to look for a snack.",
    'midden work'=>'A Wumpus spawned and is tidying up the lair.',
    'start'=>"Wumpus egg."
);

// Narrative
$this->narrative = array('inside nest'=>'Everything is dark.',
    'nest maintenance'=>"You are hunting for a Wumpus in it's lair.",
    'patrolling'=>"Now you are a Wumpus Hunter.",
    'foraging'=>"Find the Wumpus.",
    'midden work'=>'You are a Midden Worker. Have fun.',
    'start'=>"Ant egg."
);


$this->prompt_litany = array('inside nest'=>'TEXT WEB / ' . $this->choices_text,
    'nest maintenance'=>'TEXT WEB / ' . $this->choices_text,
    'patrolling'=>"TEXT WEB / " . $this->choices_text,
    'foraging'=>"TEXT WEB / " . $this->choices_text,
    'midden work'=>'TEXT WEB / ' . $this->choices_text,
    'start'=>"TEXT WEB / " . $this->choices_text
);


        $sms = "CAVES ";

        $sms .= " YOU ARE AT ";
//        $sms .= "(" . $this->x . ", " . $this->y . ")";
        $sms .= "(" . $this->cave_name . ")";


        $sms .= " | " . $this->response;
        $sms .= "| ";
//        $sms .= "AVAILABLE CHOICES ARE [" . $this->choices_text."] ";

//        $this->choices_text = $this->thing->choice->current_node; 
//        if ($this->choices['words'] != null) {
//            $this->choices_text = strtoupper(implode(" / " ,$this->choices['words']));
//        }


//        $choices = $this->thing->choice->makeLinks("2");


//        $this->cave = $this->thing->choice->load('cave');
//$this->cave = "2";
        //$this->thing->choice->Choose($this->state);
        //$this->state = $this->thing->choice->load('hive');
        //$choices = $this->thing->choice->makeLinks($this->state);
        //$this->choices = $choices;
        //$this->thing_report['choices'] = $choices ;
//        $this->thing->choice->Create('cave', $this->caves, $this->cave);
//        $this->thing->choice->Choose($this->cave);

//        $choices = $this->thing->choice->makeLinks($this->cave);



//$choices_text = implode(" ",$choices['words']);
//        $sms .= "AVAILABLE CHOICES ARE [ NORTH EAST SOUTH WEST ] ";

        $sms .= "AVAILABLE CHOICES ARE [ " . $this->choices_text." ] ";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function readSubject()
    {
		$this->response = null;

        if ($this->state == null) {
            $this->getCave();
        }

        switch ($this->state) {
            case "start":
                $this->start();
                $this->response .= "Caves started. Welcome player. ";
                break;

            case "spawn":
                $this->spawn();
                //echo "spawn";
                //$this->spawn();
                //$this->response .= "Spawned Wumpus.";
                $this->response .= "Something spawned. ";

                break;

            default:
                $this->thing->log($this->agent_prefix . 'invalid state provided "' . $this->state .'".');
                $this->response .= "You are in a dark cave. ";

				// this case really shouldn't happen.
				// but it does when a web button lands us here.
        }

        $input = strtolower($this->subject);
        // Accept wumpus commands
        $this->keywords = array("news", "back", "left", "right","caves","cave", "start", "meep");

        $pieces = explode(" ", strtolower($input));

        foreach($pieces as $key=>$piece) {
            $ngram_list[] = $piece;
        }

        foreach($pieces as $key=>$piece) {
            if (isset($last_piece)) { 
                $ngram_list[] = $last_piece . " " . $piece;
            }
            $last_piece = $piece;
        }

        foreach($pieces as $key=>$piece) {
            if ( (isset($last_piece)) and (isset($last_last_piece))) { 
                $ngram_list[] = $last_last_piece . " " . $last_piece . " " . $piece;
            }
            $last_last_piece = $last_piece;
            $last_piece = $piece;
        }

        $this->getCoordinate();

        foreach ($ngram_list as $key=>$piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {

                        case 'news':
                            $this->response .= "May 19th might be Wumpus hunt at Queen Elizabeth Park. ";
                            break;


                        case 'back':
                            $this->response .= "You moved north. ";
                            $this->y += 1; // left
                            $coordinate = new Coordinate($this->thing, "(". $this->x . "," . $this->y . ")");
           //$this->thing->choice->Create($this->primary_place, $this->node_list, "patrolling");
                   //         $this->thing->choice->Choose("patrolling");

                            break;

                        case 'left':
                            $this->response .= "You moved east. ";
                            $this->x += 1; // left
                            $coordinate = new Coordinate($this->thing, "(". $this->x . "," . $this->y . ")");
                    //        $this->thing->choice->Choose("patrolling");
                            break;
                        case 'right':
                            $this->response .= "You moved south. ";
                            $this->y -= 1; // left
                            $coordinate = new Coordinate($this->thing, "(". $this->x . "," . $this->y . ")");
                  //          $this->thing->choice->Choose("patrolling");
                            break;

                        case 'meep':
                            $this->response .= "Merp. ";
                            break;

                        case 'start':
                            $this->start();
              //              $this->thing->choice->Choose($piece);

                            $this->response .= "Heard " . $this->state .". ";
                            break;

                        case 'spawn':
                            $this->spawn();
                            $this->response .= "Spawn. ";
                            break;

                    }
                }
            }
        }

//        $this->makeChoices();
	//	$this->thing->choice->Create($this->primary_place, $this->node_list, $this->state);

		return false;
	}

	function spawn()
    {
        $this->getCave();
        $coordinate = new Coordinate($this->thing, "(0,0)");

        $pheromone['stack'] = 4;

        $this->cave_name = strval(random_int(1,20));

        $this->thing->choice->Create("cave", $this->caves, $this->cave_name);
		$this->thing->flagGreen();
	}

    function start()
    {
        $this->getCave();
        //$this->thing->choice->Create($this->primary_place, $this->node_list, "start");
        $this->response .= "Welcome player. Wumpus has started.";
        $this->thing->flagGreen();
    }

}
