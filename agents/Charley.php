<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Charley extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->test = "Development code";

        $this->node_list = ["charley" => ["charley", "rocky"]];

        $this->unit = "FUEL";
        $this->getNuuid();

        $this->mode = "voice";
        if (isset($this->thing->container["api"]["charley"]["mode"])) {
            $this->mode = $this->thing->container["api"]["charley"]["mode"];
        }

        $this->character = new Character(
            $this->thing,
            "character is Charles T. Owl"
        );

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        $this->thing_report["help"] =
            "Try changing the message passing mode. CHARLEY RADIOGRAM. Or CHARLEY VOICE.";

        $this->initCharley();
    }

    public function run()
    {
    }

    public function get()
    {
        $this->variables = new Variables(
            $this->thing,
            "variables charley " . $this->from
        );

        $mode = $this->variables->getVariable("mode");

        if ($mode != false) {
            $this->mode = $mode;
        }

        // Borrow this from iching
        $time_string = $this->thing->Read([
            "charley",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["charley", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->nom = strtolower(
            $this->thing->Read(["charley", "nom"])
        );
        $this->number = $this->thing->Read(["charley", "number"]);
        $this->suit = $this->thing->Read(["charley", "suit"]);

        if (
            $this->nom == false or
            $this->number == false or
            $this->suit == false
        ) {
            $this->getCard();

            $this->thing->Write(["charley", "nom"], $this->nom);
            $this->thing->Write(
                ["charley", "number"],
                $this->number
            );
            $this->thing->Write(["charley", "suit"], $this->suit);
        }

        $this->getCard();
    }

    public function set()
    {
        $time_string = $this->thing->time();
        $this->thing->Write(
            ["charley", "refreshed_at"],
            $time_string
        );

        $this->variables->setVariable("mode", $this->mode);
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

    function getQuickresponse($txt = "qr")
    {
        $agent = new Qr($this->thing, $txt);
        $this->quick_response_png = $agent->PNG_embed;
    }

    public function initCharley()
    {
        $this->c = new Compression($this->thing, "compression charley");

        $this->charlies = [];
        if (isset($this->c->agent->matches["charley"])) {
            $matches = $this->c->agent->matches["charley"];

            foreach ($matches as $i => $match) {
                $this->charlies[] = $match["words"];
            }
        }

        // Charley variables

        if (!isset($this->channel_count)) {
            $this->channel_count = 2;
        }
        if (!isset($this->volunteer_count)) {
            $this->volunteer_count = 3;
        }
        if (!isset($this->food)) {
            $this->food = "X";
        }

        // $this->setProbability();
        // $this->setRules();
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["info"] = "This creates an exercise message.";

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "charley"
        );
        $this->choices = $this->thing->choice->makeLinks("charley");

        $this->thing_report["choices"] = $this->choices;
    }

    public function makeSMS()
    {
        $sms = "CHARLEY " . $this->mode . "\n";

        if ($this->mode == "voice" and isset($this->voice)) {
            $sms .= $this->voice;
        }

        if ($this->mode == "radiogram" and isset($this->radiogram)) {
            $sms .= $this->radiogram;
        }

        $sms .= " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function getCast()
    {
        // Load in the cast. And roles.
        $file = $this->resource_path . "/charley/charley.txt";

        if (!file_exists($file)) {
            return true;
        }

        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $person_name = $line;
                $arr = explode(",", $line);
                $name = trim($arr[0]);
                if (isset($arr[1])) {
                    $role = trim($arr[1]);
                } else {
                    $role = "X";
                }

                // Unique name <> Role mappings. Check?
                $this->name_list[$role] = $name;
                $this->role_list[$name] = $role;

                //$this->placename_list[] = $place_name;
                $this->cast[] = ["name" => $name, "role" => $role];
            }

            fclose($handle);
        } else {
            // error opening the file.
        }
    }

    public function nameCharley($role = null)
    {
        if (!isset($this->name_list)) {
            $this->getCast();
        }

        if ($role == "X") {
            $this->charley = "Rocky";
            return;
        }

        $input = $this->charlies;

        // Pick a random Charles.
        $charley_index = 0;
        if (isset($this->thing->thing->created_at)) {
            $created_at = strtotime($this->thing->thing->created_at);
            // $charley_index = $this->refreshed_at % count($input);
            $charley_index = $created_at % count($input);
        }
        $this->charley = ucwords($input[$charley_index]);

        if (isset($this->name_list[$role])) {
            $this->charley = $this->name_list[$role];
        }

        return $this->charley;
    }

    function getResponse($nom, $suit)
    {
        if (isset($this->response)) {
            return;
        }
        $this->getCards();

        $this->getCard();

        $this->response .= $this->text;
    }

    function getCards()
    {
        if (isset($this->cards)) {
            return;
        }

        // Load in the cast. And roles.
        $file = $this->resource_path . "charley/messages-a01.txt";

        if (!file_exists($file)) {
            $this->response .= "Could not load radiogram cards. ";
            return;
        }

        if ($this->mode == "voice") {
            $file = $this->resource_path . "charley/voice-a01.txt";

            if (!file_exists($file)) {
                $this->response .= "Could not load voice cards. ";

                return true;
            }
        }

        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        $this->cards = [];

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $arr = explode(",", $line);

                $nom = $arr[0]; // Describer of the card
                $suit = trim($arr[1]);
                $number = trim($arr[2]);
                $text = trim($arr[3]);

                $from = "X";
                if (isset($arr[4])) {
                    $from = trim($arr[4]);
                }

                $to = "X";
                if (isset($arr[5])) {
                    $to = trim($arr[5]);
                }

                //$this->nom_list[] = $nom;
                //$this->suit_list[] = $suit;
                //$this->number_list[] = $number;
                //$this->text_list[] = $text;

                $this->texts[$nom][$suit] = $text;
                $this->numbers[$nom][$suit] = $number;

                $this->card_list[] = [
                    "nom" => $nom,
                    "suit" => $suit,
                    "number" => $number,
                    "text" => $text,
                    "from" => $from,
                    "to" => $to,
                ];

                $this->cards[$nom][$suit] = [
                    "nom" => $nom,
                    "suit" => $suit,
                    "number" => $number,
                    "text" => $text,
                    "from" => $from,
                    "to" => $to,
                ];
            }

            fclose($handle);
        } else {
            // error opening the file.
        }
    }

    function getCard()
    {
        $this->getCards();

        if ($this->nom == false or $this->suit == false) {
            if (!isset($this->card_list)) {
                return true;
            }

            $this->card = $this->card_list[array_rand($this->card_list)];
            $this->nom = $this->card["nom"];
            $this->suit = $this->card["suit"];
            $this->number = $this->card["number"];
        }

        $this->card = $this->cards[strtoupper($this->nom)][$this->suit];

        $this->text = $this->card["text"];
        if ($this->text == "ROCKY") {
            $this->text = "Send a ROCKY inject to the last station.";
        }

        $this->role_from = $this->card["from"];
        $this->role_to = $this->card["to"];

        if ($this->number == "X") {
            $this->instruction = "REPORT" . " " . $this->unit;
        }
        if ($this->number == 0.5) {
            $this->instruction = "HALVE" . " " . $this->unit;
        }

        if ($this->number == "x0.5") {
            $this->instruction = "HALVE" . " " . $this->unit;
        }

        if ($this->number == "MISS") {
            $this->instruction = "MISS A TURN";
        }

        if ($this->number == "-") {
            $this->instruction = "CHALLENGE";
        }

        if ($this->number == "FIRST") {
            $this->instruction = "FIRST CHECKIN";
        }

        if ($this->number == "0") {
            $this->instruction = "NO CHANGE TO " . $this->unit;
        }

        if (is_numeric($this->number)) {
            if ($this->number < 0) {
                $this->instruction =
                    "SUBTRACT " . abs($this->number) . " " . $this->unit;
            }
            if ($this->number > 0) {
                $this->instruction = "ADD " . $this->number . " " . $this->unit;
            }

            //if ($this->number == 0) {$this->number = "BINGO";}
        }

        $this->fictional_to = $this->nameCharley($this->role_to);
        $this->fictional_from = $this->nameCharley($this->role_from);

        //        $this->response = "to: " . $this->fictional_to . ", " . $this->role_to . " from: " . $this->fictional_from . ", " . $this->role_from . " / " . $this->text . " / " . $this->number . " " . $this->unit . ".";
        $this->radiogram =
            "TO " .
            $this->fictional_to .
            ", " .
            $this->role_to .
            "\nFROM " .
            $this->fictional_from .
            ", " .
            $this->role_from .
            "\n" .
            "INJECT " .
            $this->text .
            "\n" .
            $this->instruction;

        if (
            $this->role_to == "X" and
            $this->role_from == "X" and
            $this->text == "X"
        ) {
            $this->radiogram = $this->instruction;
        }

        if (
            $this->role_to == "X" and
            $this->role_from == "X" and
            $this->text != "X"
        ) {
            $this->radiogram = $this->text . "\n" . $this->instruction;
        }

        if (
            $this->role_to == "X" and
            $this->role_from != "X" and
            $this->text != "X"
        ) {
            $this->radiogram =
                "to: < ? >" .
                " from: " .
                $this->fictional_from .
                ", " .
                $this->role_from .
                " / " .
                $this->text .
                "\n" .
                $this->instruction;
        }

        $this->radiogram .= ".";

        //if (isset($this->text)) {

        $this->voice = $this->text . "\n" . $this->instruction;
        $this->voice .= ".";
    }

    function makeMessage()
    {
        $message = "";
        if (isset($this->radiogram)) {
            $message .= $this->radiogram . "<br>";
        }
        $uuid = $this->uuid;

        $message .=
            "<p>" . $this->web_prefix . "thing/$uuid/charley\n \n\n<br> ";

        $this->thing_report["message"] = $message;
    }

    function getBar()
    {
        $this->bar = new Bar($this->thing, "display");
    }

    function setCharley()
    {
    }

    function getCharley()
    {
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/charley";

        $this->node_list = ["charley" => ["charley", "rocky"]];

        if (!isset($this->html_image)) {
            $this->makePNG();
        }

        $web = "<b>Charley Agent</b>";
        $web .= "<p>";
        $web .= "<p>";

        $web .= '<a href="' . $link . '">' . $this->html_image . "</a>";
        $web .= "<br>";

        $web .= "<p>";

        //$web .= $this->nom;

        switch (trim($this->suit)) {
            case "diamonds":
                $web .= "OPERATIONS inject received.";
                break;
            case "hearts":
                $web .= "PLANNING inject received.";
                break;
            case "clubs":
                $web .= "LOGISTICS inject received.";
                break;
            case "spades":
                $web .= "FINANCE inject received.";
                break;
            default:
                $web .= "UNRECOGNIZED inject received.";
        }

        $web .= "<p>";

        if (
            isset($this->fictional_to) and
            isset($this->role_to) and
            isset($this->fictional_from) and
            isset($this->role_from)
        ) {
            $web .= "<b>TO (NAME)</b> " . $this->fictional_to . "<br>";
            $web .= "<b>TO (ROLE)</b> " . $this->role_to . "<br>";
            $web .= "<b>FROM (NAME)</b> " . $this->fictional_from . "<br>";
            $web .= "<b>FROM (ROLE)</b> " . $this->role_from . "<br>";
        }

        if (isset($this->text)) {
            $web .= "<p>";
            $web .= $this->text;
        }

        $web .= "<p>";
        $web .= "<b>" . $this->instruction . "</b><br>";
        $web .= "<p>";

        if (isset($this->thing->thing->created_at)) {
            $ago = $this->thing->human_time(
                time() - strtotime($this->thing->thing->created_at)
            );
            $web .= "This inject was created about " . $ago . " ago. ";
        }
        $link = $this->web_prefix . "privacy";
        $privacy_link = '<a href="' . $link . '">' . $link . "</a>";

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
        $txt = "Traffic for CHARLEY.\n";
        $txt .= "Duplicate messages may exist. Can you de-duplicate?";
        $txt .= "\n";
        if (isset($this->radiogram)) {
            $txt .= $this->radiogram;
        }
        $txt .= "\n";

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    public function makePNG()
    {
        $this->image = imagecreatetruecolor(164, 164);

        $width = imagesx($this->image);
        $height = imagesy($this->image);

        $this->white = imagecolorallocate($this->image, 255, 255, 255);
        $this->black = imagecolorallocate($this->image, 0, 0, 0);
        $this->red = imagecolorallocate($this->image, 255, 0, 0);
        $this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->grey = imagecolorallocate($this->image, 128, 128, 128);
        $this->blue = imagecolorallocate($this->image, 0, 68, 255);

        $this->flag_yellow = imagecolorallocate($this->image, 255, 239, 0);

        switch (trim($this->suit)) {
            case "diamonds":
                imagefilledrectangle(
                    $this->image,
                    0,
                    0,
                    $width,
                    $height,
                    $this->red
                );
                $textcolor = imagecolorallocate($this->image, 255, 255, 255);
                break;
            case "hearts":
                imagefilledrectangle(
                    $this->image,
                    0,
                    0,
                    $width,
                    $height,
                    $this->blue
                );
                $textcolor = imagecolorallocate($this->image, 255, 255, 255);
                break;
            case "clubs":
                imagefilledrectangle(
                    $this->image,
                    0,
                    0,
                    $width,
                    $height,
                    $this->flag_yellow
                );
                $textcolor = imagecolorallocate($this->image, 0, 0, 0);
                break;
            case "spades":
                imagefilledrectangle(
                    $this->image,
                    0,
                    0,
                    $width,
                    $height,
                    $this->grey
                );
                $textcolor = imagecolorallocate($this->image, 255, 255, 255);
                break;
            default:
                imagefilledrectangle(
                    $this->image,
                    0,
                    0,
                    $width,
                    $height,
                    $this->white
                );
                $textcolor = imagecolorallocate($this->image, 0, 0, 0);
        }

        // Write the string at the top left
        $border = 30;
        $radius = (1.165 * ($width - 2 * $border)) / 3;

        // devstack add path
        $font = $this->default_font;
        $text = "EXERCISE EXERCISE EXERCISE WELFARE TEST ROCKY 5";
        $text = "ROCKY";

        $text = strtoupper($this->nom);

        if (!isset($this->bar)) {
            $this->getBar();
        }

        $bar_count = $this->bar->bar_count;

        // Add some shadow to the text
        //imagettftext($this->image, 40 , 0, 0 - $this->bar->bar_count*5, 75, $this->grey, $font, $text);

        $size = 72;
        $angle = 0;

        if (file_exists($font)) {
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
            $pad = 0;

            imagettftext(
                $this->image,
                $size,
                $angle,
                $width / 2 - $bb_width / 2,
                $height / 2 + $bb_height / 2,
                $textcolor,
                $font,
                $text
            );

            $text = $this->instruction;

            $size = 9.5;
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

            imagettftext(
                $this->image,
                $size,
                $angle,
                $width / 2 - $bb_width / 2,
                ($height * 11) / 12,
                $textcolor,
                $font,
                $text
            );
        }
        // Small nuuid text for back-checking.
        imagestring($this->image, 2, 140, 0, $this->thing->nuuid, $textcolor);

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
        $this->thing_report['png'] = $imagedata;
        return $response;
    }

    function setRules()
    {
        $this->rules = [];
        /*
        $this->rules[0][0][0][0][0][1] = 1;
        $this->rules[0][0][0][0][1][1] = 2;
        $this->rules[0][0][0][1][0][1] = 3;
        $this->rules[0][0][0][1][1][1] = 4;
        $this->rules[0][0][1][0][0][1] = 5;
        $this->rules[0][0][1][0][1][1] = 6;
        $this->rules[0][0][1][1][0][1] = 7;
        $this->rules[0][0][1][1][1][1] = 8;
        $this->rules[0][1][0][1][0][1] = 9;
        $this->rules[0][1][0][1][1][1] = 10;
        $this->rules[0][1][1][0][1][1] = 11;
        $this->rules[0][1][1][1][1][1] = 12;
        $this->rules[1][1][1][1][1][1] = 13;
*/
    }

    function computeTranspositions($array)
    {
        if (count($array) == 1) {
            return false;
        }
        $result = [];
        foreach (range(0, count($array) - 2) as $i) {
            $tmp_array = $array;
            $tmp = $tmp_array[$i];
            $tmp_array[$i] = $tmp_array[$i + 1];
            $tmp_array[$i + 1] = $tmp;
            $result[] = $tmp_array;
        }

        return $result;
    }

    function readCharley()
    {
    }

    public function readSubject()
    {
        $input = strtolower($this->input);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "charley") {
                return;
            }
        }

        $keywords = [
            "charley",
            "rocky",
            "bullwinkle",
            "natasha",
            "boris",
            "voice",
            "radiogram",
        ];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "radiogram":
                            $this->response .=
                                "Switched injects to radiogram mode. ";
                            $this->mode = "radiogram";
                            return;

                        case "voice":
                            $this->response .=
                                "Switched injects to voice mode. ";
                            $this->mode = "voice";
                            return;

                        case "charley":

                        default:
                    }
                }
            }
        }
    }
}
