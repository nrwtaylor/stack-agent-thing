<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Api {

	function __construct(Thing $thing)
    {
		$this->thing_report['thing'] = false;

		if ($thing->thing != true) {

            $this->thing->log ( 'Agent "Web" ran on a null Thing ' .  $thing->uuid .  '');
  	        $this->thing_report['info'] = 'Tried to run Web on a null Thing.';
			$this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;
		}

		$this->thing = $thing;
		$this->agent_name = 'api';
        $this->agent_prefix = 'Agent "api" ';
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


		$this->node_list = array('start a'=>
					array('useful', 'useful?'),
				'start b'=>array('helpful','helpful?')
					);

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;

		$this->thing->log ( '<pre> Agent "Api" running on Thing ' .  $this->uuid . '</pre>' );
		$this->thing->log ( '<pre> Agent "Api" received this Thing "' .  $this->subject .  '"</pre>' );

//$this->node_list = array("feedback"=>array("useful"=>array("credit 100","credit 250")), "not helpful"=>array("wrong place", "wrong time"),"feedback2"=>array("awesome","not so awesome"));	


		// If readSubject is true then it has been responded to.

        $this->getLink();

		$this->readSubject();
		$this->respond(); // Return $this->thing_report;


		$this->thing->log( '<pre> Agent "Api" completed</pre>' );

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;


		return;
	}

	public function respond()
    {
		// Thing actions

        $web_thing = new Thing(null);
        //$web_thing->Create($this->from, 'ant' , 's/ web view ' . $web_thing->uuid);
        $web_thing->Create($this->from, $this->agent_name, 's/ record web view');

		//$this->sms_message = "WEB | " . $this->web_prefix . "thing/" . $this->link_uuid;
        $this->sms_message = "API | " . $this->web_prefix . "api/redpanda/thing/" . $this->link_uuid;

		$this->sms_message .= " | TEXT WHATIS";
		$this->thing_report['sms'] = $this->sms_message;


		$this->thing->json->setField("variables");
		$this->thing->json->writeVariable(array("api",
			"received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
			);


//        $this->makeWeb();

		$this->thing->flagGreen();

		//$choices = $this->thing->choice->makeLinks('start a');
        // Account for web views.
        // A Credit to Things account
        // And a debit from the Stack account.  Withdrawal.
        //$this->thing->account['thing']->Credit(25);
		//$this->thing->account['stack']->Debit(25);

//        $choices = $this->thing->choice->makeLinks('start a');

        $choices = false;

		$this->thing_report['choices'] = $choices;
		$this->thing_report['info'] = 'This is the api agent.';
		$this->thing_report['help'] = 'This agent creates a web link to the API.';

        $this->thing->log ( '<pre> Agent "Web" credited 25 to the Thing account.  Balance is now ' .  $this->thing->account['thing']->balance['amount'] . '</pre>');

		$message_thing = new Message($this->thing, $this->thing_report);

        $this->makeWeb();


        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

//        $this->thing_report['etime'] = "meep";

		return $this->thing_report;
	}
    function getLink() {

        $block_things = array();
        // See if a block record exists.
        //require_once '/var/www/html/stackr.ca/agents/findagent.php';
        $findagent_thing = new Findagent($this->thing, 'thing');

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

//$this->thing->log('Agent "Block" found ' . count($findagent_thing->thing_report['things']) ." Block Things.");

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

            $variables_json= $block_thing['variables'];
            $variables = $this->thing->json->jsontoArray($variables_json);


        //require_once '/var/www/html/stackr.ca/agents/variables.php';

        //$previous_thing = new Thing($block_thing['uuid']);

        //$this->agent = new Variables($message_thing, "variables message " . $this->from);


        if (!isset($variables['message']['agent'])) {
            $this->prior_agent = "api";
        } else {
            $this->prior_agent = $variables['message']['agent'];
        }

        return $this->link_uuid;
    }

	public function readSubject()
    {

		$this->defaultButtons();

		$status = true;
		return $status;		
	}

    function makeWeb() {

        $link = $this->web_prefix . 'api/redpanda/thing/' . $this->link_uuid;

      $this->node_list = array("api"=>array("iching", "roll"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "api");
        $choices = $this->thing->choice->makeLinks('api');
        $web = "";
/*
        $web = '<a href="' . $link . '">';
        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->link_uuid . '/receipt.png">';
        $web .= "</a>";
*/
        $web .= "<br>";

        $web .= $this->subject;
        $web .= "<br>";
        $web .= $this->sms_message;

        $web .= "<br><br>";


        $web .= "<br>";
/*  
      $web .= $head;
        $web .= $choices['button'];
        $web .= $foot;
//        $web .= $this->thing_report['channel'];

//echo        $this->thing->account['thing']->balance['amount'];
  //  echo    $this->thing->account['stack']->balance['amount'];
*/

        $this->thing_report['web'] = $web;

    }

	function defaultButtons() {

//$html_links = $this->thing->choice->makeLinks();


		if (rand(1,6) <= 3) {
			$this->thing->choice->Create('web', $this->node_list, 'start a');
		} else {
			$this->thing->choice->Create('web', $this->node_list, 'start b');
		}

		//$this->thing->choice->Choose("inside nest");
		$this->thing->flagGreen();

		return;
	}



}


?>
