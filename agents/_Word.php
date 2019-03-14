<?php
namespace Nrwtaylor\StackAgentThing;

class Word {

	function __construct(Thing $thing, $agent_input = null)
    {
//echo "meep";
//var_dump($agent_input);

        $this->start_time = microtime(true);
        if ($agent_input == null) {}
        $this->agent_input = $agent_input;
		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_prefix = 'Agent "Word" ';

//        $this->thing_report  = array("thing"=>$this->thing->thing);
        $this->thing_report['thing'] = $this->thing->thing;

	    $this->uuid = $thing->uuid;

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/words/';
        $this->resource_path_ewol = $GLOBALS['stack_path'] . 'resources/ewol/';



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
        $time_string = $this->thing->json->readVariable( array("word", "refreshed_at") );

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("word", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("word", "reading") );

            $this->readSubject();

            $this->thing->json->writeVariable( array("word", "reading"), $this->reading );

            if ($this->agent_input == null) {$this->Respond();}

        if (count($this->words) != 0) {

		    $this->thing->log($this->agent_prefix . 'completed with a reading of ' . $this->word . '.');


        } else {
                    $this->thing->log($this->agent_prefix . 'did not find words.');
        }

        $this->thing->log($this->agent_prefix . 'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

        $this->thing_report['log'] = $this->thing->log;


	}


    function getWords($test)
    {
        if ($test == false) {
            return false;
        }

        $new_words = array();

        if ($test == "") {return $new_words;}

        $pattern = '/([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC5[\x92\x93\xA0\xA1\xB8\xBD\xBE]){1,}/';
  //      $t = explode("  ", $test);
        $t = preg_split($pattern, $test);

        //$n = count($t)-1;
        //echo $n;
        //$words = explode(" | ", $t[4] );
        //$new_words = array();

        foreach($t as $key=>$word) {
            $new_words[] = trim($word);
        }
//
//var_dump($new_words);
        return $new_words;
    }


    public function stripPunctuation($input, $replace_with = " ")
    {
        $unpunctuated = preg_replace('/[\:\;\/\!\?\#\.\,\'\"\{\}\[\]\<\>\(\)]/i', $replace_with, $input);
        return $unpunctuated;
    }




    function extractWords($string)
    {
//echo "\n";
//                    $value = preg_replace('/[^a-z]+/i', ' ', $value);
//echo $string . "\n";

        preg_match_all('/([a-zA-Z]|\xC3[\x80-\x96\x98-\xB6\xB8-\xBF]|\xC5[\x92\x93\xA0\xA1\xB8\xBD\xBE]){2,}/', $string, $words);
        //print_r($emojis[0]); // Array ( [0] => 😃 [1] => 🙃 ) 
        $w = $words[0];

//echo implode("_",$w) . "\n";


        $this->words = array();

        foreach ($w as $key=>$value) {

            // Return dictionary entry.
            $value = $this->stripPunctuation($value);

            $text = $this->findWord('list', $value);

            if ($text != false) {
                 //   echo "word is " . $text . "\n";
                $this->words[] = $text;
            } else {
                 //   echo "word is not " . $value . "\n";
            }
        }

        if (count($this->words) != 0) {
            $this->word = $this->words[0];
        } else {
//            $text = $this->nearestWord($value);
//echo $text;
//exit();
            $this->word = null;
        }


        return $this->words;
    }


    function getWord() {
        if (!isset($this->words)) {
            $this->extractWords($this->subject);
        }
        if (count($this->words) == 0) {$this->word = false;return false;}
        $this->word = $this->words[0];
        return $this->word;
    }

    function ewolWords()
    {
        if (isset($this->ewol_dictionary)) {$contents = $this->ewol_dictionary;return;}
        $contents = "";
        foreach(range("A","Z") as $v) {
            $file = $this->resource_path_ewol . $v . ' Words.txt';
            $contents .= file_get_contents($file);
        }

        $arr = explode("\n",$contents);
        foreach($arr as $key=>$line) {
            if (mb_strlen($line) <=1 ) {continue;}
            $this->ewol_dictionary[$line] = true;
        }

    }

    function findWord($librex, $searchfor)
    {
        if (($librex == "") or ($librex == " ") or ($librex == null)) {return false;}

        switch ($librex) {
            case null:
                // Drop through
            case 'list':
                if (isset($this->words_list)) {$contents = $this->words_list;break;}
                $file = $this->resource_path . 'words.txt';
                $contents = file_get_contents($file);
                $this->words_list = $contents;
                break;
            case 'ewol':
                $searchfor = strtolower($searchfor);
                if (isset($this->ewol_list)) {$contents = $this->ewol_list;break;}
                $contents = "";
                foreach(range("A","Z") as $v) {
                    $file = $this->resource_path_ewol . $v . ' Words.txt';
                    $contents .= file_get_contents($file);
                }
                $this->ewol_list = $contents;
                break;


            case 'mordok':
                if (isset($this->mordok_list)) {$contents = $this->mordok_list;break;}

                $file =  $this->resource_path . 'mordok.txt';
                $contents = file_get_contents($file);
                $this->mordok_list = $contents;
                break;
            case 'context':
                if (isset($this->context_list)) {$contents = $this->context_list;break;}

                $this->contextWord();
                $contents = $this->word_context;
                $file = null;
                $this->context_list = $contents;
                break;

            case 'emotion':
                break;
            default:
                $file = $this->resource_path .  'words.txt';

        }
        $pattern = "|\b($searchfor)\b|";

        // search, and store all matching occurences in $matches
        if(preg_match_all($pattern, $contents, $matches)){
            $m = $matches[0][0];
            return $m;
        } else {
            return false;
        }

        return;
    }

    function nearestWord($input)
    {
//var_dump($input);
                $file = $this->resource_path . 'words.txt';
                $contents = file_get_contents($file);

        $words = explode("\n", $contents);

        $nearness_min = 1e6;
        $word = false;

        foreach ($words as $key=>$word) {
            $nearness = levenshtein($input, $word);
            //$nearness = similar_text($word, $input);

            if ($nearness < $nearness_min) {
                $word_list = array();
                $nearness_min = $nearness;
            }
            if ($nearness_min == $nearness) {
                $word_list[] = $word;

            }

        }

        $nearness_max = 0;
        $word = false;

        foreach ($word_list as $key=>$word) {
            //$nearness = levenshtein($input, $word);
            $nearness = similar_text($word, $input);

            if ($nearness > $nearness_max) {
                $new_word_list = array();
                $nearness_min = $nearness;
            }
            if ($nearness_min == $nearness) {
                $new_word_list[] = $word;

            }

        }

        if (!isset($new_word_list) or ($new_word_list == null)) {
            $nearest_word = false;
        } else { 
            $nearest_word = implode(" " ,$new_word_list);
        }

        return $nearest_word;
    }




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




            $this->reading = count($this->words);
            $this->thing->json->writeVariable(array("word", "reading"), $this->reading);



		return $this->thing_report;
	}


