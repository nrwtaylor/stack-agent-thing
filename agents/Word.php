<?php
/**
 * Word.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Word extends Agent
{
    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->resource_path_words =
            $this->resource_path . "words/";


        $this->resource_path_ewol = $this->resource_path . "ewol/";
        $this->keywords = [];

        $this->wordpress_path_to = false;

        if (isset($this->thing->container["api"]["wordpress"]["path_to"])) {
            $this->wordpress_path_to =
                $this->thing->container["api"]["wordpress"]["path_to"];
        }

        $this->thing_report["help"] =
            "Screens against a list of over four hundred thousand words.";
        $this->getMemcached();
        $this->initWords();
    }

    public function initWords() {
       $this->thing->log("init words start.");
       if ($this->getMemory('words-words') !== true) {
           $this->thing->log("load words words.");
           $this->loadDictionary('words/words');
           $this->setMemory('words-words',true); 
       }

       if ($this->getMemory('words-offensive') !== true) {
           $this->thing->log("load words/offensive.");
           $this->loadDictionary('offensive/bad-words');
           $this->setMemory('words-offensive',true); 
       }
       if ($this->getMemory('words-eowl') !== true) {
        $this->thing->log("load words/eowl.");
        foreach (range("A", "Z") as $v) {
            $resource = 'ewol/' . $v . " Words";
           $this->loadDictionary($resource);
        }

        $this->setMemory('words-eowl',true); 
       }
       $this->thing->log("init words complete.");

    }

    function set()
    {
        $this->reading = null;
        if (isset($this->words)) {
            $this->reading = count($this->words);
        }

        $this->thing->json->writeVariable(["word", "reading"], $this->reading);

        if ((isset($this->words) and count($this->words)) != 0) {
            $this->thing->log(
                $this->agent_prefix .
                    "completed with a reading of " .
                    $this->word .
                    "."
            );
        } else {
            $this->thing->log($this->agent_prefix . "did not find words.");
        }
    }

    function get()
    {
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
        $this->reading = $this->thing->json->readVariable(["word", "reading"]);
    }

    /**
     *
     * @param unknown $test
     * @return unknown
     */
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
        //
        return $new_words;
    }

    public function imageWord($text = null)
    {
        if (isset($this->canvas_size_x)) {
            $canvas_size_x = $this->canvas_size_x;
            $canvas_size_y = $this->canvas_size_x;
        } else {
            $canvas_size_x = 164;
            $canvas_size_y = 164;
        }

        $image = imagecreatetruecolor($canvas_size_x, $canvas_size_y);

        $this->white = imagecolorallocate($image, 255, 255, 255);
        $this->black = imagecolorallocate($image, 0, 0, 0);

        imagecolortransparent($image, $this->white);

        imagefilledrectangle(
            $image,
            0,
            0,
            $canvas_size_x,
            $canvas_size_y,
            $this->white
        );

        $textcolor = imagecolorallocate($image, 0, 0, 0);

        // Write the string at the top left
        $border = 30;
        $r = 1.165;

        $radius = ($r * ($canvas_size_x - 2 * $border)) / 3;

        // devstack add path
        $font = $this->default_font;

        $size = 26;

        $angle = 0;

        //check width of the image
        $width = imagesx($image);
        $height = imagesy($image);

        //        if (file_exists($font)) {
        $bbox = imagettfbbox($size, $angle, $font, $text);
        $bbox["left"] = 0 - min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
        $bbox["top"] = 0 - min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
        $bbox["width"] =
            max($bbox[0], $bbox[2], $bbox[4], $bbox[6]) -
            min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
        $bbox["height"] =
            max($bbox[1], $bbox[3], $bbox[5], $bbox[7]) -
            min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
        extract($bbox, EXTR_PREFIX_ALL, "bb");

        //check width of the image
        $pad = 0;
        imagettftext(
            $image,
            $size,
            $angle,
            $width / 2 - $bb_width / 2,
            $height / 2 + $bb_height / 2,
            $grey,
            $font,
            $text
        );
        //        }

        if (ob_get_contents()) {
            ob_clean();
        }

        ob_start();
        imagepng($image);
        $imagedata = ob_get_contents();

        ob_end_clean();
        $PNG_embed = "data:image/png;base64," . base64_encode($imagedata);
        return $PNG_embed;
    }

    /**
     *
     * @param unknown $string
     * @return unknown
     */
    function extractWords($string)
    {
        if ($string == "") {
            $this->words = [];
            return $this->words;
        }
        $this->thing->log('called extractWords on "' . $string . '".', "DEBUG");

        $string = strtolower($string);
        preg_match_all(
            '/([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC5[\x92\x93\xA0\xA1\xB8\xBD\xBE]){2,}/',
            $string,
            $words
        );
        //print_r($emojis[0]); // Array ( [0] => 😃 [1] => 🙃 )
        $w = $words[0];

        $this->notwords = [];
        $this->words = [];
        foreach ($w as $key => $value) {
            // Return dictionary entry.
            $value = $this->stripPunctuation($value);

            $text = $this->findWord("list", $value);

            if ($text != false) {
                $this->words[] = $text;
            } else {
                $this->notwords[] = $value;
            }
        }

        if (count($this->words) != 0) {
            $this->word = $this->words[0];
        } else {
            $this->word = null;
        }

        $this->thing->log("extracted words.");

        return $this->words;
    }

    /**
     *
     * @return unknown
     */
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

    /**
     *
     */
    function ewolWords()
    {
        if (isset($this->ewol_dictionary)) {
            $contents = $this->ewol_dictionary;
            return;
        }

        $this->thing->log("ewolWords.");

        if (
            $this->ewol_dictionary = $this->getMemory("agent-ewol-dictionary")
        ) {
            $this->thing->log("loaded ewol dictionary from memory.");
            return;
        }

        $contents = "";
        foreach (range("A", "Z") as $v) {
            $file = $this->resource_path_ewol . $v . " Words.txt";
            $c = false;
            if (file_exists($file)) {
                $c = @file_get_contents($file);
            }
            if ($c === false) {
                if (!isset($this->error)) {$this->error = "";}
                $this->error .= "Missing ewol file. ";
                return true;
            }
            $contents .= $c;
        }

        $arr = explode("\n", $contents);
        foreach ($arr as $key => $line) {
            if (mb_strlen($line) <= 1) {
                continue;
            }
            $this->ewol_dictionary[$line] = true;
        }
        if ($this->wordpress_path_to !== false) {
            $this->mem_cached->set(
                "agent-ewol-dictionary",
                $this->ewol_dictionary
            );
        }
    }

    /**
     *
     * @param unknown $librex
     * @param unknown $searchfor
     * @return unknown
     */
    function findWord($librex, $searchfor)
    {
        if ($librex == "" or $librex == " " or $librex == null) {
            return false;
        }
        switch ($librex) {
            case null:
            // Drop through
            case "list":
                if (ctype_alpha($searchfor)) {
                    $s = strtolower($searchfor);
                    $value = $this->mem_cached->get(
                        "agent-words-stack-" . strtolower($s)
                    );
                    if ($value == true) {
                        $this->thing->log("Found word in words-stack.");
                        return $searchfor;
                    }
                }

                if (isset($this->words_list)) {
                    $contents = $this->words_list;
                    break;
                }
                $file = $this->resource_path_words . "words.txt";

                if ($contents = $this->mem_cached->get("agent-words-list")) {
                    $this->words_list = $contents;
                    $this->thing->log("loaded words from memory.");

                    break;
                }

                $this->thing->log("caught exception");

                $contents = false;
                if (file_exists($file)) {
                    $contents = file_get_contents($file);
                }
                $this->mem_cached->set("agent-words-list", $contents);

                $this->thing->log("loaded words from text file.");

                $this->words_list = $contents;
                break;
            case "ewol":
                $searchfor = strtolower($searchfor);
                if (isset($this->ewol_list)) {
                    $contents = $this->ewol_list;
                    break;
                }
                $contents = "";
                foreach (range("A", "Z") as $v) {
                    $file = $this->resource_path_ewol . $v . " Words.txt";
                    if (file_exists($file)) {
                        $contents .= file_get_contents($file);
                    }
                }
                $this->ewol_list = $contents;
                break;

            case "mordok":
                if (isset($this->mordok_list)) {
                    $contents = $this->mordok_list;
                    break;
                }

                $file = $this->resource_path_words . "mordok.txt";
                $contents = "";
                if ($file_exists($file)) {
                    $contents = file_get_contents($file);
                }
                $this->mordok_list = $contents;
                break;
            case "context":
                if (isset($this->context_list)) {
                    $contents = $this->context_list;
                    break;
                }

                $this->contextWord();
                $contents = $this->word_context;
                $file = null;
                $this->context_list = $contents;
                break;

            case "emotion":
                break;
            default:
                $file = $this->resource_path_words . "words.txt";
        }
        $pattern = "|\b($searchfor)\b|";
        // search, and store all matching occurences in $matches
        if (preg_match_all($pattern, $contents, $matches)) {
            $m = $matches[0][0];
            if (ctype_alpha($m)) {
                $this->mem_cached->set(
                    "agent-words-stack-" . strtolower($m),
                    true
                );
            }

            return $m;
        } else {
            return false;
        }

        return;
    }

    /**
     *
     * @param unknown $number (optional)
     */
    function randomWord($number = null, $length = null)
    {
        if (!isset($this->ewol_dictionary)) {
            $this->ewolWords();
        }

        if ($number === null or $number === false) {
            $min_number = 3;
            $max_number = $number;
            if ($number == false) {
                $max_number = 7;
            }
            if ($number == null) {
                $max_number = 7;
            }
        } else {
            $min_number = $length;
            $max_number = $length;
        }

        // TODO recognize false ewol_dictionary
        // Review isset below.
        $word = true;
        if ($this->ewol_dictionary !== false) {
            $start_time = time();
            while (true) {
                $this->ewolWords();

                $word = array_rand($this->ewol_dictionary);
                if (
                    strlen($word) >= $min_number and
                    strlen($word) <= $max_number
                ) {
                    break;
                }
                if (time() - $start_time > 2) {
                    break;
                }
            }
        }
        $this->word = $word;
        return $word;
    }

    public function getContents()
    {
        if (isset($this->contents)) {
            return $this->contents;
        }

        if ($this->contents = $this->mem_cached->get("agent-word-contents")) {
            return $this->contents;
        }

        $file = $this->resource_path_words . "words.txt";
        $contents = "";
        if (file_exists($file)) {
            $contents = file_get_contents($file);
        }

        $this->contents = $contents;

        $this->mem_cached->set("agent-word-contents", $this->contents);
    }

    function isWord($input)
    {
        $value = $this->stripPunctuation($input);

        $text = $this->findWord("list", $value);

        if ($text != false) {
            return true;
        }

        $text = $this->findWord("list", strtoupper($value));

        if ($text != false) {
            return true;
        }

        $text = $this->findWord("list", ucwords($value));

        if ($text != false) {
            return true;
        }

        return false;

        if (!isset($this->contents)) {
            $file = $this->resource_path_words . "words.txt";

            $contents = "";
            if (file_exists($file)) {
                $contents = file_get_contents($file);
            }

            $this->contents = $contents;
        }

        $pattern = "|\b(" . $input . ")\b|";

        // search, and store all matching occurences in $matches

        // Suppress warning of preg_match fail.
        if (preg_match_all($pattern, $this->contents, $matches)) {
            $m = $matches[0][0];
            return true;
            return $m;
        } else {
            return false;
        }

        return;

        $words = explode("\n", $this->contents);

        $input = str_replace(["\r", "\n"], "", $input);

        foreach ($words as $key => $word) {

            $word = str_replace(["\r", "\n"], "", $word);

            if (strcasecmp($input, $word) == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function nearestWord($input)
    {
        $file = $this->resource_path_words . "words.txt";

        $contents = "";
        if (file_exists($file)) {
            $contents = file_get_contents($file);
        }

        $words = explode("\n", $contents);

        $nearness_min = 1e6;
        $word = false;

        foreach ($words as $key => $word) {
            $nearness = levenshtein($input, $word);

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
            $nearness = similar_text($word, $input);

            if ($nearness > $nearness_max) {
                $new_word_list = [];
                $nearness_min = $nearness;
            }
            if ($nearness_min == $nearness) {
                $new_word_list[] = $word;
            }
        }

        if (!isset($new_word_list) or $new_word_list == null) {
            $nearest_word = false;
        } else {
            $nearest_word = implode(" ", $new_word_list);
        }

        return $nearest_word;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->cost = 100;

        // Thing stuff
        $this->thing->flagGreen();

        // Make SMS
        $this->thing_report["sms"] = $this->sms_message;

        // Make message
        $this->thing_report["message"] = $this->sms_message;


        $this->thing_report["email"] = $this->sms_message;
        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

    }

    /**
     *
     */
    function makeSMS()
    {
        if (isset($this->words)) {
            if (count($this->words) == 0) {
                if (isset($this->nearest_word)) {
                    $this->sms_message =
                        "WORD | closest match " . $this->nearest_word;
                } else {
                    $this->sms_message = "WORD | no words found";
                }

                return;
            }

            if ($this->words[0] == false) {
                if (isset($this->nearest_word)) {
                    $this->sms_message =
                        "WORD | closest match " . $this->nearest_word;
                } else {
                    $this->sms_message = "WORD | no words found";
                }
                return;
            }

            if (count($this->words) > 1) {
                $this->sms_message = "WORDS ARE ";
            } elseif (count($this->words) == 1) {
                $this->sms_message = "WORD IS ";
            }
            $this->sms_message .= implode(" ", $this->words);
            return;
        }

        $this->sms_message = "WORD | no match found";
    }

    /**
     *
     */
    function makeEmail()
    {
        $this->email_message = "WORD | ";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = $this->assert($this->input, "word", false);

        $keywords = ["word", "random"];
        $pieces = explode(" ", strtolower($input));
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "random":
                            // Ignore 'random' if it comes after the word command.
                            $random_word_position = stripos(
                                $this->input,
                                "random"
                            );
                            $word_position = stripos($this->input, "word");
                            if ($random_word_position > $word_position) {
                                $this->score = strlen("word random");

                                break;
                            }

                            $number_agent = new Number($this->thing, "number");
                            $number_agent->extractNumber($input);

                            $length = $number_agent->number;
                            $this->score = strlen(
                                "word random " . $number_agent->number
                            );

                            $word = $this->randomWord(
                                $length
                            );

                            $this->words[0] = $word;
                            if ($this->word != null) {
                                return;
                            }
                        default:

                    }
                }
            }
        }
        $words = $input;
        $this->search_words = $words;
        $this->extractWords($words);


        if ($this->word != null) {
            return;
        }

        if (isset($this->search_words)) {
            $this->nearest_word = $this->nearestWord($this->search_words);
            $status = true;

            return $status;
        }
    }

    /**
     *
     * @return unknown
     */
    function contextWord()
    {
        $this->word_context = '
';

        return $this->word_context;
    }
}
