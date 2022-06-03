<?php
/*
 * Agent.php
 *
 * @package default
 */
namespace Nrwtaylor\StackAgentThing;

// Agent resolves message disposition

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

define("MAX_EXECUTION_TIME", 2); # seconds
class Agent
{
    public $input;

    /**
     *
     * @param Thing   $thing
     * @param unknown $input (optional)
     */
    function __construct(Thing $thing = null, $input = null)
    {

        $this->error = null;
        $this->status = 'loading';

        //        if ($thing == false) {
        //           $thing = new Thing(false);
        //$this->thing = false;
        //return;
        //        }

        $this->getName();

        if ($thing == null and isset($input["uuid"])) {
            // If the stack was able to pull a thing,
            // then we would have it.
            // So assume that it couldn't.

            // Return making the agent class available.
            return;
        }

        if ($thing == null) {
            $thing = new Thing(null);
        }

        // Start the thing timer.
        $this->start_time = $thing->elapsed_runtime();

        $this->agent_input = $input;
        if (is_array($input)) {
            $this->agent_input = $input;
        }
        if (is_string($input)) {
            $this->agent_input = $input;
        }

        $this->agent_prefix = 'Agent "' . ucfirst($this->agent_name) . '" ';
        // Given a "thing".  Instantiate a class to identify
        // and create the most appropriate agent to respond to it.

        $this->thing = $thing;

        $this->thing->agent_class_name_current = $this->agent_class_name;

        $this->thing_report["thing"] = $this->thing;

        $this->thing->agent_name = $this->agent_class_name;

        if (!isset($this->thing->run_count)) {
            $this->thing->run_count = 0;
        }

        $a = explode("\\", get_class($this))[2];
        $this->thing->log($a);
        $this->thing->log(
            "Saw thing run count is " . $this->thing->run_count . "."
        );

        $this->thing->log("Got thing.");
        // So I could call
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->dev = true;
            $this->test = true;
        }
        if ($this->thing->container["stack"]["engine_state"] == "dev") {
            $this->dev = true;
        }

        $this->metaAgent();

        $this->thing->log("Got meta.");
        // Tell the thing to be quiet
        if ($this->agent_input != null) {
            //            $this->thing->silenceOn();
            //            $quiet_thing = new Quiet($this->thing,"quiet on");
        }

        //$is_email = $this->isEmail($input);

        // Get some stuff from the stack which will be helpful.

        // dev test

        // TODO define all default null settings
        $this->default_font = null;
        $this->default_pdf_page_template = null;

        $stack_settings = $thing->container["stack"];
        foreach ($stack_settings as $setting_name => $setting_value) {
            // For 'backwards' compatibility.
            //$this->{$setting_name} = $thing->container['stack'][$setting_name];

            // Going forward set default_ and stack_ prefixes
            // For settings from stack private settings.
            $this->{"default_" . $setting_name} =
                $thing->container["stack"][$setting_name];
            $this->{"stack_" . $setting_name} =
                $thing->container["stack"][$setting_name];
        }

        $this->web_prefix = $thing->container["stack"]["web_prefix"];
        $this->mail_postfix = $thing->container["stack"]["mail_postfix"];
        $this->word = $thing->container["stack"]["word"];
        $this->email = $thing->container["stack"]["email"];

        // And some more stuff
        $this->short_name = $thing->container["stack"]["short_name"];

        $this->stack_state = $thing->container["stack"]["state"];

        $this->stack_engine_state = $thing->container["stack"]["engine_state"];

        $this->entity_name = $thing->container["stack"]["entity_name"];

        $this->default_font = null;
        if (isset($this->thing->container["stack"]["font"])) {
            $this->default_font = $this->thing->container["stack"]["font"];
        }

        $this->default_pdf_page_template = null;
        if (isset($this->thing->container["stack"]["pdf_page_template"])) {
            $this->default_pdf_page_template =
                $this->thing->container["stack"]["pdf_page_template"];
        }

        $this->sqlresponse = null;

        $this->thing->log("running on Thing " . $this->thing->nuuid . ".");

        $this->resource_path = $GLOBALS["stack_path"] . "resources/";
        $this->agents_path = $GLOBALS["stack_path"] . "agents/";
        $this->agents_path =
            $GLOBALS["stack_path"] .
            "vendor/nrwtaylor/stack-agent-thing/agents/";

        if (
            isset($this->thing->container["api"][strtolower($this->agent_name)])
        ) {
            $this->settings =
                $this->thing->container["api"][strtolower($this->agent_name)];
        }

        $this->agent_version = "redpanda";

        // TODO

        //$this->time_agent = new Time($this->thing,"time");
        $this->current_time = $this->thing->time();

        $this->num_hits = 0;

        $this->verbosity = 9;

        $this->context = null;

        $this->error = "";
        $this->warning = "";
        $this->response = "";

        if (isset($thing->container["api"]["agent"])) {
            if ($thing->container["api"]["agent"] == "off") {
                return;
            }
        }

        if (isset($this->dev) and $this->dev == true) {
            $this->debug();
        }
        // First things first... see if Mordok is on.
        /* Think about how this should work and the user UX/UI
            $mordok_agent = new Mordok($this->thing);

            if ($mordok_agent->state == "on") {

        $thing_report = $this->readSubject();

        $this->respond();

} else {
// Don't

}
*/

        //
        //$this->getAccounts();
        /*
        $this->getAccounts();
        if (!$this->isAccount('thing')) {
           $this->newAccount(['name'=>'thing', 'amount'=>0]);
        }

        if (!$this->isAccount('stack')) {
           $this->newAccount(['name'=>'stack', 'amount'=>0]);
        }
*/

        $this->init();

        // read the current agent.
        if (method_exists($this, "init" . $this->agent_class_name)) {
            $this->{"init" . $this->agent_class_name}();
        }
        $this->thing->log("completed init.");

        $this->get();
        $this->thing->log("completed get.");

        try {
            $this->read();
            $this->thing->log("completed read.");

            $this->run();
            $this->thing->log("completed run.");

            $this->make();
            $this->thing->log("completed make.");

            $this->set();
            $a = explode("\\", get_class($this))[2];
            $this->thing->log($a);
            $this->thing->log("completed set.");
        } catch (\OverflowException $t) {
            $this->response =
                "Stack variable store is full. Variables not saved. Text FORGET ALL.";
            $web_thing = new Thing(null);
            $web_thing->Create(
                $this->from,
                "error",
                "Overflow: try set failed."
            );
            $this->thing_report["sms"] = "STACK | " . $this->response;
            $this->thing->log("caught overflow exception.");
            // Executed only in PHP 7, will not match in PHP 5
        } catch (\Throwable $t) {
            $this->thing_report["sms"] = $this->textError($t);
            $this->thingError($t);
            /*
            $this->thing_report["sms"] = $t->getMessage();
            $web_thing = new Thing(null);
            $web_thing->Create(
                $this->from,
                "error",
                "Throwable: Set failed. " .
                    $t->getMessage() .
                    " " .
                    $t->getTraceAsString()
            );
*/
            //$error_text =
            //    $t->getLine() . "---" . $t->getFile() . $t->getMessage();
            $error_text = $this->textError($t);
            $this->thing->console($error_text . "\n");
            $this->thing->log($error_text, "ERROR");
            // Executed only in PHP 7, will not match in PHP 5
        } catch (\Exception $e) {
            $error_text =
                $t->getLine() . "---" . $t->getFile() . $t->getMessage();

            $web_thing = new Thing(null);
            $web_thing->Create(
                $this->from,
                "error",
                "Exception: Set failed. " .
                    $e->getMessage() .
                    " " .
                    $t->getTraceAsString()
            );
            $this->thing->console($error_text . "\n");
            $this->thing->log($error_text, "ERROR");
            // Executed only in PHP 5, will not be reached in PHP 7
        }

        if ($this->agent_input == null or $this->agent_input == "") {
            $this->respond();
        }

        if (!isset($this->response)) {
            $this->response = "No response found.";
        }
        $this->thing_report["response"] = $this->response;

