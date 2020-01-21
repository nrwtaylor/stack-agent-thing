<?php
/**
 * Portmanteau.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Capitalise extends Agent
{
    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
$this->capitalisations = array();
$this->capitalisation = null;

global $wp;
if (!isset($wp->slug_agent)) {
    $wp->slug_agent = new Slug($this->thing,"slug");
}


$lines = array("The quick brown fox was not Capitalized.", "The Return of the Jedi was.", "The Jedi attack.","The Attack of the Clones.");
$this->loadCapitalisations($lines);
$this->getCapitalisation("Return the jeDi to me.");
    }

    function addCapitalisation($capitalisation) {
global $wp;
$slug = $wp->slug_agent->getSlug($capitalisation);

if (!isset($this->capitalisations)) {$this->capitalisations = array();}

$count = 0;
if (isset($this->capitalisations[$slug][$capitalisation]['count'])) {
$count = $this->capitalisations[$slug][$capitalisation]['count'];
}

$arr = array("count"=>$count += 1);


$this->capitalisations[$slug][$capitalisation] = $arr;


   }

function preferredCapitalisation($text) {

global $wp;
$slug = $wp->slug_agent->getSlug($text);


if (!isset($this->capitalisations[$slug])) {

$this->addCapitalisation($text);


}

$capitalisations = $this->capitalisations[$slug];
$max_count = 0;
foreach($capitalisations as $i=>$capitalisation) {
if ($capitalisation['count'] > $max_count) {

$preferred_capitalisation = $i;

}

}

return $preferred_capitalisation;

}

function addCapitalisations($capitalisations) {

foreach($capitalisations as $i=>$capitalisation) {

$this->addCapitalisation($capitalisation);

}


}

    function loadCapitalisations($lines = null) {

    // Read all the 1-gram to 3-gram combinations.
    // And see how they are capitalised in the set.

    if ( (!is_array($lines)) and (is_string($lines)) ) {$lines = array($lines);}

//    $token_agent= new Token($this->thing, "token");
    $ngram_agent= new Ngram($this->thing,"ngram");
   // $slug_agent= new Slug($this->thing,"slug");

    foreach($lines as $i=>$line) {

//    $token_agent->extractTokens($line);
    $n = $ngram_agent->getNgrams($line, 3);
$this->addCapitalisations($n);

    $n = $ngram_agent->getNgrams($line, 2);
$this->addCapitalisations($n);

    $n = $ngram_agent->getNgrams($line, 1);
$this->addCapitalisations($n);


    }

    }



    /**
     *
     * @param unknown $message (optional)
     */
    function getCapitalisation($text = null)
    {
global $wp;
//        $agent = "agent";
//        if (isset($this->search_agent)) {
//            $agent = $this->search_agent;
//        }
//        $word_agent = new Word($this->thing, "word");

//        $words = array();

$tokens = explode(" " , $text);
$t = "";
foreach($tokens as $i=>$token) {

//$slug = $wp->slug_agent->getSlug($token);

$preferred_capitalisation = $this->preferredCapitalisation($token);

$t .= $preferred_capitalisation ." ";
}

$this->capitalisation = trim($t);

        return $this->capitalisation;
    }

    public function make()
    {
        $this->makeSMS();
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
    }

    /**
     *
     */
    function makeSMS()
    {
        //$this->sms_message = "PORTMANTEAU | no match found";
        $t = "";
        //foreach ($this->words as $i => $word) {
        //    $t .= $word . " ";
        //}
        //trim($t);

        $this->sms_message =
            "CAPITALISATION " . $this->input . " | " . $this->capitalisation;

        $this->thing_report['sms'] = $this->sms_message;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->input;
if ($input == "capitalise") {return;}

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "capitalise")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("capitalise")
            );
        } elseif (
            ($pos = strpos(strtolower($input), "capitalize")) !== false
        ) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("capitalize")
            );
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $this->filtered_input = $filtered_input;
        $this->getCapitalisation($filtered_input);
    }
}
