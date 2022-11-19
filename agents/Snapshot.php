<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Snapshot extends Agent
{
    // This gets Forex from an API.

    public $var = 'hello';

    public function init()
    {
        $this->data_source = $this->agent_input;
    }

    function set()
    {
        $this->thing->Write("refreshed_at", $this->current_time);
    }

    function get()
    {
        $this->getSnapshot();
    }

    function getSnapshot()
    {
        $data = file_get_contents($this->data_source);
        if ($data == false) {
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, true);
        if (!isset($json_data['thingReport'])) {
            return false;
        }
        if (!isset($json_data['thingReport']['snapshot'])) {
            return false;
        }
        $this->snapshot = $json_data['thingReport']['snapshot'];
        return $this->snapshot;
    }

    public function readSubject()
    {
        return false;
    }
}
