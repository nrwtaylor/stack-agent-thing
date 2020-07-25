<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);ini_set('display_errors', 1);

class makeJson {


       public $var = 'hello';


    	function __construct(Thing $thing, $input = null) {

	$this->input = $input;

	$agent_thing = new Agent($thing, $input);

// Build JSON presentation here.

// Everything ...
//        $this->thing_report = array('thing' => $thing->thing, 
//                        'json' => json_encode(array("thing_report"=>$agent_thing->thing_report)) );

// whitefox

        $this->thing_report = array('json'=>json_encode(
array(
"thing"=>array("uuid"=>$thing->uuid, 
"task"=>$thing->task,
"created_at"=>$thing->created_at
),
"thing_report"=>array("sms"=>$agent_thing->thing_report['sms'])
)
));





	}



}
