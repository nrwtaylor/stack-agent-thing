<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Crow
{
	public $var = 'hello';


    function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;
        // Crow is a proof of the Thing's choice engine.


	//function __construct($arguments) {

		//echo $arguments;
		//var_dump($arguments);
//  $defaults = array(
//    'uuid' => Uuid::uuid4(),
//    'from' => NULL,
//	'to' => NULL,
//	'subject' => NULL,
//	'sqlresponse' => NULL
//  );

//  $arguments = array_merge($defaults, $arguments);

//  echo $arguments['firstName'] . ' ' . $arguments['lastName'];


// So while that looks very clever, I am hesitant to implement it on a nice day.  Again I guess.
// On.

        // Setup Agent
        $this->agent = strtolower(get_class());
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent) . '" ';

		$this->test= "Development code";

		$this->thing = $thing;
        $this->thing_report['thing'] = $thing;

        // Example API
        $this->api_key = $this->thing->container['api']['translink'];

		// Load in some characterizations.
		$this->short_name = $this->thing->container['stack']['short_name']; 
		$this->web_prefix = $this->thing->container['stack']['web_prefix']; 
		$this->mail_prefix = $this->thing->container['stack']['mail_prefix']; 
		$this->mail_postfix = $this->thing->container['stack']['mail_postfix'];

        $this->sms_seperator = $this->thing->container['stack']['sms_separator']; // |
		$this->sms_address = $this->thing->container['stack']['sms_address'];

        // Get some stuff from the stack which will be helpful.
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];


		// Load in time quantums
        $this->cron_period = $this->thing->container['stack']['cron_period']; // 60s
		$this->thing_resolution = $this->thing->container['stack']['thing_resolution']; // 1ms

		// Load in a pointer to the stack record.
		$this->stack_uuid = $this->thing->container['stack']['uuid'];

		// Now create some shortcut conventions.
		// devstack sqlresponse as a flag code
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        // For the Crow
        $this->created_at = $thing->thing->created_at;

		$this->sqlresponse = null;

        $this->entity = new Entity ($this->thing, "crow");
var_dump($this->entity->id);

        $this->node_list = array("inside nest"=>array("nest maintenance"=>array("patrolling"=>"foraging","foraging")),"midden work"=>"foraging");

        $info = 'The "Crow" agent provides an button driven interface to manage access to your information on '. $this->short_name;
		$info .= 'from the web.  The Management suggests you explore the NEST MAINTENANCE button';

		// The 90s script
		$n = 'Information is stored as Things. Things are how ' . $this->short_name . '.';
		$n .= 'Stuff comes into a Thing, a Thing has several Agents that help it deal with Things.';
		$n .= 'Agents work for ' . $this->short_name . '.  Most of them providing ai interfaces';
		$n .= 'to services.  Basic SMS commands you can perform are "51380" or any other Translink';
		$n .= 'bus sign number.  And BALANCE, GROUP, JOIN, SAY and LISTEN.  I figure those';
		$n .= 'are handy if you are in Metro Vancouver.';

		$n .= 'And you can email those words to stack' . $this->mail_postfix . ', but my ask';
		$n .= 'right now is that you text "51380" to '. $this->sms_address .'.';
		$n .= "That is how I track new sign-ups, and kind of how people judge things.";

		$n .= $this->short_name . ' has no desire to collect your information.';
		$n .= "";
		$n .= 'The target stack setting is to FORGET Things within 4 hours.  You can';
		$n .= 'check how much information you have deposited with ' .$this->short_name .' with the ';
		$n .= 'BALANCE by texting (778) 401-2132 and/or by emailing BALANCE to stack' . $this->mail_postfix . '.';

		$n .= 'If it is near 0 units then we do not have much Things associated with ' . $this->from .'.';
		$n .= 'Balances over 100,000 do.  It costs ' . $this->short_name . ' computationally to calculate';
		$n .= 'the balance.  We charge for data retention.  If you seem to need this limited service ';
		$n .= 'will be offered it.';

		$n .= 'Which gets you where exactly? A place where this "Crow" is going to be useful.';
		$n .= 'In a place where your Things are eroding.  Like castles on the beach.';

		$n .= 'And where to explore ' . $this->short_name . ' you should click on [ Nest Maintenance ].' ;

		$ninety_seconds = $n;

		$what = 'And Things they are meant to be shared transparently, but not indiscriminately.';
		$what .= '';

		$why = $this->short_name . ' is intended as a vehicle to leverage Venture Capital investment in individual impact.';

		$this->thing->log( '<pre> Agent "Crow" running on Thing ' . $this->uuid . ' </pre>' );
		$this->thing->log( '<pre> Agent "Crow" received this Thing "' . $this->subject .'"</pre>' );

		// Read the subject as passed to this class.
		// No charge to read the subject line.  By machine.

		$this->state = $thing->choice->load('roost');

