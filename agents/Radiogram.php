<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Radiogram extends Agent
{
    public $var = 'hello';

    public function init()
    {
        // Need to add in mode changing - origin / relay

        $this->node_list = [
            "radiogram" => ["forget", "trivia", "nonsense", "weather"],
        ];

        $this->number = null;
        $this->unit = "";

        $this->default_state = "easy";
        $this->default_mode = "relay";

        $this->setMode($this->default_mode);

        $this->thing_report["info"] =
            "Creates PDF radiograms from a user provided text string.";
        $this->thing_report["help"] =
            'Try RADIOGRAM TO BOB FROM MARK MESSAGE HELLO. Text PDF. Or WEB.';

        $url = $this->resource_path . 'radiogram/radiogram.php';
        $settings = require $url;

        $this->container = new \Slim\Container($settings);

        if (isset($this->container['radiogram'])) {
            if (isset($this->container['radiogram']['agents']['urls'])) {
                $this->urls = $this->container['radiogram']['agents']['urls'];
            }

            if (isset($this->container['radiogram']['agents']['pdfs'])) {
                $this->pdfs = $this->container['radiogram']['agents']['pdfs'];
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

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->radiogram = new Variables(
            $this->thing,
            "variables radiogram " . $this->from
        );

        $this->short_message = "X";
    }

    function isRadiogram($state = null)
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
            ["radiogram", "inject"],
            $this->inject
        );

        $this->refreshed_at = $this->current_time;

        $this->radiogram->setVariable("state", $this->state);
        $this->radiogram->setVariable("mode", $this->mode);

        $this->radiogram->setVariable("refreshed_at", $this->current_time);

        $this->thing->log(
            $this->agent_prefix . 'set Radio Gram to ' . $this->state,
            "INFORMATION"
        );
    }

    function get()
    {
        $this->previous_state = $this->radiogram->getVariable("state");
        $this->previous_mode = $this->radiogram->getVariable("mode");
        $this->refreshed_at = $this->radiogram->getVariable("refreshed_at");

        $this->thing->log(
            $this->agent_prefix . 'got from db ' . $this->previous_state,
            "INFORMATION"
        );

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if (!$this->isRadiogram($this->previous_state)) {
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
            "radiogram",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["radiogram", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->inject = $this->thing->Read([
            "radiogram",
            "inject",
        ]);
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
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "radiogram"
        );
        $this->choices = $this->thing->choice->makeLinks('radiogram');

        $this->thing_report['choices'] = $this->choices;
    }

    function makeSMS()
    {
        $sms = "RADIOGRAM ";
        $sms .= " | ";
        $sms .=
            "to " .
            $this->message['name_to'] .
            " from " .
            $this->message['name_from'] .
            " message " .
            $this->message['text'];

        $sms .= " | ";

        $sms .= "TEXT WEB";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeTXT()
    {
        $txt = "RADIOGRAM.\n";

        $txt .= "\n";

        $acp125g_agent = new ACP125G($this->thing, "acp125g");
        $acp125g_agent->makeACP125G($this->message);

        if (isset($acp125g_agent->thing_report['acp125g'])) {
            $txt .= $acp125g_agent->thing_report['acp125g'];

            $txt .= "\n";
        }
        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    public function deperiodAcronym($text)
    {
        $tokens = explode(' ', $text);

        foreach ($tokens as $i => $token) {
            $dot_count = substr_count($token, ".");
            if ($dot_count > 1) {
                $tokens[$i] = str_replace('.', '', $token);
            }
        }

        $processed_text = trim(implode(' ', $tokens));
        return $processed_text;
    }

    public function translateRadiogram($text)
    {
        //$text = $message['text'];

        $text = $this->deperiodAcronym($text);

        $text = str_replace(".", " XRAY ", $text);

        $text = str_replace(',"', ' QUOTE ', $text);
        $text = str_replace(',“', ' QUOTE ', $text);
        $text = str_replace(',”', ' QUOTE ', $text);

        $text = str_replace(",", " COMMA ", $text);
        $text = str_replace('"', ' QUOTE ', $text);

        $text = str_replace('“', ' QUOTE ', $text);
        $text = str_replace('”', ' QUOTE ', $text);

        $text = str_replace('@', ' AT ', $text);

        $text = str_replace('?', ' QUERY ', $text);
        $text = str_replace('!', ' EXCLAMATION ', $text);
        $text = str_replace('$', ' DOLLAR ', $text);
        $text = str_replace('#', ' HASH ', $text);
        $text = str_replace('*', ' ASTERISK ', $text);

        $text = str_replace("'", ' APOSTROPHE ', $text);
        $text = str_replace("’", ' APOSTROPHE ', $text);
        $text = str_replace("`", ' APOSTROPHE ', $text);

        $text = str_replace("(", ' BRACKET ', $text);
        $text = str_replace(")", ' BRACKET ', $text);

        $text = str_replace("&", ' AMPERSAND ', $text);
        $text = str_replace("%", ' PERCENT ', $text);

        $text = str_replace(":", ' COLON ', $text);
        $text = str_replace(";", ' SEMICOLAN ', $text);
        $text = str_replace("-", ' HYPHEN ', $text);
        $text = str_replace("=", ' EQUALS ', $text);

        $text = str_replace('   ', ' ', $text);
        $text = str_replace('  ', ' ', $text);
        $text = trim($text);

        return $text;
    }

    function makeMessage()
    {
        $message = $this->short_message . "<br>";
        $uuid = $this->uuid;
        $message .=
            "<p>" . $this->web_prefix . "thing/$uuid/radiogram\n \n\n<br> ";
        $this->thing_report['message'] = $message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/radiogram';

        //$this->node_list = array("rocky"=>array("rocky%20%hard%20%relay", "rocky hard origin", "rocky easy origin", "rocky easy relay" ,"bullwinkle","charley"));
        // Make buttons
        //$this->thing->choice->Create($this->agent_name, $this->node_list, "rocky");
        //$choices = $this->thing->choice->makeLinks('rocky');

        if (!isset($this->html_image)) {
            $this->makePNG();
        }

        $web = "<b>Radiogram Agent</b>";
        $web .= "<p>";

        $web .= "<p>";

        $web .= "<p>";

        if (isset($this->name_to) and isset($this->name_from)) {
            $web .= "<b>TO</b> " . $this->name_to . "<br>";
            //            $web .= "<b>TO (ROLE)</b> " . $this->position_to . "<br>";

            $web .= "<b>FROM</b> " . $this->name_from . "<br>";
            //            $web .= "<b>FROM (ROLE)</b> " . $this->position_from . "<br>";
        }

        $web .= "<p>";
        if (isset($this->text)) {
            $web .= "" . $this->text;
        }

        $web .= "<p>";

        $web .= "<b>" . "HELP</b><br>";
        $web .= "<p>";

        $web .= $this->thing_report['help'] . "<br>";
        $web .= "<p>";
        $web .= "<b>" . "INFORMATION</b><br>";
        $web .= "<p>";

        $web .= $this->thing_report['info'] . "<br>";
        $web .= "<p>";

        $web .= "<b>" . "PDFS</b><br>";
        $web .= "<p>";

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/radiogram.txt';
        $web .=
            '<a href="' .
            $link .
            '">' .
            "Machine-prepared TXT radiogram (txt)" .
            "</a>";
        $web .= "<br>";

        //$web .= "<p>";
        //$web .= "PERCS format radiogram - ";

        if ($this->num_words > 25) {
            $web .= "No PERCS pdf available. Message > 25 words.<br><p>";
        } else {
            $link =
                $this->web_prefix . 'thing/' . $this->uuid . '/radiogram.pdf';
            $web .=
                '<a href="' .
                $link .
                '">' .
                "Machine-filled PERCS radiogram (pdf)" .
                "</a>";
            $web .= "<br>";
            //  $web .= "<p>";
        }

        //        $web .= "<b>" . "USEFUL LINKS</b><br>";
        //        $web .= "<p>";

        $useful_links = "None found.<br>";
        foreach ($this->pdfs as $i => $url) {
            if ($i == 0) {
                continue;
            }

            $title = $url['title'];
            $link = $url['url'];
            $useful_links .= '<a href="' . $link . '">' . $title . " (pdf)</a>";

            $useful_links .= "<br>";
        }

        $web .= $useful_links;

        $web .= "<p>";
        $web .= "<b>" . "URLS</b><br>";
        $web .= "<p>";

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/radiogram.txt';

        $useful_links = "None found.<br>";
        foreach ($this->urls as $i => $url) {
            if ($i == 0) {
                continue;
            }

            $title = $url['title'];
            $link = $url['url'];
            $useful_links .= '<a href="' . $link . '">' . $title . " (pdf)</a>";

            $useful_links .= "<br>";
        }

        $web .= $useful_links;

        $web .= "<p>";

        $web .= "<b>" . "META</b><br>";
        $web .= "<p>";

        $web .= $this->thing->nuuid . " - " . $this->thing->created_at;

        $togo = $this->thing->human_time($this->time_remaining);
        $web .= " - " . $togo . " remaining.<br>";

        $web .= "<br>";

        $link = $this->web_prefix . "privacy";
        $privacy_link = '<a href="' . $link . '">' . $link . "</a>";

        $ago = $this->thing->human_time(
            time() - strtotime($this->thing->created_at)
        );
        $web .= "Radiogram was created about " . $ago . " ago. ";

        $web .=
            "This proof-of-concept radiogram is hosted by the " .
            ucwords($this->word) .
            " service.  Read the privacy policy at " .
            $privacy_link .
            ".";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    public function makePDF()
    {
        $file = $this->resource_path . 'percs/PERCS_Message_Form_Ver1.4.pdf';
        if (($file === null) or (!file_exists($file))) {
            $this->thing_report['pdf'] = false;
            return $this->thing_report['pdf'];
        }


        if ($this->num_words > 25) {
            return;
        }

        //$txt = $this->thing_report['txt'];

        // initiate FPDI
        $pdf = new Fpdi\Fpdi();

        // http://www.percs.bc.ca/wp-content/uploads/2014/06/PERCS_Message_Form_Ver1.4.pdf
        $pdf->setSourceFile($file);
        $pdf->SetFont('Helvetica', '', 10);

        $tplidx1 = $pdf->importPage(1, '/MediaBox');

        $s = $pdf->getTemplatesize($tplidx1);

        $pdf->addPage($s['orientation'], $s);
        // $pdf->useTemplate($tplidx1,0,0,215);
        $pdf->useTemplate($tplidx1);

        $pdf->SetTextColor(0, 0, 0);

        $text =
            "Radiogram generated at " . $this->thing->created_at . ".";
        $pdf->SetXY(130, 10);
        $pdf->Write(0, $text);

        $this->getQuickresponse(
            $this->web_prefix . 'thing\\' . $this->uuid . '\\radiogram'
        );
        $pdf->Image($this->quick_response_png, 199, 2, 10, 10, 'PNG');

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
        $meta_agent = new Meta($this->thing, "meta");
        $meta_agent->extractMeta($this->subject);

        $this->name_to = $meta_agent->to;
        $this->name_from = $meta_agent->from;

        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'radiogram') {
                $this->getMessage();

                if (!isset($this->index) or $this->index == null) {
                    $this->index = 1;
                }
                return;
            }
        }

        $keywords = [
            "radiogram",
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

    function getMessage()
    {
        $meta = "X / X / X / X / X / X";
        $name_to = "X";
        $position_to = "X";
        $organization_to = "X";
        $number_to = "X";
        $text = "X";
        $name_from = "X";
        $position_from = "X";
        $organization_from = "X";
        $number_from = "X";

        // Do we have meta in the subject to fill the blanks?

        $meta_agent = new Meta($this->thing, "meta");
        $meta_agent->extractMeta($this->subject);

        if ($meta_agent->subject != "") {
            $text = $meta_agent->subject;
        }

        if ($meta_agent->to != "") {
            $name_to = $meta_agent->to;
        }

        if ($meta_agent->from != "") {
            $name_from = $meta_agent->from;
        }

        $this->message = [
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

        $this->num_words = 0;

        $this->message['number'] = "X";
        $this->message['hx'] = "X";
        $this->message['station_origin'] = "X";
        $this->message['check'] = "X";
        $this->message['place_filed'] = "X";
        $this->message['time_filed'] = "X";
        $this->message['date_filed'] = "X";

        $this->message['precedence'] = "X";

        $this->filename = "X";
        $this->title = "X";
        $this->author = "X";
        $this->date = "X";
        $this->version = "X";

        $this->text = $this->message['text'];
        return false;
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
