<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Info {

	function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = $thing->elapsed_runtime(); 

        $this->agent_input = $agent_input;
		$this->thing_report['thing'] = false;

		if ($thing->thing != true) {

            $this->thing->log ( 'Agent "Info" ran on a null Thing ' .  $thing->uuid .  '');
  	        $this->thing_report['info'] = 'Tried to run Info on a null Thing.';
			$this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;
		}

		$this->thing = $thing;
		$this->agent_name = 'info';
        $this->agent_prefix = 'Agent "Info" ';
		$this->agent_version = 'redpanda';

		$this->thing_report = array('thing' => $this->thing->thing);

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

		$this->node_list = array('info'=>array('privacy', 'whatis'),'start a'=>
					array('useful', 'useful?'),
				'start b'=>array('helpful','helpful?')
					);

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;

		$this->thing->log ( 'Agent "Info" running on Thing ' .  $this->uuid . '' );
		$this->thing->log ( 'Agent "Info" received this Thing "' .  $this->subject .  '"' );

//$this->node_list = array("feedback"=>array("useful"=>array("credit 100","credit 250")), "not helpful"=>array("wrong place", "wrong time"),"feedback2"=>array("awesome","not so awesome"));	


		// If readSubject is true then it has been responded to.

        $this->getLink();

		$this->readSubject();
		$this->respond(); // Return $this->thing_report;


		$this->thing->log( '<pre> Agent "Web" completed</pre>' );

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;

        $this->thing_report['info'] = "meep";

		return;
	}

    public function getInfo()
    {
        if (strtolower($this->prior_agent) == "info") {
            $this->info = "This provides information from the agents called by this agent.";
            $this->help = "Asks the Agent for information.";
            $this->thing_report['info'] = "Asks the Agent for info.";
            return;
        }

        try {
           $this->thing->log( $this->agent_prefix .'trying Agent "' . $this->prior_agent . '".', "INFORMATION" );
           $agent_class_name = ucwords(strtolower($this->prior_agent));
           $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;

           $agent = new $agent_namespace_name($this->prior_thing);
            $this->info = $agent->thing_report['info'];
           //$thing_report = $agent->thing_report;

        } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptions.
           $this->thing->log( $this->agent_prefix .'borked on "' . $agent_class_name . '".', "WARNING" );
           $message = $ex->getMessage();
           //$code = $ex->getCode();
           $file = $ex->getFile();
           $line = $ex->getLine();

           $input = $message . '  ' . $file . ' line:' . $line;

           // This is an error in the Place, so Bork and move onto the next context.
           $agent = new Bork($this->thing, $input);
           //continue;
            $this->info = $input;
        }

        $this->thing_report['info'] = $this->info;
    }

    public function makeSMS()
    {
        if (!isset($this->info)) {$this->getInfo();}

        $this->sms_message = "INFO | " . ucwords($this->prior_agent) . " | " . $this->info;
        $this->sms_message .= " | TEXT WHATIS";
        $this->thing_report['sms'] = $this->sms_message;

    }

	public function respond()
    {
		// Thing actions

        $this->makeSMS();

		$this->thing->json->setField("variables");
		$this->thing->json->writeVariable(array("info",
			"received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
			);

        $this->makeChoices();

		$this->thing->flagGreen();

		$this->thing_report['info'] = 'This is the info agent.';
		$this->thing_report['help'] = 'This agent takes a Thing and runs the Info agent on it.';

        if ($this->agent_input == null) {
		    $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->makeWeb();

		return $this->thing_report;
	}

    function getLink()
    {

        $block_things = array();
        // See if a block record exists.
        $findagent_thing = new FindAgent($this->thing, 'thing');

        $this->max_index =0;

        $match = 0;

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {

            $this->thing->log($block_thing['task'] . " " . $block_thing['nom_to'] . " " . $block_thing['nom_from']);

            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                if ($match == 2) {break;}
            }
        }


        $previous_thing = new Thing($block_thing['uuid']);
        $this->prior_thing = $previous_thing;
        if (!isset($previous_thing->json->array_data['message']['agent'])) {
            $this->prior_agent = "info";
        } else {
            $this->prior_agent = $previous_thing->json->array_data['message']['agent'];
        }

        return $this->link_uuid;
    
    }




	public function readSubject() {

//		$this->defaultButtons();
        $this->getInfo();
        //$this->getHelp2();
		$status = true;
		return $status;
    }

    function makeChoices()
    {

        //$this->node_list = array("web"=>array("iching", "roll"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "info");
        $choices = $this->thing->choice->makeLinks('info');

        $this->thing_report['choices'] = $choices;

    }

    function makeWeb() {

        $link = $this->web_prefix . 'web/' . $this->uuid . '/thing';

        $this->node_list = array("web"=>array("iching", "roll"));
        // Make buttons
        //$this->thing->choice->Create($this->agent_name, $this->node_list, "web");
        //$choices = $this->thing->choice->makeLinks('web');



        $web = '<a href="' . $link . '">';
        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->link_uuid . '/receipt.png">';
        $web .= "</a>";

        $web .= "<br>";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        $web .= 'The last agent to run was the ' . ucwords($this->prior_agent) . ' Agent.<br>';

        $web .= "<br>";

        $web .= $this->info . "<br>";

        $this->thing_report['web'] = $web;


    }



}


?>
