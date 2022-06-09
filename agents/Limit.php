<?php
namespace Nrwtaylor\StackAgentThing;

class Limit extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables limit " . $this->from
        );
    }

    function run()
    {
        //        $this->doLimit();
    }

    public function makeLimit()
    {

if (!isset($this->limit_tokens)) {$this->limit_tokens = $this->last_limit_tokens;}

        if ($this->agent_input == null) {
            //            $array = array('miao', 'miaou', 'hiss', 'prrr', 'grrr');
            //            $k = array_rand($array);
            //            $v = $array[$k];
            $v = "None";

            if (
                isset($this->limit_tokens) and
                is_array($this->limit_tokens) and
                count($this->limit_tokens) > 0
            ) {
                $v = implode(" ", $this->limit_tokens);
            }

            $response = strtolower($v) . ".";

            $this->limit_message = $response; // mewsage?
        } else {
            $this->limit_message = $this->agent_input;
        }
    }

    public function set()
    {
if (!isset($this->limit_tokens)) {return;}

        //$this->variables_agent->setVariable("tokens", $this->limit_tokens);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    public function get()
    {
        $last_limit_tokens = $this->variables_agent->getVariable("tokens");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->last_limit_tokens = $last_limit_tokens;
        if ($last_limit_tokens == false) {
            $this->last_limit_tokens = [];
        }

    }

    function makeSMS()
    {
        $this->sms_message =
            strtoupper($this->agent_name) .
            " | " .
            (isset($this->limit_message) ? $this->limit_message . " " : "") .
            $this->response;
        $this->thing_report["sms"] = $this->sms_message;
    }

    public function tokensLimit() {
       $limit_tokens = [];
       if (isset($this->limit_tokens)) {$limit_tokens = $this->limit_tokens;}
       return $limit_tokens;

    }

    public function readSubject()
    {

        $filtered_input = $this->assert($this->input);

if ($filtered_input == "") {
$this->response .= "Saw request for limits. ";
return;}

        $tokens = $this->extractTokens($filtered_input);
        // Respond to certain tokens.


        foreach ($tokens as $i => $token) {
            if (strtolower($token) === "clear") {
                $this->limit_tokens = [];
                $this->response .= "Cleared limit tokens. ";
                return;
            }

        }

        $this->limit_tokens = array_merge($this->last_limit_tokens, $tokens);
        $this->limit_tokens = array_unique($this->limit_tokens);
        $this->response .= "Added tokens. ";
    }
}
