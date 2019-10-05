<?php
/**
 * Callsign.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Callsign extends Agent {

    // devstack

    // https://www.ic.gc.ca/eic/site/025.nsf/eng/h_00004.html
    // Download regularly


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init() {
        $this->start_time = microtime(true);

        $this->assert_callsign = false;
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/callsign/';

        $this->keywords = array();

        $this->thing_report['help'] = "Looks up callsigns.";
        $this->thing_report['info'] = "Possibly helpful to station operators.";
    }


    /**
     *
     */
    function get() {

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("callsign", "refreshed_at") );

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("callsign", "refreshed_at"), $time_string );
        }

        $this->reading = $this->thing->json->readVariable( array("callsign", "reading") );


        $callsign = new Variables($this->thing, "variables callsign " . $this->from);

        $this->callsign_text = $callsign->getVariable("callsign");
        $this->refreshed_at = $callsign->getVariable("refreshed_at");
    }


    /**
     *
     */
    function run() {

    }


    /**
     *
     */
    function set() {

        $this->makeSMS();


        //  $this->thing_report['sms'] = strtoupper($this->agent_name) . " | " . $this->response;

        $this->thing->json->writeVariable( array("callsign", "reading"), $this->reading );

        $callsign = new Variables($this->thing, "variables callsign " . $this->from);

        if ($this->assert_callsign) {
            $callsign->setVariable("callsign", $this->callsign["callsign"]);
            $callsign->setVariable("refreshed_at", $this->refreshed_at);
        }

    }


    /**
     *
     * @param unknown $input
     */
    function assertCallsign($input) {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "callsign is")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("callsign is"));
        } elseif (($pos = strpos(strtolower($input), "callsign")) !== FALSE) {
            $whatIWant = substr(strtolower($input), $pos+strlen("callsign"));
        }
        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $callsigns = $this->extractCallsigns($filtered_input);

        $this->callsign = reset($callsigns);
        $this->assert_callsign = true;
    }


    /**
     *
     * @param unknown $test
     * @return unknown
     */
    function getCallsigns($test) {
        if ($test == false) {
            return false;
        }

        $new_callsigns = array();

        if ($test == "") {return $new_callsigns;}

        $pattern = '/([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC5[\x92\x93\xA0\xA1\xB8\xBD\xBE]){1,}/';
        $t = preg_split($pattern, $test);

        foreach ($t as $key=>$callsign) {
            $new_callsigns[] = trim($callsign);
        }
        return $new_callsigns;
    }


    /**
     *
     * @param unknown $input
     * @param unknown $replace_with (optional)
     * @return unknown
     */
    public function stripPunctuation($input, $replace_with = " ") {
        $unpunctuated = preg_replace('/[\:\;\/\!\?\#\.\,\'\"\{\}\[\]\<\>\(\)]/i', $replace_with, $input);
        return $unpunctuated;
    }


    /**
     *
     * @param unknown $x
     * @return unknown
     */
    function isDate($x) {
        $date_array = date_parse($x);

        if (($date_array['day'] != false) and ($date_array['month'] != false) and ($date_array['year'] != false)) {
            return true;
        }
        return false;
    }


    /**
     *
     * @param unknown $string
     * @return unknown
     */
    function extractCallsigns($string) {
        $pattern = '/\b\w*?\p{N}\w*\b/u';
        preg_match_all($pattern, $string, $callsigns);
        $w = $callsigns[0];

        $w = array($string);

        $this->callsigns = array();

        foreach ($w as $key=>$value) {

            // Return dictionary entry.
            $value = $this->stripPunctuation($value);
            $text = $this->findCallsign('list', $value);

if ($text == true) {return true;}

            foreach ($text as $x) {
                $line = $x['line'];
                $line = utf8_encode($line);
                $a = explode(";", $line);
                $t = $a[1];
                if ( $this->isDate($t) ) {

                    $callsign = array("callsign"=>$a[0], "first_name"=>$a[3], "second_name"=>$a[4]);

                } else {
                    $callsign = array("callsign"=>$a[0], "first_name"=>$a[1], "second_name"=>$a[2]);
                }


                if ($text != false) {
                    //   echo "callsign is " . $text . "\n";
                    $this->callsigns[$a[0]] = $callsign;
                } else {
                    //   echo "callsign is not " . $value . "\n";
                }
            }
        }

        if (count($this->callsigns) != 0) {

            $this->callsign = (reset($this->callsigns));

        } else {
            $this->callsign = null;
        }
        return $this->callsigns;
    }


    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function getCallsign($text = null) {

        if (!isset($this->callsigns)) {
            $this->extractCallsigns($text);
        }

        if (count($this->callsigns) == 0) {
            $this->callsign = false;
            return $this->callsign;
        }

        $this->callsign = reset($this->callsigns);
        return $this->callsign;
    }


    /**
     *
     * @param unknown $librex
     * @param unknown $searchfor
     * @return unknown
     */
    function findCallsign($librex, $searchfor) {
        if (($librex == "") or ($librex == " ") or ($librex == null)) {return false;}

        switch ($librex) {
        case null:
            // Drop through
        case 'list':
            if (isset($this->callsigns_list)) {$contents = $this->callsigns_list;break;}
            $file = $this->resource_path . 'amateur_delim.txt';
            $pre_contents = @file_get_contents($file);

if ($pre_contents == false) {return true;}

            // Remove address info from search space.
            $arr = explode("\n", $pre_contents);
            $contents = "";
            foreach ($arr as $key=>$line) {
                $fields = explode(";", $line);
                if (!isset($fields[1])) {continue;}

                $contents .= $fields[0] .";". $fields[1] . ";" . $fields[2] . "\n";
            }

            $file = $this->resource_path . 'special_callsign.txt';
            $contents .= file_get_contents($file);


            $this->callsigns_list = $contents;
            break;
        default:
            $file = $this->resource_path .  'amateur_delim.txt';

        }

        $line_matches = array();

        foreach (explode(" ", $searchfor) as $word) {

            $regex_pieces = "(?=.*" . $word . ")";
            $pattern = "/^" . $regex_pieces . ".*$/mi";

            // search, and store all matching occurences in $matches
            $m = false;

            preg_match_all($pattern, $contents, $matches);
            $line_matches = array_merge($line_matches, $matches[0]);
        }

        $best_score = 0;

        $sorted_matches = array();
        $test_array = array();
        foreach ($line_matches as $line) {
            $score = $this->getCloseness($line, $searchfor);

            if ($score != 0) {
                // Add to the bottom.
                $test_array[] = array("line"=>$line, "score"=>$score);
                //            $sorted_matches[] = $line;

            }
        }

        $score = array();
        foreach ($test_array as $key => $row) {
            $score[$key] = $row['score'];
        }
        array_multisort($score, SORT_DESC, $test_array);

        $i = 0;
        foreach ($test_array as $key=>$value) {
            //echo $value['score'] . " ". $value['line'] . "\n";
            $i += 1;
            if ($i >10) {break;}
        }
        return $test_array;
    }


    /**
     *
     * @param unknown $line
     * @param unknown $text
     * @return unknown
     */
    function getCloseness($line, $text) {

        $words = preg_split('/[^a-z0-9.\']+/i', $line);
        $score = 0;
        foreach (explode(" " , $text) as $text_word) {
            foreach ($words as $word) {

                if (($word == "") or ($text_word == "")) {continue;}

                if (strtolower($word) == strtolower($text_word)) {
                    $score += mb_strlen($text_word) * 10;
                    break;
                }

                if (strpos(strtolower($word), strtolower($text_word)) !== false) {
                    $score += 2;
                }

                $lev = levenshtein(strtolower($text_word), strtolower($word));
                if (mb_strlen($text_word) != $lev) {
                    $score += 1;
                }

                if (mb_substr(strtolower($text_word), 0, 3) == mb_substr(strtolower($word), 0, 3)) {
                    $score += 2;
                }


                if (mb_substr(strtolower($text_word), 0, 1) == mb_substr(strtolower($word), 0, 1)) {
                    //echo $text ." " . $word . "\n";
                    $score += 1;
                }
            }
        }
        return $score;
    }


    /**
     *
     * @return unknown
     */
    public function respondResponse() {
        //  public function respond() {

        $this->cost = 100;

        // Thing stuff
        $this->thing->flagGreen();

        // Compose email

        // Make SMS
        $this->makeSMS();
        $this->thing_report['sms'] = $this->sms_message;

        // Make message
        $this->thing_report['message'] = $this->sms_message;

        // Make email
        $this->makeEmail();

        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;


        $this->makeWeb();

        $this->reading = "X";
        if (isset($this->callsigns)) {
            $this->reading = count($this->callsigns);
        }
        $this->thing->json->writeVariable(array("callsign", "reading"), $this->reading);



        return $this->thing_report;
    }


    /**
     *
     */
    function makeSMS() {

        //$callsign_text = (implode(" ",$this->callsign));
        $r = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $this->response);

        //            $this->sms_message .= $callsign_text;


        $sms = "CALLSIGN | " . $r;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
        return;
    }


    /**
     *
     */
    function makeWeb() {
        $web = "";

        //        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        //        $web = '<a href="' . $link . '">';
        //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/flag.png">';
        //        $web .= $this->html_image;

        $web .= "</a>";
        $web .= "<br>";
        $web .= '<b>Callsign Agent</b><br>';
        $web .= "<p>";
        $web .= $this->sms_message;
        $web .= "<p>";
        if (isset($this->callsigns)) {
            foreach ($this->callsigns as $id=>$callsign) {
                $callsign_text = $callsign["callsign"] . " " . $callsign["first_name"];
                $web .= "<br>" . $callsign_text;
            }
        }

        $this->web_message = $web;
        $this->thing_report['web'] = $web;
    }



    /**
     *
     */
    function makeEmail() {

        $this->email_message = "CALLSIGN | " . $this->response;

    }



    /**
     *
     * @return unknown
     */
    public function readSubject() {
        //        if ($this->agent_input == null) {
        //            $this->input = strtolower($this->subject);
        //        } else {
        //            $this->input = strtolower($this->agent_input);
        //        }
        //var_dump($this->input);


        $prefix = 'callsign';
        $callsigns = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $this->input);
        $callsigns = ltrim($callsigns);
        $this->search_callsigns = $callsigns;
        $this->extractCallsigns($callsigns);



        $keywords = array('is', 'callsign');
        $pieces = explode(" ", strtolower($this->input));

        if (count($pieces) == 1) {
            if ($this->input == 'callsign') {
                //                $this->getCallsigns($this->callsign_text);
                $this->extractCallsigns($this->callsign_text);
                if (isset($this->callsign)) {
                    $this->response = $this->callsign["callsign"] . " " . $this->callsign["first_name"] . ". " . "Asserted callsign retrieved.";
                    return;
                }

                $this->response = "No match found.";
                return;
            }

        }

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {

                    switch ($piece) {

                    case 'is':
                        $this->assertCallsign(strtolower($this->input));

                        if (empty($this->callsign)) {
                            $this->response = "Did not find a callsign.";
                        } else {

                            $this->response = 'Callsign asserted to be ' . strtoupper($this->callsign["callsign"]) .".";
                        }

                        return;

                    default:
                        //echo 'default';
                    }
                }
            }
        }

        $first_name = $this->callsign["first_name"];

        // If more than one first name is returned.
        $arr = explode(" ", $first_name);
        if (count($arr) >= 2) {
            if (strlen($arr[1]) != 1) {$first_name = $arr[0];}
        }

        if (!isset($this->callsigns)) {
            $this->response = "No match found.";
            return;
        }

        if (count($this->callsigns) > 1) {
            $this->response = "Found " . count($this->callsigns) . " callsigns. Best " . $this->callsign["callsign"]. " ". $first_name . ".";
            return;
        }

        if (count($this->callsigns) == 1) {
            $this->response = "Found " . $this->callsign["callsign"]. " ". $first_name . ".";
            return;
        }

        $this->response = "No match found.";
    }


}
