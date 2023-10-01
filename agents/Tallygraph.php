<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);ini_set('display_errors', 1);

class Tallygraph
{
    // So Tally graph show a graph of all the messages sent

	function __construct(Thing $thing, $agent_command = null)
    {
        $this->start_time = $thing->elapsed_runtime();

        // Setup Thing
        $this->thing = $thing;
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        // Setup Agent
        $this->agent = strtolower(get_class());
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent) . '" ';

        // Setup logging
        $this->thing_report['thing'] = $this->thing->thing;

        if ($agent_command == null) {
            $this->thing->log( 'Agent "Tally" did not find an agent command.' );
        }

        $this->agent_command = $agent_command;

        $this->nom_input = $agent_command . " " . $this->from . " " . $this->subject;

        $this->ignore_empty = true;

        $this->height = 200;
        $this->width = 300;


        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';


        $this->readInput();

        $this->thing->log( $this->agent_prefix . 'settings are: ' . $this->agent . ' ' . $this->name . ' ' . $this->identity . "." );


		// So I could call
		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->current_time = $this->thing->time();

		$this->node_list = array("tallycounter");

		$this->thing->log( '<pre> ' .$this->agent_prefix . ' running on Thing ' .  $this->thing->nuuid .  ' </pre>','INFORMATION' );

        // Not sure this is limiting.

        $this->getData();

		$this->Respond();

        $this->end_time = microtime(true);
        $this->actual_run_time = $this->end_time - $this->start_time;
        $milliseconds = round($this->actual_run_time * 1000);

        //$this->thing->log( 'Agent "Tallycounter" ran for ' . $milliseconds . 'ms.', 'OPTIMIZE' );
        $this->thing->log($this->agent_prefix . 'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

        $this->thing_report['log'] = $this->thing->log;
		return;
	}





    function set()
    {

//        $this->thing->Write(array("tallycounter",
//            "count"),  $this->count
//            );

//        $this->thing->Write(array("tallycounter",
//            "display"),  $this->display
//            );


//        $this->thing->Write(array("tallycounter",
//            "refreshed_at"),  $this->thing->time()
//            );


  //      $this->variables->setVariable("count", $this->count);

    //    $this->variables->setVariable("display", $this->display);

      //  $this->variables->setVariable("refreshed_at", $this->current_time);


        return;
    }


    function get()
    {
        return;
    }

    function getData() {

        $this->identity = "null" . $this->mail_postfix;
        // We will probably want a getThings at some point.
        $this->thing->db->setFrom($this->identity);
        $thing_report = $this->thing->db->agentSearch("tallycounter", 99);


        $things = $thing_report['things'];

        if ( $things == false  ) {return;}

        $this->points = array();
        foreach ($things as $thing) {

                $uuid = $thing['uuid'];

                $variables_json= $thing['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if (isset($variables['tallycounter'])) {

                    if(isset($variables['tallycounter']['count'])) {$variable = $variables['tallycounter']['count'];}
                    if(isset($variables['tallycounter']['refreshed_at'])) {$refreshed_at = strtotime($variables['tallycounter']['refreshed_at']);}
                }

            if ((($variable == null) or ($variable == 0)) and ($this->ignore_empty)) {
                continue;
            }

            // Until bug passes through database.
            if ($variable > 1e6) {continue;}


            $this->points[] = array("created_at"=>$refreshed_at, "variable"=>$variable);

        }


    }



    function getAgent() 
    {
        // Tallycounter
//        $this->getTallycounter();

        return;
    }

