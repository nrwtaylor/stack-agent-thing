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

class Animal extends Agent
{
    public $var = "hello";
    function init()
    {
        $this->initAnimal();
    }

    public function initAnimal()
    {
        $this->thing->animal_librex_handler = new Librex(
            $this->thing,
            "librex"
        );
        $this->thing->animal_librex_handler->getLibrex("animal/animals");

        $animal_words_list = $this->thing->animal_librex_handler->linesLibrex();
        $conditioned_animal_words_list = [];
        foreach ($animal_words_list as $i => $animal_words) {
            $animal_words_conditioned = preg_replace(
                "/\[.*\]/",
                "",
                $animal_words
            );
            $animal_words_conditioned = preg_replace(
                "/\(.*\)/",
                "",
                $animal_words_conditioned
            );

            $animal_words_conditioned = str_replace(
                "n/a",
                " ",
                $animal_words_conditioned
            );
            $animal_words_conditioned = str_replace(
                "/",
                " ",
                $animal_words_conditioned
            );

            $words = explode(" ", $animal_words_conditioned);
            foreach ($words as $j => $word) {
                $conditioned_word = trim($word);
                if ($conditioned_word == "") {
                    continue;
                }
                if ($conditioned_word == "?") {
                    continue;
                }
                if ($conditioned_word == "n/a") {
                    continue;
                }
                if (strlen($conditioned_word) == 1) {
                    continue;
                }
                $conditioned_animal_words_list[] = $conditioned_word;
            }
        }

        $this->animal_words_list = array_map(
            "strtolower",
            $conditioned_animal_words_list
        );
    }

    public function hasAnimal($text = null)
    {
        if ($text == "null") {
            return false;
        }

        $tokens = explode(" ", strtolower($text));

        foreach ($tokens as $i => $token) {
            if (in_array($token, $this->animal_words_list)) {
                return true;
            }
        }

        return false;
    }

    public function countAnimal($text = null)
    {
        if ($text == "null") {
            return false;
        }
        $count = 0;
        $tokens = explode(" ", strtolower($text));

        foreach ($tokens as $i => $token) {
            if (in_array($token, $this->animal_words_list)) {
                $count += 1;
            }
        }

        return $count;
    }

    function extractAnimals($input)
    {
        if (!isset($this->animal_words_list)) {
            $this->animal_words_list = [];
            return true;
        }

        $animal_words = [];

        $tokens = explode(" ", strtolower($input));
        foreach ($tokens as $i => $token) {
            if ($token == "") {
                continue;
            }

            if (in_array($token, $this->animal_words_list)) {
                $animal_words[] = $token;
                continue;
            }

            $singular_token = $this->singularizePlural($token);

            if (in_array($singular_token, $this->animal_words_list)) {
                // Add the unsingularized token
                $animal_words[] = $token;
                continue;
            }
        }
        return $animal_words;
        $pattern = "|\[A-Za-z]|";

        preg_match_all($pattern, $input, $m);
        $animal_words = $m[0];

        return $animal_words;
    }

    function extractAnimal($input)
    {
        $animal_words = $this->extractAnimals($input);

        if (count($animal_words) == 1) {
            $animal_word = $animal_words[0];
            return $animal_word;
        }

        if (count($animal_words) == 0) {
            return false;
        }

        if (count($animal_words) > 1) {
            return true;
        }

        return true;
    }

    function readAnimal()
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

        $this->thing_report["help"] = "This is a animal word extractor.";
    }

    public function makeSMS()
    {
        $animal = "X";
        if (isset($this->animal)) {
            $animal = $this->animal;
        }

        $sms_message = "ANIMAL " . strtoupper($animal);
        $sms_message .= " " . $this->response;

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    public function readSubject()
    {
        $this->num_hits = 0;
        $this->animal_words = [];

        $input = $this->input;
        $filtered_input = $this->assert($input, "animal");

        if ($filtered_input === "") {
            return;
        }

        $this->animal_words = $this->extractAnimals($filtered_input);

        if (count($this->animal_words) > 0) {
            $this->response .= "Saw at least one animal word. ";

            $this->response .= strtoupper(implode(" ", $this->animal_words));
        }

        $this->extractAnimal($filtered_input);

        $count = $this->countAnimal($filtered_input);
        $this->num_hits = $count;
        $this->score = $count;
        $this->animal = $count;
    }
}
