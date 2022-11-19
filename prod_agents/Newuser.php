<?php
// Start by picking a random thing and seeing what needs to be done.
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Newuser extends Agent
{
    public function init()
    {
        $this->thing_report["help"] =
            "Responds to an instruction about a new user.";

        $this->node_list = ["new user" => ["opt-in"]];
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

        $this->counter = $this->counter + 1;
    }

    public function makeSMS()
    {
        switch ($this->counter) {
            case 1:
                $sms =
                    "NEW USER | This service is provided in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.";
                $sms .= " | Text OPT-IN";
                break;
            case 2:
                $sms =
                    "NEW USER | This service is provided in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.";
                $sms .= " | Text OPT-IN";
                break;
            case null:

            default:
                $sms =
                    "NEW USER | This service is provided in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.";
        }

        $sms .= " | counter " . $this->counter;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeEmail()
    {
        switch ($this->counter) {
            case 1:
                $subject =
                    "Review and accept " .
                    $this->short_name .
                    "'s Terms and Conditions of Use";

                $message =
                    "Thank you for your recent email to " .
                    $this->short_name .
                    ".  'usermanager' 
                    saw that you haven't yet accepted the " .
                    $this->short_name .
                    " Terms and 
                    Conditions of use.  If you wish to opt in to using " .
                    $this->short_name .
                    ", please either reply to
                    this email or send an email to opt-in@" .
                    $this->mail_postfix .
                    ".\n\n
                    This service is provided in the hope that it will be useful, but 
                    WITHOUT ANY WARRANTY; without even the implied warranty of 
                    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.\n\n" .
                    $this->short_name .
                    " provides a range of email based tools to get things done.\n\n" .
                    $this->short_name .
                    " will ignore further e-mails from this address.";

                break;
            case null:

            default:
                $message =
                    "NEW USER | Acknowledged. " . $this->web_prefix . "privacy";
        }
        $this->message = $message;
        $this->thing_report["email"] = $message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/start";

        $this->node_list = ["new user" => ["glossary", "warranty"]];

        $this->makeChoices();

        $web = "<b>New User Agent</b>";
        $web .= "<p>";
        $web .= "<p>";

        $web .=
            "Use your messaging service to send text messages to this stack. Try some of the words in the GLOSSARY.";

        $web .= "<br>";

        $web .= "<p>";

        $this->thing_report["web"] = $web;
    }

    public function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "new user"
        );
        $choices = $this->thing->choice->makeLinks("new user");
        $this->thing_report["choices"] = $choices;
    }

    public function respondResponse()
    {
        // Thing actions

        // New user is triggered when there is no nom_from in the db.
        // If this is the case, then a response should be sent
        // which explains what stackr is and asks either
        // for a reply to the email, or to send an email to opt-in@web_postfix.

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
            $this->agent_prefix . "responding to an instruction to new user.";
    }

    public function isNewuser($text) {
       $aliases = ['newuser', 'new user'];
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
        if ($this->isNewuser($this->input)) {
            $this->newuser();
        }
    }

    function newuser()
    {
        // Call the Usermanager agent and update the state
        $agent = new Usermanager($this->thing, "usermanager newuser");
        $this->thing->log(
            $this->agent_prefix .
                "called the Usermanager to update user state to new user.",
            "INFORMATION"
        );
    }
}
