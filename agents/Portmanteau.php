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

    function wordsPortmanteau($text = null, $words_flag = true) {
       return $this->getWords($text, $words_flag);
    }

    /**
     *
     * @param unknown $message (optional)
     */
    function getWords($text = null, $words_flag = true)
    {
        $words = [];
        $portmanteau = str_replace(" ","",$text);
        $word_agent = new Word($this->thing, "word");

        if ($words_flag == true) {
            $tokens = explode(" ",$text);

//            while (strlen($text) > 0) {
                foreach ($tokens as $i=>$token) {
                    $result = $word_agent->isWord($token);

                    if ($result) {
                        $words[] = $token;
                    }

                }

            $portmanteau = implode("", $words);
        }


        $this->portmanteau = $portmanteau;

        return $portmanteau;
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
        $portmanteau_text = "No portmanteau found.";
        if ($this->portmanteau !== "") {$portmanteau_text = $this->portmanteau;}

        $this->sms_message = "PORTMANTEAU | " . $portmanteau_text;

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

        // False - allow non-words
        // True - require words.
        $this->getWords($filtered_input, true);

    }
}
