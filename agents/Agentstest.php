<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// Canadian Hydrographic Service
class Agentstest extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->keyword = "environment";
        $this->agent_prefix = 'Agent "Weather" ';
        $this->keywords = array('agents', 'test', 'unit test');
        if ($this->verbosity == false) {$this->verbosity = 2;}
        $this->getAgentsTest();
    }

    function getAgentsTest()
    {
        $this->test = new Agents($this->thing, "agents test");
        //$this->thing = $agent->thing;
    }

	public function respond()
    {

		// Thing actions
		$this->thing->flagGreen();
		// Generate email response.

		$to = $this->thing->from;
		$from = "agent"; //sure

        $choices = false;
		$this->thing_report['choices'] = $choices;

        $this->makeSms();
//        $this->makeMessage();

        $this->thing_report['email'] = $this->sms_message;
        //$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;
        $this->thing_report['txt'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->makeWeb();

        $this->thing_report['help'] = 'This reads a web resource.';
		return;
	}

    public function makeWeb()
    {
        $web = "<b>Agents</b>";

        $ago = $this->thing->human_time ( time() - strtotime($this->refreshed_at) );

        $web .= "CHS feed last queried " . $ago .  " ago.<br>";

        //$this->sms_message = $sms_message;
        $this->thing_report['web'] = $web;

    }

    public function makeSms()
    {

//var_dump($this->test->test_results);

        if (!isset($this->test->test_results)) {$this->response = "No test results available.";}
//var_dump($this->test->test_results);
$test_text = "results ";
foreach ($this->test->test_results as $index=>$test_result) {

$test_text .= $test_result["agent_name"] . " " . strtoupper($test_result["flag"]) . " ";
echo  $test_result["agent_name"] . " " . $test_result["flag"] . $test_result["error"] . ".\n";
}


$this->response = $test_text;

        $sms_message = "AGENTS TEST | " . null;
        $sms_message .= $this->response;
//        $sms_message .= " | link " . $this->link;

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }

    function readSubject() {}

}
