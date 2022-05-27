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

use \PDO;

class Mysql extends Agent
{
    public $var = "hello";

    /**
     *
     * @param unknown $uuid
     * @param unknown $nom_from
     * @return unknown
     */
    function init()
    {
        $this->thing_report["info"] =
            "This is an agent to manage MySQL database calls.";
        $this->thing_report["help"] = "Not yet user facing.";

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
        $this->initMysql();
        //        $this->test();
    }

    public function test()
    {
        $thingreport = $this->priorMysql();
    }

    public function initMysql()
    {
        $this->statusMysql('loading');

        // create ontainer and configure it

        $settings = require $GLOBALS["stack_path"] . "private/settings.php";
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

        // Load MySQL database settings.
        if (isset($this->thing)) {
            $this->thing->container["stack"]["state"];

            if (isset($this->thing->container["settings"]["db"]["host"])) {
                $this->host = $this->thing->container["settings"]["db"]["host"];
            }

            if (isset($this->thing->container["settings"]["db"]["dbname"])) {
                $this->dbname =
                    $this->thing->container["settings"]["db"]["dbname"];
            }

            if (isset($this->thing->container["settings"]["db"]["user"])) {
                $this->user = $this->thing->container["settings"]["db"]["user"];
            }

            if (isset($this->thing->container["settings"]["db"]["pass"])) {
                $this->pass = $this->thing->container["settings"]["db"]["pass"];
            }

            $this->char_max = $this->thing->container["stack"]["char_max"];
        } else {
            // No thing provided.

            $settings = require $GLOBALS["stack_path"] . "private/settings.php";
            $db = $settings["settings"]["db"];
            $this->host = $db["host"];
            $this->dbname = $db["dbname"];
            $this->user = $db["user"];
            $this->pass = $db["pass"];
        }
        // https://stackoverflow.com/questions/6263443/pdo-connection-test/6263868#6263868
        try {
            $pdo = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->dbname,
                $this->user,
                $this->pass
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo = $pdo;
            $this->statusMysql('ready');
        } catch (\Throwable $t) {
            $this->errorMysql($t->getMessage());
        } catch (\Error $ex) {
            $this->errorMysql($ex->getMessage());
        }
    }

    public function errorMysql($text = null)
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

    public function statusMysql($text = null)
    {
        if ($text != null) {
            $this->status = $text;
        }
        return $this->status;
    }

    public function isReadyMysql()
    {
        if (isset($this->status) and $this->status == 'ready') {
            return true;
        }
        return false;
    }

    function get()
    {
    }
    function set()
    {
    }