	function getVariables($agent = null) {
return;
        if ($agent == null) {
            $agent = $this->agent;
        }

        $this->variables_horizon = 99;
        $this->variables_agent = $agent; // Allows getVariables to pull in a different agents variables.
        // Here we only need to save the count.
        // But need to inspect Tally


//        $this->variables_agent = $agent;

        // So this returns the last 3 tally Things.
        // which should be enough.  One should be enough.
        // But this just provides some resiliency.

        $this->thing->log( 'Agent "Tallycounter" requested the variables.' ,'DEBUG');


        // We will probably want a getThings at some point.
        $this->thing->db->setFrom($this->identity);
        $thing_report = $this->thing->db->agentSearch($this->variables_agent, $this->variables_horizon);
        $things = $thing_report['things'];

        if ( $things == false  ) {
            $this->startVariables();
            return;
        }


            

        $this->thing->log( 'Agent "Tallycounter" got ' . count($things) . ' recent Tally Things.', 'INFORMATION' );

        $this->counter_uuids = array();




            foreach ($things as $thing) {
                // Check each of the three Things.
                $this->variables_thing = new Thing($thing['uuid']);

                $uuid = $thing['uuid'];
                $variable = $this->getVariable('variable');
                $name = $this->getVariable('name');
                $next_uuid = $this->getVariable('next_uuid');

                if (($this->name == $name))  {

                    $this->counter_uuids[] = $uuid;

                    break;
                }

            }

        $match_uuid = $next_uuid;

        $split_time = $this->thing->elapsed_runtime();
        $index = 0 ;

        while (true) {

            foreach ($things as $thing) {
                // Check each of the three Things.
                $this->variables_thing = new Thing($thing['uuid']);

                $uuid = $thing['uuid'];
                $variable = $this->getVariable('counter');
                //$name = $this->getVariable('name');
                $next_uuid = $this->getVariable('next_uuid');

                if ($name == $match_uuid)  {

                    $this->counter_uuids[] = $uuid;
                    break;
                }
            }

                $match_uuid = $next_uuid;

            $index += 1;

            $max_time = 1000 * 10; //ms
            if ($this->thing->elapsed_runtime() - $split_time > $max_time) {break;}

        }

        return;
	}

	function startVariables() 
    {
        $this->thing->log( 'Agent "Tallycounter" started a count.' );

        if (!isset($this->variables_thing)) { $this->variables_thing = $this->thing;}

        $this->setVariable("variable", 0);
        $this->setVariable("name", $this->name);

		return;
	}

    function getVariable($variable = null) {

        // Pulls variable from the database
        // and sets variables thing on the current record.
        // so shouldn't need to adjust the $this-> set
        // of variables and can refactor that out.

        // All variables should be callable by
        // $this->variables_thing.

        // The only Thing variable of use is $this->from
        // which is used to set the identity for 
        // self-tallies.  (Thing and Agent are the 
        // only two role descriptions.)

        if ($variable == null) {$variable = 'variable';}


        $this->variables_thing->db->setFrom($this->identity);

        $this->variables_agent = "tallycounter";

        $this->variables_thing->$variable = $this->variables_thing->Read( array($this->variables_agent, $variable) );

        // And then load it into the thing
//        $this->$variable = $this->variables_thing->$variable;
//        $this->variables_thing->flagGreen();

        return $this->variables_thing->$variable;
    }

    function setVariable($variable = null, $value) 
    {

        // Take a variable in the variables_thing and save
        // into the database.  Probably end
        // up coding setVariables, to 
        // speed things up, but it isn't needed from
        // a logic perspective.

        if ($variable == null) {$variable = 'variable';}

        $this->variables_thing->$variable = $value;

        $this->variables_thing->db->setFrom($this->identity);
        $this->variables_thing->Write( array($this->variables_agent, $variable), $value );

        return $this->variables_thing->$variable;
    }

	public function Respond()
    {
		// Develop the various messages for each channel.

		// Thing actions
		// Because we are making a decision and moving on.  This Thing
		// can be left alone until called on next.

        $this->thing->flagGreen();

		$this->sms_message = "TALLY GRAPH  | " . $this->web_prefix . "tallygraph/" . $this->uuid;

        if (isset($this->function_message)) {
            $this->sms_message .= " | " . $this->function_message;
        }

		$this->sms_message .= ' | TEXT ?';

		$this->thing_report['thing'] = $this->thing->thing;
		$this->thing_report['sms'] = $this->sms_message;

        $this->makePNG();

        $this->makeTXT();

		// While we work on this
		$this->thing_report['email'] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);


