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

public function snapshotMemcached($datagram) {

if ($datagram == null) {return true;}

            $thing_report = [];

            $thing_report["start_time"] = microtime(true);

  $diff = 0;
  $poll_interval = 120000;
/*
                if (
                    isset($things[$datagram["from"]]) and
                    isset($things[$datagram["from"]]["poll_interval"])
                ) {
                    //$poll_interval = 10008;
                    $poll_interval =
                        $things[$datagram["from"]]["poll_interval"];
                }
*/
                $memory = null;

                $uuid = $datagram["from"];

                            $mem = new \Memcached("story-pool");
                            $mem->addServer("127.0.0.1", 11211);

                $prior_thing_report = $mem->get($uuid);

$created_at = null;
if (isset($datagram['createdAt'])) {
  $created_at = $datagram['createdAt'];
}

$prior_created_at = null;
if ($prior_thing_report != null) {
                $prior_created_at = $prior_thing_report["thing"]["createdAt"];
}

//                $diff = strtotime($created_at) - strtotime($prior_created_at);
                //$diff = 9999;
                $thing = [
                    "uuid" => $uuid,
                    "subject" => $datagram["subject"],
                    "to" => $datagram["to"],
                    "from" => $datagram["from"],
                    "agentInput" => null,
                    "createdAt" => $created_at,
                ];

                $memory = null;



                $thing_report["snapshot"] = null;

                if (isset($datagram["agent_input"])) {
                    $thing_report["snapshot"] = $datagram["agent_input"];
                }
                if (isset($datagram["agentInput"])) {
                    $thing_report["snapshot"] = $datagram["agentInput"];
                }

                if (isset($things[$uuid])) {
                    if (isset($things[$uuid]["text"])) {
                        $thing_report["text"] = $things[$uuid]["text"];
                    }
                }

                $thing_report["end_time"] = microtime(true);

                $thing_report["runtime"] =
                    number_format(
                        ($thing_report["end_time"] -
                            $thing_report["start_time"]) *
                            1000
                    ) . "ms";
                $thing_report["period"] = $diff;
                $thing_report["requested_poll_interval"] = $poll_interval;

                $thing_report["log"] = null;
                /*
                    "datagram" => [
                        "text" => $deslug_agent_name,
                        "agentInput" => null,
                    ],
                    "thing" => [
                        "uuid" => $web_thing->uuid,
                        "subject" => $web_thing->subject,
                        "createdAt" => $web_thing->created_at,
                    ],
                    "thingReport" => $json,

*/
                $memory = [
                    "uuid" => $uuid,
                    "datagram" => ["text" => null, "agentInput" => $memory],
                    "thing" => $thing,
                    "thingReport" => $thing_report,
                ];

                $status = $mem->set($uuid, $memory);

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
        $j = new Json($this->uuid);
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
