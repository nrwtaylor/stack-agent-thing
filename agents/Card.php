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
        //public function __construct(Thing $thing, $agent_input = null)
        //{

        // If just an empty thing is provided, turn it into a card.
        //       if (!isset($this->thing->to)) {
        //           $this->thing->Create(null,"card", 's/ card');
        //       }

        //$this->agent_input = $agent_input;
        //if ($agent_input == null) {
        //    $this->agent_input = $agent_input;
        //}

        //$this->thing = $thing;
        //$this->agent_name = 'card';
        //$this->agent_prefix = '"Card" ' . ucwords($this->agent_name) . '" ';

        //$this->thing_report['thing'] = $this->thing->thing;

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = ["card" => ["card", "charley", "roll", "trivia"]];
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->thing->log(
            'Agent "Card" running on Thing ' . $this->thing->nuuid . '.'
        );
    }

    public function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables card " . $this->from
        );
        $this->current_time = $this->thing->time();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "card",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["card", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->nom = strtolower(
            $this->thing->json->readVariable(["card", "nom"])
        );
        $this->suit = $this->thing->json->readVariable(["card", "suit"]);
        if ($this->nom == false or $this->suit == false) {
            $this->getCard();

            $this->thing->json->writeVariable(["card", "nom"], $this->nom);
            $this->thing->json->writeVariable(["card", "suit"], $this->suit);

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
        /*
        if (($this->suit == 'spades') or ($this->suit == 'clubs')) {
            $this->colour = "black";
        } else {
            $this->colour = "red";
        }
*/

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
        //        switch ($this->id) {
        //            case 1:
        //                $sms = "CARD | A card is drawn. Text CARD.";
        //                break;
        //            case 2:
        //                $sms = "CARD | Another one.  Appears. Text CARD.";
        //                break;

        //            case null:

        //            default:
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
        //$this->thing_report['sms'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] =
            $this->agent_prefix . 'responding to the word card';
        return $this->thing_report;
    }

    public function readSubject()
    {
        //$this->newCard();
        // Ignore subject.
        return;
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

        return;
    }
}
