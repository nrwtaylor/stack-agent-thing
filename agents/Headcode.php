<?php
namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Headcode 
{

    // This is a headcode.  You will probably want to read up about
    // the locomotive headcodes used by British Rail.

    // A headcode takes the form (or did in the 1960s),
    // of NANN.  Where N is a digit from 0-9, and A is an uppercase character from A-Z.

    // This implementation is recognizes lowercase and uppercase characters as the same.

    // The headcode is used by the Train agent to create the proto-train.

    // A headcode must have a route. Route is a text string.  Examples of route are:
    //  Gilmore > Hastings > Place
    //  >> Gilmore >>
    //  > Hastings

    // A headcode may have a consist. (Z - indicates train may fill consist. 
    // X - indicates train should specify the consist. (devstack: "Input" agent)
    // NnXZ is therefore a valid consist. As is "X" or "Z".  
    // A consist must always resolve to a locomotive.  Specified as uppercase letter.
    // The locomotive closest to the first character is the engine.  And gives 
    // commands to following locomotives to follow.

    // This is the headcode manager.  This person is pretty special.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) 
    {
        $this->start_time = microtime(true);

        //if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();
        $this->thing_report['thing'] = $this->thing->thing;

        $this->agent_prefix = 'Agent "Headcode" ';

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.',"INFORMATION");


        // I'm not sure quite what the node_list means yet
        // in the context of headcodes.
        // At the moment it seems to be the headcode routing.
        // Which is leading to me to question whether "is"
        // or "Place" is the next Agent to code up.  I think
        // it will be "Is" because you have to define what 
        // a "Place [is]".
 //       $this->node_list = array("start"=>array("stop 1"=>array("stop 2","stop 1"),"stop 3"),"stop 3");
 //       $this->thing->choice->load('headcode');

        $this->keywords = array('next', 'accept', 'clear', 'drop','add','new');

//        $this->headcode = new Variables($this->thing, "variables headcode " . $this->from);


        // So around this point I'd be expecting to define the variables.
        // But I can do that in each agent.  Though there will be some
        // common variables?

        // So here is building block of putting a headcode in each Thing.
        // And a little bit of work on a common variable framework. 

        // Factor in the following code.

//                'headcode' => array('default run_time'=>'105',
//                                'negative_time'=>'yes'),

                //$this->default_run_time = $this->thing->container['api']['headcode']['default run_time'];
                //$this->negative_time = $this->thing->container['api']['headcode']['negative_time'];

        // But for now use this below.

        // You will probably see these a lot.
        // Unless you learn headcodes after typing SYNTAX.

        $this->default_head_code = "0Z10";
        //$this->head_code = "0Z" . str_pad($this->index + 11,2, '0', STR_PAD_LEFT);

        $this->default_alias = "Thing";
        $this->current_time = $this->thing->json->time();

        // Loads in headcode variables.
        // This will attempt to find the latest head_code
//        $this->get(); // Updates $this->elapsed_time as well as pulling in the current headcode

        // Now at this point a  "$this->headcode_thing" will be loaded.
        // Which will be re-factored eventaully as $this->variables_thing.

        // This looks like a reminder below that the json time generator might be creating a token.

		// So created a token_generated_time field.
        //        $this->thing->json->setField("variables");
        //        $this->thing->json->writeVariable( array("stopwatch", "request_at"), $this->thing->json->time() );

        //$this->thing->json->time()


		$this->test= "Development code"; // Always iterative.

        // Non-nominal
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        // Potentially nominal
        $this->subject = $thing->subject;
        // Treat as nominal
        $this->from = $thing->from;

        // Agent variables
        $this->sqlresponse = null; // True - error. (Null or False) - no response. Text - response

//        $this->thing->log('<pre> Agent "Headcode" running on Thing '. $this->thing->nuuid . '.</pre>');
//        $this->thing->log('<pre> Agent "Headcode" received this Thing "'.  $this->subject . '".</pre>');

//$split_time = $this->thing->elapsed_runtime();
//        $this->headcode = new Variables($this->thing, "variables headcode " . $this->from);
//        $this->head_code = $this->headcode->getVariable('head_code', null);

//$this->get();

//$this->thing->log( $this->agent_prefix .' set up variables in ' . number_format($this->thing->elapsed_runtime() - $split_time) . 'ms.' );

        $this->state = null; // to avoid error messages


        // Read the subject to determine intent.
		$this->readSubject();

        // Generate a response based on that intent.
        // I think properly capitalized.
        //$this->set();
        if ($this->agent_input == null) {
		    $this->Respond();
        }
        $this->set();

//		$this->thing->log('<pre> Agent "Headcode" completed</pre>');


        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;



		return;

		}


    function set()
    {
//$this->head_code = "0Z15";
        //$headcode = new Variables($this->thing, "variables headcode " . $this->from);

        $this->headcode_id->setVariable("head_code", $this->head_code);
     //   $this->headcode->setVariable("index", $this->index);
        $this->headcode_id->setVariable("refreshed_at", $this->current_time);


        $this->thing->json->writeVariable( array("headcode", "head_code"), $this->head_code );
        $this->thing->json->writeVariable( array("headcode", "refreshed_at"), $this->current_time );


        //$headcode = new Variables($this->thing, "variables " . $this->head_code . " " . $this->from);

        //$this->head_code = $this->headcode->getVariable("head_code");
        $this->headcode->setVariable("consist", $this->consist);
        $this->headcode->setVariable("run_at", $this->run_at);
        $this->headcode->setVariable("quantity", $this->quantity);
        $this->headcode->setVariable("available", $this->available);



        return;
    }

    function nextHeadcode() {

        $this->thing->log("next headcode");
        // Pull up the current headcode
        $this->get();

        // Find the end time of the headcode
        // which is $this->end_at

        // One minute into next headcode
        $quantity = 1;
        $next_time = $this->thing->json->time(strtotime($this->end_at . " " . $quantity . " minutes"));

        $this->get($next_time);

        // So this should create a headcode in the next quantity unit.




        return $this->available;


    }

    function getVariable($variable_name = null, $variable = null) {

        // This function does a minor kind of magic
        // to resolve between $variable, $this->variable,
        // and $this->default_variable.

        if ($variable != null) {
            // Local variable found.
            // Local variable takes precedence.
            return $variable;
        }

        if (isset($this->$variable_name)) {
            // Class variable found.
            // Class variable follows in precedence.
            return $this->$variable_name;
        }

        // Neither a local or class variable was found.
        // So see if the default variable is set.
        if (isset( $this->{"default_" . $variable_name} )) {

            // Default variable was found.
            // Default variable follows in precedence.
            return $this->{"default_" . $variable_name};
        }

        // Return false ie (false/null) when variable
        // setting is found.
        return false;
    }

    function getRoute() {
            //$this->route = $this->thing->json->readVariable( array("headcode", "route") );
//            $this->route = "na";
      
        //$route_agent = new Route($this->thing, $this->head_code);
        //$this->route = $route_agent->route;
        $this->route = "Place";
    }

    function getRunat() {

            if (isset($run_at)) {
               $this->run_at = $run_at;
            } else {
                $this->run_at = "X";
            }

    }
    function getQuantity () {
        // $this->quantity = $this->thing->json->readVariable( array("headcode", "quantity"))  ;
        $this->quantity = "X";

    }


    function getHeadcodes() {

        $this->headcode_list = array();
        // See if a headcode record exists.
        $findagent_thing = new FindAgent($this->thing, 'headcode');

        $this->thing->log('Agent "Headcode" found ' . count($findagent_thing->thing_report['things']) ." headcode Things." );

        foreach (array_reverse($findagent_thing->thing_report['things']) as $thing_object) {
            // While timing is an issue of concern

            $uuid = $thing_object['uuid'];

            $thing= new Thing($uuid);
            $variables = $thing->account['stack']->json->array_data;


            if (isset($variables['headcode'])) {
                $head_code = $variables['headcode']['head_code'];
                $variables['headcode'][] = $thing_object['task'];
                $this->headcode_list[] = $variables['headcode'];
            }
        }

        return $this->headcode_list;

    }

    function get($head_code = null)
    {
        // This is a request to get the headcode from the Thing
        // and if that doesn't work then from the Stack.

        // 0. light engine with or without break vans.
        // Z. Always has been a special.
        // 10. Because starting at the beginning is probably a mistake. 
        // if you need 0Z00 ... you really need it.

//        if (!isset($this->head_code)) {
//            $this->head_code = $this->headcode->getVariable('head_code', $head_code);
//        }


//        $headcode = new Variables($this->thing, "variables " . $this->head_code . " " . $this->from);

        $this->headcode = new Variables($this->thing, "variables " . $this->head_code . " " . $this->from);


        //$this->head_code = $this->headcode->getVariable("head_code");
        $this->consist = $this->headcode->getVariable("consist");
        $this->run_at = $this->headcode->getVariable("run_at");
        $this->quantity = $this->headcode->getVariable("quantity");
        $this->available = $this->headcode->getVariable("available");


        $this->getRoute();
        $this->getConsist();
        $this->getRunat();
        $this->getQuantity();
        $this->getAvailable();


        return;
    }

    function dropHeadcode() {
        $this->thing->log($this->agent_prefix . "was asked to drop a headcode.");


        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->headcode)) {
            $this->headcode->Forget();
            $this->headcode = null;
        }

        $this->get();
 
    }


    function makeHeadcode($head_code = null) {

        $head_code = $this->getVariable('head_code', $head_code);

        $this->thing->log('Agent "Headcode" will make a headcode for ' . $head_code . ".");


        $ad_hoc = true;

        if ( ($ad_hoc != false) ) {

            // Ad-hoc headcodes allows creation of headcodes on the fly.
            // 'Z' indicates the associated 'Place' is offering whatever it has.
            // Block is a Place.  Train is a Place (just a moving one).
            $quantity = "Z";

            // Otherwise we needs to make trains to run in the headcode.

            $this->thing->log($this->agent_prefix . "was told the Place is Useable but we might get kicked out.");

            // So we can create this headcode either from the variables provided to the function,
            // or leave them unchanged.

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            $this->current_head_code = $head_code;
            $this->head_code = $head_code;

            $this->quantity = $quantity; // which is run_time

            if (isset($run_at)) {
               $this->run_at = $run_at;
            } else {
                $this->run_at = "X";
            }
            $this->getEndat();
            $this->getAvailable();

            $this->headcode_thing = $this->thing;

        }

        // Need to code in the X and <number> conditions for creating new headcodes.

        // Write the variables to the db.
        $this->set();

        //$this->headcode_thing = $this->thing;

        $this->thing->log('Agent "Headcode" found headcode a pointed to it.');

    }

    function headcodeTime($input = null) {

        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $headcode_time = "x";
            return $headcode_time;
        }


        $t = strtotime($input_time);

        $this->hour = date("H",$t);
        $this->minute =  date("i",$t);

        $headcode_time = $this->hour . $this->minute;

        if ($input == null) {$this->headcode_time = $headcode_time;}

        return $headcode_time;



    }

    function getEndat() {

        if (($this->run_at != "x") and ($this->quantity != "x")) {
            $this->end_at = $this->thing->json->time(strtotime($this->run_at . " " . $this->quantity . " minutes"));
        } else {
            $this->end_at = "x";
        }

        return $this->end_at;
    }

    function getAvailable() {

        // This proto-typical headcode manages (available) time.
        // From start_at and current_time we can calculate elapsed_time.

        if (!isset($this->end_at)) {
            $this->getEndat();
        }

        if (strtotime($this->current_time)  < strtotime($this->run_at)) {
            $this->available = strtotime($this->end_at) - strtotime($this->run_at);
           // $this->available = $this->quantity;
        } else {
            $this->available = strtotime($this->end_at) - strtotime($this->current_time);
        }

        // Allow negative block ticks (time quanta)
        // This is needed to track behind block completion.
        //if ($this->available < 0) {$this->available = 0;}
        //
        $this->thing->log('Agent "Headcode" identified ' . $this->available . ' resource units available.');

    }


    function extractConsists($input) {

        // devstack: probably need a word lookup 
        // or at least some thinking on how to differentiate Headcode from NnX
        // as a valid consist.

        if (!isset($this->consists)) {
            $this->consists = array();
        }

        $pattern = "|[A-Za-z]|";

        preg_match_all($pattern, $input, $m);
//        return $m[0];
        $this->consists = $m[0];
        //array_pop($arr);

        return $this->consists;


    }

    function getConsist($input = null) {

        $consists = $this->extractConsists($input);


        if ((count($consists) == 1) and (strtolower($consists[0]) != 'train')) {
            $this->consist = $consists[0];
            $this->thing->log('Agent "Headcode" found a consist (' . $this->consist . ') in the text.');
            return $this->consist;
        }

        $this->consist = "X";

        if (count($consists == 0)) {return false;}
        if (count($consists > 1)) {return true;}

        return true;

    }



    function extractHeadcodes($input = null) {

        if (!isset($this->head_codes)) {
            $this->head_codes = array();
        }

        $pattern = "|\d[A-Za-z]{1}\d{2}|";

        preg_match_all($pattern, $input, $m);
        $this->head_codes = $m[0];

        return $this->head_codes;
    }

    function extractHeadcode($input)
    {
        $head_codes = $this->extractHeadcodes($input);

        if (count($head_codes) == 1) {
            $this->head_code = $head_codes[0];
            $this->thing->log('Agent "Headcode" found a headcode (' . $this->head_code . ') in the text.');
            return $this->head_code;
        }

        if (count($head_codes == 0)) {return false;}
        if (count($head_codes > 1)) {return true;}

        return true;
    }


    function read()
    {
        $this->thing->log("read");

//        $this->get();
        return $this->available;
    }



    function addHeadcode() {
        //$this->makeHeadcode();
        $this->get();
        return;
    }

    function makeTXT() {
        if (!isset($this->headcode_list)) {$this->getHeadcodes();}
        $this->getHeadcodes();

        $txt = 'These are HEADCODES for RAILWAY ' . $this->headcode->nuuid . '. ';
        $txt .= "\n";
        $txt .= count($this->headcode_list). ' Headcodes retrieved.';

        $txt .= "\n";


        //$txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
        $txt .= " " . str_pad("HEAD", 4, " ", STR_PAD_LEFT);
//        $txt .= " " . str_pad("ALIAS", 10, " " , STR_PAD_RIGHT);
        //$txt .= " " . str_pad("DAY", 4, " ", STR_PAD_LEFT);

        //$txt .= " " . str_pad("RUNAT", 6, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("RUNTIME", 8, " ", STR_PAD_LEFT);

        $txt .= " " . str_pad("AVAILABLE", 6, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("QUANTITY", 9, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("CONSIST", 6, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("ROUTE", 6, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";



        //$txt = "Test \n";
        foreach ($this->headcode_list as $variable) {
            //$txt .= $variable['head_code'] . " | " . $variable['index'] . " | " . $variable['route'];
            //$txt .= $variable['consist'] . " | " .$variable['quantity'] . " | " . $variable['available'];
            //$txt .= " | " . $variable['run_at'];
            //$txt .= " | " . $variable['refreshed_at'];

        //    $txt .= "\n";


            //$txt .= str_pad($train['index'], 7, '0', STR_PAD_LEFT);
            $txt .= " " . str_pad(strtoupper($variable['head_code']), 4, "X", STR_PAD_LEFT);
            //$txt .= " " . str_pad($train['alias'], 10, " " , STR_PAD_RIGHT);

            $txt .= " " . str_pad($variable['run_at'], 8, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($variable['available'], 6, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($variable['quantity'], 9, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($variable['consist'], 6, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($variable['route'], 6, " ", STR_PAD_LEFT);

            $txt .= "\n";

        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

    }


    private function makeSMS() {

        $s = "GREEN";
        $sms_message = "HEADCODE " . strtoupper($this->head_code) ." | " . $s;
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " | ";

        $sms_message .= $this->route . " [" . $this->consist . "] " . $this->quantity;
 
//        $sms_message .= " | index " . $this->index;
//        $sms_message .= " | available " . $this->available;

        //$sms_message .= " | from " . $this->headcodeTime($this->start_at) . " to " . $this->headcodeTime($this->end_at);
        //$sms_message .= " | now " . $this->headcodeTime();
        $sms_message .= " | nuuid " . strtoupper($this->headcode->nuuid);
        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }

	private function Respond() {

		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = "headcode";


		//$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
		$this->thing_report['choices'] = $choices;

        //$this->makeTXT();


$available = $this->thing->human_time($this->available);

if (!isset($this->index)) {
    $index = "0";
} else {
    $index = $this->index;//
}

        $this->makeSMS();



  

		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>headcode state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $this->sms_message;

//		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

        $test_message .= '<br>run_at: ' . $this->run_at;
        $test_message .= '<br>end_at: ' . $this->end_at;


//		$test_message .= '<br>Requested state: ' . $this->requested_state;

			//$this->thing_report['sms'] = $sms_message;
			$this->thing_report['email'] = $this->sms_message;
			$this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;




        if (!$this->thing->isData($this->agent_input)) {
                        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        } else {
            $this->thing_report['info'] = 'Agent input was "' . $this->agent_input . '".' ;
        }

        $this->makeTXT();

        $this->thing_report['help'] = 'This is a headcode.';



		return;


	}

    function isData($variable) {
        if ( 
            ($variable !== false) and
            ($variable !== true) and
            ($variable != null) ) {
 
            return true;

        } else {
            return false;
        }
    }

    public function readSubject() 
    {

        $this->response = null;
        $this->num_hits = 0;

        $keywords = $this->keywords;

        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->from . " " . $this->subject);
        }


		//$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        // Is there a headcode in the provided datagram
        $this->extractHeadcode($input);
//var_dump($this->head_code);

        $this->headcode_id = new Variables($this->thing, "variables headcode " . $this->from);

        if (!isset($this->head_code) or ($this->head_code == false)) {
            $this->head_code = $this->headcode_id->getVariable('head_code', null);
            //var_dump($this->head_code);
            if (!isset($this->head_code) or ($this->head_code == false)) {
                $this->head_code = $this->getVariable('head_code', null);
                //var_dump($this->head_code);
            if (!isset($this->head_code) or ($this->head_code == false)) {
                $this->head_code = "0Z10";
                //var_dump($this->head_code);
            }

            }
        }


        //var_dump($this->head_code);



//        $this->headcode = new Variables($this->thing, "variables " . $this->head_code . " " . $this->from);
        //$this->get();
        $this->get();
        // Bail at this point if only a headcode check is needed.
        if ($this->agent_input == "extract") {return;}

        //$this->get();
        
//exit();
        $pieces = explode(" ", strtolower($input));


		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'headcode') {

                $this->read();
                return;
            }

        }

    foreach ($pieces as $key=>$piece) {
        foreach ($keywords as $command) {
            if (strpos(strtolower($piece),$command) !== false) {

                switch($piece) {


    case 'next':
        $this->thing->log("read subject nextheadcode");
        $this->nextheadcode();
        break;

   case 'drop':
   //     //$this->thing->log("read subject nextheadcode");
        $this->dropheadcode();
        break;


   case 'add':
   //     //$this->thing->log("read subject nextheadcode");
        //$this->makeheadcode();
        $this->get();
        break;


    default:

                                        }

                                }
                        }

                }


// Check whether headcode saw a run_at and/or run_time
// Intent at this point is less clear.  But headcode
// might have extracted information in these variables.

// $uuids, $head_codes, $this->run_at, $this->run_time



    if ($this->isData($this->head_code)) {
        $this->set();
        return;
    }

    $this->read();




                return "Message not understood";




		return false;

	
	}






	function kill() {
		// No messing about.
		return $this->thing->Forget();
	}

}

/* More on headcodes

http://myweb.tiscali.co.uk/gansg/3-sigs/bellhead.htm
1 Express passenger or mail, breakdown train en route to a job or a snow plough going to work.
2 Ordinary passenger train or breakdown train not en route to a job
3 Express parcels permitted to run at 90 mph or more
4 Freightliner, parcels or express freight permitted to run at over 70 mph
5 Empty coaching stock
6 Fully fitted block working, express freight, parcels or milk train with max speed 60 mph
7 Express freight, partially fitted with max speed of 45 mph
8 Freight partially fitted max speed 45 mph
9 Unfitted freight (requires authorisation) engineers train which might be required to stop in section.
0 Light engine(s) with or without brake vans

E     Train going to       Eastern Region
M         "     "     "         London Midland Region
N         "     "     "         North Eastern Region (disused after 1967)
O         "     "     "         Southern Region
S          "     "     "         Scottish Region
V         "     "     "         Western Region

*/
?>
