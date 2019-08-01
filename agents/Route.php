<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Route 
{

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) 
    {
//        $this->start_time = microtime(true);

        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->thing = $thing;
//        $this->start_time = $this->thing->elapsed_runtime();
        $this->thing_report['thing'] = $this->thing->thing;

        $this->agent_prefix = 'Agent "Route" ';

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.</pre>');
//        $this->thing->log('<pre> Agent "Headcode" received this Thing "'.  $this->subject . '".</pre>');


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

        $this->default_route = "Place";


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

       $this->variables = new Variables($this->thing, "variables route " . $this->from);


        $this->state = null; // to avoid error messages


        // Read the subject to determine intent.
		$this->readSubject();

        // Generate a response based on that intent.
        // I think properly capitalized.
		$this->Respond();



//		$this->thing->log('<pre> Agent "Headcode" completed</pre>');


        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;



		return;

		}





    function set()
    {
        //$this->route ="meep";
        $this->variables->setVariable("route", $this->route);
        $this->variables->setVariable("head_code", $this->head_code);
        $this->variables->setVariable("refreshed_at", $this->current_time);

        return;
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

    function getRoutes() {

        $this->routes = array();
        // See if a headcode record exists.
        $findagent_thing = new FindAgent($this->thing, 'route');


        foreach (array_reverse($findagent_thing->thing_report['things']) as $thing_object) {
            // While timing is an issue of concern

            $uuid = $thing_object['uuid'];

            $thing= new Thing($uuid);
            $variables = $thing->account['stack']->json->array_data;


            if (isset($variables['route'])) {
                //$head_code = $variables['route']['head_code'];
                //$route = $variables['route']['route'];

                //$variables['headcode'][] = $thing_object['task'];
                $this->routes[] = $variables['route'];
            }
        }

        return $this->routes;

    }


    function getRoute($selector = null){
            //$this->route = $this->thing->json->readVariable( array("headcode", "route") );
            $this->route = "Place";

        if (!isset($this->routes)) {$this->getRoutes();}

        foreach ($this->routes as $key=>$route) {

            //var_dump( $key);
            //echo $route['route'];;
        }

    }


    function get($route = null)
    {
        // This is a request to get the headcode from the Thing
        // and if that doesn't work then from the Stack.

        // 0. light engine with or without break vans.
        // Z. Always has been a special.
        // 10. Because starting at the beginning is probably a mistake. 
        // if you need 0Z00 ... you really need it.

        if (!isset($this->route)) {
            $this->route = $this->variables->getVariable('route');
            $this->head_code = $this->variables->getVariable('head_code');

        }

        $this->getRoute();

        return;
    }

    function dropRoute() {
        $this->thing->log($this->agent_prefix . "was asked to drop a route.");

        return;
        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->headcode_thing)) {
            $this->headcode_thing->Forget();
            $this->headcode_thing = null;
        }

        $this->get();
 
    }


    function makeRoute($head_code = null) {

        $this->route = "Place";

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

        //echo $t->format("Y-m-d H:i:s");
        $this->hour = date("H",$t);
        $this->minute =  date("i",$t);

        $headcode_time = $this->hour . $this->minute;

        if ($input == null) {$this->headcode_time = $headcode_time;}

        return $headcode_time;



    }




    function read()
    {
        $this->thing->log("read");
    }



    function addHeadcode() {
        //$this->makeHeadcode();
        $this->get();
        return;
    }

    function makeTXT() {

        $txt = "Test \n";
        foreach ($this->routes as $variable) {
            //$txt .= $variable['head_code'] . " | " . $variable['route'];

            if (isset($varibale['route'])) {
                $txt .= $variable['route'];
            }
            $txt .= "\n";
        }

        $this->thing_report['txt'] = $txt;
    }


	private function Respond() {

		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = "route";

    
		//$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
		$this->thing_report['choices'] = $choices;

        $this->makeTXT();



		$sms_message = "ROUTE " . ucwords($this->route);

//        $sms_message .= " | headcode " . strtoupper($this->head_code);
        $sms_message .= " | nuuid " . strtoupper($this->variables->nuuid);
        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";



			$this->thing_report['sms'] = $sms_message;
			$this->thing_report['email'] = $sms_message;
			$this->thing_report['message'] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;




        if (!$this->thing->isData($this->agent_input)) {
                        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        } else {
            $this->thing_report['info'] = 'Agent input was "' . $this->agent_input . '".' ;
        }

        $this->thing_report['help'] = 'This is a route.';


		//echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

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


        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->from . " " . $this->subject);
        }


        // Is there a headcode in the provided datagram
        $headcode = new Headcode($this->thing, "extract");
        if (isset($headcode->head_code)) {
            $this->head_code =  $headcode->head_code;
        }
        //if (!isset($this->head_code)) {$this->route = "Place";}
//var_dump($this->head_code);

        // Bail at this point if only a headcode check is needed.
        if ($this->agent_input == "extract") {return;}

        $this->get();


        $pieces = explode(" ", strtolower($input));


		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'route') {
                $this->read();
                return;
            }

        }

    foreach ($pieces as $key=>$piece) {
        foreach ($this->keywords as $command) {
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
        //$this->read();                                                    //echo 'default';

                                        }

                                }
                        }

                }


// Check whether headcode saw a run_at and/or run_time
// Intent at this point is less clear.  But headcode
// might have extracted information in these variables.

// $uuids, $head_codes, $this->run_at, $this->run_time



//if ( (isset($this->run_at)) and (isset($this->quantity)) ) {
//echo $this->head_code;
//var_dump( ($this->head_code !== true) );
//exit();
//$this->head_code = true;
    if ($this->isData($this->route)) {
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
