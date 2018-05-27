<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from Tick

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Snow {

    function __construct(Thing $thing, $agent_input = null)
    {

        if ($agent_input == null) {
            $this->agent_input = $agent_input;
        }

        $this->thing = $thing;
        $this->agent_name = 'snow';
        $this->agent_prefix = '"Snow" ' . ucwords($this->agent_name) . '" ';

        $this->thing_report['thing'] = $this->thing->thing;

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
        // I think.
        // Instead.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
		//$this->sqlresponse = null;

		$this->node_list = array("snow"=>array("stop","snow"));

        $this->thing->log( 'Agent "Snow" running on Thing '. $this->thing->nuuid . '.');

        $this->variables_agent = new Variables($this->thing, "variables snow " . $this->from);
        $this->current_time = $this->thing->json->time();

        $this->get();
		$this->readSubject();

        // frame

        $this->variable = 1;
        $this->snow();

        // frame
        $this->set();
        if ($this->agent_input == null) {
 		    $this->respond();
        }

		$this->thing->flagGreen();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.' );

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;

    	return;
	}



    function set()
    {
        $this->variables_agent->setVariable("snowflakes", $this->snowflakes);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

//        $this->thing->choice->save('usermanager', $this->state);

        return;
    }


    function get()
    {
        $this->snowflakes = $this->variables_agent->getVariable("snowflakes");
        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        $this->thing->log($this->agent_prefix .  'loaded ' . $this->snowflakes . ".");

        return;
    }



    function countSnow()
    {
        // devstack count snowflakes on stack identity
        // This is a count of all snow everywhere.
        $this->snowflakes += 1;
    }

    function getSnowflake()
    {

        $thing = new Thing(null);

        $this->snowflake = new Snowflake($this->thing);
        $this->countSnow();
    }

    function imagineSnow ()
    {

        // because it is the same as the number falling on you.

        // In the performed case.

        new Thought($this->thing,"thought");

        // 1 billion in a cubic foot
        // An inch covers.
        // So a 12th of that.

        if ($this->snowflakes > 1e9 / 12) {  
            new Stop($this->thing);
        }
    }


    private function makeSMS() {

        switch ($this->snowflakes) {
            case 1:
                $sms = "SNOW | A snowflake falls. Text SNOWFLAKE.";
                break;
            case 2:
                $sms = "SNOW | Another one.  Appears. Text SNOW.";
                break;

            case null;

            default:
                $sms = "SNOW | It is snowing. Everywhere.";

        }

            $sms .= " | snowflakes " . $this->snowflakes;

            $this->sms_message = $sms;
            $this->thing_report['sms'] = $sms;

    }


    private function makeEmail() {

        switch ($this->snowflakes) {
            case 1:
                $subject = "Snow request received";

                $message = "It is snowing.\nhttps://www.facebook.com/yokoonopage/photos/a.10150157196475535.335529.10334070534/10152999025540535/?type=1&theater\n\n";


                break;

            case null;

            default:
               $subject = "Snow request received";

               $message = "It is still snowing.\n\n";


        }

            $this->message = $message;
            $this->thing_report['email'] = $message;

    }


    private function makeChoices()
    {

            $choices = $this->thing->choice->makeLinks('snow');

            $this->choices = $choices;
            $this->thing_report['choices'] = $choices;

    }



	public function respond() {

		// Thing actions
		$this->thing->flagGreen();

		// Get the current user-state.
        $this->makeSMS();
        $this->makeEmail();
        $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['sms'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] = $this->agent_prefix . 'responding to the word snow';


		return;
	}



	public function readSubject() 
    {
        // Ignore subject.
		return;
	}


	function snow() {

        // Call the Usermanager agent and update the state
        // Stochastically call snow.
        if (rand(1, $this->variable) == 1) {$this->getSnowflake();}

        $this->thing->log( $this->agent_prefix .' says, "Think that snow is falling everwhere\nall the time.\n"' );


		return;
	}













}









?>
