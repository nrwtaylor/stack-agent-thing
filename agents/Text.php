<?php
namespace Nrwtaylor\StackAgentThing;

// Display all errors in production.
// The site must run clean transparent code.
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

//ini_set("allow_url_fopen", 1);

// This is written to be understandable.
// Apologies.

class Text extends Agent
{
    public $var = "hello";

    public function getNgrams($input, $n = 3, $delimiter = null)
    {
        if ($delimiter == null) {$delimiter = "";}
        if (!isset($this->ngrams)) {
            $this->ngrams = [];
        }
        $words = explode(" ", $input);
        $ngrams = [];

        foreach ($words as $key => $value) {
            if ($key < count($words) - ($n - 1)) {
                $ngram = "";
                for ($i = 0; $i < $n; $i++) {
                    $ngram .= " " . $words[$key + $i] . $delimiter;
                }
                $ngrams[] = trim($this->trimAlpha($ngram));
            }
        }

        return $ngrams;
    }

public function isText($text = null) {
return is_string($text);
}

public function ngramsText($text = null, $min_gram_limit = 2, $max_gram_limit = 4, $delimiter = null)
    {
        if ($delimiter == null) {$delimiter = "";}
        // See if there is an agent with the first workd
//        $arr = explode(" ", trim($text));
        $agents = [];
$arr = [];
foreach( range($min_gram_limit, $max_gram_limit,1) as $number) {
        $bigrams = $this->getNgrams($text, $number, $delimiter);

        $arr = array_merge($arr, $bigrams);
}

        return $arr;
    }


    public function trimAlpha($text)
    {
        $letters = [];
        $new_text = "";
        $flag = false;
        foreach (range(0, mb_strlen($text)) as $i) {
            $letter = substr($text, $i, 1);
            //if (ctype_alpha($letter)) {$flag = true;}
            if (ctype_alnum($letter)) {
                $flag = true;
            }

            //if ((!ctype_alpha($letter)) and ($flag == false)) {$letter = "";}
            if (!ctype_alnum($letter) and $flag == false) {
                $letter = "";
            }

            $letters[] = $letter;
        }

        //$text = $new_text;

        $new_text = "";
        $flag = false;
        foreach (array_reverse($letters) as $i => $letter) {
            //$letter = substr($text,$i,1);
            //if (ctype_alpha($letter)) {$flag = true;}
            if (ctype_alnum($letter)) {
                $flag = true;
            }

            //if ((!ctype_alpha($letter)) and ($flag == false)) {$letter = "";}
            if (!ctype_alnum($letter) and $flag == false) {
                $letter = "";
            }

            $n = count($letters) - $i - 1;

            $letters[$n] = $letter;
        }
        $new_text = implode("", $letters);

        return $new_text;
    }

    public function init()
    {
        $this->node_list = ["start" => ["helpful", "useful"]];

        $this->thing_report["info"] = "Text did not add anything useful.";
        $this->thing_report["help"] =
            "An agent which provides search insight. Click on a button.";

        $this->thing->log("Initialized Text.", "DEBUG");
    }

    public function removeextraspacesText($text = null)
    {
        return trim(preg_replace("/\s+/", " ", $text));
    }

    public function shortenText($text = null)
    {
        if ($text == null) {
            return true;
        }
        // Start by removing square brackets each time this is called.
        // not greedy
        $shortened_text = preg_replace("/\[.*?\]/", "", $text);
        // greedy
        //$shortened_text = preg_replace('/\[.*\]/', '', $text);

        if ($shortened_text != $text) {
            return $this->removeextraspacesText($shortened_text);
        }

        // That had no effect. So try removed ( brackets next.
        // not greedy
        $shortened_text = preg_replace("/\(.*?\)/", "", $text);

        // greedy
        //$shortened_text = preg_replace('/\(.*\)/', '', $text);

        if ($shortened_text != $text) {
            return $this->removeextraspacesText($shortened_text);
        }

        // So [ and ( brackets removed.
        // Next remove non sentences ie sentences that don't start with a capital.

        $sentences = explode(".", $text);
        $shortened_text = "";
        foreach ($sentences as $i => $sentence) {
            $sentence = trim($sentence);
            if (preg_match("~^\p{Lu}~u", $sentence)) {
                $shortened_text .= $sentence . ". ";
            } else {
                //echo "\"{$string}\" does not start with uppercase.";
            }
        }

        if (trim($shortened_text) != trim($text)) {
            return $this->removeextraspacesText($shortened_text);
        }

        // So now we are left with sentences.
        // Probably.

        // If two. Remove last.
        // If three. Remove middle.
        // If more than three. Remove second to last.

        $sentences = explode(".", $text);
        // tidy up
        foreach ($sentences as $i => $sentence) {
            $sentence = trim($sentence);
            if ($sentence == "") {
                unset($sentences[$i]);
                continue;
            }
            $sentences[$i] = $sentence;
        }

        $sentences_count = count($sentences);

        if ($sentences_count == 2) {
            unset($sentences[$sentences_count - 1]);
            $shortened_text = implode(". ", $sentences);
            $shortened_text .= ".";

            if ($shortened_text != $text) {
                return $this->removeextraspacesText($shortened_text);
            }
        }

        if ($sentences_count >= 3) {
            unset($sentences[$sentences_count - 2]);
            $shortened_text = implode(". ", $sentences);
            $shortened_text .= ".";
            if ($shortened_text != $text) {
                return $this->removeextraspacesText($shortened_text);
            }
        }

        return trim($text);
    }

