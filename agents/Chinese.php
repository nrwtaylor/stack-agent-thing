<?php
namespace Nrwtaylor\StackAgentThing;

class Chinese
{

	function __construct(Thing $thing, $agent_input = null)
    {

        $this->start_time = microtime(true);


        $this->agent_input = $agent_input;
		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_prefix = 'Agent "Chinese" ';

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

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $string =  $this->subject;

//$this->makeDictionary();
//exit();

        $chineses =$this->extractChinese($string);


        $this->getChinese();


        $searchfor = $this->convert_chinese($this->chinese);
        $arr = explode(" ",$searchfor);
        $this->words = array();
        $this->word = null;

        foreach ($arr as $key=>$value) {
            if ($value == "U+FE0F") {continue;}
            // Return dictionary entry.
            $text = $this->findChinese('list', $value);
            //echo $value . " " .$text . "<br>";
            $words = $this->getWords($text);
            if ($words != false) {
                $this->words = array_merge($this->getWords($text));
               $this->word = $this->words[0];
            }
        }

        $this->keywords = array();
        $this->keyword = null;

        foreach ($arr as $key=>$value) {
            $text = $this->findChinese('mordok', $value); 
            if ($value == "U+FE0F") {continue;}

            $words = $this->getWords($text);

            if ($words != false) {
                $this->keywords = array_merge($this->getWords($text));
                $this->keyword = $this->keywords[0];
            }
        }


        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("chinese", "refreshed_at") );

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("chinese", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        //$this->thing->json->setField("variables");
        $this->reading = $this->thing->json->readVariable( array("chinese", "reading") );
            $this->readSubject();
//        if ( ($this->reading == false) ) {
//            $this->thing->log( $this->agent_prefix . 'no prior reading found.' );

            $this->thing->json->writeVariable( array("chinese", "reading"), $this->reading );
//			$this->readSubject(); // Commented out 4 Dec 2017.  First call if there is a problem.
            if ($this->agent_input == null) {$this->Respond();}
//        }

        if ($this->chinese != false) {

            // So emojis were found.
//            if (strpos($this->agent_input, 'respond') !== false) {
       
//                $this->Respond();
//            }



            $this->thing->log($this->agent_prefix . 'keyword '. $this->keyword . " word  ". $this->word["traditional"] . '.');
		    $this->thing->log($this->agent_prefix . 'completed with a reading of ' . $this->chinese . '.');


        } else {
                    $this->thing->log($this->agent_prefix . 'did not find chinese.');
        }

        $this->thing->log($this->agent_prefix . 'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

        $this->thing_report['log'] = $this->thing->log;

	}


    function makeDictionary()
    {
        // Makes a one character dictionary

        $file = $this->resource_path . 'chinese/cedict_1_0_ts_utf-8_mdbg.txt';
        $contents = file_get_contents($file);


            $separator = "\r\n";
            $line = strtok($contents, $separator);

            while ($line !== false) {
                $word = $this->getWords($line);

                if (mb_strlen($word['traditional']) == 1) {

                //v/ar_dump($word);
                //$dictionary_entry = $word['traditional'] . " " . $word['simplified'] . " " . $word['pin_yin'] . implode("/",$word['english']) . "\n";
                //echo $dictionary_entry;
                $dictionary[$word['traditional']] = $line . "\n";

                //echo $line . "\n";
                }
                # do something with $line
                $line = strtok( $separator );

            }
        $file = fopen($this->resource_path . 'chinese/chinese_mordok_new.txt', 'w');
        foreach ($dictionary as $character=>$line) {
            fwrite($file, $line);
            //echo $line;

        }
    }

    function getWords($test)
    {
        // Take a CE-CCEDICT line and de-parse it
        // Traditional Simplified [pin1 yin1] /English equivalent 1/equivalent 2/

        if ($test == false) {
            return false;
        }

        if (mb_substr($test,0,1) == "#") {$word = false; return $word;}

        // $t = explode("  ", $test);
        $dict = explode("/",$test);


if ( (!isset($dict[1])) or (!isset($dict[2])) ) {
    var_dump($dict);
}

        foreach($dict as $index=>$phrase) {
            if ($index == 0) {continue;}
            if ($phrase == "") {continue;}
            $english_phrases[] = $phrase;
        }
//var_dump($english_phrases);
//exit();
//        $english_equivalent_1 = $dict[1];
//        $english_equivalent_2 = $dict[2];

        $text =  $dict[0];

        preg_match_all("/\[([^\]]*)\]/", $text, $matches);
        $pin_yin = $matches[0][0];

        $dict = explode(" ",$text);

        $traditional = $dict[0];
        $simplified = $dict[1];

        //var_dump($traditional);
        //var_dump($simplified);
        //var_dump($pin_yin);
        //var_dump($english_equivalent_1);
        //var_dump($english_equivalent_2);

        $word = array("traditional"=>$traditional,"simplified"=>$simplified,
                    "pin_yin"=>$pin_yin, "english"=>$english_phrases);

/*
        

//        $t = preg_split("/[\t]/", $test);
//exit();
        //$n = count($t)-1;
        //echo $n;
        $words = array($english_equivalent_1,$english_equivalent_2);
//explode(" | ", $t[4] );
        $new_words = array();

        foreach($words as $key=>$word) {
            $new_words[] = trim($word);
        }
//var_dump($new_words);
*/
        return $word;
    }




    function extractChinese($string)
    {
        //https://stackoverflow.com/questions/17944961/php-separate-chinese-from-english-characters
        //$str = 'Hello 你怎么样？ How are you?';

        $english = preg_replace(array('/[\p{Han}？]/u', '/(\s)+/'), array('', '$1'), $string);
        $chinese = preg_replace(array('/[^\p{Han}？]/u', '/(\s)+/'), array('', '$1'), $string);
        $this->chinese_text = $chinese;
        $this->thing->log('reads english ' . $english . " and chinese " . $chinese);

        //https://stackoverflow.com/questions/1396434/what-is-the-best-way-to-split-a-string-into-an-array-of-unicode-characters-in-ph
        $this->chineses = preg_split('//u', $chinese, -1, PREG_SPLIT_NO_EMPTY);

        return $this->chineses;
    }

    function wordsChinese($input)
    {
        //echo "input is \n";
        //var_dump($input);
        $string_length = mb_strlen($input);
        $pointer = 0;
        $window = 1;
        // https://stackoverflow.com/questions/4601032/php-iterate-on-string-characters
        //$characters = mb_str_split($input);

$characters =  (preg_split('//u', $input, null, PREG_SPLIT_NO_EMPTY));

//var_dump($characters);
//exit();
        //foreach ($characters as $key=>$character) {
        while ($pointer !== $string_length) {
            $character = $characters[$pointer];

//           echo "\nPointer " . $pointer . " of " . $string_length . ". Looking for '" . $character . "'\n";
//echo "delad";
//echo mb_strpos($character,",") . "\n";
if ($character == '，') {$pointer += 1; continue;}
if ($character == '　') {$pointer += 1; continue;}
if ($character == '。') {$pointer += 1; continue;}
if ($character == '
') {$pointer += 1; continue;}

            $text = $this->findChinese('list', $character);
            $separator = "\r\n";
            $line = strtok($text, $separator);

            //$matches = array();
            $maximum_word_length = 0;
            while ($line !== false) {
//exit();
                # do something with $line
                //$line = strtok( $separator );



                $word = $this->getWords($line);
                $word_length = mb_strlen($word["traditional"]) ;
                $sub_string = mb_substr($input,$pointer,$word_length);
//echo $sub_string . " <match this substring> \n";

//echo $word["traditional"] . " --- " . strcasecmp($word["traditional"],$sub_string) . "\n";
//echo $word["simplified"] . " --- " . strcasecmp($word["simplified"],$sub_string) . "\n";



if ((strcasecmp($word["traditional"],$sub_string) == 0 ) or (strcasecmp($word["simplified"], $sub_string) == 0 )) {

if ($word_length > $maximum_word_length) {
    //echo $sub_string ." " .$word["traditional"] . " --- " . strcasecmp($word["traditional"],$sub_string) . "\n";
    //echo $sub_string . " ".$word["simplified"] . " --- " . strcasecmp($word["simplified"],$sub_string) . "\n";
        $match = array();
        //$match[] = $word;
        $maximum_word_length = $word_length;
        //echo "match" . "\n";
    }

    if ($word_length = $maximum_word_length) {
        //echo $sub_string ." " .$word["traditional"] . " --- " . strcasecmp($word["traditional"],$sub_string) . "\n";
        //echo $sub_string . " ".$word["simplified"] . " --- " . strcasecmp($word["simplified"],$sub_string) . "\n";

        $match[] = $word;
        //$maximum_word_length = $word_length;
        //echo "match" . "\n";
    }


}

                $line = strtok( $separator );
            }


            //var_dump($match);
//echo $character . " ";
$description = "";
$shortest_concept_length = 1e6;
foreach ($match as $i=>$word) {
    foreach($word["english"] as $j=>$concept) {
        $description .= " / " .  $concept;
        if ((mb_strlen($concept) < $shortest_concept_length) and (mb_strlen($concept) != 0)) {
  //          $shortest_concept
            $shortest_concept = $concept;
            $shortest_concept_length = mb_strlen($concept);

        //echo $concept . " ";
        }
    }
}
//echo $description;
//echo $shortest_concept;
//echo "\n";

//exit();
            //if (count($matches) == 0) {"no match for " . $character . "\n";} else {
            //    var_dump($matches);
            //}
            $pointer += $maximum_word_length;

        }

exit();

        if (!isset($this->chineses)) {
            $this->chineses = $this->getChinese($input);
        }

//        $string = 'The quick brown fox jumps over the lazy dog.';
        $patterns = array();
//$patterns[0] = '/quick/';
//$patterns[1] = '/brown/';
//$patterns[2] = '/fox/';
        $replacements = array(); 

        $chinese_string = implode($this->chineses,""); 
 //       echo "merp";
        //var_dump($string); exit();

 //       foreach($this->chineses as $chinese) {
 //           $patterns[] = '/' . $chinese . '/';
            $text = $this->findChinese('mordok', $chinese_string);

// devstack
           $words = $this->getWords($text);

            if ($words == false) {
                $word = "?";
            } else {
                $word = $words[0];
            }
            $replacements[] = " ". $word . " ";
//        }
        $translation = preg_replace($patterns, $replacements, $input);

    
//exit();
        return $translation;
    }

    function getChinese($input = null) {

        if ($input == null) {$input = $this->subject;}
        if (!isset($this->chineses)) {
            $this->extractChinese($this->subject);
        }

//var_dump($this->chineses);
//exit();
        if (count($this->chineses) == 0) {$this->chinese = false;return false;}
        $this->chinese = $this->chineses[0];

        return $this->chinese;
    }

    function convertChinese($chinese)
    {
//var_dump($chinese);
//exit();
        $str = str_replace('"', "", json_encode($chinese, JSON_HEX_APOS));

        $myInput = $str;

        $myHexString = str_replace('\\u', '', $myInput);
        $myBinString = hex2bin($myHexString);

        return  iconv("UTF-16BE", "UTF-8", $myBinString);
    }

function utf8($num)
{
    if($num<=0x7F)       return chr($num);
    if($num<=0x7FF)      return chr(($num>>6)+192).chr(($num&63)+128);
    if($num<=0xFFFF)     return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
    if($num<=0x1FFFFF)   return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128).chr(($num&63)+128);
    return '';
}

function uniord($c)
{
    $ord0 = ord($c{0}); if ($ord0>=0   && $ord0<=127) return $ord0;
    $ord1 = ord($c{1}); if ($ord0>=192 && $ord0<=223) return ($ord0-192)*64 + ($ord1-128);
    $ord2 = ord($c{2}); if ($ord0>=224 && $ord0<=239) return ($ord0-224)*4096 + ($ord1-128)*64 + ($ord2-128);
    $ord3 = ord($c{3}); if ($ord0>=240 && $ord0<=247) return ($ord0-240)*262144 + ($ord1-128)*4096 + ($ord2-128)*64 + ($ord3-128);
    return false;
}

function convert_chinese($chinese) {
$u =  $this->uniord($chinese);
return strtoupper("U+".dechex($u));
//exit();
//echo "received". $emoji . "<br>";
//echo "encoding" . mb_check_encoding($emoji, 'UTF-8'). "<br>";
    // ✊🏾 --> 0000270a0001f3fe
    //$emoji = mb_convert_encoding($emoji, 'UTF-32');
    $utf32_chinese = mb_convert_encoding($chinese, 'UTF-32', 'UTF-8');

//$emoji = iconv("UTF-8", "UTF-32", $emoji);

    $hex = bin2hex($utf32_chinese);
    
//echo "<br>";
//echo "mb_convert ". $utf32_emoji;
//echo "<br>";
//echo "hex ".$hex;
//echo "<br>";


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


    function findChinese($librex, $searchfor)
    {
        if (($librex == "") or ($librex == " ") or ($librex == null)) {return false;}

//        $path = $GLOBALS['stack_path'];
        switch ($librex) {
            case null:
                // Drop through
            case 'keywords':
                $file = $this->resource_path .'chinese/chinese-keywords.txt';
                $contents = file_get_contents($file);
                break;
  //          case 'data':
  //              $file = $this->resource_path .  'chinese/chinese-data.txt';
  //              $contents = file_get_contents($file);
  //              break;
            case 'mordok':
                $file = $this->resource_path . 'chinese/chinese-mordok.txt';
                $contents = file_get_contents($file);

                break;
            case 'list':
                $file = $this->resource_path . 'chinese/cedict_1_0_ts_utf-8_mdbg.txt';
                $contents = file_get_contents($file);
                break;

            case 'english-chinese':
                $file = $this->resource_path . 'chinese/cedict_1_0_ts_utf-8_mdbg.txt';
                $contents = file_get_contents($file);
                break;

            case 'unicode':
                $file = $this->resource_path . 'chinese/unicode.txt';
                $contents = file_get_contents($file);
                break;
            case 'context':
                $this->contextChinese();
                $contents = $this->chinese_context;
                $file = null;
                break;
            case 'emotion':
                break;
            default:
//                $file = $this->resource_path . 'chinese/chinese-keywords.txt';
                $file = $this->resource_path . 'chinese/cedict_1_0_ts_utf-8_mdbg.txt';


        }

// devstack add \b to Word
        $pattern = preg_quote($searchfor, '/');
        // finalise the regular expression, matching the whole line
        $pattern = "/^.*". $pattern. ".*\$/m";

        if ($librex == "mordok") {
            $pattern = preg_quote($searchfor . " ", '/');
            // finalise the regular expression, matching the whole line
            $pattern = "/^.*". $pattern. ".*\$/m";
        }

        if ($librex == "english-chinese") {
            $pattern = "\b" . preg_quote($searchfor, '/'). "\b";
            // finalise the regular expression, matching the whole line
            $pattern = "/^.*". $pattern. ".*\$/m";
        }

        // search, and store all matching occurences in $matches
        $m = false;
        if(preg_match_all($pattern, $contents, $matches)){
            //echo "Found matches:\n";
            $m = implode("\n", $matches[0]);
            $this->matches = $matches;
        }
        return $m;
    }



	public function Respond()
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

        $this->reading = $this->chinese;
        $this->thing->json->writeVariable(array("chinese", "reading"), $this->reading);

		return $this->thing_report;
	}

    function makeWeb()
    {
        $html = "<b>CHINESE " . $this->filtered_input . " </b>";
        $html .= "<p><br>";    
        foreach($this->words as $index=>$word) {
//var_dump($word);
            $line = $word["traditional"] . " " . $word["simplified"] . " " . $word["pin_yin"];

            $i = 0;
            foreach ($word["english"] as $english) {
                $line .= " / " . $english;
            }
            //$line = trim(" /", $line);
            $html .= $line . "<br>";

        }

        $this->web_message = $html;
        $this->thing_report['web'] = $html;
    }

    function makeSMS()
    {
        if (isset($this->word)) {
            if (!$this->has_chinese_characters) {
                // Assume english to chinese
                $sms = "CHINESE | ";
                foreach ($this->words as $word) {
                    if (mb_strlen($sms) > 60) {$sms .= "Text 'Web'.";break;}
                    $sms .= $word["traditional"] . " / ";
                }
                $this->sms_message = $sms;
                return;


            }
            // Assume this means a word was found.
            $this->sms_message = "CHINESE ";
            $this->sms_message .= count($this->words) . " phrases found.";
            $this->sms_message .= " | ";

            $this->sms_message .= implode(" / " ,$this->word["english"]);
            return;

        }

        if (isset($this->chinese_from_words)) {

            if (count($this->chineses) > 1) {
                $this->sms_message = "CHINESE CHARACTERS ARE ";
            } else {
                $this->sms_message = "CHINESE CHARACTER IS ";
            }
            //$this->sms_message .= implode("",$this->chineses);
    
            $this->sms_message .= $this->chinese_text;
            $this->sms_message .= " | " . $this->search_words;
            return;
        }

        if ((isset($this->chinese)) and ($this->chinese != false)) {
            $this->sms_message = "CHINESE CHARACTER IS ";
            $this->sms_message .= $this->chinese_text;
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

        $this->sms_message = "CHINESE | no match found.";
       return;
    }

    function makeEmail()
    {
        $this->email_message = "CHINESE | ";
    }

    public function test()
    {
    $input = "王先生是北大的老教师，一九三八年五月二十四号出生，　今年五十八岁。　今天是他的生日。　他是一位非常有经验的法语老师。这个学期他教大三的学生现代法语语法。

王先生的一位老朋友是老年大学的老师，　他经常在这个大学教日语。

他的一个学生在师大工作。　他有汉语书，法语书和日语书。　他天天教留学生现代汉语。现在他有五个男学生，八个女学生。";
    return $input;

    }


	public function readSubject()
    {

        $input = $this->subject;


        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "chinese is")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("chinese is")); 
        } elseif (($pos = strpos(strtolower($input), "chinese")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("chinese")); 
        }


