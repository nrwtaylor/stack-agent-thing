<?php
namespace Nrwtaylor\StackAgentThing;

//error_reporting(E_ALL);ini_set('display_errors', 1);

// And now the makePng class, exactly like the makePdf
// Let's call give it an N-gram to facilitate command 'make pdf'.
// Also means post-poning tackling what Pdf is actually defined as.
// Which might be exactly how it should be.

// Allowing these core channel processing functions to be handled
// in Composer packages

class makePng
{
    public $var = 'hello';

    function __construct(Thing $thing, $input = null)
    {

	    $this->input = $input;

        // routes passes image_name to make png as $input
	    $image_name = $input;

    	//require_once('/var/www/html/stackr.ca/agents/agent.php');
    	$this->agent_thing = new Agent($thing, $input);
        $this->thing_report = array('thing' => $thing, 'png' => $this->agent_thing->thing_report['png']);
if (isset($this->agent_thing->thing_report['pngs'])) {
        $this->thing_report = array('thing' => $thing, 'pngs' => $this->agent_thing->thing_report['pngs']);
}


	}
}

?>
