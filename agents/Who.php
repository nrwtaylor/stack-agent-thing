<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Who
{
	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;

        $this->start_time = $thing->elapsed_runtime();
        $this->agent_prefix = 'Agent "Who" ';

        $this->thing = $thing;
        $this->thing->elapsed_runtime();

        $this->thing_report['thing'] = $this->thing->thing;

		$this->agent_name = 'who';

		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

        $this->email = $this->thing->container['stack']['email'];

        $this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid . '.');

        $this->current_time = $this->thing->time();
        $this->verbosity = 9;

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

		$this->sqlresponse = null;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];
        $this->nominal = $thing->container['stack']['nominal'];
        $this->mail_regulatory = $thing->container['stack']['mail_regulatory'];

        $this->entity_name = $thing->container['stack']['entity_name'];

		// Allow for a new state tree to be introduced here.
		$this->node_list = array("who"=>array("privacy", "weather"));

		$this->readSubject();

        $this->respond();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );
        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());

        $this->thing_report['log'] = $this->thing->log;
        $this->thing_report['response'] = $this->response;
		return;

	}

    public function who()
    {

        $mail_regulatory = str_replace("\r", "", $this->mail_regulatory);
        $mail_regulatory = str_replace("\n", " ", $mail_regulatory);

        $this->sms_message = 'WHO | ' . ucwords($this->nominal) . ' | ' . $this->email . ' | ' . ltrim($mail_regulatory) ;
 
        $this->message = $this->sms_message;

        return $this->message;
    }



	private function respond()
    {
		// Thing actions
		$this->thing->flagGreen();

		// Generate email response.
		$to = $this->thing->from;
		$from = "who";

        $this->makeSMS();
        $this->makeChoice();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $thing_report['info'] = $message_thing->thing_report['info'] ;
        }

		$this->thing_report['help'] = 'Who manager';

		return $this->thing_report;


	}

    private function makeMessage()
    {


    }

    private function makeSMS()
    {
        if (!isset($this->sms_message)) {
            $this->sms_message = "WHO | Message not understood.";
        }
        $this->thing_report['sms'] = $this->sms_message;
    }

    private function makeChoice()
    {
        $this->thing->choice->Create($this->agent_name, $this->node_list, "who");
        $choices = $this->thing->choice->makeLinks('who');
        $this->thing_report['choices'] = $choices;
    }

	private function nextWord($phrase) {


	}

	public function readSubject()
    {

		$this->response = null;

		$keywords = array('?');
		$input = strtolower($this->subject);
		$prior_uuid = null;

		$pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            $input = $this->subject;
            if ($input == "who") {
			    $this->response = "Single word who received";
                $this->thing->log('got a single keyword.');
                $this->who();
                return;
            }

            $this->who();
            $this->response = "Provided contact details.";
            return;
        }
/*
		// If there are more than one piece then look at order.
        $this->thing->log('now checking pieces.');

		foreach ($pieces as $key=>$piece) {
			foreach ($keywords as $command) {
				if (strpos(strtolower($piece),$command) !== false) {
					switch($piece) {
						case 'contact':

                            $this->thing->log($this->agent_prefix . 'found a question mark.');

                            $this->contact();
                            $this->response = "Saw the word contact.";
                            return;

						default:
							// default

					}

				}
			}

		}

        // Okay so we arrive at this point not knowing what the message is.
        // Confirm it ends in a ? mark.

        $test = "?";
        // https://stackoverflow.com/questions/619610/whats-the-most-efficient-test-of-whether-a-php-string-ends-with-another-string
        $endsWith = substr_compare( $input, $test, -strlen( $test ) ) === 0;
        if ( $endsWith ) {
            $this->contact();
            return;
        }
		// Message not understood
        $this->contact();
*/
        $this->response = true;
        //$this->response = "Message not understood.";
        return;
	}

}

?>
