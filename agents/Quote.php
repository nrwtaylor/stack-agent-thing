<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Quote extends Agent
{
    public $var = 'hello';

    public function init()
    {
        $this->node_list = ["quote" => ["quote", "trivia", "nonsense"]];

        $this->number = null;
        $this->unit = "";

        $this->default_state = "easy";
        $this->default_mode = "american";

        // Non-existent meta replacements.
        $this->filename = "No filename provided.";
        $this->title = "No title provided.";
        $this->author = "No author provided.";
        $this->date = "No date provided.";
        $this->version = "No version provided.";

        $this->setMode($this->default_mode);

        $this->character = new Character($this->thing, "character is X");

        $this->qr_code_state = "off";

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->variables_agent = new Variables(
            $this->thing,
            "variables quote " . $this->from
        );
    }

    function isQuote($state = null)
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
        $this->thing->json->writeVariable(["quote", "inject"], $this->inject);

        $this->refreshed_at = $this->current_time;

        $this->variables_agent->setVariable("state", $this->state);
        $this->variables_agent->setVariable("mode", $this->mode);

        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->thing->log(
            $this->agent_prefix . 'set Radio Relay to ' . $this->state,
            "INFORMATION"
        );
    }

    function get()
    {
        $this->previous_state = $this->variables_agent->getVariable("state");
        $this->previous_mode = $this->variables_agent->getVariable("mode");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(
            $this->agent_prefix . 'got from db ' . $this->previous_state,
            "INFORMATION"
        );

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isQuote($this->previous_state)) {
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

        $this->thing->log(
            $this->agent_prefix .
                'got a ' .
                strtoupper($this->state) .
                ' FLAG.',
            "INFORMATION"
        );

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "quote",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["quote", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->inject = $this->thing->json->readVariable(["quote", "inject"]);
    }

    function setState($state)
    {
        $this->state = "easy";
        if (strtolower($state) == "ham" or strtolower($state) == "hard") {
            $this->state = $state;
        }
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
        if ($bank == "quote" or $bank == null) {
            $this->bank = "quotes-american-a01";
        }

        // Example. Repeat.
        if ($bank == "<something>" or $bank == null) {
            $this->bank = "quotes-american-a01";
        }
    }

    function getBank()
    {
        if (!isset($this->state) or $this->state == "easy") {
            $this->bank = "quotes-" . $this->mode . "-a01";
        }

        if (isset($this->inject) and $this->inject != false) {
            $arr = explode("-", $this->inject);
            $this->bank = $arr[0] . "-" . $arr[1] . "-" . $arr[2];
        }
        return $this->bank;
    }

    public function respondResponse()
    {
        $this->makeChoices();
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This creates a quote.";
        $this->thing_report["help"] = 'Try QUOTE.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "quote"
        );
        $this->choices = $this->thing->choice->makeLinks('quote');

        $this->thing_report['choices'] = $this->choices;
    }

    function makeTXT()
    {
        $sms = "QUOTE " . "\n";

        $sms .= trim($this->short_message) . "\n";

        $this->sms_message = $sms;
        $this->thing_report['txt'] = $sms;
    }

    public function infoQuote($text = null)
    {
        $t = $this->stripQuote($text) . "\n";

        $r = str_replace(' 	', '/', $t);

        $answers = explode("/", $r);

        $l = [];
        foreach ($answers as $i => $answer) {
            if ($answer == "") {
                continue;
            }

            // Tailoring.
            if (is_numeric($answer) and $answer < 100) {
                continue;
            }

            // And one more.
            if (substr($answer, 0, 1) == "[") {
                continue;
            }

            $l[] = $answer;
        }

        $r = implode(" / ", $l);

        return $r;
    }

    function makeSMS()
    {
        $sms = "QUOTE " . "\n";

        $sms .= trim($this->short_message) . "\n";

        $sms .= $this->infoQuote($this->text) . "";

        //$sms .= "TEXT WEB";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function getMessages()
    {
        if (isset($this->messages) and $this->messages != null) {
            return;
        }
        //if ($test != false) {$this->messages = $test; return;}
        // Load in the name of the message bank.
        $this->getBank();
        // Latest transcribed sets.
        $resource = "quote/" . $this->bank . ".txt";

        //$filename = "quote/quotes-american-a01.txt";
        $file = $this->resource_path . $resource;

        //        $contents = file_get_contents($file);
        if (!file_exists($file)) {
            $this->response .= "Resource not found. ";
            return true;
        }

        $handle = fopen($file, "r");
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

                $count += 1;

                $text = $this->extractQuote($line);

                $message_array = [
                    "quote" => $text,
                    "text" => $line,
                ];

                $this->messages[] = $message_array;

                if ($bank_info == null) {
                    $bank_meta[] = $line;

                    if (count($bank_meta) == 4) {
                        $bank_info = $this->metaQuote($bank_meta);

                        if ($bank_info != true) {
                            array_shift($this->messages);
                            array_shift($this->messages);
                            array_shift($this->messages);
                            array_shift($this->messages);
                        }
                        continue;
                    }
                }

                //              }
            }

            fclose($handle);
        } else {
            // error opening the file.
        }
    }

    public function metaQuote($meta)
    {
        // Simple test.
        $tokens = explode(":", $meta[0]);

        if (!isset($tokens[1])) {
            return true;
        }

        if (!isset($tokens[2])) {
            return true;
        }

        $title = trim(explode(":", $meta[0])[1]);
        $this->title = $title;
        $author = trim(explode(":", $meta[1])[1]);
        $this->author = $author;

        $date = trim(explode(":", $meta[2])[1]);
        $this->date = $date;

        $version = trim(explode(":", $meta[3])[1]);
        $this->version = $version;

        //    $count = 0;
        $message = null;

        $bank_meta = [
            "title" => $this->title,
            "author" => $this->author,
            "date" => $this->date,
            "version" => $this->version,
        ];

        return $bank_meta;
    }

    public function extractQuote($text = null)
    {
        //https://stackoverflow.com/questions/1017051/php-to-extract-a-string-from-double-quote
        if (preg_match('/"([^"]+)"/', $text, $m)) {
            $text = $m[1];
        } else {
            //preg_match returns the number of matches found,
            //so if here didn't match pattern
        }

        return $text;
    }

    public function getInject()
    {
        $this->getMessages();

        if (!isset($this->messages) or !is_array($this->messages)) {
            $this->num = "X";
            $this->inject = $this->bank . "-" . $this->num;
            return;
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
            $this->bank = $arr[0] . "-" . $arr[1] . "-" . $arr[2];
            $this->num = $arr[3];
        }
    }

    public function getMessage()
    {
        $this->getMessages();

        $is_empty_inject = true;

        if ($this->inject === false) {
            $is_empty_inject = false;
        }

        while (true) {
            $this->getInject();
            if (!isset($this->messages)) {
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

        if (!isset($message)) {
            $message['text'] = 'No quote found.';
            $text = $message['text'];
        }

        $this->message = $message;

        $this->message['text'] = $text;

        $this->quote_text = trim($this->message['quote'], "//");

        $this->text = trim($this->message['text'], "//");

        $this->short_message = "" . $this->quote_text . "\n";

        if ($this->text == "X") {
            $this->response = "No message to pass.";
        }
    }

    function makeMessage()
    {
        $message = $this->short_message . "<br>";
        $uuid = $this->uuid;
        $message .= "<p>" . $this->web_prefix . "thing/$uuid/quote\n \n\n<br> ";
        $this->thing_report['message'] = $message;
    }

    public function stripQuote($text)
    {
        $t = str_replace($this->quote, "", $text);
        $t = str_replace('""', '', $t);

        return $t;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/quote';

        $web = "<b>Quote Agent</b>";
        $web .= "<p>";

        if (isset($this->quote)) {
            $web .= "" . $this->quote;
            $web .= "<p>";
        }

        if (isset($this->text)) {
            $web .= "" . $this->stripQuote($this->text);
        }

        $web .= "<p>";

        $web .= "Message Bank - ";
        $web .= $this->filename . " - ";
        $web .= $this->title . " - ";
        $web .= $this->author . " - ";
        $web .= $this->date . " - ";
        $web .= $this->version . "";

        $web .= "<p>";
        $web .= "Message Metadata - ";

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
        $web .= "Quote question was created about " . $ago . " ago. ";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    public function readSubject()
    {
        $input = strtolower($this->input);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'quote') {
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
