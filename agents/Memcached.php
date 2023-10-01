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
    public function writeField($field_text, $string_json)
    {
        // Hmmm
        // Ugly but do this for now.
        $j = new ThingJson($this->uuid);
        $j->jsontoarrayJson($string_json);
        $data = $j->jsontoarrayJson($string_json);

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
        $d = $data;
        if (is_array($existing)) {
            $d = array_replace_recursive($existing, $data);
        }

        // In development

        $this->mem_cached->set($this->uuid, $d);
    }

    public function getMemcached($uuid)
    {
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
