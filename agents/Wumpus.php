<?php
namespace Nrwtaylor\StackAgentThing;

use setasign\Fpdi;

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
/*
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
*/

        $this->caves = array("1"=>array("8", "20", "12"),
             "2"=>array("5", "9", "13"),
             "3"=>array("9", "11", "15"),
             "4"=>array("5", "7", "14"),
             "5"=>array("2", "4", "10"),
             "6"=>array("14", "16", "19"),
            "7"=>array("4", "10", "14"),
            "8"=>array("1", "12", "17"),
            "9"=>array("2", "3", "11"),
            "10"=>array("5", "7", "20"),
            "11"=>array("3", "9", "20"),
            "12"=>array("1", "8", "17"),
            "13"=>array("2", "15", "16"),
            "14"=>array("4", "6", "7"),
            "15"=>array("3", "13", "18"),
            "16"=>array("6", "13", "18"),
            "17"=>array("8", "12", "19"),
            "18"=>array("15", "16", "19"),
            "19"=>array("6", "17", "18"),
            "20"=>array("1", "10", "11"));


        $info = 'The "Wumpus" agent provides an text driven interface to manage a 3-D coordinate on '. $this->short_name;
		$info .= 'from the web.  The Management suggests you explore the NEST MAINTENANCE button';

    }

    public function run()
    {
        $this->getWumpus();
//        $this->getClocktime();
//        $this->getBar();
        //$this->getCoordinate();
        $this->getState();

//        $this->getTick();

        // Err ... making sure the state is saved.
//        $this->thing->choice->Choose($this->state);
//        $this->state = $this->thing->choice->load('lair');
        $this->state = $this->entity_agent->choice->load('lair');

        $this->thing->log('state is "' . $this->state . '".');
    }

    public function set()
    {

//$this->x = "9";

//        $this->thing->json->writeVariable( array("wumpus", "left_count"), $this->left_count );
//        $this->thing->json->writeVariable( array("wumpus", "right_count"), $this->right_count );

        // Which cave is the Wumpus in?  And is it a number or a name?
//        $this->thing->json->writeVariable( array("wumpus", "cave"), strval($this->x) );


//        $this->thing->choice->Choose($this->state);

//        $this->state = $this->thing->choice->load($this->primary_place);

        $this->entity_agent->json->writeVariable( array("wumpus", "left_count"), $this->left_count );
        $this->entity_agent->json->writeVariable( array("wumpus", "right_count"), $this->right_count );

        // Which cave is the Wumpus in?  And is it a number or a name?
        $this->entity_agent->json->writeVariable( array("wumpus", "cave"), strval($this->x) );


        $this->entity_agent->choice->Choose($this->state);

        $this->state = $this->entity_agent->choice->load($this->primary_place);



    }




    public function get($crow_code = null)
    {
        $this->getWumpus();

//        $this->current_time = $this->thing->json->time();
        $this->current_time = $this->entity_agent->json->time();


        // Borrow this from iching
//        $this->thing->json->setField("variables");
//        $this->time_string = $this->thing->json->readVariable( array("wumpus", "refreshed_at") );

        $this->entity_agent->json->setField("variables");
        $this->time_string = $this->entity_agent->json->readVariable( array("wumpus", "refreshed_at") );


        if ($crow_code == null) {$crow_code = $this->uuid;}

        if ($this->time_string == false) {
//            $this->thing->json->setField("variables");
//            $this->time_string = $this->thing->json->time();
//            $this->thing->json->writeVariable( array("wumpus", "refreshed_at"), $this->time_string );
            $this->entity_agent->json->setField("variables");
            $this->time_string = $this->entity_agent->json->time();
            $this->entity_agent->json->writeVariable( array("wumpus", "refreshed_at"), $this->time_string );


        }

        $this->refreshed_at = strtotime($this->time_string);


//        $this->thing->json->setField("variables");
//        $this->left_count = strtolower($this->thing->json->readVariable( array("wumpus", "left_count") ));
//        $this->right_count = $this->thing->json->readVariable( array("wumpus", "right_count") );
//        $this->x = $this->thing->json->readVariable( array("wumpus", "cave") );

        $this->entity_agent->json->setField("variables");
        $this->left_count = strtolower($this->entity_agent->json->readVariable( array("wumpus", "left_count") ));
        $this->right_count = $this->entity_agent->json->readVariable( array("wumpus", "right_count") );
        $this->x = $this->entity_agent->json->readVariable( array("wumpus", "cave") );



//        if( ($this->cave == false)) {$this->cave = random_int(1,20);}

        if( ($this->left_count == false) or ($this->left_count = "")) {$this->left_count = 0;$this->right_count = 0;}
        if( ($this->right_count == false) or ($this->right_count = "")) {$this->left_count = 0;$this->right_count = 0;}


//echo "Got variable x " . $this->x . "\n";



        // For the Crow
//        $this->created_at = $this->thing->thing->created_at;

//        $this->state = $this->thing->choice->load($this->primary_place);
        $this->state = $this->entity_agent->choice->load($this->primary_place);


        if ($this->state == false) {$this->state = "foraging";}

//        $this->entity = new Entity ($this->thing, "wumpus");

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
        $this->entity_agent = $entity->thing;

//        $this->thing = $entity->thing;



//        $this->state = $this->thing->choice->load('lair');
        $this->state = $this->entity_agent->choice->load('lair');



//        $this->uuid = $this->thing->uuid;
        $this->uuid = $this->entity_agent->uuid;

//        $this->nuuid = $this->thing->nuuid;
        $this->nuuid = $this->entity_agent->nuuid;


//        if ($this->x == 0) {$this->x = random_int(1,20);}

        $this->getCave();

        // But not this ... use the provided input
//        $this->subject = $this->thing->subject;

        //$this->choices = $this->thing->choice->makeLinks($this->state);
        $this->choices = $this->entity_agent->choice->makeLinks($this->state);
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

    }

    private function getNews()
    {
        //if (isset($this->cave_names)) {return;}

        // Makes a one character dictionary

        $file = $this->resource_path . 'wumpus/news.txt';
        $contents = file_get_contents($file);


        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            $items = explode(",",$line);
            $this->news = $items[2];
            break;

            # do something with $line
            $line = strtok( $separator );
        }

    }


    private function getCave($cave_number = null)
    {

        $this->getCaves();

        $cave_number = "X";


        if ($cave_number == null) {$cave_number = $this->x;}


        $cave_name = "A dark room";
        if (isset($this->cave_names[strval($cave_number)])) {$cave_name = $this->cave_names[strval($cave_number)];}
        $this->cave_name = $cave_name;
    }

    private function getState()
    {

//        $this->state = $this->thing->choice->load($this->primary_place);
//        $this->thing->choice->Create($this->primary_place, $this->node_list, $this->state);
//        $this->thing->choice->Choose($this->state);

//        $choices = $this->thing->choice->makeLinks($this->state);

        $this->state = $this->entity_agent->choice->load($this->primary_place);
        $this->entity_agent->choice->Create($this->primary_place, $this->node_list, $this->state);
        $this->entity_agent->choice->Choose($this->state);

        $choices = $this->entity_agent->choice->makeLinks($this->state);


     //   $this->state = "AWAKE";
    }

    private function getClocktime()
    {
        $this->clocktime_agent = new Clocktime($this->thing, "clocktime");
    }
