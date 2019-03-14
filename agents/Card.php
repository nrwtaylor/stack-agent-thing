<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from Tick

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Card
{
    public function __construct(Thing $thing, $agent_input = null)
    {

        // If just an empty thing is provided, turn it into a card.
        if (!isset($thing->to)) {
            $thing->Create(null,"card", 's/ card');
        }

        $this->agent_input = $agent_input;
        if ($agent_input == null) {
            $this->agent_input = $agent_input;
        }

        $this->thing = $thing;
        $this->agent_name = 'card';
        $this->agent_prefix = '"Card" ' . ucwords($this->agent_name) . '" ';

        $this->thing_report['thing'] = $this->thing->thing;

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        //$this->sqlresponse = null;

        $this->node_list = array("card"=>array("stop","play"));

        $this->thing->log('Agent "Card" running on Thing '. $this->thing->nuuid . '.');

        $this->variables_agent = new Variables($this->thing, "variables card " . $this->from);
        $this->current_time = $this->thing->time();
        $this->get();
        $this->readSubject();

        // frame

        $this->variable = 1;
        $this->card();

        // frame

        $this->set();
        if ($this->agent_input == null) {
            $this->respond();
        }
        $this->thing->flagGreen();

        $this->thing->log($this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.');

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;

        return;
    }

    public function set()
    {
        $this->variables_agent->setVariable("id", $this->id);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

        return;
    }


    public function get()
    {
        $this->id = $this->variables_agent->getVariable("id");
        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        $this->thing->log($this->agent_prefix .  'loaded ' . $this->id . ".");

        return;
    }


/*
    public function countCard()
    {
        // devstack count snowflakes on stack identity
        // This is a count of all snow everywhere.
        $this->count += 1;
    }
*/
    public function getCard()
    {

        // So if the word card is provided.
        // It means we want to see the current card.

        // But for now create a standard face card randomly independent of prior deck selection.

        
        $array = array('spades','hearts','diamonds','clubs');
        $k = array_rand($array);
        $v = $array[$k];

        $this->suit = strtolower($v);

        if (($this->suit == 'spades') or ($this->suit == 'clubs')) {
            $this->colour = "black";
        } else {
            $this->colour = "red";
        }

        $array = array('A','2','3','4','5','6','7','8','9','J','Q','K');
        $k = array_rand($array);
        $v = $array[$k];

        $this->face = strtolower($v);

        $this->response = "Drew " . $this->colour . " " . $this->face . " " . $this->suit;

//echo "make a thing":
        //$thing = new Thing(null);
//echo "turn it into a snowflake";

    //$thing->Create(null,"card", 's/ card message');
//echo "made a card/n";
    // Think about this :/
    //$card = new Card($thing, "card message card@stackr.ca");


        //$this->snowflake = new Snowflake($thing);
//echo "count";
        //$this->countCard();

    }

    public function newCard()
    {
        $this->card = new Thing(null);
        $this->card->Create(null, "card", "s/ new card");
//echo "made card";

    }

    public function imagineCard()
    {

        // because it is the same as the number falling on you.

        // In the performed case.

        new Thought($this->thing, "thought");

        // 1 billion in a cubic foot
        // An inch covers.
        // So a 12th of that.

        //if ($this->count > 1e9 / 12) {
        //    new Stop($this->thing);
        //}
    }

    private function makeSMS()
    {
        switch ($this->id) {
            case 1:
                $sms = "CARD | A snowflake falls. Text SNOWFLAKE.";
                break;
            case 2:
                $sms = "CARD | Another one.  Appears. Text CARD.";
                break;

            case null:

            default:
                $sms = "CARD | " . $this->response;

        }

        $sms .= " | id " . $this->id;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    private function makeEmail()
    {
        switch ($this->id) {
            case 1:
                $subject = "Card request received";
                $message = "Carding.\n\n";

                break;

            case null:

            default:
               $subject = "Card request received";
               $message = "It is still playing.\n\n";
        }

        $this->message = $message;
        $this->thing_report['email'] = $message;
    }

    private function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks('snow');

        $this->choices = $choices;
        $this->thing_report['choices'] = $choices;
    }

    public function respond()
    {
        // Thing actions
        $this->thing->flagGreen();

        // Get the current user-state.
        $this->makeSMS();
        $this->makeEmail();
        $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
        //$this->thing_report['sms'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] = $this->agent_prefix . 'responding to the word card';
        return $this->thing_report;
    }

    public function readSubject()
    {
        $this->newCard();
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

        $this->thing->log($this->agent_prefix .' says, "Think that card could be any card.\n"');

        return;
    }
}
