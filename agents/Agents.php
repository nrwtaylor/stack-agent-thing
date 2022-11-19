<?php
namespace Nrwtaylor\StackAgentThing;

class Agents extends Agent
{
	public $var = 'hello';

    function init()
    {
        $this->thing_report["agency"] = "Prepare a list of ALL stack agents.";
        $this->thing_report["info"] = "This shares what agents the stack has.";
        $this->thing_report["help"] = "This gives a list of the Agents available to the Stack.";
	}

    function run()
    {
        $this->getAgents();
    }

    function getAgents()
    {
        if (isset($this->agents)) {return;}

        $this->agent_list = array();
        $this->agents = array();

        // Only use Stackr agents for now
        // Single source folder ensures uniqueness of N-grams
//        $dir    = $GLOBALS['stack_path'] . 'vendor/nrwtaylor/stack-agent-thing/agents';

//        $dir    = "/home/nick/codebase/stack-agent-thing-dev/". 'vendor/nrwtaylor/stack-agent-thing/agents';
        $dir = "/home/nick/codebase/stack-agent-thing-dev/agents";

        $files = scandir($dir);
        foreach ($files as $key=>$file) {
            if ($file[0] == "_") {continue;}
            if ( strtolower(substr($file, 0, 3)) == "dev") {continue;}
            if ( strtolower(substr($file, -4)) != ".php") {continue;}
            if (!ctype_upper($file[0])) {continue;}

            $agent_name = substr($file, 0, -4);
            $this->agent_list[] =  ucwords($agent_name);

            $this->agents[$agent_name] =  array("name"=>$agent_name);
        }
    }

	public function respond()
    {
		$this->thing->flagGreen(); // Test report

        $this->makeSMS();
        $this->makeWeb();

        $choices = false;
		$this->thing_report[ "choices" ] = $choices;

        $this->report();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }
	}

    public function report()
    {
        $this->thing_report['thing'] = $this->thing;
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;
    }

    public function test()
    {
        $this->test_results = array();

        $this->getAgents();

        $skip_to_agent = "Bar";
$flag = false;

        $dev_agents = array("Agent","Agents","Agentstest",
                        "Chart", "Discord", "Emailhandler","Forgetall",
                        "Shuffleall","Googlehangouts","Makelog","Makepdf",
                        "Makephp","Makepng","Maketxt","Makeweb","Number",
                        "Nuuid","Object","PERCS","Ping","Place","Random",
                        "Robot","Rocky","Search","Serial","Serialhandler",
                        "Stackrinteractive","Tally","Thought","Timestamp",
                        "Uuid","Variables","Wikipedia","Wordgame","Wumpus");

$this->split_time = $this->thing->elapsed_runtime();
$this->time_budget = 2000;

        do {
            $k = array_rand($this->agents);
            $v = $this->agents[$k];

            $agent_class_name = $v["name"];
            $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;


if (strtolower($agent_class_name) =="agents") {continue;}
if (strtolower($agent_class_name) =="agentstest") {continue;}

            $flag = "red";
            $ex = null;
            try {
                $test_agent = new $agent_namespace_name($this->thing, $agent_class_name); 
                $flag = $test_agent->test();
                $m = null;

            } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptio$
                $m = $ex->getMessage();
                $flag = "red";
            }

            $this->test_results[] = array("agent_name"=>$agent_class_name, "flag"=>$flag, "error"=>$m);


        } while ($this->thing->elapsed_runtime() - $this->split_time < $this->time_budget);

    }

    function makeSMS()
    {
        $sms = "AGENTS | ";
        $rand_agents = array_rand($this->agents, 3);
        $sms .= $this->agents[$rand_agents[0]]['name'] . " ";
        $sms .= $this->agents[$rand_agents[1]]['name'] . " ";
        $sms .= $this->agents[$rand_agents[2]]['name'];


        $this->sms_message = $sms;
    }

    function makeWeb()
    {
        $web = '<b>Agents</b>';
        foreach ($this->agents as $key=>$agent) {
        $web .= "<br>" . $agent['name'];
        }
        $this->thing_report['web'] = $web;
    }

	public function readSubject()
    {
        if ($this->agent_input == "agents test") {
            $this->test();
            return;
        }
		return false;
    }

}
