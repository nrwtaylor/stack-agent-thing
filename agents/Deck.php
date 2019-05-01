<?php
namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Deck
{

	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->agent_input = $agent_input;

		$this->agent_name = "deck";
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

        $this->node_list = array("deck"=>array("index", "uuid"));

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

        $this->thing->log( $this->agent_prefix .'completed init. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("nuuids", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("nuuids", "refreshed_at"), $time_string );
        }

        $split_time = $this->thing->elapsed_runtime();

        //$agent = new Retention($this->thing, "retention");
        //$this->retain_to = $agent->retain_to;

        //$agent = new Persistence($this->thing, "persistence");
        //$this->time_remaining = $agent->time_remaining;
        //$this->persist_to = $agent->persist_to;

        //$this->thing->log( $this->agent_prefix .'got retention. ' . number_format($this->thing->elapsed_runtime() - $split_time) .  'ms.', "OPTIMIZE" );


        $this->readSubject();

        $this->init();
        $this->initDeck();

        //$this->thing->log( $this->agent_prefix .'completed getSnowflake. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        //$this->setNuuids();

        if ($this->agent_input == null) {$this->respond();}

        $this->thing->log( $this->agent_prefix .'completed setSignals. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->thing->log( $this->agent_prefix .'completed setNuuids. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );
        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['log'] = $this->thing->log;
	}

// https://www.math.ucdavis.edu/~gravner/RFG/hsud.pdf

// -----------------------

