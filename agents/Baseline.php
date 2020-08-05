<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Baseline extends Agent
{
    // devstack

    public $var = 'hello';

    public function init()
    {
        $this->node_list = ["baseline" => ["baseline", "nonsense"]];

        $this->number = null;
        $this->unit = "";

        $this->default_state = "easy";
        $this->default_mode = "relay";

        $this->setMode($this->default_mode);

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->baseline = new Variables(
            $this->thing,
            "variables baseline " . $this->from
        );
    }
    function isBaseline($state = null)
    {
        if ($state == null) {
            if (!isset($this->state)) {
                $this->state = "easy";
            }

            $state = $this->state;
        }

        if ($state == "easy" or $state == "hard") {
            return false;
        }

        return true;
    }

    function set($requested_state = null)
    {
        $this->thing->json->writeVariable(
            ["baseline", "inject"],
            $this->inject
        );

        $this->refreshed_at = $this->current_time;

        $this->baseline->setVariable("state", $this->state);
        $this->baseline->setVariable("mode", $this->mode);

        $this->baseline->setVariable("refreshed_at", $this->current_time);

        if (isset($this->prior_thing)) {
            $this->prior_thing->json->writeVariable(
                ["baseline", "response_time"],
                $this->response_time
            );
        }
    }

    function get()
    {
        $this->previous_state = $this->baseline->getVariable("state");
        $this->previous_mode = $this->baseline->getVariable("mode");
        $this->refreshed_at = $this->baseline->getVariable("refreshed_at");

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isBaseline($this->previous_state)) {
            $this->state = $this->previous_state;
        } else {
            $this->state = $this->default_state;
        }

        if ($this->state == false) {
            $this->state = $this->default_state;
        }

        if ($this->previous_mode == false) {
            $this->previous_mode = $this->default_mode;
        }

        $this->mode = $this->previous_mode;

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "baseline",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["baseline", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->inject = $this->thing->json->readVariable([
            "baseline",
            "inject",
        ]);

        $this->last_response_time = $this->thing->json->readVariable([
            "baseline",
            "response_time",
        ]);

        $this->microtime_agent = new Microtime($this->thing, "microtime");
        $this->timestamp = $this->microtime_agent->timestamp;
        $this->getLink();
        $microtime_agent = new Microtime($this->prior_thing, "microtime");
        $this->last_timestamp = $microtime_agent->timestamp;
    }

    function setState($state)
    {
        $this->state = "easy";
    }

    function getState()
    {
        if (!isset($this->state)) {
            $this->state = "easy";
        }
        return $this->state;
    }

    function setBank($bank = null)
    {
        if ($bank == "baseline" or $bank == null) {
            $this->bank = "baseline-a03";
        }
    }

    function getBank()
    {
        //$this->bank = "queries";
        //return $this->bank;

        if (!isset($this->state) or $this->state == "easy") {
            $this->bank = "baseline-a03";
        }

        if (isset($this->inject) and $this->inject != false) {
            $arr = explode("-", $this->inject);
            $this->bank = $arr[0] . "-" . $arr[1];
        }
        return $this->bank;
    }

    public function respondResponse()
    {
        $this->makeChoices();
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This creates a question.";
        $this->thing_report["help"] = 'Try BASELINE.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "baseline"
        );
        $this->choices = $this->thing->choice->makeLinks('baseline');

        $this->thing_report['choices'] = $this->choices;
    }

    function makeTXT()
    {
        $sms = "BASELINE " . "\n";

        $sms .= trim($this->short_message) . "\n";

        $this->sms_message = $sms;
        $this->thing_report['txt'] = $sms;
    }

    public function run()
    {
        $this->calcBaseline();
    }

    public function calcBaseline()
    {
        if (
            $this->microtime_agent->epochtimeMicrotime($this->timestamp) <
            $this->microtime_agent->epochtimeMicrotime($this->last_timestamp)
        ) {
            $this->response_time = "X";
            return;
        }

        $age =
            $this->microtime_agent->epochtimeMicrotime($this->timestamp) -
            $this->microtime_agent->epochtimeMicrotime($this->last_timestamp);
        $this->response_time = $age;
    }

    function makeSMS()
    {
        $sms = "BASELINE " . "\n";
        if (is_numeric($this->response_time)) {
            $sms .= number_format($this->response_time * 1000) . "ms\n";
        }
        $sms .= trim($this->short_message) . "\n";

        $sms .= "TEXT WEB";
        // $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function getBaseline()
    {
        $this->lines = $this->loadBank('baseline-a03');
    }

    public function getMessages()
    {
        $this->messages = $this->loadBank('questions-a01');
    }

    public function loadBank($bank_name = null)
    {
        //if (isset($this->messages)) {
        //    return;
        //}
        $lines = [];
        // Load in the name of the message bank.
        $this->getBank();

        // Latest transcribed sets.

        if ($bank_name == null) {
            $bank_name = $this->bank;
        }
        $this->filename = $bank_name . ".txt";

        $filename = "baseline/" . $this->filename;
        $file = $this->resource_path . $filename;
        //        $contents = file_get_contents($file);

        $handle = @fopen($file, "r");

        if ($handle === false) {
            $this->title = 'Not available';
            $this->author = 'Not available';
            $this->date = 'Not available';
            $this->version = 'Not available';
            $lines = [];
            return $lines;
        }

        $count = 0;

        $bank_info = null;
        $bank_meta = [];
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                //              $count += 1;
                if ($line == "---") {
                    continue;
                }

                if (substr($line, 0, 1) == "#") {
                    continue;
                }

                if ($bank_info == null) {
                    $bank_meta[] = $line;

                    if (count($bank_meta) == 4) {
                        $title = trim(explode(":", $bank_meta[0])[1]);
                        $this->title = $title;
                        $author = trim(explode(":", $bank_meta[1])[1]);
                        $this->author = $author;

                        $date = trim(explode(":", $bank_meta[2])[1]);
                        $this->date = $date;

                        $version = trim(explode(":", $bank_meta[3])[1]);
                        $this->version = $version;

                        //    $count = 0;
                        $message = null;

                        $bank_info = [
                            "title" => $this->title,
                            "author" => $this->author,
                            "date" => $this->date,
                            "version" => $this->version,
                        ];
                        continue;
                    }
                    continue;
                }

                $count += 1;

                //              if ($line_count == 10) {
                // recognize as J-format

                $text = $line;

                $message_array = [
                    "text" => $text,
                ];

                $lines[] = $message_array;
                //              }
            }

            fclose($handle);
        } else {
            // error opening the file.
        }

        //$this->messages = $lines;
        return $lines;
    }

    public function getInject()
    {
        $this->getMessages();

        if (!isset($this->messages) or $this->messages == []) {
            return true;
        }

        if ($this->inject == false) {
            $this->num = array_rand($this->messages);
            $this->inject = $this->bank . "-" . $this->num;
        }

        if ($this->inject == null) {
            // Pick a random message
            $this->num = array_rand($this->messages);

            $this->inject = $this->bank . "-" . $this->num;
        } else {
            $arr = explode("-", $this->inject);
            $this->bank = $arr[0] . "-" . $arr[1];
            $this->num = $arr[2];
        }
    }

    public function getWord()
    {
        $tokens = [];
        $text = "";
        foreach ($this->lines as $i => $line) {
            $new_tokens = explode(" ", $line['text']);
            foreach ($new_tokens as $j => $token) {
                $tokens[] = preg_replace('/[^\w\s]/', '', $token);
            }
            //$tokens = array_merge($new_tokens, $tokens);
            $text .= $line['text'] . " ";
        }

        $brilltagger = new Brilltagger($this->thing, "brilltagger");
        $m = $brilltagger->tag($text);
        $this->baseline_tokens = $m;

        foreach ($tokens as $i => $token) {
            if (mb_strlen($token) <= 6) {
                unset($tokens[$i]);
            }
        }

        //        $token = $tokens[array_rand($tokens)];
        // score tokens

        foreach ($tokens as $i => $token) {
            if (!isset($score[strtolower($token)])) {
                $score[strtolower($token)] = 0;
            }
            $score[strtolower($token)] += 1;
        }

        $token = false;
        if (isset($score)) {
            $max_score = 0;
            foreach ($score as $i => $s) {
                if ($s > $max_score) {
                    $token = $i;
                    $max_score = $s;
                }
            }
        }

        return $token;
    }

    public function getMessage()
    {
        //        $this->getInject();
        $this->getMessages();

        $this->getBaseline();

        $word = $this->getWord();
        $is_empty_inject = true;

        if ($this->inject === false) {
            $is_empty_inject = false;
        }

        $text = "Test.";
        $message['text'] = $text;

        while (true) {
            $this->getInject();
            if (!isset($this->num)) {
                break;
            }
            $message = $this->messages[$this->num];

            $text = $message['text'];

            // Tidy up space after comma if there is none.
            $text = str_replace(",", ", ", $text);
            $text = str_replace("  ", " ", $text);
            $text = ucfirst($text);

            $word_count = count(explode(" ", $text));

            if ($is_empty_inject === true) {
                break;
            }

            if ($word_count >= 25) {
                $this->inject = null;
                continue;
            }

            if (stripos($text, 'sex') !== false) {
                $this->inject = null;
                continue;
            }

            if (stripos($text, 'which of the following') !== false) {
                $this->inject = null;
                continue;
            }
            if (stripos($text, 'which of these') !== false) {
                $this->inject = null;
                continue;
            }

            break;
        }

        $this->message = $message;
        foreach ($this->baseline_tokens as $i => $tagged_token) {
            if ($tagged_token['tag'] == "VBD") {
                $verbs[] = $tagged_token['token'];
            }
        }

        $verb = "X";
        if (isset($verbs)) {
            $verb = $verbs[array_rand($verbs)];
        }
        // verb not used it appears the interjection is a constant.

        //$text = $text . " " . ucwords($word) . ".";

        if (stripos($text, "<verb>") !== false) {
            $text = str_replace("<verb>", $word, $text);
            $text = str_replace("<Verb>", ucwords($word), $text);
        } else {
            if ($word != false) {
                $text = $text . " " . ucwords($word) . ".";
            }
        }

        if (count($this->lines) > 0) {
            if (rand(1, 6) <= 2) {
                $line = $this->lines[array_rand($this->lines)];
                //$phrases = explode(array(".",","),$line);
                $phrases = preg_split("/ (.|,) /", $line['text']);

                $phrase = $phrases[array_rand($phrases)];
                $text = $phrase;

                $ngrams = new Ngram($this->thing, "ngram");

                $t = $ngrams->extractNgrams($phrase, 3);
                if ($t != []) {
                    $phrase = $t[array_rand($t)];
                }

                $text = $phrase;
            }
        }
        $text_agent = new Text($this->thing, "text");

        $punctuated_text = $text_agent->punctuateText($text);

        $this->message['text'] = $punctuated_text;

        $this->text = trim($this->message['text'], "//");

        $this->short_message = "" . $this->text . "\n";

        if ($this->text == "X") {
            //$this->response = $this->number . " " . $this->unit . ".";
            $this->response = "No message to pass.";
        }
    }

    function makeMessage()
    {
        $message = $this->short_message . "<br>";
        $uuid = $this->uuid;
        $message .=
            "<p>" . $this->web_prefix . "thing/$uuid/baseline\n \n\n<br> ";
        $this->thing_report['message'] = $message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/baseline';

        //        if (!isset($this->html_image)) {
        //            $this->makePNG();
        //        }

        $web = "<b>Baseline Agent</b>";
        $web .= "<p>";

        if (isset($this->text)) {
            $web .= "" . $this->text;
        }

        $web .= "<p>";

        if (isset($this->response_time) and $this->response_time != false) {
            $web .= "Response time is ";
            $web .= "" . $this->response_time;
            $web .= "<br>";
        }

        if (
            isset($this->last_response_time) and
            $this->last_response_time != false
        ) {
            $web .= "Last response time is ";
            $web .= "" . $this->last_response_time;
            $web .= "<br>";
        }

        $web .= "<p>";

        $web .= "Message Bank - ";
        //        $web .= "<p>";
        $web .= $this->filename . " - ";
        $web .= $this->title . " - ";
        $web .= $this->author . " - ";
        $web .= $this->date . " - ";
        $web .= $this->version . "";

        $web .= "<p>";
        $web .= "Message Metadata - ";
        //        $web .= "<p>";

        $web .=
            $this->inject .
            " - " .
            $this->thing->nuuid .
            " - " .
            $this->thing->thing->created_at;

        $togo = $this->thing->human_time($this->time_remaining);
        $web .= " - " . $togo . " remaining.<br>";

        $web .= "<br>";

        $link = $this->web_prefix . "privacy";
        $privacy_link = '<a href="' . $link . '">' . $link . "</a>";

        $ago = $this->thing->human_time(
            time() - strtotime($this->thing->thing->created_at)
        );
        $web .= "Baseline question was created about " . $ago . " ago. ";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'baseline') {
                $this->getMessage();

                if (!isset($this->index) or $this->index == null) {
                    $this->index = 1;
                }
                return;
            }
        }

        $this->getMessage();

        if (!isset($this->index) or $this->index == null) {
            $this->index = 1;
        }
    }

    function setMode($mode = null)
    {
        if ($mode == null) {
            return;
        }
        $this->mode = $mode;
    }

    function getMode()
    {
        if (!isset($this->mode)) {
            $this->mode = $this->default_mode;
        }
        return $this->mode;
    }
}
