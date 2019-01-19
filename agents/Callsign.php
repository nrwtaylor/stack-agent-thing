<?php
namespace Nrwtaylor\StackAgentThing;

class Callsign {

// https://www.ic.gc.ca/eic/site/025.nsf/eng/h_00004.html
// Download regularly


	function __construct(Thing $thing, $agent_input = null)
    {
//echo "meep";
//exit();
//var_dump($agent_input);

        $this->start_time = microtime(true);
        if ($agent_input == null) {}
        $this->agent_input = $agent_input;
		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_prefix = 'Agent "Callsign" ';

        $this->thing_report['thing'] = $this->thing->thing;

	    $this->uuid = $thing->uuid;

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/callsign/';

        if (!isset($thing->to)) {$this->to = null;} else {$this->to = $thing->to;}
        if (!isset($thing->from)) {$this->from = null;} else {$this->from = $thing->from;}
	    if (!isset($thing->subject)) {$this->subject = $agent_input;} else {$this->subject = $thing->subject;}

		$this->sqlresponse = null;

		$this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid .'.');
		$this->thing->log($this->agent_prefix . 'received this Thing "' . $this->subject .  '".');

        $this->keywords = array();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("callsign", "refreshed_at") );

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("callsign", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("callsign", "reading") );

        $this->readSubject();

        $this->thing->json->writeVariable( array("callsign", "reading"), $this->reading );

        if ($this->agent_input == null) {$this->Respond();}

        if (isset($this->callsigns) and count($this->callsigns) != 0) {
		    $this->thing->log($this->agent_prefix . 'completed with a reading of ' . implode($this->callsign) . '.');
        } else {
            $this->thing->log($this->agent_prefix . 'did not find callsigns.');
        }

        $this->thing->log($this->agent_prefix . 'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');
        $this->thing_report['log'] = $this->thing->log;
        $this->thing_report['response'] = $this->response;
	}


    function getCallsigns($test)
    {
        if ($test == false) {
            return false;
        }

        $new_callsigns = array();

        if ($test == "") {return $new_callsigns;}

        $pattern = '/([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC5[\x92\x93\xA0\xA1\xB8\xBD\xBE]){1,}/';
        $t = preg_split($pattern, $test);

        foreach($t as $key=>$callsign) {
            $new_callsigns[] = trim($callsign);
        }
        return $new_callsigns;
    }


    public function stripPunctuation($input, $replace_with = " ")
    {
        $unpunctuated = preg_replace('/[\:\;\/\!\?\#\.\,\'\"\{\}\[\]\<\>\(\)]/i', $replace_with, $input);
        return $unpunctuated;
    }

