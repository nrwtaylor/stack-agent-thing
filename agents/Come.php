<?php
namespace Nrwtaylor\StackAgentThing;

class Come extends Agent {

	public $var = 'hello';

    function init() {
	}


    public function readCome() {

        if ($this->agent_input == null) {
            $array = array("I'm here.");
            $k = array_rand($array);
            $v = $array[$k];

            $response = "COME | " . $v;


            $this->come_message = $response;
        } else {
            $this->come_message = $this->agent_input;
        }

    }

	public function respondResponse()
    {
		$this->thing->flagGreen();

        if ($this->agent_input == null) {
            $array = array("I'm here.");
            $k = array_rand($array);
            $v = $array[$k];

            $response = "COME | " . $v;


            $this->come_message = $response;
        } else {
            $this->come_message = $this->agent_input;
        }

 		$this->thing_report["info"] = "This is saying you are here, when someone needs you."; 
 		$this->thing_report["help"] = "This is about being very consistent.";

		$this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

	}

    function makeSMS()
    {
        $this->node_list = array("come"=>array("stay","go", "game"));
        $this->sms_message = "" . $this->come_message;

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
        $this->readCome();
    }

}
