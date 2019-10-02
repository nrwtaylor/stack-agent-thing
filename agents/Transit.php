<?php
namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


ini_set("allow_url_fopen", 1);

class Transit 
{

    // This is a resource block.  It is a train which be run by the block scheduler.
    // It will respond to trains with a signal.
    // Red - Not available
    // Green - Slot allocated
    // Yellow - Next signal Red.
    // Double Yellow - Next signal Yellow

    // The block keeps track of the uuids of associated resources.
    // And checks to see what the block signal should be.  And pass and collect tokens.

    // This is the block manager.  They are an ex-British Rail signalperson.

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null) {


        if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;

        $this->keyword = "mordok";

        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();
        $this->thing_report['thing'] = $this->thing->thing;

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;

        $this->num_hits =0;

        $this->agent_prefix = 'Agent "Transit" ';

        $this->node_list = array("start"=>array("stop 1"=>array("stop 2","stop 1"),"stop 3"),"stop 3");
        $this->thing->choice->load('train');

        $this->keywords = array('run','change','next', 'accept', 'clear', 'drop','add','run','red','green');

//                'block' => array('default run_time'=>'105',
//                                'negative_time'=>'yes'),

        $this->current_time = $this->thing->json->time();


        //$this->default_run_time = $this->thing->container['api']['train']['default run_time'];
        //$this->negative_time = $this->thing->container['api']['train']['negative_time'];
        $this->default_run_time = $this->current_time;
        $this->negative_time = true;
        //$this->app_secret = $this->thing->container['api']['facebook']['app secret'];

        //$this->page_access_token = $this->thing->container['api']['facebook']['page_access_token'];

        $default_train_name = "transit";

//        $this->variables_agent = new Variables($this->thing, "variables " . $default_train_name . " " . $this->from);


        $this->current_time = $this->thing->json->time();

        // Loads in Train variables.
        $this->get(); 

		$this->thing->log('<pre> Agent "Transit" running on Thing '. $this->thing->nuuid . '.</pre>');
		$this->thing->log('<pre> Agent "Transit" received this Thing "'.  $this->subject . '".</pre>');

		$this->readSubject();

        if ($this->agent_input == null) {
		$this->respond();
        }
        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

		$this->thing->log($this->agent_prefix . 'completed.');

        $this->thing_report['log'] = $this->thing->log;



		return;

		}





    function set()
    {

        // A block has some remaining amount of resource and 
        // an indication where to start.


        // This makes sure that
        if (!isset($this->train_thing)) {
            $this->train_thing = $this->thing;
        }

        if ($requested_state == null) {
            $requested_state = $this->requested_state;
        }

        // Update calculated variables.

        //$this->variables_agent->setVariable("state", $this->state);
        //$this->variables_agent->setVariable("stop_id", $this->transit_id);
        //$this->variables_agent->setVariable("agency", $this->agency);

        //$this->variables_agent->setVariable("refreshed_at", $this->refreshed_at);

        $this->identity = $this->from;

        $this->thing->db->setFrom($this->identity);
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("transit", "state"), $this->state );
        $this->thing->json->writeVariable( array("transit", "transit_id"), $this->transit_id );
        $this->thing->json->writeVariable( array("transit", "agency"), $this->agency );

        $this->thing->json->writeVariable( array("transit", "refreshed_at"), $this->refreshed_at );

