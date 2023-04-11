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


class _Mixed extends Agent
{
    public $var = 'hello';

public function isMixed($token) {

                    if (ctype_alnum($token) and !ctype_alpha($token) and !is_numeric($token)) {
                        return true;
                    }
return false;


}

    public function getNgrams($input, $n = 3, $delimiter = null) {
if ($delimiter == null) {$delimiter = "";}

if (!isset($this->ngrams)) {$this->ngrams = array();}
        $words = explode(' ', $input);
        $ngrams = array();

        foreach ($words as $key=>$value) {

            if ($key < count($words) - ($n - 1)) {
                $ngram = "";
                for ($i = 0; $i < $n; $i++) {
                    $ngram .= " " . $words[$key + $i]. $delimiter;
                }
                $ngrams[] = trim($this->trimAlpha($ngram));
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

    function extractMixeds($input = null)
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
        $this->mixeds = $codes;
        return $this->mixeds;
    }

    public function run()
    {
        $this->doMixed();
    }

    public function makeResponse()
    {
        // This is a short simple structured response.
        if (!isset($this->response)) {$this->response = "";}
        $this->response .= 'Asked about,"' . $this->subject . '"' . '. ';
    }



//public function make() {}

public function makeSMS() {

$this->thing_report['sms'] = "MIXED";

}



    public function doMixed($text = null)
    {
}

    public function get()
    {
        $time_string = $this->thing->Read(["mixed", "refreshed_at"]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(["mixed", "refreshed_at"], $time_string);
        }

    }


    public function set()
    {
        // Log which agent was requested ie Ebay.
        // And note the time.

/// ?
//$place_agent thing = new Place($this->thing, $ngram);


        $this->thing->log("Set mixed refreshed_at.");
    }

public function readSubject() {

if ($this->input == "mixed") {return;}

}

}
