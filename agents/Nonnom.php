<?php
namespace Nrwtaylor\StackAgentThing;

class Nonnom extends Agent
{
    public function init()
    {
        if ($this->agent_input == "fixed length") {
            $this->mode = "fixed length";
            $this->nonnom_length = 6;
        }

        $this->keywords = [];
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "nonnom",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["nonnom", "refreshed_at"],
                $time_string
            );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->json->readVariable([
            "nonnom",
            "reading",
        ]);
    }

    public function set()
    {
        $reading = null;
        if (isset($this->nominals)) {
            $reading = count($this->nominals);
        }

        $this->reading = $reading;

        $this->thing->json->writeVariable(
            ["nonnom", "reading"],
            $this->reading
        );

        if (isset($this->nominals) and count($this->nominals) != 0) {
            $this->nominal = $this->nominals[0];
            $this->thing->log(
                $this->agent_prefix .
                    'completed with a reading of ' .
                    $this->reading .
                    '.'
            );
        } else {
            $this->ngram = null;
            $this->thing->log($this->agent_prefix . 'did not find words.');
        }
    }

    function getWords($message = null)
    {
        if ($message == null) {
            $message = $this->subject;
        }

        if ($message == "") {
            $this->words = [];
            return;
        }

        $agent = new Word($this->thing, $message);
        $this->words = $agent->words;
    }

    function extractNominals($input, $min_length = 3)
    {
        //if ($input == null) {$input = $this->subject;}
        if (!isset($this->nominals)) {
            $this->nominals = [];
        }
        if (!isset($this->words)) {
            $this->getWords();
        }

        $words = $this->words;
        $nominals = [];

        $grams = explode(" ", $input);
        $message_nonnom = "";

        foreach ($grams as $key => $gram) {
            if ($gram == "") {
                continue;
            }
            $gram_filtered = $this->stripPunctuation($gram, "");

            if ($this->isWord($gram_filtered) == false) {
                // Not a word so very likely nominal
                // in some way

                $gram_nonnom = $this->nonnomify($gram, $this->mode);
                $message_nonnom .= " " . $gram_nonnom;
                $this->nominals[] = $gram;

            } else {
                $message_nonnom .= " " . $gram;
            }
        }

        $message_nonnom = ltrim($message_nonnom);

        $this->message_nonnom = $message_nonnom;

        return $nominals;
    }

    public function isWord($gram)
    {
        if (!isset($this->words)) {
            $this->getWords();
        }

        $gram_filtered = $this->stripPunctuation($gram, "");

        $is_word = false;
        foreach ($this->words as $temp => $word) {

            $match = strtolower($word) == strtolower($gram_filtered);

            if ($match) {
                //if (strtolower($word) == strtolower($gram_filtered)) {
                $is_word = true;
            } else {
            }
        }


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

    public function nonnomify($input, $mode = null)
    {
        if ($mode == null) {
            $mode = 'retain length';
        }

        // https://stackoverflow.com/questions/4949279/remove-non-numeric-characters-except-periods-and-commas-from-a-string
        //        $punctuation_string = preg_replace('/[\!\?\#\.\,\'\"\{\}\[\]\<\>\(\)]+/i', '_', $input);
        $punctuation_string = preg_replace(
            '/[^\:\;\/\!\?\#\.\,\'\"\{\}\[\]\<\>\(\)]/i',
            ' ',
            $input
        );
        $punctuation_array = str_split($punctuation_string);

        $length = strlen($input);
        $repeat_gram = "nomnom";

        $length_repeat_gram = strlen($repeat_gram);

        if ($mode == "fixed length") {
            $nonnom_input = "";
            foreach ($punctuation_array as $temp => $value) {
                if ($value == "_") {
                    break;
                }
                $nonnom_input .= $value;
            }

            $nonnom_input = "nonnom";

            foreach (array_reverse($punctuation_array) as $temp => $value) {
                if ($value == "_") {
                    break;
                }
                $nonnom_input .= $value;
            }
            $s = $nonnom_input;
        } else {
            $num = floor($length / $length_repeat_gram);
            $remainder = $length % $length_repeat_gram;
            $nonnom_input = str_repeat($repeat_gram, $num);
            $nonnom_input .= substr($repeat_gram, 0, $remainder);

            // Add punction back in.
            $s = "";
            $i = 0;
            //        $punctuation_array = str_split($punctuation_string);
            foreach ($punctuation_array as $temp => $punctuation) {
                if ($punctuation != " ") {
                    $s .= substr($punctuation_string, $i, 1);
                } else {
                    $s .= substr($nonnom_input, $i, 1);
                }
                $i += 1;
            }
        }

        return $s;
    }

    public function respondResponse()
    {
        $this->cost = 100;

        // Thing stuff

        $this->thing->flagGreen();

        $this->thing_report['message'] = $this->sms_message;

        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

    }

    public function makeSMS()
    {
        switch (true) {
            case (!isset($this->words)):
                $sms = "NONNOM | no words found";
                break;
            case(count($this->words) == 0):
                $sms = "NONNOM | no words found";
                break;
            case ($this->words[0] == false):
                $sms = "NONNOM | no words found";
                break;
            case (count($this->words) > 1):
                $sms = "NOMINALS ARE ";
                $sms .= implode(" ", $this->nominal);
                break;
            case (count($this->words) > 1):
                $sms = "NOMINAL IS ";
                $sms .= implode(" ", $this->nominal);

                break;

            default:
                $sms = "NONNOM | no match found";
        }

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeEmail()
    {
        $this->email_message = "WORD | ";
    }

    public function readSubject()
    {
        $input = strtolower($this->input);

        if (strtolower($input) == "nonnom") {
            return;
        }

        $keywords = ['nonnom', 'nonnominal', 'non-nom', 'non-nominal'];
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {

                if (strpos(strtolower($piece), $command) !== false) {


                    switch ($piece) {
                        case 'nonnominal':
                            $prefix = 'nonnominal';
                        case 'non-nominal':
                            $prefix = 'non-nominal';
                        case 'non-nom':
                            $prefix = 'non-nom';
                        case 'nonnom':
                            if (!isset($prefix)) {
                                $prefix = 'nonnom';
                            }
                            $words = preg_replace(
                                '/^' . preg_quote($prefix, '/') . '/',
                                '',
                                $input
                            );
                            $words = ltrim($words);


                            //$this->search_words = $words;
                            $this->extractNominals($words);

                            return;

                        default:

                    }
                }
            }
        }

        $this->extractNominals($input);
    }
}
