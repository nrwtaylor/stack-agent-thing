<?php
namespace Nrwtaylor\StackAgentThing;

// Refactor to use GLOBAL variable
require "/var/www/stackr.test/vendor/autoload.php";

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

//register_shutdown_function('shutdown');

echo "Worker whitefox 27 August 2021\n";
echo "Gearman Worker started\n";
$worker = new \GearmanWorker();

$worker->addServer();
$uuid = null;
$name = "call_agent";
$task = "Nrwtaylor\StackAgentThing\call_agent_function";

$worker->addFunction(
    $name,
    function () use ($task) {
// https://vancelucas.com/blog/handling-exceptions-in-gearman-tasks-even-background-ones/
//         set_error_handler(
//               'worker_fatal_handler',
//               E_FATAL
//           );

        try {
            $result = call_user_func_array($task, func_get_args());
        } catch (\Exception $e) {
            $result = GEARMAN_WORK_EXCEPTION;
            echo "Gearman: CAUGHT EXCEPTION: " . $e->getMessage();
            // Send exception to Exceptional so it can be logged with details
            // Exceptional::handle_exception($e, false);
            file_put_contents('/tmp/test.log', $e->getMessage(), FILE_APPEND);
        }

//            restore_error_handler();

        return $result;
    },
    $uuid
);

$name = "call_agent_welfare";
$task = "Nrwtaylor\StackAgentThing\call_agent_function";

$worker->addFunction(
    $name,
    function () use ($task) {
// https://vancelucas.com/blog/handling-exceptions-in-gearman-tasks-even-background-ones/
//         set_error_handler(
//               'worker_fatal_handler',
//               E_FATAL
//           );

        try {
            $result = call_user_func_array($task, func_get_args());
        } catch (\Exception $e) {
            $result = GEARMAN_WORK_EXCEPTION;
            echo "Gearman: CAUGHT EXCEPTION: " . $e->getMessage();
            // Send exception to Exceptional so it can be logged with details
            // Exceptional::handle_exception($e, false);
            file_put_contents('/tmp/test.log', $e->getMessage(), FILE_APPEND);
        }

//            restore_error_handler();

        return $result;
    },
    $uuid
);

$name = "call_agent_routine";

$worker->addFunction(
    $name,
    function () use ($task) {
// https://vancelucas.com/blog/handling-exceptions-in-gearman-tasks-even-background-ones/
//         set_error_handler(
//               'worker_fatal_handler',
//               E_FATAL
//           );

        try {
            $result = call_user_func_array($task, func_get_args());
        } catch (\Exception $e) {
            $result = GEARMAN_WORK_EXCEPTION;
            echo "Gearman: CAUGHT EXCEPTION: " . $e->getMessage();
            // Send exception to Exceptional so it can be logged with details
            // Exceptional::handle_exception($e, false);
            file_put_contents('/tmp/test.log', $e->getMessage(), FILE_APPEND);
        }

//            restore_error_handler();

        return $result;
    },
    $uuid
);

$name = "call_agent_priority";

$worker->addFunction(
    $name,
    function () use ($task) {
// https://vancelucas.com/blog/handling-exceptions-in-gearman-tasks-even-background-ones/
//         set_error_handler(
//               'worker_fatal_handler',
//               E_FATAL
//           );

        try {
            $result = call_user_func_array($task, func_get_args());
        } catch (\Exception $e) {
            $result = GEARMAN_WORK_EXCEPTION;
            echo "Gearman: CAUGHT EXCEPTION: " . $e->getMessage();
            // Send exception to Exceptional so it can be logged with details
            // Exceptional::handle_exception($e, false);
            file_put_contents('/tmp/test.log', $e->getMessage(), FILE_APPEND);
        }

//            restore_error_handler();

        return $result;
    },
    $uuid
);

$name = "call_agent_emergency";

$worker->addFunction(
    $name,
    function () use ($task) {
// https://vancelucas.com/blog/handling-exceptions-in-gearman-tasks-even-background-ones/
//         set_error_handler(
//               'worker_fatal_handler',
//               E_FATAL
//           );

        try {
            $result = call_user_func_array($task, func_get_args());
        } catch (\Exception $e) {
            $result = GEARMAN_WORK_EXCEPTION;
            echo "Gearman: CAUGHT EXCEPTION: " . $e->getMessage();
            // Send exception to Exceptional so it can be logged with details
            // Exceptional::handle_exception($e, false);
            file_put_contents('/tmp/test.log', $e->getMessage(), FILE_APPEND);
        }

//            restore_error_handler();

        return $result;
    },
    $uuid
);


