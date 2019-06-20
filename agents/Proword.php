<?php
namespace Nrwtaylor\StackAgentThing;

// An agent to recognize and understand Prowords.

class Proword extends Word
{
    function init()
    {

        $this->hits = 0;

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->keywords = array();
        $this->keyword = "proword";

        $this->getProwords();

	}

    function run()
    {
        $string =  strtolower($this->subject);

        $this->keyword = "proword";
        $this->thingreportProword();
    }

    function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("proword", "refreshed_at") );

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->json->writeVariable( array("proword", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("proword", "reading") );
    }

    function set()
    {
        $this->thing->json->writeVariable( array("proword", "reading"), $this->has_prowords );

$this->thing_report['help'] = "Reads the short message for prowords.";
    }

    function thingreportProword()
    {
        $this->thing_report['log'] = $this->thing->log;
$this->thing_report['help'] = "Reads the short message for prowords.";

    }

    function prowordThing()
    {
        // Get all of this users Things
        // To search for the last Proword text provided.
        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(''); // Designed to accept null as $this->uuid.

        $things = $thingreport['thing'];

        // Get the earliest from the current data set
        foreach (array_reverse($things) as $thing) {
            $this->extractProwords ( $thing['task'] );
            if ($this->prowords != array()) {break;}
        }
    }

    function findWord($librex, $searchfor)
    {
        $this->findProword($librex, $searchfor);
    }

    function getWords($test)
    {
        $this->getProwords($test);
    }

    function isProword($test)
    {
        $this->getProwords('acp125g');
        $match = false;
        foreach ($this->prowords as $proword=>$arr) {
            if ($proword == "") {continue;}
            if (strpos(strtolower($test), strtolower($proword)) !== false) {
                $match = true; break;
            }
        }

        return $match;

    }

    function extractProwords($text)
    {
        $words = explode(" ", $text);

        $this->getProwords('acp125g');
        $prowords_list = array();

        foreach ($this->prowords as $proword=>$arr) {
            if ($proword == "") {continue;}
            if (strpos(strtolower($text), strtolower($proword)) !== false) {
                $prowords_list[] = $proword;
            }
        }
        $this->extracted_prowords = $prowords_list;
        return $prowords_list;
    }

    function countProwords($text)
    {

        $words = explode(" ",$text);

        $this->getProwords('acp125g');
        $count = 0;
        foreach($words as $word) {
            foreach ($this->prowords as $proword=>$arr) {
                if ($proword == "") {continue;}
                if (strpos(strtolower($word), strtolower($proword)) !== false) {
                    $count += 1; break;
                }
            }
        }

        return $count;
    }


    function extractProword($string)
    {
        // devstack
    }

    function getLibrex($librex)
    {
        // Look up the meaning in the dictionary.
        if (($librex == "") or ($librex == " ") or ($librex == null)) {return false;}

        switch ($librex) {
            case null:
                // Drop through
            case 'prowords':
                $file = $this->resource_path .'proword/prowords.txt';
                break;
            case 'acp125g':
                $file = $this->resource_path .'proword/prowords.txt';
                break;
            case 'vector':
                $file = $this->resource_path . 'proword/vector.txt';
                break;
            default:
                $file = $this->resource_path . 'proword/prowords.txt';
        }
        $this->librex = file_get_contents($file);


    }

    function getProwords($librex, $searchfor = null)
    {
        $this->getLibrex($librex);
        $contents = $this->librex;


        $this->prowords = array();
            $separator = "\r\n";
            $line = strtok($contents, $separator);

        while ($line !== false) {

            $word = $this->parseProword($line);
            $this->prowords[$word['proword']] = $word;
            # do something with $line
            $line = strtok( $separator );
        }

        if ($searchfor == null) {return null;}



        // devstack add \b to Word
        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        $pattern = "/^.*". strtolower($pattern). ".*\$/m";
        //$pattern = "/^.*". strtolower($pattern). ".*\$/m";

        //$pattern = '/^.*\b' . strtolower($searchfor) . '\b.*$/m';

        // search, and store all matching occurences in $matches
        $m = false;
        if(preg_match_all($pattern, strtolower($contents), $matches)){
            foreach ($matches[0] as $match) {

                $word = $this->parseProword($match);
                if ($word == false) {continue;}
                $this->matches[$word['proword']] = $word;
            }
        }

        if (!isset($this->matches)) {$this->matches = array();}
        return $m;
    }

    private function parseProword($test)
    {
        if (mb_substr($test,0,1) == "#") {$word = false; return $word;}

        $dict = explode("/",$test);

        if ( (!isset($dict[1])) or (!isset($dict[2])) ) {
        }

        foreach($dict as $index=>$phrase) {
            if ($index == 0) {continue;}
            if ($phrase == "") {continue;}
            $english_phrases[] = $phrase;
        }
        $text =  $dict[0];

        $dict = explode(",",$text);
        $proword = trim($dict[0]);
        $words = trim($dict[1]);

        $instruction = null;
        $english_phrases = null;
        $words = trim($dict[1]);
        if (isset($dict[2])) {$english_phrases = trim($dict[2]);}
        if (isset($dict[3])) {$instruction = trim($dict[3]);}


        $parsed_line = array("proword"=>$proword,"words"=>$words,
                    "instruction"=>$instruction, "english"=>$english_phrases);
        return $parsed_line;


    }

    public function respond()
    {
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

            foreach($this->matches as $proword=>$word) {
                $line = "<b>" . strtoupper($word["proword"]) . "</b> " . $word["words"];
                if ($word["words"] == null) {continue;}
                $html .= $line . "<br>";
            }

        }

        $this->web_message = $html;
        $this->thing_report['web'] = $html;
    }

    function findProword($librex,$search_text)
    {


    }

    function makeSMS()
    {
        $sms = "PROWORD | ";

        $response_text = $this->response;
        if ($this->response == null) {
            $response_text = "X";
        }

        $sms .= $response_text;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function prowordString($word)
    {
        $proword = $word['proword'];
        $words = $word['words'];
        $instruction = $word['instruction'];
        $english = $word['english'][0];

        $word_string = $proword . " " . $words . " " . $instruction . " " . $english;
        return $word_string ;
    }

    function makeEmail()
    {
        $this->email_message = "PROWORD | ";
    }

    public function test()
    {
        $short_input = "wrong";
        $short_input = "standby";

        $input = "agent proword wrong";
        return $input;
    }

    public function readSubject()
    {

        $this->response = "";
        $input = $this->subject;

        if (strtolower($input) == "proword") {
            $this->prowordThing();
            $this->response = "Retrieved a message with Proword in it.";
            return;
        }

        // Ignore "proword is" or "proword"
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "proword is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("proword is")); 
        } elseif (($pos = strpos(strtolower($input), "proword")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("proword")); 
        }

        // Clean input
        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $string_length = mb_strlen($filtered_input);

$this->extractProwords($filtered_input);

        $this->has_prowords = $this->isProword($filtered_input);

        $this->getProwords('acp125g', $filtered_input);


        $ngram = new Ngram($this->thing, "ngram");
        $ngram->extractNgrams($filtered_input,3);
        $search_phrases = $ngram->ngrams;
        usort($search_phrases, function($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        foreach($search_phrases as $search_phrase) {
            $this->getProwords('acp125g', $search_phrase);
        }
        $this->filtered_input = $filtered_input;


        if ($this->has_prowords) {

            if (count($this->matches) == 0 ) {
                $this->response = "No proword found."; 
                return;
            }


            if (count($this->matches) ==1 ) {
                $key   = key($this->matches);
                $value = reset($this->matches);
                $this->response = strtoupper($key) . " " . $value['words'];
                return;
            }
        }

        // devstack closeness
        $this->results = $this->matches;
        $words = explode(" " ,$filtered_input);

        $closest = 0;
        foreach ($this->results as &$result) {
            $closeness = 0;
            foreach ($words as $word) {

                $p_words = explode(" " , $result['words']);
                foreach($p_words as $p_word) {

                    // Ignore 1 and 2 letter words
                    if (strlen($word) <= 2) {continue;}

                    if ( strtolower( $word) == strtolower($p_word)) {$closeness += 1;}

                }
                if ($closeness > $closest) {$closest = $closeness; $best_proword = $result;}
             }
        }

        $sms = "";
        $count = 0;
        $flag_long = false;

        foreach ($this->matches as $proword=>$word) {
            if (mb_strlen($sms) > 140) {$flag_long = true;}
            $sms .= strtoupper($word["proword"]) . " " . $word['words']. " / ";
            $count += 1;
        }

        // If too long, then try without the definition.
        if ($flag_long) {
            $sms = "";
            $flag_long = false;
            foreach ($this->matches as $proword=>$word) {
                if (mb_strlen($sms) > 140) {$flag_long = true;}
                $sms .= strtoupper($word["proword"]) . " / ";
                $count += 1;
            }
        }

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

        $this->response = $sms;

   }


}
