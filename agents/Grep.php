<?php
namespace Nrwtaylor\StackAgentThing;
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Grep extends Agent
{
    function init()
    {
        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->test = true;
        }
        // I think.
        // Instead.

        $this->node_list = ["start", "grep" => ["grep"]];

        $this->thing->log('running on Thing ' . $this->uuid . ' ');
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "grep",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            // Then this Thing has no group information
            //$this->thing->json->setField("variables");
            //$time_string = $this->thing->json->time();
            //$this->thing->json->writeVariable( array("group", "refreshed_at"), $time_string );
        }
    }

    function isGrep($string)
    {
        if (strpos(strtolower($string), 'grep') !== false) {
            return true;
        }
        return false;
    }

    function getGreps()
    {
        $text = $this->search_words;

        // Search how?

        $this->thing->db->setFrom($this->from);
        $thing_report = $this->thing->db->agentSearch($text, 3);
        $agent_things = $thing_report['things'];

        // Searches
        //$this->thing->db->setUser($this->from);
        $thing_report = $this->thing->db->userSearch($text);
        $user_things = $thing_report['thing']; // Fix this discrepancy thing vs things

        // Or this.
        $thing_report = $this->thing->db->variableSearch(null, $text);
        $variable_things = $thing_report['things'];

        $this->things = array_merge(
            $agent_things,
            $user_things,
            $variable_things
        );

        $this->sms_message = "";
        $reset = false;

        $this->greps = [];
        foreach ($this->things as $thing) {
            $task = $thing['task'];
            $created_at = $thing['created_at'];
            $thing_string = $created_at . ' "' . $task . '"';

            if ($this->isGrep($task)) {
                continue;
            }
            // echo $thing_string . "\n";
            $this->greps[] = $thing;
        }
    }

    public function makeSMS()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/events';
        $count = count($this->greps);

        if (!isset($this->greps[0])) {
            $sms = "GREP | " . $this->response;
        } else {
            $sms = "GREP " . $count;
            $sms .= " | " . $this->greps[0]['task'];
            $sms .= " | " . $this->response;
        }
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function grepString($thing)
    {
        $string = $thing['created_at'] . " " . $thing['task'];
        return $string;
    }

    public function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/grep';

        $html = "<b>Grep Agent</b><br>";
        $html .= "<p>";
        $html .= "<p>";
        $html .= "<b>RESPONSE</b><br>";
        $html .= "Grep says , '";
        $html .= $this->sms_message . "'";

        $html .= "<p>";

        $html .= "<b>COLLECTED THINGS</b><br>";

        foreach ($this->greps as $grep) {
            $html .= "" . $this->grepString($grep) . "<br>";
        }

        $this->web_message = $html;
        $this->thing_report['web'] = $html;
    }

    function makeTXT()
    {
        $txt = "grep for " . $this->search_words . "\n";
        foreach ($this->things as $thing) {
            $txt .= "created " . $thing['created_at'] . "";
            //$txt .= '"' . $thing['task'] .'". ';
            $txt .= " " . $thing['task'] . "\n";
        }

        $this->thing_report['txt'] = $txt;
    }

    public function respondResponse()
    {
        // Develop the various messages for each channel.
        $this->thing->flagGreen();

        //$this->makeSms();

        $this->thing_report['email'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
        }

        return $this->thing_report;
    }

    public function readSubject()
    {
        $input = $this->input;

        $filtered_input = $this->assert($input);
        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->getGreps();
            $this->response .= 'Grepped "' . $this->search_words . '"';
            return false;
        }
    }
}
