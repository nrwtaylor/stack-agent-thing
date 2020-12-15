<?php
/**
 * Proword.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

// An agent to recognize and understand Prowords.

class Proword extends Word
{
    /**
     *
     */
    function init()
    {
        $this->hits = 0;
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->keywords = [];
        $this->keyword = "proword";

        $this->default_librex_name = "acp125g";

        $this->proword_variables = new Variables(
            $this->thing,
            "variables proword " . $this->from
        );
    }

    /**
     *
     */
    function run()
    {
        $this->thingreportProword();
    }

    /**
     *
     */
    function get()
    {
        $this->previous_librex_name = $this->proword_variables->getVariable(
            "librex"
        );
        //$this->librex_name = $this->previous_librex_name;

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "proword",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->json->writeVariable(
                ["proword", "refreshed_at"],
                $time_string
            );
        }

        //        $this->librex_name = $this->thing->json->readVariable( array("proword", "librex") );

        if (!isset($this->librex_name) or $this->librex_name == false) {
            $this->librex_name = $this->default_librex_name;
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable([
            "proword",
            "reading",
        ]);
    }

    /**
     *
     */
    function set()
    {
        if (!isset($this->has_prowords)) {
            $this->has_prowords = true;
        }

        $this->proword_variables->setVariable("librex", $this->librex_name);

        $this->thing->json->writeVariable(
            ["proword", "reading"],
            $this->has_prowords
        );

        $this->thing_report['help'] = "Reads the short message for prowords.";
    }

    /**
     *
     */
    function thingreportProword()
    {
        $this->thing_report['log'] = $this->thing->log;
        $this->thing_report['help'] = "Reads the short message for prowords.";
    }

    /**
     *
     */
    function prowordThing()
    {
        // Get all of this users Things
        // To search for the last Proword text provided.
        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

        $things = $thingreport['thing'];

        // Get the earliest from the current data set
        foreach (array_reverse($things) as $thing) {
            $this->extractProwords($thing['task']);
            if ($this->prowords != []) {
                break;
            }
        }
    }

    /**
     *
     * @param unknown $librex
     * @param unknown $searchfor
     */
    function findWord($librex, $searchfor)
    {
        $this->findProword($librex, $searchfor);
    }

    /**
     *
     * @param unknown $test
     */
    function getWords($test)
    {
        $this->getProwords($test);
    }

    /**
     *
     * @param unknown $test
     * @return unknown
     */
    function isProword($test)
    {
        $this->getProwords($this->librex_name);
        $match = false;
        foreach ($this->prowords as $proword => $arr) {
            if ($proword == "") {
                continue;
            }
            if (strpos(strtolower($test), strtolower($proword)) !== false) {
                $match = true;
                break;
            }
        }

        return $match;
    }

    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function extractProwords($text)
    {
        $words = explode(" ", $text);

        $this->getProwords($this->librex_name);
        $prowords_list = [];

        foreach ($this->prowords as $proword => $arr) {
            if ($proword == "") {
                continue;
            }
            if (strpos(strtolower($text), strtolower($proword)) !== false) {
                $prowords_list[] = $proword;
            }
        }
        $this->extracted_prowords = $prowords_list;
        return $prowords_list;
    }

    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function countProwords($text)
    {
        $words = explode(" ", $text);

        $this->getProwords($this->librex_name);
        $count = 0;
        foreach ($words as $word) {
            foreach ($this->prowords as $proword => $arr) {
                if ($proword == "") {
                    continue;
                }
                if (strpos(strtolower($word), strtolower($proword)) !== false) {
                    $count += 1;
                    break;
                }
            }
        }

        return $count;
    }

    /**
     *
     * @param unknown $string
     */
    function extractProword($string)
    {
        // devstack
    }

    /**
     *
     * @param unknown $librex_name
     * @return unknown
     */
    function getLibrex($librex_name)
    {
        if (strtolower($librex_name) == strtolower($this->librex_name)) {
            if (isset($this->librex)) {
                return;
            }
        }

        // Look up the meaning in the dictionary.
        if ($librex_name == "" or $librex_name == " " or $librex_name == null) {
            return false;
        }

        switch ($librex_name) {
            case null:
            // Drop through
            case 'prowords':
                $file = $this->resource_path . 'proword/prowords.txt';
                break;
            case 'acp125g':
                $file = $this->resource_path . 'proword/prowords.txt';
                break;
            case 'arrl':
                // devstack create file
                $file = $this->resource_path . 'proword/arrl.txt';
                break;

            case 'vector':
                $file = $this->resource_path . 'proword/vector.txt';
                break;
            case 'compression':
                $file = $this->resource_path . 'compression/compression.txt';
                break;

            default:
                $file = $this->resource_path . 'proword/prowords.txt';
        }

        $this->librex_name = $librex_name;

$contents = "";
if (file_exists($file)) {
        $contents = file_get_contents($file);
}


        $this->librex = $contents;
    }

    /**
     *
     * @param unknown $librex_name
     * @param unknown $searchfor   (optional)
     * @return unknown
     */
    function getProwords($librex_name, $searchfor = null)
    {
        $this->getLibrex($librex_name);

        $contents = $this->librex;

        $this->prowords = [];
        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {

            $word = $this->parseProword($line);

            if ($word === false) {
                $line = strtok($separator);
                continue;
            }

            $this->prowords[$word['proword']] = $word;
            // do something with $line
            $line = strtok($separator);
        }

        if ($searchfor == null) {
            return null;
        }
        // devstack add \b to Word
        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        //        $pattern = "/^.*". strtolower($pattern). ".*\$/m";
        $pattern = "/^.*\b" . strtolower($pattern) . "\b.*\$/m";
        //        $pattern = "/^.*\b". strtolower($pattern). "\b.*$/m";
        //$pattern = "/^.*". strtolower($pattern). ".*\$/m";

        //$pattern = '/^.*\b' . strtolower($searchfor) . '\b.*$/m';

        // search, and store all matching occurences in $matches
        $m = false;
        if (preg_match_all($pattern, strtolower($contents), $matches)) {
            foreach ($matches[0] as $match) {
                $word = $this->parseProword($match);
                if ($word == false) {
                    continue;
                }
                // Multiple matches.
                $this->matches[$word['proword']][] = $word;
            }
        }
        if (!isset($this->matches)) {
            $this->matches = [];
        }

        return $m;
    }

    function parseArrl($text)
    {
        //        $dict = explode(",", $text);
        //if ($this->librex == "arrl") {$comma = "";}
        //$dict=explode($comma,str_replace(array('  ', '--',':',';'),$comma,$text));
        $dict = explode("  ", str_replace(['--', ':', ';'], "  ", $text));

        $dict = array_values(array_filter($dict, 'strlen'));
        return $dict;
    }

    /**
     *
     * @param unknown $test
     * @return unknown
     */
    private function parseProword($test)
    {
        if (mb_substr($test, 0, 1) == "#") {
            $word = false;
            return $word;
        }

        $dict = explode("/", $test);

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
        $text = $dict[0];

        $dict = explode(",", $text);
        $proword = trim($dict[0]);

        $dict = explode(",", $text);
        //$comma = ",";

        // Special instructions for ARRL librex.

        if ($this->librex_name == "arrl") {
            $dict = $this->parseArrl($text);
        }

        $proword = trim($dict[0]);
        //if (strlen($proword) > 10) {$proword = "N/A";}

        //        $words = trim($dict[1]);

        $words = null;
        $instruction = null;
        $english_phrases = null;
        if (isset($dict[1])) {
            $words = trim($dict[1]);
        }
        if (!isset($dict[1])) {
            $words = trim($dict[0]);
            $proword = strtoupper(trim(explode(" ", $dict[0])[0]));
        }

        if (isset($dict[2])) {
            $english_phrases = trim($dict[2]);
        }
        if (isset($dict[3])) {
            $instruction = trim($dict[3]);
        }

        $parsed_line = [
            "proword" => $proword,
            "words" => $words,
            "instruction" => $instruction,
            "english" => $english_phrases,
        ];
        return $parsed_line;
    }

    /**
     *
     * @return unknown
     
    public function respondResponse() {
        $this->cost = 100;

        // Thing stuff
        $this->thing->flagGreen();

        // Make SMS
        $this->makeSMS();
        $this->thing_report['sms'] = $this->sms_message;

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

        $this->thing_report['help'] = "Reads the short message for prowords.";

        return $this->thing_report;
    }
*/

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
        $html = "<b>PROWORD " . $input . " </b>";
        $html .= "<p><br>";

        if (isset($this->matches)) {
            foreach ($this->matches as $proword => $word) {
                // Use the first match.
                $word = $word[0];
                $line =
                    "<b>" .
                    strtoupper($word["proword"]) .
                    "</b> " .
                    $word["words"];
                if ($word["words"] == null) {
                    continue;
                }
                $html .= $line . "<br>";
            }
        }

        $this->web_message = $html;
        $this->thing_report['web'] = $html;
    }

    /**
     *
     * @param unknown $librex
     * @param unknown $search_text
     */
    function findProword($librex, $search_text)
    {
    }

    /**
     *
     */
    function makeSMS()
    {
        $sms = "PROWORD ";

        $sms .= strtoupper($this->librex_name) . " | ";

        $response_text = $this->response;
        if ($this->response == null) {
            $response_text = "Standby";
        }

        $sms .= $response_text;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     * @param unknown $word
     * @return unknown
     */
    function prowordString($word)
    {
        $proword = $word['proword'];
        $words = $word['words'];
        $instruction = $word['instruction'];
        $english = $word['english'][0];

        $word_string =
            $proword . " " . $words . " " . $instruction . " " . $english;
        return $word_string;
    }

    /**
     *
     */
    function makeEmail()
    {
        $this->email_message = "PROWORD | ";
    }

    /**
     *
     * @return unknown
     */
    public function test()
    {
        $short_input = "wrong";
        $short_input = "standby";

        $input = "agent proword wrong";
        return $input;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->response = "";

        $this->input = $this->agent_input;

        $librexes = ['acp125g', 'compression', 'arrl'];

        if ($this->agent_input == null) {
            $this->input = $this->subject;
            $text = $this->input;
        } else {
            $text = $this->agent_input;
            $words = explode(" ", $this->agent_input);
            foreach ($words as $index => $word) {
                foreach ($librexes as $index => $strip_word) {
                    $whatIWant = $text;
                    if (
                        ($pos = strpos(
                            strtolower($text),
                            $strip_word . " is"
                        )) !== false
                    ) {
                        $whatIWant = substr(
                            strtolower($text),
                            $pos + strlen($strip_word . " is")
                        );
                    } elseif (
                        ($pos = strpos(strtolower($text), $strip_word)) !==
                        false
                    ) {
                        $whatIWant = substr(
                            strtolower($text),
                            $pos + strlen($strip_word)
                        );
                    }

                    $text = $whatIWant;
                }
            }
        }

        $match = false;
        foreach ($librexes as $librex_candidate) {
            if (
                strpos(
                    strtolower($this->input),
                    strtolower($librex_candidate)
                ) !== false
            ) {
                $match = true;
                break;
            }
        }

        if ($match == true) {
            $this->librex_name = $librex_candidate;
        }

        //        if (!isset($librex_name)) {$librex_name = $this->default_librex_name;}
        //        if (!isset($this->librex_name)) {$librex_name = $this->default_librex_name;}
        //        $this->librex_name = $librex_name;

        if (strtolower($this->input) == "proword") {
            $this->prowordThing();
            $this->response = "Retrieved a message with Proword in it.";
            return;
        }
        // Ignore "proword is" or "proword"

        $whatIWant = $text;

        //        $whatIWant = $this->input;
        if (($pos = strpos(strtolower($whatIWant), "proword is")) !== false) {
            $whatIWant = substr(
                strtolower($whatIWant),
                $pos + strlen("proword is")
            );
        } elseif (
            ($pos = strpos(strtolower($whatIWant), "proword")) !== false
        ) {
            $whatIWant = substr(
                strtolower($whatIWant),
                $pos + strlen("proword")
            );
        }

        // Do the same
        if (($pos = strpos(strtolower($whatIWant), "arrl is")) !== false) {
            $whatIWant = substr(
                strtolower($whatIWant),
                $pos + strlen("arrl is")
            );
        } elseif (($pos = strpos(strtolower($whatIWant), "arrl")) !== false) {
            $whatIWant = substr(strtolower($whatIWant), $pos + strlen("arrl"));
        }

        // Clean input
        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $string_length = mb_strlen($filtered_input);

        $this->extractProwords($filtered_input);
        $this->has_prowords = $this->isProword($filtered_input);

        $this->getProwords($this->librex_name, $filtered_input);

        $ngram = new Ngram($this->thing, "ngram");
        $ngrams = $ngram->extractNgrams($filtered_input, 3);
        $search_phrases = $ngrams;

        usort($search_phrases, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        foreach ($search_phrases as $search_phrase) {
            $this->getProwords($this->librex_name, $search_phrase);
        }
        $this->filtered_input = $filtered_input;

        if ($this->has_prowords) {
            if (count($this->matches) == 0) {
                $this->response = "No proword found.";
                return;
            }

            if (count($this->matches) == 1) {
                $key = key($this->matches);
                $value = reset($this->matches);
                // Use first match. For now.
                $k = strtoupper($key);
                $w = $value[0]['words'];

                if (strtolower($k) == strtolower($w)) {
                    $k = strtoupper(explode(" ", $w)[0]);
                }
                $this->response = $k . " " . $w;

                return;
            }
        }

        // devstack closeness

        if (!isset($this->matches)) {
            $this->response .= "No matches found. ";
            return;
        }

        $this->results = $this->matches;
        $words = explode(" ", $filtered_input);

        $closest = 0;

        foreach ($this->results as &$result) {
            $closeness = 0;
            foreach ($words as $word) {
                // For now only use the first match
                $p_words = explode(" ", $result[0]['words']);

                //                $p_words = explode(" " , $result['words'][0]);
                foreach ($p_words as $p_word) {
                    // Ignore 1 and 2 letter words
                    if (strlen($word) <= 2) {
                        continue;
                    }

                    if (strtolower($word) == strtolower($p_word)) {
                        $closeness += 1;
                    }
                }
                if ($closeness > $closest) {
                    $closest = $closeness;
                    $best_proword = $result[0];
                }
            }
        }

        $sms = "";
        $count = 0;
        $flag_long = false;

        foreach ($this->matches as $proword => $word) {
            if (mb_strlen($sms) > 140) {
                $flag_long = true;
            }
            $sms .=
                strtoupper($word[0]["proword"]) .
                " " .
                $word[0]['words'] .
                " / ";
            $count += 1;
        }

        // If too long, then try without the definition.
        if ($flag_long) {
            $sms = "";
            $flag_long = false;
            foreach ($this->matches as $proword => $word) {
                if (mb_strlen($sms) > 140) {
                    $flag_long = true;
                }
                $sms .= strtoupper($word[0]["proword"]) . " / ";
                $count += 1;
            }
        }

        // If still too long, select the 'best' proword.
        if ($flag_long) {
            //            foreach ($this->matches as $proword=>$word) {
            //                if (mb_strlen($sms) > 131) {$sms .= "TEXT WEB";break;}
            //                $sms .= $word["proword"] . " / ";
            //                $count += 1;
            $sms =
                strtoupper($best_proword["proword"]) .
                " " .
                $best_proword['words'];
            //            }
            //            $this->response = $sms;
            $this->hits = $count;
        }
        $this->response = $sms;
    }
}
