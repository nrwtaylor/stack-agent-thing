<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);ini_set('display_errors', 1);

class Chart extends Agent
{
    // Latencygraph shows the stack latency history.

    // refactor
    function init()
	//function __construct(Thing $thing, $agent_command = null)
    {
  //      $this->start_time = $thing->elapsed_runtime();

        // Setup Thing
  //      $this->thing = $thing;
  //     $this->uuid = $thing->uuid;
  //      $this->to = $thing->to;
  //      $this->from = $thing->from;
  //      $this->subject = $thing->subject;

        // Setup Agent
  //      $this->agent = strtolower(get_class());
  //      $this->agent_prefix = 'Agent "' . ucfirst($this->agent) . '" ';

        // Setup logging
 //       $this->thing_report['thing'] = $this->thing->thing;

  //      if ($agent_command == null) {
  //          $this->thing->log( 'Agent "Chart" did not find an agent command.' );
  //      }
        $agent_command = $this->agent_input; //
        $this->agent_command = $agent_command;

        $this->nom_input = $agent_command . " " . $this->from . " " . $this->subject;

        $this->ignore_empty = true;

        $this->height = 200;
        $this->width = 300;


        $this->read();

//        $this->thing->log( $this->agent_prefix . 'settings are: ' . $this->agent . ' ' . $this->name . ' ' . $this->identity . "." );


		// So I could call
//		if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}
		// I think.
		// Instead.


        // Get some stuff from the stack which will be helpful.
  //      $this->web_prefix = $thing->container['stack']['web_prefix'];
  //      $this->mail_postfix = $thing->container['stack']['mail_postfix'];
  //      $this->word = $thing->container['stack']['word'];
  //      $this->email = $thing->container['stack']['email'];

  //      $this->current_time = $this->thing->json->time();

		$this->node_list = array("chart");

//		$this->thing->log( '<pre> ' .$this->agent_prefix . ' running on Thing ' .  $this->thing->nuuid .  ' </pre>','INFORMATION' );

        $this->getData();

//        if ($agent_command == null) {
//		    $this->respond();
//        }

  //      $this->thing->log('Agent "chart" ran for ' . number_format($this->thing->elapsed_runtime()-$this->start_time)."ms.", "OPTIMIZE");

   //     $this->thing_report['log'] = $this->thing->log;
	}

    function set()
    {
        $this->thing->json->setField("variables");
    }

    function getData()
    {
        $split_time = $this->thing->elapsed_runtime();

        $agent_name = "age";
        $tock_series = "age";

        $this->identity = "null" . $this->mail_postfix;
        // We will probably want a getThings at some point.
        $this->thing->db->setFrom($this->identity);
        $thing_report = $this->thing->db->agentSearch($agent_name, 99);

        $things = $thing_report['things'];

        if ( $things == false  ) {return;}

        $this->points = array();
        foreach ($things as $thing) {

            $variables_json= $thing['variables'];

            $variables = $this->thing->json->jsontoArray($variables_json);

            if (!isset($variables[$agent_name])) {continue;}

            ${$agent_name} = $variables[$agent_name];

            ${$dimension[0]} = $agent_name[$dimension[0]];
            ${$dimension[1]} = $agent_name[$dimension[1]];
            ${$tock_series} = strtotime($agent_name[$tock_series]);

            $elapsed_time = $run_time + $queue_time;

            if ((($dimension[0] == null) or ($dimension[0] == 0)) and ($this->ignore_empty)) {
                continue;
            }
            if ((($dimension[1] == null) or ($dimension[1] == 0)) and ($this->ignore_empty)) {
                continue;
            }
            if ((($tock_series == null) or ($tock_series == 0)) and ($this->ignore_empty)) {
                continue;
            }

            $this->points[] = array($tock_series=>${$tock_series}, $dimension[0]=>${$dimension[0]}, $dimension[1]=>${$dimension[1]});
        }

        $this->thing->log('Agent "charth" getData ran for ' . number_format($this->thing->elapsed_runtime()-$split_time)."ms.", "OPTIMIZE");

    }

	public function respond() {

		// Develop the various messages for each channel.

		// Thing actions
		// Because we are making a decision and moving on.  This Thing
		// can be left alone until called on next.
		$this->thing->flagGreen(); 
/*
		$this->sms_message = "LATENCY GRAPH  | " . $this->web_prefix . "latencygraph/" . $this->uuid;

        if (isset($this->function_message)) {
            $this->sms_message .= " | " . $this->function_message;
        }
		$this->sms_message .= ' | TEXT ?';
*/
        $this->makeSMS();
$this->makePNG();
        $this->makeWeb();
		$this->thing_report['thing'] = $this->thing->thing;
//		$this->thing_report['sms'] = $this->sms_message;

       // $this->makePNG();
        $this->makeTXT();

		// While we work on this
		$this->thing_report['email'] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);


        //$this->makeWeb();

