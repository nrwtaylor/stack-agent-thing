<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

// For testing.
// See https://jolicode.com/blog/find-segfaults-in-php-like-a-boss

class Thing
{
    public $var = 'hello';

    public $from = null;

    function __construct($uuid, $test_message = null)
    {
        //declare(ticks=1);

        //$resource_path = "/var/www/stackr.test/resources/debug";
        //require $resource_path . '/HardCoreDebugLogger.php';
        //\HardCoreDebugLogger::register();

        // Now at 0.0.10

        // Imagine both a need not to touch anything.
        // And a need to make this much tidier.
        // So expect lots of comments.  And a few deletions.

        // Start the clock
        $this->elapsed_runtime();
        $this->log("Thing deserialization started.");

        // At this point, we are presented a UUID.
        // Whether or not the record exists is another question.

        // But we don't need to "find", because the UUID is randomly created.
        // Chance of collision super-super-small.

        // https://www.quora.com/Has-there-ever-been-a-UUID-collision

        // So just return the contents of thing.  false if it doesn't exist.
        // create container and configure it

        // That UUID collisions can occur is not a concern of the Thing object.
        // It is a significant Stack concern.

        if (!isset($GLOBALS['stack_path'])) {
            // Try this, otherwise fail.
            $GLOBALS['stack_path'] = "/var/www/stackr.test/";
            //$GLOBALS['stack_path'] = "/var/www/html/stackr.ca/";
        }

        //set_error_handler(array($this, "exception_error_handler"));

        $url = $GLOBALS['stack_path'] . 'private/settings.php';
        $settings = require $url;

        //        $settings = require $url;

        $this->container = new \Slim\Container($settings);

        //$this->container = $app->getContainer();
        //$this->test = true;

        // A REMINDER THAT IT IS TRIVIAL TO ADD ACCESS TO THE STACK MYSQL
        // DATABASE SETTINGS FROM A THING.
        //		$this->container['db'] = function ($c) {
        //			$db = $c['settings']['db'];
        //			$pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        //				$db['user'], $db['pass']);
        //			//$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //			$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        //			return $pdo;
        //			};
        // BUT THINGS DON'T NEED THAT.  THAT LEADS TO A BROKEN STACK.
        // NRWTAYLOR 9:48 17 April 2017

        // Action: Look for this in submitted code.

        $this->container['stack'] = function ($c) {
            $db = $c['settings']['stack'];
            return $db;
        };

        $this->container['api'] = function ($c) {
            $db = $c['settings']['api'];
            return $db;
        };

        $this->char_max = $this->container['stack']['char_max'];

        $this->mail_postfix = $this->container['stack']['mail_postfix'];
        $this->stack_uuid = $this->container['stack']['uuid'];

        $this->associate_prior = $this->container['stack']['associate_prior'];
        $this->associate_posterior =
            $this->container['stack']['associate_posterior'];

        $this->web_prefix = $this->container['stack']['web_prefix'];
        $this->engine_state = $this->container['stack']['engine_state'];

        $this->console_output = 'on';
        if (isset($this->container['stack']['console_output'])) {
            $this->console_output = $this->container['stack']['console_output'];
        }

        $this->logging_console = 'off';
        if (isset($this->container['stack']['logging_console'])) {
            $this->logging_console =
                $this->container['stack']['logging_console'];
        }

        $this->logging_level_default = "off";
        if (isset($this->container['stack']['logging_level_default'])) {
            $this->logging_level_default =
                $this->container['stack']['logging_level_default'];
        }

        $this->logging_level_trigger = "off";
        if (isset($this->container['stack']['logging_level_trigger'])) {
            $this->logging_level_trigger =
                $this->container['stack']['logging_level_trigger'];
        }

        $this->queue_handler = "none";
        if (isset($this->container['stack']['queue_handler'])) {
            $this->queue_handler = $this->container['stack']['queue_handler'];
        }

        $this->hash_algorithm = "sha256";
        if (isset($this->container['stack']['hash_algorithm'])) {
            $this->hash_algorithm = $this->container['stack']['hash_algorithm'];
        }


        //set_error_handler(array($this, "exception_error_handler"));

        try {
            $this->getThing($uuid);
        } catch (\Exception $e) {
            $this->log("No Thing to get.");

            // Fail quietly. There was no Thing to get.
            //echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        // Deal with it.

        // devstack
        //		echo "Stack Balance<br>";
        //		$this->stackBalance($this->uuid);

        $this->log("Thing instantiation completed.");
    }

    function __destruct()
    {

        $t = "";
        if (isset($this->nuuid)) {
            $t = $this->nuuid;
        }
        $this->log("Thing " . $t . " de-instantiated.");
    }

    function getThing($uuid = null)
    {
        if (null === $uuid) {
            // ONLY PLACE IN STACK WHERE UUIDs ARE ASSIGNED
            //
            // ---
            // THIS IS CORE TECHNOLOGY.  THE SELECTION OF THE UUID GENERATOR IS
            // CRITICAL.  INTENTIONALLY LEFT OPEN AS CHAR(34) DB FIELD.

            $this->uuid = $this->getUUid();
            $this->nuuid = substr($this->uuid, 0, 4);
            //$this->uuid = (string) Uuid::uuid4();

            $this->log("Thing made a UUID.");

            // And then we pull out some Thing related variables and settings.

            $this->container['thing'] = function ($c) {
                $db = $c['settings']['thing'];
                return $db;
            };

            $this->stack_account = $this->container['thing']['stack_account'];
            // The settings file can make Thing set up a specific Thing account.
            $this->thing_account = $this->container['thing']['thing_account'];

            // I'm still working on what the difference between the two really
            // is.  Settings determine the functioning of the Thing.
            // Variables are stuff that can be lost when the Thing
            // deinstantiates.

            // Variable overflow is challenging. See VARIABLES.

            // Can't call db here, can only call it when $from is known.
            // $this->db = new Database($this->uuid, $this->from);

            $this->json = new Json($this->uuid);

            $this->log("JSON connector made.");
            $this->log("Made a thing from null.");

            // Testing this as of 15 June 2018.  Not used by framework yet.
            $this->variables = new Json($this->uuid);
            $this->variables->setField("variables");

            $this->choice = new Choice($this->uuid, $this->from);

            $this->log("Choice connector made.");

            // Sigh.  Hold this Thing to account.  Unless it is a forager.
            $this->state = 'foraging'; // Add code link later.

            // Don't create accounts here because that is done on ->Create()
            // The instatiation function needs to return a minimum clean false
            // Thing.
            $this->thing = false;

            // Calling constructor with a uuid that doesn't exist,
            // returns false, and with a Thing instantiated.  For tasking.
        } else {
            $this->log("Thing was given a UUID.");

            // Reinstate existing Thing from Stack

            // EXISTING THING IS CONNECTED TO THE STACK.

            // A specific-Uuid has been called by Uuid reference.
            // This section re-creates a Thing and sets scalar either to 0,
            // or to Stack value.

            $this->uuid = $uuid;
            $this->nuuid = substr($this->uuid, 0, 4);

            // Is link to the ->db broken when the Thing is deinstantiated.
            // Assume yes.
            $this->db = new Database($this->uuid, 'null' . $this->mail_postfix);

            $this->log("Thing made a db connector.");

            // Provide handler for Json translation from/to MySQL.
            $this->json = new Json($this->uuid);

            // This is a placeholder for refactoring the Thing variables
            $this->variables = new Json($this->uuid);
            $this->variables->setField("variables");
            $this->log("Thing made a json connector.");

            // Provide handler to support state maps and navigation.
            // And state persistence through de-instantiation/instantiation.
            $this->choice = new Choice($this->uuid, $this->from);
            $this->log("Thing made a choice connector.");

            // Cost of connecting to a Thing is 100 <units>.
            // That is set by the stack variable.  No need to do anything here
            // except load the Things internal balances.
            $this->loadAccounts();
            $this->log("Thing loaded accounts.");

            // Examples:

            //$this->account[$account_name] = new Account($this->uuid, $account_name);
            //$this->account[$account_name]->getBalance(); // Yup.  That easy.

            //$this->account['cost']->getBalance(); // Yup.  That easy.

            //$this->account['run_time'] = new Account($this->uuid, $account_name);
            //$this->account['run_time']->Create(0, 'time', 'seconds');
            //$this->account['run_time']->getBalance(); // Yup.  That easy.

            // Pull the Thing's record from the stack.  Providing
            // $to(stack), $from, $task, $message0-7, ,$variables, $settings
            // $message0-7 not implemented except for development testing.

            $this->Get();

            $this->log("Get call completed.");

            // And fire up the stack balance calculation to make
            // sure stack balance snapshot is latest.

            // Clearly I'm quite keen that a Thing can return the stack balance.
            // But there is an agent to do that.
            //$this->stackBalance();
        }
    }

    function spawn($datagram = null)
    {
        if (strtolower($this->queue_handler) != "gearman") {
            $this->log("No queue handler recognized");
            return true;
        }

        // "Failed to set exception option."
        // Try to catch.

        $client = new \GearmanClient();

        $arr = (array) $client;
        if (!$arr) {
            $this->log("spawn. Job queue not available.");
            // do stuff

            $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $caller = isset($dbt[1]['function']) ? $dbt[1]['function'] : null;
            //$this->log("spawn backtrace " . $caller);
            return true;
        }

        $client->addServer();
        $arr = json_encode($datagram);

        $client->doLowBackground("call_agent", $arr);
        $this->log("spawned a Thing.");
    }

    function getUUid()
    {
        return (string) Uuid::uuid4();
    }

    function Shuffle()
    {
        $this->uuid = $this->getUUid();
        $this->nuuid = substr($this->uuid, 0, 4);

        $this->db->writeField('uuid', $this->uuid);
        $this->Forget();
    }

    function Create($from = null, $to = "", $subject = "")
    {
        if ($from == null) {
            $from = 'null' . $this->mail_postfix;
        }
        $message0 = [];
        $message0['50 words'] = null;
        $message0['500 words'] = null;

        // Now create the new thing
        // I think it is a valid point to redo the '@' check.
        // Though hear we should throw some kind of exception to an agent.
        // Which I suppose would be a message to nonnom.  Saying
        // I found and deleted an @ sign.

        if (strpos($to, "@") !== false) {
            $to = "";
            $message0['50 words'] .=
                $this->uuid . " found and removed an @ sign";
        }

        $this->db = new Database($this->uuid, $from);
        $this->log("Create. Database connector made.");

        // All records are associated with a posterior record.  Ideally
        // one of the two latest records matching the newly created
        // records created_at.

        // Unfortunately this requires another database call to find that
        // record and get the uuid, and write it to the database.

        // If it is called before the Create command, maybe we can save
        // some database calls and writes.
        //$ref_time = microtime(true);
        $this->associatePosterior($from, $to); // 3s 2s 3s
        $this->log("Create. Associated posterior.");

        // Currently timing (27 Feb) at 268 ms 340ms 261ms
        // Currently timing (1 May 2019) at 5-8ms
        //echo number_format((microtime(true)-$ref_time)*1000) . "ms";

        // Is stack set for associating prior records?
        // Associate the new record to the last create record.
        if ($this->container['settings']['stack']['associate_prior'] === true) {
            $this->pushJson('associations', $prior_uuid);
            $this->log("Create. Pushed json associations.");
        }

        // First query after instantiating Database. And after the associate
        // Posterior business.

        // Load newly created values into thing memory
        // Simple insert query to MySql.

        // This seems to create the db entry.
        //Commented out 27 Feb 2018.  And it stopped creating mysql records.
        //$query = $this->db->Create($subject, $to); // 3s

        $query = $this->db->Create($subject, $to); // 3s
        $this->log("Create. Database create call completed.");

        $this->to = $to;
        $this->from = $from;
        $this->subject = $subject;

        // test 9383 30 January 2021
        $this->created_at = time();

        if ($query == true) {
            // will return true if successfull else it will return false

            // This increases the expectation of unreliability.
            // User must expect that logging might not have happened.
            $this->sqlresponse = "New record created successfully.";
            $message0['500 words'] .= $this->sqlresponse;
        } elseif ($query == false) {
            return false;
        } else {
            $error_text = $query->errorInfo();
            $this->sqlresponse = "Error: " . implode(":", $query->error_text());
            $message0['50 words'] .= $this->sqlresponse;
        }

        if ($to == "error") {
            return true;
        }

        if ($query == true) {
            $this->sqlresponse = "New record created successfully.";
            $this->to = $to;
            $this->from = $from;
            $this->subject = $subject;
            $message0['500 words'] .= $this->sqlresponse;
        } else {
            //$error = $query->errorInfo();
            //$this->sqlresponse = "Error: " . $sql . "<br>" . $query->errorInfo();
            $this->sqlresponse = "Error: " . implode(":", $query->errorInfo());
            $message0['50 words'] .= $this->sqlresponse;
            return false;
        }

        // Create new accounts.  Still under development as of 25 April.
        // Credit and debit records testing pass.

        // Here we create an array to call named accounts on.
        // Each account has a 'nickname'.  A mechanism that prevents
        // similar named accounts
        // or as current behaviour (anticipated) overrides account
        // information with newly presented information.

        // Which means the stack can reset a Things balance.  Handy.

        $this->account = [];

        // Kind of ugly.  But I guess this isn't Python.  And null
        // accounts can't be allowed.

        if ($this->stack_account != null) {
            $this->newAccount(
                $this->stack_uuid,
                $this->stack_account['account_name'],
                $this->stack_account['balance']
            );
        }

        if ($this->thing_account != null) {
            $this->newAccount(
                $this->uuid,
                $this->thing_account['account_name'],
                $this->thing_account['balance']
            );
        }

        // No need to save accounts here, as all we have done
        // is load them into this Thing
        // from the settings files and from variables.

        // But we do need to calculate the stack balance.
        // $this->thing_account['account_name']->balance['amount'] will
        // return a scalar amount.  Thing balances.  Amount
        // this Thing owes or is owed by other Things.

        // Calling ->db->UUids() will provide the corresponding UUids.
        // Which can then be polled (later?) to provide the Stack balances.
        // The sum of the balances of all uuids which have records
        // corresponding to $this->uuid.

        // Commented out 27 Feb 2018.  Seems unnecessary.
        ///		$thingreport = $this->db->UUids(); // Designed to accept null as $this->uuid.

        ///		$things = $thingreport['things'];

        //$this->stackBalance();

        $this->log("Create completed.");
        $this->log("Now called Get. (again?)");

        return $this->Get();
    }

    public function newAccount($account_uuid, $account_name, $balance = null)
    {
        if ($account_uuid == null or $account_name == null) {
            return true;
        }

        if ($balance == null) {
            $balance['amount'] = 0;
        }

        if (!isset($this->account)) {
            $this->account = [];
        }

        $this->account[$account_name] = new Account(
            $this->uuid,
            $account_uuid,
            $account_name
        );
        $this->account[$account_name]->Create($balance);

        return false;
    }

    public function loadAccounts()
    {
        $this->json->setField("variables");

        $accounts = $this->json->readVariable(["account"]);

        // At this point we have a PHP array of all accounts on
        // this Thing.

        // This means that we can generate the thing and stack balance now.
        // And set-up all Thing accounts.

        if ($accounts == null) {
            return false;
        }
        foreach ($accounts as $uuid => $account) {
            foreach ($account as $account_name => $balance) {
                if ($uuid == 'stack' or $uuid == 'thing') {
                    //    corrupted account list
                    return true;
                }
                $this->newAccount($uuid, $account_name, $balance);
            }
        }
    }

    function stackBalance()
    {
        // Query stack for matching uuid and nom_from

        // "WORK ON STACK BALANCE";

        $thingreport = $this->db->UUids(); // Designed to accept null as $this->uuid.

        $things = $thingreport['things'];

        if ($things == null or $things == []) {
            return false;
        }

        // Should have an array... which could be presumptuous.
        if (!is_array($things)) {
            return false;
        }

        if (!isset($this->from)) {
            return false;
        }

        // Okay pretty sure we can do this now.
        $thingreport = $this->db->UUids($account_uuid);
    }

    function time($time = null)
    {
        if ($time == null) {
            $time = time();
        }
        $this->time = gmdate("Y-m-d\TH:i:s\Z", $time);

        return $this->time;
    }

    function microtime($microtime = null)
    {
        if ($microtime == null) {
            $microtime = microtime();
        }
        list($usec, $sec) = explode(' ', $microtime);

        $this->microtime = date('Y-m-d H:i:s', $sec) . " " . $usec;

        return $this->microtime;
    }

    public function test($variable = null, $agent = null, $action = null)
    {
        if ($agent == null) {
            $agent = "null";
        }
        if ($action == null) {
            $action = "did something with";
        }
        // Keep it simple for now.

        //		echo '<pre> Agent "'.$agent.'" ' . $action . ' this Thing at ';print_r($variable);echo'</pre>';
    }

    public function getVariable($variable_set, $variable)
    {
        if (!isset($this->account['stack'])) {
            return true;
        }

        $variables = $this->account['stack']->json->array_data;

        if (isset($variables[$variable_set])) {
            $this->$variable_set = (object) [$variables[$variable_set]][0];

            if (!isset($this->$variable_set->$variable)) {
                $this->$variable_set->$variable = false; //Not found
            }

            return $this->$variable_set->$variable;
        }

        return false;
    }

    public function Forget()
    {
        $this->log("Thing Forget started.");

        // Call to account destruction.  Both for DB and stack account.
        // And the Thing.

        // To be developed.  Stack account destruction.
        // $this->account['scalar']->Destroy(100, '<not set>', '<not set>');

        // Current behaviour:
        // Stack account value is destroyed on deinstantiation of the Thing.
        // at a net cost to Stack of 0.

        // Planned behaviour:
        // Stack account value is distributed within defined groups.
        // $thingreport = $this->db->Uuids();

        // For now don't do this.  Just forget the record as quickly as possibly.
        // Stack Engine No.1 7ms 10ms 8ms

        // Call Db and forget the record.

        if (!isset($this->db)) {
            return;
        }
        $thingreport = $this->db->Forget($this->uuid);
    }

    public function Ignore()
    {
        $this->json->setField("variables");
        $this->json->writeVariable(["thing", "status"], "green");
        $this->Get();
    }

    public function flagRed()
    {
        // Make the Thing show Red
        $this->json->setField("variables");
        $this->json->writeVariable(["thing", "status"], "red");
        $this->Get();
    }

    public function silenceOn()
    {
        // Make the Thing show Red
        $this->json->setField("variables");
        $this->json->writeVariable(["thing", "silence"], "on");
        $this->Get();
    }

    public function silenceOff()
    {
        // Make the Thing show Red
        $this->json->setField("variables");
        $this->json->writeVariable(["thing", "silence"], "off");
        $this->Get();
    }

    public function isSilent()
    {
        // Ask if the Thing is Green
        $var_path = ["thing", "silence"];
        if ($this->json->readVariable($var_path) == "on") {
            return true;
        }
        return false;
    }

    public function flagAmber()
    {
        // Make the Thing show Amber
        $this->json->setField("variables");
        $this->json->writeVariable(["thing", "status"], "amber");
        $this->Get();
    }

    public function flagGreen()
    {
        // Make the Thing show Green
        $this->json->setField("variables");
        $this->json->writeVariable(["thing", "status"], "green");
        $this->Get();
    }

    public function isRed()
    {
        // Ask if the Thing is Red
        $var_path = ["thing", "status"];
        if ($this->json->readVariable($var_path) == "red") {
            return true;
        }
        return false;
    }

    public function isGreen()
    {
        // Ask if the Thing is Green
        $var_path = ["thing", "status"];
        if ($this->json->readVariable($var_path) == "green") {
            return true;
        }
        return false;
    }

    // Yeah - it's amber.  Cycles red > red + amber > green > amber > red
    public function isAmber()
    {
        // Ask if the Thing is Amber.  Is it ready to go?
        $var_path = ["thing", "status"];
        if ($this->json->readVariable($var_path) == "amber") {
            return true;
        }
        return false;
    }

    function isData($variable)
    {
        // Ask if the Thing carries data.
        // Thing has four ways to answer.

        if (
            $variable !== false and
            $variable !== true and
            $variable !== null and
            $variable !== ""
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function flagGet()
    {
        // More open way to ask a thing for its flag
        $var_path = ["thing", "status"];
        return $this->json->readVariable($var_path);
    }

    public function flagSet($color = null)
    {
        // And to tell a thing to set its flag to a particular one
        if ($color == null) {
            $color = 'red';
        }

        $this->json->setField("variables");
        $this->json->writeVariable(["thing", "status"], $color);
        $this->Get();
    }

    public function Get()
    {
        $this->log("Thing Get started.");

        // Bootstrapping db access.
        // A Thing can call an UUID so called up
        // the requested UUID.  Using the null account.
/*
if (isset($this->db)) {
        $hash_nom_from = hash($this->hash_algorithm, $this->from);

$prior_uuid = $this->db->getMemory($hash_nom_from);
echo "Previous uuid got " . ($prior_uuid) . "\n";
}
*/


$thing = false;
if (isset($this->db)) {
        $thingreport = $this->db->Get($this->uuid);
        $thing = $thingreport['thing'];
}

        $this->log("loaded thing " . $this->nuuid . " from db.");

        if ($thing == false) {
            //$this->uuid = $this->thing->uuid;
            $this->to = null;
            $this->from = null;
            $this->subject = null;
        } else {
            // This just makes sure these four variables
            // are consistently available
            // as top level Thing objects.
            //$this->uuid = $this->thing->uuid;
            $this->to = $thing->nom_to;
            $this->from = $thing->nom_from;

            // One of these looks promising.

            //$thingreport = $this->db->setUser($this->from);
            //$thingreport = $this->db->from = $this->from;

            $this->subject = $thing->task;
        }

        $this->thing = $thing;
/*
if ((isset($this->db)) and (!isset($this->prior_uuid))) {
        $hash_nom_from = hash($this->hash_algorithm, $this->from);
$this->prior_uuid = $this->db->getMemory($hash_nom_from);
}


if ((isset($this->db)) and (!isset($this->flag_set_uuid))) {
        $hash_nom_from = hash($this->hash_algorithm, $this->from);

$prior_uuid = $this->db->setMemory($hash_nom_from, $this->uuid);
$log_text = "wrote key:value " . $hash_nom_from . " " . $this->uuid;
//echo $log_text . "\n";
$this->log($log_text);
$this->flag_set_uuid = true;
}
*/

        // Once the Thing has been pulled we can update the db connector
        // to use the current from.

        //		$this->db->setFrom($this->from);

        //$this->db = new Database($this->uuid, $this->from);

        //$thingreport = $this->db->setUser($this->from);

        // Uncommenting any of the above options results in non-working uuids

        // Commented out to allow PNG to work

        // Keep for debugging.  Working as of 5 April 2017.  And passing all tests
        // 25 Apr, except Test 4: Test posterior association.
        // echo '<pre>  db.php Get()'; print_r($this->thing); echo '</pre>';

        return $thing;
    }

    function isPositive($str)
    {
        return is_numeric($str) && $str > 0 && $str == round($str);
    }

    public function readSubject()
    {
        return false;
    }

    function getState($agent = null)
    {
        // This can probably be deprecated after updating usermanager
        //echo "agent provided";$agent;

        if ($agent == null) {
            $agent = 'thing';
        }
        // Need to find latest record with a usermanager state in it for $from.

        // LET'S START HERE
        // Have we dealt with this nom_from before?
        // Get the latest 3 usermanager interactions.

        $thingreport = $this->db->agentSearch($agent, 3); // Get newest

        $things = $thingreport['things'];

        $states = [];
        foreach ($things as $thing) {
            $uuid = $thing['uuid'];

            $thing = new Thing($uuid);
            // append to states

            $t = $thing->choice->load($agent);

            if (is_array($t)) {
                // unexpected
                $t = true;
            }
            $states[] = $t;
        }

        if ($states == []) {
            return $this->current_state = null;
        }

        if (array_key_exists(1, $states)) {
            // Then this isn't the only one...
            return $this->current_state = $states[1];
        } else {
            return $this->current_state = $states[0];
        }
        return false;
    }

    function associatePosterior($nom_from, $nom_to)
    {
        // Get the UUID of the last entry in the db with
        // the same planned $to email address.

        // This is likely to be a pretty intensive call.
        // It search the db for the most recent last record.

        // Factored out one call (a new Thing instantiation)
        // to the database.  26 Apr.  Got to be worth something.
        // Apparently enough to get rid of Too many connections in test_account.php
        // Passing test_redpanda.php 26 Apr.
        $thingreport = $this->db->priorGet(); // 3s

        $this->log("associatePosterior. PriorGet database call completed.");
        $posterior_thing = $thingreport['thing'];

        if ($posterior_thing != false) {
            // Check stack settings and associate previous record with new
            // record if true.  Previous record updated to point to new record.

            if ($this->associate_posterior === true) {
                $posterior_thing->json = new Json($posterior_thing->uuid);
                $posterior_thing->json->setField("associations");
                $posterior_thing->json->pushStream($this->uuid);
                //Tested with unset and commented out
                //doesn't seem to improve (at least) the
                //too many connection issue.  Leave it in for
                //the time being.  25 Apr.
                //unset($posterior_thing);
                $this->log("Associated posterior thing.");
            }
            return;
            //		return 'Posterior uuid ' . $posterior_thing->uuid .
            //				' associated with Thing uuid ' . $this->uuid;
        }

        $this->log("Posterior thing is false");
    }

    function associate($uuids = null, $mode = "default")
    {
        if ($uuids == null) {
            return false;
        }

        if (is_string($uuids)) {
            $uuids = [$uuids];
        }

        if (is_array($uuids)) {
            $current_field = $this->json->field;
            $this->json->setField("associations");

            foreach ($uuids as $uuid) {
                //$this->json->setField("associations");
                //$this->json->pushStream($uuid);
                if ($mode == "default") {
                    $this->json->pushStream($uuid);
                } else {
                    $this->json->fallingWater($uuid);
                }
            }
            $this->json->setField($current_field);

            return false;
        }
        return true;
    }

    function has($text = null)
    {
        if (stripos($this->subject, $text) !== false) {
            return true;
        }

        return false;
    }

    public function console($text = null)
    {
        //$this->c_output = "off";
        //if (isset($this->container['stack']['console_output'])) {
        //        $this->console_output = $this->container['stack']['console_output'];
        //}

        if (!isset($this->console_output)) {
            return;
        }
        if ($this->console_output != 'on') {
            return;
        }

        echo $text;
    }

    function log($text = null, $logging_level = null)
    {
        if ($text == null) {
            return $this->log_last;
        }

        if (!isset($this->log)) {
            $this->log = "\n";
        }
        // DEBUG, INFORMATION, WARNING, ERROR, FATAL
        // Plus OPTIMIZE

        if ($logging_level == null) {
            $logging_level = "INFORMATION";
            if (isset($this->logging_level_default)) {
                $logging_level = $this->logging_level_default; // If message isn't specific - assume WARNING
            }
            //if (isset($this->logging_level)) {$logging_level = $this->logging_level;}
        }

        //get the calling class

        // Causing a segmentation fault?
        //    72.1589   76828520
        //   -> debug_backtrace() /var/www/stackr.test/vendor/nrwtaylor/stack-agent-thing/src/Thing.php:1020

        // Adjusted PHP7.4 CLI dev memory limit. Test

        //        $trace = debug_backtrace();
        //        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $trace = debug_backtrace(false, 2);

        // Get the class that is asking for who awoke it
        $class_name = "X";
        if (isset($trace[1]['class'])) {
            $class_namespace = $trace[1]['class'];
            $class_name_array = explode("\\", $class_namespace);
            $class_name = end($class_name_array);
        }

        $runtime = number_format($this->elapsed_runtime()) . "ms";

        $text = strip_tags($text);
        $agent_prefix = 'Agent "' . ucwords($class_name) . '"';

        $text = str_replace($agent_prefix, "", $text);

        $text = lcfirst($text);
        $text = trim($text);
        $t =
            str_pad($runtime, 10, " ", STR_PAD_LEFT) .
            " " .
            $agent_prefix .
            ' ' .
            strip_tags($text);

        $this->log .= $t . " [" . $logging_level . "]" . "<br>";

        if (isset($this->logging_console)) {
            switch (strtoupper($this->logging_console)) {
                case "ON":
                    $this->console($t . " [" . $logging_level . "]" . "\n");
                    break;
                case "OPTIMIZE":
                case "FATAL":
                case "ERROR":
                case "WARNING":
                case "INFORMATION":
                case "DEBUG":
                    if (
                        strtoupper($logging_level) ===
                        strtoupper($this->logging_console)
                    ) {
                        $this->console($t . " [" . $logging_level . "]" . "\n");
                        break;
                    }
                default:
                //echo "i is not equal to 0, 1 or 2";
            }
        }
        $this->log_last = $t;
    }

    function elapsed_runtime()
    {
        if (!isset($this->start_time)) {
            $this->start_time = microtime(true);
        }

        $run_time = microtime(true) - $this->start_time;
        return round($run_time * 1000);
    }

    function human_time($ptime)
    {
        //$etime = time() - $ptime;
        $etime = $ptime;

        if ($etime < 1) {
            return '0 seconds';
        }

        $a = [
            10 * 365 * 24 * 60 * 60 => 'decade',
            365 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60 => 'month',
            7 * 24 * 60 * 60 => 'week',
            24 * 60 * 60 => 'day',
            60 * 60 => 'hour',
            60 => 'minute',
            1 => 'second',
        ];
        $a_plural = [
            'decade' => 'decade',
            'year' => 'years',
            'month' => 'months',
            'week' => 'weeks',
            'day' => 'days',
            'hour' => 'hours',
            'minute' => 'minutes',
            'second' => 'seconds',
        ];

        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);
                return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . '';
            }
        }
    }
    //}

    function exception_error_handler($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}
