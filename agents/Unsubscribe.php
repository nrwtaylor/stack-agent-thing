<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Unsubscribe extends Agent
{
    public function init()
    {
        $this->node_list = ["unsubscribe" => ["opt-in", "start"]];
        $this->verbosity = 1;
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
            "variables unsubscribe " . $this->from
        );

        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(
            "has been run " . $this->counter . " times.",
            "DEBUG"
        );

        $this->counter = $this->counter + 1;
    }

    public function makeSMS()
    {
        switch ($this->counter) {
            case 1:
                $sms =
                    "UNSUBSCRIBE | This device has been unsubscribed from Stackr.  Read our Privacy Policy " .
                    $this->web_prefix .
                    "policy";
                break;
            case 2:
                $sms =
                    "UNSUBSCRIBE | Unsubscribed. " .
                    $this->web_prefix .
                    "privacy";
                break;

            case null:

            default:
                $sms =
                    "UNSUBSCRIBE | Unsubscribed. " .
                    $this->web_prefix .
                    "privacy";
        }
        if ($this->verbosity > 5) {
            $sms .= " | counter " . $this->counter;
        }
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeEmail()
    {
        switch ($this->counter) {
            case 1:
                $subject = "Unsubscribe request";

                $message =
                    "You have been unsubscribed from Stackr.
                    <br>
                    Email start" .
                    $this->mail_postfix .
                    " to start again.
                    <br>

                    ";
                break;

            case null:

            default:
                $message =
                    "Unsubscribe acknowledged.  " .
                    $this->web_prefix .
                    "privacy";
        }

        $this->message = $message;
        $this->thing_report["email"] = $message;
    }

    public function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "unsubscribe"
        );
        $choices = $this->thing->choice->makeLinks("unsubscribe");

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
            $this->agent_prefix .
            "responding to an instruction to unsubscribe.";
    }

    public function isUnsubscribe($text)
    {
        $aliases = ["unsubscribe"];
        foreach ($aliases as $alias) {
            if (trim(strtolower($text)) === $alias) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     */
    public function readSubject()
    {
        if ($this->isUnsubscribe($this->input)) {
            $this->unsubscribe();
        }
    }

    function unsubscribe()
    {
        // Call the Usermanager agent and update the state
        $agent = new Usermanager($this->thing, "usermanager unsubscribe");
        $this->thing->log(
            "called the Usermanager to update user state to unsubscribe."
        );
    }
}
