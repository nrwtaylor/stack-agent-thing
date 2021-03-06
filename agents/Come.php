<?php
namespace Nrwtaylor\StackAgentThing;

class Come {

	public $var = 'hello';


    function __construct(Thing $thing, $text = null)
    {

		$this->agent_name = 'come';
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

        //$this->getNegativetime();

		$this->thing_report = $this->respond();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format( $this->thing->elapsed_runtime() - $this->start_time ) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;

		return;

	}


    function getNegativetime()
    {

        $agent = new Negativetime($this->thing, "come");
        $this->negative_time = $agent->negative_time; //negative time is asking

    }

// -----------------------

	private function respond()
    {
		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "come";


        if ($this->agent_input == null) {
            $array = array("I'm here.");
            $k = array_rand($array);
            $v = $array[$k];

            $response = "COME | " . $v;


            $this->come_message = $response;
        } else {
            $this->come_message = $this->agent_input;
        }

        $this->makeSMS();
        $this->makeChoices();

 		$this->thing_report["info"] = "This is saying you are here, when someone needs you."; 
 		$this->thing_report["help"] = "This is about being very consistent.";

		//$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

		return $this->thing_report;
	}

    function makeSMS()
    {
        $this->node_list = array("come"=>array("stay","go", "game"));
        $this->sms_message = "" . $this->come_message;
        //if ($this->negative_time < 0) {
        //    $this->sms_message .= " " .$this->thing->human_time($this->negative_time/-1) . ".";
        //}
        $this->thing_report['sms'] = $this->sms_message;

    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "come");
        $choices = $this->thing->choice->makeLinks('come');
        $this->thing_report['choices'] = $choices;
    }


	public function readSubject()
    {
        //$input = strtolower($this->subject);
		return false;
    }

}
