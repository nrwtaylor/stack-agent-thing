<?php
/**
 * Makejpeg.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

//error_reporting(E_ALL);ini_set('display_errors', 1);

// And now the makeJpeg class, exactly like the makePdf
// Let's call give it an N-gram to facilitate command 'make pdf'.
// Also means post-poning tackling what Pdf is actually defined as.
// Which might be exactly how it should be.

// Allowing these core channel processing functions to be handled
// in Composer packages

class makeJpeg {
    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $input (optional)
     */
    function __construct(Thing $thing, $input = null) {

        $this->input = $input;

        // routes passes image_name to make jpeg as $input
        $image_name = $input;

        $tokens = explode("-", $image_name);
        $agent_name = $tokens[0];
        $index = null;
        if (isset($tokens[1])) {$index = $tokens[1];}

        $this->agent_thing = new Agent($thing, $agent_name);

        if ($index == null) {
            $this->thing_report = array('thing' => $thing, 'jpeg' => $this->agent_thing->thing_report['jpeg']);
            return;
        }

        if (isset($this->agent_thing->thing_report['jpegs'])) {

$suffix = $index - 1;

            $jpeg = $this->agent_thing->thing_report['jpegs'][$agent_name . '-' . $suffix] ;
            $this->thing_report = array('thing' => $thing, 'jpeg' => $jpeg);

        }

    }


}


?>
