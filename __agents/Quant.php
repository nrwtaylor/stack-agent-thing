<?php
namespace Nrwtaylor\StackAgentThing;


class Quant
{
	function __construct(Thing $thing, $agent_input = null)
    {

        $this->start_time = microtime(true);

        if ($agent_input == null) {}
        $this->agent_input = $agent_input;

		$this->thing = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        $this->agent_name = "quant";
        $this->agent_prefix = 'Agent "Quant" ';

        $this->thing_report['thing'] = $this->thing->thing;

	    $this->uuid = $thing->uuid;

        $agent = new \Nrwtaylor\Stackr\Meta($this->thing, $agent_input);
        $this->subject = $agent->subject;
        $this->to = $agent->to;
        $this->from = $agent->from;

		$this->sqlresponse = null;

		$this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid .'.');
		$this->thing->log($this->agent_prefix . 'received this Thing "' . $this->subject .  '".');

        $this->keywords = array();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("quant", "refreshed_at") );

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("quant", "refreshed_at"), $time_string );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable( array("quant", "reading") );

        $this->readSubject();

        $this->thing->json->writeVariable( array("quant", "reading"), $this->reading );

        if ($this->agent_input == null) {$this->Respond();}

        if (count($this->word_count) != 0) {
            //$this-> = $this->ngrams[0];
		    $this->thing->log($this->agent_prefix . 'completed with ' . count($this->word_count) . ' wordcount.');


        } else {
            $this->words = null;
                    $this->thing->log($this->agent_prefix . 'did not find words to quantify.');
        }

        $this->thing->log($this->agent_prefix . 'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');

        $this->thing_report['log'] = $this->thing->log;


	}

    function extractQuants($message = null) {

        if ($message == null) {$message = $this->subject;}

        $this->character_length = strlen($message); 
        $this->word_number = count(explode(" ",$message));

        $this->word_count = count(explode(" ",$message));
        $this->significance = $this->getSignificance($message);

        if (!isset($this->words)) {$this->getWords($message);} 

        $n = 0;
        $l = 0;
        $max = 0;

        if (count($this->words) == 0) {
            $this->average_word_length = true;  
            $this->max_word_length = true;
            $this->words_number = true;
            return; 
        }

        foreach ($this->words as $key=>$word) {
            $length = strlen($word);
            if ($length > $max) {$max = $length;}
            $l = $length + $l;
            $n += 1;
        }

        $this->average_word_length = round($l/$n);  
        $this->max_word_length = $max;


        $this->words_number = count($this->words);  

    }

    function getWords($message=null)
    {
        if ($message == null) {$message = $this->subject;}
        if ($message == null) {$this->words =array(); return;}

        $agent = new \Nrwtaylor\Stackr\Word($this->thing, $message);
        $this->words = $agent->words;
    }


    function getSignificance($message)
    {
        $significance = 0;

        //$this->getWords();
        $words = explode(" ", $message);
        foreach ($words as $key=>$word) {
           if (strlen($word) > 4) {
              $significance += 1;
           }
        }
        return $significance;
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

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->reading = count($this->messages);
        $this->thing->json->writeVariable(array("quant", "reading"), $this->reading);

        return $this->thing_report;
	}


    function makeSMS()
    {
        if (isset($this->word_count)) {

           if (count($this->word_count) == 0) {
                $this->sms_message = "QUANT | no words found";
                return;
            }

            if (count($this->word_count) >= 1) {
                $this->sms_message = "QUANT | " . count($this->word_count) . " words counted.";
            }

            $this->sms_message .= "QUANT | undefined response";
            return;
        }

        $this->sms_message = "QUANT | no messages found";
        return;
    }

    function makeEmail()
    {
        $this->email_message = $this->sms_message;
    }

	public function readSubject()
    {

        if ($this->agent_input != null) {$input = $this->agent_input;} else {
        $input = strtolower($this->subject);
        }

        $keywords = array('quant','quantities', 'quantitative');
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {

                    switch($piece) {
                        case 'quant':
                        case 'quants':
                        case 'quantities':
                        case 'quantitative':
                            $prefix = $piece;
                            if (!isset($prefix)) {$prefix = 'quant';}
                            $words = preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $input);
                            $words = ltrim($words);

                            //$this->search_words = $words;

                            $this->extractQuants($words);

                            return;

                        default:

                            //echo 'default';

                    }

                }
            }

        }

        $this->extractQuants($input);


		$status = true;

	return $status;
    }

}



?>