/*
    function getNuuid()
    {
        $agent = new Nuuid($this->thing, "nuuid");
        $this->nuuid_png = $agent->PNG_embed;
    }
*/
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
        if (!isset($this->deck)) {$this->deck = 1;}
        if (!isset($this->max)) {$this->max = "A";}
        if (!isset($this->size)) {$this->size = 25;}

        //$this->setProbability();
       // $this->setRules();
    }

	public function respond()
    {
		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "nuuids";

//        $this->makePNG();

        $this->thing->log( $this->agent_prefix .'completed makePNG. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->makeSMS();

        $this->makeMessage();
        //$this->makeTXT();
        $this->makeChoices();

 		$this->thing_report["info"] = "This creates a deck of cards.";
 		$this->thing_report["help"] = 'Try "CARD"';

        $this->thing->log( $this->agent_prefix .'started message. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        $this->makeWeb();

        $this->makeTXT();
        $this->makePDF();

		return $this->thing_report;
	}

    function makeChoices ()
    {
        $this->thing->log( $this->agent_prefix .'started makeChoices. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->thing->choice->Create($this->agent_name, $this->node_list, "nuuids");
        $this->thing->log( $this->agent_prefix .'completed create choice. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->choices = $this->thing->choice->makeLinks('nuuids');
        $this->thing->log( $this->agent_prefix .'completed makeLinks. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


        $this->thing_report['choices'] = $this->choices;
    }

    function makeSMS()
    {
        $sms = "DECK | ";
        $sms .= $this->web_prefix . "thing/".$this->uuid."/deck.pdf";
        $sms .= " | TEXT QR";
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeMessage()
    {

        $message = "Stackr made a non-duplicable index for you.<br>";
        $uuid = $this->uuid;
        $message .= "Keep on stacking.\n\n<p>" . $this->web_prefix . "thing/$uuid/deck\n \n\n<br> ";

        $this->thing_report['message'] = $message;

    }

    function setNuuids()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("deck", "card"), $this->card );
        //$this->thing->log($this->agent_prefix . ' saved duplicable index ' . $this->index . '.', "INFORMATION") ;
    }

    function getNuuids()
    {

        $this->thing->json->setField("variables");
        $this->card = $this->thing->json->readVariable( array("deck", "card") );

        if ($this->card == false) {
            $this->thing->log($this->agent_prefix . ' did not find a duplicable index.', "INFORMATION") ;
            // Return.
            return true;
        }

        $this->thing->log($this->agent_prefix . ' loaded deck index ' . $this->card . '.', "INFORMATION") ;
        return;
    }

    function makeWeb()
    {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/deck';
        $this->node_list = array("web"=>array("deck","card"));

        $web = "";

        //$web = '<a href="' . $link . '">';
        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';
        //$web .= "</a>";
        $i = 0;
        foreach ($this->cards as $key=>$thing) {
//var_dump($key);
//var_dump($value);

//            $thing = new Thing($train_thing['uuid']);
//var_dump($thing->suit);
//var_dump($thing->face);
//var_dump($thing->colour);

//exit();
            //$variables_json= $thing['variables'];
            //$variables = $this->thing->json->jsontoArray($variables_json);

//var_dump($variables);

            $web .= $thing->suit . " " . $thing->face . " " . $thing->colour . "<br>";
//            if ($i == 10) {break;} else {$i += 1;} 
        }

        $web .= "<br>";

        $web .= "<br><br>";
        $this->thing_report['web'] = $web;
    }

    function makeTXT()
    {

        $txt = "This is a DECK of CARDS.\n";
        $txt .= "\n";

        $txt .= "\n";

        $txt .= "\n";
        $txt .= "\n";

        $num_rows = 40;
        $num_columns = 10;
        $offset = 0;
        $page =1;
        $i = 1;

/*
$this->duplicables_list = array();
foreach(range($this->min,$this->max) as $index) {
    if ($this->cards[$index] == false) {continue;}

    $this->duplicables_list[$i] = $this->cards[$index];
    $i +=1;
    $max_i = $i;
}

$i =0;
$blanks = true;
if ($blanks) {
    $max_i = $this->max;
}
*/
/*
$num_pages = ceil($this->max / ($num_rows * $num_columns));
while ($i<$max_i) {
        $txt .= "PAGE " . $page . " OF " . $num_pages . "\n";
        $txt .= "FROM " . ($i+1) . " TO " . (($num_rows * $num_columns)*($page )) ."\n";
        $txt .= "\n";
        foreach(range(1,$num_rows) as $row) {
            foreach(range(1,$num_columns) as $col) {
                $local_offset = 0;
                $i = (($page - 1) * $num_rows * $num_columns) + ($col-1) * $num_rows + $row + $offset;

                if ($blanks) {
                    if (!isset($this->cards[$i + $local_offset])) {continue;}
                    $txt .= " " . str_pad($this->cards[$i + $local_offset], 10, ' ', STR_PAD_LEFT);
                } else {
                    $txt .= " " . str_pad($this->duplicables_list[$i], 10, ' ', STR_PAD_LEFT);
                }
            }
            $txt .= "\n";
        }
        $txt .= "\n";
        $page += 1;

}
*/

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }
/*
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
        $this->drawSnowflake(164/2,164/2);

        // Write the string at the top left
        $border = 30;
        $radius = 1.165 * (164 - 2 * $border) / 3;



// devstack add path
//$font = $this->resource_path . '/var/www/html/stackr.test/resources/roll/KeepCalm-Medium.ttf';
$font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';
$text = "test";
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


        return $response;




        $this->PNG = $image;    
        $this->thing_report['png'] = $image;
 
       return;
    }

*/

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


    function initDeck()
    {
        $this->thing->log($this->agent_prefix . 'initialized the deck index.', "INFORMATION");

        $this->cards = array();
        //$v[0] = 1;
        //$v[1] = 2;

        foreach(range(0,$this->size) as $i) {
            $this->cards[$i] = $this->makeCard();
            //$this->index[$i] = $this->makeNumber(3);
        }

        foreach(range(0,$this->size) as $k=>$i) {
            foreach(range(0,$this->size) as $k=>$j) {
                if ($i == $j) {continue;}
                if ($this->cards[$i] == $this->cards[$j]) {
                    $this->cards[$i]= false;
                    //$this->index[$i]= "*" . $this->index[$i] . "*";

                }
            }
        }



//var_dump($v);
        return $this->cards;

    }


    function echoDuplicables() {
//
//        $rows = 20;
//        $columns = 5;

//        foreach(range(0,$rows) as $row_index) {
//            foreach(range(0,columns) as $column_index) {
//            echo $row_index . " " . $column_index . " ".$value. " ";
//        }

    }

    function makeCards($n = null) 
    {
/*
//$n = "1234";
$n = ltrim($n, '0');

        $elems = str_split($n);

        $num_digits = $this->size;
        $num_digits = count($elems);
*/

        $v = array();
        //$v[0] = 1;
        //$v[1] = 2;

//        foreach(range(0,100) as $i) {
//            $v[$i] = rand(0,9999);
//        }
//var_dump($v);
//        return $v;

    }

function makeCard()
{
    $thing = new Thing(null);
//echo $thing->uuid . "\n";

    $thing->Create(null,"deck", 's/ make card');
    $card_thing = new Card($thing, "card");

    //$associations_agent = new Associations($card_thing, $card_thing->uuid);
    $this->thing->associate($card_thing->uuid);

    return $card_thing;
}

    function makeNumber($digits = 4)
    {
        return str_pad(rand(0,pow(10, $digits)-1), $digits, "0", STR_PAD_LEFT);
    }

    function computeTranspositions($array) {
        if (count($array) == 1) {return false;}
        $result = [];
        foreach(range(0,count($array)-2) as $i) {
            $tmp_array = $array;
            $tmp = $tmp_array[$i];
            $tmp_array[$i] = $tmp_array[$i+1];
            $tmp_array[$i+1] = $tmp;
            //$this->array_swap($array, $i, $i+1);
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

        $tplidx1 = $pdf->importPage(2, '/MediaBox');  

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s['orientation'], $s);  
//        $pdf->useTemplate($tplidx1,0,0,215);  
        $pdf->useTemplate($tplidx1);  


//$separator = "\r\n";
//$line = strtok($this->thing_report['txt'], $separator);

//while ($line !== false) {
//    # do something with $line
//    $line = strtok( $separator );
//echo $line;
//}
        $pdf->SetTextColor(0,0,0);
/*

        $num_rows = 17;
        $num_columns = 13;
        $offset = 0;
        $page =1;
        $i = 1;


$row_height = 15.85; //10
$column_width = 15.85; //10


        $size_x = $column_width - 2;
        $size_y = $row_height -2;


$i =0;
$blanks = true;
if ($blanks) {
    $max_i = $this->max;
}

$num_pages = ceil($this->max / ($num_rows * $num_columns));

//$row_height = $size_x; //10
//$column_width = $size_y; //10

$left_margin = 6;
$top_margin = 5;
*/
/*
while ($i<=$max_i) {
//        $pdf->SetXY(15, 10);

//        $txt = "PAGE " . $page . " OF " . $num_pages . "\n";
//        $pdf->Write(0, $txt);

//        $pdf->SetXY(15, 15);

//        $txt = "QR CODES FROM " . ($i+1) . " TO " . (($num_rows * $num_columns)*($page )) ."\n";
//        $pdf->Write(0, $txt);

        foreach(range(1,$num_rows) as $row) {
            foreach(range(1,$num_columns) as $col) {
                $local_offset = 0;
                $i = (($page - 1) * $num_rows * $num_columns) + ($col-1) * $num_rows + $row + $offset;

                if ($blanks) {
                    if (!isset($this->cards[$i + $local_offset])) {continue;}
                    if ($this->cards[$i + $local_offset] == false) {continue;}

                    $txt = " " . str_pad($this->cards[$i + $local_offset], 10, ' ', STR_PAD_LEFT);

                    //$pdf->SetXY(10 + ($col-1) *19, 30 + $row *5);
                    //$pdf->Write(0, $txt);

//        $t = new Thing(null);
        $this->getQuickresponse($txt);
        $pdf->Image($this->quick_response_png,$left_margin + ($col-1) * $column_width, $top_margin + ($row-1) * $row_height,$size_x,$size_y,'PNG');


                } else {
                    $txt .= " " . str_pad($this->duplicables_list[$i], 10, ' ', STR_PAD_LEFT);

                    //$pdf->SetXY(10 + ($col-1) *19, 30 + $row *5);
                    //$pdf->Write(0, $txt);

//        $t = new Thing(null);
        $this->getQuickresponse($txt);
        $pdf->Image($this->quick_response_png,$left_margin + ($col-1) *19, $top_margin + $row *5,30,30,'PNG');


                }
            }
            $txt .= "\n";
        }
        $txt .= "\n";
        $page += 1;



        // Bubble
        $pdf->SetFont('Helvetica','',12);
//        $pdf->SetXY(17, 248);

//        $txt = "NUUIDS | An index of four character";
//        $pdf->Write(0, $txt);

//        $pdf->SetXY(17, 253);
//
//        $txt = "identifiers.";
//        $pdf->Write(0, $txt);

        $pdf->SetFont('Helvetica','',10);

        if ($i >= $max_i) {
            break;
        } else {
        $pdf->addPage($s['orientation'], $s);  
//        $pdf->useTemplate($tplidx1,0,0,215);  
        $pdf->useTemplate($tplidx1);  
        }
}
*/

        $tplidx2 = $pdf->importPage(2, '/MediaBox');  
        $pdf->addPage($s['orientation'], $s);  

        $pdf->useTemplate($tplidx2);  


        // Generate some content for page 2
        $pdf->SetFont('Helvetica','',10);



        $txt = $this->web_prefix ."thing/" .$this->uuid."/nuuids"; // Pure uuid.  

        //$this->getUuid();
        //$pdf->Image($this->uuid_png,175,5,30,30,'PNG');

        $this->getQuickresponse($txt);
        $pdf->Image($this->quick_response_png,175,5,30,30,'PNG');


        $pdf->SetTextColor(0,0,0);

//        $pdf->SetXY(15, 10);
//        $t = $this->web_prefix . "thing/".$this->uuid;
//        $t = $this->uuid;

        $pdf->SetTextColor(0,0,0);
        $pdf->SetXY(15, 10);
        $t = $this->thing_report['sms'] . "";


        $pdf->Write(0, $t);

        //$pdf->SetXY(15, 15);
        //$text = $this->timestampSnowflake();
        //$pdf->Write(0, $text);

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

            if ($input == 'deck') {
                $this->makeCards();

//                if ((!isset($this->cards)) or 
//                    ($this->cards == null)) {
//                    $this->cards = 1;
//                }

//                $this->max = 221;
//                $this->size = 4;
                //$this->lattice_size = 40;
                return;
            }
        }

        $keywords = array("deck", "card", "cards", "suit");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) {

                        case 'deck':

                            $this->makeCards();

                            return;

                        case 'on':
                            //$this->setFlag('green');
                            //break;


                        default:
                     }
                }
            }
        }



        $this->makeCards();

        if ((!isset($this->card)) or 
            ($this->card == null)) {
            $this->card = 1;
        }

    }

}
