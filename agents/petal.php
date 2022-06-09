<?php

//echo "Watson says hi<br>";
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '/var/www/html/stackr.ca/agents/message.php';

class Petal {

	public $var = 'hello';


    function __construct(Thing $thing, $agent_input = null) {

        $this->agent_input = $agent_input;
        $this->start_time = microtime(true);

		$this->agent_name = "petal";
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
		$this->test= "Development code";

//      This is how old roll.php is.
//		$thingy = $thing->thing;
		$this->thing = $thing;

         $this->thing_report  = array("thing"=>$this->thing->thing);

        $command_line = null;

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        $this->node_list = array("roll"=>array("roll d20"));


		$this->haystack = $thing->uuid . 
				$thing->to . 
				$thing->subject . 
				$command_line .
		                $this->agent_input;

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.', "INFORMATION");
        $this->thing->log($this->agent_prefix . 'received this Thing "'.  $this->subject . '".', "DEBUG");


        $this->current_time = $this->thing->json->time();


//        $this->flag = new Variables($this->thing, "variables roll " . $this->from);


		//echo "construct email responser";

		// If readSubject is true then it has been responded to.
		// Forget thing.

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("petal", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("petal", "refreshed_at"), $time_string );
        }

        $this->thing->json->setField("variables");
        $this->roll = $this->thing->json->readVariable( array("petal", "roll") );
        $this->result = $this->thing->json->readVariable( array("petal", "result") );

//        if ( ($this->roll == false) or ($this->result == false) ) {


            $this->readSubject();


            $this->thing->json->writeVariable( array("petal", "roll"), $this->roll );
            $this->thing->json->writeVariable( array("petal", "result"), $this->result );

