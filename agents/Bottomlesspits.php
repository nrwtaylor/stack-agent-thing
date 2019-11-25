<?php
namespace Nrwtaylor\StackAgentThing;
use RecursiveIteratorIterator;
use RecursiveArrayIterator;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Bottomlesspits extends Agent
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

$this->bottomless_pits = array("1","2");

//$this->bottomless_pit_name = "Little Mountain";

$this->node_list = $this->bottomless_pits;
//var_dump($this->bottomless_pits);


        $info = 'The "Bottomless Pits" agent creates and manages some bottomless pits.';
		$info .= 'You can explore Bottomless Pits by falling into them looking for a WUMPUS.';

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
        $this->getBottomlesspit();
        $this->getClocktime();
        $this->getBar();
        $this->getCoordinate();

        $this->getTick();
    }

    public function set()
    {
        $this->thing->json->writeVariable( array($this->agent_name, "name"), $this->bottomless_pit_name );



        $this->number_agent = new Number($this->thing,"number");
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
        $this->bottomless_pit_name = $this->thing->json->readVariable( array($this->agent_name, "name") );

        if( ($this->bottomless_pit_name == false)) {
            $this->bottomless_pit();
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

        $this->whatisthis = array('bottomless pit'=>'A pit. With no bottom. You are still falling.');

		// Generate email response.

		$to = $this->thing->from;
		$from = "bottomless pits";


        $this->makeChoices();
        $this->makeMessage();
        $this->makeSMS();
        //$this->makeWeb();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = 'This is the "Bottomless Pits" Agent. It stumbles around Things.' ;

		return;
	}

    public function makeWeb()
    {
        // No web response for now.
        return;
        $test_message = "<b>BOTTOMLESS PITS " . strtoupper($this->thing->nuuid) . "</b>" . '<br>';
        $test_message .= "<p>";
        $test_message .= '<p><b>Bottomless Pits State</b>';


        $test_message .= "<p>";

        $refreshed_at = max($this->created_at, $this->created_at);
        $test_message .= "<p>";
        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($refreshed_at) );
        $test_message .= "<br>Thing happened about ". $ago . " ago.";

        $this->thing_report['web'] = $test_message;
    }
/*
    public function makeChoices()
    {
        $this->choices_text = "Wumpus";
        $this->bottomless_pit_name = $this->thing->choice->load($this->primary_place);

        if ($this->bottomless_pit_name == false) {
            // Hopefully the first run
            $this->bottomless_pit_name = strval(random_int(1,20));
        }
//var_dump($this->bottomless_pit_name);
//        $this->choices = $this->bottomless_pits[$this->bottomless_pit_name];
//var_dump($this->choices);
    //$choices_text = implode(" ",$choices['words']);
//        $sms .= "AVAILABLE CHOICES ARE [ NORTH EAST SOUTH WEST ] ";

      //  $sms .= "AVAILABLE CHOICES ARE [" . $choices_text."] ";


        //$this->choices_text = $this->thing->choice->current_node; 
        $this->choices_text = "";
        if ($this->choices != null) {
//var_dump($this->choices);
            $this->choices_text = strtoupper(implode("  " ,$this->choices));
        }


        //$choices = $this->thing->choice->makeLinks();
        //$this->choices = $choices;
        $this->thing_report['choices'] = $this->choices ;
    }

*/
public function makeChoices() {
$this->state = "END";
        $choices = $this->thing->choice->makeLinks($this->state);
$this->choices_text = "End";
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

        $this->makeChoices();

        //$this->choices_text = $this->thing->choice->current_node; 
   //     if ($this->choices['words'] != null) {
   //         $this->choices_text = strtoupper(implode(" / " ,$this->choices['words']));
   //     }

        // Generate SMS response
$this->litany = array('bottomless pit'=>"Yeah. Bottomless. This isn't going to end."
);

$this->thing_behaviour = array('bottomless pit'=>'Aaaarrrrggggghhh.'
);

// Behaviour
$this->behaviour = array('bottomless pit'=>'Arms crossed. Falling.  Faster and faster.'
);

// Narrative
$this->narrative = array('bottomless pit'=>'It was dark. You fell into a pit.'
);


$this->prompt_litany = array('bottomless pit'=>'TEXT WUMPUS',
);


        $sms = "BOTTOMLESS PIT ";

        $sms .= " YOU ARE AT ";
//        $sms .= "(" . $this->x . ", " . $this->y . ")";
        $sms .= "(" . $this->bottomless_pit_name . ")";


        $sms .= " | " . $this->response;
        $sms .= "| ";
        $sms .= "AVAILABLE CHOICES ARE [" . $this->choices_text."] ";

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
            case "bottonless pit":
                $this->bottomless_pit();
                $this->response .= "You are in a bottomless pit. Play another round.";
                break;

            default:
                $this->thing->log($this->agent_prefix . 'invalid state provided "' . $this->state .'".');
                $this->response .= "It is dark.";

				// this case really shouldn't happen.
				// but it does when a web button lands us here.
        }

        $input = strtolower($this->subject);
        // Accept wumpus commands
        $this->keywords = array("news", "run wumpus", "bottomless pits","bottomless pit", "pit", "falling");

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
                            $this->response .= "June 22nd. Watch out for bottomless pits in Queen Elizabeth Park. ";
                            break;

                        case 'start':
                            $this->start();
              //              $this->thing->choice->Choose($piece);

                            $this->response .= "Heard " . $this->state .". ";
                            break;


                    }
                }
            }
        }

//        $this->makeChoices();
	//	$this->thing->choice->Create($this->primary_place, $this->node_list, $this->state);

		return false;
	}

    function getBottomlesspit() {

        $this->bottomless_pit_name = strval(random_int(1,20));

    }

	function bottomless_pit()
    {
        $this->getBottomlesspit();
//        $coordinate = new Coordinate($this->thing, "(0,0)");

        //$pheromone['stack'] = 4;

//        $this->bottomless_pit_name = strval(random_int(1,20));
// And then check if it is one of the adjacent pits.
		$this->thing->flagRed();
	}


}