//        $this->thing->choice->save('train', $this->state);
//        $this->state = $requested_state;

        $this->refreshed_at = $this->current_time;

        return;
    }

    function get($train_time = null)
    {
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

    function getFlag()
    {
        $this->flag_thing = new Flag($this->thing, 'flag');
        $this->flag = $this->flag_thing->state;

        return $this->flag;
    }

    function setFlag($colour)
    {
        $this->flag_thing = new Flag($this->thing, 'flag '.$colour);
        $this->flag = $this->flag_thing->state;

        return $this->flag;
    }

	private function respond() {

		// Thing actions
		$this->thing->flagGreen();
		// Generate email response.

        $this->state = 'green';

        $this->requested_state = 'green';

        $this->set();


		$to = $this->thing->from;
		$from = "transit";

		$choices = $this->thing->choice->makeLinks($this->state);
//		$this->thing_report['choices'] = $choices;
        $choice = false;

        //$s = $this->block_thing->state;
        if (!isset($this->flag)) {
            $this->flag = strtoupper($this->getFlag());
        }

		$sms_message = "TRANSIT ";
        $sms_message .= " | flag " . $this->flag;

//        $sms_message .= " | alias " . strtoupper($this->alias);
//        $route_description = $this->route . " [" . $this->consist . "] " . $this->quantity;
        $sms_message .= " | " . $this->stop;


        $sms_message .= " | nuuid " . substr($this->thing->uuid,0,4); 
        $sms_message .= " | rtime " . number_format($this->thing->elapsed_runtime()) . 'ms';


		$test_message = 'Last thing heard: "' . $this->subject . '".  Your next choices are [ ' . $choices['link'] . '].';
		$test_message .= '<br>Train state: ' . $this->state . '<br>';

		$test_message .= '<br>' . $sms_message;

		$test_message .= '<br>Current node: ' . $this->thing->choice->current_node;

//		$test_message .= '<br>Requested state: ' . $this->requested_state;

		$this->thing_report['sms'] = $sms_message;
		$this->thing_report['email'] = $sms_message;
		$this->thing_report['message'] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

                        $this->thing_report['info'] = $message_thing->thing_report['info'] ;



		//$this->thing->email->sendGeneric($to,$from,$this->subject, $test_message, $choices);

//        $this->thing->json->writeVariable( array("transit", "agency"), $this->agency );


$this->thing_report['help'] = 'This is a bus with people on it.';


		//echo '<pre> Agent "Account" email NOT sent to '; echo $to; echo ' </pre>';
//echo $message;

		return;


	}



    function extractStops($input) {

        if (!isset($this->stops)) {
            $this->stops = array();
        }

        $pattern = "|\d{5}$|";
//$pattern = "|\(d{5})$|";
        //$pattern = '/(\d{5})/';
        //$pattern = "|^[0-9]{5}\z|";
        //$pattern = "|^[0-9]{5}$|";
$pattern = '/\b(\d{5})\b/';
        preg_match_all($pattern, $input, $m);
        $this->stops = $m[0];
//var_dump($this->stops);
//exit();
//echo $input;
//var_dump($this->stops);
//exit();
        return $this->stops;
    }

    function getStop($input) {

        $stops = $this->extractStops($input);

        if (count($stops) == 1) {
            $this->stop = $stops[0];
            $this->thing->log('Agent "Transit" found a stop (' . $this->stop . ') in the text.');
            return $this->stop;
        }

        if (count($stops) == 0) {return false;}
        if (count($stops) > 1) {return true;}

        return true;
    }


    function getAgency($input) 
    {
        $this->agency = "translink";
    
        if ( ( substr($input, 0, 4) == "1778" ) or ( mb_substr($input,0,4) == "1778" ) ) {
            $this->agency = "translink";
            return $this->agency;
        }

        return $this->agency;

    }

    public function readSubject() 
    {
        $this->response = null;

        $keywords = $this->keywords;

        if ($this->agent_input != null) {

            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);

        } else {

            $input = strtolower($this->subject);

        }

//var_dump($input);
//exit();

        $this->input = $input;

		$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;


        //$this->stop = "X";
        $this->getStop($haystack); // Can X be improved on?

        $this->getAgency($input);    
//var_dump($this->stop);
//exit();

        if (isset($this->stop)) {
            $this->thing->log('Agent "Transit" found a stop (' . $this->stop . ') for agency ' . $this->agency . '.');
            $this->transit_id = $this->stop;
        }


        // Bail at this point if only a headcode check is needed.
        if ($this->agent_input == "extract") {return;}

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == 'transit') {

                return;
            }
        }

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) {

                        case 'red':
                            $this->setFlag('red');
                            break;

                        case 'green':
                            $this->setFlag('green');
                            break;

                        case 'on':
                            //$this->setFlag('green');
                            //break;


                        default:
                     }
                }
            }
        }

        return "Message not understood";
	}






	function kill() {
		// No messing about.
		return $this->thing->Forget();
	}


}
