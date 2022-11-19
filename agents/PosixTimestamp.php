<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class PosixTimestamp extends Agent
{
    public $var = "hello";
    function init()
    {

        $this->keywords = [
            "millis",
            "milli",
            "milliseconds",
            "ms",
            "microtime",
            "micros",
            "micro",
            "microseconds",
            "microseconds",
        ];
        $this->current_time = $this->thing->microtime();

        // Default is not to show end user microtime.
        $this->micro_time_flag = false;

        $this->test = "Development code"; // Always iterative.

        $this->state = null; // to avoid error messages

        $this->posix_timestamp_prefix = "";

        $this->thing_report["help"] =
            "This returns the current posix timestamp. Now. Try MICROTIME. Or TIME.";
    }

    function test()
    {
        $file = $this->resource_path . "posix-timestamp/test.txt";
        if (!file_exists($file)) {
            return true;
        }

        $test_corpus = @file_get_contents($file);
        if ($test_corpus === false) {
            $this->response .= "Could not load test corpus. ";
            return true;
        }
        $test_corpus = explode("\n", $test_corpus);

        $this->response = "";
        foreach ($test_corpus as $key => $line) {
            if ($line == "-") {
                break;
            }
            $this->extractPosixTimestamp($line);
            if (
                isset($this->posix_timestamp) and
                $this->posix_timestamp != null
            ) {
                $line .
                    "<br>" .
                    "posix-timestamp " .
                    $this->posix_timestamp .
                    "<br>" .
                    "<br>";
            }
        }
    }

    public function get()
    {
        $time_string = $this->thing->Read(["posix-timestamp", "refreshed_at"]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["posix-timestamp", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->posix_timestamp = $this->thing->Read([
            "posix-timestamp",
            "timestamp",
        ]);
    }

    public function set()
    {
        if ($this->posix_timestamp != false) {
            $this->thing->Write(
                ["posix-timestamp", "timestamp"],
                $this->posix_timestamp
            );
        }
    }

    function validPosixTimestamp(string $date, string $format = "Y-m-d"): bool
    {
        $dateObj = \DateTime::createFromFormat($format, $date);
        return $dateObj && $dateObj->format($format) == $date;
    }

    public function hasPosixTimestamp($text = null)
    {
        $timestamp = $this->extractTimestamp($text);

        if (is_string($timestamp) === true) {
            return true;
        }
        if ($timestamp === true) {
            return true;
        }

        return false;
    }

    public function isPosixTimestamp($text = null)
    {
        if ($text == null or $text == "") {
            return false;
        }
        // Apparently strtotime reads "zulu" as current time?
        // Return true. Because this is not a timestamp we can meaningfully extract time from.
        if (strtolower($text) == "zulu") {
            return false;
        }

        if ($this->validPosixTimestamp($text, "Y-m-d\TH:i:s\J") === true) {
            return true;
        }
        if ($this->validPosixTimestamp($text, "Y-m-d\TH:i:s\Z") === true) {
            return true;
        }

        return false;
    }

    function extractPosixTimestamp($text = null)
    {
        $tokens = explode(" ", $text);
        foreach ($tokens as $i => $token) {
            if ($this->isTimestamp($token) === true) {
                return $token;
            }

            if ($this->validPosixTimestamp($token, "Y-m-d") === true) {
                return $token;
            }

            if (isset($tokens[$i + 1])) {
                if (
                    $this->validPosixTimestamp(
                        $token . " " . $tokens[$i + 1],
                        "Y-m-d h:i:s"
                    ) === true
                ) {
                    return $token . " " . $tokens[$i + 1];
                }
            }

            if (isset($tokens[$i + 2])) {
                if (
                    $this->validPosixTimestamp(
                        $token . " " . $tokens[$i + 1] . " " . $tokens[$i + 2],
                        "Y m d"
                    ) === true
                ) {
                    return $token .
                        " " .
                        $tokens[$i + 1] .
                        " " .
                        $tokens[$i + 2];
                }
            }

            if (isset($tokens[$i + 2])) {
                if (
                    $this->validPosixTimestamp(
                        $token . " " . $tokens[$i + 1] . " " . $tokens[$i + 2],
                        "F j, Y"
                    ) === true
                ) {
                    return $token .
                        " " .
                        $tokens[$i + 1] .
                        " " .
                        $tokens[$i + 2];
                }
            }

            if (isset($tokens[$i + 2])) {
                if (
                    $this->validPosixTimestamp(
                        $token . " " . $tokens[$i + 1] . " " . $tokens[$i + 2],
                        "F j Y"
                    ) === true
                ) {
                    return $token .
                        " " .
                        $tokens[$i + 1] .
                        " " .
                        $tokens[$i + 2];
                }
            }

            if (isset($tokens[$i + 2])) {
                if (
                    $this->validPosixTimestamp(
                        $token . " " . $tokens[$i + 1] . " " . $tokens[$i + 2],
                        "j F Y"
                    ) === true
                ) {
                    return $token .
                        " " .
                        $tokens[$i + 1] .
                        " " .
                        $tokens[$i + 2];
                }
            }
        }
        return false;
        // Get the clock time.
        // Then date
        $this->posix_timestamp = "X";
        return $this->posix_timestamp;
    }

    function readPosixTimestamp($variable = null)
    {
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/posix-timestamp";

        $this->node_list = [
            "posix-timestamp" => ["posix-timestamp", "timestamp"],
        ];

        //$this->makeChoices();

        $web = "";
        $web .= "<p>";
        $web .= "<p>";
        $this->makeLink();
        $web .= '<a href="' . $this->link . '">';
        $web .= $this->link;
        $web .= "</a>";
        $web .= " ";
        $web .= "Copy-and-paste link";
        $web .= "<br />";
        $web .= $this->posix_timestamp;
        //$web .= $this->sms_message;

        $web .= "<br>";
        $web .= "<p>";

        $this->thing_report["web"] = $web;
    }

    public function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "posix-timestamp"
        );
        $choices = $this->thing->choice->makeLinks("posix-timestamp");
        $this->thing_report["choices"] = $choices;
    }

    function makeLink()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/posix-timestamp";

        $this->link = $link;
        $this->thing_report["link"] = $link;
    }

    // Devstack
    // ?
    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        } else {
            $this->thing_report["info"] =
                'Agent input was "' . $this->agent_input . '".';
        }
    }

    public function findPosixTimestamp($text = null)
    {
        if ($this->posix_timestamp != false) {
            return;
        }
        //        if (strtotime($text) == false) {return true;}

        // Apparently strtotime reads "zulu" as 1605193914;
        //        if (strtolower($text) == "zulu") {return true;}
        $this->posix_timestamp = $text;
    }

    public function makeSMS()
    {
        if (isset($this->thing_report["sms"])) {
            return;
        }
        $sms = "POSIX TIMESTAMP | " . $this->posix_timestamp;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function readSubject()
    {
        if ($this->agent_input == "test") {
            $this->test();
            return;
        }

        $input = $this->agent_input;
        if ($this->agent_input == null or $this->agent_input == "") {
            $input = $this->subject;
        }
        if ($this->agent_input == "posix-timestamp") {
            $input = $this->subject;
        }
        if ($this->isPosixTimestamp($input)) {
            $this->findPosixTimestamp($input);
            return;
        }

        $stamp_agent = new Stamp($this->thing, $input);
        $this->thing_report = $stamp_agent->thing_report;
    }
}
