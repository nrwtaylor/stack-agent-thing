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
        $this->auto_glossary = "off";

        $this->glossary_file_error = false;

        $this->time_budget = 5000; // Don't spend more than 5s building the glossary.


    }

    /**
     *
     */
    function run()
    {
        $this->test_results = [];

        $data_source = $this->resource_path . "glossary/glossary.txt";

        // No glossary resource.
        // And no auto generate instruction.
        // Return empty.
        if (!file_exists($data_source) and $this->auto_glossary !== "on") {
            $this->response .= "No glossary found. ";
            return true;
        }

        $file_flag = false;

        $data = @file_get_contents($data_source);
        $file_flag = true;

        if ($data == false) {
            // Start the glossary.

            $this->response .= "No existing glossary file seen. ";
            //$this->saveappendGlossary();
        } else {
            $this->data = $data;
        }

        $this->split_time = $this->thing->elapsed_runtime();

        while ($this->auto_glossary == "on") {
            $this->glossary();
            echo "time " .
                ($this->thing->elapsed_runtime() - $this->split_time) .
                "\n";

            if (
                $this->thing->elapsed_runtime() - $this->split_time >
                $this->time_budget
            ) {
                $this->response .= "Time budget elapsed. Run again. ";
                break;
            }
        }
        $this->listAgents();
        //var_dump($this->agents_list);
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
    public function glossary()
    {
        //        $this->test_results = array();

        if (!isset($this->agents_list) or $this->agents_list == []) {
            //var_dump("merp");
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
        $count = 0;
        while ($count < 10) {
            $count += 1;
            $k = array_rand($this->agents_list);
            $agent = $this->agents_list[$k];

// See if this is an agent to exclude.
if ($this->excludeGlossary($agent) === true) {continue;}

// See if this exists in the glossary already.
if ($this->existsGlossary($agent) === true) {continue;}

        $this->thing->console($agent["name"] . "\n");

        $this->glossary_agents[] = $agent;

        $v = $agent;
        $agent_class_name = $v["name"];

        if (strtolower($agent_class_name) == "agents") {
            return;
        }
        if (strtolower($agent_class_name) == "agentstest") {
            return;
        }

        $glossary = $this->agentGlossary($agent_class_name);
        $this->test_results[] = $glossary;

$this->saveGlossary();

$this->loadGlossary();

}

var_dump($test_results);
    }

    public function existsGlossary($agent_name) {

            $match_flag = false;
            foreach ($this->glossary as $glossary_agent_name => $librex) {
                if (strtolower($agent_name) == strtolower($glossary_agent_name)) {
                    //$match_flag = true;
                    return true;
                    //break;
                }
            }

           // if (!$match_flag) {
           //     break;
           // }
        return false;


    }

    public function excludeGlossary($agent_name) {

        $exclude_agents = ["Emailhandler", "Forgetall", "Tally"];


            foreach ($exclude_agents as $i => $exlude_agent_name) {
                //echo $agent['name'] ." " .$agent_name ."\n";
                if (strtolower($agent_name) == strtolower($exclude_agent_name)) {
//                    echo $agent["name"] . " " . $agent_name . "\n";
return true;
//                    $match_flag = true;
//                    break;
                }
            }

return false;

    }

    public function agentGlossary($agent_class_name)
    {
        $agent_namespace_name =
            "\\Nrwtaylor\\StackAgentThing\\" . $agent_class_name;

        //$flag = "red";
        //$ex = null;
        try {
            $thing = new Thing(null);
            $test_agent = $this->getAgent($agent_class_name, null, $thing);

            $help_text = "No help available.";
            if (isset($test_agent->thing_report["help"])) {
                $help_text = $test_agent->thing_report["help"];
            }
        } catch (\Throwable $ex) {
            // Error is the base class for all internal PHP error exceptio$

            //            } catch (\Error $ex) { // Error is the base class for all internal>
            $m = $ex->getMessage();
            $help_text = "No help available.";
        }

        $glossary = [
            "agent_name" => $agent_class_name,
            "text" => $help_text,
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

    public function commandsGlossary($matches)
    {
        $slug_agent = new Slug($this->thing, "slug");
        $command_agent = new Command($this->thing, "command");
        //$commands = $command_agent->extractCommands($array['text']);

        $commands = [];
        foreach ($matches as $agent_name => $packet) {
            $l = $this->uc_first_word($packet["words"]);

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

        $line = $this->uc_first_word($array["text"]);

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
    function saveGlossary()
    {
        $data = "";
        //$data = implode(" " , $this->test_results);

        foreach ($this->test_results as $i => $result) {
            $data .= "" . $result["agent_name"] . " " . $result["text"] . "\n";
        }

        $file = $this->resource_path . "glossary/glossary.txt";
        try {
            //                if ($file_flag == false) {
            file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
            //                }
        } catch (Exception $e) {
            $this->glossary_file_error = true;
            // Handle quietly.
        }

        $this->data = $data;
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

        $txt = "";
        ksort($librex_agent->matches);

        foreach (
            array_reverse($librex_agent->matches)
            as $agent_name => $packet
        ) {
            if (isset($this->glossary[$agent_name])) {
                continue;
            }
            $this->glossary[$agent_name] = $packet;
        }

        $this->librex_matches = $librex_agent->matches;
        $this->response .= "Read glossary. ";
        $this->commandsGlossary($this->librex_matches);
    }

    public function loadGlossary() {

        // Load glossary from resource and render as text.

        $librex_agent = new Librex($this->thing, "glossary/glossary");

        $librex_agent->getMatches();

        $txt = "";
        ksort($librex_agent->matches);
        $this->glossary = $librex->matches;
    }

    /**
     *
     */
    function makeTXT()
    {
        // Load glossary from resource and render as text.

        $librex_agent = new Librex($this->thing, "glossary/glossary");

        $librex_agent->getMatches();

        $txt = "";
        ksort($librex_agent->matches);
        foreach ($librex_agent->matches as $agent_name => $packet) {
            if (!isset($prior_firstChar)) {
                $prior_firstChar = "";
            }
            $firstChar = mb_substr($agent_name, 0, 1, "UTF-8");
            if ($prior_firstChar != $firstChar) {
                $txt .= $firstChar . "\n";
            }
            $prior_firstChar = $firstChar;

            $txt .= $agent_name . " " . $packet["words"] . "\n";
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
        $librex_agent = new Librex($this->thing, "glossary/glossary");

        $librex_agent->getMatches();

        ksort($librex_agent->matches);
        foreach ($librex_agent->matches as $agent_name => $packet) {
            if (!isset($prior_firstChar)) {
                $prior_firstChar = "";
            }

            // Seperate out alphabetically.
            $firstChar = mb_substr($agent_name, 0, 1, "UTF-8");
            if ($prior_firstChar != $firstChar) {
                $web .= "<p>" . "<b>" . $firstChar . "</b><br>";
            }
            $prior_firstChar = $firstChar;

            if (strpos($packet["words"], "DEV") !== false) {
                continue;
            }

            if (stripos($packet["words"], "no sms response") !== false) {
                continue;
            }

            if (stripos($packet["words"], "no text response") !== false) {
                continue;
            }

            if (stripos($packet["words"], "agent response") !== false) {
                continue;
            }

            if (stripos($packet["words"], "no help available") !== false) {
                continue;
            }

            if (stripos($packet["words"], "not operational") !== false) {
                continue;
            }

            if (stripos($packet["words"], "no response") !== false) {
                continue;
            }

            if (stripos($packet["words"], "devstack") !== false) {
                continue;
            }

            $arr = ["name" => $agent_name, "text" => $packet["words"]];
            $html = $this->htmlGlossary($arr);

            $web .= $html . "<br>";
        }

        //      foreach ($this->test_results as $key=>$result) {
        //        $web .= "<br>" . $result['agent_name']." " . $result['text'];
        //        }
        $this->thing_report["web"] = $web;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->readGlossary();

        $input = $this->assert($this->input, "glossary", false);
        //var_dump($input);
        return;
    }
}
