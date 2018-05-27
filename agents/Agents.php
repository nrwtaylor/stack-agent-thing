<?php
namespace Nrwtaylor\Stackr;

class Agents {

	public $var = 'hello';


    function __construct(Thing $thing, $text = null) {

        $this->start_time = microtime(true);

		$this->agent_name = "agents";
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
		$this->test= "Development code";

//      This is how old roll.php is.
//		$thingy = $thing->thing;
		$this->thing = $thing;

         $this->thing_report  = array("thing"=>$this->thing->thing);


        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;


        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log($this->agent_prefix . "received this Thing ".  $this->subject . '".');

        $this->getAgencies();

		$this->readSubject();

		$this->thing_report = $this->respond();

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($milliseconds) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;

		return;

	}

    function getAgencies()
    {

        $this->agencies = array();
        // Only use Stackr agents for now
        $dir    = $GLOBALS['stack'] . 'vendor/nrwtaylor/stackr/agents'; 
// /var/www/stackr.test/vendor/nrwtaylor/stackr/agent 
//' ';
//echo $dir;
        $files = scandir($dir);

        foreach ($files as $key=>$file) {
            if ($file[0] == "_") {continue;}

            if ( strtolower(substr($file, 0, 3)) == "dev") {continue;}

            if ( strtolower(substr($file, -4)) != ".php") {continue;}

            if (!ctype_upper($file[0])) {continue;}

            $agent_name = substr($file, 0, -4);

            $this->agencies[] =  ucwords($agent_name);
        }

        //var_dump($files);
        $this->agent_names = $this->agencies;

    }

// -----------------------

	private function respond() {


		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;

		//echo "to:". $to;

		$from = "agents";

$s = "AGENTS | ";
$rand_keys = array_rand($this->agencies, 3);
$s .= $this->agencies[$rand_keys[0]] . " ";
$s .= $this->agencies[$rand_keys[1]] . " ";
$s .= $this->agencies[$rand_keys[2]];



        $this->sms_message = $s;


        $choices = false;

		$this->thing_report[ "choices" ] = $choices;

        //$this->thing_report["agency"] = "Prepare a list of stack agents."; 

 		$this->thing_report["info"] = "This shares what agents the stack has."; 
 		$this->thing_report["help"] = "This gives a list of the Agents available to the Stack.";

		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;


        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->makeWeb();

		return $this->thing_report;


	}

    function makeWeb()
    {
        $w = '<b>Agents</b>';
        foreach ($this->agencies as $key=>$agent) {
        $w .= "<br>" . $agent;
        }
        $this->thing_report['web'] = $w;
    }

/*
    function extractRoll($input) {

//echo $input;
//exit();

preg_match('/^(\\d)?d(\\d)(\\+\\d)?$/',$input,$matches);

print_r($matches);

$t = preg_filter('/^(\\d)?d(\\d)(\\+\\d)?$/',
                '$a="$1"? : 1;for(; $i++<$a; $s+=rand(1,$2) );echo$s$3;',
                $input)?:'echo"Invalid input";';


    }
*/



	public function readSubject()
    {


        //$input = strtolower($this->subject);


		return false;
    }

}



return;
