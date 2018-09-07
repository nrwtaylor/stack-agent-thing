<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '../vendor/autoload.php';
//require '/var/www/html/stackr.ca/vendor/autoload.php';
//require_once '/var/www/html/stackr.ca/agents/message.php';


//require_once '/var/www/html/stackr.ca/agents/route.php';
//require_once '/var/www/html/stackr.ca/agents/message.php';

//require '/var/www/html/stackr.ca/public/agenthandler.php'; // until the callAgent call can be
								// factored to
								// call agent 'Agent'

ini_set("allow_url_fopen", 1);

class Runat 
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

        $this->agent_prefix = 'Agent "Runat" ';

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

        //$this->runat = new Variables($this->thing, "variables runat " . $this->from);
        $this->runat = false;

        // Read the subject to determine intent.
		$this->readSubject();

        // Generate a response based on that intent.
        // I think properly capitalized.
        //$this->set();
        if ($this->agent_input == null) {
		    $this->Respond();
        }

        $this->set();



        $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );

        $this->thing_report['log'] = $this->thing->log;



		return;

		}


    function set()
    {
        //$this->head_code = "0Z15";
        //$headcode = new Variables($this->thing, "variables headcode " . $this->from);

        if ($this->runat == false) {return;}

        $this->runat->setVariable("refreshed_at", $this->current_time);
        $this->runat->setVariable("day", $this->day);
        $this->runat->setVariable("hour", $this->hour);
        $this->runat->setVariable("minute", $this->minute);

//        $this->flag->setVariable("state", $this->state);

        $this->thing->log( $this->agent_prefix .' saved ' . $this->day . " " . $this->hour . " " . $this->minute . ".", "DEBUG" );



  //      $this->thing->json->writeVariable( array("run_at", "day"), $this->day );
  //      $this->thing->json->writeVariable( array("run_at", "hour"), $this->hour );
  //      $this->thing->json->writeVariable( array("run_at", "minute"), $this->minute );
  //      $this->thing->json->writeVariable( array("run_at", "refreshed_at"), $this->current_time );



        

        return;
    }

