<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Rocky
{
	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;

		$this->agent_name = "rocky";
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
		$this->test= "Development code";

		$this->thing = $thing;

        $this->thing_report['thing']  = $thing;

        $this->start_time = $this->thing->elapsed_runtime(); 
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $command_line = null;

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        $this->node_list = array($this->agent_name=>array("index", "uuid"));

		$this->haystack = $thing->uuid . 
    		$thing->to . 
			$thing->subject . 
			$command_line .
	        $this->agent_input;

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.', "INFORMATION");
        $this->thing->log($this->agent_prefix . 'received this Thing "'.  $this->subject . '".', "DEBUG");

        $this->current_time = $this->thing->time();

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->unit = "FUEL";

        $this->default_state = "easy";

        $this->getNuuid();

        $this->character = new Character($this->thing, "character is Rocket J. Squirrel");


        $this->thing->log( $this->agent_prefix .'completed init. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );
/*
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("rocky", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("rocky", "refreshed_at"), $time_string );
        }
*/
        $split_time = $this->thing->elapsed_runtime();

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->rocky = new Variables($this->thing, "variables rocky " . $this->from);

        $this->init();

        $this->get();

//        $this->get();

        $this->getCast();
        $this->getMessages();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("rocky", "refreshed_at") );


        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("rocky", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);


        //$this->state = strtolower($this->thing->json->readVariable( array("rocky", "nom") ));
        //$this->number = $this->thing->json->readVariable( array("rocky", "number") );
        //$this->suit = $this->thing->json->readVariable( array("rocky", "suit") );


        //if ( ($this->nom == false) or ($this->number == false) ) {

            $this->readSubject();


            $this->thing->json->writeVariable( array("rocky", "state"), $this->state );
            //$this->thing->json->writeVariable( array("rocky", ""), $this->suit );

            //$this->thing->json->writeVariable( array("rocky", "number"), $this->number );

            $this->thing->log($this->agent_prefix . ' completed read.', "OPTIMIZE") ;
        






  //      $this->readSubject();

//        $this->init();
//        $this->get();

//        $this->getCast();
//        $this->getCards();

        if ($this->agent_input == null) {$this->setSignals();}

        $this->set();




        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['log'] = $this->thing->log;

		return;
	}




// https://www.math.ucdavis.edu/~gravner/RFG/hsud.pdf

// -----------------------

    function isRocky($state = null)
    {
var_dump($state);
        // Validates whether the Flag is green or red.
        // Nothing else is allowed.

        if ($state == null) {
            if (!isset($this->state)) {$this->state = "easy";}

            $state = $this->state;
        }

        if (($state == "easy") or 
                ($state == "hard")

            ) {return false;}

        return true;
    }



    function set($requested_state = null)
    {
 /*
        if ($requested_state == null) {
            if (!isset($this->requested_state)) {
                // Set default behaviour.
                // $this->requested_state = "green";
                // $this->requested_state = "red";
                $this->requested_state = $this->default_state; // If not sure, show green.
            }
            $requested_state = $this->requested_state;
        }

        $this->state = $requested_state;
*/
        $this->refreshed_at = $this->current_time;

        $this->rocky->setVariable("state", $this->state);

        //$this->nuuid = substr($this->variables_thing->variables_thing->uuid,0,4); 
        //$this->variables_thing->setVariable("flag_id", $this->nuuid);

        $this->rocky->setVariable("refreshed_at", $this->current_time);

        //$this->makeChoices();
        //$this->makePNG();

        $this->thing->log($this->agent_prefix . 'set Rocky to ' . $this->state, "INFORMATION");


        return;
    }


    function get()
    {
        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->previous_state = $this->rocky->getVariable("state");
        $this->refreshed_at = $this->rocky->getVariable("refreshed_at");

        $this->thing->log($this->agent_prefix . 'got from db ' . $this->previous_state, "INFORMATION");


        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isRocky($this->previous_state)) {
            $this->state = $this->previous_state;
        } else {
            $this->state = $this->default_state;
        }

