<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from Tick

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Boundary extends Agent
{
    public function init()
    {
        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = ["boundary" => ["forget"]];
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->default_suit = "routine";

        $this->thing->log(
            'Agent "Boundary" running on Thing ' . $this->thing->nuuid . '.'
        );
    }

    public function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables boundary " . $this->from
        );
        $this->current_time = $this->thing->time();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "boundary",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["boundary", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->nom = strtolower(
            $this->thing->json->readVariable(["boundary", "nom"])
        );

        $this->suit = $this->default_suit;
        $suit = $this->thing->json->readVariable(["boundary", "suit"]);
        if ($suit !== false) {
            $this->suit = $suit;
        }

        if ($this->nom == false or $this->suit == false) {
            $this->getBoundary();

            $this->thing->json->writeVariable(["boundary", "nom"], $this->nom);
            $this->thing->json->writeVariable(["boundary", "suit"], $this->suit);

            $this->thing->log(
                $this->agent_prefix . ' completed read.',
                "OPTIMIZE"
            );
        } else {
            $this->colourBoundary();

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

    public function readBoundary($text = null)
    {
        if ($text == null) {
            return true;
        }

        if (strtolower($text) == "boundary") {
            return false;
        }

        // And ignore these too.
        if (strtolower($text) == "rule") {
            return false;
        }
        if (strtolower($text) == "etiquette") {
            return false;
        }

        $tokens = explode(" ", strtolower($text));
        if ($tokens[1] == "etiquette" and substr($text, 0, 1) == "@") {
            return false;
        }

        $first_two_characters = strtolower(substr($text, 0, 2));

        if ($first_two_characters == 's/') {
            return false;
        }

        return $text;
    }

    private function colourBoundary()
    {
        if ($this->suit == 'welfare' or $this->suit == 'routine') {
            $this->colour = "black";
        } else {
            $this->colour = "red";
        }
    }

    public function getBoundary()
    {
        // So if the word rule is provided.
        // It means we want to see the current rule.

        // But for now create a standard face rule randomly independent of prior deck selection.

        if ($this->nom == false or $this->suit == false) {
            $this->newBoundary();
        }

        $this->colourBoundary();
    }

    public function newBoundary()
    {
        if (strtolower($this->subject) == "boundary") {
            $this->response .= "No boundary found. ";
            return;
        }
        $array = ['emergency', 'priority', 'routine', 'welfare'];
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

        $this->response .=
            'Boundary is, "' . $this->readBoundary($this->subject) . '". ';
    }

    public function imagineBoundary()
    {
        new Thought($this->thing, "thought");
    }

    public function makeSMS()
    {
        $sms = "BOUNDARY | " . $this->response;

        //        }

        //$sms .= " | id " . $this->id;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    private function svgBoundary($rule)
    {
        $suits = [
            "emergency" => "H",
            "priority" => "C",
            "routine" => "S",
            "welfare" => "D",
        ];

        $suit = "priority"; // Default
        if (isset($suits[$rule['suit']])) {
            $suit = $suits[$rule['suit']];
        }
        $filename = strtoupper($rule['nom']) . $suit . ".svg";

        return $filename;
    }

    public function makeWeb()
    {
        $web = "<b>Boundary Agent</b><br>";
        $web .= "<p>";
        $web .= "<p>";
        $web .= "<b>BOUNDARY</b><p>";
        if ($this->readBoundary($this->subject) !== false) {
            $web .= $this->readBoundary($this->subject);


            $web .= "<p>";
            $web .= "To forget this boundary CLICK on the FORGET button.<p>";
        } else {
            $web .= "No boundary found.";
        }

        $this->thing_report['web'] = $web;
    }

    public function makeSnippet()
    {
            $web = $this->readBoundary($this->subject);

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
            "boundary"
        );
        $this->choices = $this->thing->choice->makeLinks('boundary');
        $this->thing_report['choices'] = $this->choices;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        // Get the current user-state.
        $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] =
            $this->agent_prefix . 'responding to the word boundary';
        return $this->thing_report;
    }

    public function readSubject()
    {
    }

    public function boundary()
    {
        $this->getBoundary();

        $this->thing->log(
            $this->agent_prefix .
                ' says, "Think that boundary could be any boundary.\n"'
        );

    }
}
