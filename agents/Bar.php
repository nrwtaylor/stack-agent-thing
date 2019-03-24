<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Bar extends Agent
{
    public function init()
    {
        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log($this->agent_prefix . "received this Thing ".  $this->subject . '".');

        $this->state = "red"; // running

        $this->variables = new Variables($this->thing, "variables bar " . $this->from);
        $this->current_time = $this->thing->time();

        $this->max_bar_count = 80;
        $this->response = "";
    }

    public function set()
    {
        $this->variables->setVariable("count", $this->bar_count);
        $this->variables->setVariable("refreshed_at", $this->current_time);
    }

    public function get()
    {
        $this->bar_count = $this->variables->getVariable("count");
        $this->refreshed_at = $this->variables->getVariable("refreshed_at");

        $this->thing->log($this->agent_prefix .  'loaded ' . $this->bar_count . ".");
    }

    public function countBar()
    {
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
        $this->sms_message .= " | " . $this->bar_count . " of " . $this->max_bar_count . ". " . $this->response;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $web = '<a href="' . $link . '">';
        // $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/flag.png">';
        $web .= $this->html_image;

        $web .= "</a>";
        $web .= "<br>";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        $web .= $this->sms_message;

        $this->thing_report['web'] = $web;
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);
        $pieces = explode(" ", strtolower($input));
        $keywords = array("bar", "advance");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    $this->read($piece);
                }
            }
        }
    }

    public function read($piece)
    {
        switch($piece) {
           case 'stack':    
            $from = "null@stackr.ca";    
            $this->variables = new Variables($this->thing, "variables bar " . $this->from);
            $this->get();
            return;

           case 'advance':
               $this->doBar();
               return;
           case 'on':
           default:
        }
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

        if (($this->bar_count % 2) == 0) {

            $thing = new Thing(null);
            $thing->Create(null,"latency", 's/ latency check');
            $stackcount = new Latency($thing, 'latency check');

            $this->response .= "Checked stack latency. ";

        }

        if (($this->bar_count % 7) == 0) {

            $arr = json_encode(array("to"=>"null@stackr.ca", "from"=>"damage", "subject"=>"s/ damage 10000"));

            $client= new \GearmanClient();
            $client->addServer();
                //$client->doNormal("call_agent", $arr);
            $client->doLowBackground("call_agent", $arr);


            //$thing = new Thing(null);
            //$thing->Create(null,"damage", 's/ damage 10000');
            //$damage= new Damage($thing, 'damage 10000);

            $this->response .= "Damage. ";

        }


    }


    public function makeImage()
    {
        // Create a x_width x y_width image
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

        $border = 25;

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
            imagettftext($this->image, $size, $angle, 200 - 25, 110, $this->black, $font, $count_notation + 1);
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
