<?php
namespace Nrwtaylor\StackAgentThing;

class Punctuation extends Agent
{
    function init()
    {
        $this->start_time = microtime(true);
        $this->start_time = $this->thing->elapsed_runtime();

        $this->keywords = [];

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "punctuation",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["punctuation", "refreshed_at"],
                $time_string
            );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable([
            "punctuation",
            "reading",
        ]);

        $this->readSubject();

        $this->thing->json->writeVariable(
            ["punctuation", "reading"],
            $this->reading
        );

        if ($this->agent_input == null) {
            $this->respondResponse();
        }

        if (count($this->punctuations) != 0) {
            $this->punctuation = $this->punctuations[0];
            $this->thing->log(
                $this->agent_prefix .
                    'completed with a reading of ' .
                    $this->reading .
                    '.'
            );
        } else {
            $this->punctuation = null;
            $this->thing->log(
                $this->agent_prefix . 'did not find punctuation makrs.'
            );
        }
    }

    function getWords($message = null)
    {
        if ($message == null) {
            $message = $this->subject;
        }

        $agent = new Word($this->thing, $message);
        $agent->extractWords($message);
        $this->words = $agent->words;
    }

    function extractPunctuations($input, $min_length = 3)
    {
        //if ($input == null) {$input = $this->subject;}
        if (!isset($this->punctuations)) {
            $this->punctuations = [];
        }
        if (!isset($this->words)) {
            $this->getWords();
        }

        $words = $this->words;
        $punctuations = [];

        $grams = explode(" ", $input);
        $message = "";

        foreach ($grams as $key => $gram) {
            $gram_filtered = $this->stripPunctuation($gram, "");

            if ($this->isWord($gram_filtered) == false) {
                // Not a word so very likely punctuation
                // in some way

                $gram_nonnom = $this->nonnomify($gram);
                $message .= " " . $gram_nonnom;
                $this->punctuations[] = $gram;
            } else {
                $message .= " " . $gram;
            }
        }

        $message = ltrim($message);

        $this->message = $message;

        return $punctuations;
    }

    public function isWord($gram)
    {
        if (!isset($this->words)) {
            $this->getWords();
        }

        $gram_filtered = $this->stripPunctuation($gram, "");
        //var_dump($gram_filtered);

        $is_word = false;
        foreach ($this->words as $temp => $word) {
            $match = strtolower($word) == strtolower($gram_filtered);

            if ($match) {
                $is_word = true;
            } else {
            }
        }

        //echo "isWord? " . ($is_word==true) . " " . $gram  . " (filtered: " . $gram_filtered .")". "\n";

        return $is_word;
    }

    public function stripPunctuation($input, $replace_with = " ")
    {
        $unpunctuated = preg_replace(
            '/[\:\;\/\!\?\#\.\,\'\"\{\}\[\]\<\>\(\)]/i',
            $replace_with,
            $input
        );
        return $unpunctuated;
    }
    /*
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
*/

    public function respondResponse()
    {
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
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->reading = count($this->punctuations);
        $this->thing->json->writeVariable(
            ["punctuation", "reading"],
            $this->reading
        );

        return $this->thing_report;
    }

    function makeSMS()
    {
        if (isset($this->punctuations)) {
            if (count($this->punctuations) == 0) {
                $this->sms_message =
                    "PUNCTUATION | No punctuation marks found.";
                return;
            }

            if ($this->punctuations[0] == false) {
                $this->sms_message = "PUNCTUATION | No words found.";
                return;
            }

            if (count($this->punctuations) > 1) {
                $this->sms_message = "PUNCTUATION ARE ";
            } elseif (count($this->punctuations) == 1) {
                $this->sms_message = "PUNCTUATION IS ";
            }

            $this->sms_message .= implode(" ", $this->punctuation);

            return;
        }

        $this->sms_message = "PUNCTUATION | No match found.";
    }

    function makeEmail()
    {
        $this->email_message = "PUNCTUATION";
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $keywords = ['punctuation'];
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'punctuation':
                            if (!isset($prefix)) {
                                $prefix = 'punctuation';
                            }
                            $words = preg_replace(
                                '/^' . preg_quote($prefix, '/') . '/',
                                '',
                                $input
                            );
                            $words = ltrim($words);

                            //$this->search_words = $words;

                            $this->extractPunctuations($words);

                            return;

                        default:

                        //echo 'default';
                    }
                }
            }
        }

        $this->extractPunctuations($input);

        $status = true;

        return $status;
    }

    function contextPunctuation()
    {
        $this->punctuation_context = '
';

        return $this->punctuation_context;
    }
}
