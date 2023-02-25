<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Snapshot extends Agent
{
    // This gets Forex from an API.

    public $var = "hello";

    public function init()
    {
        $this->nuuids = require $this->resource_path . "nuuid/nuuids.php";

        $this->data_source = $this->agent_input;
    }

    function set()
    {
        //        $this->thing->Write("refreshed_at", $this->current_time);
    }

    function get()
    {
        $this->getSnapshot();
    }

    function getSnapshot()
    {
        if ($this->data_source == null) {
            return true;
        }

        $data = file_get_contents($this->data_source);
        if ($data == false) {
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, true);
        if (!isset($json_data["thingReport"])) {
            return false;
        }
        if (!isset($json_data["thingReport"]["snapshot"])) {
            return false;
        }
        $this->snapshot = $json_data["thingReport"]["snapshot"];
        return $this->snapshot;
    }

    public function thingSnapshot($obj)
    {
        if ($obj == null) {
            return true;
        }

        if (is_string($obj)) {
            $units = $this->extractUnits($obj);

            // Develop this to recognize multiple text string schemas

            $this->recognized_schema["b97f"] = [
                "V" => ["text" => "voltage-house"],
                "C1" => ["text" => "temperature-thing"],
                "Pa" => ["text" => "pressure"],
                "uT" => ["text" => "magnetic-field"],
                "g" => ["text" => "acceleration-z"],
                "X1" => ["text" => "pitch"],
                "X2" => ["text" => "roll"],
                "M" => ["text" => "heading-magnetic"],
                "C2" => ["text" => "temperature-a"],
                "C3" => ["text" => "temperature-b"],
                "C4" => ["text" => "temperature-c"],
                "mm" => ["text" => "bilge-engine-compartment"],
                "timestamp" => ["text" => "timestamp"],
            ];

            $snap = [];
            $count = 1;
            $nuuid = "b97f";
            foreach (
                $this->recognized_schema["b97f"]
                as $recognized_schema => $descriptor
            ) {
                $snap[$descriptor["text"]] = $units[$count]["value"];
                $count += 1;
            }
            return $snap;
        }
    }

    function makeSMS()
    {
        $this->point_message = "SNAPSHOT | ";
        $this->sms_message = "" . $this->point_message . $this->response;
        $this->thing_report["sms"] = $this->sms_message;
    }

    public function readSubject()
    {
        $snap = $this->thingSnapshot($this->input);

        $created_at = date("Y-m-d H:i:s");

        $conditionedInput = str_replace("snapshot", "", $this->input);
        $conditionedInput = trim($conditionedInput);
        $parts = explode(" ", $conditionedInput);

        $nuuid = $this->extractNuuid($parts[2]);

        $uuid = $this->nuuids[$nuuid]["uuid"];
        $datagram = [
            "subject" => $this->subject,
            "from" => $uuid,
            "to" => $this->to,
            "created_at" => $created_at,
            "agent_input" => $snap,
        ];

//                 "text" => $this->nuuids[$nuuid]["text"],



        $this->snapshotMemcached($datagram);

        $this->response .= "Took snapshot of Thing " . $nuuid . ". ";

        return false;
    }
}
