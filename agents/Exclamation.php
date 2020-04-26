<?php
namespace Nrwtaylor\Stackr;


class Punctuation {

	// Responds to a query from $from to useragent@stackr.co

	function __construct(Thing $thing, $agent_input = null)
    {

        $this->start_time = microtime(true);
        if ($agent_input == null) {}
        $this->agent_input = $agent_input;
		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_prefix = 'Agent "Punctuation" ';

//        $this->thing_report  = array("thing"=>$this->thing->thing);
        $this->thing_report['thing'] = $this->thing->thing;

	    $this->uuid = $thing->uuid;

//        $this->resource_path = '/home/nick/txt/vendor/nrwtaylor/stackr/resources/words/';

        if (!isset($thing->to)) {$this->to = null;} else {$this->to = $thing->to;}
        if (!isset($thing->from)) {$this->from = null;} else {$this->from = $thing->from;}
	    if (!isset($thing->subject)) {$this->subject = $agent_input;} else {$this->subject = $thing->subject;}


		$this->sqlresponse = null;

		$this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid .'.');
		$this->thing->log($this->agent_prefix . 'received this Thing "' . $this->subject .  '".');



        $this->keywords = array();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("nonnom", "refreshed_at") );

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("nonnom", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("nonnom", "reading") );

            $this->readSubject();

            $this->thing->json->writeVariable( array("nonnom", "reading"), $this->reading );

            if ($this->agent_input == null) {$this->Respond();}

        if (count($this->nominals) != 0) {
            $this->nominal = $this->nominals[0];
		    $this->thing->log($this->agent_prefix . 'completed with a reading of ' . $this->reading . '.');


        } else {
            $this->ngram = null;
                    $this->thing->log($this->agent_prefix . 'did not find words.');
        }

        $this->thing->log($this->agent_prefix . 'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

        $this->thing_report['log'] = $this->thing->log;


	}


    function getWords($message=null)
    {
        if ($message == null) {$message = $this->subject;}

        $agent = new \Nrwtaylor\Stackr\Word($this->thing,$message);
        $this->words = $agent->words;
    }

    function extractNominals($input, $min_length = 3)
    {

        //if ($input == null) {$input = $this->subject;}
        if (!isset($this->nominals)) {$this->nominals = array();}
        if (!isset($this->words)) {$this->getWords();}

//var_dump($this->words);

        $words = $this->words;
        $nominals = array();

        $grams = explode(" ", $input);
        $message_nonnom = "";

        foreach ($grams as $key=>$gram) {

            $gram_filtered = $this->stripPunctuation($gram,"");
//var_dump($gram_filtered);
            if ($this->isWord($gram_filtered) == false) {

                // Not a word so very likely nominal 
                // in some way

                $gram_nonnom = $this->nonnomify($gram);
                $message_nonnom .= " " . $gram_nonnom;
                $this->nominals[] = $gram;

//echo $gram_nonnom . " " . $gram . "\n";


            } else {
                $message_nonnom .= " " . $gram;
            }
        }

        $message_nonnom = ltrim($message_nonnom);

        $this->message_nonnom = $message_nonnom;

//        echo "nominal message\n";
//        echo $this->subject;
//        echo "\n";
//        echo "nonnom message\n";
//        echo $this->message_nonnom;
//        echo "\n";
//exit();


//        if (count($ngrams) != 0) {
//            array_push($this->ngrams, ...$ngrams);
//        }
//        array_merge($this->ngrams, $ngram);
        return $nominals;
    }

    public function isWord($gram)
    {
        if (!isset($this->words)) {$this->getWords();}

        $gram_filtered = $this->stripPunctuation($gram, "");
        //var_dump($gram_filtered);

        $is_word = false;
        foreach ($this->words as $temp=>$word) {

          //  var_dump(strtolower($word));

            $match = (strtolower($word) == strtolower($gram_filtered));

            //echo strtolower($word);
            //echo strtolower($gram_filtered);
            //var_dump($match);


            if ($match) {

            //if (strtolower($word) == strtolower($gram_filtered)) {
                $is_word = true;
            } else {
            }

        }

        //echo "isWord? " . ($is_word==true) . " " . $gram  . " (filtered: " . $gram_filtered .")". "\n";

   

        return $is_word;


    }

    public function stripPunctuation($input, $replace_with = " ")
    {
        $unpunctuated = preg_replace('/[\:\;\/\!\?\#\.\,\'\"\{\}\[\]\<\>\(\)]/i', $replace_with, $input);
        return $unpunctuated;
    }

    public function nonnomify($input) 
    {
// https://stackoverflow.com/questions/4949279/remove-non-numeric-characters-except-periods-and-commas-from-a-string
//        $punctuation_string = preg_replace('/[\!\?\#\.\,\'\"\{\}\[\]\<\>\(\)]+/i', '_', $input);
 $punctuation_string = preg_replace('/[^\:\;\/\!\?\#\.\,\'\"\{\}\[\]\<\>\(\)]/i', ' ', $input);

//echo "punctatation string:" .  $punctuation_string . "\n";
        $length = strlen($input);
        $repeat_gram = "nomnom";

        $length_repeat_gram = strlen($repeat_gram);

        $num = floor($length / $length_repeat_gram);

        $nonnom_input = str_repeat($repeat_gram, $num);

        $remainder = $length % $length_repeat_gram;
        $nonnom_input .= substr($repeat_gram, 0, $remainder);

        // Add punction back in.
        $s = "";
        $i = 0;
        $punctuation_array = str_split($punctuation_string);
        foreach($punctuation_array as $temp=>$punctuation)
        {
            if ($punctuation != " ") {
                $s .= substr($punctuation_string,$i,1);
            } else {
                $s .= substr($nonnom_input,$i,1);
            }
            $i += 1;
        }

//var_dump($s);
        return $s;
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

        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->reading = count($this->nominals);
        $this->thing->json->writeVariable(array("nonnom", "reading"), $this->reading);

        return $this->thing_report;
	}


    function makeSMS()
    {

        if (isset($this->words)) {

        if (count($this->words) == 0) {
            $this->sms_message = "NONOM | no words found";
            return;
        }


        if ($this->words[0] == false) {
            $this->sms_message = "NONNOM | no words found";
            return;
        }

        if (count($this->words) > 1) {
            $this->sms_message = "NOMINALS ARE ";
        } elseif (count($this->nominals) == 1) {
            $this->sms_message = "NOMINAL IS ";
        }
        $this->sms_message .= implode(" ",$this->nominal);
        return;
    }

        $this->sms_message = "NONNOM | no match found";
   return;
    }


    function makeEmail()
    {
        $this->email_message = "WORD | ";
    }



	public function readSubject()
    {
        $input = strtolower($this->subject);


        $keywords = array('nomnom','nonnominal','non-nom','non-nominal');
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
                        case 'nonnominal':   
                            $prefix = 'nonnominal';
                        case 'non-nominal':   
                            $prefix = 'non-nominal';
                        case 'non-nom':   
                            $prefix = 'non-nom';
                        case 'nonnom':
                            if (!isset($prefix)) {$prefix = 'nonnom';}
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);

                            //$this->search_words = $words;

                            $this->extractNominals($words);


                            return;

                        default:

                            //echo 'default';

                    }

                }
            }

        }

        $this->extractNominals($input);


		$status = true;



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
