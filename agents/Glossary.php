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
$this->glossary_agents = array();
	}

    function run()
    {

        $this->test_results = array();


        $data_source = $this->resource_path . "glossary/glossary.txt";

        $file_flag = false;

        $data = @file_get_contents($data_source);
        $file_flag = true;
        if ($data == false) {

// Start the glossary.
$this->doGlossary();

            // Handle quietly.

//            $data_source = trim($this->link);

//            $data = file_get_contents($data_source);
//            if ($data === false) {
                // Handle quietly.
//            }



//            $file = $this->resource_path . "vector/channels.txt";
//            try {

//                if ($file_flag == false) {
//                    file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
//                }
//            } catch (Exception $e) {
//                // Handle quietly.
//            }
        } else {
        $this->data = $data;
}
$this->split_time = $this->thing->elapsed_runtime();
$this->time_budget = 5000;

while (true) {
$this->glossary();
echo "time " . ($this->thing->elapsed_runtime() - $this->split_time) . "\n";

if  ($this->thing->elapsed_runtime() - $this->split_time > $this->time_budget) {break;}
}


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
        $this->makeTxt();

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
//        $this->thing_report['txt'] = $this->sms_message;
    }

    public function glossary()
    {

//        $this->test_results = array();

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

$exclude_agents = array("Emailhandler","Forgetall", "Tally");

if ((!isset($this->librex_matches)) or ($this->librex_matches == null)) {$this->makeGlossary();}
//var_dump($this->matches);
//$this->split_time = $this->thing->elapsed_runtime();
//$this->time_budget = 10000;

//$s = shuffle($this->agents);

$count = 0;
while ($count < 10) {
$count += 1;
            $k = array_rand($this->agents);
            $agent = $this->agents[$k];
$match_flag = false;

foreach($exclude_agents as $i=>$agent_name) {
echo $agent['name'] ." " .$agent_name ."\n";
if (strtolower($agent['name']) == strtolower($agent_name)) {

$match_flag = true; break;

}

if (($match_flag)) {break;}


}

echo $agent['name'] ." ";
$match_flag = false;
foreach($this->librex_matches as $agent_name=>$librex) {

if ( strtolower($agent_name) == strtolower($agent['name']) ) {
    $match_flag = true;
    break;
}

}

if (!($match_flag)) {break;}

}


echo $agent["name"] . "\n";

$this->glossary_agents[] = $agent;

//var_dump($s);
//foreach($s as $i=>$agent) {
//var_dump($agent);
//        do {
//echo "MERP";
            //$array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            //$k = array_rand($this->agents);
            //$v = $this->agents[$k];
$v = $agent;
            $agent_class_name = $v["name"];
            $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;


if (strtolower($agent_class_name) =="agents") {return;}
if (strtolower($agent_class_name) =="agentstest") {return;}

            $flag = "red";
            $ex = null;
            try {
//                $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;


// devstack

$thing = new Thing(null);
//new Meta($thing, "meta");
$thing->Create(null, null, null);
//$thing->to = null;
//$thing->from = null;
//$thing->subject = null;
//$thing->db = null;
//register_shutdown_function('shutDownFunction');
                $test_agent = new $agent_namespace_name($thing, $agent_class_name);
 
$help_text = "No help available.";
if (isset($test_agent->thing_report['help'])) {$help_text = $test_agent->thing_report['help'];}
//	var_dump($help_text);
            } catch (\Throwable $ex) { // Error is the base class for all internal PHP error exceptio$

//            } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptio$
                //echo $agent_name . "[ RED ]" . "\n";
                $m = $ex->getMessage();
//var_dump($m);
                $help_text = "No help available.";
                //continue;
            }

            $this->test_results[] = array("agent_name"=>$agent_class_name, "text"=>$help_text);

//echo "time " . ($this->thing->elapsed_runtime() - $this->split_time) . "\n";
// if  ($this->thing->elapsed_runtime() - $this->split_time > $this->time_budget) {break;}
//}
//var_dump($this->test_results);
//echo "done";
//exit();
    }

//function makeGlossary() {

//$t = explode($this->data,"\n");
//var_dump($t);


//}

    function makeSMS()
    {
        $sms = "GLOSSARY | ";

if (isset($this->response)) {$sms .= $this->response;}
//        $rand_agents = array_rand($this->glossary_agents, 3);
$sms .= "Updated glossary for ";
foreach($this->glossary_agents as $i=>$agent) {
        $sms .= $agent['name'] . " ";
//        $sms .= $agent['name'] . " ";
//        $sms .= $agent['name'];
}

        $this->sms_message = $sms;
    }


