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

        if (!isset($this->slug_agent)) {
            $this->slug_agent = new Slug($this->thing, "slug");
        }

        //$lines = array("The quick brown fox was not Capitalized.", "The Return of the Jedi was.", "The Jedi attack.","The Attack of the Clones.");
        $this->initCapitalise();
    }

    public function capitaliseTitle($text = null)
    {
        $h_test = $this->getCapitalisation($text);
        $tks = explode(" ", $text);
        $s = mb_strlen($h_test);

        $brilltagger_agent = new Brilltagger($this->thing, "brilltagger");
        $m = $brilltagger_agent->tag($text);

        $capitalised_title = "";

        foreach ($m as $i => $tag_array) {
            $tag = trim($tag_array['tag']);
            $token = strtolower($tag_array['token']);
            $capitalised_token = $token;

            if (substr($tag, 0, 2) == "NN") {
                $capitalised_token = ucfirst($token);
            }
            if (substr($tag, 0, 2) == "VB") {
                $capitalised_token = ucfirst($token);
            }
            if (substr($tag, 0, 2) == "JJ") {
                $capitalised_token = ucfirst($token);
            }

            if (!ctype_lower($token)) {
                $token_test = $this->getCapitalisation($token);
                if (!ctype_lower($token_test)) {
                    $capitalised_token = $token_test;
                }
            }

            if (is_numeric($token)) {
                if (isset($tks[$i])) {
                    $capitalised_token = $tks[$i];
                }
            }

            $mixed_agent = new Mixed($this->thing, "mixed");

            if ($mixed_agent->isMixed($token)) {
                $tcapitalised_token = strtoupper($tag_array['token']);
            }

            if ($tag == "CC") {
            }

            $capitalised_title .= " " . $capitalised_token;
        }

        $this->capitalised_title = $capitalised_title;

        return $capitalised_title;
    }

    function addCapitalisation($capitalisation)
    {
        //global $wp;
        $slug = $this->slug_agent->getSlug($capitalisation);

        if (!isset($this->capitalisations)) {
            $this->capitalisations = array();
        }

        $count = 0;
        if (isset($this->capitalisations[$slug][$capitalisation]['count'])) {
            $count = $this->capitalisations[$slug][$capitalisation]['count'];
        }

        $arr = array("count" => ($count += 1));

        $this->capitalisations[$slug][$capitalisation] = $arr;
    }

    public function initCapitalise()
    {
        $lines = [];
        $path = '/var/www/stackr.test/resources/capitalise/capitalise.txt';

        if (file_exists($path)) {
        $contents = file_get_contents(
            $path
        );

        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            $lines[] = $line;
            // do something with $line
            $line = strtok($separator);
        }
}
        $this->loadCapitalisations($lines);
    }

    function preferredCapitalisation($text)
    {
        $slug = $this->slug_agent->getSlug($text);

        if (!isset($this->capitalisations[$slug])) {
            $this->addCapitalisation($text);
        }

        $capitalisations = $this->capitalisations[$slug];
        $max_count = 0;
        foreach ($capitalisations as $i => $capitalisation) {
            if ($capitalisation['count'] > $max_count) {
                $preferred_capitalisation = $i;
            }
        }

        return $preferred_capitalisation;
    }

    function addCapitalisations($capitalisations)
    {
        foreach ($capitalisations as $i => $capitalisation) {
            $this->addCapitalisation($capitalisation);
        }
    }

    function loadCapitalisations($lines = null)
    {
        // Read all the 1-gram to 3-gram combinations.
        // And see how they are capitalised in the set.

        if (!is_array($lines) and is_string($lines)) {
            $lines = array($lines);
        }

        $ngram_agent = new Ngram($this->thing, "ngram");

        foreach ($lines as $i => $line) {
            $n = $ngram_agent->getNgrams($line, 3);
            $this->addCapitalisations($n);

            $n = $ngram_agent->getNgrams($line, 2);
            $this->addCapitalisations($n);

            $n = $ngram_agent->getNgrams($line, 1);
            $this->addCapitalisations($n);
        }
    }

    /**
     *
     * @param unknown $message (optional)
     */
    function getCapitalisation($text = null)
    {
        $tokens = explode(" ", strtolower($text));
        $t = "";
        foreach ($tokens as $i => $token) {
            $preferred_capitalisation = $this->preferredCapitalisation($token);
            $t .= $preferred_capitalisation . " ";
        }

        $this->capitalisation = trim($t);

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
        $t = "";

        $this->sms_message =
            "CAPITALISATION " . $this->input . " | " . $this->capitalisation;

        $this->thing_report['sms'] = $this->sms_message;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->input;
        if ($input == "capitalise") {
            return;
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "capitalise")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("capitalise")
            );
        } elseif (($pos = strpos(strtolower($input), "capitalize")) !== false) {
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
