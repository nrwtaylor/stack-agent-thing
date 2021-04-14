<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

use setasign\Fpdi;

ini_set("allow_url_fopen", 1);

class Picanic extends Agent
{
    public $var = 'hello';

    public function init()
    {
        $this->node_list = ["picanic" => ["picanic"]];

        $this->unit = "POINTS";

        $this->character = new Character(
            $this->thing,
            "character is Charles T. Owl"
        );

        // Get the remaining persistence of the message.
        $agent = new Persistence($this->thing, "persistence 60 minutes");
        $this->time_remaining = $agent->time_remaining;
        $this->persist_to = $agent->persist_to;

        if (
            isset($this->thing->container['stack']['font'])
        ) {
            $this->font =
                $this->thing->container['stack']['font'];
        }


        $this->initPicanic();
    }

    public function run()
    {
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "picanic",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["picanic", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->thing->json->setField("variables");
        $this->nom = strtolower(
            $this->thing->json->readVariable(["picanic", "nom"])
        );
        $this->number = $this->thing->json->readVariable(["picanic", "number"]);
        $this->suit = $this->thing->json->readVariable(["picanic", "suit"]);

        // Maintain a stack variable
        // For the maximum number of ants that can be tolerated this round.

        $this->variables = new Variables(
            $this->thing,
            "variables picanic " . $this->from
        );

        $ants_max = $this->variables->getVariable("ants_max");
        if ($this->ants_max !== false) {
            $this->ants_max = $ants_max;
        }
    }

    public function set()
    {
        $this->variables->setVariable("ants_max", $this->ants_max);
    }

    function initPicanic()
    {
        // devstack
        if (!isset($this->channel_count)) {
            $this->channel_count = 1;
        }

        if (!isset($this->player_count)) {
            $this->player_count = 4; // Assume 4.
        }

        if (!isset($this->food)) {
            $this->food = "X";
        }

        $this->ants_max = rand(10, 99);
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["info"] = "This creates an exercise message.";
        $this->thing_report["help"] = 'Try NONSENSE.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "picanic"
        );
        $this->choices = $this->thing->choice->makeLinks('picanic');

        $this->thing_report['choices'] = $this->choices;
    }