//        $this->thing->choice->Create($this->keyword, $this->node_list, $this->state);
//        $check = $this->thing->choice->current_node;

        $this->thing->log($this->agent_prefix . 'got a ' . strtoupper($this->state) . ' FLAG.' , "INFORMATION");
return;

    }



    function getNuuid()
    {
        $agent = new Nuuid($this->thing, "nuuid");
        $this->nuuid_png = $agent->PNG_embed;
    }

    function getUuid()
    {
        $agent = new Uuid($this->thing, "uuid");
        $this->uuid_png = $agent->PNG_embed;
    }

    function getQuickresponse($txt = "qr")
    {
        $agent = new Qr($this->thing, $txt);
        $this->quick_response_png = $agent->PNG_embed;
    }

    function setState($state)
    {
        $this->state = "easy";
        if ((strtolower($state) == "hard") or (strtolower($state) == "easy")) {
            $this->state = $state;
        }
    }

    function getState()
    {
        if (!isset($this->state)) {$this->state = "easy";}
        return $this->state;

    }


    function init()
    {
        // Rocky variables

        if (!isset($this->channel_count)) {$this->channel_count = 2;}
        if (!isset($this->volunteer_count)) {$this->volunteer_count = 3;}
        if (!isset($this->food)) {$this->food = "X";}

        // $this->setProbability();
        // $this->setRules();
    }

	private function setSignals()
    {

        $this->getResponse();

		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "rocky";

         $this->makePNG();

        $this->makeSMS();

        $this->makeMessage();
        // $this->makeTXT();
        $this->makeChoices();

 		$this->thing_report["info"] = "This creates an exercise message.";
 		$this->thing_report["help"] = 'Try CHARLEY.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        $this->makeWeb();

        $this->makeTXT();
        $this->makePDF();

		return $this->thing_report;
	}

    function makeChoices ()
    {
       $this->thing->choice->Create($this->agent_name, $this->node_list, "rocky");
       $this->choices = $this->thing->choice->makeLinks('rocky');

       $this->thing_report['choices'] = $this->choices;
    }

    function makeSMS()
    {
        $sms = "ROCKY " . strtoupper($this->state) . " | ";

        $sms .= $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function getCast()
    {
        // Load in the cast. And roles.
        // Use the Charley cast.
        $file = $this->resource_path .'/charley/charley.txt';
        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {

                $person_name = $line;
                $arr = explode(",",$line);
                $name= trim($arr[0]);
                if(isset($arr[1])) {$role = trim($arr[1]);} else {$role = "X";}

                // Unique name <> Role mappings. Check?
                $this->name_list[$role] = $name;
                $this->role_list[$name] = $role;

                //$this->placename_list[] = $place_name;
                $this->cast[] = array("name"=>$name, "role"=>$role); 
            }

            fclose($handle);
        } else {
            // error opening the file.
        }
    }

    function getName($role = null)
    {
        if (!isset($this->name_list)) {$this->getCast();}

        if ($role == "X") {$this->name = "Rocky"; return;}

        $this->name = array_rand(array("Rocky", "Rocket J. Squirrel"));

        $input = array("Rocky", "Rocket");

        // Pick a random Charles.
        $this->name = $input[array_rand($input)];
        if (isset($this->name_list[$role])) {$this->name = $this->name_list[$role];}

        return $this->name;
    }

    function getResponse()
    {

        if (isset($this->response)) {return;}

        //$text = $this->texts[$nom][$suit];
        //$number = $this->numbers[$nom][$suit];

//var_dump($text);
//var_dump($number);

//        $this->response = $text;

    }

    function getMember()
    {
        if (isset($this->member)) {return;}

        var_dump($this->from);

        //if (isset($this->messages)) {return;}

        // Load in the cast. And roles.
        $file = $this->resource_path .'/vector/members.txt';
        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        $count = 0;

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $arr = explode(",",$line);
                $member_call_sign = trim($arr[0]);
                $member_sms = trim($arr[1]);

                if ($this->from == $member_sms) {
                    $this->member['call_sign'] = $member_call_sign;
                    $this->member['sms'] = $member_sms;
                    return;
                }

  //         var_dump($arr); 
            }
        }

        $this->member['call_sign'] = "ROCKY";
        $this->member['sms'] = "(778) 792-0847";


    }

    function getMessages()
    {
        if (isset($this->messages)) {return;}

        // Load in the cast. And roles.

        $this->getState();
//var_dump($this->state);

        if ($this->state == "hard") {
            $file = $this->resource_path .'/vector/message-hard-a01.txt'; // Note s missing
        } else if ($this->state == "easy") { 
            $file = $this->resource_path .'/vector/messages-easy-a01.txt';
        }


        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        $count = 0;

        if ($handle) {
            while (($line = fgets($handle)) !== false) {

            $line = trim($line);

$count += 1;

if ($line == "---") {

    var_dump($message);

    $max_line_length = 0;

    foreach($message as $line_number=>$text) {
        if ($line_number == 0) {continue;}
        if ($line_number == 1) {continue;}
        if (strlen($text) > $max_line_length) {$max_line_length = strlen($text); $longest_line_number = $line_number;}
    }

$line_count = count($message) - 1;
$meta = $message[1];
//$place_from = $message[$line_count-1];
//$message_from = $message[$line_count-2];

//$text = $message[$line_count-3];
$text = $message[$longest_line_number];

//$line_count = $line_count - 1;

if ($longest_line_number+ 4 == ($line_count)) {

$name_from = $message[$longest_line_number+1];
$position_from = $message[$longest_line_number+2];
$organization_from = $message[$longest_line_number+3];
$number_from = $message[$longest_line_number + 4];

} else if ($longest_line_number+3 == $line_count) {

$name_from = $message[$longest_line_number+1];
$position_from = $message[$longest_line_number+2];
$organization_from = $message[$longest_line_number+3];
$number_from = "X";
} else if ($longest_line_number+2 == $line_count) {

$name_from = $message[$longest_line_number+1];
$position_from = $message[$longest_line_number+2];
$organization_from = "X";
$number_from = "X";
} else if ($longest_line_number+1 == $line_count) {

$name_from = $message[$longest_line_number+1];
$position_from = "X";
$organization_from = "X";
$number_from = "X";
}

if (strtolower(substr($message[$longest_line_number-4],0,2)) == "nr") {

$name_to = $message[$longest_line_number-3];
$position_to = $message[$longest_line_number-2];
$organization_to = $message[$longest_line_number - 1];
$number_to = "X";

} else if (strtolower(substr($message[$longest_line_number-3],0,2)) == "nr") {

$name_to = $message[$longest_line_number-2];
$position_to = $message[$longest_line_number-1];
$organization_to = "X";
$number_to = "X";

} else if (strtolower(substr($message[$longest_line_number-2],0,2)) == "nr")  {

$name_to = $message[$longest_line_number-1];
$position_to = "X";
$organization_to = "X";
$number_to = "X";

} else {
$name_to = $message[$longest_line_number-4];
$position_to = $message[$longest_line_number-3];
$organization_to = $message[$longest_line_number-2];
$number_to = $message[$longest_line_number - 1];
}


//        foreach(range(0,2) as $i) {

//            $tmp_array = $array;
//            $tmp = $tmp_array[$i];
//            $tmp_array[$i] = $tmp_array[$i+1];
//            $tmp_array[$i+1] = $tmp;
//            $result[] = $tmp_array;
//        }


//$message_to = "foo";
//$place_to = "bar";


//$text = "text";

//    $this->messages[] = array("meta"=>$meta, "message_to"=>$message_to, "place_to"=>$place_to, "text"=>$text, "message_from"=>$message_from, "place_from"=>$place_from ); 
    $this->messages[] = array("meta"=>$meta, "name_to"=>$name_to,  "position_to"=>$position_to,  "organization_to"=>$organization_to,"number_to"=>$number_to, "text"=>$text, 
        "name_from"=>$name_from, "position_from"=>$place_from, "organization_from"=>$organization_from, "number_from"=>$number_from ); 




    $count = 0;
    $nr = null;
    $nom_to = null;
    $place_to = null;
    $message = null;
    $nom_from = null;
    $place_from = null;
}


/*
switch($count)
{
    case 0:
        break;
    case 1:
        $meta = $line;
        break;
    case 2:
        $message_to = $line;
        break;
    case 3: 
        $place_to = $line;
        break;
    case 4: 
        $text = $line;
        break;
    case 5: 
        $message_from = $line;
        break;
    case 6: 
        $place_from = $line;
        break;
    default;
    //    echo 'Please make a new selection...';
    break;
}
*/
    $message[] = $line;

/*
                $arr = explode(",",$line);

                $nom = $arr[0]; // Describer of the card
                $suit = $arr[1];
                $number = trim($arr[2]);
                $text = trim($arr[3]);

                $from = "X";
                if (isset($arr[4])) {$from = trim($arr[4]);}

                $to = "X";
                if (isset($arr[5])) {$to = trim($arr[5]);}

                //$this->nom_list[] = $nom;
                //$this->suit_list[] = $suit;
                //$this->number_list[] = $number;
                //$this->text_list[] = $text;

                $this->texts[$nom][$suit] = $text;
                $this->numbers[$nom][$suit] = $number;
*/
//                if ((isset($place_from)) and ($place_from != null)) {
//                    $this->messages[] = array("meta"=>$meta, "message_to"=>$message_to, "place_to"=>$place_to, "text"=>$text, "message_from"=>$message_from, "place_from"=>$place_from ); 
//                }
           }

            fclose($handle);
        } else {
            // error opening the file.
        }
    }

    function getMessage()
    {
        $this->getMessages();
        $this->message = $this->messages[array_rand($this->messages)];

var_dump($this->message);

        $this->meta = $this->message['meta'];

        $this->name_to = $this->message['name_to'];
        $this->position_to = $this->message['position_to'];
        $this->organization_to = $this->message['organization_to'];

        $this->text = $this->message['text'];

        $this->name_from = $this->message['name_from'];
        $this->position_from = $this->message['position_from'];
        $this->organization_from = $this->message['organization_from'];


        //if ($this->number == "X") {$this->number = "REPORT";}
        //if ($this->number == 0.5) {$this->number = "HALVE";}

        //if (is_numeric($this->number)) {
        //    if ($this->number < 0) {$this->number = "SUBTRACT " . abs($this->number);}
        //    if ($this->number > 0) {$this->number = "ADD " . $this->number;} 
            //if ($this->number == 0) {$this->number = "BINGO";} 
        //}

        //$this->fictional_to = $this->getName($this->role_to);
        //$this->fictional_from = $this->getName($this->role_from);

        $this->response = $this->meta . " " . $this->name_to . " " . $this->position_to . " " . $this->organization_from. " " . $this->text ." " . $this->name_from . ", " . $this->position_from . ", " . $this->organization_from;

$arr = explode("/",$this->meta);
var_dump($arr);
$this->message['number'] = $arr[0];
$this->message['precedence'] = $arr[1];
$this->message['hx'] = null; // Not used?
$this->message['station_origin'] = $arr[2];
$this->message['check'] = $arr[3];
$this->message['place_filed'] = $arr[4];
$this->message['time_filed'] = $arr[5];
$this->message['date_filed'] = $arr[6];


        //if (($this->role_to == "X") and ($this->role_from == "X") and ($this->text == "X")) {
        //    $this->response = $this->number . " " . $this->unit . ".";
        //}

        //if (($this->role_to == "X") and ($this->role_from == "X") and ($this->text != "X")) {
        //    $this->response = $this->text . " / " . $this->number. " " . $this->unit . ".";
        //}

        //if (($this->role_to == "X") and ($this->role_from != "X") and ($this->text != "X")) {
        //    $this->response = "to: < ? >" . " from: " . $this->fictional_from .  ", " . $this->role_from . " / " . $this->text . " / " . $this->number. " " . $this->unit . ".";
        //}

    }

    function makeMessage()
    {
        $message = $this->response . "<br>";

        $uuid = $this->uuid;

        $message .= "<p>" . $this->web_prefix . "thing/$uuid/rocky\n \n\n<br> ";

        $this->thing_report['message'] = $message;

        return;

    }

    function getBar()
    {
        $this->bar = new Bar($this->thing, "display");
    }


    function setRocky()
    {
    }

    function getRocky()
    {
    }

    function makeWeb()
    {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/rocky';

        $this->node_list = array("rocky"=>array("rocky", "bullwinkle","charley"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "rocky");
        $choices = $this->thing->choice->makeLinks('rocky');

        if (!isset($this->html_image)) {$this->makePNG();}

        $web = '<a href="' . $link . '">'. $this->html_image . "</a>";
        $web .= "<br>";

        $web .= $this->response . "<br";
        $web .= "<br>";


        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( time() - $this->refreshed_at );
        $web .= "Injected about ". $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['web'] = $web;


    }

    function makeTXT()
    {
        $txt = "Traffic for ROCKY.\n";
        $txt .= 'Duplicate messages may exist. Can you de-duplicate?';
        $txt .= "\n";

        $txt .= $this->response;

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
        $this->blue = imagecolorallocate($this->image, 0, 68, 255);

        $this->flag_yellow = imagecolorallocate($this->image, 255, 239, 0);


//var_dump($this->suit);
//exit();
/*
switch (trim($this->suit)) {
    case "diamonds":
        imagefilledrectangle($this->image, 0, 0, 164, 164, $this->red);
        $textcolor = imagecolorallocate($this->image, 255, 255, 255);
        break;
    case "hearts":
        imagefilledrectangle($this->image, 0, 0, 164, 164, $this->blue);
        $textcolor = imagecolorallocate($this->image, 255, 255, 255);
        break;
    case "clubs":
        imagefilledrectangle($this->image, 0, 0, 164, 164, $this->flag_yellow);
        $textcolor = imagecolorallocate($this->image, 0, 0, 0);
        break;
    case "spades":
        imagefilledrectangle($this->image, 0, 0, 164, 164, $this->grey);
        $textcolor = imagecolorallocate($this->image, 255, 255, 255);
        break;

    default:

        imagefilledrectangle($this->image, 0, 0, 164, 164, $this->white);
        $textcolor = imagecolorallocate($this->image, 0, 0, 0);
}
*/
        imagefilledrectangle($this->image, 0, 0, 164, 164, $this->white);
        $textcolor = imagecolorallocate($this->image, 0, 0, 0);

//        $this->drawRocky(164/2,164/2);

        // Write the string at the top left
        $border = 30;
        $radius = 1.165 * (164 - 2 * $border) / 3;



// devstack add path
//$font = $this->resource_path . '/var/www/html/stackr.test/resources/roll/KeepCalm-Medium.ttf';
$font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';
$text = "EXERCISE EXERCISE EXERCISE WELFARE TEST ROCKY 5";
$text = "ROCKY";
$text = $this->message['text'];

if (!isset($this->bar)) {$this->getBar();}

$bar_count = $this->bar->bar_count;


// Add some shadow to the text

imagettftext($this->image, 40 , 0, 0 - $this->bar->bar_count*5, 75, $this->grey, $font, $text);

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

imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $textcolor, $font, $this->message['station_origin']);

$size = 10;

imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height*4/5, $textcolor, $font, $this->message['station_origin']);


    // Small nuuid text for back-checking.
     imagestring($this->image, 2, 140, 0, $this->thing->nuuid, $textcolor);




        // Save the image
        //header('Content-Type: image/png');
        //imagepng($im);
        //xob_clean();

