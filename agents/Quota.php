<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Quota extends Agent
{
    public function init()
    {
        $quotas_list = [
            "railway",
            "block",
            "train",
            "daily",
            "hourly",
            "minute",
            "second",
        ];
        $quotas_list = ["daily"];

        $this->quota_name = "message_perminute";
        $this->quota_period = 60;

        $this->period_limit =
            $this->thing->container["api"]["quota"][
                $this->quota_name . "_limit"
            ];

        $this->node_list = ["quota" => ["opt-in"]];
    }

    function set()
    {
        $this->setFlag();
        $this->variables->setVariable("counter", $this->counter);
        $this->variables->setVariable("reset_at", $this->reset_at);
        $this->variables->setVariable("refreshed_at", $this->current_time);
    }

    function get()
    {
        $this->variables = new Variables(
            $this->thing,
            "variables quota_" . $this->quota_name . " " . $this->from
        );

        $this->counter = $this->variables->getVariable("counter");
        $this->reset_at = $this->variables->getVariable("reset_at");
        $this->refreshed_at = $this->variables->getVariable("refreshed_at");

        if ($this->counter == null) {
            $this->counter = 1;
        }
        if ($this->reset_at == null) {
            $this->reset_at = $this->current_time;
        }

        $this->thing->log(
            $this->agent_prefix . "loaded " . $this->counter . ".",
            "DEBUG"
        );

        $t = strtotime($this->current_time) - strtotime($this->reset_at);
        if ($t > $this->quota_period) {
            $this->quotaReset();
        }
    }

    function setFlag($colour = null)
    {
        if ($colour == null) {
            $colour = "red";
        }

        if ($this->counter <= $this->period_limit) {
            $colour = "green";
        } else {
            $colour = "red";
        }

        $this->flag_thing = new Flag(
            $this->thing,
            "flag_quota_" . $this->quota_name . " " . $colour
        );
        $this->flag = $this->flag_thing->state;

        // Is this do our set?,  Setting signals.
    }

    public function makeSMS()
    {
        $sms = "QUOTA | ";

//        $sms .= "flag " . strtoupper($this->flag_thing->state) . " ";
        $sms .=
            $this->quota_name .
            " " .
            $this->counter .
            " of " .
            $this->period_limit .
            "";

        switch (true) {
            case $this->counter > $this->period_limit:
                $sms .= " | ";
                $sms .= "Quota exceeded. | ";
                $sms .= "Text QUOTA RESET";
                break;
            case $this->counter > 0.5 * $this->period_limit:
                $sms .= " | ";
                $sms .= "Text QUOTA RESET";
                break;

            case true:
            default:
        }

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeEmail()
    {
        switch ($this->counter) {
            case 1:
            // drop through
            case 2:
            // drop through
            case null:
            // drop through
            default:
                $message =
                    "QUOTA | Acknowledged. " . $this->web_prefix . "privacy";
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
            "quota"
        );
        $choices = $this->thing->choice->makeLinks("quota");
        $this->thing_report["choices"] = $choices;
    }

    public function respondResponse()
    {
        if ($this->agent_input !== null) {
            return;
        }

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
            $this->agent_prefix . "manages stack quotas.";
    }

    public function readSubject()
    {
        // Process as User>Agent or as Thing>Agent?
        if ($this->agent_input == null) {
            $piece = strtolower($this->from . " " . $this->subject);
        } else {
            $piece = $this->agent_input;
        }

        // Check for other ideas in the message
        switch (true) {
            case strpos(strtolower($piece), "reset") !== false:
                // Match phrase within phrase
                $this->quotaReset();
                break;

            case strpos(strtolower($piece), "use") !== false:
                // Match phrase within phrase
                $this->quotaUse();
                break;

            case true:
                $this->quota();
            default:
        }
    }

    function quota()
    {
        if ($this->counter >= 10000) {
            $this->state = "Meep";
        }
    }

    function quotaReset()
    {
        $this->reset_at = $this->current_time;
        $this->counter = 1;
    }

    function quotaUse()
    {
        $this->counter += 1;
    }
}
