<?php
/**
 * BrillTagger.php
 *
 * @package default
 */
namespace Nrwtaylor\StackAgentThing;

require_once '/var/www/stackr.test/vendor/autoload.php';

// Splits sentences into syllables.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use Vanderlee\Syllable\Syllable;


class Syllables extends Agent
{

    private $dict;


    /**
     *
     */
    function init() {
    }


    /**
     *
     * @param unknown $tags
     */
    function printTag($tags) {
        foreach ($tags as $t) {
            echo $t['token'] . "/" . $t['tag'] .  " ";
        }
        echo "\n";
    }


    /**
     *
     * @param unknown $tags
     */
    function textTag($tags) {

        $text = "";

        foreach ($tags as $t) {
            $text .= $t['token'] . "/" . $t['tag'] .  " ";
        }
        $text .= "\n";
        $this->text = $text;
    }


    /**
     *
     */
    public function respond() {
        // Thing actions
        $this->makeSms();
        $from = $this->from;
        $to = $this->to;


        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

    }


    /**
     *
     */
    function makeSms() {

        $this->thing_report['sms'] = "SYLLABLES | " . $this->text . " | count " . $this->syllable_count;

    }


    /**
     *
     */
    public function readSubject() {

        // Strip out "syllables" commands.
        $input = $this->input;
        $whatIWant = $this->input;
        if (($pos = strpos(strtolower($input), "syllables")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("syllables")); 
        } elseif (($pos = strpos(strtolower($input), "syllables")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("syllables")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        $syllable = new Syllable('en-us');
        $syllable->getCache()->setPath('/var/www/stackr.test/vendor/vanderlee/syllable/src/Cache');

        $syllable->setHyphen("-");
        $this->text = $syllable->hyphenateText($filtered_input);
        $this->word_count = $syllable->countWordsText($filtered_input);
        $this->syllable_count = $syllable->countSyllablesText($filtered_input);
        $this->syllables = $syllable->splitText($filtered_input);

    }


}