$this->get();

        //$this->getCrow();

//        $this->state = $this->crow->state;
		$this->readSubject();

		$this->respond();

$this->set();
//var_dump($this->thing);

        // Err ... making sure the state is saved.
        $this->thing->choice->Choose($this->state);

        $this->state = $thing->choice->load('roost');

        $this->thing->log($this->agent_prefix . 'state is "' . $this->state . '".');
        $this->thing->log($this->agent_prefix . 'completed.');

        $this->thing_report['log'] = $this->thing->log;

        return;

    }


    function set()
    {
/*
        if (!isset($this->refreshed_at)) {$this->refreshed_at = $this->thing->time();}
        //$this->refreshed_at = $this->current_time;
        $crow = new Variables($this->thing, "variables crow " . $this->from);

        $crow->setVariable("left_count", $this->left_count);
        $crow->setVariable("right_count", $this->right_count);

        $crow->setVariable("refreshed_at", $this->refreshed_at);

        $crow->thing->log( $this->agent_prefix .' set ' . $this->left_count . ' and ' . $this->right_count . ".", "INFORMATION" );
*/
//        $crow = new Variables($this->thing, "variables " . $this->uuid . " " . $this->from);
//        $crow->setVariable("left_count", $this->left_count);
//        $crow->setVariable("right_count", $this->right_count);
//        $crow->setVariable("refreshed_at", $this->refreshed_at);

//        if ( ($this->roll == false) or ($this->result == false) ) {


            $this->thing->json->writeVariable( array("crow", "left_count"), $this->left_count );
            $this->thing->json->writeVariable( array("crow", "right_count"), $this->right_count );

            $this->thing->log($this->agent_prefix . ' completed read.', "OPTIMIZE") ;
//        }



        return;
    }

    private function get($crow_code = null)
    {

        $this->current_time = $this->thing->json->time();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $this->time_string = $this->thing->json->readVariable( array("crow", "refreshed_at") );


        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.
        if ($crow_code == null) {$crow_code = $this->uuid;}

        //$this->crow = new Variables($this->thing, "variables " . $place_code . " " . $this->from);

//        $this->left_count = $this->thing->getVariable("left_count");
//        $this->right_count = $this->thing->getVariable("right_count");
//        $this->refreshed_at = $this->place->getVariable("refreshed_at");


        if ($this->time_string == false) {
            $this->thing->json->setField("variables");
            $this->time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("crow", "refreshed_at"), $this->time_string );
        }

        $this->refreshed_at = strtotime($this->time_string);


        $this->thing->json->setField("variables");
        $this->left_count = strtolower($this->thing->json->readVariable( array("crow", "left_count") ));
        $this->right_count = $this->thing->json->readVariable( array("crow", "right_count") );

