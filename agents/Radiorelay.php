<?php
namespace Nrwtaylor\StackAgentThing;

use setasign\Fpdi;

class Radiorelay extends Agent
{
    function init()
    {
        $this->node_list = ["radiorelay" => ["trivia", "privacy"]];

        $this->number = null;
        $this->unit = "";

        $this->filename = "not set";
        $this->title = "not provided";
        $this->author = "not provided";
        $this->date = "not avalable";
        $this->version = "none";

        $this->default_state = "easy";
        $this->default_mode = "relay";

        $this->setMode($this->default_mode);

        $this->qr_code_state = "off";

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->radiorelay = new Variables(
            $this->thing,
            "variables radiorelay " . $this->from
        );

        $url = $this->resource_path . 'radiorelay/radiorelay.php';
        $settings = require $url;

        $this->container = new \Slim\Container($settings);

        if (isset($this->container['radiorelay'])) {
            if (isset($this->container['radiorelay']['default_bank'])) {
                $this->default_bank =
                    $this->container['radiorelay']['default_bank'];
            }

            if (isset($this->container['radiorelay']['agents']['urls'])) {
                $this->urls = $this->container['radiorelay']['agents']['urls'];
            }

            if (isset($this->container['radiorelay']['agents']['pdfs'])) {
                $this->pdfs = $this->container['radiorelay']['agents']['pdfs'];
            }


            if (isset($this->container['radiorelay']['agents']['when'])) {
                $this->when = $this->container['radiorelay']['agents']['when'];
            }

            if (isset($this->container['radiorelay']['checkin'])) {
                $this->checkin_frequencies =
                    $this->container['radiorelay']['checkin'];
            }

            if (isset($this->container['radiorelay']['calling'])) {
                $this->calling_frequencies =
                    $this->container['radiorelay']['calling'];
            }

            if (isset($this->container['radiorelay']['transfer'])) {
                $this->transfer_frequencies =
                    $this->container['radiorelay']['transfer'];
            }

            if (isset($this->container['radiorelay']['agents'])) {
                $this->agent_responses =
                    $this->container['radiorelay']['agents'];

                $this->thing_report["info"] =
                    $this->container['radiorelay']['agents']['info'];
                $this->thing_report["help"] =
                    $this->container['radiorelay']['agents']['help'];
            }
        }
        $this->checkin_name = array_rand($this->checkin_frequencies);
        $this->checkin_frequency =
            $this->checkin_frequencies[$this->checkin_name];

        $this->callsign =
            '<' .
            'ASK ' .
            strtoupper($this->checkin_name) .
            " " .
            $this->checkin_frequency .
            '>';

        $this->calling_name = array_rand($this->calling_frequencies);
        $this->calling_frequency =
            $this->calling_frequencies[$this->calling_name];

        $this->transfer_name = array_rand($this->transfer_frequencies);
        $this->transfer_frequency =
            $this->transfer_frequencies[$this->transfer_name];
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
        $this->thing->Write(
            ["radiorelay", "inject"],
            $this->inject
        );

        $this->thing->Write(
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

        //   $this->thing->log(
        //       $this->agent_prefix . 'got from db ' . $this->previous_state,
        //       "INFORMATION"
        //   );

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

        $time_string = $this->thing->Read([
            "radiorelay",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["radiorelay", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->inject = $this->thing->Read([
            "radiorelay",
            "inject",
        ]);

        $callsign = $this->thing->Read([
            "radiorelay",
            "callsign",
        ]);

        if ($callsign != false) {
            $this->callsign = $callsign;
        }
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
            $this->bank = $this->default_bank;
        }
    }

    function getBank()
    {
        if (!isset($this->state) or $this->state == "easy") {
            $this->bank = $this->default_bank;
        }

        if (isset($this->inject) and $this->inject != false) {
            $arr = explode("-", $this->inject);
            $this->bank = $arr[0] . "-" . $arr[1];
        }
        return $this->bank;
    }

    public function respondResponse()
    {
        $this->makeACP125G();
        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
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
        // Signal this agent has not been accessed before.
        $outro = "TEXT WEB";

        if ($this->previous_state === false) {
            $link = $this->web_prefix . 'thing/' . $this->uuid . '/radiorelay';
            $outro = $link;
            if (isset($this->urls[0]['url'])) {
                $outro = $this->urls[0]['url'];
            }
        }
        $sms = "RADIO RELAY\n";

        if (isset($this->short_message)) {
            $sms .= trim($this->short_message) . "\n";
        }

        $sms .= $this->response;

        if (isset($this->short_message)) {
            $sms .=
                "FREQUENCIES/CHANNELS - COORDINATION " .
                $this->checkin_name .
                " " .
                $this->checkin_frequency .
                " / CALLING " .
                $this->calling_name .
                " " .
                $this->calling_frequency .
                " / TRANSFER " .
                $this->transfer_name .
                " " .
                $this->transfer_frequency;

            //$text = "TEXT WEB";
            //if (isset($this->urls[0]['url'])) {
            //$text = $this->urls[0]['url'];
            $sms .= " / " . $outro;
            //}
        }

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeACP125G()
    {
        if (!isset($this->message)) {
            return null;
        }

        $this->acp125g = new ACP125G($this->thing, "acp125g");
        $this->acp125g->makeACP125G($this->message);
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

        $this->filename = $this->bank . ".txt";

        $filename = "/radiorelay/". $this->default_bank .".txt";
        $file = $this->resource_path . $filename;

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

                $meta = "X";

                $name_to = "<ASK COORDINATION CHANNEL>";
                $position_to = "X";
                $organization_to = "X";
                $number_to = "X";

                $text = $line;

                $name_from = '<YOUR CALLSIGN>';
                $position_from = 'X';
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

        $this->getMessages();
        $this->getCallsign();

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

        if ($this->callsign == "") {
            $this->message['name_to'] = '<ASK COORDINATION CHANNEL>';
        } else {
            $this->message['name_to'] = $this->callsign;
        }
        $this->message['position_to'] = "";
        $this->message['organization_to'] = "";

        $this->message['text'] = $text;

        $this->message['name_from'] = '<YOUR CALLSIGN>';
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
            $this->response = "No message to pass.";
        }

        if (
            $this->position_to == "X" and
            $this->position_from == "X" and
            $this->text != "X"
        ) {
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

        $precedence = "";
        if (isset($arr[1])) {
            $precedence = $arr[1];
        }
        $this->message['precedence'] = $precedence;

        $this->message['hx'] = null; // Not used?

        $station_origin = "";
        if (isset($arr[2])) {
            $station_origin = $arr[2];
        }

        $this->message['station_origin'] = $station_origin;

        $check = "";
        if (isset($arr[3])) {
            $check = $arr[3];
        }

        $this->message['check'] = $check;

        $place_filed = "";
        if (isset($arr[4])) {
            $place_filed = $arr[4];
        }

        $this->message['place_filed'] = $place_filed;

        $time_filed = "";
        if (isset($arr[5])) {
            $time_filed = $arr[5];
        }

        $this->message['time_filed'] = $time_filed;

        $date_filed = "";
        if (isset($arr[6])) {
            $date_filed = $arr[6];
        }

        $this->message['date_filed'] = $date_filed;
    }

    function makeMessage()
    {
        $message = "";
        if (isset($this->short_message)) {
            $message .= $this->short_message . "<br>";
        }
        $uuid = $this->uuid;
        $message .=
            "<p>" . $this->web_prefix . "thing/$uuid/radiorelay\n \n\n<br> ";
        $this->thing_report['message'] = $message;
    }

    function getBar()
    {
        $this->bar = new Bar($this->thing, "display");
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/radiorelay';

        if (!isset($this->html_image)) {
            $this->makePNG();
        }

        $web = "<b>Radio Relay Agent</b>";
        $web .= "<p>";

        $web .= "<p>";

        $web .= "<b>TRIVIA MESSAGE</b><br>";
        $web .= "<p>";

        $web .= $this->response;

        if (!isset($this->callsigns_heard)) {
            $this->getCallsigns();
        }

        if (!isset($this->callsign)) {
            $this->thing_report['web'] = $web;
            return;
        }

        if (!isset($this->message)) {
            $this->thing_report['web'] = $web;
            return;
        }

        $last_seen_text = "";
        if (isset($this->callsigns_heard[$this->callsign]['refreshed_at'])) {
            $last_refreshed_at =
                $this->callsigns_heard[$this->callsign]['refreshed_at'];
            $current_time = $this->thing->time();

            $seconds_ago =
                strtotime($current_time) - strtotime($last_refreshed_at);
            $ago = $this->thing->human_time($seconds_ago);
            if ($seconds_ago > 60 * 60 * 24) {
                $last_seen_text = "";
            } else {
                $last_seen_text = "[last seen about " . $ago . " ago]";
            }
        }

        if (
            isset($this->name_to) and
            isset($this->position_to) and
            isset($this->name_from) and
            isset($this->position_from)
        ) {
            $web .=
                "<b>TO</b> " .
                htmlspecialchars($this->message['name_to']) .
                " " .
                $last_seen_text .
                "<br>";
            //            $web .= "<b>TO (ROLE)</b> " . $this->position_to . "<br>";
            $web .=
                "<b>FROM</b> " .
                htmlspecialchars($this->message['name_from']) .
                "<br>";
            //            $web .= "<b>FROM (ROLE)</b> " . $this->position_from . "<br>";
        }

        $web .= "<p>";
        if (isset($this->text)) {
            $web .= "" . $this->text;
        }

        $web .= "<p>";

        $web .= "<b>" . "HOST</b><br>";
        $web .= "<p>";

        if (isset($this->urls[0])) {
            $link = $this->urls[0]['url'];
            $title = $this->urls[0]['title'];
            $web .= " ";
            $web .= '<a href="' . $link . '">' . $title . "</a>";
            $web .= "<br>";
        }

        $web .= "<p>";
        $web .= "<b>" . "RULES AND GUIDELINES</b><br>";
        $web .= "<p>";

        if (isset($this->pdfs[0])) {
            $link = $this->pdfs[0]['url'];
            $title = $this->pdfs[0]['title'];
            $web .= " ";
            $web .= '<a href="' . $link . '">' . $title . " (pdf)</a>";
            $web .= "<br>";

            if (count($this->pdfs) > 1) {
                $web .= "<p>";

                foreach ($this->pdfs as $i => $pdf) {
                    if ($i == 0) {
                        continue;
                    }

                    $link = $pdf['url'];
                }
            }
        }
        $web .= "<p>";

        $web .= "<b>" . "WHEN</b><br>";
        $web .= "<p>";
        $web .= $this->when;
        $web .= "<p>";

        $web .= "<b>" . "RADIOGRAMS</b><br>";

/*
        $web .= "<p>";

        if (isset($this->message)) {
            $title = "ACP 125(G) format text";
            $this->makeACP125G($this->message);

            $link =
                $this->web_prefix . 'thing/' . $this->uuid . '/radiorelay.txt';
            $web .= '<a href="' . $link . '">' . $title . "</a>";
            $web .= "<br>";
        }
*/
        $web .= "<p>";
        $title = "PERCS machine-filled radiogram (pdf)";

        if (isset($this->num_words)) {
            if ($this->num_words > 25) {
                $web .= "No PERCS pdf available. Message > 25 words.<br><p>";
            } else {
                $link =
                    $this->web_prefix .
                    'thing/' .
                    $this->uuid .
                    '/radiorelay.pdf';
                $web .= '<a href="' . $link . '">' . $title . "</a>";
                $web .= "<br>";
                $web .= "<p>";
            }
        }

        $web .= "<b>" . "USEFUL LINKS</b><br>";
        $web .= "<p>";

                $useful_links = "";
                foreach ($this->urls as $i => $url) {
                    if ($i == 0) {
                        continue;
                    }

                    $title = $url['title'];
                    $link = $url['url'];
                    $useful_links .=
                        '<a href="' . $link . '">' . $title . "</a>";

                    $useful_links .= "<br>";
                }


        $web .= $useful_links;
        $web .= "<p>";
        $web .= "<b>" . "DOCUMENT META</b><br>";
        $web .= "<p>";

        $web .= "Message Bank - ";

        $web .= $this->filename . " - ";
        $web .= $this->title . " - ";
        $web .= $this->author . " - ";
        $web .= $this->date . " - ";
        $web .= $this->version . "";

        if (isset($this->message_meta)) {
            $web .= "<p>";
            $web .= "Message Metadata - ";

            $web .=
                $this->inject .
                " - " .
                $this->thing->nuuid .
                " - " .
                $this->thing->created_at;

            $togo = $this->thing->human_time($this->time_remaining);
            $web .= " - " . $togo . " remaining.<br>";
        }

        $web .= "<p>";

        $ago = $this->thing->human_time(
            time() - strtotime($this->thing->created_at)
        );
        $web .= "This radiogram was created about " . $ago . " ago. ";

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

        $this->current_time = $this->thing->time();
        $call_horizon = 30; // minutes

        $callsigns_available = [];
        foreach ($this->callsigns_heard as $i => $callsign_heard) {
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

        if (isset($this->acp125g->thing_report['acp125g'])) {
            $txt .= $this->acp125g->thing_report['acp125g'];

            $txt .= "\n";
        }
        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    public function makePDF()
    {
        $file = $this->resource_path . 'percs/PERCS_Message_Form_Ver1.4.pdf';
        if (($file === null) or (!file_exists($file))) {
            $this->thing_report['pdf'] = false;
            return $this->thing_report['pdf'];
        }

        if (!isset($this->num_words) or $this->num_words > 25) {
            return;
        }
        $txt = $this->thing_report['txt'];

        // initiate FPDI
        $pdf = new Fpdi\Fpdi();

        // http://www.percs.bc.ca/wp-content/uploads/2014/06/PERCS_Message_Form_Ver1.4.pdf
        $pdf->setSourceFile(
            $file
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
                "Inject generated at " . $this->thing->created_at . ".";
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
        $input = strtolower($this->subject);

        $ngram_agent = new Ngram($this->thing, "ngram");
        $pieces = [];
        $arr = $ngram_agent->getNgrams(strtolower($this->input), 3);
        $pieces = array_merge($pieces, $arr);
        $arr = $ngram_agent->getNgrams(strtolower($this->input), 2);
        $pieces = array_merge($pieces, $arr);
        $arr = $ngram_agent->getNgrams(strtolower($this->input), 1);
        $pieces = array_merge($pieces, $arr);

        $pieces = array_reverse($pieces);

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
            "when",
            "about",
            "how",
            "what",
            "where",
            "when",
            "why",
            "help",
            "info",
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
            "radio relay",
            "radiorelay",
        ];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "how":
                        case "why":
                        case "what":
                        case "who":
                        case "where":
                        case "about":
                        case "when":
                        case "info":
                        case "help":
                            $agent_name = $piece;
                            if ($piece == "about") {
                                $agent_name = 'whatis';
                            }

                            $this->response .=
                                $this->agent_responses[$agent_name];
                            return;
                        case "radiorelay":
                        case "radio relay":
                            $this->getMessage();
                            if (!isset($this->index) or $this->index == null) {
                                $this->index = 1;
                            }
                            return;

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