// https://stackoverflow.com/questions/14549110/failed-to-delete-buffer-no-buffer-to-delete
if (ob_get_contents()) ob_clean();

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
        //imagedestroy($this->image);
//        $this->thing_report['png'] = $imagedata;



//        $this->PNG_data = "data:image/png;base64,'.base64_encode($imagedata).'";

        $this->html_image = $response;
        //$this->image = $agent->image;

        //$this->thing_report['png'] = $agent->PNG;
//        $this->thing_report['png'] = $agent->image_string;



        return $response;




        $this->PNG = $image;    
        $this->thing_report['png'] = $image;
 
       return;
    }



    function setRules()
    {
        $this->rules = array();
/*
        $this->rules[0][0][0][0][0][1] = 1;
        $this->rules[0][0][0][0][1][1] = 2;
        $this->rules[0][0][0][1][0][1] = 3;
        $this->rules[0][0][0][1][1][1] = 4;
        $this->rules[0][0][1][0][0][1] = 5;
        $this->rules[0][0][1][0][1][1] = 6;
        $this->rules[0][0][1][1][0][1] = 7;
        $this->rules[0][0][1][1][1][1] = 8;
        $this->rules[0][1][0][1][0][1] = 9;
        $this->rules[0][1][0][1][1][1] = 10;
        $this->rules[0][1][1][0][1][1] = 11;
        $this->rules[0][1][1][1][1][1] = 12;
        $this->rules[1][1][1][1][1][1] = 13;
*/
    }

    function computeTranspositions($array) {
        if (count($array) == 1) {return false;}
        $result = [];
        foreach(range(0,count($array)-2) as $i) {
            $tmp_array = $array;
            $tmp = $tmp_array[$i];
            $tmp_array[$i] = $tmp_array[$i+1];
            $tmp_array[$i+1] = $tmp;
            $result[] = $tmp_array;
        }

        return $result;
    }

    function read()
    {
        return $this->state;
    }

    function extractNuuid($input)
    {
        if (!isset($this->duplicables)) {
            $this->duplicables = array();
        }

        return $this->duplicables;
    }

    public function makePDF()
    {
        $txt = $this->thing_report['txt'];
        //$txt = explode($txt , "\n");
        // initiate FPDI
        $pdf = new Fpdi\Fpdi();


        // http://www.percs.bc.ca/wp-content/uploads/2014/06/PERCS_Message_Form_Ver1.4.pdf
        $pdf->setSourceFile($this->resource_path . 'percs/PERCS_Message_Form_Ver1.4.pdf');
        $pdf->SetFont('Helvetica','',10);

        $tplidx1 = $pdf->importPage(1, '/MediaBox');  

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s['orientation'], $s);  
//        $pdf->useTemplate($tplidx1,0,0,215);  
        $pdf->useTemplate($tplidx1);  

        $pdf->SetTextColor(0,0,0);

        $text = "Pre-printed text and graphics (c) 2018 Stackr Interactive Ltd";
        $pdf->SetXY(15, 20);
        $pdf->Write(0, $text);

        $pdf->SetXY(15, 20);
        $pdf->Write(0, $this->message['text']);


        $pdf->SetXY(8, 50);
        $pdf->Write(0, $this->message['number']);
        

