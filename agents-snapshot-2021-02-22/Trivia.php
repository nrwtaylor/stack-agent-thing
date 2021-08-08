<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Trivia extends Agent
{
    public $var = 'hello';

    public function init()
    {
        // Need to add in mode changing - origin / relay

        //        $this->node_list = array("rocky"=>array("rocky", "charley", "nonsense"));
        $this->node_list = ["trivia" => ["trivia", "nonsense"]];

        $this->number = null;
        $this->unit = "";

        $this->default_state = "easy";
        $this->default_mode = "relay";

        $this->setMode($this->default_mode);

        $this->character = new Character(
            $this->thing,
            "character is Rocket J. Squirrel"
        );

        $this->qr_code_state = "off";

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->trivia = new Variables(
            $this->thing,
            "variables trivia " . $this->from
        );

        //var_dump($this->thing);
        //exit();
        //        $this->getMemcached();
    }

    function isTrivia($state = null)
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
            ["trivia", "inject"],
            $this->inject
        );

        $this->refreshed_at = $this->current_time;

        $this->trivia->setVariable("state", $this->state);
        $this->trivia->setVariable("mode", $this->mode);

        $this->trivia->setVariable("refreshed_at", $this->current_time);

        $this->thing->log(
            $this->agent_prefix . 'set Radio Relay to ' . $this->state,
            "INFORMATION"
        );
    }

    function get()
    {
        $this->previous_state = $this->trivia->getVariable("state");
        $this->previous_mode = $this->trivia->getVariable("mode");
        $this->refreshed_at = $this->trivia->getVariable("refreshed_at");

        $this->thing->log(
            $this->agent_prefix . 'got from db ' . $this->previous_state,
            "INFORMATION"
        );

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isTrivia($this->previous_state)) {
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
            "trivia",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["trivia", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->inject = $this->thing->json->readVariable([
            "trivia",
            "inject",
        ]);


    }

    function setState($state)
    {
        $this->state = "easy";
        if (
            strtolower($state) == "16ln" or
            strtolower($state) == "hard" or
            strtolower($state) == "easy"
        ) {
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
        if ($bank == "trivia" or $bank == null) {
            $this->bank = "trivia-a01";
            //$this->bank = "easy-a05";
        }
        /*
        if ($bank == "hard") {
            $this->bank = "hard-a06";
        }

        if ($bank == "16ln") {
            $this->bank = "16ln-a02";
        }

        if ($bank == "ics213") {
            $this->bank = "ics213-a01";
        }
*/
    }

    function getBank()
    {
        //$this->bank = "queries";
        //return $this->bank;

        if (!isset($this->state) or $this->state == "easy") {
            $this->bank = "trivia-a01";
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

        $this->thing_report["info"] =
            "This creates a question.";
        $this->thing_report["help"] =
            'Try TRIVIA.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "trivia"
        );
        $this->choices = $this->thing->choice->makeLinks('trivia');

        $this->thing_report['choices'] = $this->choices;
    }

    function makeTXT()
    {
        $sms = "TRIVIA ". "\n";

        $sms .= trim($this->short_message) . "\n";


        $this->sms_message = $sms;
        $this->thing_report['txt'] = $sms;
    }


    function makeSMS()
    {
        $sms = "TRIVIA ". "\n";

        $sms .= trim($this->short_message) . "\n";

        $sms .= "TEXT WEB";
        // $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }


    public function getMessages()
    {
        if (isset($this->messages)) {
            return;
        }

        //$test = $this->mem_cached->get('radiorelay-queries');
        //if ($test != false) {$this->messages = $test; return;}
        // Load in the name of the message bank.
        $this->getBank();
        // Latest transcribed sets.
        $filename = "/vector/messages-" . $this->bank . ".txt";

        $this->filename = $this->bank . ".txt";

        //$filename = "/radiorelay/" . $this->filename;
        $filename = "/radiorelay/trivia-a01.txt";
        $file = $this->resource_path . $filename;
        //        $contents = file_get_contents($file);

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

                // returns "d"
                //                $line_count = count($message) - 1;

                //  if ($line_count == 10) {
                // recognize as J-format

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

                //if ($bank_fino == null) {continue;}

                $count += 1;

                //              if ($line_count == 10) {
                // recognize as J-format

                $text = $line;

                $message_array = [
                    "text" => $text,
                ];

                $this->messages[] = $message_array;
                //              }

            }

            fclose($handle);
        } else {
            // error opening the file.
        }
        //        $this->mem_cached->set("radiorelay-queries", $this->messages);
    }

    public function getInject()
    {
        $this->getMessages();

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

    public function getMessage()
    {
        //        $this->getInject();
        $this->getMessages();

        $is_empty_inject = true;

        if ($this->inject === false) {
            $is_empty_inject = false;
        }

        while (true) {
            $this->getInject();

            $message = $this->messages[$this->num];

$text = $message['text'];
      //      $radiogram_agent = new Radiogram($this->thing, "radiogram");
      //      $text = $radiogram_agent->translateRadiogram($message['text']);

// Tidy up space after comma if there is none.
$text = str_replace(",",", ",$text);
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


        $this->message['text'] = $text;

        $this->text = trim($this->message['text'], "//");



        $this->short_message =
            "" .
            $this->text .
            "\n";

        if (
            $this->text == "X"
        ) {
            //$this->response = $this->number . " " . $this->unit . ".";
            $this->response = "No message to pass.";
        }


    }

    function makeMessage()
    {
        $message = $this->short_message . "<br>";
        $uuid = $this->uuid;
        $message .=
            "<p>" . $this->web_prefix . "thing/$uuid/trivia\n \n\n<br> ";
        $this->thing_report['message'] = $message;
    }


    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/trivia';

//        if (!isset($this->html_image)) {
//            $this->makePNG();
//        }

        $web = "<b>Trivia Agent</b>";
        $web .= "<p>";

        if (isset($this->text)) {
            $web .= "" . $this->text;
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

	$created_at_text = "";
	if (isset($this->thing->thing->created_at)) {
	    $created_at_text = $this->thing->thing->created_at;
	}

        $web .=
            $this->inject .
            " - " .
            $this->thing->nuuid .
            " - " .
            $created_at_text;

        //        $ago = $this->thing->human_time ( time() - strtotime( $this->thing->thing->created_at ) );

        //        $web .= "Inject was created about ". $ago . " ago.";
        //        $web .= "<p>";
        //        $web .= "Inject " . $this->thing->nuuid . " generated at " . $this->thing->thing->created_at. "\n";

        $togo = $this->thing->human_time($this->time_remaining);
        $web .= " - " . $togo . " remaining.<br>";

        $web .= "<br>";

        $link = $this->web_prefix . "privacy";
        $privacy_link = '<a href="' . $link . '">' . $link . "</a>";

        $ago = $this->thing->human_time(
            time() - strtotime($created_at_text)
        );
        $web .= "Trivia question was created about " . $ago . " ago. ";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }


    public function readSubject()
    {

        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'trivia') {
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
