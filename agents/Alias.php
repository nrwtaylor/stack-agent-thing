<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Alias extends Agent
{
    // This is the alias manager.  It assigns an alias coding to
    // N-grams which are the same idea gram.

    // It needs to return the latest alias record for the current context.
    // So first find the context.
    // Then find the latest alias record in that context.

    public $var = "hello";
    public function init()
    {
        $this->keyword = "alias";

        $this->test = "Development code"; // Always

        $this->node_list = ["off" => ["on" => ["off"]]];

        $this->current_time = $this->thing->json->time();

        $this->variables_agent = new Variables(
            $this->thing,
            "variables alias " . $this->from
        );

        $this->alias_thing = $this->variables_agent->thing;
        $this->keywords = ["alias", "is"];

        $this->context = null;
        $this->context_id = null;
        $this->alias = null;
        $this->alias_id = null;

        $default_alias_name = "alias";
    }

    function getState()
    {
        if (!isset($this->state)) {
            $this->state = "X";
        }

        return $this->state;
    }

    function set()
    {
        if (strtolower($this->input) == strtolower($this->agent_name)) {
            return false;
        }

        // A block has some remaining amount of resource and
        // an indication where to start.

        // This makes sure that
        if (!isset($this->alias_thing)) {
            $this->alias_thing = $this->thing;
        }

        $this->variables_agent->setVariable("alias", $this->alias);

        $this->variables_agent->setVariable("context", $this->context);
        $this->variables_agent->setVariable("alias_id", $this->alias_id); // exactly same as context id

        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->thing->json->writeVariable(["alias", "alias"], $this->alias);
        $this->thing->json->writeVariable(["alias", "context"], $this->context);
        $this->thing->json->writeVariable(
            ["alias", "alias_id"],
            $this->alias_id
        ); // exactly same as context_id

        $this->thing->json->writeVariable(
            ["alias", "refreshed_at"],
            $this->current_time
        );

        $this->thing->log(
            $this->agent_prefix .
                " thought " .
                $this->alias .
                " " .
                $this->context .
                " " .
                $this->alias_id .
                "."
        );

        $this->refreshed_at = $this->current_time;
    }

    function extractContext($input = null)
    {
        $this->context_agent = new Context($this->thing, "context " . $input);

        $this->context = $this->context_agent->context;
        $this->context_id = $this->context_agent->context_id;

        $this->thing->log(
            $this->agent_prefix .
                " got context " .
                $this->context .
                " " .
                $this->context_id .
                ". ",
            "DEBUG"
        );

        return $this->context;
    }

    public function isAlias($text = null)
    {
        if ($text == null) {
            return null;
        }

        if (!isset($this->aliases_list)) {
            $this->getAliases();
        }

        foreach ($this->aliases_list as $i => $alias) {
            $alias_name = strtolower($alias["alias"]);
            if (strtolower($text) == $alias_name) {
                return true;
            }
        }

        return false;
    }

    function getAliases()
    {
        $this->aliases_list = [];

        $findagent_thing = new Findagent($this->thing, "alias");

        if ($findagent_thing->thing_report["things"] === true) {
            return true;
        }

        $this->thing->log(
            'Agent "Alias" found ' .
                count($findagent_thing->thing_report["things"]) .
                " Alias Agent Things."
        );
        $this->thing->log(
            'Agent "Alias". Timestamp ' .
                number_format($this->thing->elapsed_runtime()) .
                "ms."
        );

        foreach ($findagent_thing->thing_report["things"] as $thing_object) {
            // While timing is an issue of concern

            $uuid = $thing_object["uuid"];

            if ($thing_object["nom_to"] != "usermanager") {
                $variables_json = $thing_object["variables"];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if (
                    isset($variables["alias"]) and
                    isset($variables["alias"]["alias"])
                ) {
                    $alias = $variables["alias"]["alias"];

                    $variables["alias"][] = $thing_object["task"];
                    $this->aliases_list[] = $variables["alias"];
                }
            }
        }
        return $this->aliases_list;
    }

    function extractAliases($input = null)
    {
        // Get the list of aliases
        if (!isset($this->aliases_list)) {
            $this->getAliases();
        }

        $search_array = null;
        if ($input == null) {
            $input = strtolower($this->subject);
        }

        $this->aliases = [];

        $pieces = explode(" ", strtolower($input));
        foreach ($pieces as $key => $piece) {
            foreach ($this->aliases_list as $key => $alias_arr) {
                $alias = $alias_arr["alias"];

                if (isset($search_array[strtolower($piece)])) {
                } else {
                    $alphanum_alias = preg_replace("/[^A-Z]+/", "", $alias);
                    $this->aliases[] = $alphanum_alias;
                    $search_array = array_combine(
                        array_map("strtolower", $this->aliases),
                        $this->aliases
                    );
                }
            }
        }
        return $this->aliases;
    }

    function get($train_time = null)
    {
        // Loads current alias into $this->alias_thing

        $this->get_start_time = $this->thing->elapsed_runtime();

        $this->thing->log(
            "Timestamp " .
                number_format($this->thing->elapsed_runtime()) .
                "ms."
        );

        $this->thing->json->setField("variables");
        $this->head_code = $this->thing->json->readVariable([
            "headcode",
            "head_code",
        ]);

        $flag_variable_name = "_" . $this->head_code;

        $this->variables_agent = new Variables(
            $this->thing,
            "variables alias" . $flag_variable_name . " " . $this->from
        );

        $this->alias = $this->variables_agent->getVariable("alias");
        $this->alias_id = $this->variables_agent->getVariable("alias_id");

        return;

        $this->variables_agent->getVariables();

        $this->thing->log(
            "Timestamp " . $this->thing->elapsed_runtime() . "ms."
        );

        // So if no alias records are returned, then this is the first
        // record to be set. A null call to set() will start things off.

        // if ($this->variables_agent->alias != null) {
        // Otherwise, we know we have at least a handful of
        // existing aliases to check.

        // Filter by context_id
        $this->getAliases();
        $aliases = [];
        foreach ($this->aliases_list as $key => $alias) {
            if ($alias["alias_id"] == $this->context_id) {
                $aliases[] = $alias;
            }
        }

        if (count($aliases) == 0) {
            $this->response .= "Got zero aliases. ";
            $this->alias = null;
        } else {
            $this->alias = $aliases[0]["alias"];
            $this->alias_id = $aliases[0]["alias_id"];

            $this->alias = null;
            $this->alias_id = null;
        }
    }

    function dropAlias()
    {
        $this->thing->log($this->agent_prefix . "was asked to drop an alias.");

        if (isset($this->alias_thing)) {
            $this->alias_thing->Forget();
            $this->alias_thing = null;
        }

        $this->get();
    }

    function runAlias()
    {
        $this->makeAlias($this->alias);

        $this->state = "running";
    }

    function makeAlias($alias = null)
    {
        $this->thing->log(
            $this->agent_prefix .
                "will make an Alias with " .
                $this->alias .
                "."
        );

        $allow_create_alias = true;

        if ($allow_create_alias) {
            $this->thing->log(
                "found an alias " .
                    $this->alias .
                    "and made a Alias entry" .
                    $this->alias_id .
                    "."
            );
        } else {
            $this->thing->log(
                $this->agent_prefix . "was not allowed to make a Alias entry."
            );
        }

        $this->thing->log(
            $this->agent_prefix . "found an alias and made a Alias entry."
        );
    }

    function extractAlias($input = null)
    {
        // Extract everything to the right
        // of the first is or =
        $pieces = explode(" ", strtolower($input));

        if ($input == null) {
            $alias = "X";
            return $alias;
        } else {
            $input = strtolower($this->subject);

            $keywords = ["is"];
            $pieces = explode(" is ", strtolower($input));

            if (count($pieces) == 2) {
                // A left and a right pairing and nothing else.
                // So we can substitute the word and pass it to Alias.

                $this->left_grams = $pieces[0];
                $this->right_grams = $pieces[1];

                if (strtolower($this->left_grams) == "alias") {
                    $this->alias_id = "alias";
                    $this->alias = $this->right_grams;
                    return;
                }

                if (strtolower($this->right_grams) == "alias") {
                    $this->alias_id = "alias";
                    $this->alias = $this->left_grams;
                    return;
                }

                $left_num_words = count(explode(" ", $this->left_grams));
                $right_num_words = count(explode(" ", $this->right_grams));

                if ($left_num_words < $right_num_words) {
                    $this->alias_id = $this->left_grams;
                    $this->alias = $this->right_grams;
                } else {
                    $this->alias_id = $this->right_grams;
                    $this->alias = $this->left_grams;
                }

                //            if ($left_num_words <= $this->max_ngram) {

                // Could call this as a Gearman worker.
                // Pass it to Alias which handles is/alias as the same word.
                //$instruction = $left_grams . " alias " . $right_grams;

                $this->response .= "Got alias " . $this->alias . ". ";

                if ($this->alias == "place") {
                    // Okay straight to Place
                    $place_agent = new Place($this->thing);
                    return;
                }

                return;
            }
        }
        $alias = "X";
        return $alias;
    }

    function readAlias($text = null)
    {
        if ($text == null) {
            $text = $this->input;
        }

        $this->thing->log("read");
    }

    function addAlias()
    {
        $this->makeAlias();
        $this->get();
    }

    function makeTXT()
    {
        if (!isset($this->aliases_list)) {
            $this->getAliases();
        }

        $txt =
            "These are ALIASES for RAILWAY " .
            $this->variables_agent->nuuid .
            ". ";
        $txt .= "\n";
        $txt .= count($this->aliases_list) . " Aliases retrieved.";

        $txt .= "\n";

        $txt .= " " . str_pad("ALIAS", 24, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("ALIAS_ID", 8, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("CONTEXT", 6, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        foreach ($this->aliases_list as $key => $alias) {
            $txt .= " " . str_pad($alias["alias"], 24, " ", STR_PAD_RIGHT);

            if (!isset($alias["alias_id"])) {
                $alias["alias_id"] = "X";
            }
            if (!isset($alias["context"])) {
                $alias["context"] = "X";
            }

            $txt .= " " . str_pad($alias["alias_id"], 8, " ", STR_PAD_LEFT);
            $txt .= " " . str_pad($alias["context"], 6, " ", STR_PAD_LEFT);

            $txt .= "\n";
        }

        $txt .= "\n";
        $txt .= "---\n";

        $txt .= "alias is " . $this->alias . "\n";
        $txt .= "context is " . $this->context . "\n";
        $txt .= "alias_id is " . $this->alias_id . "\n";

        $txt .= "---";

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;

        return $txt;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $this->thing_report["choices"] = false;

        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index;
        }

        $this->makeChoices();

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] = "This is the Aliasing manager.";
    }

    public function makeChoices()
    {
        if (!isset($this->choices)) {
            $this->thing->choice->Create(
                $this->agent_name,
                $this->node_list,
                "alias"
            );
            $this->choices = $this->thing->choice->makeLinks("alias");
        }
        $this->thing_report["choices"] = $this->choices;
    }

    public function makeSMS()
    {
        if (!isset($this->sms_messages)) {
            $this->sms_messages = [];
        }

        $this->sms_messages[] =
            "ALIAS | Could not find an agent to respond to your message.";
        $this->node_list = ["alias" => ["agent", "message"]];

        $sms = "ALIAS ";

        if (isset($this->alias_id) and strtolower($this->alias_id) != "alias") {
            $sms .= "alias id " . strtoupper($this->alias_id) . " ";
        }

        if (isset($this->alias)) {
            $sms .= "" . strtoupper($this->alias) . " ";
        }

        $sms .= strtoupper($this->head_code);
        $sms .= " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function readSubject()
    {
        $this->num_hits = 0;

        $input = $this->input;

        $this->extractAlias($input);

        // Bail at this point if
        // only extract wanted.
        if ($this->agent_input == "extract") {
            // Added return here March 17 2018
            return;
            if ($this->alias != false) {
                return;
            }
        }

        $this->getAliases();

        $this->extractContext();

        $this->input = $input;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // Keyword

        if (count($pieces) == 1) {
            if ($this->input == "alias") {
                $this->num_hits += 1;
                $this->response .= "Saw request for current alias. ";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "drop":
                            $this->dropAlias();
                            break;

                        case "add":
                            $this->makeAlias();
                            break;

                        case "is":
                            $this->makeAlias($this->alias);
                            return;

                        default:
                    }
                }
            }
        }

        // So we know we don't just have a keyword.

        if (isset($this->alias)) {
            // Likely matching a head_code to a uuid.
            $this->makeAlias($this->alias);
            return;
        }

        if ($pieces[0] == "alias") {
            $this->makeAlias($this->input);
            $this->set();
            //$this->alias = "meepmeep";
            return;
        }

        // Guess we check if it's a Place then?

        $this->readAlias();

        return false;
    }
}
