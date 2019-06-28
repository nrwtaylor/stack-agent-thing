<?php
namespace Nrwtaylor\StackAgentThing;

class Compression extends Agent
{

    function init()
    {
    }

    function run() {
        $command = "compression ". $this->input;
        $this->agent = new Proword($this->thing, $command);
        $this->thing_report = $this->agent->thing_report;

        $this->agent->makeSMS();
        $this->thing_report['sms'] = $this->agent->sms_message;

        $this->doCompression();
    }

    function doCompression() {
        $this->filtered_input = $this->input;

if (!isset($this->agent->matches)) {
   $this->thing_report['sms'] = "COMPRESSION | No matches.";
   return;
}

$matches = $this->agent->matches;

$keys = array_map('strlen', array_keys($matches));
array_multisort($keys, SORT_DESC, $matches);

        foreach($matches as $agent_name=>$compression){

             $this->filtered_input = str_ireplace($compression['words'], 
                 $agent_name, $this->filtered_input);
        }
        $this->thing_report['sms'] = "COMPRESSION | " . $this->filtered_input . "";
    }

    function readSubject() {}
}

