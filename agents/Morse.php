<?php
/**
 * Morse.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

// An agent to recognize and understand Morse characters.

class Morse extends Agent {


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init() {

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $string =  $this->subject;

        $this->keyword = "morse";

        // devstack

        $morse_words =$this->extractMorse($string);

        $this->getMorse();

        $searchfor = $this->convert_morse($this->morse);
        $arr = explode(" ", $searchfor);
        $this->words = array();
        $this->word = null;

        foreach ($arr as $key=>$value) {
            if ($value == "U+FE0F") {continue;}
            // Return dictionary entry.
            $text = $this->findMorse('list', $value);
            //echo $value . " " .$text . "<br>";
            $words = $this->getConcept($text);
            if (($words != false) ) {
                $this->words = array_merge($this->getConcept($text));
$this->word = null;
if (isset($this->words[0])) {$this->word = $this->words[0];}
            }
        }

        $this->keywords = array();
        $this->keyword = "morse";

        foreach ($arr as $key=>$value) {
            $text = $this->findMorse('mordok', $value);
            if ($value == "U+FE0F") {continue;}

            $words = $this->getConcept($text);

            if ($words != false) {
                $this->keywords = array_merge($this->getConcept($text));

$this->keyword = null;
if (isset($this->keywords[0])) {$this->keyword = $this->keywords[0];}
            }
        }

    }


    /**
     *
     */
    function get() {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("morse", "refreshed_at") );

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->json->writeVariable( array("morse", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("morse", "reading") );


    }


    /**
     *
     */
    function set() {

        $this->thing->json->writeVariable( array("morse", "reading"), $this->reading );



    }


    /**
     *
     */
    function run() {

        if ($this->morse != false) {
            $this->thing->log('keyword '. $this->keyword . " word  ". $this->word["morse"] . '.');
            $this->thing->log('completed with a reading of ' . $this->morse . '.');
        } else {
            $this->thing->log('did not find morse.');
        }

        //        $this->thing->log('ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

        $this->thingreportMorse();

    }


    /**
     *
     */
    function thingreportMorse() {
//        $this->makeSMS();

//        $this->thing_report['sms'] = $this->sms_message;

        $this->thing_report['log'] = $this->thing->log;
        //        $this->thing_report['sms'] = $this->sms_message;
    }


    /**
     *
     * @param unknown $text     (optional)
     * @param unknown $logogram (optional)
     * @return unknown
     */
    function getWord($text = null, $logogram = null) {
        $logogram = trim($logogram);
        //if ($concept == null) {return;}
        $this->thing->log("logo gram " . $logogram ."\n");
        $separator = "\r\n";
        $line = strtok($text, $separator);

        $maximum_word_length = 0;

        while ($line !== false) {

            // do something with $line

            $word = $this->getConcept($line);

            // Look for the shortest matching logogram sequences
            // Or for an exact match.
            $word_length = mb_strlen($word["english"]) ;

            if (strcasecmp($word["english"], $logogram) == 0 ) {

                if ($word_length > $maximum_word_length) {
                    $match = array();
                    $maximum_word_length = $word_length;
                }

                if ($word_length = $maximum_word_length) {
                    $match[] = $word;
                }
            }
            $line = strtok( $separator );


        }


        if (!isset($match)) {return true;}


        $description = "";
        $shortest_concept_length = 1e6;

        $best_concept = "";
        $best_concept_length = 1e6;
        $best_concept_num_words = 1e6;
        if (!isset($match)) {return true;}
        foreach ($match as $i=>$word) {

            $description = "";
            foreach ($word["english"] as $j=>$concept) {

                // Use only the first three matching english concepts.
                if ($j >= 2) {break;}
                $description .= " / " .  $concept;

                //                    if ((mb_strlen($concept) < $shortest_concept_length) and (mb_strlen($concept) != 0)) {
                if (mb_strlen($concept) != 0) {


                    $words = explode(" " , $concept);
                    //echo "Counted " . count($words) . " words.\n";
                    $num_words = count($words);
                    $concept_length = mb_strlen($concept);

                    // Get longest word if only one word available.
                    // As proxy for concept complexity.
                    if (($num_words == 1) and ($best_concept_num_words == 1)) {

                        if ((mb_strlen($concept)) > (mb_strlen($best_concept))) {
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

        return $best_concept;
    }


    //echo $translation;


    //}


    /**
     *
     */
    function morseThing() {
        // Get all of this users Things
        // To search for the last Morse text provided.
        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

        $things = $thingreport['thing'];

        // Get the earliest from the current data set
        foreach (array_reverse($things) as $thing) {
            $this->extractMorse( $thing['task'] );
            if ($this->morse_words != array()) {break;}
        }
    }


    /**
     *
     */
    function makeDictionary() {
        // Makes a one character dictionary

        $file = $this->resource_path . 'morse/morse.txt';
        $contents = file_get_contents($file);


        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {
            $word = $this->getConcept($line);

            if (mb_strlen($word['english']) == 1) {

                //v/ar_dump($word);
                //$dictionary_entry = $word['traditional'] . " " . $word['simplified'] . " " . $word['pin_yin'] . implode("/",$word['english']) . "\n";
                //echo $dictionary_entry;
                $dictionary[$word['english']] = $line . "\n";

            }
            // do something with $line
            $line = strtok( $separator );
        }

        $file = fopen($this->resource_path . 'morse/morse_new.txt', 'w');
        foreach ($dictionary as $character=>$line) {
            fwrite($file, $line);
        }
    }


    /**
     *
     * @param unknown $test
     * @return unknown
     */
    function getConcept($test) {
        // Take a CE-CCEDICT line and de-parse it
        // Traditional Simplified [pin1 yin1] /English equivalent 1/equivalent 2/

        if ($test == false) {
            return false;
        }

        if (mb_substr($test, 0, 1) == "#") {$word = false; return $word;}

        $dict = explode(" ", $test);

        if ( (!isset($dict[1])) or (!isset($dict[2])) ) {
        }

        foreach ($dict as $index=>$phrase) {
            if ($index == 0) {continue;}
            if ($phrase == "") {continue;}
            $english_phrases[] = $phrase;
        }

//        $text =  $dict[0];

//        preg_match_all("/\[([^\]]*)\]/", $text, $matches);
//        $pin_yin = $matches[0][0];

//        $dict = explode(" ", $text);
//var_dump($dict);
        $english = $dict[0];
        $morse = $dict[1];

        $word = array("morse"=>$morse,
            "english"=>$english);

        return $word;
    }


    /**
     *
     * @param unknown $string
     * @return unknown
     */
    function extractMorse($string) {
        //https://stackoverflow.com/questions/17944961/php-separate-chinese-from-english-characters
        //$str = 'Hello 你怎么样？ How are you?';

        $english = preg_replace(array('/[\p{Han}？]/u', '/(\s)+/'), array('', '$1'), $string);
        $morse = preg_replace(array('/[^\p{Han}？]/u', '/(\s)+/'), array('', '$1'), $string);
        $this->morse_text = $morse;
        $this->thing->log('reads english ' . $english . " and morse " . $morse);

        //https://stackoverflow.com/questions/1396434/what-is-the-best-way-to-split-a-string-into-an-array-of-unicode-characters-in-ph
        $this->morse_words = preg_split('//u', $morse, -1, PREG_SPLIT_NO_EMPTY);

        return $this->morse_words;
    }


    /**
     *
     * @param unknown $string
     * @return unknown
     */
    function isMorse($string) {
        // Are there chinese (Han) characters in the string.

        //https://stackoverflow.com/questions/17944961/php-separate-chinese-from-english-characters
        //$str = 'Hello 你怎么样？ How are you?';
        $morse = preg_replace(array('/[^\p{Han}？]/u', '/(\s)+/'), array('', '$1'), $string);
        if ($morse == "") {return false;}
        return true;

    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function wordsMorse($input) {

        $translation = "";

        //$input = "短信机";
$input = "- .... .  --.- ..- .. -.-. -.-";
        //throw new Exception('Devstack.')
        $string_length = mb_strlen($input);
        $pointer = 0;
        $window = 1;
        // https://stackoverflow.com/questions/4601032/php-iterate-on-string-characters
        //$characters = mb_str_split($input);

        $characters =  (preg_split('//u', $input, null, PREG_SPLIT_NO_EMPTY));

        $logogram_sequence_length = mb_strlen(implode("", $characters));
        $end_flag = false;
        while ($pointer !== $string_length) {

//            echo "----------------- " . $pointer . " -----------". "\n";
//            echo "translation " . $translation . "\n";
            if ($end_flag) {break;}
            $character = $characters[$pointer];

            // Process phrase seperators
            if ($character == '，') {$pointer += 1; $translation .= ", ";continue;}
            if ($character == '　') {$pointer += 1; $translation .= " ";continue;}
            if ($character == '。') {$pointer += 1; $translation .= ". ";continue;}
            if ($character == ' ') {$pointer += 1; $translation .= " ";continue;}

            if ($character == '
') {$pointer += 1; continue;}

            $character_string = "";
            $test_character_string = "";
            $text = "";
            $match_flag = false;

            foreach (array(0, 1, 2, 3, 4, 5, 6) as $index=>$value) {
                if (($pointer + $value) >= $logogram_sequence_length) {$end_flag = true; break;}


                $test_character_string .= $characters[$pointer + $value];


                if ($this->isMorse($test_character_string) == false) {
//                    echo "no chinese found in test character string: " . $test_character_string . "\n";
                    //$pointer += mb_strlen($character_string);
                    $character_string = $test_character_string;
                    $pointer += $value + 1;
                    break;
                }



                //echo "test if character string " . $test_character_string . " is in dictionary.\n";
                $text_temp = $this->findMorse('list', $test_character_string);

                if ($text_temp == false) {
                    //echo "Not in dictionary" . "\n";

                    $pointer += $value;
                    //echo "character string " . $character_string . "\n";
                    break;
                }
                //echo "Is in dictionary." . "\n";

                $match_flag = true;
                //if ($match_flag = false) {break;}
                $text = $text_temp;
                $character_string = $test_character_string;

if ($value == 6) {$pointer += 1;}

            }

            $english_word = $this->getWord($text, $character_string);

            if ($english_word === true) {
                //true I guess
                $translation .= $character_string ;

            } else {

                $translation .= $english_word . " " ;

            }
        }
        return $translation;
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function getMorse($input = null) {
        // Get an array with all the Morse character words.
        if ($input == null) {$input = $this->subject;}
        if (!isset($this->morse_words)) {
            $this->extractMorse($this->subject);
        }

        if (count($this->morse_words) == 0) {$this->morse = false;return false;}
        $this->morse = $this->morse_words[0];

        return $this->morse;
    }


    /**
     *
     * @param unknown $morse
     * @return unknown
     */
    function convertMorse($morse) {
        // Convert Morse encoding to UTF-8
        $str = str_replace('"', "", json_encode($morse, JSON_HEX_APOS));

        $myInput = $str;

        $myHexString = str_replace('\\u', '', $myInput);
        $myBinString = hex2bin($myHexString);

        return  iconv("UTF-16BE", "UTF-8", $myBinString);
    }


    /**
     *
     * @param unknown $num
     * @return unknown
     */
    function utf8($num) {
        // More UTF nonsense.
        if ($num<=0x7F)       return chr($num);
        if ($num<=0x7FF)      return chr(($num>>6)+192).chr(($num&63)+128);
        if ($num<=0xFFFF)     return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
        if ($num<=0x1FFFFF)   return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128).chr(($num&63)+128);
        return '';
    }


    /**
     *
     * @param unknown $c
     * @return unknown
     */
    function uniord($c) {
        // And back again.
        $ord0 = ord($c[0]); if ($ord0>=0   && $ord0<=127) return $ord0;
        $ord1 = ord($c[1]); if ($ord0>=192 && $ord0<=223) return ($ord0-192)*64 + ($ord1-128);
        $ord2 = ord($c[2]); if ($ord0>=224 && $ord0<=239) return ($ord0-224)*4096 + ($ord1-128)*64 + ($ord2-128);
        $ord3 = ord($c[3]); if ($ord0>=240 && $ord0<=247) return ($ord0-240)*262144 + ($ord1-128)*4096 + ($ord2-128)*64 + ($ord3-128);
        return false;
    }


    /**
     *
     * @param unknown $morse
     * @return unknown
     */
    function convert_morse($morse) {
        $u =  $this->uniord($morse);
        return strtoupper("U+".dechex($u));
    }


    /**
     *
     * @param unknown $str
     * @return unknown
     */
    function format($str) {
        $copy = false;
        $len = strlen($str);
        $res = '';

        for ($i = 0; $i < $len; ++$i) {
            $ch = $str[$i];

            if (!$copy) {
                if ($ch != '0') {
                    $copy = true;
                }
                // Prevent format("0") from returning ""
                else if (($i + 1) == $len) {
                    $res = '0';
                }
            }

            if ($copy) {
                $res .= $ch;
            }
        }
        return 'U+'.strtoupper($res);
    }


    /**
     *
     * @param unknown $librex
     * @param unknown $searchfor
     * @return unknown
     */
    public function findMorse($librex, $searchfor) {
        // Look up the meaning in the dictionary.
        if (($librex == "") or ($librex == " ") or ($librex == null)) {return false;}

        switch ($librex) {
        case null:
            // Drop through
        case 'keywords':
            $file = $this->resource_path .'morse/morse-keywords.txt';
            $contents = file_get_contents($file);
            break;
        case 'list':
            $file = $this->resource_path . 'morse/morse.txt';
            $contents = file_get_contents($file);
            break;

        case 'english-morse':
            $file = $this->resource_path . 'morse/morse.txt';
            $contents = file_get_contents($file);
            break;
        default:
            $file = $this->resource_path . 'morse/morse.txt';
            $contents = file_get_contents($file);

        }

        // devstack add \b to Word
//var_dump($contents);
//$searchfor = "J";
        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        $pattern = "/^.*". $pattern. ".*\$/m";

        if ($librex == "english-morse") {
            $pattern = "\b" . preg_quote($searchfor, '/'). "\b";
            // finalise the regular expression, matching the whole line
            $pattern = "/^.*". $pattern. ".*\$/m";
        }

        // search, and store all matching occurences in $matches
        $m = false;
        if (preg_match_all($pattern, $contents, $matches)) {
            //echo "Found matches:\n";
            $m = implode("\n", $matches[0]);
            $this->matches = $matches;
        }

        return $m;
    }


    /**
     *
     * @return unknown
     */
    public function respond() {
        $this->cost = 100;

        // Thing stuff
        $this->thing->flagGreen();

        // Make SMS
        $this->makeSMS();
        //        $this->thing_report['sms'] = $this->sms_message;

        // Make message
        $this->thing_report['message'] = $this->sms_message;

        // Make email
        $this->makeEmail();
        $this->thing_report['email'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->makeWeb();

        $this->reading = $this->morse;
        $this->thing->json->writeVariable(array("morse", "reading"), $this->reading);

        return $this->thing_report;
    }


    /**
     *
     */
    function makeWeb() {
        if (!isset($this->filtered_input)) {
            $input = "X";
        } else {
            $input = $this->filtered_input;
        }

        $html = "<b>MORSE " . $input . " </b>";
        $html .= "<p><br>";
var_dump($this->words);
        foreach ($this->words as $index=>$word) {
            $line = $word["english"];
            $i = 0;
if ($word["english"] != null) {
            foreach ($word["english"] as $english) {
                $line .= " / " . $english;
            }
            $html .= $line . "<br>";
        }
}
        $this->web_message = $html;
        $this->thing_report['web'] = $html;
    }


    /**
     *
     */
    function makeSMS() {

switch (true) {
    case (isset($this->word)):

            if (!$this->has_morse_characters) {

                // Assume english to morse
                $sms = "MORSE | ";
                foreach ($this->words as $word) {
                    if (mb_strlen($sms) > 60) {$sms .= "TEXT WEB";break;}

                    $w = $this->wordsMorse($word["english"]);

                    $sms .= trim($word["english"] . " " . $w) . " / ";
                }
                $this->sms_message = $sms;
                break;

            }

            // Assume this means a word was found.
            $this->sms_message = "MORSE ";
            $this->sms_message .= count($this->words) . " phrases found.";
            $this->sms_message .= " | ";

            $this->sms_message .= implode(" / " , $this->word["english"]);

        break;
    case (isset($this->morse_from_words)):

            if (count($this->morse_words) > 1) {
                $this->sms_message = "MORSE CHARACTERS ARE ";
            } else {
                $this->sms_message = "MORSE CHARACTER IS ";
            }

            $this->sms_message .= $this->morse_text;
            $this->sms_message .= " | " . $this->search_words;

  //          return;


        break;
    case ((isset($this->morse)) and ($this->morse != false)):

            if (mb_strlen($this->morse_text) > 6) {
                $this->sms_message = "MORSE";
            } else {
                $this->sms_message = "MORSE CHARACTER IS ";
                $this->sms_message .= $this->morse_text;
            }

            if ($this->words != false) {
                if (count($this->words) > 1) {

                    $morse = $this->words[0]['morse'];
                    $english = $this->words[0]['english'][0];

                    $word_string = $morse . " " . $english;

                    $this->sms_message .= " | word is " . $word_string;

                } else {
                    $s = $this->morseString($this->words[0]);
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
            $this->sms_message .= " | TEXT ?";
//            return;

        break;
    default:
        $this->sms_message = "MORSE | no match found.";
}

$this->thing_report['sms'] = $this->sms_message;


    }


    /**
     *
     * @param unknown $word
     * @return unknown
     */
    function morseString($word) {
        $morse = $word['morse'];
        $english = $word['english'];

        $word_string = $morse . " " . $english;
        return $word_string ;
    }


    /**
     *
     * @param unknown $logogram_sequence (optional)
     * @return unknown
     */
    function readMorse($logogram_sequence = null) {

        if ($logogram_sequence == null) {return;}

        $translated_logogram_sequence = $this->wordsMorse($logogram_sequence);
        return $translated_logogram_sequence;
    }


    /**
     *
     */
    function makeEmail() {
        $this->email_message = "MORSE | ";
    }


    /**
     *
     * @return unknown
     */
    public function test() {
        $short_input = "短信机";
        $short_input = "因應短信";

        $input = "王先生是北大的老教师，一九三八年五月二十四号出生，　今年五十八岁。　今天是他的生日。　他是一位非常有经验的法语老师。这个学期他教大三的学生现代法语语法。

王先生的一位老朋友是老年大学的老师，　他经常在这个大学教日语。

他的一个学生在师大工作。　他有汉语书，法语书和日语书。　他天天教留学生现代汉语。现在他有五个男学生，八个女学生。";

        $input = "This is a mix of morse .. - .... -.- characters and english words.";


        return $input;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {
        $input = $this->subject;

        //        $input = $this->test();
        //$input = $this->input;

        if (strtolower($input) == "morse") {
            $this->morseThing();
            $this->response = "Retrieved a message with Morse in it.";
            return;
        }


        $halves=explode(' ', $input, 2);  // create a two-element(maximum) array

        $first=array_splice($halves, 0, 1)[0];  // assign first element to $first, now $halves is a single, reindexed element

        $filtered_input = $input;
        if (strtolower($first) == "morse") {
            $filtered_input=$halves[0];
        }


        //        $whatIWant = $input;
        //        if (($pos = strpos(strtolower($input), "morse is")) !== FALSE) {
        //            $whatIWant = substr(strtolower($input), $pos+strlen("morse is"));
        //        } elseif (($pos = strpos(strtolower($input), "morse")) !== FALSE) {
        //            $whatIWant = substr(strtolower($input), $pos+strlen("morse"));
        //        }

        // Clean input
        $filtered_input = strtolower($filtered_input);
        $filtered_input = trim($filtered_input);
        //        $filtered_input = ltrim(strtolower($whatIWant), " ");

        $string_length = mb_strlen($filtered_input);

        $this->extractMorse($filtered_input);

        if (count($this->morse_words) > 0) {$has_morse_characters = true;} else {$has_morse_characters = false;}

        $this->has_morse_characters = $has_morse_characters;

        if ($has_morse_characters) {

            $t=            $this->readMorse($filtered_input);
            //$this->translation = $t;
            $this->translated_input = $t;
            //echo "translation " . $t. "\n";

            $text =  $this->findMorse("list", $filtered_input) ;
            $separator = "\r\n";
            $line = strtok($text, $separator);
            $this->words = array();
            while ($line !== false) {
                $word = $this->getConcept($line);
                $this->words[] = $word;
                // do something with $line
                $line = strtok( $separator );
            }
            if (count($this->words) == 0) {
                $this->response = "No Morse translation found.";
            }

            // Sort by length of phrase. Shortest first.
            $morse = array();
            foreach ($this->words as $key => $row) {
                $morse[$key] = mb_strlen($row['morse']);
            }
            array_multisort($morse, SORT_ASC, $this->words);

            if (!isset($this->words[0])) {
                $this->word = null;
                $this->response = "Did not find a Morse translation";
            } else {
                $this->word = $this->words[0];
                foreach ($this->words as $word) {
                    if (($word['morse'] == $filtered_input) or ($word['simplified'] == $filtered_input)) {
                        $this->word = $word;
                        break;
                    }
                }
                $this->response = "Found a Morse translation.";
            }
            $this->filtered_input = $filtered_input;
            return;
        }

        if ($filtered_input == "") {$filtered_input = "hello";}

        $text =  $this->findMorse("english-morse", $filtered_input) ;

        $separator = "\r\n";
        $line = strtok($text, $separator);

        $this->words = array();
        while ($line !== false) {
            $word = $this->getConcept($line);
            $this->words[] = $word;
            // do something with $line
            $line = strtok( $separator );
        }

        if (count($this->words) == 0) {
            $this->response = "No English translation found.";
        }

        if (!isset($this->words[0])) {$this->word = null;} else {
            $this->word = $this->words[0];
        }

        $this->response = "No response.";
        $this->filtered_input = $filtered_input;
        return;

        // devstack code below here.
        // Including a test read of a long passage.

        $t = $this->findMorse('english-morse', $filtered_input);

        $this->filtered_input = $filtered_input;
        $this->response = "Provided morse words for english.";
        return;

        test:

        $input = $this->test();

        $this->translated_input = $this->wordsMorse($input);

        if (count($this->morse_words) > 0) {

            // This line catches snowflakes as a temp solution
            // They are not recognized.  devstack
            if (($this->translated_input == " ? ") and ($this->keyword = "snowflake")) {$this->translated_input = "snowflake";}
            return;
        }

        $input = strtolower($this->subject);
        $keywords = array('morse');
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {

                    switch ($piece) {
                    case 'morse':

                        $prefix = 'morse';
                        $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                        $words = ltrim($words);
                        $this->search_words = $words;
                        $t = $this->findMorse('list', $words);

                        // Strip out non-word matches
                        $arr = array();
                        foreach ($this->matches[0] as $match) {
                            ///    /\b($word)\b/i
                            $text = preg_replace('/[^a-z\s]/', '', strtolower($match));
                            $text = preg_split('/\s+/', $text, NULL, PREG_SPLIT_NO_EMPTY);
                            $text = array_flip($text);

                            $word = strtolower($words);
                            if (isset($text[$word])) $arr[] = $match;
                        }

                        if ($arr == null) {
                            $this->morse_words = null;
                        } else {
                            //$array = $this->matches[0];
                            $k = array_rand($arr);
                            $v = $arr[$k];

                            $this->morse_from_words = $v;

                            $this->morse_words = $this->extractMorse(implode(" ", $arr));
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


    /**
     *
     * @return unknown
     */
    function contextMorse() {

        $this->morse_context = '
';

        return $this->morse_context;
    }


}
