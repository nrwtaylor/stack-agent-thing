<?php
namespace Nrwtaylor\StackAgentThing;

// Refactor to use GLOBAL variable
require '/var/www/stackr.test/vendor/autoload.php';

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "Worker whitefox 8 September 2020\n";
echo "Gearman Worker started\n";
$worker = new \GearmanWorker();
$worker->addServer();
$uuid = null;
$name = "call_agent";
$task = "Nrwtaylor\StackAgentThing\call_agent_function";
$worker->addFunction(
    "call_agent",
    "Nrwtaylor\StackAgentThing\call_agent_function",
    $uuid
);

/*
$worker->addFunction($name, function() use($task) {
     try {
         $result = call_user_func_array($task, func_get_args());
     } catch(\Exception $e) {
         $result = GEARMAN_WORK_EXCEPTION;
         echo "Gearman: CAUGHT EXCEPTION: " . $e->getMessage();
         // Send exception to Exceptional so it can be logged with details
         Exceptional::handle_exception($e, FALSE);
     }

     return $result;
});
*/

// This would limit the length of any one worker.
// This is handled by supervisor
//$worker->setTimeout(1000);

while ($worker->work()) {
    echo "\nWaiting for a job\n";

//      if ($worker->returnCode() != GEARMAN_SUCCESS)
//      {
//        echo "return_code: " . $worker->returnCode() . "\n";
//      }
//        echo "\nGearman return code " . $worker->returnCode() . "\n";

}

function call_agent_function($job)
{
    echo "worker received job workload " . $job->workload() . "\n";
    $arr = json_decode($job->workload(), true);

    $agent_input = null;
    if (isset($arr['agent_input'])) {
        $agent_input = $arr['agent_input'];
    }

    if (isset($arr['uuid'])) {
        echo "worker found uuid\n";
        $thing = new Thing($arr['uuid'], $agent_input);
        $start_time = $thing->elapsed_runtime();
    } else {
        echo "worker found message\n";
        $thing = new \Nrwtaylor\StackAgentThing\Thing(null, $agent_input);
        $start_time = $thing->elapsed_runtime();
        $thing->Create($arr['to'], $arr['from'], $arr['subject']);
    }

    if ($thing->thing == false) {
        echo "Thing is false";
        return true;
    }
    echo "worker nuuid " . $thing->nuuid . "\n";
    echo "worker uuid " . $thing->uuid . "\n";
    echo "worker timestamp " . $thing->microtime() . "\n";
    echo "job timestamp " . $thing->thing->created_at . "\n";

    echo "agent input" . $agent_input . "\n";

    $do_not_respond = false;
    if (isset($arr['body']['messageId'])) {
        $message_id = $arr['body']['messageId'];

        $m = $thing->db->variableSearch(null, $message_id);

        var_dump(count($m['things']));
        if (count($m['things']) > 0) {
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

    if (!isset($t->thing_report['sms'])) {
        echo "WORKER | No SMS message found." . "\n";
    } else {
        echo $t->thing_report['sms'] . "\n";
    }

    // Gearman can't pass a raw image variable
    // Needs to be base64 encoded first
    // Devstask PNG (to convert $image to PNG)
    if (isset($t->thing_report['png'])) {
        $t->thing_report['png'] = base64_encode($t->thing_report['png']);
    }

    $t->thing_report['png'] = null;
    $t->thing_report['pdf'] = null;

    // Not needed either.
    $t->thing_report['thing'] = null;

    echo "worker ran for " .
        number_format($thing->elapsed_runtime() - $start_time) .
        "ms\n\n";

    $json = json_encode(
        $t->thing_report,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    return $json;

    //    return json_encode($t->thing_report);
}
?>