    function needlesText($needles, $haystack)
    {
        if (empty($needles)) {
            return false;
        }

        foreach ($needles as $needle) {
            if (strpos($haystack, $needle) == false) {
                return false;
            }
        }
        return true;
    }

    public function postfixText(
        $text = null,
        $post_fix = null,
        $allowed_length = 64,
        $part_tokens = false
    ) {
        if ($text == null) {
            return true;
        }
        $text = trim($text);
        if ($post_fix == null) {
            $post_fix = "";
        }

        $tokens = explode(" ", $text);

        $new_text = trim(
            substr($text, 0, $allowed_length - mb_strlen($post_fix))
        );

        $tokens_new = explode(" ", $new_text);
        $last_index = count($tokens_new) - 1;
        if ($tokens_new[$last_index] != $tokens[$last_index]) {
            $tokens_new[$last_index] = "";
        }
        $new_text = trim(implode(" ", $tokens_new)) . $post_fix;

        return $new_text;
    }

    function extractCodes($input = null)
    {
        if (is_array($input)) {
            return true;
        }
        $tokens = explode(
            " ",
            str_replace(
                [",", "*", "(", ")", "[", "]", "!", "&", "and", ".", "-"],
                " ",
                $input
            )
        );

        $codes = [];

        //     if (!isset($words) or count($words) == 0) {return $ngrams;}

        // Rare for a model to not have a number.
        // And if it doesn't it should be picked up as an ngram.

        foreach ($tokens as $key => $token) {
            //if(1 === preg_match('~[A-Z][0-9]~', strtolower($value))){
            //    $codes[] = $value;
            //}

            if (
                preg_match("/[A-Za-z]/", $token) &&
                preg_match("/[0-9]/", $token)
            ) {
                $codes[] = $token;
            }
        }
        $this->codes = $codes;
        return $this->codes;
    }

    function filterText($log_text, $log_includes = null, $log_excludes = null)
    {
        $response = "";
        $lines = preg_split("/<br[^>]*>/i", $log_text);

        foreach ($lines as $i => $line) {
            foreach ($log_excludes as $j => $log_exclude) {
                if (stripos($line, $log_exclude) !== false) {
                    continue 2;
                }
            }

            if (count($log_includes) == 0) {
                $response .= trim($line) . "\n";
                continue;
            }

            foreach ($log_includes as $j => $log_include) {
                if (stripos($line, $log_include) !== false) {
                    $response .= trim($line) . "\n";
                    continue 2;
                }
            }
        }
        if ($response === "") {
            return true;
        }
        return $response;
    }

    public function punctuateText($text)
    {
        $text = trim($text);
        if (substr($text, -1) == ".") {
            return $text;
        }

        if (substr($text, -1) == "!") {
            return $text;
        }
        if (substr($text, -1) == "?") {
            return $text;
        }

        $text .= ".";
        return $text;
    }

    function extractNumbers($input = null)
    {
        // Numbers as text.
        // Vs agent number.

        if (is_array($input)) {
            return true;
        }
        $tokens = explode(
            " ",
            str_replace(
                [",", "*", "(", ")", "[", "]", "!", "&", "and", ".", "-"],
                " ",
                $input
            )
        );

        $numbers = [];

        //     if (!isset($words) or count($words) == 0) {return $ngrams;}

        // Rare for a model to not have a number.
        // And if it doesn't it should be picked up as an ngram.

        foreach ($tokens as $key => $token) {
            //if(1 === preg_match('~[A-Z][0-9]~', strtolower($value))){
            //    $codes[] = $value;
            //}

            //            if (
            //                preg_match('/[A-Za-z]/', $token) &&
            //                preg_match('/[0-9]/', $token)
            //            ) {

            if (
                //                preg_match('/[0-9]/', $token)
                is_numeric($token)
            ) {
                $numbers[] = $token;
            }
        }
        $this->numbers = $numbers;
        return $this->numbers;
    }

