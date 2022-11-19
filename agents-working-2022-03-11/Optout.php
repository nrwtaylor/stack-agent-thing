<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Optout extends Agent
{
    public function init()
    {
        $this->node_list = ["opt-out" => ["opt-in", "unsubscribe"]];
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
            "variables optout " . $this->from
        );
        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );
        $this->counter = $this->counter + 1;
    }

    public function makeSMS()
    {
        switch ($this->counter) {
            case 1:
                $sms =
                    "OPT-OUT | Opted out. This channel is no longer authenticated. Text START.";
                break;
            case 2:
                $sms =
                    "OPT-OUT | Opted out. This channel is not authenticated. Text START.";
                break;

            case null:

            default:
                $sms = "OPT-OUT | Opted out.";
        }

        $sms .= " | counter " . $this->counter;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeEmail()
    {
        switch ($this->counter) {
            case 1:
                $subject = "Opt-out request received";

                $message =
                    "Thank you for your opt-out request.  'usermanager' can confirm 
                    that " .
                    $this->from .
                    " is no longer on the accepted list of ".$this->short_name." emails.
                    Click opt-in if you wish to use ".$this->short_name.".\n\n";

                break;

            case null:

            default:
                $subject = "Opt-out request received";

                $message =
                    "Thank you for your opt-out request.  'usermanager' can confirm
                  that " .
                    $this->from .
                    " is no longer on the accepted list of ". $this->short_name ." e-mails
                  Click opt-in if you wish to use " . $this->short_name . ".\n\n";
        }

        $this->message = $message;
        $this->thing_report["email"] = $message;
    }

    public function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks("opt-out");

        $this->choices = $choices;
        $this->thing_report["choices"] = $choices;
    }

    public function respondresponse()
    {
        // Thing actions

        // New user is triggered when there is no nom_from in the db.
        // If this is the case, then send out a response
        // which explains what this is and asks either
        // for a reply to the email, or to send an email to opt-in@<email postfix>.

        $this->thing->flagGreen();

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

    public function isOptout($text) {
       $aliases = ['optout','opt out','opt-out'];
       foreach($aliases as $alias) {
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
if ($this->isOptout($this->input)) { 
$this->optout();
}
}


    function optout()
    {
        // Call the Usermanager agent and update the state
        $agent = new Usermanager($this->thing, "usermanager optout");
        $this->thing->log(
            $this->agent_prefix .
                "called the Usermanager to update user state to optout."
        );
    }
}
