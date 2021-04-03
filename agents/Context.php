<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Context extends Agent
{
    // This is the Context manager.

    // Usage:
    // Context - return the current users context.

    public $var = "hello";

    public function init()
    {
        $this->keyword = "context";

        $this->contexts = [
            "identity" => "uuid",
            "headcode" => "head_code",
            "train" => "head_code",
            "transit" => "transit_id",
            "circus" => null,
            "event" => null,
            "place" => null,
            "group" => "group_id",
        ];

        $this->node_list = ["off" => ["on" => ["off"]]];

        $this->current_time = $this->thing->json->time();

        $this->keywords = ["context"];

        $this->verbosity = 9;

        $this->requested_state = false;
        $this->index = 0;
    }

    function set()
    {
        // This makes sure that
        if (!isset($this->context_thing)) {
            $this->context_thing = $this->thing;
        }

        $requested_state = $this->requested_state;
        // Update calculated variables.

        $this->context_thing->json->writeVariable(
            ["context", "state"],
            $requested_state
        );

        if (isset($this->context)) {
            $this->context_thing->json->writeVariable(
                ["context", "context"],
                $this->context
            );
        }
        if (isset($this->context_id)) {
            $this->context_thing->json->writeVariable(
                ["context", "context_id"],
                $this->context_id
            );
        }
        if (isset($this->variables_agent)) {
            $this->variables_agent->setVariable(
                "refreshed_at",
                $this->current_time
            );
        }

        $this->thing->json->writeVariable(
            ["context", "state"],
            $requested_state
        );

        if (isset($this->context)) {
            $this->thing->json->writeVariable(
                ["context", "context"],
                $this->context
            );
        }
        if (isset($this->context_id)) {
            $this->thing->json->writeVariable(
                ["context", "context_id"],
                $this->context_id
            );
        }
        $this->thing->json->writeVariable(
            ["context", "refreshed_at"],
            $this->current_time
        );

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;

        $this->thing->log(
            $this->agent_prefix .
                "deduced " .
                $this->context .
                " " .
                $this->context_id .
                "."
        );
    }

    function getContexts()
    {
        $this->default_context = "identity";
        $this->context = $this->default_context;
        $this->context_id = 1;

        $context_things = [];
        $this->previous_contexts = [];

        // See if a context record exists.
        $findagent_thing = new Findagent($this->thing, "thing");
        $this->max_index = 0;
        $match = 0;
        $things = $findagent_thing->thing_report["things"];
        if (!isset($things)) {
            return $this->context;
        }

        if ($things == true) {
            return;
        }

        foreach ($things as $thing_object) {
            $ref_time = microtime(true);
            $uuid = $thing_object["uuid"];

            if ($thing_object["nom_to"] != "usermanager") {
                $match += 1;

                $variables_json = $thing_object["variables"];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if (isset($variables["train"]["head_code"])) {
                    $this->context = "train";
                    $this->context_id = $variables["train"]["head_code"];
                    break;
                }

                if (isset($variables["headcode"]["head_code"])) {
                    $this->context = "headcode";
                    $this->context_id = $variables["headcode"]["head_code"];
                    break;
                }

                if (isset($variables["group"]["group_id"])) {
                    $this->context = "group";
                    $this->context_id = $variables["group"]["group_id"];
                    break;
                }

                if (isset($variables["transit"]["transit_id"])) {
                    $this->context = "transit";
                    $this->context_id = $variables["group"]["transit_id"];
                    break;
                }

                $run_time = microtime(true) - $ref_time;
                $milliseconds = round($run_time * 1000);
                if ($this->verbosity == 9) {
                    $this->thing->log(
                        $this->agent_prefix .
                            " context get forloop " .
                            $milliseconds .
                            "ms."
                    );
                }

                if ($match >= 20) {
                    $this->context = null;
                    $this->context_id = null;
                    break;
                }
            }
        }

        $thing = new Thing($uuid);

        $this->variables_agent = new Variables(
            $thing,
            "variables context " . $this->from
        );
        $this->variables_agent->getVariables();

        $this->thing->log(
            $this->agent_prefix .
                "looked at " .
                $match .
                " Things before finding " .
                $this->context_id .
                " one with " .
                $this->context .
                " Context."
        );

        return $this->context;
    }

    function get($train_time = null)
    {
        $this->getContexts();

        $this->thing->log(
            $this->agent_prefix .
                ". Timestamp " .
                number_format($this->thing->elapsed_runtime()) .
                "ms.",
            "OPTIMIZE"
        );

        $this->set();
    }

    function makeContext($context = null)
    {
        if ($context == null) {
            $this->context = "identity";
        }

        $this->thing->log(
            $this->agent_prefix .
                "will make an Context with " .
                $this->context .
                ".",
            "INFORMATION"
        );

        // Check that the shift is okay for making aliases.

        $shift_override = true;
        $shift_state = "off";
        if (
            $shift_state == "off" or
            $shift_state == "null" or
            $shift_state == "" or
            $shift_override
        ) {
            // Only if the shift state is off can we
            // create blocks on the fly.

            // Otherwise we needs to make trains to run in the block.

            $this->thing->log(
                $this->agent_prefix . "found that this is the Off shift."
            );

            // So we can create this block either from the variables provided to the function,
            // or leave them unchanged.

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            $this->context = $context;

            //?
            $this->variables_thing = $this->thing;
        } else {
            $this->thing->log(
                $this->agent_prefix .
                    " checked the shift state: " .
                    $shift_state .
                    "."
            );
            // ... and decided there was already a shift running ...
            $this->context = "meep";
        }

        $this->set();

        $this->thing->log(
            $this->agent_prefix . "found an context and made a Context entry."
        );
    }

    function extractContext()
    {
        foreach ($this->contexts as $context => $context_id) {
            if (
                stripos(
                    strtolower($this->subject . " " . $this->agent_input),
                    strtolower($context)
                ) !== false
            ) {
                $this->context = $context;
                $this->context_id = $context_id;
                return;
            }
        }

        set_error_handler([$this, "warning_handler"], E_WARNING);

        foreach ($this->contexts as $context => $context_id) {
            $agent_class_name = ucfirst($context);
            $agent_namespace_name =
                "\\Nrwtaylor\\StackAgentThing\\" . $agent_class_name;

            try {
                $agent = new $agent_namespace_name($this->thing, "extract");

                if (isset($agent->{$context_id})) {
                    $success = true;
                    break;
                }
            } catch (\Exception $e) {
                $success = false;
            }
        }

        restore_error_handler();

        $this->context = $context;
        $this->context_id = $agent->{$context_id};

        $this->thing->log(
            $this->agent_prefix .
                "extracted " .
                $this->context .
                " " .
                $this->context_id .
                ".",
            "INFORMATION"
        );
    }
    /*
    function getVariable($variable_name = null, $variable = null)
    {
        // This function does a minor kind of magic
        // to resolve between $variable, $this->variable,
        // and $this->default_variable.

        // Doesn't yet do it's magic with...
        // identity_variable
        // thing_variable
        // stack_variable

        // Prefer closest...
        // Or prefer furthest ...

        if ($variable != null) {
            // Local variable found.
            // Local variable takes precedence.
            return $variable;
        }

        if (isset($this->$variable_name)) {
            // Class variable found.
            // Class variable follows in precedence.
            return $this->$variable_name;
        }

        // Neither a local or class variable was found.
        // So see if the default variable is set.
        if (isset($this->{"default_" . $variable_name})) {
            // Default variable was found.
            // Default variable follows in precedence.
            return $this->{"default_" . $variable_name};
        }

        // Return false ie (false/null) when variable
        // setting is found.
        return false;
    }
*/
    function getContext()
    {
        $this->getHeadcode();
        $head_code = $this->headcode_thing->head_code;

        if ($head_code == null) {
            $this->context = "train";
            $this->context_id = "ivor";
            $this->alias_id = "ivor";
        } else {
            $this->context = "train";
            $this->context_id = $head_code;
            $this->alias_id = $this->context_id;
        }

        return $this->context;
    }

    function getHeadcode()
    {
        if (isset($this->head_code) and isset($this->headcode_thing)) {
            return $this->head_code;
        }

        //$this->headcode_thing = new Headcode($this->variables_agent->thing, 'headcode '. $this->input);
        $this->headcode_thing = new Headcode(
            $this->variables_agent->thing,
            "extract"
        );

        $this->head_code = $this->headcode_thing->head_code;

        return $this->head_code;
    }

    function addContext()
    {
        $this->makeContext();
        $this->get();
    }

    public function makeSMS()
    {
        $sms_message = "CONTEXT IS " . strtoupper($this->context);

        $sms_message .= " | context id " . $this->context_id;
        //        $sms_message .= " | nuuid " . substr($this->variables_agent->variables_thing->uuid,0,4);
        $sms_message .=
            " | ~rtime " .
            number_format($this->thing->elapsed_runtime()) .
            "ms";
        $sms_message .= " | TEXT ?";

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
        return;
    }

    public function makeTXT()
    {
        $txt = "This is the CONTEXT. ";

        $txt .= "\n";
        $txt .= count($this->previous_contexts) . " Contexts retrieved.";
        $txt .= "\n";

        $txt .= "Context is " . $this->context . " " . $this->context_id . ".";

        $txt .= "\n";
        $txt .= str_pad("INDEX", 7, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("CONTEXT", 10, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("CONTEXT ID", 10, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("TASK", 53, " ", STR_PAD_RIGHT);

        $txt .= "\n";
        $txt .= "\n";

        foreach ($this->previous_contexts as $key => $context) {
            $txt .= str_pad($train["index"], 7, "0", STR_PAD_LEFT);
            $txt .=
                " " .
                str_pad(
                    strtoupper($context["context"]),
                    10,
                    " ",
                    STR_PAD_RIGHT
                );
            $txt .=
                " " .
                str_pad(strtoupper($context["id"]), 10, " ", STR_PAD_LEFT);
            $txt .=
                " " .
                str_pad(strtoupper($context["task"]), 53, " ", STR_PAD_RIGHT);

            $txt .= "\n";
        }

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["choices"] = false;

        $test_message = 'Last thing heard: "' . $this->subject . '"';
        $test_message .= "<br>Train state: " . $this->state . "<br>";
        $test_message .= "<br>" . $this->sms_message;

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report["info"] = $message_thing->thing_report["info"];
        $this->thing_report["help"] = "This is the context extractor.";
    }

    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;

        $this->extractContext();
        if ($this->context_id != null) {
            return;
        }

        if (strtolower($this->agent_input) == "extract") {
            $this->getContexts();
            return;
        }

        $keywords = $this->keywords;

        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        $this->input = $input;

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($this->input == "context") {
                $this->set();
                return;
            }

            if ($this->input == "train") {
                $this->makeContext("train");
                $this->set();
                return;
            }
        }

        // Look here if there is a problem with Context. NRWTAYLOR 6 Dec 2017
        //if ($matches == 1) {
        //    $this->context = $piece;
        //    $this->num_hits += 1;
        //}

        /*
    if ((isset($this->run_time)) and (isset($this->run_at))) {
        // Good chance with both these set that asking for a new
        // block to be created, or to override existing block.
        $this->thing->log('Agent "Block" found a run time.');

        $this->nextBlock();
        return;
    }
*/
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "accept":
                            $this->acceptThing();
                            break;

                        case "clear":
                            $this->clearThing();
                            break;

                        case "next":
                            $this->thing->log("read subject next Context");
                            $this->nextContext();
                            break;

                        case "drop":
                            $this->dropContext();
                            break;

                        case "add":
                            $this->makeContext();
                            break;

                        case "run":
                            $this->runContext();
                            break;

                        case "is":
                            $this->context = $this->input;
                            $this->makeContext($this->context);
                            $this->set();
                            break;

                        default:
                    }
                }
            }
        }

        if ($pieces[0] == "context") {
            $this->makeContext($this->input);
            $this->set();
            //$this->alias = "meepmeep";
            return;
        }

        if (isset($this->context)) {
            //$this->thing->log('Agent "Block" found a run_at and a run_time and made a Block.');
            // Likely matching a head_code to a uuid.
            $this->makeContext($this->context);
            return;
        }

        //    if ((isset($this->run_time)) and (isset($this->run_at))) {
        // Good chance with both these set that asking for a new
        // block to be created, or to override existing block.
        //        $this->thing->log('Agent "Block" found a run time.');

        //        $this->nextBlock();
        //        return;
        //    }

        // If all else fails try the discriminator.

        $this->requested_state = $this->discriminateInput($haystack); // Run the discriminator.
        switch ($this->requested_state) {
            case "start":
                $this->start();
                break;
            case "stop":
                $this->stop();
                break;
            case "reset":
                $this->reset();
                break;
            case "split":
                $this->split();
                break;
        }

        //    $this->read();
        $this->set();

        return "Message not understood";

        return false;
    }

    function discriminateInput($input, $discriminators = null)
    {
        //$input = "optout opt-out opt-out";

        if ($discriminators == null) {
            $discriminators = ["accept", "clear"];
        }

        $default_discriminator_thresholds = [2 => 0.3, 3 => 0.3, 4 => 0.3];

        if (count($discriminators) > 4) {
            $minimum_discrimination = $default_discriminator_thresholds[4];
        } else {
            $minimum_discrimination =
                $default_discriminator_thresholds[count($discriminators)];
        }

        $contexts = [];

        $contexts["accept"] = ["accept", "add", "+"];
        $contexts["clear"] = ["clear", "drop", "clr", "-"];

        $words = explode(" ", $input);

        $count = [];

        $total_count = 0;
        // Set counts to 1.  Bayes thing...
        foreach ($discriminators as $discriminator) {
            $count[$discriminator] = 1;

            $total_count = $total_count + 1;
        }
        // ...and the total count.

        foreach ($words as $word) {
            foreach ($discriminators as $discriminator) {
                if ($word == $discriminator) {
                    $count[$discriminator] = $count[$discriminator] + 1;
                    $total_count = $total_count + 1;
                }

                foreach ($contexts[$discriminator] as $context) {
                    if ($word == $context) {
                        $count[$discriminator] = $count[$discriminator] + 1;
                        $total_count = $total_count + 1;
                    }
                }
            }
        }

        // Set total sum of all values to 1.

        $normalized = [];
        foreach ($discriminators as $discriminator) {
            $normalized[$discriminator] = $count[$discriminator] / $total_count;
        }

        // Is there good discrimination
        arsort($normalized);

        // Now see what the delta is between position 0 and 1

        foreach ($normalized as $key => $value) {

            if (isset($max)) {
                $delta = $max - $value;
                break;
            }
            if (!isset($max)) {
                $max = $value;
                $selected_discriminator = $key;
            }
        }

        if ($delta >= $minimum_discrimination) {
            return $selected_discriminator;
        } else {
            return false; // No discriminator found.
        }

        return true;
    }

    function warning_handler($errno, $errstr, $errfile, $errline)
    {
        //throw new \Exception('Class not found.');
        //trigger_error("Fatal error", E_USER_ERROR);
        // do something
    }
}
