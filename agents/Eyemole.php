<?php
namespace Nrwtaylor\StackAgentThing;

// TODO?

class Eyemole extends Agent
{
    public function init()
    {
        $this->resource_name = "eyemole/eyemole.txt";
        $this->keywords = [];
    }

    public function set()
    {
        $this->thing->Write(
            ["eyemole", "reading"],
            $this->reading
        );
    }

    public function get()
    {
        $time_string = $this->thing->Read([
            "eyemole",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["eyemole", "refreshed_at"],
                $time_string
            );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->Read([
            "eyemole",
            "reading",
        ]);
    }

    function getWord()
    {
        if (!isset($this->words)) {
            $this->extractWords($this->subject);
        }
        if (count($this->words) == 0) {
            $this->word = false;
            return false;
        }
        $this->word = $this->words[0];
        return $this->word;
    }

    function readEyemole()
    {
        $file = $this->resource_path . $this->resource_name;
        $contents = file_get_contents($file);

        $nuggets = explode("s/ ", $contents);

        $agents = new Agents($this->thing, "agents");

        $this->content = [];

        if (!isset($agents->agents)) {return true;}

        foreach ($nuggets as $nugget) {
            foreach ($agents->agents as $id => $agent) {
                $agent = strtolower($agent['name']);
                $first_word = substr($nugget, 0, mb_strlen($agent));

                if (strtolower($agent) == strtolower($first_word)) {
                    $this->content[$agent][] = $nugget;
                    continue;
                }

                if (strtolower($agent) == strtolower("make" . $first_word)) {
                    $this->content[$agent][] = $nugget;
                    continue;
                }
            }
        }
    }

    public function respondResponse()
    {
        $this->cost = 100;

        $this->thing->flagGreen();

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $sms_content = $this->content["sms"][0];
        $litany = [];
        $lines = explode("\n", $sms_content);

        foreach ($lines as $line) {
            if ($lines == "sms") {
                continue;
            }
            preg_match("/^d[1-9]{0,1}[0-9]{0,15}. (.*)$/m", $line, $m);

            if (isset($m[0])) {
                $roll_text = explode(". ", $m[0])[0];
                $litany[$roll_text][] = $m[1];
            }
        }

        foreach ($litany as $roll_code => $text) {
            if ($roll_code == "d") {
                $roll_code = "d1";
            }

            if (!isset($roll_description)) {
                $roll_description = $roll_code;
            } else {
                $roll_description = $roll_description . "+" . $roll_code;
            }
        }

        $roll = new Roll($this->thing, "roll " . $roll_description);

        foreach ($roll->result as $index => $roll) {
            reset($roll);
            $first_key = key($roll);

            if ($roll[$first_key] == 1) {
                if ($first_key == "d1") {
                    $first_key = "d";
                }
                $sms = $litany[$first_key][0];
                break;
            }
        }

        $sms = "EYEMOLE | " . $sms;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeEmail()
    {
        $this->email_message = "EYEMOLE";
    }

    public function readSubject()
    {
/*
        if ($this->agent_input == null) {
            $input = strtolower($this->subject);
        } else {
            $input = strtolower($this->agent_input);
        }
*/
//        $input = $this->assert($this->input, "eyemole", false);
        $input = $this->input;

        $keywords = ["eyemole"];
        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "eyemole":
                            $prefix = "eyemole";
                            $words = preg_replace(
                                "/^" . preg_quote($prefix, "/") . "/",
                                "",
                                $input
                            );
                            $words = ltrim($words);
                            $this->search_words = $words;


                        default:
                    }
                }
            }
        }

    }
}
