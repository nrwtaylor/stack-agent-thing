<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Roll {

	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = $thing->elapsed_runtime(); 

        $this->agent_input = $agent_input;

		$this->agent_name = "roll";
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
		$this->test= "Development code";

		$this->thing = $thing;

        $this->thing_report['thing']  = $thing;

        $this->start_time = $this->thing->elapsed_runtime();


        $command_line = null;

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = strtolower($thing->subject);

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->node_list = array("roll"=>array("roll", "roll d20"));

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

		$this->haystack = $thing->uuid . 
				$thing->to . 
				$thing->subject . 
				$command_line .
		                $this->agent_input;

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.', "INFORMATION");
        $this->thing->log($this->agent_prefix . 'received this Thing "'.  $this->subject . '".', "DEBUG");


        $this->current_time = $this->thing->json->time();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("roll", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("roll", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);


        $this->thing->json->setField("variables");
        $this->roll = strtolower($this->thing->json->readVariable( array("roll", "roll") ));
        $this->result = $this->thing->json->readVariable( array("roll", "result") );

        if ( ($this->roll == false) or ($this->result == false) ) {


            $this->readSubject();

            $this->thing->json->writeVariable( array("roll", "roll"), $this->roll );
            $this->thing->json->writeVariable( array("roll", "result"), $this->result );

            $this->thing->log($this->agent_prefix . ' completed read.', "OPTIMIZE") ;
        }

        $this->respond();

        $this->thing->log($this->agent_prefix . ' set response.', "OPTIMIZE") ;

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['log'] = $this->thing->log;

		return;

	}



// -----------------------

	private function respond() {


		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;

		//echo "to:". $to;

		$from = "roll";
		$roll = -1;


//        $this->sms_message = "ROLL | ";


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
        $this->makeWeb();

        $this->makeEmail();

 		$this->thing_report["info"] = "This rolls a dice.  See 
				https:\\codegolf.stackexchange.com/questions/25416/roll-dungeons-and-dragons-dice";
        if (!isset($this->thing_report['help'])) {
 		    $this->thing_report["help"] = 'This is about dice with more than 6 sides.  Try "ROLL d20"';
        }

		//$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;


        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

		return $this->thing_report;


	}

    function makeChoices () {

//        $this->thing->choice->Choose($this->state);
//        $this->thing->choice->save($this->keyword, $this->state);

        $this->thing->choice->Create($this->agent_name, $this->node_list, "roll");

        $choices = $this->thing->choice->makeLinks('roll');
        $this->thing_report['choices'] = $choices;

    }

    function makeEmail() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/roll';

        $this->node_list = array("roll"=>array("roll d20", "roll"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "roll");
        $choices = $this->thing->choice->makeLinks('roll');

        $web = '<a href="' . $link . '">';
//        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/roll.png" jpg" 
//                width="100" height="100" 
//                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/roll.tx$

        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';

        if (!isset($this->html_image)) {$this->makePNG();}

        $web .= $this->html_image;

        $web .= "</a>";
        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( time() - $this->refreshed_at );
        $web .= "Rolled about ". $ago . " ago.";

        $web .= "<br>";


        $this->thing_report['email'] = $web;
    }



    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = array("roll"=>array("roll d20", "roll"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "web");
        $choices = $this->thing->choice->makeLinks('web');

        if (!isset($this->html_image)) {$this->makePNG();}

        $web = '<a href="' . $link . '">'. $this->html_image . "</a>";
        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( time() - $this->refreshed_at );
        $web .= "Rolled about ". $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    function makeSMS()
    {

        $temp_sms_message = "";

        if (!isset($this->result) or ($this->result == 'Invalid input' ) or ($this->result == null)) {

          $sms = "ROLL | Request not processed. Check syntax.";
        } elseif ($this->roll == "d6") {

            $sms = "ROLL | " . $this->result[1]['roll'];

        } else {

            $sms = "ROLL | ";
            foreach($this->result as $k=>$v) {
                foreach ($v as $key=>$value) {

                    if ($key == 'roll') {
                        $roll = $value;
                    } else {
                        $temp_sms_message .= $key . '=' . $value . ' ';
                    }
                }
            }

            $sms = "ROLL = " . $roll . " | ";
            $sms .= $temp_sms_message;
            $sms .= '| TEXT ?';


        }


        $this->sms_message = $sms;
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


    public function makeImage()
    {
        //if (isset($this->image)) {return;}
        // here DB request or some processing


        $number = $this->result[1]['roll'];

        $image = imagecreatetruecolor(125, 125);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $grey = imagecolorallocate($image, 128, 128, 128);

        imagefilledrectangle($image, 0, 0, 125, 125, $white);

        $textcolor = imagecolorallocate($image, 0, 0, 0);

        if (count($this->result) != 2) {$this->image = $image;return;}


        if ($this->roll == "d6") {

            $this->ImageRectangleWithRoundedCorners($image, 0,0, 125, 125, 12, $black);
            $this->ImageRectangleWithRoundedCorners($image, 6,6, 125-6, 125-6, 12-6, $white);

            $number = ($this->result[0]['d6']);

            // Build pip array
            $pips = array();
            $pips[1] = array(array(1,1));
            $pips[2] = array(array(0,0), array(2,2));
            $pips[3] = array(array(0,0), array(1,1), array(2,2));
            $pips[4] = array(array(0,0), array(0,2), array(2,0), array(2,2));
            $pips[5] = array(array(0,0), array(0,2), array(1,1), array(2,0), array(2,2));
            $pips[6] = array(array(0,0), array(0,1), array(0,2), array(2,0), array(2,1), array(2,2));

            // Write the string at the top left
            $border = 30;
            $radius = 1.165 * (125 - 2 * $border) / 3;

            foreach($pips[$number] as $key=>$value) {
                list($x,$y) = $value;

                $die_x = (125 - 2 * $border)/2*$x + $border;
                $die_y = (125- 2 *$border)/2*$y + $border;

                imagefilledellipse($image, $die_x, $die_y, $radius, $radius, $black);
            }

        } else {

            if ($number>99) {$this->image = $image;return;}

            if (false) {

                // draws triangle lines based on the rules of math
                $size = 100;
                list($pta_x,$pta_y) = array(0,0);
                list($ptb_x,$ptb_y) = array($size/2,$size*sqrt(3)/2);
                list($ptc_x,$ptc_y) = array($size,0);

                imageline($image, $pta_x, $pta_y, $ptb_x, $ptb_y, $black);
                imageline($image, $ptb_x, $ptb_y, $ptc_x, $ptc_y, $black);
                imageline($image, $ptc_x, $ptc_y, $pta_x, $pta_y, $black);

            }

            //$font = $GLOBALS['stack'] . 'vendor/nrwtaylor/stack-agent-thing/resources/roll/KeepCalm-Medium.ttf';
            $font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';

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
            $width = imagesx($image); 
            $height = imagesy($image);
            $pad = 0;
            imagettftext($image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $grey, $font, $number);

            //var_dump ($width);
            imagestring($image, 2, 100, 0, $this->roll, $textcolor);
        }

        $this->image = $image;
    }

    public function makePNG()
    {
        //if (!isset($this->image)) {$this->makeImage();}

        $agent = new Png($this->thing, "png");
        $this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;

        //$this->thing_report['png'] = $agent->PNG;
        $this->thing_report['png'] = $agent->image_string;

    }

/*
    public function makePNG()
    {
        if (!isset($this->image)) {$this->makeImage();}

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();
        ob_clean();
        ob_end_clean();

//var_dump($imagedata);
//$imagedata = null;
        $this->PNG_embed = "data:image/png;base64,".base64_encode($imagedata);
        $this->PNG = $imagedata;

        $this->thing_report['png'] = $imagedata;

        if (isset($this->result[1]['roll'])) {
            $alt_text = "Rolled " . $this->roll . " and got " . $this->result[1]['roll'] . ".";
        } else {
            $alt_text = "Roll result not available";
        }


        //echo '<img src="data:image/png;base64,'.base64_encode($imagedata).'"/>';
//        $response = '<img src="data:image/png;base64,'.base64_encode($imagedata).'"alt="hexagram"/>';

        $response = '<img src="data:image/png;base64,'.base64_encode($imagedata). '"
                width="100" height="100" 
                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/roll.txt">';
        //$response = null;

        $this->html_image = $response;

//        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/roll.png" jpg" 
//                width="100" height="100" 
//                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/roll.txt">';


        //$this->thing_report['png'] = null;
//        imagedestroy($image);


        return $this->thing_report['png'];

        return $response;

//        $this->PNG = $image;    
//        $this->thing_report['png'] = $image;

//       return;
    }
*/

    function ImageRectangleWithRoundedCorners(&$im, $x1, $y1, $x2, $y2, $radius, $color)
    {
        // draw rectangle without corners
        imagefilledrectangle($im, $x1+$radius, $y1, $x2-$radius, $y2, $color);
        imagefilledrectangle($im, $x1, $y1+$radius, $x2, $y2-$radius, $color);

        // draw circled corners
        imagefilledellipse($im, $x1+$radius, $y1+$radius, $radius*2, $radius*2, $color);
        imagefilledellipse($im, $x2-$radius, $y1+$radius, $radius*2, $radius*2, $color);
        imagefilledellipse($im, $x1+$radius, $y2-$radius, $radius*2, $radius*2, $color);
        imagefilledellipse($im, $x2-$radius, $y2-$radius, $radius*2, $radius*2, $color);
    }

    function drawTriangle()
    {
        $pta = array(0,0);
        $ptb = array(sqrt(20),1);
        $ptc = array(20,0);

        imageline($image, 20, 20, 280, 280, $black);
        imageline($image, 20, 20, 20, 280, $black);
        imageline($image, 20, 280, 280, 280, $black);
    }

    function read()
    {
        $this->get();
        return $this->state;
    }

    function getRoll($input)
    {
        if (!isset($this->rolls)) {
            $this->rolls = $this->extractRolls($input);
        }

        if (count($this->rolls) == 1) {
            $this->roll = strtolower($this->rolls[0]);
            return $this->roll;  
        }

        if (count($this->rolls) == 0) {
            $this->roll = "d6";
            return $this->roll;  
        }

        $this->roll = false;
        //array_pop($arr);
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
//        if ($this->agent_input != null) {
//            $input = strtolower($this->agent_input);
//        } else {
            $input = strtolower($this->subject);
//        }

        // Translate from emoji
        $temp_thing = new Emoji($this->thing, "emoji");
        $input = $temp_thing->translated_input; 

        $n = substr_count($input, "roll");

        //$input=preg_replace('/\b(\S+)(?:\s+\1\b)+/i', '$1', $input);
        $input=preg_replace('/\b(\S+)(?:\s+\1\b)+/i', "roll " . $n ."d6" , $input);


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

        $roll = 0;

		$dies = explode("+",$this->roll);


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
                    $N_rolls = 1;
                    $die_N = 6;
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
