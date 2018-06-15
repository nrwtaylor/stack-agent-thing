<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


// And now the makePhp class, exactly like the makePdf and makePng and makeTxt
// Let's call give it an N-gram to facilitate command 'make php'.
// Also means post-poning tackling what Pdf is actually defined as.
// Which might be exactly how it should be.

// Allowing these core channel processing functions to be handled
// in Composer packages

class Makelog
{
    public $var = 'hello';

    function __construct(Thing $thing, $input = null)
    {
	    $this->input = $input;
        $this->thing = $thing;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];
        $this->email = $thing->container['stack']['entity_name'];

        $header = "THING AGENT LOG";
        $header .= "\n";

        $footer = "\n";


    	if ($input == null) {
	    	$footer .= "Agent 'make log' says 'Nothing received'";//
	    } else {
		    $footer .= "Agent 'make log' says '" . $input . "' received.";
	    }

        $footer .= "\n";
        $footer .=  "\n";
        $footer .=  $this->web_prefix;

        $file = $header . "\n";


    	// routes passes image_name to make png as $input
        $this->agent_thing = new Agent($thing, $input);

        $text = str_replace("<br>", "\n", ($this->agent_thing->thing->log));
        $text = str_replace("\n ", "\n", $text);
        $this->text = $text;

        $this->getRuntimes();

        $t = "\nSelf-reported Agent runtimes\n";
        foreach(array_reverse($this->agent_run_for) as $key=>$agent_run_for) {
            $t .= $agent_run_for['agent_name'] . " " . number_format($agent_run_for['run_for']) . "ms\n";
        }

        $file .= $t. "\n";

        $t = "\nSelf-reported Agent chain-of-custody\n";
        foreach(($this->agent_sequence) as $key=>$array) {
            $t .= $array["agent_name"];
            if ($array["run_for"] != "X") {
                $t .= " (" . number_format($array["run_for"]) . "ms)";
                $t .= " | ";
            }  else {
                $t .= " > ";
            }
        }
        $t .= "\n";

        $file .= $t. "\n";
        $file .= "\nSelf-report\n";
        $file .= $text;
        $file .= $footer;

        $this->thing_report = array('thing' => $thing, 
            'log' => $file);

	}

    function getRuntimes()
    {
        $c = 0;
        $this->runtimes = array();
        $lines = explode("\n" , $this->text);  
        $time_stamp = 0;
        $run_time = 0;
        $previous_run_time = 0;
        $this->agent_run_for = array();
        foreach (($lines) as $key=>$line) {

            preg_match_all('/[a-zA-Z]+/',$line,$matches);
            $words = $matches[0];

            preg_match_all('/[0-9,]+/',$line,$matches);
            $numbers = $matches[0];

            $agent_name = $words[2];

            if (strpos($line, 'ran for') !== false) {
                $c += 1;
                $this->agent_run_time = array();

                $run_time = (int) (str_replace(",", "", $numbers[0])) ;
                $run_for = $run_time;
                $previous_run_time = $run_time;

                $this->agent_run_for[] = array("agent_name"=>$agent_name, "run_for"=>$run_for);

            }


            if ($agent_name != $previous_agent_name) {
                if (!isset($run_for)) {$run_for = "X";}
                $this->agent_sequence[] = array("agent_name"=>$agent_name, "run_for"=>$run_for);
            }
            $previous_agent_name = $agent_name;
            $run_for = null;
        }
    }
}


?>