function doGlossary()
{

$data = "";
//$data = implode(" " , $this->test_results);

foreach($this->test_results as $i=>$result) {

$data .= "" . $result['agent_name']." " . $result['text'] . "\n";


}

            $file = $this->resource_path . "glossary/glossary.txt";
            try {

//                if ($file_flag == false) {
                    file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
//                }
            } catch (Exception $e) {
                // Handle quietly.
            }

$this->data = $data;

//$this->thing_report['txt'] = $data;

}

function makeGlossary() {




//$this->makeGlossary();
//var_dump($this->getLibrex("Nonsense"));

$librex_agent = new Librex($this->thing, "glossary/glossary");

$librex_agent->getMatches();
//var_dump($librex_agent->matches);
$txt = "";
ksort($librex_agent->matches);
foreach($librex_agent->matches as $agent_name=>$packet) {

//if (!isset($prior_firstChar)) {$prior_firstChar = "";}
//$firstChar = mb_substr($agent_name, 0, 1, "UTF-8");
//if ($prior_firstChar != $firstChar) {$txt .= $firstChar ."\n";}
//$prior_firstChar = $firstChar;

//$txt .= $agent_name . " " .$packet['words'] ."\n";

}

//    $this->thing_report['txt'] = $txt;
$this->librex_matches = $librex_agent->matches;
}



function getLibrex($text) {

$librex_agent = new Librex($this->thing, "glossary/glossary");
//$librex_agent->getMatches($this->input, $text);

// test
//$text = "fountain";


$librex_agent->getMatch($text);

//echo "matching " . $text .".\n";
//var_dump($librex_agent->matches);
//var_dump($librex_agent->response);
//var_dump($librex_agent->best_match);

$this->librex_response = $librex_agent->response;
$this->librex_best_match = $librex_agent->best_match;

//return($librex_agent->best_match);
return $librex_agent->response;
}


function makeTxt() {




//$this->makeGlossary();
//var_dump($this->getLibrex("Nonsense"));

$librex_agent = new Librex($this->thing, "glossary/glossary");

$librex_agent->getMatches();
//var_dump($librex_agent->matches);
$txt = "";
ksort($librex_agent->matches);
foreach($librex_agent->matches as $agent_name=>$packet) {

if (!isset($prior_firstChar)) {$prior_firstChar = "";}
$firstChar = mb_substr($agent_name, 0, 1, "UTF-8");
if ($prior_firstChar != $firstChar) {$txt .= $firstChar ."\n";}
$prior_firstChar = $firstChar;

$txt .= $agent_name . " " .$packet['words'] ."\n";

}

    $this->thing_report['txt'] = $txt;

}

    function makeWeb()
    {
        $web = '<b>Glossary</b>';
$web .= "<p><p>";
$librex_agent = new Librex($this->thing, "glossary/glossary");

$librex_agent->getMatches();
//var_dump($librex_agent->matches);
//$txt = "";
ksort($librex_agent->matches);
foreach($librex_agent->matches as $agent_name=>$packet) {
if (!isset($prior_firstChar)) {$prior_firstChar = "";}

$firstChar = mb_substr($agent_name, 0, 1, "UTF-8");
if ($prior_firstChar != $firstChar) {$web .= "<p>" . $firstChar ."<br>";}
$prior_firstChar = $firstChar;


$web .= $agent_name . " " .$packet['words'] ."<br>";

}


  //      foreach ($this->test_results as $key=>$result) {
//        $web .= "<br>" . $result['agent_name']." " . $result['text'];
//        }
        $this->thing_report['web'] = $web;
    }

	public function readSubject()
    {


$input = $this->input;
var_dump($input);
if (strtolower($input) != "glossary") {

$strip_word="glossary";
                    $whatIWant = $input;
                    if (($pos = strpos(strtolower($input), $strip_word. " is")) !== FALSE) {
                        $whatIWant = substr(strtolower($input), $pos+strlen($strip_word . " is"));
                    } elseif (($pos = strpos(strtolower($input), $strip_word)) !== FALSE) {
                        $whatIWant = substr(strtolower($input), $pos+strlen($strip_word));
                    }

                    $input = trim($whatIWant);
//var_dump($input);
$this->response = $this->getLibrex($input);
//var_dump($this->response);
//exit();
}

//$this->glossary();
		return false;
    }


}


//function shutDownFunction() { 
//    $error = error_get_last();
//    // fatal error, E_ERROR === 1
//    if ($error['type'] === E_ERROR) { 
//        //do your stuff     
//    } 
//}
