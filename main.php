<?php

namespace Nrwtaylor\StackAgentThing;
require './vendor/autoload.php';

echo "test_abc.php";





$thing = new Thing(null);
$thing->Create("null@stackr.ca", "cat", "cat");



$abc_agent = new Agent($thing);

//var_dump($abc_agent->thing_report);
echo $abc_agent->thing_report['sms'];

$abc_agent = new Dog($thing);
echo $abc_agent->thing_report['sms'];


?>
