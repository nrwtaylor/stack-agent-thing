<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from Tick

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Rule extends Agent
{
    public function init()
    {
        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = ["rule" => ["forget"]];
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->default_suit = "routine";

        $this->thing->log(
            'Agent "Rule" running on Thing ' . $this->thing->nuuid . '.'
        );
    }

    public function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables rule " . $this->from
        );
        $this->current_time = $this->thing->time();

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "rule",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["rule", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->nom = strtolower(
            $this->thing->json->readVariable(["rule", "nom"])
        );

        $this->suit = $this->default_suit;
        $suit = $this->thing->json->readVariable(["rule", "suit"]);
        if ($suit !== false) {
            $this->suit = $suit;
        }

        if ($this->nom == false or $this->suit == false) {
            $this->getRule();

            $this->thing->json->writeVariable(["rule", "nom"], $this->nom);
            $this->thing->json->writeVariable(["rule", "suit"], $this->suit);

            $this->thing->log(
                $this->agent_prefix . ' completed read.',
                "OPTIMIZE"
            );
        } else {
            $this->colourRule();

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

    public function readRule($text = null)
    {
        if ($text == null) {
            return true;
        }

        if (strtolower($text) == "rule") {
            return false;
        }
        if (strtolower($text) == "etiquette") {
            return false;
        }
        if (strtolower($text) == "boundary") {
            return false;
        }

        $tokens = explode(" ", strtolower($text));

        if (
            isset($tokens[1]) and
            ($tokens[1] == "etiquette" and substr($text, 0, 1) == "@")
        ) {
            return false;
        }

        $first_two_characters = strtolower(substr($text, 0, 2));

        if ($first_two_characters == 's/') {
            return false;
        }

        //$image = @file_get_contents($this->resource_path . 'rule/' . $filename);

        return $text;
    }

    private function colourRule()
    {
        if ($this->suit == 'welfare' or $this->suit == 'routine') {
            $this->colour = "yellow";
        } else {
            $this->colour = "red";
        }
    }

    public function getRule()
    {
        // So if the word rule is provided.
        // It means we want to see the current rule.

        // But for now create a standard face rule randomly independent of prior deck selection.

        if ($this->nom == false or $this->suit == false) {
            $this->newRule();
        }

        //$this->response = "Drew " . $this->colour . " " . $this->face . " " . $this->suit;
        $this->colourRule();
        /*
        if (($this->suit == 'spades') or ($this->suit == 'clubs')) {
            $this->colour = "black";
        } else {
            $this->colour = "red";
        }
*/

        //$this->response .= 'Read rule. ';
        /*
        $this->response =
            "" .
            strtoupper($this->nom) .
            " of " .
            ucwords($this->suit) .
            " [" .
            strtoupper($this->colour) .
            "].";
*/
    }

    public function newRule()
    {
        if (strtolower($this->subject) == "rule") {
            $this->response .= "No rule found. ";
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
            'Rule is, "' . $this->readRule($this->subject) . '". ';
    }

    public function imagineRule()
    {
        new Thought($this->thing, "thought");
    }

    public function makeSMS()
    {
        $sms = "RULE | " . $this->response;

        //        }

        //$sms .= " | id " . $this->id;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    private function svgRule($rule)
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
        $rule = ["nom" => $this->nom, "suit" => $this->suit];

        $filename = $this->svgRule($rule);

        //        $image = @file_get_contents($this->resource_path . 'rule/' . $filename);

        $web = "<b>Rule Agents</b><br>";
        $web .= "<p>";
        $web .= "<p>";
        $web .= "<b>RULE</b><p>";
        if ($this->readRule($this->subject) !== false) {
            $web .= $this->readRule($this->subject);

            //            $web .= "<center>" . $image . "</center>";

            $web .= "<p>";
            $web .= "To forget this rule CLICK on the FORGET button.<p>";
        } else {
            $web .= "No rule found.";
        }

        $this->thing_report['web'] = $web;
    }

    public function makeSnippet()
    {
        $rule = ["nom" => $this->nom, "suit" => $this->suit];

        //        $filename = $this->svgRule($rule);

        //        $image = @file_get_contents($this->resource_path . 'rule/' . $filename);

        //        $web = "<center" . $image . "</center";
        $web = $this->nom . " " . $this->suit . " " . $this->input;
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
            "rule"
        );
        $this->choices = $this->thing->choice->makeLinks('rule');
        $this->thing_report['choices'] = $this->choices;
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
            $this->agent_prefix . 'responding to the word rule';
        return $this->thing_report;
    }

    public function readSubject()
    {
        //$this->newRule();
        // Ignore subject.
        return;
    }

    public function rule()
    {
        // Call the Usermanager agent and update the state
        // Stochastically call rule.
        //if (rand(1, $this->variable) == 1) {
        $this->getRule();
        //}

        $this->thing->log(
            $this->agent_prefix .
                ' says, "Think that rule could be any rule.\n"'
        );

        return;
    }
}
