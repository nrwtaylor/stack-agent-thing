<?php
namespace Nrwtaylor\StackAgentThing;

// Refactor to use GLOBAL variable
//require $GLOBALS['stack_path'] . "vendor/autoload.php";
require '/var/www/stackr.test/vendor/autoload.php';

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "Worker redpanda 15 June 2018\n";
echo "Gearman Worker started\n";
$worker = new \GearmanWorker();
$worker->addServer();
$uuid = null;
$name = "call_agent";
$task = "Nrwtaylor\StackAgentThing\call_agent_function";
$worker->addFunction("call_agent", "Nrwtaylor\StackAgentThing\call_agent_function", $uuid);

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

while ($worker->work()){
    echo "\nWaiting for a job\n";

//  if ($worker->returnCode() != GEARMAN_SUCCESS)
//  {
//    echo "return_code: " . $worker->returnCode() . "\n";
//  }
//    echo "\nGearman return code " . $worker->returnCode() . "\n";

}

function call_agent_function($job)
{
    echo "worker received job workload "  . $job->workload() . "\n";
    $arr = json_decode($job->workload(),true);

    if (isset($arr['uuid'])) {
        echo "worker found uuid\n";
        $thing = new Thing($arr['uuid']);
        $start_time = $thing->elapsed_runtime();
    } else {
        echo "worker found message\n";
        $thing = new \Nrwtaylor\StackAgentThing\Thing(null);
        $start_time = $thing->elapsed_runtime();
        $thing->Create($arr['to'], $arr['from'], $arr['subject'] );
    }

    echo "worker nuuid " . $thing->nuuid."\n";
    echo "worker uuid " . $thing->uuid."\n";
    echo "worker timestamp " . $thing->microtime(). "\n";
    echo "job timestamp " . $thing->thing->created_at. "\n";

    //$thing = new Thing($uuid);
    echo "worker call agent\n";
    $t = new Agent($thing);

    if (!isset($t->thing_report['sms'])) {
        echo "WORKER | No SMS message found.". "\n";
    } else {
        echo $t->thing_report['sms'] . "\n";
    }

    // Gearman can't pass a raw image variable
    // Needs to be base64 encoded first
    // Devstask PNG (to convert $image to PNG)
    if (isset($t->thing_report['png'])) {
        $t->thing_report['png'] = base64_encode($t->thing_report['png']);
    }

    echo "worker ran for " . number_format($thing->elapsed_runtime() - $start_time) . "ms\n\n";

    return json_encode($t->thing_report);
}
?>
