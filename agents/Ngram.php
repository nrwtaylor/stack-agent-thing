<?php
/**
 * Ngram.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;


class Ngram {


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function __construct(Thing $thing, $agent_input = null) {

        $this->start_time = microtime(true);
        if ($agent_input == null) {}
        $this->agent_input = $agent_input;
        $this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_prefix = 'Agent "N-Gram" ';

        $this->thing_report['thing'] = $this->thing->thing;

        $this->uuid = $thing->uuid;

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/words/';

        if (!isset($thing->to)) {$this->to = null;} else {$this->to = $thing->to;}
        if (!isset($thing->from)) {$this->from = null;} else {$this->from = $thing->from;}
        if (!isset($thing->subject)) {$this->subject = $agent_input;} else {$this->subject = $thing->subject;}


        $this->sqlresponse = null;

        $this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid .'.');
        $this->thing->log($this->agent_prefix . 'received this Thing "' . $this->subject .  '".');



        $this->keywords = array();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("ngram", "refreshed_at") );

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("ngram", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("ngram", "reading") );

        $this->readSubject();

        $this->thing->json->writeVariable( array("ngram", "reading"), $this->reading );

        if ($this->agent_input == null) {$this->Respond();}

        if (count($this->ngrams) != 0) {
            $this->ngram = $this->ngrams[0];
            $this->thing->log($this->agent_prefix . 'completed with a reading of ' . $this->ngram . '.');


        } else {
            $this->ngram = null;
            $this->thing->log($this->agent_prefix . 'did not find words.');
        }

        $this->thing->log($this->agent_prefix . 'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

        $this->thing_report['log'] = $this->thing->log;


    }


    /**
     *
     * @param unknown $message (optional)
     */
    function getWords($message=null) {
        //        $agent = new \Nrwtaylor\Stackr\Word($this->thing,$this->subject);
        if ($message == null) {$message = $this->subject;}
        $agent = new Word($this->thing, "word");


        $agent->extractWords($message);

        $this->words = $agent->words;


    }




    /**
     *
     * @param unknown $input (optional)
     * @param unknown $n     (optional)
     * @return unknown
     */
    function extractNgrams($input = null, $n = 3) {

        if (!isset($this->ngrams)) {$this->ngrams = array();}
        if (!isset($this->words)) {$this->getWords($input);}

        $words = $this->words;
        $ngrams = array();

        if (!isset($words) or count($words) == 0) {return $ngrams;}

        foreach ($words as $key=>$value) {

            if ($key < count($words) - ($n - 1)) {
                $ngram = "";
                for ($i = 0; $i < $n; $i++) {
                    $ngram .= " " . $words[$key + $i];
                }

                $ngram = ltrim($ngram);
                $ngram = rtrim($ngram);
                $ngrams[] = $ngram;
            }
        }

        //$this->ngrams[] = $ngram;

        if (count($ngrams) != 0) {
            array_push($this->ngrams, ...$ngrams);
        }
        //        array_merge($this->ngrams, $ngram);
        return $ngrams;
    }


    public function isEqual($text_a, $text_b) {

    


    }


    /**
     *
     * @return unknown
     */
    public function Respond() {

        $this->cost = 100;

        // Thing stuff


        $this->thing->flagGreen();

        // Compose email

        //  $status = false;//
        //  $this->response = false;

        //  $this->thing->log( "this reading:" . $this->reading );




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

        $this->reading = count($this->words);
        $this->thing->json->writeVariable(array("word", "reading"), $this->reading);

        return $this->thing_report;
    }


    /**
     *
     */
    function makeSMS() {

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
            $this->sms_message .= implode(" ", $this->words);
            return;
        }

        $this->sms_message = "WORD | no match found";
        return;
    }


    /**
     *
     */
    function makeEmail() {
        $this->email_message = "WORD | ";
    }



    /**
     *
     * @return unknown
     */
    public function readSubject() {
        $input = strtolower($this->subject);
        if ($this->agent_input != null) {
            $input = $this->agent_input;
        }


        $keywords = array('ngram', 'n-gram');
        $pieces = explode(" ", strtolower($input));
/*
        if (count($pieces) == 1) {

            if ($input == 'ngram') {
$this->ngrams = array();
$this->response = "No response.";
             //   $this->getMessage();

             //   if ((!isset($this->index)) or 
             //       ($this->index == null)) {
             //       $this->index = 1;
             //   }
                return;
            }
        }
*/

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {

                    switch ($piece) {
                    case 'ngram':
                        $prefix = 'ngram';
                    case 'n-gram':
                        if (!isset($prefix)) {$prefix = 'n-gram';}
                        $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                        $words = ltrim($words);

                        //$this->search_words = $words;

                        $this->extractNgrams($words, 3);
                        $this->extractNgrams($words, 2);
                        $this->extractNgrams($words, 1);


                        return;

                    default:

                        //echo 'default';

                    }

                }
            }

        }

        $this->extractNgrams($input, 3);
        $this->extractNgrams($input, 2);
        $this->extractNgrams($input, 1);


        $status = true;



        //        }

        return $status;
    }






    /**
     *
     * @return unknown
     */
    function contextWord() {

        $this->word_context = '
';

        return $this->word_context;
    }


}
