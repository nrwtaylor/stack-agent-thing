<?php
namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Activation extends Agent
{
    public $var = "hello";
    function init()
    {
        $this->initActivation();
    }

    public function initActivation()
    {
        $this->thing->activation_librex_handler = new Librex(
            $this->thing,
            "librex"
        );
        $this->thing->activation_librex_handler->getLibrex(
            "activation/activation_words"
        );

        $activation_words_list = $this->thing->activation_librex_handler->linesLibrex();
        $this->activation_words_list = array_map(
            "strtolower",
            $activation_words_list
        );
    }

    public function hasActivation($text = null)
    {
        if ($text == "null") {
            return false;
        }

        $tokens = explode(" ", strtolower($text));

        foreach ($tokens as $i => $token) {
            if (in_array($token, $this->activation_words_list)) {
                return true;
            }
        }

        return false;
    }

    public function countActivations($text = null)
    {
        if ($text == "null") {
            return false;
        }
        $count = 0;
        $tokens = explode(" ", strtolower($text));

        foreach ($tokens as $i => $token) {
            if (in_array($token, $this->activation_words_list)) {
                $count += 1;
            }
        }

        return $count;
    }

    function extractActivations($input)
    {
        if (!isset($this->activation_words_list)) {
            $this->activation_words_list = [];
            return true;
        }

        $activation_words = [];

        $tokens = explode(" ", strtolower($input));
        foreach ($tokens as $i => $token) {
            if (in_array($token, $this->activation_words_list)) {
                if ($token == "") {
                    continue;
                }
                $activation_words[] = $token;
            }
        }
        return $activation_words;
        $pattern = "|\[A-Za-z]|";

        preg_match_all($pattern, $input, $m);
        $activation_words = $m[0];

        return $activation_words;
    }

    function extractActivation($input)
    {
        $activation_words = $this->extractActivations($input);

        if (count($activation_words) == 1) {
            $activation_word = $activation_words[0];
            return $activation_word;
        }

        if (count($activation_words) == 0) {
            return false;
        }

        if (count($activation_words) > 1) {
            return true;
        }

        return true;
    }

    function readActivation()
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

        $this->thing_report["help"] = "This is a activation word extractor.";
    }

    public function makeSMS()
    {
        $activation = "X";
        if (isset($this->activation)) {
            $activation = $this->activation;
        }

        $sms_message = "ACTIVATION " . strtoupper($activation);
        $sms_message .= " " . $this->response;

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    public function readSubject()
    {
        $this->num_hits = 0;
        $this->activation_words = [];

        $input = $this->input;
        $filtered_input = $this->assert($input, "activation");

        if ($filtered_input === "") {
            return;
        }

        $this->activation_words = $this->extractActivations($filtered_input);

        if (count($this->activation_words) > 0) {
            $this->response .= "Saw at least one activation word. ";
        }

        $this->extractActivation($filtered_input);

        $count = $this->countActivations($filtered_input);
        $this->num_hits = $count;
        $this->score = $count;
        $this->activation = $count;
    }
}
