<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Persistence
{

	function __construct(Thing $thing, $agent_input = null)
    {

    	$this->agent_input = $agent_input;

		// Given a "thing".  Instantiate a class to identify and create the
		// most appropriate agent to respond to it.
 		$this->thing = $thing;
        $this->thing_report['thing'] = $thing;

	    // Get some stuff from the stack which will be helpful.
		$this->web_prefix = $thing->container['stack']['web_prefix'];
		$this->stack_state = $thing->container['stack']['state'];
		$this->short_name = $thing->container['stack']['short_name'];
        $this->persist_for = $thing->container['stack']['persist_for'];

		// Create some short-cuts.
		$this->uuid = $thing->uuid;
		$this->nuuid = $thing->nuuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		//$this->sqlresponse = null;

		// Before doing anything else
		$this->thing->json->setField("variables");
        $this->remember_status = $this->thing->json->readVariable( array("remember", "status") );


        if ( ($this->remember_status == true) ) {
            $this->thing->log( '<pre> Agent "Retention" found a record flagged for Remember </pre>' );
            //$this->setRemember();
        } else {
            $this->created_at =  strtotime($thing->thing->created_at);

            $dteStart = time();

            // Provide for translation to stack time unit
                if  ($this->persist_for['unit'] == 'hours') {
                $age = $this->persist_for['amount'] * (60*60);
        }

        if  ($this->persist_for['unit'] == 'days') {
            $age = $this->persist_for['amount'] * (24*60*60);
        }

        $this->persist_to = $dteStart + $age;

        $this->thing->json->setField("variables");
        $variables = $this->thing->json->read();
        $this->refreshed_at = 0;

		foreach ($variables as $key=>$variable) {

		if (  isset( $variable['refreshed_at'] )) {
    		$dte = strtotime ( $variable['refreshed_at'] );

    		if ($dte > $this->refreshed_at) {$this->refreshed_at = $dte;}

		}
    }

    if ($this->refreshed_at == 0) { $this->refreshed_at = $this->created_at; }

    $this->time_remaining = $this->persist_to - $this->refreshed_at;

    }

		$this->thing->log('<pre> Agent "Retention" started running on Thing ' . date("Y-m-d H:i:s") . '</pre>');
		$this->node_list = array("start"=>
						array("useful","useful?"));

		$this->aliases = array("destroy"=>array("delete"));


                $this->thing->json->setField("variables");
                $this->thing->json->writeVariable(array("persistence",
                        "persist_to"),  $this->thing->json->time($this->persist_to)
                        );


    if ($this->agent_input == null) {
            $this->respond();
        }


        return;

	}

	public function respond()
    {
		// Thing actions

		$this->thing->json->setField("variables");
		$this->thing->json->writeVariable(array("persistence",
			"persist_to"),  $this->thing->json->time($this->persist_to)
			);


		$this->thing->flagGreen();

		$from = $this->from;
		$to = $this->to;

		//echo "from",$from,"to",$to;

		$subject = $this->subject;

		// Now passed by Thing object
		$uuid = $this->uuid;
		$sqlresponse = "yes";

        $message = "Thank you $from this was PERSISTENCE";

        $this->sms_message = "PERSISTENCE | ";
        $this->sms_message .= "Thing " . $this->thing->nuuid . " will persist for " . $this->thing->human_time($this->time_remaining) . ".";
        $this->sms_message .= ' | TEXT PRIVACY';

        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;

        $this->thing_report['choices'] = false;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->makeWeb();

		$this->thing_report['thing'] = $this->thing->thing;

		return;
	}

    public function makeWeb()
    {

        $w = '<b>Persistence Agent</b><br>';
        $w .= "This agent sets how long a Thing is on the stack after the last refresh.<br>";
        $w .= "persist for " . $this->persist_for['amount'] ." " .$this->persist_for['unit'] ."<br>";
        $w.= "created at " . strtoupper(date('Y M d D H:i',$this->created_at)). "<br>";
        $w.= "refreshed at " . strtoupper(date('Y M d D H:i',$this->refreshed_at)). "<br>";
        $w.= "persist to " . strtoupper(date('Y M d D H:i',$this->persist_to)) . "<br>";
        $w.= "time remaining is " . $this->thing->human_time($this->time_remaining) . "<br>";
        // $w.= "<br>" . $this->age;
        // $w .= $this->retain_to;
        // $w .= ' | TEXT ?';

        $this->web_message = $w;
        $this->thing_report['web'] = $w;

    }

    public function readSubject()
    {
        $status = true;
        return $status;
    }

}

?>
