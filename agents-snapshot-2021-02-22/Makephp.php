<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);ini_set('display_errors', 1);

// And now the makePhp class, exactly like the makePdf and makePng and makeTxt
// Let's call give it an N-gram to facilitate command 'make php'.

class Makephp
{
    public $var = 'hello';

    function __construct(Thing $thing, $input = null)
    {
        $this->input = $input;

        $class_name = ucwords($input);

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $stack_state = $thing->container['stack']['state'];
	$file = false;
	if ($class_name !== "") {
           $file = @file_get_contents(__DIR__ . '/../agents/'. $class_name . '.php');
	}
        if($file=== FALSE) { // handle error here... }
            $file = "Agent 'make php' says " .ucwords($input) . " is not a recognized Agent on this Stack.";
        }

        $this->thing_report = array('thing' => $thing, 
            'php' => $file);

	}
}

?>
