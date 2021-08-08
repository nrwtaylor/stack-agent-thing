<?php
/**
 * Deportmanteau.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Deportmanteau extends Agent
{
    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
    }

    /**
     *
     * @param unknown $message (optional)
     */
    function getWords($text = null)
    {
        $word_agent = new Word($this->thing, "word");

        $words = array();

        while (strlen($text) > 0) {
            foreach (range(strlen($text), 1, -1) as $n) {
                $test_text = trim(substr($text, 0, $n));
                $result = $word_agent->isWord($test_text);

                if ($result) {
                    $words[] = $test_text;
                    $remaining_word = substr($text, $n, strlen($text));
                    break;
                }

            }

            $text = $remaining_word;
        }

        $this->words = $words;

        return $this->words;
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
        //$this->sms_message = "DEPORTMANTEAU | no match found";
        $t = "";
        foreach ($this->words as $i => $word) {
            $t .= $word . " ";
        }
        trim($t);

        $this->sms_message =
            "DEPORTMANEAU " . strtoupper($this->filtered_input) . " | " . $t;

        $this->thing_report['sms'] = $this->sms_message;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->input;
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "deportmanteau")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("deportmanteau")
            );
        } elseif (
            ($pos = strpos(strtolower($input), "deportmanteau")) !== false
        ) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("deportmanteau")
            );
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $this->filtered_input = $filtered_input;
        $this->getWords($filtered_input);
    }
}
