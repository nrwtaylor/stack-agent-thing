<?php
namespace Nrwtaylor\StackAgentThing;

class Claws extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doClaws();
    }

    public function doClaws()
    {
        if ($this->agent_input == null) {
            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            $k = array_rand($array);
            $v = $array[$k];

            $response = "CLAWS | " . strtolower($v) . ".";

            $this->claws_message = $response; // mewsage?
        } else {
            $this->claws_message = $this->agent_input;
        }
    }

    function getNegativetime()
    {
        $agent = new Negativetime($this->thing, "claws");
        $this->negative_time = $agent->negative_time; //negative time is asking
    }

    // -----------------------

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a claws keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = array("claws" => array("claws", "dog"));
        $this->sms_message = "" . $this->claws_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "claws");
        $choices = $this->thing->choice->makeLinks('claws');
        $this->thing_report['choices'] = $choices;
    }

    public function filenameClaws($text = null) {
       if ($text == null) {return true;}
       $filename = trim($text, '"');
     //  $this->filename = $filename;
       if ((is_string($filename)) and (file_exists($filename))) {
        $text = file_get_contents($filename);
       }
       return $text;
    }

    public function readClaws($text = null)
    {
        var_dump("Claws readClaws");
        var_dump($text);
    }

    public function whenClaws() {

if ($this->claws_when_flag != "on") {return;}

// Code to write When calendar line item goes here.

// Build entry for when calendar
$line = "test item";


        $this->writeWhen($line);
        $this->response .= "Wrote item to When calendar file. ";

    }

    public function readSubject()
    {

        $input = $this->input;

// Note for dev.
// Try this as $this->assert($input, false).

//        $filtered_input = $this->assert($input);
//        $this->filenameClaws($filtered_input);

        // Recognize if the instruction has "when" in it.
        // Set a flag so that we can later create a calendar item if needed.
        $indicators = [
            'when'=>['when']
        ];
        $this->flagAgent($indicators, $input);

        $string = $input;
        $str_pattern = 'claws';
        $str_replacement = '';
        $filtered_input = $input;
        if (strpos($string, $str_pattern) !== false) {
            $occurrence = strpos($string, $str_pattern);
            $filtered_input = substr_replace(
                $string,
                $str_replacement,
                strpos($string, $str_pattern),
                strlen($str_pattern)
            );
        }

// See note above to re-factor above.

        $tokens=explode(" ",$filtered_input);
        foreach($tokens as $i=>$token) {
          $filename=trim($token);

// Delegating contents to agents for processing
          $contents = $this->filenameClaws($filename);

// Pass contents through MH routine to remove trailing =

          $mh_agent = new MH($this->thing, "mh");
          $contents = $mh_agent->textMH($contents);

//$meta = $mh_agent->metaMH($contents);
$meta = $this->metaMH($contents);
var_dump("Claws metaMH response");
var_dump($meta);

$call = $this->readCall($contents);
var_dump("Claws readCall response");
var_dump($call);
// Test output.

// dev Handle non-snake case agent names (all caps).
//$contents = $this->textMH($contents);


          $url_agent = new Url($this->thing, "url");
          $t = $url_agent->extractUrls($contents);

// Try replacing with this.
// $t = $this->extractUrls($contents);
var_dump("Claws extractUrls response");
var_dump($t);
        }

// get an MH reader to clean up the format
// See what we get from Call.
//$call_agent = new Call($this->thing, "call");

// desired actions - priority and focuses
// 1. insert with conference link into when calendar
// 2. take conference link to forward it in an email (?)
// 3. clickable action to connect to conference link (?)
// 4. include subject of original email

        return false;
    }
}
