<?php
namespace Nrwtaylor\StackAgentThing;

// https://www.php.net/manual/en/mongodb.tutorial.library.php

class Mongo extends Agent
{
    public $var = "hello";
    /*
This code encapsulates all the PHP needed to handle the core
Thing calls to a Mongo database. Including self test functions
you can call with "mongo test"
*/

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

    static function initStaticMongo()
    {
        //$this->mongo_test_flag = "off";
        $path =
            "mongodb://127.0.0.1:27017/?compressors=disabled&gssapiServiceName=mongodb";

        try {
            $client = new \MongoDB\Client($path);
            //$this->db = $client;
            $collection = $client->stack_db->things;
return $collection;
            //$this->statusMongo("ready");
            //$this->thing->log("Mongo initMongo ok");
        } catch (\Throwable $t) {
            //$this->thing->log("Mongo initMongo Throwable");
            //$this->errorMongo($t->getMessage());
        } catch (\Error $ex) {
            //$this->thing->log("Mongo initMongo Error");

            //$this->errorMongo($ex->getMessage());
        }
    }

    // use memcache model for get.
    static function getStaticMongo($text = null)
    {
        // Get mongo key by uuid.
//        if (!$this->isReadyMongo()) {
//            return;
//        }
        $path =
            "mongodb://127.0.0.1:27017/?compressors=disabled&gssapiServiceName=mongodb";

        $result = null;
        try {
            $client = new \MongoDB\Client($path);
            $collection = $client->stack_db->things;
            $result = $collection->findOne(["uuid" => $text]);
        } catch (\Throwable $t) {
//var_dump($t->getMessage());
   //         $this->errorMongo($t->getMessage());
        } catch (\Error $ex) {
//var_dump($t->getMessage());
   //         $this->errorMongo($ex->getMessage());
        }
//var_dump("getStaticMongo result", $result);
        if ($result == null) {
            return false;
        }
$arr = json_decode(json_encode($result), true);

//var_dump($arr);
//exit();
//        $thing = iterator_to_array($result);
        unset($arr["_id"]);
        return $thing;
        //        return iterator_to_array($result);
    }



