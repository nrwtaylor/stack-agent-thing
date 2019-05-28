<?php
namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Runtime 
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

        $this->agent_input = $agent_input;

        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();
        $this->thing_report['thing'] = $this->thing->thing;

        $this->agent_prefix = 'Agent "Runtime" ';

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

       // $this->runat = new Variables($this->thing, "variables runat " . $this->from);


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


        $this->current_time = $this->thing->json->time();


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

        $this->state = null; // to avoid error messages
        $this->runtime = false;

        //$this->runtime = new Variables($this->thing, "variables runtime " . $this->from);


        // Read the subject to determine intent.
		$this->readSubject();

        // Generate a response based on that intent.
        // I think properly capitalized.
        //$this->set();

        if ($this->agent_input == null) {
		    $this->Respond();
        }

//exit();
        $this->set();



        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;



		return;

		}


    function set()
    {

        if ($this->runtime == false) {return;}

        $this->runtime->setVariable("refreshed_at", $this->current_time);
        $this->runtime->setVariable("minutes", $this->minutes);

        $this->thing->log( $this->agent_prefix .' saved ' . $this->minutes . ".", "DEBUG" );

  //      $this->thing->json->writeVariable( array("run_at", "day"), $this->day );
  //      $this->thing->json->writeVariable( array("run_at", "hour"), $this->hour );
  //      $this->thing->json->writeVariable( array("run_at", "minute"), $this->minute );
  //      $this->thing->json->writeVariable( array("run_at", "refreshed_at"), $this->current_time );



        

        return;
    }

    function getRuntime()
    {
        if (!isset($this->run_time)) {
            if (isset($run_time)) {
               $this->run_time = $run_time;
            } else {
                $this->run_at = "Meep";
            }
        }
        return $this->run_time;
    }


    function getHeadcodes()
    {

        $this->headcode_list = array();
        // See if a headcode record exists.
        require_once '/var/www/html/stackr.ca/agents/findagent.php';
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

    function get($run_at = null)
    {
        if ($this->runtime == false) {return;}

        $this->minutes = $this->runtime->getVariable("minutes");


        return;
    }

    function extractRuntime($input = null)
    {

        $this->minutes = "X";
        $periods = array(1440=>array("d","days","dys","dys","dy", "day"),
                        60=>array("h","hours","hrs","hs","hr"),
                      1=>array("minutes","m", "mins","min","mn"));


        $pieces = explode(" ", $input);
        $previous_piece = null;

        $list = array();

        foreach ($pieces as $key=>$piece) {

            foreach ($periods as $multiplier=>$period) {

//echo $piece . " " . $period;
//echo "<br>";

                foreach ($period as $period_name) {

                if (($period_name == $piece) and (is_numeric($previous_piece))) {

                    $list[] = $previous_piece * $multiplier;
                } elseif (is_numeric($piece)) {
                    // skip

                } elseif (is_numeric(str_replace($period_name, "", $piece))) {
                    $list[] = str_replace($period_name, "", $piece) * $multiplier;
 
                }
                }

            }

            $previous_piece = $piece;
        }
//var_dump($input);
//var_dump($list);
//exit();
        // If nothing found assume a lone number represents minutes
        if (count($list) == 0) {
            foreach ($pieces as $key=>$piece) {    

                if ($this->is_decimal($piece)) {
                    // Assue this is hours
                    $list[] = $piece * 60;
                } elseif (is_numeric($piece)) {

                    $list[] = $piece;

                }

            }
        }

//var_dump($input);
//var_dump($list);

        if (count($list) == 1) { $this->minutes = $list[0];}

//exit();

        return $this->minutes;
    }

    function is_decimal( $val )
    {
        return is_numeric( $val ) && floor( $val ) != $val;
    }

    function extractTime($input = null) 
    {
        $this->minutes = "X";
        $days = array(22=>array("default"),
                      15=>array("quarter hour","quarter", "1/4","0.25"),
                      30=>array("half hour","half hour", "half", "0.5"),
                      60=>array("hour","hr"),
                      1440=>array("day"));

        foreach ($days as $key=>$day_names) {
           

            if (strpos(strtolower($input), strtolower($key)) !== false) {
                $this->minutes = $key;
                break;
            }

            foreach ($day_names as $day_name) {

                if (strpos(strtolower($input), strtolower($day_name)) !== false) {
                    $this->minutes = $key; 
                    break;
                }
            }
        }


        return $this->minutes;
    }




    function read()
    {
        $this->thing->log("read");
        return;
    }

    function makeTXT() {
        $txt = $this->sms_message;

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;





    }


    private function makeSMS()
    {
        $sms_message = "RUNTIME";
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " | minutes " . $this->minutes;

        $sms_message .= " | nuuid " . strtoupper($this->runtime->nuuid);
        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

	private function Respond()
    {
		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = "runtime";


		//$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
		$this->thing_report['choices'] = $choices;

        //$this->makeTXT();

        $this->makeSMS();



  

		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>headcode state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $this->sms_message;

//		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

//        $test_message .= '<br>run_at: ' . $this->run_at;
        //$test_message .= '<br>end_at: ' . $this->end_at;


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

        $this->thing_report['help'] = 'This is the runtime manager.';

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
            $input = strtolower($this->subject);
        }
		//$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        // Is there a headcode in the provided datagram

//        if ($this->agent_input == "runtime") {
//	        $this->runtime = new Variables($this->thing, "variables runtime " . $this->from);
//        	$this->getRuntime();
//	return;
//	}


        $this->extractRuntime($input);

        if ($this->minutes == "X") {
        $this->extractTime($input);
        }

	

        //$this->runat = new Variables($this->thing, "variables runat " . $this->from);



        if ($this->agent_input == "extract") {return;}

//        if ($this->agent_input == "runtime") {return;}




        $this->runtime = new Variables($this->thing, "variables runtime " . $this->from);


        $pieces = explode(" ", strtolower($input));

    if ($this->minutes == "X") {

        $this->get();
        return;
    }





                return "Message not understood";




		return false;

	
	}






	function kill() {
		// No messing about.
		return $this->thing->Forget();
	}

}

?>