switch (strtolower($this->message['precedence'])) {
    case 'r':
    case 'routine':
        $pdf->SetXY(24, 52.5);
        $pdf->Write(0, "X");
        break;
    case "p":
    case "priority":
        $pdf->SetXY(24, 46);
        $pdf->Write(0, "X");
        break;
    case "w":
    case "welfare":
        $pdf->SetXY(24, 59);
        $pdf->Write(0, "X");
        break;

    case "e":
    case "emergency":
        $pdf->SetXY(24, 39);
        $pdf->Write(0, "X");
        break;
    default:
}

        $pdf->SetXY(30, 76);
        $pdf->Write(0, $this->message['name_to']);

        $pdf->SetXY(30, 86);
        $pdf->Write(0, $this->message['position_to']);

        $pdf->SetXY(30, 97);
        $pdf->Write(0, $this->message['organization_to']);



        $pdf->SetXY(60+44, 84+81);
        $pdf->Write(0, $this->message['name_from']);

        $pdf->SetXY(60+44, 91+81);
        $pdf->Write(0, $this->message['position_from']);

        $pdf->SetXY(60+44, 97+81);
        $pdf->Write(0, $this->message['organization_from']);




        //$pdf->SetXY(30, 40);
        //$pdf->Write(0, $this->message['precedence']);

        $pdf->SetXY(50, 40);
        $pdf->Write(0, $this->message['hx']);

        $pdf->SetXY(80, 50);
        $pdf->Write(0, $this->message['station_origin']);

        $pdf->SetXY(112, 50);
        $pdf->Write(0, $this->message['check']);


        $pdf->SetXY(123, 50);
        $pdf->Write(0, $this->message['place_filed']);

        $pdf->SetXY(166, 50);
        $pdf->Write(0, $this->message['time_filed']);

        $pdf->SetXY(181, 50);
        $pdf->Write(0, $this->message['date_filed']);

        $num_rows = 5;
        $num_columns = 5;
        $offset = 0;
        $page =1;
        //$i = 1;

