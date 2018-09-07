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

//        $this->thing_report  = array("thing"=>$this->thing->thing);
        $this->thing_report['thing'] = $this->thing->thing;

	    $this->uuid = $thing->uuid;

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/callsign/';
        //$this->resource_path_ewol = $GLOBALS['stack_path'] . 'resources/ewol/';



        if (!isset($thing->to)) {$this->to = null;} else {$this->to = $thing->to;}
        if (!isset($thing->from)) {$this->from = null;} else {$this->from = $thing->from;}
	    if (!isset($thing->subject)) {$this->subject = $agent_input;} else {$this->subject = $thing->subject;}


		$this->sqlresponse = null;

		$this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid .'.');
		$this->thing->log($this->agent_prefix . 'received this Thing "' . $this->subject .  '".');

//        $test = "6     U+1F604     😄   grinning face with smiling eyes     eye | face | grinning face with smiling eyes | mouth | open | smile";


//        $string =  $this->subject;

//        $words =$this->extractWord($string);

//        $this->getWord();


        $this->keywords = array();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("callsign", "refreshed_at") );

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("callsign", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("callsign", "reading") );

            $this->readSubject();

            $this->thing->json->writeVariable( array("callsign", "reading"), $this->reading );

            if ($this->agent_input == null) {$this->Respond();}

        if (count($this->callsigns) != 0) {

		    $this->thing->log($this->agent_prefix . 'completed with a reading of ' . implode($this->callsigns[0]) . '.');


        } else {
                    $this->thing->log($this->agent_prefix . 'did not find callsigns.');
        }

        $this->thing->log($this->agent_prefix . 'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');
        $this->thing_report['log'] = $this->thing->log;
	}


    function getCallsigns($test)
    {
        if ($test == false) {
            return false;
        }

        $new_callsigns = array();

        if ($test == "") {return $new_callsigns;}

        $pattern = '/([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC5[\x92\x93\xA0\xA1\xB8\xBD\xBE]){1,}/';
  //      $t = explode("  ", $test);
        $t = preg_split($pattern, $test);

        //$n = count($t)-1;
        //echo $n;
        //$words = explode(" | ", $t[4] );
        //$new_words = array();

        foreach($t as $key=>$callsign) {
            $new_callsigns[] = trim($callsign);
        }
//
//var_dump($new_words);
        return $new_callsigns;
    }


    public function stripPunctuation($input, $replace_with = " ")
    {
        $unpunctuated = preg_replace('/[\:\;\/\!\?\#\.\,\'\"\{\}\[\]\<\>\(\)]/i', $replace_with, $input);
        return $unpunctuated;
    }

    function extractCallsigns($string)
    {
//var_dump($string);

        //$pattern = '/([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC5[\x92\x93\xA0\xA1\xB8\xBD\xBE]){2,}/';
        $pattern = '/\b\w*?\p{N}\w*\b/u';
        preg_match_all($pattern, $string, $callsigns);
        $w = $callsigns[0];
$w = array($string);
//var_dump($w);


//exit();

        //$w = strtoupper($string);

//echo implode("_",$w) . "\n";


        $this->callsigns = array();

        foreach ($w as $key=>$value) {

            // Return dictionary entry.
            $value = $this->stripPunctuation($value);
//var_dump($value);
            $text = $this->findCallsign('list', $value);

$a = explode(";", $text);
//var_dump($a[0]);
//var_dump($a[1]);
//var_dump($a[2]);

//exit();
$callsign = array("callsign"=>$a[0], "first_name"=>$a[1], "second_name"=>$a[2]);
//var_dump($callsign);
//exit();

            if ($text != false) {
                 //   echo "callsign is " . $text . "\n";
                $this->callsigns[] = $callsign;
            } else {
                 //   echo "callsign is not " . $value . "\n";
            }
        }

        if (count($this->callsigns) != 0) {
            $this->callsign = $this->callsigns[0];
        } else {
//            $text = $this->nearestWord($value);
//echo $text;
//exit();
            $this->callsign = null;
        }
var_dump($this->callsigns);
exit();

        return $this->callsigns;
    }


    function getCallsign() {
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

            case 'mordok':
                if (isset($this->mordok_list)) {$contents = $this->mordok_list;break;}

                $file =  $this->resource_path . 'mordok.txt';
                $contents = file_get_contents($file);
                $this->mordok_list = $contents;
                break;
            default:
                $file = $this->resource_path .  'amateur_delim.txt';

        }
