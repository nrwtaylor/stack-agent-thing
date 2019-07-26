<?php
/**
 * Librex.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

// An agent to recognize and understand Prowords.

class Librex extends Word
{


    /**
     *
     */
    function init() {

        $this->hits = 0;
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->keywords = array();
        $this->keyword = "librex";

        $this->default_librex_name = "compression/acp125g";

        $this->librex_variables = new Variables($this->thing, "variables librex " . $this->from);

    }


    /**
     *
     */
    function run() {
        $this->thingreportLibrex();
    }


    /**
     *
     */
    function get() {

        $this->previous_librex_name = $this->librex_variables->getVariable("librex_name");
        //$this->librex_name = $this->previous_librex_name;


        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("librex", "refreshed_at") );

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->json->writeVariable( array("librex", "refreshed_at"), $time_string );
        }

        //        $this->librex_name = $this->thing->json->readVariable( array("proword", "librex") );


        if ((!isset($this->librex_name)) or ($this->librex_name == false)) {
            $this->librex_name = $this->default_librex_name;
        }


        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("librex", "reading") );
    }


    /**
     *
     */
    function set() {
        if (!isset($this->has_matches)) {$this->has_matches = true;}

        $this->librex_variables->setVariable("librex", $this->librex_name);

        $this->thing->json->writeVariable( array("librex", "reading"), $this->has_matches );

        $this->thing_report['help'] = "Reads the short message for a librex reference.";
    }


    /**
     *
     */
    function thingreportLibrex() {
        $this->thing_report['log'] = $this->thing->log;
        $this->thing_report['help'] = "Reads the short message for librexes.";

    }


    /**
     *
     */
    function librexThing() {
        // Get all of this users Things
        // To search for the last Proword text provided.
        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

        $things = $thingreport['thing'];

        // Get the earliest from the current data set
        foreach (array_reverse($things) as $thing) {
            $this->extractLibrexes ( $thing['task'] );
            if ($this->librexes != array()) {break;}
        }
    }


    /**
     *
     * @param unknown $librex
     * @param unknown $searchfor
     */
    function findWord($librex, $searchfor) {
        $this->findLibrex($librex, $searchfor);
    }


    /**
     *
     * @param unknown $test
     */
    function getWords($test) {
        $this->getMatches($test);
    }


    /**
     *
     * @param unknown $test
     * @return unknown
     */
    function isLibrex($test) {

        // Not working
        // Rewrite to check file directly.
        // Not needed at the moment can get the file with a try/catch.
        try {
            $this->getLibrex($test);
            return true;
        } catch (Exception $e) {
            return false;

        }


        return;
        $this->getMatches();
        $match = false;
        foreach ($this->librexes as $proword=>$arr) {
            if ($librex == "") {continue;}
            if (strpos(strtolower($test), strtolower($librex)) !== false) {
                $match = true; break;
            }
        }

        return $match;

    }


    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function extractLibrexes($text) {
        $words = explode(" ", $text);

        $this->getMatches();
        $matches_list = array();

        foreach ($this->matches as $match=>$arr) {
            if ($match == "") {continue;}
            if (strpos(strtolower($text), strtolower($match)) !== false) {
                $matches_list[] = $match;
            }
        }
        $this->extracted_librexes = $matches_list;
        return $matches_list;
    }


    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function countMatches($text) {

        $words = explode(" ", $text);

        $this->getMatches();
        $count = 0;
        foreach ($words as $word) {
            foreach ($this->librexes as $librex=>$arr) {
                if ($librex == "") {continue;}
                if (strpos(strtolower($word), strtolower($librex)) !== false) {
                    $count += 1; break;
                }
            }
        }

        return $count;
    }


    /**
     *
     * @param unknown $string
     */
    function extractLibrex($string) {
        // devstack
    }


    /**
     *
     * @param unknown $librex_name
     * @return unknown
     */
    function getLibrex($librex_name) {
        if ( strtolower($librex_name) == strtolower($this->librex_name)) {

            if (isset($this->librex)) {return;}
        }

        // Look up the meaning in the dictionary.
        if (($librex_name == "") or ($librex_name == " ") or ($librex_name == null)) {
            return false;
        }
        /*
        switch ($librex_name) {
        case null:
            // Drop through
        case 'proword/prowords':
            $file = $this->resource_path .'proword/prowords.txt';
            break;
        case 'proword/acp125g':
            $file = $this->resource_path .'proword/prowords.txt';
            break;
        case 'proword/arrl':
            // devstack create file
            $file = $this->resource_path .'proword/arrl.txt';
            break;

        case 'proword/vector':
            $file = $this->resource_path . 'proword/vector.txt';
            break;
        case 'proword/compression':
            $file = $this->resource_path . 'compression/compression.txt';
            break;

        case 'vancouverparksboard/queen_elizabeth_park':
            $file = $this->resource_path . $librex_name . '.txt';
            break;


        default:
            $file = $this->resource_path . 'proword/prowords.txt';
        }
*/
        $file = $this->resource_path . $librex_name . '.txt';


        $this->librex_name = $librex_name;

        $this->librex = file_get_contents($file);


    }


    /**
     *
     * @param unknown $librex_name
     * @param unknown $searchfor   (optional)
     * @return unknown
     */
    function getMatches($searchfor = null, $parser_name = "Match") {
if (!isset($this->librex)) {$this->getLibrex($this->librex_name);}

        $contents = $this->librex;

        $this->matches = array();
        $separator = "\r\n";
        $line = strtok($contents, $separator);

        while ($line !== false) {



//            $word = $this->parseMatch($line);
$parse_function = "parse".ucfirst($parser_name);
            $word = $this->$parse_function($line);


            $line = strtok( $separator );
                if ($word == false) {continue;}

            $this->matches[$word['proword']] = $word;
            // do something with $line
//            $line = strtok( $separator );
        }

        if ($searchfor == null) {return null;}
$this->matches = array();
        // devstack add \b to Word
        $pattern = preg_quote($searchfor, '/');

        // finalise the regular expression, matching the whole line
        //        $pattern = "/^.*". strtolower($pattern). ".*\$/m";
        $pattern = "/^.*\b". strtolower($pattern). "\b.*\$/m";
        //        $pattern = "/^.*\b". strtolower($pattern). "\b.*$/m";
        //$pattern = "/^.*". strtolower($pattern). ".*\$/m";

        //$pattern = '/^.*\b' . strtolower($searchfor) . '\b.*$/m';

        // search, and store all matching occurences in $matches
        $m = false;
        if (preg_match_all($pattern, strtolower($contents), $matches)) {

            //var_dump($matches[0]);
            foreach ($matches[0] as $match) {

            $word = $this->$parse_function($match);

//                $word = $this->parseMatch($match);
                if ($word == false) {continue;}

                // Multiple matches.
                $this->matches[$word['proword']][] = $word;
            }
        }
        if (!isset($this->matches)) {$this->matches = array();}


        return $m;
    }


    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function parseArrl($text) {

        //        $dict = explode(",", $text);
        //if ($this->librex == "arrl") {$comma = "";}
        //$dict=explode($comma,str_replace(array('  ', '--',':',';'),$comma,$text));
        $dict=explode("  ", str_replace(array('--', ':', ';'), "  ", $text));

        $dict = array_values(array_filter($dict, 'strlen'));
        return $dict;
    }

    function parseCSV($text) {

                $dict = explode(",", $text);

$words = null;
$instruction = null;
$english_phrases = $text;


        $parsed_line = array("proword"=>$dict[0], "words"=>$words,
            "instruction"=>$instruction, "english"=>$english_phrases);
        return $parsed_line;


        return $parsed_line;
    }