if( ($this->left_count == false) or ($this->left_count = "")) {$this->left_count = 0;$this->right_count = 0;}
if( ($this->right_count == false) or ($this->right_count = "")) {$this->left_count = 0;$this->right_count = 0;}




        return array($this->left_count, $this->right_count);
    }




	private function respond()
    {

		// Thing actions


		$this->thing->flagGreen();

		// Generate SMS response

//		$this->message['sms'] = $litany[$this->state];
        $this->makeMessage();
        $this->makeSMS();
        // . " " . if (isset($this->response)) {$this->response;};


$this->whatisthis = array('inside nest'=>'Each time the ' . $this->short_name . ' service is accessed, Stackr creates a uniquely identifable Thing.
				This one is ' . $this->uuid . '.
				This message from the "Crow" ai which was been tasked with mediating web access to this Thing. 
				Manage Things on ' . $this->short_name . ' using the [ NEST MAINTENANCE ] command.  
				If Crow\'s are bothing you, you can either use the [ FORGET ] command
				to stop receiving notifications for the Thing, or you can turn [ CROW OFF ].
				"Crow" is how ' . $this->short_name . ' manages interactions with your Things by other identities.
				[CROW OFF] will stop any "Crow" agent responding.  You can say [ NEST MAINTENANCE ] later if you change your mind.',
        'nest maintenance'=>'A Things of yours was displayed again, perhaps by yourself.  This Crow is doing some nest maintenance.',
        'patrolling'=>"A Thing associated with " . "this identity" ." was displayed (or requested by) a device.  That's twice now.  This Crow is patrolling.",
        'foraging'=>"This crow is on it's last legs.  It has gone foraging for stack information about you to forget.",
        'midden work'=>'One of your records was displayed, perhaps by yourself.  A Crow hatched and is doing midden work.',
        'start'=>"Start. Not normally means that you displayed a record, let's see if we get any more Crow messages."
);

		// Generate email response.

		$to = $this->thing->from;
		$from = "crow";

        $this->makeChoices();
        $this->makeWeb();
        $this->makeTXT();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = 'This is the "Crow" Agent. It organizes your Things.' ;

//		return;
	}

    private function getCrow($requested_nuuid = null)
    {
        if ($requested_nuuid == null) {$requested_nuuid = $this->entity->id;}

        $entity = new Entity($this->thing, "crow");

        $this->thing = $entity->thing;
return;

        // Get up to 10000 crows.
        $crow = new FindAgent($this->thing, "crow");
        $crow->horizon = 10000;
        $crow->findAgent("crow");
        $crow_things = $crow->thing_report['things'];




        $matching_uuids = array();

        foreach($crow_things as $key=>$crow) {
            $crow_nuuid = substr($crow['uuid'], 0, 4);

            if (strtolower($crow_nuuid) == strtolower($requested_nuuid)) {
                // Consistently match the nuuid to a specific uuid.
                $this->crows[] = new Thing($crow['uuid']);
            }
        }

        if (!isset($this->crows[0])) {return true;}

        $this->thing = $this->crows[0];
    }

    public function makeWeb()
    {
        $test_message = "<b>CROW " . strtoupper($this->thing->nuuid) . "</b>" . '<br>';
        $test_message .= "<p>";
        $test_message .= '<p><b>Crow State</b>';

        $test_message .= '<br>Last thing heard: "' . $this->subject . '"<br>' . 'The next Crow choices are [ ' . $this->choices['link'] . '].';
        $test_message .= '<br>Roost state: ' . $this->state;
        $test_message .= '<br>left_count is ' . $this->left_count;
        $test_message .= '<br>right count is ' . $this->right_count;

        $test_message .= '<br>' .$this->crow_behaviour[$this->state] . '<br>';


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
        $test_message .= '<br>' .$this->crow_narrative[$this->state] . '<br>';

       // $test_message .= '<p>Agent "Crow" is responding to your web view of datagram subject "' . $this->subject . '", ';
       // $test_message .= "which was received " . $this->thing->human_time($this->thing->elapsed_runtime()) . " ago.";

        $refreshed_at = max($this->created_at, $this->created_at);
        $test_message .= "<p>";
        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($refreshed_at) );
        $test_message .= "<br>Thing happened about ". $ago . " ago.";

        //$test_message .= '<br>' .$this->whatisthis[$this->state] . '<br>';

        //$this->thing_report['sms'] = $this->message['sms'];
        $this->thing_report['web'] = $test_message;


    }

    public function makeTXT()
    {
//        $txt = "";
//        foreach($this->crows as $key=>$crow) {
//            $txt .= "ghet" . "\n";
//        }

//        $this->thing_report['txt'] = $txt;
//        $this->txt = $txt;



    }


    public function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks($this->state);
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
        // Generate SMS response

        $narratives = array("predator"=>"Crow is Watching for predators.",
            "human"=>"Crow is analyzing humans.",
            "quest"=>"Crow is questing for the oracle.",
            "funnies"=>"Crow has found a Peanut's comic strip.");
        $key = array_rand($narratives);
        $patrolling_narrative = $narratives[$key];

        $behaviours = array("predator"=>"Crow is Watching for predators.",
            "human"=>"Crow is analyzing humans.",
            "quest"=>"Crow is questing for the oracle.",
            "funnies"=>"Crow has found a Peanut's comic strip.");
        $patrolling_behaviour = $behaviours[$key];

        $litanies = array("predator"=>"Crow is Watching for predators.",
            "human"=>"Crow is analyzing humans.",
            "quest"=>"Crow is questing for the oracle.",
            "funnies"=>"Crow has found a Peanut's comic strip.");
        $patrolling_litany = $litanies[$key];

        $this->litany = array('inside nest'=>'One of your records was displayed, perhaps by yourself.  A Crow arrived and is waiting in the nest.',
            'nest maintenance'=>'A record of yours was displayed again, perhaps by yourself.  This Crow is doing some nest maintenance.',
            'patrolling'=>$patrolling_litany,
            'foraging'=>"This crow is on it's last legs.  It has gone foraging for stack information about you to forget.",
            'midden work'=>'One of your records was displayed, perhaps by yourself.  An Crow arrived and is doing midden work.',
            'start'=>"Start.  Not normally means that you displayed a record, let's see if we get any more Crow messages."
        );

        // Not used. Consider removing.
        $this->thing_behaviour = array('inside nest'=>'A Thing was instantiated.',
            'nest maintenance'=>'A Thing was instantiated again.',
            'patrolling'=>"A Thing was instantiated twice.",
            'foraging'=>"A Thing is searching the stack.",
            'midden work'=>'A Thing is doing stack work.',
            'start'=>"Start. A Thing started."
        );

        // Behaviour
        $this->crow_behaviour = array('inside nest'=>'Crow hatched and is waiting in the nest.',
            'nest maintenance'=>'Crow is doing some nest maintenance.',
            'patrolling'=>$patrolling_behaviour,
            'foraging'=>"This crow is on it's last legs.  It has gone foraging for stack information about you to forget.",
            'midden work'=>'An Crow spawned and is doing midden work.',
            'start'=>"Crow egg."
        );

        // Narrative
        $this->crow_narrative = array('inside nest'=>'Everything is dark.',
            'nest maintenance'=>'You are a Nest Maintainer. What does that even mean?',
            'patrolling'=>$patrolling_narrative,
            'foraging'=>"Now you are a Forager. What are you foraging for?",
            'midden work'=>'You are a Midden Worker. Have fun.',
            'start'=>"Crow egg."
        );

        $this->prompt_litany = array('inside nest'=>'TEXT WEB / NEST MAINTENANCE',
            'nest maintenance'=>'TEXT WEB / PATROLLING / FORAGING',
            'patrolling'=>"TEXT WEB / FORGET",
            'foraging'=>"TEXT WEB / FORGET",
            'midden work'=>'TEXT WEB / FORGET',
            'start'=>"TEXT WEB / MIDDEN WORK / NEST MAINTENANCE"
        );

        $sms = "CROW | " . $this->thing->nuuid;
