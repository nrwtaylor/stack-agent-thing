<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';


//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


echo "test_thing.php";





echo "<br>";
echo "Test 1: Test false thing.";


$thing1 = new Thing(false);
$t = new Ping($thing1);

var_dump($t->sms_message);

//$thing1->Create("test@stackr.ca", "test", "Test: Embed uuid's in subject - " . $thing1->uuid . " at " . time());

