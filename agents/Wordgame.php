<?php
namespace Nrwtaylor\StackAgentThing;

class Wordgame extends Agent
{
    public function init()
    {
        $this->resource_path = $GLOBALS["stack_path"] . "resources/words/";

        $this->show_best_words = true;
        $this->show_best_score = true;

        $this->node_list = ["wordgame" => ["wordgame"]];

        if (!isset($thing->to)) {
            $this->to = null;
        } else {
            $this->to = $thing->to;
        }
        if (!isset($thing->from)) {
            $this->from = null;
        } else {
            $this->from = $thing->from;
        }
        if (!isset($thing->subject)) {
            $this->subject = $this->agent_input;
        } else {
            $this->subject = $thing->subject;
        }

        $this->keywords = [];

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "word",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["word", "refreshed_at"],
                $time_string
            );
        }

        // If it has already been processed ...
        $this->search_words = $this->thing->json->readVariable([
            "word",
            "letters",
        ]);

        $this->response = "Loaded letters.";

        if ($this->search_words == false) {
            $this->readSubject();
        }

        if ($this->search_words == "s/ is wordgame button") {
            $this->search_words = "";
            $this->response = "Button pressed.";
        }

        $this->word_agent = new Word($this->thing, "word");
        $this->word_agent->ewolWords();

        $this->getLetters();

        $this->thing->json->writeVariable(
            ["word", "letters"],
            $this->search_words
        );

        if ($this->agent_input == null) {
            $this->respondResponse();
        }

        $this->set();

        if (count($this->best_words) != 0) {
            $this->thing->log(
                $this->agent_prefix .
                    "completed with a reading of " .
                    $this->best_words[0]["word"] .
                    "."
            );
        } else {
            $this->thing->log($this->agent_prefix . "did not find words.");
        }

        $this->thing_report["log"] = $this->thing->log;
        $this->thing_report["response"] = $this->response;
    }

    function set()
    {
        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable(
            ["wordgame", "letters"],
            $this->search_words
        );

        $time_string = $this->thing->time();
        $this->thing->json->writeVariable(
            ["wordgame", "refreshed_at"],
            $time_string
        );
    }

    function scoreWord($text)
    {
        if ($text == "" or $text == null) {
            return 0;
        }

        if (!isset($this->letter_scores)) {
            $this->initBag();
        }

        $letters = str_split($text);
        $score = 0;
        foreach ($letters as $letter) {
            $score += $this->letter_scores[strtoupper($letter)];
        }

        return $score;
    }

    function initBag()
    {
        $letter_bag = [
            0 => [" " => 2],
            1 => [
                "E" => 12,
                "A" => 9,
                "I" => 9,
                "O" => 8,
                "N" => 6,
                "R" => 6,
                "T" => 6,
                "L" => 4,
                "S" => 4,
                "U" => 4,
            ],
            2 => ["D" => 4, "G" => 3],
            3 => ["B" => 2, "C" => 2, "M" => 2, "P" => 2],
            4 => ["F" => 2, "H" => 2, "V" => 2, "W" => 2, "Y" => 2],
            5 => ["K" => 1],
            8 => ["J" => 1, "X" => 1],
            10 => ["Q" => 1, "Z" => 1],
        ];

        $this->letter_tiles = [];
        foreach ($letter_bag as $score => $tile_set) {
            foreach ($tile_set as $letter => $quantity) {
                foreach (range(1, $quantity) as $n) {
                    $this->letter_tiles[] = [
                        "letter" => $letter,
                        "score" => $score,
                    ];
                    $this->letter_scores[$letter] = $score;
                }
            }
        }
    }

    function randomLetters($n = null)
    {
        if ($n == null) {
            $n = 7;
        }

        if (!isset($this->letter_tiles)) {
            $this->initBag();
        }
        shuffle($this->letter_tiles);

        $player_letter_tiles = [];
        $elems = [];
        foreach (range(1, 7) as $n) {
            $tile = array_pop($this->letter_tiles);

            $player_letter_tiles[] = $tile;
            $elems[] = $tile["letter"];
        }

        return $elems;
    }

    function getLetters()
    {
        if (!isset($this->letter_tiles)) {
            $this->initBag();
        }
        shuffle($this->letter_tiles);

        $player_letter_tiles = [];
        $elems = [];
        foreach (range(1, 7) as $n) {
            $tile = array_pop($this->letter_tiles);
            $player_letter_tiles[] = $tile;

            $elems[] = $tile["letter"];
        }

        $this->player_tiles = $player_letter_tiles;

        if (isset($this->search_words) and $this->search_words != "") {
            $elems = str_split($this->search_words);
        } else {
            $this->search_words = implode("", $elems);
            $this->response = "Selected 7 letters";
        }

        // https://stackoverflow.com/questions/12160843/generate-all-possible-combinations-using-a-set-of-strings

        $letter_arrangements = [];
        //$set = array("a", "b", "c");
        $this->gen_nos($elems, $letter_arrangements);

        // Remove dupes
        $letter_arrangements = array_unique($letter_arrangements);

        // Create seven tile variants for asterisk

        foreach ($letter_arrangements as $letter_arrangement) {
            $letters = str_split($letter_arrangement);
            foreach ($letters as $i => $letter) {
                if ($letter == "*") {
                    $letter_arrangement[$i] = "?";

                    $max_padding = 7 - mb_strlen($letter_arrangement);

                    foreach (range(0, $max_padding) as $x) {
                        if ($x == 0) {
                            continue;
                        } // Allow zero space

                        $letter_arrangement = substr_replace(
                            $letter_arrangement,
                            "?",
                            $i,
                            0
                        );
                        $letter_arrangements[] = $letter_arrangement;
                    }
                }
            }
        }

        // Create A-Z variants for blanks

        foreach ($letter_arrangements as $letter_arrangement) {
            $letters = str_split($letter_arrangement);
            foreach ($letters as $i => $letter) {
                if ($letter == " " or $letter == "?") {
                    foreach (range("a", "z") as $v) {
                        $letter_arrangement[$i] = strtoupper($v);
                        $letter_arrangements[] = $letter_arrangement;
                    }
                }
            }
        }

        // Remove variants with a space

        foreach ($letter_arrangements as $i => $letter_arrangement) {
            $letters = str_split($letter_arrangement);
            foreach ($letters as $key => $letter) {
                if ($letter == " " or $letter == "?") {
                    unset($letter_arrangements[$i]);
                }
            }
        }

        $this->letter_arrangements = $letter_arrangements;

        $min_score = 1;
        $high_score = 0;
        $best_word_list = [];
        foreach ($this->letter_arrangements as $letter_arrangement) {
            $score = $this->scoreWord($letter_arrangement);
            if (mb_strlen($letter_arrangement) == 1) {
                continue;
            } //Disallow 1 word words

            // Quicker to score first.  Then do a full dictionary lookup.
            //    $is_word = $word->findWord("ewol",$letter_arrangement);
            $is_word = isset(
                $this->word_agent->ewol_dictionary[
                    strtolower($letter_arrangement)
                ]
            );

            if ($is_word == true) {
                $high_score = $score;
                array_unshift($best_word_list, [
                    "word" => $letter_arrangement,
                    "score" => $score,
                ]);

                //$score = $this->scoreWord($letter_arrangement);
                $this->thing->log($letter_arrangement . " " . $score . ".");
            }
        }

        //https://stackoverflow.com/questions/1597736/how-to-sort-an-array-of-associative-arrays-by-value-of-a-given-key-in-php

        $score = [];
        foreach ($best_word_list as $key => $row) {
            $score[$key] = $row["score"];
        }
        array_multisort($score, SORT_DESC, $best_word_list);

        $this->best_words = $best_word_list;
    }

    function gen_nos(&$set, &$results)
    {
        for ($i = 0; $i < count($set); $i++) {
            $results[] = $set[$i];
            $tempset = $set;
            array_splice($tempset, $i, 1);
            $tempresults = [];
            $this->gen_nos($tempset, $tempresults);
            foreach ($tempresults as $res) {
                $results[] = $set[$i] . $res;

                $t = $this->thing->elapsed_runtime() - $this->start_time;
                if ($t > 8000) {
                    $this->response = "Too many letters. Ran out of time.";
                    return;
                }
            }
        }
    }

    function lengthWordgame($text, $n = null)
    {
        if ($n == null) {
            $n = 2;
        }

        $n = [2, 3];

        //        foreach($n as $word_length) {
        foreach ($this->letter_arrangements as $letter_arrangement) {
            foreach ($n as $word_length) {
                $characters = mb_strlen($letter_arrangement);
                if ($characters == $word_length) {
                    $is_word = isset(
                        $this->word_agent->ewol_dictionary[
                            strtolower($letter_arrangement)
                        ]
                    );

                    if ($is_word == true) {
                        $words[] = $letter_arrangement;
                    }
                }
            }
        }
        $this->selective_length_words = $words;
    }

    function anagrams($text)
    {
        $letters = str_split($text);

        $combos = $letters;
        $lastres = $letters;
        for ($i = 1; $i < count($letters); $i++) {
            $newres = [];
            foreach ($lastres as $r) {
                foreach ($letters as $let) {
                    $newres[] = $r . $let;
                }
            }

            foreach ($newres as $w) {
                $combos[] = $w;
            }

            $lastres = $newres;
        }

        return $combos;
    }

    function create_possible_arrays($string)
    {
        $letters = str_split($string);

        $combos = array_unique($letters);
        $lastres = $letters;
        for ($i = 1; $i < count($letters); $i++) {
            $newres = [];
            foreach ($lastres as $r) {
                foreach ($letters as $let) {
                    $new = $r . $let;
                    if (!in_array($new, $newres)) {
                        $newres[] = $new;

                        // Action
                        $combos[] = $new;
                    }
                }
            }

            $lastres = $newres;
        }

        return $combos;
    }

    // For an array of n words, return an array of n! strings, each containing the words in a different order.
    function wordcombos($words)
    {
        if (count($words) <= 1) {
            $result = $words;
        } else {
            $result = [];
            for ($i = 0; $i < count($words); ++$i) {
                $firstword = $words[$i];
                $remainingwords = [];
                for ($j = 0; $j < count($words); ++$j) {
                    if ($i != $j) {
                        $remainingwords[] = $words[$j];
                    }
                }
                $combos = $this->wordcombos($remainingwords);
                for ($j = 0; $j < count($combos); ++$j) {
                    $result[] = $firstword . "" . $combos[$j];
                }
            }
        }
        return $result;
    }

    function comb($n, $elems)
    {
        if ($n > 0) {
            $tmp_set = [];
            $res = $this->comb($n - 1, $elems);
            foreach ($res as $ce) {
                foreach ($elems as $e) {
                    array_push($tmp_set, $ce . $e);
                }
            }
            return $tmp_set;
        } else {
            return [""];
        }
    }

    function getWords($test)
    {
        if ($test == false) {
            return false;
        }

        $new_words = [];

        if ($test == "") {
            return $new_words;
        }

        $pattern =
            '/([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC5[\x92\x93\xA0\xA1\xB8\xBD\xBE]){1,}/';
        $t = preg_split($pattern, $test);

        foreach ($t as $key => $word) {
            $new_words[] = trim($word);
        }
        return $new_words;
    }

    public function stripPunctuation($input, $replace_with = " ")
    {
        $unpunctuated = preg_replace(
            '/[\:\;\/\!\?\#\.\,\'\"\{\}\[\]\<\>\(\)]/i',
            $replace_with,
            $input
        );
        return $unpunctuated;
    }

    function extractWords($string)
    {
        preg_match_all(
            '/([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC5[\x92\x93\xA0\xA1\xB8\xBD\xBE]){2,}/',
            $string,
            $words
        );
        $w = $words[0];

        $this->words = [];

        foreach ($w as $key => $value) {
            // Return dictionary entry.
            $value = $this->stripPunctuation($value);

            $text = $this->findWord("list", $value);

            if ($text != false) {
                $this->words[] = $text;
            } else {
            }
        }

        if (count($this->words) != 0) {
            $this->word = $this->words[0];
        } else {
            $this->word = null;
        }

        return $this->words;
    }

    function getWord()
    {
        if (!isset($this->words)) {
            $this->extractWords($this->subject);
        }
        if (count($this->words) == 0) {
            $this->word = false;
            return false;
        }
        $this->word = $this->words[0];
        return $this->word;
    }

    function findWord($librex, $searchfor)
    {
        if ($librex == "" or $librex == " " or $librex == null) {
            return false;
        }
        switch ($librex) {
            case null:
            // Drop through
            case "list":
                $file = $this->resource_path . "words.txt";
                $contents = file_get_contents($file);
                break;
            case "mordok":
                $file = $this->resource_path . "mordok.txt";
                $contents = file_get_contents($file);
                break;
            case "context":
                $this->contextWord();
                $contents = $this->word_context;
                $file = null;
                break;

            case "emotion":
                break;
            default:
                $file = $this->resource_path . "words.txt";
        }

        $pattern = "|\b($searchfor)\b|";
        // search, and store all matching occurences in $matches

        if (preg_match_all($pattern, $contents, $matches)) {
            $m = $matches[0][0];
            return $m;
        } else {
            return false;
        }

        return;
    }

    function nearestWord($input)
    {
        if ($input == "") {
            return true;
        }

        $file = $this->resource_path . "words.txt";
        $contents = file_get_contents($file);

        $words = explode("\n", $contents);

        $nearness_min = 1e6;
        $word = false;

        foreach ($words as $key => $word) {
            $nearness = levenshtein($input, $word);
            //$nearness = similar_text($word, $input);

            if ($nearness < $nearness_min) {
                $word_list = [];
                $nearness_min = $nearness;
            }
            if ($nearness_min == $nearness) {
                $word_list[] = $word;
            }
        }

        $nearness_max = 0;
        $word = false;

        foreach ($word_list as $key => $word) {
            //$nearness = levenshtein($input, $word);
            $nearness = similar_text($word, $input);

            // Figure out how to deal with sequences of question marks

            if ($nearness > $nearness_max) {
                $new_word_list = [];
                $nearness_min = $nearness;
            }
            if ($nearness_min == $nearness) {
                $new_word_list[] = $word;
            }
        }

        $nearest_word = implode(" ", $new_word_list);

        return $nearest_word;
    }

    public function respondResponse()
    {
        $this->cost = 100;

        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->makeChoices();
        $this->thing_report["help"] =
            "Tells you the highest scoring letter arrangement. Or the score.";
    }

    function makeChoices()
    {
        $this->thing->choice->Create("wordgame", $this->node_list, "wordgame");
        $choices = $this->thing->choice->makeLinks("wordgame");
        $this->thing_report["choices"] = $choices;
    }

    function makeSMS()
    {
        $this->node_list = ["wordgame" => ["wordgame"]];

        $index = 0;
        $max_index = 3;
        $best_words = "";

        foreach ($this->best_words as $best_word) {
            if ($index > $max_index) {
                break;
            }
            if ($index == 0) {
                $best_words .=
                    " " . str_pad("", mb_strlen($best_word["word"]), "?");
            } else {
                $best_words .= " " . strtoupper($best_word["word"]);
            }
            $index++;
        }

        if (isset($this->best_words[1]["score"])) {
            $second_best_score = $this->best_words[1]["score"];
        }

        $text = str_replace(" ", "?", $this->search_words);

        $sms = "WORDGAME " . strtoupper($text);

        switch (true) {
            case !isset($this->best_words[0]):
                //      $sms .= " | " . $this->response;
                break;

            case strtoupper($this->best_words[0]["word"]) ==
                strtoupper($this->search_words):
                $sms .= " | scores " . $this->best_words[0]["score"];
                break;
            case $this->show_best_words != true:
                $sms .= " | best words " . trim($best_words);
                break;
            case $this->show_best_score != true:
                $sms .=
                    " | " .
                    strtoupper($this->best_words[1]["word"]) .
                    " scores " .
                    $second_best_score;
                break;
            default:
        }

        if (isset($this->response)) {
            $sms .= " | " . $this->response;
        }

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
        return;
    }

    function makeWeb()
    {
        $this->node_list = ["wordgame" => ["wordgame"]];

        $text = str_replace(" ", "?", $this->search_words);

        $html = "<b>WORDGAME " . strtoupper($text) . "</b><br>";

        $html .= "<p><b>Best scoring word</b>";

        $html .=
            "<br>" .
            $this->best_words[0]["word"] .
            " scores " .
            $this->best_words[0]["score"];

        $html .= "<p><b>Dictionary words</b>";
        $html .= "<br>";
        foreach ($this->best_words as $best_word) {
            $html .= " " . $best_word["word"];
        }
        $html .= "<p><b>Game letters and tile scores</b>";

        $html .= "<br>" . $text . "<br>";

        foreach (str_split($this->search_words) as $letter) {
            $score = $this->letter_scores[strtoupper($letter)];

            if ($letter == " ") {
                $letter = "?";
            }

            $html .= $letter . "" . $score . " ";
        }

        $this->web_message = "<br>" . trim($html) . "<br>";
        $this->thing_report["web"] = $html;
    }

    function makeEmail()
    {
        $this->email_message = "WORD | ";
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $this->show_best_words = false;
        $this->show_best_score = false;

        $keywords = ["wordgame", "best"];
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "wordgame":
                            $prefix = "wordgame";
                            $words = preg_replace(
                                "/^" . preg_quote($prefix, "/") . "/",
                                "",
                                $input
                            );
                            $words = ltrim($words);

                            $text = str_replace(" ", "?", $words);
                            $this->search_words = $words;

                            $this->response = "Used provided letters.";

                        case "length":

                        default:
                    }
                }
            }
        }

        $this->nearest_word = $this->nearestWord($this->search_words);

        $status = true;

        return $status;
    }

    function contextWord()
    {
        $this->word_context = '
';

        return $this->word_context;
    }
}
