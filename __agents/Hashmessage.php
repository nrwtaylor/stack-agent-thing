<?php
namespace Nrwtaylor\StackAgentThing;


class Hashmessage {

	public $var = 'hello';


    function __construct(Thing $thing, $text = null) {


		$this->agent_name = 'hashmessage';
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
		$from = "hashmessage";


        if ($this->agent_input == null) {
            $this->hash_message = "#hashmessage";
        } else {
            $this->hash_message = $this->agent_input;
        }
        //$this->sms_message = "TIMEOUT";

//        if ($this->agent_input != null) {
            $this->sms_message = "" . $this->hash_message;
//        }

//        $this->sms_message .= " | " . number_format( $this->thing->elapsed_runtime() ) . "ms.";

			

        $choices = false;

		$this->thing_report[ "choices" ] = $choices;
 		$this->thing_report["info"] = "This creates a hashtag message."; 
 		$this->thing_report["help"] = "This is about informational message injection.";

		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;


        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;


		return $this->thing_report;


	}
/*
    function extractRoll($input) {

//echo $input;
//exit();

preg_match('/^(\\d)?d(\\d)(\\+\\d)?$/',$input,$matches);

print_r($matches);

$t = preg_filter('/^(\\d)?d(\\d)(\\+\\d)?$/',
                '$a="$1"? : 1;for(; $i++<$a; $s+=rand(1,$2) );echo$s$3;',
                $input)?:'echo"Invalid input";';


    }
*/



	public function readSubject()
    {


        //$input = strtolower($this->subject);


		return false;
    }

}



return;
