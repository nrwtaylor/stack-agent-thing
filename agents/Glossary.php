<?php
namespace Nrwtaylor\StackAgentThing;

class Glossary extends Agent
{
	public $var = 'hello';

    function init()
    {
        $this->thing_report["agency"] = "Prepare a helpful glossary of ALL stack agents.";
        $this->thing_report["info"] = "This shares what agents the stack has. And what they do.";
        $this->thing_report["help"] = "This gives a list of the help text for each Agent.";
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
        $dir    = $GLOBALS['stack_path'] . 'vendor/nrwtaylor/stack-agent-thing/agents';
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

    public function glossary()
    {
        $this->test_results = array();

        if (!isset($this->agents)) {$this->getAgents();}

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
$this->time_budget = 10000;

foreach($this->agents as $i=>$agent) {
var_dump($agent);
//        do {
//echo "MERP";
            //$array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            //$k = array_rand($this->agents);
            //$v = $this->agents[$k];
$v = $agent;
            $agent_class_name = $v["name"];
            $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;


if (strtolower($agent_class_name) =="agents") {continue;}
if (strtolower($agent_class_name) =="agentstest") {continue;}

            $flag = "red";
            $ex = null;
            try {
//                $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;


// devstack

$thing = new Thing(null);
new Meta($thing, "meta");
//$thing->Create(null, null, null);
//$thing->to = null;
//$thing->from = null;
//$thing->subject = null;
//$thing->db = null;

                $test_agent = new $agent_namespace_name($thing, $agent_class_name);
 
$help_text = "No help available.";
if (isset($test_agent->thing_report['help'])) {$help_text = $test_agent->thing_report['help'];}
var_dump($help_text);
            } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptio$
                //echo $agent_name . "[ RED ]" . "\n";
                $m = $ex->getMessage();
var_dump($m);
                $help_text = "No help available.";
                //continue;
            }

            $this->test_results[] = array("agent_name"=>$agent_class_name, "text"=>$help_text);

echo "time " . ($this->thing->elapsed_runtime() - $this->split_time) . "\n";
 if  ($this->thing->elapsed_runtime() - $this->split_time > $this->time_budget) {break;}
}
//var_dump($this->test_results);
//echo "done";
//exit();
    }

    function makeSMS()
    {
        $sms = "GLOSSARY | ";
        $rand_agents = array_rand($this->agents, 3);
        $sms .= $this->agents[$rand_agents[0]]['name'] . " ";
        $sms .= $this->agents[$rand_agents[1]]['name'] . " ";
        $sms .= $this->agents[$rand_agents[2]]['name'];


        $this->sms_message = $sms;
    }

    function makeWeb()
    {
        $web = '<b>Glossary</b>';
        foreach ($this->agents as $key=>$agent) {
        $web .= "<br>" . $agent['name'];
        }
        $this->thing_report['web'] = $web;
    }

	public function readSubject()
    {
$this->glossary();
		return false;
    }

}
