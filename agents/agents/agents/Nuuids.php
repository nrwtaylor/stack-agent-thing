<?php
namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Nuuids
{

	public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {

        $this->agent_input = $agent_input;

		$this->agent_name = "nuuids";
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

        $this->node_list = array("nuuids"=>array("index", "uuid"));

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
        $this->initNuuids();

        //$this->thing->log( $this->agent_prefix .'completed getSnowflake. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );
        $this->setNuuids();

        if ($this->agent_input == null) {$this->setSignals();}

        $this->thing->log( $this->agent_prefix .'completed setSignals. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->thing->log( $this->agent_prefix .'completed setNuuids. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );
        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['log'] = $this->thing->log;

		return;
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
        if (!isset($this->min)) {$this->min = 1;}
        if (!isset($this->max)) {$this->max = 400;}
        if (!isset($this->size)) {$this->size = 4;}

        //$this->setProbability();
       // $this->setRules();
    }

	private function setSignals()
    {
		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "nuuids";

//        $this->makePNG();

        $this->thing->log( $this->agent_prefix .'completed makePNG. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->makeSMS();

        $this->makeMessage();
        //$this->makeTXT();
        $this->thing->log( $this->agent_prefix .'completed makeTXT. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );
        $this->makeChoices();
        $this->thing->log( $this->agent_prefix .'completed makeChoices. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $this->thing->log( $this->agent_prefix .'completed makeWeb. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );


 		$this->thing_report["info"] = "This creates a duplicable number set.";
 		$this->thing_report["help"] = 'Try "DUPLICABLE"';

        $this->thing->log( $this->agent_prefix .'started message. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        $this->makeWeb();

        $this->makeTXT();
        $this->makePDF();
        $this->thing->log( $this->agent_prefix .'completed message. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE" );

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

     //  $this->thing_report['choices'] = false;

    }



    function makeSMS()
    {
        $sms = "NUUIDS | ";
        $sms .= $this->web_prefix . "thing/".$this->uuid."/nuuids.pdf";
        $sms .= " | TEXT NUUID";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeMessage()
    {

        $message = "Stackr made a non-duplicable index for you.<br>";

        $uuid = $this->uuid;

        $message .= "Keep on stacking.\n\n<p>" . $this->web_prefix . "thing/$uuid/nuuids\n \n\n<br> ";

        $this->thing_report['message'] = $message;

        return;

    }

    function setNuuids()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable( array("nuuids", "index"), $this->index );
//var_dump($this->index);
        $this->thing->log($this->agent_prefix . ' saved duplicable index ' . $this->index[0] . '.', "INFORMATION") ;
    }

    function getNuuids()
    {

        $this->thing->json->setField("variables");
        $this->index = $this->thing->json->readVariable( array("nuuids", "index") );

        if ($this->index == false) {
            $this->thing->log($this->agent_prefix . ' did not find a duplicable index.', "INFORMATION") ;
            // Return.
            return true;
        }

        $this->thing->log($this->agent_prefix . ' loaded nuuids index ' . $this->index . '.', "INFORMATION") ;
        return;
    }

    function makeWeb()
    {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';
        $this->node_list = array("web"=>array("nuuids","nuuid"));

        $web = "";

        //$web = '<a href="' . $link . '">';
        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';
        //$web .= "</a>";
        $i = 0;
        foreach ($this->index as $key=>$value) {
            $web .= $value . "<br>";
            if ($i == 10) {break;} else {$i += 1;} 
        }

        $web .= "<br>";

        $web .= "<br><br>";
        $this->thing_report['web'] = $web;
    }

    function makeTXT()
    {

        $txt = "This is an index of semi-unique NUUIDS.\n";
        $txt .= 'Duplicate NUUIDs omitted.';
        $txt .= "\n";
        //$txt .= count($this->lattice). ' cells retrieved.';


        $txt .= "\n";
            //$txt .= str_pad("INDEX", 15, ' ', STR_PAD_LEFT);
            //$txt .= " " . str_pad("DUPLICABLE", 10, " ", STR_PAD_LEFT);
            //$txt .= " " . str_pad("STATE", 10, " " , STR_PAD_RIGHT);
            //$txt .= " " . str_pad("VALUE", 10, " ", STR_PAD_LEFT);

            //$txt .= " " . str_pad("COORD (X,Y)", 6, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        $num_rows = 40;
        $num_columns = 10;
        $offset = 0;
        $page =1;
        $i = 1;


$this->duplicables_list = array();
foreach(range($this->min,$this->max) as $index) {
    if ($this->index[$index] == false) {continue;}

    $this->duplicables_list[$i] = $this->index[$index];
    $i +=1;
    $max_i = $i;
}

$i =0;
$blanks = true;
if ($blanks) {
    $max_i = $this->max;
}

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
                    if (!isset($this->index[$i + $local_offset])) {continue;}
                    $txt .= " " . str_pad($this->index[$i + $local_offset], 10, ' ', STR_PAD_LEFT);
                } else {
                    $txt .= " " . str_pad($this->duplicables_list[$i], 10, ' ', STR_PAD_LEFT);
                }
            }
            $txt .= "\n";
        }
        $txt .= "\n";
        $page += 1;

}


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


    function initNuuids()
    {
        $this->thing->log($this->agent_prefix . 'initialized the duplicables index.', "INFORMATION");

        $this->index = array();
        //$v[0] = 1;
        //$v[1] = 2;

        foreach(range(0,400) as $i) {
            $this->index[$i] = $this->makeNuuid();
            //$this->index[$i] = $this->makeNumber(3);

        }

        foreach(range(0,400) as $k=>$i) {
            foreach(range(0,400) as $k=>$j) {
                if ($i == $j) {continue;}
                if ($this->index[$i] == $this->index[$j]) {
                    $this->index[$i]= false;
                    //$this->index[$i]= "*" . $this->index[$i] . "*";

                }
            }
        }



//var_dump($v);
        return $this->index;

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

    function makeNuuids($n = null) 
    {
//echo "meep";
//exit();

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

        foreach(range(0,100) as $i) {
            $v[$i] = rand(0,9999);
        }
//var_dump($v);
        return $v;

    }

function makeNuuid()
{
    $t = new Thing(null);
    return $t->nuuid;
}

function makeNumber($digits = 4)
{
    return str_pad(rand(0,pow(10, $digits)-1), $digits, "0", STR_PAD_LEFT);
}

function computeTranspositions($array) {
//echo "foo";
//echo count($array);
//var_dump($array);
    if (count($array) == 1) {return false;}
//var_dump($array);
    $result = [];
    foreach(range(0,count($array)-2) as $i) {
        $tmp_array = $array;
        $tmp = $tmp_array[$i];
        $tmp_array[$i] = $tmp_array[$i+1];
        $tmp_array[$i+1] = $tmp;
        //$this->array_swap($array, $i, $i+1);
        $result[] = $tmp_array;
//        var_dump($array);
    }

//exit();
    return $result;

}
/*
function array_swap(&$array,$swap_a,$swap_b){
   list($array[$swap_a],$array[$swap_b]) = array($array[$swap_b],$array[$swap_a]);
}
*/
/*
function computePermutations($array) {
    $result = [];

    $recurse = function($array, $start_i = 0) use (&$result, &$recurse) {
        if ($start_i === count($array)-1) {
            array_push($result, $array);
        }

        for ($i = $start_i; $i < count($array); $i++) {
            //Swap array value at $i and $start_i
            $t = $array[$i]; $array[$i] = $array[$start_i]; $array[$start_i] = $t;

            //Recurse
            $recurse($array, $start_i + 1);

            //Restore old order
            $t = $array[$i]; $array[$i] = $array[$start_i]; $array[$start_i] = $t;
        }
    };

    $recurse($array);

    return $result;
}
*/
/*
function permutations(array $elements)
{
    if (count($elements) <= 1) {
        yield $elements;
    } else {
        foreach ($this->permutations(array_slice($elements, 1)) as $permutation) {
            foreach (range(0, count($elements) - 1) as $i) {
                yield array_merge(
                    array_slice($permutation, 0, $i),
                    [$elements[0]],
                    array_slice($permutation, $i)
                );
            }
        }
    }
}
*/
/*
function pc_permute($items, $perms = array( )) {
    if (empty($items)) { 
        print join(' ', $perms) . "<br>";
    }  else {
        for ($i = count($items) - 1; $i >= 0; --$i) {
             $newitems = $items;
             $newperms = $perms;
             list($foo) = array_splice($newitems, $i, 1);
             array_unshift($newperms, $foo);
             $this->pc_permute($newitems, $newperms);
         }
    }
}
*/
/*
function comb ($n, $elems) {
    if ($n > 0) {
      $tmp_set = array();
      $res = $this->comb($n-1, $elems);
      foreach ($res as $ce) {
          foreach ($elems as $e) {
             array_push($tmp_set, $ce . $e);
          }
       }
       return $tmp_set;
    }
    else {
        return array('');
    }
}
*/
//$elems = array('A','B','C');
//$v = comb(4, $elems);




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


//$separator = "\r\n";
//$line = strtok($this->thing_report['txt'], $separator);

//while ($line !== false) {
//    # do something with $line
//    $line = strtok( $separator );
//echo $line;
//}
        $pdf->SetTextColor(0,0,0);


        $num_rows = 40;
        $num_columns = 10;
        $offset = 0;
        $page =1;
        $i = 1;



$i =0;
$blanks = true;
if ($blanks) {
    $max_i = $this->max;
}

$num_pages = ceil($this->max / ($num_rows * $num_columns));

while ($i<=$max_i) {
        $pdf->SetXY(15, 10);

        $txt = "PAGE " . $page . " OF " . $num_pages . "\n";
        $pdf->Write(0, $txt);

        $pdf->SetXY(15, 15);

        //$txt = "INDICES FROM " . ($i+1) . " TO " . (($num_rows * $num_columns)*($page )) ."\n";
        $txt = "A PAGE OF DIFFERENT NOT UNIQUE NUMBERS";
        $pdf->Write(0, $txt);




        foreach(range(1,$num_rows) as $row) {
            foreach(range(1,$num_columns) as $col) {
                $local_offset = 0;
                $i = (($page - 1) * $num_rows * $num_columns) + ($col-1) * $num_rows + $row + $offset;

                if ($blanks) {
                    if (!isset($this->index[$i + $local_offset])) {continue;}
                    if ($this->index[$i + $local_offset] == false) {continue;}

                    $txt = " " . str_pad($this->index[$i + $local_offset], 10, ' ', STR_PAD_LEFT);

                    $pdf->SetXY(10 + ($col-1) *19, 30 + $row *5);
                    $pdf->Write(0, $txt);

                } else {
                    $txt .= " " . str_pad($this->duplicables_list[$i], 10, ' ', STR_PAD_LEFT);

                    $pdf->SetXY(10 + ($col-1) *19, 30 + $row *5);
                    $pdf->Write(0, $txt);

                }
            }
            $txt .= "\n";
        }
        $txt .= "\n";
        $page += 1;



        // Bubble
        $pdf->SetFont('Helvetica','',12);
        $pdf->SetXY(17, 248);

        $txt = "NUUIDS | An index of four character";
        $pdf->Write(0, $txt);

        $pdf->SetXY(17, 253);

        $txt = "identifiers.";
        $pdf->Write(0, $txt);

        $pdf->SetFont('Helvetica','',10);

        if ($i >= $max_i) {
            break;
        } else {
        $pdf->addPage($s['orientation'], $s);  
//        $pdf->useTemplate($tplidx1,0,0,215);  
        $pdf->useTemplate($tplidx1);  
        }
}


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

            if ($input == 'nuuids') {
                $this->makeNuuids();

                if ((!isset($this->index)) or 
                    ($this->index == null)) {
                    $this->index = 1;
                }
                $this->max = 400;
                $this->size = 4;
                //$this->lattice_size = 40;
                return;
            }
        }

        $keywords = array("nuuids");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) {

                        case 'nuuids':

                            $this->makeNuuids();

                            return;

                        case 'on':
                            //$this->setFlag('green');
                            //break;


                        default:
                     }
                }
            }
        }



        $this->makeNuuids();

        if ((!isset($this->index)) or 
            ($this->index == null)) {
            $this->index = 1;
        }

        //$this->max = 9999;
        //$this->size = 4;
        //$this->lattice_size = 40;

    return;
    }

}



?>
