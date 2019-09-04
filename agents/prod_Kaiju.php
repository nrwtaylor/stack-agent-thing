<?php
/**
 * Kaiju.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Kaiju extends Chart
{
    public $var = 'hello';

    /**
     *
     */
    public function init() {

if ((isset($this->test_flag)) and ($this->test_flag === true)) {$this->test();}
        $this->node_list = array("kaiju"=>array("kaiju"));

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->unit = "Health";

        $this->default_state = "X";

        $this->max_words = 25;

        $this->getNuuid();

        $this->height = 200;
        $this->width = 300;

        $this->series = array('kaiju');


        $this->character = new Character($this->thing, "character is Kaiju");

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->variable = new Variables($this->thing, "variables kaiju " . $this->from);
    }


    /**
     *
     */
    function run() {
        $this->getAddress($this->thing->from);
        $this->getKaiju();
$this->makePNG();
    }


    /**
     *
     * @param unknown $state (optional)
     * @return unknown
     */
    function isKaiju($state = null) {

        if ($state == null) {
            if (!isset($this->state)) {$this->state = "easy";}

            $state = $this->state;
        }

        if (($state == "easy") or ($state == "hard")
        ) {return false;}

        return true;
    }





    function drawGraph() {


        $this->chart_width = $this->width - 20;
        $this->chart_height = $this->height - 20;

        $num_points = count($this->points);
        $column_width = $this->width / $num_points;

        $series_1 = $this->points[0]['series_1'];
        $series_2 = $this->points[0]['series_2'];


        $refreshed_at = $this->points[0]['refreshed_at'];

        // Get min and max
        if (!isset($y_min)) { $y_min = $series_1 + $series_2; }
        if (!isset($y_max)) {$y_max = $series_1 + $series_2;}

        if (!isset($x_min)) { $x_min = $refreshed_at; }
        if (!isset($x_max)) { $x_max = $refreshed_at; }

        $i = 0;
        foreach ($this->points as $point) {

            $series_1 = $point['series_1'];
            $queue_time = $point['series_2'];
            $elapsed_time = $series_1 + $series_2;

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

            $series_1 = $point['series_1'];
            $series_2 = $point['series_2'];
            $elapsed_time = $series_1 + $series_2;
            $refreshed_at = $point['refreshed_at'];

            $y_spread = $y_max - $y_min;
            if ($y_spread == 0) {$y_spread = 100;$this->y_spread = $y_spread;}

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




/*
   function getData()
    {
        $split_time = $this->thing->elapsed_runtime();

        $this->identity = "null" . $this->mail_postfix;
        // We will probably want a getThings at some point.
        $this->thing->db->setFrom($this->identity);
        $thing_report = $this->thing->db->agentSearch("kaiju", 99);

        $things = $thing_report['things'];
var_dump($things);

        if ( $things == false  ) {return;}

        $this->points = array();
        foreach ($things as $thing) {

            $thing_subject= $thing['task'];

$kaiju_array = explode(" " , $thing_subject);
var_dump($kaiju_array);
$this->points[] = null;
   //         $this->points[] = array("refreshed_at"=>$created_at, "run_time"=>$run_time, "queue_time"=>$queue_time);
        }

        $this->thing->log('Agent "Latencygraph" getData ran for ' . number_format($this->thing->elapsed_runtime()-$split_time)."ms.", "OPTIMIZE");

    }

*/

    /**
     *
     * @param unknown $requested_state (optional)
     */
    function set($requested_state = null) {
        $this->thing->json->writeVariable( array("kaiju", "inject"), $this->inject );

        $this->refreshed_at = $this->current_time;

        $this->variable->setVariable("state", $this->state);
        $this->variable->setVariable("refreshed_at", $this->current_time);

        $this->thing->log($this->agent_prefix . 'set Kaiju to ' . $this->state, "INFORMATION");
    }


    /**
     *
     */
    function get() {
        $this->previous_state = $this->variable->getVariable("state");

        $this->refreshed_at = $this->variable->getVariable("refreshed_at");

        $this->thing->log($this->agent_prefix . 'got from db ' . $this->previous_state, "INFORMATION");

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isKaiju($this->previous_state)) {
            $this->state = $this->previous_state;
        } else {
            $this->state = $this->default_state;
        }

        if ($this->state == false) {
            $this->state = $this->default_state;
        }

        $this->thing->log($this->agent_prefix . 'got a ' . strtoupper($this->state) . ' FLAG.' , "INFORMATION");

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("kaiju", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("kaiju", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->inject = $this->thing->json->readVariable( array("kaiju", "inject") );
    }


    /**
     *
     */
    function getNuuid() {
        $agent = new Nuuid($this->thing, "nuuid");
        $this->nuuid_png = $agent->PNG_embed;
    }


    /**
     *
     */
    function getUuid() {
        $agent = new Uuid($this->thing, "uuid");
        $this->uuid_png = $agent->PNG_embed;
    }


    /**
     *
     * @param unknown $text (optional)
     */
    function getQuickresponse($text = null) {
        if ($text == null) {$text = $this->web_prefix;}
        $agent = new Qr($this->thing, $text);
        $this->quick_response_png = $agent->PNG_embed;
    }


    /**
     *
     * @param unknown $state
     */
    function setState($state) {
        $this->state = "easy";
        if ((strtolower($state) == "hard") or (strtolower($state) == "easy")) {
            $this->state = $state;
        }
    }


    /**
     *
     * @return unknown
     */
    function getState() {
        if (!isset($this->state)) {$this->state = "easy";}
        return $this->state;

    }


    /**
     *
     * @param unknown $bank (optional)
     */
    function setBank($bank = null) {
        //if (($bank == "easy") or ($bank == null)) {
        //    $this->bank = "easy-a03";
        //}

        //if ($bank == "hard") {
        //    $this->bank = "hard-a05";
        //}

        //if ($bank == "16ln") {
        $this->bank = "16ln-a00";
        //}

    }


    /**
     *
     * @param unknown $librex
     * @return unknown
     */
    public function getLibrex($librex) {
        // Look up the meaning in the dictionary.
        if (($librex == "") or ($librex == " ") or ($librex == null)) {return false;}

        switch ($librex) {
        case null:
            // Drop through
        case 'kaiju':
            $file = $this->resource_path .'kaiju/kaiju.txt';
            break;
        default:
            $file = $this->resource_path . 'kaiju/kaiju.txt';
        }
        $this->librex = file_get_contents($file);


    }


    /**
     *
     * @return unknown
     */
    function getKaiju() {
        if (!isset($this->kaiju_address)) {$this->getAddress($this->thing->from);}
        if (!isset($this->kaiju_address)) {return;}

        //var_dump($this->kaiju_address);
        //exit();

        $this->kaiju_thing = new Thing(null);
        $this->kaiju_thing->Create($this->kaiju_address, "null", "s/ kaiju thing");

        $block_things = array();
        // See if a stack record exists.
        $findagent_thing = new Findagent($this->kaiju_thing, 'thing');
        $this->max_index =0;

        $match = 0;

        $link_uuids = array();
        $kaiju_messages = array();

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {

            $this->thing->log($block_thing['task'] . " " . $block_thing['nom_to'] . " " . $block_thing['nom_from']);
            //echo $block_thing['task'] . " " . $block_thing['nom_to'] . " " . $block_thing['nom_from'] . "\n";
            if ($block_thing['nom_from'] != $this->kaiju_address) {continue;}

            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                $link_uuids[] = $block_thing['uuid'];
                //                $kaiju_messages[] = $block_thing['task'];
                $kaiju_messages[] = $block_thing;
                //var_dump($block_thing['task']);
                // if ($match == 2) {break;}
                // Get upto 10 matches
                if ($match == 99) {break;}


            }
        }

        $this->kaiju_things = array();
        foreach ($kaiju_messages as $key=>$thing) {
            $parsed_thing = $this->parseThing($thing['task']);
            if ($parsed_thing != null) {
                $parsed_thing['created_at'] = strtotime($thing['created_at']);
                $this->kaiju_things[] = $parsed_thing;


            $thing_subject= $thing['task'];

$kaiju_array = explode("|" , $thing_subject);
$data_array = explode(" " ,$kaiju_array[1]);
//$voltage = (float)str_replace("V","",$data_array[2]);

$voltage = $this->parseData($data_array[2]);
//$array = array();
//$array["refreshed_at"] = $parsed_thing['created_at'];
//$array["series_1"] = $voltage;
//$array["series_2"] = 0;
//var_dump($array);


//var_dump($data_array[2]);
$this->points[] = array("refreshed_at"=>$parsed_thing['created_at'], "series_1"=>$voltage["voltage"], "series_2"=>0);
//$this->points[] = array("refreshed_at"=>$parsed_thing['created_at'], $voltage, "series_2"=>0);

   //         $this->points[] = array("refreshed_at"=>$created_at, "run_time"=>$run_time, "queue_time"=>$queue_time);



            }

        }

        $this->kaiju_thing = $this->kaiju_things[0];
        return $this->kaiju_thing;
    }

function parseData($text) {

$map = array("V" => "voltage", "Pa"=>"pressure", "uT"=>"magnetic_field", "g"=>"acceleration",
"mm"=>"bilge");

foreach($map as $symbol=>$name) {

if (strpos($text, $symbol) !== false) {
$voltage = (float)str_replace($symbol,"",$text);
//echo $symbol . " " . $name ." " . $voltage.  "\n";

$a[$name] = $voltage;
return $a;

}
}

return null;

}

    /**
     *
     * @param unknown $searchfor (optional)
     */
    function getAddress($searchfor = null) {
        $librex = "kaiju.txt";
        $this->getLibrex($librex);
        $contents = $this->librex;


        $this->kaijus = array();
        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {

            $word = $this->parseKaiju($line);
            $this->kaijus[$word['owner']] = $word;
            // do something with $line
            $line = strtok( $separator );
        }
        $kaiju_list = array();
        foreach ($this->kaijus as $kaiju_name=>$arr) {

            if ($this->thing->from == $kaiju_name) {
                $kaiju_list[] = $arr['address'];
            }
        }


        //        if ($searchfor == null) {return null;}


        $this->kaiju_address = null;
        if (count($kaiju_list) == 1) {
            $this->kaiju_address = $kaiju_list[0];
        }
        //$this->getKaiju();
        //        $this->kaiju_thing = new Thing(null);

        //        $agent_sms = new Sms($thing,"sms");

        //        $agent_sms->sendSMS($kaiju_address, "thing"

    }


    //    function getKaiju()
    //    {

    //        $agent_sms = new Sms($this->thing,"sms");

    // $agent_sms->sendSMS("XXXXXXXXXX", "thing");

    //    }

    /**
     *
     * @param unknown $Wtest
     * @return unknown
     */
    private function parseKaiju($test) {

if (isset($this->test_string)) {$test = $this->test_string;}

        if (mb_substr($test, 0, 1) == "#") {$word = false; return $word;}

        $dict = explode("/", $test);

        if ( (!isset($dict[1])) or (!isset($dict[2])) ) {
        }

        foreach ($dict as $index=>$phrase) {
            if ($index == 0) {continue;}
            if ($phrase == "") {continue;}
            $english_phrases[] = $phrase;
        }
        $text =  $dict[0];

        $dict = explode(",", $text);
        $kaiju_owner = $dict[0];
        $kaiju_address = trim($dict[1]);

        $parsed_line = array("owner"=>$kaiju_owner, "address"=>$kaiju_address);
        return $parsed_line;
    }


    /**
     *
     * @param unknown $test
     * @return unknown
     */
    private function parseThing($test) {
        if (mb_substr($test, 0, 1) == "#") {$word = false; return $word;}

        $dict = explode(" ", $test);
        if ( (!isset($dict[1])) or (!isset($dict[2])) or (!isset($dict[3])) ) {
            return null;
        }

if (!isset($dict[4])) {return;}


        //foreach($dict as $index=>$phrase) {
        //    if ($index == 0) {continue;}
        //    if ($phrase == "") {continue;}
        //    $english_phrases[] = $phrase;
        //}
        $nuuid =  $dict[2];
        $kaiju_voltage =  $dict[3];
        $kaiju_temperature =  $dict[4];
        $pressure = $dict[5];
        $magnetic_field =  $dict[6];
        $vertical_acceleration =  $dict[7];
        $temperature_1 =  $dict[8];
        $temperature_2 =  $dict[9];
        $temperature_3 =  $dict[10];
        $bilge_level =  $dict[11];


        //        $dict = explode(",",$text);
        //        $kaiju_owner = $dict[0];
        //        $kaiju_address = trim($dict[1]);


        $parsed_line = array(
            "nuuid" =>  $dict[2],
            "kaiju_voltage" =>  $dict[3],
            "kaiju_temperature" =>  $dict[4],
            "pressure" =>  $dict[5],
            "magnetic_field" =>  $dict[6],
            "vertical_acceleration" =>  $dict[7],
            "temperature_1" =>  $dict[8],
            "temperature_2" =>  $dict[9],
            "temperature_3" =>  $dict[10],
            "bilge_level" =>  $dict[11]);

        return $parsed_line;
    }

function test() {

$this->test_string = "THING | b97f 0.00V 27.4C 100060Pa 46.22uT 0.00g 25.9C 26.6C 25.8C 516mm 1564091111";

}


    /**
     *
     * @return unknown
     */
    function getBank() {
        if (!isset($this->bank)) {
            $this->bank = "16ln-a00";
        }

        if (isset($this->inject) and ($this->inject != false)) {
            $arr = explode("-", $this->inject);
            $this->bank = $arr[0] . "-" . $arr[1];
        }
        return $this->bank;
    }

//    public function makeImage() {
//       $this->image = null;

//    }

    /**
     *
     */
    public function respond() {

        //$this->getAddress($this->thing->from);
        //$this->getKaiju();

        $this->getResponse();

        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "kaiju";


        $this->makeSMS();

        $this->makeMessage();
        // $this->makeTXT();
        $this->makeChoices();

        $this->thing_report["info"] = "This creates an exercise message.";
        $this->thing_report["help"] = 'Try CHARLEY. Or NONSENSE.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        $this->makeWeb();

        $this->makeTXT();
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create($this->agent_name, $this->node_list, "kaiju");
        $this->choices = $this->thing->choice->makeLinks('kaiju');

        $this->thing_report['choices'] = $this->choices;
    }


    /**
     *
     */
    function makeSMS() {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/kaiju.pdf';

        //        $sms = "KAIJU " . $this->inject . " | " . $link . " | " . $this->response;
	$text = "Was not found.";
	if (isset($this->kaiju_thing)) {$text = implode(" " ,$this->kaiju_thing);}
        $sms = "KAIJU THING | " . $text;


        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }


    /**
     *
     */
    function makeACP125G() {
        $this->acp125g = new ACP125G($this->thing, "acp125g");
        $this->acp125g->makeACP125G($this->message);
    }


    /**
     *
     */
    function getResponse() {
        if (isset($this->response)) {return;}
    }


    /**
     *
     */
    function makeMessage() {


	if (!isset($this->sms_message)) {$this->makeSMS();}
        $message = $this->sms_message . "<br>";
//        $uuid = $this->uuid;
//        $message .= "<p>" . $this->web_prefix . "thing/$uuid/kaiju\n \n\n<br> ";
        $this->thing_report['message'] = $message;
    }


    /**
     *
     */
    function getBar() {
        $this->bar = new Bar($this->thing, "display");
    }


    /**
     *
     */
    function setInject() {
    }

    /**
     *
     */
    function makeWeb() {

        $this->drawGraph();

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/kaiju';

        $this->node_list = array("asleep"=>array("awake", "moving"));
        // Make buttons
        //$this->thing->choice->Create($this->agent_name, $this->node_list, "rocky");
        //$choices = $this->thing->choice->makeLinks('rocky');

        if (!isset($this->html_image)) {$this->makePNG();}

        $web = "<b>Kaiju Agent</b>";
        $web .= "<p>";

        $web .= "<p>";


        //$web .= '<a href="' . $link . '">'. $this->html_image . "</a>";
        //$web .= "<br>";

        $web .= $this->sms_message;
        $web .= "\n";

        $web .= "<p>";


        $ago = $this->thing->human_time ( time() - strtotime( $this->thing->thing->created_at ) );

        $txt = '<a href="' . $link . ".txt" . '">';
        $txt .= 'TEXT';
        $txt .= "</a>";

        $web .= "Kaiju report here " . $txt .".";
        $web .= "<p>";

        $web .= '<a href="' . $link . '">';
        $web .= $this->image_embedded;
        $web .= "</a>";
        $web .= "<br>";

        $web .= "kaiju graph";

        $web .= "<br><br>";


        $web .= "Requested about ". $ago . " ago.";
//        $web .= "<p>";
//        $web .= "Inject " . $this->thing->nuuid . " generated at " . $this->thing->thing->created_at. "\n";

        $togo = $this->thing->human_time($this->time_remaining);
        $web .= "This link will expire in " . $togo. ".<br>";

        $web .= "<br>";

        $privacy = '<a href="' . $this->web_prefix . "privacy" . '">';
        $privacy .= $this->web_prefix . 'privacy';
        $privacy .= "</a>";

        $web .= "This Kaiju thing is hosted by the " . ucwords($this->word) . " service.  Read the privacy policy at " . $privacy .".";

//        $web .= "This Kaiju thing is hosted by the " . ucwords($this->word) . " service.  Read the privacy policy at " . $this->web_prefix . "privacy";
        $web .= "<br>";

        $this->thing_report['web'] = $web;


    }


    /**
     *
     */
    function makeTXT() {
        $txt = "Kaiju traffic.\n";
        $txt .= 'Duplicate messages may exist. Can you de-duplicate?';
        $txt .= "\n";

        if (!isset($this->sms_message)) {$this->makeSMS();}

        $txt .= $this->sms_message;

        $txt .= "\n\n";

        $txt .= "Full log follows.\n";

        if (isset($this->kaiju_things)) {

        foreach ($this->kaiju_things as $key=>$thing) {

            $flat_thing = implode($thing, " ");
//            if ($parsed_thing != null) {
//                $txt .= $parsed_thing['created_at'] . "\n";
                $txt .=  $flat_thing . "\n";
//            }

        }

        }



        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractNuuid($input) {
        if (!isset($this->duplicables)) {
            $this->duplicables = array();
        }

        return $this->duplicables;
    }


    /**
     *
     */
    public function makeKaiju() {
        //        $this->makePDF();
        //        $this->thing_report['percs'] = $this->thing_report['pdf'];
    }



    /**
     *
     */
    public function readSubject() {



//        if (!$this->getMember()) {$this->response = "Generated an inject.";}

        $input = strtolower($this->subject);
if ((isset($this->test_flag)) and ($this->test_flag === true)) {$input = $this->test_string;}

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            if ($input == 'kaiju') {

  //              $this->getMessage();

//                if ((!isset($this->index)) or
//                    ($this->index == null)) {
//                    $this->index = 1;
//                }
                return;
            }
        }

        $keywords = array("test","kaiju", "hard", "easy", "hey", "on", "off");
        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {

                    case 'hard':
                    case 'easy':
                        $this->setState($piece);
                        $this->setBank($piece);

//                        $this->getMessage();
                        $this->response .= " Set messages to " . strtoupper($this->state) .".";

                        return;

                    case 'hey':

                        return;

                    case 'test':
$this->test_flag = true;
$this->test();
$l = $this->parseThing($this->test_string);
var_dump($l);
                        return;

                    case 'on':
                    default:
                    }
                }
            }
        }

//        $this->getMessage();

        if ((!isset($this->index)) or
            ($this->index == null)) {
            $this->index = 1;
        }
    }


}
