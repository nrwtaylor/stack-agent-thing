<?php

namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';

//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_usermanager.php";




echo "<br>";
echo "Test 1: Test create usermanager agent<br>";

$test_thing = new Thing(null);

$test_email = "4sdfw2@stackr.ca";


if ($test_thing->container['stack']['state'] != 'dev') {exit();};

$test_thing->Create($test_email, "usermanager", "Hey usermanager at " . date("Y-m-d H:i:s"));

// echo '<pre> thingtest.php $test_thing->thing (after Create): '; print_r($test_thing->thing); echo '</pre>';

// Run the Usermanager agent on this Thing.  This is actually done in a loop by
// agenthandler.php.

// echo '<pre> thingtest.php $test_thing->thing (before Usermanager): '; print_r($test_thing->thing); echo '</pre>';

$thing = new Usermanager($test_thing);
$thing_report = $thing->thing_report;
//echo '<pre> thingtest.php $test_thing->thing (after Usermanager): '; print_r($test_thing->thing); echo '</pre>';

// First run of usermanager on a new Thing sent to usermanager with no 
// previous matches.

// What does that do?

//var_dump($thing_report);

// It will find a message from an identifier to 'usermanager'.  This is the 
// most generic type of incoming usermanager message.

// usermanager should tag this message with the usermanager node tree,
// and a current node of 'unvalidated'.

if($thing_report['thing'] == false) {
	echo "Pass  ";
} else {
	echo "Fail  ";
	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> test_usermanager thing2: '; print_r($thing_report['thing']); echo '</pre>';

}

echo '<pre> test_usermanager.php $thing_report thing: '; print_r($thing_report['thing']); echo '</pre>';


exit();

echo "<br>";
echo "Test 2: Test a follow-up opt-in email<br>";

// Now simulate another message being sent.  And being processed by
// user manager.

$thing2 = new Thing(null);
$thing2->Create($test_email, "opt-in", "Hey at" . time());

// Run the Usermanager agent on this Thing.  This is actually done in a loop by
// agenthandler.php.
$thingreport = new Usermanager($thing2);

	echo '<pre> thingtest.php thing2: '; print_r($thing2->thing); echo '</pre>';
//	echo '<pre> thingtest.php thing2 %thingreport: '; print_r($thingreport); echo '</pre>';




?>