    function extractHyphenates($input = null)
    {
        if (is_array($input)) {
            return true;
        }
        $tokens = explode(
            " ",
            str_replace(
                [",", "*", "(", ")", "[", "]", "!", "&", "and", "."],
                " ",
                $input
            )
        );
        $hyphens = [];

        //     if (!isset($words) or count($words) == 0) {return $ngrams;}

        // Rare for a model to not have a number.
        // And if it doesn't it should be picked up as an ngram.

        foreach ($tokens as $key => $token) {
            //if(1 === preg_match('~[A-Z][0-9]~', strtolower($value))){
            //    $codes[] = $value;
            //}

            //            if (
            //                preg_match('/[A-Za-z]/', $token) &&
            //                preg_match('/[0-9]/', $token)
            //            ) {

            //            if (
            //                preg_match('/[A-Za-z]/', $token) &&
            //                preg_match('/[0-9]/', $token)
            //            ) {
            //                $hyphens[] = $token;
            //            }

            if (preg_match('/^[^\W-]+-[^\W-]+$/', $token)) {
                $hyphens[] = $token;
            }
        }
        $this->hyphenates = $hyphens;
        return $this->hyphenates;
    }

    public function run()
    {
        $this->doText();
    }

    public function makeResponse()
    {
        // This is a short simple structured response.
        if (!isset($this->response)) {
            $this->response = "";
        }
        $this->response .= 'Asked about,"' . $this->subject . '"' . ". ";
    }

    public function textN3($input)
    {
        $p_array = explode(" ", $input);
        $text = "";
        foreach ($p_array as $i => $word) {
            if ($i >= 3) {
                break;
            }
            $text .= $word . " ";
        }
        $text = trim($text);
        return $text;
    }

    public function textNouns($input)
    {
        global $wp;
        if (!isset($this->thing->brilltagger_agent)) {
            $this->thing->brilltagger_agent = new Brilltagger(
                $this->thing,
                "brilltagger"
            );
        }

        $tags = $this->thing->brilltagger_agent->tag($input);

        $text = "";
        foreach ($tags as $index => $tag) {
            if (is_numeric($tag["token"])) {
                continue;
            }

            if (1 === preg_match("~[0-9]~", $tag["token"])) {
                continue;
            }
            $token = $tag["token"];

            // False. Is not a word.
            //$nearest_word = $word_agent->isWord($token);

            if (strpos($tag["tag"], "VB") !== false) {
                $text .= $tag["token"] . " ";
                continue;
            }

            if (strpos($tag["tag"], "JJ") !== false) {
                $text .= $tag["token"] . " ";
                continue;
            }

            if (strpos($tag["tag"], "NN") !== false) {
                $text .= $tag["token"] . " ";
                continue;
            }
        }

        $text = trim($text);
        $this->thing->log(
            'text adjectives and nouns built query, "' . $text . '".'
        );
        return $text;
    }

    function textOr($input)
    {
        $text = "(" . trim($input) . ")";
        $text = str_replace(" ", ",", $text);

        $text = trim($text);
        // Any words
        return $text;
    }

    public function compressText($text1, $text2)
    {
        $raw = $text1 . " " . $text2;
        $raw = strtolower($raw);
        $filtered = implode(" ", array_unique(explode(" ", $raw)));
        return $filtered;
    }

    function textNgram($input, $t = "@1")
    {
        $text = "(" . trim($input) . ")";
        $text = str_replace(" ", ",", $text);

        $text = $t . " " . trim($text);
        // Any words
        return $text;
    }

    public function posText($text = null, $pattern = "mixed-adjective")
    {
        // dev stack

        if ($text == null) {
            return true;
        }

        $processed_text = $this->tagText($text);
        $pattern_tokens = explode("-", $pattern);
        $process_text_tokens = explode("-", $pattern);

        foreach ($pattern_tokens as $i => $pattern_token) {
        }
    }

