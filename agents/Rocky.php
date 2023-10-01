<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Rocky extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->node_list = ["rocky" => ["rocky", "charley", "nonsense"]];

        $this->number = null;
        $this->unit = "";

        $this->default_state = "easy";
        $this->default_mode = "origin";

        $this->getNuuid();

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->rocky = new Variables(
            $this->thing,
            "variables rocky " . $this->from
        );

        $this->link = $this->web_prefix . "thing/" . $this->uuid . "/rocky";
    }

    function isRocky($state = null)
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
        $this->thing->Write(["rocky", "inject"], $this->inject);

        $this->refreshed_at = $this->current_time;

        $this->rocky->setVariable("state", $this->state);
        $this->rocky->setVariable("mode", $this->mode);

        $this->rocky->setVariable("refreshed_at", $this->current_time);

        $this->thing->log(
            $this->agent_prefix . "set Rocky to " . $this->state,
            "INFORMATION"
        );
    }

    function get()
    {
        $this->previous_state = $this->rocky->getVariable("state");
        $this->previous_mode = $this->rocky->getVariable("mode");
        $this->refreshed_at = $this->rocky->getVariable("refreshed_at");

        $this->thing->log(
            $this->agent_prefix . "got from db " . $this->previous_state,
            "INFORMATION"
        );

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isRocky($this->previous_state)) {
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
                "got a " .
                strtoupper($this->state) .
                " FLAG.",
            "INFORMATION"
        );

        $time_string = $this->thing->Read([
            "rocky",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["rocky", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->inject = $this->thing->Read(["rocky", "inject"]);
    }

    function getNuuid()
    {
        $agent = new Nuuid($this->thing, "nuuid");
        $this->nuuid_png = $agent->PNG_embed;
    }

    function getUuid()
    {
        $agent = new Uuid($this->thing, "uuid");
        $this->uuid_png = $agent->PNG_embed;
    }

    function getQuickresponse($text = null)
    {
        if ($text == null) {
            $text = $this->web_prefix;
        }
        //        $agent = new Qr($this->thing, $text);
        //        $this->quick_response_png = $agent->PNG_embed;

        $this->qr_agent = new Qr($this->thing, $this->link);
        $this->quick_response_png = $this->qr_agent->PNG_embed;
        $this->html_image = $this->qr_agent->html_image;
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
        if ($bank == "easy" or $bank == null) {
            $this->bank = "easy-a05";
        }

        if ($bank == "hard") {
            $this->bank = "hard-a06";
        }

        if ($bank == "16ln") {
            $this->bank = "16ln-a02";
        }

        if ($bank == "ics213") {
            $this->bank = "ics213-a01";
        }
    }

    function getBank()
    {
        if (!isset($this->state) or $this->state == "easy") {
            $this->bank = "easy-a05";
        }

        //        if (!isset($this->bank)) {
        //            $this->bank = "easy-a03";
        //        }

        if ($this->state == "hard") {
            $this->bank = "hard-a06";
        }

        if ($this->state == "16ln") {
            $this->bank = "16ln-a02";
        }

        if ($this->state == "ics213") {
            $this->bank = "ics213-a01";
        }

        if (isset($this->inject) and $this->inject != false) {
            $arr = explode("-", $this->inject);
            $this->bank = $arr[0] . "-" . $arr[1];
        }
        return $this->bank;
    }

    public function respondResponse()
    {
        $this->getResponse();

        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "rocky";
        $this->makeACP125G();

        $this->makeChoices();

        $this->thing_report["info"] = "This creates an exercise message.";
        $this->thing_report["help"] = "Try CHARLEY. Or NONSENSE.";

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "rocky"
        );
        $this->choices = $this->thing->choice->makeLinks("rocky");

        $this->thing_report["choices"] = $this->choices;
    }

    function makeSMS()
    {
        $sms = "ROCKY " . $this->inject . " " . $this->mode . "\n";

        $sms .= trim($this->short_message) . "\n";

        $sms .= "TEXT WEB";

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeACP125G()
    {
        $this->acp125g = new ACP125G($this->thing, "acp125g");
        $this->acp125g->makeACP125G($this->message);
    }

    function getResponse()
    {
        if (isset($this->response)) {
            return;
        }
    }

    function getMember()
    {
        if (isset($this->member)) {
            return;
        }

        // Load in the cast. And roles.
        $file = $this->resource_path . "/vector/members.txt";
        $contents = file_get_contents($file);
        $handle = fopen($file, "r");

        $count = 0;

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $arr = explode(",", $line);
                $member_call_sign = trim($arr[0]);
                $member_sms = trim($arr[1]);

                if ($this->from == $member_sms) {
                    $this->member["call_sign"] = $member_call_sign;
                    $this->member["sms"] = $member_sms;
                    return;
                }
            }
        }

        if (!isset($this->member["call_sign"])) {
            $this->member["call_sign"] = "ROCKY";
            $this->member["sms"] = "(XXX) XXX-XXXX";
        }
    }

    function getMessages()
    {
        if (isset($this->messages)) {
            return;
        }

        // Load in the name of the message bank.
        $this->getBank();

        // Latest transcribed sets.
        $filename = "/vector/messages-" . $this->bank . ".txt";

        $this->filename = $this->bank . ".txt";

        $file = $this->resource_path . $filename;
        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        $count = 0;

        $bank_info = null;

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);

                $count += 1;

                if ($line == "---") {
                    $line_count = count($message) - 1;

                    //  if ($line_count == 10) {
                    // recognize as J-format
                    if ($bank_info == null) {
                        $title = trim(explode(":", $message[1])[1]);
                        $this->title = $title;
                        $author = trim(explode(":", $message[2])[1]);
                        $this->author = $author;

                        $date = trim(explode(":", $message[3])[1]);
                        $this->date = $date;

                        $version = trim(explode(":", $message[4])[1]);
                        $this->version = $version;

                        $count = 0;
                        $message = null;

                        $bank_info = [
                            "title" => $this->title,
                            "author" => $this->author,
                            "date" => $this->date,
                            "version" => $this->version,
                        ];
                        continue;
                    }

                    if ($line_count == 10) {
                        // recognize as J-format

                        $meta = $message[1];

                        $name_to = $message[2];
                        $position_to = $message[3];
                        $organization_to = $message[4];
                        $number_to = $message[5];

                        $text = $message[6];

                        $name_from = $message[7];
                        $position_from = $message[8];
                        $organization_from = $message[9];
                        $number_from = $message[10];

                        $message_array = [
                            "meta" => $meta,
                            "name_to" => $name_to,
                            "position_to" => $position_to,
                            "organization_to" => $organization_to,
                            "number_to" => $number_to,
                            "text" => $text,
                            "name_from" => $name_from,
                            "position_from" => $position_from,
                            "organization_from" => $organization_from,
                            "number_from" => $number_from,
                        ];

                        $this->messages[] = $message_array;
                    }

                    if ($line_count == 16) {
                        $line = [];
                        $message_array = [
                            "line_1" => $message[1],
                            "line_2" => $message[2],
                            "line_3" => $message[3],
                            "line_4" => $message[4],
                            "line_5" => $message[5],
                            "line_6" => $message[6],
                            "line_7" => $message[7],
                            "line_8" => $message[8],
                            "line_9" => $message[9],
                            "line_10" => $message[10],
                            "line_11" => $message[11],
                            "line_12" => $message[12],
                            "line_13" => $message[13],
                            "line_14" => $message[14],
                            "line_15" => $message[15],
                            "line_16" => $message[16],
                        ];

                        $this->messages[] = $message_array;
                    }
                    $count = 0;
                    $message = null;
                }

                $message[] = $line;
            }

            fclose($handle);
        } else {
            // error opening the file.
        }
    }

    function getInject()
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

    function librexRocky()
    {
        $start_time = time();
        while (true) {
            $word = $this->randomWord(6);
            $this->thing->wikipedia_handler = new Wikipedia(
                $this->thing,
                "wikipedia"
            );
            $this->thing->wikipedia_handler->apiWikipedia($word);
            $raw_text = $this->thing->wikipedia_handler->text;
            if ($this->hasOffensive($raw_text) === false) {
                break;
            }
            if (time() - $start_time > 2) {
                $raw_text =
                    "There was a problem. Something was found. But it was not appropriate. Try again.";
                break;
            }
        }
        // TODO refactor
        // TODO Build null/random addressing routines.
        // TODO Persist librex generated message and addresses.
        $text = $raw_text;

        $parts = explode(".", $raw_text);
        $text = trim($parts[0] . ".");

        $text = $this->filterAlphanumeric($text);
        //    $this->extractAlphanumeric();

        $this->thing->punctuation_handler = new Punctuation(
            $this->thing,
            "punctuation"
        );
        $text = $this->thing->punctuation_handler->stripPunctuation($text, " ");
        $text = preg_replace("/\s+/", " ", $text);
        $text = strtoupper($text);

        // Generate addressing.

        $this->thing->charley_handler = new Charley($this->thing, "charley");
        $this->thing->charley_handler->getCast();
        $this->cast = $this->thing->charley_handler->cast;

        $to = $this->cast[array_rand($this->cast)];

        $person_to = $to["name"];
        $position_to = $to["role"];

        $from = $this->cast[array_rand($this->cast)];
        $person_from = $from["name"];
        $position_from = $from["role"];

        //["name" => $name, "role" => $role];
        /*
$this->name_list = $this->thing->charley_handler->name_list;
$person_to = $this->name_list[array_rand($this->name_list)];
$person_from = $this->name_list[array_rand($this->name_list)];
*/

        // TODO Refactor as seperate function.

        $meta = null;
        $name_to = $person_to;
        $position_to = null;
        $organization_to = null;
        $number_to = null;
        //$text = $m;
        $name_from = $person_from;
        $position_from = null;
        $organization_from = null;
        $number_from = null;

        // Do this for now.
        $message_array = [
            "number" => null,
            "precedence" => null,
            "hx" => null, // Not used?
            "station_origin" => null,
            "check" => null,
            "place_filed" => null,
            "time_filed" => null,
            "date_filed" => "X",
            "meta" => $meta,
            "name_to" => $name_to,
            "position_to" => $position_to,
            "organization_to" => $organization_to,
            "number_to" => $number_to,
            "text" => $text,
            "name_from" => $name_from,
            "position_from" => $position_from,
            "organization_from" => $organization_from,
            "number_from" => $number_from,
        ];
        $this->num_words = 25;
        $this->filename = null;
        $this->title = "random librex wikipedia";
        $this->author = "none";
        $this->date = null;
        $this->version = null;
        $this->text = $text;
        $unit = "X";
        $this->short_message =
            "TO " .
            $name_to .
            ", " .
            $position_to .
            " [" .
            $organization_to .
            "]" .
            "\nFROM " .
            $name_from .
            ", " .
            $position_from .
            " [" .
            $organization_from .
            "]" .
            "\n" .
            "" .
            $text .
            "\n" .
            $number_from .
            " " .
            $unit .
            "";

        // Write message to file for recall.
        $acp125g_lines = $this->linesACP125G($message_array);
        $message = implode("\n", $acp125g_lines);
        $message .= "\n---\n";
        $this->saveACP125G(
            $message,
            "rocky/messages",
            "rocky-librex-wikipedia-test"
        );

        $this->message = $message_array;
        $this->messages[0] = $message_array;
    }

    function getMessage()
    {
        $this->getInject();

        $this->getMessages();

        $this->message = $this->messages[$this->num];

        // If meta is not set,
        // Then probably 16ln format needing parsing.
        if (!isset($this->message["meta"])) {
            $this->message = $this->parseACP125G($this->message);
        }

        $this->meta = trim($this->message["meta"], "//");

        $this->name_to = $this->message["name_to"];
        $this->position_to = $this->message["position_to"];
        $this->organization_to = $this->message["organization_to"];
        $this->number_to = trim($this->message["number_to"], "//");

        $this->text = trim($this->message["text"], "//");

        $this->words = explode(" ", $this->text);
        $this->num_words = count($this->words);

        $this->name_from = $this->message["name_from"];
        $this->position_from = $this->message["position_from"];
        $this->organization_from = $this->message["organization_from"];
        $this->number_from = $this->message["number_from"];

        $name_to = ucwords($this->name_to);
        $position_to = ucwords($this->position_to);
        $organization_to = strtoupper($this->organization_to);

        $name_from = ucwords($this->name_from);
        $position_from = ucwords($this->position_from);
        $organization_from = strtoupper($this->organization_from);

        $this->short_message =
            "TO " .
            $name_to .
            ", " .
            $position_to .
            " [" .
            $organization_to .
            "]" .
            "\nFROM " .
            $name_from .
            ", " .
            $position_from .
            " [" .
            $organization_from .
            "]" .
            "\n" .
            "" .
            $this->text .
            "\n" .
            $this->number .
            " " .
            $this->unit .
            "";

        if (
            $this->position_to == "X" and
            $this->position_from == "X" and
            $this->text == "X"
        ) {
            //$this->response = $this->number . " " . $this->unit . ".";
            $this->response = "No message to pass. ";
        }

        if (
            $this->position_to == "X" and
            $this->position_from == "X" and
            $this->text != "X"
        ) {
            //$this->response = $this->text . "\n" . $this->number. " " . $this->unit . ".";
            $this->response = "Unaddressed message.";
        }

        if (
            $this->position_to == "X" and
            $this->position_from != "X" and
            $this->text != "X"
        ) {
            $this->response =
                "TO < ? >" .
                " FROM " .
                $this->fictional_from .
                ", " .
                $this->role_from .
                " / " .
                $this->text .
                "\n" .
                $this->number .
                " " .
                $this->unit .
                ".";
        }

        $arr = explode("/", $this->meta);
        $this->message["number"] = $arr[0];
        $this->message["precedence"] = $arr[1];
        $this->message["hx"] = null; // Not used?
        $this->message["station_origin"] = $arr[2];
        $this->message["check"] = $arr[3];
        $this->message["place_filed"] = $arr[4];
        $this->message["time_filed"] = $arr[5];

        $this->message["date_filed"] = "X";
        if (isset($arr[6])) {
            $this->message["date_filed"] = $arr[6];
        }
    }

    function makeMessage()
    {
        $message = $this->short_message . "<br>";
        $uuid = $this->uuid;
        $message .= "<p>" . $this->web_prefix . "thing/$uuid/rocky\n \n\n<br> ";
        $this->thing_report["message"] = $message;
    }

    function getBar()
    {
        $this->bar = new Bar($this->thing, "display");
    }

    function setRocky()
    {
    }

    function getRocky()
    {
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/rocky";

        if (!isset($this->html_image)) {
            $this->makePNG();
        }

        $web = "<b>Rocky Agent</b>";
        $web .= "<p>";

        $web .= "<p>";

        $web .= "<p>";

        if (
            isset($this->name_to) and
            isset($this->position_to) and
            isset($this->name_from) and
            isset($this->position_from)
        ) {
            $web .= "<b>TO (NAME)</b> " . $this->name_to . "<br>";
            $web .= "<b>TO (ROLE)</b> " . $this->position_to . "<br>";
            $web .= "<b>FROM (NAME)</b> " . $this->name_from . "<br>";
            $web .= "<b>FROM (ROLE)</b> " . $this->position_from . "<br>";
        }

        $web .= "<p>";
        if (isset($this->text)) {
            $web .= "" . $this->text;
        }

        $web .= "<p>";

        if ($this->mode == "origin") {
            $web .= "<b>" . "ORIGINATE THIS MESSAGE</b><br>";
        }

        if ($this->mode == "relay") {
            $web .= "<b>" . "RELAY THIS MESSAGE</b><br>";
        }

        $web .= "<p>";

        //        //$received_at = strtotime($this->thing->thing->created_at);
        //        $ago = $this->thing->human_time ( time() - $this->refreshed_at );
        //        $web .= "This inject was created about ". $ago . " ago. ";

        //        $link = $this->web_prefix . "privacy";
        //        $privacy_link = '<a href="' . $link . '">'. $link . "</a>";

        $web .= "ACP 125(G) inject - ";
        //        $web .= "<p>";
        $this->makeACP125G($this->message);
        //        $web .= nl2br($this->acp125g->thing_report['acp125g']);

        $link = $this->web_prefix . "thing/" . $this->uuid . "/rocky.txt";
        $web .= '<a href="' . $link . '">' . $link . "</a>";
        $web .= "<br>";

        $web .= "<p>";
        $web .= "PDF inject - ";
        //      $web .= "<p>";

        if ($this->num_words > 25) {
            $web .= "No PERCS pdf available. Message > 25 words.<br><p>";
        } else {
            $link = $this->web_prefix . "thing/" . $this->uuid . "/rocky.pdf";
            $web .= '<a href="' . $link . '">' . $link . "</a>";
            $web .= "<br>";
            $web .= "<p>";
        }

        $web .= "Inject Bank - ";
        //        $web .= "<p>";
        $web .= $this->filename . " - ";
        $web .= $this->title . " - ";
        $web .= $this->author . " - ";
        $web .= $this->date . " - ";
        $web .= $this->version . "";

        $web .= "<p>";
        $web .= "Inject Metadata - ";

        $web .= $this->inject . " - " . $this->thing->nuuid;
        if (isset($this->thing->thing->created_at)) {
            $web .= " - " . $this->thing->thing->created_at;

            $togo = $this->thing->human_time($this->time_remaining);
            $web .= " - " . $togo . " remaining.<br>";
        }
        $web .= "<br>";

        $link = $this->web_prefix . "privacy";
        $privacy_link = '<a href="' . $link . '">' . $link . "</a>";

        if (isset($this->thing->thing->created_at)) {
            $ago = $this->thing->human_time(
                time() - strtotime($this->thing->thing->created_at)
            );
            $web .= "Inject was created about " . $ago . " ago. ";
        }

        $web .=
            "This proof-of-concept inject is hosted by the " .
            ucwords($this->word) .
            " service.  Read the privacy policy at " .
            $privacy_link .
            ".";

        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    function makeTXT()
    {
        $txt = "Traffic for ROCKY.\n";

        if ($this->mode == "relay") {
            $txt .= "Relay this message.\n";
            $txt .= "Duplicate messages may exist. Can you de-duplicate?";
        }

        if ($this->mode == "origin") {
            $txt .= "Originate this message.\n";
        }

        $txt .= "\n";

        $txt .= $this->acp125g->thing_report["acp125g"];

        $txt .= "\n";

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    public function makePNG()
    {
        $this->image = imagecreatetruecolor(164, 164);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);
        $this->blue = imagecolorallocate($this->image, 0, 68, 255);

        $this->flag_yellow = imagecolorallocate($this->image, 255, 239, 0);

        imagefilledrectangle($this->image, 0, 0, 164, 164, $this->white);
        $textcolor = imagecolorallocate($this->image, 0, 0, 0);

        // $this->drawRocky(164/2,164/2);

        // Write the string at the top left
        $border = 30;
        $radius = (1.165 * (164 - 2 * $border)) / 3;

        // devstack add path
        $font = $this->default_font;
        if (file_exists($font)) {
            $text = "EXERCISE EXERCISE EXERCISE WELFARE TEST ROCKY 5";
            $text = "ROCKY";
            $text = $this->message["text"];

            if (!isset($this->bar)) {
                $this->getBar();
            }

            $bar_count = $this->bar->bar_count;

            // Add some shadow to the text

            imagettftext(
                $this->image,
                40,
                0,
                0 - $this->bar->bar_count * 5,
                75,
                $this->grey,
                $font,
                $text
            );

            $size = 72;
            $angle = 0;
            $bbox = imagettfbbox($size, $angle, $font, $text);
            $bbox["left"] = 0 - min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["top"] = 0 - min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            $bbox["width"] =
                max($bbox[0], $bbox[2], $bbox[4], $bbox[6]) -
                min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["height"] =
                max($bbox[1], $bbox[3], $bbox[5], $bbox[7]) -
                min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            extract($bbox, EXTR_PREFIX_ALL, "bb");
            //check width of the image
            $width = imagesx($this->image);
            $height = imagesy($this->image);
            $pad = 0;

            imagettftext(
                $this->image,
                $size,
                $angle,
                $width / 2 - $bb_width / 2,
                $height / 2 + $bb_height / 2,
                $textcolor,
                $font,
                $this->message["station_origin"]
            );

            $size = 10;

            imagettftext(
                $this->image,
                $size,
                $angle,
                $width / 2 - $bb_width / 2,
                $height / 2 + ($bb_height * 4) / 5,
                $textcolor,
                $font,
                $this->message["station_origin"]
            );
        }
        // Small nuuid text for back-checking.
        imagestring($this->image, 2, 140, 0, $this->thing->nuuid, $textcolor);

        // https://stackoverflow.com/questions/14549110/failed-to-delete-buffer-no-buffer-to-delete
        if (ob_get_contents()) {
            ob_clean();
        }

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();

        ob_end_clean();

        $this->thing_report["png"] = $imagedata;

        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="snowflake"/>';

        $this->PNG_embed = "data:image/png;base64," . base64_encode($imagedata);

        $this->PNG = $imagedata;

        $this->html_image = $response;

        return $response;
    }

    function extractNuuid($input)
    {
        if (!isset($this->duplicables)) {
            $this->duplicables = [];
        }

        return $this->duplicables;
    }

    public function makePDF()
    {
        $file = $this->resource_path . "percs/PERCS_Message_Form_Ver1.4.pdf";

        if ($file === null) {
            $this->thing_report["pdf"] = false;
            return;
        }

        if ($this->num_words > 25) {
            return;
        }
        $txt = $this->thing_report["txt"];

        // initiate FPDI
        $pdf = new Fpdi\Fpdi();

        // http://www.percs.bc.ca/wp-content/uploads/2014/06/PERCS_Message_Form_Ver1.4.pdf
        $pdf->setSourceFile($file);
        $pdf->SetFont("Helvetica", "", 10);

        $tplidx1 = $pdf->importPage(1, "/MediaBox");

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s["orientation"], $s);
        // $pdf->useTemplate($tplidx1,0,0,215);
        $pdf->useTemplate($tplidx1);

        $pdf->SetTextColor(0, 0, 0);

        if (isset($this->thing->thing->created_at)) {
            $text =
                "Inject generated at " . $this->thing->thing->created_at . ".";
            $pdf->SetXY(130, 10);
            $pdf->Write(0, $text);
        }

        $this->getQuickresponse(
            $this->web_prefix . "thing\\" . $this->uuid . '\\rocky'
        );
        $pdf->Image($this->quick_response_png, 199, 2, 10, 10, "PNG");

        //$pdf->SetXY(15, 20);
        //$pdf->Write(0, $this->message['text']);

        if ($this->mode == "relay") {
            $pdf->SetXY(8, 50);
            $pdf->Write(0, $this->message["number"]);

            $pdf->SetXY(50, 40);
            $pdf->Write(0, $this->message["hx"]);

            $pdf->SetXY(80, 50);
            $pdf->Write(0, $this->message["station_origin"]);

            $pdf->SetXY(112, 50);
            $pdf->Write(0, $this->message["check"]);

            $pdf->SetXY(123, 50);
            $pdf->Write(0, $this->message["place_filed"]);

            $pdf->SetXY(166, 50);
            $pdf->Write(0, $this->message["time_filed"]);

            $pdf->SetXY(181, 50);
            $pdf->Write(0, $this->message["date_filed"]);
        }

        switch (strtolower($this->message["precedence"])) {
            case "r":
            case "routine":
                $pdf->SetXY(24, 52.5);
                $pdf->Write(0, "X");
                break;
            case "p":
            case "priority":
                $pdf->SetXY(24, 46);
                $pdf->Write(0, "X");
                break;
            case "w":
            case "welfare":
                $pdf->SetXY(24, 59);
                $pdf->Write(0, "X");
                break;
            case "e":
            case "emergency":
                $pdf->SetXY(24, 39);
                $pdf->Write(0, "X");
                break;
            default:
        }

        $pdf->SetXY(30, 76);
        $pdf->Write(0, strtoupper($this->message["name_to"]));

        $pdf->SetXY(30, 76 + 10);
        $pdf->Write(0, strtoupper($this->message["position_to"]));

        $pdf->SetXY(30, 76 + 21);
        $pdf->Write(0, strtoupper($this->message["organization_to"]));

        $pdf->SetXY(60 + 44, 168);
        $pdf->Write(0, strtoupper($this->message["name_from"]));

        $pdf->SetXY(60 + 44, 168 + 10);
        $pdf->Write(0, strtoupper($this->message["position_from"]));

        $pdf->SetXY(60 + 44, 168 + 21);
        $pdf->Write(0, strtoupper($this->message["organization_from"]));

        //$pdf->SetXY(30, 40);
        //$pdf->Write(0, $this->message['precedence']);
        /*
        $pdf->SetXY(50, 40);
        $pdf->Write(0, $this->message['hx']);

        $pdf->SetXY(80, 50);
        $pdf->Write(0, $this->message['station_origin']);

        $pdf->SetXY(112, 50);
        $pdf->Write(0, $this->message['check']);


        $pdf->SetXY(123, 50);
        $pdf->Write(0, $this->message['place_filed']);

        $pdf->SetXY(166, 50);
        $pdf->Write(0, $this->message['time_filed']);

        $pdf->SetXY(181, 50);
        $pdf->Write(0, $this->message['date_filed']);
*/
        $num_rows = 5;
        $num_columns = 5;
        $offset = 0;
        $page = 1;
        //$i = 1;

        $i = 0;
        $words = explode(" ", $this->text);

        $col_offset = 59;
        $row_offset = 122;
        $col_spacing = 38;
        $row_spacing = 9;

        $row = 0;
        foreach ($words as $index => $word) {
            $col = $index % 5;
            $pdf->SetXY(
                $col_offset + ($col - 1) * $col_spacing,
                $row_offset + $row * $row_spacing
            );
            $pdf->Write(0, $word);

            if ($col == 4) {
                $row += 1;
            }
        }
        $image = $pdf->Output("", "S");

        $this->thing_report["pdf"] = $image;

        return $this->thing_report["pdf"];
    }

    public function readSubject()
    {
        if (!$this->getMember()) {
            $this->response = "Generated an inject. ";
        }

        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "rocky") {
                $this->getMessage();

                if (!isset($this->index) or $this->index == null) {
                    $this->index = 1;
                }
                return;
            }
        }

        $keywords = [
            "librex",
            "hard",
            "16ln",
            "easy",
            "hey",
            "rocky",
            "charley",
            "bullwinkle",
            "natasha",
            "boris",
            "source",
            "origin",
            "relay",
        ];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "librex":
                            $this->librexRocky();
                            $this->response .=
                                "Picked a random word and got librex (wikipedia) text. ";
                            $this->score = strlen("rocky librex");
                            $this->setState("n/a");
                            $this->setBank("librex wikipedia");
                            return;

                        case "hard":
                        case "easy":
                        case "16ln":
                            $this->setState($piece);
                            $this->setBank($piece);

                            $this->getMessage();
                            $this->response .=
                                " Set messages to " .
                                strtoupper($this->state) .
                                ". ";

                            return;

                        case "origin":
                        case "source":
                            $this->response .= "Set mode to origin. ";
                            $this->setMode("origin");
                            $this->getMessage();
                            return;
                        case "relay":
                            $this->response .= "Set mode to relay. ";
                            $this->setMode("relay");
                            $this->getMessage();
                            return;

                        case "hey":
                            $this->getMember();
                            $this->response =
                                "Hey " .
                                strtoupper($this->member["call_sign"]) .
                                ". ";

                            return;
                        case "on":
                        default:
                    }
                }
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
