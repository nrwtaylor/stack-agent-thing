<?php
namespace Nrwtaylor\StackAgentThing;

// https://www.php.net/manual/en/mongodb.tutorial.library.php

class Mongo extends Agent
{
    public $var = "hello";

    function init()
    {
        $path =
            "mongodb://127.0.0.1:27017/?compressors=disabled&gssapiServiceName=mongodb";
        $client = new \MongoDB\Client($path);

        $db = $client->test;
        $this->collection = $client->stack_db->things;

        $uuid = "5282cdc9-8252-4bd6-9d03-e1e0c0cec927";

        $thing = [
            "uuid" => $this->uuid,
            "subject" => $this->subject,
            "from" => $this->from,
            "to" => $this->to,
            "settings" => null,
            "variables" => null,
        ];

        $result = $this->collection->insertOne($thing);

if (!isset($this->response)) {$this->response = "";}
        $this->response .= "Inserted with Object ID '{$result->getInsertedId()}'. ";

        $value = $this->getMongo($uuid);

        $this->response .= "Got value for key " . $uuid . ". ";

        $condition = ["_id" => $uuid];

        // Dev review how this works.

        $result = $this->setMongo($uuid, [$thing]);

        $result = $this->setMongo(null, [$thing]);

        $result = $this->findMongo(["subject" => "mongo"]);

        $this->response .= "Found " . count($result) . " things. ";

        foreach ($result as $entry) {
            //            echo $entry["_id"], ": ", $entry["subject"], "\n";
        }

        // For testing
        $this->forgetMongo("609b5994088b47714d648172");
    }

    function run()
    {
    }

    public function doMongo()
    {
    }

    // use memcache model for get.
    public function getMongo($text = null)
    {
        $result = $this->collection->findOne(["_id" => $text]);
        if ($result == null) {
            return false;
        }
        return iterator_to_array($result);
    }

    // use memcache model for set.
    public function setMongo($key = null, $variable = null)
    {
        // Stack rule.
        // You can not create a specific uuid on the stack.

        // If a uuid key is provided, check if it exists.

        if ($this->isUuid($key)) {
            $m = $this->getMongo($key);
            if ($m === false) {
                return true;
            }
        }

        // Create random uuid key if none provided.
        if ($key == null) {
            $key = $this->thing->getUUid();
            /*
            $uuid = $this->uuid;
            $task = $this->subject;
            $nom_from = $this->from;

            $hash_nom_from = hash($this->hash_algorithm, $nom_from);

            if ($this->hash_state == "off") {
                $hash_nom_from = $nom_from;
            }
            $nom_to = $this->to;
            $variable = ['task'=>$task, 'nom_from'=>$from, 'nom_to'=>$to, 'variables'=>$variable];
*/
        }

        $condition = ["_id" => $key];

        $value = $variable;
        if (is_array($variable)) {
            $value = $variable[0];
        }

        $result = $this->collection->updateOne($condition, [
            '$set' => $value,
        ]);
        return $key;
    }

    // Delete by key.
    public function forgetMongo($key = null)
    {
        if ($key == null) {
            return true;
        }

        $condition = ["_id" => $key];

        $result = $this->collection->deleteOne($condition);
        $count = $result->getDeletedCount();
        return $result;
    }

    public function findMongo($arr)
    {
        // example
        // [ '_id' => $uuid ]
        $result = $this->collection->find($arr);

        $arr = [];
        foreach ($result as $entry) {
            $key_id = (string) $entry["_id"];
            $arr[$key_id] = iterator_to_array($entry);
        }
        return $arr;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is an agent to manage Mongo database calls.";
        $this->thing_report["help"] = "Not yet user facing.";

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $this->node_list = ["mongo" => ["mongo"]];

        $sms = "MONGO | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function readSubject()
    {
    }
}