    public function tagText($text = null)
    {
        global $wp;
        if ($text == null) {
            return false;
        }
        if (!isset($this->thing->brilltagger_agent)) {
            $this->thing->brilltagger_agent = new Brilltagger(
                null,
                "brilltagger"
            );
        }

        if (!isset($this->thing->mixed_agent)) {
            $this->thing->mixed_agent = new _Mixed(null, "brilltagger");
        }

        if (!isset($this->thing->alpha_agent)) {
            $this->thing->alpha_agent = new Alpha(null, "alpha");
        }

        $tags = $this->thing->brilltagger_agent->tag($text);

        // --- now it gets tricky.
        // https://cs.uwaterloo.ca/~jimmylin/downloads/brill-javadoc/edu/mit/csail/brill/BrillTagger.html

        $arr = [
            "adjective" => ["JJ", "JJR", "JJS"],
            "noun" => ["NN", "NNS", "NNP", "NNPS"],
            "pronoun" => ["PRP", "PRPS", "WP"],
            "verb" => ["VB", "VBD", "VBG", "VBN", "VBP", "VBZ"],
            "adverb" => ["RB", "RBR", "RBS", "WRB"],
            "preposition" => ["IN"],
        ];

        //$pattern_tokens = explode("-", $pattern);

        $processed_text = "";
        foreach ($tags as $i => $token_tag) {
            $tag = $tags[$i]["tag"];
            $token = $tags[$i]["token"];

            switch (true) {
                case $this->thing->mixed_agent->isMixed($token):
                    $tags[$i]["pos"] = "mixed";
                    break;
                case is_numeric($token):
                    $tags[$i]["pos"] = "numeric";
                    break;
                case $this->thing->alpha_agent->isAlpha($token):
                    if (in_array($tag, $arr["adjective"])) {
                        $tags[$i]["pos"] = "adjective";
                    } else {
                        $tags[$i]["pos"] = "alpha";
                    }

                    break;
                case !isset($tags[$i]["pos"]):
                default:
                    $tags[$i]["pos"] = "X";
                    break;
            }

            $processed_text .= "-" . $tags[$i]["pos"];
        }
        $processed_text = trim($processed_text, "-");

        return $processed_text;
    }

    public function make()
    {
    }

    public function doText($text = null)
    {
    }

    public function set()
    {
        // Log which agent was requested ie Ebay.
        // And note the time.
        $time_string = $this->thing->time();
        $this->thing->Write(["text", "refreshed_at"], $time_string);

        /// ?
        //$place_agent thing = new Place($this->thing, $ngram);

        $this->thing->log("Set text refreshed_at.");
    }

    public function readTitle($post_title)
    {
        global $wp;

        $codes = $this->extractCodes($post_title);
        $numbers = $this->extractNumbers($post_title);
        $hyphenates = $this->thing->text_agent->extractHyphenates($post_title);

        $alpha_agent = new Alpha($this->thing, "alpha");
        $mixed_agent = new _Mixed($this->thing, "mixed");
        $word_agent = new Word($this->thing, "word");
        $brilltagger_agent = new Brilltagger($this->thing, "brilltagger");
        $slug_agent = new Slug($this->thing, "slug");
        $singular_agent = new Singular($this->thing, "singular");

        $alphas = $alpha_agent->extractAlphas($post_title);
        $mixeds = $mixed_agent->extractMixeds($post_title);

        $words = $word_agent->extractWords($post_title);
        $notwords = $word_agent->notwords;

        $t = "";

        $tags = $brilltagger_agent->tag($post_title);

        $tokens = $slug_agent->getSlug($post_title);
        $p = "";
        foreach ($tags as $i => $tag) {
            $token = $tag["token"];

            if (strpos($tag["tag"], "NNS") !== false) {
                $token = $singular_agent->singularize($token);
            }

            $p .= $token . " ";
        }

        $p = trim($p);
        $post_title = $p;

        $adjectives = "";
        $nouns = "";

        foreach ($tags as $i => $tag) {
            if ($tag["tag"] == "JJ") {
                $adjectives .= $tag["token"] . " ";
            }

            if (strpos($tag["tag"], "NN") !== false) {
                $nouns .= $tag["token"] . " ";
            }
        }

        $adjectives = trim($adjectives);
        $nouns = trim($nouns);

        $processed_text = [
            "adjectives" => $adjectives,
            "nouns" => $nouns,
            "codes" => $codes,
            "alphas" => $alphas,
            "mixed" => $mixeds,
            "words" => $words,
            "notwords" => $notwords,
        ];
    }

    public function hasText($haystack = null, $needle = null)
    {
        // https://stackoverflow.com/questions/4366730/how-do-i-check-if-a-string-contains-a-specific-word
        // a classic

        if (stripos($haystack, $needle) !== false) {
            return true;
        }
        return false;
    }

    public function readSubject()
    {
        if ($this->input == "text") {
            return;
        }
    }
}
