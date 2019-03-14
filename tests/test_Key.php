<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';


echo '<pre> test_key.php started running on Thing ';echo date("Y-m-d H:i:s");echo'</pre>';
//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_key.php";


echo "<br>";
echo "Test 1: Test create Key agent";



$thing = new Thing(null);

$test_email = $thing->container['stack']['email'];
echo $test_email;

$thing->Create($test_email, "key", "Hey key at " . date("Y-m-d H:i:s") );
	echo '<pre> Thing '.$thing->uuid.' created by Key agent.';


	echo '<pre> new Key($thing)'; echo print_r($thing->thing); echo '</pre>';

$receipt = new Key($thing);


if($thing->uuid == $receipt->uuid) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> test_key.php receipt: '; print_r($thing->uuid); echo '</pre>';
	echo '<pre> test_key.php receipt %thingreport: '; print_r($thingreport); echo '</pre>';

	}





?>
