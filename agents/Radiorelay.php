<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Radiorelay extends Agent
{
    public $var = 'hello';

    public function init()
    {
        // Need to add in mode changing - origin / relay

        //        $this->node_list = array("rocky"=>array("rocky", "charley", "nonsense"));
        $this->node_list = ["radiorelay" => ["radiorelay", "nonsense"]];

        $this->number = null;
        $this->unit = "";

        $this->default_state = "easy";
        $this->default_mode = "relay";

        $this->setMode($this->default_mode);

        //        $this->getNuuid();

        $this->character = new Character(
            $this->thing,
            "character is Rocket J. Squirrel"
        );

        $this->qr_code_state = "off";

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->radiorelay = new Variables(
            $this->thing,
            "variables radiorelay " . $this->from
        );

        //var_dump($this->thing);
        //exit();
        //        $this->getMemcached();
    }

    function isRadiorelay($state = null)
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
            ["radiorelay", "inject"],
            $this->inject
        );

        $this->thing->json->writeVariable(
            ['radiorelay', 'callsign'],
            $this->callsign
        );

        $this->refreshed_at = $this->current_time;

        $this->radiorelay->setVariable("state", $this->state);
        $this->radiorelay->setVariable("mode", $this->mode);

        $this->radiorelay->setVariable("refreshed_at", $this->current_time);

        $this->thing->log(
            $this->agent_prefix . 'set Radio Relay to ' . $this->state,
            "INFORMATION"
        );
    }

    function get()
    {
        $this->previous_state = $this->radiorelay->getVariable("state");
        $this->previous_mode = $this->radiorelay->getVariable("mode");
        $this->refreshed_at = $this->radiorelay->getVariable("refreshed_at");

        $this->thing->log(
            $this->agent_prefix . 'got from db ' . $this->previous_state,
            "INFORMATION"
        );

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isRadiorelay($this->previous_state)) {
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
            "radiorelay",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["radiorelay", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->inject = $this->thing->json->readVariable([
            "radiorelay",
            "inject",
        ]);

        $callsign = $this->thing->json->readVariable([
            "radiorelay",
            "callsign",
        ]);

        if ($callsign != false) {
            $this->callsign = $callsign;
        }
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
        $agent = new Qr($this->thing, $text);
        $this->quick_response_png = $agent->PNG_embed;
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

    public function respond()
    {
        $this->getResponse();

        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "radiorelay";
        $this->makeACP125G();

        $this->makePNG();

        $this->makeSMS();

        $this->makeMessage();
        // $this->makeTXT();
        $this->makeChoices();

        $this->thing_report["info"] =
            "This creates a question in a radiogram format.";
        $this->thing_report["help"] =
            'Try RADIORELAY then WEB. Or PDF. Or Text.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
        $this->makeWeb();

        $this->makeTXT();
        $this->makePDF();
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "radiorelay"
        );
        $this->choices = $this->thing->choice->makeLinks('radiorelay');

        $this->thing_report['choices'] = $this->choices;
    }

    function makeSMS()
    {
        $sms = "RADIO RELAY " . $this->inject . " " . $this->mode . "\n";
        //        $sms .= $this->response;

        $sms .= trim($this->short_message) . "\n";

        $sms .= "TEXT WEB";
        // $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeACP125G()
    {
        $this->acp125g = new ACP125G($this->thing, "acp125g");
        $this->acp125g->makeACP125G($this->message);
    }

    public function getCast()
    {
        //$callsign_agent = new Callsign($this->thing, "callsign");

        //$this->getCallsigns();
        /*
//var_dump($this->callsigns_heard);
foreach ($this->callsigns_heard as $i=>$callsign_heard) {
    var_dump($callsign_heard);
}
*/
        // Unique name <> Role mappings. Check?
        /*
                $this->name_list[$role] = $name;
                $this->role_list[$name] = $role;

                $this->cast[] = array("name"=>$name, "role"=>$role); 

exit();

/*
                // Unique name <> Role mappings. Check?
                $this->name_list[$role] = $name;
                $this->role_list[$name] = $role;

                $this->cast[] = array("name"=>$name, "role"=>$role); 
*/
    }

    function getName($role = null)
    {
        if (!isset($this->name_list)) {
            $this->getCast();
        }

        if ($role == "X") {
            $this->name = "Rocky";
            return;
        }

        $this->name = array_rand(["Rocky", "Rocket J. Squirrel"]);

        $input = ["Rocky"];

        // Pick a random Charles.
        $this->name = $input[array_rand($input)];
        if (isset($this->name_list[$role])) {
            $this->name = $this->name_list[$role];
        }

        return $this->name;
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
        $file = $this->resource_path . '/vector/members.txt';
        $contents = file_get_contents($file);
        $handle = fopen($file, "r");

        $count = 0;

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $arr = explode(",", $line);
                $member_call_sign = trim($arr[0]);
                $member_sms = trim($arr[1]);

                if ($this->from == $member_sms) {
                    $this->member['call_sign'] = $member_call_sign;
                    $this->member['sms'] = $member_sms;
                    return;
                }
            }
        }

        if (!isset($this->member['call_sign'])) {
            $this->member['call_sign'] = "ROCKY";
            $this->member['sms'] = "(XXX) XXX-XXXX";
        }
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

                $meta = "X";

                $name_to = "X";
                $position_to = "X";
                $organization_to = "X";
                $number_to = "X";

                $text = $line;

                $name_from = "X";
                $position_from = "X";
                $organization_from = "X";
                $number_from = "X";

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
                //              }

                /*
        if ($line_count == 16) {

                $line = array();
                $message_array = array("line_1"=>$message[1],
                    "line_2"=>$message[2],
                    "line_3"=>$message[3],
                    "line_4"=>$message[4],
                    "line_5"=>$message[5],
                    "line_6"=>$message[6],
                    "line_7"=>$message[7],
                    "line_8"=>$message[8],
                    "line_9"=>$message[9],
                    "line_10"=>$message[10],
                    "line_11"=>$message[11],
                    "line_12"=>$message[12],
                    "line_13"=>$message[13],
                    "line_14"=>$message[14],
                    "line_15"=>$message[15],
                    "line_16"=>$message[16]
                );

                $this->messages[] = $message_array;


        }
  
              $count = 0;
                $message = null;
            }


            $message[] = $line;
*/
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
        /*
        $callsign_agent = new Callsign($this->thing, "callsign");
        $callsign_agent->getCallsigns();
        $callsign_agent->netCallsign();

        var_dump($callsign_agent->callsigns_heard);
        $this->callsigns_heard = $callsign_agent;
*/
        $this->getCallsign();
        //$status = true;
        //if ($this->inject == null) {$status = false;}
        //if ($this->inject == false) {$status = false;}

        $is_empty_inject = true;

        if ($this->inject === false) {
            $is_empty_inject = false;
        }

        while (true) {
            $this->getInject();

            $message = $this->messages[$this->num];

            $radiogram_agent = new Radiogram($this->thing, "radiogram");
            $text = $radiogram_agent->translateRadiogram($message['text']);

            $word_count = count(explode(" ", $text));

            if ($is_empty_inject === true) {
                break;
            }

            if ($word_count >= 25) {
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

        $this->meta = trim($this->message['meta'], "//");

        $this->message['name_to'] = $this->callsign;
        $this->message['position_to'] = "";
        $this->message['organization_to'] = "";

        $this->message['text'] = $text;

        $this->message['name_from'] = "";
        $this->message['position_from'] = "";
        $this->message['organization_from'] = "";

        $this->name_to = $this->message['name_to'];
        $this->position_to = $this->message['position_to'];
        $this->organization_to = $this->message['organization_to'];
        $this->number_to = trim($this->message['number_to'], "//");

        $this->text = trim($this->message['text'], "//");

        $this->words = explode(" ", $this->text);
        $this->num_words = count($this->words);

        $this->name_from = $this->message['name_from'];
        $this->position_from = $this->message['position_from'];
        $this->organization_from = $this->message['organization_from'];
        $this->number_from = $this->message['number_from'];

        /*
        $this->short_message = $this->meta . "\n" .
             $this->name_to . ", " . $this->position_to . ", " .
             $this->organization_to.", " . $this->number_to. ". " . "\n" .
             $this->text ." " . "\n" .
             $this->name_from . ", " .
             $this->position_from . ", " . $this->organization_from . ", " .
             $this->number_from. ".";
*/

        $name_to = ucwords($this->name_to);
        $position_to = ucwords($this->position_to);
        $organization_to = strtoupper($this->organization_to);

        $name_from = ucwords($this->name_from);
        $position_from = ucwords($this->position_from);
        $organization_from = strtoupper($this->organization_from);
        /*
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
*/
        $this->short_message =
            "TO " .
            $name_to .
            "\nFROM " .
            $name_from .
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
            $this->response = "No message to pass.";
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
        $this->message['number'] = $arr[0];

        $this->message['number'] = "";

        $this->message['precedence'] = $arr[1];
        $this->message['hx'] = null; // Not used?
        $this->message['station_origin'] = $arr[2];
        $this->message['check'] = $arr[3];
        $this->message['place_filed'] = $arr[4];
        $this->message['time_filed'] = $arr[5];
        $this->message['date_filed'] = $arr[6];
    }

    function makeMessage()
    {
        $message = $this->short_message . "<br>";
        $uuid = $this->uuid;
        $message .=
            "<p>" . $this->web_prefix . "thing/$uuid/radiorelay\n \n\n<br> ";
        $this->thing_report['message'] = $message;
    }

    function getBar()
    {
        $this->bar = new Bar($this->thing, "display");
    }

    function setRadiorelay()
    {
    }

    function getRadiorelay()
    {
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/radiorelay';

        //$this->node_list = array("rocky"=>array("rocky%20%hard%20%relay", "rocky hard origin", "rocky easy origin", "rocky easy relay" ,"bullwinkle","charley"));
        // Make buttons
        //$this->thing->choice->Create($this->agent_name, $this->node_list, "rocky");
        //$choices = $this->thing->choice->makeLinks('rocky');

        if (!isset($this->html_image)) {
            $this->makePNG();
        }

        $web = "<b>Radio Relay Agent</b>";
        $web .= "<p>";

        $web .= "<p>";

        $web .= "<p>";

        if (!isset($this->callsigns_heard)) {
            $this->getCallsigns();
        }

        $last_refreshed_at =
            $this->callsigns_heard[$this->callsign]['refreshed_at'];
        $current_time = $this->thing->time();

        $seconds_ago = strtotime($current_time) - strtotime($last_refreshed_at);
        $ago = $this->thing->human_time($seconds_ago);
        if ($seconds_ago > 60 * 60 * 24) {
            $last_seen_text = "";
        } else {
            $last_seen_text = "[last seen about " . $ago . " ago]";
        }

        if (
            isset($this->name_to) and
            isset($this->position_to) and
            isset($this->name_from) and
            isset($this->position_from)
        ) {
            $web .=
                "<b>TO (STATION CALLSIGN)</b> " .
                $this->name_to .
                " " .
                $last_seen_text .
                "<br>";
            //            $web .= "<b>TO (ROLE)</b> " . $this->position_to . "<br>";
            $web .=
                "<b>FROM (STATION CALLSIGN)</b> " . $this->name_from . "<br>";
            //            $web .= "<b>FROM (ROLE)</b> " . $this->position_from . "<br>";
        }

        $web .= "<p>";
        if (isset($this->text)) {
            $web .= "" . $this->text;
        }

        $web .= "<p>";

        if ($this->mode == "origin") {
            $web .= "<b>" . "ORIGINATE THIS RADIOGRAM</b><br>";
        }

        if ($this->mode == "relay") {
            $web .= "<b>" . "RELAY THIS RADIOGRAM</b><br>";
        }

        $web .= "<p>";

        //        //$received_at = strtotime($this->thing->thing->created_at);
        //        $ago = $this->thing->human_time ( time() - $this->refreshed_at );
        //        $web .= "This inject was created about ". $ago . " ago. ";

        //        $link = $this->web_prefix . "privacy";
        //        $privacy_link = '<a href="' . $link . '">'. $link . "</a>";

        $web .= "ACP 125(G) format message - ";
        //        $web .= "<p>";

        $this->makeACP125G($this->message);
        //        $web .= nl2br($this->acp125g->thing_report['acp125g']);

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/radiorelay.txt';
        $web .= '<a href="' . $link . '">' . $link . "</a>";
        $web .= "<br>";

        $web .= "<p>";
        $web .= "PERCS format radiogram - ";

        if ($this->num_words > 25) {
            $web .= "No PERCS pdf available. Message > 25 words.<br><p>";
        } else {
            $link =
                $this->web_prefix . 'thing/' . $this->uuid . '/radiorelay.pdf';
            $web .= '<a href="' . $link . '">' . $link . "</a>";
            $web .= "<br>";
            $web .= "<p>";
        }

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
            time() - strtotime($this->thing->thing->created_at)
        );
        $web .= "Inject was created about " . $ago . " ago. ";

        $web .=
            "This proof-of-concept inject is hosted by the " .
            ucwords($this->word) .
            " service.  Read the privacy policy at " .
            $privacy_link .
            ".";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    public function getCallsigns()
    {
        $callsign_agent = new Callsign($this->thing, "callsign");
        $callsign_agent->getCallsigns();
        $callsign_agent->netCallsign();

        $this->callsigns_heard = $callsign_agent->callsigns_heard;
    }

    public function getCallsign()
    {
        if (isset($this->callsign)) {
            return;
        }

        $this->getCallsigns();

        $this->current_time = $this->thing->json->time();
        $call_horizon = 30; // minutes

        $callsigns_available = [];
        foreach ($this->callsigns_heard as $i => $callsign_heard) {
            //var_dump($callsign_heard);
            if (
                $callsign_heard['refreshed_at'] == null or
                $callsign_heard['refreshed_at'] == "X"
            ) {
                continue;
            }
            if ($callsign_heard['action'] == 'checkout') {
                continue;
            }

            $age =
                strtotime($this->current_time) -
                strtotime($callsign_heard['refreshed_at']);
            if ($age > $call_horizon * 60) {
                continue;
            }
            $callsigns_available[$i] = $callsign_heard;
        }
        $this->callsigns_available = $callsigns_available;
        if (count($this->callsigns_available) == 0) {
            $this->callsign = "";
            return;
        }
        $k = array_rand($this->callsigns_available);
        $this->callsign = $k;
    }

    function makeTXT()
    {
        $txt = "Traffic for RADIO RELAY.\n";

        if ($this->mode == "relay") {
            $txt .= "Relay this message.\n";
            $txt .= 'Duplicate messages may exist. Can you de-duplicate?';
        }

        if ($this->mode == "origin") {
            $txt .= "Originate this message.\n";
        }

        $txt .= "\n";

        $txt .= $this->acp125g->thing_report['acp125g'];

        $txt .= "\n";

        $this->thing_report['txt'] = $txt;
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
        //$font = $this->resource_path . '/var/www/html/stackr.test/resources/roll/KeepCalm-Medium.ttf';
        $font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';
        $text = "EXERCISE EXERCISE EXERCISE WELFARE TEST RADIO RELAY";
        $text = "RADIO RELAY";
        $text = $this->message['text'];

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
        extract($bbox, EXTR_PREFIX_ALL, 'bb');
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
            $this->message['station_origin']
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
            $this->message['station_origin']
        );

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

        $this->thing_report['png'] = $imagedata;

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
        if ($this->num_words > 25) {
            return;
        }
        $txt = $this->thing_report['txt'];

        // initiate FPDI
        $pdf = new Fpdi\Fpdi();

        // http://www.percs.bc.ca/wp-content/uploads/2014/06/PERCS_Message_Form_Ver1.4.pdf
        $pdf->setSourceFile(
            $this->resource_path . 'percs/PERCS_Message_Form_Ver1.4.pdf'
        );
        $pdf->SetFont('Helvetica', '', 10);

        $tplidx1 = $pdf->importPage(1, '/MediaBox');

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s['orientation'], $s);
        // $pdf->useTemplate($tplidx1,0,0,215);
        $pdf->useTemplate($tplidx1);

        $pdf->SetTextColor(0, 0, 0);

        if ($this->qr_code_state == "on") {
            $text =
                "Inject generated at " . $this->thing->thing->created_at . ".";
            $pdf->SetXY(130, 10);
            $pdf->Write(0, $text);

            $this->getQuickresponse(
                $this->web_prefix . 'thing\\' . $this->uuid . '\\radiorelay'
            );
            $pdf->Image($this->quick_response_png, 199, 2, 10, 10, 'PNG');
        }

        //$pdf->SetXY(15, 20);
        //$pdf->Write(0, $this->message['text']);

        if ($this->mode == "relay") {
            $pdf->SetXY(8, 50);
            $pdf->Write(0, $this->message['number']);

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
        }

        switch (strtolower($this->message['precedence'])) {
            case 'r':
            case 'routine':
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
        $pdf->Write(0, strtoupper($this->message['name_to']));

        $pdf->SetXY(30, 76 + 10);
        $pdf->Write(0, strtoupper($this->message['position_to']));

        $pdf->SetXY(30, 76 + 21);
        $pdf->Write(0, strtoupper($this->message['organization_to']));

        $pdf->SetXY(60 + 44, 168);
        $pdf->Write(0, strtoupper($this->message['name_from']));

        $pdf->SetXY(60 + 44, 168 + 10);
        $pdf->Write(0, strtoupper($this->message['position_from']));

        $pdf->SetXY(60 + 44, 168 + 21);
        $pdf->Write(0, strtoupper($this->message['organization_from']));

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
        $image = $pdf->Output('', 'S');

        $this->thing_report['pdf'] = $image;

        return $this->thing_report['pdf'];
    }

    public function readSubject()
    {
        if (!$this->getMember()) {
            $this->response = "Generated an inject.";
        }

        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'radiorelay') {
                $this->getMessage();

                if (!isset($this->index) or $this->index == null) {
                    $this->index = 1;
                }
                return;
            }
        }
        $keywords = [
            "hey",
            "radio",
            "relay",
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
                        /*
                        case 'hard':
                        case 'easy':
                        case '16ln':
                            $this->setState($piece);
                            $this->setBank($piece);

                            $this->getMessage();
                            $this->response .=
                                " Set messages to " .
                                strtoupper($this->state) .
                                ".";

                            return;
*/
                        //        case 'origin':
                        //        case 'source':
                        //            $this->response .= " Set mode to origin.";
                        //            $this->setMode('origin');
                        //            $this->getMessage();
                        //            return;
                        //       case 'relay':
                        //           $this->response .= " Set mode to relay.";
                        //           $this->setMode('relay');
                        //           $this->getMessage();
                        //           return;

                        case 'hey':
                            $this->getMember();
                            $this->response =
                                "Hey " .
                                strtoupper($this->member['call_sign']) .
                                ".";

                            return;
                        /*
                        case 'info':
                            $this->response = $this->thing_report['info'];

                            return;
*/

                        case 'on':
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
