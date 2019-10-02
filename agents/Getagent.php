<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from cron 
// On call determine best thing to be addressed.

// Start by picking a random thing and seeing what needs to be done.


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '/home/wildtay3/public_html/stackr/vendor/autoload.php';
//require '../vendor/autoload.php';
//require '/var/www/html/stackr.ca/vendor/autoload.php';


class Getagent {

	function __construct(Thing $thing) {


		$this->thing_report['thing'] = false;

		if ($thing->thing != true) {
			//print "falsey";

	                $this->thing->log ( '<pre> Agent "Getagent" ran on a null Thing ' .  $thing->uuid .  '</pre>');
        	        $this->thing_report['info'] = 'Tried to run Web on a null Thing.';
			$this->thing_report['help'] = "That isn't going to work";

	                return $this->thing_report;


		//	exit();

		}

		

	
		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

		$this->agent_name = 'getagent';
		$this->agent_version = 'redpanda';

//		$this->thing_report = array('thing' => $this->thing->thing); 

        $this->thing_report['thing'] = $this->thing->thing;

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

		$this->web_prefix = $this->thing->container['stack']['web_prefix'];

		$this->node_list = array('start a'=>
					array('web default 1'),
				'start b'=>array('web default 2')
					);

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;

		$this->thing->log ( '<pre> Agent "Getagent" running on Thing ' .  $this->thing->nuuid . '.</pre>' );
		$this->thing->log ( '<pre> Agent "Getagent" received this Thing "' .  $this->subject .  '".</pre>' );

//$this->node_list = array("feedback"=>array("useful"=>array("credit 100","credit 250")), "not helpful"=>array("wrong place", "wrong time"),"feedback2"=>array("awesome","not so awesome"));	

//var_dump($this->thing->thing);

        $this->getLink();
		// If readSubject is true then it has been responded to.
        $this->getAgent();

//var_dump($this->prior_thing->thing->variables);

		$this->readSubject();
		$this->respond(); // Return $this->thing_report;


		$this->thing->log( '<pre> Agent "Getagent" ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) .'ms.' );

        $this->thing_report['log'] = $this->thing->log;

		return;
	}


    function getLink() {

        $block_things = array();
        // See if a block record exists.
        require_once '/var/www/html/stackr.ca/agents/findagent.php';
        $findagent_thing = new FindAgent($this->thing, 'thing');

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

//$this->thing->log('Agent "Block" found ' . count($findagent_thing->thing_report['things']) ." Block Things.");

        $this->max_index =0;

        $match = 0;

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {

$this->thing->log($block_thing['task'] . " " . $block_thing['nom_to'] . " " . $block_thing['nom_from']);

            // I can't remember why I screen for usermanager here.  But 
            // it 
            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                if ($match == 2) {break;}
            }
        }

//var_dump($this->link_uuid);
//exit();
        // So here I've got the uuid of the previous link.


        return $this->link_uuid;
    }

    function getAgent($link_uuid = null) {

        if ($link_uuid == null) {$link_uuid = $this->link_uuid;}
//var_dump($link_uuid);
//exit();
        //if (isset($this->link_uuid)) {

        // Retrieve Thing

        //require_once '/var/www/html/stackr.ca/agents/message.php';

        $this->prior_thing = new Thing($link_uuid);

// Returns the same but only prior to message.
                require_once '/var/www/html/stackr.ca/agents/callagent.php';
                $prior_agent = new Callagent($this->prior_thing, "getagent");
var_dump($prior_agent->thing_report);
var_dump($this->prior_thing->log);
exit();

//        $this->prior_thing = new Thing($link_uuid);

//        $this->variables_agent = new Variables($this->thing, "variables " . $default_train_name . " " . $this->from);

        $this->variables_agent = new Variables($this->prior_thing, "variables ". "getagent" . " " . $this->from);


//                $this->variables_thing->$name = $variable;
//                $this->agent_variables[] = $name;


        
//        var_dump($this->variables_agent->agent_variables);

//var_dump($this->prior_thing->thing->variables);

        $variables = $this->prior_thing->account['stack']->json->array_data;
//var_dump($variables);
//exit();
        //if (isset($variables[$this->variable_set_name])) {
            //$this->context = "train";
            //$t = $variables[$this->variable_set_name];

            $this->agent_variables = array();
            // Load to Thing variable for operations.
$newest= null;
$newest_name = null;
            foreach ($variables as $name=>$variable) 
            {

if (isset($variable['refreshed_at'])) {
    $dt = strtotime($variable['refreshed_at']);
} else {
    $dt = null;
}


if ($dt > $newest) {$newest = $dt;
$newest_name = $name;}

// Relies on JSON variable order
// Because it is the message before message which
// is the one the channel receives.
if ($name == "message") {$this->agent_name = $newest_name;}

                //$this->variables_thing->$name = $variable;
                $this->agent_variables[] = $name;
            }

            //return false;
        //} else {
            //return null;
        //}


//$this->agent_name = $newest_name;

//exit();
$this->agent_names = implode(" ", $this->agent_variables);
//var_dump($this->agent_names);

//exit();


        return $this->agent_name;

    }

	public function respond() {

		// Thing actions

                //$web_thing = new Thing(null);
                //$web_thing->Create($this->from, 'ant' , 's/ web view');

		$this->sms_message = "GETAGENT | " . ucwords($this->agent_name) . " | " . $this->agent_names;
//		$this->sms_message .= " | TEXT WHATIS";

		$this->thing_report['sms'] = $this->sms_message;


		$this->thing->json->setField("variables");
		$this->thing->json->writeVariable(array("getagent",
			"received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
			);



		$this->thing->flagGreen();

		//$choices = $this->thing->choice->makeLinks('start a');

        //$this->thing->account['thing']->Credit(25);
		//$this->thing->account['stack']->Debit(25);


                //$choices = $this->thing->choice->makeLinks('start a');
        $choices = false;
		$this->thing_report['choices'] = $choices;

		$this->thing_report['info'] = 'This is gets the name of the agent that last ran on the last Thing.';
		$this->thing_report['help'] = 'This give the Agent name.';

                $this->thing->log ( '<pre> Agent "Getagent" credited 25 to the Thing account.  Balance is now ' .  $this->thing->account['thing']->balance['amount'] . '</pre>');
 


		require_once '/var/www/html/stackr.ca/agents/message.php';
		$message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;


		return $this->thing_report;
	}

	public function readSubject() {

		//$this->defaultButtons();

		$status = true;
		return $status;		
	}

	function defaultButtons() {

//$html_links = $this->thing->choice->makeLinks();


		if (rand(0,5) <= 3) {
			$this->thing->choice->Create('link', $this->node_list, 'start a');
		} else {
			$this->thing->choice->Create('link', $this->node_list, 'start b');
		}

		//$this->thing->choice->Choose("inside nest");
		$this->thing->flagGreen();

		return;
	}



}
