<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';


//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


echo "test_thing.php";





echo "<br>";
echo "Test 1: Test UUID create mongo.";


$thing1 = new Thing(null);
$thing1->Create("derp","earp","flerp");

$t = new Mongo($thing1);
$t->setMongo("5cf1d20e-64ed-44a3-8232-fe96e07dca5f",["merp" => "merp"]);
var_dump($t->sms_message);

echo "<br>";
echo "Test 2: Test null create key.";


$thing1 = new Thing(null);
$thing1->Create("derp","earp","flerp");
$t = new Mongo($thing1);
$t->setMongo(null,["foo" => "bar"]);
var_dump($t->sms_message);

