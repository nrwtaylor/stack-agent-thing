<?php

namespace Nrwtaylor\StackAgentThing;
require './vendor/autoload.php';

echo "test_abc.php";





$thing = new Thing(null);
$thing->Create("null@stackr.ca", "chooser", "spawn");



$abc_agent = new Cat($thing);

//var_dump($abc_agent->thing_report);
echo $abc_agent->thing_report['sms'];
?>
