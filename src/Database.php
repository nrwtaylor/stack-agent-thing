<?php
/**
 * Database.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use \PDO;
use Nrwtaylor\StackAgentThing\Mysql;
use Nrwtaylor\StackAgentThing\Mongo;
use Nrwtaylor\StackAgentThing\Agent;
use Nrwtaylor\StackAgentThing\Thing;

class Database
{
    public $var = "hello";

    /**
     *
     * @param unknown $uuid
     * @param unknown $nom_from
     * @return unknown
     */
    //public function init()
    function __construct($thing = null, $agent_input = null)
    {
        $uuid = $agent_input["uuid"];
        $nom_from = $agent_input["from"];
        $to = isset($agent_input["to"]) ? $agent_input["to"] : null;
        $subject = isset($agent_input["subject"])
            ? $agent_input["subject"]
            : null;

        $start_time = microtime(true);
        $this->start_time = $start_time;
        $this->split_time = $start_time;
        $this->operations_time = 0;
        $this->operations = 0;
        $this->log = [];

        //        $this->hash_algorithm = 'sha256';
        //        $this->hash_state = 'on';

        // Database controls access by $uuid.

        // Should know $nom_from of requester.

        // Thing Database services.
        // "Without a name AND a uuid I'm not doing anything."
        // Which I started at 10.10am.

        // Basic Database should only be able to query multiple records
        // by $nom_from, or by the records which contain it's UUid.

        // So if $uuid is blank, that's okay. > Return matching
        // nom_from records.   And if $nom_from is blank
        // that's okay > Return matching uuid records.

        // The problem is when they are both null.
        // Code here should allow either.

        if ($nom_from == null and $uuid == null) {
            throw new \Exception('No
			$nom_from and $uuid provided to Class Db.');
        }

        if ($nom_from == null) {
            throw new \Exception('No $nom_from provided to
			Class Db.');
        }
        if ($uuid == null) {
            throw new \Exception('No $uuid provided to
			Class Db.');
        }
        // Determine which filestore to use.

        $this->agent_input = $agent_input;

        $service = null;
        $handler = null;

        $this->from = $nom_from;
        $this->uuid = $uuid;
        $this->subject = $subject;
        $this->to = $to;

        $settings = require $GLOBALS["stack_path"] . "private/settings.php";

        // Get let of stacks
        $this->stacks = [
            "mysql" => ["infrastructure" => "mysql"],
            "mongo" => ["infrastructure" => "mongo"],
            "memcached" => ["infrastructure" => "memcached"],
            "memory" => ["infrastructure" => "memory"],
        ];
        if (isset($settings["settings"]["stacks"])) {
            $this->stacks = $settings["settings"]["stacks"];
        }

        $this->web_prefix = $settings["settings"]["stack"]["web_prefix"];
        $this->state = $settings["settings"]["stack"]["state"];

        $this->hash_state = "off";
        if (isset($settings["settings"]["stack"]["hash"])) {
            $this->hash_state = $settings["settings"]["stack"]["hash"];
        }

        $this->hash_algorithm = "sha256";
        if (isset($settings["settings"]["stack"]["hash"])) {
            $this->hash_algorithm =
                $settings["settings"]["stack"]["hash_algorithm"];
        }

        $this->get_prior = true;
        if (isset($settings["settings"]["stack"]["get_prior"])) {
            $this->get_prior = $settings["settings"]["stack"]["get_prior"];
        }

        $this->available_stacks = [];
        $this->stack_handlers = [];

        $this->candidate_stacks = $this->stacks;

        foreach (
            $this->candidate_stacks
            as $candidate_service_name => $candidate_service
        ) {
            try {
                $handler = $this->connectDatabase($candidate_service);
            } catch (\Throwable $t) {
            } catch (\Error $ex) {
            }

            if ($handler !== true) {
                $this->available_stacks[
                    $candidate_service_name
                ] = $candidate_service;
                $this->stack_handlers[$candidate_service_name] = $handler;
            }
        }
        $this->active_stacks = $this->available_stacks;

        $this->stack = false;
        $this->stack_handler = false;
        if (count($this->available_stacks) > 0) {
            $primary_stack = reset($this->available_stacks);
            $primary_stack_name = key($this->available_stacks);

            $this->stack = $this->available_stacks[$primary_stack_name];
            $this->stack_handler = $this->stack_handlers[$primary_stack_name];
        }

        $this->container = new \Slim\Container($settings);

        // create app instance
        $app = new \Slim\App($this->container);
        $c = $app->getContainer();

        // Haven't seen this triggered.
        $c["errorHandler"] = function ($c) {
            return function ($request, $response, $exception) use ($c) {
                return $c["response"]
                    ->withStatus(500)
                    ->withHeader("Content-Type", "text/html")
                    ->write("AGENT | Maintenance.");
            };
        };

        $c["stack"] = function ($c) {
            $db = $c["settings"]["stack"];
            return $db;
        };

        $this->get_calling_function();

        $this->test("<b>" . $this->get_calling_function() . "</b>");

        // NRW Taylor 12 June 2018
        // devstack Database for to disk persistent memory calls, redis for in ram persistent calls

        $this->char_max = $c["stack"]["char_max"];

        $this->uuid = $uuid;

        $this->test("Database set-up ");

        // Which means at this point, we have a UUID
        // whether or not the record exists is another question.

        // But we don't need to find, it because the UUID is randomly created.
        // Chance of collision super-super-small.

        // So just return the contents of thing.  false if it doesn't exist.

        $this->split_time = microtime(true);

        // No memory available.
        if ($thing === false) {
            echo "false thing";
            return;
        }

        $c["db"] = function ($c) {
            $db = $c["settings"]["db"];

            //try {

            $pdo = new PDO(
                "mysql:host=" . $db["host"] . ";dbname=" . $db["dbname"],
                $db["user"],
                $db["pass"]
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        };
    }

    /**
     *
     */
    function __destruct()
    {
        // Log database transactions in test
        $this->test("Database destruct ");
    }

    // Test whether the persistence service is available.
    // If it is provide a handler.
    // Otherwise return true.

    function connectDatabase($stack)
    {
        $agent_name = $stack["infrastructure"];
        $agent_class_name = ucwords($agent_name);
        $agent_namespace_name =
            "\\Nrwtaylor\\StackAgentThing\\" . $agent_class_name;

        try {
            //$handler = new $agent_namespace_name($this->thing, $this->agent_input);
            $handler = new $agent_namespace_name(null, $this->agent_input);

            $handler->uuid = $this->uuid;
            $handler->from = $this->from;
            $handler->to = $this->to;
            $handler->subject = $this->subject;
            $handler->hash_state = $this->hash_state;
            $handler->hash_algorithm = $this->hash_algorithm;
            $handler->get_prior = $this->get_prior;

            $handler->init();

            if (isset($stack["host"])) {
                $handler->host = $stack["host"];
            }

            if (isset($stack["pass"])) {
                $handler->pass = $stack["pass"];
            }
            if (isset($stack["user"])) {
                $handler->user = $stack["user"];
            }
            return $handler;
        } catch (\Throwable $t) {
        } catch (\Error $ex) {
        }

        return true;
    }

    /**
     *
     * @param unknown $comment     (optional)
     * @param unknown $instruction (optional)
     */
    function test($comment = null, $instruction = null)
    {
        if (isset($this->state) and $this->state != "test") {
            return;
        }
        if ($comment == null and $instruction == null) {
            $comment = "";
        }

        // devstack save to variable
        echo "<pre>";
        echo substr($this->uuid, 0, 4) . "-" . $this->caller . " ";
        echo str_pad(
            number_format((microtime(true) - $this->start_time) * 1000) . "ms",
            0,
            " ",
            STR_PAD_RIGHT
        );

        echo "[" . number_format($this->operations_time * 1e3) . "ms]";
        echo " " . $comment . " ";
        echo "split time " .
            number_format((microtime(true) - $this->split_time) * 1000) .
            "ms ";
        echo "operations " . $this->operations . " ";
        echo "<br>";

        foreach ($this->log as $key => $value) {
            echo str_pad("", 4, " ") . $value . "<br>";
        }
        $this->log = [];
        echo "</pre>";
    }

    /**
     *
     * @return unknown
     */
    function get_calling_function()
    {
        // see stackoverflow.com/questions/190421
        $caller = debug_backtrace();

        $caller = $caller[2];
        $r = $caller["function"] . "()";

        if (isset($caller["class"])) {
            $r .= " called by ";
            // $r .= $caller['class'];
            $t = explode("\\", $caller["class"]);
            $r .= "" . $t[count($t) - 1];
        }

        $this->caller = $t[count($t) - 1];

        $r .= "\\";
        $r .= debug_backtrace()[1]["function"];

        if (isset($caller["object"])) {
            //$r .= ' (' . get_class($caller['object']) . ')';
        }
        return $r;
    }

    /**
     *
     * @param unknown $created_at (optional)
     * @return unknown
     */
    function priorGet($created_at = null)
    {
        $thingreport = [
            "thing" => false,
            "info" => "Prior get is off for this stack.",
            "help" => "Now help available.",
        ];

        foreach (
            $this->active_stacks
            as $active_service_name => $active_service
        ) {
            if ($active_service_name == "mysql") {
                $thingreport = $this->stack_handlers["mysql"]->priorMysql(
                    $created_at
                );
                break;
            }
        }
        return $thingreport;
    }

    /**
     *
     * @param unknown $field_text
     * @param unknown $string_text
     */
    public function writeField($field_text, $string_text, $uuid = null)
    {
        foreach (
            $this->active_stacks
            as $active_service_name => $active_service
        ) {
            if ($active_service_name == "mysql") {
                $this->stack_handlers["mysql"]->writeField(
                    $field_text,
                    $string_text
                );
            }
            if ($active_service_name == "memcached") {
                $key = $this->stack_handlers["memcached"]->writeField(
                    $field_text,
                    $string_text
                );
                //if ($key === true) {return true;}
            }

            
        if ($active_service_name == "mongo") {
            $key = $this->stack_handlers["mongo"]->writeMongo($field_text, $string_text);
            //if ($key === true) {return true;}
        }
/*
        if ($active_service == "memory") {
            $memory = $this->stack_handlers["memory"]->set($key, $value);

            if ($memory === false) {
                return true;
            } //error
            if ($memory === true) {
                return $value;
            } // success
            return true; // error
        }
*/
        }
    }

    /**
     *
     * @return unknown
     */
    function count()
    {
        if ($this->stack_handler == null) {
            return true;
        }
        $thingreport = true;
        if ($this->stack["infrastructure"] == "mysql") {
            $thingreport = $this->stack_handler->countMysql();
        }
        return $thingreport;
    }

    /**
     *
     * @param unknown $field
     * @return unknown
     */
    function readField($field)
    {
        $thingreport = $this->Get();

        $this->thing = $thingreport["thing"];

        if (isset($this->thing->$field)) {
            // I think I should also do
            $this->$field = $this->thing->$field;
            return $this->thing->$field;
        } else {
            return false;
        }
    }

    /**
     *
     * @param unknown $subject
     * @param unknown $to
     * @return unknown
     */
    function Create($subject, $to)
    {
        foreach ($this->available_stacks as $stack_name => $stack_descriptor) {
            $stack_infrastructure = $stack_descriptor["infrastructure"];
            if ($stack_infrastructure == "mysql") {
                $response = $this->stack_handlers['mysql']->createMysql($subject, $to);
                $this->available_stacks["mysql"]["response"] = $response;
            }

            if ($stack_infrastructure == "memory") {
                $response = $this->stack_handlers['memory']->createMemory($subject, $to);
                $this->available_stacks["memory"]["response"] = $response;
            }

            if ($stack_infrastructure == "mongo") {
                $response = $this->stack_handlers['mongo']->createMongo($subject, $to);
                $this->available_stacks["memory"]["response"] = $response;
            }


        }

        foreach ($this->available_stacks as $stack_name => $stack_descriptor) {
            if (
                isset($stack_descriptor['response']) and
                $stack_descriptor["response"] === true
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @return unknown
     */
    function Get()
    {
        // But we don't need to find, it because the UUID is randomly created.
        // Chance of collision super-super-small.

        // So just return the contents of thing.  false if it doesn't exist.

        // Get first available.

//        $thing = false;
        $thing = [];
        foreach ($this->available_stacks as $stack_name => $stack) {
            switch ($stack["infrastructure"]) {
                case "mysql":
                    $thing['mysql'] = $this->stack_handlers[
                        $stack["infrastructure"]
                    ]->getMysql();

                    //          if ($thing['mysql'] !== false and $thing['mysql'] !== true) {
                    //              break 2;
                    //          }

//if ($thing === false) {$thing = $thing['mysql'];}
                    break;
                case "memcached":
                    $thing['memcached'] = $this->stack_handlers[
                        $stack["infrastructure"]
                    ]->getMemcached($this->uuid);
                    break;

                case "mongo":
                    $thing['mongo'] = $this->stack_handlers[
                        $stack["infrastructure"]
                    ]->getMongo($this->uuid);
                    break;


                //                    if ($thing !== false and $thing !== true) {
                //                        break 2;
                //                   }

                /*
                case "mongo":
                    break;
                case "memory":
                    $thing = $this->stack_handlers[
                        $stack["infrastructure"]
                    ]->getMemory($this->uuid);

                    if ($thing !== false and $thing !== true) {
                        break 2;
                    }
*/
                /*
                    if ($thing !== false) {
                        //$thing = new Thing(null);
                        //$thing->created_at = null;
                        //$thing->nom_to = null;
                        //$thing->nom_from = null;
              c          //$thing->task = "empty task";
                        //$thing->variables = json_encode($t, true);
                        //$thing->settings = null;
                        break;
                    }
*/

                //break 2;
            }
        }

        // dev decide which thing is most authorative.
        // merge?
        $thing = $thing['mysql'];

/*
if (is_array($thing)) {

if (count($thing) == 0) {
$thing = false;
}

if (count($thing) >= 1) {
$thcing = $thing[array_key_first($thing)];
}


}
*/
        $thingreport = [
            "thing" => $thing,
            "info" =>
                "Turns out it has an imperfect and forgetful memory.  But you can see what is on the stack by typing " .
                $this->web_prefix .
                "api/thing/<32 characters>.",
            "help" => "Check your junk/spam folder.",
        ];

        return $thingreport;
    }

    /**
     *
     * @return unknown
     */
    function Forget()
    {
        $thing_reports = [];
        foreach (
            $this->active_stacks
            as $active_service_name => $active_service
        ) {
            if ($active_service_name == "mysql") {
                $thing_reports[
                    $active_service_name
                ] = $this->stack_handler->forgetMysql($this->uuid);
            }

            if ($active_service_name == "mongo") {
                $thing_reports[
                    $active_service_name
                ] = $this->stack_handler->forgetMongo($this->uuid);
            }

            if ($active_service_name == "memory") {
                $memory = $this->stack_handler->setMemory($this->uuid, null);
                $thing_reports[$active_service_name] = [
                    "info" => "That thing was forgotten.",
                    "error" => $memory,
                ];
            }
        }

        // Thing report fusion?
        // But for now get first element
        $thing_report = reset($thing_reports);

        return $thing_report;
    }

    /**
     *
     * @param unknown $id (optional)
     */
    function setUser($id = null)
    {
        if ($id == null) {
            $settings = require $GLOBALS["stack_path"] . "private/settings.php";

            $id = "null@" . $settings["settings"]["stack"]["mail_postfix"];
        }
        $this->from = $id;
        return;
    }

    /**
     *
     * @param unknown $nom_from
     */
    function setFrom($nom_from)
    {
        $this->from = $nom_from;
        return;
    }

    /**
     *
     * @param unknown $value
     * @param unknown $max   (optional)
     * @return unknown
     */
    function associationSearch($value, $max = null)
    {
        $thing_report = [
            "things" => [],
            "info" => "Did not find any things with that association.",
            "help" => "Finds associated things.",
        ];

        if ($this->stack["infrastructure"] == "mysql") {
            $thing_report = $this->stack_handlers[
                "mysql"
            ]->associationsearchMysql($value, $max);
        }

        return $thing_report;
    }

    function isValidMd5($md5 = '')
    {
        return strlen($md5) == 32 && ctype_xdigit($md5);
    }

    function isValidSha256($sha256 = '')
    {
        return strlen($sha256) == 64 && ctype_xdigit($sha256);
    }

    /**
     *
     * @param unknown $path
     * @param unknown $value
     * @param unknown $max   (optional)
     * @return unknown
     */
    public function variableSearch(
        $path,
        $value,
        $max = null,
        $string_in_string = false
    ) {
        //        $thing = false;
        /*
        $thing_report = [];
        $thing_report["info"] =
                'No things found.';
        $thing_report["things"] = [];

        foreach($this->available_stacks as $stack_name=>$stack) {

switch ($stack['infrastructure']) {
    case 'mysql':
            $thing_report = $this->stack_handler->variablesearchMysql($path, $value, $max);
        break 2;
    case 'mongo':
//        break 2;
    case 'memory':
//        break 2;
}


        }

return $thing_report;
*/

        if ($max == null) {
            $max = 3;
        }
        $max = (int) $max;

        $user_search = $this->from;
        $hash_user_search = hash($this->hash_algorithm, $user_search);

        $hash_user_search = $user_search;
        if (!$this->isValidSha256($user_search)) {
            $hash_user_search = hash($this->hash_algorithm, $user_search);
        }
        // https://stackoverflow.com/questions/11068230/using-like-in-bindparam-for-a-mysql-pdo-query
        if ($string_in_string === true) {
            $value = "%$value%"; // Value to search for in Variables
        }
        $thingreport["things"] = [];

        try {
            //            $query =
            //                "SELECT * FROM stack FORCE INDEX (created_at_nom_from) WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND variables LIKE :value ORDER BY created_at DESC LIMIT :max";

            //            $query =
            //              "SELECT * FROM stack WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND variables LIKE :value ORDER BY created_at DESC LIMIT :max";

            if ($this->hash_state == "off") {
                $query =
                    "SELECT * FROM stack WHERE nom_from=:user_search AND variables LIKE :value ORDER BY created_at DESC LIMIT :max";
            }

            if ($this->hash_state == "on") {
                $query =
                    "SELECT * FROM stack WHERE nom_from=:hash_user_search AND variables LIKE :value ORDER BY created_at DESC LIMIT :max";
            }
            //           $query =
            //                "(SELECT * FROM stack WHERE nom_from=:hash_user_search AND variables LIKE :value) UNION ALL (SELECT * FROM stack WHERE nom_from=:hash_user_search AND variables LIKE :value) ORDER BY created_at DESC LIMIT :max";
            //$value = "+$value"; // Value to search for in Variables

            //    $query =
            //        'SELECT * FROM stack WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND MATCH(variables) AGAINST (:value IN BOOLEAN MODE) ORDER BY created_at DESC LIMIT :max';

            $sth = $this->container->db->prepare($query);
            if ($this->hash_state == "off") {
                $sth->bindParam(":user_search", $user_search);
            }
            if ($this->hash_state == "on") {
                $sth->bindParam(":hash_user_search", $hash_user_search);
            }
            $sth->bindParam(":value", $value);
            $sth->bindParam(":max", $max, PDO::PARAM_INT);
            $sth->execute();

            $things = $sth->fetchAll();
            $thingreport["info"] =
                'So here are Things with the variable you provided in \$variables. That\'s what you want';
            $thingreport["things"] = $things;
        } catch (\PDOException $e) {
            //var_dump($e->getMessage());
            // echo "Error in PDO: ".$e->getMessage()."<br>";
            $thingreport["info"] = $e->getMessage();
            $thingreport["things"] = [];
        }

        // Destroy object
        // https://stackoverflow.com/questions/5772626/fatal-error-call-to-undefined-method-pdoclose
        $sth = null;

        return $thingreport;
    }

    function nuuidSearch($nuuid)
    {
        $user_search = $this->from;
        $hash_user_search = hash($this->hash_algorithm, $user_search);

        $nuuid = "$nuuid%"; // Value to search for in Variables

        $thingreport["things"] = [];

        try {
            $query =
                "SELECT * FROM stack WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND uuid LIKE :nuuid ORDER BY created_at DESC";

            $sth = $this->container->db->prepare($query);

            $sth->bindParam(":user_search", $user_search);
            $sth->bindParam(":hash_user_search", $hash_user_search);

            $sth->bindParam(":nuuid", $nuuid);
            $sth->execute();

            $things = $sth->fetchAll();

            $thingreport["info"] =
                "So here are Things with the nuuid you provided.";
            $thingreport["things"] = $things;
        } catch (\PDOException $e) {
            $thingreport["info"] = $e->getMessage();
            $thingreport["things"] = [];
        }

        $sth = null;

        return $thingreport;
    }

    /**
     *
     * @param unknown $agent
     * @param unknown $max   (optional)
     * @return unknown
     */
    function subjectSearch($keyword_input, $agent, $max, $mode = null)
    {
        $user_search = $this->from;
        $hash_user_search = hash($this->hash_algorithm, $this->from);

        //        $keyword = "%$keyword%"; // Value to search for in Variables
        //        $keyword = '"' . $keyword .'"'; // Value to search for in Variables
        //$keyword = "$keyword";

        //$keyword = $this->container->db->quote($keyword_input);

        if ($max == null) {
            $max = 3;
        }
        $max = (int) $max;

        if ($mode == null or strtolower($mode) == "boolean") {
            $keyword = $this->container->db->quote($keyword_input);
            $query =
                "SELECT * FROM stack WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND nom_to=:agent AND MATCH(task) AGAINST (:keyword IN BOOLEAN MODE) ORDER BY created_at DESC LIMIT :max";
        }

        if (strtolower($mode) == "like") {
            $keyword = "$keyword_input"; // Value to search for in Variables
            $query =
                "SELECT * FROM stack WHERE task LIKE BINARY :keyword AND nom_to=:agent AND (nom_from=:user_search OR nom_from=:hash_user_search) ORDER BY created_at DESC LIMIT :max";
        }
        if (strtolower($mode) == "where") {
            $keyword = $this->container->db->quote($keyword_input);
            $query =
                "SELECT * FROM stack WHERE task = BINARY :keyword AND nom_to=:agent AND (nom_from=:user_search OR nom_from=:hash_user_search) ORDER BY created_at DESC LIMIT :max";
        }

        if (strtolower($mode) == "natural language") {
            $keyword = $this->container->db->quote($keyword_input);
            $query =
                "SELECT * FROM stack WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND nom_to=:agent AND MATCH(task) AGAINST (:keyword IN NATURAL LANGUAGE MODE) ORDER BY created_at DESC LIMIT :max";
        }

        if (strtolower($mode) == "equal") {
            $keyword =
                "adidas adiPower S bounce men\'s spikeless Golf Shoe NEW";
            //      $keyword = $keyword_input; // Value to search for in Variables
            $keyword = $this->container->db->quote(
                "adidas adiPower S bounce men\'s spikeless Golf Shoe NEW"
            );

            $keyword = '$keyword_input';
            //           $keyword = $this->container->db->quote($keyword_input);
            //$text = "adidas adiPower S bounce men's spikeless Golf Shoe NEW";
            //  $query ='SELECT * FROM stack WHERE task=:keyword AND nom_to=:agent AND nom_from=:user_search ORDER BY created_at DESC LIMIT :max';

            $query = 'SELECT * FROM stack WHERE task=":keyword"';
        }

        $sth = $this->container->db->prepare($query);
        $sth->bindParam(":user_search", $user_search);
        $sth->bindParam(":hash_user_search", $hash_user_search);

        $sth->bindParam(":keyword", $keyword);
        $sth->bindParam(":agent", $agent);
        $sth->bindParam(":max", $max, PDO::PARAM_INT);

        try {
            $sth->execute();
        } catch (\PDOException $e) {
            //            $t = new Thing(null);
            //            $t->Create("stack", "error", 'subjectSearch ' .$e->getMessage());
            //            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        $things = $sth->fetchAll();

        $sth = null;

        $thingreport = [
            "things" => $things,
            "info" =>
                'So here are Things with the phrase you provided in \$variables. That\'s what you wanted.',
            "help" => "It is up to you what you do with these.",
            "whatisthis" =>
                "A list of Things which match at the provided phrase.",
        ];

        return $thingreport;
    }

    function fromcountDatabase($horizon = null)
    {
        $thing_report = [
            "thing" => false,
            "things" => [],
            "info" => "Did not count any things.",
            "help" => "Counts things.",
        ];

        foreach (
            $this->active_stacks
            as $active_service_name => $active_service
        ) {
            if ($active_service_name == "mysql") {
                $thingreport = $this->stack_handlers["mysql"]->fromcountMysql(
                    $horizon
                );
                break;
            }
        }

        return $thingreport;

        $query = "SELECT DISTINCT nom_from FROM stack";

        if ($horizon != null) {
            $horizon = (int) $horizon;
            $query =
                "SELECT DISTINCT nom_from FROM stack WHERE created_at > (NOW() - INTERVAL :horizon HOUR)";
        }

        $sth = $this->container->db->prepare($query);

        if ($horizon != null) {
            $sth->bindParam(":horizon", $horizon, PDO::PARAM_INT);
        }

        try {
            $sth->execute();
            $things = $sth->fetchAll();
        } catch (\PDOException $e) {
            $things = [];
            //            $t = new Thing(null);
            //            $t->Create("stack", "error", 'subjectSearch ' .$e->getMessage());

            //            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        $sth = null;

        $thingreport = [
            "things" => $things,
            "info" => "Count unique nom_from in the stack.",
            "help" => "It is up to you what you do with these.",
            "whatisthis" => "A count of the nom_from channels in the stack.",
        ];
        return $thingreport;
    }

    function agentCount($agent, $horizon = 48)
    {
        //SELECT COUNT(*) FROM stack WHERE nom_to="agent" AND created_at > (NOW() - INTERVAL 6 HOUR);

        if ($horizon == null) {
            $horizon = 48;
        }
        $horizon = (int) $horizon;

        $user_search = $this->from;
        $hash_user_search = hash($this->hash_algorithm, $user_search);

        $query =
            "SELECT COUNT(*) FROM stack WHERE nom_to=:agent AND (nom_from=:user_search OR nom_from=:hash_user_search) AND created_at > (NOW() - INTERVAL :horizon HOUR)";

        $sth = $this->container->db->prepare($query);
        $sth->bindParam(":user_search", $user_search);
        $sth->bindParam(":hash_user_search", $hash_user_search);

        $sth->bindParam(":agent", $agent);
        $sth->bindParam(":horizon", $horizon, PDO::PARAM_INT);
        //        $sth->execute();

        //        $things = $sth->fetchAll();

        try {
            $sth->execute();
            $things = $sth->fetchAll();
        } catch (\PDOException $e) {
            $things = [];
            //            $t = new Thing(null);
            //            $t->Create("stack", "error", 'subjectSearch ' .$e->getMessage());

            //            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

        $sth = null;

        $thingreport = [
            "things" => $things,
            "info" =>
                'So here are Things with the phrase you provided in \$variables. That\'s what you wanted.',
            "help" => "It is up to you what you do with these.",
            "whatisthis" =>
                "A list of Things which match at the provided phrase.",
        ];
        return $thingreport;
    }

    // devstack
    function agentForget($agent, $max = 0)
    {
        // DELETE FROM stack WHERE nom_to="tile" AND created_at < (NOW() - INTERVAL 6 HOUR);

        if ($max == null) {
            $max = 3;
        }
        $max = (int) $max;

        $user_search = $this->from;
        //$user_search= "%$user_search%"; // Value to search for in Variables

        $query =
            "SELECT * FROM stack WHERE nom_from LIKE :user_search AND nom_to = :agent ORDER BY created_at DESC LIMIT :max";
        //$query = 'DELETE FROM stack WHERE nom_to=:agent and task <= (SELECT task FROM (SELECT task FROM stack ORDER BY id DESC LIMIT 1 OFFSET 1) foo)';

        $sth = $this->container->db->prepare($query);
        //        $sth->bindParam(":user_search", $user_search);
        //        $sth->bindParam(":agent", $agent);
        //        $sth->bindParam(":max", $max, PDO::PARAM_INT);
        $sth->execute();

        $things = $sth->fetchAll();

        $sth = null;

        $thingreport = [
            "things" => $things,
            "info" =>
                'So here are Things with the phrase you provided in \$variables. That\'s what you wanted.',
            "help" => "It is up to you what you do with these.",
            "whatisthis" =>
                "A list of Things which match at the provided phrase.",
        ];
        return $thingreport;
    }

    // Keep only the newest agent task.
    function agentDeduplicate($agent)
    {
        $user_search = $this->from;
        $hash_user_search = hash($this->hash_algorithm, $user_search);
        //$user_search= "%$user_search%"; // Value to search for in Variables

        //$query = "SELECT * FROM stack WHERE nom_from LIKE :user_search AND nom_to = :agent ORDER BY created_at DESC LIMIT :max";
        // $query = "DELETE FROM stack WHERE nom_to=:agent AND nom_from = :user_search AND task NOT IN (SELECT task FROM (SELECT task FROM stack ORDER BY id DESC LIMIT :max) foo)";

        //$query = 'DELETE t1 FROM stack t1, stack t2 WHERE t1.id < t2.id AND t1.task = t2.task AND t1.nom_to = :agent AND nom_from = :user_search';
        //$query = 'delete from stack where nom_to=:agent and nom_from=:user_search and not exists (select * from (select MAX(id) maxID FROM stack GROUP BY task HAVING count(*) > 0 ) AS q WHERE maxID=id)';
        //$query = 'delete /*+ MAX_EXECUTION_TIME(100) */ from stack where nom_to="tile" and not exists (select * from (select MAX(id) maxID FROM stack GROUP BY task HAVING count(*) > 0 ) AS q WHERE maxID=id)';
        $query =
            "delete from stack where nom_to=:agent AND (nom_from=:user_search OR nom_from=:hash_user_search) and not exists (select * from (select MAX(id) maxID FROM stack GROUP BY task HAVING count(*) > 0 ) AS q WHERE maxID=id)";

        $sth = $this->container->db->prepare($query);
        $sth->bindParam(":user_search", $user_search);
        $sth->bindParam(":hash_user_search", $hash_user_search);

        $sth->bindParam(":agent", $agent);
        $sth->execute();

        //        $things = $sth->fetchAll();

        $sth = null;

        $thingreport = [
            "things" => null,
            "info" => "Asked to delete records by agent.",
            "help" => "It is up to you what you do with these.",
            "whatisthis" =>
                "A command to delete all but some of a specific agent records.",
        ];
        return $thingreport;
    }

    /**
     *
     * @param unknown $agent
     * @param unknown $max   (optional)
     * @return unknown
     */
    function agentSearch($agent, $max = null)
    {
        if ($max == null) {
            $max = 3;
        }
        $max = (int) $max;

        $user_search = $this->from;
        $hash_user_search = hash($this->hash_algorithm, $this->from);

        //$user_search= "%$user_search%"; // Value to search for in Variables

        //$query = "SELECT * FROM stack WHERE nom_from LIKE :user_search AND nom_to = :agent ORDER BY created_at DESC LIMIT :max";
        $query =
            "SELECT * FROM stack WHERE (nom_from = :user_search OR nom_from=:hash_user_search) AND nom_to = :agent ORDER BY created_at DESC LIMIT :max";

        try {
            $sth = $this->container->db->prepare($query);
            $sth->bindParam(":user_search", $user_search);
            $sth->bindParam(":hash_user_search", $hash_user_search);
            $sth->bindParam(":agent", $agent);
            $sth->bindParam(":max", $max, PDO::PARAM_INT);
            $sth->execute();
            $things = $sth->fetchAll();
        } catch (\Exception $e) {
            //            $t = new Thing(null);
            //            $t->Create("stack", "error", 'agentSearch ' . $e->getMessage());

            //            echo 'Caught error: ', $e->getMessage(), "\n";
            $things = false;
        }

        $sth = null;

        $thingreport = [
            "things" => $things,
            "info" =>
                'So here are Things with the phrase you provided in \$variables. That\'s what you wanted.',
            "help" => "It is up to you what you do with these.",
            "whatisthis" =>
                "A list of Things which match at the provided phrase.",
        ];
        return $thingreport;
    }

    /**
     * add bindparam
     *
     * @param unknown $keyword
     * @return unknown
     */
    function userSearch($keyword)
    {
        $user_search = $this->from;
        $hash_user_search = hash($this->hash_algorithm, $this->from);

        $keyword = "%$keyword%"; // Value to search for in Variables

        //  $query = "SELECT * FROM stack WHERE nom_from LIKE '%$user_search%' AND task LIKE '%$keyword%' ORDER BY created_at DESC";
        //      $query = "SELECT * FROM stack WHERE nom_from = :user_search AND task LIKE '%$keyword%' ORDER BY created_at DESC";
        $query =
            "SELECT * FROM stack WHERE (nom_from = :user_search OR nom_from=:hash_user_search) AND task LIKE :keyword ORDER BY created_at DESC";

        try {
            $sth = $this->container->db->prepare($query);

            $sth->bindParam(":user_search", $user_search);
            $sth->bindParam(":hash_user_search", $hash_user_search);

            $sth->bindParam(":keyword", $keyword);

            $sth->execute();
            $things = $sth->fetchAll();
        } catch (\Exception $e) {
            //            $t = new Thing(null);
            //            $t->Create("stack", "error", 'userSearch' . $e->getMessage());

            //            echo 'Caught error: ', $e->getMessage(), "\n";
            $things = false;
        }

        $sth = null;

        $thingreport = [
            "thing" => $things,
            "info" => "Searches by nom_from and task.",
            "help" => "Keyword subject line search.",
        ];

        return $thingreport;
    }

    /*
    // add bindparam
    function userThings() {

        $user_search = $this->from;

        $query = "SELECT * FROM stack WHERE (nom_from = :user_search OR nom_from=:hash_user_search) ORDER BY created_at DESC";


        $sth = $this->container->db->prepare($query);

        $sth->execute();

        //$this->container->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

        //if (!isset($this->test_count)) {$this->test_count = 0;}
        //$this->test_count += 1;
        //echo $this->test_count;
        $this->user_things = $sth;
        $this->user = $this->from;
        //$things = $sth->fetchAll();

        $thing_report['thing'] = false;
        $thing_report['info'] = 'So here is a db pointer.';
        $thing_report['help'] = 'No help';

        return $thing_report;
    }
*/

    /**
     *
     * @return unknown
     */
    function userNextThing()
    {
        if (!isset($this->user_things)) {
            $this->userThings();
        }

        if ($this->from != $this->user) {
            return;
        }

        $thing = $this->user_things->fetch();

        $thing_report["thing"] = $thing;
        $thing_report["info"] = "So here is the next thing.";
        $thing_report["help"] = "No help";

        return $thing_report;
    }

    /**
     *
     * @return unknown
     */
    private function updateThing()
    {
        $this->uuid = $thing->uuid;
        $this->to = $thing->nom_to;
        $this->from = $thing->nom_from;
        $this->subject = $thing->task;
        return "Thing read " . $this->uuid;
    }

    /**
     *
     */
    function validateThing()
    {
        if (!isset($this->from) and !isset($this->uuid)) {
            throw new Exception(
                '$this->from and $this->uuid not set.  Required.'
            );
        }

        // Fail if a null nom_from is provided
        if ($this->from == null) {
            throw new Exception('$this->nom_from set as null.  Required.');
        }

        // Every Thing should be able to do this.
        // but which Things reply should be private to the Things.
        // But for accounting a Thing has to be able to say
        // "Do you know me. Tell me your balance"
        //
        // This will need to be a public Stack variable.

        // Double-UU intentionally.
    }

    /**
     *
     * @param unknown $uuid (optional)
     * @return unknown
     */
    function UUids($uuid = null)
    {
        // Either $uuid or $nom_from might not be set.
        // UUids requires both to be set to do a search
        // for all records contain the $uuid.

        $this->validateThing(); // Throws exception if request
        // is asking for too much.

        // Do a self look-up if no uuid provided.
        if ($uuid == null);
        if ($this->uuid == null) {
            $thingreport = ["things" => false];
            $thingreport["info"] =
                'So here are the uuids of all records matching your request.  That\'s what you wanted.';
            $thingreport["help"] = "No matching records returned.";

            return $thingreport;
        } else {
            $uuid = $this->uuid;
        }

        // Should be able to request multiple Things uuids at the same time.
        if (is_string($uuid));
        $task_inclusions = [$uuid];

        // Should not be able to request by nom_from.
        // So commented out here.
        //if ( is_string($nom_from) ); {$nom_to_inclusions = array($nom_from);
        //$nom_from = 'bob@example.com';
        //$nom_to_inclusions = array($nom_from);
        // For stack_db.php

        $task_exclusions = null;

        $querable_fields = [
            "nom_from",
            "nom_to",
            "associations",
            "task",
            "message0",
            "settings",
        ];

        // Double capitals to make you thing before you ask a Thing
        // about what other Things it knows.

        // Which is why this only returns 5 to 7.  Mostly.

        $mostly = rand(0, 3) + 4;

        $task_sql = "";
        $exclusion_sql = "";
        $inclusion_sql = "";
        $sub_query = "";

        foreach ($task_inclusions as $task_inclusion) {
            // Example:
            // SELECT * FROM stack WHERE nom_from='<blank>' and ((task not like '%?%') or ($field not like '%transit%') or ($field not like '%test%')) ORDER BY RAND() LIMIT 3;

            if ($task_inclusions == null) {
                $task_sql = "";
            } else {
                $or = "";
                $task_sql = "";
                foreach ($querable_fields as $querable_field) {
                    $task_sql .=
                        $or .
                        "($querable_field like '%" .
                        $task_inclusion .
                        "%')";
                    if (count($task_inclusions) > 0) {
                        $or = " or ";
                    }
                }
            }

            $inclusion_sql .= $task_sql;

            //  if ($task_exclusions == null) {
            //   $task_sql = "";
            //  } else {
            //   $or = "";
            ////   $task_sql = "";
            //   foreach ($querable_fields as $querable_field) {
            //    $task_sql .= $or . "($querable_field not like '%" . $task_exclusion . "%')";
            //    if (count($task_exclusion) > 1) {$or = " or ";}
            //   }
            //  }

            //  $exclusion_sql .= $task_sql;

            // It's the or here that makes the exclusion script fail.
            //  $sub_query .= "' and (" . $inclusion_sql . " or " . $exclusion_sql . ")";
            $sub_query .= "' and (" . $inclusion_sql . ")";
        }

        $query =
            "SELECT * FROM stack WHERE nom_from='" .
            $this->from .
            $sub_query .
            " ORDER BY RAND()"; // LIMIT 3";

        $sth = $this->container->db->prepare($query);
        $sth->bindParam("nom_from", $this->from);
        $sth->execute();
        $things = $sth->fetchAll();

        $sth = null;

        $thingreport = [
            "things" => $things,
            "info" =>
                'So here are the uuids of all records matching your request.  That\'s what you wanted.',
            "help" => "It is up to you what you do with these.",
        ];

        return $thingreport;
    }

    /*
    // add bindparam
	function byPhrase($phrase){

		$query = "SELECT * FROM stack
			WHERE variables LIKE '%$phrase%'
			ORDER BY RAND()
			";

		$sth = $this->container->db->prepare($query);
		$sth->execute();
		$things = $sth->fetchAll();


		$thingreport = array('thing' => $things, 'info' => 'So here are Things with the phrase you provided in \$variables. That\'s what you wanted.','help' => 'It is up to you what you do with these.', 'whatisthis' => 'A list of Things which match at the provided phrase.');

		return $thingreport;
	}
*/

    /*
    // add bindparam
	function excludeWordlist(Array $words) {
        //http://www.sqltrainingonline.com/sql-not-like-with-multiple-values/

		// SELECT * FROM stack WHERE not (variables like '%dispatch%' or variables like '%iching%' or variables like '%credit%');
		$query = 'SELECT * FROM stack WHERE not (';


		$flag = false;
		foreach ($words as $word) {
			if ($flag == true) {$query .= ' OR ';}
			$query .= "variables LIKE '%$word%'";
			$flag = true;
		}
		$query .= ')';

		$sth = $this->container->db->prepare($query);

		//$sth->bindParam("nom_from", $nom_from);
		$sth->execute();
		//$thing = $sth->fetchObject();
		$things = $sth->fetchAll();


		$thingreport = array('thing' => $things, 'info' => 'So here are Things matching at least one of the words provided. That\'s what you wanted.','help' => 'It is up to you what you do with these.', 'whatisthis' => 'A list of Things which match at least one keyword.');



		return $thingreport;
	}
*/

    /**
     *
     * @return unknown
     */
    function getRed()
    {
        // Get all red items on the stack.
        // Not an identity function.
        //http://www.sqltrainingonline.com/sql-not-like-with-multiple-values/

        $search_term = "'%{\"status\":\"red\"}%'";

        $query = "SELECT * FROM stack WHERE variables LIKE " . $search_term;

        $sth = $this->container->db->prepare($query);
        $sth->execute();
        //$thing = $sth->fetchObject();
        $things = $sth->fetchAll();

        $sth = null;

        $thingreport = [
            "thing" => $things,
            "info" => "So here are Things which are flagged red.",
            "help" => "It is up to you what you do with these.",
            "whatisthis" => "A list of Things which have status red.",
        ];

        return $thingreport;
    }

    /**
     *
     * @param unknown $max (optional)
     * @return unknown
     */
    function getStack($max = null)
    {
        if ($max == null) {
            $max = 99;
        }
        $max = (int) $max;

        $agent = "stack";
        $user_search = "null@stackr.ca";
        $hash_user_search = hash($this->hash_algorithm, $user_search);

        //$user_search= "%$user_search%"; // Value to search for in Variables

        //$query = "SELECT * FROM stack WHERE nom_from LIKE :user_search AND nom_to = :agent ORDER BY created_at DESC LIMIT :max";
        $query =
            "SELECT * FROM stack WHERE (nom_from = :user_search OR nom_from=:hash_user_search) AND nom_to = :agent ORDER BY created_at DESC LIMIT :max";

        $sth = $this->container->db->prepare($query);
        $sth->bindParam(":user_search", $user_search);
        $sth->bindParam(":hash_user_search", $hash_user_search);

        $sth->bindParam(":agent", $agent);
        $sth->bindParam(":max", $max, PDO::PARAM_INT);
        $sth->execute();

        $things = $sth->fetchAll();

        $sth = null;

        $thingreport = [
            "things" => $things,
            "info" => "So here are Things which are flagged as stack reports.",
            "help" => "This reports on stack health",
        ];

        return $thingreport;
    }

    /**
     *
     * @return unknown
     */
    public static function getNew()
    {
        $query = "SELECT * FROM stack WHERE variables is NULL";

        $sth = $this->container->db->prepare($query);
        $sth->execute();
        $things = $sth->fetchAll();

        $sth = null;

        $thingreport = [
            "thing" => $things,
            "info" => "So here are Things which are flagged red.",
            "help" => "It is up to you what you do with these.",
            "whatisthis" => "A list of Things which have status red.",
        ];

        return $thingreport;
    }

    /**
     *
     * @return unknown
     */
    function length()
    {
        $thing_report = [
            "thing" => false,
            "db" => true,
            "info" => "So here is the length of the variables field.",
            "help" =>
                "There is a limit to the variables the stack can keep track of.",
            "whatisthis" => "The maximum length of the variables field.",
        ];

        if ($this->stack_handler == null) {
            return $thing_report;
        }

        if ($this->stack["infrastructure"] == "mysql") {
            $thing_report = $this->stack_handler->lengthMysql();
        }

        return $thing_report;
    }

    /**
     *
     * @return unknown
     */
    function connections()
    {
        $thing_report = [
            "thing" => false,
            "db" => true,
            "info" => "Connected threads.",
            "help" => "It is up to you what you do with these.",
        ];

        if ($this->stack_handler == null) {
            return $thing_report;
        }

        if ($this->stack["infrastructure"] == "mysql") {
            $thing_report = $this->stack_handler->connectionsMysql();
        }

        return $thing_report;
    }

    /**
     *
     * @param unknown $nom_from (optional)
     * @param unknown $n        (optional)
     * @return unknown
     */
    function random($nom_from = null, $n = 1)
    {
        $thing_report = [
            "thing" => [],
            "info" => "So here is a random thing from the stack.",
            "help" => "It is up to you what you do with these.",
        ];

        if ($this->stack_handler == null) {
            return $thing_report;
        }

        if ($this->stack["infrastructure"] == "mysql") {
            $thing_report = $this->stack_handler->randomMysql($nom_from, $n);
        }

        return $thing_report;
    }

    /**
     *
     * @param unknown $nom_from
     * @param unknown $n        (optional)
     * @return unknown
     */
    function randomn($nom_from, $n = 3)
    {
        $thing_report = [
            "thing" => [],
            "info" =>
                'So here are three things you put on the stack.  That\'s what you wanted.',
            "help" => "It is up to you what you do with these.",
        ];

        if ($this->stack_handler == null) {
            return $thing_report;
        }

        if ($this->stack["infrastructure"] == "mysql") {
            $thing_report = $this->stack_handler->randomnMysql($nom_from, $n);
        }

        return $thing_report;
    }

    /**
     * code review
     *
     * @param unknown $nom_from
     * @param unknown $task_exclusions   (optional)
     * @param unknown $nom_to_exclusions (optional)
     * @return unknown
     */
    function reminder(
        $nom_from,
        $task_exclusions = null,
        $nom_to_exclusions = null
    ) {
        $thing_report = [
            "thing" => [],
            "info" =>
                'So here are three things you put on the stack.  That\'s what you wanted.',
            "help" => "It is up to you what you do with these.",
        ];

        if ($this->stack_handler == null) {
            return $thing_report;
        }

        if ($this->stack["infrastructure"] == "mysql") {
            $thing_report = $this->stack_handler->reminderMysql(
                $nom_from,
                $task_exclusions,
                $nom_to_exclusions
            );
        }

        return $thing_report;
    }
}

// https://insomanic.me.uk/php-trick-catching-fatal-errors-e-error-with-a-custom-error-handler-cea2262697a2
//set_error_handler('myErrorHandler');
register_shutdown_function(
    'Nrwtaylor\StackAgentThing\fatalErrorShutdownHandler'
);

/**
 *
 * @param unknown $errno
 * @param unknown $errstr
 * @param unknown $errfile
 * @param unknown $errline
 * @return unknown
 */
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (E_RECOVERABLE_ERROR === $errno) {
        //ob_clean();
        //echo "BORK | Bounty ";
        //ob_clean();
        if (ob_get_contents()) {
            ob_clean();
        }
        echo "BORK | 3797e2c1-6585-4ae8-a256-b3e5466c980f ";
        //echo "'caught' fatal error E_RECOVERABLE_ERROR\n";
        return true;
    } elseif (E_ERROR === $errno) {
        // If there is stuff in the screen buffer clear it.
        if (ob_get_contents()) {
            ob_clean();
        }
        //ob_clean();
        echo "BORK | e5ffb5de-a502-466e-8ecc-f0ec9f861e0d";
        if (preg_match("(Maximum|execution|time|exceeded)", $errstr) === 1) {
            echo " | We only have a limited amount of steam on this server.";
        }

        // echo $errno . "\n";
        // echo $errstr . "\n";
        // echo $errfile . "\n";
        // echo $errline . "\n";

        //        echo "'caught' fatal E_ERROR | BOUNTY\n";
        //        $actual_link = "meep";
        // For debugging.
        // https://stackoverflow.com/questions/6768793/get-the-full-url-in-php
        //        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        //        echo $actual_link;

        return true;
    }
    if (ob_get_contents()) {
        ob_clean();
    }

    //ob_clean();
    echo "BORK | 00539cf2-0f56-495a-87df-64746cebfd41";
    echo "'dropped' catchable fatal error\n";
    return false;
}

/**
 *
 */
function fatalErrorShutdownHandler()
{
    // Options

    // This displays on each uuid end-point
    // echo "#test test test global notification";
    // exit(); // will not stop display of full Stackr response

    // Present blank screen
    // ob_clean();
    // Add text.
    //echo "BORK | Bounty ";

    // This is the end-point to the full Stackr response.
    // exit(); // has no effect/affect
    // Present blank screen
    // ob_clean();

    //Add text.

    $last_error = error_get_last();

    if (($last_error["type"] ?? null) === E_ERROR) {
        // fatal error
        myErrorHandler(
            E_ERROR,
            $last_error["message"],
            $last_error["file"],
            $last_error["line"]
        );
    }

    // This is the end-point to the full Stackr response.
    //
    //ob_clean(); // Stops buttons and footer.
    //Add text.
    //echo "BORK | No response."; // Will appear in the web page
    // An exit here will cut off the footer display.
    //exit();
}
