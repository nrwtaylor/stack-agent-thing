<?php
namespace Nrwtaylor\StackAgentThing;

class Offensive extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->initOffensive();
    }

    function initOffensive()
    {
        $this->thing->offensive_librex_handler = new Librex(
            $this->thing,
            "librex"
        );
        $this->thing->offensive_librex_handler->getLibrex(
            "offensive/bad-words"
        );
    }

    function run()
    {
        $this->doOffensive();
    }

    public function doOffensive()
    {
        if ($this->agent_input == null) {
            $response = "OFFENSIVE | " . $this->response;

            $this->offensive_message = $response;
        } else {
            $this->offensive_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This checks for offensiveness.";
        $this->thing_report["help"] = "This is about not being offensive.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = ["offensive" => ["offensive", "unoffensive"]];
        $this->sms_message = "" . $this->offensive_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    public function hasOffensive($text = null)
    {
        if ($text === null) {
            return false;
        }
        if ($text === "") {
            return false;
        }

        $tokens = explode(" ", $text);
        foreach ($tokens as $i => $token) {
            $this->thing->offensive_librex_handler->matchesLibrex($token);
            $matches = $this->thing->offensive_librex_handler->matches;

            if (count($matches) > 0) {
                return true;
            }
            return false;
        }
    }

    public function isOffensive($text = null)
    {
        if ($text === null) {
            return false;
        }
        if ($text === "") {
            return false;
        }

        $this->thing->offensive_librex_handler->matchesLibrex($text);
        $matches = $this->thing->offensive_librex_handler->matches;

        if (count($matches) > 0) {
            return true;
        }
        return false;
    }

    public function readSubject()
    {
        $input = $this->assert($this->input, "offensive", false);
        $response = $this->hasOffensive($input);

        if ($response === true) {
            $this->response .= "Probably. ";
        } else {
            $this->response .= "Probably not. ";
        }
        return false;
    }
}
