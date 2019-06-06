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

class Kaiju extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    public function init() {
        $this->node_list = array("kaiju"=>array("kaiju"));

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->unit = "Health";

        $this->default_state = "X";

        $this->max_words = 25;

        $this->getNuuid();

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
                $parsed_thing['created_at'] = $thing['created_at'];
                $this->kaiju_things[] = $parsed_thing;
            }

        }

        $this->kaiju_thing = $this->kaiju_things[0];
        return $this->kaiju_thing;
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
     * @param unknown $test
     * @return unknown
     */
    private function parseKaiju($test) {
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

        //foreach($dict as $index=>$phrase) {
        //    if ($index == 0) {continue;}
        //    if ($phrase == "") {continue;}
        //    $english_phrases[] = $phrase;
        //}
        $nuuid =  $dict[2];
        $kaiju_voltage =  $dict[3];
        $kaiju_temperature =  $dict[4];
        $pressure =  $dict[5];
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

    public function makeImage() {
       $this->image = null;

    }

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

        $keywords = array("kaiju", "hard", "easy", "hey", "on", "off");
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
