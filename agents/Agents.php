<?php
namespace Nrwtaylor\StackAgentThing;

class Agents
{
	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = $thing->elapsed_runtime();
        $this->agent_input = $agent_input;

        $this->start_time = microtime(true);

		$this->agent_name = "agents";
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
		$this->test= "Development code";

		$this->thing = $thing;
        $this->thing_report['thing']  = $thing;

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log($this->agent_prefix . "received this Thing ".  $this->subject . '".');

        $this->getAgencies();

		$this->readSubject();

		$this->respond();

        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;


		return;

	}

    function getAgencies()
    {

        $this->agencies = array();

        // Only use Stackr agents for now
        $dir    = $GLOBALS['stack_path'] . 'vendor/nrwtaylor/stack-agent-thing/agents'; 
        $files = scandir($dir);

        foreach ($files as $key=>$file) {
            if ($file[0] == "_") {continue;}
            if ( strtolower(substr($file, 0, 3)) == "dev") {continue;}
            if ( strtolower(substr($file, -4)) != ".php") {continue;}
            if (!ctype_upper($file[0])) {continue;}

            $agent_name = substr($file, 0, -4);
            $this->agencies[] =  ucwords($agent_name);
        }

        $this->agent_names = $this->agencies;

    }

// -----------------------

	private function respond()
    {

		$this->thing->flagGreen();


		$to = $this->thing->from;
		$from = "agents";

        $s = "AGENTS | ";
        $rand_keys = array_rand($this->agencies, 3);
        $s .= $this->agencies[$rand_keys[0]] . " ";
        $s .= $this->agencies[$rand_keys[1]] . " ";
        $s .= $this->agencies[$rand_keys[2]];

        $this->sms_message = $s;

        $choices = false;

		$this->thing_report[ "choices" ] = $choices;

        //$this->thing_report["agency"] = "Prepare a list of stack agents."; 

 		$this->thing_report["info"] = "This shares what agents the stack has."; 
 		$this->thing_report["help"] = "This gives a list of the Agents available to the Stack.";

		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->makeWeb();

		return $this->thing_report;
	}

    function makeWeb()
    {
        $w = '<b>Agents</b>';
        foreach ($this->agencies as $key=>$agent) {
        $w .= "<br>" . $agent;
        }
        $this->thing_report['web'] = $w;
    }

	public function readSubject()
    {
		return false;
    }

}
