<?php
namespace Nrwtaylor\StackAgentThing;

// https://www.php.net/manual/en/mongodb.tutorial.library.php

class Mongo extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->hash_state = "off";
        if (isset($settings["settings"]["stack"]["hash"])) {
            $this->hash_state = $settings["settings"]["stack"]["hash"];
        }

        $this->hash_algorithm = "sha256";
        if (isset($settings["settings"]["stack"]["hash"])) {
            $this->hash_algorithm =
                $settings["settings"]["stack"]["hash_algorithm"];
        }
        $this->status = null;
        $this->initMongo();
    }

    public function initMongo()
    {
        $this->mongo_test_flag = "off";
        $path =
            "mongodb://127.0.0.1:27017/?compressors=disabled&gssapiServiceName=mongodb";
        try {
            $client = new \MongoDB\Client($path);
            $this->db = $client;
            $this->collection = $this->db->stack_db->things;
            $this->statusMongo('ready');
        } catch (\Throwable $t) {
            $this->errorMongo($t->getMessage());
        } catch (\Error $ex) {
            $this->errorMongo($ex->getMessage());
        }
    }

    public function errorMongo($text = null)
    {
        if ($text == null) {
            return;
        }
        $this->statusMongo('error');
        var_dump("error: " . $text);
        $this->error = $text;

        if (!isset($this->response)) {
            $this->response = "";
        }
        $this->response .= $text . " ";
    }

    // Useful later for matching in variables
    // https://groups.google.com/g/mongodb-user/c/lv8XbtAkS4w?pli=1

    public function statusMongo($text = null)
    {
        if ($text != null) {
            $this->status = $text;
        }
        //var_dump($this->status);

        return $this->status;
    }

    public function isReadyMongo()
    {
        if (isset($this->status) and $this->status == 'ready') {
            return true;
        }
        return false;
    }

    public function priorMongo()
    {
        $nom_from = $this->from;
        if (!$this->isReadyMongo()) {
            return;
        }

        $things = $this->collection->find(["from" => $nom_from]);

        //        var_dump($things);
        /*        
        usort($things, function ($first, $second) {
            return strtotime($first->created_at) > strtotime($second->created_at);
        });
*/

        foreach ($things as $object_key => $thing) {
            $conditioned_things[$thing['uuid']] = $thing;
        }

        usort($conditioned_things, function ($first, $second) {
            return strtotime($first['created_at']) <
                strtotime($second['created_at']);
        });

        $obj = $conditioned_things[0];

        $arr = (array) $obj;
        unset($arr['_id']);
        //var_dump($arr);
        return $arr;
        /*
        
        foreach ($things as $object_key => $thing) {

            echo $object_key . " " . $thing["uuid"] .
                " " .
                (isset($thing["created_at"])
                    ? $thing["created_at"]
                    : "No created stamp") .
                " " .
                $thing["from"] .
                " " .
                $thing["subject"] .
                "\n";
        }
*/
    }

    public function testMongo()
    {
        $test_result = "NOT OK";
        $test_response = "";
        if ($this->mongo_test_flag != "on") {
            $test_response .= "Test flag not on";
            $this->response .= $test_response;
            return;
        }

        if (!$this->isReadyMongo()) {
            $test_response .= "Mongo not ready. ";
            $this->response .= $test_response;
            return;
        }

        $test_create_uuid = $this->createMongo("test create mongo", "mongo");
        $this->priorMongo();

        // Test will fail if this uuid does not exist in database.

        //        $uuid = "5282cdc9-8252-4bd6-9d03-e1e0c0cec927";
        $uuid = $test_create_uuid;
        $thing = [
            //            "uuid" => $uuid,
            "subject" => $this->subject,
            "from" => $this->from,
            "to" => $this->to,
            "settings" => null,
            "variables" => null,
        ];

        $result = $this->setMongo($uuid, $thing);

        $value = $this->getMongo($uuid);
        $subject = $value['subject'];

        $test_response .= "Got value '{$subject}' for uuid " . $uuid . ". ";

        // Now try with a dynamically got uuid
        // Dev review how this works.

        $uuid = $this->createMongo($this->subject, $this->from);
        $test_response .= "createMongo created record for " . $uuid . ". ";

        $result = $this->getMongo($uuid);
        //        var_dump("mongo got record " . $uuid, $result);

        $result = $this->setMongo($uuid, [$thing]);
        //        var_dump("setMongo by uuid", $result);

        // For testing
        $this->forgetMongo($uuid);

        //        $this->forgetMongo("609b5994088b47714d648172");

        // Test find
        $result = $this->findMongo(["subject" => "mongo"]);
        $test_response .= "Found " . count($result) . " things. ";

        $this->test_response = $test_response;
        $this->test_result = $test_result;
        $this->response .= $test_response . "Test result: " . $test_result;
    }

    // dev

    // START HERE
    public function writeMongo($field_text, $string_json)
    {
        if (!$this->isReadyMongo()) {
            return;
        }

        $existing = $this->getMongo($this->uuid);
        //$variables = $existing['variables'];
        // Hmmm
        // Ugly but do this for now.
        $j = new ThingJson($this->uuid);
        $j->jsontoarrayJson($string_json);
        $data = $j->jsontoarrayJson($string_json);
        //        $this->setValueFromPath($this->array_data, $var_path, $value);
        //        $this->arraytoJson();
        //        $t = $this->write();

        //         $this->setValueFromPath($this->array_data, $var_path, $value);

        $data = ["variables" => $data];

        // dev develop associations.
        //$associations = null;
        if (isset($this->associations)) {
            $data["associations"] = $this->associations;
        }

        if (isset($this->uuid)) {
            $data["uuid"] = $this->uuid;
        }

        if (isset($this->from)) {
            $data["nom_from"] = $this->from;
        }

        if (isset($this->to)) {
            $data["nom_to"] = $this->to;
        }

        if (isset($this->subject)) {
            $data["task"] = $this->subject;
        }

        $existing = $this->getMongo($this->uuid);
        $d = $data;
        if (is_array($existing)) {
            $d = array_replace_recursive($existing, $data);
        }

        // In development
        //$this->db->set($this->uuid, $d);
        $this->setMongo($this->uuid, $d);
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
        // Get mongo key by uuid.
        if (!$this->isReadyMongo()) {
            return;
        }

        $result = null;
        try {
            $result = $this->collection->findOne(["uuid" => $text]);
            //            $result = $this->collection->findOne(["_id" => $text]);
        } catch (\Throwable $t) {
            $this->errorMongo($t->getMessage());
        } catch (\Error $ex) {
            $this->errorMongo($ex->getMessage());
        }

        if ($result == null) {
            return true;
        }
        return iterator_to_array($result);
    }

    // BETTER TO START here.

    public function createMongo($subject, $to)
    {
        // Cannot create a thing on the stack with a specific uuid.
        // That's the rule.

        if (!isset($this->response)) {
            $this->response = "";
        }

        $task = $subject;
        $nom_from = $this->from;

        // dev test
        //$this->hash_algorithm = 'sha256';
        $hash_nom_from = hash($this->hash_algorithm, $nom_from);

        if ($this->hash_state == "off") {
            $hash_nom_from = $nom_from;
        }
        $nom_to = $to;

        $stamp = new Stamp(null, "stamp");
        $created_at = $stamp->zuluStamp();

        $sha256 = new SHA256(null, "sha256");
        $o = $sha256->isSHA256($nom_from);
        if ($o == true and $this->hash_state == "on") {
            $from = $nom_from;
        } else {
            $from = $hash_nom_from;
        }

        $thing = [
            //            "uuid" => $this->uuid,
            "subject" => $subject,
            "from" => $from,
            "to" => $this->to,
            "created_at" => $created_at,
            "variables" => null,
        ];
        /*
        $thing = [
            //            "uuid" => $uuid,
            "subject" => $this->subject,
            "from" => $this->from,
            "to" => $this->to,
            "settings" => null,
            "variables" => null,
        ];
*/
        //            $t = new Thing(null);
        //            $uuid = $t->uuid;
        //        var_dump("createMongo");

        if (isset($this->thing)) {
            if ($this->thing == null) {
                $t = new Thing(null);
                $uuid = $t->uuid;
            } else {
                $uuid = $this->thing->getUUid();
            }
        }

        if (!isset($this->thing)) {
            $t = new Thing(null);
            $uuid = $t->uuid;
        }

        $thing['uuid'] = $uuid;
        $result = $this->collection->insertOne($thing);

        // setMongo returns the key
        //        $key_uuid = $this->setMongo(null, $thing); // null creates a new uuid
        //        var_dump("createMongo key_uuid", $key_uuid);
        return $uuid;
    }

    // use memcache model for set.
    public function setMongo($key = null, $variable = null)
    {
        if (!$this->isReadyMongo()) {
            return;
        }

        if (isset($variable['uuid'])) {
            if ($variable['uuid'] != $key and $key != null) {
                var_dump("Inconsistent uuids");
                return;
            }

            if ($key == null) {
                $key = $variable['uuid'];
            }
        }

        //echo "getMongo called (" . $key . ")\n";
        // Stack rule.
        // You can not create a specific uuid on the stack.

        // If a uuid key is provided, check if it exists.

        if ($this->isUuid($key) and $key !== null) {
            $m = $this->getMongo($key);
            if ($m === true) {
                return true;
            }
        }
        // Create random uuid key if none provided.
        if ($key == null) {
            // Because thing is the only plae uuids are made.
            $t = new Thing(null);
            $key = $t->uuid;
            //$key = $uuid->randomUuid();
            //            $key = $this->thing->getUUid();

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
        $condition = ["uuid" => $key];

        //$condition = ["_id" => $key];

        $value = $variable;
        if (is_array($variable) and isset($variable[0])) {
            $value = $variable[0];
        }
        $value['uuid'] = $key;
        //var_dump("value", $value);
        try {
            $result = $this->collection->updateOne($condition, [
                '$set' => $value,
            ]);
        } catch (\Throwable $t) {
            $this->errorMongo($t->getMessage());
        } catch (\Error $ex) {
            $this->errorMongo($ex->getMessage());
        }
        //var_dump("result", $result);
        return $key;
    }

    // Delete by key.
    public function forgetMongo($uuid = null)
    {
        if (!$this->isReadyMongo()) {
            return true;
        }

        if ($uuid == null) {
            return true;
        }

        $condition = ["uuid" => $key];

        $result = $this->collection->deleteOne($condition);
        $count = $result->getDeletedCount();
        $this->response .= "Deleted " . $count . " thing. ";
        if ($count == 1) {
            return true;
        }
        return false;
        //return $result;
    }

    public function findMongo($arr)
    {
        if (!$this->isReadyMongo()) {
            return true;
        }

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

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["email"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["keyword"] = "mongo";
        $this->thing_report["help"] = "Mongo database handler.";
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
        $input = $this->assert($this->input);
        if ($input == "test") {
            $this->mongo_test_flag = "on";
            $this->testMongo();
        }

        if ($input == "prior") {
            $this->priorMongo();
        }
    }
}
