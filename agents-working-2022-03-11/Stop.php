<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Stop extends Agent
{
    public function init()
    {
        $this->node_list = ["stop" => ["start", "opt-in"]];

        $this->thing->flagGreen();
    }

    function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables stop " . $this->from
        );
        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(
            $this->agent_prefix . "loaded " . $this->counter . "."
        );

        $this->counter = $this->counter + 1;
    }

    public function makeSMS()
    {
        switch ($this->counter) {
            case 1:
                $sms = "STOP | Stackr stopped.  Text START.";
                break;

            case null:

            default:
                $sms = "STOP | Stopped.";
        }

        $sms .= " | counter " . $this->counter;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeEmail()
    {
        switch ($this->counter) {
            case 1:
                $subject = "Stop Stackr?";

                $message = "So an action you took (or someone else took) stopped Stackr.
                    <br>
                    ";
                break;

            case null:

            default:
                $message =
                    "STOP | Acknowledged. " . $this->web_prefix . "privacy";
        }

        $this->message = $message;
        $this->thing_report["email"] = $message;
    }

    public function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks("stop");

        $this->choices = $choices;
        $this->thing_report["choices"] = $choices;
    }

    public function respondResponse()
    {
        // Thing actions

        // New user is triggered when there is no nom_from in the db.
        // If this is the case, then Stackr should send out a response
        // which explains what stackr is and asks either
        // for a reply to the email, or to send an email to opt-in@<email postfix>.

        $this->thing->flagGreen();

        // Get the current user-state.

        $this->makeChoices();

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["sms"] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] =
            $this->agent_prefix . "responding to an instruction to opt-in.";
    }

    public function readSubject()
    {
        $this->stop();
    }

    function stop()
    {
        // Call the Usermanager agent and update the state
        $agent = new Usermanager($this->thing, "usermanager stop");
        $this->thing->log(
            $this->agent_prefix .
                "called the Usermanager to update user state to stop."
        );
    }
}
