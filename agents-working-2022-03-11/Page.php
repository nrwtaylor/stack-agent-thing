<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from Tick

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Page extends Agent
{
    public function init()
    {
    }

    public function set()
    {
        if ($this->page != $this->previous_page) {
            $this->variables_agent->setVariable("page", $this->page);

            $this->current_time = $this->thing->time();

            $this->variables_agent->setVariable(
                "refreshed_at",
                $this->current_time
            );
        }
    }

    public function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables page " . $this->from
        );

        $this->page = $this->variables_agent->getVariable("page");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );
        $this->thing->log($this->agent_prefix . "loaded " . $this->page . ".");

        $this->previous_page = $this->page;
    }

    function padPage()
    {
        $this->page_padded = str_pad(
            $this->page,
            $this->pad_length,
            "0",
            STR_PAD_LEFT
        );
    }

    public function assertPage($n)
    {
        if (!isset($n)) {
            $this->get();
            $n = $this->page;
        }

        // devstack count snowflakes on stack identity
        // This is a count of all snow everywhere.
        $this->page = $n;
    }

    public function resetPage()
    {
        // devstack count snowflakes on stack identity
        // This is a count of all snow everywhere.
        $this->page = 1;
    }

    public function incrementPage()
    {
        if (!isset($this->page)) {
            $this->get();
        }

        // devstack count snowflakes on stack identity
        // This is a count of all snow everywhere.
        $this->page += 1;
    }

    public function makeSMS()
    {
        switch ($this->page) {
            case 1:
                $sms = "PAGE | Book started.";
                break;
            case 2:
                $sms = "PAGE | Page number is two.";
                break;
            case null:
            case false:
                $sms = "PAGE | Book is closed.";
                break;
            default:
                $sms = "PAGE";
        }

        if ($sms != false) {
            $sms .= " | " . $this->page;
        }

        $sms .= " | " . $this->refreshed_at;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeEmail()
    {
        switch ($this->page) {
            case 1:
                $subject = "Page request received";
                $message = "Page is " . $this->page . ".\n\n";

                break;

            case null:

            case false:
                $subject = "Page request received";
                $message = "Book closed";

            default:
                $subject = "Page request received";
                $message = "Page is " . $this->page . ".\n\n";
        }

        $this->message = $message;
        $this->thing_report["email"] = $message;
    }

    public function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks("page");

        $this->choices = $choices;
        $this->thing_report["choices"] = $choices;
    }

    public function respondResponse()
    {
        if ($this->agent_input != null) {
            return;
        }

        $this->thing->flagGreen();

        // Thing actions
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["email"] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] =
            $this->agent_prefix . "providing the current page.";
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $keywords = ["page", "next", "last", "+", "plus", "reset"];
        $pieces = explode(" ", strtolower($input));

        // Don't read.
        if ($this->agent_input == "page") {
            return;
        }
        // See if there is just one number provided
        $number_agent = new Number($this->thing, $input);
        // devstack number
        if ($number_agent->number != false) {
            $this->assertPage($number_agent->number);
            return;
        }

        // So this is really the 'sms' section
        // Keyword
        $pieces = explode(" ", strtolower($input));
        if (count($pieces) == 1) {
            if ($input == "page") {
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "next":
                        case "increment":
                        case "+":
                            $this->incrementPage();
                            return;
                        case "reset":
                            $this->resetPage();
                            return;
                        default:
                        // Could not recognize a command.
                        // Drop through
                    }
                }
            }
        }

        // Ignore subject.
    }

    public function page()
    {
        $this->thing->log(
            $this->agent_prefix .
                ' says, "Keeping track of the page number\n\n"'
        );
    }
}
