<?php
/**
 * Start.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

// devstack refactor here as Start extends Agent.

class Start extends Agent
{
    /**
     *
     * @param Thing   $thing
     */
    public function init()
    {
        $this->thing_report["help"] = "Responds to an instruction to start.";

        $this->node_list = ["start" => ["new user", "opt-in", "opt-out"]];

        $this->verbosity = 1;

        $this->thing->flagGreen();
    }

    /**
     *
     */
    function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    /**
     *
     */
    function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables start " . $this->from
        );

        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(
            $this->agent_prefix . "loaded " . $this->counter . ".",
            "DEBUG"
        );
    }

    /**
     *
     */
    public function makeSMS()
    {
        switch ($this->counter) {
            case false:
                $sms =
                    "START | Carrier SMS rates apply. Read our Privacy Policy " .
                    $this->web_prefix .
                    "privacy. | TEXT PRIVACY";
                break;

            case 1:
                $sms =
                    "START | Thank you for starting to use " .
                    ucwords($this->word) .
                    ".  Read our Privacy Policy " .
                    $this->web_prefix .
                    "privacy | Started.";
                break;
            case 2:
                $sms =
                    "START | Read our Privacy Policy at " .
                    $this->web_prefix .
                    "privacy | Started. Again.";
                break;

            case null:

            default:
                $sms = "START | " . $this->web_prefix . "privacy | Started.";
        }

        if ($this->verbosity > 5) {
            $sms .= " | counter " . $this->counter;
        }
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/start";

        $this->node_list = [
            "start" => ["glossary", "new user", "privacy", "terms of use"],
        ];

        $this->makeChoices();

        $web = "<b>Start Agent</b>";
        $web .= "<p>";
        $web .= "<p>";

        $web .= "Text the word START to our BC SMS portal at (778) 401-2132.";

        $web .= "<br>";

        $web .= "<p>";

        $this->thing_report["web"] = $web;
    }

    /**
     *
     */
    public function makeEmail()
    {
        switch ($this->counter) {
            case 1:
                $subject = 'Start ' . $this->short_name . '?';

                $message = "So an action you took (or someone else took) opted you into " . $this->short_name . ".
                    <br>
                    There is always that little element of uncertainity.  So we clearly think
                    this is a good thing and are excited to start
                    making associations from your emails that (which?) we know will be
                    helpful or useful to you.
                    <br>
                    So thanks for that and be sure to keep an eye on your stack balance. Which
                    will be maintained at least until you opt-out.
                    <br>
                    Keep on stacking.

                    ";
                break;
            case 2:
                $subject = "Opt-in request accepted";

                $message =
                    "Thank you for your opt-in request.  'optin' has
                    added " .
                    $this->from .
                    " to the accepted list of ". $this->short_name . " emails.
                    You can now use " . $this->short_name . ".  Keep on stacking.\n\n";

                break;

            case null:

            default:
                $message = "START | Acknowledged. " . $this->web_prefix;
        }

        $this->message = $message;
        $this->thing_report["email"] = $message;
    }

    /**
     *
     */
    public function makeChoices()
    {
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "start"
        );
        $choices = $this->thing->choice->makeLinks("start");
        $this->thing_report["choices"] = $choices;
    }

    // devstack refactor out

    /**
     *
     */
    public function respondResponse()
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

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] =
            $this->agent_prefix . "responding to an instruction to start.";
    }

    public function isStart($text) {
       $aliases = ['start'];
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
        if ($this->isStart($this->input)) {
            $this->start();
        }
    }

    /**
     *
     */
    function start()
    {
        $this->counter = $this->counter + 1;

        // Call the Usermanager agent and update the state
        $agent = new Usermanager($this->thing, "usermanager start");
        $this->thing->log(
            $this->agent_prefix .
                'called the Usermanager and said "usermanager start".'
        );
        $timestamp = new Timestamp($this->thing, "timestamp");

        return;
    }
}
