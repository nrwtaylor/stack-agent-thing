<?php

//echo "Watson says hi<br>";
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '/var/www/html/stackr.ca/agents/message.php';

class Hexflake {

	public $var = 'hello';


    function __construct(Thing $thing, $agent_input = null) {

        $this->agent_input = $agent_input;
        $this->start_time = microtime(true);

		$this->agent_name = "hexflake";
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

        $this->node_list = array("snowflake"=>array("snowflake", "uuid"));


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
        $time_string = $this->thing->json->readVariable( array("snowflake", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("snowflake", "refreshed_at"), $time_string );
        }

    //    $this->thing->json->setField("variables");
    //    $this->roll = $this->thing->json->readVariable( array("snowflake", "roll") );
    //    $this->result = $this->thing->json->readVariable( array("snowflake", "result") );

            $this->getSnowflake();

            $this->readSubject();


            //$this->thing->json->writeVariable( array("snowflake", "roll"), $this->roll );
            //$this->thing->json->writeVariable( array("snowflake", "result"), $this->result );

//            $this->setSnowflake();

            $this->thing->log($this->agent_prefix . ' completed read. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE") ;
//        }



        if ($this->agent_input == null) {$this->setSignals();}

            $this->setSnowflake();


        $this->thing->log($this->agent_prefix . ' set response. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE") ;
        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['log'] = $this->thing->log;

		return;

	}

// https://www.math.ucdavis.edu/~gravner/RFG/hsud.pdf

// -----------------------

	private function setSignals() {

		$this->thing->flagGreen();

		// This should be the code to handle non-matching responses.

		$to = $this->thing->from;

		//echo "to:". $to;

		$from = "snowflake";

// This choice element is super slow.  It 
// is the difference between 6s and 351ms.
// Hard to justify a button question in response to a die roll.

        $this->makePNG();

        $this->makeSMS();
        $this->makeMessage();
        $this->makeTXT();
        $this->makeChoices();

        $this->makeWeb();

 		$this->thing_report["info"] = "This creates a snowflake.";
 		$this->thing_report["help"] = "This is about hexagons.";

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

		return $this->thing_report;


	}

    function makeChoices ()
    {

        $this->thing->choice->Create($this->agent_name, $this->node_list, "snowflake");

        $choices = $this->thing->choice->makeLinks('snowflake');
        $this->thing_report['choices'] = $choices;

    }



    function makeSMS()
    {
        $cell = $this->lattice[0][0][0];
        $sms = "SNOWFLAKE | cell (0,0,0) state ". strtoupper($cell['state']);

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeMessage()
    {

        $message = "Stackr made a snowflake for you.<br>";

        $uuid = $this->uuid;
        $this->web_prefix = "https://stackr.ca/";

        $message .= "Keep on stacking.\n\n<p>" . $this->web_prefix . "thing/$uuid/snowflake.png\n \n\n<br> ";
        $message .= '<img src="' . $this->web_prefix . 'thing/'. $uuid.'/snowflake.png" alt="snowflake" height="92" width="92">';


        $this->thing_report['message'] = $message;


        return;

    }

    function setSnowflake()
    {
        $this->thing->json->setField("message7");
        $this->thing->json->writeVariable( array("snowflake", "lattice"), $this->lattice );
    }

    function getSnowflake()
    {
        $n = 2;
        $this->thing->json->setField("message7");
        $this->lattice = $this->thing->json->readVariable( array("snowflake","lattice") );

        if ($this->lattice == false) {
            $this->initLattice($n);
        }
    }

    function makeWeb() {

        $link = 'https://stackr.ca/thing/' . $this->uuid . '/agent';

      $this->node_list = array("web"=>array("snowflake"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "web");
        $choices = $this->thing->choice->makeLinks('web');

$head= '
<td>
<table border="0" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF; border-bottom:0; border-radius:10px">
<tr>
<td align="center" valign="top">
<div padding: 5px; text-align: center">';


$foot = "</td></div></td></tr></tbody></table></td></tr>";

        $web = '<a href="' . $link . '">';
        $web .= '<img src= "https://stackr.ca/thing/' . $this->uuid . '/snowflake.png">';
        $web .= "</a>";
        $web .= "<br>";

        $web .= $this->sms_message;

        $web .= "<br><br>";
        $web .= $head;
        $web .= $choices['button'];
        $web .= $foot;

        $this->thing_report['web'] = $web;

    }



    function makeTXT()
    {
        $txt = 'This is a SNOWFLAKE';
        $txt .= "\n";
        $txt .= count($this->lattice). ' cells retrieved.';

        $txt .= "\n";
            $txt .= str_pad("COORD (Q,R,S)", 15, ' ', STR_PAD_LEFT);
            $txt .= " " . str_pad("NAME", 10, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad("STATE", 10, " " , STR_PAD_RIGHT);
            $txt .= " " . str_pad("VALUE", 10, " ", STR_PAD_LEFT);

            $txt .= " " . str_pad("COORD (X,Y)", 6, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";


        // Centre framed on 0,0,0
        $q_array= array(-2,-1,0,1,2);
        $r_array= array(-2,-1,0,1,2);
        $s_array= array(-2,-1,0,1,2);


        // Run the lattice update/display loops
        foreach($q_array as $q){
            foreach($r_array as $r){
                foreach($s_array as $s){

              
                    //$cell = $this->lattice[$q][$r][$s];
                    $cell = $this->getCell($q,$r,$s);


            $txt .= " " . str_pad("(".$q.",".$r.",".$s.")", 15, " ", STR_PAD_LEFT);

            $txt .= " " . str_pad($cell['name'], 10, ' ', STR_PAD_LEFT);
            $txt .= " " . str_pad($cell['state'], 10, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($cell['value'], 10, " " , STR_PAD_RIGHT);
        $txt .= "\n";


                }
            }
        }



        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;


    }







    public function makePNG()
    {


        $this->image = imagecreatetruecolor(125, 125);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        imagefilledrectangle($this->image, 0, 0, 125, 125, $this->white);

        $textcolor = imagecolorallocate($this->image, 0, 0, 0);
        $this->drawSnowflake(56,64);

        // Write the string at the top left
        $border = 30;
        $radius = 1.165 * (125 - 2 * $border) / 3;

//$number = 6;


//if ($number>99) {return;}

//if ($this->roll == "d8") {

//$this->drawSnowflake();




//$number = ($this->result[0]);
//var_dump($number);
//exit();
$font = '/var/www/html/stackr.ca/resources/roll/KeepCalm-Medium.ttf';
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

     imagestring($this->image, 2, 100, 0, $this->thing->nuuid, $textcolor);




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

    function hex_corner($center_x, $center_y, $x, $y, $i)
    {
        // So this takes a centre co-ordinate
        // and projects a point $size away from it at angle $i.

        $PI=3.14159;
        $angle_deg = 60 * $i   + 30;
        $angle_rad = $PI / 180 * $angle_deg;
//    return array($center_x + $size_x * cos($angle_rad),
//                 $center_y + $size_y * sin($angle_rad));

        return array($center_x + $x * cos($angle_rad) - sin($angle_rad) * $y,
                 $center_y + $x * sin($angle_rad) + cos($angle_rad) * $y);
    }

    function hextopixel($r, $g, $b, $s) {

        if ($r + $g + $b != 0) {return;}

        $y = 3/2 * $s * $b;
        // $b = 2/3 * $y / $s
        $x = sqrt(3) * $s * ( $b/2 + $r);
        //$x = - sqrt(3) * $s * ( $b/2 + $g )
        //$r = (sqrt(3)/3 * $x - $y/3 ) / $s
        //$g = -(sqrt(3)/3 * $x + $y/3 ) / $s

        return  array($x,$y);

    }

    function drawHexagon($q, $r, $s, $center_x, $center_y, $angle, $size, $color = null)
    {

        if ($color == null) {$color = $this->white;}

        list ($x_pt, $y_pt) = $this->hextopixel($q,$r,$s,$size);

        // Draw centre points of hexagons
        imageline($this->image, $center_x+$x_pt, $center_y+$y_pt, $center_x+$x_pt, $center_y+$y_pt, $this->black);


        $arr = array(0, 1, 2, 3, 4, 5);
        list($x_old,$y_old) = $this->hex_corner($x_pt,$y_pt, $size,0, count($arr)-1);
        $point_array = array();
        foreach ($arr as &$value) {

            list($x,$y) = $this->hex_corner($x_pt,$y_pt, $size, 0 ,$value);

            $point_array[] = $x+$center_x;
            $point_array[] = $y+$center_y;
            //imageline($this->image, $x+60, $y+60, $x_old+60, $y_old+60, $this->black);

            $x_old = $x;
            $y_old = $y;

        }

        imagepolygon($this->image, $point_array, count($point_array)/2, $this->black);

        if ($color != $this->white) {
            imagefilledpolygon($this->image, $point_array, count($point_array)/2, $color);
        }

    }

    function setProbability()
    {

        $this->p_freeze = array();
        $this->p_melt = array();
        foreach (range(1,13) as $t) {
            $this->p_melt[$t] = rand(0,1000)/1000;
            $this->p_freeze[$t] = rand(0,1000)/1000;
        }


    }

    function setRules() {

        $this->rules = array();
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
    }

    function getProb($a,$b,$c,$d,$e,$f) {

        //echo $a,$b,$c,$d,$e,$f;
        if (isset($this->rules[$f][$e][$d][$c][$b][$a])) {
            $n = $this->rules[$f][$e][$d][$c][$b][$a];
        } else {
            $n = 13;
        }
        //echo " p = " .$n

        // So we are supposed to use rule N for 
        // finding the probability of melting 
        // and freezing to the cell.

        $p_melt = $this->p_melt[$n];
        $p_freeze = $this->p_freeze[$n];

        return array($n, $p_melt,$p_freeze);
    }

    function initLattice($n)
    {
        $this->lattice_size = $n;

        foreach(range(-$n,$n) as $i){
            $q_array[$i] = null;
            $r_array[$i] = null;
            $s_array[$i] = null;
        }

        //$value=null;
        $value= array("name"=>null, "state"=>null, "value"=>0);

        foreach(range(-$n,$n) as $q){
            foreach(range(-$n,$n) as $r){
                foreach(range(-$n,$n) as $s){
                    $this->lattice[$q][$r][$s] = $value; 
//array($q=>array($r=>array($s=>$value)));
                }
            }
        }


   $this->lattice[-1][0][0] = array("name"=>"seed", "state"=>"on", "value"=>.5); 
   $this->lattice[0][0][0] = array("name"=>"seed", "state"=>"on", "value"=>.5); 
   $this->lattice[2][2][2] = array("name"=>"seed", "state"=>"on", "value"=>.5); 


    }

    function getCell($q,$r,$s) 
    {

  // $cell = true;

    if (($q > $this->lattice_size) or
        ($q < -$this->lattice_size) or
        ($r > $this->lattice_size) or
        ($r < -$this->lattice_size) or
        ($s > $this->lattice_size) or
        ($s < -$this->lattice_size)){

        $cell = array('name'=>'boundary', 'state'=>'off', 'value'=>0); // red?

    } else {
        if (isset($this->lattice[$q][$r][$s])) {
            $cell = $this->lattice[$q][$r][$s];
        } else {
            // Flag an error;
            $cell = array('name'=>"bork", 'state'=>'off', 'value'=>true);
        }
    }

//if (!isset($cell['state'])) {
//var_dump($cell['state']);
//exit();
//}

        return $cell;
    }

    function updateCell($q,$r,$s)
    {
        // Process the cell;
        // Because CA is 3D spreadsheets.
        //$q_array= array(-1,1);
        //$r_array= array(-1,1);
        //$s_array= array(-1,1);

        //$cell_value = 0;

        // Build a list of the state of the surrounding cells.

        $cell = $this->getCell($q,$r,$s);



        $states = array();
        $i = 0;
        foreach(range(-1,1,2) as $q_offset){
            foreach(range(-1,1,2) as $r_offset){
                foreach(range(-1,1,2) as $s_offset){
                    $neighbour_cell = $this->getCell($q+$q_offset,$r+$r_offset,$s+$s_offset);

                    if ($neighbour_cell['state'] == 'on') {
                        $states[$i] = 1;
                    } else {
                        $states[$i] = 0;
                    }
                    $i += 1;

                }
            }
        }

        // Perform some calculation here on $states,
        // to determine what state the current cell should be in.

        list($n, $p_melt, $p_freeze)  = $this->getProb($states[0],$states[1],$states[2],$states[3],$states[4],$states[5]);

        

        echo "( " . $q . ", ".  $r . ", " . $s . ") ";
        echo " | " . $p_melt . " " . $p_freeze;
        echo "<br>";

        if ($p_melt < $p_freeze) {
            $cell['state'] = 'on';
        }
        // Then set lattice value
        $this->lattice[$q][$r][$s] = $cell;
    }

    function drawSnowflake($q = null, $r = null, $s = null, $size = null, $index = 0)
    {

        $index += 1; // Track for recursion
        if ($index >= 2) {return;}

        if ($q == null) {$q=0;}
        if ($r == null) {$r=0;}
        if ($s == null) {$s=0;}

        if ($size == null) {$size=5;}

        $this->setProbability();
        $this->setRules();

        //$n = 3;
        $this->initLattice(2);

        $value= array("name"=>"seed " . $index, "state"=>'on', "value"=>.75);
//        $this->lattice[0][0][0] = $value; 

        // Centre framed on 0,0,0
        $q_array= array(-2,-1,0,1,2);
        $r_array= array(-2,-1,0,1,2);
        $s_array= array(-2,-1,0,1,2);


        foreach($q_array as $q){
            foreach($r_array as $r){
                foreach($s_array as $s){
                    $this->updateCell($q,$r,$s);
                }
            }
        }
        // Run the lattice update/display loops
        foreach($q_array as $q){
            foreach($r_array as $r){
                foreach($s_array as $s){

                 //   $this->updateCell($q,$r,$s);

                    // Gives any cell value
                    $cell = $this->lattice[$q][$r][$s];

                    $color = $this->black;
                    if ($cell['state'] == 'on') {$color = $this->red;}

                    //if ($cell['name'] == 'boundary') {$color = $this->black;}

                    if($index == 2) {$color=$this->green;}
                    // Draw out the state

$center_x = 60;
$center_y = 60;
$angle = 0;

                    foreach(range(0,5) as $i) {
                        $x = $size * 6;
                        $y = 0;

                    list($x_next, $y_next) = $this->hex_corner($center_x, $center_y, $x, $y, $i);
                    $angle = $i/5 * 3.14159;

                    // Draw an individual hexagon (q,r,s) centred at at an angle and distance from (x,y)

     //   if ($index >= 2) {

                   $this->drawHexagon($q,$r,$s,$x_next,$y_next,$angle,$size,$color);
    //            return;
  //      }
//$this->drawSnowflake($q,$r,$s,$index);

                       }


//                    $this->drawSnowflake($q,$r,$s,$index);

                    // Which eventually becomes recursively $this->drawSnowflake(...)

                }
            }
        }


        return;
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
        $input = strtolower($this->subject);
		return;
        }

    }



return;
