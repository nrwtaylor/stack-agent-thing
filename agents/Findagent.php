<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// Capitalization?

class Findagent extends Agent
{
    public $var = 'hello';

    public function init()
    {
        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }

        $this->retain_for = 4; // Retain target 4 hours.
        $this->time_units = "hrs";

        $this->verbosity = 1;

        // I don't think either of these variables are used.
        $this->sms_message = "";
        $this->response = false;

        $this->horizon = 99;
        $this->requested_agent_name = 'thing';

        $this->readInstruction();

        // Allow for a new state tree to be introduced here.

        $ref_time = microtime(true);

        $this->node_list = [
            "start" => [
                "listen" => ["say hello" => ["listen"]],
                "new group" => ["say hello"],
            ],
        ];

        $this->choices = false;

        $this->thing_report['info'] =
            '"Find Agent" looks for matching previous Things.';
        $this->thing_report['help'] = 'This is the "Find Agent" manager.';
        $this->thing_report['num_hits'] = $this->num_hits;

        if ($this->verbosity >= 2) {
            $this->thing->log(
                'returned ' . count($this->thing_report['things']) . ' Things.',
                "DEBUG"
            );
        }
    }

    function findAgent($name = null, $id = null)
    {
        $this->thing->log("findAgent called " . "name " .$name . " id " . $id . ".", "DEBUG");
        $ref_time = $this->thing->elapsed_runtime();

        // Search for a reference within the variables field to the agent.

        $things = [];
        if (isset($this->thing->db)) {
            $thingreport = $this->thing->db->setUser($this->from);
            $thingreport = $this->thing->db->variableSearch(
                null,
                $name,
                $this->horizon
            );
            $things = $thingreport['things'];
        }

        $run_time = $this->thing->elapsed_runtime() - $ref_time;
        $this->thing->log('findAgent db call for ' . $name . ' took ' . $run_time . 'ms.', "OPTIMIZE");

        $groups = [];
        $agent_things = [];
        $this->thing->log('Scanning ' . count($things) . ' Things.');
        foreach ($things as $thing_obj) {
            if ($id == null) {
                // No id matching, just grab thing
                $agent_things[] = $thing_obj;
            } else {
                $uuid = $thing_object['uuid'];

                if ($thing_object['nom_to'] != "usermanager") {
                    $match += 1;

                    $variables_json = $thing_obj['variables'];
                    $variables = $this->thing->json->jsontoArray(
                        $variables_json
                    );

                    if (isset($variables[$name])) {
                        $agent_thing_id = $variables[$name][$name . "_id"];
                    } else {
                        // No alias variable set
                        // Try the next one.
                        $agent_thing_id = null;
                        //break;
                    }
                }

                if (!($agent_thing_id == false) or $agent_thing_id == null) {
                    $agent_things[] = $thing_obj;
                }
            }
        }

        if (count($agent_things) == 0) {
            $this->response .= "No agent thing found.";
            //$this->sms_message .= "";
            //$this->sms_message .= " | No agent thing found.";
            $this->thing_report['things'] = true;
        } else {
            $this->agent_thing_id = $agent_things[0];
            //$this->sms_message .=
                ' | This is the "Find Agent" function.  Commands: none.';
            $this->thing_report['things'] = $agent_things;
        }
        $this->thing->log("findAgent call done.","DEBUG");
        return $this->thing_report['things'];
    }

    public function run()
    {
        $this->findAgent($this->requested_agent_name);
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        // Generate email response.

        $to = $this->thing->from;

        $from = "group";

        $this->thing_report['choices'] = false;

        $t = "";
        if (isset($this->choice['link']) and $this->choices['link'] !== false) {
            $sms_end = strtoupper(strip_tags($this->choices['link']));
            $x = implode("", explode("FORGET", $sms_end, 2));

            $t = " | TEXT " . $x;
        }

        $this->sms_message =
            strtoupper($this->agent_name) . " | " . $this->sms_message . $t;

        $this->message = $this->sms_message;

        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->message;
    }

    private function nextWord($phrase)
    {
    }

    public function readInstruction()
    {
        if ($this->agent_input == null) {
            //$this->defaultCommand();
            return;
        }

        $pieces = explode(" ", strtolower($this->agent_input));

        if (isset($pieces[0])) {
            $this->requested_agent_name = $pieces[0];
        }
        if (isset($pieces[1])) {
            $this->horizon = $pieces[1];
        }
    }

    public function readSubject()
    {
        //$this->response = null;
        $this->num_hits = 0;
    }
}
