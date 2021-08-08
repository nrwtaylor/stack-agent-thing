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
    function getWords($text = null, $words_flag = true)
    {

        $portmanteau = str_replace(" ","",$text);
        if ($words_flag == true) {

            $word_agent = new Word($this->thing, "word");

            $tokens = explode(" ",$text);

//            while (strlen($text) > 0) {
                foreach ($tokens as $i=>$token) {
                    $result = $word_agent->isWord($token);

                    if ($result) {
                        $words[] = $token;
                    }

                }

//                $text = $remaining_word;
//            }

//            $this->words = $words;
            $portmanteau = implode("", $words);
        }


        //$this->words = str_replace(" ", "", $
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
        $this->sms_message = "PORTMANTEAU | " . $this->portmanteau;

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