/*
    private function getCoordinate()
    {
        $this->coordinate = new Coordinate($this->thing, "coordinate");

        $this->x = $this->coordinate->coordinates[0]['coordinate'][0];
        $this->y = $this->coordinate->coordinates[0]['coordinate'][1];

    }
*/
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


        //$this->makeChoices();
$this->choices = false;
        $this->makeMessage();
        $this->makeSMS();

        $this->makeWeb();

        //if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        //}

        $this->makePDF();

        $this->thing_report['help'] = 'This is the "Wumpus" Agent. It stumbles around Things.' ;
	}

    public function makeWeb()
    {
//        return;
        // No web response for now.
//        $test_message = "<b>WUMPUS " . strtoupper($this->thing->nuuid) . "" . ' NOW ';
        $test_message = "<b>WUMPUS " . strtoupper($this->entity_agent->nuuid) . "" . ' NOW ';


        $test_message .= "AT ";
        // . strtoupper($this->x) . "" . 
        //$test_message .= '<br>';

        if (isset($this->caves[strval($this->x)])) {
            $this->choices_text = "";
            $this->cave_list_text = trim(implode($this->caves[strval($this->x)]," ")) . "";
        }



        $test_message .= strtoupper($this->cave_names[strval($this->x)]);

//        $test_message .= "<b>WITH ROUTES TO " . strtoupper($this->cave_list_text) . "</b>" . '<br>';

        $test_message .= "</b><p>";

        //$test_message .= "".  nl2br($this->sms_message);
        $test_message .= "YOUR CHOICES ARE";
        $test_message .= "<p>";


        $test_message .= "PDF ";

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/wumpus.pdf';
        $test_message .= '<a href="' . $link . '">wumpus.pdf</a>';
        //$web .= " | ";


        $test_message .="<br>";
        $test_message .= "<p>";



        $this->response = "";
//$this->getCave();

        $current_cave = $this->x;

        trim($this->response);
$test_message .= "<p>";
        //$this->caves[$current_cave];
foreach($this->caves[$current_cave] as $key=>$cave) {
$test_message .= "Place " . $cave ." is the  " .(strtoupper($this->cave_names[$cave])) ."<br>";
}
     //   $this->response .= "";



        if ($this->state != false) {

        $test_message .= '<p><b>Wumpus State</b>';

        $test_message .= '<br>Last thing heard: "' . $this->subject . '"<br>' . 'The next Wumpus choices are [ ' . $this->choices['link'] . '].';
        $test_message .= '<br>Lair state: ' . $this->state;

        //$test_message .= '<br>left_count is ' . $this->left_count;
        //$test_message .= '<br>right count is ' . $this->right_count;

        $test_message .= '<br>' .$this->behaviour[$this->state] . '<br>';
        $test_message .= '<br>' .$this->thing_behaviour[$this->state] . '<br>';
        $test_message .= '<br>' .$this->litany[$this->state] . '<br>';
        $test_message .= '<br>' .$this->narrative[$this->state] . '<br>';

}

//        $test_message .= "<p>";
//        $test_message .= '<p><b>Thing Information</b>';
//        $test_message .= '<br>subject: ' . $this->subject . '<br>';
//        $test_message .= 'created_at: ' . $this->created_at . '<br>';
//        $test_message .= 'from: ' . $this->from . '<br>';
//        $test_message .= 'to: ' . $this->to . '<br>';


        $refreshed_at = max($this->created_at, $this->created_at);
        $test_message .= "<p>";
//        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($refreshed_at) );
        $ago = $this->thing->human_time ( strtotime($this->entity_agent->time()) - strtotime($refreshed_at) );

        $test_message .= "<br>Thing happened about ". $ago . " ago.";

        //$test_message .= '<br>' .$this->whatisthis[$this->state] . '<br>';

        //$this->thing_report['sms'] = $this->message['sms'];
        $this->thing_report['web'] = $test_message;
    }

    public function makeChoices()
    {


//        $this->state = $this->thing->choice->load($this->primary_place);
//        $choices = $this->thing->choice->makeLinks($this->state);

        $this->state = $this->entity_agent->choice->load($this->primary_place);
        $choices = $this->entity_agent->choice->makeLinks($this->state);


        $this->choices = $choices;


        //$this->choices_text = $this->thing->choice->current_node; 
        $this->choices_text = "";
        if ($this->choices['words'] != null) {
            $this->choices_text = strtoupper(implode(" / " ,$this->choices['words']));
        }




//        $choices = $this->thing->choice->makeLinks();
        $choices = $this->entity_agent->choice->makeLinks();

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

$this->choices_text = "WUMPUS";
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
             $sms .= " is " . strtoupper($this->state);
        }