$i =0;
//$blanks = true;
//if ($blanks) {
//    $max_i = $this->max;
//}

//$num_pages = ceil($this->max / ($num_rows * $num_columns));

//while ($i<=$max_i) {
//        $pdf->SetXY(15, 10);

//        $txt = "PAGE " . $page . " OF " . $num_pages . "\n";
//        $pdf->Write(0, $txt);

//        $pdf->SetXY(15, 15);

//        $txt = "INDICES FROM " . ($i+1) . " TO " . (($num_rows * $num_columns)*($page )) ."\n";
//        $pdf->Write(0, $txt);

//var_dump($this->text);
        $words = explode(" ", $this->message['text']);

//var_dump($words);
$col_offset = 59;
$row_offset = 122;
$col_spacing = 38;
$row_spacing = 9;
//$i = 0;

$row = 0;
foreach($words as $index=>$word) {

    $col = $index % 5;
//var_dump($col);
//var_dump($row);
    $pdf->SetXY($col_offset + ($col-1) * $col_spacing, $row_offset + $row *$row_spacing);

    $pdf->Write(0, $word);

    if ($col == 4) {$row += 1;}



}
/*
        foreach(range(1,$num_rows) as $row) {
            foreach(range(1,$num_columns) as $col) {
                $local_offset = 0;
                $i = (($page - 1) * $num_rows * $num_columns) + ($col-1) * $num_rows + $row + $offset;


                if (isset($words[$i])) {
//                    if (!isset($this->duplicables_index[$i + $local_offset])) {continue;}
//                    if ($this->duplicables_index[$i + $local_offset] == false) {continue;}

y                    //$txt = " " . str_pad($this->duplicables_index[$i + $local_offset], 10, ' ', STR_PAD_LEFT);

//                    $pdf->SetXY($col_offset + ($col-1) * $col_spacing, $row_offset + $row *$row_spacing);
//                    $pdf->SetXY($row_offset + $row *$row_spacing, $col_offset + ($col-1) * $col_spacing);

//                    $pdf->SetXY(10 + ($col-1) *19, 30 + $row *5);
  
                  $pdf->Write(0, $words[$i]);

                }
       //         $i += 1;
            }
         //   $txt .= "\n";
        }
*/
        //$txt .= "\n";


