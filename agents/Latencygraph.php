<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);ini_set('display_errors', 1);

class Latencygraph
{
    // Latencygraph shows the stack latency history.

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
            $this->thing->log( 'Agent "Latencygraph" did not find an agent command.' );
        }

        $this->agent_command = $agent_command;

        $this->nom_input = $agent_command . " " . $this->from . " " . $this->subject;

        $this->ignore_empty = true;

        $this->height = 200;
        $this->width = 300;


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


        $this->current_time = $this->thing->json->time();

		$this->node_list = array("latencygraph");

		$this->thing->log( '<pre> ' .$this->agent_prefix . ' running on Thing ' .  $this->thing->nuuid .  ' </pre>','INFORMATION' );

        //$split_time = $this->thing->elapsed_runtime();
        $this->getData();
        //$this->thing->log('Agent "Latencygraph" getData ran for ' . number_format($this->thing->elapsed_runtime()- $split_time)."ms.", "OPTIMIZE");

        if ($agent_command == null) {
		    $this->Respond();
        }

        $this->thing->log('Agent "Latencygraph" ran for ' . number_format($this->thing->elapsed_runtime()-$this->start_time)."ms.", "OPTIMIZE");

        $this->thing_report['log'] = $this->thing->log;
		return;
	}





    function set()
    {

        $this->thing->json->setField("variables");


        return;
    }

/*
    function get()
    {
        return;
    }
*/
    function getData()
    {
        $split_time = $this->thing->elapsed_runtime();

        $this->identity = "null" . $this->mail_postfix;
        // We will probably want a getThings at some point.
        $this->thing->db->setFrom($this->identity);
        $thing_report = $this->thing->db->agentSearch("latency", 99);

        $things = $thing_report['things'];

        if ( $things == false  ) {return;}

        $this->points = array();
        foreach ($things as $thing) {

            $variables_json= $thing['variables'];

            $variables = $this->thing->json->jsontoArray($variables_json);

            if (!isset($variables['latency'])) {continue;}

            $latency = $variables['latency'];

            // Check each of the three Things.
            //$this->variables_thing = new Thing($thing['uuid']);

            //$thing = new Thing($thing['uuid']);
            //$thing->json->setField("variables");

            //$run_time = $thing->getVariable("latency", "run_time");
            //$queue_time = $thing->getVariable("latency", "queue_time");
            //$refreshed_at = strtotime($thing->getVariable("latency", "refreshed_at"));


            $run_time = $latency['run_time'];
            $queue_time = $latency['queue_time'];
            $refreshed_at = strtotime($latency['refreshed_at']);

            $elapsed_time = $run_time + $queue_time;

            if ((($queue_time == null) or ($queue_time == 0)) and ($this->ignore_empty)) {
                continue;
            }
            if ((($run_time == null) or ($run_time == 0)) and ($this->ignore_empty)) {
                continue;
            }
            if ((($refreshed_at == null) or ($refreshed_at == 0)) and ($this->ignore_empty)) {
                continue;
            }

            $this->points[] = array("refreshed_at"=>$refreshed_at, "run_time"=>$run_time, "queue_time"=>$queue_time);
        }

        $this->thing->log('Agent "Latencygraph" getData ran for ' . number_format($this->thing->elapsed_runtime()-$split_time)."ms.", "OPTIMIZE");

    }
/*
    function getAgent()
    {
        return;
    }
*/
/*
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

                    //$next_uuid = $uuid;
                    $this->counter_uuids[] = $uuid;

     //               $this->thing->log( 'Agent "Tallycounter" loaded the tallycounter variable: ' . $this->variables_thing->variable . '.','INFORMATION' );
     //               $this->thing->log( 'Agent "Tallycounter" loaded the tallycounter name: ' . $this->variables_thing->name . '.','INFORMATION' );
     //               $this->thing->log( 'Agent "Tallycounter" next counter pointer is: ' . substr($this->variables_thing->next_uuid,0,4) . "." ,'DEBUG');

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

*/

