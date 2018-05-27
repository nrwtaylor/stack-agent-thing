<?php
namespace Nrwtaylor\StackAgentThing;

//ini_set('display_startup_errors', 1);
//ini_set('display_errors', 1);
//error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Ant {
	

	public $var = 'hello';


    function __construct(Thing $thing) {

// Ant is a proof of the Thing's choice engine.


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

        // Setup logging
        $this->thing_report['thing'] = $this->thing->thing;


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
		$this->stack_uuid = $this->thing->container['stack']['uuid']; // 60s

		// Now create some shortcut conventions. 
		// devstack sqlresponse as a flag code
	        $this->uuid = $thing->uuid;
        	$this->to = $thing->to;
        	$this->from = $thing->from;
      		$this->subject = $thing->subject;
		$this->sqlresponse = null;



$this->node_list = array("inside nest"=>array("nest maintenance"=>array("patrolling"=>"foraging","foraging")),"midden work"=>"foraging");

        	$info = 'The "Ant" agent provides an button driven interface to manage access to your information on '. $this->short_name;
		$info .= 'from the web.  The Management suggests you explore the NEST MAINTENANCE button';
	
		// The 90s script
		$n = 'Information iss stored as Things. Things are how ' . $this->short_name . '.';
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

		$n .= 'Which gets you where exactly? A place where this "Ant" is going to be useful.';
		$n .= 'In a place where your Things are eroding.  Like castles on the beach.';

		$n .= 'And where to explore ' . $this->short_name . ' you should click on [ Nest Maintenance ].' ;

		$ninety_seconds = $n;

		$what = 'And Things they are meant to be shared transparently, but not indiscriminately.';
		$what .= '';

		$why = $this->short_name . ' is intended as a vehicle to leverage Venture Capital investment in individual impact.';

		//echo '<info>';echo $info;echo'</info>';
		//echo '<br>';
		//echo '<90s>';echo $ninety_seconds;echo'</90s>';

		$this->thing->log( '<pre> Agent "Ant" running on Thing ' . $this->uuid . ' </pre>' );
		$this->thing->log( '<pre> Agent "Ant" received this Thing "' . $this->subject .'"</pre>' );

		// echo "construct email response";

		// $this->thing->account->scalar = new Account($this->uuid,

		// $scalar_amount = 0;
		// $this->createAccount($scalar_amount); // Yup


		// Read the subject as passed to this class.
		// No charge to read the subject line.  By machine.

		//echo '<pre> Agent "Ant" start state is ';
		$this->state = $thing->choice->load('hive');
		//echo $this->thing->getState('usermanager');
		//echo $this->state;
		//echo'"</pre>';


       




		$this->readSubject();
		$this->respond();


// Err ... making sure the state is saved.
$this->thing->choice->Choose($this->state);

		// Which means at this point, we have a UUID
		// whether or not the record exists is another question.

		// But we don't need to find, it because the UUID is randomly created.	
		// Chance of collision super-super-small.

		// So just return the contents of thing.  false if it doesn't exist.
		
		//return $this->getThing();

        //echo '<pre> Agent "Ant" end state is ';
        $this->state = $thing->choice->load('hive');
        //echo $this->thing->getState('usermanager');
        //echo $this->state;
        //echo'"</pre>';

        $this->thing->log($this->agent_prefix . 'state is "' . $this->state . '".');

        $this->thing->log($this->agent_prefix . 'completed.');

        //$this->thing->log('<pre> Agent "Ant" completed</pre>');

        $this->thing_report['log'] = $this->thing->log;


		//echo '<pre> Agent "Ant" completed</pre>';

		return;

		}




//	function createAccount(String $account_name, $amount) {

//		$scalar_account = new Account($this->uuid, 'scalar', $amount, "happiness", "Things forgotten"); // Yup.
//		$this->thing->scalar = $scalar_account;
//		return;
//	}


