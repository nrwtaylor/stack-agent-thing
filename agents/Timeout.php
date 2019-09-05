<?php
namespace Nrwtaylor\StackAgentThing;

class Timeout
{
	public $var = 'hello';

    function __construct(Thing $thing, $text = null) {


		$this->agent_name = "timeout";
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


		$this->readSubject();

		$this->thing_report = $this->respond();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format( $this->thing->elapsed_runtime() - $this->start_time ) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;

		return;

	}


// -----------------------

	private function respond() {


		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "timeout";

    $timeout =false;

    if ($timeout) {

        $this->sms_message = "TIMEOUT";

        if ($this->agent_input != null) {
            $this->sms_message .= " | " . $this->agent_input;
        }
        $this->sms_message .= " | " . number_format( $this->thing->elapsed_runtime() ) . "ms.";

			

        $choices = false;

		$this->thing_report[ "choices" ] = $choices;
 		$this->thing_report["info"] = "This stops a query from running too long."; 
 		$this->thing_report["help"] = "This is about processor resource management.";

		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

    } else {

        //$message_thing = new Message($this->thing, $this->thing_report);
        //$this->thing_report['info'] = $message_thing->thing_report['info'] ;


        $d = rand(1,20);




switch (true) {
    case ($d <= 2):
        $cashmessage_thing = new Cashmessage($this->thing);
        $this->thing_report = $cashmessage_thing->thing_report ;
        break;
    case ($d <= 4):
        $hashmessage_thing = new Hashmessage($this->thing);
        $this->thing_report = $hashmessage_thing->thing_report ;
        break;


    case ($d <= 20):
        //$message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = 'No message sent.' ;
        // null
        break;
    default:
}


}

//        $cashmessage_thing = new Cashmessage($this->thing);
//        $this->thing_report = $cashmessage_thing->thing_report ;







		return $this->thing_report;


	}



	public function readSubject()
    {


        //$input = strtolower($this->subject);


		return false;
    }

}

