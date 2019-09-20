<?php
/**
 * BrillTagger.php
 *
 * @package default
 */

// Thank you http://phpir.com/part-of-speech-tagging

namespace Nrwtaylor\StackAgentThing;

// Recognizes parts of speech. See also NOUN. And ADVERB.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Brilltagger extends Agent
{

    private $dict;


    /**
     *
     */
    function init() {

        $lexicon = $this->resource_path . "brilltagger/lexicon.txt";
        $fh = fopen($lexicon, 'r');
        while ($line = fgets($fh)) {
            $tags = explode(' ', $line);
            $this->dict[strtolower(array_shift($tags))] = $tags;
        }
        fclose($fh);


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

        $this->thing_report['sms'] = "BRILL TAGGER | " . $this->text;

    }


    /**
     *
     */
    public function readSubject() {

        // Strip out brilltagger commands.
        $input = $this->input;

        if (strtolower($input) == "brilltagger") {
            $this->getTask();
//            $this->doSyllables($this->link_task);
        // Then run it through the classifier.
        $tags = $this->tag($this->link_task);
//        $this->printTag($tags);
        $this->textTag($tags);

            return;
        }


        $whatIWant = $this->input;
        if (($pos = strpos(strtolower($input), "brill tagger")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("brill tagger")); 
        } elseif (($pos = strpos(strtolower($input), "brilltagger")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("brilltagger")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        // Then run it through the classifier.
        $tags = $this->tag($filtered_input);
$this->tags = $tags;
//        $this->printTag($tags);
        $this->textTag($tags);

    }

    /**
     *
     * @param unknown $text
     * @return unknown
     */
    public function tag($text) {
        preg_match_all("/[\w\d\.]+/", $text, $matches);
        $nouns = array('NN', 'NNS');

        $return = array();
        $i = 0;
        foreach ($matches[0] as $token) {
            // default to a common noun
            $return[$i] = array('token' => $token, 'tag' => 'NN');

            // remove trailing full stops
            if (substr($token, -1) == '.') {
                $token = preg_replace('/\.+$/', '', $token);
            }

            // get from dict if set
            if (isset($this->dict[strtolower($token)])) {
                $return[$i]['tag'] = $this->dict[strtolower($token)][0];
            }

            // Converts verbs after 'the' to nouns
            if ($i > 0) {
                if ($return[$i - 1]['tag'] == 'DT' &&
                    in_array($return[$i]['tag'],
                        array('VBD', 'VBP', 'VB'))) {
                    $return[$i]['tag'] = 'NN';
                }
            }

            // Convert noun to number if . appears
            if ($return[$i]['tag'][0] == 'N' && strpos($token, '.') !== false) {
                $return[$i]['tag'] = 'CD';
            }

            // Convert noun to past particile if ends with 'ed'
            if ($return[$i]['tag'][0] == 'N' && substr($token, -2) == 'ed') {
                $return[$i]['tag'] = 'VBN';
            }

            // Anything that ends 'ly' is an adverb
            if (substr($token, -2) == 'ly') {
                $return[$i]['tag'] = 'RB';
            }

            // Common noun to adjective if it ends with al
            if (in_array($return[$i]['tag'], $nouns)
                && substr($token, -2) == 'al') {
                $return[$i]['tag'] = 'JJ';
            }

            // Noun to verb if the word before is 'would'
            if ($i > 0) {
                if ($return[$i]['tag'] == 'NN'
                    && strtolower($return[$i-1]['token']) == 'would') {
                    $return[$i]['tag'] = 'VB';
                }
            }

            // Convert noun to plural if it ends with an s
            if ($return[$i]['tag'] == 'NN' && substr($token, -1) == 's') {
                $return[$i]['tag'] = 'NNS';
            }

            // Convert common noun to gerund
            if (in_array($return[$i]['tag'], $nouns)
                && substr($token, -3) == 'ing') {
                $return[$i]['tag'] = 'VBG';
            }

            // If we get noun noun, and the second can be a verb, convert to verb
            if ($i > 0) {
                if (in_array($return[$i]['tag'], $nouns)
                    && in_array($return[$i-1]['tag'], $nouns)
                    && isset($this->dict[strtolower($token)])) {
                    if (in_array('VBN', $this->dict[strtolower($token)])) {
                        $return[$i]['tag'] = 'VBN';
                    } else if (in_array('VBZ',
                            $this->dict[strtolower($token)])) {
                        $return[$i]['tag'] = 'VBZ';
                    }
                }
            }

            $i++;
        }

        return $return;
    }


}