// This would limit the length of any one worker.
// This is handled by supervisor
//$worker->setTimeout(1000);

while ($worker->work()) {

    if ($worker->returnCode() != GEARMAN_SUCCESS) {
        echo "worker unsuccessful [" . $worker->returnCode() . "]\n";
    }

    echo "\n";
    echo "worker waiting for a job\n";
}

function call_agent_function($job)
{
    echo "worker received job workload " . $job->workload() . "\n";
    $arr = json_decode($job->workload(), true);

    $agent_input = null;
    if (isset($arr["agent_input"])) {
        $agent_input = $arr["agent_input"];
    }

    if (isset($arr["uuid"])) {
        echo "worker found uuid\n";
        $thing = new Thing($arr["uuid"], $agent_input);
        $start_time = $thing->elapsed_runtime();
    } else {
        echo "worker found message\n";
        $thing = new \Nrwtaylor\StackAgentThing\Thing(null, $agent_input);
        $start_time = $thing->elapsed_runtime();
        $thing->Create($arr["to"], $arr["from"], $arr["subject"]);
    }

    if ($thing->thing == false) {
        echo "Thing is false";
        //return true;
    }
    echo "thing nuuid " . $thing->nuuid . "\n";
    echo "thing uuid " . $thing->uuid . "\n";
    echo "worker timestamp " . $thing->microtime() . "\n";
    echo "thing subject " . $thing->subject . "\n";


    $age = true;
    if (isset($thing->thing->created_at)) {
        echo "thing timestamp " . $thing->thing->created_at . "\n";
        $age = strtotime($start_time) - strtotime($thing->thing->created_at);
    }

    $agent_input_text = "?";
    if (is_string($agent_input)) {
        $agent_input_text = $agent_input;
    }
    echo "agent input " . $agent_input_text . "\n";

    $do_not_respond = false;

    // Exploring here to see how long it has been waiting.
    // I don't see a call to get the task age from Gearman.
    // So this will show the age a a uuid retrieved from the stack.
    // Or that a new thing was created.
    echo "thing age is " . $thing->human_time($age) . " ago.\n";

    if (isset($arr["body"]["messageId"])) {
        $message_id = $arr["body"]["messageId"];

        $m = $thing->db->variableSearch(null, $message_id);

        var_dump(count($m["things"]));
        if (count($m["things"]) > 0) {
            echo "Found message already.";
            $do_not_respond = true;
        }

        new Messageidentifier($thing, $message_id);
    }
    $thing->db->setFrom($thing->from);

    $thing->json->setField("message0");
    $thing->json->writeVariable(["msg"], $arr);

    if ($do_not_respond == false) {
        echo "worker call agent\n";
        $t = new Agent($thing);
    }

    if (!isset($t->thing_report["sms"])) {
        echo "WORKER | No SMS message found." . "\n";
    } else {
        echo $t->thing_report["sms"] . "\n";
    }

    if (isset($t->thing_report["response"])) {
        echo "response " . $t->thing_report["response"] . "\n";
    }

    // Gearman can't pass a raw image variable
    // Needs to be base64 encoded first
    // Devstask PNG (to convert $image to PNG)
    if (isset($t->thing_report["png"])) {
        $t->thing_report["png"] = base64_encode($t->thing_report["png"]);
    }

    $t->thing_report["png"] = null;
    $t->thing_report["pdf"] = null;

    // Not needed either.
    $t->thing_report["thing"] = null;

    echo "worker ran for " .
        number_format($thing->elapsed_runtime() - $start_time) .
        "ms\n";

    $json = json_encode(
        $t->thing_report,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    return $json;
}

function worker_fatal_handler($errno, $errstr, $errfile, $errline, $errContext)
{
    //throw new \Exception('Class not found.');
    //trigger_error("Fatal error", E_USER_ERROR);
    var_dump($errno);
    var_dump($errstr);
}

?>
