<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Wumpus extends Agent
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

        $this->primary_place = "lair";

        $this->created_at = $this->thing->thing->created_at;

		$this->sqlresponse = null;


        $this->node_list = array("start"=>array("inside nest"=>array("nest maintenance"=>array("patrolling"=>"foraging","foraging")),"midden work"=>"foraging"));

        $this->caves = array("1"=>array("2", "3", "4"),
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


        $info = 'The "Wumpus" agent provides an text driven interface to manage a 3-D coordinate on '. $this->short_name;
		$info .= 'from the web.  The Management suggests you explore the NEST MAINTENANCE button';

    }

    public function run()
    {
        $this->getWumpus();
        $this->getClocktime();
        $this->getBar();
        $this->getCoordinate();
        $this->getState();

        $this->getTick();

        // Err ... making sure the state is saved.
//        $this->thing->choice->Choose($this->state);
        $this->state = $this->thing->choice->load('lair');
        $this->thing->log('state is "' . $this->state . '".');
    }

    public function set()
    {
        $this->thing->json->writeVariable( array("wumpus", "left_count"), $this->left_count );
        $this->thing->json->writeVariable( array("wumpus", "right_count"), $this->right_count );

        // Which cave is the Wumpus in?  And is it a number or a name?
        $this->thing->json->writeVariable( array("wumpus", "cave"), $this->cave );

        $this->thing->choice->Choose($this->state);

        $this->state = $this->thing->choice->load($this->primary_place);
    }




    public function get($crow_code = null)
    {


        $this->current_time = $this->thing->json->time();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $this->time_string = $this->thing->json->readVariable( array("wumpus", "refreshed_at") );


        if ($crow_code == null) {$crow_code = $this->uuid;}

        if ($this->time_string == false) {
            $this->thing->json->setField("variables");
            $this->time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("wumpus", "refreshed_at"), $this->time_string );
        }

        $this->refreshed_at = strtotime($this->time_string);


        $this->thing->json->setField("variables");
        $this->left_count = strtolower($this->thing->json->readVariable( array("wumpus", "left_count") ));
        $this->right_count = $this->thing->json->readVariable( array("wumpus", "right_count") );
        $this->cave = $this->thing->json->readVariable( array("wumpus", "cave") );

        if( ($this->cave == false)) {$this->cave = random_int(1,20);}


        if( ($this->left_count == false) or ($this->left_count = "")) {$this->left_count = 0;$this->right_count = 0;}
        if( ($this->right_count == false) or ($this->right_count = "")) {$this->left_count = 0;$this->right_count = 0;}


        // For the Crow
//        $this->created_at = $this->thing->thing->created_at;

        $this->state = $this->thing->choice->load($this->primary_place);

        if ($this->state == false) {$this->state = "foraging";}

        $this->entity = new Entity ($this->thing, "wumpus");

        return array($this->left_count, $this->right_count);

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

    private function getWumpus($requested_nuuid = null)
    {

        //if ($requested_nuuid == null) {$requested_nuuid = $this->entity->id;}

        //$entity = new Entity($this->thing, "wumpus");
        //$this->thing = $entity->thing;

        //return;

        //if ($requested_nuuid == null) {$requested_nuuid = $this->id;}

        $entity = new Entity($this->thing, "wumpus");

        $this->thing = $entity->thing;

        $this->state = $this->thing->choice->load('lair');

        $this->uuid = $this->thing->uuid;
        $this->nuuid = $this->thing->nuuid;

        if ($this->x == 0) {$this->x = random_int(1,20);}

        $this->getCave();

        // But not this ... use the provided input
//        $this->subject = $this->thing->subject;

        $this->choices = $this->thing->choice->makeLinks($this->state);
    }

    private function getCaves()
    {
        if (isset($this->cave_names)) {return;}

        // Makes a one character dictionary

        $file = $this->resource_path . 'wumpus/wumpus.txt';
        $contents = file_get_contents($file);


        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            $items = explode(",",$line);
            $this->cave_names[$items[0]] = $items[1];

            # do something with $line
            $line = strtok( $separator );
        }



