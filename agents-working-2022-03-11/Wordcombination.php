<?php
/**
 * Ngram.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Wordcombination extends Agent
{
    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
    }

    public function run()
    {
    }

    public function test()
    {
        $test_result = $this->isEqual("quick brown fox", "fox Brown quick");
        $test_result = $this->isEqual("quick brown fox", "fox blue quick");
    }

    public function get()
    {
        $time_string = $this->thing->Read([
            "wordcombination",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["wordcombination", "refreshed_at"],
                $time_string
            );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->Read([
            "wordcombination",
            "reading",
        ]);
    }

    public function set()
    {
        $this->thing->Write(
            ["wordcombination", "reading"],
            $this->reading
        );

        if (
            isset($this->wordcombinations) and
            count($this->wordcombinations) != 0
        ) {
            $this->wordcombination = $this->wordcombinations[0];
            $this->thing->log(
                $this->agent_prefix .
                    'completed with a reading of ' .
                    $this->wordcombination .
                    '.'
            );
        } else {
            $this->wordcombination = null;
            $this->thing->log($this->agent_prefix . 'did not find words.');
        }
    }

    /**
     *
     * @param unknown $message (optional)
     */
    function getWords($message = null)
    {
        $this->words = explode(" ", $message);
    }

    /**
     *
     * @param unknown $input (optional)
     * @param unknown $n     (optional)
     * @return unknown
     */
    function extractWordcombinations($input = null)
    {
        if (!isset($this->words)) {
            $this->getWords($input);
        }
        $this->wordcombinations = $this->wordcombos($this->words);
        return $this->wordcombinations;
    }

    // For an array of n words, return an array of all possible combinations.
    // https://stackoverflow.com/questions/31486946/create-every-possible-combination-in-php
    function wordcombos($words)
    {
        if (count($words) <= 1) {
            $result = $words;
        } else {
            $result = [];
            for ($i = 0; $i < count($words); ++$i) {
                $firstword = $words[$i];
                $remainingwords = [];
                for ($j = 0; $j < count($words); ++$j) {
                    if ($i != $j) {
                        $remainingwords[] = $words[$j];
                    }
                }
                $combos = $this->wordcombos($remainingwords);
                for ($j = 0; $j < count($combos); ++$j) {
                    $result[] = $firstword . ' ' . $combos[$j];
                }
            }
        }
        return $result;
    }

    public function isEqual($text_a, $text_b)
    {
        $words_a = explode(" ", $text_a);
        $words_b = explode(" ", $text_b);

        $word_combos_a = $this->wordcombos($words_a);
        $word_combos_b = $this->wordcombos($words_b);

        foreach ($word_combos_a as $i => $word_combo_a) {
            foreach ($word_combos_b as $j => $word_combo_b) {
                if (strtolower($word_combo_a) == strtolower($word_combo_b)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->cost = 100;

        $this->thing->flagGreen();

        // Make message
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->reading = "X";
        if (isset($this->words) and count($this->words) > 0) {
            $this->reading = count($this->words);
        }

        $this->thing->Write(["word", "reading"], $this->reading);
    }

    /**
     *
     */
    function makeSMS()
    {
        if (isset($this->words)) {
            if (count($this->words) == 0) {
                $this->sms_message = "WORD ORDER | no words found";
                $this->thing_report['sms'] = $this->sms_message;
                return;
            }

            if ($this->words[0] == false) {
                $this->sms_message = "WORD ORDER | no words found";
                $this->thing_report['sms'] = $this->sms_message;
                return;
            }

            if (count($this->words) > 1) {
                $this->sms_message = "WORD ORDERS ARE ";
            } elseif (count($this->words) == 1) {
                $this->sms_message = "WORD ORDER IS ";
            }
            $this->sms_message .= implode(" ", $this->words);
            $this->thing_report['sms'] = $this->sms_message;

            return;
        }

        $this->sms_message = "WORD ORDER | no match found";
        $this->thing_report['sms'] = $this->sms_message;
    }

    /**
     *
     */
    function makeEmail()
    {
        $this->email_message = "WORD ORDER | ";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        if ($this->input == "wordcombination") {
            return;
        }

        $input = strtolower($this->subject);
        if ($this->agent_input != null) {
            $input = $this->agent_input;
        }

        $keywords = [
            'wordcombination',
            'word combination',
            'word combo',
            'word jumble',
        ];
        $pieces = explode(" ", strtolower($input));

        $this->word_count = count($pieces);

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'wordorder':
                        case 'wordcombination':
                        case 'word combination':
                        case 'word-combination':
                            $prefix = $piece;
                        case 'word-order':
                            if (!isset($prefix)) {
                                $prefix = 'word-order';
                            }
                            $words = preg_replace(
                                '/^' . preg_quote($prefix, '/') . '/',
                                '',
                                $input
                            );
                            $words = ltrim($words);

                            $this->extractWordcombinations($words);

                            return;

                        default:
                    }
                }
            }
        }

        $this->extractWordcombinations($input);

        $status = true;
        return $status;
    }
}