    static function setStaticMongo($key = null, $variable = null)
    {
       // if (!$this->isReadyMongo()) {
       //     return true;
       // }
        if (isset($variable["uuid"])) {
            if ($variable["uuid"] != $key and $key != null) {
      //          $this->errorMongo(
      //              "Thing update requested with inconsistent uuids."
      //          );
                return true;
            }

            if ($key == null) {
                $key = $variable["uuid"];
            }
        }

        // Stack rule.
        // You can not create a specific uuid on the stack.

        // If a uuid key is provided, check if it exists.

//        if ($this->isUuid($key) and $key !== null) {
//       if ($key !== null) {
//            $m = Mongo::getStaticMongo($key);
//            if ($m === true) {
//                return true;
//            }
//        }

        // Create random uuid key if none provided.
//        if ($key == null) {
            // Because thing is the only plae uuids are made.
        //    $t = new Thing(null);
        //    $key = $t->uuid;
//$key =null;
//        }
        $condition = ["uuid" => $key];

        $value = $variable;
 //       if (is_array($variable) and isset($variable[0])) {
 //           $value = $variable[0];
 //       }
$value["hhh"] = "jjj";
        $value["uuid"] = $key;

        try {


        $path =
            "mongodb://127.0.0.1:27017/?compressors=disabled&gssapiServiceName=mongodb";

            $client = new \MongoDB\Client($path);
            $collection = $client->stack_db->things;

            $result = $collection->updateOne($condition, [
                '$set' => $value,
            ], ['upsert'=>true]);
        } catch (\Throwable $t) {
var_dump($t->getMessage());
//            $this->errorMongo($t->getMessage());
            return true;
        } catch (\Error $ex) {
var_dump($ex->getMessage());
//            $this->errorMongo($ex->getMessage());
            return true;
        }
//var_dump("result",$result);
        return $key;
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
            $this->statusMongo("ready");
            $this->thing->log("Mongo initMongo ok");
        } catch (\Throwable $t) {
            $this->thing->log("Mongo initMongo Throwable");
            $this->errorMongo($t->getMessage());
        } catch (\Error $ex) {
            $this->thing->log("Mongo initMongo Error");

            $this->errorMongo($ex->getMessage());
        }
    }

    public function errorMongo($text = null)
    {
        $this->thing->log("error" . $text);
        if ($text == null) {
            return;
        }
        $this->statusMongo("error");
        //var_dump("error: " . $text);
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
        return $this->status;
    }

    public function isReadyMongo()
    {
        if (isset($this->status) and $this->status == "ready") {
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

        $things = $this->collection->find(["nom_from" => $nom_from]);

        foreach ($things as $object_key => $thing) {
            $conditioned_things[$thing["uuid"]] = $thing;
        }

        usort($conditioned_things, function ($first, $second) {
            // dev Allow null fields in sort.
            if ($first["created_at"] == null) {
                return +1;
            }
            if ($second["created_at"] == null) {
                return -1;
            }

            return strtotime($first["created_at"]) -
                strtotime($second["created_at"]);
        });
        $obj = $conditioned_things[0];

        $arr = (array) $obj;
        unset($arr["_id"]);
        return $arr;
    }

    public function testMongo()
    {
        $test_result = "OK";
        $this->response .= "Test called. ";
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
        } else {
            $test_response .= "Ready OK. ";
        }

        $test_create_uuid = $this->createMongo("test create mongo", "mongo");
        $test_response .= "Create " . $test_create_uuid . " OK. ";
        $prior_thing = $this->priorMongo();
        $test_response .=
            "Prior " .
            $prior_thing["uuid"] .
            " " .
            $prior_thing["subject"] .
            "OK. ";
        // Test will fail if this uuid does not exist in database.

        $uuid = $test_create_uuid;
        $thing = [
            //            "uuid" => $uuid,
            "task" => $this->subject,
            "nom_from" => $this->from,
            "nom_to" => $this->to,
            "settings" => null,
            "variables" => null,
        ];

        $result = $this->setMongo($uuid, $thing);

        $value = $this->getMongo($uuid);

        // task subject text. synonyms.

        if ($this->isText($value["task"])) {
            $subject = $value["task"];
        }

        if ($this->isText($value["subject"])) {
            $subject = $value["subject"];
        }

        if ($subject == null) {
            $test_result = "NOT OK";
        }

        //$test_response .= "Got value '{$subject}' for uuid " . $uuid . ". ";

        // Test of updating a record.

        // Now try with a dynamically got uuid
        // Dev review how this works.

        $uuid = $this->createMongo("mongo test", $this->from);
        if ($this->isUuid($uuid)) {
            $test_response .= "Create " . $uuid . "OK. ";

            $result = $this->getMongo($uuid);

            $result2 = $this->setMongo($uuid, [$thing]);

            $result = $this->getMongo($uuid);

            if ($result2 !== $uuid or $result["task"] !== "mongo test") {
                $test_result = "NOT OK";
                $test_response .= "Set NOT OK. ";
            } else {
                $test_response .= "Set " . $uuid . " OK. ";
            }
        } else {
            $test_result = "NOT OK";
            $test_response .= "Create NOT OK. ";
        }

        // Test of forgetting a record.

        $uuid = $this->createMongo("Mongo forget test", $this->from);

        if ($this->isUuid($uuid)) {
            $test_response .= "Create " . $uuid . " OK. ";
        } else {
            $test_response .= "Create NOT OK. ";
            $result_result = "NOT OK";
        }
        $this->forgetMongo($uuid);

        $result = $this->getMongo($uuid);

        if ($result !== false) {
            $test_result = "NOT OK";
            $test_response .= "Forget NOT OK. ";
        } else {
            $test_response .= "Forget " . $uuid . " OK. ";
        }

        // Test find
        // Going to have to remember this. Some databases text field
        // might be task.
        // I think that is the specification established.

        $result = $this->findMongo(["task" => "mongo test"]);

        $test_response .= "Found " . count($result) . " things. ";

        if ($result === 0) {
            $test_result = "NOT OK";
            $test_response .= "Find NOT OK. ";
        } else {
            $test_response .= "Find OK. ";
        }

        $this->test_response = $test_response;
        $this->test_result = $test_result;
        $this->response .= $test_response . " [ " . $test_result . "] ";
    }







    static function writeStaticMongo($uuid, $field_text, $arr)
    {
/*
        if (!isset($this->write_fail_count)) {
            $this->write_fail_count = 0;
        }

        if (!$this->isReadyMongo()) {
            $this->write_fail_count += 1;
            return true;
        }
*/
        $existing = Mongo::getStaticMongo($uuid);
//var_dump("existing",$uuid,$existing);
        //$variables = $existing['variables'];
        // Hmmm
        // Ugly but do this for now.
        //     $j = new Json(null, $this->uuid);
        //     $j->jsontoarrayJson($string_json);
        //     $data = $j->jsontoarrayJson($string_json);
        //$data = $arr;
        $data = ["variables" => $arr];

        // dev develop associations.
/*
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
*/

$data["uuid"] = $uuid;


//        $existing = Mongo::getStaticMongo($uuid);

/*
        if ($existing == false) {
            //$this->errorMongo("Existing uuid not found on write request.");
            return false;
        }
*/

        $d = $data;
        if (is_array($existing)) {
            $d = array_replace_recursive($existing, $data);
        }
        $u = Mongo::setStaticMongo($uuid, $d);
        if ($u == true) {
            //$this->write_fail_count += 1;
            return true;
        }

        //$this->thing->log("Mongo write " . $uuid);

        return $uuid;
    }










    public function writeMongo($field_text, $arr)
    {
        if (!isset($this->write_fail_count)) {
            $this->write_fail_count = 0;
        }

        if (!$this->isReadyMongo()) {
            $this->write_fail_count += 1;
            return true;
        }

        $existing = $this->getMongo($this->uuid);
        //$variables = $existing['variables'];
        // Hmmm
        // Ugly but do this for now.
        //     $j = new Json(null, $this->uuid);
        //     $j->jsontoarrayJson($string_json);
        //     $data = $j->jsontoarrayJson($string_json);
        $data = $arr;
        $data = ["variables" => $data];

        // dev develop associations.
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
        if ($existing == false) {
            $this->errorMongo("Existing uuid not found on write request.");
            return false;
        }

        $d = $data;
        if (is_array($existing)) {
            $d = array_replace_recursive($existing, $data);
        }
        //var_dump("u", $this->uuid);
        //var_dump("e", $d);
        // In development
        $uuid = $this->setMongo($this->uuid, $d);
        if ($uuid == true) {
            $this->write_fail_count += 1;
            return true;
        }

        $this->thing->log("Mongo write " . $uuid);

        return $uuid;
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
            // $result = $this->collection->findOne(["_id" => $text]);
        } catch (\Throwable $t) {
            $this->errorMongo($t->getMessage());
        } catch (\Error $ex) {
            $this->errorMongo($ex->getMessage());
        }

        if ($result == null) {
            return false;
        }
        $thing = iterator_to_array($result);
        unset($thing["_id"]);
        return $thing;
        //        return iterator_to_array($result);
    }

    public function createMongo($subject, $to)
    {
        if (!$this->isReadyMongo()) {
            return true;
        }

        // Cannot create a thing on the stack with a specific uuid.
        // That's the rule.

        // Enforce that here. And in setMongo.

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
            "task" => $subject,
            "nom_from" => $from,
            "nom_to" => $this->to,
            "created_at" => $created_at,
            "variables" => null,
        ];

        // dev
        $uuid = Uuid::createUuid();

        /*
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
*/
        $thing["uuid"] = $uuid;

        try {
            $result = $this->collection->insertOne($thing);
        } catch (\Throwable $t) {
            $this->errorMongo($t->getMessage());
            return true;
        } catch (\Error $ex) {
            $this->errorMongo($ex->getMessage());
            return true;
        }

        // dev Test for successful insertion of new record.

        // setMongo returns the key
        //        $key_uuid = $this->setMongo(null, $thing); // null creates a new uuid
        return $uuid;
    }

    // use memcache model for set.
    public function setMongo($key = null, $variable = null)
    {
        if (!$this->isReadyMongo()) {
            return true;
        }

        if (isset($variable["uuid"])) {
            if ($variable["uuid"] != $key and $key != null) {
                $this->errorMongo(
                    "Thing update requested with inconsistent uuids."
                );
                return true;
            }

            if ($key == null) {
                $key = $variable["uuid"];
            }
        }

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
        //    $t = new Thing(null);
        //    $key = $t->uuid;
$key =null;
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

        $value = $variable;
        if (is_array($variable) and isset($variable[0])) {
            $value = $variable[0];
        }
        $value["uuid"] = $key;

        try {
            $result = $this->collection->updateOne($condition, [
                '$set' => $value,
            ]);
        } catch (\Throwable $t) {
            $this->errorMongo($t->getMessage());
            return true;
        } catch (\Error $ex) {
            $this->errorMongo($ex->getMessage());
            return true;
        }
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

        $condition = ["uuid" => $uuid];

        $result = $this->collection->deleteOne($condition);

        $count = $result->getDeletedCount();
        $this->response .= "Forgot " . $count . " thing(s). ";
        if ($count == 1) {
            return true;
        }
        return false;
    }

    public function findMongo($arr)
    {
        if (!$this->isReadyMongo()) {
            $this->response .= "findMongo saw Mongo not ready. ";
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

    public function variablesearchMongo($path, $value, $max = null)
    {

// Harder than it seems iniitally to replicate the
// exact Mysql behaviour.

        if ($max == null) {
            $max = 3;
        }
        $max = (int) $max;

        $user_search = $this->from;
        $hash_user_search = hash($this->hash_algorithm, $user_search);

        // https://stackoverflow.com/questions/11068230/using-like-in-bindparam-for-a-mysql-pdo-query
        //$value = "%$value%"; // Value to search for in Variables

        $thingreport["things"] = [];

        try {
/*
            $query =
                "SELECT * FROM stack WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND variables LIKE :value ORDER BY created_at DESC LIMIT :max";
            $sth = $this->pdo->prepare($query);
            $sth->bindParam(":user_search", $user_search);
            $sth->bindParam(":hash_user_search", $hash_user_search);
            $sth->bindParam(":value", $value);
            $sth->bindParam(":max", $max, PDO::PARAM_INT);
            $sth->execute();
            $things = $sth->fetchAll();
*/
/*
$condition, [
                '$set' => $value,
            ], ['upsert'=>true]);
*/

//        $path =
//            "mongodb://127.0.0.1:27017/?compressors=disabled&gssapiServiceName=mongodb";

//        try {

$value = null;
$user_search = "default_console_user";

$things = $this->collection.find([
  '$or' => [
    [ "nom_from" => $user_search ],
    [ "nom_from" => $hash_user_search ]
  ],
  ['variables' =>[ '$regex' =>  $value ]],
]);



//.sort([ "created_at"=>-1 ])
//.limit(max);

            $thingreport["info"] =
                'So here are Things with the variable you provided in \$variables. That\'s what you want';
            $thingreport["things"] = $things;
        } catch (\PDOException $e) {
            // echo "Error in PDO: ".$e->getMessage()."<br>";
            $thingreport["info"] = $e->getMessage();
            $thingreport["things"] = [];
        }

        $sth = null;

        return $thingreport;
    }



    static function variablesearchStaticMongo($path, $value, $max = null, $from=null, $hash_algorithm='sha256')
    {

// Harder than it seems iniitally to replicate the
// exact Mysql behaviour.

        if ($max == null) {
            $max = 3;
        }
        $max = (int) $max;

        $user_search = $from;
//var_dump($hash_algorithm);
//exit();
        $hash_user_search = hash($hash_algorithm, $user_search);

        // https://stackoverflow.com/questions/11068230/using-like-in-bindparam-for-a-mysql-pdo-query
        $value = "%$value%"; // Value to search for in Variables

        $thingreport["things"] = [];

        try {
/*
$condition, [
                '$set' => $value,
            ], ['upsert'=>true]);
*/


   $path2 =            "mongodb://127.0.0.1:27017/?compressors=disabled&gssapiServiceName=mongodb";

            $client = new \MongoDB\Client($path2);
            //$this->db = $client;
            $collection = $client->stack_db->things;


/*
$things = $collection->find([
  '$or' => [
    [ "nom_from" => $user_search ],
    [ "nom_from" => $hash_user_search ]
  ],
  ['variables' =>[ '$regex' =>  $value ]],
]);
*/

//var_dump($from);
//exit();
$things = $collection->find(
['$or' => [
    [ "nom_from" => $from ],
    [ "nom_from" => $hash_user_search ],
],
],
);
//if (is_array($things) and count($things) > 0) {
$matchingResults = $things->toArray();
//}
//var_dump($matchingResults);
//exit();
// Initialize an array to store the converted results
$convertedResults = [];

// Convert each MongoDB object to an associative array
foreach ($matchingResults as $document) {
unset($document['_id']);
    $convertedResults[] = json_decode(json_encode($document), true);
}

//.sort([ "created_at"=>-1 ])
//.limit(max);
//var_dump($convertedResults);
//var_dump("merp");
//exit();

            $thingreport["info"] =
                'So here are Things with the variable you provided in \$variables. That\'s what you want';
            $thingreport["things"] = $convertedResults;
        } catch (\PDOException $e) {
            // echo "Error in PDO: ".$e->getMessage()."<br>";
            $thingreport["info"] = $e->getMessage();
            $thingreport["things"] = [];
        }

        $sth = null;

        return $thingreport;
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

        $text_response = $this->response;

        //        $empty_agent = new _Empty($this->thing, "empty");
        //        if ($empty_agent->isEmpty($this->response)) {
        //            $text_response = "Mongo connector responded. ";
        //        }

        $sms = "MONGO | " . $text_response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function readSubject()
    {

        $responsive = false;
        $input = $this->assert($this->input);
//var_dump("Mondo readSubject");
//return;
        $uuid = $this->extractUuid($input);
//$uuid = null;
//var_dump($input);
//exit();
        if ($input == "test") {
            $this->mongo_test_flag = "on";
            $this->testMongo();
            $responsive = true;
        }

        //$this->response .= "merp";

        if ($input == "prior") {
            $prior_thing = $this->priorMongo();
            $this->response .=
                "Got prior thing " .
                $prior_thing["uuid"] .
                " " .
                $prior_thing["subject"] .
                ". ";
            $responsive = true;
        }

        if ($this->hasText($input, "get")) {
            //    if ($input == "get") {
            $thing = $this->getMongo($uuid);
            $this->response .=
                "Got thing " . $uuid . " " . $thing["subject"] . ". ";
            $responsive = true;
        }
        if ($this->hasText($input, "create")) {
            //    if ($input == "create") {
            $create_mongo_uuid = $this->createMongo("foo", "bar");
            $this->response .= "Created " . $create_mongo_uuid . ". ";
            $responsive = true;
        }

        if ($responsive === false) {
            $this->response .= "Try TEST PRIOR GET CREATE. ";
        }
    }
}
