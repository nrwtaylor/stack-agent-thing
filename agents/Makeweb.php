<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);ini_set('display_errors', 1);


class MakeWeb
{

    public $var = 'hello';

    function __construct(Thing $thing, $input = null) 
    {


        $this->input = $input;
        $this->thing_report = array('thing' => $thing);

        // routes passes image_name to make png as $input
        $image_name = $input;

        $this->agent_thing = new Agent($thing, $input);

       if (!isset($this->agent_thing->thing_report['web'])) {
        //    $this->thing_report['web'] = $this->agent_thing->thing_report['web'];
        //} else {

            $this->agent_thing = new Web($thing, $input);
            //$this->thing_report['web'] = $this->agent_thing->thing_report['web'];
        }

//            $web = $this->agent_thing->thing_report['web'];
$web = "";
if (isset($this->agent_thing->thing_report['web'])) {$web = $this->agent_thing->thing_report['web'];}


$head= '
<table class="makeweb">
<tr>
<td class="makeweb">
<div class="makeweb">';


//$head= '
//<td>
//<table border="0" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF; border-bottom:0; border-radius:10px">

//<tr>
//<td align="center" valign="top">
//<div padding: 5px; text-align: center>';



$foot = "</td></div></td></tr></tbody></table></td></tr>";

        
        //$web .= "<center>";
        $web .= $head;

$button_text = "";
if (isset($this->agent_thing->thing_report['choices']['button'])) {$button_text = $this->agent_thing->thing_report['choices']['button'];}

        $web .= $button_text;
        $web .= $foot;




/*
        if (isset($this->agent_thing->thing_report['web'])) {
            $this->thing_report['web'] = $this->agent_thing->thing_report['web'];
        } else {

            $this->agent_thing = new Web($thing, $input);
            $this->thing_report['web'] = $this->agent_thing->thing_report['web'];
        }

*/

$this->thing_report['web'] = $web;



//echo $this->agent_thing->thing_report['log'];
        $this->thing_report['etime'] = number_format($thing->elapsed_runtime());



        return;

	    $this->input = $input;
        $web_agent = new Web($thing, $input);

	    if ($input == null) {
		    echo "Agent 'make web' says 'Nothing received'";//
	    } else {
		    echo "Agent 'make web' says '" . $input . "' received.";
	    }

        $this->thing_report = array('thing' => $thing, 
            'web' => $web_agent->thing_report['web']);

	}
}

?>