    function makeSMS() {

//var_dump($this->words);
    if (isset($this->words)) {

        if (count($this->words) == 0) {
            if (isset($this->nearest_word)) {
                $this->sms_message = "WORD | closest match " . $this->nearest_word;
            } else {
                $this->sms_message = "WORD | no words found";
            }

//            $this->sms_message = "WORD | no words found";
            return;
        }


        if ($this->words[0] == false) {
            if (isset($this->nearest_word)) {
                $this->sms_message = "WORD | closest match " . $this->nearest_word;
            } else {
                $this->sms_message = "WORD | no words found";
            }
            return;
        }

        if (count($this->words) > 1) {
            $this->sms_message = "WORDS ARE ";
        } elseif (count($this->words) == 1) {
            $this->sms_message = "WORD IS ";
        }
        $this->sms_message .= implode(" ",$this->words);
        return;
    }

        $this->sms_message = "WORD | no match found";
   return;
    }


    function makeEmail() {

        $this->email_message = "WORD | ";

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

        $keywords = array('word');
        $pieces = explode(" ", strtolower($input));



        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {

                        case 'word':   

                            $prefix = 'word';
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);
                            $this->search_words = $words;

                            $this->extractWords($words);

                            if ($this->word != null) {return;}
                            //return;

                        default:

                            //echo 'default';

                    }

                }
            }

        }

        $this->nearest_word = $this->nearestWord($this->search_words);
//var_dump($this->word);
        //$this->extractWords($input);

		$status = true;


	return $status;		
	}






    function contextWord () 
    {

$this->word_context = '
';

return $this->word_context;
}
}



?>
