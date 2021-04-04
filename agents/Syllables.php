<?php
/**
 * BrillTagger.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;



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

        $this->cache_path = '/var/www/stackr.test/vendor/vanderlee/syllable/src/Cache';
$this->resource_path_cache = $GLOBALS['stack_path'] . 'vendor/vanderlee/syllable/src/Cache';


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

        $text = "No response.";
        if (isset($this->text)) {
            $text = $this->text;
        }

        $syllable_count = "None seen.";
        if (isset($this->syllable_count)) {
            $syllable_count = $this->syllable_count;
        }


        $this->thing_report['sms'] = "SYLLABLES | " . $text . " | count " . $syllable_count;

    }


    /**
     *
     */
    public function readSubject() {

        // Strip out "syllables" commands.
        $input = $this->input;

        if (strtolower($input) == "syllables") {
            $this->getTask();

            if (!isset($this->link_task)) {return;}

            $this->doSyllables($this->link_task);
            return;
        }

        $whatIWant = $this->input;
        if (($pos = strpos(strtolower($input), "syllables")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("syllables"));
        } elseif (($pos = strpos(strtolower($input), "syllables")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("syllables"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        $this->doSyllables($filtered_input);
    }


    /**
     *
     * @param unknown $filtered_input
     */
    function doSyllables($filtered_input) {

        $syllable = new Syllable('en-ca');
        $cache = $syllable->getCache();
//        $cache->setPath('/var/www/html/stackr.ca/vendor/vanderlee/syllable/src/Cache');

        $syllable->getSource()->setPath($this->resource_path_cache);


        $syllable->setMinWordLength(0);
        $syllable->setHyphen("-");
        $this->text = $syllable->hyphenateText($filtered_input);
        $this->word_count = $syllable->countWordsText($filtered_input);
        $this->syllable_count = $syllable->countSyllablesText($filtered_input);
        $this->syllables = $syllable->splitText($filtered_input);

    }

}
