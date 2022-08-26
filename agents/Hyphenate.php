<?php
/**
 * Hyphenate.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

// Recognizes and handles UUIDS.
// Does not generate them.  That is a Thing function.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Hyphenate extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->agent_name = "HYPHENATE";
        //$this->multiplier = "MHz";

        $this->stack_state = $this->thing->container['stack']['state'];
        $this->short_name = $this->thing->container['stack']['short_name'];

        $this->created_at = false;
        if (isset($this->thing->thing->created_at)) {
           $this->created_at = strtotime($this->thing->thing->created_at);
        }

        $this->thing->log(
            'started running on Thing ' . date("Y-m-d H:i:s") . ''
        );

        $this->node_list = ["hyphenate" => ["hyphenate", "snowflake"]];

        $this->pattern = '|[0-9]{1,3}[" "]?[.]?[0-9]{1,4}|';

        $this->aliases = ["learning" => ["good job"]];

        //        $this->makePNG();

        $this->thing_report['help'] = "Recognizes hyphenates.";
    }

    function extractHyphenates($input = null)
    {
        if (is_array($input)) {
            return true;
        }
        $tokens = explode(
            ' ',
            str_replace(
                [',', '*', '(', ')', '[', ']', '!', '&', 'and', '.'],
                ' ',
                $input
            )
        );
        $hyphens = [];

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

    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function hasHyphenate($text)
    {
        $this->extractHyphenates($text);
        if (isset($this->hyphenates) and count($this->hypenates) > 0) {
            return true;
        }
        return false;
    }

    function set()
    {
        $this->thing->Write(
            ["hyphenate", "received_at"],
            $this->thing->time()
        );


    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->extractHyphenates($this->input);
        if (isset($this->hyphenate) and $this->hyphenate != null) {
            $this->response = "Hyphenate spotted.";
            return;
        }

        $input = $this->input;
        $strip_words = ["hyphenate"];

        foreach ($strip_words as $i => $strip_word) {
            $whatIWant = $input;
            if (
                ($pos = strpos(strtolower($input), $strip_word . " is")) !==
                false
            ) {
                $whatIWant = substr(
                    strtolower($input),
                    $pos + strlen($strip_word . " is")
                );
            } elseif (
                ($pos = strpos(strtolower($input), $strip_word)) !== false
            ) {
                $whatIWant = substr(
                    strtolower($input),
                    $pos + strlen($strip_word)
                );
            }

            $input = $whatIWant;
        }

        $filtered_input = ltrim(strtolower($input), " ");
        return false;
    }

    /**
     *
     */
    function makeResponse()
    {
        if (isset($this->response)) {
            return;
        }
        $this->response = "X";
        if (isset($this->hyphenates) and count($this->hyphenates) > 0) {
            $this->response = "";
            foreach ($this->hyphenates as $index => $hyphenate) {
                $this->response .= $hyphenate . " ";
            }
        }
    }

    /**
     *
     */
    function makeSMS()
    {
        $this->sms_message = strtoupper($this->agent_name) . "";

        $t = "";
        foreach ($this->hyphenates as $i => $hyphenate) {
            $t .= $hyphenate . " ";
        }
        $t = trim($t);

        if ($t != "") {
            $this->sms_message .= " | Found: " . $t . "";
        }

        if ($this->response != "") {
            $this->sms_message .= $this->response;
        }

        $this->sms_message .= ' | TEXT CHANNEL';

        $this->thing_report['sms'] = $this->sms_message;
    }

    /**
     *
     */
    function makeChoices()
    {
        $this->thing->choice->Create(
            "hyphenate",
            $this->node_list,
            "hyphenate"
        );

        $choices = $this->thing->choice->makeLinks("hyphenate");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }

    /**
     *
     */
    function makeImage()
    {
        $this->image = null;
    }
}
