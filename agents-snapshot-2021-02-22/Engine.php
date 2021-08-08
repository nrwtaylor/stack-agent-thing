<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Engine extends Agent
{
    function init()
    {

        if ($this->thing != true) {
            $this->thing->log(
                'ran on a null Thing ' . $this->thing->uuid . '.'
            );
            $this->thing_report['info'] =
                'Tried to run Engine on a null Thing.';
            $this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;
        }

        $this->agent_version = 'redpanda';

        $this->node_list = array(
            'engine' => array('privacy'),
            'privacy' => array('retention', 'persistence'),
            'warranty' => array('helpful', 'useful?')
        );

        $this->thing_report['info'] = 'This is the engine agent.';
        $this->thing_report['help'] =
            'This agent reports on the engine(s) running.  See Github.';
    }

    public function get()
    {
        $this->getEngine();
    }

    public function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            array("engine", "refreshed_at"),
            gmdate("Y-m-d\TH:i:s\Z", time())
        );
    }

    public function respondResponse()
    {

        $this->thing->flagGreen();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        return $this->thing_report;
    }

    function getEngine()
    {

        $path =
            $this->resource_path .
            '../vendor/nrwtaylor/stack-agent-thing/composer.json';

        $file = @file_get_contents($path);

        if ($file === false) {
            // handle error here... }
            $this->thing->log("Agent 'Engine' says, " . '"Could not find the engine file."');
        }


        $data = json_decode($file, true);

        $this->engine_string = $data['version'] . " " . $data['description'];

    }

    function makeSMS()
    {
        $this->sms_message = "ENGINE | No engine found.";
        $this->sms_message = "ENGINE " . $this->engine_string;

        $this->sms_message .= " | TEXT WARRANTY";
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
        $status = true;
        return $status;
    }

    function makeChoices()
    {
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "engine"
        );
        $choices = $this->thing->choice->makeLinks('engine');

        $this->thing_report['choices'] = $choices;
    }

    public function makePDF()
    {
        $this->thing->report['pdf'] = false;
        return;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'web/' . $this->uuid . '/thing';

        $this->node_list = array("engine" => array("privacy", "warranty"));

        $web = "";

        $web .= 'This Thing said it heard, "' . $this->subject . '".<br>';
        $web .= $this->sms_message . "<br>";

        $received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time(time() - $received_at);
        $web .= "About " . $ago . " ago.";

        $web .= "<br>";
        $this->thing_report['web'] = $web;
    }
}
