<?php
namespace Nrwtaylor\StackAgentThing;

class Memcached extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->initMemcached();
    }

    function run()
    {
        $v = $this->mem_cached->get("test99");
        $this->response .= "Got " . $v . ". ";
        $text = rand(0, 6);
        $this->response .= "Made random number " . $text . ". ";
        $result = $this->mem_cached->set("test99", $text);
        $this->response .= "memcached said " . $result . ". ";
        $this->response .= "Set " . $text . ". ";
    }

    public function makeSMS()
    {
        $this->sms = $this->response;
        $this->thing_report["sms"] = $this->response;
    }

    // dev
    public function writeMemcached($field_text, $arr)
    {
        if (!isset($this->write_fail_count)) {
            $this->write_fail_count = 0;
        }

        // Hmmm
        // Ugly but do this for now.
        //        $j = new Json(null, $this->uuid);
        //        $j->jsontoarrayJson($string_json);
        //        $data = $j->jsontoarrayJson($string_json);
        $data = $arr;
        $data = ['variables' => $data];

        // dev develop associations.
        //$associations = null;
        if (isset($this->associations)) {
            $data['associations'] = $this->associations;
        }

        if (isset($this->uuid)) {
            $data['uuid'] = $this->uuid;
        }

        if (isset($this->from)) {
            $data['nom_from'] = $this->from;
        }

        if (isset($this->to)) {
            $data['nom_to'] = $this->to;
        }

        if (isset($this->subject)) {
            $data['task'] = $this->subject;
        }

        $existing = $this->mem_cached->get($this->uuid);

        if ($existing == false) {
            $this->errorMemcached("Existing uuid not found on write request.");
            $this->write_fail_count += 1;
            return false;
        }

        $d = $data;
        if (is_array($existing)) {
            $d = array_replace_recursive($existing, $data);
        }

        // In development

        $response = $this->mem_cached->set($this->uuid, $d);

        if ($response === true) {
            var_dump("Memcached write OK " . $this->uuid);
            return $this->uuid;
        }

        $this->errorMemcached("Write request not successful.");
        $this->write_fail_count += 1;
        return true;
    }

    public function createMemcached($subject, $to)
    {
        //        if (!$this->isReadyMongo()) {
        //            return true;
        //        }

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
        $thing['uuid'] = $uuid;

        try {
            //$result = $this->collection->insertOne($thing);
            $result = $this->mem_cached->set($uuid, $thing);
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

    public function getMemcached($uuid = null)
    {
        if ($uuid == null) {
            $uuid = $this->uuid;
        }

        $t = $this->mem_cached->get($uuid);
        //var_dump($t);
        return $t;
    }

    public function initMemcached()
    {
        if (isset($this->mem_cached)) {
            return;
        }

        // Null?
        // $this->mem_cached = null;

        try {
            $this->mem_cached = new \Memcached(); //point 2.
            $this->mem_cached->addServer("127.0.0.1", 11211);
        } catch (\Throwable $t) {
            // Failto
            $this->mem_cached = new Memory($this->thing, "memory");
            //restore_error_handler();
            $this->thing->log(
                "caught memcached throwable. made memory",
                "WARNING"
            );
            return;
        } catch (\Error $ex) {
            $this->thing->log("caught memcached error.", "WARNING");
            return true;
        }
    }

    public function readSubject()
    {
        return false;
    }
}
