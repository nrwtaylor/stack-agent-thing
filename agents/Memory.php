<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Memory extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->initMemory();
    }

    public function initMemory()
    {
        $this->error = null;
        $this->status = 'loading';
        if (
            isset($this->agent_input) and
            $this->agent_input == 'fallback memory'
        ) {
            return;
        }
        if (!isset($this->memory)) {
            try {
                $this->memory = new \Memcached(); //point 2.
                $this->memory->addServer("127.0.0.1", 11211);
            } catch (\Throwable $t) {
                //$this->response .= "Could not access memory. ";
                // Failto

                //$thing = $this->thing;
                //if ($this->thing == null) {
                //$thing = new Thing(null);
                //}
                //                $this->memory = new Memory($this->thing, "fallback memory");

                //$this->memory = new Memory($thing, "merp");
                //restore_error_handler();
                //                $this->thing->log(
                //                    "caught memcached throwable. made memory",
                //                    "WARNING"
                //                );
                return;
            } catch (\Error $ex) {
                //                $this->thing->log("caught memcached error.", "WARNING");
                return true;
            }
        }
    }

    public function errorMemory($text = null)
    {
        if ($text == null) {
            return;
        }

        $this->statusMemory('error');
        $this->error = $text;

        if (!isset($this->response)) {
            $this->response = "";
        }
        $this->response .= $text . " ";
    }

    public function statusMemory($text = null)
    {
        if ($text != null) {
            $this->status = $text;
        }
        return $this->status;
    }

    public function isReadyMemory()
    {
        if (isset($this->status) and $this->status == 'ready') {
            return true;
        }
        return false;
    }


    function run()
    {
        $this->doMemory();
    }

    // dev
    public function writeMemory($field_text, $string_json)
    {
        if (!isset($this->uuid)) {
            return true;
        }

        if ($this->uuid == null) {
            return true;
        }
        // Hmmm
        // Ugly but do this for now.
        $j = new ThingJson($this->uuid);
//        $j->jsontoarrayJson($string_json);
        $data = $j->jsontoarrayJson($string_json);
//$data = null;

//        $j->jsontoarrayJson($string_json);
//        $data = $this->jsontoarrayJson($string_json);

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

        $existing = $this->memory->get($this->uuid);

if ($existing == false) {
$this->errorMemory("Existing uuid not found on write request.");
return false;}

        $d = $data;
        if (is_array($existing)) {
            $d = array_replace_recursive($existing, $data);
        }

        // In development

        $response = $this->memory->set($this->uuid, $d);

if ($response === true) {
var_dump("Memory write OK " . $this->uuid);
return $this->uuid;}

$this->errorMemory("Memory write NOT OK " . $this->uuid);

return true;

    }

    public function doMemory()
    {
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "MEMORY | " . strtolower($v) . ".";

            $this->memory_message = $response; // mewsage?
        } else {
            $this->memory_message = $this->agent_input;
        }
    }

    public function getMemory($text = null)
    {
        if (!isset($this->memory)) {
            return null;
        }

        if ($text == null) {
            $text = $this->uuid;
        }

        // Null?
        // $this->mem_cached = null;
        // Fail to stack php memory code if Memcached is not availble.
        //if ($text == null) {return false;} // false?
        $memory = $this->memory->get($text);
        return $memory;
    }
    /*
    public function createMemory($subject, $to) {

if (!isset($this->response)) {$this->response = "";}
  //         $this->thing->log(
  //              'asked to create a Memory.',
  //              "INFORMATION"
  //          );
        $this->response .= "Created a Memory. ";

        return $this->setMemory(null,['from'=>$to,'to'=>'memory','task'=>$subject]);
    }
*/
    public function createMemory($subject, $to)
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

        try {
            //$result = $this->collection->insertOne($thing);
            $result = $this->memory->set($uuid, $thing);
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

    public function setMemory($text = null, $variable = null)
    {
        // Stack rule.
        // You can not create a specific uuid on the stack.
        // If a uuid key is provided, check if it exists.
        if ($this->isUuid($text)) {
            $m = $this->getMemory($text);

            if ($m === false) {
                return true;
            }
        }

        // Create random uuid key if none provided.

        if ($text === null) {
            $text = $this->thing->getUuid();
        }
        if (!isset($this->memory)) {
            return true;
        }

        $memory = $this->memory->set($text, $variable);
        return $memory;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] =
            "This is a memory keeping an eye on how late this Thing is.";
        $this->thing_report["help"] = "This is about being inscrutable.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];

        return $this->thing_report;
    }

    function makeSMS()
    {
        $this->node_list = ["memory" => ["memory"]];
        $this->sms_message = "" . $this->memory_message;
        $this->thing_report["sms"] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("channel", $this->node_list, "memory");
        $choices = $this->thing->choice->makeLinks("memory");
        $this->thing_report["choices"] = $choices;
    }

    public function readSubject()
    {
    }
}
