<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';


//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_newuser.php";


echo "<br>";
echo "Test 1: Test create newuser agent.  Should send terms and conditions.";


$test_email = rand(1000000,9999999) . "@stackr.ca";

$thing = new Thing(null);
$thing->Create($test_email, "new user", "New user at" . time());

$newuser_thing = new Newuser($thing);

$thing_report = $newuser_thing->thing_report;


if(strpos($thing_report['choices']['link'], 'Opt-in')) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> test_newuser.php $thing->uuid: '; print_r($thing->uuid); echo '</pre>';
	echo '<pre> test_newuser.php $thingreport: '; print_r($thing_report); echo '</pre>';

	}


echo "<br>";
echo "Test 2: Call newuser agent again with same email.  Should send a helpful note with an opt-in link.";


$thing = new Thing(null);
$thing->Create($test_email, "new user", "New user at" . time());

$newuser_thing = new Newuser($thing);

$thing_report = $newuser_thing->thing_report;


if(strpos($thing_report['choices']['link'], 'Opt-in')) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> test_newuser.php $thing->uuid: '; print_r($thing->uuid); echo '</pre>';
	echo '<pre> test_newuser.php $thingreport: '; print_r($thing_report); echo '</pre>';

	}


?>
