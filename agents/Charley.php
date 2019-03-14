<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Charley
{
	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;

		$this->agent_name = "charley";
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

        $this->node_list = array("charley"=>array("index", "uuid"));

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
        $this->getNuuid();

        $this->character = new Character($this->thing, "character is Charles T. Owl");


        $this->thing->log( $this->agent_prefix .'completed init. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );
/*
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("charley", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("charley", "refreshed_at"), $time_string );
        }
*/
        $split_time = $this->thing->elapsed_runtime();

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->init();
//        $this->get();

        $this->getCast();
        $this->getCards();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("charley", "refreshed_at") );


        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("charley", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);


        $this->nom = strtolower($this->thing->json->readVariable( array("charley", "nom") ));
        $this->number = $this->thing->json->readVariable( array("charley", "number") );
        $this->suit = $this->thing->json->readVariable( array("charley", "suit") );


        if ( ($this->nom == false) or ($this->number == false) ) {

            $this->readSubject();

            $this->thing->json->writeVariable( array("charley", "nom"), $this->nom );
            $this->thing->json->writeVariable( array("charley", "suit"), $this->suit );

            $this->thing->json->writeVariable( array("charley", "number"), $this->number );

            $this->thing->log($this->agent_prefix . ' completed read.', "OPTIMIZE") ;
        }

        // $this->readSubject();

        // $this->init();
        // $this->get();

        // $this->getCast();
        // $this->getCards();

        if ($this->agent_input == null) {$this->setSignals();}

        // $this->set();

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['log'] = $this->thing->log;

		return;
	}

    // https://www.math.ucdavis.edu/~gravner/RFG/hsud.pdf
    // -----------------------

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


    function init()
    {
        // Charley variables

        if (!isset($this->channel_count)) {$this->channel_count = 2;}
        if (!isset($this->volunteer_count)) {$this->volunteer_count = 3;}
        if (!isset($this->food)) {$this->food = "X";}

        // $this->setProbability();
        // $this->setRules();
    }

	private function setSignals()
    {

        $this->getResponse($this->nom, $this->suit);

		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "charley";

         $this->makePNG();

        $this->makeSMS();

        $this->makeMessage();
        // $this->makeTXT();
        $this->makeChoices();

 		$this->thing_report["info"] = "This creates an exercise message.";
 		$this->thing_report["help"] = 'Try NONSENSE.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        $this->makeWeb();

        $this->makeTXT();
        $this->makePDF();

		return $this->thing_report;
	}

    function makeChoices ()
    {
       $this->thing->choice->Create($this->agent_name, $this->node_list, "charley");
       $this->choices = $this->thing->choice->makeLinks('charley');

       $this->thing_report['choices'] = $this->choices;
    }

    function makeSMS()
    {
        $sms = "CHARLEY | ";

        $sms .= $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function getCast()
    {
        // Load in the cast. And roles.
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

        if ($role == "X") {$this->charley = "Rocky"; return;}

        $this->charley = array_rand(array("Charley", "Charlie"));

        $input = array("Charlie", "Charley", "Charles", "Charlene", "Charlize", "Carl", "Karl", "Carlos", "Caroline", "Charlotte");

        // Pick a random Charles.
        $this->charley = $input[array_rand($input)];
        if (isset($this->name_list[$role])) {$this->charley = $this->name_list[$role];}

        return $this->charley;
    }

    function getResponse($nom, $suit)
    {

        if (isset($this->response)) {return;}

        $text = $this->texts[$nom][$suit];
        $number = $this->numbers[$nom][$suit];
var_dump($text);
var_dump($number);

        $this->response = $text;

    }

    function getCards()
    {
        if (isset($this->cards)) {return;}

        // Load in the cast. And roles.
        $file = $this->resource_path .'/charley/messages.txt';
        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
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


                $this->cards[] = array("nom"=>$nom, "suit"=>$suit, "number"=>$number, "text"=>$text, "from"=>$from, "to"=>$to ); 
            }

            fclose($handle);
        } else {
            // error opening the file.
        }
    }

    function getCard()
    {
        $this->getCards();
        $this->card = $this->cards[array_rand($this->cards)];

        $this->nom = $this->card['nom'];
        $this->suit = $this->card['suit'];
        $this->number = $this->card['number'];
        $this->text = $this->card['text'];
        $this->role_from = $this->card['from'];
        $this->role_to = $this->card['to'];

        if ($this->number == "X") {$this->number = "REPORT";}
        if ($this->number == 0.5) {$this->number = "HALVE";}

        if (is_numeric($this->number)) {
            if ($this->number < 0) {$this->number = "SUBTRACT " . abs($this->number);}
            if ($this->number > 0) {$this->number = "ADD " . $this->number;} 
            //if ($this->number == 0) {$this->number = "BINGO";} 
        }

        $this->fictional_to = $this->getName($this->role_to);
        $this->fictional_from = $this->getName($this->role_from);

        $this->response = "to: " . $this->fictional_to . ", " . $this->role_to . " from: " . $this->fictional_from . ", " . $this->role_from . " / " . $this->text . " / " . $this->number . " " . $this->unit . ".";

        if (($this->role_to == "X") and ($this->role_from == "X") and ($this->text == "X")) {
            $this->response = $this->number . " " . $this->unit . ".";
        }

        if (($this->role_to == "X") and ($this->role_from == "X") and ($this->text != "X")) {
            $this->response = $this->text . " / " . $this->number. " " . $this->unit . ".";
        }

        if (($this->role_to == "X") and ($this->role_from != "X") and ($this->text != "X")) {
            $this->response = "to: < ? >" . " from: " . $this->fictional_from .  ", " . $this->role_from . " / " . $this->text . " / " . $this->number. " " . $this->unit . ".";
        }

    }

    function makeMessage()
    {
        $message = $this->response . "<br>";

        $uuid = $this->uuid;

        $message .= "<p>" . $this->web_prefix . "thing/$uuid/charley\n \n\n<br> ";

        $this->thing_report['message'] = $message;

        return;

    }

    function getBar()
    {
        $this->bar = new Bar($this->thing, "display");
    }


    function setCharley()
    {
    }

    function getCharley()
    {
    }

    function makeWeb()
    {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/charley';

        $this->node_list = array("charley"=>array("rocky", "bullwinkle","charley"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "charley");
        $choices = $this->thing->choice->makeLinks('charley');

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
        $txt = "Traffic for CHARLEY.\n";
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

//        $this->drawCharley(164/2,164/2);

        // Write the string at the top left
        $border = 30;
        $radius = 1.165 * (164 - 2 * $border) / 3;



// devstack add path
//$font = $this->resource_path . '/var/www/html/stackr.test/resources/roll/KeepCalm-Medium.ttf';
$font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';
$text = "EXERCISE EXERCISE EXERCISE WELFARE TEST ROCKY 5";
$text = "ROCKY";
$text = $this->nom;

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

imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $textcolor, $font, $this->nom);

$size = 10;

imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height*4/5, $textcolor, $font, $this->number . " FUEL");


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

        $pdf->setSourceFile($this->resource_path . 'snowflake/bubble.pdf');
        $pdf->SetFont('Helvetica','',10);

        $tplidx1 = $pdf->importPage(3, '/MediaBox');  

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s['orientation'], $s);  
//        $pdf->useTemplate($tplidx1,0,0,215);  
        $pdf->useTemplate($tplidx1);  

        $pdf->SetTextColor(0,0,0);

        $text = "Pre-printed text and graphics (c) 2018 Stackr Interactive Ltd";
        $pdf->SetXY(15, 20);
        $pdf->Write(0, $text);

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

        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            if ($input == 'charley') {
                $this->getCard();

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

        $keywords = array("charley","rocky","bullwinkle","natasha","boris");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) {

                        case 'charley':
                            $this->getCard();

                            return;

                        case 'on':
                            //$this->setFlag('green');
                            //break;


                        default:
                     }
                }
            }
        }

        $this->getCard();

        if ((!isset($this->index)) or 
            ($this->index == null)) {
            $this->index = 1;
        }

    return;
    }

}

?>