//}



/*
        ob_start();
        $image = $pdf->Output('', 'I');
        $image = ob_get_contents();
        ob_clean();
*/
          $image = $pdf->Output('', 'S');


        $this->thing_report['pdf'] = $image;

        return $this->thing_report['pdf'];
    }

	public function readSubject()
    {
        if (!$this->getMember()) {$this->response = "Merp.";}

        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            if ($input == 'rocky') {
                $this->getMessage();

                if ((!isset($this->index)) or 
                    ($this->index == null)) {
                    $this->index = 1;
                }
//                $this->max = 400;
//                $this->size = 4;
                //$this->lattice_size = 40;
                return;
            }
        }

        $keywords = array("hard", "easy","hey", "rocky","charley","bullwinkle","natasha","boris");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) {

                        case 'hard':
                        case 'easy':
                            //$this->member_call_sign = "bb";
                            $this->setState($piece);
                            $this->getMessage();
                            $this->response .= " Set messages to " . strtoupper($this->state) .".";

                            return;

                        case 'hey':
                            //$this->member_call_sign = "bb";
                            $this->getMember();
                            $this->response = "Hey " . strtoupper($this->member['call_sign']) . ".";

                            return;


                        //case 'rocky':
                        //    $this->getMessage();

                        //    return;

                        case 'on':
                            //$this->setFlag('green');
                            //break;


                        default:
                     }
                }
            }
        }

        $this->getMessage();

        if ((!isset($this->index)) or 
            ($this->index == null)) {
            $this->index = 1;
        }

    return;
    }

}

?>