/*
        $this->cave_names = array("1"=>"CAMS","2"=>"Field Day Site","3"=>"Fountain","4"=>"Bloedel Conservatory","5"=>"Tennis Courts","6"=>"Quarry Gardens","7"=>"Off-leash area",
            "8"=>"Quarry Bridge", "9"=>"Pond", "10"=>"Quarry overlook",
            "11"=>"Midlothian Avenue",
            "12"=>"Queen Elizabeth Pitch & Putt",
            "13"=>"Disc Golf",
            "14"=>"Rose Garden",
            "15"=>"Celebration Pavilion",
            "16"=>"Nat Bailey Stadium",
            "17"=>"West Parking",
            "18"=>"Centre Parking",
            "19"=>"South Parking",
            "20"=>"Lookout");
*/

    }

    private function getCave($number = null)
    {
        $this->getCaves();
        $cave_number = "X";
        if ($number == null) {$cave_number = $this->x;}
        if ($number == "") {$cave_number = $this->x;}
        $cave_name = "A dark room";
        if (isset($this->cave_names[strval($cave_number)])) {$cave_name = $this->cave_names[strval($cave_number)];}
        $this->cave_name = $cave_name;
        $this->cave_number = $cave_number;
    }

    private function getState()
    {

        $this->state = $this->thing->choice->load($this->primary_place);
        $this->thing->choice->Create($this->primary_place, $this->node_list, $this->state);
        $this->thing->choice->Choose($this->state);

        $choices = $this->thing->choice->makeLinks($this->state);

     //   $this->state = "AWAKE";
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
				This message from the "Wumpus" ai which was been tasked with mediating web access to this Thing. 
				Manage Things on ' . $this->short_name . ' using the [ NEST MAINTENANCE ] command.  
				If Wumpus\'s are bothing you, you can either use the [ FORGET ] command
				to stop receiving notifications for the Thing, or you can turn [ WUMPUS OFF ].
				"Wumpus" is how ' . $this->short_name . ' manages interactions with your Things by other identities.
				[WUMPUS OFF] will stop any "Wumpus" agent responding.  You can say [ NEST MAINTENANCE ] later if you change your mind.',
        'nest maintenance'=>'A Things of yours was displayed again, perhaps by yourself.  This Wumpus is doing some nest maintenance.',
        'patrolling'=>"A Thing associated with " . "this identity" ." was displayed (or requested by) a device.  That's twice now.  This Wumpus is patrolling.",
        'foraging'=>"This wumpus is on it's last legs.  It has gone foraging for stack information about you to forget.",
        'midden work'=>'One of your records was displayed, perhaps by yourself.  A Wumpus spawned and is doing midden work.',
        'start'=>"Start. Not normally means that you displayed a record, let's see if we get any more Wumpus messages."
);

		// Generate email response.

		$to = $this->thing->from;
		$from = "wumpus";


        $this->makeChoices();
        $this->makeMessage();
        $this->makeSMS();
        //$this->makeWeb();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = 'This is the "Wumpus" Agent. It stumbles around Things.' ;
	}

    public function makeWeb()
    {
        return;
        // No web response for now.
        $test_message = "<b>WUMPUS " . strtoupper($this->thing->nuuid) . "</b>" . '<br>';
        $test_message .= "<p>";
        $test_message .= '<p><b>Wumpus State</b>';

        $test_message .= '<br>Last thing heard: "' . $this->subject . '"<br>' . 'The next Wumpus choices are [ ' . $this->choices['link'] . '].';
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


        $this->state = $this->thing->choice->load($this->primary_place);
        $choices = $this->thing->choice->makeLinks($this->state);
        $this->choices = $choices;


        //$this->choices_text = $this->thing->choice->current_node; 
        $this->choices_text = "";
        if ($this->choices['words'] != null) {
            $this->choices_text = strtoupper(implode(" / " ,$this->choices['words']));
        }




        $choices = $this->thing->choice->makeLinks();
        $this->choices = $choices;
      $this->thing_report['choices'] = $choices ;
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
$this->litany = array('inside nest'=>'One of your records was displayed, perhaps by yourself.  A Wumpus spawned and is waiting in the nest.',
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


        $sms = "WUMPUS " . strtoupper($this->nuuid) .  "";

//$this->state = "hungry";
	    if ((isset($this->state)) and ($this->state != false)) {
             $sms .= " IS " . strtoupper($this->state);
        }

if ( in_array($this->x, range(1,20)) ) {
        $sms .= " IS AT ";
//        $sms .= "(" . $this->x . ", " . $this->y . ")";
        $sms .= "(" . $this->x  . ") ";
        $sms .= "" . strtoupper($this->cave_names[$this->x]) . "";
}

if ( $this->x == 0  ) {
        $sms .= " IS OUT OF BOUNDS. ";
}

//        $sms .= "" . strtoupper($this->cave_names[$this->x]) . "";


        $sms .= " | " . $this->response;
        $sms .= "| ";

        $this->cave_list_text = implode($this->caves[strval($this->x)]," ");

        $sms .= "AVAILABLE CHOICES ARE [ " . $this->cave_list_text . " " . $this->choices_text." ] ";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function readSubject()
    {
		$this->response = null;

        if ($this->state == null) {
            $this->getWumpus();
        }

        switch ($this->state) {
            case "start":
                $this->start();
                $this->response .= "Wumpus started. Welcome player. ";
                break;

            case "spawn":
                $this->spawn();
                //echo "spawn";
                //$this->spawn();
                //$this->response .= "Spawned Wumpus.";
                break;
            case "foraging":
                $this->thing->choice->Choose("foraging");
                $this->response .= "Foraging.";
                break;
            case "inside nest":
                $this->thing->choice->Choose("in nest");
                $this->response .= "Wumpus is inside the " . $this->primary_place .". ";
                break;
            case "nest maintenance":
                $this->response .= "Wumpus is doing Nest Maintenance. ";
                $this->thing->choice->Choose("nest maintenance");
                break;
            case "patrolling":
                $this->response .= "Wumpus is Patrolling. ";
                $this->thing->choice->Choose("patrolling");
                break;
            case "midden work":
                $this->response .= "Wumpus is taking a look at the midden. ";
                $this->middenwork();

                // Need to figure out how to set flag to red given that respond will then reflag it as green.
                // Can green reflag red?  Think about reset conditions.

                break;

            default:
                $this->thing->log($this->agent_prefix . 'invalid state provided "' . $this->state .'".');
                $this->response .= "You are in a dark cave. ";

				// this case really shouldn't happen.
				// but it does when a web button lands us here.
        }

        $input = strtolower($this->subject);

        $r = "";
        $this->requested_cave_number = (int)$this->cave;
        $this->number_agent = new Number($this->thing, $input);
        $this->number_agent->extractNumber($input);
        if ($this->number_agent->number != false) {$this->requested_cave_number = $this->number_agent->number;}

        $this->getCoordinate();
        if ($this->x == 0) {$this->getWumpus();}

        // Check if this is one of the available caves.
        $available_cave_names = $this->caves[strval($this->x)];
        $match = false;
        foreach ($available_cave_names as $key=>$cave_name) {
            if ($cave_name == strval($this->requested_cave_number)) {
                $this->cave_name = $cave_name;
                $this->x = $this->requested_cave_number;
                            $coordinate = new Coordinate($this->thing, "(". $this->x . "," . $this->y . ")");

                $match= true;
                break;

            }

        }

        if ($this->requested_cave_number == strval($this->x)) {$r = "Took a look around the cave.";}
 
        if (($match != true) and ($this->number_agent->number != false)) {$r = "Choose one of the three numbers. ";}

        $this->response .= $r;

        // Accept wumpus commands
        $this->keywords = array("teleport","caves","look","arrow", "news", "forward", "north", "east", "south", "west", "up", "down", "left", "right","wumpus","meep","thing","lair","foraging","nest maintenance", "patrolling", "midden work", "nest maintenance", "start", "meep");

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

                        case 'arrow':
                            $this->arrow();
                            //$this->response .= "Fired a wonky arrow. ";
                            break;


                        case 'look':
                            $this->getCave($this->x);
                            $this->response .= "You see " . $this->cave_name . ". ";
                            break;

                        case 'caves':
                            $this->caves();
                            break;

                        case 'north':
                            $this->response .= "North? ";
//                            $this->y += 1; // left
                     //       $coordinate = new Coordinate($this->thing, "(". $this->x . "," . $this->y . ")");
           //$this->thing->choice->Create($this->primary_place, $this->node_list, "patrolling");
                   //         $this->thing->choice->Choose("patrolling");

                            break;

                        case 'east':
                            $this->response .= "East? ";
                   //         $this->x += 1; // left
                 //           $coordinate = new Coordinate($this->thing, "(". $this->x . "," . $this->y . ")");
                    //        $this->thing->choice->Choose("patrolling");
                            break;
                        case 'south':
                            $this->response .= "South? ";
                 //           $this->y -= 1; // left
                //            $coordinate = new Coordinate($this->thing, "(". $this->x . "," . $this->y . ")");
                  //          $this->thing->choice->Choose("patrolling");
                            break;
                        case 'west':
                            $this->response .= "West? ";
                            $this->x -= 1; // left
               //             $coordinate = new Coordinate($this->thing, "(". $this->x . "," . $this->y . ")");
                //            $this->thing->choice->Choose("patrolling");
                            break;

                        case 'left':
//                            $this->left_count += 1;
//$available_cave_names[0];
                            $this->response .= "You turned left. ";

                            break;

                        case 'right':
                            $this->response .= "You turned right. ";
                            break;

                        case 'forward':
                            $this->left_count += 1;
                            $this->right_count += 1;
                            $this->response .= "You bumped into the wall. ";
                            break;

                        case 'lair':
                            $this->response .= "Lair. ";
                            break;

                        case 'meep':
                            $this->response .= "Merp. ";
                            break;

                        case 'start':
                            $this->start();
                            $this->thing->choice->Choose($piece);

                            $this->response .= "Heard " . $this->state .". ";
                            break;

                        case 'teleport';
                        case 'spawn':
                            $this->spawn();
                            $this->response .= "Spawn. ";
                            break;


                        case 'inside nest':
                                $this->thing->choice->Choose($piece);
                            $this->state = $this->thing->choice->current_node;

                            $this->response .= "Heard inside nest.";
                            break;

                        case 'foraging':
                            $this->thing->choice->Choose($piece);
                            $this->state = $this->thing->choice->current_node;

                            $this->response .= "Now foraging. ";
                            break;

                        case 'nest maintenance':
                            $this->thing->choice->Choose($piece);
                            $this->state = $this->thing->choice->current_node;
                            $this->response .= "Heard nest maintenance. ";
                            break;

                        case 'patrolling':

                            $this->thing->choice->Choose($piece);
                            $this->state = $this->thing->choice->current_node;

                            $this->response .= "Now " . $piece .". ";
                            break;

                        case 'midden work':
                            $this->middenwork();
                            $this->thing->choice->Choose($piece);
                            $this->state = $this->thing->choice->current_node;


                            $this->response .= "Heard midden work. Urgh. ";
                            break;



                    }
                }
            }
        }

        //$this->makeChoices();