        // Clean input

        $filtered_input = ltrim(strtolower($whatIWant), " ");

//var_dump($filtered_input);

        $string_length = mb_strlen($filtered_input);

        $this->extractChinese($filtered_input);
        //var_dump( $this->chineses )
        if (count($this->chineses) > 0) {$has_chinese_characters = true;} else {$has_chinese_characters = false;}

        $this->has_chinese_characters = $has_chinese_characters;

        if ($has_chinese_characters) {

            $text =  $this->findChinese("list",$filtered_input) ;

            $separator = "\r\n";
            $line = strtok($text, $separator);

            //$matches = array();
            $this->words = array();
            while ($line !== false) {
                $word = $this->getWords($line);
                $this->words[] = $word;
                # do something with $line
                $line = strtok( $separator );
            }
            //$this->word = $word;
            if (count($this->words) == 0) {
                $this->response = "No Chinese translation found.";
            }

            $this->word = $this->words[0];

            $this->response = "Found a Chinese translation.";
            $this->filtered_input = $filtered_input;
            return;
        }

        if ($filtered_input == "") {$filtered_input = "hello";}





            $text =  $this->findChinese("english-chinese",$filtered_input) ;

            $separator = "\r\n";
            $line = strtok($text, $separator);

            //$matches = array();
            $this->words = array();
            while ($line !== false) {
//echo $line . "\n";
                $word = $this->getWords($line);
                $this->words[] = $word;
                # do something with $line
                $line = strtok( $separator );
            }
            //$this->word = $word;
            if (count($this->words) == 0) {
                $this->response = "No English translation found.";
            }

