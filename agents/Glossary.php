<?php
/**
 * Glossary.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Glossary extends Agent
{
    public $var = 'hello';

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

        $this->auto_glossary = "on";
    }

    /**
     *
     */
    function run()
    {
        $this->test_results = [];

        $data_source = $this->resource_path . "glossary/glossary.txt";

//        if (!file_exists($data_source)) {$this->response .= "No glossary found. "; return true;}

        $file_flag = false;

        $data = @file_get_contents($data_source);
        $file_flag = true;
        if ($data == false) {
            // Start the glossary.
            $this->doGlossary();

            // Handle quietly.

            //            $data_source = trim($this->link);

            //            $data = file_get_contents($data_source);
            //            if ($data === false) {
            // Handle quietly.
            //            }

            //            $file = $this->resource_path . "vector/channels.txt";
            //            try {

            //                if ($file_flag == false) {
            //                    file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
            //                }
            //            } catch (Exception $e) {
            //                // Handle quietly.
            //            }
        } else {
            $this->data = $data;
        }
        $this->split_time = $this->thing->elapsed_runtime();
        $this->time_budget = 5000;

        while ($this->auto_glossary == "on") {
            $this->glossary();
            echo "time " .
                ($this->thing->elapsed_runtime() - $this->split_time) .
                "\n";

            if (
                $this->thing->elapsed_runtime() - $this->split_time >
                $this->time_budget
            ) {
                break;
            }
        }

        $this->getAgents();
    }

    /**
     *
     */
    function getAgents()
    {
        if (isset($this->agents)) {
            return;
        }

        $this->agent_list = [];
        $this->agents = [];

        // Only use Stackr agents for now
        // Single source folder ensures uniqueness of N-grams
        $dir =
            $GLOBALS['stack_path'] .
            'vendor/nrwtaylor/stack-agent-thing/agents';
        $files = scandir($dir);

        foreach ($files as $key => $file) {
            if ($file[0] == "_") {
                continue;
            }
            if (strtolower(substr($file, 0, 3)) == "dev") {
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

            $this->agents[$agent_name] = ["name" => $agent_name];
        }
    }

    /**
     *
     */
    public function respond()
    {
        $this->thing->flagGreen(); // Test report

        $this->makeSMS();
        $this->makeWeb();
        $this->makeTxt();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->report();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }
    }

    /**
     *
     */
    public function report()
    {
        $this->thing_report['thing'] = $this->thing;
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        //        $this->thing_report['txt'] = $this->sms_message;
    }

    /**
     *
     */
    public function glossary()
    {
        //        $this->test_results = array();

        if (!isset($this->agents)) {
            $this->getAgents();
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
            $k = array_rand($this->agents);
            $agent = $this->agents[$k];
            $match_flag = false;

            foreach ($exclude_agents as $i => $agent_name) {
                //echo $agent['name'] ." " .$agent_name ."\n";
                if (strtolower($agent['name']) == strtolower($agent_name)) {
                    echo $agent['name'] . " " . $agent_name . "\n";

                    $match_flag = true;
                    break;
                }

                //if (($match_flag)) {continue;}
            }

            if ($match_flag) {
                continue;
            }

            echo $agent['name'] . " ";
            $match_flag = false;
            foreach ($this->librex_matches as $agent_name => $librex) {
                if (strtolower($agent_name) == strtolower($agent['name'])) {
                    $match_flag = true;
                    break;
                }
            }

            if (!$match_flag) {
                break;
            }
        }

        echo $agent["name"] . "\n";

        $this->glossary_agents[] = $agent;

        $v = $agent;
        $agent_class_name = $v["name"];
        $agent_namespace_name =
            '\\Nrwtaylor\\StackAgentThing\\' . $agent_class_name;

        if (strtolower($agent_class_name) == "agents") {
            return;
        }
        if (strtolower($agent_class_name) == "agentstest") {
            return;
        }

        $flag = "red";
        $ex = null;
        try {
            //                $agent_namespace_name = '\\Nrwtaylor\\StackAgentThing\\'.$agent_class_name;

            // devstack

            $thing = new Thing(null);
            //new Meta($thing, "meta");
            $thing->Create(null, null, null);
            //$thing->to = null;
            //$thing->from = null;
            //$thing->subject = null;
            //$thing->db = null;
            //register_shutdown_function('shutDownFunction');
            $test_agent = new $agent_namespace_name($thing, $agent_class_name);

            $help_text = "No help available.";
            if (isset($test_agent->thing_report['help'])) {
                $help_text = $test_agent->thing_report['help'];
            }
        } catch (\Throwable $ex) {
            // Error is the base class for all internal PHP error exceptio$

            //            } catch (\Error $ex) { // Error is the base class for all internal PHP error exceptio$
            //echo $agent_name . "[ RED ]" . "\n";
            $m = $ex->getMessage();
            $help_text = "No help available.";
            //continue;
        }

        $this->test_results[] = [
            "agent_name" => $agent_class_name,
            "text" => $help_text,
        ];
    }

    function uc_first_word($string)
    {
        $s = explode(' ', $string);

        $s[0] = strtoupper(strtolower($s[0]));
        $s = implode(' ', $s);
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
            $l = $this->uc_first_word($packet['words']);

            $commands_new = $command_agent->extractCommands($l);

            $commands = array_merge($commands, $commands_new);
        }

        foreach($slug_agent->getSlugs() as $i=>$slug) {
            $s = str_replace("-"," ",$slug);
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
        //        $command_agent = new Command($this->thing, "command");
        //        $commands = $command_agent->extractCommands($array['text']);

        //        usort($commands, function($a, $b) {
        //            return strlen($b) <=> strlen($a);
        //        });
        if (!isset($this->slug_agent)) {
            $this->slug_agent = new Slug($this->thing, "slug");
        }

        //$line = $array['text'];
        $line = $this->uc_first_word($array['text']);
        //        $line = $this->bold_first_word($line);

        $t = $line;

        foreach ($this->commands as $i => $command) {

            if (substr($t, 0, strlen($command)) === $command) {

                $html = '<b>' . strtoupper($command) . '</b>';

                //                $t = preg_replace('/' . $command . '/i', $html, $t);
                $t = preg_replace('/\b' . $command . '\b/u', $html, $t, 1);

                break;
            }
        }

        foreach ($this->web_commands as $i => $command) {

            if (stripos($line, $command) !== false) {
                $slug = $this->slug_agent->extractSlug($command);

                $html =
                    '<a href="' .
                    $this->web_prefix .
                    '' .
                    $slug .
                    '">' .
                    strtoupper($command) .
                    '</a>';

                //                $t = str_replace($command, $html, $t);
                $t = preg_replace('/\b' . $command . '\b/u', $html, $t);
                //                $t = preg_replace('/' . $command . '/', $html, $t);
            }
            //var_dump($t);
            //echo "<br>";
        }

        return $t;
    }

    function bold_first_word($string)
    {
        $s = explode(' ', $string);

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
        $s = implode(' ', $s);
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
        //        $rand_agents = array_rand($this->glossary_agents, 3);
        $sms .= $this->web_prefix . "thing/" . $this->uuid . "/glossary";
        $sms .= " Made a glossary. ";

        if (
            isset($this->glossary_agents) and
            count($this->glossary_agents) != 0
        ) {
            $sms .= "Updated glossary for ";
            foreach ($this->glossary_agents as $i => $agent) {
                $sms .= $agent['name'] . " ";
                //        $sms .= $agent['name'] . " ";
                //        $sms .= $agent['name'];
            }
        }
        $this->sms_message = $sms;
    }

    /**
     *
     */
    function doGlossary()
    {
        $data = "";
        //$data = implode(" " , $this->test_results);

        foreach ($this->test_results as $i => $result) {
            $data .= "" . $result['agent_name'] . " " . $result['text'] . "\n";
        }

        $file = $this->resource_path . "glossary/glossary.txt";
        try {
            //                if ($file_flag == false) {
            file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
            //                }
        } catch (Exception $e) {
            // Handle quietly.
        }

        $this->data = $data;

        //$this->thing_report['txt'] = $data;
    }

    /**
     *
     */
    function readGlossary()
    {
        $librex_agent = new Librex($this->thing, "glossary/glossary");

        $librex_agent->getMatches();

        $txt = "";
        ksort($librex_agent->matches);

        //$this->commandsGlossary($librex_agent->matches);

        foreach ($librex_agent->matches as $agent_name => $packet) {
            //if (!isset($prior_firstChar)) {$prior_firstChar = "";}
            //$firstChar = mb_substr($agent_name, 0, 1, "UTF-8");
            //if ($prior_firstChar != $firstChar) {$txt .= $firstChar ."\n";}
            //$prior_firstChar = $firstChar;

            //$txt .= $agent_name . " " .$packet['words'] ."\n";
        }

        //    $this->thing_report['txt'] = $txt;
        $this->librex_matches = $librex_agent->matches;
        $this->response .= "Read glossary. ";
        $this->commandsGlossary($this->librex_matches);
    }

    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function getLibrex($text)
    {
        $librex_agent = new Librex($this->thing, "glossary/glossary");
        //$librex_agent->getMatches($this->input, $text);

        // test
        //$text = "fountain";

        $librex_agent->getMatch($text);

        $this->librex_response = $librex_agent->response;
        $this->librex_best_match = $librex_agent->best_match;

        //return($librex_agent->best_match);
        return $librex_agent->response;
    }

    /**
     *
     */
    function makeTxt()
    {
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

            $txt .= $agent_name . " " . $packet['words'] . "\n";
        }

        $this->thing_report['txt'] = $txt;
    }

    /**
     *
     */
    function makeWeb()
    {
        // Use this to recognize open links.
        //$slug_agent = new Slug($this->thing,"slug");

        $web = '<b>Glossary</b>';
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

            if (strpos($packet['words'], 'DEV') !== false) {
                continue;
            }

            if (stripos($packet['words'], 'no sms response') !== false) {
                continue;
            }

            if (stripos($packet['words'], 'no text response') !== false) {
                continue;
            }

            if (stripos($packet['words'], 'agent response') !== false) {
                continue;
            }

            if (stripos($packet['words'], 'no help available') !== false) {
                continue;
            }

            if (stripos($packet['words'], 'not operational') !== false) {
                continue;
            }

            if (stripos($packet['words'], 'no response') !== false) {
                continue;
            }

            if (stripos($packet['words'], 'devstack') !== false) {
                continue;
            }

            $arr = ["name" => $agent_name, "text" => $packet['words']];
            $html = $this->htmlGlossary($arr);

            //     $web .= $this->bold_first_word(
            //         $this->uc_first_word($packet['words']) . "<br>"
            $web .= $html . "<br>";

            //      );
            //            $web .= $agent_name . " " .$packet['words'] ."<br>";
        }

        //      foreach ($this->test_results as $key=>$result) {
        //        $web .= "<br>" . $result['agent_name']." " . $result['text'];
        //        }
        $this->thing_report['web'] = $web;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->readGlossary();
        $input = $this->input;

        if (strtolower($input) != "glossary") {
            $strip_word = "glossary";
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

            $input = trim($whatIWant);

            $this->response = $this->getLibrex($input);
        }

        return false;
    }
}

//function shutDownFunction() {
//    $error = error_get_last();
//    // fatal error, E_ERROR === 1
//    if ($error['type'] === E_ERROR) {
//        //do your stuff
//    }
//}