//var_dump($this->state);
		//$this->thing->choice->Create($this->primary_place, $this->node_list, $this->state);

		return false;
	}

    function middenwork()
    {

        $middenwork = "on";
        if ($middenwork != "on") {$this->response .= "No work done. ";return;}

  //         $this->thing->choice->Create($this->primary_place, $this->node_list, "midden work");

        $this->response .= "Wumpus is fixing up the lair. ";
    }

    function arrow()
    {

        $current_cave = $this->x;
        $arrow_cave_previous = $current_cave;
        $arrow_cave = $current_cave;
        $this->response .= "Arrow fired through caves";

        foreach(range(1,5) as $key=>$value) {
            $available_caves = $this->caves[$arrow_cave];
            $arrow_cave_previous = $arrow_cave;

            while ($arrow_cave_previous == $arrow_cave) {
            $arrow_cave = $available_caves[array_rand($available_caves)];
            }

            $this->response .= " " . $arrow_cave;
        }


        $this->response .= ". Nothing happened. ";
    }

    function caves()
    {
        $this->response = "";
$this->getCave();

        $current_cave = $this->x;

        trim($this->response);

        $this->caves[$current_cave];
foreach($this->caves[$current_cave] as $key=>$cave) {
$this->response .= " <" . $cave ."> " .strtoupper($this->cave_names[$cave]);
}
        $this->response .= " ";
    }




    function foraging()
    {

        $this->thing->choice->Create($this->primary_place, $this->node_list, "foraging");

        $this->response .= "Wumpus is foraging. ";
    }

    function patrolling()
    {
        $this->thing->choice->Create($this->primary_place, $this->node_list, "patrolling");
        $this->response .= "Wumpus is patrolling. ";
    }


	function spawn()
    {
        $this->getWumpus();
        //$coordinate = new Coordinate($this->thing, "(0,0)");

        $pheromone['stack'] = 4;

        $this->cave = strval(random_int(1,20));
        $this->x = $this->cave;
        $coordinate = new Coordinate($this->thing, "(".$this->cave.",0)");


        $this->thing->choice->Create($this->primary_place, $this->node_list, $this->state);
		$this->thing->flagGreen();
	}

    function start()
    {
        $this->getWumpus();
        //$this->thing->choice->Create($this->primary_place, $this->node_list, "start");
        $this->response .= "Welcome player. Wumpus has started.";
        $this->thing->flagGreen();
    }

}
