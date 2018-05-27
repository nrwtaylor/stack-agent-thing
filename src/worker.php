<?php
//namespace Nrwtaylor\Stackr;
//require_once '/var/www/html/stackr.ca/agents/agent.php';
$worker = new \GearmanWorker();
$worker->addServer();
$uuid = null;
$worker->addFunction("call_agent", "call_agent_function", $uuid);

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
//var_dump($arr);
        $thing = new Thing(null);
        $start_time = $thing->elapsed_runtime();
        $thing->Create($arr['to'], $arr['from'], $arr['subject'] );
    }
//$thing->flagRed();

    //$thing = new Thing($uuid);
echo "worker call agent\n";
    $t = new Agent($thing);

echo "worker completed " . number_format($thing->elapsed_runtime() - $start_time) . "ms\n\n";

//    $t->thing_report;
  return json_encode($t->thing_report);
}

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

