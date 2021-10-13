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

        $this->event_horizon = 60 * 60 * 24;
        $this->y_max_limit = null;
        $this->y_min_limit = null;
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
            return true;
        }

        return false;
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
        if ($this->isBaseline($this->previous_state)) {
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
        $baseline = $this->priorBaseline();

        $microtime_agent = new Microtime($this->prior_thing, "microtime");
        $this->last_timestamp = $microtime_agent->timestamp;
    }

    function setState($state)
    {
        $this->state = "easy";
    }

    public function priorBaseline()
    {
        $things = $this->getThings('baseline');

        foreach (array_reverse($things) as $uuid => $thing) {
            if ($uuid == $this->uuid) {
                continue;
            }
            $this->prior_thing = new Thing($uuid);
            //$this->response .= "Got prior thing. ";
            break;
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
        if ($bank == "baseline" or $bank == null) {
            $this->bank = "baseline-a03";
        }
    }

    function getBank()
    {

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

    public function statisticsBaseline()
    {
        $statistics_agent = new Statistics(
            $this->thing,
            "statistics baseline response_time"
        );
        //$this->response .= $statistics_agent->response;

        $this->statistics_text = "";
        if (
            isset($statistics_agent->minimum) and
            isset($statistics_agent->mean) and
            isset($statistics_agent->maximum) and
            isset($statistics_agent->count) and
            isset($statistics_agent->number)
        ) {
            $this->statistics_text =
                $statistics_agent->number . 's'.
                ' ' .
                "[" .
                $statistics_agent->minimum .
                " (" .
                $statistics_agent->mean .
                ") " .
                $statistics_agent->maximum .
                "] " .
                "N=" .
                $statistics_agent->count;
        }
    }

    function makeSMS()
    {
        $sms = "BASELINE " . "\n";
        if (is_numeric($this->response_time)) {
            $sms .= $this->response_time . "s\n";
        }

        if (isset($this->statistics_text)) {
            $sms .= $this->statistics_text . "\n";
        }

        $sms .= trim($this->short_message) . "\n";

        if ((is_string($this->response)) and ($this->response != "")) {
            $sms .= $this->response . "\n";
        }

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
            $this->response .= "No message to pass.";
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

        //            $web = '<a href="' . $link . '">';
        $web .= $this->image_embedded;
        //            $web .= "</a>";

        if (isset($this->text)) {
            $web .= "" . $this->text;
        }

        $web .= "<p>";

        if (isset($this->response_time) and $this->response_time != false) {
            $web .= "Response time is ";
            $web .= "" . $this->response_time . " seconds";
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
        $input = strtolower($this->input);

        $pieces = explode(" ", strtolower($input));
        $this->statisticsBaseline();
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

    public function makeImage()
    {
        $this->image = $this->chart_agent->image;
    }

    function historyBaseline()
    {
        // See if a stack record exists.
        //$findagent_thing = new Findagent($this->thing, 'number '. $this->horizon);
        $things = $this->getThings('baseline');

        $this->baselines_history = [];

        if ($things === true) {
            return;
        }
        if ($things === null) {
            return;
        }

        foreach ($things as $uuid => $thing) {
            //     $variables_json= $thing_object['variables'];
            //     $variables = $this->thing->json->jsontoArray($variables_json);
            $variables = $thing->variables;
            if (isset($variables['baseline'])) {
                $response_time = "X";
                $refreshed_at = "X";

                if (isset($variables['baseline']['refreshed_at'])) {
                    $refreshed_at = $variables['baseline']['refreshed_at'];
                }
                if (isset($variables['baseline']['response_time'])) {
                    $response_time = $variables['baseline']['response_time'];
                }
            }

            $age = strtotime($this->current_time) - strtotime($refreshed_at);
            if ($age > $this->event_horizon) {
                continue;
            }

            if (!is_numeric($response_time)) {
                continue;
            }

            $this->baselines_history[] = [
                "timestamp" => $refreshed_at,
                "response_time" => $response_time,
            ];
        }

        $refreshed_at = [];
        foreach ($this->baselines_history as $key => $row) {
            $refreshed_at[$key] = $row['timestamp'];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->baselines_history);
    }

    public function makeChart()
    {
        if (!isset($this->baselines_history)) {
            $this->historyBaseline();
        }
        $t = "NUMBER CHART\n";
        $points = [];

        // Defaults needed.
        $x_min = 1e99;
        $x_max = -1e99;

        $y_min = 1e99;
        $y_max = -1e99;

        foreach ($this->baselines_history as $i => $number_object) {
            $created_at = strtotime($number_object['timestamp']);
            $number = $number_object['response_time'];

            $points[$created_at] = $number;

            if (!isset($x_min)) {
                $x_min = $created_at;
            }
            if (!isset($x_max)) {
                $x_max = $created_at;
            }

            if ($created_at < $x_min) {
                $x_min = $created_at;
            }
            if ($created_at > $x_max) {
                $x_max = $created_at;
            }

            if (!isset($y_min)) {
                $y_min = $number;
            }
            if (!isset($y_max)) {
                $y_max = $number;
            }

            if ($number < $y_min) {
                $y_min = $number;
            }
            if ($number > $y_max) {
                $y_max = $number;
            }
        }

        $this->chart_agent = new Chart(
            $this->thing,
            "chart number " . $this->from
        );
        $this->chart_agent->points = $points;

        $this->chart_agent->x_min = $x_min;
        $this->chart_agent->x_max = $x_max;
        $this->chart_agent->x_max = strtotime($this->thing->time);

        if ($this->y_min_limit != false or $this->y_min_limit != null) {
            $y_min = $this->y_min_limit;
        }

        $this->chart_agent->y_min = $y_min;

        if ($this->y_max_limit != false or $this->y_max_limit != null) {
            $y_max = $this->y_max_limit;
        }
        $this->chart_agent->y_max = $y_max;

        $y_spread = 100;
        if (
            $this->chart_agent->y_min == false and
            $this->chart_agent->y_max === false
        ) {
            //
        } elseif (
            $this->chart_agent->y_min == false and
            is_numeric($this->chart_agent->y_max)
        ) {
            $y_spread = $y_max;
        } elseif (
            $this->chart_agent->y_max == false and
            is_numeric($this->chart_agent->y_min)
        ) {
            // test stack
            $y_spread = abs($this->chart_agent->y_min);
        } else {
            $y_spread = $this->chart_agent->y_max - $this->chart_agent->y_min;
            //            if ($y_spread == 0) {$y_spread = 100;}
        }
        if ($y_spread == 0) {
            $y_spread = 100;
        }

        $this->chart_agent->y_spread = $y_spread;
        $this->chart_agent->drawGraph();
    }

    public function makePNG()
    {
        if (!isset($this->image)) {
            return true;
        }
        $this->chart_agent->makePNG();
        $this->image_embedded = $this->chart_agent->image_embedded;
        $this->thing_report['png'] = $this->chart_agent->thing_report['png'];
    }
}
