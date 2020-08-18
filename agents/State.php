<?php
/**
 * Crow.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// devstack
// Build a tree of allowed states.

class State extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    public function init()
    {
        $this->test = "Development code";

        $this->primary_place = "roost";
        $this->signals = ["on", "off"];

        $this->node_list = [
            "inside nest" => [
                "nest maintenance" => ["patrolling" => "foraging", "foraging"],
            ],
            "midden work" => "foraging",
        ];

        $this->thing_report['help'] = 'This is the state agent.';

        $entity = new Entity($this->thing, "state");
        $this->state_thing = $entity->thing;
    }

    public function get()
    {
        // devstack
        // Use the settings array to find out what states are available to this thing.
        $settings_json = $this->thing->thing->settings;
        $settings = $this->thing->json->jsontoArray($settings_json);
    }

    function run()
    {
        $this->doState();
    }

    private function getState()
    {
        $this->state = $this->state_thing->choice->load($this->primary_place);
        $this->state_thing->choice->Create(
            $this->primary_place,
            $this->node_list,
            $this->state
        );
        $this->state_thing->choice->Choose($this->state);

        $choices = $this->state_thing->choice->makeLinks($this->state);
    }

    private function setState()
    {
        $this->state_thing->choice->Choose($this->state);
        $choices = $this->state_thing->choice->makeLinks($this->state);
    }

    /**
     *
     */
    public function set()
    {
        $this->state_thing->json->writeVariable(
            ["state", "name"],
            $this->state_name
        );
        $this->state_thing->json->writeVariable(
            ["state", "signal"],
            $this->signal
        );
    }

    function doState()
    {
        if (!isset($this->state) or $this->state == null) {
            //$this->response = "detected state null - run subject discriminator";
            $this->thing->log($this->agent_prefix . 'state is null.');
        }

        $this->state = $this->thing->choice->load('roost');

        // Will need to develop this to only only valid state changes.
        switch ($this->state) {
            case "on":
                //$this->spawn();
                $this->response .= "Spawn state. ";
                $this->onState();
                break;
            case "off":
                $this->response .= "Dead Crow. ";
                $this->offState();
                //$this->kill();
                break;
            case "foraging":
                //$this->thing->choice->Choose("foraging");
                $this->response .= "Foraging. ";
                break;
            case "inside nest":
                //$this->thing->choice->Choose("in nest");
                $this->response .= "Crow is Inside Nest. ";
                break;
            case "nest maintenance":
                $this->response .= "Crow is doing Nest Maintenance. ";
                //$this->thing->choice->Choose("nest maintenance");
                break;
            case "patrolling":
                $responses = [
                    "Crow is Watching for predators. ",
                    "Crow is analyzing humans. ",
                    "Crow is questing for the oracle. ",
                    "Crow has found a Peanut's comic strip. ",
                ];
                $this->response .= array_rand($responses);
                break;
            case "midden work":
                $this->response .= "Crow is doing Midden Work. ";
                break;

            case false:
                $this->response .= "Heisenberg.";

            case true:
            case null:
            case false:

            default:
                $this->thing->log(
                    'invalid state provided "' . $this->state . "."
                );
                $this->response = "State handling is broken. ";
        }

        $this->signal = "X";
        $this->state_name = "X";
    }

    function respond()
    {
        $this->thing_report['sms'] = "STATE " . "| " . $this->response;
    }

    function onState()
    {
        $this->state = "on";
    }

    function offState()
    {
        $this->state = "off";
    }

    function readSubject()
    {
    }
}
