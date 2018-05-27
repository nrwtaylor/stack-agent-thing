<?php
namespace Nrwtaylor\StackAgentThing;

class Emoji
{

	function __construct(Thing $thing, $agent_input = null)
    {

        $this->start_time = microtime(true);
        if ($agent_input == null) {}
        $this->agent_input = $agent_input;
		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_prefix = 'Agent "Emoji" ';

//        $this->thing_report  = array("thing"=>$this->thing->thing);
        $this->thing_report['thing'] = $this->thing->thing;

	    $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
	    $this->subject = $thing->subject;
		$this->sqlresponse = null;

		$this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid .'.');
		$this->thing->log($this->agent_prefix . 'received this Thing "' . $this->subject .  '".');

//        $test = "6     U+1F604     😄   grinning face with smiling eyes     eye | face | grinning face with smiling eyes | mouth | open | smile";


        $string =  $this->subject;

        $emojis =$this->extractEmoji($string);


        $this->getEmoji();


        $searchfor = $this->convert_emoji($this->emoji);

        $arr = explode(" ",$searchfor);
        $this->words = array();

        foreach ($arr as $key=>$value) {
            if ($value == "U+FE0F") {continue;}
            // Return dictionary entry.
            $text = $this->findEmoji('list', $value);
            //echo $value . " " .$text . "<br>";
            $this->words = array_merge($this->getWords($text));
            $this->word = $this->words[0];
        }

        $this->keywords = array();

