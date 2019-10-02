<?php
/**
 * Compression.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Compression extends Agent
{

    /**
     *
     */
    function init() {
    }


    /**
     *
     */
    function run() {
        $command = "compression ". $this->input;
        $this->agent = new Proword($this->thing, $command);
//        $this->thing_report = $this->agent->thing_report;

//        $this->agent->makeSMS();
//        $this->thing_report['sms'] = $this->agent->sms_message;

        $this->doCompression();
    }


    /**
     *
     */
    function doCompression() {
        $this->filtered_input = $this->input;


        if (!isset($this->agent->matches)) {
            $this->thing_report['sms'] = "COMPRESSION | No matches.";
            return;
        }

        $matches = $this->agent->matches;
        $keys = array_map('strlen', array_keys($matches));
        array_multisort($keys, SORT_DESC, $matches);


$text = $this->filtered_input;

        foreach ($matches as $agent_name=>$compression) {

            $text = str_ireplace($compression[0]['words'],
                $agent_name, $text);
        }

$this->filtered_input = $text;

        $this->thing_report['sms'] = "COMPRESSION | " . $this->filtered_input . "";
//var_dump($this->filtered_input);
    }


    /**
     *
     */
    function readSubject() {}


}
