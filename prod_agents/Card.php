<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from Tick

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Card extends Agent
{
    public function init()
    {
        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = ["card" => ["card", "roll", "trivia"]];

    }

    public function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables card " . $this->from
        );
        $this->current_time = $this->thing->time();

        // Borrow this from iching
        $time_string = $this->thing->Read([
            "card",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["card", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->nom = strtolower(
            $this->thing->Read(["card", "nom"])
        );
        $this->suit = $this->thing->Read(["card", "suit"]);
        if ($this->nom == false or $this->suit == false) {
            $this->getCard();

            $this->thing->Write(["card", "nom"], $this->nom);
            $this->thing->Write(["card", "suit"], $this->suit);

            $this->thing->log(
                $this->agent_prefix . ' completed read.',
                "OPTIMIZE"
            );
        } else {
            $this->colourCard();

            $this->response =
                "Retrieved " .
                strtoupper($this->nom) .
                " of " .
                ucwords($this->suit) .
                " [" .
                strtoupper($this->colour) .
                "].";
        }
    }

    public function set()
    {
    }

    private function colourCard()
    {
        if ($this->suit == 'spades' or $this->suit == 'clubs') {
            $this->colour = "black";
        } else {
            $this->colour = "red";
        }
    }

    public function getCard()
    {
        // So if the word card is provided.
        // It means we want to see the current card.

        // But for now create a standard face card randomly independent of prior deck selection.

        if ($this->nom == false or $this->suit == false) {
            $this->newCard();
        }

        //$this->response = "Drew " . $this->colour . " " . $this->face . " " . $this->suit;
        $this->colourCard();

        $this->response =
            "" .
            strtoupper($this->nom) .
            " of " .
            ucwords($this->suit) .
            " [" .
            strtoupper($this->colour) .
            "].";
    }

    public function newCard()
    {
        $array = ['spades', 'hearts', 'diamonds', 'clubs'];
        $k = array_rand($array);
        $v = $array[$k];

        $this->suit = strtolower($v);

        $array = [
            'A',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '10',
            'J',
            'Q',
            'K',
        ];
        $k = array_rand($array);
        $v = $array[$k];

        $this->nom = strtolower($v);
    }

    public function imagineCard()
    {
        new Thought($this->thing, "thought");
    }

    public function makeSMS()
    {
        $sms = "CARD | " . $this->response;

        //        }

        //$sms .= " | id " . $this->id;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    private function svgCard($card)
    {
        $suits = [
            "hearts" => "H",
            "clubs" => "C",
            "spades" => "S",
            "diamonds" => "D",
        ];
        $suit = $suits[$card['suit']];

        $filename = strtoupper($card['nom']) . $suit . ".svg";

        return $filename;
    }

    public function makePNG() {
//        if (isset($this->canvas_size_x)) {
//            $canvas_size_x = $this->canvas_size_x;
//            $canvas_size_y = $this->canvas_size_x;
//        } else {

            $canvas_size_x = 400;
            $canvas_size_y = 400;
//        }


        $this->image = imagecreatetruecolor($canvas_size_x, $canvas_size_y);



        $card = ["nom" => $this->nom, "suit" => $this->suit];

        $filename = $this->svgCard($card);
        $svg = file_get_contents($this->resource_path . 'card/' . $filename);

return;
    $imagick = new \Imagick();
$this->image = $imagick->readImageBlob($svg);


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
            '"alt="day"/>';

        $this->html_image =
            '<img src="data:image/png;base64,' .
            base64_encode($imagedata) .
            '"alt="day"/>';

        $this->PNG_embed = "data:image/png;base64," . base64_encode($imagedata);

        $this->PNG = $imagedata;

        return $response;
    }


    public function makeWeb()
    {
        $card = ["nom" => $this->nom, "suit" => $this->suit];

        $filename = $this->svgCard($card);

        $web =
            "<center>" .
            file_get_contents($this->resource_path . 'card/' . $filename) .
            "</center>";

        $this->thing_report['web'] = $web;
    }

    public function makeSnippet()
    {
        $card = ["nom" => $this->nom, "suit" => $this->suit];

        $filename = $this->svgCard($card);

        $web =
            "<center" .
            file_get_contents($this->resource_path . 'card/' . $filename) .
            "</center";

        $this->thing_report['snippet'] = $web;
    }

    public function makeEmail()
    {
        $message = "It is still playing.\n\n";

        $this->message = $message;
        $this->thing_report['email'] = $message;
    }

    public function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "card"
        );
        $this->choices = $this->thing->choice->makeLinks('card');
        $this->thing_report['choices'] = $this->choices;

        //$choices = $this->thing->choice->makeLinks('card');

        //$this->choices = $choices;
        //$this->thing_report['choices'] = $choices;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        // Get the current user-state.
        //$this->makeSMS();
        //$this->makeEmail();
        $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] =
            $this->agent_prefix . 'responding to the word card';
    }

    public function readSubject()
    {
    }

    public function card()
    {
        // Call the Usermanager agent and update the state
        // Stochastically call card.
        //if (rand(1, $this->variable) == 1) {
        $this->getCard();
        //}

        $this->thing->log(
            $this->agent_prefix .
                ' says, "Think that card could be any card.\n"'
        );
    }
}
