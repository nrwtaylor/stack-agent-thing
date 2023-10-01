<?php
/**
 * Cluster.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Cluster extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    public function init()
    {
        $this->test = "Development code";

        //  $this->primary_place = "roost";
        //  $this->signals = array("on", "off");

        $this->node_list = ["cluster" => ["cluster"]];

        $this->thing_report['help'] = 'This is the cluster agent.';

        $this->size = null;
        $this->characteristic = null;
        $this->threshold = null;

        //        $entity = new Entity($this->thing, "state");
        //      $this->state_thing = $entity->thing;
    }

    function run()
    {
        $this->doCluster();
    }

    private function getCluster()
    {
        //   $this->state = $this->state_thing->choice->load($this->primary_place);
        //   $this->state_thing->choice->Create($this->primary_place, $this->node_list, $this->state);
        //   $this->state_thing->choice->Choose($this->state);

        //   $choices = $this->state_thing->choice->makeLinks($this->state);
    }

    public function makeSMS()
    {
        $this->thing_report['sms'] = "CLUSTER";
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function setCharacteristic($characteristic)
    {
        $this->characteristic = $characteristic;
    }

    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;
    }

    private function setCluster()
    {
        //       $this->state_thing->choice->Choose($this->state);
        //       $choices = $this->state_thing->choice->makeLinks($this->state);
    }

    /**
     *
     */
    public function set()
    {
        //if (!isset($this->size)) {return true;}
        $this->thing->Write(["cluster", "size"], $this->size);
        $this->thing->Write(
            ["cluster", "characteristic"],
            $this->characteristic
        );
        $this->thing->Write(
            ["cluster", "threshold"],
            $this->threshold
        );

        $time_string = $this->thing->time();
        $this->thing->Write(
            ["cluster", "refreshed_at"],
            $time_string
        );
    }

    function doCluster()
    {
        if (!isset($this->size) or $this->size == null) {
            //$this->response = "detected state n run subject discriminator";
            $this->thing->log($this->agent_prefix . 'size is null.');
        }

        //     $this->state = $this->thing->choice->load('roost');

        /*
        // Will need to develop this to only only valid state changes.
        switch ($this->state) {
        default:
            $this->thing->log('invalid state provided "' . $this->state. ".");
            $this->response = "Cluster handling is broken. ";
        }
 */
        //$this->signal = "X";
        $this->state = "X";
    }

    function respond()
    {
        $this->thing_report['sms'] = "STATE " . "| " . $this->response;
    }

    public function associateCluster($uuid = null, $nom_from = null)
    {
        if ($uuid == null) {
            return true;
        }
        if ($nom_from == null) {
            $nom_from = $this->from;
        }

        $this->thing->json->setFrom($nom_from);
        $this->thing->associate($uuid, "falling water");
        $this->thing->json->setField("variables");
    }

    public function readSubject()
    {
        //$uuid_agent = new Uuid($this->thing);
        //$uuid = $uuid_agent->extractUuid($this->agent_input);

        //$this->thing->associate($this->agent_input, "falling water");

        //$this->thing->json->setField("variables");
    }
}
