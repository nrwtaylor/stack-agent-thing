<?php
namespace Nrwtaylor\StackAgentThing;

class Punctuation extends Agent
{
    public function init()
    {
        $this->start_time = microtime(true);
        $this->start_time = $this->thing->elapsed_runtime();
    }

    public function set()
    {
        $this->reading = count($this->punctuations);

        $this->thing->Write(
            ["punctuation", "reading"],
            $this->reading
        );

        if (count($this->punctuations) != 0) {
            $this->punctuation = $this->punctuations[0];
            $this->thing->log(
                $this->agent_prefix .
                    "completed with a reading of " .
                    $this->reading .
                    "."
            );
        } else {
            $this->punctuation = null;
            $this->thing->log(
                $this->agent_prefix . "did not find punctuation makrs."
            );
        }
    }

    public function get()
    {
        $time_string = $this->thing->Read([
            "punctuation",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["punctuation", "refreshed_at"],
                $time_string
            );

/*                $this->punctuations[] = $gram;
            } else {
                $message .= " " . $gram;
            }
*/
        }

        // If it has already been processed ...
        $this->reading = $this->thing->Read([
            "punctuation",
            "reading",
        ]);
    }

    function extractPunctuations($input, $min_length = 3)
    {
        preg_match_all("#[[:punct:]]#", $input, $matches);
        $this->punctuations = $matches[0];
        return $this->punctuations;
    }

    public function stripPunctuation($input, $replace_with = " ")
    {
        return preg_replace("#[[:punct:]]#", $replace_with, $input);
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        // Make message
        $this->thing_report["message"] = $this->sms_message;

        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
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
        //$input = strtolower($this->subject);
        $input = $this->assert($this->input, "punctuation", false);

        $keywords = ["punctuation"];
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "punctuation":
                            if (!isset($prefix)) {
                                $prefix = "punctuation";
                            }
                            $words = preg_replace(
                                "/^" . preg_quote($prefix, "/") . "/",
                                "",
                                $input
                            );
                            $words = ltrim($words);

                            $this->extractPunctuations($words);

                            return;

                        default:
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