            $this->thing->log($this->agent_prefix . ' completed read. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE") ;
//        }



        if ($this->agent_input == null) {$this->setSignals();}

        $this->thing->log($this->agent_prefix . ' set response. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE") ;

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['log'] = $this->thing->log;

		return;

	}



// -----------------------

	private function setSignals() {

		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;

		//echo "to:". $to;

		$from = "roll";
		$roll = -1;


        $this->sms_message = "ROLL | ";


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

        $this->makePNG();
        $this->makeChoices();
// Testing 20 July.
//		$email_thing = new Thing($this->uuid);
//		require_once '/var/www/html/stackr.ca/agents/makeemail.php';
//		$test = new makeEmail($email_thing);



//		$this->thing->email->sendGeneric($to,"roll",
//			$this->subject . ' = ' . $roll, 
//			$message,
//			$choices);

//		echo '<pre> Agent "Roll" sent an email</pre>';

//		$this->thing_report  = array("thing"=>$this->thing->thing,
		//$this->thing_report[ "roll" ] = $roll;
		//$this->thing_report[ "choices" ] = $choices;
 		$this->thing_report["info"] = "This rolls a dice.  See 
				https:\\codegolf.stackexchange.com/questions/25416/roll-dungeons-and-dragons-dice";
 		$this->thing_report["help"] = "This is about dice with more than 6 sides.";

		//$this->thing_report['sms'] = $this->sms_message;
//		$this->thing_report['message'] = $this->sms_message;


        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;


		return $this->thing_report;


	}

    function makeChoices () {

//        $this->thing->choice->Choose($this->state);
//        $this->thing->choice->save($this->keyword, $this->state);

        $this->thing->choice->Create($this->agent_name, $this->node_list, "roll");

        $choices = $this->thing->choice->makeLinks('roll');
        $this->thing_report['choices'] = $choices;

    }



    function makeSMS()
    {

        $temp_sms_message = "";

        if (!isset($this->result) or ($this->result == 'Invalid input' ) or ($this->result == null)) {

          $sms = "ROLL | Request not processed. Check syntax.";

        } else {

            $sms = "ROLL | ";
//var_dump($this->result);
            foreach($this->result as $k=>$v) {
                foreach ($v as $key=>$value) {

            if ($key == 'roll') {
             //   $message .= '<br>Total roll is ' . $value . '<br>';
                //$temp_sms_message .= 'Total roll = ' . $value;
                $roll = $value;
            } else {
             //   $message .= $key . ' giving ' . $value . '<br>';
                $temp_sms_message .= $key . '=' . $value . ' ';
            }


                }

            }

            $sms = "ROLL = " . $roll . " | ";
            $sms .= $temp_sms_message;
            $sms .= '| TEXT ?';


        }



        $this->thing_report['sms'] = $sms;

    }

    function makeMessage()
    {

        $message = "Stackr rolled the following for you.<br>";

        foreach($this->result as $k=>$v) {
            foreach ($v as $key=>$value) {
                if ($key == 'roll') {
                    $message .= '<br>Total roll is ' . $value . '<br>';
                    $roll = $value;
                } else {
                    $message .= $key . ' giving ' . $value . '<br>';
                }
            }
        }

        $this->thing_report['message'] = $message;


        return;

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


    public function makePNG()
    {

        // here DB request or some processing
//        $codeText = "thing:".$this->state;
//echo count($this->result);
//exit();

//$this->drawHexagon();
//exit();

//var_dump($this->result);
if (count($this->result) != 2) {return;}
//var_dump($this->result);
//exit();
$number = $this->result[1]['roll'];

$this->image = imagecreatetruecolor(125, 125);

$this->white = imagecolorallocate($this->image, 255, 255, 255);
$this->black = imagecolorallocate($this->image, 0, 0, 0);
$this->red = imagecolorallocate($this->image, 255, 0, 0);
$this->green = imagecolorallocate($this->image, 0, 255, 0);
$this->grey = imagecolorallocate($this->image, 128, 128, 128);

imagefilledrectangle($this->image, 0, 0, 125, 125, $this->white);

$textcolor = imagecolorallocate($this->image, 0, 0, 0);


$this->drawSnowflake(56,64);
//echo "meep";
//exit();


$number = ($this->result[0]['d6']);
// Create a 55x30 image
//$image = imagecreatetruecolor(125, 125);

// Draw a white rectangle


//imagefilledrectangle($image, 0, 0, 200, 125, ${$this->state});

//imagefilledrectangle($image, 0, 0, 125, 125, $white);

//$textcolor = imagecolorallocate($image, 0, 0, 0);

//}
// Write the string at the top left
$border = 30;
$radius = 1.165 * (125 - 2 * $border) / 3;

//$number = 6;


if ($number>99) {return;}

//if ($this->roll == "d8") {

//$this->drawSnowflake();




//$number = ($this->result[0]);
//var_dump($number);
//exit();
$font = '/var/www/html/stackr.ca/resources/roll/KeepCalm-Medium.ttf';
$text = $number;
// Add some shadow to the text
//imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

$size = 72;
$angle = 0;
$bbox = imagettfbbox ($size, $angle, $font, $text); 
$bbox["left"] = 0- min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
$bbox["top"] = 0- min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
$bbox["width"] = max($bbox[0],$bbox[2],$bbox[4],$bbox[6]) - min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
$bbox["height"] = max($bbox[1],$bbox[3],$bbox[5],$bbox[7]) - min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
extract ($bbox, EXTR_PREFIX_ALL, 'bb'); 
//check width of the image 
$width = imagesx($this->image); 
$height = imagesy($this->image);
$pad = 0;
//imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $grey, $font, $number);

//     imagestring($this->image, 2, 100, 0, $this->roll, $textcolor);




        // Save the image
        //header('Content-Type: image/png');
        //imagepng($im);

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();
        ob_end_clean();

        $this->thing_report['png'] = $imagedata;

        //echo '<img src="data:image/png;base64,'.base64_encode($imagedata).'"/>';
        $response = '<img src="data:image/png;base64,'.base64_encode($imagedata).'"alt="hexagram"/>';



//        $this->thing_report['png'] = $image;

        imagedestroy($this->image);

        return $response;



        $this->PNG = $image;    
        $this->thing_report['png'] = $image;
 
       return;
    }





    function drawTriangle() {

        $pta = array(0,0);
        $ptb = array(sqrt(20),1);
        $ptc = array(20,0);

        imageline($image, 20, 20, 280, 280, $black);
        imageline($image, 20, 20, 20, 280, $black);
        imageline($image, 20, 280, 280, 280, $black);



    }

function hex_corner($center_x, $center_y, $size, $i){
$PI=3.14159;
    $angle_deg = 60 * $i   + 30;
    $angle_rad = $PI / 180 * $angle_deg;
    return array($center_x + $size * cos($angle_rad),
                 $center_y + $size * sin($angle_rad));
}
/*
    function drawHexagon()
    {
        $arr = array(0, 1, 2, 3, 4, 5);
        foreach ($arr as &$value) {
            $j = 0;

            foreach ($this->points as $point) {
                $j += 1;
                $x_pt = $point[0];
                $y_pt = $point[1];


                list($x,$y) = $this->hex_corner($x_pt,$y_pt,30,$value);

                if (isset($x_new)) {
                    imageline($this->image, $x, $y, $x_new, $y_new, $this->black);
                }
                $x_new = $x;
                $y_new = $y;
            }

        }
    }
*/

    function drawSnowflake($n, $p)
    {

            $this->step_length = 20;
            $this->points = array(array(0,0), array(0,$this->step_length), array(0,2*$this->step_length), array(0,3*$this->step_length));

        $arr = array(0, 1, 2, 3, 4, 5);
        foreach ($arr as &$value) {

            list($x,$y) = $this->hex_corner(60,60,30,$value);

  //              $this->iterateSnowflake();

            if (isset($x_new)) {
                imageline($this->image, 60,60, $x, $y, $this->black);
            }
            $x_new = $x;
            $y_new = $y;
        }
//echo "meep";
//exit();
//        $this->drawHexagon();

    }


    function iterateSnowflake()
    {
        $step_length = 20;
echo "<pre>";
            $drive_vector = array(0,1);
$i = 0;
            foreach($this->points as &$point) {

          //  var_dump(rand(0,360)/360);
    
                $disturbance_vector = array(rand(0,100)/100, rand(0,100)/100);
                $growth_vector = array($drive_vector[0] + $disturbance_vector[0],$drive_vector[1] + $disturbance_vector[1]);

                $i += 1;
                $this->points[$i][0] = $point[0] + $growth_vector[0] * $this->step_length;
                $this->points[$i][1] = $point[1] + $growth_vector[1] * $this->step_length;

//                foreach($growth_vector as $key=>$scalar) {
//                    var_dump($scalar);
//                }
            }


    }


    function read()
    {
        //$this->thing->log("read");

        $this->get();
        return $this->state;
    }




    function getRoll($input)
    {
        if (!isset($this->rolls)) {
            $this->rolls = $this->extractRolls($input);
        }
//var_dump($this->rolls);

        if (count($this->rolls) == 1) {
            $this->roll = $this->rolls[0];
            return $this->roll;  
      }

        if (count($this->rolls) == 0) {
            $this->roll = "d6";
            return $this->roll;  
      }


        $this->roll = false;
        
        //array_pop($arr);
//exit();
        return false;
    }


    function extractRolls($input)
    {
        if (!isset($this->rolls)) {
            $this->rolls = array();
        }

//Why not combine them into one character class? /^[0-9+#-]*$/ (for matching) and /([0-9+#-]+)/ for capturing ? 
        $pattern = "|^(\\d)?d(\\d)(\\+\\d)?$|";
        //$pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";
        $pattern = '/([0-9d+]+)/';
        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);
        $this->rolls = $arr;

//$var_dump($this->rolls);
//exit();

        return $this->rolls;


    }


    function dieRoll($die_N = 6, $modifier = 0) {

        $d = rand(1, $die_N);
        $roll = $d + $modifier;

        return $roll;
    }



	public function readSubject()
    {

//        $input = '2d20+5+d100';

        $input = strtolower($this->subject);

// Translate from emoji
require_once '/var/www/html/stackr.ca/agents/emoji.php';
$temp_thing = new Emoji($this->thing, "emoji");
$input = $temp_thing->translated_input; 

$n = substr_count($input, "roll");

//$input=preg_replace('/\b(\S+)(?:\s+\1\b)+/i', '$1', $input);
$input=preg_replace('/\b(\S+)(?:\s+\1\b)+/i', "roll " . $n ."d6" , $input);



//echo $input;
//exit();

//        $this->get();

        //var_dump($this->roll);
//        if ($this->roll != false) {return;}


        

//exit();

        $this->getRoll($input);


//        $words = explode(" ", $input);

//        if ((count($words) ==1) and ($words[0] == $this->agent_name)) {
//            $input = "d6";
//        }

//        if ($words[0] == $this->agent_name) {
//	        array_shift($words);
//            if (count($words) == 0) {
                $input = "d6";
//            } else {
//	            $input = implode(" ", $words);
//                $input = $this->roll;
//            }
//        }

        if ($this->roll == false) {

            $this->roll = "d6";
        }

        $result = array();
//var_dump($this->roll);
//exit();
//preg_match('/^(\\d)?d(\\d)(\\+\\d)?$/',$input,$matches);

//print_r($matches);

//$t = preg_filter('/^(\\d)?d(\\d)(\\+\\d)?$/',
//                '$a="$1"? : 1;for(; $i++<$a; $s+=rand(1,$2) );echo$s$3;',
//                $input)?:'echo"Invalid input";';
//echo $input;
//exit();

        $roll = 0;
//$modifier = 0;
//$N_rolls = 0;
//$die_N =0;
// https://codegolf.stackexchange.com/questions/118703/dd-5e-hp-calculator

		$dies = explode("+",$this->roll);

//var_dump($dies);

		if ( count( $dies ) == 0 ) {

			//$dies[0] = "d6";
            //return;
			return "Invalid input";
		}

		foreach ($dies as $die) {
			//echo $die;

			$elements = explode("d", $die, 2);

			if ( (count($elements) == 1 ) and
				is_numeric($elements[0]) ) {

				$modifier = $elements[0];
				$roll = $roll + $modifier;
				$result[] = array('modifier'=>$modifier);

			} else {

				if (is_numeric($elements[0]) and
					  is_numeric($elements[1]) ) {

					$N_rolls = $elements[0];
					$die_N = $elements[1];


				} elseif ( ($die[0] == 'd') and 
					is_numeric($elements[1]) ) {

                    $N_rolls = 1;
                    $die_N = $elements[1];

				} else {

                    // Roll a d6 if unclear
                    //$N_rolls = 1;
                    //$die_N = 6;
                    //return;

//					return "Invalid input";	
                }



				for ($i = 1; $i <= $N_rolls; $i++) {
					$d = rand(1, $die_N);
					 $result[] = array('d'.$die_N=>$d);

					$roll = $roll + $d;

				}

			}

		}

		$result[] = array('roll'=>$roll);

        $this->result = $result;
        $this->sum = $result;

		return $result;
        }

    }



return;
