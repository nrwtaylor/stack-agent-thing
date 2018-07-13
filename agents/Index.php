<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from Tick

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Index
{
    public function __construct(Thing $thing, $agent_input = null)
    {
        if ($agent_input == null) {
            $this->agent_input = $agent_input;
        }

        $this->thing = $thing;
        $this->agent_name = 'index';
        $this->agent_prefix = '"Index" ' . ucwords($this->agent_name) . '" ';

        $this->thing_report['thing'] = $this->thing->thing;

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;
        //$this->sqlresponse = null;

        $this->pad_length = 4;

        $this->node_list = array("index"=>array("index"));

        $this->thing->log('Agent "Index" running on Thing '. $this->thing->nuuid . '.');

        $this->variables_agent = new Variables($this->thing, "variables index " . $this->from);
        $this->current_time = $this->thing->time();
        $this->get();
        $this->readSubject();

        // frame

        //$this->variable = 1;
        //$this->snow();

        // frame

        $this->padIndex();

        $this->set();
        if ($this->agent_input == null) {
            $this->respond();
        }

        $this->thing->flagGreen();

        $this->thing->log($this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.');

        $this->thing_report['etime'] = number_format($this->thing->elapsed_runtime());
        $this->thing_report['log'] = $this->thing->log;

        return;
    }

    public function set()
    {
        $this->variables_agent->setVariable("index", $this->index);
        $this->variables_agent->setVariable("refreshed_at", $this->current_time);

        return;
    }


    public function get()
    {
        $this->index = $this->variables_agent->getVariable("index");
        $this->refreshed_at = $this->variables_agent->getVariable("refreshed_at");

        $this->thing->log($this->agent_prefix .  'loaded ' . $this->index . ".");

        return;
    }

    function padIndex()
    {
        $this->index_padded = str_pad($this->index, $this->pad_length, "0", STR_PAD_LEFT);

    }

   public function assertIndex($n)
    {
        if (!isset($n)) {$this->get(); $n = $this->index;}

        // devstack count snowflakes on stack identity
        // This is a count of all snow everywhere.
        $this->index = $n;
    }

   public function resetIndex()
    {
        // devstack count snowflakes on stack identity
        // This is a count of all snow everywhere.
        $this->index = 1;
    }


    public function incrementIndex()
    {
        if (!isset($this->index)) {$this->get();}

        // devstack count snowflakes on stack identity
        // This is a count of all snow everywhere.
        $this->index += 1;
    }


    private function makeSMS()
    {
        switch ($this->index) {
            case 1:
                $sms = "INDEX | Index is one.";
                break;
            case 2:
                $sms = "INDEX | Index is two.";
                break;
            case null:
            default:
                $sms = "INDEX";

        }

        $sms .= " | " . $this->index_padded;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    private function makeEmail()
    {
        switch ($this->index) {
            case 1:
                $subject = "Index request received";
                $message = "Index is " . $this->index . ".\n\n";

                break;

            case null:

            default:
               $subject = "Index request received";
               $message = "Index is " . $this->index . ".\n\n";
        }

        $this->message = $message;
        $this->thing_report['email'] = $message;
    }

    private function makeChoices()
    {
        $choices = $this->thing->choice->makeLinks('index');

        $this->choices = $choices;
        $this->thing_report['choices'] = $choices;
    }

    public function respond()
    {
        // Thing actions
        $this->thing->flagGreen();

        // Get the current user-state.
        $this->makeSMS();
        $this->makeEmail();
        $this->makeChoices();

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
        //$this->thing_report['sms'] = $this->sms_message;

        // While we work on this
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] = $this->agent_prefix . 'providing the current index.';
        return $this->thing_report;
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $keywords = array('index','next','last', '+', 'plus','reset');
        $pieces = explode(" ", strtolower($input));

        // Don't read.
        if ($this->agent_input == "index") {return;}
        // See if there is just one number provided
        $number_agent = new Number($this->thing, $input);
        // devstack number
        if ($number_agent->number != false) {
            $this->assertIndex($number_agent->number);
            return;
        }


        // So this is really the 'sms' section
        // Keyword
        $pieces = explode(" ", strtolower($input));
        if (count($pieces) == 1) {
            if ($input == 'index') {return;}
        }

        foreach ($pieces as $key=>$piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece),$command) !== false) {
                    switch($piece) {
                        case 'next':   
                        case 'increment':
                        case '+':
                            $this->incrementIndex();
                            return;
                        case 'reset':
                            $this->resetIndex();
                            return;
                        default:
                            // Could not recognize a command.
                            // Drop through
                    }
                }
            }
        }

        // Ignore subject.
        return;
    }

    public function index()
    {
        $this->thing->log($this->agent_prefix .' says, "Keeping an index\n\n"');

        return;
    }
}