if ( in_array($this->x, range(1,20)) ) {
        $sms .= " is at ";
//        $sms .= "(" . $this->x . ", " . $this->y . ")";
        $sms .= "(" . $this->x  . ") ";
        $sms .= "" . trim(strtoupper($this->cave_names[$this->x])) . "";
}

if ( $this->x == 0  ) {
        $sms .= " IS OUT OF BOUNDS. ";
}

//        $sms .= "" . strtoupper($this->cave_names[$this->x]) . "";


        $sms .= " \n" . $this->response;
$sms .= "\n";
//$sms .= $this->web_prefix . "thing/". $this->uuid . "/wumpus" . "";

//        $sms .= "\n";


if (strpos($this->web_prefix, '192.168') !== false) {
    echo 'true';
} else {
$sms .= $this->web_prefix . "thing/". $this->uuid . "/wumpus" . "";

        $sms .= "\n";
}



//var_dump($this->x);

        $this->cave_list_text = "";
        $this->choices_text = "SPAWN";

        if (isset($this->caves[strval($this->x)])) {
            $this->choices_text = "";
            $this->cave_list_text = trim(implode($this->caves[strval($this->x)]," ")) . "";
        }

        $sms .= "YOUR CHOICES ARE [ " . $this->cave_list_text . " " . $this->choices_text."] ";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }


  public function makePDF()
    {
        $txt = $this->thing_report['sms'];

        // initiate FPDI
        $pdf = new Fpdi\Fpdi();


        // http://www.percs.bc.ca/wp-content/uploads/2014/06/PERCS_Message_Form_Ver1.4.pdf
 //       $pdf->setSourceFile($this->resource_path . 'percs/PERCS_Message_Form_Ver1.4.pdf');
        $pdf->setSourceFile($this->resource_path . 'wumpus/wumpus.pdf');

        $pdf->SetFont('Helvetica','',10);

        $tplidx1 = $pdf->importPage(1, '/MediaBox');

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s['orientation'], $s);
        // $pdf->useTemplate($tplidx1,0,0,215);
        $pdf->useTemplate($tplidx1);

        $pdf->SetTextColor(0,0,0);

