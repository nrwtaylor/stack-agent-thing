<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);


class Callagent
{

	public function __construct($thing, $agent_input = null)
    {
        return;
        echo "foo";
        $this->agent_instruction = $agent_input;

        //$this->arg = $arg;

        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->thing_report['thing'] = $this->thing->thing;


        if ($agent_input == "getagent") {$this->callAgent($this->thing->uuid);}
        $this->thing_report['log'] = $this->thing->log;
        echo "bar";
	}


    public function run() {

            $this->callAgent($uuid, 'receipt');
    }


    function callAgent($uuid, $to = null)
    {

	    // dev note: rename $to to $agent_requested

	    $thing = new Thing($uuid);

	    if ($thing->isGreen() == true) {
          
		    //echo 'callagent called Thing ' . $thing->uuid . 'has a Green flag.  callagent ignoring./n';
		    return;
	    }

	    if ($to == null) {
		    $to = $thing->to;
	    }

	    // Hacky here.  If there is an @ sign and it isn't @stackr. then delete
	    $arr = explode("@", $to, 2);
	    $to = $arr[0];


	    $agent_class_name = ucfirst($to);
	    echo "Agent name: ",$agent_class_name;
/*
	    try {
		    include_once '/var/www/html/stackr.ca/agents/' . strtolower($agent_class_name) . '.php';
	    } catch (Exception $e) {
    		echo 'Caught exception: ',  $e->getMessage(), "\n";
	    }
*/
	    if (class_exists($agent_class_name)) {

		    $agent = new $agent_class_name($thing, $this->agent_instruction);

	    } else {

		    echo '<pre> Agent\'s file not found: '; print_r($agent_class_name); echo '</pre>';	
		    //require_once '/var/www/html/stackr.ca/agents/agent.php';
		    // If class doesn't exist then call standard agent.

            //        try {
            //register_shutdown_function('shutDownFunction');
            try {
                $agent = new Agent($thing, $this->agent_instruction);
            } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptions.

                //require_once '/var/www/html/stackr.ca/agents/bork.php';

                $message = $ex->getMessage();
                $code = $ex->getCode();
                $file = $ex->getFile();
                $line = $ex->getLine();

                $input = $message . ' / ' . $code . ' / ' . $file . ' / ' . $line;

                $agent = new Bork($thing, 'agenthandler/' . $input );

            }


	    }
        // Added Mar 17 2018
        $this->thing_report = $agent->thing_report;

    }


}



?>
