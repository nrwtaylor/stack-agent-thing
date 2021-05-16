<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';


//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


echo "test_thing.php";





echo "<br>";
echo "Test 1: Test UUID create memory.";


$thing1 = new Thing(false);
$t = new Memory($thing1);
$t->setMemory("5cf1d20e-64ed-44a3-8232-fe96e07dca5f","merp");
var_dump($t->sms_message);

echo "<br>";
echo "Test 2: Test null create memory.";


$thing1 = new Thing(false);
$t = new Memory($thing1);
$t->setMemory(null,"merp");
var_dump($t->sms_message);


//$thing1->Create("test@stackr.ca", "test", "Test: Embed uuid's in subject - " . $thing1->uuid . " at " . time());

