<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Flag
{

    public $var = 'hello';

    function __construct(Thing $thing, $agent_input = null)
    {
        $this->start_time = $thing->elapsed_runtime();

        //if ($agent_input == null) {$agent_input = "";}

        $this->agent_input = $agent_input;
        $this->agent_name = "flag";
        $this->keyword = "flag";
        $this->agent_prefix = 'Agent "' . ucwords($this->keyword) . '" ';

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing->thing;
        $this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid . ".", "INFORMATION");

        // $this->start_time = $this->thing->elapsed_runtime();

        $this->test= "Development code"; // Always

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        $this->sqlresponse = null;
        $this->thing->log($this->agent_prefix . 'received this Thing, "' . $this->subject .  '".', "DEBUG") ;


        // Set up default flag settings
        $this->verbosity = 1;
        $this->requested_state = null;
        $this->default_state = "green";
        $this->node_list = array("green"=>array("red"=>array("green")));

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/flag';


        $this->refreshed_at = null;

        $this->current_time = $this->thing->time();

        // Get the current Identities flag
        $this->flag = new Variables($this->thing, "variables flag " . $this->from);
        //$this->nuuid = substr($this->variables_thing->variables_thing->uuid,0,4); 

        $this->thing->log($this->agent_prefix . ' got flag variables. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE") ;


        // At this point the flag object
        // has the current flag variables loaded.

		$this->readSubject();
        $this->thing->log($this->agent_prefix . ' completed read. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE") ;

        if ($this->agent_input == null) {$this->Respond();}
        $this->thing->log($this->agent_prefix . ' set response. Timestamp ' . number_format($this->thing->elapsed_runtime()) .  'ms.', "OPTIMIZE") ;


        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.', "OPTIMIZE" );

        $this->thing_report['log'] = $this->thing->log;
        if(isset($this->response)) {$this->thing_report['response'] = $this->response;}

		return;

		}


    function set($requested_state = null)
    {
 
        if ($requested_state == null) {
            if (!isset($this->requested_state)) {
                // Set default behaviour.
                // $this->requested_state = "green";
                // $this->requested_state = "red";
                $this->requested_state = "green"; // If not sure, show green.
            }
            $requested_state = $this->requested_state;
        }

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        $this->flag->setVariable("state", $this->state);

        //$this->nuuid = substr($this->variables_thing->variables_thing->uuid,0,4); 
        //$this->variables_thing->setVariable("flag_id", $this->nuuid);

        $this->flag->setVariable("refreshed_at", $this->current_time);

        //$this->makeChoices();
        //$this->makePNG();

        $this->thing->log($this->agent_prefix . 'set Flag to ' . $this->state, "INFORMATION");


        return;
    }

    function isFlag($flag = null)
    {
        // Validates whether the Flag is green or red.
        // Nothing else is allowed.

        if ($flag == null) {
            if (!isset($this->state)) {$this->state = "red";}

            $flag = $this->state;
        }

        if (($flag == "red") or 
                ($flag == "green") or 
                ($flag == "rainbow") or
                ($flag == "yellow") or 
                ($flag == "blue") or 
                ($flag == "indigo") or 
                ($flag == "violet") or 
                ($flag == "orange") or
                ($flag == "grey")

            ) {return false;}

        return true;
    }

    function get()
    {
        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->previous_state = $this->flag->getVariable("state");
        $this->refreshed_at = $this->flag->getVariable("refreshed_at");

        $this->thing->log($this->agent_prefix . 'got from db ' . $this->previous_state, "INFORMATION");


        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isFlag($this->previous_state)) {
            $this->state = $this->previous_state;
        } else {
            $this->state = $this->default_state;
        }

//        $this->thing->choice->Create($this->keyword, $this->node_list, $this->state);
//        $check = $this->thing->choice->current_node;

        $this->thing->log($this->agent_prefix . 'got a ' . strtoupper($this->state) . ' FLAG.' , "INFORMATION");

        return;

    }


    function read()
    {
        //$this->thing->log("read");

        $this->get();
        return $this->state;
    }



    function selectChoice($choice = null)
    {

        if ($choice == null) {
            if (!isset($this->state)) {
                $this->state = $this->default_state;
            }
            $choice = $this->state;
        }

        if (!isset($this->state)) {
            $this->state = "X";
        }
        $this->previous_state = $this->state;
        $this->state = $choice;

        //$this->thing->choice->Choose($this->state);
        //$this->thing->choice->save($this->keyword, $this->state);


        $this->thing->log('Agent "' . ucwords($this->keyword) . '" chose "' . $this->state . '".', "INFORMATION");

        return $this->state;
    }

    function makeChoices () {

//        $this->thing->choice->Choose($this->state);
//        $this->thing->choice->save($this->keyword, $this->state);

        $this->thing->choice->Create($this->keyword, $this->node_list, $this->state);

        $choices = $this->flag->thing->choice->makeLinks($this->state);
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }

    function makeWeb() {

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

	private function Respond()
    {

        // At this point state is set
        $this->set($this->state);

		// Thing actions

		$this->thing->flagGreen();

		// Generate email response.

		$to = $this->thing->from;
		$from = $this->keyword;

        if ($this->state == "inside nest") {
            $t = "NOT SET";
        } else {
            $t = $this->state;
        }

        $this->makeSMS();
        $this->makeMessage();

		$this->thing_report['email'] = $this->message;

        $this->makePNG();
//        $this->makeChoices(); // Turn off because it is too slow.

        $this->makeTXT();

        $this->makeChoices();
        $this->makeWeb();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        //$this->thing_report['help'] = 'This Flag is either RED or GREEN. RED means busy.';
        $this->makeHelp();

		return;
	}

    function makeHelp()
    {
        if ($this->state == "green") {
            $this->thing_report['help'] = 'This Flag is either RED or GREEN. GREEN means available.';
        }

        if ($this->state == "red") {
            $this->thing_report['help'] = 'This Flag is either RED or GREEN. RED means busy.';
        }
    }

    function makeTXT()
    {
        $txt = 'This is FLAG POLE ' . $this->flag->nuuid . '. ';
        $txt .= 'There is a '. strtoupper($this->state) . " FLAG. ";
        if ($this->verbosity >= 5) {
            $txt .= 'It was last refreshed at ' . $this->current_time . ' (UTC).';
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    function makeSMS()
    {

        $sms_message = "FLAG IS " . strtoupper($this->state);

        if ($this->verbosity > 6) {
            $sms_message .= " | previous state " . strtoupper($this->previous_state);
            $sms_message .= " state " . strtoupper($this->state);
            $sms_message .= " requested state " . strtoupper($this->requested_state);
            $sms_message .= " current node " . strtoupper($this->base_thing->choice->current_node);
        }
        if ($this->verbosity > 2) {
            $sms_message .= " | nuuid " . strtoupper($this->thing->nuuid);
        }
        if ($this->verbosity >= 9) {
            $sms_message .= " | base nuuid " . strtoupper($this->flag->thing->nuuid);
        }

        if ($this->verbosity > 0) {
            $sms_message .= " | nuuid " . $this->flag->nuuid; 
        }

        if ($this->verbosity > 2) {
            if ($this->state == "red") {
                $sms_message .= " | MESSAGE Green";
            }


            if ($this->state == "green") {
                $sms_message .= ' | MESSAGE Red';
            }
        }
        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;

    }


    function makeMessage()
    {

        $message = 'This is a FLAG POLE.  The flag is a ' . trim(strtoupper($this->state)) . " FLAG. ";

        if ($this->state == 'red') {
            $message .= 'It is a BAD time at the moment. ';
        }

        if ($this->state == 'green') {
            $message .= 'It is a GOOD time now. ';
        }

        //$test_message .= 'And the flag is ' . strtoupper($this->state) . ".";

        $this->message = $message;
        $this->thing_report['message'] = $message; // NRWTaylor. Slack won't take hmtl raw. $test_message;


    }

    public function makeImage()
    {
//var_dump ($this->state);
//exit();
        // here DB request or some processing
//        $codeText = "thing:".$this->state;

// Create a 55x30 image

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

        // For Vancouver Pride 2018

        // https://en.wikipedia.org/wiki/Rainbow_flag
        // https://en.wikipedia.org/wiki/Rainbow_flag_(LGBT_movement)
        // https://www.schemecolor.com/lgbt-flag-colors.php

        // https://www.bustle.com/p/9-pride-flags-whose-symbolism-everyone-should-know-9276529

        $this->blue = imagecolorallocate($this->image, 0, 68, 255);

        $this->pride_red = imagecolorallocate($this->image, 231, 0, 0);
        $this->pride_orange = imagecolorallocate($this->image, 255, 140, 0);
        $this->pride_yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->pride_green = imagecolorallocate($this->image, 0, 129, 31);
        $this->pride_blue = imagecolorallocate($this->image, 0, 68, 255);
        $this->pride_violet = imagecolorallocate($this->image, 118, 0, 137);

        $this->flag_red = imagecolorallocate($this->image, 231, 0, 0);
        $this->flag_orange = imagecolorallocate($this->image, 255, 140, 0);
        $this->flag_yellow = imagecolorallocate($this->image, 255, 239, 0);
        $this->flag_green = imagecolorallocate($this->image, 0, 129, 31);
        $this->flag_blue = imagecolorallocate($this->image, 0, 68, 255);
        // Indigo https://www.rapidtables.com/web/color/purple-color.html
        $this->flag_indigo = imagecolorallocate($this->image, 75, 0, 130);
        $this->flag_violet = imagecolorallocate($this->image, 118, 0, 137);



        $this->indigo = imagecolorallocate($this->image, 75, 0, 130);


        $this->color_palette = array($this->pride_red,
                                    $this->pride_orange,
                                    $this->pride_yellow,
                                    $this->pride_green,
                                    $this->pride_blue,
                                    $this->pride_violet);

        // Draw a white rectangle
        if ((!isset($this->state)) or ($this->state == false)) {
            $color = $this->grey;
        } else {
            if (isset($this->{$this->state})) {
                $color = $this->{$this->state};
            } elseif (isset($this->{'flag_' . $this->state})) {
                $color = $this->{'flag_' . $this->state};
            }
        }

//imagefilledrectangle($image, 0, 0, 200, 125, ${$this->state});
        if ($this->state == "rainbow") {
//    $color = $this->grey;
            foreach(range(0,5) as $n) {
                $a = $n * (200/6);
                $b = $n *(200/6) + (200/6);
                $color = $this->color_palette[$n];

//                imagefilledrectangle($this->image, $a, 0, $b, 125, $color);
                $a = $n * (125/6);
                $b = $n *(125/6) + (200/6);

                imagefilledrectangle($this->image, 0, $a, 200, $b, $color);

            }
        } else {
            if (!isset($color)) {$color = $this->grey;}
            imagefilledrectangle($this->image, 0, 0, 200, 125, $color);
        }

        $light_text_list = array("red","rainbow","indigo","violet", "blue");
        if (in_array($this->state, $light_text_list)) {
            $textcolor = imagecolorallocate($this->image, 255, 255, 255);
        } else {
            $textcolor = imagecolorallocate($this->image, 0, 0, 0);
        }

        // Write the string at the top left
        imagestring($this->image, 2, 150, 100, $this->flag->nuuid, $textcolor);
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

    public function readSubject() 
    {
        $this->response = null;

        $keywords = array('flag', 'red', 'green', 'rainbow','blue','indigo', 'orange', 'yellow', 'violet',
                        'gray',
                        'grey',
                        'gris',
                        'cinzento');

        if (isset($this->agent_input)) {
            $input = $this->agent_input;
        } else {
            $input = strtolower($this->subject);
        }
		//$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;
        //$haystack = $this->agent_input . " " . $this->from;
        //$haystack = $input . " " . $this->from;
        $haystack = "";

//		$this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

		// So this is really the 'sms' section
		// Keyword
        if (count($pieces) == 1) {

            if ($input == $this->keyword) {
                $this->get();
                $this->response = "Got the flag.";
                return;
            }
        }


        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) 
                    {

                        case 'red':
                            $this->thing->log($this->agent_prefix . 'received request for RED FLAG.', "INFORMATION");
                            $this->selectChoice('red');
                            $this->response = "Selected a red flag.";
                            return;
                        case 'green':
                            $this->selectChoice('green');
                            $this->response = "Selected a green flag.";
                            return;
                        case 'rainbow':
                        case 'blue':
                        case 'indigo':
                        case 'orange':
                        case 'yellow':
                        case 'violet':

                            $this->selectChoice($piece);
                            $this->response = "Selected a Flag.";
                            return;

                        case 'gray':
                        case 'grey':
                        case 'gris':
                        case 'cinzento':

                            $this->selectChoice('grey');
                            $this->response = "Selected a grey flag.";
                            return;


                        case 'next':

                        default:

                    }
                }
            }
        }

        // If all else fails try the discriminator.
//        if (!isset($haystack)) {$this->response = "Did nothing."; return;} 
        $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.
        switch($this->requested_state)
        {
            case 'green':
                $this->selectChoice('green');
                $this->response = "Asserted a Green Flag.";
                return;
            case 'red':
                $this->selectChoice('red');
                $this->response = "Asserted a Red Flag.";
                return;
        }

        $this->read();
        $this->response = "Looked at the Flag.";

        // devstack
        return "Message not understood";
		return false;
	}

/*
	function kill()
    {
		// No messing about.
		return $this->thing->Forget();
	}
*/
    function discriminateInput($input, $discriminators = null)
    {
        //$input = "optout opt-out opt-out";

        if ($discriminators == null) {
            $discriminators = array('red', 'green');
        }



        $default_discriminator_thresholds = array(2=>0.3, 3=>0.3, 4=>0.3);

        if (count($discriminators) > 4) {
            $minimum_discrimination = $default_discriminator_thresholds[4];
        } else {
            $minimum_discrimination = $default_discriminator_thresholds[count($discriminators)];
        }

        $aliases = array();

        $aliases['red'] = array('r', 'red','on');
        $aliases['green'] = array('g','grn','gren','green', 'gem', 'off');
        //$aliases['reset'] = array('rst','reset','rest');
        //$aliases['lap'] = array('lap','laps','lp');

        $words = explode(" ", $input);

        $count = array();

        $total_count = 0;
        // Set counts to 1.  Bayes thing...
        foreach ($discriminators as $discriminator) {
            $count[$discriminator] = 1;
            $total_count = $total_count + 1;
        }
        // ...and the total count.

        foreach ($words as $word) {
            foreach ($discriminators as $discriminator) {
                if ($word == $discriminator) {
                    $count[$discriminator] = $count[$discriminator] + 1;
                    $total_count = $total_count + 1;
                    //echo "sum";
                }
                foreach ($aliases[$discriminator] as $alias) {
                    if ($word == $alias) {
                        $count[$discriminator] = $count[$discriminator] + 1;
                        $total_count = $total_count + 1;
                        //echo "sum";
                    }
                }
            }
        }

        $this->thing->log('Agent "Flag" matched ' . $total_count . ' discriminators.',"DEBUG");
        // Set total sum of all values to 1.

        $normalized = array();
        foreach ($discriminators as $discriminator) {
            $normalized[$discriminator] = $count[$discriminator] / $total_count;            
        }

        // Is there good discrimination
        arsort($normalized);

        // Now see what the delta is between position 0 and 1

        foreach ($normalized as $key=>$value) {
            //echo $key, $value;

            if ( isset($max) ) {$delta = $max-$value; break;}
            if ( !isset($max) ) {$max = $value;$selected_discriminator = $key; }
        }


        //echo '<pre> Agent "Usermanager" normalized discrimators "';print_r($normalized);echo'"</pre>';


        if ($delta >= $minimum_discrimination) {
            //echo "discriminator" . $discriminator;
            return $selected_discriminator;
        } else {
            return false; // No discriminator found.
        }

    return true;
    }
}
