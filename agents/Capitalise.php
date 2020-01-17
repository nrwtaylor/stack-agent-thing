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
    }

    /**
     *
     * @param unknown $message (optional)
     */
    function getCapitalisation($text = null)
    {
        $agent = "agent";
        if (isset($this->search_agent)) {
            $agent = $this->search_agent;
        }
        $word_agent = new Word($this->thing, "word");

        $words = array();

        $capitalisations = $word_agent->extractWords($text);
        //$result = $word_agent->isWord($text);
$this->capitalisations = $capitalisations;
$this->capitalisation = null;
if (isset($this->capitalisations[0])) {
        $this->capitalisation = $this->capitalisations[0];
}
//    $thing_report = $this->thing->db->subjectSearch($text, $agent, 999);


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
            "CAPITALIZATION " . strtoupper($this->filtered_input) . " | " . $this->capitalisation;

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