    function makeSMS()
    {
        $sms = "PICANIC\n";
        $sms .= $this->traffic;
        //$sms .= $this->ants_max . " maximum allowed Ants.\n";
        $sms .= $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function getPersona($role = null)
    {
        if ($role == "X") {
            $this->picanic = "Where is Yogi";
            return;
        }

        $this->picanic = array_rand([
            "picnic",
            "picanick",
            "picanic",
            "piknick",
        ]);

        $this->picanic = "Picanic";
        return $this->picanic;
    }

    public function getCards()
    {
        if (isset($this->cards)) {
            return;
        }

        // Load in the picnic items.
        $file = $this->resource_path . '/picanic/messages.txt';
        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        $this->cards = [];

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (trim($line) == "") {
                    continue;
                }
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

    public function getCard()
    {
        $this->getCards();

        if ($this->nom == false or $this->suit == false) {
            $this->card = $this->card_list[array_rand($this->card_list)];

            $this->nom = $this->card['nom'];
            $this->suit = $this->card['suit'];
            $this->number = $this->card['number'];
        }

        $this->card = $this->cards[$this->nom][$this->suit];

        $this->text = $this->card['text'];

        $this->role_from = $this->card['from'];
        $this->role_to = $this->card['to'];

        $this->fictional_to = $this->getPersona($this->role_to);
        $this->fictional_from = $this->getPersona($this->role_from);

        $this->traffic =
            "TO " .
            //$this->fictional_to .
            //", " .
            $this->role_to .
            "\nFROM " .
            //$this->fictional_from .
            //", " .
            $this->role_from .
            "\n" .
            "You now have " .
            strtolower($this->text) .
            " [" .
            $this->suit .
            "] " .
            //" " .
            //$this->unit .
            "\n";
        // . $this->number . " " . $this->unit . ".";

        $this->antsPicanic($this->number);

        if (
            $this->role_to == "X" and
            $this->role_from == "X" and
            $this->text == "X"
        ) {
            $this->traffic = $this->number . " " . $this->unit . ".";
        }

        if (
            $this->role_to == "X" and
            $this->role_from == "X" and
            $this->text != "X"
        ) {
            $this->traffic =
                $this->text . "\n" . $this->number . " " . $this->unit . ".";
        }

        if (
            $this->role_to == "X" and
            $this->role_from != "X" and
            $this->text != "X"
        ) {
            $this->traffic =
                "to: < ? >" .
                " from: " .
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
    }

    public function antsPicanic($n = null)
    {
        // Create ants.
        // Ants correspond to perceived value of resource.

        //$datagram = array("to"=>"picanic","from"=>"ant","subject"=>$this->text);
        $datagram = [
            "to" => $this->from,
            "from" => "ant",
            "subject" => $this->text,
        ];

        foreach (range(0, $n) as $i) {
            $this->thing->spawn($datagram);
        }

        if ($this->number == 1) {
            $this->response .= "" . $this->number . " POINT. ";
        } else {
            $this->response .= "" . $this->number . " POINTS. ";
        }

        $ants = $this->getThings('ant');

        $count = count($ants);
        $this->response .= "Counted " . $count . " ants. ";

        if ($count > $this->ants_max) {
            $this->response .= "ANTS. There are lots of Ants. ";
        }
    }

    public function makeMessage()
    {
        $message = $this->response . "<br>";

        $uuid = $this->uuid;

        $message .=
            "<p>" . $this->web_prefix . "thing/$uuid/picanic\n \n\n<br> ";

        $this->thing_report['message'] = $message;
    }

    public function getBar()
    {
        $this->bar = new Bar($this->thing, "display");
    }

    public function setPicanic()
    {
    }

    public function getPicanic()
    {
    }

    public function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/picanic';

        $this->node_list = ["picanic" => []];

        if (!isset($this->html_image)) {
            $this->makePNG();
        }

        $web = "<b>Picanic Agent</b>";
        $web .= "<p>";

        $web .= '<a href="' . $link . '">' . $this->html_image . "</a>";
        $web .= "<br>";

        $web .= "<p>";

        if (
            isset($this->fictional_to) and
            isset($this->role_to) and
            isset($this->fictional_from) and
            isset($this->role_from)
        ) {
        }

        $web .= "<p>";
        $web .= $this->text;
        //        }

        $web .= "<p>";
        $web .= "<b>" . $this->number . " " . $this->unit . "</b><br>";
        $web .= "<p>";

        $ago = $this->thing->human_time(time() - $this->refreshed_at);
        $web .= "This picnic item was created about " . $ago . " ago. ";

        $link = $this->web_prefix . "privacy";
        $privacy_link = '<a href="' . $link . '">' . $link . "</a>";

        $web .=
            "This text-based picnic is hosted by the " .
            ucwords($this->word) .
            " service.  Read the privacy policy at " .
            $privacy_link .
            ".";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    public function makeTXT()
    {
        $txt = "Traffic for PICANIC.\n";
        $txt .= 'Duplicate messages may exist. Can you de-duplicate?';
        $txt .= "\n";

        $txt .= $this->response;

        $txt .= "\n";

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    public function makeImage()
    {
        // public function makePNG()
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

        $text = strtoupper($this->nom);

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

        $text = strtoupper($this->unit);

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
        extract($bbox, EXTR_PREFIX_ALL, 'bb');

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

        // Small nuuid text for back-checking.
        imagestring($this->image, 2, 140, 0, $this->thing->nuuid, $textcolor);
    }

    public function makeAlttext()
    {
        $this->thing_report['alt_text'] =
            $this->number . " " . $this->unit . "";
    }

    public function makePNG()
    {
        // https://stackoverflow.com/questions/14549110/failed-to-delete-buffer-no-buffer-to-delete
        if (ob_get_contents()) {
            ob_clean();
        }

        ob_start();
        imagepng($this->image);
        $imagedata = ob_get_contents();

        ob_end_clean();

        $this->thing_report['png'] = $imagedata;

        $alt_text = 'picanic token';
        if (isset($this->thing_report['alt_text'])) {
            $alt_text = $this->thing_report['alt_text'];
        }

        $response =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="' .
            $alt_text .
            '"/>';

        $this->PNG_embed = "data:image/png;base64," . base64_encode($imagedata);

        $this->PNG = $imagedata;

        $this->html_image = $response;

        return $response;
    }

    // devstack
    public function read($variable = null)
    {
        if (
            $this->nom == false or
            $this->number == false or
            $this->suit == false
        ) {
            $this->readSubject();

            $this->thing->json->writeVariable(["picanic", "nom"], $this->nom);
            $this->thing->json->writeVariable(
                ["picanic", "number"],
                $this->number
            );
            $this->thing->json->writeVariable(["picanic", "suit"], $this->suit);

            $this->thing->log(
                $this->agent_prefix . ' completed read.',
                "OPTIMIZE"
            );
        }

        $this->getCard();
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'picanic') {
                //$this->getCard();

                return;
            }
        }
        $keywords = ["reset", "picanic", "ants"];
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'reset':
                            $ants = $this->getThings('ant');
                            //$count = count($ants);
                            $count = 0;
                            foreach ($ants as $uuid => $ant) {

                                $thing = new Thing($uuid);
                                $thing->Forget();
                                $count += 1;
                            }
                            $this->ants_max = rand(10, 90);
                            $this->response .=
                                "Set ant max to " . $this->ants_max . ". ";

                            $this->response .= "Killed " . $count . " Ants. ";
                            return;

                        case 'on':

                        default:
                    }
                }
            }
        }
    }
}
