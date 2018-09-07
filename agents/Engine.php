<?php
namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Engine
{
	function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = $thing->elapsed_runtime();
        $this->agent_input = $agent_input;

		$this->thing_report['thing'] = false;

		if ($thing->thing != true) {

            $this->thing->log ( 'ran on a null Thing ' .  $thing->uuid .  '.');
  	        $this->thing_report['info'] = 'Tried to run Engine on a null Thing.';
			$this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;
		}

		$this->thing = $thing;
		$this->agent_name = 'engine';
        $this->agent_prefix = 'Agent "Engine" ';
		$this->agent_version = 'redpanda';

		$this->thing_report['thing'] = $thing;

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.
       // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

		$this->node_list = array('engine'=>array('privacy'),'privacy'=>
					array('retention', 'persistence'),
				'warranty'=>array('helpful','useful?')
					);

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;

		$this->thing->log ( 'running on Thing ' .  $this->uuid . '.' );
		$this->thing->log ( 'received this Thing "' .  $this->subject .  '".' );

//$this->node_list = array("feedback"=>array("useful"=>array("credit 100","credit 250")), "not helpful"=>array("wrong place", "wrong time"),"feedback2"=>array("awesome","not so awesome"));	


		// If readSubject is true then it has been responded to.

		$this->readSubject();

        $this->getEngine();

//        if ($this->agent_input == null) {
		    $this->respond(); // Return $this->thing_report;
//        }


		$this->thing->log( 'completed' );

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;

		return;
  	}

	public function respond()
    {
		// Thing actions

        //$web_thing = new Thing(null);
        //$web_thing->Create($this->from, $this->agent_name, 's/ record web view');
        $this->makeSMS();
/*
        if (strtolower($this->prior_agent) == "pdf") {
            $this->sms_message = "PDF | No pdf available.";
        } else {

		$this->sms_message = "PDF | " . $this->web_prefix . "" . $this->link_uuid . "/" . strtolower($this->prior_agent) . ".pdf";
        }


		$this->sms_message .= " | TEXT WARRANTY";
		$this->thing_report['sms'] = $this->sms_message;
*/
		$this->thing->json->setField("variables");
		$this->thing->json->writeVariable(array("engine",
			"refreshed_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
			);
        $this->makeChoices();

		$this->thing->flagGreen();

		$this->thing_report['info'] = 'This is the engine agent.';
		$this->thing_report['help'] = 'This agent reports on the engine(s) running.  See Github.';

        //$this->thing->log ( '<pre> Agent "Code" credited 25 to the Thing account.  Balance is now ' .  $this->thing->account['thing']->balance['amount'] . '</pre>');

        if ($this->agent_input == null) {
		    $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->makeWeb();
//        $this->makePDF();

		return $this->thing_report;
	}

    function getEngine()
    {

       //$file = @file_get_contents(__DIR__ . '/../agents/'. $class_name . '.php');

        $path = $this->resource_path . '../vendor/nrwtaylor/stack-agent-thing/composer.json';

//echo $path;

       $file = @file_get_contents($path);


        if($file=== FALSE) { // handle error here... }
            echo "Agent 'Engine' says, " . '"Could not find the engine file."';
        }

        echo "\n";

        //if ($input == null) {
        //    echo "Agent 'make php' says 'Nothing received'";
        //} else {
        //    echo "Agent 'make php' says '" . $input . "' received.";
        //}

        $data = json_decode($file, true);


//        var_dump($data['name']);
//        var_dump($data['version']);
//        var_dump($data['description']);

        $this->engine_string = $data['version'] . " " . $data['description'];

//exit();

    }

    function makeSMS()
    {
        $this->sms_message = "ENGINE | No engine found.";
        $this->sms_message = "ENGINE " . $this->engine_string;

        $this->sms_message .= " | TEXT WARRANTY";
        $this->thing_report['sms'] = $this->sms_message;
    }

	public function readSubject()
    {
		$status = true;
		return $status;
    }

    function makeChoices()
    {
        //$this->node_list = array("web"=>array("iching", "roll"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "engine");
        $choices = $this->thing->choice->makeLinks('engine');

        $this->thing_report['choices'] = $choices;
    }

    public function makePDF()
    {
        $this->thing->report['pdf'] = false;
        return;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'web/' . $this->uuid . '/thing';

        $this->node_list = array("engine"=>array("privacy", "warranty"));

        //$web = '<a href="' . $link . '">';
        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->link_uuid . '/receipt.png">';
        //$web .= "</a>";
        //$web .= "<br>";
        //$web .= '<img src= "https://stackr.ca/thing/' . $this->link_uuid . '/flag.png">';

        $web = "";
//        $web .= '<b>' . ucwords($this->prior_agent) . ' Agent</b><br>';

        //$web .= 'The last agent to run was the ' . ucwords($this->prior_agent) . ' Agent.<br>';

        $web .= 'This Thing said it heard, "' . $this->subject . '".<br>';
        $web .= $this->sms_message . "<br>";
        //$web .= 'About '. $this->thing->created_at;

        $received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( time() - $received_at );
        $web .= "About ". $ago . " ago.";

        $web .= "<br>";
        $this->thing_report['web'] = $web;
    }


}

?>