            $this->word = $this->words[0];
//var_dump($this->word);
            $this->response = "Found English translation.";
            $this->filtered_input = $filtered_input;
            return;












        $t = $this->findChinese('english-chinese', $filtered_input);

        $this->filtered_input = $filtered_input;
        $this->response = "Provided chinese words for english.";
var_dump($t);
        return;
exit();
//var_dump($lines);
//exit();


    $input = $this->test();




        $this->translated_input = $this->wordsChinese($input);

        if (count($this->chineses) > 0) {

// This line catches snowflakes as a temp solution
// They are not recognized.  devstack
if (($this->translated_input == " ? ") and ($this->keyword = "snowflake")) {$this->translated_input = "snowflake";}
            return;
        }
        $input = strtolower($this->subject);
        $keywords = array('chinese');
        $pieces = explode(" ", strtolower($input));



        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
                        case 'chinese':   

                            $prefix = 'chinese';
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);
                            $this->search_words = $words;
                        $t = $this->findChinese('list', $words);

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
$this->chineses = null;
} else {
            //$array = $this->matches[0];
            $k = array_rand($arr);
            $v = $arr[$k];

            $this->chinese_from_words = $v;

//            $this->emojis = implode("", $this->extractEmoji($this->emoji_from_words));

            $this->chineses = $this->extractChinese(implode(" ", $arr));
}

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

//exit();
	return $status;		
	}






    function contextChinese () 
    {

$this->chinese_context = '
';

return $this->chinese_context;
}
}



?>
