<?php
namespace Nrwtaylor\StackAgentThing;

// Start by picking a random thing and seeing what needs to be done.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Poll extends Agent
{
    function init()
    {
        //	function __construct(Thing $thing)
        //		$this->thing = $thing;
        //		$this->agent_name = 'start';
        //      $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';

        //        $this->thing_report['thing'] = $this->thing->thing;

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        //      $this->uuid = $thing->uuid;
        //    $this->to = $thing->to;
        //  $this->from = $thing->from;
        //$this->subject = $thing->subject;

        $agent_list = ["yes", "maybe", "no"];
        // $agent_name = $agent_list[array_rand($agent_list)];
        shuffle($agent_list);

        $this->node_list = [
            "poll" => $agent_list,
            "yes" => "thanks",
            "maybe" => "thanks",
            "no" => "thanks",
            "thanks" => "results",
        ];

        //      $this->thing->log( $this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.</pre>', "INFORMATION");

        $this->variables_agent = new Variables(
            $this->thing,
            "variables poll " . $this->from
        );

        //      $this->current_time = $this->thing->json->time();

        $this->verbosity = 1;

        //      $this->get();
        //	$this->readSubject();

        //    $this->set();
        // 		$this->respond();

        $this->thing->flagGreen();

        //     $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.', "OPTIMIZE" );
        //
        //      $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        //    $this->thing_report['log'] = $this->thing->log;

        //	return;
    }

    public function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    public function get()
    {
        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(
            $this->agent_prefix . 'loaded ' . $this->counter . ".",
            "DEBUG"
        );

        $this->counter = $this->counter + 1;
    }

    public function makeSMS()
    {
        switch ($this->counter) {
            case 1:
                $sms =
                    "POLL | What is your answer to the question.  Read our Privacy Policy https://stackr.ca/policy";
                break;
            case 2:
                $sms =
                    "POLL | Stackr started. Read our Privacy Policy at https://stackr.ca/privacy";
                break;

            case null:

            default:
                $sms =
                    "POLL | What is your answer to the question.  https://stackr.ca/privacy";
        }

        //if ($this->from == "null@stackr.ca") {
        $sms = "POLL | Here is a question.";
        //}

        //if ($this->verbosity > 5) {
        $sms .= " | counter " . $this->counter;
        //}
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function isNominal()
    {
        if ($this->from == "null@stackr.ca" or $this->from == "web@stackr.ca") {
            return false;
        }
        return true;
    }

    function makeWeb()
    {
        //if (!$this->isNominal()) {
        //    $web = "NO POLL FOUND";
        //}

        $web = "Here is a question.";
        $this->web_message = $web;
        $this->thing_report['web'] = $web;
    }

    public function makeEmail()
    {
        switch ($this->counter) {
            case 1:
                $subject = "Poll XXXX";

                $message = "So somebody sent you this Poll.
                    <br>
                    So they had a question.  And the answers are the 
                    buttons.
                    <br>
                    So thanks for taking a moment to choose which button(s) to press.
                    <br>
                    Watch the stack.
                    <br>
                    Thanks.
                    ";
                break;
            case 2:
                $subject = "Poll XXXX" . strtoupper($this->thing->nuuid);

                $message = "Thank you for your poll response.  'poll' has 
                    counted your input.  Keep on stacking.\n\n";

                break;

            case null:

            default:
                $message =
                    "Poll XXXX | Acknowledged.  https://stackr.ca/privacy";
        }

        $this->message = $message;
        $this->thing_report['email'] = $message;
    }

    public function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "poll"
        );
        $choices = $this->thing->choice->makeLinks('poll');
        // $choices = false;

        //if ($this->from == "null@stackr.ca") {
        //    $choices = $this->thing->choice->makeLinks("agents");
        //}

        $this->thing_report['choices'] = $choices;
    }

    public function respondResponse()
    {
        // Thing actions

        // New user is triggered when there is no nom_from in the db.
        // If this is the case, then Stackr should send out a response
        // which explains what stackr is and asks either
        // for a reply to the email, or to send an email to opt-in@stackr.co.

        $this->thing->flagGreen();

        //$this->makeSMS();
        //$this->makeEmail();
        $this->makeChoices();
        //$this->makeWeb();
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['sms'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] =
            $this->agent_prefix . 'responding to a poll of some sort.';
    }

    public function readSubject()
    {
        $this->start();
    }

    public function start()
    {
    }
}
