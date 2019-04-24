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
        foreach ($this->agents as $key=>$agent) {

          //  if ( (!isset($skip_to_agent)) or ($skip_to_agent == null) or ($skip_to_agent == "")) {$flag = true;}
//            if (strtolower($agent['name']) == strtolower($skip_to_agent)) {$flag = true;}

//            if ($flag != true) {continue;}

            $agent_class_name = $agent['name'];


            $agent_flag = false;
            foreach($dev_agents as $key=>$dev_agent) {
          // Big issue
            if ($agent_class_name == $dev_agent) {$agent_flag = true; break;}
            }

            if ($agent_flag == true) {continue;}

            //$thing = new Thing(null);
            //$subject = "s/ " . $agent_name;
            //$thing->Create(null, "test", $subject);

            //$test_agent = new $agent_name($this->thing, $agent_name);

            //$this->getAgent($agent_name);
            //echo $this->thing_report['sms'] . "\n";
echo $agent_class_name . "\n\n";
            $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;

            
            $ex = null;
            try {
//                $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;

                $test_agent = new $agent_namespace_name($this->thing, $agent_class_name); 
                $flag = $test_agent->test();
                $m = null;
            } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptio$
                //echo $agent_name . "[ RED ]" . "\n";
                $m = $ex->getMessage();
                $flag = "red";
                //continue;
            }

            $this->test_results[] = array("agent_name"=>$agent_class_name, "flag"=>$flag, "error"=>$m);

            //echo $agent_class_name . "[ " . $flag . " ] ". "\n";


        }
var_dump($this->test_results);
//echo "done";
exit();
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
