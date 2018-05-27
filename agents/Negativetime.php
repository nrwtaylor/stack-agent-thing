<?php
namespace Nrwtaylor\StackAgentThing;

class Negativetime {

	public $var = 'hello';


    function __construct(Thing $thing, $text = null) {


		$this->agent_name = 'negative time';
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
		$this->test= "Development code";

        $this->agent_input = $text;

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

        $this->getNegativetime();

		$this->thing_report = $this->respond();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format( $this->thing->elapsed_runtime() - $this->start_time ) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;

		return;

	}


// -----------------------

    function getNegativetime()
    {

        $this->train_agent = new Train($this->thing, "train"); //negative time is asking
        if (isset($this->train_agent->available)) {
            $this->negative_time = $this->train_agent->available;
        } else {
            $this->negative_time = null;
        }

    }


	private function respond() {


		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "cat";





//        $response = $input . "Try " . strtoupper($v) . ".";


        $this->makeSMS();
        $this->makeChoices();

 		$this->thing_report["info"] = "This is about negative time."; 
 		$this->thing_report["help"] = "This is about needing to track how late a Thing is.";

		//$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;


        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $thing_report['info'] = $message_thing->thing_report['info'] ;
        }

		return $this->thing_report;


	}

    function makeSMS()
    {

        if ($this->agent_input == null) {
            $array = array('Negative time is the time after a Bell.  It is a measure of the total delay to the next bell.');
            $k = array_rand($array);
            $pos = $array[$k];

            $array = array('Negative time is the time after a Bell.  It is a measure of the total advance on the bell.');
            $k = array_rand($array);
            $neg = $array[$k];

            if ($this->negative_time < 0) {
                $response = "NEGATIVE TIME | " . strtolower($pos). " " . $this->thing->human_time( $this->negative_time / -1)."."; 
            } else {
                $response = "NEGATIVE TIME | " . strtolower($neg). " " . $this->thing->human_time( $this->negative_time)."."; 
            }


            $this->cat_message = $response;
        } else {
            $this->cat_message = $this->agent_input;
        }


        $this->node_list = array("cat"=>array("cat","negative time"));
        $this->sms_message = "" . $this->cat_message;
        $this->thing_report['sms'] = $this->sms_message;

    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "cat");
        $choices = $this->thing->choice->makeLinks('cat');
        $this->thing_report['choices'] = $choices;
    }


	public function readSubject()
    {


        //$input = strtolower($this->subject);


		return false;
    }

}



return;