		return $this->thing_report;
	}

    function makeSMS()
    {
        $this->sms_message = "LATENCY GRAPH  | " . $this->web_prefix . "chart/" . $this->uuid;

        if (isset($this->function_message)) {
            $this->sms_message .= " | " . $this->function_message;
        }
        $this->sms_message .= ' | TEXT ?';

        $this->thing_report['sms'] = $this->sms_message;
    }


    function drawGraph()
    {
        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;


        $i = 0;

        $this->tubAge();

        foreach ($this->tubs as $x=>$y) {

            $common_variable = $y;

      //      $this->y_spread = $y_max - $y_min;
      //      if ($this->y_spread == 0) {$this->y_spread = 100;}

            $y = 10 + $this->chart_height - ($common_variable - $this->y_min) / ($this->y_spread) * $this->chart_height;
            $x = 10 + ($x - $this->x_min) / ($this->x_max - $this->x_min) * $this->chart_width;

            if (!isset($x_old)) {$x_old = $x;}
            if (!isset($y_old)) {$y_old = $y;}

            // +1 to overlap bars
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

        $allowed_steps = array(0.02,0.05,0.2,0.5,2,5,10,20,25,50,100,200,250,500,1000,2000,2500, 10000, 20000, 25000, 100000,200000,250000);
        $inc = ($this->y_max - $this->y_min)/ 5;

        $closest_distance = $y_max;

        foreach ($allowed_steps as $key=>$step) {

            $distance = abs($inc - $step);
            if ($distance < $closest_distance) {
                $closest_distance = $distance;
                $preferred_step = $step;
            }
        }

        $this->drawGrid($this->y_min, $this->y_max, $preferred_step);
    }

    public function drawGrid($y_min, $y_max, $inc)
    {

        $y = $this->roundUpToAny($y_min, $inc);

        //echo $y . " ". $y_max;
        //exit();
        while ($y <= $y_max) {
            $y_spread = $y_max - $y_min;
            if ($this->y_spread == 0) {$this->y_spread = 100;}

            $plot_y = 10 + $this->chart_height - ($y - $y_min) / $y_spread * $this->chart_height;


            imageline($this->image,
                10 , $plot_y,
                300-10, $plot_y,
                $this->black);

            $font = $GLOBALS['stack_path'] . 'resources/roll/KeepCalm-Medium.ttf';

            $text = $y;

            $size = 6;
            $angle = 0;
            $pad = 0;

            imagettftext($this->image, $size, $angle, 10, $plot_y-1, $this->grey, $font, $text);

            $y = $y + $inc;
        }
    }

    function roundUpToAny($n,$x=5)
    {
        return round(($n+$x/2)/$x)*$x;
    }

    private function drawBar()
    {

    }

    public function makePNG()
    {
        //    $this->height = 200;
        //    $this->width = 300;

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

        $font = $GLOBALS['stack_path'] . 'resources/roll/KeepCalm-Medium.ttf';

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

        //echo '<img src="data:image/png;base64,'.base64_encode($imagedata).'"/>';
        $response = '<img src="data:image/png;base64,'.base64_encode($imagedata).'"alt="chart"/>';
        $this->image_embedded = $response;

//        imagedestroy($this->image);

        return $response;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'chart/' . $this->uuid . '/agent';

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

        $web .= "latency graph";

        $web .= "<br><br>";

        $this->thing_report['web'] = $web;
    }


    function makeTXT()
    {
        $txt = 'This is a CHART. ';
        $txt .= "\n";

        $count = null;
        if (is_array($this->points)) {
          $count =  count($this->points);
        }

        $txt .= $count . '' . ' Points retrieved.\n';

        $tubs = array();
        $dimension[0] = "age";
        $dimension[1] = "bin_sum";

        foreach($this->points as $key=>$point) {
            if (!isset($x_min)) {$x_min = $point['age'];}
            if (!isset($x_max)) {$x_max = $point['age'];}

            if ($point['age'] < $x_min) {$x_min = $point['age'];}
            if ($point['age'] > $x_max) {$x_max = $point['age'];}

            if (!isset($y_min)) {$y_min = $point['bin_sum'];}
            if (!isset($y_max)) {$y_max = $point['bin_sum'];}

            if ($point['bin_sum'] < $y_min) {$y_min = $point['bin_sum'];}
            if ($point['bin_sum'] > $y_max) {$y_max = $point['bin_sum'];}

        }

        $this->x_max = $x_max;
        $this->x_min = $x_min;

        $this->y_max = $y_max;
        $this->y_min = $y_min;


        $this->x_spread = $this->x_max - $this->x_min;
        $txt .= "";
        $txt .= "Dimension[0] tock_series spread is " . $this->x_spread . "\n";

        $num_tubs = 3;

        foreach($this->points as $key=>$point) {

            //$spread = the distance between youngest and oldest age
            $tub_index = intval(($num_tubs - 1) * ($x_max - $point['age']) / $this->x_spread) + 1;

            if(!isset($tubs[$tub_index])) {$tubs[$tub_index] = 1; continue;}
            $tubs[$tub_index] += 1;
        }

        foreach($tubs as $x=>$y) {
            $txt .= str_pad($x, 7, ' ', STR_PAD_LEFT);
            $txt .= " ";
            $txt .= str_pad($y, 7, ' ', STR_PAD_LEFT);
            $txt .= "\n";
        }


        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }




    public function defaultCommand()
    {
        $this->agent = "chart";
        $this->name = "thing";
        $this->identity = $this->from;
    }

    public function readInstruction()
    {
        if($this->agent_command == null) {
            $this->defaultCommand();
        }

        $pieces = explode(" ", strtolower($this->nom_input));

        $this->agent = $pieces[0];
        $this->name = $pieces[1];
        $this->identity = $pieces[2];
    }

	public function readText()
    {
        // No need to read text.  Any identity input to Tally
        // increments the tally.
	}

    public function read()
    {
        $this->readInstruction();
        $this->readText();
    }
}