//var_dump($searchfor);
//$searchfor = "(nicholas|taylor)";
        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line

//$pattern = "/(?=.*ve7ntx)(?=.*nicholas)/i";
        $pattern = "/^.*". $pattern. ".*\$/mi";

$regex_pieces = "";
$pieces = explode(" ", $searchfor);
foreach($pieces as $piece) {
    $regex_pieces .= "(?=.*" . $piece . ")";
}
$pattern = "/^" . $regex_pieces . ".*$/mi";

//var_dump($regex_pieces);
//exit();

//$pattern = "/^(?=.*nicholas)(?=.*taylor).*$/mi";

//var_dump($pattern);
//exit();
        // search, and store all matching occurences in $matches
        $m = false;
        if(preg_match_all($pattern, $contents, $matches)){


//echo "meep";
//$searchfor = strtoupper($searchfor);

       // $pattern = "|\b($searchfor)\b|";

        // search, and store all matching occurences in $matches
//        if(preg_match_all($pattern, $contents, $matches)){
            $m = $matches[0][0];
//var_dump($matches);
//exit();
            return $m;
        } else {
            return false;
        }

//var_dump($matches);
//exit();

        return;
    }
/*
    function nearestCallsign($input)
    {
//var_dump($input);
                $file = $this->resource_path . 'amateur_delim.txt';
                $contents = file_get_contents($file);

                $file = $this->resource_path . 'special_callsign.txt';
                $contents .= file_get_contents($file);


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

//		$status = false;//
//		$this->response = false;

//		$this->thing->log( "this reading:" . $this->reading );




        // Make SMS
        $this->makeSMS();
		$this->thing_report['sms'] = $this->sms_message;

        // Make message
		$this->thing_report['message'] = $this->sms_message;

        // Make email
        $this->makeEmail(); 

//        $this->thing_report['email'] = array('to'=>$this->from,
//                'from'=>'emoji',
//                'subject' => $this->subject,
//                'message' => $this->email_message,
//                'choices' => false);

//		$email = new Makeemail($this->thing);
//		$this->thing_report['email'] = $email->thing_report['email'];
        $this->thing_report['email'] = $this->sms_message;

            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;




            $this->reading = count($this->callsigns);
            $this->thing->json->writeVariable(array("callsign", "reading"), $this->reading);



		return $this->thing_report;
	}


    function makeSMS() {

//var_dump($this->words);
    if (isset($this->callsigns)) {

        if (count($this->callsign) == 0) {
            //if (isset($this->nearest_callsign)) {
            //    $this->sms_message = "CALLSIGN | closest match " . $this->nearest_callsign;
            //} else {
                $this->sms_message = "CALLSIGN | no callsigns found";
            //}

//            $this->sms_message = "WORD | no words found";
            return;
        }


        if ($this->callsigns[0] == false) {
            //if (isset($this->nearest_callsign)) {
            //    $this->sms_message = "CALLSIGN | closest match " . $this->nearest_callsign;
            //} else {
                $this->sms_message = "CALLSIGN | no callsigns found";
            //}
            return;
        }

        if (count($this->callsigns) > 1) {
            $this->sms_message = "CALLSIGNS ARE " . count($this->callsigns) . " ";
        } elseif (count($this->callsigns) == 1) {
            $this->sms_message = "CALLSIGN IS ";
        }
        $this->sms_message .= implode(" ",$this->callsigns[0]);
        return;
    }

        $this->sms_message = "CALLSIGN | no match found";
   return;
    }


    function makeEmail() {

        $this->email_message = "CALLSIGN | ";

    }



	public function readSubject() {

//        $this->translated_input = $this->wordsEmoji($this->subject);

        if ($this->agent_input == null) {
        $input = strtolower($this->subject);
        } else {
            $input = strtolower($this->agent_input);
        }

//        if (count($this->words) == 0) {
//            return;
//        }

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

                            if ($this->callsign != null) {return;}
                            //return;

                        default:

                            //echo 'default';

                    }

                }
            }

        }

        //$this->nearest_callsign = $this->nearestCallsign($this->search_callsigns);
//var_dump($this->word);
        //$this->extractWords($input);

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
