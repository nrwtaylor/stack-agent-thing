<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';

//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


// Test a clerk based on redpanda.
// step 1 search and replace Redpanda for Clerk
// manual true
// (false) open test_clerk Search Replace...
// for both redpanda and Redpanda
// reward 1 satoshi
// true // indicates test needs attention



//echo "Stackfunctest.php 1";

echo "test_clerk.php";



echo "<br>";
echo "Test 1: Test create clerk agent from subject line<br>";


$thing = new Thing(null);
$thing->Create("null@stackr.ca", "clerk", "Create tracker 125 PV hrs");

$clerk_thing = new Clerk($thing);

$message = $clerk_thing->message;
$uuid = $thing->uuid;




if($thing->account['tracker']->balance['amount'] == 125) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($clerk_thing->thing->account['tracker']->balance); echo '</pre>';

	}


echo "<br>";
echo "Test 2: Test credit clerk agent from subject line<br>";


$thing = new Thing(null);
$thing->Create("null@stackr.ca", "clerk", "Credit tracker 25");

$clerk_thing = new Clerk($thing);

$message = $clerk_thing->message;
$uuid = $thing->uuid;




if($thing->account['tracker']->balance['amount'] == 150) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($clerk_thing->thing->account['tracker']->balance); echo '</pre>';

	}




echo "<br>";
echo "Test 3. Number extraction from subject line<br>";


$thing = new Thing(null);
$thing->Create("null@stackr.ca", "clerk", "100 500 -123.3, -4, +3");

$clerk_thing = new Clerk($thing);

$message = $clerk_thing->message;
$uuid = $thing->uuid;
echo "foo";
echo $clerk_thing->getAmount();
echo "bar";

if($thing->account['tracker']->balance['amount'] == 125) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($clerk_thing->thing->account['tracker']->balance); echo '</pre>';

	}






exit();


$clerk_thing->thing->account['tracker']->Credit(1.005);
if($clerk_thing->thing->account['tracker']->balance['amount'] == 126.005) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($clerk_thing->thing->account['tracker']->balance); echo '</pre>';
	}

$clerk_thing->thing->account['tracker']->Debit(50.005);
if($clerk_thing->thing->account['tracker']->balance['amount'] == 76.000) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($clerk_thing->thing->account['tracker']->balance); echo '</pre>';
	}















exit();


	echo '<pre> new Thing(null)'; echo '</pre>';
$thing = new Thing(null);
	echo '<pre> makeThing(...)'; echo '</pre>';
$thing->Create("null@stackr.ca", "clerk", "Hey clerk at" . time());
	echo '<pre> Get()'; echo '</pre>';


//$thing->Get($thing->uuid);
	echo '<pre> new clerk($thing)'; echo '</pre>';
	echo '<pre> new clerk($thing)'; echo print_r($thing->thing); echo '</pre>';

$clerk = new Clerk($thing);


if($thing->uuid == $clerk->uuid) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2 %thingreport: '; print_r($thingreport); echo '</pre>';

	}





?>
