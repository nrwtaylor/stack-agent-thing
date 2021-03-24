<?php
namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Cue extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->keyword = "cue";

        $this->keywords = ["cue", "is"];
    }

    public function get() {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables cue " . $this->from
        );

    }

    public function set()
    {
        if (!isset($this->alias_thing)) {
            $this->alias_thing = $this->thing;
        }

        $this->variables_agent->setVariable("headcode", $this->head_code);
        $this->variables_agent->setVariable("context", $this->context);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->thing->json->writeVariable(
            ["cue", "headcode"],
            $this->head_code
        );
        $this->thing->json->writeVariable(["alias", "context"], $this->context);
        $this->thing->json->writeVariable(
            ["cue", "refreshed_at"],
            $this->current_time
        );

        $this->refreshed_at = $this->current_time;
    }

    function getContext()
    {
        $this->context_agent = new Context($this->thing, "context");
        $this->context = $this->context_agent->context;
        $this->context_id = $this->context_agent->context_id;
        return $this->context;
    }

    function getAlias($input = null)
    {
        // Extract everything to the right
        // of the first is or =
        $pieces = explode(" ", strtolower($input));

        if ($input == null) {
            $this->alias = "X";
            return $this->alias;
        } else {
            $keywords = $this->keywords;
            foreach ($pieces as $key => $piece) {
                switch ($piece) {
                    case "=":
                    case "is":
                        $key += 1;
                        $t = "";
                        while ($key < count($pieces)) {
                            //$key = $key +1;
                            $t .= $pieces[$key] . " ";
                            $key += 1;
                        }
                        $this->alias = $t;
                        return $this->alias;
                }
            }
        }

        $this->alias = "X";
        return $this->alias;
    }

    function getHeadcode()
    {
        if (isset($this->head_code) and isset($this->headcode_thing)) {
            return $this->head_code;
        }

        $this->head_code = "0O00";

        return $this->head_code;

        $this->headcode_thing = new Headcode(
            $this->variables_agent->thing,
            "headcode " . $this->input
        );
        $this->head_code = $this->headcode_thing->head_code;

        //if ($this->head_code == false) { // Didn't return a useable headcode.
        //    // So assign a 'special'.
        //    //$this->head_code = "0Z" . str_pad($this->index + 11,2, '0', STR_PAD_LEFT);
        //    $this->head_code = "2Z99";
        //}

        // Not sure about the direct variable
        // probably okay if the variable is renamed to variable.  Or if $headcode_thing
        // resolves to the variable.

        return $this->head_code;
    }

    public function respondResponse()
    {
        if ($this->agent_input != null) {
            return true;
        }
        $this->thing->flagGreen();
        $this->thing_report["choices"] = false;

        $sms_message = "CUE ";
        $sms_message .= " | context " . ucwords($this->context);
        $sms_message .=
            " | rtime " . number_format($this->thing->elapsed_runtime()) . "ms";
        $sms_message .= " | TEXT ?";

        $test_message = 'Last thing heard: "' . $this->subject . '".';
        $test_message .= "<br>" . $sms_message;

        $this->thing_report["sms"] = $sms_message;
        $this->thing_report["email"] = $sms_message;
        $this->thing_report["message"] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] =
            "This is the Cueing manager.  Normally only gets called when a Cue has not been recognized.";
    }

    public function readSubject()
    {
        $this->getContext();
    }
}
