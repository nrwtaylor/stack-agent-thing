<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Nuuid {

	public $var = 'hello';


    function __construct(Thing $thing, $agent_input = null) {

        $this->agent_input = $agent_input;

		$this->agent_name = "nuuid";
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
		$this->test= "Development code";

		$this->thing = $thing;
        $this->thing_report['thing']  = $thing;

        $this->start_time = $this->thing->elapsed_runtime(); 

        $command_line = null;

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        $this->node_list = array("nuuid"=>array("nuuid"));

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

		$this->haystack = $thing->uuid . 
		$thing->to . 
		$thing->subject . 
		$command_line .
        $this->agent_input;

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.', "INFORMATION");
        $this->thing->log($this->agent_prefix . 'received this Thing "'.  $this->subject . '".', "DEBUG");


        $this->current_time = $this->thing->json->time();


      //  $this->max = 12;
      //  $this->size = 3.7;
      //  $this->lattice_size = 15;

      //  $this->init();

        $this->thing->log( $this->agent_prefix .'completed init. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


//        $this->flag = new Variables($this->thing, "variables roll " . $this->from);


		//echo "construct email responser";

		// If readSubject is true then it has been responded to.
		// Forget thing.

        // Borrow this from iching


        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("nuuid", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("nuuid", "refreshed_at"), $time_string );
        }


        //$this->getSnowflake();

        $this->readSubject();

//        $this->max = 12;
//        $this->size = 3.7;
//        $this->lattice_size = 15;

        $this->init();

//        if ((!isset($this->decimal_snowflake)) or 
//            ($this->decimal_snowflake == null)) {$this->getSnowflake();}

        //$this->initSnowflake();
//        $this->drawSnowflake(0,0,0,$this->max);


        //$this->thing->log( $this->agent_prefix .'completed getSnowflake. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


//        if ($this->decimal_snowflake == false) {
//            $this->updateSnowflake();        
//            $this->thing->log( $this->agent_prefix .'completed updateSnowflake. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );
//        }

      //  $this->readSubject();
        //$this->setSnowflake();


        if ($this->agent_input == null) {$this->setSignals();}

        $this->thing->log( $this->agent_prefix .'completed setSignals. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


//        $this->setSnowflake();

        $this->makePNG();

        $this->thing->log( $this->agent_prefix .'completed setSnowflake. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );


        $this->thing_report['log'] = $this->thing->log;

		return;
	}

// https://www.math.ucdavis.edu/~gravner/RFG/hsud.pdf

// -----------------------

    function init()
    {

    }

	private function setSignals() {

		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;

		//echo "to:". $to;

		$from = "nuuid";

// This choice element is super slow.  It 
// is the difference between 6s and 351ms.
// Hard to justify a button question in response to a die roll.

        $this->makePNG();

//        $this->thing->json->setField("variables");
//          $this->thing->json->writeVariable( array("snowflake", "decimal"), $this->decimal_snowflake );

        $this->thing->log( $this->agent_prefix .'completed makePNG. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


        $this->makeSMS();
        $this->makeMessage();
        $this->makeTXT();
        $this->thing->log( $this->agent_prefix .'completed makeTXT. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->makeChoices();
        $this->thing->log( $this->agent_prefix .'completed makeChoices. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


        $this->makeWeb();
        $this->thing->log( $this->agent_prefix .'completed makeWeb. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


 		$this->thing_report["info"] = "This creates a not UUID.  Rememberable. Machine guessable. Short.";
 		$this->thing_report["help"] = "This is about keeping track of things.";

        $this->thing->log( $this->agent_prefix .'started message. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->thing->log( $this->agent_prefix .'completed message. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


		return $this->thing_report;


	}

    function makeChoices ()
    {
       $this->thing->log( $this->agent_prefix .'started makeChoices. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->thing->choice->Create($this->agent_name, $this->node_list, "nuuid");
        $this->thing->log( $this->agent_prefix .'completed create choice. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->choices = $this->thing->choice->makeLinks('nuuid');
        $this->thing->log( $this->agent_prefix .'completed makeLinks. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


        $this->thing_report['choices'] = $this->choices;

     //  $this->thing_report['choices'] = false;


    }



    function makeSMS()
    {
        $sms = "NUUID | " . $this->thing->nuuid;
        $sms .= " | " . $this->web_prefix . "snowflake/".$this->uuid;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeMessage()
    {

        $message = "Stackr has a nuuid for you.<br>";

        $uuid = $this->uuid;

        $message .= "Keep on stacking.\n\n<p>" . $this->web_prefix . "thing/$uuid/nuuid.png\n \n\n<br> ";
        $message .= '<img src="' . $this->web_prefix . 'thing/'. $uuid.'/nuuid.png" alt="nuuid" height="92" width="92">';


        $this->thing_report['message'] = $message;


        return;

    }



    function makeWeb()
    {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $web = '<a href="' . $link . '">';
        $web .= '<img src= "'. $this->web_prefix . 'thing/' . $this->uuid . '/nuuid.png">';
        $web .= "</a>";

        $web .= "<br><br>";

        $this->thing_report['web'] = $web;

    }



    function makeTXT()
    {
        $txt = 'This is a NUUID';
        $txt .= "\n";
        $txt .= $this->thing->nuuid;

        $txt .= "\n";


        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;


    }







    public function makePNG()
    {

        $this->image = imagecreatetruecolor(164, 164);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        imagefilledrectangle($this->image, 0, 0, 164, 164, $this->white);

        $textcolor = imagecolorallocate($this->image, 0, 0, 0);
        #$this->drawSnowflake(164/2,164/2);

        // Write the string at the top left
        $border = 30;
        $radius = 1.165 * (164 - 2 * $border) / 3;

//$number = 6;


//if ($number>99) {return;}

//if ($this->roll == "d8") {

//$this->drawSnowflake();




//$number = ($this->result[0]);
//var_dump($number);
//exit();
$font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';
$text = $this->thing->nuuid;
// Add some shadow to the text
//imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

$size = 26;
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

imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $this->grey, $font, $text);

//var_dump ($width);
       //imagestring($image, 2, 100, 0, $this->roll, $textcolor);
     //imagestring($this->image, 20, $bbox["left"], $bbox["top"], $this->thing->nuuid, $textcolor);




        // Save the image
        //header('Content-Type: image/png');
        //imagepng($im);

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();
        ob_end_clean();

        $this->thing_report['png'] = $imagedata;

        //echo '<img src="data:image/png;base64,'.base64_encode($imagedata).'"/>';
        $response = '<img src="data:image/png;base64,'.base64_encode($imagedata).'"alt="snowflake"/>';

$this->PNG_embed = "data:image/png;base64,".base64_encode($imagedata);

//        $this->thing_report['png'] = $image;

//        $this->PNG = $this->image;    
        $this->PNG = $imagedata;
        imagedestroy($this->image);

        return $response;



        $this->PNG = $image;    
        $this->thing_report['png'] = $image;
 
       return;
    }




    function read()
    {
        return $this->state;
    }




	public function readSubject()
    {
        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
//var_dump($input);
//exit();

        if (count($pieces) == 1) {

            if ($input == 'nuuid') {

                return;
            }
        }

        $keywords = array("nuuid");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) {

                        case 'nuuid':
                            return;

                        case 'on':

                        default:
                     }
                }
            }
        }

        return;

        if (strpos($input, 'nuuid') !== false) {
            //    $this->uuidSnowflake();
    
        }

        if ($this->agent_input == "nuuid test") {
            $this->thing->log($this->agent_prefix . 'received a command to do something with the nuuid.', "INFORMATION");
		    return;
        }

    }

    

}
