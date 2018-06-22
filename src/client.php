<?php
namespace Nrwtaylor\StackAgentThing;
// Create our client object
$client = new \GearmanClient();

$arr = json_encode(array("to"=>"test", "from"=>"client", "subject"=>"latency"));

// Add a server
//$client->addServer(); // by default host/port will be "localhost" & 4730
$client->addServer(); // by default host/port will be "localhost" & 4730

echo "Gearman. Sending job\n";

// Send reverse job
$result = $client->doNormal("call_agent", $arr);
if ($result) {
  echo "Success: $result\n";
}


