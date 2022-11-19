<?php
namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

        // The free dictionary
        // 1. Giving or affording pleasure or enjoyment; agreeable:
        // a pleasant scene; pleasant sensations.
        // 2. Pleasing in manner, behavior, or appearance.

        // Going to focus on the first.


class Pleasantness extends Agent
{
    public $var = "hello";
    function init()
    {
        $this->initPleasantness();
    }

    public function initPleasantness()
    {
        $this->thing->pleasantness_librex_handler = new Librex(
            $this->thing,
            "librex"
        );
        $this->thing->pleasantness_librex_handler->getLibrex(
            "pleasant/pleasant_words"
        );

        $pleasant_words_list = $this->thing->pleasantness_librex_handler->linesLibrex();
        $this->pleasant_words_list = array_map(
            "strtolower",
            $pleasant_words_list
        );
    }

    public function hasPleasantness($text = null)
    {
        if ($text == "null") {
            return false;
        }

        $tokens = explode(" ", strtolower($text));

        foreach ($tokens as $i => $token) {
            if (in_array($token, $this->pleasant_words_list)) {
                return true;
            }
        }

        return false;
    }

    public function countPleasantness($text = null)
    {
        if ($text == "null") {
            return false;
        }
        $count = 0;
        $tokens = explode(" ", strtolower($text));

        foreach ($tokens as $i => $token) {
            if (in_array($token, $this->pleasant_words_list)) {
                $count += 1;
            }
        }

        return $count;
    }

    function extractPleasantnesses($input)
    {
        if (!isset($this->pleasant_words_list)) {
            $this->pleasant_words_list = [];
            return true;
        }

        $pleasant_words = [];

        $tokens = explode(" ", strtolower($input));
        foreach ($tokens as $i => $token) {
            if (in_array($token, $this->pleasant_words_list)) {
                if ($token == "") {
                    continue;
                }
                $pleasant_words[] = $token;
            }
        }
        return $pleasant_words;
        $pattern = "|\[A-Za-z]|";

        preg_match_all($pattern, $input, $m);
        $pleasant_words = $m[0];

        return $pleasant_words;
    }

    function extractPleasantness($input)
    {
        $pleasant_words = $this->extractPleasantnesses($input);

        if (count($pleasant_words) == 1) {
            $pleasant_word = $pleasant_words[0];
            return $pleasant_word;
        }

        if (count($pleasant_words) == 0) {
            return false;
        }

        if (count($pleasant_words) > 1) {
            return true;
        }

        return true;
    }

    function readPleasantness()
    {
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] = "This is a pleasant word extractor.";
    }

    public function makeSMS()
    {
        $pleasantness = "X";
        if (isset($this->pleasantness)) {
            $pleasantness = $this->pleasantness;
        }

        $sms_message = "PLEASANTNESS " . strtoupper($pleasantness);
        $sms_message .= " " . $this->response;

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    public function readSubject()
    {
        $this->num_hits = 0;
        $this->pleasant_words = [];

        $input = $this->input;
        $filtered_input = $this->assert($input, "pleasantness");

        if ($filtered_input === "") {
            return;
        }

        $this->pleasant_words = $this->extractPleasantnesses($filtered_input);

        if (count($this->pleasant_words) > 0) {
            $this->response .= "Saw at least one pleasant word. ";
        }

        $this->extractPleasantness($filtered_input);

        $count = $this->countPleasantness($filtered_input);
        $this->num_hits = $count;
        $this->score = $count;
        $this->pleasantness = $count;
    }
}
