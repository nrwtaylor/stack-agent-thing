<?php
namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Pdf extends Agent
{
    function init()
    {
		if ($this->thing->thing != true) {

            $this->thing->log ( 'Agent "Pdf" ran on a null Thing ' .  $thing->uuid .  '.');
  	        $this->thing_report['info'] = 'Tried to run Pdf on a null Thing.';
			$this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;
		}

		$this->agent_version = 'redpanda';

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

		$this->node_list = array('pdf'=>array('privacy'),'start a'=>
					array('useful', 'useful?'),
				'start b'=>array('helpful','helpful?')
					);
  	}

    public function run()
    {
        $this->getLink();

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


		$this->sms_message .= " | TEXT INFO";
		$this->thing_report['sms'] = $this->sms_message;
*/
		$this->thing->json->setField("variables");
		$this->thing->json->writeVariable(array("pdf",
			"received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
			);

        $this->makeChoices();

		$this->thing->flagGreen();

		$this->thing_report['info'] = 'This is the pdf agent.';
		$this->thing_report['help'] = 'This agent takes an UUID and runs the pdf agent on it.';

        $this->thing->log ( '<pre> Agent "Pdf" credited 25 to the Thing account.  Balance is now ' .  $this->thing->account['thing']->balance['amount'] . '</pre>');

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
        $this->sms_message = "PDF | No pdf found";
        if (strtolower($this->prior_agent) == "pdf") {
            $this->sms_message = "PDF | No pdf available.";
        } else {

        // prod 27 July 2018
        //$agent_class_name = ucwords($this->prior_agent);
        //$agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;
        //$agent = new {$agent_namespace_name}($this->thing);

//var_dump($agent->thing_report['pdf']);

//            if (isset($agent->thing_report['pdf'])) {
                $this->sms_message = "PDF | " . $this->web_prefix . "" . $this->link_uuid . "/" . strtolower($this->prior_agent) . ".pdf";
//            }
        }

//var_dump($this->thing_report['pdf']);

        if (!$this->pdf_exists) {$this->sms_message = "PDF | No PDF available from the last agent.";}


        $this->sms_message .= " | TEXT INFO";
        $this->thing_report['sms'] = $this->sms_message;


    }

    function getLink($variable = null)
    {

        $block_things = array();
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, 'thing');

        $this->max_index =0;

        $match = 0;

        $link_uuids = array();

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {

            $this->thing->log($block_thing['task'] . " " . $block_thing['nom_to'] . " " . $block_thing['nom_from']);

            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                $link_uuids[] = $block_thing['uuid'];
                // if ($match == 2) {break;}
                // Get upto 10 matches
                if ($match == 10) {break;}


            }
        }
        $this->prior_agent = "pdf";
        foreach($link_uuids as $key=>$link_uuid) {
            $previous_thing = new Thing($link_uuid);

            if (isset($previous_thing->json->array_data['message']['agent'])) {

                $this->prior_agent = $previous_thing->json->array_data['message']['agent'];

                if (in_array(strtolower($this->prior_agent), array('web','pdf','txt','log','php'))) {
                    continue;
                }

                $this->link_uuid = $link_uuid;
                break;
            }
        }

        $previous_thing->silenceOn();
        $quiet_thing = new Quiet($previous_thing, "on");


        $this->pdf_exists = true;
        $agent_thing = new Agent($previous_thing);
//var_dump($agent_thing->thing_report['pdf']);
        if (!isset($agent_thing->thing_report['pdf'] )) {$this->pdf_exists = false;}

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
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "pdf");
        $choices = $this->thing->choice->makeLinks('pdf');

        $this->thing_report['choices'] = $choices;
    }

    public function makePDF()
    {
        $this->thing->report['pdf'] = false;
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

        $received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( time() - $received_at );
        $web .= "About ". $ago . " ago.";

        $web .= "<br>";
        $this->thing_report['web'] = $web;
    }

	function defaultButtons()
    {
		if (rand(1,6) <= 3) {
			$this->thing->choice->Create('pdf', $this->node_list, 'start a');
		} else {
			$this->thing->choice->Create('pdf', $this->node_list, 'start b');
		}
		$this->thing->flagGreen();
	}

}