    public function respondResponse()
    {
        $this->thing_report["message"] = $this->thing_report["sms"];
        if (
            $this->agent_input == null or
            $this->agent_input == "" or
            $this->agent_input == "response"
        ) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    function readSubject()
    {
    }

    function run()
    {
    }

    public function makeMessage()
    {
        $m = "MySQL stack available.";
        if (!isset($this->pdo) or $this->pdo == null) {
            $m = "MySQL stack not available.";
        }

        $this->message = $m;
        $this->thing_report["message"] = $m;
    }

    public function makeSMS()
    {
        $sms = "MYSQL | " . $this->message;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     * @param unknown $created_at (optional)
     * @return unknown
     */
    function priorMysql($created_at = null)
    {
        if (
            !isset($this->pdo) or
            $this->pdo == null or
            $this->get_prior === false
        ) {
            $thingreport = [
                "thing" => false,
                "info" => "Prior get is off for this stack.",
                "help" => "No help available.",
            ];

            return $thingreport;
        }

        $nom_from = $this->from;
        $hash_nom_from = hash($this->hash_algorithm, $nom_from);
        // Given a $uuid.  Find the previous record the $from user
        // created.

        // Review thought.  Wouldn't searching for the latest record
        // before the time_stamp be more efficient?

        // http://stackoverflow.com/questions/28451031/how-to-get-second-last-row-from-mysql-database
        // Doesn't work in case of same time_stamp.
        // That is acceptable.  A second resolution for creating records is
        // likely a good limit.  Easy to upgrade by adding a 'microsecond' column to the
        // database.

        // Change to InnoDB means stack is likely now working on microsecond
        // time quantum.

        // sqlinjection commentary
        // nom_from is a carrier provided identifier, therefore judged safe to
        // pass by message carriers.
        // created_at is a stack created field

        if ($created_at == null) {
            $query_string =
                "SELECT * FROM (SELECT * FROM stack WHERE
				(nom_from='" .
                $this->from .
                "' OR nom_from='" .
                $hash_nom_from .
                "'" .
                ") ORDER BY created_at DESC LIMIT 2) AS t ORDER BY created_at ASC LIMIT 2";
        } else {
            $query_string =
                "SELECT * FROM stack where (nom_from = '" .
                $this->from .
                "' OR nom_from='" .
                $hash_nom_from .
                "'" .
                ") and created_at < '" .
                $created_at .
                "' order by created_at DESC LIMIT 3";
        }
        try {
            $sth = $this->pdo->prepare($query_string);
            $sth->execute();
            $thing = $sth->fetchObject();
        } catch (\Exception $e) {
            // Devstack - decide how to handle thing full

            //            $t = new Thing(null);
            //            $t->Create("stack", "error", 'priorGet ' . $e->getMessage());
            $thing = false;
        }

        $sth = null;

        $thingreport = [
            "thing" => $thing,
            "info" =>
                "Turns out it has an imperfect and forgetful memory.  But you can see what is on the stack by typing " .
                $this->web_prefix .
                "api/thing/<32 characters>.",
            "help" => "Check your junk/spam folder.",
        ];

        // Runs in 0 to 8ms

        return $thingreport;
    }

    /**
     *
     * @param unknown $field_text
     * @param unknown $string_text
     */
    function writeMysql($field_text, $string_text)
    {
/*
if (is_array($string_text)) {
$string_text = $this->arrayJson($string_text);
var_dump("string_text", $string_text);
}
*/

//$arr = $this->arrayJson($string_text);
//$string_text = $this->jsonArr($arr);

        // merp
        if (strlen($string_text) > 100000) {
            //var_dump($string_text);
            //$this->error .= "Field length exceeded.";
            $this->errorMysql("Field length exceeded.");
            $string_text = json_encode(["mysql" => ["field length exceed"]]);
            // Do not write field if too large.
            //return;
            $this->last_update = true;
            return true;
        }

//        if (!isset($this->pdo)) {
//            return true;
//        }

        if (!$this->isReadyMysql()) {
            return true;
        }


        $uuid = $this->uuid;


var_dump("Mysql writeMysql uuid " . $uuid);
        try {
            $query = "UPDATE stack SET $field_text=:string_text WHERE uuid=:uuid";
            $sth = $this->pdo->prepare($query);
            $sth->bindParam(":uuid", $uuid);

            $sth->bindParam(":string_text", $string_text);

            // This is not allowed by PHP.
            // Noting that field_text is generated by an Agent.  Not channel input.
            //$sth->bindParam(":field_text", $field_text);

            $sth->execute();

            if (!$sth) {
                //$this->error .= $sth->errorInfo();
                $this->errorMysql($sth->errorInfo());
            }

            $this->last_update = false;
        } catch (\PDOException $e) {
            $this->errorMysql($e->getMessage());

            $sth = null;
            return true;
        } catch (\Throwable $e) {
            $this->errorMysql($e->getMessage());

            $sth = null;
            return true;
        } catch (\Exception $e) {
            $this->errorMysql($e->getMessage());

            $thing = false;
            $this->last_update = true;

            $sth = null;
            return true;
        }
        $sth = null;
        return $this->uuid;
    }

    /**
     *
     * @return unknown
     */
    function countMysql()
    {
        if (!isset($this->pdo)) {
            return true;
        }
        $info = "Did not count things on stack.";
        $thing_count = null;
        try {
            $sth = $this->pdo->prepare("SELECT count(*) FROM stack");
            $sth->execute();

            $thing_count = $sth->fetchColumn();
            $info = "Counted " . $thing_count . "  records on stack.";
        } catch (\PDOException $e) {
            $this->errorMysql($e->getMessage());
        } catch (\Throwable $e) {
            $this->errorMysql($e->getMessage());
        } catch (\Exception $e) {
            $this->errorMysql($e->getMessage());

            //            $thing = false;
            //            $this->last_update = true;
        }

        $sth = null;

        $thingreport = [
            "things" => false,
            "info" => $info,
            "help" => "This is how big the stack is.",
        ];
        $thingreport["number"] = $thing_count;

        return $thingreport;
    }

    /**
     *
     * @param unknown $field
     * @return unknown
     */
    function readField($field)
    {
        $thing = $this->getMysql();
        //$this->thing = $thingreport["thing"];

        if (isset($thing->$field)) {
            // I think I should also do
            $this->$field = $thing->$field;
            return $thing->$field;
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
    function createMysql($subject, $to)
    {
        if (!$this->isReadyMysql()) {
            return true;
        }

// dev
$uuid = Uuid::createUuid();

        try {
            // Create a new record in the db for the Thing.

            $query = $this->pdo->prepare("INSERT INTO stack
                        (uuid,task,nom_from,nom_to)
                        VALUES (:uuid, :task, :nom_from, :nom_to)");
//var_dump("Mysql createMysql u", $u);
            $uuid = $this->uuid;
//$uuid = $u;
            $task = $subject;
            $nom_from = $this->from;

            $hash_nom_from = hash($this->hash_algorithm, $nom_from);

            if ($this->hash_state == "off") {
                $hash_nom_from = $nom_from;
            }
            $nom_to = $to;

            $query->bindParam(":uuid", $uuid);
            $query->bindParam(":task", $task);
            $query->bindParam(":nom_from", $nom_from);
            //  $query->bindParam(":hash_nom_from", $hash_nom_from);
            $query->bindParam(":nom_to", $nom_to);

            $query->execute();
            $query = null;

            //           $this->thing->log(
            //                'made MYSQL record.',
            //                "INFORMATION"
            //            );
            return $uuid;
            //return true;
            //return $query;
        } catch (\Exception $e) {
            //           $this->thing->log(
            //                'could not create MySQL record.',
            //                "INFORMATION"
            //            );

            // Devstack - decide how to handle thing full
            // Do this for now.

            //            $t = new Thing(null);
            //            $t->Create("stack", "error", 'Create' . $e->getMessage());

            // Commented out 24 November 2019.
            // Prevents a SQLSTATE[22001] error from looping.
            //$t = new Bork($t);

            //echo "BORK | Thing is full.";
            //echo 'Caught error: ',  $e->getMessage(), "\n";
            $query = null;
            $thing = false;
            $this->last_update = true;
            return true;
            //return false;
        }
    }

    /**
     *
     * @return unknown
     */
    //function Get()

    function getMysql($uuid = null)
    {
        // But we don't need to find, it because the UUID is randomly created.
        // Chance of collision super-super-small.
        // So just return the contents of thing.  false if it doesn't exist.
        //        if (!isset($this->pdo) or $this->pdo == null) {
        //            return false;
        //        }
        if (!$this->isReadyMysql()) {
            return true;
        }

        if ($uuid == null) {
            $uuid = $this->uuid;
        }

        try {
            // Trying long form.  Doesn't seme to have performance advantage.
            //            $sth = $this->pdo->prepare(
            //                "SELECT uuid, task, nom_from, nom_to, created_at, associations, message0, message1, message2, message3, message4, message5, message6, message7, settings, variables FROM stack WHERE uuid=:uuid"
            //            );
            $query =
                "SELECT uuid, task, nom_from, nom_to, created_at, associations, message0, settings, variables FROM stack WHERE uuid=:uuid";
            $sth = $this->pdo->prepare($query);

            //$sth = $this->container->db->prepare("SELECT * FROM stack WHERE uuid=:uuid");
            $sth->bindParam("uuid", $uuid);

            $sth->execute();

            $thing = $sth->fetchObject();
            //    } catch ( \PDOException $e ) {
            //        echo 'ERROR!';
            //exit();
            //        print_r( $e )
        } catch (\Exception $e) {
            // devstack look get the error code.
            // SQLSTATE[HY000] [2002] Connection refused
            if ($e->getCode() == "2002" or $e->getCode() == "HY000") {
                // devstack write to text file?
                // Don't try making more entries when the database is refusing entries...
            } else {
                //            $t = new Thing(null);
                //            $t->Create("stack", "error", 'Get ' . $e->getCode());
            }
            $thing = false;
        }
        $sth = null;

        return $thing;
    }

    /**
     *
     * @return unknown
     */
    function forgetMysql($uuid = null)
    {
        if (!$this->isReadyMysql()) {
            return true;
        }

        if ($uuid == null) {
            $uuid = $this->uuid;
        }
        $error = null;
        try {
            $sth = $this->pdo->prepare("DELETE FROM stack WHERE uuid=:uuid");
            $sth->bindParam("uuid", $uuid);
            $sth->execute();
        } catch (\Throwable $t) {
            $error = true;
        } catch (\Error $ex) {
            $error = true;
        }

        $thingreport = [
            "info" => "That thing was forgotten.",
            "error" => $error,
        ];
        $sth = null;
        return $thingreport;
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
    function associationsearchMysql($value, $max = null)
    {
        if ($max == null) {
            $max = 3;
        }
        $max = (int) $max;

        $user_search = $this->from;
        //        $hash_user_search($this->hash_algorithm, $user_search);
        $hash_user_search = hash($this->hash_algorithm, $user_search);

        // https://stackoverflow.com/questions/11068230/using-like-in-bindparam-for-a-mysql-pdo-query
        $value = "%$value%"; // Value to search for in Variables

        $thingreport["things"] = [];

        try {
            $value = "%$value%"; // Value to search for in Variables

            $query =
                "SELECT * FROM stack WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND associations LIKE :value ORDER BY created_at DESC LIMIT :max";

            // $query = "SELECT * FROM stack WHERE nom_from=:user_search AND MATCH(variables) AGAINST(:value IN BOOLEAN MODE ) ORDER BY creat$
            // $query = "SELECT * FROM stack WHERE nom_from=:user_search AND MATCH(variables) AGAINST(:value IN BOOLEAN MODE ) ORDER BY creat$
            // $query = "SELECT uuid, task, nom_from, nom_to, created_at, message0, settings, variables FROM stack WHERE nom_from=:user_searc$

            // $value = "*$value*"; // Value to search for in Variables
            // $query = "SELECT uuid, variables FROM stack WHERE nom_from=:user_search AND MATCH(variables) AGAINST(:value IN BOOLEAN MODE ) $

            $sth = $this->pdo->prepare($query);

            $sth->bindParam(":user_search", $user_search);
            $sth->bindParam(":hash_user_search", $hash_user_search);

            $sth->bindParam(":value", $value);
            $sth->bindParam(":max", $max, PDO::PARAM_INT);
            $sth->execute();

            $things = $sth->fetchAll();

            //$sth = null;

            $thingreport["info"] =
                'So here are Things with the association you provided. That\'s what you want';
            $thingreport["things"] = $things;
        } catch (\PDOException $e) {
            //            $t = new Thing(null);
            //            $t->Create("stack", "error", 'associationSearch ' .$e->getMessage());

            // echo "Error in PDO: ".$e->getMessage()."<br>";
            $thingreport["info"] = $e->getMessage();
            $thingreport["things"] = [];
        }

        $sth = null;

        return $thingreport;
    }

    /**
     *
     * @param unknown $path
     * @param unknown $value
     * @param unknown $max   (optional)
     * @return unknown
     */
    function variablesearchMysql($path, $value, $max = null)
    {
        if ($max == null) {
            $max = 3;
        }
        $max = (int) $max;

        $user_search = $this->from;
        $hash_user_search = hash($this->hash_algorithm, $user_search);

        // https://stackoverflow.com/questions/11068230/using-like-in-bindparam-for-a-mysql-pdo-query
        $value = "%$value%"; // Value to search for in Variables

        $thingreport["things"] = [];

        try {
            //            $query =
            //                "SELECT * FROM stack FORCE INDEX (created_at_nom_from) WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND variables LIKE :value ORDER BY created_at DESC LIMIT :max";

            $query =
                "SELECT * FROM stack WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND variables LIKE :value ORDER BY created_at DESC LIMIT :max";

            //$value = "+$value"; // Value to search for in Variables

            //    $query =
            //        'SELECT * FROM stack WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND MATCH(variables) AGAINST (:value IN BOOLEAN MODE) ORDER BY created_at DESC LIMIT :max';

            $sth = $this->pdo->prepare($query);

            $sth->bindParam(":user_search", $user_search);
            $sth->bindParam(":hash_user_search", $hash_user_search);

            $sth->bindParam(":value", $value);
            $sth->bindParam(":max", $max, PDO::PARAM_INT);
            $sth->execute();

            $things = $sth->fetchAll();

            $thingreport["info"] =
                'So here are Things with the variable you provided in \$variables. That\'s what you want';
            $thingreport["things"] = $things;
        } catch (\PDOException $e) {
            // echo "Error in PDO: ".$e->getMessage()."<br>";
            $thingreport["info"] = $e->getMessage();
            $thingreport["things"] = [];
        }

        $sth = null;

        return $thingreport;
    }

    function nuuidsearchMysql($nuuid)
    {
        $user_search = $this->from;
        $hash_user_search = hash($this->hash_algorithm, $user_search);

        $nuuid = "$nuuid%"; // Value to search for in Variables

        $thingreport["things"] = [];

        try {
            $query =
                "SELECT * FROM stack WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND uuid LIKE :nuuid ORDER BY created_at DESC";

            $sth = $this->pdo->prepare($query);

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
            $keyword = $this->pdo->quote($keyword_input);
            $query =
                "SELECT * FROM stack WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND nom_to=:agent AND MATCH(task) AGAINST (:keyword IN BOOLEAN MODE) ORDER BY created_at DESC LIMIT :max";
        }

        if (strtolower($mode) == "like") {
            $keyword = "$keyword_input"; // Value to search for in Variables
            $query =
                "SELECT * FROM stack WHERE task LIKE BINARY :keyword AND nom_to=:agent AND (nom_from=:user_search OR nom_from=:hash_user_search) ORDER BY created_at DESC LIMIT :max";
        }
        if (strtolower($mode) == "where") {
            $keyword = $this->pdo->quote($keyword_input);
            $query =
                "SELECT * FROM stack WHERE task = BINARY :keyword AND nom_to=:agent AND (nom_from=:user_search OR nom_from=:hash_user_search) ORDER BY created_at DESC LIMIT :max";
        }

        if (strtolower($mode) == "natural language") {
            $keyword = $this->pdo->quote($keyword_input);
            $query =
                "SELECT * FROM stack WHERE (nom_from=:user_search OR nom_from=:hash_user_search) AND nom_to=:agent AND MATCH(task) AGAINST (:keyword IN NATURAL LANGUAGE MODE) ORDER BY created_at DESC LIMIT :max";
        }

        if (strtolower($mode) == "equal") {
            $keyword =
                "adidas adiPower S bounce men\'s spikeless Golf Shoe NEW";
            //      $keyword = $keyword_input; // Value to search for in Variables
            $keyword = $this->pdo->quote(
                "adidas adiPower S bounce men\'s spikeless Golf Shoe NEW"
            );

            $keyword = '$keyword_input';
            //           $keyword = $this->container->db->quote($keyword_input);
            //$text = "adidas adiPower S bounce men's spikeless Golf Shoe NEW";
            //  $query ='SELECT * FROM stack WHERE task=:keyword AND nom_to=:agent AND nom_from=:user_search ORDER BY created_at DESC LIMIT :max';

            $query = 'SELECT * FROM stack WHERE task=":keyword"';
        }

        $sth = $this->pdo->prepare($query);
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

    function fromcountMysql($horizon = null)
    {
        $query = "SELECT DISTINCT nom_from FROM stack";

        if ($horizon != null) {
            $horizon = (int) $horizon;
            $query =
                "SELECT DISTINCT nom_from FROM stack WHERE created_at > (NOW() - INTERVAL :horizon HOUR)";
        }

        $sth = $this->pdo->prepare($query);

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

        $sth = $this->pdo->prepare($query);
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

        $sth = $this->pdo->prepare($query);
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

        $sth = $this->pdo->prepare($query);
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
            $sth = $this->pdo->prepare($query);
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
            $sth = $this->pdo->prepare($query);

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
        //$things = $sth->fetchAll();

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

        return;
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

        $sth = $this->pdo->prepare($query);
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

        $sth = $this->pdo->prepare($query);
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

        $sth = $this->pdo->prepare($query);
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

        $sth = $this->pdo->prepare($query);
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
    function lengthMysql()
    {
        $query =
            "SELECT variables, LENGTH(variables) AS mlen FROM stack ORDER BY mlen DESC LIMIT 1";
        $sth = $this->pdo->prepare($query);
        $sth->execute();
        $response = $sth->fetchAll();

        $keys = array_keys($response);

        $sth = null;

        $thingreport = [
            "thing" => false,
            "db" => $response,
            "info" => "So here is the length of the variables field.",
            "help" =>
                "There is a limit to the variables the stack can keep track of.",
            "whatisthis" => "The maximum length of the variables field.",
        ];

        return $thingreport;
    }

    /**
     *
     * @return unknown
     */
    function connectionsMysql()
    {
        // NOT TESTED

        $query = "SHOW STATUS WHERE `variable_name` = 'Threads_connected'";

        $sth = $this->pdo->prepare($query);
        $sth->execute();
        $response = $sth->fetchAll();

        $keys = array_keys($response);

        $sth = null;

        $thingreport = [
            "thing" => false,
            "db" => $response,
            "info" =>
                'So here are Things matching at least one of the words provided. That\'s what you wanted.',
            "help" => "It is up to you what you do with these.",
            "whatisthis" =>
                "A list of Things which match at least one keyword.",
        ];

        //$thingreport = false;

        return $thingreport;
    }

    /**
     *
     * @param unknown $nom_from (optional)
     * @param unknown $n        (optional)
     * @return unknown
     */
    function randomMysql($nom_from = null, $n = 1)
    {
        if ($nom_from == null) {
            // https://explainextended.com/2009/03/01/selecting-random-rows/
            // https://stackoverflow.com/questions/1244555/how-can-i-optimize-mysqls-order-by-rand-function
            /*
            $q = "SELECT  *
                FROM    (
                    SELECT  @cnt := COUNT(*) + 1,
                        @lim := 1
                FROM stack
                ) vars
                STRAIGHT_JOIN
                (
                SELECT  r.*,
                    @lim := @lim - 1
                    FROM    stack r
                    WHERE   (@cnt := @cnt - 1)
                    AND RAND(20090301) < @lim / @cnt
                ) i";
*/
            //            $q = "SELECT * FROM stack WHERE RAND()<=0.0006 limit 1";
            //            $q = "SELECT * FROM stack WHERE RAND()<(SELECT ((1/COUNT(*))*10) FROM stack) LIMIT 1";
            //            $q = "SELECT * FROM stack WHERE RAND()<(SELECT ((1/COUNT(*))*10) FROM stack) LIMIT 1";
            //            $q = "SELECT * FROM stack WHERE RAND()<(SELECT ((1/COUNT(*))*10) FROM stack) ORDER BY RAND() LIMIT 1";

            $q =
                "SELECT * FROM stack WHERE RAND()<(SELECT ((" .
                $n .
                "/COUNT(*))*10) FROM stack) ORDER BY RAND() LIMIT " .
                $n;

            //            $q = "SELECT * FROM stack ORDER BY RAND() LIMIT " . $n;
            //            $q = "SELECT * FROM stack WHERE RAND()<(SELECT ((20/COUNT(*))*10) FROM stack) ORDER BY RAND() LIMIT 20";

            $sth = $this->pdo->prepare($q);

            $sth->execute();
            //      $thing = $sth->fetchObject();
            $things = $sth->fetchAll();

            $thingreport = [
                "things" => $things,
                "info" =>
                    'So here are three things you put on the stack.  That\'s what you wanted.',
                "help" => "It is up to you what you do with these.",
            ];
        } else {
            $q =
                "SELECT * FROM stack WHERE RAND()<(SELECT ((1/COUNT(*))*10) FROM stack) ORDER BY RAND() LIMIT 1";
            $sth = $this->pdo->prepare($q);

            $sth->execute();
            $thing = $sth->fetchObject();

            $this->to = $thing->nom_to;
            $this->from = $thing->nom_from;
            $this->subject = $thing->task;

            $thingreport = [
                "things" => $thing,
                "info" =>
                    'So here are three things you put on the stack.  That\'s what you wanted.',
                "help" => "It is up to you what you do with these.",
            ];
        }

        $sth = null;

        return $thingreport;
    }

    /**
     *
     * @param unknown $nom_from
     * @param unknown $n        (optional)
     * @return unknown
     */
    function randomnMysql($nom_from, $n = 3)
    {
        $hash_nom_from = hash($this->hash_algorithm, $nom_from);

        // Pick N of identity's things.
        $sth = $this->pdo->prepare(
            "SELECT * FROM stack WHERE (nom_from=:nom_from OR nom_from=:hash_nom_from) ORDER BY RAND() LIMIT 3"
        );
        $sth->bindParam("nom_from", $nom_from);
        $sth->bindParam("hash_nom_from", $hash_nom_from);

        $sth->execute();
        $things = $sth->fetchAll();

        $thingreport = [
            "thing" => $things,
            "info" =>
                'So here are three things you put on the stack.  That\'s what you wanted.',
            "help" => "It is up to you what you do with these.",
        ];

        $sth = null;

        return $thingreport;
    }

    /**
     * code review
     *
     * @param unknown $nom_from
     * @param unknown $task_exclusions   (optional)
     * @param unknown $nom_to_exclusions (optional)
     * @return unknown
     */
    function reminderMysql(
        $nom_from,
        $task_exclusions = null,
        $nom_to_exclusions = null
    ) {
        if (!isset($this->pdo)) {
            return true;
        }

        // Example:
        // SELECT * FROM stack WHERE nom_from='test@test.test' and ((task not like '%?%') or (nom_from not like '%transit%') or (nom_from not like '%test%')) ORDER BY RAND() LIMIT 3;

        // sqlinjection comment
        // $task_exlusions and $nom_to_exclusions are code defined

        if ($task_exclusions == null) {
            $task_exclusions = "";
        } else {
            $and = "";
            $task_sql = "(";
            foreach ($task_exclusions as $task_exclusion) {
                $task_sql .= $and . "task not like '%" . $task_exclusion . "%'";
                $and = " and ";
            }
            $task_sql .= " and task not like ''";
            $task_sql .= ")";
        }

        if ($nom_to_exclusions == null) {
            $nom_to_exclusions = "";
        } else {
            $nom_to_sql = "(";
            $and = "";
            //$nom_to_sql = "";
            foreach ($nom_to_exclusions as $nom_to_exclusion) {
                $nom_to_sql .=
                    $and . "nom_to not like '%" . $nom_to_exclusion . "%'";
                $and = " and ";
            }
            $nom_to_sql .= ")";
        }

        $query =
            "SELECT * FROM stack WHERE nom_from='" .
            $nom_from .
            "' and (" .
            $task_sql .
            " and " .
            $nom_to_sql .
            ") ORDER BY RAND() LIMIT 3";

        $sth = $this->pdo->prepare($query);
        //        $sth->bindParam("nom_from", $nom_from);
        $sth->execute();
        $things = $sth->fetchAll();

        $sth = null;

        $thingreport = [
            "thing" => $things,
            "info" =>
                'So here are three things you put on the stack.  That\'s what you wanted.',
            "help" => "It is up to you what you do with these.",
        ];

        return $thingreport;
    }
}
