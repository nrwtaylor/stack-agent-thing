<?php
namespace Nrwtaylor\StackAgentThing;

class An extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function run()
    {
    }

    public function respondResponse()
    {
    }

    public function filterAn($text = null)
    {
        if ($text == null) {
            return true;
        }

        $tokens = explode(" ", $text);

        foreach ($tokens as $i => $token) {

            // Only process a's and an's.
            if (!(strtolower($token) == "a" or strtolower($token) == "an")) {
                continue;
            }

            if (!isset($tokens[$i + 1])) {
                continue;
            }

/*
dev think about repeated a's and an's
*/
            // Repeated a. Weird. Ignore.
            if ( ( strtolower($tokens[$i]) == "a" and strtolower($tokens[$i+1]) == "a") ) {
                $tokens[$i] = "an";
                continue;
            }
            // Repeated an. Weird. Ignore.
            if ( (strtolower($tokens[$i]) == "an" and strtolower($tokens[$i+1]) == "an")) {
                continue;
            }

            // a followed by an. Ignore.
            if ( (strtolower($tokens[$i]) == "a" and strtolower($tokens[$i+1]) == "an")) {
                $tokens[$i] = "an";
                continue;
            }

            $first_letter_next_token = substr($tokens[$i + 1], 0, 1);

            $vowels = ["a", "e", "i", "o", "u"];
            $tokens[$i] = "a";
            if (in_array($first_letter_next_token, $vowels)) {
                $tokens[$i] = "an";
            }
        }

        $filtered_text = implode(" ", $tokens);

        return $filtered_text;
    }

    public function readSubject()
    {
    }
}
