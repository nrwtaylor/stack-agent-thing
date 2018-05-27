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

//var_dump($w);

        foreach ($w as $key=>$value) {

            // Return dictionary entry.
            $value = $this->stripPunctuation($value);

            $text = $this->findWord('list', $value);
//echo $text;
            //echo $value . " " .$text . "<br>";
//            $this->words = array_merge($this->getWords($text));
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




    function findWord($librex, $searchfor)
    {
        if (($librex == "") or ($librex == " ") or ($librex == null)) {return false;}
//echo getcwd();
//exit();
        switch ($librex) {
            case null:
                // Drop through
            case 'list':
                $file = $this->resource_path . 'words.txt';
                $contents = file_get_contents($file);
                break;
            case 'mordok':
                $file =  $this->resource_path . 'mordok.txt';
                $contents = file_get_contents($file);
                break;
            case 'context':
                $this->contextWord();
                $contents = $this->word_context;
                $file = null;
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
            $this->sms_message = "WORD | no words found";
            return;
        }


        if ($this->words[0] == false) {
            $this->sms_message = "WORD | no words found";
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

        $input = strtolower($this->subject);
//        $this->extractWords($input);
//var_dump($this->words);


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

                            //$t = $this->findWord('list', $words);
//echo "test";
//var_dump($this->words);
//exit();
            //$this->words = implode(" ", $t);

                            return;


                        default:

                            //echo 'default';

                    }

                }
            }

        }

        $this->extractWords($input);

		$status = true;

//        if (count($this->emojis) == 0) {

//            $text = $this->findEmoji('list', $searchfor);


//        }

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
