<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Bar
{
    function __construct(Thing $thing, $agent_input = null)
    {
        // Ticks are just a sub-division of a bar.
        // Tick variable = 15 minutes

        // Play a bar when asked.

        $this->agent_name = 'bar';
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
        $this->test= "Development code";

        $this->agent_input = $agent_input;

        $this->thing = $thing;

        $this->thing_report['thing']  = $thing;
        $this->start_time = $this->thing->elapsed_runtime();

        // Thing stuff
        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log($this->agent_prefix . "received this Thing ".  $this->subject . '".');

        //$this->value_destroyed = 0;
        //$this->things_destroyed = 0;

        $this->stack_idle_mode = 'use'; // Prevents stack generated execution when idle.
        $this->cron_period = $this->thing->container['stack']['cron_period'];
        $this->start_time = $this->thing->elapsed_runtime();


        $this->variables = new Variables($this->thing, "variables tick " . $this->from);
        $this->current_time = $this->thing->time();

        $this->get();
        $this->readSubject();

        $this->thing->log($this->agent_prefix . "called Tallycounter.");


        $this->max_bar_count = 4;

        $this->response ="";

        $this->doBar();

// devstack bring in settings variable
//        if ($this->bar_count > 8) {$this->bar_count = 0;}

        $this->set();

        if ($this->agent_input == null) {
            $this->respond();
        }

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format( $this->thing->elapsed_runtime() - $this->start_time ) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;
    }

    function set()
    {

        $this->thing->json->writeVariable(array("bar",
            "refreshed_at"),  $this->thing->json->time()
            );

        $this->thing->json->writeVariable(array("bar",
            "count"),  $this->bar_count
            );



        $this->variables->setVariable("count", $this->bar_count);
        $this->variables->setVariable("refreshed_at", $this->current_time);
    }

    function get()
    {
        $this->bar_count = $this->variables->getVariable("count");
        $this->refreshed_at = $this->variables->getVariable("refreshed_at");

        $this->thing->log( $this->agent_prefix .  'loaded ' . $this->bar_count . ".", "DEBUG");

        $this->bar_count = $this->bar_count + 1;
    }

    function respond()
    {
        $this->makeSMS();
    }

    function makeSMS()
    {
        $this->sms_message = "BAR";
        $this->sms_message .= " | " . $this->bar_count . " " . $this->response;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function readSubject() {}

    function doBar($depth = null)
    {
        if ($this->bar_count > $this->max_bar_count) {
            $this->bar_count = 0;

            $this->response .= "Reset bar count. ";

        }

        $this->thing->log($this->agent_prefix . "called Tallycounter.");

        $thing = new Thing(null);
        $thing->Create(null,"tallycounter", 's/ tallycounter message');
        $tallycounter = new Tallycounter($thing, 'tallycounter message tally@stackr.ca');

        $this->response .= "Did a tally count. ";

//        $tallycounter = new Tallycounter($this->thing, 'tallycounter message tally@stackr.ca');

        if ($this->bar_count == 1) {

//            $stack_thing = new Stack($this->thing);

            $thing = new Thing(null);
            $thing->Create(null,"stack", 's/ stack count');
            $stackcount = new Stack($thing, 'stack count');

            $this->response .= "Did a stack count. ";

        }

//        echo $tallycounter->count;

    }

}

?>
