<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from Tick

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Thought extends Agent
{
    function init()
    {
        $this->node_list = array("snow" => array("stop", "snow"));

        $this->current_time = $this->thing->json->time();

        $this->variable = 10; //s

        $this->thing_report['help'] = $this->agent_prefix . 'had a thought.';
        $this->thing_report['infop'] = 'Deep thoughts.';
    }

    public function run()
    {
        $this->thought();
    }

    public function makeSMS()
    {
        switch (rand(1, 3)) {
            case 1:
                $sms = "THOUGHT";
                break;
            case 2:
                $sms = "THOUGHT";
                break;

            case null:

            default:
                $sms = "THOUGHT";
        }

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function makeEmail()
    {
        switch (rand(1, 3)) {
            case 1:
                $subject = "Thought request received";
                $message = "Thought.\n\n";
                break;

            case null:

            default:
                $subject = "Thought request received";
                $message = "Thought.\n\n";
        }

        $this->message = $message;
        $this->thing_report['email'] = $message;
    }

    public function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks('thought');

        $this->choices = $choices;
        $this->thing_report['choices'] = $choices;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['sms'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    public function readSubject()
    {
        // Ignore subject.
    }

    function thought()
    {
        // Reminder?
        $thingreport = $this->thing->db->reminder(
            $this->from,
            array('s/', 'stack record'),
            array('ant', 'email', 'transit', 'translink')
        );
        $things = $thingreport['thing'];

        $thing = $things[array_rand($things)];
        $uuid = $thing['uuid'];

        $this->thought = $thing['task'];

        sleep(rand(1, $this->variable));

        $this->thing->log($this->agent_prefix . ' says, "Thought."');
    }
}
