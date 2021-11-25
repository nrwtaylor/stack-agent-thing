<?php
/**
 * Chinese.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

// An agent to recognize and understand Chinese characters.

class Chinese extends Agent
{
    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->keyword = "chinese";

        // devstack

        //$this->initMemcached();
        $this->memcachedAgent();
        $this->words = [];
    }

    public function doChinese($text = null)
    {
        if ($text == null) {
            return true;
        }
        $string = $text;
        $chineses = $this->extractChinese($string);

        $this->getChinese();

        $searchfor = $this->convert_chinese($this->chinese);
        $arr = explode(" ", $searchfor);
        $this->words = [];
        $this->word = null;

        foreach ($arr as $key => $value) {
            if ($value == "U+FE0F") {
                continue;
            }
            // Return dictionary entry.
            $text = $this->findChinese("list", $value);
            $words = $this->conceptChinese($text);
            if ($words != false) {
                $this->words = array_merge($this->conceptChinese($text));

                if (isset($this->words[0])) {
                    $this->word = $this->words[0];
                }
            }
        }

        $this->keywords = [];

        foreach ($arr as $key => $value) {
            $text = $this->findChinese("mordok", $value);
            if ($value == "U+FE0F") {
                continue;
            }

            $words = $this->conceptChinese($text);

            if ($words != false) {
                $this->keywords = array_merge($this->conceptChinese($text));
                if (isset($this->keywords[0])) {
                    $this->keyword = $this->keywords[0];
                }
            }
        }
    }

    /**
     *
     */
    function get()
    {
        $time_string = $this->thing->Read(["chinese", "refreshed_at"]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(["chinese", "refreshed_at"], $time_string);
        }

        // If it has already been processed ...
        $this->reading = $this->thing->Read(["chinese", "reading"]);
    }

    /**
     *
     */
    function set()
    {
        if (!isset($this->chinese)) {
            return true;
        }

        $this->reading = $this->chinese;

        $this->thing->Write(["chinese", "reading"], $this->reading);
    }

    /**
     *
     */
    function run()
    {
    }

    /**
     *
     */
    function thingreportChinese()
    {
        $this->thing_report["log"] = $this->thing->log;
    }

    /**
     *
     * @param unknown $text     (optional)
     * @param unknown $logogram (optional)
     * @return unknown
     */
    function getWord($text = null, $logogram = null)
    {
        $logogram = trim($logogram);
        //if ($concept == null) {return;}
        $this->thing->log("logo gram " . $logogram . "\n");
        $separator = "\r\n";
        $line = strtok($text, $separator);

        $maximum_word_length = 0;

        while ($line !== false) {
            // do something with $line

            $word = $this->conceptChinese($line);

            // Look for the shortest matching logogram sequences
            // Or for an exact match.
            $word_length = mb_strlen($word["traditional"]);

            if (
                strcasecmp($word["traditional"], $logogram) == 0 or
                strcasecmp($word["simplified"], $logogram) == 0
            ) {
                if ($word_length > $maximum_word_length) {
                    $match = [];
                    $maximum_word_length = $word_length;
                }

                if ($word_length = $maximum_word_length) {
                    $match[] = $word;
                }
            }
            $line = strtok($separator);
        }

        if (!isset($match)) {
            return true;
        }

        $description = "";
        $shortest_concept_length = 1e6;

        $best_concept = "";
        $best_concept_length = 1e6;
        $best_concept_num_words = 1e6;
        if (!isset($match)) {
            return true;
        }
        foreach ($match as $i => $word) {
            $description = "";
            foreach ($word["english"] as $j => $concept) {
                // Use only the first three matching english concepts.
                if ($j >= 2) {
                    break;
                }
                $description .= " / " . $concept;

                //                    if ((mb_strlen($concept) < $shortest_concept_length) and (mb_strlen($concept) != 0)) {
                if (mb_strlen($concept) != 0) {
                    $words = explode(" ", $concept);
                    $num_words = count($words);
                    $concept_length = mb_strlen($concept);

                    // Get longest word if only one word available.
                    // As proxy for concept complexity.
                    if ($num_words == 1 and $best_concept_num_words == 1) {
                        if (mb_strlen($concept) > mb_strlen($best_concept)) {
                            $best_concept = $concept;
                            $best_concept_num_words = 1;
                            $best_concept_length = mb_strlen($concept);
                        }
                    } else {
                        if ($best_concept_length > $concept_length) {
                            $best_concept = $concept;
                            $best_concept_num_words = $num_words;
                            $best_concept_length = $concept_length;
                        }
                    }
                }
            }
        }
        $this->thing->log("best concept " . $best_concept);
        return $best_concept;
    }

    /**
     *
     */
    function chineseThing()
    {
        // Get all of this users Things
        // To search for the last Chinese text provided.
        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(""); // Designed to accept null as $this->uuid.

        $things = $thingreport["thing"];

        // Get the earliest from the current data set
        foreach (array_reverse($things) as $thing) {
            $this->extractChinese($thing["task"]);
            if ($this->chineses != []) {
                break;
            }
        }
    }

    /**
     *
     */
    function dictionaryChinese()
    {
        // Makes a one character dictionary

        $file = $this->resource_path . "chinese/cedict_1_0_ts_utf-8_mdbg.txt";

        $contents = "";
        if (file_exists($file)) {
            $contents = file_get_contents($file);
        }

        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            $word = $this->conceptChinese($line);

            if (mb_strlen($word["traditional"]) == 1) {
                //$dictionary_entry = $word['traditional'] . " " . $word['simplified'] . " " . $word['pin_yin'] . implode("/",$word['english']) . "\n";
                $dictionary[$word["traditional"]] = $line . "\n";
            }
            // do something with $line
            $line = strtok($separator);
        }

        $file = fopen(
            $this->resource_path . "chinese/chinese_mordok_new.txt",
            "w"
        );
        foreach ($dictionary as $character => $line) {
            fwrite($file, $line);
        }
    }

    /**
     *
     * @param unknown $test
     * @return unknown
     */
    function conceptChinese($test)
    {
        // Take a CE-CCEDICT line and de-parse it
        // Traditional Simplified [pin1 yin1] /English equivalent 1/equivalent 2/

        if ($test == false) {
            return false;
        }

        if (mb_substr($test, 0, 1) == "#") {
            $word = false;
            return $word;
        }

        $dict = explode("/", $test);

        if (!isset($dict[0])) {
            return true;
        }

        if (!isset($dict[1]) or !isset($dict[2])) {
        }

        foreach ($dict as $index => $phrase) {
            if ($index == 0) {
                continue;
            }
            if ($phrase == "") {
                continue;
            }
            $english_phrases[] = $phrase;
        }

        if (!isset($english_phrases)) {
            $english_phrases = ["X"];
        }

        $text = $dict[0];
        preg_match_all("/\[([^\]]*)\]/", $text, $matches);
        $pin_yin = "X";
        if (isset($matches[0][0])) {
            $pin_yin = $matches[0][0];
        }
        $dict = explode(" ", $text);

        $traditional = "X";
        if (isset($dict[0])) {
            $traditional = $dict[0];
        }

        $simplified = "X";
        if (isset($dict[1])) {
            $simplified = $dict[1];
        }
        $word = [
            "traditional" => $traditional,
            "simplified" => $simplified,
            "pin_yin" => $pin_yin,
            "english" => $english_phrases,
        ];

        return $word;
    }

    /**
     *
     * @param unknown $string
     * @return unknown
     */
    function extractChinese($string)
    {
        //https://stackoverflow.com/questions/17944961/php-separate-chinese-from-english-characters
        //$str = 'Hello 你怎么样？ How are you?';

        $english = preg_replace(
            ["/[\p{Han}？]/u", "/(\s)+/"],
            ["", '$1'],
            $string
        );
        $chinese = preg_replace(
            ["/[^\p{Han}？]/u", "/(\s)+/"],
            ["", '$1'],
            $string
        );
        $this->chinese_text = $chinese;
        $this->thing->log(
            "reads english " . $english . " and chinese " . $chinese
        );

        //https://stackoverflow.com/questions/1396434/what-is-the-best-way-to-split-a-string-into-an-array-of-unicode-characters-in-ph
        $this->chineses = preg_split("//u", $chinese, -1, PREG_SPLIT_NO_EMPTY);

        return $this->chineses;
    }

    /**
     *
     * @param unknown $string
     * @return unknown
     */
    function isChinese($string)
    {
        // Are there chinese (Han) characters in the string.

        //https://stackoverflow.com/questions/17944961/php-separate-chinese-from-english-characters
        //$str = 'Hello 你怎么样？ How are you?';
        $chinese = preg_replace(
            ["/[^\p{Han}？]/u", "/(\s)+/"],
            ["", '$1'],
            $string
        );
        if ($chinese == "") {
            return false;
        }
        return true;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function wordsChinese($input)
    {
        $translation = "";

        //$input = "短信机";
        //throw new Exception('Devstack.')
        $string_length = mb_strlen($input);
        $pointer = 0;
        $window = 1;
        // https://stackoverflow.com/questions/4601032/php-iterate-on-string-characters
        //$characters = mb_str_split($input);

        $characters = preg_split("//u", $input, null, PREG_SPLIT_NO_EMPTY);

        $logogram_sequence_length = mb_strlen(implode("", $characters));
        $end_flag = false;
        while ($pointer !== $string_length) {
            if ($end_flag) {
                break;
            }
            $character = $characters[$pointer];

            // Process phrase seperators
            if ($character == "，") {
                $pointer += 1;
                $translation .= ", ";
                continue;
            }
            if ($character == "　") {
                $pointer += 1;
                $translation .= " ";
                continue;
            }
            if ($character == "。") {
                $pointer += 1;
                $translation .= ". ";
                continue;
            }
            if ($character == " ") {
                $pointer += 1;
                $translation .= " ";
                continue;
            }

            if (
                $character ==
                '
'
            ) {
                $pointer += 1;
                continue;
            }

            $character_string = "";
            $test_character_string = "";
            $text = "";
            $match_flag = false;

            foreach ([0, 1, 2, 3, 4, 5, 6] as $index => $value) {
                if ($pointer + $value >= $logogram_sequence_length) {
                    $end_flag = true;
                    break;
                }

                $test_character_string .= $characters[$pointer + $value];

                if ($this->isChinese($test_character_string) == false) {
                    $character_string = $test_character_string;
                    $pointer += $value + 1;
                    break;
                }

                // Devstack
                // Next line testing at 22ms.
                $text_temp = $this->findChinese("list", $test_character_string);

                if ($text_temp == false) {
                    $pointer += $value;
                    break;
                }

                $match_flag = true;
                //if ($match_flag = false) {break;}
                $text = $text_temp;
                $character_string = $test_character_string;

                if ($value == 6) {
                    $pointer += 1;
                }
            }
            $english_word = $this->getWord($text, $character_string);
            if ($english_word === true) {
                //true I guess
                $translation .= $character_string;
            } else {
                $translation .= $english_word . " ";
            }
        }
        return $translation;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function getChinese($input = null)
    {
        // Get an array with all the Chinese character words.
        if ($input == null) {
            $input = $this->subject;
        }
        if (!isset($this->chineses)) {
            $this->extractChinese($this->subject);
        }

        if (count($this->chineses) == 0) {
            $this->chinese = false;
            return false;
        }
        $this->chinese = $this->chineses[0];

        return $this->chinese;
    }

    /**
     *
     * @param unknown $chinese
     * @return unknown
     */
    function convertChinese($chinese)
    {
        // Convert Chinese encoding to UTF-8
        $str = str_replace('"', "", json_encode($chinese, JSON_HEX_APOS));

        $myInput = $str;

        $myHexString = str_replace("\\u", "", $myInput);
        $myBinString = hex2bin($myHexString);

        return iconv("UTF-16BE", "UTF-8", $myBinString);
    }

    /**
     *
     * @param unknown $num
     * @return unknown
     */
    function utf8($num)
    {
        // More UTF nonsense.
        if ($num <= 0x7f) {
            return chr($num);
        }
        if ($num <= 0x7ff) {
            return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
        }
        if ($num <= 0xffff) {
            return chr(($num >> 12) + 224) .
                chr((($num >> 6) & 63) + 128) .
                chr(($num & 63) + 128);
        }
        if ($num <= 0x1fffff) {
            return chr(($num >> 18) + 240) .
                chr((($num >> 12) & 63) + 128) .
                chr((($num >> 6) & 63) + 128) .
                chr(($num & 63) + 128);
        }
        return "";
    }

    /**
     *
     * @param unknown $c
     * @return unknown
     */
    function uniord($c)
    {
        // And back again.
        $ord0 = ord($c[0] ?? "default value");
        if ($ord0 >= 0 && $ord0 <= 127) {
            return $ord0;
        }
        $ord1 = ord($c[1] ?? "default value");
        if ($ord0 >= 192 && $ord0 <= 223) {
            return ($ord0 - 192) * 64 + ($ord1 - 128);
        }
        $ord2 = ord($c[2] ?? "default value");
        if ($ord0 >= 224 && $ord0 <= 239) {
            return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
        }
        $ord3 = ord($c[3] ?? "default value");
        if ($ord0 >= 240 && $ord0 <= 247) {
            return ($ord0 - 240) * 262144 +
                ($ord1 - 128) * 4096 +
                ($ord2 - 128) * 64 +
                ($ord3 - 128);
        }
        return false;
    }

    /**
     *
     * @param unknown $chinese
     * @return unknown
     */
    function convert_chinese($chinese)
    {
        $u = $this->uniord($chinese);
        return strtoupper("U+" . dechex($u));
    }

    /**
     *
     * @param unknown $str
     * @return unknown
     */
    function format($str)
    {
        $copy = false;
        $len = strlen($str);
        $res = "";

        for ($i = 0; $i < $len; ++$i) {
            $ch = $str[$i];

            if (!$copy) {
                if ($ch != "0") {
                    $copy = true;
                }
                // Prevent format("0") from returning ""
                elseif ($i + 1 == $len) {
                    $res = "0";
                }
            }

            if ($copy) {
                $res .= $ch;
            }
        }
        return "U+" . strtoupper($res);
    }

    /**
     *
     * @param unknown $librex
     * @param unknown $searchfor
     * @return unknown
     */
    public function contentsChinese($librex = null)
    {
        // Look up the meaning in the dictionary.
        if ($librex == "" or $librex == " " or $librex == null) {
            return false;
        }

        // Already loaded by this thing?
        if (isset($this->contents[$librex])) {
            return $this->contents[$librex];
        }

        // Avalable via memcache
        $contents = $this->mem_cached->get(
            "agent-chinese-contents-" . strtolower($librex)
        );

        if ($contents != false) {
            $this->contents[$librex] = $contents;
            return $this->contents[$librex];
        }

        switch ($librex) {
            case null:
            // Drop through
            case "keywords":
                $file = $this->resource_path . "chinese/chinese-keywords.txt";
                $contents = "";
                if (file_exists($file)) {
                    $contents = file_get_contents($file);
                }
                break;
            case "agent":
                $file = $this->resource_path . "chinese/chinese-agent.txt";
                $contents = "";
                if (file_exists($file)) {
                    $contents = file_get_contents($file);
                }

                break;
            case "list":
                $file =
                    $this->resource_path .
                    "chinese/cedict_1_0_ts_utf-8_mdbg.txt";
                $contents = "";
                if (file_exists($file)) {
                    $contents = file_get_contents($file);
                }

                break;

            case "english-chinese":
                $file =
                    $this->resource_path .
                    "chinese/cedict_1_0_ts_utf-8_mdbg.txt";
                $contents = "";
                if (file_exists($file)) {
                    $contents = file_get_contents($file);
                }

                break;

            case "unicode":
                $file = $this->resource_path . "chinese/unicode.txt";
                $contents = "";
                if (file_exists($file)) {
                    $contents = file_get_contents($file);
                }

                break;
            case "context":
                $this->contextChinese();
                $contents = $this->chinese_context;
                $file = null;
                break;
            case "emotion":
                break;
            default:
                $file =
                    $this->resource_path .
                    "chinese/cedict_1_0_ts_utf-8_mdbg.txt";
        }

        $this->contents[$librex] = $contents;

        $this->mem_cached->set(
            "agent-chinese-contents-" . strtolower($librex),
            $this->contents[$librex]
        );
    }

    public function findChinese($librex, $searchfor, $return_array_flag = false)
    {
        if (!isset($this->contents[$librex])) {
            $this->contentsChinese($librex);
        }

        // factor out
        $contents = $this->contents[$librex];

        if (!isset($contents) or $contents == false) {
            $this->matches = [];
            return true;
        }

        $this->thing->log("searchfor " . $searchfor);
        // devstack add \b to Word
        $pattern = preg_quote($searchfor, "/");
        // finalise the regular expression, matching the whole line
        $pattern = "/^.*" . $pattern . ".*\$/m";

        if ($librex == "mordok") {
            $pattern = preg_quote($searchfor . " ", "/");
            // finalise the regular expression, matching the whole line
            $pattern = "/^.*" . $pattern . ".*\$/m";
        }

        if ($librex == "english-chinese") {
            $pattern = "\b" . preg_quote($searchfor, "/") . "\b";
            // finalise the regular expression, matching the whole line
            $pattern = "/^.*" . $pattern . ".*\$/m";
        }
        // search, and store all matching occurences in $matches
        $m = false;

        //$pattern = '/' . '\b' . $searchfor . '\b/m';

        $this->thing->log("regex " . $pattern);

        if (true) {
            if (preg_match_all($pattern, $contents, $matches)) {
                $m = implode("\n", $matches[0]);
                if ($return_array_flag === true) {
                    $m = $matches[0];
                }

                $this->matches = $matches;
            }
        } else {
            // devstack
            // dev faster?
            // speed test wth stripos.
            // But matches part words.

            // Does not match words.
            // Do not use. Dev/test only.

            $matches[0] = [];
            $separator = "\r\n";

            $line = strtok($contents, $separator);
            while ($line !== false) {
                if (mb_stripos($line, $searchfor) !== false) {
                    $matches[0][] = $line;
                }

                $line = strtok($separator);
            }

            $m = implode("\n", $matches[0]);
            if ($return_array_flag === true) {
                $m = $matches[0];
            }
            $this->matches = $matches;
        }

        return $m;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->cost = 100;

        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    /**
     *
     */
    function makeWeb()
    {
        if (!isset($this->filtered_input)) {
            $input = "X";
        } else {
            $input = $this->filtered_input;
        }

        $html = "<b>CHINESE " . $input . " </b>";
        $html .= "<p><br>";

        foreach ($this->words as $index => $word) {
            if (!isset($word["traditional"])) {
                continue;
            }
            if (!isset($word["simplified"])) {
                continue;
            }
            if (!isset($word["pin_yin"])) {
                continue;
            }
            if (!isset($word["english"])) {
                continue;
            }

            $line =
                $word["traditional"] .
                " " .
                $word["simplified"] .
                " " .
                $word["pin_yin"];
            $i = 0;

            if ($word["english"] != null) {
                foreach ($word["english"] as $english) {
                    $line .= " / " . $english;
                }
                $html .= $line . "<br>";
            }
        }
        $this->web_message = $html;
        $this->thing_report["web"] = $html;
    }

    /**
     *
     */
    function makeSMS()
    {
        switch (true) {
            case isset($this->english_chinese_translation):
                $sms =
                    "CHINESE | " .
                    $this->english_chinese_translation;
                $this->sms_message = $sms;
                break;
            case isset($this->word):
                if (
                    isset($this->has_chinese_characters) and
                    !$this->has_chinese_characters
                ) {
                    // Assume english to chinese
                    $sms = "CHINESE | ";
                    foreach ($this->words as $word) {
                        if (mb_strlen($sms) > 60) {
                            $sms .= "TEXT WEB";
                            break;
                        }

                        if (!isset($word["traditional"])) {
                            continue;
                        }

                        $w = $this->wordsChinese($word["traditional"]);

                        $sms .= trim($word["traditional"] . " " . $w) . " / ";
                    }
                    $this->sms_message = $sms;
                    break;
                }

                // Assume this means a word was found.
                $this->sms_message = "CHINESE ";

                if (count($this->words) == 1) {
                    $this->sms_message .= "One dictionary phrase found.";
                }
                if (count($this->words) > 1) {
                    $this->sms_message .=
                        count($this->words) . " dictionary phrases found.";
                }

                if (count($this->words) != 0) {
                    $this->sms_message .= " | ";
                }
                if (isset($this->word["english"])) {
                    $this->sms_message .= implode(
                        " / ",
                        $this->word["english"]
                    );
                }
                break;
            case isset($this->chinese_from_words):
                if (count($this->chineses) > 1) {
                    $this->sms_message = "CHINESE CHARACTERS ARE ";
                } else {
                    $this->sms_message = "CHINESE CHARACTER IS ";
                }

                $this->sms_message .= $this->chinese_text;
                $this->sms_message .= " | " . $this->search_words;

                //          return;

                break;
            case isset($this->chinese) and $this->chinese != false:
                if (mb_strlen($this->chinese_text) > 6) {
                    $this->sms_message = "CHINESE";
                } else {
                    $this->sms_message = "CHINESE CHARACTER IS ";
                    $this->sms_message .= $this->chinese_text;
                }

                if ($this->words != false) {
                    if (count($this->words) > 1) {
                        $traditional = $this->words[0]["traditional"];
                        $simplified = $this->words[0]["simplified"];
                        $pin_yin = $this->words[0]["pin_yin"];
                        $english = $this->words[0]["english"][0];

                        $word_string =
                            $traditional .
                            " " .
                            $simplified .
                            " " .
                            $pin_yin .
                            " " .
                            $english;

                        $this->sms_message .= " | word is " . $word_string;
                    } else {
                        $s = $this->textChinese($this->words[0]);
                        $this->sms_message .= " | words are " . $s;
                    }
                } else {
                    if (isset($this->translated_input)) {
                        $this->sms_message .= " | " . $this->translated_input;
                    } else {
                        $this->sms_message .= " | character not recognized";
                    }
                }

                $this->sms_message .= " | Heard " . $this->keyword . ". ";
                //                $this->sms_message .= " | TEXT ?";
                //            return;

                break;
            default:
                if (isset($this->translated_input)) {
                    $this->sms_message = "CHINESE | " . $this->translated_input;
                } else {
                    $this->sms_message = "CHINESE | no match found.";
                }
        }

        $this->thing_report["sms"] = $this->sms_message . " " . $this->response;
    }

    /**
     *
     * @param unknown $word
     * @return unknown
     */
    function textChinese($word)
    {
        $traditional = $word["traditional"];
        $simplified = $word["simplified"];
        $pin_yin = $word["pin_yin"];
        $english = $word["english"][0];

        $word_string =
            $traditional . " " . $simplified . " " . $pin_yin . " " . $english;
        return $word_string;
    }

    /**
     *
     * @param unknown $logogram_sequence (optional)
     * @return unknown
     */
    function readChinese($logogram_sequence = null)
    {
        if ($logogram_sequence == null) {
            return;
        }
        $translated_logogram_sequence = $this->wordsChinese($logogram_sequence);

        return $translated_logogram_sequence;
    }

    /**
     *
     */
    function makeEmail()
    {
        $this->email_message = $this->sms_message;
        $this->thing_report["email"] = $this->sms_message;
    }

    /**
     *
     * @return unknown
     */
    public function test()
    {
        $short_input = "短信机";
        $short_input = "因應短信";

        $input = "王先生是北大的老教师，一九三八年五月二十四号出生，　今年五十八岁。　今天是他的生日。　他是一位非常有经验的法语老师。这个学期他教大三的学生现代法语语法。

王先生的一位老朋友是老年大学的老师，　他经常在这个大学教日语。

他的一个学生在师大工作。　他有汉语书，法语书和日语书。　他天天教留学生现代汉语。现在他有五个男学生，八个女学生。";

        $input =
            "This is a mix of chinese 短信 characters 机 and english words.";

        return $input;
    }

    public function hasChinese($text = null)
    {
        $this->extractChinese($text);

        if (count($this->chineses) > 0) {
            //$has_chinese_characters = true;
            return true;
        } else {
            //$has_chinese_characters = false;
            return false;
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $input = strtolower($this->input);

        if (strtolower($input) == "chinese") {
            $this->response .= "Retrieved a message with Chinese in it. ";
            return;
        }

        // No chinese characters seen.
        if ($this->hasChinese($input) === false) {
            $tokens = explode(" ", $input);
            if ($tokens[0] == "chinese") {
                $input = $this->assert($input);
            } else {
                return;
            }
        }

        $this->doChinese();

        $halves = explode(" ", $input, 2); // create a two-element(maximum) array

        $first = array_splice($halves, 0, 1)[0]; // assign first element to $first, now $halves is a single, reindexed element

        $filtered_input = $input;
        if (strtolower($first) == "chinese") {
            $filtered_input = $halves[0];
        }

        // Clean input
        $filtered_input = strtolower($filtered_input);
        $filtered_input = trim($filtered_input);

        $string_length = mb_strlen($filtered_input);

        $has_chinese_characters = $this->hasChinese($filtered_input);
        $this->has_chinese_characters = $has_chinese_characters;

        if ($has_chinese_characters) {
            $t = $this->readChinese($filtered_input);
            $this->translated_input = $t;
            $text = $this->findChinese("list", $filtered_input);
            $separator = "\r\n";
            $line = strtok($text, $separator);
            $this->words = [];
            while ($line !== false) {
                $word = $this->conceptChinese($line);
                $this->words[] = $word;
                // do something with $line
                $line = strtok($separator);
            }
            if (count($this->words) == 0) {
                $this->response .= "No Chinese translation found. ";
            }

            // Sort by length of phrase. Shortest first.
            $traditional = [];
            foreach ($this->words as $key => $row) {
                $traditional[$key] = mb_strlen($row["traditional"]);
            }
            array_multisort($traditional, SORT_ASC, $this->words);

            if (!isset($this->words[0])) {
                $this->word = null;
                $this->response .= "Did not find a Chinese translation. ";
            } else {
                $this->word = $this->words[0];
                foreach ($this->words as $word) {
                    if (
                        $word["traditional"] == $filtered_input or
                        $word["simplified"] == $filtered_input
                    ) {
                        $this->word = $word;
                        break;
                    }
                }
                $this->response .= "Found a Chinese translation. ";
            }
            $this->filtered_input = $filtered_input;
            return;
        }

        if ($filtered_input == "") {
            $filtered_input = "hello";
        }

        $text = $this->findChinese("english-chinese", $filtered_input);

        $separator = "\r\n";
        $line = strtok($text, $separator);

        $this->words = [];
        while ($line !== false) {
            $word = $this->conceptChinese($line);
            $this->words[] = $word;
            // do something with $line
            $line = strtok($separator);
        }

        if (count($this->words) == 0) {
            $this->response .= "No direct English translation found. ";
        }

        if (!isset($this->words[0])) {
            $t = $this->englishChinese($filtered_input);
            $this->english_chinese_translation = $t;
            $this->response .= "Did a rough translation. ";
            $this->word = null;
        } else {
            $this->word = $this->words[0];
        }

        $this->filtered_input = $filtered_input;
        return;

        $t = $this->findChinese("english-chinese", $filtered_input);

        $this->filtered_input = $filtered_input;
        $this->response .= "Provided chinese words for english. ";
        return;

        test:

        $input = $this->test();

        $this->translated_input = $this->wordsChinese($input);

        if (count($this->chineses) > 0) {
            // This line catches snowflakes as a temp solution
            // They are not recognized.  devstack
            if (
                $this->translated_input == " ? " and
                ($this->keyword = "snowflake")
            ) {
                $this->translated_input = "snowflake";
            }
            return;
        }

        $input = strtolower($this->subject);
        $keywords = ["chinese"];
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "chinese":
                            $prefix = "chinese";
                            $words = preg_replace(
                                "/^" . preg_quote($prefix, "/") . "/",
                                "",
                                $input
                            );
                            $words = ltrim($words);
                            $this->search_words = $words;
                            $t = $this->findChinese("list", $words);

                            // Strip out non-word matches
                            $arr = [];
                            foreach ($this->matches[0] as $match) {
                                ///    /\b($word)\b/i
                                $text = preg_replace(
                                    "/[^a-z\s]/",
                                    "",
                                    strtolower($match)
                                );
                                $text = preg_split(
                                    "/\s+/",
                                    $text,
                                    null,
                                    PREG_SPLIT_NO_EMPTY
                                );
                                $text = array_flip($text);

                                $word = strtolower($words);
                                if (isset($text[$word])) {
                                    $arr[] = $match;
                                }
                            }

                            if ($arr == null) {
                                $this->chineses = null;
                            } else {
                                //$array = $this->matches[0];
                                $k = array_rand($arr);
                                $v = $arr[$k];

                                $this->chinese_from_words = $v;

                                $this->chineses = $this->extractChinese(
                                    implode(" ", $arr)
                                );
                            }
                            return;

                        default:
                        // 'default';
                    }
                }
            }
        }
        $status = true;

        return $status;
    }

    public function bestChinese(
        $dictionary_entries = null,
        $english_text = null
    ) {
        // dev Lots more work needed here.

        // Remove words like restrain when we want train

        if ($english_text != null) {
            $english_text = strtolower($english_text);
            foreach ($dictionary_entries as $i => $dictionary_entry) {
                //$tokens = explode(" " ,$dictionary_entry);

                $tokens = preg_split(
                    "/[\s,-,\/]+/",
                    strtolower($dictionary_entry)
                );

                $match = false;
                foreach ($tokens as $j => $token) {
                    if ($token == $english_text) {
                        $match = true;
                    }
                }
                if ($match === false) {
                    unset($dictionary_entries[$i]);
                }
            }
        }

        $besties = [];
        foreach ($dictionary_entries as $i => $dictionary_entry) {
            $tokens = explode(" ", $dictionary_entry);
            $length = mb_strlen($tokens[0]);
            if (!isset($besties[$length])) {
                $besties[$length] = [];
            }
            $besties[$length][] = $dictionary_entry;

            if (!isset($min_length)) {
                $min_length = $length;
            }
            if ($length < $min_length) {
                $min_length = $length;
            }
        }

        // Having got the shortest logogram.
        // Now take the longest dictionary entry.
        // This encapsulates the most complicated concept with the short number of logograms.

        foreach ($besties[$min_length] as $i => $bestie) {
            $bestie_length = mb_strlen($bestie);

            if (!isset($min_bestie_length)) {
                $min_bestie_length = $bestie_length;
                $best_bestie = $bestie;
            }

            if ($bestie_length < $min_bestie_length) {
                $best_bestie = $bestie;
                $min_bestie_length = $bestie_length;
            }
        }

        return $best_bestie;
    }

    public function makeNgramStack($text)
    {
        $tokens = explode(" ", $text);
        $count = count($tokens);

        $input_stack = array_reverse($this->ngramsText($text, $count, ' '));
        array_unique($input_stack);
        usort($input_stack, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        return $input_stack;
    }

    public function englishChinese($text = null)
    {
        // Process largest anglo concept that returns a dictionary result.
        // Do this by working from left and removing a word until a result comes back.
        $undigested_text = $text;

        $input_stack = $this->makeNgramStack($undigested_text);
        $output_text = $text;

        $logograms = "";
        $unmatched_stack = [];

        while (count($input_stack) != 0) {
            $input_text = array_pop($input_stack);
            $input_text = preg_replace('/\s+/', " ", $input_text);
            if ($input_text == "") {
                continue;
            }
            $tokens = explode(" ", $input_text);
            $t = $this->logogramChinese($input_text);

            if ($t == false) {
                $unmatched_stack[] = $input_text;
                array_unique($unmatched_stack);
                if (count($input_stack) == 0) {
                    break;
                }
                continue;
            }

            $undigested_text = trim(
                str_replace($t['english'], "", $undigested_text)
            );

            $input_stack = $this->makeNgramStack($undigested_text);
            if ($t['chinese'] == null) {
                continue;
            }

            $m = $t['chinese'];
            $logogram = explode(" ", $m)[0];

            $output_text = trim(
                str_replace($t['english'], $logogram, $output_text)
            );
        }

        return $output_text;
    }

    public function logogramChinese($text)
    {
        $tokens = explode(" ", $text);
        foreach ($tokens as $index => $token) {
            $token_string = "";
            foreach (range(0, count($tokens) - 1 - $index, 1) as $number) {
                $token_string .= $tokens[$number] . " ";
            }
            $token_string = trim($token_string);
            $t = $this->findChinese("list", $token_string, true);

            // No matches.
            if ($t === false) {
                continue;
            }
            // Then return shortest matching
            $x = $this->bestChinese($t, $token_string);
            //if ($x == false) {return false;}
            return ['english' => $token_string, 'chinese' => $x];
        }

        return false;
    }

    /**
     *
     * @return unknown
     */
    function contextChinese()
    {
        $this->chinese_context = '
';

        return $this->chinese_context;
    }
}