/*
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
*/

    function getRunat()
    {

        if (!isset($this->run_at)) {
            if (isset($run_at)) {
               $this->run_at = $run_at;
            } else {
                $this->run_at = "Meep";
            }
        }
    return $this->run_at;

    }

    function getHeadcodes()
    {
        $this->headcode_list = array();
        // See if a headcode record exists.
        //require_once '/var/www/html/stackr.ca/agents/findagent.php';
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

        if ($this->runat == false) {return;}

        $this->day = $this->runat->getVariable("day");
        $this->hour = $this->runat->getVariable("hour");
        $this->minute = $this->runat->getVariable("minute");


        return;
    }

    function isToday($text = null)
    {

        $unixTimestamp = strtotime($this->current_time);
        $day = date("D", $unixTimestamp);

        if (strtoupper($this->day) == strtoupper($day)) {
            return true;
        } else {
            return false;
        }

        // true = yes, false = no
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

    function extractNumbers($input =  null)
    {
        $this->numbers = array();

        //require_once '/var/www/html/stackr.ca/agents/number.php';
        $agent = new Number($this->thing, "number");
        $numbers = $agent->numbers;
        if (count($numbers) > 0) {
            $this->numbers = $numbers; 
        }

        return;
    }

    function extractNumber($input =  null)
    {
        $this->number = "X";

        if (!isset($this->numbers)) {$this->extractNumbers($input);}
        //require_once '/var/www/html/stackr.ca/agents/number.php';
        //$agent = new Number($this->thing, "number");

        if (count($this->numbers) == 1) {$this->number = $this->numbers[0]; 
        }

    }


    function extractRunat($input = null) 
    {
        $this->parsed_date = date_parse($input);

        //$this->day = $this->parsed_date['year'] ."/".$this->parsed_date['month']. "/" . $this->parsed_date['day'];

        $this->minute = $this->parsed_date['minute']; 
        $this->hour = $this->parsed_date['hour']; 

        $this->extractDay($input);

        if ($this->day == false) {
            $this->day = "X";
        }


        if (($this->minute == false) and ($this->hour == false)) {
            $this->minute = "X";
            $this->hour = "X";

            $this->extractMeridian($input);

            return null;
        }

        return array($this->day,$this->hour, $this->minute);
    }

    function extractMeridian($input = null)
    {

        if (!isset($this->number)) {$this->extractNumber($input);}

        if (count($this->numbers) == 2) {
            if (($this->numbers[0] <= 12) and ($this->numbers[0]>=1)) {
            $this->hour = $this->numbers[0];
            }
            if (($this->numbers[1] >=1) and ($this->numbers[1]<=59)) {
                $this->minute = $this->numbers[1];
            }
        }

        if (count($this->numbers) == 1) {
            if (($this->numbers[0] <= 12) and ($this->numbers[0]>=1)) {
            $this->hour = $this->numbers[0];
            }
        }



    $pieces = explode(strtolower($input), " ");

    $keywords = array("am","pm","morning","evening","late","early");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
                        case 'stop':    

                            if ($key + 1 > count($pieces)) {
                                //echo "last word is stop";
                                $this->stop = false;
                                return "Request not understood";
                            } else {
                                $this->stop = $pieces[$key+1];
                                $this->response = $this->stopTranslink($this->stop);
                                return $this->response;
                            }
                            break;

                        case 'am':
                                break;

                        case 'pm':
                                $this->hour = $this->hour + 12;
                                break;

                    default:
                    }

                }
            }
        }



    }


    function extractDay($input = null) 
    {
        $this->day = "X";


//$unixTimestamp = strtotime($input);
//$this->day = date("l", $unixTimestamp);
 


        $days = array("MON"=>array("mon","monday","M"),
                      "TUE"=>array("tue","tuesday","Tu"),
                      "WED"=>array("wed","wednesday","W"),
                      "THU"=>array("thur","thursday","Th"),
                        "FRI"=>array("fri","friday","F","Fr"),
                    "SAT"=>array("sat","saturday","Sa"),
                    "SUN"=>array("sun","sunday","Su"));

        foreach ($days as $key=>$day_names) {
           

            if (strpos(strtolower($input), strtolower($key)) !== false) {
                $this->day = $key;
                break;
            }

            foreach ($day_names as $day_name) {

                if (strpos(strtolower($input), strtolower($day_name)) !== false) {
                    $this->day = $key; 
                    break;
                }
            }
        }

        $this->parsed_date = date_parse($input);

        if (($this->parsed_date['year'] != false) and ($this->parsed_date['month'] != false) and ($this->parsed_date['day'] != false)) {

            $date_string = $this->parsed_date['year'] ."/".$this->parsed_date['month']. "/" . $this->parsed_date['day'];

            $unixTimestamp = strtotime($date_string);
            $day = date("D", $unixTimestamp);

            if ($this->day == "X") {$this->day = $day;}
        }

        $this->thing->log("found day " . $this->day . ".");

        return $this->day;
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


    private function makeSMS() {

        $sms_message = "RUNAT";
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " | day " . $this->day . " hour " . $this->hour . " minute " . $this->minute;

        $sms_message .= " | nuuid " . strtoupper($this->runat->nuuid);
        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;


    }

	private function Respond() {

		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = "runat";


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
            $input = strtolower($this->subject);
        }


		//$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        // Is there a headcode in the provided datagram
        $this->extractNumbers($input);
        $this->extractRunat($input);
        $this->extractDay($input);

        //$this->runat = new Variables($this->thing, "variables runat " . $this->from);

        if ($this->agent_input == "extract") {return;}

        $pieces = explode(" ", strtolower($input));
        if (explode(" " , strtolower($this->agent_input))[0] == "extract") {return;}


        $this->runat = new Variables($this->thing, "variables runat " . $this->from);


    if (($this->day == "X") and ($this->minute == "X") and ($this->hour == "X")) {

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
