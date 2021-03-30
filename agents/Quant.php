<?php
namespace Nrwtaylor\StackAgentThing;

class Quant extends Agent
{
    public function init()
    {
        $this->keywords = [];
    }

    public function run()
    {
    }

    public function set()
    {
        $this->thing->json->writeVariable(["quant", "significance"], $this->significance);
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "quant",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["quant", "refreshed_at"],
                $time_string
            );
        }

        // If it has already been processed ...
        $this->significance = $this->thing->json->readVariable(["quant", "significance"]);
    }

    function extractQuants($message = null)
    {
        if ($message == null) {
            $message = $this->subject;
        }

        $this->character_length = strlen($message);
        $this->word_number = count(explode(" ", $message));

        $this->word_count = count(explode(" ", $message));
        $this->significance = $this->getSignificance($message);

        if (!isset($this->words)) {
            $this->getWords($message);
        }

        $n = 0;
        $l = 0;
        $max = 0;

        if (count($this->words) == 0) {
            $this->average_word_length = true;
            $this->max_word_length = true;
            $this->words_number = true;
            return;
        }

        foreach ($this->words as $key => $word) {
            $length = strlen($word);
            if ($length > $max) {
                $max = $length;
            }
            $l = $length + $l;
            $n += 1;
        }

        $this->average_word_length = round($l / $n);
        $this->max_word_length = $max;

        $this->words_number = count($this->words);
    }

    function getWords($message = null)
    {
        if ($message == null) {
            $message = $this->subject;
        }
        if ($message == null) {
            $this->words = [];
            return;
        }

        $agent = new Word($this->thing, $message);
        $this->words = $agent->words;
    }

    function getSignificance($message)
    {
        $significance = 0;

        $words = explode(" ", $message);
        foreach ($words as $key => $word) {
            if (strlen($word) > 4) {
                $significance += 1;
            }
        }
        return $significance;
    }

    public function respondResponse()
    {
        if ($this->agent_input != null) {
            return;
        }

        $this->cost = 100;

        // Thing stuff
        $this->thing->flagGreen();

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $message = "QUANT | no messages found";

        if (isset($this->word_count)) {
            $message = "Undefined response.";

            if ($this->word_count == 0) {
                $message = "No words found.";
                return;
            }

            if ($this->word_count >= 1) {
                $message = $this->word_count . " words counted. " . "Significance " . $this->significance .".";
            }
        }

        $sms = "QUANT | " . $message . " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeEmail()
    {
        $this->email_message = $this->sms_message;
    }

    public function readQuant()
    {
        if ($this->word_count != 0) {
            $this->thing->log(
                $this->agent_prefix .
                    "completed with " .
                    $this->word_count .
                    " wordcount."
            );
        } else {
            $this->words = null;
            $this->thing->log(
                $this->agent_prefix . "did not find words to quantify."
            );
        }
    }

    public function readSubject()
    {
        if ($this->agent_input != null) {
            $input = $this->agent_input;
        } else {
            $input = strtolower($this->subject);
        }

        $keywords = ["quant", "quantities", "quantitative"];
        $pieces = explode(" ", strtolower($input));
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "quant":
                        case "quants":
                        case "quantities":
                        case "quantitative":
                            $prefix = $piece;
                            if (!isset($prefix)) {
                                $prefix = "quant";
                            }
                            $words = preg_replace(
                                "/^" . preg_quote($prefix, "/") . "/",
                                "",
                                $input
                            );
                            $words = ltrim($words);

                            $this->extractQuants($words);
                            return;

                        default:
                    }
                }
            }
        }

        $this->extractQuants($input);
    }
}
