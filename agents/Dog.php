<?php
namespace Nrwtaylor\StackAgentThing;

class Dog {

	public $var = 'hello';


    function __construct(Thing $thing, $text = null) {


		$this->agent_name = 'hashmessage';
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
		$this->test= "Development code";

        $this->agent_input = $text;

//      This is how old roll.php is.
//		$thingy = $thing->thing;
		$this->thing = $thing;

        $this->thing_report  = array("thing"=>$this->thing->thing);
        $this->start_time = $this->thing->elapsed_runtime();



        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;


        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log($this->agent_prefix . "received this Thing ".  $this->subject . '".');


		$this->readSubject();

//        $this->getNegativetime();
        $this->negative_time = null;

        $this->getFlag();

		$this->thing_report = $this->respond();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format( $this->thing->elapsed_runtime() - $this->start_time ) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;

		return;

	}


// -----------------------

    function getNegativetime()
    {

        $agent = new Negativetime($this->thing, "dog");
        $this->negative_time = $agent->negative_time; //negative time is asking

        //$this->time_remaining = -1 * $this->negative_time;

    }

    function getFlag()
    {

        $agent = new Flag($this->thing, "flag");
        $this->flag = $agent->state; //negative time is asking

//var_dump($this->state);
    }



	private function respond() {


		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "dog";





        //$response = $input . "Try " . strtoupper($v) . ".";

        if ($this->agent_input == null) {
//var_dump($this->negative_time);
        switch (true) {
            case (($this->negative_time <= 0) and ($this->flag == "red")):
                $array = array('Bark', 'Woof');
                $k = array_rand($array);
                $v = $array[$k];
                $response = "DOG | " . $v . ". Check on the cat.";

                // Bark like crazy.  We're late.
                break;
            case ($this->negative_time > 150):
                $response = "DOG | Zzzzz.";
                break;
            case ($this->negative_time > 120):
                $array = array('ready?');
                $k = array_rand($array);
                $v = $array[$k];
                $response = "DOG | " . strtolower($v) . ". " . $this->thing->human_time($this->negative_time ) .".";

                break;

            case ($this->negative_time > 0):
                // https://www.psychologytoday.com/us/blog/canine-corner/201211/how-dogs-bark-in-different-languages
                $array = array('bark','woof','grrr','ruff-ruff','woof-woof','bow-wow','yap-yap','yip-yip');
                $k = array_rand($array);
                $v = $array[$k];

                $response = "DOG | " . strtolower($v) . ". " . $this->thing->human_time($this->negative_time ) .".";
                break;


            default:
                $response =  "DOG | Zzzzzzzz.";
            }


            $this->dog_message = $response;
        } else {
            $this->dog_message = $this->agent_input;
        }

        $this->makeSMS();
        $this->makeChoices();
        //$this->sms_message = "TIMEOUT";

//        if ($this->agent_input != null) {
        //    $this->sms_message = "" . $this->dog_message;
//        }

//        $this->sms_message .= " | " . number_format( $this->thing->elapsed_runtime() ) . "ms.";

			

        //$choices = false;

		//$this->thing_report[ "choices" ] = $choices;
 		$this->thing_report["info"] = "This is a dog."; 
 		$this->thing_report["help"] = "This is about barking.";

		$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;


        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;


		return $this->thing_report;


	}

    function makeSMS()
    {
        $this->node_list = array("dog"=>array("dog","cat"));
        $this->sms_message = "" . $this->dog_message;
        $this->thing_report['sms'] = $this->sms_message;

    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "dog");
        $choices = $this->thing->choice->makeLinks('dog');
        $this->thing_report['choices'] = $choices;
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
?>
