<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Nuuid extends Agent
{

	public $var = 'hello';
/*
    public function __construct(Thing $thing, $agent_input = null)
    {
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

        $this->current_time = $this->thing->time();

        $this->thing->log( $this->agent_prefix .'completed init. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("nuuid", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("nuuid", "refreshed_at"), $time_string );
        }

        $this->readSubject();

        $this->init();

        if ($this->agent_input == null) {$this->setSignals();}

        $this->thing->log( $this->agent_prefix .'completed setSignals. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->makePNG();

        $this->thing->log( $this->agent_prefix .'completed setSnowflake. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );


        $this->thing_report['log'] = $this->thing->log;

		return;
	}
*/
// https://www.math.ucdavis.edu/~gravner/RFG/hsud.pdf

// -----------------------

    function init()
    {
       $command_line = null;

        $this->node_list = array("nuuid"=>array("nuuid"));

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $this->thing->container['stack']['web_prefix'];
        $this->mail_postfix = $this->thing->container['stack']['mail_postfix'];
        $this->word = $this->thing->container['stack']['word'];
        $this->email = $this->thing->container['stack']['email'];

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->haystack = $this->thing->uuid . 
        $this->thing->to . 
        $this->thing->subject . 
        $command_line .
        $this->agent_input;

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.', "INFORMATION");
        $this->thing->log($this->agent_prefix . 'received this Thing "'.  $this->subject . '".', "DEBUG");

        $this->current_time = $this->thing->time();

        $this->thing->log( $this->agent_prefix .'completed init. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("nuuid", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("nuuid", "refreshed_at"), $time_string );
        }

        $this->makePNG();
    }

    public function respond() {

		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;
		$from = "nuuid";

// This choice element is super slow.  It 
// is the difference between 6s and 351ms.
// Hard to justify a button question in response to a die roll.

        $this->makePNG();

        $this->makeSMS();
        $this->makeMessage();
        $this->makeTXT();

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

    function extractNuuids($input = null)
    {
        if (!isset($this->head_codes)) {
            $this->nuuids = array();
        }
        //Why not combine them into one character class? /^[0-9+#-]*$/ (for matching) and /([0$
//        $pattern = "|[A-Za-z0-9]{4}|"; echo $input;
        $pattern = "|[A-Fa-f0-9]{4}|";

        //$pattern = "|\b\d{1}[A-Za-z]{1}\d{2}\b|";
        preg_match_all($pattern, $input, $m);
        $this->nuuids = $m[0];

        return $this->nuuids;
    }

    function extractNuuid($input)
    {
        $nuuids = $this->extractNuuids($input);
        if (!(is_array($nuuids))) {return true;}

        if ((is_array($nuuids)) and (count($nuuids) == 1)) {
            $this->nuuid = $nuuids[0];
            $this->thing->log('Agent "Nuuid" found a nuuid (' . $this->nuuid . ') in the text.');
            return $this->nuuid;
        }

        if  ((is_array($nuuids)) and (count($nuuids) == 0)){return false;}
        if  ((is_array($nuuids)) and (count($nuuids) > 1)) {return true;}

        return true;
    }

    function makeChoices ()
    {
       $this->thing->log( $this->agent_prefix .'started makeChoices. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->thing->choice->Create($this->agent_name, $this->node_list, "nuuid");
        $this->thing->log( $this->agent_prefix .'completed create choice. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->choices = $this->thing->choice->makeLinks('nuuid');
        $this->thing->log( $this->agent_prefix .'completed makeLinks. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->thing_report['choices'] = $this->choices;
    }

    function makeSMS()
    {
        $sms = "NUUID | " . $this->thing->nuuid;
        $sms .= " | " . $this->web_prefix . "snowflake/".$this->uuid;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function makeMessage()
    {

        $message = "Stackr has a nuuid for you.<br>";

        $uuid = $this->uuid;

        $message .= "Keep on stacking.\n\n<p>" . $this->web_prefix . "thing/$uuid/nuuid.png\n \n\n<br> ";
        $message .= '<img src="' . $this->web_prefix . 'thing/'. $uuid.'/nuuid.png" alt="nuuid" height="92" width="92">';

        $this->thing_report['message'] = $message;

        return;
    }

    public function makeWeb()
    {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $web = '<a href="' . $link . '">';
        $web .= '<img src= "'. $this->web_prefix . 'thing/' . $this->uuid . '/nuuid.png">';
        $web .= "</a>";

        $web .= "<br><br>";

        $this->thing_report['web'] = $web;

    }

    public function makeTXT()
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

        // imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $grey, $font, $number);
        imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $this->grey, $font, $text);

        // imagestring($image, 2, 100, 0, $this->roll, $textcolor);
        // imagestring($this->image, 20, $bbox["left"], $bbox["top"], $this->thing->nuuid, $textcolor);

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

        $this->PNG = $imagedata;
        imagedestroy($this->image);

        return $response;
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

//        return;

        if (strpos($input, 'nuuid') !== false) {

        }

        if ($this->agent_input == "nuuid test") {
            $this->thing->log($this->agent_prefix . 'received a command to test nuuid.', "INFORMATION");
		    return;
        }
    }
}