// -----------------------

	private function respond() {

		// Thing actions


		$this->thing->flagGreen();

		// Generate SMS response
$litany = array('inside nest'=>'ANT | One of your records was displayed, perhaps by yourself.  An Ant spawned and is waiting in the nest. | TEXT NEST MAINTENANCE',
	'nest maintenance'=>'ANT | A record of yours was displayed again, perhaps by yourself.  This Ant is doing some nest maintenance. | FORGET',
	'patrolling'=>"ANT | A record of yours was displayed.  That's twice now.  This Ant is patrolling. | FORGET",
	'foraging'=>"ANT | This ant is on it's last legs.  It has gone foraging for stack information about you to forget. | FORGET",
	'midden work'=>'ANT | One of your records was displayed, perhaps by yourself.  An Ant spawned and is doing midden work. | FORGET',
 	'start'=>"ANT | Start.  Not normally means that you displayed a record, let's see if we get any more Ant messages. | FORGET"
);


				


		$this->message['sms'] = $litany[$this->state];


$whatisthis = array('inside nest'=>'Each time the ' . $this->short_name . ' service is accessed, Stackr creates a uniquely identifable Thing.
				This one is ' . $this->uuid . '.
				This message from the "Ant" ai which was been tasked with mediating web access to this Thing. 
				Manage Things on ' . $this->short_name . ' using the [ NEST MAINTENANCE ] command.  
				If Ant\'s are bothing you, you can either use the [ FORGET ] command
				to stop receiving notifications for the Thing, or you can turn [ ANT OFF ].
				"Ant" is how ' . $this->short_name . ' manages interactions with your Things by other identiies.
				[ANT OFF] will stop any "Ant" agent responding.  You can say [ NEST MAINTENANCE ] later if you change your mind.',
        'nest maintenance'=>'A Things of yours was displayed again, perhaps by yourself.  This Ant is doing some nest maintenance.',
        'patrolling'=>"A Thing associated with ' . $this->from .' was displayed (or requested by) a device.  That's twice now.  This Ant is patrolling.",
        'foraging'=>"This ant is on it's last legs.  It has gone foraging for stack information about you to forget.",
        'midden work'=>'One of your records was displayed, perhaps by yourself.  An Ant spawned and is doing midden work.',
        'start'=>"Start. Not normally means that you displayed a record, let's see if we get any more Ant messages."
);

		// Generate email response.

		$to = $this->thing->from;
		$from = "ant";

//		echo "<br>";



		$choices = $this->thing->choice->makeLinks($this->state);
        $this->thing_report['choices'] = $choices ;

		//echo "<br>";
		//echo $html_links;


		$test_message = "Agent 'Ant' Status" . '<br>';
		$test_message .= 'Last thing heard: "' . $this->subject . '"<br>' . 'The next Ant choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>Hive state: ' . $this->state . '<br>';
		$test_message .= '<br>Thing information' . '<br>';

		$test_message .= '<br>subject: ' . $this->subject . '<br>';
		$test_message .= 'created_at: ' . $this->thing->thing->created_at . '<br>';
		$test_message .= 'from: ' . $this->from . '<br>';
		$test_message .= 'to: ' . $this->to . '<br>';


		$test_message .= '<br>' .$litany[$this->state] . '<br>';
                $test_message .= '<br>' .$whatisthis[$this->state] . '<br>';

            $this->thing_report['sms'] = $this->message['sms'];
            $this->thing_report['email'] = $test_message;
            $this->thing_report['message'] = $test_message; // NRWTaylor 4 Oct - slack can't take html in$


                        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

		
//		$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);


$this->thing_report['help'] = 'This is the "Ant" Agent. It organizes your Things.' ;


		//echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}


	public function readSubject() {

		//test
//echo "meep";
		//$this->middenwork();

		$this->response = null;

		if ($this->state == null) {
		echo "ant detected state null - run subject discriminator";

            $this->thing->log($this->agent_prefix . 'state is null.  Subject discriminator run.');


		switch ($this->subject) {
			case "spawn":
				//echo "spawn";
				$this->spawn();
				break;
			case "kill":
				$this->kill();
				break;
			case "foraging":
				$this->thing->choice->Choose("foraging");

				break;
			case "inside nest":
				$this->thing->choice->Choose("inside nest");
				break;
			case "nest maintenance":
				$this->thing->choice->Choose("nest maintenance");
				break;
			case "patrolling":
				$this->thing->choice->Choose("patrolling");
				break;
			case "midden work":
				$this->thing->choice->Choose("midden work");
				
//				$this->middenwork();

				// Need to figure out how to set flag to red given that respond will then reflag it as green.
				// Can green reflag red?  Think about reset conditions.

				break;
			default:
			   echo "not found => spawn()";
				$this->spawn();
		}


		}


		$this->state = $this->thing->choice->load('hive');

		//echo "this state is " .$this->state;
		//echo "meep";

		// Will need to develop this to only only valid state changes.

                switch ($this->state) {
                        case "spawn":
                                //echo "spawn";
                                //$this->spawn();
                                break;
                        case "kill":
                                //$this->kill();
                                break;
                        case "foraging":
                                //$this->thing->choice->Choose("foraging");

                                break;
                        case "inside nest":
                                //$this->thing->choice->Choose("in nest");
                                break;
                        case "nest maintenance":
                                //$this->thing->choice->Choose("nest maintenance");
                                break;
                        case "patrolling":
                                //$this->thing->choice->Choose("patrolling");
                                break;
                        case "midden work":
                                //$this->thing->choice->Choose("midden work");
                                $this->middenwork();

                                // Need to figure out how to set flag to red given that respond will then reflag it as green.
                                // Can green reflag red?  Think about reset conditions.

                                break;
                        default:
                            $this->thing->log($this->agent_prefix . 'invalid state provided "' . $this->state .'".');

                          // echo "not found";

				// this case really shouldn't happen.
				// but it does when a web button lands us here.


		                //if (rand(0,5)<=3) {
               			//         $this->thing->choice->Create('hive', $this->node_list, "inside nest");
                		//} else {
                        	//	$this->thing->choice->Create('hive', $this->node_list, "midden work");
                		//}



                }

		$this->thing->choice->Create('hive', $this->node_list, $this->state);




		return false;

	
	}



function middenwork() {
	// So here we define what a midden work does when it is called by the agenthandler.
	// Midden Work is the building and maintenance work of the stack.
	// Midden Work is about putting Things back in their place.

	// First Thing that is out of place are the button clicks which are posterior uuid linked.

	// So explore the user's associations and replace any null@stackr. owners with ?

	// Options are the ant's identifier, the stack identifier, the user identifier, or
	// to determine the latest decision and the strongest decision path.

	// Strongest decision path is the one with the most engagement - ie button pressed multiple times.
	// Devstack: So think tokenlimiting on button pushes.

	// Latest decision is the last time the outcome was decided.

	// Midden Worker should build a uuid tree.

	// So first thing.  Get a list of all user Things.

	// Well as an Ant Midden Worker we don't know a huge amount.
	// Taking a s/ forget ant
	// We have(?) two accounts associated.  Which should be true until the Foraging state.

	// So first question is why is this ant being called?
	// Errr. Because the state is midden work and the flag is red.

	// Ok stuck, because the midden worker doesn't know enough and is null@<mail_postfix>


	// Then what?
	// Then we figure it out.

// Then use an agent state?
// getState($agent = null)
// ->db->agentSearch($agent, limit)
// ->db->userSearch($keyword)
// ->UUids($uuid = null)


	echo "ant state: " . $this->thing->getState('hive');

	$haystack ="";


        $t = $this->thing->thing;
        $haystack .= json_encode($t);

	$t = $this->thing->db->UUids();
	$haystack .= json_encode($t);

	$t = $this->thing->db->agentSearch('ant');
	$haystack .= json_encode($t);    

        $t = $this->thing->db->userSearch($this->uuid);
	$haystack .= json_encode($t);     


	$thingreport = $this->thing->db->priorGet();
	$posterior_thing = $thingreport['thing'];

	$haystack .= json_encode($posterior_thing);



// And we can do this...

//echo "the thing is:";
//print_r($this->thing);


// $match = "/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12‌​}/";


//echo "<pre>";
//echo "haystack search<br>";

//preg_match_all('/(\S{4,})/i', $haystack, $matches); // more than four letters long

//preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $haystack, $matches);
//preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12‌​}/', $haystack, $matches);
preg_match_all('/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/',$haystack,$matches);

//echo "all uuids extracted using tools available to Thing " . $this->uuid ."<br>";
$arr = array_values(array_unique($matches[0]));

$linked = array();

foreach ($arr as $key=>$value) {
	//echo $value;
	$temp_thing = new Thing($value);

	if ($temp_thing == false) {break;}

//	print_r($temp_thing->thing);
	//print_r($temp_thing->thing->uuid);	
	$haystack = json_encode($temp_thing->thing);

	if ( (strpos($haystack,$this->uuid) !== false) and ($value != $this->uuid) ) {
	  //                  print_r($temp_thing->thing);
		$linked[] = $value;

	        }
        }
    }


	function spawn()
    {
        $ant_pheromone['stack'] = 4;
        if ((rand(0,5) + 1) <= $ant_pheromone['stack']) {
           $this->thing->choice->Create('hive', $this->node_list, "inside nest");
        } else {
            $this->thing->choice->Create('hive', $this->node_list, "midden work");
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

