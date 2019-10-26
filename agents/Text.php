<?php
namespace Nrwtaylor\StackAgentThing;

// Display all errors in production.
// The site must run clean transparent code.
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//ini_set("allow_url_fopen", 1);

// This is written to be understandable.
// Apologies.


class Text extends Agent
{
    public $var = 'hello';

    private function getNgrams($input, $n = 3) {
        $words = explode(' ', $input);
        $ngrams = array();

        foreach ($words as $key=>$value) {

            if ($key < count($words) - ($n - 1)) {
                $ngram = "";
                for ($i = 0; $i < $n; $i++) {
                    $ngram .= " " . $words[$key + $i];
                }
                $ngrams[] = trim($ngram);
            }
        }
        return $ngrams;
    }


    public function init()
    {

        $this->node_list = array("start" => array("helpful", "useful"));

        $this->thing_report['info'] = 'Text did not add anything useful.';
        $this->thing_report['help'] =
            "An agent which provides search insight. Click on a button.";

        $this->thing->log("Initialized Text.", "DEBUG");

    }

    function extractCodes($input = null)
    {
        if (is_array($input)) {
            return true;
        }
        $tokens = explode(
            ' ',
            str_replace(
                array(',', '*', '(', ')', '[', ']', '!', '&', 'and', '.', '-'),
                ' ',
                $input
            )
        );

        $codes = array();

        //     if (!isset($words) or count($words) == 0) {return $ngrams;}

        // Rare for a model to not have a number.
        // And if it doesn't it should be picked up as an ngram.

        foreach ($tokens as $key => $token) {
            //if(1 === preg_match('~[A-Z][0-9]~', strtolower($value))){
            //    $codes[] = $value;
            //}

            if (
                preg_match('/[A-Za-z]/', $token) &&
                preg_match('/[0-9]/', $token)
            ) {
                $codes[] = $token;
            }
        }
        $this->codes = $codes;
        return $this->codes;
    }


    function extractNumbers($input = null)
    {
// Numbers as text.
// Vs agent number.

        if (is_array($input)) {
            return true;
        }
        $tokens = explode(
            ' ',
            str_replace(
                array(',', '*', '(', ')', '[', ']', '!', '&', 'and', '.', '-'),
                ' ',
                $input
            )
        );

        $numbers = array();

        //     if (!isset($words) or count($words) == 0) {return $ngrams;}

        // Rare for a model to not have a number.
        // And if it doesn't it should be picked up as an ngram.

        foreach ($tokens as $key => $token) {
            //if(1 === preg_match('~[A-Z][0-9]~', strtolower($value))){
            //    $codes[] = $value;
            //}

//            if (
//                preg_match('/[A-Za-z]/', $token) &&
//                preg_match('/[0-9]/', $token)
//            ) {

            if (
                preg_match('/[0-9]/', $token)
            ) {


                $numbers[] = $token;
            }
        }
        $this->numbers = $numbers;
        return $this->numbers;
    }

    function extractHyphenates($input = null)
    {
        if (is_array($input)) {
            return true;
        }
        $tokens = explode(
            ' ',
            str_replace(
                array(',', '*', '(', ')', '[', ']', '!', '&', 'and', '.'),
                ' ',
                $input
            )
        );
        $hyphens = array();

        //     if (!isset($words) or count($words) == 0) {return $ngrams;}

        // Rare for a model to not have a number.
        // And if it doesn't it should be picked up as an ngram.

        foreach ($tokens as $key => $token) {
            //if(1 === preg_match('~[A-Z][0-9]~', strtolower($value))){
            //    $codes[] = $value;
            //}

//            if (
//                preg_match('/[A-Za-z]/', $token) &&
//                preg_match('/[0-9]/', $token)
//            ) {

            if (
                preg_match('/[A-Za-z]/', $token) &&
                preg_match('/[0-9]/', $token)
            ) {


                $hyphens[] = $token;
            }
        }
        $this->hyphenates = $hyphens;
        return $this->hyphenates;
    }


    public function run()
    {
        $this->doText();
    }

    public function makeResponse()
    {
        // This is a short simple structured response.
        if (!isset($this->response)) {$this->response = "";}
        $this->response .= 'Asked about,"' . $this->subject . '"' . '. ';
    }


public function textN3($input) {

$p_array = explode(" ",$input);
$text = "";
foreach($p_array as $i=>$word) {
if ($i >= 3) {break;}
$text .= $word ." ";

}
$text = trim($text);
return $text;
}

public function textNouns($input) {

global $wp;
if (!isset($wp->brilltagger_agent)) {
$wp->brilltagger_agent = new Brilltagger($this->thing, $input);
}
//$word_agent = new Word($this->thing, "word");
$tags = $wp->brilltagger_agent->tags;
$text = "";
foreach ($tags as $index=>$tag) {


if (is_numeric($tag["token"])) {continue;}

if(1 === preg_match('~[0-9]~', $tag["token"])){
continue;
}
$token = $tag["token"];


// False. Is not a word.
//$nearest_word = $word_agent->isWord($token);

//echo $token ." " . $nearest_word . "<br>";
//if ($nearest_word == false) {continue;}

 if (strpos($tag["tag"], 'VB') !== false) {
        $text .= $tag["token"]. " ";
continue;
    }



 if (strpos($tag["tag"], 'JJ') !== false) {
        $text .= $tag["token"]. " ";
continue;
    }




    if (strpos($tag["tag"], 'NN') !== false) {
        $text .= $tag["token"]. " ";
continue;
    }


}

$text = trim($text);
$this->thing->log('text adjectives and nouns built query, "'. $text .'".');
return $text;

}

function textOr($input) {

$text = "(" . trim($input). ")";
$text = str_replace(" ", ",", $text);

$text = trim($text);
// Any words
return $text;

}

function textNgram($input, $t = "@1") {

$text = "(" . trim($input). ")";
$text = str_replace(" ", ",", $text);

$text = $t . " ". trim($text);
// Any words
return $text;

}



public function make() {}

    public function doText($text = null)
    {
}


    public function set()
    {
        // Log which agent was requested ie Ebay.
        // And note the time.

        $time_string = $this->thing->json->time();
        $this->thing->json->writeVariable(
            array("text", "refreshed_at"),
            $time_string
        );


/// ?
//$place_agent thing = new Place($this->thing, $ngram);


        $this->thing->log("Set text refreshed_at.");
    }

public function readSubject() {

if ($this->input == "text") {return;}

}

}