        $this->makeWeb();

		return $this->thing_report;
	}

    function drawGraph()
    {
        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;


        $num_points = count($this->points);
        if ($num_points == 0) {return;}

        $column_width = $this->width / $num_points;

        // Get min and max
        if (!isset($y_min)) { $y_min = $this->points[0]['variable']; }
        if (!isset($y_max)) { $y_max = $this->points[0]['variable']; }

        if (!isset($x_min)) { $x_min = $this->points[0]['created_at']; }
        if (!isset($x_max)) { $x_max = $this->points[0]['created_at']; }

        $i = 0;
        foreach ($this->points as $point) {
            if (($point['variable'] == null) or ($point['variable'] == 0)) {
                continue;
            }

            if ($point['variable'] < $y_min) {$y_min = $point['variable'];}
            if ($point['variable'] > $y_max) {$y_max = $point['variable'];}

            if ($point['created_at'] < $x_min) {$x_min = $point['created_at'];}
            if ($point['created_at'] > $x_max) {$x_max = $point['created_at'];}

            $i += 1;
        }

        $x_max = strtotime($this->current_time);

        $i = 0;

        foreach ($this->points as $point) {

            $y_spread = $y_max - $y_min;
            if ($y_spread == 0) {$y_spread = 100;}

            $y = 10 + $this->chart_height - ($point['variable'] - $y_min) / ($y_spread) * $this->chart_height;
            $x = 10 + ($point['created_at'] - $x_min) / ($x_max - $x_min) * $this->chart_width;

            if (!isset($x_old)) {$x_old = $x;}
            if (!isset($y_old)) {$y_old = $y;}

            $width = $x - $x_old;
            $offset = 1.5;

            imagefilledrectangle($this->image,
                    $x_old - $offset , $y_old - $offset,
                    $x_old + $width / 2 + $offset, $y_old + $offset,
                    $this->red);

            imagefilledrectangle($this->image,
                    $x_old + $width / 2 - $offset, $y_old - $offset,
                    $x - $width / 2 + $offset, $y + $offset ,
                    $this->red);

            imagefilledrectangle($this->image,
                    $x - $width / 2 - $offset , $y - $offset,
                    $x + $offset, $y + $offset ,
                    $this->red);

            $y_old = $y;
            $x_old = $x;

            $i += 1;
        }

        $allowed_steps = array(2,5,10,20,25,50,100,200,250,500,1000,2000,2500,10000,20000,25000,100000,200000,1000000,2000000,2500000,);
        $inc = ($y_max - $y_min)/ 5;
        $closest_distance = $y_max;

        foreach ($allowed_steps as $key=>$step) {
            $distance = abs($inc - $step);
            if ($distance < $closest_distance) {
                $closest_distance = $distance;
                $preferred_step = $step;
            }
        }
        $this->drawGrid($y_min, $y_max, $preferred_step);
    }

    private function drawGrid($y_min, $y_max, $inc)
    {


        $y = $this->roundUpToAny($y_min, $inc);

        while ($y <= $y_max) {

            $y_spread = $y_max - $y_min;
            if ($y_spread == 0) {$y_spread = 100;}

            $plot_y = 10 + $this->chart_height - ($y - $y_min) / $y_spread * $this->chart_height;


            imageline($this->image,
                    10 , $plot_y,
                    300-10, $plot_y,
                    $this->black);


            $font = $this->default_font; 

            $text = $y;
            // Add some shadow to the text
            //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);

            $size = 6;
            $angle = 0;

//$bbox = imagettfbbox ($size, $angle, $font, $text); 
//$bbox["left"] = 0- min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
//$bbox["top"] = 0- min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
//$bbox["width"] = max($bbox[0],$bbox[2],$bbox[4],$bbox[6]) - min($bbox[0],$bbox[2],$bbox[4],$bbox[6]); 
//$bbox["height"] = max($bbox[1],$bbox[3],$bbox[5],$bbox[7]) - min($bbox[1],$bbox[3],$bbox[5],$bbox[7]); 
//extract ($bbox, EXTR_PREFIX_ALL, 'bb'); 
//check width of the image 
//$width = imagesx($this->image); 
//$height = imagesy($this->image);

            $pad = 0;

            imagettftext($this->image, $size, $angle, 10, $plot_y-1, $this->grey, $font, $text);


            $y = $y + $inc;
        }
    }

    function roundUpToAny($n,$x=5) {
        return round(($n+$x/2)/$x)*$x;
    }

    private function drawBar() {
    }

    public function makePNG()
    {

        $this->image = imagecreatetruecolor($this->width, $this->height);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $this->white);

        $textcolor = imagecolorallocate($this->image, 0, 0, 0);

        $this->drawGraph();

        // Write the string at the top left
        $border = 30;
        $radius = 1.165 * (125 - 2 * $border) / 3;

        $font = $this->default_font;

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

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();
        ob_end_clean();

        $this->thing_report['png'] = $imagedata;

        $response = '<img src="data:image/png;base64,'.base64_encode($imagedata).'"alt="tallygraph"/>';
        $this->image_embedded = $response;

        imagedestroy($this->image);

        return $response;

        $this->PNG = $image;
        $this->thing_report['png'] = $image;

        return;
    }


    function makeWeb()
    {

        $link = $this->web_prefix . 'tallygraph/' . $this->uuid . '/agent';

$head= '
<td>
<table border="0" cellpadding="0" cellspacing="0" style="background-color:#FFFFFF; border-bottom:0; border-radius:10px">
<tr>
<td align="center" valign="top">
<div padding: 5px; text-align: center">';


$foot = "</td></div></td></tr></tbody></table></td></tr>";


        $web = '<a href="' . $link . '">';
        $web .= $this->image_embedded;
        $web .= "</a>";
        $web .= "<br>";

        if (count($this->points) != 0) {
            $tally =  $this->points[0]['variable'];
            $web .= number_format($tally) . " messages";
        }

        $web .= "<br><br>";

        $this->thing_report['web'] = $web;

    }

    function makeTXT()
    {
//        $txt = 'This is a TALLY for RAILWAY ' . $this->variables_agent->nuuid . '. ';
        $txt = 'This is a MESSAGE TALLY. ';

        $txt .= "\n";

        $count = 0;
        if (is_array($this->points)) {
          $count =  count($this->points);
        }

        $txt .= $count . ' points retrieved.';

        $txt .= "\n";
        $txt .= str_pad("VARIABLE", 7, ' ', STR_PAD_LEFT);

        $txt .= " " . str_pad("CREATED AT", 6, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        foreach($this->points as $key=>$point) {
            $txt .= str_pad($point['variable'], 7, '0', STR_PAD_LEFT);
            $txt .= " " . str_pad(strtoupper(date('Y M d D H:i', $point['created_at'])), 6, " ", STR_PAD_LEFT) . "";
            $txt .= "\n";
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;

    }

    public function defaultCommand() 
    {
        $this->agent = "tallycounter";
        //$this->limit = 5;
        $this->name = "thing";
        $this->identity = $this->from;
        return;
    }

    public function readInstruction() 
    {
        if($this->agent_command == null) {
            $this->defaultCommand();
            return;
        }

        $pieces = explode(" ", strtolower($this->nom_input));

        $this->agent = $pieces[0];
        $this->name = $pieces[1];
        $this->identity = $pieces[2];


        return;

    }

	public function readText()
    {
        // No need to read text.  Any identity input to Tally
        // increments the tally.
        return;
	}

    public function readInput()
    {
        $this->readInstruction();
        $this->readText();
        return;
    }


}
