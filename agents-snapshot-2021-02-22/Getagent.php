<?php
namespace Nrwtaylor\StackAgentThing;

// Call regularly from cron
// On call determine best thing to be addressed.

// Start by picking a random thing and seeing what needs to be done.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Getagent extends Agent
{
    function init()
    {
        //
        $this->agent_version = 'redpanda';

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = ['start' => []];
    }

    public function get()
    {
        $this->getLink();
        $this->readAgent();
    }

    public function set()
    {
        if ($this->thing != true) {
            //print "falsey";

            $this->thing->log(
                '<pre> Agent "Getagent" ran on a null Thing ' .
                    $this->uuid .
                    '</pre>'
            );
            $this->thing_report['info'] = 'Tried to run Web on a null Thing.';
            $this->thing_report['help'] = "That isn't going to work";

            return $this->thing_report;
        }
    }

    public function getLink($text = null)
    {
        $block_things = [];
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, 'thing');

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        //$this->thing->log('Agent "Block" found ' . count($findagent_thing->thing_report['things']) ." Block Things.");

        $this->max_index = 0;

        $match = 0;

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {
            $this->thing->log(
                $block_thing['task'] .
                    " " .
                    $block_thing['nom_to'] .
                    " " .
                    $block_thing['nom_from']
            );

            // I can't remember why I screen for usermanager here.  But
            // it
            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                if ($match == 2) {
                    break;
                }
            }
        }

        return $this->link_uuid;
    }

    function readAgent($link_uuid = null)
    {
        if ($link_uuid == null) {
            $link_uuid = $this->link_uuid;
        }

        $this->prior_thing = new Thing($link_uuid);

        // Returns the same but only prior to message.

        //    $prior_agent = new Callagent($this->prior_thing, "getagent");
        //    $prior_agent->callAgent($link_uuid);

        $variables = $this->prior_thing->variables;
        $agent = "Null";
        if (isset($variables->array_data['message'])) {
            $agent = $variables->array_data['message']['agent'];
        }
        $this->agent_name = $agent;
    }

    public function respondResponse()
    {
        // Thing actions

        $sms = "GETAGENT | " . ucwords($this->agent_name);
        $this->thing_report['sms'] = $sms;

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["getagent", "received_at"],
            gmdate("Y-m-d\TH:i:s\Z", time())
        );

        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report['choices'] = $choices;

        $this->thing_report['info'] =
            'This is gets the name of the agent that last ran on the last Thing.';
        $this->thing_report['help'] = 'This give the Agent name.';

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    public function readSubject()
    {
        //$this->defaultButtons();

        $status = true;
        return $status;
    }
}
