<?php
namespace Nrwtaylor\StackAgentThing;

class Hash extends Agent
{
    public $var = 'hello';

/*
            "hash" => "on",
            "hash_algorithm" => "sha256",
            "hashtag" => "#devstack",
            "hashmessage" => "#devstack",
*/

    function init()
    {
//var_dump($this->stack_hash);
//var_dump($this->stack_hash_algorithm);
//var_dump($this->stack_hashtag);
//var_dump($this->stack_hashmessage);
//var_dump($this->from);
    }

    function run()
    {
        $this->doHash();
    }

    public function doHash()
    {
        if ($this->agent_input == null) {

            $response = "HASH | " . strtoupper($this->stack_hash) .". ";
            $response .= $this->stack_hashtag .". ";

            $this->hash_message = $response;
        } else {
            $this->hash_message = $this->agent_input;
        }
    }

    function makeSMS()
    {
        $this->node_list = array("hash" => array("cash", "dehash"));
        $this->sms_message = "" . $this->hash_message;
        $this->thing_report['sms'] = $this->sms_message;
    }

    public function readSubject()
    {
        return false;
    }
}
