<?php

namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';


//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_optin.php";


echo "<br>";
echo "Test 1: Test create opt-in agent.  Should send tems and conditions.";


$test_email = rand(1000000,9999999) . "@stackr.ca";

$thing = new Thing(null);
$thing->Create($test_email, "optin", "Opt-in at" . time());

$optin_thing = new Optin($thing);
$thing_report = $optin_thing->thing_report;

if(strpos($thing_report['choices']['link'], 'Opt-in')) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> test_optin.php $thing->uuid: '; print_r($thing->uuid); echo '</pre>';
	echo '<pre> test_optin.php $thingreport: '; print_r($thing_report); echo '</pre>';

	}

echo "<br>";
echo "Test 2: Test create opt-in agent.  Pre-set state to newuser.  ";


$test_email = rand(1000000,9999999) . "@stackr.ca";

$thing = new Thing(null);
$thing->Create($test_email, "optin", "Opt-in at" . time());

$thing->previous_state = "new user";

$optin_thing = new Optin($thing);
$thing_report = $optin_thing->thing_report;

//var_dump($thing_report);

if(strpos($thing_report['choices']['link'], 'Opt-in')) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> test_optin.php $thing->uuid: '; print_r($thing->uuid); echo '</pre>';
	echo '<pre> test_optin.php $thingreport: '; print_r($thing_report); echo '</pre>';

	}



echo "<br>";
echo "Test 3: Test create opt-in agent.  Pre-set state to optin.  ";


$test_email = rand(1000000,9999999) . "@stackr.ca";

$thing = new Thing(null);
$thing->Create($test_email, "optin", "Opt-in at" . time());

$thing->previous_state = "opt-in";

$optin_thing = new Optin($thing);
$thing_report = $optin_thing->thing_report;

var_dump($thing_report);

if(strpos($thing_report['choices']['link'], 'Opt-out')) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> test_optin.php $thing->uuid: '; print_r($thing->uuid); echo '</pre>';
	echo '<pre> test_optin.php $thingreport: '; print_r($thing_report); echo '</pre>';

	}



?>
