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
$worker->addFunction("call_agent", "Nrwtaylor\StackAgentThing\call_agent_function", $uuid);

while ($worker->work());

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

    echo "worker timestamp " . $thing->microtime(). "\n";
    //$thing = new Thing($uuid);
    echo "worker call agent\n";
    $t = new Agent($thing);

    echo "worker ran for " . number_format($thing->elapsed_runtime() - $start_time) . "ms\n\n";

    return json_encode($t->thing_report);
}
/*
function call_agent_function_old($job)
{

var_dump($job->workload());

    $uuid = $job->workload();

    //$thing = (object) json_decode($thing);//
    $thing = new Thing($uuid);
    $thing->flagRed();
    //$t->Create("from","to","gearman test");

    $t = new Agent($thing);

//    $t->thing_report;
  return json_encode($t->thing_report);
}
*/
?>
