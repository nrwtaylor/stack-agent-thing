<?php
namespace Nrwtaylor\StackAgentThing;

class Punctuation extends Agent
{
    public function init()
    {
        $this->start_time = microtime(true);
        $this->start_time = $this->thing->elapsed_runtime();

        $this->keywords = [];
    }

    public function set() {

        $this->reading = count($this->punctuations);

        $this->thing->json->writeVariable(
            ["punctuation", "reading"],
            $this->reading
        );

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

    public function get() {

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

    }

    function extractPunctuations($input, $min_length = 3)
    {
        //if ($input == null) {$input = $this->subject;}
        if (!isset($this->punctuations)) {
            $this->punctuations = [];
        }
//        if (!isset($this->words)) {
//            $this->getWords();
//        }

//        $words = $this->words;

        $words = $this->extractWords($input);

        $punctuations = [];

        $grams = explode(" ", $input);
        $message = "";

        foreach ($grams as $key => $gram) {
            $gram_filtered = $this->stripPunctuation($gram, "");

            if ($this->isWord($gram_filtered) == false) {
                // Not a word so very likely punctuation
                // in some way

//                $gram_nonnom = $this->nonnomify($gram);
//                $message .= " " . $gram_nonnom;
                $this->punctuations[] = $gram;
            } else {
                $message .= " " . $gram;
            }
        }

        $message = ltrim($message);

        $this->message = $message;

        return $punctuations;
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

    public function respondResponse()
    {
        $this->cost = 100;

        // Thing stuff

        $this->thing->flagGreen();

        // Make message
        $this->thing_report['message'] = $this->sms_message;

        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
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

            $this->sms_message .= implode(" ", $this->punctuations);

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
