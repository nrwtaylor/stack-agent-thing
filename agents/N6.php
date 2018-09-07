<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class N6 {

	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = $thing->elapsed_runtime(); 

        $this->agent_input = $agent_input;

		$this->agent_name = "n6";
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
		$this->test= "Development code";

		$this->thing = $thing;
        $this->thing_report['thing']  = $thing;

        //$this->start_time = $this->thing->elapsed_runtime();


        //$command_line = null;

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = strtolower($thing->subject);

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->node_list = array("n6"=>array("n6"));

        $this->thing->log('running on Thing '. $this->thing->nuuid . '.', "INFORMATION");
        $this->thing->log('received this Thing "'.  $this->subject . '".', "DEBUG");


        $this->current_time = $this->thing->time();


        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("n6", "refreshed_at") );

        if ($time_string == false) {
//            $this->thing->json->setField("variables");
            $time_string = $this->thing->time();
            $this->thing->json->writeVariable( array("n6", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);


//        $this->thing->json->setField("variables");
        $this->number = $this->thing->json->readVariable( array("n6", "number"));
        $this->text = $this->thing->json->readVariable( array("n6", "text") ); // Test because this will become A6.

$this->readSubject();
            $this->thing->log($this->agent_prefix . ' completed read.', "OPTIMIZE") ;

        if ( ($this->number == false) or ($this->text == false) ) {

            $this->makeN6();
            $this->thing->json->writeVariable( array("n6", "number"), $this->number );
            $this->thing->json->writeVariable( array("n6", "text"), $this->text );

        }

        //$this->readSubject();

        $this->respond();

//        $this->set();

        $this->thing->log($this->agent_prefix . ' set response.', "OPTIMIZE") ;

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['log'] = $this->thing->log;

		return;
	}

    public function makeN6()
    {
        if (is_numeric($this->number)) {
            $this->response = "Read this six-digit number.";
            return;
        }

        $this->response = "Made this six-digit number.";
        // Built-in best PHP
        // PHP states cryptographically secure
        $this->random = new Random($this->thing,"random");
        $this->random->randomRandomint(100000,999999);
        $this->number = $this->random->number;
    }


// -----------------------

	private function respond() {


		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;

		//echo "to:". $to;

		$from = "n6";
		$roll = -1;

// This choice element is super slow.  It 
// is the difference between 6s and 351ms.
// Hard to justify a button question in response to a die roll.

//		$node_list = array('start'=>array('useful','what is this'));
//        $this->thing->choice->Create($this->agent_name, $node_list, 'start');
//        $choices = $this->thing->choice->makeLinks('start');

        $choices = false;

		// When making an email.
		// The Thing will have the to address (aka nom_from in db).
		// The originating agent will have to be passed in this call.
		// The message and choices will need to be passed in this call.

		// Really?  Are choices not embedded in Thing?

		// So maybe not choices, but the message needs to be passed.
        $this->makeSMS();
        $this->makeMessage();
//        $this->makePNG();

        $this->makeChoices();
        $this->makeWeb();

        $this->makeEmail();

 		$this->thing_report["info"] = "This makes six digit number.";
        if (!isset($this->thing_report['help'])) {
 		    $this->thing_report["help"] = 'This is about six digit numbers.  Try "n6".';
        }

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

		return $this->thing_report;
	}

    function makeChoices ()
    {
        $this->thing->choice->Create($this->agent_name, $this->node_list, "n6");

        $choices = $this->thing->choice->makeLinks('n6');
        $this->thing_report['choices'] = $choices;
    }

    function makeEmail()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/n6';

        $this->node_list = array("n6"=>array("n6"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "n6");
        $choices = $this->thing->choice->makeLinks('n6');

        $web = '<a href="' . $link . '">';
//        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/roll.png" jpg" 
//                width="100" height="100" 
//                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/roll.tx$

        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';

//        if (!isset($this->html_image)) {$this->makePNG();}

//        $web .= $this->html_image;

//        $web .= "</a>";
//        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( time() - $this->refreshed_at );
        $web .= "This number was made about ". $ago . " ago.";

        $web .= "<br>";


        $this->thing_report['email'] = $web;
    }



    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = array("n6"=>array("n6"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "web");
        $choices = $this->thing->choice->makeLinks('web');

        $web = "N6 is " . $this->number .".";
//        if (!isset($this->html_image)) {$this->makePNG();}

//        $web = '<a href="' . $link . '">'. $this->html_image . "</a>";
//        $web .= "<br>";

        $web .= '<br>N6 says, "'  .$this->sms_message . '".'; 


        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( time() - $this->refreshed_at );
        $web .= "<p>The number was made about ". $ago . " ago.";


        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    function makeSMS()
    {

        //if (!isset($this->text) or ($this->text == 'Invalid input' ) or ($this->text == null)) {
        //    $sms = "N6 | Request not processed. Check syntax.";
        //} else {

            $sms = "N6 | " . $this->number . " | " . $this->response;

        //}


        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;

    }

    function makeMessage()
    {
        $message = "Stackr got this N6 for you.<br>";
        $message .= $this->number .".";

        $this->thing_report['message'] = $message;

        return;
    }


    function read()
    {
        $this->get();
        return $this->state;
    }

/*
    function extractN6($input)
    {
        // devstack
        return;
        preg_match('/^(\\d)?d(\\d)(\\+\\d)?$/',$input,$matches);
        print_r($matches);

        $t = preg_filter('/^(\\d)?d(\\d)(\\+\\d)?$/',
                '$a="$1"? : 1;for(; $i++<$a; $s+=rand(1,$2) );echo$s$3;',
                $input)?:'echo"Invalid input";';
    }
*/

    function extractN6($input)
    {
        if (!isset($this->n6s)) {
            $this->response = "Found lots of six-digit numbers.";
            $this->n6s = $this->extractN6s($input);
        }

        if (count($this->n6s) == 1) {
            $this->response = "Found a six-digit number.";
            $this->n6 = strtolower($this->n6s[0]);
            return $this->n6;
        }

        if (count($this->n6s) == 0) {
            $this->response = "Did not find any six-digit numbers.";
            $this->n6 = null;
            return $this->n6;
        }

        $this->n6 = false;
        //array_pop($arr);
        return false;
    }


    function extractN6s($input)
    {
        if (!isset($this->n6s)) {
            $this->n6s = array();
        }

        //Why not combine them into one character class? /^[0-9+#-]*$/ (for matching) and /([0-9+#-]+)/ for capturing ? 
        $pattern = "|^(\\d)?d(\\d)(\\+\\d)?$|";
        //$pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";
        $pattern = '/([0-9d+]+)/';
        $pattern = '/(\d{6})/';
        $pattern = "|\b[xXzZ0-9]{6}\b|";
        //$pattern = "|\b[X]{6}\b|";
        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);
        $this->n6s = $arr;
        return $this->n6s;
    }

	public function readSubject()
    {
        $this->response = "Read.";

        //        $input = '2d20+5+d100';
        $input = strtolower($this->subject);

        if ($this->agent_input != null) {
            $input = strtolower($this->agent_input);
        }


        $this->extractN6($input);

//var_dump($this->n6);
        if ($this->n6 == null) {
            $this->n6 = "XXXXXX";
        }

        //$result = array();

        //$n6 = 0;

		return;
    }

}
