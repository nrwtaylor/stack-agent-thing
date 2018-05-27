<?php

namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Input {

	public $var = 'hello';


    function __construct(Thing $thing, $text = null) {


		$this->agent_name = 'input';
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
		$this->test= "Development code";

        $this->agent_input = $text;

//      This is how old roll.php is.
//		$thingy = $thing->thing;
		$this->thing = $thing;

        $this->thing_report  = array("thing"=>$this->thing->thing);
        $this->start_time = $this->thing->elapsed_runtime();



        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;


        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log($this->agent_prefix . "received this Thing ".  $this->subject . '".');

        $this->variables_agent = new Variables($this->thing, "variables " . "input " . $this->from);
        $this->current_time = $this->thing->json->time();


        //$this->damage_budget = 1000;
        //$this->time_budget = 10000; //ms
        //$this->shell_impact = 50;
        //$this->shell_cost = 50;

        //$this->value_destroyed = $this->doDamage();

        //$this->Set();
        if ($this->agent_input != null) {
            $this->readInstruction();
        }


        if ($this->agent_input == null) {
            $this->Set();
		    $this->readSubject();
        }

		$this->respond();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format( $this->thing->elapsed_runtime() - $this->start_time ) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;

		return;

	}


// -----------------------

    function Set()
    {
        //$this->variables_agent->setVariable("value_destroyed", $this->value_destroyed);

        //$this->variables_agent->setVariable("things_destroyed", $this->things_destroyed);

        //$this->thing->setVariable("damage_cost", $this->damage_cost);

        $this->variables_agent->setVariable("uuid", null);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

    }

    function makeSMS()
    {
        if ($this->state == true) {
            $sms = "INPUT | ?";
        }

        if ($this->state == false) {
            $sms = "INPUT | " . $this->subject;

        }

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;

    }


	private function respond() {


		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "input";





//        $response = $input . "Try " . strtoupper($v) . ".";


        $this->makeSMS();
      //  if ($this->agent_input == null) {
      //      //$array = array('miao','miaou','hiss','prrr', 'grrr');
      //      //$k = array_rand($array);
      //      //$v = $array[$k];

      //      $response = "INPUT | ?";


      //      $this->cat_message = $response;
      //  } else {
      //      $this->cat_message = $this->agent_input;
      //  }
        //$this->sms_message = "TIMEOUT";

//        if ($this->agent_input != null) {
      //      $this->sms_message = "" . $this->cat_message;
//        }

//        $this->sms_message .= " | " . number_format( $this->thing->elapsed_runtime() ) . "ms.";

        $choices = false;

		$this->thing_report[ "choices" ] = $choices;
 		$this->thing_report["info"] = "This makes an input thing."; 
 		$this->thing_report["help"] = "This is about input variables.";

		//$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

		return $this->thing_report;


	}




	public function readSubject()
    {


        //$input = strtolower($this->subject);
       //$this->getInput();

		return false;
    }

    public function readInstruction()
    {


        //$input = strtolower($this->subject);
       $this->getInput();

        return false;
    }



    function getInput() {

        $block_things = array();
        // See if a block record exists.
        $findagent_thing = new FindAgent($this->thing, 'thing');

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

                $thing= new Thing($this->link_uuid);
                $variables = $thing->account['stack']->json->array_data;
//                $input_uuid = null;

                if ((isset($variables['input'])) and ($match == 2)) {
//                    if (!isset($input_uuid = $variables['input']['uuid'])) {
                        break;
//                    }
                }



                //if ($match == 2) {break;}
            }
        }

        echo "meep";

        $input_uuid = $variables['input']['uuid'];

        if ($input_uuid == null) {
            // This is input
            $this->variables_agent = new Variables($thing, "variables " . "input " . $this->from);
            $this->variables_agent->setVariable("uuid", $this->uuid);

            $this->state = false;
        } else {
            $this->state = true;
            // This isn't input
        }




        return $this->link_uuid;
    
    }



}



return;
