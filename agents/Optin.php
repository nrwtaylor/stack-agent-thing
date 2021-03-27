<?php

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Optin extends Agent
{
    public function init()
    {
        $this->node_list = ["opt-in" => ["new user", "opt-out", "unsubscribe"]];

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
            "variables optin " . $this->from
        );

        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log('Agent "Optin" loaded ' . $this->counter . ".");

        $this->counter = $this->counter + 1;
    }

    public function makeSMS()
    {
        switch ($this->counter) {
            case 1:
                $sms =
                    "OPT-IN | Thank you for opting into " .
                    $this->short_name .
                    ". " .
                    $this->web_prefix .
                    " | Opted-in.";
                break;
            case 2:
                $sms =
                    "OPT-IN | Read our Privacy Policy at " .
                    $this->web_prefix .
                    "privacy | Opted-in.";
                break;

            case null:

            default:
                $sms =
                    "OPT-IN | " . $this->web_prefix . "privacy | Acknowledged.";
        }

        $sms .= " | counter " . $this->counter;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function makeEmail()
    {
        switch ($this->counter) {
            case 1:
                $subject = "Well hello?";

                $message = "So an action you took (or someone else took) opted you into 
                    Stackr.
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
                    " to the accepted list of Stackr emails.
                    You can now use Stackr.  Keep on stacking.\n\n";

                break;

            case null:

            default:
                $message =
                    "OPT-IN | Acknowledged.  " . $this->web_prefix . "privacy";
        }

        $this->message = $message;
        $this->thing_report['email'] = $message;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/start';

        $this->node_list = ["new user" => ["glossary", "warranty"]];

        $this->makeChoices();

        $web = "<b>Opt-in Agent</b>";
        $web .= "<p>";
        $web .= "<p>";

        $web .= "Text OPTIN in your text channel.";

        $web .= "<br>";

        $web .= "<p>";

        $this->thing_report['web'] = $web;
    }

    public function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks('opt-in');

        $this->choices = $choices;
        $this->thing_report['choices'] = $choices;
    }

    public function respondResponse()
    {
        // Thing actions

        // New user is triggered when there is no nom_from in the db.
        // If this is the case, then Stackr should send out a response
        // which explains what stackr is and asks either
        // for a reply to the email, or to send an email to opt-in@<email postfix>.

        $this->thing->json->setField("settings");
        $this->thing->json->writeVariable(
            [$this->agent_name, "opt-in", "received_at"],
            date("Y-m-d H:i:s")
        );

        $this->thing->flagGreen();

        // Get the current user-state.

        //    $this->makeSMS();
        //  $this->makeEmail();
        //   $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['sms'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] =
            'Agent "Optin" responding to an instruction to opt-in.';
        //    $this->makeWeb();

        //		return;
    }

    public function isOptin($text) {
       $aliases = ['optin','opt in','opt-in'];
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
if ($this->isOptin($this->input)) {
$this->optin();
}
}


    function optin()
    {
        // Call the Usermanager agent and update the state
        $agent = new Usermanager($this->thing, "usermanager optin");
        $this->thing->log(
            $this->agent_prefix .
                'called the Usermanager to update user state to optin.'
        );
    }
}
