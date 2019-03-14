<?php

namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';

//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_db.php";


echo "<br>";
echo "Test 1: Test priorGet with two items";


$thing = new Thing(null);
$thing->Create("test@stackr.ca", "associator", "test_db test 1 A new thing 1 at" . time());

$thing->json->setField("variables");


// add, get, set, delete

$thing->json->writeVariable(array("a","b"), 123.01);
$thing->json->writeVariable(array("c","d"), 345.02);
$thing->json->writeVariable(array("a","c"), 111.22);

$thing->json->writeVariable(array("a","b"), 0.123);

$thing->json->writeVariable(array("a"), 456.123);

echo $thing->json->readVariable(array("a","b"));



$thing->Get();


	echo '<pre> thingtest.php $thing->thing: '; print_r($thing->thing); echo '</pre>';



//if($expected_result ==$thing->json->json_data) {
//	echo "Pass";
//	} else {
//	echo "Fail";
//	}



?>
