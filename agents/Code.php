<?php
namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '/var/www/html/stackr.ca/vendor/autoload.php';

class Code
{
	function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = $thing->elapsed_runtime();
        $this->agent_input = $agent_input;

		$this->thing_report['thing'] = false;

		if ($thing->thing != true) {

            $this->thing->log ( 'Agent "Code" ran on a null Thing ' .  $thing->uuid .  '.');
  	        $this->thing_report['info'] = 'Tried to run Code on a null Thing.';
			$this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;
		}

		$this->thing = $thing;
		$this->agent_name = 'code';
        $this->agent_prefix = 'Agent "Code" ';
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

		$this->node_list = array('code'=>array('privacy'),'start a'=>
					array('useful', 'useful?'),
				'start b'=>array('helpful','helpful?')
					);

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;

		$this->thing->log ( 'Agent "Code" running on Thing ' .  $this->uuid . '.' );
		$this->thing->log ( 'Agent "Code" received this Thing "' .  $this->subject .  '".' );

//$this->node_list = array("feedback"=>array("useful"=>array("credit 100","credit 250")), "not helpful"=>array("wrong place", "wrong time"),"feedback2"=>array("awesome","not so awesome"));	


		// If readSubject is true then it has been responded to.
        $this->getLink();

		$this->readSubject();

//        if ($this->agent_input == null) {
		    $this->respond(); // Return $this->thing_report;
//        }


		$this->thing->log( 'Agent "Code" completed' );

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;

		return;
  	}

	public function respond()
    {
		// Thing actions

        $web_thing = new Thing(null);
        $web_thing->Create($this->from, $this->agent_name, 's/ record web view');
        $this->makeSMS();
/*
        if (strtolower($this->prior_agent) == "pdf") {
            $this->sms_message = "PDF | No pdf available.";
        } else {

		$this->sms_message = "PDF | " . $this->web_prefix . "" . $this->link_uuid . "/" . strtolower($this->prior_agent) . ".pdf";
        }


		$this->sms_message .= " | TEXT INFO";
		$this->thing_report['sms'] = $this->sms_message;
*/
		$this->thing->json->setField("variables");
		$this->thing->json->writeVariable(array("code",
			"received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
			);
        $this->makeChoices();

		$this->thing->flagGreen();

		$this->thing_report['info'] = 'This is the code agent.';
		$this->thing_report['help'] = 'This agent takes an UUID and shows the code of the agent run on it.';

        $this->thing->log ( '<pre> Agent "Code" credited 25 to the Thing account.  Balance is now ' .  $this->thing->account['thing']->balance['amount'] . '</pre>');

        if ($this->agent_input == null) {
		    $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }
        $this->makeWeb();
        $this->makePDF();

		return $this->thing_report;
	}

    function makeSMS()
    {
        $this->sms_message = "CODE | No php found.";
        if (strtolower($this->prior_agent) == "php") {
            $this->sms_message = "CODE | No php available.";
        } else {
      //  $agent_class_name = ucwords($this->prior_agent);
      //  $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;
//echo $agent_class_name; 
  //     $agent = new $agent_namespace_name($this->thing);

    //        if (isset($agent->thing_report['php'])) {
                $this->sms_message = "CODE | " . $this->web_prefix . "" . $this->link_uuid . "/" . strtolower($this->prior_agent) . ".php";
      //      }
        }


        $this->sms_message .= " | TEXT INFO";
        $this->thing_report['sms'] = $this->sms_message;


    }

    function getLink()
    {

        $block_things = array();
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, 'thing');

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

        if (!isset($previous_thing->json->array_data['message']['agent'])) {
            $this->prior_agent = "php";
        } else {
            $this->prior_agent = $previous_thing->json->array_data['message']['agent'];
        }

        return $this->link_uuid;
    }

	public function readSubject()
    {
		$this->defaultButtons();

		$status = true;
		return $status;
    }

    function makeChoices()
    {
        //$this->node_list = array("web"=>array("iching", "roll"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "php");
        $choices = $this->thing->choice->makeLinks('php');

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

        $this->node_list = array("web"=>array("iching", "roll"));

        $web = '<a href="' . $link . '">';
        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->link_uuid . '/receipt.png">';
        $web .= "</a>";
        //$web .= "<br>";
        //$web .= '<img src= "https://stackr.ca/thing/' . $this->link_uuid . '/flag.png">';

        $web .= "<br>";
        $web .= '<b>' . ucwords($this->prior_agent) . ' Agent</b><br>';

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

	function defaultButtons()
    {
		if (rand(1,6) <= 3) {
			$this->thing->choice->Create('php', $this->node_list, 'start a');
		} else {
			$this->thing->choice->Create('php', $this->node_list, 'start b');
		}

		//$this->thing->choice->Choose("inside nest");
		$this->thing->flagGreen();

		return;
	}

}

?>
