<?php
namespace Nrwtaylor\StackAgentThing;

class Test extends Agent {

	public $var = 'hello';


    function __construct(Thing $thing, $text = null)
    {

		$this->agent_name = 'test';
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

    function doTest($input = null)
    {

    }

    function resultTest() 
    {
        if($this->value == $this->expected_value) {echo "Pass  \n";} else {
            echo "Fail  \n";
            echo 'returned $value1'; print_r($this->value); echo '\n';
            echo '$expected_response: '; print_r($this->expected_value); echo '\n';
        }
    }

    function expectedTest($expected_value = null)
    {
        $this->expected_value = $expected_value;
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "test");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

// -----------------------

	public function respond()
    {
		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "test";


        if ($this->agent_input == null) {
            $array = array("I'm here.");
            $k = array_rand($array);
            $v = $array[$k];

            $response = "TEST | " . $v;


            $this->test_message = $response;
        } else {
            $this->test_message = $this->agent_input;
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
        $this->node_list = array("test"=>array("stay","go", "game"));
        $this->sms_message = "" . $this->test_message;
        //if ($this->negative_time < 0) {
        //    $this->sms_message .= " " .$this->thing->human_time($this->negative_time/-1) . ".";
        //}
        $this->thing_report['sms'] = $this->sms_message;

    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "test");
        $choices = $this->thing->choice->makeLinks('test');
        $this->thing_report['choices'] = $choices;
    }


	public function readSubject()
    {

        var_dump( $this->isAgent("asdf"));

        $agent_test = new Agent($this->thing, "agent");
        $agent_test->test();
echo $agent_test->test_result;

        // See if an agent number appears in the subject

        //$input = strtolower($this->subject);
		return false;
    }

}
