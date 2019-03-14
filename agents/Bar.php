<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Bar
{
    function __construct(Thing $thing, $agent_input = null)
    {
        // Ticks are just a sub-division of a bar.
        // Tick variable = 15 minutes

        // Play a bar when asked.

        $this->agent_name = 'bar';
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
        $this->test= "Development code";

        $this->agent_input = $agent_input;

        $this->thing = $thing;

        $this->thing_report['thing']  = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        // Thing stuff
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log($this->agent_prefix . "received this Thing ".  $this->subject . '".');

        //$this->value_destroyed = 0;
        //$this->things_destroyed = 0;

        $this->stack_idle_mode = 'use'; // Prevents stack generated execution when idle.
        $this->cron_period = $this->thing->container['stack']['cron_period'];
        $this->start_time = $this->thing->elapsed_runtime();

        $this->state = "red"; // running

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';


        $this->variables = new Variables($this->thing, "variables bar " . $this->from);
        $this->current_time = $this->thing->time();

        $this->max_bar_count = 80;
        $this->response = "";


        $this->get();
        $this->readSubject();

//        if ($this->bar_count > 8) {$this->bar_count = 0;}

        $this->set();

        if ($this->agent_input == null) {
            $this->respond();
        }

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format( $this->thing->elapsed_runtime() - $this->start_time ) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;
    }

    public function set()
    {
        $this->variables->setVariable("count", $this->bar_count);
        $this->variables->setVariable("refreshed_at", $this->current_time);

        return;
    }


    public function get()
    {
        $this->bar_count = $this->variables->getVariable("count");
        $this->refreshed_at = $this->variables->getVariable("refreshed_at");

        $this->thing->log($this->agent_prefix .  'loaded ' . $this->bar_count . ".");

        return;
    }



    public function countBar()
    {
        // devstack count snowflakes on stack identity
        // This is a count of all snow everywhere.
        $this->bar_count += 1;
    }



    function respond()
    {
        $this->makeSMS();
        $this->makePNG();
        $this->makeWeb();
    }

    function makeSMS()
    {
        $this->sms_message = "BAR";
        $this->sms_message .= " | " . $this->bar_count . " " . $this->response;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $web = '<a href="' . $link . '">';
//        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/flag.png">';
        $web .= $this->html_image;

        $web .= "</a>";
        $web .= "<br>";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        $web .= $this->sms_message;

        $this->thing_report['web'] = $web;
    }


    function readSubject() {
//        if ($this->agent_input == "display") {return;}
        $this->doBar();
    }

    function doBar($depth = null)
    {
        $this->countBar();

        if ($this->bar_count >= $this->max_bar_count) {
            $this->bar_count = 0;
            $this->response .= "Reset bar count. ";
        }

        $this->thing->log($this->agent_prefix . "called Tallycounter.");

        $thing = new Thing(null);
        $thing->Create(null,"tallycounter", 's/ tallycounter message');
        $tallycounter = new Tallycounter($thing, 'tallycounter message tally@stackr.ca');

        $this->response .= "Did a tally count. ";

//        $tallycounter = new Tallycounter($this->thing, 'tallycounter message tally@stackr.ca');

        if ($this->bar_count == 0) {

//            $stack_thing = new Stack($this->thing);

            $thing = new Thing(null);
            $thing->Create(null,"stack", 's/ stack count');
            $stackcount = new Stack($thing, 'stack count');

            $this->response .= "Did a stack count. ";

        }

        if ($this->bar_count == 2) {

//            $stack_thing = new Stack($this->thing);

            $thing = new Thing(null);
            $thing->Create(null,"latency", 's/ latency check');
            $stackcount = new Latency($thing, 'latency check');

            //$this->response .= "Did a stack count. ";

        }


//        echo $tallycounter->count;

    }


    public function makeImage()
    {
//var_dump ($this->state);
//exit();
        // here DB request or some processing
//        $codeText = "thing:".$this->state;

// Create a 55x30 image

        $x_width = 200;
        $y_width = 125;

        $this->image = imagecreatetruecolor(200, 125);
        //$red = imagecolorallocate($this->image, 255, 0, 0);
        //$green = imagecolorallocate($this->image, 0, 255, 0);
        //$grey = imagecolorallocate($this->image, 100, 100, 100);

        //$this->image = imagecreatetruecolor($canvas_size_x, $canvas_size_y);
        //$this->image = imagecreatetruecolor(164, 164);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);

        $this->red = imagecolorallocate($this->image, 231, 0, 0);

        $this->yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->green = imagecolorallocate($this->image, 0, 129, 31);

        $this->color_palette = array($this->red,
                                    $this->yellow,
                                    $this->green);


       imagefilledrectangle($this->image, 0, 0, 200, 125, $this->white);


//        if ((!isset($this->state)) or ($this->state == false)) {
//            $color = $this->grey;
//        } else {
//            if (isset($this->{$this->state})) {
//                $color = $this->{$this->state};
//            } elseif (isset($this->{'flag_' . $this->state})) {
//                $color = $this->{'flag_' . $this->state};
//            }
//        }


        // Bevel top of signal image

        $border = 10;

        $lines = array("e","g", "b", "d","f");
        $i = 0;
        foreach ($lines as $key=>$line) {
            $x1 = 0;
            $x2 = $x_width;
            $y1 = $i *15 + 25;
            $y2 = $y1;
            imageline($this->image, $x1+$border, $y1, $x2-$border, $y2, $this->black);
            $i = $i + 1;
        }

        imageline($this->image, 0+$border, 25, 0+$border, 4 * 15 + 25, $this->black);
        imageline($this->image, 200-$border, 25, 200-$border, 4 * 15 + 25, $this->black);

        //$points = array(0,0,6,0,0,6);
        //imagefilledpolygon($this->image, $points, 3, $this->white);

        //$points = array(60,0,60-6,0,60,6);
        //imagefilledpolygon($this->image, $points, 3, $this->white);


        $green_x = 30;
        $green_y = 50;

        $red_x = 30;
        $red_y = 100;

        $yellow_x = 30;
        $yellow_y = 75;

        $double_yellow_x = 30;
        $double_yellow_y = 25;

        $textcolor = $this->black;

        //imagestring($this->image, 2, 0+10, 110, $this->bar_count - 1, $this->black);
        //imagestring($this->image, 2, 200-10, 110, $this->bar_count, $this->black);

        $font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';

        $size = 10;
        $angle = 0;

        $count_notation = $this->bar_count + 1;

        if (($count_notation <> 1) or ($count_notation == $this->max_bar_count)) {
            imagettftext($this->image, $size, $angle, 0 + 10, 110, $this->black, $font, $count_notation);
        }        

        if (($count_notation + 1 <> 1) or ($count_notation + 1 == $this->max_bar_count + 1)) {
            imagettftext($this->image, $size, $angle, 200 - 10, 110, $this->black, $font, $count_notation + 1);
        }
//        imagettftext($this->image, $size, $angle, $width/2-$bb_width/2, $height/2+ $bb_height/2, $grey, $font, $text);
        //imagestring($this->image, 2, $image_width-75, 10, $this->place_code, $textcolor);

//        $this->image = $image;


//        $font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';


        return;

    }

    public function makePNG()
    {
        if (!isset($this->image)) {$this->makeImage();}
        $agent = new Png($this->thing, "png");

        //$this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;
    }
}

?>