//        $sms .= " | " . $this->thing_behaviour[$this->state];
        $sms .= " | " . $this->crow_behaviour[$this->state];
        $sms .= " " . $this->response;
        $sms .= " | " . $this->prompt_litany[$this->state];

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;

    }

	public function readSubject()
    {

        $nuuid_agent = new Nuuid($this->thing, "nuuid");
        $nuuid_agent->extractNuuid($this->subject);

        $nuuid = $this->entity->id;
        if (isset($nuuid_agent->nuuid)) {$nuuid = $nuuid_agent->nuuid;}

        $this->getCrow($nuuid);

//        if ($this->crow == null) {$this->getCrow(); $this->response = "Crow " . $nuuid . " was not found. Got last crow.";}

		$this->response = null;

		if ($this->state == null) {
		    //$this->response = "detected state null - run subject discriminator";
            $this->thing->log($this->agent_prefix . 'state is null.  Subject discriminator run.');


		    switch ($this->subject) {
			    case "spawn":
				    //echo "spawn";
				    $this->spawn();
                    $place = new Place($this->thing, "roost");
                    $this->response = "Spawned an Crow at Roost.";
				    break;
			    case "kill":
				    $this->kill();
                    $this->response = "Killed this Crow.";
				    break;
			    case "foraging":
				    $this->thing->choice->Choose("foraging");
                    $this->response = "This Crow is Foraging.";
				    break;
			    case "inside nest":
				    $this->thing->choice->Choose("inside nest");
                    $this->response = "This Crow is Inside the Roost.";
				    break;
			    case "nest maintenance":
				    $this->thing->choice->Choose("nest maintenance");
                    $this->response = "This Crow is doing Nest Maintenance.";
				    break;
			    case "patrolling":
				    $this->thing->choice->Choose("patrolling");
                    $this->response = "This Crow is Patrolling.";
				    break;
			    case "midden work":
				    $this->thing->choice->Choose("midden work");
                    $this->response = "This Crow is doing Midden Work.";
				    $this->middenwork();

				    // Need to figure out how to set flag to red given that respond will then reflag it as green.
				    // Can green reflag red?  Think about reset conditions.

				    break;
			    default:
                    $this->response = "Crow spawned.";
			        // echo "not found => spawn()";
				    $this->spawn();
		    }
		}

		$this->state = $this->thing->choice->load('roost');

		//echo "this state is " .$this->state;
		//echo "meep";
        if ($this->state == false) {
            $this->state = $this->subject;
            return;
        }

		// Will need to develop this to only only valid state changes.
        switch ($this->state) {
            case "spawn":
                //echo "spawn";
                //$this->spawn();
                $this->response = "Spawned Crow.";
                break;
            case "kill":
                $this->response = "Dead Crow.";
                //$this->kill();
                break;
            case "foraging":
                //$this->thing->choice->Choose("foraging");
                $this->response = "Foraging.";
                break;
            case "inside nest":
                //$this->thing->choice->Choose("in nest");
                $this->response = "Crow is Inside Nest.";
                break;
            case "nest maintenance":
                $this->response = "Crow is doing Nest Maintenance.";
                //$this->thing->choice->Choose("nest maintenance");
                break;
            case "patrolling":
                $responses = array("Crow is Watching for predators.",
                    "Crow is analyzing humans.",
                    "Crow is questing for the oracle.",
                    "Crow has found a Peanut's comic strip.");
$this->response = array_rand ($responses);
                //$this->response = "Crow is Patrolling.";
                //$this->thing->choice->Choose("patrolling");
                break;
            case "midden work":
                $this->response = "Crow is doing Midden Work.";
                $this->middenwork();

                // Need to figure out how to set flag to red given that respond will then reflag it as green.
                // Can green reflag red?  Think about reset conditions.

                break;

            case false:

            default:
                $this->thing->log($this->agent_prefix . 'invalid state provided "' . $this->state .'".');
                $this->response = "Crow is broken.";
                // echo "not found";

				// this case really shouldn't happen.
				// but it does when a web button lands us here.


		        //if (rand(0,5)<=3) {
               	//         $this->thing->choice->Create('roost', $this->node_list, "inside nest");
                //} else {
                //	$this->thing->choice->Create('roost', $this->node_list, "midden work");
                //}



        }

        $input = strtolower($this->subject);
        // Accept crow commands
        $this->keywords = array("forward", "left", "right");


        $pieces = explode(" ", strtolower($input));
/*
        if (count($pieces) == 1) {
            if ($input == 'crow') {
                $this->getPlace();
                $this->response = "Last 'place' retrieved.";
                return;
            }

        }
*/
        foreach ($pieces as $key=>$piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {

                        case 'left':
                            $this->left_count += 1;
                            $this->response = "Crow moved left.";
                            break;

                        case 'right':
                            $this->right_count += 1;
                            $this->response = "Crow moved right.";
                            break;

                        case 'forward':
                            $this->left_count += 1;
                            $this->right_count += 1;
                            $this->response = "Crow moved forward.";
                            break;

                    }
                }
            }
        }

        // Update Crow's state tree
		$this->thing->choice->Create('roost', $this->node_list, $this->state);

		return false;
	}



    function middenwork()
    {

        $middenwork = "on";
        if ($middenwork != "on") {$this->response = "No work done.";return;}

	    // So here we define what a midden work does when it is called by the agenthandler.
	    // Midden Work is the building and maintenance work of the stack.
	    // Midden Work is about putting Things back in their place.

	    // First Thing that is out of place are the button clicks which are posterior uuid linked.

	    // So explore the user's associations and replace any null@stackr. owners with ?

	    // Options are the crow's identifier, the stack identifier, the user identifier, or
	    // to determine the latest decision and the strongest decision path.

	    // Strongest decision path is the one with the most engagement - ie button pressed multiple times.
	    // Devstack: So think tokenlimiting on button pushes.

	    // Latest decision is the last time the outcome was decided.

	    // Midden Worker should build a uuid tree.

	    // So first thing.  Get a list of all user Things.

	    // Well as an Crow Midden Worker we don't know a huge amount.
	    // Taking a s/ forget crow
	    // We have(?) two accounts associated.  Which should be true until the Foraging state.

	    // So first question is why is this crow being called?
	    // Errr. Because the state is midden work and the flag is red.

	    // Ok stuck, because the midden worker doesn't know enough and is null@<mail_postfix>


	    // Then what?
	    // Then we figure it out.

        // Then use an agent state?
        // getState($agent = null)
        // ->db->agentSearch($agent, limit)
        // ->db->userSearch($keyword)
        // ->UUids($uuid = null)

    	echo "crow state: " . $this->thing->getState('roost');

        // Form a haystack from the whole thing.
    	$haystack ="";
        $t = $this->thing->thing;
        $haystack .= json_encode($t);

        // Get stack related UUIDs (and add to the haystack)
        $t = $this->thing->db->UUids();
        $haystack .= json_encode($t);

        // Computers are very good at looking for needles in haystacks
        // So also add the words of any other crow.
	    $t = $this->thing->db->agentSearch('crow');
	    $haystack .= json_encode($t);

        // Add in words from this Crow's Uuid.
        // What is being said about Crow?
        $t = $this->thing->db->userSearch($this->uuid);
	    $haystack .= json_encode($t);


        $thingreport = $this->thing->db->priorGet();
        $posterior_thing = $thingreport['thing'];

        $haystack .= json_encode($posterior_thing);

        // And we can do this...

        //echo "the thing is:";
        //print_r($this->thing);

        // But that really depends on the security of the Channel.

        // devstack use Uuid to extract Uuids.
        // $match = "/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12‌​}/";

        // This is a loose screen on any alphanumeric sequence with UUID like hyphenation.

        // Some other screens
        //preg_match_all('/(\S{4,})/i', $haystack, $matches); // more than four letters long
        //preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $haystack, $matches);
        //preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12‌​}/', $haystack, $matches);

        // But use this one.
        preg_match_all('/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/',$haystack,$matches);

        // All Uuids visible to this Crow loaded.
        $arr = array_values(array_unique($matches[0]));


        // Now go through each Uuid
        // Make a list of the Uuids which mention this Crow's Uuid.
        $linked = array();

        foreach ($arr as $key=>$value) {
	        //echo $value;
	        $temp_thing = new Thing($value);

            if ($temp_thing == false) {break;}

            // print_r($temp_thing->thing);
	        // print_r($temp_thing->thing->uuid);
	        $haystack = json_encode($temp_thing->thing);

	        if ( (strpos($haystack,$this->uuid) !== false) and ($value != $this->uuid) ) {
	            // print_r($temp_thing->thing);
		        $linked[] = $value;
	        }
        }

        // And then don't do anything with the list.
        $this->response = "Collected Uuids and then discarded them without action.";
    }


	function spawn()
    {
        $crow_pheromone['stack'] = 4;
        if ((rand(0,5) + 1) <= $crow_pheromone['stack']) {
           $this->thing->choice->Create('roost', $this->node_list, "inside nest");
        } else {
            $this->thing->choice->Create('roost', $this->node_list, "midden work");
        }

		$this->thing->flagGreen();

		return;
	}

	function kill()
    {
		// No messing about.
		return $this->thing->Forget();
	}

}

?>
