<?php
/**
 * Ngram.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Ngram extends Agent
{
    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->resource_path = $GLOBALS["stack_path"] . "resources/words/";

        $this->keywords = [];

        $this->thing->log(
            $this->agent_prefix .
                "ran for " .
                number_format(
                    $this->thing->elapsed_runtime() - $this->start_time
                ) .
                "ms."
        );

        $this->thing_report["log"] = $this->thing->log;
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "ngram",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            //$this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["ngram", "refreshed_at"],
                $time_string
            );
        }

        //$this->reading = $this->thing->json->readVariable( array("ngram", "reading") );
    }

    public function set()
    {
        //$this->thing->json->writeVariable( array("ngram", "reading"), $this->reading );

        //        if ($this->agent_input == null) {$this->respond();}

        if (isset($this->ngrams) and count($this->ngrams) != 0) {
            $this->ngram = $this->ngrams[0];
            $this->thing->log(
                $this->agent_prefix .
                    "completed with a reading of " .
                    $this->ngram .
                    "."
            );
        } else {
            $this->ngram = null;
            $this->thing->log($this->agent_prefix . "did not find words.");
        }
    }

    public function getNgrams($input, $n = 3)
    {
        if (is_array($input)) {
            return true;
        }
        $words = explode(" ", $input);
        $ngrams = [];

        foreach ($words as $key => $value) {
            if ($key < count($words) - ($n - 1)) {
                $ngram = "";
                for ($i = 0; $i < $n; $i++) {
                    $ngram .= " " . $words[$key + $i];
                }
                $ngrams[] = trim($ngram);
            }
        }
        return $ngrams;
    }

    /**
     *
     * @param unknown $message (optional)
     */
    function getWords($message = null)
    {
        if ($message == null) {
            $message = $this->subject;
        }
        if (!isset($this->word_agent)) {
            $this->word_agent = new Word($this->thing, "word");
        }

        $this->word_agent->extractWords($message);
        $this->words = $this->word_agent->words;
    }

    /**
     *
     * @param unknown $input (optional)
     * @param unknown $n     (optional)
     * @return unknown
     */
    function wordNgrams($input = null, $n = 3)
    {
        if (!isset($this->ngrams)) {
            $this->ngrams = [];
        }
        if (!isset($this->words)) {
            $this->getWords($input);
        }

        $words = $this->words;
        $ngrams = [];

        if (!isset($words) or count($words) == 0) {
            return $ngrams;
        }

        foreach ($words as $key => $value) {
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

    public function extractNgrams($input, $n = 3)
    {
        if (is_array($input)) {
            return true;
        }
        $words = explode(" ", $input);
        $ngrams = [];

        $num = $n;
        foreach (range(1, $n, 1) as $num) {
            foreach ($words as $key => $value) {
                if ($key < count($words) - ($num - 1)) {
                    $ngram = "";
                    for ($i = 0; $i < $num; $i++) {
                        $ngram .= " " . $words[$key + $i];
                    }
                    $ngrams[] = trim($ngram);
                }
            }
        }
        $ngrams = array_unique($ngrams);

        $this->ngrams = $ngrams;
        return $ngrams;
    }

    public function isEqual($text_a, $text_b)
    {
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->cost = 100;

        $this->thing->flagGreen();

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

    }

    /**
     *
     */
    function makeSMS()
    {
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

    public function makeNgrams($lines = [], $field = null)
    {
        $this->thing->log("start make ngrams");
        $ngrams = [];

        foreach ($lines as $index => $line) {
            if ($field != null) {
                $line = $line[$field];
            }

            $line_filter = preg_replace("/[^a-zA-Z0-9 ]+/", "", $line);
            foreach ([2, 3, 4] as $i => $n) {
                $t = $this->extractNgrams($line_filter, $n);
                foreach ($t as $i => $ngram) {
                    // Not sure why.
                    // Some one word ngrams coming through.

                    if (count(explode(" ", $ngram)) == 1) {
                        continue;
                    }

                    if (strlen($ngram) <= 2) {
                        continue;
                    }

                    // Do not do strtolower because we want Ngrams.
                    // 5c is different to 5C.
                    // And we need to be able to spot that.

                    if (!isset($ngrams[$ngram])) {
                        $ngrams[$ngram] = 0;
                    }
                    $ngrams[$ngram] += 1;
                }
            }
        }

        asort($ngrams);
        $this->ngrams = $ngrams;
        $this->ngrams_unique = [];
        $this->ngrams_duplicate = [];
        $html_ngrams_unique = "";
        $html_ngrams_duplicate = "";
        foreach ($ngrams as $ngram => $score) {
            // Ignore only words
            if ($score == 1) {
                $this->ngrams_unique[$ngram] = 1;
                $html_ngrams_unique .= $ngram . " " . $score . "<br>";

                continue;
            }
            $this->ngrams_duplicate[$ngram] = $score;

            //            $html_ngrams_duplicate .= $ngram . " " . $score . "<br>";
        }

        $count = 0;
        foreach (array_reverse($this->ngrams_duplicate) as $ngram => $score) {
            $count += 1;
            $html_ngrams_duplicate .= $ngram . " " . $score . "<br>";
            if ($count >= 10) {
                break;
            }
        }

        $this->thing->log("made ngrams");

        $html =
            "<br><br>DUPLICATE NGRAMS<br>" . $html_ngrams_duplicate . "<br>";

        $this->ngram_html = $html;
        $this->thing_report["ngram"] = $html;
    }

    /**
     *
     */
    function makeEmail()
    {
        $this->email_message = "WORD | ";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        //$input = strtolower($this->subject);

        $input = $this->agent_input;
        if ($this->agent_input == null or $this->agent_input == "") {
            $input = $this->subject;
        }

        if ($input == "ngram") {
            return;
        }

        $keywords = ["ngram", "n-gram"];
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "ngram":
                            $prefix = "ngram";
                        case "n-gram":
                            if (!isset($prefix)) {
                                $prefix = "n-gram";
                            }
                            $words = preg_replace(
                                "/^" . preg_quote($prefix, "/") . "/",
                                "",
                                $input
                            );
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
    function contextWord()
    {
        $this->word_context = '
';

        return $this->word_context;
    }
}