function hasUrl($text) {

    $reg_exUrl = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";

    // Check if there is a url in the text
    if(preg_match($reg_exUrl, $text, $url)) {
return true;
    }
return false;

}
/*
private function parseCsv($text) {

        if (mb_substr($test, 0, 1) == "#") {$word = false; return $word;}

$dict = explode("/", $test);

$words = null;
$instruction = null;
$english_phrases = $text;

        $parsed_line = array("proword"=>$dict[0], "words"=>$words,
            "instruction"=>$instruction, "english"=>$english_phrases);
        return $parsed_line;

}
*/

    /**
     *
     * @param unknown $test
     * @return unknown
     */
    private function parseMatch($test) {
        if (mb_substr($test, 0, 1) == "#") {$word = false; return $word;}

$dict = explode("/", $test);
if ($this->hasUrl($test)) {
    $dict[0] = $test;
//var_dump($dict);
}

//        $dict = explode("/", $test);

        if ( (!isset($dict[1])) or (!isset($dict[2])) ) {
        }

        foreach ($dict as $index=>$phrase) {
            if ($index == 0) {continue;}
            if ($phrase == "") {continue;}
            $english_phrases[] = $phrase;
        }
        $text =  $dict[0];

        $dict = explode(",", $text);
        $proword = trim($dict[0]);




        $dict = explode(",", $text);
        //$comma = ",";

        // Special instructions for ARRL librex.

        if ($this->librex_name == "arrl") {$dict = $this->parseArrl($text);}


        $proword = trim($dict[0]);
        //if (strlen($proword) > 10) {$proword = "N/A";}





        //        $words = trim($dict[1]);

        $words = null;
        $instruction = null;
        $english_phrases = null;
        if (isset($dict[1])) {$words = trim($dict[1]);}
        if (!isset($dict[1])) {

            $words = trim($dict[0]);
            $proword = strtoupper(trim(explode(" ", $dict[0])[0]));

        }


        if (isset($dict[2])) {$english_phrases = trim($dict[2]);}
        if (isset($dict[3])) {$instruction = trim($dict[3]);}

$prowords_count = count(explode($proword," "));
if ($prowords_count >= 3) {
$proword = "INJECT";
$words = $line;
$english_phrases = null;
$instruction = null;

}

        $parsed_line = array("proword"=>$proword, "words"=>$words,
            "instruction"=>$instruction, "english"=>$english_phrases);
        return $parsed_line;


    }


    /**
     *
     * @return unknown
     */
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

        $this->thing_report['help'] = "Reads the short message for matches against a librex.";

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
        $html = "<b>LIBREX " . $input . " </b>";
        $html .= "<p><br>";

        if (isset($this->matches)) {

            foreach ($this->matches as $proword=>$word) {
                // Use the first match.
                $word = $word[0];
                $line = "<b>" . strtoupper($word["proword"]) . "</b> " . $word["words"];
                if ($word["words"] == null) {continue;}
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
    function findProword($librex, $search_text) {


    }


    /**
     *
     */
    function makeSMS() {
        $sms = "LIBREX ";

        $sms .= strtoupper($this->librex_name) . " | ";

        $response_text = $this->response;
        if ($this->response == null) {
            $response_text = "X";
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
    function prowordString($word) {
        $proword = $word['proword'];
        $words = $word['words'];
        $instruction = $word['instruction'];
        $english = $word['english'][0];

        $word_string = $proword . " " . $words . " " . $instruction . " " . $english;
        return $word_string ;
    }


    /**
     *
     */
    function makeEmail() {
        $this->email_message = "PROWORD | ";
    }


    /**
     *
     * @return unknown
     */
    public function test() {
        $short_input = "wrong";
        $short_input = "standby";

        $input = "agent proword wrong";
        return $input;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->response = "";

//        $this->input = $this->agent_input;

        $librexes = array('acp125g', 'compression', 'arrl');



        if (($this->agent_input == null) or (strtolower($this->agent_input) == "librex")) {
            $this->input = $this->subject;
            $text = $this->input;
        } else {

            $text = $this->agent_input;
            $words = explode(" ", $this->agent_input);
            foreach ($words as $index=>$word) {
                foreach ($librexes as $index=>$strip_word) {


                    $whatIWant = $text;
                    if (($pos = strpos(strtolower($text), $strip_word. " is")) !== FALSE) {
                        $whatIWant = substr(strtolower($text), $pos+strlen($strip_word . " is"));
                    } elseif (($pos = strpos(strtolower($text), $strip_word)) !== FALSE) {
                        $whatIWant = substr(strtolower($text), $pos+strlen($strip_word));
                    }

                    $text = $whatIWant;
                }
            }

        }

        /*(

        $match = false;
        foreach ($librexes as $librex_candidate) {
            if (strpos(strtolower($this->input), strtolower($librex_candidate)) !== false) {
                $match = true;
                break;
            }
        }

        if ($match == true) {
            $this->librex_name  = $librex_candidate;
        }
*/
//        $librex_candidate = $this->agent_input;

$this->librex_name = $this->agent_input;

        //        if (!isset($librex_name)) {$librex_name = $this->default_librex_name;}
        //        if (!isset($this->librex_name)) {$librex_name = $this->default_librex_name;}
        //        $this->librex_name = $librex_name;


        if (strtolower($this->input) == "librex") {
            $this->prowordThing();
            $this->response = "Retrieved a message with Librex in it.";
            return;
        }
        // Ignore "proword is" or "proword"


        $whatIWant = $text;

        //        $whatIWant = $this->input;
        if (($pos = strpos(strtolower($whatIWant), "librex is")) !== FALSE) {
            $whatIWant = substr(strtolower($whatIWant), $pos+strlen("librex is"));
        } elseif (($pos = strpos(strtolower($whatIWant), "librex")) !== FALSE) {
            $whatIWant = substr(strtolower($whatIWant), $pos+strlen("librex"));
        }

        /*
// Do the same
        if (($pos = strpos(strtolower($whatIWant), "arrl is")) !== FALSE) {
            $whatIWant = substr(strtolower($whatIWant), $pos+strlen("arrl is"));
        } elseif (($pos = strpos(strtolower($whatIWant), "arrl")) !== FALSE) {
            $whatIWant = substr(strtolower($whatIWant), $pos+strlen("arrl"));
        }
*/


        // Clean input
        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $string_length = mb_strlen($filtered_input);
return;

        //var_dump($filtered_input);
        //        $this->extractLibrexes($filtered_input);
        //        $this->has_librexes = $this->isLibrex($filtered_input);

        //var_dump($this->librexes);
        //exit();

        if ($this->isLibrex($filtered_input)) {

            $this->getMatches($filtered_input);

        }

        $ngram = new Ngram($this->thing, "ngram");
        $ngram->extractNgrams($filtered_input, 3);

        $search_phrases = $ngram->ngrams;

        usort($search_phrases, function($a, $b) {
                return strlen($b) <=> strlen($a);
            });

        foreach ($search_phrases as $search_phrase) {
            $this->getMatches($search_phrase);
        }
        $this->filtered_input = $filtered_input;


        if (true) {

            if (count($this->matches) == 0 ) {
                $this->response = "No match found.";
                return;
            }


            if (count($this->matches) ==1 ) {
                $key   = key($this->matches);
                $value = reset($this->matches);
                // Use first match. For now.
                $k = strtoupper($key);
                $w = $value[0]['words'];

                if (strtolower($k) == strtolower($w)) {

                    $k = strtoupper(explode(" ", $w)[0]);

                    //

                }
                $this->response = $k . " " . $w;
                return;
            }

        }

        // devstack closeness

        if (!isset($this->matches)) {$this->response .= "No matches found. ";return;}




//$this->getMatch($filtered_input);
$this->getMatch($filtered_input);



    }

function getMatch($text) {

$this->getMatches($text);

//var_dump($text);
$filtered_input = trim($text);

      $this->results = $this->matches;
        $words = explode(" " , $filtered_input);

        $closest = 0;
//var_dump($this->results);
        foreach ($this->results as &$result) {
//        foreach ($this->results as &$result) {

//var_dump($result);
if ($result == false) {continue;}
if (!isset($result[0])) {continue;}

if ($result[0] == null) {continue;}

$r = $result[0];
  //              $p_words = explode(" " , $result[0]['words']);


                $p_words = explode(" " , $r['words']);
            $closeness = 0;
            foreach ($words as $word) {
                // For now only use the first match

                foreach ($p_words as $p_word) {
                    // Ignore 1 and 2 letter words
                    if (strlen($word) <= 2) {continue;}
                    if ( strtolower( $word) == strtolower($p_word)) {$closeness += 1;}

                }
                if ($closeness > $closest) {$closest = $closeness; $best_proword = $r;}
            }
        }



        $sms = "";
        $count = 0;
        $flag_long = false;

        foreach ($this->matches as $proword=>$word) {
            if (mb_strlen($sms) > 140) {$flag_long = true;}
if (!isset($word[0])) {continue;}
            $sms .= strtoupper($word[0]["proword"]) . " " . $word[0]['words']. " / ";

if (!isset($best_proword)) {
$best_proword = $word[0];
}

            $count += 1;
        }

        // If too long, then try without the definition.
        if ($flag_long) {
            $sms = "";
            $flag_long = false;
            foreach ($this->matches as $proword=>$word) {
                if (mb_strlen($sms) > 140) {$flag_long = true;}
if (!isset($word[0])) {continue;}
                $sms .= strtoupper($word[0]["proword"]) . " / ";

if (!isset($best_proword)) {
$best_proword = $word[0];
}

               $count += 1;
            }
        }

//var_dump($this->results);

        // If still too long, select the 'best' proword.
        if ($flag_long) {
            //            foreach ($this->matches as $proword=>$word) {
            //                if (mb_strlen($sms) > 131) {$sms .= "TEXT WEB";break;}
            //                $sms .= $word["proword"] . " / ";
            //                $count += 1;
            $sms = strtoupper($best_proword["proword"]) . " " . $best_proword['words'];
            //            }
            //            $this->response = $sms;
            $this->hits = $count;
        }

if (!isset($best_proword)) {
$best_proword = null;
//var_dump($this->matches);


$bear_name = "ted";
$bear_response = "Quiet.";
$min_lev = 1e99;
foreach($this->matches as $index=>$match) {
//shuffle($bears);
//foreach($bears as $key=>$value) {

$librex_text = $match['proword']. " " .$match['words'] ." " .$match['instruction'] ." " .$match['english'] . "\n";

if (stripos($librex_text, $filtered_input) !== false) {
$best_proword = $match;
$sms = $librex_text;
break;
}

}





}
$this->best_match = $best_proword;

        $this->response = $sms;



}

}

