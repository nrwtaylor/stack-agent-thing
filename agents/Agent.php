<?php /*
 * Agent.php
 *
 * @package default
 */

/*
 * Agent.php
 *
 * @package default
 */
namespace Nrwtaylor\StackAgentThing;

// Agent resolves message disposition

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

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
        //microtime(true);
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
            //$this->agent_input = strtolower($input);
            // TODO Variable configured case sensitive strings 3 November 2020.
            $this->agent_input = $input;
        }

        $this->getName();
        $this->agent_prefix = 'Agent "' . ucfirst($this->agent_name) . '" ';
        // Given a "thing".  Instantiate a class to identify
        // and create the most appropriate agent to respond to it.

        $this->thing = $thing;
        $this->thing_report['thing'] = $this->thing;

        if (!isset($this->thing->run_count)) {
            $this->thing->run_count = 0;
        }

        $this->thing->log("Got thing.");
        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {
            $this->dev = true;
            $this->test = true;
        }
        if ($this->thing->container['stack']['engine_state'] == 'dev') {
            $this->dev = true;
        }
        $this->getMeta();
        $this->thing->log("Got meta.");
        // Tell the thing to be quiet
        if ($this->agent_input != null) {
            //            $this->thing->silenceOn();
            //            $quiet_thing = new Quiet($this->thing,"quiet on");
        }

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        // And some more stuff
        $this->short_name = $thing->container['stack']['short_name'];
        $this->stack_state = $thing->container['stack']['state'];

        $this->stack_engine_state = $thing->container['stack']['engine_state'];

        $this->sqlresponse = null;

        $this->thing->log('running on Thing ' . $this->thing->nuuid . '.');

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';
        $this->agents_path = $GLOBALS['stack_path'] . 'agents/';
        $this->agents_path =
            $GLOBALS['stack_path'] .
            'vendor/nrwtaylor/stack-agent-thing/agents/';

        if (
            isset($this->thing->container['api'][strtolower($this->agent_name)])
        ) {
            $this->settings =
                $this->thing->container['api'][strtolower($this->agent_name)];
        }

        $this->agent_version = 'redpanda';

        // TODO

        //$this->time_agent = new Time($this->thing,"time");
        //$this->current_time = $this->time_agent->time;
        $this->current_time = $this->thing->time();

        $this->num_hits = 0;

        $this->verbosity = 9;

        $this->context = null;
        $this->response = "";

        if (isset($thing->container['api']['agent'])) {
            if ($thing->container['api']['agent'] == "off") {
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

        //$link_agent = new Link($this->thing,"link");

        $this->init();
        $this->get();
        try {
            $this->read();

            $this->run();

            $this->make();

            // This is where we deal with insufficient space to serialize the variabes to the stack.
            //if (!isset($this->signal_thing)) {return true;}
            //        try {
            $this->set();
        } catch (\OverflowException $t) {
            $this->response =
                "Stack variable store is full. Variables not saved. Text FORGET ALL.";

            $web_thing = new Thing(null);
            $web_thing->Create(
                $this->from,
                "error",
                "Overflow: try set failed."
            );
            $this->thing_report['sms'] = "STACK | " . $this->response;
            $this->thing->log("caught overflow exception.");
            // Executed only in PHP 7, will not match in PHP 5
        } catch (\Throwable $t) {
            $this->thing_report['sms'] = $t->getMessage();
            $web_thing = new Thing(null);
            $web_thing->Create(
                $this->from,
                "error",
                "Throwable: Set failed." .
                    $t->getMessage() .
                    " " .
                    $t->getTraceAsString()
            );

            echo $t->getLine() . "---" . $t->getFile() . $t->getMessage();
            echo "\n";

            //$this->response = "STACK | Variable store is full. Text FORGET ALL.";
            //$this->thing_report['sms'] = "STACK | Variable store is full. Text FORGET ALL.";
            $this->thing->log("caught throwable.");
            // Executed only in PHP 7, will not match in PHP 5
        } catch (\Exception $e) {
            echo $t->getLine() . "---" . $t->getFile() . $t->getMessage();
            echo "\n";

            $web_thing = new Thing(null);
            $web_thing->Create(
                $this->from,
                "error",
                "Exception: Set failed." .
                    $e->getMessage() .
                    " " .
                    $t->getTraceAsString()
            );
            $this->thing->log("caught exception");
            // Executed only in PHP 5, will not be reached in PHP 7
        }

        if ($this->agent_input == null or $this->agent_input == "") {
            $this->respond();
        }

        if (!isset($this->response)) {
            $this->response = "No response found.";
        }
        $this->thing_report['response'] = $this->response;

        $this->thing->log(
            'ran for ' . number_format($this->thing->elapsed_runtime()) . 'ms.'
        );

        $this->thing_report['etime'] = number_format(
            $this->thing->elapsed_runtime()
        );
        $this->thing_report['log'] = $this->thing->log;
        if (isset($this->test) and $this->test) {
            $this->test();
        }
    }

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
    }

    /**
     *
     */
    public function set()
    {
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
        if (!is_array($findagent_thing->thing_report['things'])) {
            return;
        }
        $count = count($findagent_thing->thing_report['things']);

        //$rule_agent = new Rule($this->thing, "rule");

        if ($count > 0) {
            foreach (
                array_reverse($findagent_thing->thing_report['things'])
                as $thing_object
            ) {
                $uuid = $thing_object['uuid'];
                $variables_json = $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                $associations_json = $thing_object['associations'];
                $associations = $this->thing->json->jsontoArray(
                    $associations_json
                );

                //$thing = new \stdClass();
                $thing = new Thing(null);
                $thing->subject = $thing_object['task'];

                $thing->uuid = $thing_object['uuid'];
                $thing->nom_to = $thing_object['nom_to'];
                $thing->nom_from = $thing_object['nom_from'];

                $thing->variables = $variables;
                $thing->created_at = $thing_object['created_at'];

                $thing->associations = $associations;

                if (isset($variables[$agent_name]) or $agent_name == 'things') {
                    //                    $things[$uuid] = $variables[$agent_name];
                    $things[$uuid] = $thing;
                }

                $response = $this->readAgent($thing_object['task']);
            }
        }

        return $things;
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
        if (!is_array($findagent_thing->thing_report['things'])) {
            return;
        }
        $count = count($findagent_thing->thing_report['things']);

        //$rule_agent = new Rule($this->thing, "rule");

        if ($count > 0) {
            foreach (
                array_reverse($findagent_thing->thing_report['things'])
                as $thing_object
            ) {
                $uuid = $thing_object['uuid'];
                $variables_json = $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                //if (isset($variables[$agent_name])) {
                $variables_array[$uuid] = $variables;
                //}

                //$response = $this->readAgent($thing_object['task']);
            }
        }

        return $variables_array;
    }

    public function memoryAgent($text = null) {

            //$agent_class_name = "Dateline";
$agent_class_name = $text;
            $agent_name = strtolower($agent_class_name);

            $slug_agent = new Slug($this->thing, "slug");
            $slug = $slug_agent->getSlug(
                $agent_name . "-" . $this->from
            );


                $agent_namespace_name =
                    '\\Nrwtaylor\\StackAgentThing\\' . $agent_class_name;

                ${$agent . '_agent'} = new $agent_namespace_name($this->thing, $agent_name);

                ${$agent_name} = ${$agent . '_agent'}->{'get' .
                    $agent_class_name}();
                ${$agent_name}['retrieved_at'] = $this->current_time;

                $this->memory->set($slug, ${$agent_name});

                $this->response .= "Got {$agent_name}. ";


        return ${$agent_name};


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
        // So ... don't call yourself.
        // Don't do a make on yourself.
        $this->thing->log("start make.");
        $this->makeAgent();

        $this->makeResponse();
        //$this->makeChoices();
        $this->makeMessage();
        $this->makeChart();
        $this->makeImage();
        $this->makePNG();
        $this->makePNGs();
        $this->makeSMS();
        $this->makeWeb();

        // Explore adding in INFO and HELP to web response.
        $dev_agents = ["response", "help", "info", "sms", "message"];
        $prod_agents = ["response", "help", "info"];

        $agents = $dev_agents;
        if ($this->stack_engine_state == 'prod') {
            $agents = $prod_agents;
        }
        $web = "";
        //        if (isset($this->thing_report['web'])) {
        if (isset($this->thing_report['web'])) {
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
                if (strpos($this->thing_report['web'], $needle) !== false) {
                    continue;
                }

                $web .= "<b>" . strtoupper($agent_name) . "</b><p>";
                $web .= $this->thing_report[$agent_name];
                $web .= "<p>";
            }
        }

        if (isset($this->thing_report['web'])) {
            if ($this->agent_name != "agent") {
                $needle = ucwords($this->agent_name) . " Agent";

                if (strpos($this->thing_report['web'], $needle) !== false) {
                } else {
                    $this->thing_report['web'] =
                        "<b>" .
                        ucwords($this->agent_name) .
                        " Agent" .
                        "</b><br><p>" .
                        $this->thing_report['web'];
                }
            }
            $needle = '<p>';
            $pos = strpos($this->thing_report['web'], $needle);
            $length = strlen($this->thing_report['web']);
            $needle_length = strlen($needle);

            // Note our use of ===.  Simply == would not work as expected
            // because the position of 'a' was the 0th (first) character.
            if ($pos === false) {
                //    echo "The string '$findme' was not found in the string '$mystring'";
                $this->thing_report['web'] .= "<p>";
            } else {
                //    echo "The string '$findme' was found in the string '$mystring'";
                //    echo " and exists at position $pos";

                if ($pos == $length - $needle_length) {
                } else {
                    //$this->thing_report['web'] .= "<p>";
                }
                $this->thing_report['web'] .= "<p>";
            }

            $this->thing_report['web'] .= "<p>" . $web;
        }

        $this->makeSnippet();
        $this->makeEmail();
        $this->makeTXT();

        $this->makePDF();

        $this->makeKeyword();
        $this->makeLink();

        $this->makeHelp();
        $this->makeInfo();

        // devstack

        if ($this->agent_name != "web" and !isset($this->thing->web_agent)) {
            $this->thing->web_agent = new Web($this->thing, "agent");
            $this->web_state = $this->thing->web_agent->state;
        }

        //        if ($this->agent_name != "url" and !isset($this->thing->url_agent)) {
        //            $this->thing->url_agent = new Url($this->thing);
        //        }

        $web_state = "off";
        if (isset($this->web_state)) {
            $web_state = $this->web_state;
        }

        if (
            isset($this->thing->web_agent->state) and
            $this->thing->web_agent->state == "on"
        ) {
            if (
                isset($this->thing_report['sms']) and
                //and (!$this->thing->url_agent->hasUrls($this->thing_report['sms']))
                substr($this->thing_report['link'], -4) != "help"
            ) {
                if (substr_count($this->thing_report['sms'], "http") == 0) {
                    $this->thing_report['sms'] =
                        $this->thing_report['sms'] .
                        " " .
                        $this->thing_report['link'];
                }
            }
        }
        $this->thing->log("completed make.");
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

        $file = $this->resource_path . '' . $resource_name;

        if (!file_exists($file)) {
            return true;
        }

        $contents = file_get_contents($file);

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
    public function getCallingagent()
    {
        //get the trace
        $trace = debug_backtrace();

        // Get the class that is asking for who awoke it
        if (!isset($trace[1]['class'])) {
            $this->calling_agent = true;
            return true;
        }

        $class_name = $trace[1]['class'];
        // +1 to i cos we have to account for calling this function
        for ($i = 1; $i < count($trace); $i++) {
            if (isset($trace[$i])) {
                if (
                    isset($trace[$i]['class']) and
                    $class_name != $trace[$i]['class']
                ) {
                    // is it set?
                    // is it a different class
                    $this->calling_agent = $trace[$i]['class'];
                    return $trace[$i]['class'];
                }
            }
        }

        $this->calling_agent = null;
    }

    public function makeAgent()
    {
        $this->currentAgent();
        $agent = "help";
        if (isset($this->current_agent)) {
            $agent = $this->current_agent;

            $this->thing_report['agent'] = $agent;
        }
    }

    /**
     *
     */
    function makeChannel($name = null)
    {
        $text = strtolower($this->agent_name);
        $file = $this->resource_path . '/' . $text . '/' . $text . '.txt';

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
                $text = trim(str_replace(['#', '[', ']'], '', $line));
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

    public function getMemcached()
    {
        if (isset($this->mem_cached)) {
            return;
        }

        // Null?
        // $this->mem_cached = null;

        try {
            $this->mem_cached = new \Memcached(); //point 2.
            $this->mem_cached->addServer("127.0.0.1", 11211);
        } catch (\Throwable $t) {
            // Failto
            $this->mem_cached = new Memory($this->thing, "memory");
            //restore_error_handler();
            $this->thing->log(
                'caught memcached throwable. made memory',
                "WARNING"
            );
            return;
        } catch (\Error $ex) {
            $this->thing->log('caught memcached error.', "WARNING");
            return true;
        }
    }

    // Plan to deprecate getMemcached terminology.
    public function getMemory($text = null)
    {
//        if (isset($this->memory)) {
//            return;
//        }

        // Null?
        // $this->mem_cached = null;
        // Fail to stack php memory code if Memcached is not availble.
if (!isset($this->memory)) {
        try {
            $this->memory = new \Memcached(); //point 2.
            $this->memory->addServer("127.0.0.1", 11211);
        } catch (\Throwable $t) {
            // Failto
            $this->memory = new Memory($this->thing, "memory");
            //restore_error_handler();
            $this->thing->log(
                'caught memcached throwable. made memory',
                "WARNING"
            );
            return;
        } catch (\Error $ex) {
            $this->thing->log('caught memcached error.', "WARNING");
            return true;
        }
}

        $memory = $this->memory->get($text);
        return $memory;
    }



    /**
     *
     */
    public function getName()
    {
        $this->agent_name = explode("\\", strtolower(get_class($this)))[2];
    }

    function debug()
    {
        $this->thing->log('agent_name is  ' . $this->agent_name . '.');

        $this->getCallingagent();
        $this->thing->log('Calling agent is  ' . $this->calling_agent . '.');

        $agent_input_text = $this->agent_input;
        if (is_array($this->agent_input)) {
            $agent_input_text = "array";
            if (isset($this->agent_input['thing'])) {
                $agent_input_text = "thing";
            }
        }

        $this->thing->log('agent_input is  ' . $agent_input_text . '.');
        $this->thing->log('subject is  ' . $this->subject . '.');
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
        $whatIWant = $input;

        $pos = strpos(strtolower($input), $agent);

        if (($pos = strpos(strtolower($input), $agent . " is")) !== false) {
            $whatIWant = substr($input, $pos + strlen($agent . " is"));
        } elseif (($pos = strpos(strtolower($input), $agent)) !== false) {
            // Distinguish if assertion match is at beginning or end of text.
            if (strlen($input) == $pos + strlen($agent)) {
                $length = strlen($input) - strlen($agent);
                $whatIWant = substr($input, 0, $length);
            } else {
                $whatIWant = substr($input, $pos + strlen($agent));
            }
        }
        $filtered_input = trim($whatIWant, " ");

        if ($flag_lowercase === true) {
            $filtered_input = strtolower($filtered_input);
        }

        return $filtered_input;
    }

    /**
     *
     * @param unknown $thing (optional)
     */
    public function getMeta($thing = null)
    {
        /*
        if ($thing == null) {
            $thing = $this->thing;
        }

        // Non-nominal
        $this->uuid = $thing->uuid;
        if (!isset($thing->to)) {
            $this->to = null;
        } else {
            $this->to = $thing->to;
        }

        // Potentially nominal
        if (!isset($thing->subject)) {
            $this->subject = null;
        } else {
            $this->subject = $thing->subject;
        }

        // Treat as nomina
        if (!isset($thing->from)) {
            $this->from = null;
        } else {
            $this->from = $thing->from;
        }
        // Treat as nomina
        if (!isset($thing->created_at)) {
            $this->created_at = null;
        } else {
            $this->created_at = $thing->created_at;
        }
*/

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
            $this->subject = "merp";
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
        if (!isset($this->thing->json->array_data['message']['agent'])) {
            $this->current_agent = "help";
        } else {
            $this->current_agent =
                $this->thing->json->array_data['message']['agent'];
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
        $findagent_thing = new Findagent($this->thing, 'thing');

        // This pulls up a list of other Block Things.
        // We need the newest block as that is most likely to be relevant to
        // what we are doing.

        $this->max_index = 0;

        $match = 0;

        if ($findagent_thing->thing_report['things'] == true) {
            $this->link_uuid = null;
            return false;
        }

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {
            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_uuid = $block_thing['uuid'];
                if ($match == 2) {
                    break;
                }
            }
        }

        $previous_thing = new Thing($block_thing['uuid']);
        $this->prior_thing = $previous_thing;
        if (!isset($previous_thing->json->array_data['message']['agent'])) {
            $this->prior_agent = "help";
        } else {
            $this->prior_agent =
                $previous_thing->json->array_data['message']['agent'];
        }

        $this->link =
            $this->web_prefix .
            'thing/' .
            $this->uuid .
            '/' .
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
        $findagent_thing = new Findagent($this->thing, 'thing');

        $this->max_index = 0;
        $match = 0;
        $link_uuids = [];

        foreach ($findagent_thing->thing_report['things'] as $block_thing) {
            $this->thing->log(
                $block_thing['task'] .
                    " " .
                    $block_thing['nom_to'] .
                    " " .
                    $block_thing['nom_from']
            );
            if ($block_thing['nom_to'] != "usermanager") {
                $match += 1;
                $this->link_task = $block_thing['task'];
                $link_tasks[] = $block_thing['task'];
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
                        'web',
                        'pdf',
                        'txt',
                        'log',
                        'php',
                        'syllables',
                        'brilltagger',
                    ])
                ) {
                    continue;
                }

                $this->link_task = $link_task;
                break;
            }
        }

        $this->web_exists = true;
        if (!isset($agent_thing->thing_report['web'])) {
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
        $this->respondResponse();
    }

    /**
     *
     */
    public function respondResponse()
    {
        $agent_flag = true;
        if ($this->agent_name == "agent") {
            return;
        }

        if ($agent_flag == true) {
            if (!isset($this->thing_report['sms'])) {
                $this->thing_report['sms'] = "AGENT | Standby.";
            }

            $this->thing_report['message'] = $this->thing_report['sms'];
            if ($this->agent_input == null or $this->agent_input == "") {
                $message_thing = new Message($this->thing, $this->thing_report);
                $this->thing_report['info'] =
                    $message_thing->thing_report['info'];
            }
        }
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
    }

    /**
     *
     */
    public function makeWeb()
    {
    }

    public function makeLink()
    {
        //$link = $this->web_prefix . "thing/" . $this->uuid . "/" . $this->agent_name;
        //$this->thing_report['link'] = $link;
        //return;

        //if (isset($this->thing_report['link'])) {return;}
        //$link = $this->web_prefix;

        //if (isset($this->keyword)) {
        //$link = $this->web_prefix . "thing/" . $this->uuid . "/" . $this->keyword;
        //}

        if (isset($this->link)) {
            $link = $this->link;
        }

        if (isset($this->agent->link)) {
            $link = $this->agent->link;
        }

        if (isset($this->current_agent)) {
            $link =
                $this->web_prefix .
                'thing/' .
                $this->uuid .
                '/' .
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
        $this->thing_report['link'] = $link;
    }

    /**
     *
     */
    public function makeHelp()
    {
    }

    /**
     *
     */
    public function makeInfo()
    {
        if (!isset($this->thing_report['info'])) {
            if (isset($this->info)) {
                $this->thing_report['info'] = $this->info;
                return;
            }

            $info = $this->info();
            $this->thing_report['info'] = $info;
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

        if (isset($this->thing_report['sms'])) {
            $tokens = explode("|", $this->thing_report['sms']);
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
        $this->thing_report['keyword'] = $keyword;
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
        if (isset($this->thing_report['choices'])) {
            return;
        }
        if (isset($this->choices)) {
            $this->thing_report['choices'] = $this->choices;
            return;
        }

        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    /**
     *
     */

    public function makeSnippet()
    {
        if (isset($this->thing_report['snippet'])) {
            $this->thing_report['snippet'] = str_replace(
                '[word]',
                $this->word,
                $this->thing_report['snippet']
            );
        }

        if (!isset($this->thing_report['snippet'])) {
            $this->thing_report['snippet'] = "";
        }
    }

    /**
     *
     */
    public function makeTXT()
    {
        //if (!isset($this->thing_report['sms'])) {$this->makeSMS();}
        //        $this->thing_report['txt'] = $this->thing_report['sms'];
    }

    /**
     *
     */
    public function makeMessage()
    {
        //if (!isset($this->thing_report['sms'])) {$this->makeSMS();}

        //        $this->thing_report['message'] = $this->thing_report['sms'];
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

        if (!isset($this->thing_report['sms'])) {
            if (isset($this->sms_message)) {
                $this->thing_report['sms'] = $this->sms_message;
            }

            if (!isset($this->thing_report['sms'])) {
                $sms = strtoupper($this->agent_name);

                if ($this->response == "") {
                    $sms .= " >";
                } else {
                    $sms .= " | " . $this->response;
                }

                $this->thing_report['sms'] = $sms;
                $this->thing_report['sms'] = null;
            }

            if (!isset($this->sms_message)) {
                $this->sms_message = $this->thing_report['sms'];
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

        $this->prior_task = $prior_thing['thing']->task;
        $this->prior_agent = $prior_thing['thing']->nom_to;

        $uuid = $prior_thing['thing']->uuid;
        $variables_json = $prior_thing['thing']->variables;
        $variables = $this->thing->json->jsontoArray($variables_json);

        $this->prior_variables = $variables;
    }

    /**
     *
     * @param unknown $input
     * @param unknown $n     (optional)
     * @return unknown
     */
    private function getNgrams($input, $n = 3)
    {
        $words = explode(' ', $input);
        $ngrams = [];

        foreach ($words as $key => $value) {
            if ($key < count($words) - ($n - 1)) {
                $ngram = "";
                for ($i = 0; $i < $n; $i++) {
                    $ngram .= $words[$key + $i];
                }
                $ngrams[] = $ngram;
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
            case 'place':
                $array = ['place', 'mornington crescent'];
                break;
            case 'group':
                $array = ['group', 'say hello', 'listen', 'join'];
                break;
            case 'train':
                $array = ['train', 'run train', 'red', 'green', 'flag'];
                break;
            case 'headcode':
                $array = ['headcode'];
                break;
            case 'identity':
                $array = ['headcode', 'mordok', 'jarvis', 'watson'];
                break;
            default:
                $array = [
                    'link',
                    'roll d20',
                    'roll',
                    'iching',
                    'bible',
                    'wave',
                    'eightball',
                    'read',
                    'group',
                    'flag',
                    'tally',
                    'emoji',
                    'red',
                    'green',
                    'balance',
                    'age',
                    'mordok',
                    'pain',
                    'receipt',
                    'key',
                    'uuid',
                    'remember',
                    'reminder',
                    'watson',
                    'jarvis',
                    'whatis',
                    'privacy',
                    '?',
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
            //$text = $this->thing->thing->created_at;
            $text = $this->created_at;
        }
        $time = strtotime($text);

        $text = strtoupper(date('Y M d D H:i', $time));
        $this->timestamp = $text;
        return $this->timestamp;
    }

    /**
     *
     * @param unknown $text (optional)
     */
    public function read($text = null)
    {
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

        $this->readFrom();

        $this->readSubject();
        $this->thing->log('completed read.');
    }

    public function readFrom($text = null)
    {
        $from = $this->from;
        if ($text != null) {
            $from = $text;
        }

        if (!isset($this->thing->deny_agent)) {
            $this->thing->deny_agent = new Deny($this->thing, "deny");
        }

        if ($this->thing->deny_agent->isDeny() === true) {
            $this->do_not_respond = true;
            //return;
            throw new \Exception("Address not allowed.");
        }

        // Get uuid from incoming datagram.
        // Devstack

        // $uuid = some function of from
        $uuid = false;

        if (isset($uuid) and is_string($uuid)) {
            $thing = new Thing($uuid);
            if ($thing->thing != false) {
                //$this->thing = $thing->thing;
                $agent = new Agent($thing->thing);

                return;
            }
        }
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
        register_shutdown_function([$this, "shutdownHandler"]);

        //if ($agent_class_name == 'Test') {return false;}
        set_error_handler([$this, 'warning_handler'], E_WARNING | E_NOTICE);

        //set_error_handler("warning_handler", E_WARNING);

        try {
            $agent_namespace_name =
                '\\Nrwtaylor\\StackAgentThing\\' . $agent_class_name;
            $this->thing->log(
                'trying Agent "' . $agent_class_name . '".',
                "INFORMATION"
            );

            // In test 25 May 2020

            if (!isset($thing->subject)) {
                $thing->subject = $this->input;
                //               }
            }

            $agent = new $agent_namespace_name($thing, $agent_input);
            restore_error_handler();

            // If the agent returns true it states it's response is not to be used.
            if (isset($agent->response) and $agent->response === true) {
                throw new Exception("Flagged true.");
            }

            //if ($agent->thing_report == false) {return false;}

            $this->thing_report = $agent->thing_report;
            $this->agent = $agent;

            //        } catch (Throwable $ex) { // Error is the base class for all internal PHP error exceptions.
        } catch (\Throwable $t) {
            restore_error_handler();

            $this->thing->log('caught throwable.', "WARNING");
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

            $input = $message . '  ' . $file . ' line:' . $line;
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
        set_error_handler([$this, 'warning_handler'], E_WARNING);
        //set_error_handler("warning_handler", E_WARNING);
        $this->thing->log(
            'looking for keyword matches with available agents.',
            "INFORMATION"
        );
        $agents_tested = [];
        foreach (['', 's', 'es'] as $postfix_variant) {
            foreach ($arr as $keyword) {
                // Don't allow agent to be recognized
                if (strtolower($keyword) == 'agent') {
                    continue;
                }

                $agent_class_name = ucfirst(strtolower($keyword));

                $agent_class_name = substr_replace(
                    $agent_class_name,
                    '',
                    -1,
                    strlen($postfix_variant)
                );
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
                    $agents[] = $agent_package;
                }

                // 2nd way
                $agent_class_name = strtolower($keyword);

                // Can probably do this quickly by loading path list into a variable
                // and looping, or a direct namespace check.
                $filename = $this->agents_path . $agent_class_name . ".php";
                if (file_exists($filename)) {
                    $agent_package = [$agent_class_name => null];
                    //                    $agents[] = $agent_class_name;
                    $agents[] = $agent_package;
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
                    $agents[] = $agent_package;
                }
            }
        }
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
            //$agent_class_name = '\Nrwtaylor\Stackr\' . $agent_class_name;
            // Allow for doing something smarter here with
            // word position and Bayes.  Agent scoring
            // But for now call the first agent found and
            // see where that consistency takes this.

            $agent_class_name = key($agent_package);

            $agent_input = null;
            if (isset($agent_package[$agent_class_name]['agent_input'])) {
                $agent_input = $agent_package[$agent_class_name]['agent_input'];
            }

            // Ignore Things for now 19 May 2018 NRWTaylor
            if ($agent_class_name == "Thing") {
                continue;
            }

            // And Email ... because email\uuid\roll otherwise goes to email
            if (count($agents) > 1 and $agent_class_name == "Email") {
                continue;
            }
            if ($this->getAgent($agent_class_name, $agent_input)) {
                $score = 1;
                $responsive_agents[] = [
                    "agent_name" => $agent_class_name,
                    "thing_report" => $this->thing_report,
                    "score" => $score,
                ];
                //            if ($this->getAgent($agent_class_name, $input)) {
                //return $this->thing_report;
            }
        }

        // For now just take the first match.
        // This allows for sophication in resolving multi agent responses.

        $this->responsive_agents = $responsive_agents;
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

        // MERP

        $uuid = new Uuid($this->thing, "extract");
        $uuid->extractUuids($agent_class_name);
        if ($agent_class_name == $uuid->uuid) {
            return false;
        }

        try {
            $agent_namespace_name =
                '\\Nrwtaylor\\StackAgentThing\\' . $agent_class_name;

            $this->thing->log(
                'trying Agent "' . $agent_class_name . '".',
                "INFORMATION"
            );

            // Added agent_class_name to avoid double call.
            // 24 May 2020
            $agent = new $agent_namespace_name($this->thing, $agent_class_name);

            return true;

            // If the agent returns true it states it's response is not to be used.
            if (isset($agent->response) and $agent->response === true) {
                throw new Exception("Flagged true.");
            }

            $this->thing_report = $agent->thing_report;

            $this->agent = $agent;
            return true;
        } catch (\Throwable $t) {
            $this->thing->log('caught throwable.', "WARNING");
        } catch (\Error $ex) {
            // Error is the base class for all internal PHP error exceptions.
            $this->thing->log(
                'caught error. Could not load "' . $agent_class_name . '".',
                "WARNING"
            );
            $message = $ex->getMessage();
            // $code = $ex->getCode();
            $file = $ex->getFile();
            $line = $ex->getLine();

            //            $input = $message . '  ' . $file . ' line:' . $line;
            $this->thing->log($input, "WARNING");

            // This is an error in the Place, so Bork and move onto the next context.
            // $bork_agent = new Bork($this->thing, $input);
            //continue;
            return false;
        }
    }

    public function ngramsText($text = null)
    {
        // See if there is an agent with the first workd
        $arr = explode(' ', trim($text));
        $agents = [];

        $bigrams = $this->getNgrams($text, 2);
        $trigrams = $this->getNgrams($text, 3);

        $arr = array_merge($arr, $bigrams);
        $arr = array_merge($arr, $trigrams);
        return $arr;
    }

    public function extractAgents($input)
    {
        $agent_input_text = $this->agent_input;
        if (is_array($this->agent_input)) {
            $agent_input_text = "";
        }

        $arr = $this->ngramsText($input);

        // Added this March 6, 2018.  Testing.

        if ($this->agent_input == null) {
            $arr[] = $this->to;
        } else {
            $arr = $this->ngramsText($agent_input_text);
            //$arr = explode(' ', $agent_input_text);
        }

        // Does this agent have code.
        $this->validateAgents($arr);

        $uuid_agent = new Uuid($this->thing, "uuid");
        //$t = $uuid_agent->stripUuids($input);
        // TODO: Build a seperate function.
        // Is there a translation for this command.
        $librex_agent = new Librex($this->thing, "agent/agent");

        $text = trim(str_replace("agent", "", $input));
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
                    if (str_replace("-", "", $agent_hit) == $agent_candidate) {
                        //echo $agent_hit . " " . $agent_input_hit . "\n";

                        $agent_package = [
                            $agent_input_hit => ['agent_input' => $agent_hit],
                        ];
                        array_unshift($this->agents, $agent_package);
                    }
                }
            }
        }

        // Does this agent provide a text response.
        $this->responsiveAgents($this->agents);
        foreach ($this->responsive_agents as $i => $responsive_agent) {
             //echo $responsive_agent['agent_name']. " " ;
        }

        return;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->thing->log('read subject "' . $this->subject . '".');

        $status = false;
        $this->response = false;
        // Because we need to be able to respond to calls
        // to specific Identities.

        $agent_input_text = $this->agent_input;

        if (is_array($this->agent_input)) {
            $agent_input_text = "";
        }
        /*
        $input = strtolower(
            $agent_input_text . " " . $this->to . " " . $this->subject
        );
        if ($this->agent_input == null) {
            $input = strtolower($this->to . " " . $this->subject);
        } else {
            $input = strtolower($agent_input_text);
        }
*/
        $input = $agent_input_text . " " . $this->to . " " . $this->subject;

        if ($this->agent_input == null) {
            $input = $this->to . " " . $this->subject;
        } else {
            $input = $agent_input_text;
        }

        //$input = strtolower($this->input);

        // Recognize and ignore stack commands.
        // Devstack
        if (substr($this->subject, 0, 2) == 's/') {
            if (
                substr($this->subject, 0, 5) == 's/ is' and
                substr($this->subject, -6) == 'button'
            ) {
                $t = str_replace('s/ is', '', $this->subject);
                $t = str_replace('button', '', $t);
                $t = trim($t);
                $button_agent = $t;
            }

            $agent_tokens = explode(' ', $this->agent_input);
            // Expect at least  tokens.
            // Get the last alpha tokens.

            $selected_agent_tokens = [];
            foreach (array_reverse($agent_tokens) as $i => $agent_token) {
                //if (is_string($agent_token)) {

                if (ctype_alpha(str_replace(' ', '', $agent_token)) === false) {
                    break;
                }
                $selected_agent_tokens[] = $agent_token;
            }

            $token_agent = implode(' ', array_reverse($selected_agent_tokens));
            $agglutinated_token_agent = implode(
                '',
                array_reverse($selected_agent_tokens)
            );
            $hyphenated_token_agent = implode(
                '-',
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

                if ($flag === false) {
                    return false;
                }
            }

            //if ($button_agent == $token_agent) {
            //    $this->response .=
            //        "Clicked the " . strtoupper($button_agent) . " button.";
            //}
        }

        // Dev test for robots
        $this->thing->log('created a Robot agent.', "INFORMATION");
        $this->robot_agent = new Robot($this->thing, 'robot');

        if ($this->robot_agent->isRobot()) {
            $this->response .= 'We think you are a robot.';
            $this->thing_report = $this->robot_agent->thing_report;
            return;
        }

        $dispatcher_agent = new Dispatcher($this->thing, 'dispatcher');

        // See if the string has a pointer to a channel nuuid.

        $nuuid = new Nuuid($this->thing, "nuuid");
        $n = $nuuid->extractNuuid($input);

        // See if this matches a stripe token
        if ($n != false) {
            $temp_email = $this->thing->db->from;
            $this->thing->db->from = "stripe" . $this->mail_postfix;

            $t = $this->thing->db->nuuidSearch($n);
            $t = $t['things'];

            if (count($t) >= 1) {
                // At least one valid four character token found.
                // This is close enought to authorize stack service.

                // Loop through the returned tokens and see which are stripe success tokens.
                foreach ($t as $t_uuid => $t_thing) {
                    if ($t_thing['task'] == "stripe-success") {
                        $success_agent = new Success(
                            $this->thing,
                            "channel token recognized"
                        );
                        $this->thing_report = $success_agent->thing_report;
                        return;
                    }
                }
            }
            // Reset the database email address
            $this->thing->db->from = $temp_email;
        }

        if (isset($nuuid->nuuid_uuid) and is_string($nuuid->nuuid_uuid)) {
            $thing = new Thing($nuuid->nuuid_uuid);
            $f = trim(str_replace($nuuid->nuuid_uuid, "", $input));
            $agent = new Agent($thing, $f);
            $this->thing_report = $agent->thing_report;
            return;
        }

        $uuid = new Uuid($this->thing, "uuid");
        $uuid = $uuid->extractUuid($input);

        if (isset($uuid) and is_string($uuid)) {
            $thing = new Thing($uuid);

            if ($thing->thing != false) {
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

                if ($f == "" or $f == 'agent') {
                    $agent = new Uuid($thing, $f);
                    $this->thing_report = $agent->thing_report;
                    return;
                }
                $agent = new Agent($thing, $f);
                $this->thing_report = $agent->thing_report;
                return;
            }
        }

        // Handle call intended for humans.
        //        $t = $this->assert($input);
        $human_agent = new Human($this->thing, 'human');

        //$web_agent = new Web($this->thing,'web');

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

        $text = urldecode($agent_input_text);

        //$text = urldecode($input);

        //$text = urldecode($input);

        $text = strtolower($text);

        //$arr = explode(' ', trim($text));

        $arr = explode('\%20', trim(strtolower($text)));

        $agents = [];
        $onegrams = $this->getNgrams($text, $n = 1);
        $bigrams = $this->getNgrams($text, $n = 2);
        $trigrams = $this->getNgrams($text, $n = 3);

        $arr = array_merge($arr, $onegrams);
        $arr = array_merge($arr, $bigrams);
        $arr = array_merge($arr, $trigrams);

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
            if ($this->isAgent($ngram)) {
                $matches[] = $ngram;
                //                return $this->thing_report;
            }
        }

        if (count($matches) == 1) {
            $this->getAgent($matches[0]);
            return $this->thing_report;
        }

        // First things first.  Special instructions to ignore.
        if (strpos($input, 'cronhandler run') !== false) {
            $this->thing->log('Agent "Agent" ignored "cronhandler run".');
            $this->thing->flagGreen();
            //$thing_report['thing'] = $this->thing;
            $this->thing_report['thing'] = $this->thing->thing;
            $this->thing_report['info'] =
                'Mordok ignored a "cronhandler run" request.';
            return $this->thing_report;
        }

        // Second.  Ignore web view flags for now.
        if (strpos($input, 'web view') !== false) {
            $this->thing->log('Agent "Agent" ignored "web view".');
            $this->thing->flagGreen();
            $this->thing_report['thing'] = $this->thing->thing;
            $this->thing_report['info'] =
                'Mordok ignored a "web view" request.';
            return $this->thing_report;
        }

        // Third.  Forget.
        if (strpos($input, 'forget') !== false) {
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
                    $this->thing_report['sms'] =
                        $forget_agent->thing_report['sms'];

                    //                $this->thing_report['sms'] =
                    //                    "AGENT | Saw a FORGET instruction.";
                    return $this->thing_report;
                }
            }

            if (strpos($input, 'all') !== false) {
                // pass through
            } else {
                $this->thing->log('did not ignore a forget".');
                //$this->thing->flagGreen();
                $this->thing->Forget();
                $this->thing_report = false;
                $this->thing_report['info'] =
                    'Agent did not ignore a "forget" request.';
                $this->thing_report['sms'] =
                    "FORGET | That Thing has been forgotten.";
                return $this->thing_report;
            }
        }

        $check_beetlejuice = "off";
        if ($check_beetlejuice == "on") {
            $this->thing->log(
                'created a Beetlejuice agent looking for incoming message repeats.'
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
            $this->burst = new Burst($this->thing, 'burst');

            $this->thing->log(
                'Agent "Agent" created a Similar agent looking for incoming message repeats.',
                "DEBUG"
            );

            $this->similar = new Similar($this->thing, 'similar');
            $similarness = $this->similar->similarness;
            $bursts = $this->burst->burst;

            $burstiness = $this->burst->burstiness;
            $similarities = $this->similar->similarity;

            $elapsed = $this->thing->elapsed_runtime();

            $burst_age_limit = 900; //s
            $similiarness_limit = 90;

            $burst_age =
                strtotime($this->current_time) -
                strtotime($this->burst->burst_time);
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
                    ' and similarness ' .
                    $similarness .
                    '.'
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
        $chinese_agent = new Chinese($this->thing, 'chinese');
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
        if (strtolower($input) == 'agent') {
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

            $this->thing_report['sms'] =
                "AGENT | " . $agent_text . $this->response;
            return $this->thing_report;
        }

        $this->thing->log('looking for optin/optout');
        //    $usermanager_thing = new Usermanager($this->thing,'usermanager');

        if (strpos($input, 'optin') !== false) {
            $this->thing->log('created a Usermanager agent.');
            $usermanager_thing = new Usermanager($this->thing);
            $this->thing_report = $usermanager_thing->thing_report;
            return $this->thing_report;
        }

        if (strpos($input, 'optout') !== false) {
            $this->thing->log('created a Usermanager agent.');
            $usermanager_thing = new Optout($this->thing);
            $this->thing_report = $usermanager_thing->thing_report;
            return $this->thing_report;
        }

        if (strpos($input, 'opt-in') !== false) {
            $this->thing->log('Agent created a Usermanager agent.');
            $usermanager_thing = new Optin($this->thing);
            $this->thing_report = $usermanager_thing->thing_report;
            return $this->thing_report;
        }

        if (strpos($input, 'opt-out') !== false) {
            $this->thing->log('Agent created a Usermanager agent.');
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
        $this->thing->log('looking for UUID in address.', 'INFORMATION');

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
        //        $chatbot = new Chatbot($this->thing,"chatbot");
        //        $input =  $chatbot->filtered_input;

        // Remove reference to thing.
        //$input = str_replace("thing","",$input);

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
        }

        // Temporarily alias robots
        if (strpos($input, 'robots') !== false) {
            $this->thing->log(
                '<pre> Agent created a Robot agent</pre>',
                "INFORMATION"
            );
            if (!isset($this->robot_agent)) {
                $this->robot_agent = new Robot($this->thing);
            }
            $this->thing_report = $this->robot_agent->thing_report;
            return $this->thing_report;
        }

        $this->thing->log(
            'now looking at Words (and Places and Characters).  Timestamp ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.',
            "OPTIMIZE"
        );
        $arr = $this->extractAgents($input);
        $this->input = $input;

        if (count($this->responsive_agents) > 0) {
            $this->thing_report = $this->responsive_agents[0]['thing_report'];
            return $this->thing_report;
        }

        $this->thing->log('did not find an Ngram agent to run.', "INFORMATION");

        $this->thing->log('now looking at Group Context.');

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

        $this->thing->log('now looking at Transit Context.');

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

        $this->thing->log('now looking at Place Context.');
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

        $this->thing->log('now looking at Group Context.');

        if ($this->stack_engine_state == 'dev') {
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

        if (stripos($input, 'frequency') !== false) {
            $frequency_exception_flag = false;
        }

        if (stripos($input, 'freq') !== false) {
            $frequency_exception_flag = false;
        }

        if (stripos($input, 'hz') !== false) {
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
                stripos($input, 'frequency')
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
            'now looking at Nest Context.  Timestamp ' .
                number_format($this->thing->elapsed_runtime()) .
                'ms.'
        );

        if (strtolower($this->from) != "null@stackr.ca") {
            $entity_list = ["Crow", "Wumpus", "Ant"];
            //$agent_name = "entity";
            foreach ($entity_list as $key => $entity_name) {
                $findagent_agent = new Findagent($this->thing, $entity_name);

                $things = $findagent_agent->thing_report['things'];

                if (!isset($things[0])) {
                    break;
                }
                $uuid = $things[0]['uuid'];

                $thing = new Thing($uuid);

                if ($thing == false) {
                    continue;
                }
                if (!isset($thing->account)) {
                    continue;
                }
                if (!isset($thing->account['stack'])) {
                    continue;
                }

                $variables = $thing->account['stack']->json->array_data;

                // Check
                if (!isset($variables[strtolower($entity_name)])) {
                    continue;
                }

                if (
                    !isset($variables[strtolower($entity_name)]['refreshed_at'])
                ) {
                    continue;
                }

                $last_heard[strtolower($entity_name)] = strtotime(
                    $variables[strtolower($entity_name)]['refreshed_at']
                );

                if (!isset($last_heard['entity'])) {
                    $last_heard['entity'] =
                        $last_heard[strtolower($entity_name)];
                    $agent_name = $entity_name;
                }

                if (
                    $last_heard['entity'] <
                    $last_heard[strtolower($entity_name)]
                ) {
                    $last_heard['entity'] =
                        $last_heard[strtolower($entity_name)];
                    $agent_name = $entity_name;
                }
            }

            if (!isset($agent_name)) {
                $agent_name = "Ant";
            }

            $agent_namespace_name =
                '\\Nrwtaylor\\StackAgentThing\\' . $agent_name;

            if (strpos($input, 'nest maintenance') !== false) {
                $ant_thing = new $agent_namespace_name($this->thing);
                $this->thing_report = $ant_thing->thing_report;
                return $this->thing_report;
            }

            if (strpos($input, 'patrolling') !== false) {
                $ant_thing = new $agent_namespace_name($this->thing);
                $this->thing_report = $ant_thing->thing_report;
                return $this->thing_report;
            }

            if (strpos($input, 'foraging') !== false) {
                $ant_thing = new $agent_namespace_name($this->thing);
                $this->thing_report = $ant_thing->thing_report;
                return $this->thing_report;
            }
        }

        $pattern = '/\?/';

        if (preg_match($pattern, $input)) {
            // returns true with ? mark
            $this->thing->log(
                'found a question mark and created a Question agent',
                "INFORMATION"
            );
            $question_thing = new Question($this->thing);
            $this->thing_report = $question_thing->thing_report;
            return $this->thing_report;
        }

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
            'ran Context ' .
                number_format($this->thing->elapsed_runtime() - $split_time) .
                'ms.'
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

        $this->thing->log('now looking for Resource.');
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
            case 'group':
                // Now if it is a head_code, it might also be a train...
                if ($this->stack_engine_state == 'dev') {
                    $group_thing = new Group($this->thing, 'group');
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

            case 'headcode':
                // Now if it is a head_code, it might also be a train...
                //$train_thing = new Train($this->thing, $this->head_code);
                $headcode_thing = new Headcode($this->thing, 'extract');
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
            case 'train':
                // Now if it is a head_code, it might also be a train...
                $train_thing = new Train($this->thing, 'extract');
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

            case 'character':
                // Character recognition should be replaceable by alias
                // by refactoring character to use the aliasing engine.
                $character_thing = new Character($this->thing, 'character');
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

            case 'place':
                // Character recognition should be replaceable by alias
                // by refactoring character to use the aliasing engine.
                $place_thing = new Place($this->thing, 'place');
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
        $alias_thing = new Alias($this->thing, 'extract');

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

        $this->thing->log('now looking at Identity Context.', "OPTIMIZE");

        if (
            isset($chinese_thing->chineses) and
            $chinese_thing->chineses != []
        ) {
            $c = new Chinese($this->thing, "chinese");
            $this->thing_report = $c->thing_report;
            //            $this->thing_report['sms'] = "AGENT | " . "Heard " . $input .".";
            return $this->thing_report;
            //exit();
        }

        // Most useful thing is to acknowledge the url.
        if (count($urls) > 0) {
            $this->thing_report = $url->thing_report;
            return $this->thing_report;
        }

        return $this->thing_report;

        if (isset($chinese_thing->chineses) or isset($emoji_thing->emojis)) {
            $this->thing_report['sms'] = "AGENT | " . "Heard " . $input . ".";
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
    function filterAgent($text = null)
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

        if ($this->stack_engine_state != 'prod') {
            echo $console . "\n";
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
        $this->thing_report['sms'] = "Test";
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
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

        switch ($lasterror['type'] ?? null) {
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
                    $lasterror['type'] .
                    " | msg:" .
                    $lasterror['message'] .
                    " | file:" .
                    $lasterror['file'] .
                    " | ln:" .
                    $lasterror['line'];
                $this->mylog($error, "fatal");
        }
    }

    function mylog($error, $errlvl)
    {
        //        echo $this->response;
        //        echo "\n";
        //        echo $this->thing->log;
        //...do whatever you want...
        //echo $this->uuid;
    }
}
