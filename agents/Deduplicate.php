<?php
/**
 * Deduplicate.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Deduplicate extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    function init()
    {
        $this->test = "Development code";

        $this->node_list = array("deduplicate" => array("deduplicate"));
    }

    function run()
    {
        // Do not run.
        // Only run as a direct call.
        // $deduplicate_agent = new Deduplicate($this->thing, "deduplicate roll");
        // $this->thing->db->setFrom("bob"); if needed
        // $deduplicate_agent->doDeduplicate;

        // or

        // $deduplicate_agent = new Deduplicate($this->thing, "deduplicate");
        // $this->thing->db->setFrom("bob"); if needed
        // $deduplicate_agent->setAgent("an agent");
        // $deduplicate_agent->doDeduplicate;

        // $this->doDeduplicate();
    }

    public function setAgent($agent)
    {
        if (isset($agent)) {
            $this->deduplicate_agent = $agent;
        }
    }

    public function doDeduplicate($text = null)
    {
        // devstack
        $this->thing->db->agentDeduplicate($this->deduplicate_agent);
        $this->response .= 'Deduplicated, "' . $this->deduplicate_agent . '". ';
    }

    /**
     *
     * @return unknown
     */
    public function respond()
    {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        //    $choices = false;

        //    $this->makeSMS();
        //    $this->makeMessage();

        //    $this->makeWeb();

        $this->thing_report["info"] = "This deduplicates agent records.";
        if (!isset($this->thing_report['help'])) {
            $this->thing_report["help"] =
                'This is about deduplication.  Try "deduplicate roll".';
        }

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        return $this->thing_report;
    }


    public function arrayDeduplicate($thing_array = null, $deduplicate_field = "text")
    {
        // Deduplicate

        foreach ($thing_array as $i => $thing_a) {
            foreach ($thing_array as $j => $thing_b) {
                if ($i == $j) {
                    continue;
                }

                if (
                    strtolower($thing_a[$deduplicate_field]) ==
                    strtolower($thing_b[$deduplicate_field])
                ) {
                    unset($thing_array[$i]);
                }
            }
        }

        return $thing_array;
    }



    /**
     *
     */
    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/deduplicate';

        $web = '<br>DEDUPLICATE says, "' . $this->sms_message . '".';

        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    function makeSMS()
    {
        //if (!isset($this->text) or ($this->text == 'Invalid input' ) or ($this->text == null)) {
        //    $sms = "N6 | Request not processed. Check syntax.";
        //} else {

        $sms = "DEDUPLICATE | " . $this->response;

        //}

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     */
    function makeMessage()
    {
        $message = "Deduplicate. ";
        $message .= $this->response;

        $this->thing_report['message'] = $message;

        return;
    }
/*
    function assert($search, $input)
    {
        $search = strtolower($search);
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), $search . " is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen($subject . " is")
            );
        } elseif (($pos = strpos(strtolower($input), $search)) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen($search));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        return $filtered_input;
    }
*/
    /**
     *
     */
    public function readSubject()
    {
        //if ($this->input == "deduplicate") {return;}

        $input = $this->input;
        $filtered_input = $this->assert("deduplicate", $input);

        if ($filtered_input == "") {
            return;
        }

        $this->setAgent($filtered_input);
    }
}