/*
	function startVariables() 
    {
        $this->thing->log( 'Agent "Tallycounter" started a count.' );

        if (!isset($this->variables_thing)) { $this->variables_thing = $this->thing;}

        $this->setVariable("variable", 0);
        $this->setVariable("name", $this->name);

		return;
	}
*/
/*
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

//echo $this->identity;
//echo "meep";
//exit();

        $this->variables_thing->db->setFrom($this->identity);
        $this->variables_thing->json->setField("variables");

$this->variables_agent = "tallycounter";

        $this->variables_thing->$variable = $this->variables_thing->json->readVariable( array($this->variables_agent, $variable) );

        // And then load it into the thing
//        $this->$variable = $this->variables_thing->$variable;
//        $this->variables_thing->flagGreen();

        return $this->variables_thing->$variable;
    }
*/
/*
    function setVariable($variable = null, $value) 
    {

        // Take a variable in the variables_thing and save
        // into the database.  Probably end
        // up coding setVariables, to 
        // speed things up, but it isn't needed from
        // a logic perspective.

        if ($variable == null) {$variable = 'variable';}
//        if (!isset($this->variables_thing)) { $this->variables_thing = $this->thing;}

        $this->variables_thing->$variable = $value;

//echo $value;
//echo $this->identity;
//exit();

        $this->variables_thing->db->setFrom($this->identity);
        $this->variables_thing->json->setField("variables");
        $this->variables_thing->json->writeVariable( array($this->variables_agent, $variable), $value );

//        $this->$variable = $value;
//        $this->variables_thing->flagGreen();

        return $this->variables_thing->$variable;
    }

*/



	public function Respond() {

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
		$this->thing_report['thing'] = $this->thing->thing;
//		$this->thing_report['sms'] = $this->sms_message;

        $this->makePNG();


		// While we work on this
		$this->thing_report['email'] = $this->sms_message;
        $message_thing = new Message($this->thing, $this->thing_report);


        $this->makeWeb();

		return $this->thing_report;
	}

    function makeSMS()
    {
        $this->sms_message = "LATENCY GRAPH  | " . $this->web_prefix . "latencygraph/" . $this->uuid;

        if (isset($this->function_message)) {
            $this->sms_message .= " | " . $this->function_message;
        }
        $this->sms_message .= ' | TEXT ?';

        $this->thing_report['sms'] = $this->sms_message;
    }


    function drawGraph() {

        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

if (!isset($this->points)) {return true;}

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;

        $run_time = $this->points[0]['run_time'];
        $queue_time = $this->points[0]['queue_time'];

        $refreshed_at = $this->points[0]['refreshed_at'];

        // Get min and max
        if (!isset($y_min)) { $y_min = $run_time + $queue_time; }
        if (!isset($y_max)) {$y_max = $run_time + $queue_time;}

        if (!isset($x_min)) { $x_min = $refreshed_at; }
        if (!isset($x_max)) { $x_max = $refreshed_at; }

        $i = 0;
        foreach ($this->points as $point) {

            $run_time = $point['run_time'];
            $queue_time = $point['queue_time'];
            $elapsed_time = $run_time + $queue_time;
                    $refreshed_at = $point['refreshed_at'];

            if (($elapsed_time == null) or ($elapsed_time == 0 )) {
                continue;
            }

            if ($elapsed_time < $y_min) {$y_min = $elapsed_time;}
            if ($elapsed_time > $y_max) {$y_max = $elapsed_time;}

            if ($refreshed_at < $x_min) {$x_min = $refreshed_at;}
            if ($refreshed_at > $x_max) {$x_max = $refreshed_at;}

            $i += 1;
        }

        $x_max = strtotime($this->current_time);

        $i = 0;

        foreach ($this->points as $point) {

            $run_time = $point['run_time'];
            $queue_time = $point['queue_time'];
            $elapsed_time = $run_time + $queue_time;
            $refreshed_at = $point['refreshed_at'];

            $y_spread = $y_max - $y_min;
            if ($y_spread == 0) {$y_spread = 100;}

            $y = 10 + $this->chart_height - ($elapsed_time - $y_min) / ($y_spread) * $this->chart_height;
            $x = 10 + ($refreshed_at - $x_min) / ($x_max - $x_min) * $this->chart_width;

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

        //echo $y . " ". $y_max;
        //exit();
        while ($y <= $y_max) {
            $y_spread = $y_max - $y_min;
            if ($y_spread == 0) {$y_spread = 100;}

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
        $response = '<img src="data:image/png;base64,'.base64_encode($imagedata).'"alt="latencygraph"/>';
        $this->image_embedded = $response;

        imagedestroy($this->image);

        return $response;

//        $this->PNG = $image;
//        $this->thing_report['png'] = $image;

//       return;
    }

    function makeWeb()
    {

        $link = $this->web_prefix . 'latencygraph/' . $this->uuid . '/agent';

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

    public function defaultCommand()
    {
        $this->agent = "latencygraph";
        $this->name = "thing";
        $this->identity = $this->from;
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

    }

	public function readText()
    {
        // No need to read text.  Any identity input to Tally
        // increments the tally.
	}

    public function readInput()
    {
        $this->readInstruction();
        $this->readText();
    }
}
