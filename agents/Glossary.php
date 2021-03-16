<?php
/**
 * Glossary.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Glossary extends Agent
{
    public $var = "hello";

    /**
     *
     */
    function init()
    {
        $this->thing_report["agency"] =
            "Prepare a helpful glossary of ALL stack agents.";
        $this->thing_report["info"] =
            "This shares what agents the stack has. And what they do.";
        $this->thing_report["help"] =
            "This gives a list of the help text for each Agent.";
        $this->glossary_agents = [];

        //$this->auto_glossary = "on";
        $this->auto_glossary = $this->settingsAgent(["glossary", "auto"]);
        if ($this->auto_glossary === null) {
            $this->auto_glossary = "off";
        }
        $this->auto_glossary = "on";

        $this->glossary_file_error = false;

        $this->time_budget = 5000; // Don't spend more than 5s building the glossary.
    }

    function run()
    {
        if (
            isset($this->glossary_build_flag) and
            $this->glossary_build_flag and
            $this->auto_glossary == "on"
        ) {
            $this->response .= "Saw request to build glossary. ";
            $this->buildGlossary();
            return;
        }

        if (
            isset($this->glossary_update_flag) and
            $this->glossary_update_flag and
            $this->auto_glossary == "on"
        ) {
            $this->response .= "Saw request to update glossary. ";
            $this->updateGlossary();
        }
    }

    /**
     *
     */
    public function respondResponse()
    {
        $this->thing->flagGreen(); // Test report

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    /**
     *
     */
    public function updateGlossary()
    {
        //        $this->test_results = array();

        if (!isset($this->agents_list) or $this->agents_list == []) {
            $this->listAgents();
        }

        $skip_to_agent = "Bar";
        $flag = false;

        $dev_agents = [
            "Agent",
            "Agents",
            "Agentstest",
            "Chart",
            "Discord",
            "Emailhandler",
            "Forgetall",
            "Shuffleall",
            "Googlehangouts",
            "Makelog",
            "Makepdf",
            "Makephp",
            "Makepng",
            "Maketxt",
            "Makeweb",
            "Number",
            "Nuuid",
            "Object",
            "PERCS",
            "Ping",
            "Place",
            "Random",
            "Robot",
            "Rocky",
            "Search",
            "Serial",
            "Serialhandler",
            "Stackrinteractive",
            "Tally",
            "Thought",
            "Timestamp",
            "Uuid",
            "Variables",
            "Wikipedia",
            "Wordgame",
            "Wumpus",
        ];

        $exclude_agents = ["Emailhandler", "Forgetall", "Tally"];

        if (!isset($this->librex_matches) or $this->librex_matches == null) {
            $this->readGlossary();
        }

        // Pick random glossary item.
        $glossary_item = $this->randomGlossary();
        $this->glossary[
            strtoupper($glossary_item["agent_name"])
        ] = $glossary_item;
        $this->appendGlossary();

        $this->loadGlossary();
    }

    // Is there an item in the glossary for the agent name.

    public function existsGlossary($agent_name)
    {
        $match_flag = false;
        foreach ($this->glossary as $glossary_agent_name => $librex) {
            if (strtolower($agent_name) == strtolower($glossary_agent_name)) {
                return true;
            }
        }

        return false;
    }

    public function buildGlossary()
    {
        $this->listAgents();

        foreach ($this->agents_list as $agent) {
            if ($this->excludeGlossary($agent["name"]) === true) {
                continue;
            }

            // See if this exists in the glossary already.
            if ($this->existsGlossary($agent["name"]) === true) {
                continue;
            }

            $this->thing->console($agent["name"] . "\n");

            $this->glossary_agents[] = $agent;

            $v = $agent;
            $agent_class_name = $v["name"];

            if (strtolower($agent_class_name) == "agents") {
                continue;
            }
            if (strtolower($agent_class_name) == "agentstest") {
                continue;
            }

            $glossary_item = $this->agentGlossary($agent_class_name);
            $glossary[$agent_class_name] = $glossary_item;
        }
        $this->glossary = $glossary;
        $this->appendGlossary();
        $this->loadGlossary();
    }

    public function randomGlossary()
    {
        $count = 0;
        while ($count < 10) {
            $count += 1;
            $k = array_rand($this->agents_list);
            $agent = $this->agents_list[$k];
            // See if this is an agent to exclude.
            if ($this->excludeGlossary($agent["name"]) === true) {
                continue;
            }

            // See if this exists in the glossary already.
            if ($this->existsGlossary($agent["name"]) === true) {
                continue;
            }

            $this->thing->console($agent["name"] . "\n");

            $this->glossary_agents[] = $agent;

            $v = $agent;
            $agent_class_name = $v["name"];

            if (strtolower($agent_class_name) == "agents") {
                continue;
            }
            if (strtolower($agent_class_name) == "agentstest") {
                continue;
            }

            $glossary_item = $this->agentGlossary($agent_class_name);
            return $glossary_item;
        }
        return true;
    }

    public function excludeGlossary($agent_name)
    {
        $exclude_agents = ["Emailhandler", "Forgetall", "Tally"];

        foreach ($exclude_agents as $i => $exclude_agent_name) {
            if (strtolower($agent_name) == strtolower($exclude_agent_name)) {
                return true;
            }
        }

        $exclude_prefixes = ["Make"];
        foreach ($exclude_prefixes as $i => $exclude_prefix) {
            if (
                substr(strtolower($agent_name), 0, strlen($exclude_prefix)) ===
                strtolower($exclude_prefix)
            ) {
                return true;
            }
        }

        return false;
    }

    public function agentGlossary($agent_class_name)
    {
        $agent_namespace_name =
            "\\Nrwtaylor\\StackAgentThing\\" . $agent_class_name;

        try {
            $thing = new Thing(null);
            $test_agent = $this->getAgent(
                $agent_class_name,
                $agent_class_name,
                $thing
            );
        } catch (\Throwable $ex) {
            // Error is the base class for all internal PHP error exceptio$

            //            } catch (\Error $ex) { // Error is the base class for all internal>
            $m = $ex->getMessage();
            $help_text = "No help available.";
        }

        $help_text = "No help available.";
        if (isset($test_agent->thing_report["help"])) {
            $help_text = $test_agent->thing_report["help"];
        }
        $info_text = "No info available.";
        if (isset($test_agent->thing_report["info"])) {
            $info_text = $test_agent->thing_report["info"];
        }

        $glossary = [
            "agent_name" => $agent_class_name,
            "text" => $help_text,
            "help" => $help_text,
            "info" => $info_text,
        ];

        return $glossary;
    }

    function uc_first_word($string)
    {
        $s = explode(" ", $string);

        $s[0] = strtoupper(strtolower($s[0]));
        $s = implode(" ", $s);
        return $s;
    }

    function sort($a, $b)
    {
        return strlen($b) - strlen($a);
    }

    public function commandsGlossary($glossary)
    {
        if ($glossary == null) {
            $commands = [];
            $this->web_commands = $commands;
            $this->commands = $commands;
            return $commands;
        }

        $slug_agent = new Slug($this->thing, "slug");
        $command_agent = new Command($this->thing, "command");

        $commands = [];
        foreach ($glossary as $agent_name => $glossary_item) {
            $l = $this->uc_first_word($glossary_item["help"]);

            $commands_new = $command_agent->extractCommands($l);

            $commands = array_merge($commands, $commands_new);
        }

        foreach ($slug_agent->getSlugs() as $i => $slug) {
            $s = str_replace("-", " ", $slug);
            $s = strtoupper($s);
            $commands[] = $s;
        }

        array_unique($commands);

        usort($commands, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        // Check if isSlug.

        $web_commands = [];

        foreach ($commands as $i => $command) {
            if ($slug_agent->isSlug(strtolower($command))) {
                $web_commands[] = $command;
                continue;
            }

            $hyphenated_command = str_replace(" ", "-", strtolower($command));
            if ($slug_agent->isSlug($hyphenated_command)) {
                $web_commands[] = $command;
            }
        }

        $this->web_commands = $web_commands;
        $this->commands = $commands;
        return $commands;
    }

    public function htmlGlossary($array)
    {
        if (!isset($this->slug_agent)) {
            $this->slug_agent = new Slug($this->thing, "slug");
        }

        $line = $this->uc_first_word($array["help"]);

        $t = $line;

        foreach ($this->commands as $i => $command) {
            if (substr($t, 0, strlen($command)) === $command) {
                $html = "<b>" . strtoupper($command) . "</b>";

                $t = preg_replace("/\b" . $command . "\b/u", $html, $t, 1);

                break;
            }
        }

        foreach ($this->web_commands as $i => $command) {
            if (stripos($line, $command) !== false) {
                $slug = $this->slug_agent->extractSlug($command);

                $html =
                    '<a href="' .
                    $this->web_prefix .
                    "" .
                    $slug .
                    '">' .
                    strtoupper($command) .
                    "</a>";

                $t = preg_replace("/\b" . $command . "\b/u", $html, $t);
            }
        }

        return $t;
    }

    function bold_first_word($string)
    {
        $s = explode(" ", $string);

        //    $s[0] = "<b>" . $s[0] . "</b>";
        foreach ($s as $i => $token) {
            if ($i > 0) {
                if (strlen($token) == 1) {
                    break;
                }
            }

            if (ctype_upper($token)) {
                $s[$i] = "<b>" . $s[$i] . "</b>";
                continue;
            }
            break;
        }
        $s[0] = "<b>" . $s[0] . "</b>";
        //    $s[0] = "<b>" . $s[0] . "</b>";
        $s = implode(" ", $s);
        return $s;
    }

    //}

    /**
     *
     */
    function makeSMS()
    {
        $sms = "GLOSSARY | ";

        if (isset($this->response)) {
            $sms .= $this->response;
        }
        $sms .= $this->web_prefix . "thing/" . $this->uuid . "/glossary";

        if (
            isset($this->glossary_agents) and
            count($this->glossary_agents) != 0
        ) {
            $sms .= "Updated glossary for ";
            foreach ($this->glossary_agents as $i => $agent) {
                $sms .= $agent["name"] . " ";
            }
        }
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */
    function appendGlossary()
    {
        $data = "";
        $data .= "# " . $this->current_time . "\n";
        foreach ($this->glossary as $i => $glossary_item) {
            $data .=
                "" .
                $glossary_item["agent_name"] .
                " " .
                $glossary_item["help"] .
                "\n";
        }

        $file = $this->resource_path . "glossary/glossary.txt";
        try {
            file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
            $this->response .= "Updated glossary file. ";
        } catch (Exception $e) {
            $this->response .= "Could not write to glossary file. ";
            $this->glossary_file_error = true;
            // Handle quietly.
        }
    }
    // Overwrite. Use with rebuild.
    function saveGlossary()
    {
        $data = "";
        $data .= "# " . $this->current_time . "\n";
        foreach ($this->glossary as $i => $glossary_item) {
            $data .=
                "" .
                $glossary_item["agent_name"] .
                " " .
                $glossary_item["help"] .
                "\n";
        }

        $file = $this->resource_path . "glossary/glossary.txt";
        try {
            file_put_contents($file, $data);
            $this->response .= "Overwrote glossary file. ";
        } catch (Exception $e) {
            $this->response .= "Could not write to glossary file. ";
            $this->glossary_file_error = true;
            // Handle quietly.
        }
    }

    /**
     *
     */
    function readGlossary()
    {
        if (!isset($this->glossary)) {
            $this->glossary = null;
        }
        $librex_agent = new Librex($this->thing, "glossary/glossary");
        $librex_agent->getMatches();
        if (
            is_array($librex_agent->matches) and
            count($librex_agent->matches) === 0
        ) {
            $this->response .= "Could not read glossary. ";
        }
        $txt = "";
        ksort($librex_agent->matches);

        foreach (
            array_reverse($librex_agent->matches)
            as $agent_name => $packet
        ) {
            if (isset($this->glossary[$agent_name])) {
                continue;
            }
            $this->glossary[strtoupper($agent_name)] = [
                "agent_name" => $packet["proword"],
                "help" => $packet["words"],
            ];
        }

        $this->response .= "Read glossary. ";
        $this->commandsGlossary($this->glossary);
    }

    public function loadGlossary()
    {
        // Load glossary from resource and render as text.

        $librex_agent = new Librex($this->thing, "glossary/glossary");
        $librex_agent->getMatches();

        $txt = "";
        ksort($librex_agent->matches);
        $glossary = [];
        foreach ($librex_agent->matches as $agent_name => $librex_array) {
            $text = $this->startstripText(
                $librex_array["words"],
                $librex_array["proword"]
            );
            $glossary_item = [
                "name" => $librex_array["proword"],
                "help" => $text,
            ];

            $glossary[$agent_name] = $glossary_item;
        }
        $this->glossary = $glossary;
    }

    public function startstripText($text, $prefix)
    {
        if (substr($text, 0, strlen($prefix)) == $prefix) {
            $text = trim(substr($text, strlen($prefix)));
        }
        return $text;
    }

    /**
     *
     */
    function makeTXT()
    {
        // Load glossary from resource and render as text.

        $glossary = $this->glossary;
        $txt = "";
        ksort($glossary);
        foreach ($glossary as $agent_name => $glossary_item) {
            if (!isset($prior_firstChar)) {
                $prior_firstChar = "";
            }
            $firstChar = mb_substr($agent_name, 0, 1, "UTF-8");
            if ($prior_firstChar != $firstChar) {
                $txt .= $firstChar . "\n";
            }
            $prior_firstChar = $firstChar;

            $txt .= $agent_name . " " . $glossary_item["help"] . "\n";
        }

        $this->thing_report["txt"] = $txt;
    }

    /**
     *
     */
    function makeWeb()
    {
        // Use this to recognize open links.
        //$slug_agent = new Slug($this->thing,"slug");

        $web = "<b>Glossary</b>";
        $web .= "<p><p>";

        $glossary = $this->glossary;

        ksort($glossary);
        foreach ($glossary as $agent_name => $glossary_item) {
            if (!isset($prior_firstChar)) {
                $prior_firstChar = "";
            }

            // Seperate out alphabetically.
            $firstChar = mb_substr($agent_name, 0, 1, "UTF-8");
            if ($prior_firstChar != $firstChar) {
                $web .= "<p>" . "<b>" . $firstChar . "</b><br>";
            }
            $prior_firstChar = $firstChar;

            if ($this->validateGlossary($glossary_item) === false) {
                continue;
            }
            /*
            if (strpos($glossary_item["help"], "DEV") !== false) {
                continue;
            }

            if (stripos($glossary_item["help"], "no sms response") !== false) {
                continue;
            }

            if (stripos($glossary_item["help"], "no text response") !== false) {
                continue;
            }

            if (stripos($glossary_item["help"], "agent response") !== false) {
                continue;
            }

            if (
                stripos($glossary_item["help"], "no help available") !== false
            ) {
                continue;
            }

            if (stripos($glossary_item["help"], "not operational") !== false) {
                continue;
            }

            if (stripos($glossary_item["help"], "no response") !== false) {
                continue;
            }

            if (stripos($glossary_item["help"], "devstack") !== false) {
                continue;
            }
*/
            //$arr = ["name" => $agent_name, "text" => $packet["words"]];
            $html = $this->htmlGlossary($glossary_item);

            $web .= $html . "<br>";
        }

        //      foreach ($this->test_results as $key=>$result) {
        //        $web .= "<br>" . $result['agent_name']." " . $result['text'];
        //        }
        $this->thing_report["web"] = $web;
    }

    public function validateGlossary($glossary_item)
    {
        if (strpos($glossary_item["help"], "DEV") !== false) {
            return false;
        }

        if (stripos($glossary_item["help"], "no sms response") !== false) {
            return false;
        }

        if (stripos($glossary_item["help"], "no text response") !== false) {
            return false;
        }

        if (stripos($glossary_item["help"], "agent response") !== false) {
            return false;
        }

        if (stripos($glossary_item["help"], "no help available") !== false) {
            return false;
        }

        if (stripos($glossary_item["help"], "not operational") !== false) {
            return false;
        }

        if (stripos($glossary_item["help"], "no response") !== false) {
            return false;
        }

        if (stripos($glossary_item["help"], "devstack") !== false) {
            return false;
        }

        return true;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->readGlossary();

        $input = $this->assert($this->input, "glossary", false);

        if ($input == "dateline") {
            //            $this->questionDateline();
            return;
        }

        if (stripos($input, "update") !== false) {
            $this->glossary_update_flag = true;
        }

        if (stripos($input, "full") !== false) {
            $this->glossary_full_flag = true;
        }

        if (stripos($input, "build") !== false) {
            $this->glossary_build_flag = true;
        }

        //        $this->dateline = $this->extractDateline($input);
    }
}