//        $text = "Inject generated at " . $this->thing->thing->created_at. ".";
//        $pdf->SetXY(130, 10);
//        $pdf->Write(0, $text);

        $image = $pdf->Output('', 'S');
//var_dump($image);
        $this->thing_report['pdf'] = $image;

        return $this->thing_report['pdf'];
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
//                $this->thing->choice->Choose("foraging");
                $this->entity_agent->choice->Choose("foraging");

                $this->response .= "Foraging. ";
                break;
            case "inside nest":
//                $this->thing->choice->Choose("in nest");
                $this->entity_agent->choice->Choose("in nest");

                $this->response .= "Wumpus is inside the " . $this->primary_place .". ";
                break;
            case "nest maintenance":
                $this->response .= "Wumpus is doing Nest Maintenance. ";
                //$this->thing->choice->Choose("nest maintenance");
                $this->entity_agent->choice->Choose("nest maintenance");

                break;
            case "patrolling":
                $this->response .= "Wumpus is Patrolling. ";
                //$this->thing->choice->Choose("patrolling");
                $this->entity_agent->choice->Choose("patrolling");

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
        $this->requested_cave_number = $this->x;
//        $this->number_agent = new Number($this->thing, $input);
        $this->number_agent = new Number($this->entity_agent, $input);

        $this->number_agent->extractNumber($input);

        if ($this->number_agent->number != false) {$this->requested_cave_number = $this->number_agent->number;}

        // Check if this is one of the available caves.

        if (!isset($this->caves[strval($this->x)])) {$this->spawn();}
        $available_cave_names = $this->caves[strval($this->x)];

        $match = false;
        foreach ($available_cave_names as $key=>$cave_name) {
            if ($cave_name == strval($this->requested_cave_number)) {
                $this->x = $this->requested_cave_number;
                $match= true;
                break;
            }
        }

        if ($this->requested_cave_number == strval($this->x)) {$r = "Took a look around the cave. ";}

        if (($match != true) and ($this->number_agent->number != false)) {$r = "Choose one of the three numbers. ";$this->response = $r;}
        else {

        $this->response .= $r;
        }
        // Accept wumpus commands
        $this->keywords = array("teleport","caves","look","arrow", "news", "forward", "north", "east", "south", "west", "up", "down", "left", "right","wumpus","meep","thing","lair","foraging","nest maintenance", "patrolling", "midden work", "nest maintenance", "start", "meep", "spawn");

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

        //$this->getCoordinate();

        foreach ($ngram_list as $key=>$piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {

                        case 'news':
                            $this->getNews();
                            $this->response .= $this->news;
                            //$this->response .= "May 18th is a Wumpus hunt at Queen Elizabeth Park. ";
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

                        case 'west':
                        case 'south':
                        case 'east':
                        case 'north':
                            $this->response .= ucwords($piece) . "? ";
                            break;

                        case 'left':
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
                            //$this->thing->choice->Choose($piece);
                            $this->entity_agent->choice->Choose($piece);

                            $this->response .= "Heard " . $this->state .". ";
                            break;

                        case 'teleport';
                        case 'spawn':
                            $this->spawn();
                            $this->response .= "Spawn. ";
                            break;

                        case 'inside nest':
//                                $this->thing->choice->Choose($piece);
                            $this->entity_agent->choice->Choose($piece);

//                            $this->state = $this->thing->choice->current_node;
                            $this->state = $this->entity_agent->choice->current_node;

                            $this->response .= "Heard inside nest.";
                            break;

                        case 'foraging':
                            //$this->thing->choice->Choose($piece);
                            $this->entity_agent->choice->Choose($piece);

//                            $this->state = $this->thing->choice->current_node;
                            $this->state = $this->entity_agent->choice->current_node;

                            $this->response .= "Now foraging. ";
                            break;

                        case 'nest maintenance':
//                            $this->thing->choice->Choose($piece);
                            $this->entity_agent->choice->Choose($piece);

                            // $this->state = $this->thing->choice->current_node;
                            $this->state = $this->entity_agent->choice->current_node;

                            $this->response .= "Heard nest maintenance. ";
                            break;

                        case 'patrolling':

//                            $this->thing->choice->Choose($piece);
//                            $this->state = $this->thing->choice->current_node;

                            $this->entity_agent->choice->Choose($piece);
                            $this->state = $this->entity_agent->choice->current_node;


                            $this->response .= "Now " . $piece .". ";
                            break;

                        case 'midden work':
                            $this->middenwork();
//                            $this->thing->choice->Choose($piece);
//                            $this->state = $this->thing->choice->current_node;

                            $this->entity_agent->choice->Choose($piece);
                            $this->state = $this->entity_agent->choice->current_node;


                            $this->response .= "Heard midden work. Urgh. ";
                            break;



                    }
                }
            }
        }
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

        //$this->caves[$current_cave];
