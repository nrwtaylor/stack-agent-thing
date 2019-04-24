<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// Canadian Hydrographic Service
class Agentstest extends Agent
{
    // SOAP needs enabling in PHP.ini

    // https://www.waterlevels.gc.ca/docs/Specifications%20-%20Spine%20observation%20and%20predictions%202.0.3(en).pdf

    // https://www.waterlevels.gc.ca/eng/info/Webservices
    // Canadian Hydrographic Service

    // License required from Canadian Hydrographic Service to re-publish.
    // https://www.waterlevels.gc.ca/eng/info/Licence

    //  “This product has been produced by or for
    // [insert User's corporate name] and includes data and
    // services provided by the Canadian Hydrographic Service
    // of the Department of Fisheries and Oceans. The
    // incorporation of data sourced from the Canadian
    //  Hydrographic Service of the Department of Fisheries
    // and Oceans within this product does NOT constitute an
    // endorsement by the Canadian Hydrographic Service or
    // the Department of Fisheries and Oceans of this product.”

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
        $agent = new Agents($this->thing, "agents test");
        $this->thing = $agent->thing;
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
        $this->makeMessage();

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

        if (!isset($this->forecast_conditions)) {$this->forecast_conditions = "No forecast available.";}

        $sms_message = "TIDES | " . null;
        $sms_message .= $this->forecast_conditions;
//        $sms_message .= " | link " . $this->link;
        $sms_message .= " | source CHS";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }


}
