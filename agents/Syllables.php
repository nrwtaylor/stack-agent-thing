<?php
/**
 * BrillTagger.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

require_once '/var/www/html/stackr.ca/vendor/autoload.php';

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
     * @return unknown

    function getTask() {

        $block_things = array();
        // See if a stack record exists.
        $findagent_thing = new Findagent($this->thing, 'thing');

        $this->max_index =0;
        $match = 0;
        $link_uuids = array();

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {

            $this->thing->log($block_thing['task'] . " " . $block_thing['nom_to'] . " " . $block_thing['nom_from']);
            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_task = $block_thing['task'];
                $link_tasks[] = $block_thing['task'];
                // if ($match == 2) {break;}
                // Get upto 10 matches
                if ($match == 10) {break;}
            }
        }
        $this->prior_agent = "web";
        foreach ($link_tasks as $key=>$link_task) {
            //            $previous_thing = new Thing($link_uuid);
            var_dump($link_task);
            //            if (isset($previous_thing->json->array_data['message']['agent'])) {
            if (isset($link_task)) {

                if (in_array(strtolower($link_task), array('web', 'pdf', 'txt', 'log', 'php', 'syllables', 'brilltagger'))) {
                    continue;
                }

                $this->link_task = $link_task;
                break;
            }
        }

        $this->web_exists = true;
        if (!isset($agent_thing->thing_report['web'] )) {$this->web_exists = false;}

        return $this->link_task;
    }

*/


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

        if (strtolower($input) == "syllables") {
            $this->getTask();
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
        $cache->setPath('/var/www/html/stackr.ca/vendor/vanderlee/syllable/src/Cache');

        $syllable->getSource()->setPath('/var/www/html/stackr.ca/vendor/vanderlee/syllable/languages');


        $syllable->setMinWordLength(0);
        $syllable->setHyphen("-");
        $this->text = $syllable->hyphenateText($filtered_input);
        $this->word_count = $syllable->countWordsText($filtered_input);
        $this->syllable_count = $syllable->countSyllablesText($filtered_input);
        $this->syllables = $syllable->splitText($filtered_input);



    }


}