foreach($this->caves[$current_cave] as $key=>$cave) {
$this->response .= "<" . $cave .">" .(strtoupper($this->cave_names[$cave])) ." ";
}
        $this->response .= "";
    }




    function foraging()
    {

//        $this->thing->choice->Create($this->primary_place, $this->node_list, "foraging");
        $this->entity_agent->choice->Create($this->primary_place, $this->node_list, "foraging");

        $this->response .= "Wumpus is foraging. ";
    }

    function patrolling()
    {
//        $this->thing->choice->Create($this->primary_place, $this->node_list, "patrolling");
        $this->entity_agent->choice->Create($this->primary_place, $this->node_list, "patrolling");

        $this->response .= "Wumpus is patrolling. ";
    }


	function spawn()
    {
        $this->getWumpus();
        //$coordinate = new Coordinate($this->thing, "(0,0)");

        $pheromone['stack'] = 4;

        $this->cave = strval(random_int(1,20));
        $this->x = $this->cave;

//        $coordinate = new Coordinate($this->thing, "(".$this->cave.",0)");


//        $this->thing->choice->Create($this->primary_place, $this->node_list, $this->state);
        $this->entity_agent->choice->Create($this->primary_place, $this->node_list, $this->state);

//		$this->thing->flagGreen();
        $this->entity_agent->flagGreen();

	}

    function start()
    {
        $this->x = "X";
        $this->getWumpus();
        //$this->thing->choice->Create($this->primary_place, $this->node_list, "start");
        $this->response .= "Welcome player. Wumpus has started.";
        //$this->thing->flagGreen();
        $this->entity_agent->flagGreen();
    }

}
