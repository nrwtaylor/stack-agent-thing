<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from Tick

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Snow extends Agent
{
    public function init()
    {
        $this->node_list = ["snow" => ["stop", "snow"]];

        $this->variables_agent = new Variables(
            $this->thing,
            "variables snow " . $this->from
        );
    }

    public function run()
    {

        $this->variable = 1;
        $this->snow();

    }

    public function set()
    {
        $this->variables_agent->setVariable("snowflakes", $this->snowflakes);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    public function get()
    {
        $this->snowflakes = $this->variables_agent->getVariable("snowflakes");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(
            $this->agent_prefix . 'loaded ' . $this->snowflakes . "."
        );
    }

    public function countSnow()
    {
        // devstack count snowflakes on stack identity
        // This is a count of all snow everywhere.
        $this->snowflakes += 1;
    }

    public function getSnowflake()
    {
        // Make a Thing.
        $thing = new Thing(null);
        // Turn the Thing into a snowflake";
        $this->snowflake = new Snowflake($this->thing);
        // Count this Snowflake.
        $this->countSnow();
    }

    public function imagineSnow()
    {
        // Because it is the same as the number falling on you.
        // In the performed case.

        new Thought($this->thing, "thought");

        // 1 billion in a cubic foot
        // An inch covers.
        // So a 12th of that.

        if ($this->snowflakes > 1e9 / 12) {
            new Stop($this->thing);
        }
    }

    public function makeSMS()
    {
        switch ($this->snowflakes) {
            case 1:
                $sms = "SNOW | A snowflake falls. Text SNOWFLAKE.";
                break;
            case 2:
                $sms = "SNOW | Another one.  Appears. Text SNOW.";
                break;

            case null:

            default:
                $sms = "SNOW | It is snowing. Everywhere.";
        }

        $sms .= " | snowflakes " . $this->snowflakes;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function makeEmail()
    {
        switch ($this->snowflakes) {
            case 1:
                $subject = "Snow request received";
                $message =
                    "It is snowing.\nhttps://www.facebook.com/yokoonopage/photos/a.10150157196475535.335529.10334070534/10152999025540535/?type=1&theater\n\n";

                break;

            case null:

            default:
                $subject = "Snow request received";
                $message = "It is still snowing.\n\n";
        }

        $this->message = $message;
        $this->thing_report['email'] = $message;
    }

    public  function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks('snow');

        $this->choices = $choices;
        $this->thing_report['choices'] = $choices;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] =
            $this->agent_prefix . 'responding to the word snow';
        return $this->thing_report;
    }

    public function readSubject()
    {
        // Ignore subject.
    }

    public function snow()
    {
        // Stochastically call snow.
        if (rand(1, $this->variable) == 1) {
            $this->getSnowflake();
        }

        $this->thing->log(
            $this->agent_prefix .
                ' says, "Think that snow is falling everwhere\nall the time.\n"'
        );

    }
}