        foreach ($arr as $key=>$value) {
            $text = $this->findEmoji('mordok', $value); 

            if ($value == "U+FE0F") {continue;}

            $this->keywords = array_merge($this->getWords($text));
            $this->keyword = $this->keywords[0];
        }


        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("emoji", "refreshed_at") );

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("emoji", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        //$this->thing->json->setField("variables");
        $this->reading = $this->thing->json->readVariable( array("emoji", "reading") );

            $this->readSubject();
//        if ( ($this->reading == false) ) {
//            $this->thing->log( $this->agent_prefix . 'no prior reading found.' );

            $this->thing->json->writeVariable( array("emoji", "reading"), $this->reading );
//			$this->readSubject(); // Commented out 4 Dec 2017.  First call if there is a problem.
            if ($this->agent_input == null) {$this->Respond();}
//        }

        if ($this->emoji != false) {

            // So emojis were found.
//            if (strpos($this->agent_input, 'respond') !== false) {
       
//                $this->Respond();
//            }



            $this->thing->log($this->agent_prefix . 'keyword '. $this->keyword . " word  ". $this->word . '.');
		    $this->thing->log($this->agent_prefix . 'completed with a reading of ' . $this->emoji . '.');


        } else {
                    $this->thing->log($this->agent_prefix . 'did not find emojis.');
        }

        $this->thing->log($this->agent_prefix . 'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

        $this->thing_report['log'] = $this->thing->log;


	}

    function getWords($test)
    {
        if ($test == false) {
            return false;
        }
        // $t = explode("  ", $test);
        $t = preg_split("/[\t]/", $test);

        //$n = count($t)-1;
        //echo $n;
        $words = explode(" | ", $t[4] );
        $new_words = array();

        foreach($words as $key=>$word) {
            $new_words[] = trim($word);
        }

        return $new_words;
    }




    function extractEmoji($string)
    {

        preg_match_all('/([0-9#][\x{20E3}])|[\x{00ae}\x{00a9}\x{203C}\x{2047}\x{2048}\x{2049}\x{3030}\x{303D}\x{2139}\x{2122}\x{3297}\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', $string, $emojis);

        //print_r($emojis[0]); // Array ( [0] => 😃 [1] => 🙃 ) 
        $this->emojis = $emojis[0];
        return $this->emojis;
    }

    function wordsEmoji($string)
    {

        if (!isset($this->emojis)) {
            $this->emojis = $this->getEmoji();
        }

//        $string = 'The quick brown fox jumps over the lazy dog.';

        $patterns = array();
//$patterns[0] = '/quick/';
//$patterns[1] = '/brown/';
//$patterns[2] = '/fox/';
        $replacements = array();

foreach($this->emojis as $emoji) {
    $patterns[] = '/' . $emoji . '/';

$text = $this->findEmoji('mordok', $emoji);

$words = $this->getWords($text);

if ($words == false) {
    $word = "?";
} else {
    $word = $words[0];
}
    $replacements[] = " ". $word . " ";
}
$translation = preg_replace($patterns, $replacements, $string);

    
//exit();
        return $translation;
    }

    function getEmoji() {
        if (!isset($this->emojis)) {
            $this->extractEmoji($this->subject);
        }
        if (count($this->emojis) == 0) {$this->emoji = false;return false;}
        $this->emoji = $this->emojis[0];
        return $this->emoji;
    }

    function convertEmoji($emoji)
    {
        $str = str_replace('"', "", json_encode($emoji, JSON_HEX_APOS));

        $myInput = $str;

        $myHexString = str_replace('\\u', '', $myInput);
        $myBinString = hex2bin($myHexString);

        print iconv("UTF-16BE", "UTF-8", $myBinString);
    }

function convert_emoji($emoji) {
    // ✊🏾 --> 0000270a0001f3fe
    $emoji = mb_convert_encoding($emoji, 'UTF-32', 'UTF-8');
    $hex = bin2hex($emoji);

    // Split the UTF-32 hex representation into chunks
    $hex_len = strlen($hex) / 8;
    $chunks = array();

    for ($i = 0; $i < $hex_len; ++$i) {
        $tmp = substr($hex, $i * 8, 8);
        // Format each chunk
        $chunks[$i] = $this->format($tmp);
    }
    // Convert chunks array back to a string
    return implode($chunks, ' ');
}

    function format($str)
    {
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


    function findEmoji($librex, $searchfor)
    {
        if (($librex == "") or ($librex == " ") or ($librex == null)) {return false;}

        $path = $GLOBALS['stack_path'];

        switch ($librex) {
            case null:
                // Drop through
            case 'keywords':
                $file = $path .'resources/emoji/emoji-keywords.txt';
                $contents = file_get_contents($file);
                break;
            case 'data':
                $file = $path . 'resources/emoji/emoji-data.txt';
                $contents = file_get_contents($file);
                break;
            case 'mordok':
                $file = $path . 'resources/emoji/emoji-mordok.txt';
                $contents = file_get_contents($file);

                break;
            case 'list':
                $file = $path . 'resources/emoji/emoji-list.txt';
                $contents = file_get_contents($file);
                break;
            case 'unicode':
                $file = $path . 'resources/emoji/unicode.txt';
                $contents = file_get_contents($file);
                break;
            case 'context':
                $this->contextEmoji();
                $contents = $this->emoji_context;
                $file = null;
                break;
            case 'emotion':
                break;
            default:
                $file = $GLOBALS['stack_path'] . '/resources/emoji-keywords.txt';

        }
//        header('Content-Type: text/plain');
        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        $pattern = "/^.*$pattern.*\$/m";
        // search, and store all matching occurences in $matches
        if(preg_match_all($pattern, $contents, $matches)){
            //echo "Found matches:\n";
            $m = implode("\n", $matches[0]);
            $this->matches = $matches;
            return $m;
        } else {
            //echo "no found";            
            return false;
            //echo "No matches found";
        }

        return;
    }


//    public function toWords() {



//    }





	public function Respond() {

		$this->cost = 100;

		// Thing stuff


		$this->thing->flagGreen();

		// Compose email

//		$status = false;//
//		$this->response = false;

//		$this->thing->log( "this reading:" . $this->reading );

//echo "meep";
//exit();



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




            $this->reading = $this->emoji;
            $this->thing->json->writeVariable(array("emoji", "reading"), $this->reading);



		return $this->thing_report;
	}


    function makeSMS()
    {

        if (isset($this->emoji_from_words)) {

            if (count($this->emojis) > 1) {
                $this->sms_message = "EMOJIS ARE ";
            } else {
                $this->sms_message = "EMOJI IS ";
            }
            $this->sms_message .= implode("",$this->emojis);
            $this->sms_message .= " | " . $this->search_words;
            return;
        }

        if ((isset($this->emoji)) and ($this->emoji != false)) {
            $this->sms_message = "EMOJI IS " . $this->emoji;

            if ($this->words != false) {
                if (count($this->words) > 1) {
                    $this->sms_message .= " | word is " . implode(" ", $this->words);
                } else {
                    $this->sms_message .= " | words are " . implode(" ", $this->words);
                }
            } else {
                $this->sms_message .= " | character not recognized";
                $this->keyword = "cue";
            }
            $this->sms_message .= " | mordok hears " . $this->keyword;
            $this->sms_message .= " | TEXT ?";
            return;
        }

        $this->sms_message = "EMOJI | no match found.";
       return;
    }


    function makeEmail() {

        $this->email_message = "EMOJI | ";

    }



	public function readSubject() {

        $this->translated_input = $this->wordsEmoji($this->subject);

        if (count($this->emojis) > 0) {
            return;
        }
        $input = strtolower($this->subject);
        $keywords = array('emoji');
        $pieces = explode(" ", strtolower($input));



        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
                        case 'emoji':   

                            $prefix = 'emoji';
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);
                            $this->search_words = $words;

                        $t = $this->findEmoji('list', $words);

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
            //$array = $this->matches[0];
            $k = array_rand($arr);
            $v = $arr[$k];

            $this->emoji_from_words = $v;

//            $this->emojis = implode("", $this->extractEmoji($this->emoji_from_words));

            $this->emojis = $this->extractEmoji(implode(" ", $arr));


//echo $this->emoji_from_words;

//exit();
                            return;


                        default:

                            //echo 'default';

                    }

                }
            }

        }
		$status = true;

//        if (count($this->emojis) == 0) {

//            $text = $this->findEmoji('list', $searchfor);


//        }

//echo $this->translated_input;
//exit();
	return $status;		
	}






    function contextEmoji () 
    {

$this->emoji_context = '
';

return $this->emoji_context;
}
}



?>
