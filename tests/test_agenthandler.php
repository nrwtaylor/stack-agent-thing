<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';

//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_redpanda.php";


echo "<br>";
echo "Test 1: Test create redpanda agent";

echo "foo";
include "../src/emailhandler.php";
echo "bar";

include "../src/agenthandler.php";


exit();



	echo '<pre> new Thing(null)'; echo '</pre>';
$thing = new Thing(null);
	echo '<pre> makeThing(...)'; echo '</pre>';
$thing->Create("null@stackr.ca", "redpanda", "Hey redpanda at" . time());
	echo '<pre> Get()'; echo '</pre>';


//$thing->Get($thing->uuid);
	echo '<pre> new Redpanda($thing)'; echo '</pre>';
	echo '<pre> new Redpanda($thing)'; echo print_r($thing->thing); echo '</pre>';

$redpanda = new Redpanda($thing);


if($thing->uuid == $redpanda->uuid) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2 %thingreport: '; print_r($thingreport); echo '</pre>';

	}





?>
