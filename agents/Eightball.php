<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class EightBall {

	public $var = 'hello';


    function __construct(Thing $thing, $text = null) {

        $this->start_time = microtime(true);

		$this->agent_name = "eightball";
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
		$this->test= "Development code";

//      This is how old roll.php is.
//		$thingy = $thing->thing;
		$this->thing = $thing;

         $this->thing_report  = array("thing"=>$this->thing->thing);


        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;


        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log($this->agent_prefix . "received this Thing ".  $this->subject . '".');


		$this->readSubject();

		$this->thing_report = $this->respond();

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($milliseconds) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;

		return;

	}


// -----------------------

	private function respond() {


		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;

		//echo "to:". $to;

		$from = "eightball";



        $i = rand(1,20);

        switch ($i) {
            case 1:
                $answer = "It is certain";
                break;
            case 2:
                $answer = "It is decidely so";
                break;
            case 3:
                $answer = "Without a doubt";
                break;
            case 4:
                $answer = "Yes definitely";
                break;
            case 5:
                $answer = "You may rely on it";
                break;

            case 6:
                $answer = "As I see it, yes";
                break;

            case 7:
                $answer = "Most likely";
                break;

            case 8:
                $answer = "Outlook good";
                break;

            case 9:
                $answer = "Yes";
                break;

            case 10:
                $answer = "Signs point to yes";
                break;

            case 11:
                $answer = "Reply hazy try again";
                break;

            case 12:
                $answer = "Ask again later";
                break;

            case 13:
                $answer = "Better not tell you now";
                break;

            case 14:
                $answer = "Cannot predict now";
                break;

            case 15:
                $answer = "Concentrate and ask again";
                break;

            case 16:
                $answer = "Don't count on it";
                break;

            case 17:
                $answer = "My reply is no";
                break;

            case 18:
                $answer = "My sources say no";
                break;

            case 19:
                $answer = "Outlook not so good";
                break;

            case 20:
                $answer = "Very doubtful";
                break;

            default:
                $answer = "Broken";
                break;
            }





        $this->sms_message = "8 BALL";

        $this->sms_message .= " | " . $answer;
        $this->sms_message .= ' | TEXT ?';

			

        $choices = false;

		$this->thing_report[ "choices" ] = $choices;
 		$this->thing_report["info"] = "This makes a prognistication."; 
 		$this->thing_report["help"] = "This is about stochastics.";

		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;


        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;


		return $this->thing_report;


	}
/*
    function extractRoll($input) {

//echo $input;
//exit();

preg_match('/^(\\d)?d(\\d)(\\+\\d)?$/',$input,$matches);

print_r($matches);

$t = preg_filter('/^(\\d)?d(\\d)(\\+\\d)?$/',
                '$a="$1"? : 1;for(; $i++<$a; $s+=rand(1,$2) );echo$s$3;',
                $input)?:'echo"Invalid input";';


    }
*/



	public function readSubject()
    {


        //$input = strtolower($this->subject);


		return false;
    }

}



return;
