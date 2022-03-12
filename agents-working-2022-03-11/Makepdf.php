<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);ini_set('display_errors', 1);

// First off this is called 'make pdf' intentionally to mimic the command structure.
// Touchy much?

// This seems to be necessary.  So lets leave all this at the start for now.


use setasign\Fpdi;

class makePdf {


       public $var = 'hello';


    	function __construct(Thing $thing, $input = null) {

	$this->input = $input;

	$agent_thing = new Agent($thing, $input);
        $this->thing_report = array('thing' => $thing->thing, 
                        'pdf' => $agent_thing->thing_report['pdf']);

	}



}


?>