        $this->thing->log(
            "ran for " . number_format($this->thing->elapsed_runtime()) . "ms."
        );
        $this->thing_report["etime"] = number_format(
            $this->thing->elapsed_runtime()
        );
        $this->thing_report["log"] = $this->thing->log;
        if (isset($this->test) and $this->test) {
            $this->thing->log("start test");
            $this->test();
        }
        $this->thing->log($a);
        $this->thing->log("__construct complete");
    }

    function __destruct()
    {
        if (!isset($this->thing)) {
            return;
        }

        $this->thing->log(
            $this->agent_prefix .
                "ran for " .
                number_format(
                    $this->thing->elapsed_runtime() - $this->start_time
                ) .
                "ms."
        );
    }

    public function errorAgent($text = null)
    {
        if ($text == null) {
            return;
        }

        $this->statusMysql('error');
        $this->error = $text;

        if (!isset($this->response)) {
            $this->response = "";
        }
        $this->response .= $text . " ";
    }

    public function statusAgent($text = null)
    {
        if ($text != null) {
            $this->status = $text;
        }
        return $this->status;
    }

    public function isReadyAgent()
    {
        if (isset($this->status) and $this->status == 'ready') {
            return true;
        }
        return false;
    }


    public function variantsAgent(
        $agent_class_name,
        $_namespace = "\\Nrwtaylor\\StackAgentThing\\"
    ) {
        $agent_namespace_name = $_namespace . $agent_class_name;

        // See if the method exists within the function.
        // Call it if we find it.

        $agent_namespace_names[] = $agent_namespace_name;
        $agent_namespace_names[] = $_namespace . strtoupper($agent_class_name);

        // Try plural and singular variants of agent name.
        if (substr($agent_namespace_name, -3) == "ies") {
            $agent_namespace_names[] =
                rtrim($agent_namespace_name, "ies") . "y";
        } elseif (substr($agent_namespace_name, -2) == "es") {
            $agent_namespace_names[] = rtrim($agent_namespace_name, "es");
            $agent_namespace_names[] = rtrim($agent_namespace_name, "es") . "e";
        } elseif (substr($agent_namespace_name, -1) == "s") {
            $agent_namespace_names[] = rtrim($agent_namespace_name, "s");
        } elseif (substr($agent_namespace_name, -1) == "y") {
            $agent_namespace_names[] =
                rtrim($agent_namespace_name, "y") . "ies";
        } else {
            $agent_namespace_names[] = $agent_namespace_name . "s";
            $agent_namespace_names[] = $agent_namespace_name . "es";
        }
        return $agent_namespace_names;
    }

    public function initAgent()
    {
    }
    /*
    public function settingsAgent($settings_array, $default_setting = null)
    {
        $t = $this->thing->container["api"];
        foreach ($settings_array as $setting) {
            if (!isset($t[$setting])) {
                return $default_setting;
            }

            $t = $t[$setting];
        }
        return $t;
    }
*/
    // TODO DEV?
    public function __call($agent_function, $args)
    {
        if (!isset($this->thing)) {
            return true;
        }

        $this->thing->log("__call started.");

        //        $this->thing->log("__call start");
        /*
        Generalize this pattern from agents.
        $agent_handler = new $agent_namespace_name($this->thing, $agent_input);
        $response = $agent_handler->functionAgent($text);

        Replace with instead.
        $response = $this->functionAgent($text);

        And add a generic useAgent call if there is no useAgent method in the class.
       */
        $pieces = preg_split("/(?=[A-Z])/", $agent_function, 2);

        $agent_class_name = "Agent";
        if (isset($pieces[1])) {
            $agent_class_name = $pieces[1];
        }

        $agent_name = strtolower($agent_class_name);

        // Get the first "bit" of the function name.
        // ie use from useHook.
        $function_primitive_name = "use";
        if (isset($pieces[0])) {
            $function_primitive_name = $pieces[0];
        }

        //    $this->thing->log(
        //        "Check if " . $agent_name . " == " . $this->agent_name
        //    );
        if ($agent_name == $this->agent_name) {
            $this->thing->log("__call saw agent name is the same.");

            return false;
        }

        // Looking for the function in the namespace functionAgent.
        $function_name = $agent_function;

        // Allow for customizing this later.
        $agent_input = $agent_name;

        // dev test
        //$agent_input = $this->agent_input;
        //if ($this->agent_input == null) {$agent_input = $agent_name;}

        // Namespaced class.
        $agent_namespace_name =
            "\\Nrwtaylor\\StackAgentThing\\" . $agent_class_name;

        $agent_namespace_names = $this->variantsAgent(
            $agent_class_name,
            "\\Nrwtaylor\\StackAgentThing\\"
        );

        // See if the method exists within the function.
        // Call it if we find it.
        /*
        $agent_namespace_names[] = $agent_namespace_name;
        $agent_namespace_names[] =
            "\\Nrwtaylor\\StackAgentThing\\" . strtoupper($agent_class_name);

        // Try plural and singular variants of agent name.
        if (substr($agent_namespace_name, -3) == "ies") {
            $agent_namespace_names[] =
                rtrim($agent_namespace_name, "ies") . "y";
        } elseif (substr($agent_namespace_name, -2) == "es") {
            $agent_namespace_names[] = rtrim($agent_namespace_name, "es");
            $agent_namespace_names[] = rtrim($agent_namespace_name, "es") . "e";
        } elseif (substr($agent_namespace_name, -1) == "s") {
            $agent_namespace_names[] = rtrim($agent_namespace_name, "s");
        } elseif (substr($agent_namespace_name, -1) == "y") {
            $agent_namespace_names[] =
                rtrim($agent_namespace_name, "y") . "ies";
        } else {
            $agent_namespace_names[] = $agent_namespace_name . "s";
            $agent_namespace_names[] = $agent_namespace_name . "es";
        }
*/

        foreach (
            $agent_namespace_names
            as $i => $agent_namespace_name_variant
        ) {
            if (method_exists($agent_namespace_name_variant, $function_name)) {
                /*
                $agent_handler = new $agent_namespace_name_variant(
                    $this->thing,
                    $agent_input
                );
                $response = $agent_handler->{$function_name}(...$args);
*/
                // Test optimize by only initiating once.
                if (!isset($this->thing->{$agent_name . "_handler"})) {
                    $this->thing->{$agent_name .
                        "_handler"} = new $agent_namespace_name_variant(
                        $this->thing,
                        $agent_input
                    );
                }
                $response = $this->thing->{$agent_name .
                    "_handler"}->{$function_name}(...$args);
                //              $this->thing->log("__call response complete");
                $this->thing->log("__call got method response.");

                return $response;
            }
        }

        // No functionAgent found in the namespace.
        if (class_exists($agent_namespace_name)) {
            // No functionAgent found in the namespace.
            // ie flerpMerp

            if ($function_primitive_name == "use") {
                // But we did see a request for the use function.

                // Consider this as a starting point for all agents.
                $agent_handler = new $agent_namespace_name(
                    $this->thing,
                    $agent_input . implode(" ", $args)
                );
                $agent_handler->init();
                $agent_handler->read(...$args);
                $agent_handler->run();

                $variable = null;
                if (isset($agent_handler->$agent_name)) {
                    $variable = $agent_handler->$agent_name;
                }
                $this->thing->log("__call hook return");
                return [$variable, $agent_handler];
            }
        }
        throw new \Exception(
            "Agent (" .
                $this->agent_name .
                ") called for a non-existent functionAgent [" .
                $agent_function .
                "]. "
        );

        $this->thing->log("__call complete");
    }

    // dev exploration
    /*
public function __set($name, $value) {
//	if ((stripos($name, "prior_thing")) and (!isset($this->$$name))) {
//		throw new \Exception($name.' thing does not exist');
//	}

}
*/

    /**
     *
     */
    public function init()
    {
    }

    /**
     *
     */
    public function get()
    {
        $this->refreshedatAgent();
    }

    public function refreshedatAgent()
    {
        $agent_name = strtolower($this->agent_name);
        $time_string = $this->thing->Read([$agent_name, "refreshed_at"]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write([$agent_name, "refreshed_at"], $time_string);
        }
    }

    /**
     *
     */
    public function set()
    {
        if ($this->agent_name == "agent") {
            return;
        }
        if (!isset($this->{$this->agent_name})) {
            return true;
        }
        $this->thing->Write([$this->agent_name], $this->{$this->agent_name});
    }

    public function getThings($agent_name = null)
    {
        $things = [];
        if ($agent_name == null) {
            $agent_name = "tick";
        }
        $agent_name = strtolower($agent_name);
        $rules_list = [];
        $this->rules_list = [];
        $this->unique_count = 0;
        $findagent_thing = new Findagent($this->thing, $agent_name);

        if (!is_array($findagent_thing->thing_report["things"])) {
            return;
        }
        $count = count($findagent_thing->thing_report["things"]);

        //$rule_agent = new Rule($this->thing, "rule");

        if ($count > 0) {
            foreach (
                array_reverse($findagent_thing->thing_report["things"])
                as $thing_object
            ) {
                $uuid = $thing_object["uuid"];
        //        $variables_json = $thing_object["variables"];
        //        $variables = $this->thing->json->jsontoArray($variables_json);

                $variables = $thing_object["variables"];


            //    $associations_json = $thing_object["associations"];
            //    $associations = $this->thing->json->jsontoArray(
            //        $associations_json
            //    );

    $associations = $thing_object["associations"];

                //$thing = new \stdClass();
                $thing = new Thing(null);
                $thing->subject = $thing_object["task"];

                $thing->uuid = $thing_object["uuid"];
                $thing->nom_to = $thing_object["nom_to"];
                $thing->nom_from = $thing_object["nom_from"];

                $thing->variables = $variables;
                $thing->created_at = $thing_object["created_at"];

                $thing->associations = $associations;

                if (isset($variables[$agent_name]) or $agent_name == "things") {
                    //                    $things[$uuid] = $variables[$agent_name];
                    $things[$uuid] = $thing;
                }

                $response = $this->readAgent($thing_object["task"]);
            }
        }

        return $things;
    }

    public function isThing($thing)
    {
        if ($thing === null) {
            return false;
        }
        return true;
    }

    public function getVariables($agent_name = null)
    {
        $variables_array = [];
        if ($agent_name == null) {
            $agent_name = "tick";
        }
        $agent_name = strtolower($agent_name);
        $rules_list = [];

        $this->rules_list = [];
        $this->unique_count = 0;

        $findagent_thing = new Findagent($this->thing, $agent_name);
        if (!is_array($findagent_thing->thing_report["things"])) {
            return;
        }
        $count = count($findagent_thing->thing_report["things"]);

        //$rule_agent = new Rule($this->thing, "rule");

        if ($count > 0) {
            foreach (
                array_reverse($findagent_thing->thing_report["things"])
                as $thing_object
            ) {
                $uuid = $thing_object["uuid"];
//                $variables_json = $thing_object["variables"];
//                $variables = $this->thing->json->jsontoArray($variables_json);

                $variables = $thing_object["variables"];


                $variables_array[$uuid] = $variables;
            }
        }
        return $variables_array;
    }

    // Read the provided text and create a set of flags.
    public function flagAgent($indicators = null, $input = null)
    {
        foreach ($indicators as $flag_name => $flag_indicators) {
            foreach ($flag_indicators as $flag_indicator) {
                $f = $this->agent_name . "_" . $flag_name . "_flag";
                if (stripos($input, $flag_indicator) !== false) {
                    $this->{$f} = "on";
                }

                if (
                    stripos($input, str_replace("-", " ", $flag_indicator)) !==
                    false
                ) {
                    $this->{$f} = "on";
                }
            }
        }
    }

    public function memoryAgent($text = null)
    {
        $agent_class_name = $text;
        $agent_name = strtolower($agent_class_name);

        $slug = $this->getSlug($agent_name . "-" . $this->from);

        $agent_namespace_name =
            "\\Nrwtaylor\\StackAgentThing\\" . $agent_class_name;

        ${$agent . "_agent"} = new $agent_namespace_name(
            $this->thing,
            $agent_name
        );

        ${$agent_name} = ${$agent . "_agent"}->{"get" . $agent_class_name}();
        ${$agent_name}["retrieved_at"] = $this->current_time;

        $this->memory->set($slug, ${$agent_name});

        $this->response .= "Got {$agent_name}. ";

        return ${$agent_name};
    }

    public function settingsAgent($settings_array, $default_setting = null)
    {
        $t = $this->thing->container["api"];
        foreach ($settings_array as $setting) {
            if (!isset($t[$setting])) {
                return $default_setting;
            }

            $t = $t[$setting];
        }
        return $t;
    }

    public function readAgent($text = null)
    {
        // devstack
        return true;
    }

    /**
     *
     */
    public function make()
    {
        // Call the classes make function.
        try {
            $this->{"make" . $this->agent_class_name}();
        } catch (\Throwable $t) {
            $this->thing->log(
                "caught make " . $this->agent_class_name . " throwable.",
                "WARNING"
            );
            //return;
        } catch (\Error $ex) {
            $warning_text =
                $t->getLine() .
                "---" .
                $t->getFile() .
                $t->getMessage() .
                " caught make " .
                $this->agent_class_name .
                " error.";
            $this->thing->log($warning_text, "WARNING");
        }

        // So ... don't call yourself.
        // Don't do a make on yourself.
        $this->thing->log("start make.");
        $this->makeAgent();

        $this->makeResponse();
        $this->makeInput();
        //$this->makeChoices();
        $this->makeMessage();

        $this->makeChart();
        $this->makeImage();
        $this->makePNG();
        $this->makePNGs();
        $this->makeJPEG();
        $this->makeJPEGs();

        $this->thing->log("completed make of image channels.");

        $this->makeSMS();

        if (
            isset($this->error) and
            $this->error != "" and
            $this->error != null
        ) {
            $sms = $this->thing_report["sms"];
            $this->sms_message = $sms . " " . $this->error;
            $this->thing_report["sms"] = $sms . " " . $this->error;
        }
        $this->thing->log("completed make of sms channel.");

        // Snippet might be used by web.
        // So run it first.
        $this->makeSnippet();

        $this->thing->log("completed make of snippet channel.");

        $this->makeWeb();
        $this->thing->log(
            "got class name " . explode("\\", strtolower(get_class($this)))[2]
        );
        $this->thing->log("completed make of web channel.");

        $this->makeJson();

        // Explore adding in INFO and HELP to web response.
        $dev_agents = ["response", "help", "info", "sms", "message"];
        $prod_agents = ["response", "help", "info"];

        $agents = $dev_agents;
        if ($this->stack_engine_state == "prod") {
            $agents = $prod_agents;
        }

        $web = "";
        if (isset($this->thing_report["web"])) {
            foreach ($agents as $i => $agent_name) {
                if (
                    !isset($this->thing_report[$agent_name]) or
                    $this->thing_report[$agent_name] == null
                ) {
                    if (isset($this->{$agent_name})) {
                        $this->thing_report[$agent_name] = $this->{$agent_name};
                    }
                }

                if (!isset($this->thing_report[$agent_name])) {
                    continue;
                }

                if ($this->thing_report[$agent_name] == "") {
                    continue;
                }
                // dev stack filter out repeated agent web reports
                $needle = "<b>" . strtoupper($agent_name) . "</b>";
                if (strpos($this->thing_report["web"], $needle) !== false) {
                    continue;
                }

                $web .= "<b>" . strtoupper($agent_name) . "</b><p>";
                $web .= $this->thing_report[$agent_name];
                $web .= "<p>";
            }
        }

        if (isset($this->thing_report["web"])) {
            if ($this->agent_name != "agent") {
                $needle = ucwords($this->agent_name) . " Agent";

                if (strpos($this->thing_report["web"], $needle) !== false) {
                } else {
                    $this->thing_report["web"] =
                        "<b>" .
                        ucwords($this->agent_name) .
                        " Agent" .
                        "</b><br><p>" .
                        $this->thing_report["web"];
                }
            }
            $needle = "<p>";
            $pos = strpos($this->thing_report["web"], $needle);
            $length = strlen($this->thing_report["web"]);
            $needle_length = strlen($needle);

            // Note our use of ===.  Simply == would not work as expected
            // because the position of 'a' was the 0th (first) character.
            if ($pos === false) {
                $this->thing_report["web"] .= "<p>";
            } else {
                if ($pos == $length - $needle_length) {
                } else {
                    //$this->thing_report['web'] .= "<p>";
                }
                $this->thing_report["web"] .= "<p>";
            }

            $this->thing_report["web"] .= "<p>" . $web;
        }

        //$this->makeSnippet();
        $this->makeEmail();
        $this->makeTXT();

        $this->makePDF();

        $this->makeKeyword();
        $this->makeLink();

        if (isset($this->thing_report["png"]) and isset($this->link)) {
            //if (!(($this->thing_report['png'] == false) or ($this->thing_report['png'] == null) or ($this->thing_report['png'] == true))) {

            $this->image_url = $this->link . ".png";
            $this->thing_report["image_url"] = $this->image_url;
            //}
        }

        $this->makeHelp();
        $this->makeInfo();

        // devstack

        if ($this->agent_name != "web" and !isset($this->thing->web_agent)) {
            $this->thing->web_agent = new Web($this->thing, "agent");
            $this->web_state = $this->thing->web_agent->state;
        }

        // Check the web agent to see whether urls should be appended the sms response.
        $web_state = "off";
        if (isset($this->web_state)) {
            $web_state = $this->web_state;
        }
//var_dump($this->thing->web_agent->state);
//var_dump($this->thing_report['link']);
        if (
            isset($this->thing->web_agent->state) and
            $this->thing->web_agent->state == "on"
        ) {
            if (
                isset($this->thing_report["sms"]) and
                //and (!$this->thing->url_agent->hasUrls($this->thing_report['sms']))
                substr($this->thing_report["link"], -4) != "help"
            ) {
                if (substr_count($this->thing_report["sms"], "http") == 0) {
                    $this->thing_report["sms"] =
                        $this->thing_report["sms"] .
                        " " .
                        $this->thing_report["link"];
                }
            }
        }

        $this->makeThingreport();

        if (
            strtolower($this->agent_name) == "agent" and
            isset($this->thing_report)
        ) {
            $variable_name = "thing-report-" . $this->uuid;

            if (!isset($this->thing->refresh_at)) {
                $this->thing->refresh_at = false;
            } // false = request again now.

            $created_at = false;
            if (isset($this->thing->created_at)) {
                $created_at = $this->thing->created_at;
            } // false = request again now.

            $thing_report = $this->thing_report;

            //unset($thing_report['thing']['thing']);
            $t = [
                "uuid" => $this->thing->uuid,
                "to" => $this->thing->to,
                "from" => $this->thing->from,
                "subject" => $this->thing->subject,
                //'agent_input'=>$thing_report['thing']->agent_input,
                "created_at" => $created_at,
                "refresh_at" => $this->thing->refresh_at,
            ];

            $thing_report["thing"] = $t;
            $this->setMemory($variable_name, $thing_report);
        }
    }

    /**
     *
     */
    public function run()
    {
    }

    /**
     *
     * @return unknown
     */
    public function kill()
    {
        // No messing about.
        return $this->thing->Forget();
    }

    /**
     *
     */
    public function test()
    {
        // See if it can run an agent request
        //$agent_thing = new Agent($this->thing, "agent");
        // No result for now
        //$this->test = null;
    }

    public function parse($text = null)
    {
    }

    public function load($resource_name = null)
    {
        if ($resource_name == null) {
            return true;
        }

        $file = $this->resource_path . "" . $resource_name;

        if (!file_exists($file)) {
            return true;
        }

        $contents = file_get_contents($file);

        return $contents;

        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $this->parse($line);
            }
            fclose($handle);
        } else {
            return true;
            // error opening the file.
        }
    }

    /**
     *
     * @return unknown
     */
    public function callingAgent()
    {
        //get the trace
        $trace = debug_backtrace();

        // Get the class that is asking for who awoke it
        if (!isset($trace[1]["class"])) {
            $this->calling_agent = true;
            return true;
        }

        $class_name = $trace[1]["class"];
        // +1 to i cos we have to account for calling this function
        for ($i = 1; $i < count($trace); $i++) {
            if (isset($trace[$i])) {
                if (
                    isset($trace[$i]["class"]) and
                    $class_name != $trace[$i]["class"]
                ) {
                    // is it set?
                    // is it a different class
                    $this->calling_agent = $trace[$i]["class"];
                    return $trace[$i]["class"];
                }
            }
        }

        $this->calling_agent = null;
    }

    public function traceAgent()
    {
        //get the trace
        $agent_trace = [];
        $trace = debug_backtrace();
        foreach ($trace as $i => $t) {
            $agent_trace[] = $t["class"];
        }
        return $agent_trace;
        // Get the class that is asking for who awoke it
        if (!isset($trace[1]["class"])) {
            $this->calling_agent = true;
            return true;
        }

        $class_name = $trace[1]["class"];
        // +1 to i cos we have to account for calling this function
        for ($i = 1; $i < count($trace); $i++) {
            if (isset($trace[$i])) {
                if (
                    isset($trace[$i]["class"]) and
                    $class_name != $trace[$i]["class"]
                ) {
                    // is it set?
                    // is it a different class
                    $this->calling_agent = $trace[$i]["class"];
                    return $trace[$i]["class"];
                }
            }
        }

        $this->calling_agent = null;
    }

    function listAgents()
    {
        $this->agent_list = [];
        $this->agents_list = [];

        // Only use Stackr agents for now
        // Single source folder ensures uniqueness of N-grams
        $dir =
            $GLOBALS["stack_path"] .
            "vendor/nrwtaylor/stack-agent-thing/agents";
        $files = scandir($dir);

        foreach ($files as $key => $file) {
            if ($file[0] == "_") {
                continue;
            }
            if (strtolower(substr($file, 0, 3)) == "dev") {
                continue;
            }

            // Ignore Makejson, Makepdf, etc
            if (strtolower(substr($file, 0, 4)) == "Make") {
                continue;
            }

            if (strtolower(substr($file, -7)) == "handler") {
                continue;
            }

            if (strtolower(substr($file, -4)) != ".php") {
                continue;
            }
            if (!ctype_upper($file[0])) {
                continue;
            }

            $agent_name = substr($file, 0, -4);

            $this->agent_list[] = ucwords($agent_name);
            $this->agents_list[$agent_name] = ["name" => $agent_name];
        }
    }

    public function makeAgent()
    {
        $this->currentAgent();
        $agent = "help";
        if (isset($this->current_agent)) {
            $agent = $this->current_agent;

            $this->thing_report["agent"] = $agent;
        }
    }

    public function makeJson()
    {
        if (!isset($this->thing_report["json"])) {
            $this->thing_report["json"] = null;
        }
    }

    /**
     *
     */
    function makeChannel($name = null)
    {
        $text = strtolower($this->agent_name);
        $file = $this->resource_path . "/" . $text . "/" . $text . ".txt";

        if (!file_exists($file)) {
            return true;
        }
        $contents = file_get_contents($file);
        $handle = fopen($file, "r");

        $channels = [
            "sms",
            "email",
            "txt",
            "snippet",
            "han",
            "word",
            "slug",
            "choices",
            "message",
            "response",
            "help",
            "info",
            "request",
        ];
        $channel = "null";
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $text = trim(str_replace(["#", "[", "]"], "", $line));
                if (in_array($text, $channels)) {
                    $channel = $text;
                    continue;
                }

                if (!isset($this->thing_report[$channel])) {
                    $this->thing_report[$channel] = "";
                }
                $this->thing_report[$channel] .= $line;
            }

            fclose($handle);
        } else {
            // error opening the file.
        }
    }

    public function memcachedAgent()
    {
        $t = new Memcached($this->thing, "memcached");
        $this->mem_cached = $t->mem_cached;
    }

    /**
     *
     */
    public function getName()
    {
        $this->agent_name = explode("\\", strtolower(get_class($this)))[2];
        $this->agent_class_name = explode("\\", get_class($this))[2];
    }

    function debug()
    {
        $this->thing->log("agent_name is  " . $this->agent_name . ".");

        $this->callingAgent();
        $this->thing->log("Calling agent is  " . $this->calling_agent . ".");

        $agent_input_text = $this->agent_input;
        if (is_array($this->agent_input)) {
            $agent_input_text = "array";
            if (isset($this->agent_input["thing"])) {
                $agent_input_text = "thing";
            }
        }

        $this->thing->log("agent_input is  " . $agent_input_text . ".");
        $this->thing->log("subject is  " . $this->subject . ".");
    }

    /**
     *
     * @param unknown $input
     * @param unknown $agent (optional)
     * @return unknown
     */
    function assert($input, $agent = null, $flag_lowercase = true)
    {
        if ($agent == null) {
            $agent = $this->agent_name;
        }

        if (is_array($input)) {
            return "";
        }

        $string = $input;
        $str_pattern = $agent;
        $str_replacement = "";
        $filtered_input = $input;
        if (strpos($string, $str_pattern) !== false) {
            $occurrence = strpos($string, $str_pattern);
            $filtered_input = substr_replace(
                $string,
                $str_replacement,
                strpos($string, $str_pattern),
                strlen($str_pattern)
            );
        }
        $filtered_input = trim($filtered_input);

        if ($flag_lowercase === true) {
            $filtered_input = strtolower($filtered_input);
        }

        return $filtered_input;
    }

    /**
     *
     * @param unknown $thing (optional)
     */
    public function metaAgent($thing = null)
    {
        // TODO move meta code to Meta agent.
        //if (!isset($this->meta_handler)) {$this->meta_handler = new Meta($this->thing, "meta");}

        if ($thing == null) {
            $thing = $this->thing;
        }

        // Non-nominal
        $this->uuid = $thing->uuid;

        if (isset($thing->to)) {
            $this->to = $thing->to;
        }

        // Potentially nominal
        if (isset($thing->subject)) {
            $this->subject = $thing->subject;
        }

        // Treat as nomina
        if (isset($thing->from)) {
            $this->from = $thing->from;
        }
        // Treat as nomina
        if (isset($thing->created_at)) {
            $this->created_at = $thing->created_at;
        }

        if (isset($this->thing->thing->created_at)) {
            $this->created_at = strtotime($this->thing->thing->created_at);
        }
        if (!isset($this->to)) {
            $this->to = "null";
        }
        if (!isset($this->from)) {
            $this->from = "null";
        }
        if (!isset($this->subject)) {
            $this->subject = "null";
        }
        //if (!isset($this->created_at)) {$this->created_at = date('Y-m-d H:i:s');}
        if (!isset($this->created_at)) {
            $this->created_at = time();
        }
    }

    public function currentAgent()
    {
        //        $previous_thing = new Thing($block_thing['uuid']);
        //        $this->prior_thing = $previous_thing;
        if (!isset($this->thing->variables->array_data["message"]["agent"])) {
            $this->current_agent = "help";
        } else {
            $this->current_agent =
                $this->thing->variables->array_data["message"]["agent"];
        }
        /*
        $this->link =
            $this->web_prefix .
            'thing/' .
            $this->uuid .
            '/' .
            strtolower($this->current_agent);
*/
    }

    /**
     *
     * @return unknown
     */
    public function getLink($variable = null)
    {
        $block_things = [];
        // See if a block record exists.
        $findagent_thing = new Findagent($this->thing, "thing");

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        $this->max_index = 0;

        $match = 0;

        if ($findagent_thing->thing_report["things"] == true) {
            $this->link_uuid = null;
            return false;
        }

        foreach ($findagent_thing->thing_report["things"] as $block_thing) {
            if ($block_thing["nom_to"] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing["uuid"];
                if ($match == 2) {
                    break;
                }
            }
        }

        $previous_thing = new Thing($block_thing["uuid"]);
        $this->prior_thing = $previous_thing;
        if (!isset($previous_thing->variables->array_data["message"]["agent"])) {
            $this->prior_agent = "help";
        } else {
            $this->prior_agent =
                $previous_thing->variables->array_data["message"]["agent"];
        }

        $this->link =
            $this->web_prefix .
            "thing/" .
            $this->uuid .
            "/" .
            strtolower($this->prior_agent);

        return $this->link_uuid;
    }

    /**
     *
     * @return unknown
     */
    function getTask()
    {
        $block_things = [];
        // See if a stack record exists.
        $findagent_thing = new Findagent($this->thing, "thing");

        $this->max_index = 0;
        $match = 0;
        $link_uuids = [];

        $things = $findagent_thing->thing_report["things"];
        if ($things === true) {
            return;
        }

        foreach ($things as $block_thing) {
            $this->thing->log(
                $block_thing["task"] .
                    " " .
                    $block_thing["nom_to"] .
                    " " .
                    $block_thing["nom_from"]
            );
            if ($block_thing["nom_to"] != "usermanager") {
                $match += 1;
                $this->link_task = $block_thing["task"];
                $link_tasks[] = $block_thing["task"];
                // if ($match == 2) {break;}
                // Get upto 10 matches
                if ($match == 10) {
                    break;
                }
            }
        }
        $this->prior_agent = "web";
        foreach ($link_tasks as $key => $link_task) {
            if (isset($link_task)) {
                if (
                    in_array(strtolower($link_task), [
                        "web",
                        "pdf",
                        "txt",
                        "log",
                        "php",
                        "syllables",
                        "brilltagger",
                    ])
                ) {
                    continue;
                }

                $this->link_task = $link_task;
                break;
            }
        }

        $this->web_exists = true;
        if (!isset($agent_thing->thing_report["web"])) {
            $this->web_exists = false;
        }

        return $this->link_task;
    }

    /**
     *
     * @param unknown $variable_name (optional)
     * @param unknown $variable      (optional)
     * @return unknown
     */
    function getVariable($variable_name = null, $variable = null)
    {
        // This function does a minor kind of magic
        // to resolve between $variable, $this->variable,
        // and $this->default_variable.

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

    /**
     *
     */
    public function respond()
    {
        //if ($this->agent_name !== 'agent') {return;}
        // Call the response agent
        $this->respondResponse();
    }

    public function makeInput()
    {
    }

    /**
     *
     */
    private function makeResponse()
    {
        if (isset($this->response)) {
            return;
        }

        //$this->response = "Standby.";
        $this->response = "";
        $this->thing_report["response"] = $this->response;
    }

    /**
     *
     */
    public function makeWeb()
    {
    }

    public function makeLink()
    {
        if (isset($this->link)) {
            $link = $this->link;
        }

        if (isset($this->agent->link)) {
            $link = $this->agent->link;
        }

        if (isset($this->current_agent)) {
            $link =
                $this->web_prefix .
                "thing/" .
                $this->uuid .
                "/" .
                strtolower($this->current_agent);
        }

        if (!isset($link) and isset($this->keyword)) {
            $link =
                $this->web_prefix .
                "thing/" .
                $this->uuid .
                "/" .
                $this->keyword;
        }

        if (!isset($link)) {
            $link = $this->web_prefix;
        }

        $this->link = $link;
        $this->thing_report["link"] = $link;
    }

    /**
     *
     */
    public function makeHelp()
    {
        //$this->{strtolower($this->agent_name) . "Help"};
        if (isset($this->thing_report["help"])) {
            //echo $this->thing_report['help'];
            //    $this->thing_report['help'] = $this->restoreUrl($this->thing_report['help']);
        }
    }

    /**
     *
     */
    public function makeInfo()
    {
        if (!isset($this->thing_report["info"])) {
            if (isset($this->info)) {
                $this->thing_report["info"] = $this->info;
                return;
            }

            $info = $this->info();
            $this->thing_report["info"] = $info;
            $this->info = $info;
        }
    }

    public function info()
    {
        $info = "Text WIKIPEDIA " . strtoupper($this->agent_name) . ".";
        return $info;
    }

    /**
     *
     */
    public function makePDF()
    {
    }

    public function makeKeyword()
    {
        $keyword = "help";

        if (isset($this->thing_report["sms"])) {
            $tokens = explode("|", $this->thing_report["sms"]);
            if (isset($tokens[0])) {
                $keyword = strtolower($tokens[0]);
            }
        }

        if (isset($this->keywords[0])) {
            $keyword = $this->keywords[0];
        }

        if (isset($this->keyword)) {
            $keyword = $this->keyword;
        }

        if (isset($this->agent->keywords[0])) {
            $keyword = $this->agent->keywords[0];
        }

        if (isset($this->agent->keyword)) {
            $keyword = $this->agent->keyword;
        }

        $this->keyword = $keyword;
        $this->thing_report["keyword"] = $keyword;
    }

    /**
     *
     */
    public function makeChart()
    {
    }

    /**
     *
     */
    public function makeImage()
    {
    }

    public function makeChoices()
    {
        if (isset($this->thing_report["choices"])) {
            return;
        }
        if (isset($this->choices)) {
            $this->thing_report["choices"] = $this->choices;
            return;
        }

        $choices = false;
        $this->thing_report["choices"] = $choices;
    }

    /**
     *
     */

    public function makeSnippet()
    {
        if (isset($this->thing_report["snippet"])) {
            $this->thing_report["snippet"] = str_replace(
                "[word]",
                $this->word,
                $this->thing_report["snippet"]
            );
        }

        if (!isset($this->thing_report["snippet"])) {
            $this->thing_report["snippet"] = "";
        }
    }

    /**
     *
     */
    public function makeTXT()
    {
    }

    /**
     *
     */
    public function makeMessage()
    {
        if (!isset($this->message)) {
            if (isset($this->thing_report['sms'])) {
                $this->message = $this->thing_report['sms'];
            }
        }
    }
    /**
     *
     */
    public function makeEmail()
    {
    }

    /**
     *
     */
    public function makeSMS()
    {
        //$this->makeResponse();
        // So this is the response if nothing else has responded.

        if (!isset($this->thing_report["sms"])) {
            if (isset($this->sms_message)) {
                $this->thing_report["sms"] = $this->sms_message;
            }

            if (!isset($this->thing_report["sms"])) {
                $sms = strtoupper($this->agent_name);

                if ($this->response == "") {
                    $sms .= " >";
                } else {
                    $sms .= " | " . $this->response;
                }

                $this->thing_report["sms"] = $sms;
                $this->thing_report["sms"] = null;
            }

            if (!isset($this->sms_message)) {
                $this->sms_message = $this->thing_report["sms"];
            }
        }
    }

    /**
     *
     */
    public function getPrior()
    {
        // See if the previous subject line is relevant
        $this->thing->db->setUser($this->from);
        $prior_thing = $this->thing->db->priorGet();
        $this->prior_thing = $prior_thing;

        $this->prior_task = $prior_thing["thing"]->task;
        $this->prior_agent = $prior_thing["thing"]->nom_to;

        $uuid = $prior_thing["thing"]->uuid;
      //  $variables_json = $prior_thing["thing"]->variables;
      //  $variables = $this->thing->json->jsontoArray($variables_json);

        $variables = $prior_thing["thing"]->variables;


        $this->prior_variables = $variables;
    }

    /**
     *
     * @param unknown $input
     * @param unknown $n     (optional)
     * @return unknown
     */
    public function getNgrams($input, $n = 3, $delimiter = null)
    {
        if ($delimiter == null) {
            $delimiter = "";
        }
        $words = explode(" ", $input);
        $ngrams = [];

        foreach ($words as $key => $value) {
            if ($key < count($words) - ($n - 1)) {
                $ngram = "";
                for ($i = 0; $i < $n; $i++) {
                    $ngram .= $words[$key + $i] . $delimiter;
                }
                $ngrams[] = trim($ngram);
            }
        }
        return $ngrams;
    }

    /**
     *
     * @param unknown $time_limit (optional)
     * @param unknown $input      (optional)
     * @return unknown
     */
    function timeout($time_limit = null, $input = null)
    {
        if ($time_limit == null) {
            $time_limit = 10000;
        }

        if ($input == null) {
            $input = "No matching agent found. ";
        }

        // Timecheck

        switch (strtolower($this->context)) {
            case "place":
                $array = ["place", "mornington crescent"];
                break;
            case "group":
                $array = ["group", "say hello", "listen", "join"];
                break;
            case "train":
                $array = ["train", "run train", "red", "green", "flag"];
                break;
            case "headcode":
                $array = ["headcode"];
                break;
            case "identity":
                $array = ["headcode", "mordok", "jarvis", "watson"];
                break;
            default:
                $array = [
                    "link",
                    "roll d20",
                    "roll",
                    "iching",
                    "bible",
                    "wave",
                    "eightball",
                    "read",
                    "group",
                    "flag",
                    "tally",
                    "emoji",
                    "red",
                    "green",
                    "balance",
                    "age",
                    "mordok",
                    "pain",
                    "receipt",
                    "key",
                    "uuid",
                    "remember",
                    "reminder",
                    "watson",
                    "jarvis",
                    "whatis",
                    "privacy",
                    "?",
                ];
        }

        $k = array_rand($array);
        $v = $array[$k];

        $response = $input . "Try " . strtoupper($v) . ".";

        if ($this->thing->elapsed_runtime() > $time_limit) {
            $this->thing->log(
                'Agent "Agent" timeout triggered. Timestamp ' .
                    number_format($this->thing->elapsed_runtime())
            );

            $timeout_thing = new Timeout($this->thing, $response);
            $this->thing_report = $timeout_thing->thing_report;

            return $this->thing_report;
        }

        return false;
    }

    public function timestampAgent($text = null)
    {
        if ($text == null) {
            $text = $this->created_at;
        }
        $time = strtotime($text);

        $text = strtoupper(date("Y M d D H:i", $time));
        $this->timestamp = $text;
        return $this->timestamp;
    }

    /**
     *
     * @param unknown $text (optional)
     */
    public function read($text = null)
    {
        $this->thing->log("read start.");

        if ($text == null) {
            $text = $this->subject;
        } // Always.
        if (isset($this->filtered_input)) {
            $text = $this->filtered_input;
        }
        if (isset($this->translated_input)) {
            $text = $this->translated_input;
        }

        switch (true) {
            case isset($this->input):
                break;

            case is_array($this->agent_input):
                $this->input = $this->agent_input;
                break;

            case $this->agent_input == null:

            case strtolower($this->agent_input) == "extract":
            case strtolower($this->agent_input) ==
                strtolower($this->agent_name):
                //                $this->input = strtolower($text);
                $this->input = $text;

                break;
            default:
                //                $this->input = strtolower($this->agent_input);
                $this->input = $this->agent_input;
        }

        $this->thing->log('read "' . $this->subject . '".');

        // dev here?

        $indicators = [
            "link" => ["web", "link"],
        ];

        $this->flagAgent($indicators, $this->subject);
        $this->readFrom();
        $this->readSubject();
        // read the current agent.
        if (
            $this->agent_class_name !== "Agent" and
            method_exists($this, "read" . $this->agent_class_name)
        ) {
            $this->{"read" . $this->agent_class_name}($text);
        }

        $this->thing->log("read input " . $this->input . ".");
        $this->thing->log("read completed.");
    }

    public function readFrom($text = null)
    {
        $this->thing->log("read from start.");
        $from = $this->from;
        if ($text != null) {
            $from = $text;
        }

        if (!isset($this->thing->deny_agent)) {
            $this->thing->deny_agent = new Deny($this->thing, "deny");
        }

        if ($this->thing->deny_agent->isDeny() === true) {
            $this->do_not_respond = true;
            throw new \Exception("Address not allowed.");
        }

        // Get uuid from incoming datagram.
        // Devstack

        // $uuid = some function of from
        $uuid = false;

        if (isset($uuid) and is_string($uuid)) {
            $thing = new Thing($uuid);
            $this->thing->log("read from made a new thing.");
            if ($thing->thing != false) {
                //$this->thing = $thing->thing;

                $agent = new Agent($thing->thing);
                $this->thing->log("read from ran agent on new thing.");
                //return;
            }
        }
        $this->thing->log("read from complete.");
    }

    /**
     *
     * @param unknown $agent_class_name (optional)
     * @param unknown $agent_input      (optional)
     * @return unknown
     */
    public function getAgent(
        $agent_class_name = null,
        $agent_input = null,
        $thing = null
    ) {
        //$agent = null;
        if ($thing == null) {
            $thing = $this->thing;
        }

        // Do not call self.
        // devstack depthcount
        if (strtolower($this->agent_name) == strtolower($agent_class_name)) {
            return true;
        }

        //if ($agent_class_name == null) {return true;}

        //$shouldExit = true;
        register_shutdown_function([$this, "shutdownHandler"]);
        /*
register_shutdown_function(function() use (&$shouldExit) {
    if (! $shouldExit) {
echo "!shouldexit";
        return;
    }
$this->shutdownHandler();
});
*/

        //if ($agent_class_name == 'Test') {return false;}
        set_error_handler([$this, "warning_handler"], E_WARNING | E_NOTICE);

        //set_error_handler("warning_handler", E_WARNING);

        try {
            $agent_namespace_name =
                "\\Nrwtaylor\\StackAgentThing\\" . $agent_class_name;

            $this->thing->log(
                'trying Agent "' . $agent_class_name . '".',
                "INFORMATION"
            );

            if ($agent_class_name == null) {
                throw \Exception($agent_class_name . " is a null agent.");
            }

            if (
                is_subclass_of(
                    $agent_namespace_name,
                    "\\Nrwtaylor\\StackAgentThing\\Agent"
                ) === false
            ) {
                $this->thing->log($agent_namespace_name . " is not an agent.");
                throw \Exception($agent_namespace_name . " is not an agent.");
            }

            // In test 25 May 2020

            if (!isset($thing->subject)) {
                $thing->subject = $this->input;
            }

            $thing->subject = $this->stripAgent($thing->subject);

            $agent = new $agent_namespace_name($thing, $agent_input);
            //$shouldExit = false;

            /*
$pid = pcntl_fork();
if ($pid == -1) {
 die('could not fork');
} else if ($pid) {
 // we are the parent
 pcntl_waitpid($pid, $status, WUNTRACED); //Protect against Zombie children
 if (pcntl_wifexited($status)) {
   echo "Child exited normally";

 } else if (pcntl_wifstopped($status)) {
   echo "Signal: ", pcntl_wstopsig($status), " caused this child to stop.";
 } else if (pcntl_wifsignaled($status)) {
   echo "Signal: ",pcntl_wtermsig($status)," caused this child to exit with return code: ", pcntl_wexitstatus($status);
 }
} else {
            $agent = new $agent_namespace_name($thing, $agent_input);
            $this->thing_report = $agent->thing_report;
            $this->agent = $agent;

// pcntl_exec("/path/to/php/script");
 echo "Could not Execute...";
}
*/

            restore_error_handler();

            // If the agent returns true it states it's response is not to be used.
            if (isset($agent->response) and $agent->response === true) {
                throw new Exception("Flagged true.");
            }

            //if ($agent->thing_report == false) {return false;}

            //if (isset($agent)) {
            $this->thing_report = $agent->thing_report;
            $this->agent = $agent;
            //} else {
            //$this->thing_report = false;
            //$this->agent = false;
            //$agent = false;
            //}
        } catch (\Throwable $t) {
            restore_error_handler();
            $this->thing->log("caught throwable.", "WARNING");

            $message = $t->getMessage();

            // $code = $ex->getCode();
            $file = $t->getFile();
            $line = $t->getLine();

            $input = $message . "  " . $file . " line:" . $line;
            $this->thing->log($input, "WARNING");

            return false;
        } catch (\Error $ex) {
            restore_error_handler();
            // Error is the base class for all internal PHP error exceptions.
            $this->thing->log(
                'caught error. Could not load "' . $agent_class_name . '".',
                "WARNING"
            );
            $message = $ex->getMessage();

            // $code = $ex->getCode();
            $file = $ex->getFile();
            $line = $ex->getLine();

            $input = $message . "  " . $file . " line:" . $line;
            $this->thing->log($input, "WARNING");

            // This is an error in the Place, so Bork and move onto the next context.
            // $bork_agent = new Bork($this->thing, $input);
            //continue;
            return false;
        }
        return $agent;
    }

    public function validateAgents($arr = null)
    {
        $agents = [];
        set_error_handler([$this, "warning_handler"], E_WARNING);
        //set_error_handler("warning_handler", E_WARNING);
        $this->thing->log(
            "looking for keyword matches with available agents.",
            "INFORMATION"
        );
        $agents_tested = [];

        // Build list to be tested.
        $agent_names = [];
        foreach ($arr as $keyword) {
            if (strtolower($keyword) == "agent") {
                continue;
            }

            $agent_names = array_merge(
                $agent_names,
                $this->variantsAgent($keyword, "")
            );
        }

        //        foreach (["", "s", "es"] as $postfix_variant) {
        foreach ($agent_names as $keyword) {
            // Don't allow agent to be recognized
            if (strtolower($keyword) == "agent") {
                continue;
            }

            $agent_class_name = ucfirst(strtolower($keyword));
            /*
                $agent_class_name = substr_replace(
                    $agent_class_name,
                    "",
                    -1,
                    strlen($postfix_variant)
                );
*/
            if (isset($agents_tested[$agent_class_name])) {
                continue;
            }

            $agent_class_name = str_replace("-", "", $agent_class_name);

            // Can probably do this quickly by loading path list into a variable
            // and looping, or a direct namespace check.
            $filename = $this->agents_path . $agent_class_name . ".php";
            if (file_exists($filename)) {
                $agent_package = [$agent_class_name => null];
                //                    $agents[] = $agent_class_name;
                $agents[$agent_class_name] = $agent_package;
            }

            // 2nd way
            $agent_class_name = strtolower($keyword);

            // Can probably do this quickly by loading path list into a variable
            // and looping, or a direct namespace check.
            $filename = $this->agents_path . $agent_class_name . ".php";
            if (file_exists($filename)) {
                $agent_package = [$agent_class_name => null];
                //                    $agents[] = $agent_class_name;
                $agents[$agent_class_name] = $agent_package;
            }

            $agents_tested[$agent_class_name] = true;

            // 3rd way
            $agent_class_name = strtoupper($keyword);

            // Can probably do this quickly by loading path list into a variable
            // and looping, or a direct namespace check.
            $filename = $this->agents_path . $agent_class_name . ".php";
            if (file_exists($filename)) {
                $agent_package = [$agent_class_name => null];
                //                    $agents[] = $agent_class_name;
                $agents[$agent_class_name] = $agent_package;
            }
        }
        //  }
        restore_error_handler();
        $this->agents = $agents;
    }

    public function responsiveAgents($agents = null)
    {
        if (isset($this->responsive_agents)) {
            return;
        }
        $responsive_agents = [];
        foreach ($agents as $i => $agent_package) {
            // Allow for doing something smarter here with
            // word position and Bayes.  Agent scoring
            // But for now call the first agent found and
            // see where that consistency takes this.

            $agent_class_name = key($agent_package);
            $agent_input = null;
            if (isset($agent_package[$agent_class_name]["agent_input"])) {
                $agent_input = $agent_package[$agent_class_name]["agent_input"];
            }

            // Ignore Things for now 19 May 2018 NRWTaylor
            if ($agent_class_name == "Thing") {
                continue;
            }

            // And Email ... because email\uuid\roll otherwise goes to email
            if (count($agents) > 1 and $agent_class_name == "Email") {
                continue;
            }
            $temp_agent_handler = $this->getAgent(
                $agent_class_name,
                $agent_input
            );
            if ($temp_agent_handler) {
                $score = 1;

                $matched_characters = null;
                if (isset($temp_agent_handler->score)) {
                    $matched_characters = $temp_agent_handler->score;
                }
                $score = $this->scoreAgent(
                    $agent_class_name,
                    $matched_characters
                );

                $responsive_agents[] = [
                    "agent_name" => $agent_class_name,
                    "thing_report" => $this->thing_report,
                    "score" => $score,
                ];
            }
        }

        // Are any specific agents flagged for agent response.
        // eg Question

        if ($this->flag_question) {
            $responsive_agents[] = [
                "agent_name" => "Question",
                "thing_report" => $this->thing_report,
                "score" => 1,
            ];
        }

        // Use length of matched agent name as proxy for match closeness.
        // If more than one word.
        usort($responsive_agents, function ($a, $b) {
            return -1 * ($a["score"] - $b["score"]);
        });

        // For now just take the first match.
        // This allows for sophication in resolving multi agent responses.
        $this->responsive_agents = $responsive_agents;

        foreach ($this->responsive_agents as $i => $j) {
            $this->thing->log(
                $j["agent_name"] . " " . $j["score"] . "\n",
                "INFORMATION"
            );
            $this->thing->console($j["agent_name"] . " " . $j["score"] . "\n");
        }
var_dump($this->responsive_agents);
    }

    // Take a piece of returned text,
    // And score it to see how close it is to the provided input.

    public function scoreAgent($text, $provided_score = 0)
    {
        // dev this function needs improvement to handle closeness of multi-gram strings

        //        if ($provided_score === null) {
        $matched_characters_count =
            strlen($text) -
            strlen(
                str_replace(strtolower($this->input), "", strtolower($text))
            );
        $unmatched_characters_count = strlen($text) - $matched_characters_count;
        //        }

        $pieces = explode(" ", $text);
        $num_pieces = count($pieces);

        $s = 0;
        if ($matched_characters_count != 0) {
            $s =
                ($matched_characters_count - $unmatched_characters_count) /
                $matched_characters_count;
        }

        $score = $s * pow(10, $num_pieces) + $provided_score;

        return $score;
    }

    /**
     *
     * @param unknown $agent_class_name (optional)
     * @return unknown
     */
    public function isAgent($agent_class_name = null)
    {
        if ($agent_class_name == null) {
            $agent_class_name = strtolower($this->agent_name);
        }

        if (substr($agent_class_name, 0, 5) === "Thing") {
            return false;
        }

        $uuid = new Uuid($this->thing, "extract");
        $uuid->extractUuids($agent_class_name);
        if ($agent_class_name == $uuid->uuid) {
            return false;
        }

        if (
            $this->getAgent($agent_class_name, $agent_class_name, null) ===
            false
        ) {
            return false;
        } else {
            return true;
        }
    }

    public function ngramsText($text = null, $gram_limit = 4, $delimiter = null)
    {
        if ($delimiter == null) {
            $delimiter = "";
        }
        // See if there is an agent with the first workd
        $arr = explode(" ", trim($text));
        $agents = [];

        foreach (range(2, $gram_limit, 1) as $number) {
            $bigrams = $this->getNgrams($text, $number, $delimiter);
            //        $trigrams = $this->getNgrams($text, 3, $delimiter);
            //        $quadgrams = $this->getNgrams($text, 4, $delimiter);

            $arr = array_merge($arr, $bigrams);
            //        $arr = array_merge($arr, $trigrams);
            //        $arr = array_merge($arr, $quadgrams);
        }

        return $arr;
    }

    public function extractAgents($input)
    {
        $agent_input_text = $this->agent_input;
        if (is_array($this->agent_input)) {
            $agent_input_text = "";
        }

        $conditioned_input = $this->getSlug($input);
        $conditioned_input = str_replace("-", " ", $conditioned_input);

        $arr = $this->ngramsText($conditioned_input);
        // Added this March 6, 2018.  Testing.

        if ($this->agent_input == null) {
            $arr[] = $this->to;
        } else {
            $arr = $this->ngramsText($agent_input_text);
        }
        // Does this agent have code.
        $this->validateAgents($arr);

        $uuid_agent = new Uuid($this->thing, "uuid");
        //$t = $uuid_agent->stripUuids($input);
        // TODO: Build a seperate function.
        // Is there a translation for this command.
        $librex_agent = new Librex($this->thing, "agent/agent");

        $text = trim(str_replace("agent", "", $conditioned_input));
        $text = trim(str_replace("thing", "", $text));

        $slug_agent = new Slug($this->thing, "slug");
        $text = $slug_agent->getSlug($text);

        $uuids = $uuid_agent->extractUuids($text);
        foreach ($uuids as $i => $uuid) {
            $text = trim(str_replace($uuid, "", $text));
        }
        $text = trim($text, "-");

        $this->hits = $librex_agent->getHits($text);

        if ($this->hits != null) {
            foreach ($this->hits as $i => $hit) {
                $agent_hit = trim(explode(",", $hit)[0]);
                $agent_input_hit = trim(explode(",", $hit)[1]);

                // TODO: Consider capitalization format of agent/agent
                // For now use ucwords
                $agent_input_hit = ucwords($agent_input_hit);

                foreach ($arr as $j => $agent_candidate) {
                    if (
                        strtolower(str_replace("-", "", $agent_hit)) ==
                        strtolower($agent_candidate)
                    ) {
                        $agent_package = [
                            $agent_input_hit => ["agent_input" => $agent_hit],
                        ];
                        array_unshift($this->agents, $agent_package);
                    }
                }
            }
        }

        // Does this agent provide a text response.
        $this->responsiveAgents($this->agents);

        foreach ($this->responsive_agents as $i => $responsive_agent) {
        }

        return $this->responsive_agents;
    }

    public function stripAgent($text = null)
    {
        $filtered_text = $text;
        $pos = stripos($text, "agent");
        if ($pos === 0) {
            $filtered_text = trim(
                substr_replace($text, "", 0, strlen("agent"))
            );
        }

        // Strip Discord ids.
        $filtered_text = preg_replace("/\<\@\!.*?\>/", "", $filtered_text);
        $filtered_text = preg_replace("/\<\@.*?\>/", "", $filtered_text);

        $filtered_text = ltrim($filtered_text);
        return $filtered_text;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        // Only run this for agent
        if ($this->agent_name !== "agent") {
            return;
        }

        $this->thing->log('read subject "' . $this->subject . '".');

        $status = false;
        $this->response = false;
        // Because we need to be able to respond to calls
        // to specific Identities.

        $agent_input_text = $this->agent_input;
        if (is_array($this->agent_input)) {
            $agent_input_text = "";
        }
        $input = $agent_input_text . " " . $this->to . " " . $this->subject;

        // If there is no agent_input provided.
        // Then set the input to the to and subject
        // Otherwise set the input to the provided agent_input

        // TODO recognize piped text from command line

        // subject and agent_input

        if ($this->agent_input == null) {
            $input = $this->to . " " . $this->subject;
        } else {
            $input = $agent_input_text;
        }

        // Recognize and ignore stack commands.
        // Devstack
        if (substr($this->subject, 0, 2) == "s/") {
            if (
                substr($this->subject, 0, 5) == "s/ is" and
                substr($this->subject, -6) == "button"
            ) {
                $t = str_replace("s/ is", "", $this->subject);
                $t = str_replace("button", "", $t);
                $t = trim($t);
                $button_agent = $t;
            }

            $agent_tokens = explode(" ", $this->agent_input);
            // Expect at least  tokens.
            // Get the last alpha tokens.

            $selected_agent_tokens = [];
            foreach (array_reverse($agent_tokens) as $i => $agent_token) {
                //if (is_string($agent_token)) {

$g = str_replace(" ", "", $agent_token);
$g = str_replace("-", "", $g);

     if (ctype_alpha($g) === false) {
       //         if (ctype_alpha(str_replace(" ", "", $agent_token)) === false) {
                    break;
                }
                $selected_agent_tokens[] = $agent_token;
            }

            $token_agent = implode(" ", array_reverse($selected_agent_tokens));
            $agglutinated_token_agent = implode(
                "",
                array_reverse($selected_agent_tokens)
            );
            $hyphenated_token_agent = implode(
                "-",
                array_reverse($selected_agent_tokens)
            );

            if (isset($button_agent) and isset($token_agent)) {
                $flag = false;

                if ($button_agent == $token_agent) {
                    $this->response .=
                        "Clicked the " . strtoupper($button_agent) . " button.";
                }

                if ($button_agent == $agglutinated_token_agent) {
                    $flag = true;
                }

                if ($button_agent == $hyphenated_token_agent) {
                    $flag = true;
                }

                if ($button_agent == $token_agent) {
                    $flag = true;
                }

if ("is ".$button_agent ." button" == $token_agent) {
$flag = true;
}


                if ($flag === false) {
                    return false;
                }
            }
        }

        // Recognize incoming things.
        // Develop channel forwarding.
        if (substr($this->subject, 0, 7) == "THING |") {
            $to_repeat = $this->addressKaiju($this->from);
            if ($to_repeat !== null) {
                $this->sendDiscord($this->subject, $to_repeat);
            }
        }

        // recognize when command
        // with piped input

        if (strtolower($this->subject) == "when") {
            if (
                $this->input != null or
                $this->input != "" or
                strtolower($this->input) != "when"
            ) {
                $this->when_agent = new When($this->thing, $this->input);
                $this->thing_report = $this->when_agent->thing_report;
                return;
            }
        }

        // Dev test for robots
        $this->thing->log("created a Robot agent.", "INFORMATION");
        $this->robot_agent = new Robot($this->thing, "robot");

        if ($this->robot_agent->isRobot()) {
            $this->response .= "We think you are a robot.";
            $this->thing_report = $this->robot_agent->thing_report;
            return;
        }

        // ignore agent at the start
        $input = $this->stripAgent($input);

        $dispatcher_agent = new Dispatcher($this->thing, "dispatcher");

        // Is it a timestamp?
        $time_tokens = explode(" ", $input);

        $timestamp_agent = new Timestamp($this->thing, "timestamp");

        if ($time_tokens[0] == "agent") {
            array_shift($time_tokens);
        }
        foreach ($time_tokens as $time_token) {
            if ($timestamp_agent->isTimestamp($time_token) === true) {
                if (count($time_tokens) == 1) {
                    $this->thing_report = $timestamp_agent->thing_report;
                    return;
                }
            }
        }

        // See if the string has a pointer to a channel nuuid.

        $nuuid = new Nuuid($this->thing, "nuuid");
        $n = $nuuid->extractNuuid($input);

        // See if this matches a stripe token
        if (!($n == false || $n == true)) {
            // if ($n != false) {
            $temp_email = $this->thing->db->from;
            $this->thing->db->from = "stripe" . $this->mail_postfix;

            $t = $this->thing->db->nuuidSearch($n);
            $t = $t["things"];

            if (count($t) >= 1) {
                // At least one valid four character token found.
                // This is close enought to authorize stack service.

                // Loop through the returned tokens and see which are stripe success tokens.
                foreach ($t as $t_uuid => $t_thing) {
                    if ($t_thing["task"] == "stripe-success") {
                        $success_agent = new Success(
                            $this->thing,
                            "channel token recognized"
                        );
                        $this->thing_report = $success_agent->thing_report;
                        return;
                    }
                }

                foreach ($t as $t_uuid => $t_thing) {
                    if (strpos($t_thing["task"], "ship") !== false) {
                        $thing = new Thing($t_thing["uuid"]);
                        $ship_agent = new Ship($thing, "ship token recognized");
                        $this->thing_report = $ship_agent->thing_report;
                        return;
                    }
                }

                // Reset the database email address
                $this->thing->db->from = $temp_email;

                // Single match to the nuuid.
                // dev Things can be forgotten so no guarantee this is
                // the matching uuid.
                // But it is the best there is on this stack.
                if (
                    isset($nuuid->nuuid_uuid) and is_string($nuuid->nuuid_uuid)
                ) {
                    $nuuid_filtered_input = trim(
                        str_replace($nuuid->nuuid_uuid, "", $input)
                    );

                    $thing = new Thing($nuuid->nuuid_uuid);
                    $agent = new Agent($thing, $nuuid_filtered_input);
                    $this->thing_report = $agent->thing_report;
                    return;
                }

                // Multiple match to the nuuid.
                if (isset($nuuid->nuuid_uuid) and $nuuid->nuuid_uuid == null) {
                    $this->response .=
                        "Development code for multiple matching nuuids. ";
                    $nuuid_filtered_input = trim(
                        str_replace($nuuid->nuuid_uuid, "", $input)
                    );
                    $thing = new Thing($nuuid->nuuid_uuid);
                    $lowest_score = 1e99;
                    foreach ($nuuid->things as $i => $nuuid_thing) {
                        $score = levenshtein(
                            $nuuid_thing["task"],
                            $nuuid_filtered_input
                        );
                        if ($score < $lowest_score) {
                            $lowest_score = $score;
                            $best_thing = $nuuid_thing;
                        }
                        //            $agent = new Agent($thing, $nuuid_filtered_input);
                        //            $this->thing_report = $agent->thing_report;
                        //            return;
                    }
                    $agent = new Agent($best_thing, $nuuid_filtered_input);
                    $this->thing_report = $agent->thing_report;
                    return;
                }
            }
        }

        $uuid = new Uuid($this->thing, "uuid");
        $uuid = $uuid->extractUuid($input);

        if (isset($uuid) and is_string($uuid)) {
            $thing = new Thing($uuid);

            if ($thing->thing != false and isset($thing->created_at)) {
                $f = trim(str_replace($uuid, "", $input));

                // TODO: Test
                // TODO: Explore shorter token recognition.
                if ($thing->subject == "stripe-success") {
                    $success_agent = new Success(
                        $thing,
                        "channel token recognized"
                    );
                    $this->thing_report = $success_agent->thing_report;
                    return;
                }

                if ($f == "" or $f == "agent") {
                    $agent = new Uuid($thing, $f);
                    $this->thing_report = $agent->thing_report;
                    return;
                }
                $agent = new Agent($thing, $f);
                $this->thing_report = $agent->thing_report;
                return;
            }
        }

        // Check for recognizable robot strings.
        // like NMEA

        // $TZXDR,X,3445.000000,mV,ThingVcc*01
        if ($this->isNMEA($this->subject)) {
            $nmea_agent = new NMEA($this->thing, "nmea");
            $this->thing_report = $nmea_agent->thing_report;
            return $this->thing_report;
        }

        // Handle call intended for humans.
        $human_agent = new Human($this->thing, "human");

        if (is_string($human_agent->address)) {
            $this->thing_report = $human_agent->thing_report;
            return $this->thing_report;
        }

        // Strip @ callsigns from input
        $atsign_agent = new Atsign($this->thing, "atsign");
        $input = $atsign_agent->stripAtsigns($input);
        // Basically if the agent input directly matches an agent name
        // Then run it.
        // So look hear to generalize that.
        $text = $agent_input_text != null ? urldecode($agent_input_text) : "";
        //$text = urldecode($input);
        $text = strtolower($text);
        //$arr = explode(' ', trim($text));

        $arr = explode("\%20", trim(strtolower($text)));

        $agents = [];
        $onegrams = $this->getNgrams($text, $n = 1);
        $bigrams = $this->getNgrams($text, $n = 2);
        $trigrams = $this->getNgrams($text, $n = 3);
        $quadgrams = $this->getNgrams($text, $n = 4);

        $arr = array_merge($arr, $onegrams);
        $arr = array_merge($arr, $bigrams);
        $arr = array_merge($arr, $trigrams);
        $arr = array_merge($arr, $quadgrams);

        usort($arr, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });
        $matches = [];
        foreach ($arr as $i => $ngram) {
            $ngram = ucfirst($ngram);
            if ($ngram == "Thing") {
                continue;
            }

            // Exclude incoming web links asking for buttons
            if ($ngram == "Button") {
                continue;
            }

            if ($ngram == "Agent") {
                continue;
            }

            if ($ngram == "Sms") {
                continue;
            }

            if ($ngram == "") {
                continue;
            }

            $matches[] = $ngram;
        }
        if (count($matches) == 1) {
            $this->getAgent($matches[0], $this->agent_input);
            return $this->thing_report;
        }

        // First things first.  Special instructions to ignore.
        if (strpos($input, "cronhandler run") !== false) {
            $this->thing->log('Agent "Agent" ignored "cronhandler run".');
            $this->thing->flagGreen();
            //$thing_report['thing'] = $this->thing;
            $this->thing_report["thing"] = $this->thing->thing;
            $this->thing_report["info"] =
                'Mordok ignored a "cronhandler run" request.';
            return $this->thing_report;
        }

        // Second.  Ignore web view flags for now.
        if (strpos($input, "web view") !== false) {
            $this->thing->log('Agent "Agent" ignored "web view".');
            $this->thing->flagGreen();
            $this->thing_report["thing"] = $this->thing->thing;
            $this->thing_report["info"] =
                'Mordok ignored a "web view" request.';
            return $this->thing_report;
        }

        // Third.  Forget.
        if (strpos($input, "forget") !== false) {
            $forget_tokens = [
                "all",
                "now",
                "today",
                "second",
                "seconds",
                "minute",
                "minutes",
                "hour",
                "hours",
                "day",
                "days",
                "week",
                "weeks",
                "month",
                "months",
                "year",
                "years",
                "everything",
            ];
            $tokens = explode(" ", $input);
            foreach ($tokens as $i => $token) {
                if (in_array(strtolower($token), $forget_tokens)) {
                    $forget_agent = new Forgetcollection($this->thing);
                    $this->thing_report["sms"] =
                        $forget_agent->thing_report["sms"];

                    //                $this->thing_report['sms'] =
                    //                    "AGENT | Saw a FORGET instruction.";
                    return $this->thing_report;
                }
            }

            if (strpos($input, "all") !== false) {
                // pass through
            } else {
                $this->thing->log('did not ignore a forget".');
                //$this->thing->flagGreen();
                $this->thing->Forget();
                $this->thing_report = false;
                $this->thing_report["info"] =
                    'Agent did not ignore a "forget" request.';
                $this->thing_report["sms"] =
                    "FORGET | That Thing has been forgotten.";
                return $this->thing_report;
            }
        }

        $check_beetlejuice = "off";
        if ($check_beetlejuice == "on") {
            $this->thing->log(
                "created a Beetlejuice agent looking for incoming message repeats."
            );
            $beetlejuice_thing = new Beetlejuice($this->thing);

            if ($beetlejuice_thing->flag == "red") {
                $this->thing->log('Agent "Agent" has heard this three times.');
            }

            $this->thing_report = $beetlejuice_thing->thing_report;
        }

        $burst_check = true; // Runs in about 3s.  So need something much faster.
        $burst_limit = 8;

        $burst_age_limit = 900; //s
        $similarness_limit = 100;
        $similiarities_limit = 500; //
        $burstiness_limit = 750;
        $bursts_limit = 1;
        if ($burst_check) {
            $this->thing->log(
                'Agent "Agent" created a Burst agent looking for burstiness.',
                "DEBUG"
            );

            if (!isset($this->thing->burst_handler)) {
                $this->thing->burst_handler = new Burst($this->thing, "burst");
            }
            $this->thing->log(
                'Agent "Agent" created a Similar agent looking for incoming message repeats.',
                "DEBUG"
            );

            if (!isset($this->thing->similar_handler)) {
                $this->thing->similar_handler = new Similar(
                    $this->thing,
                    "similar"
                );
            }

            $similarness = $this->thing->similar_handler->similarness;
            $bursts = $this->thing->burst_handler->burst;

            $burstiness = $this->thing->burst_handler->burstiness;
            $similarities = $this->thing->similar_handler->similarity;

            $elapsed = $this->thing->elapsed_runtime();

            $burst_age_limit = 900; //s
            $similiarness_limit = 90;
            //var_dump ($this->current_time);
            $burst_age = 0;
            if ($this->thing->burst_handler->burst_time != null) {
                $burst_age =
                    strtotime($this->current_time) -
                    strtotime($this->thing->burst_handler->burst_time);
            }
            if ($burst_age < 0) {
                $burst_age = 0;
            }

            if (
                $bursts >= $bursts_limit and
                $burstiness < $burstiness_limit and
                $similarities >= $similiarities_limit and
                $similarness < $similarness_limit and
                $burst_age < $burst_age_limit
            ) {
                // Don't respond
                $this->thing->log(
                    'Agent "Agent" heard similarities, similarness, with bursts and burstiness.',
                    "WARNING"
                );

                if ($this->verbosity >= 9) {
                    $t = new Hashmessage(
                        $this->thing,
                        "#channelbursts " .
                            $bursts .
                            "/" .
                            $bursts_limit .
                            " #channelburstiness " .
                            $burstiness .
                            "/" .
                            $burstiness_limit .
                            " #channelsimilarities " .
                            $similarities .
                            "/" .
                            $similiarities_limit .
                            " #channelsimilarness " .
                            $similarness .
                            "/" .
                            $similiarness_limit .
                            " #thingelapsedruntime " .
                            $elapsed .
                            " #burstage " .
                            $burst_age
                    );
                } elseif ($this->verbosity >= 8) {
                    $t = new Hashmessage(
                        $this->thing,
                        "MESSAGE | #stackoverage | wait " .
                            number_format(
                                ($burst_age_limit - $burst_age) / 60
                            ) .
                            " minutes"
                    );
                } elseif ($this->verbosity >= 7) {
                    $t = new Hashmessage(
                        $this->thing,
                        "MESSAGE | The stack is handling a burst of similar requests. | Wait " .
                            number_format(
                                ($burst_age_limit - $burst_age) / 60
                            ) .
                            " minutes and then retry."
                    );
                } else {
                    $t = new Hashmessage(
                        $this->thing,
                        "#testtesttest 15m timeout"
                    );
                }

                $this->thing_report = $t->thing_report;
                return $this->thing_report;
            }

            $this->thing->log(
                'Agent "Agent" noted burstiness ' .
                    $burstiness .
                    " and similarness " .
                    $similarness .
                    "."
            );
        }

        // Based on burstiness and similiary decide if this message is okay.
        //  if ($burstiness

        //        $this->thing->log( 'Agent "Agent" noted burstiness ' . $burstiness . ' and similarness ' . $similarness . '.' );
        /*

                if (($burstiness < 1000) and ($similarness < 100)) {
                    $t = new Hashmessage($this->thing, "#burstiness". $burstiness. "similarness" . $similarness);
                    $thing_report = $t->thing_report ;

                    return $thing_report;
                }
*/
        // Expand out emoji early
        // devstack - replace this with a fast general character
        // character recognizer of concepts.
        $emoji_thing = new Emoji($this->thing, "emoji");
        $this->thing_report = $emoji_thing->thing_report;
        if ($emoji_thing->hasEmoji() === true) {
            //if ((isset($emoji_thing->emojis)) and ($emoji_thing->emojis != [])) {
            // Emoji found.
            $input = $emoji_thing->translated_input;
        }
        // expand out chinese characters
        // Added to stack 29 July 2019 NRW Taylor
        $this->thing->log("expand out chinese characters");
        $chinese_agent = new Chinese($this->thing, "chinese");
        if ($chinese_agent->hasChinese($input) === true) {
            $chinese_thing = new Chinese($this->thing, $input);
            $this->thing_report = $chinese_thing->thing_report;
            if (
                isset($chinese_thing->chineses) and
                isset($chinese_thing->translated_input)
            ) {
                $input = $chinese_thing->translated_input;
            }
        }
        $this->thing->log("expand out compression phrases");
        // And then compress
        // devstack - replace this with a fast general character
        // character recognizer of concepts.
        $compression_thing = new Compression($this->thing, $input);
        if (isset($compression_thing->filtered_input)) {
            // Compressions found.
            $input = $compression_thing->filtered_input;
        }

        $input = trim($input);
        $this->input = $input;

        // Check if it is a command (starts with s slash)
        if (strtolower(substr($input, 0, 2)) != "s/") {
            // Okay here check for input

            if (strtolower($this->subject) == "break") {
                $input_thing = new Input($this->thing, "break");
                $this->thing_report = $input_thing->thing_report;
                return $this->thing_report;
            }

            // Where is input routed to?
            $input_thing = new Input($this->thing, "input");
            if (
                $input_thing->input_agent != null and
                $input_thing->input_agent != $input
            ) {
            }
        }

        $this->thing->log('processed haystack "' . $input . '".', "DEBUG");

        // Now pick up obvious cases where the keywords are embedded
        // in the $input string.
        if (strtolower($input) == "agent") {
            $this->getLink();
            $agent_text = "Ready.";
            if (isset($this->prior_agent)) {
                $link =
                    $this->web_prefix .
                    "agent/" .
                    $this->link_uuid .
                    "/" .
                    strtolower($this->prior_agent);
                $agent_text = $link;
                $this->response .= "Made an agent link. ";
            }

            $this->thing_report["sms"] =
                "AGENT | " . $agent_text . $this->response;
            return $this->thing_report;
        }

        $this->thing->log("looking for optin/optout");

        if (strpos($input, "optin") !== false) {
            $this->thing->log("created a Usermanager agent.");
            $usermanager_thing = new Usermanager($this->thing);
            $this->thing_report = $usermanager_thing->thing_report;
            return $this->thing_report;
        }

        if (strpos($input, "optout") !== false) {
            $this->thing->log("created a Usermanager agent.");
            $usermanager_thing = new Optout($this->thing);
            $this->thing_report = $usermanager_thing->thing_report;
            return $this->thing_report;
        }

        if (strpos($input, "opt-in") !== false) {
            $this->thing->log("Agent created a Usermanager agent.");
            $usermanager_thing = new Optin($this->thing);
            $this->thing_report = $usermanager_thing->thing_report;
            return $this->thing_report;
        }

        if (strpos($input, "opt-out") !== false) {
            $this->thing->log("Agent created a Usermanager agent.");
            $usermanager_thing = new Optout($this->thing);
            $this->thing_report = $usermanager_thing->thing_report;
            return $this->thing_report;
        }

        $this->getLink();
        if (
            isset($this->prior_agent) and
            strtolower($this->prior_agent) == "baseline"
        ) {
            $baseline_agent = new Baseline($this->thing, "response");
        }

        // Then look for messages sent to UUIDS
        $this->thing->log("looking for UUID in address.", "INFORMATION");

        // Is Identity Context?
        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";
        if (preg_match($pattern, $this->to)) {
            $this->thing->log(
                'Agent "Agent" found a  UUID in address.',
                "INFORMATION"
            );

            $uuid_thing = new Uuid($this->thing);

            $this->thing_report = $uuid_thing->thing_report;
            return $this->thing_report;
        }

        $this->thing->log('Agent "Agent" looking for UUID in input.');
        // Is Identity Context?
        $uuid = new Uuid($this->thing, "uuid");

        $uuid->extractUuids($input);
        if (isset($uuid->uuids) and count($uuid->uuids) > 0) {
            $this->thing->log(
                'Agent "Agent" found a  UUID in input.',
                "INFORMATION"
            );

            // Check if only a UUID is provided.
            // If it is send it to the UUID agent.

            if (strtolower($input) == strtolower($uuid->uuid)) {
                $uuid = new Uuid($this->thing);
                $this->thing_report = $uuid->thing_report;
                return $this->thing_report;
            }
        }

        $this->thing->log('Agent "Agent" looking for URL in input.');
        // Is Identity Context?
        $url = new Url($this->thing, "url");
        $urls = $url->extractUrls($input);

        //$urls = $this->extractUrls($input);

        if ($urls !== true and (isset($urls) and count($urls) > 0)) {
            $this->thing->log(
                'Agent "Agent" found a URL in input.',
                "INFORMATION"
            );

            if (isset($urls[0])) {
                $this->url = $urls[0];

                $tokens = explode(" ", $input);
                if (count($tokens) == 1) {
                    $url = new Url($this->thing);
                    $this->thing_report = $url->thing_report;
                    return $this->thing_report;
                }
            }
        }

        // Remove references to named chatbot agents
        $chatbots = $this->extractChatbots($input);

        $input = $this->filterChatbots($input);

        //$input = preg_replace("/\<[^)]+\>/","",$input); // 'ABC '

        // Remove reference to thing.
        //$input = str_replace("thing","",$input);

        // Currently case sensitive.

        if (count($chatbots) === 1) {
            $agent_class_name = ucwords($input);
            $agent_handler = $this->getAgent($agent_class_name, null);
            if ($agent_handler !== false) {
            }
        }

        // dev
        // Check whether input is expected.
        // Or not.
        /*
        if (
            $this->getAgent($agent_class_name, null) ===
            false
        ) {
echo "FALSE";
            //return false;
        } else {
echo "TRUE";
            //return true;
        }
*/

        $input_agent = new Input($this->thing, "input");
        $input_state = $input_agent->stateInput();
        if ($input_agent->stateInput() == "anticipate") {
            $this->response .= "Input anticipated ";

            $agent_class_name = $input_agent->agentInput();
            if (!is_string($agent_class_name)) {
                $agent_class_name = $this->agent_name;
            }

            $this->response .= "by " . $agent_class_name . ". ";

            $agent = $this->getAgent($agent_class_name, "input");

            if (!($agent == false or $agent == true)) {
                $this->thing_report = $agent->thing_report;
                $input_agent->dropInput();

                return $this->thing_report;
            }
        }

        $flag_question = false;
        if ($this->hasQuestion($input)) {
            $flag_question = true;
        }
        $this->flag_question = $flag_question;

        /*
        $pattern = "/\?/";

        if (preg_match($pattern, $input)) {
            // returns true with ? mark
            $this->thing->log(
                "found a question mark and created a Question agent",
                "INFORMATION"
            );
            $question_thing = new Question($this->thing);
//            $this->thing_report = $question_thing->thing_report;
//            return $this->thing_report;
var_dump("saw question");
        }

*/

        $headcode = new Headcode($this->thing, "extract");
        $headcode->extractHeadcodes($input);

        if ($headcode->response === true) {
        } else {
            //if ( is_string($headcode->head_code)) {

            if (
                is_array($headcode->head_codes) and
                count($headcode->head_codes) > 0
            ) {
                // OK have found a headcode.
                // But what if there is an active agent with the request?

                $tokens = explode(" ", $input);

                if (count($tokens) == 1) {
                    $this->thing->log(
                        'Agent "Agent" found a headcode in address.',
                        "INFORMATION"
                    );
                    $headcode_thing = new Headcode($this->thing);
                    $this->thing_report = $headcode_thing->thing_report;
                    return $this->thing_report;
                }
                // Otherwise check in as last resort...
            }
            $this->head_code = $headcode->head_code;
        }

        // Temporarily alias robots
        if (strpos($input, "robots") !== false) {
            $this->thing->log(
                "<pre> Agent created a Robot agent</pre>",
                "INFORMATION"
            );
            if (!isset($this->robot_agent)) {
                $this->robot_agent = new Robot($this->thing);
            }
            $this->thing_report = $this->robot_agent->thing_report;
            return $this->thing_report;
        }

        $this->thing->log(
            "now looking at Words (and Places and Characters).  Timestamp " .
                number_format($this->thing->elapsed_runtime()) .
                "ms.",
            "OPTIMIZE"
        );

        $arr = $this->extractAgents($input);
        $this->input = $input;
        // Sort and pick best scoring agent response.

        usort($this->responsive_agents, function ($a, $b) {
            return $b["score"] - $a["score"];
        });

        //foreach($this->responsive_agents as $i=>$r) {
        //$r['thing_report'] = null;
        //}
        foreach ($this->responsive_agents as $i => $responsive_agent) {
            //echo $responsive_agent['agent_name'] . " " . $responsive_agent['score'];
            //echo "\n";
        }

        if (count($this->responsive_agents) > 0) {
            $this->thing_report = $this->responsive_agents[0]["thing_report"];
            return $this->thing_report;
        }

        $this->thing->log("did not find an Ngram agent to run.", "INFORMATION");

        $this->thing->log("now looking at Group Context.");

        // So no agent ran.

        // Which means that Mordok doesn't have a concept for any
        // emoji which were included.

        // Treat a single emoji as a request
        // for information on the emoji.

        if (isset($emoji_thing->emojis) and count($emoji_thing->emojis) > 0) {
            $emoji_thing = new Emoji($this->thing);
            $this->thing_report = $emoji_thing->thing_report;

            return $this->thing_report;
        }

        $this->thing->log("now looking at Transit Context.");

        $transit_thing = new Transit($this->thing, "extract");
        $this->thing_report = $transit_thing->thing_report;

        if (
            isset($transit_thing->stop) and
            ($transit_thing->stop != false and $transit_thing->stop != "X")
        ) {
            $translink_thing = new Translink($this->thing);
            $this->thing_report = $translink_thing->thing_report;
            return $this->thing_report;
        }

        $this->thing->log("now looking at Place Context.");
        $place_thing = new Place($this->thing, "place");

        if (!$place_thing->isPlace($input)) {
            //        if (!$place_thing->isPlace($this->subject)) {
            //if (($place_thing->place_code == null) and ($place_thing->place_name == null) ) {
        } else {
            // place found
            $place_thing = new Place($this->thing);
            $this->thing_report = $place_thing->thing_report;
            return $this->thing_report;
        }

        $this->thing->log("now looking at Group Context.");

        if ($this->stack_engine_state == "dev") {
            $group_thing = new Group($this->thing, "group");

            if (!$group_thing->isGroup($input)) {
                //        if (!$place_thing->isPlace($this->subject)) {
                //if (($place_thing->place_code == null) and ($place_thing->place_n>
            } else {
                // place found
                $group_thing = new Group($this->thing);
                $this->thing_report = $group_thing->thing_report;
                return $this->thing_report;
            }
        }

        // Here are some other places

        $number_thing = new Number($this->thing, "number");

        $frequency_exception_flag =
            ($number_thing->getDigits($input) == 1 and
            $number_thing->getPrecision($input) == 1);

        if ($number_thing->getPrecision($input) == 0) {
            $frequency_exception_flag = true;
        }

        if (stripos($input, "frequency") !== false) {
            $frequency_exception_flag = false;
        }

        if (stripos($input, "freq") !== false) {
            $frequency_exception_flag = false;
        }

        if (stripos($input, "hz") !== false) {
            $frequency_exception_flag = false;
        }

        $frequency_thing = new Frequency($this->thing, "extract");
        if (
            $frequency_thing->hasFrequency($input) and
            !$frequency_exception_flag
        ) {
            $frequency_thing = new Frequency($this->thing);

            if (
                isset($frequency_thing->band_matches) or
                stripos($input, "frequency")
            ) {
                //if ($frequency_thing->response != "") {
                //            $ars_thing = new Amateurradioservice($this->thing);
                $this->thing_report = $frequency_thing->thing_report;
                return $this->thing_report;
            }
        }

        $repeater_thing = new Repeater($this->thing, "extract");
        $this->thing_report = $repeater_thing->thing_report;

        if (
            $repeater_thing->hasRepeater($input) and !$frequency_exception_flag
        ) {
            $ars_thing = new Amateurradioservice($this->thing, $input);

            if ($ars_thing->response == false) {
                $ars_thing = new Callsign($this->thing);
                $this->thing_report = $ars_thing->thing_report;
                return $this->thing_report;
            } else {
                $ars_thing = new Amateurradioservice($this->thing);
                if ($ars_thing->callsign != null) {
                    $this->thing_report = $ars_thing->thing_report;
                    return $this->thing_report;
                }
            }
        }

        if (is_numeric($input)) {
            $this->thing_report = $number_thing->thing_report;
            return $this->thing_report;
        }

        $this->thing->log(
            "now looking at Nest Context.  Timestamp " .
                number_format($this->thing->elapsed_runtime()) .
                "ms."
        );

        if (strtolower($this->from) != "null@stackr.ca") {
            $entity_list = ["Crow", "Wumpus", "Ant"];
            //$agent_name = "entity";
            foreach ($entity_list as $key => $entity_name) {
                $findagent_agent = new Findagent($this->thing, $entity_name);

                $things = $findagent_agent->thing_report["things"];

                if (!isset($things[0])) {
                    break;
                }
                $uuid = $things[0]["uuid"];

                $thing = new Thing($uuid);

                if ($thing == false) {
                    continue;
                }
                if (!isset($thing->account)) {
                    continue;
                }
                if (!isset($thing->account["stack"])) {
                    continue;
                }

                $variables = $thing->account["stack"]->json->array_data;

                // Check
                if (!isset($variables[strtolower($entity_name)])) {
                    continue;
                }

                if (
                    !isset($variables[strtolower($entity_name)]["refreshed_at"])
                ) {
                    continue;
                }

                $last_heard[strtolower($entity_name)] = strtotime(
                    $variables[strtolower($entity_name)]["refreshed_at"]
                );

                if (!isset($last_heard["entity"])) {
                    $last_heard["entity"] =
                        $last_heard[strtolower($entity_name)];
                    $agent_name = $entity_name;
                }

                if (
                    $last_heard["entity"] <
                    $last_heard[strtolower($entity_name)]
                ) {
                    $last_heard["entity"] =
                        $last_heard[strtolower($entity_name)];
                    $agent_name = $entity_name;
                }
            }

            if (!isset($agent_name)) {
                $agent_name = "Ant";
            }

            $agent_namespace_name =
                "\\Nrwtaylor\\StackAgentThing\\" . $agent_name;

            if (strpos($input, "nest maintenance") !== false) {
                $ant_thing = new $agent_namespace_name($this->thing);
                $this->thing_report = $ant_thing->thing_report;
                return $this->thing_report;
            }

            if (strpos($input, "patrolling") !== false) {
                $ant_thing = new $agent_namespace_name($this->thing);
                $this->thing_report = $ant_thing->thing_report;
                return $this->thing_report;
            }

            if (strpos($input, "foraging") !== false) {
                $ant_thing = new $agent_namespace_name($this->thing);
                $this->thing_report = $ant_thing->thing_report;
                return $this->thing_report;
            }
        }

        /*
        $pattern = "/\?/";

        if (preg_match($pattern, $input)) {
            // returns true with ? mark
            $this->thing->log(
                "found a question mark and created a Question agent",
                "INFORMATION"
            );
            $question_thing = new Question($this->thing);
            $this->thing_report = $question_thing->thing_report;
            return $this->thing_report;
        }
*/
        // Timecheck
        $this->thing_report = $this->timeout(15000);
        if ($this->thing_report != false) {
            return $this->thing_report;
        }
        // Now pull in the context
        // This allows us to be more focused
        // with the remaining time.

        $split_time = $this->thing->elapsed_runtime();

        $context_thing = new Context($this->thing, "extract");
        $this->context = $context_thing->context;
        $this->context_id = $context_thing->context_id;
        $this->thing->log(
            "ran Context " .
                number_format($this->thing->elapsed_runtime() - $split_time) .
                "ms."
        );

        // Timecheck
        if ($this->context != null) {
            $r = "Context is " . strtoupper($this->context);
            $r .= " " . $this->context_id . ". ";
        } else {
            $r = null;
        }

        $this->thing_report = $this->timeout(15000, $r);
        if ($this->thing_report != false) {
            return $this->thing_report;
        }

        if (
            is_array($headcode->head_codes) and
            count($headcode->head_codes) > 0
        ) {
            $this->thing->log(
                'Agent "Agent" found a headcode in address.',
                "INFORMATION"
            );
            $headcode_thing = new Headcode($this->thing);
            $this->thing_report = $headcode_thing->thing_report;
            return $this->thing_report;
        }

        $this->thing->log("now looking for Resource.");
        $resource_agent = new Resource($this->thing, "resource");

        if (!$resource_agent->isResource($input)) {
            //        if (!$place_thing->isPlace($this->subject)) {
            //if (($place_thing->place_code == null) and ($place_thing->place_name == null) ) {
        } else {
            // place found
            $resource_agent = new Resource($this->thing);
            $this->thing_report = $resource_agent->thing_report;
            return $this->thing_report;
        }

        switch (strtolower($this->context)) {
            case "group":
                // Now if it is a head_code, it might also be a train...
                if ($this->stack_engine_state == "dev") {
                    $group_thing = new Group($this->thing, "group");
                    $this->groups = $group_thing->groups;

                    if ($this->groups != null) {
                        // Group was recognized.
                        // Assign to Group manager.

                        // devstack Should check here for four letter
                        // words ie ivor dave help

                        $group_thing = new Group($this->thing);
                        $this->thing_report = $group_thing->thing_report;

                        return $this->thing_report;
                    }
                }

                //Timecheck
                $this->thing_report = $this->timeout(
                    45000,
                    "No matching groups found. "
                );
                if ($this->thing_report != false) {
                    return $this->thing_report;
                }

                break;

            case "headcode":
                // Now if it is a head_code, it might also be a train...
                //$train_thing = new Train($this->thing, $this->head_code);
                $headcode_thing = new Headcode($this->thing, "extract");
                $this->head_codes = $headcode_thing->head_codes;

                if ($this->head_codes != null) {
                    // Headcode was recognized.
                    // Assign to Train manager.

                    $headcode_thing = new Headcode($this->thing);
                    $this->thing_report = $headcode_thing->thing_report;

                    return $this->thing_report;
                }

                //Timecheck
                $this->thing_report = $this->timeout(
                    45000,
                    "No matching headcodes found. "
                );
                if ($this->thing_report != false) {
                    return $this->thing_report;
                }

                break;
            case "train":
                // Now if it is a head_code, it might also be a train...
                $train_thing = new Train($this->thing, "extract");
                //$headcode_thing = new Headcode($this->thing, 'extract');
                $this->headcodes = $train_thing->head_codes;

                if ($this->head_codes != null) {
                    // Headcode was recognized.
                    // Assign to Train manager.

                    $train_thing = new Train($this->thing);
                    $this->thing_report = $train_thing->thing_report;

                    return $this->thing_report;
                }

                //Timecheck
                $this->thing_report = $this->timeout(
                    45000,
                    "No matching train headcodes found. "
                );
                if ($this->thing_report != false) {
                    return $this->thing_report;
                }

                break;

            case "character":
                // Character recognition should be replaceable by alias
                // by refactoring character to use the aliasing engine.
                $character_thing = new Character($this->thing, "character");
                $this->name = $character_thing->name;

                if ($this->name != null) {
                    // Headcode was recognized.
                    // Assign to Train manager.

                    $character_thing = new Character($this->thing);
                    $this->thing_report = $character_thing->thing_report;

                    return $this->thing_report;
                }

                $this->thing_report = $this->timeout(
                    45000,
                    "No matching characters found. "
                );
                if ($this->thing_report != false) {
                    return $this->thing_report;
                }

                break;

            case "place":
                // Character recognition should be replaceable by alias
                // by refactoring character to use the aliasing engine.
                $place_thing = new Place($this->thing, "place");
                $this->place_code = $place_thing->place_code;

                if ($this->place_code != null) {
                    // Headcode was recognized.
                    // Assign to Train manager.

                    ///                    $place_thing = new Place($this->thing);
                    $this->thing_report = $place_thing->thing_report;

                    return $this->thing_report;
                }

                $this->thing_report = $this->timeout(
                    45000,
                    "No matching places found. "
                );
                if ($this->thing_report != false) {
                    return $this->thing_report;
                }

                break;

            default:
                $this->thing_report = $this->timeout(
                    45000,
                    "No matching context found. "
                );
                if ($this->thing_report != false) {
                    return $this->thing_report;
                }
        }

        // So if it falls through to here ... then we are really struggling.

        // This is going to be the most generic form of matching.
        // And probably thre most common...
        // It needs to be here to pick up four letter
        // aliases ie Ivor.
        $alias_thing = new Alias($this->thing, "extract");

        if ($alias_thing->isAlias($input) === true) {
            // Alias was recognized.
            $alias_thing = new Alias($this->thing);
            $this->thing_report = $alias_thing->thing_report;

            return $this->thing_report;
        }

        //Timecheck
        $this->thing_report = $this->timeout(
            45000,
            "No matching aliases found. "
        );
        if ($this->thing_report != false) {
            return $this->thing_report;
        }

        $this->thing->log("now looking at Identity Context.", "OPTIMIZE");

        if (
            isset($chinese_thing->chineses) and
            $chinese_thing->chineses != []
        ) {
            $c = new Chinese($this->thing, "chinese");
            $this->thing_report = $c->thing_report;
            //            $this->thing_report['sms'] = "AGENT | " . "Heard " . $input .".";
            return $this->thing_report;
        }

        // Most useful thing is to acknowledge the url.
        if (count($urls) > 0) {
            $this->thing_report = $url->thing_report;
            return $this->thing_report;
        }

        return $this->thing_report;

        if (isset($chinese_thing->chineses) or isset($emoji_thing->emojis)) {
            $this->thing_report["sms"] = "AGENT | " . "Heard " . $input . ".";
            return $this->thing_report;
        }

        // If a chatbot name is seen, respond.
        //        if ((is_array($chatbot->chatbot_names)) and (count($chatbot->chatbot_names) > 0)) {
        //            $this->thing_report = $chatbot->thing_report;
        //            return $this->thing_report;
        //        }

        $this->thing->log(
            '<pre> Agent "Agent" created a Redpanda agent.</pre>',
            "WARNING"
        );
        $redpanda_thing = new Redpanda($this->thing);

        $this->thing_report = $redpanda_thing->thing_report;

        return $this->thing_report;
    }

    /**
     *
     * @param unknown $text (optional)
     * @return unknown
     */
    function filterAgent($text = null, $more_strip_words = [])
    {
        //$input = strtolower($this->subject);
        $input = $this->input;
        if ($text != null) {
            $input = $text;
        }

        $strip_words = [
            $this->agent_name,
            strtolower($this->agent_name),
            ucwords($this->agent_name),
            strtoupper($this->agent_name),
        ];

        $strip_words = array_merge($strip_words, $more_strip_words);

        foreach ($strip_words as $i => $strip_word) {
            $strip_words[] = str_replace(" ", "", $strip_word);
        }

        foreach ($strip_words as $i => $strip_word) {
            //                    $strip_word = $strip_word['words'];
            $whatIWant = $input;
            if (
                ($pos = strpos(strtolower($input), $strip_word . " is")) !==
                false
            ) {
                $whatIWant = substr(
                    strtolower($input),
                    $pos + strlen($strip_word . " is")
                );
            } elseif (
                ($pos = strpos(strtolower($input), $strip_word)) !== false
            ) {
                $whatIWant = substr(
                    strtolower($input),
                    $pos + strlen($strip_word)
                );
            }

            $input = $whatIWant;
        }
        $input = trim($input);

        $this->input = $input;
        return $input;
    }

    /**
     *
     */
    public function makePNG()
    {
    }

    /**
     *
     */
    public function makePNGs()
    {
    }

    /**
     *
     */
    public function makeJPEG()
    {
    }

    /**
     *
     */
    public function makeJPEGs()
    {
    }

    /**
     *
     * @param unknown $errno
     * @param unknown $errstr
     */
    function warning_handler($errno, $errstr, $errfile, $errline)
    {
        //throw new \Exception('Class not found.');
        //trigger_error("Fatal error", E_USER_ERROR);
        $this->thing->log($errno);
        $this->thing->log($errstr);

        $console =
            "Warning seen. " .
            $errline .
            " " .
            $errfile .
            " " .
            $errno .
            " " .
            $errstr .
            ". ";

        if ($this->stack_engine_state != "prod") {
            $this->thing->console($console . "\n");
            $this->response .= "Warning seen. " . $errstr . ". ";
        }
        // do something
    }

    /**
     *
     * @param unknown $e
     */
    function my_exception_handler($e)
    {
        $this->thing_report["sms"] = "Test";
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
        restore_exception_handler();
        $this->thing->log("fatal exception");
        //$this->thing_report['sms'] = "Merp.";
        $this->thing->log($e);
        // do some erorr handling here, such as logging, emailing errors
        // to the webmaster, showing the user an error page etc
        $this->response .= "Agent could not run. ";
    }

    function shutdownHandler()
    {
        //will be called when php script ends.
        $this->response .= "Shutdown thing. ";
        $lasterror = error_get_last();

        switch ($lasterror["type"] ?? null) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_PARSE:
                $error =
                    "[SHUTDOWN] lvl:" .
                    $lasterror["type"] .
                    " | msg:" .
                    $lasterror["message"] .
                    " | file:" .
                    $lasterror["file"] .
                    " | ln:" .
                    $lasterror["line"];
                $this->mylog($error, "fatal");
        }
    }

    function mylog($error, $errlvl)
    {
        $this->thing->log($error);
        $this->thing->console($error);
    }
}