function isDate($x) {
        $date_array = date_parse($x);

        if (($date_array['day'] != false) and ($date_array['month'] != false) and ($date_array['year'] != false)) {
            return true;
        }
    return false;
}

    function extractCallsigns($string)
    {
        $pattern = '/\b\w*?\p{N}\w*\b/u';
        preg_match_all($pattern, $string, $callsigns);
        $w = $callsigns[0];

        $w = array($string);
        //var_dump($w);

        $this->callsigns = array();

        foreach ($w as $key=>$value) {

            // Return dictionary entry.
            $value = $this->stripPunctuation($value);
            $text = $this->findCallsign('list', $value);

            foreach($text as $x) {
                $line = $x['line'];
                $line = utf8_encode($line);
                $a = explode(";", $line);
                $t = $a[1];
                if ( $this->isDate($t) ) {
//var_dump($a);
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

    function getCallsign()
    {
        if (!isset($this->callsigns)) {
            $this->extractCallsigns($this->subject);
        }
        if (count($this->callsigns) == 0) {$this->callsign = false;return false;}
        $this->callsign = $this->callsigns[0];
        return $this->callsign;
    }

    function findCallsign($librex, $searchfor)
    {
        if (($librex == "") or ($librex == " ") or ($librex == null)) {return false;}

        switch ($librex) {
            case null:
                // Drop through
            case 'list':
                if (isset($this->callsigns_list)) {$contents = $this->callsigns_list;break;}
                $file = $this->resource_path . 'amateur_delim.txt';
                $contents = file_get_contents($file);

                $file = $this->resource_path . 'special_callsign.txt';
                $contents .= file_get_contents($file);


                $this->callsigns_list = $contents;
                break;
            default:
                $file = $this->resource_path .  'amateur_delim.txt';

        }

        $line_matches = array();

        foreach (explode(" ",$searchfor) as $word) {

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
        foreach($line_matches as $line) {
            $score = $this->getCloseness($line,$searchfor);
/*
            //if ($score != 0) {$matches[] = $line;}

            if ($score >= $best_score) {
                // Add to the top
                $best_score = $score;
                $best_match = $line;
                array_unshift($test_array, array("line"=>$line,"score"=>$score));
                array_unshift($sorted_matches, $line);

            } else
*/
            if ($score != 0) {
                // Add to the bottom.
                $test_array[] = array("line"=>$line,"score"=>$score);
    //            $sorted_matches[] = $line;

            }
            //var_dump($score);
        }

$score = array();
foreach ($test_array as $key => $row)
{
    $score[$key] = $row['score'];
}
array_multisort($score, SORT_DESC, $test_array);

$i = 0;
foreach($test_array as $key=>$value) {
    //echo $value['score'] . " ". $value['line'] . "\n";
$i += 1;
if ($i >10) {break;}
}
//var_dump($test_array);
//exit();
//var_dump($sorted_matches);
//exit();
/*
        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line

        $pattern = "/^.*". $pattern. ".*\$/mi";

        $regex_pieces = "";
        $pieces = explode(" ", $searchfor);
        foreach($pieces as $piece) {
            $regex_pieces .= "(?=.*" . $piece . ")";
        }
        $pattern = "/^" . $regex_pieces . ".*$/mi";

        // search, and store all matching occurences in $matches
        $m = false;
        if(preg_match_all($pattern, $contents, $matches)){


//echo "meep";
//$searchfor = strtoupper($searchfor);

       // $pattern = "|\b($searchfor)\b|";

        // search, and store all matching occurences in $matches
            echo $word ." " . $text . "\n";
//        if(preg_match_all($pattern, $contents, $matches)){
           // $m = $matches[0][0];
//var_dump($matches);
//var_dump($m);
            $m = $matches[0];
//exit();
            return $m;
        } else {
            return false;
        }

//var_dump($matches);
//exit();
*/
//var_dump($matches[0]);
//exit();        
//var_dump($matches[0]);
        //return $best_match;
        return $test_array;
    }

    function getCloseness($line, $text)
    {
//$line = 'VA1SAR;Darren   Bruce;Mac Leod;3567 MORLEY AVE;NEW WATERFORD;NS;B1H2"3;;;;;E;;;;;;';
//$text = "darren hodder";

        $words = preg_split('/[^a-z0-9.\']+/i', $line);
        $score = 0;
    foreach(explode(" " ,$text) as $text_word) {
        foreach($words as $word) {
//echo $word . " " ;
           if (strtolower($word) == strtolower($text_word)) {
//echo $word . " " . $text_word . "\n";
                $score += mb_strlen($text_word) * 10;
                break;
            } 

            if (strpos(strtolower($word), strtolower($text_word)) !== false) {
                $score += 2;
            } 

            //else {
                //echo mb_strlen($text);
            $lev = levenshtein(strtolower($text_word), strtolower($word));
            if (mb_strlen($text_word) != $lev) {
               $score += 1;
               //echo $lev. " " . $word . " - " . $text . "\n";
            }

            if (mb_substr(strtolower($text_word), 0, 3) == mb_substr(strtolower($word), 0, 3)) {
                //echo $text ." " . $word . "\n";
               $score += 2;
               //echo $lev. " " . $word . " - " . $text . "\n";
            }


            //$lev = levenshtein($text, $word);
            if (mb_substr(strtolower($text_word), 0, 1) == mb_substr(strtolower($word), 0, 1)) {
                //echo $text ." " . $word . "\n";
               $score += 1;
               //echo $lev. " " . $word . " - " . $text . "\n";
            }
}
        }
//echo $score;
//exit();

  //      echo $score . " - " . $text . " - " . $line . "\n";
        return $score;
    }

/*
    function nearestCallsign($input)
    {
//var_dump($input);
                $file = $this->resource_path . 'amateur_delim.txt';
                $contents = file_get_contents($file);

                $file = $this->resource_path . 'special_callsign.txt';
                $contents .= file_get_contents($file);

v
        $callsigns = explode("\n", $contents);

        $nearness_min = 1e6;
        $callsign = false;

        foreach ($callsigns as $key=>$callsign) {
            $nearness = levenshtein($input, $callsign);
            //$nearness = similar_text($word, $input);

            if ($nearness < $nearness_min) {
                $callsign_list = array();
                $nearness_min = $nearness;
            }
            if ($nearness_min == $nearness) {
                $callsign_list[] = $callsign;

            }

        }

        $nearness_max = 0;
        $callsign = false;

        foreach ($callsign_list as $key=>$callsign) {
            //$nearness = levenshtein($input, $word);
            $nearness = similar_text($callsign, $input);

            if ($nearness > $nearness_max) {
                $new_callsign_list = array();
                $nearness_min = $nearness;
            }
            if ($nearness_min == $nearness) {
                $new_callsign_list[] = $callsign;

            }

        }

        if (!isset($new_callsign_list) or ($new_callsign_list == null)) {
            $nearest_callsign = false;
        } else { 
            $nearest_callsign = implode(" " ,$new_callsign_list);
        }

        return $nearest_callsign;
    }
*/



	public function Respond() {

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


    function makeSMS()
    {

        if (isset($this->callsigns)) {

            if (count($this->callsign) == 0) {
                $this->sms_message = "CALLSIGN | no callsigns found";
                return;
            }

            if ($this->callsign == false) {
                $this->sms_message = "CALLSIGN | no callsigns found";
                return;
            }

            if (count($this->callsigns) > 1) {
                $this->sms_message = "CALLSIGNS ARE " . count($this->callsigns) . " ";
            } elseif (count($this->callsigns) == 1) {
                $this->sms_message = "CALLSIGN IS ";
            }
            $this->sms_message .= (implode(" ",$this->callsign));
            return;
        }

        $this->sms_message = "CALLSIGN | no match found";
        return;
    }


    function makeWeb()
    {
        $html = "";
        if (isset($this->callsigns)) {
            foreach ($this->callsigns as $id=>$callsign) {
                $html .= "<br>" . implode(" ", $callsign);
            }
        }

        $this->web_message = $html;
        $this->thing_report['web'] = $html;

        return;
    }



    function makeEmail() {

        $this->email_message = "CALLSIGN | ";

    }



	public function readSubject()
    {

        if ($this->agent_input == null) {
            $input = strtolower($this->subject);
        } else {
            $input = strtolower($this->agent_input);
        }

        $keywords = array('callsign');
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {

                        case 'callsign':   

                            $prefix = 'callsign';
                            $callsigns = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $callsigns = ltrim($callsigns);
                            $this->search_callsigns = $callsigns;
                            $this->extractCallsigns($callsigns);

                            if ($this->callsign != null) {$this->response = "Found callsign(s)."; return;}

                            $this->response = "Did not find a callsign.";
                            return;

                        default:

                            //echo 'default';

                    }

                }
            }

        }

        //$this->nearest_callsign = $this->nearestCallsign($this->search_callsigns);
//var_dump($this->word);
        //$this->extractWords($input);
        $this->response = "No response.";
		$status = true;
    	return $status;		
	}






    function contextCallsign () 
    {
        $this->callsign_context = '
';

        return $this->callsign_context;
    }
}

?>
