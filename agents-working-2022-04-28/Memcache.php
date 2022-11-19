<?php
namespace Nrwtaylor\StackAgentThing;

class Memcache extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->getMemcached();
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

    public function readSubject()
    {
        return false;
    }
}
