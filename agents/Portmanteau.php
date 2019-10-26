<?php
/**
 * Portmanteau.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Portmanteau extends Agent
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
        //$this->sms_message = "PORTMANTEAU | no match found";
        $t = "";
        foreach ($this->words as $i => $word) {
            $t .= $word . " ";
        }
        trim($t);

        $this->sms_message =
            "PORTMANEAU " . strtoupper($this->filtered_input) . " | " . $t;

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
        if (($pos = strpos(strtolower($input), "portmanteau")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("portmanteau")
            );
        } elseif (
            ($pos = strpos(strtolower($input), "portmanteau")) !== false
        ) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("portmanteau")
            );
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $this->filtered_input = $filtered_input;
        $this->getWords($filtered_input);
    }
}
