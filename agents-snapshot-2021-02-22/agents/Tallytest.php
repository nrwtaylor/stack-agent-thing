<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);ini_set('display_errors', 1);

class Tallytest
{

    // So Tally just increments a variable and keeps going past 0.
    // limit:5 => 1, 2, 3, 4, 5, 1, 2, 3, 4, 5, 1, 2 ...
    // And that is what this does.

    // If an Agent gives it a command, it will set up the 
    // parameters of the Tally, which by default are:
    //   tally /  5   / mordok  /  tally@stackr.ca
    
    //   tally <tally_limit> <agent> <identity> ie
    // a tally of 5 for mordok for tally@stackr.ca

    // Without an agent instruction, tally
    // return the calling identities self-tally.

    //   tally /  5   / thing  /   $this->from

	function __construct(Thing $thing, $agent_command = null) {

        $this->start_time = microtime(true);

        // Setup Thing
        $this->thing = $thing;
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        // Setup Agent
        $this->agent = strtolower(get_class());
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent) . '" ';

        // Setup logging
        $this->thing_report['thing'] = $this->thing->thing;

        if ($agent_command == null) {
        } else {
        }

        $agent_command = null;
        $this->agent_command = $agent_command;
        $this->nom_input = $agent_command . " " . $this->from . " " . $this->subject;

        $this->readInput();


		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

     	$this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
    	$this->subject = $thing->subject;
		//$this->sqlresponse = null;

		$this->node_list = array("start");

		$this->thing->log( '<pre> ' .$this->agent_prefix . ' running on Thing ' .  $this->thing->nuuid .  ' </pre>' );

        // Not sure this is limiting.
        //$this->getVariables();

		$this->readText();

//        $this->thing->log( 'Flag Potentially Nominal - Agent "Tally" processed "' . $this->nom_input . '".' );


//        $this->addTally();


$command = "tally 2 binary tally@stackr.ca";
$this->tally = new Tally($this->thing, $command);

$this->function_message = $command . " was called";
		$this->Respond();


        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( 'Agent "Tally" ran for ' . $milliseconds . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;
		return;
	}

    function getAgent() 
    {
        return;
    }


	public function Respond() {

		// Develop the various messages for each channel.

		// Thing actions
		// Because we are making a decision and moving on.  This Thing
		// can be left alone until called on next.
		$this->thing->flagGreen(); 


        $this->thing->log( 'Agent "Tally" variable is ' . $this->tally->variables_thing->variable . '.' );

		$this->sms_message = "TALLY TEST";
        //$this->sms_message .= " | was called";

        //$this->sms_message .= " | nuuid " . substr($this->variables_thing->next_uuid, 0 ,4);

        if (isset($this->function_message)) {
            $this->sms_message .= " | " . $this->function_message;
        }

        if (isset($this->tally->variables_thing->variable)) {
            $this->sms_message .= " | " . $this->tally->variables_thing->variable;
        }

		$this->sms_message .= ' | TEXT ?';

		$this->thing_report['thing'] = $this->thing->thing;
		$this->thing_report['sms'] = $this->sms_message;


		// While we work on this
		$this->thing_report['email'] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);



		return $this->thing_report;
	}


    public function defaultCommand() 
    {
        $this->agent = "tally";
        $this->limit = 5;
        $this->name = "thing";
        $this->identity = $this->from;
        return;
    }


    public function readInstruction() 
    {
        if($this->nom_input == null) {
            $this->defaultCommand();
            return;
        }

        $pieces = explode(" ", strtolower($this->nom_input));

        $this->agent = $pieces[0];
        $this->limit = $pieces[1];
        $this->name = $pieces[2];
        if (isset($pieces[3])) {
            $this->identity = $pieces[3];
        } else {
            $this->identity = "X";
        }

        if (!isset($pieces[4])) {
            $this->index = 0;
        } else {        
            $this->index = $pieces[4];
        }


        $this->thing->log( 'Agent "Tally" read the instruction and got ' . $this->agent . ' ' . $this->limit . ' ' . $this->name . ' ' . $this->identity . "." );

        return;

    }




	public function readText() {

        // No need to read text.  Any identity input to Tally
        // increments the tally.
     
        return;
	}

    public function readInput() {
        $this->readInstruction();
        $this->readText();
        return;
    }


}

?>
