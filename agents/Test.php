<?php
namespace Nrwtaylor\StackAgentThing;

class Test extends Agent
{
    public $var = 'hello';

    public function set()
    {
        $this->response = true;
    }

    function init()
    {
        $this->thing_report["info"] =
            "This is saying you are here, when someone needs you.";
        $this->thing_report["help"] = "This is about being very consistent.";
    }

    function doTest($input = null)
    {
    }

    function resultTest()
    {
        if ($this->value == $this->expected_value) {
            echo "Pass  \n";
        } else {
            echo "Fail  \n";
            echo 'returned $value1';
            print_r($this->value);
            echo '\n';
            echo '$expected_response: ';
            print_r($this->expected_value);
            echo '\n';
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

    public function respondResponse()
    {
        $this->thing->flagGreen();

        //		$to = $this->thing->from;
        //		$from = "test";

        $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];
    }

    function run()
    {
    }

    function makeSMS()
    {
        $this->node_list = ["test" => ["stay", "go", "game"]];
        $this->sms_message = "TEST | " . $this->response;
        //if ($this->negative_time < 0) {
        //    $this->sms_message .= " " .$this->thing->human_time($this->negative_time/-1) . ".";
        //}
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        //        $this->thing->choice->Create('channel', $this->node_list, "test");
        //        $choices = $this->thing->choice->makeLinks('test');
        //        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        $input = $this->input;
        //$this->extractAgents($input);
        //var_dump($this->responsive_agents);

        //echo "test readsibject";

        //$input = $this->input;

        //$t = $this->getCallingagent();
        //var_dump($t);

        if ($input == "test") {
            $this->response .= "I'm here. ";
            return;
        }

        //devstack
        //if (!isset($this->agents)) {
        //$this->extractAgents($input);
        //}
        //var_dump($this->agents);
    }
}
