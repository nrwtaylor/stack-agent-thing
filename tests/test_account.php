<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';


//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_translink.php<br>";

echo "<br>";
$test_title = "Test 1 See if Thing thing and stack accounts are set up.";

$thing = new Thing(null);
$thing->Create("null@stackr.ca", "test", "test");


if($thing->account['stack']->account_name == 'stack') {
	echo "Pass ";
	} else {
	echo "Fail ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->account['stack']->account_name); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->thing); echo '</pre>';

	}

if($thing->account['thing']->account_name == 'thing') {
	echo "Pass ";
	} else {
	echo "Fail ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->account['thing']->account_name); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->thing); echo '</pre>';
	}

echo $test_title;
// done.



echo "<br>";
$test_title = "Test 2.1: Check that Thing 'stack' account is created by default with 100 credit.";


if($thing->account['stack']->balance['amount'] == 100) {
	echo "Pass ";
	} else {
	echo "Fail ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->account['stack']->balance); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->thing); echo '</pre>';
	}
echo $test_title;



$test_title = "Test 3: Check that Thing 'stack' account is credited with 10.";

$thing->account['stack']->Credit(10);
$thing->account['stack']->balance['amount'];

if($thing->account['stack']->balance['amount'] == 110) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->account['scalar']->balance); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->thing); echo '</pre>';
	}
echo $test_title;
echo "<br>";


$test_title = "Test 2.2: Debit 'stack' account by 50.";

echo $thing->account['stack']->Debit(50) . "<br>";
echo $thing->account['stack']->balance['amount'] . "<br>";

if($thing->account['stack']->balance['amount'] == 60) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php : '; print_r($thing->account['stack']->balance); echo '</pre>';
	echo '<pre> thingtest.php thing: '; print_r($thing->thing); echo '</pre>';
	}
echo $test_title;
echo "<br>";

// Test 3.1 //

$test_title = "Test 3.1: Create -10m height account<br>";

$balance = array("amount"=>-10, "attribute"=>"height", "unit"=>"m");

$thing->newAccount($thing->uuid,'height0', $balance);

if (($thing->account['height0']->balance['amount'] == -10) and
	($thing->account['height0']->balance['attribute'] == "height"))
	 {
	echo "Pass  ";
	} else {
	echo "Fail  ";
	echo '<pre> thingtest.php : '; print_r($thing->account['height0']->balance['amount']); echo '</pre>';
	echo '<pre> thingtest.php thing: '; print_r($thing->thing); echo '</pre>';
	}
echo $test_title;
echo "<br>";


$test_title = "Test 3.2: Check stack account is unaffected.";

if (($thing->account['stack']->balance['amount'] == 60) and
	($thing->account['stack']->balance['attribute'] == "capital"))
	 {
	echo "Pass  ";
	} else {
	echo "Fail  ";
	echo '<pre> thingtest.php stack balance: '; print_r($thing->account['stack']->balance['amount']); echo '</pre>';
	echo '<pre> thingtest.php thing: '; print_r($thing->thing); echo '</pre>';
	}
echo $test_title;
echo "<br>";



// Test 5 //


$test_title = "Test 5: Save and load balance from MySQL";


$thing = new Thing(null);
$thing->Create("null@stackr.ca", "test", "Test: Save and load balance from MySQL at ". time());



$thing->account['stack']->Credit(15);
$uuid = $thing->uuid;

unset($thing); // Thing unset.  Which means it should be fully closed down.

$same_thing = new Thing($uuid);


$same_thing->account['stack']->Credit(20);
$same_thing->Get();


if (($same_thing->account['stack']->balance['amount'] == 135) and
	($same_thing->account['stack']->balance['attribute'] == "capital")) {
		echo "Pass  ";
	} else {
		echo "Fail  ";
		echo '<pre> thingtest.php thing2: '; print_r($same_thing->account['stack']->balance['amount']); echo '</pre>';

	}
echo $test_title;
echo "<br>";

echo '<pre> thingtest.php thing: '; print_r($same_thing->thing); echo '</pre>';










$test_title =  "Test 6: Credit Thing1 and Thing2 and transfer stack credit";
$test_title .=  "<br>&nbsp&nbsp        by posting debit/credit to each<br>";


$thing1 = new Thing(null);
$uuid1 = $thing1->uuid;
$thing1->Create("null@stackr.ca", "test", "Thing 1 and Thing 2 test at ". time());

$thing1->account['stack']->Credit(10); // We know 'stack' is a Stack account
unset($thing1);
// Thing unset


$thing2 = new Thing(null);
$uuid2 = $thing2->uuid;
$thing2->Create("null@stackr.ca", "test", "Thing 1 and Thing 2 test at ". time());

$thing2->account['stack']->Debit(10); // We know 'stack' is a Stack account
unset($thing2);
// Thing unset

$thinga = new Thing($uuid1);
$thingb = new Thing($uuid2);



if (($thinga->account['stack']->balance['amount'] == 110) and
	($thingb->account['stack']->balance['amount'] == 90))
	 {
	echo "Pass  ";
	} else {
	echo "Fail  ";
	echo '<pre> thingtest.php thing2: '; print_r($same_thing->account['stack']->balance['amount']); echo '</pre>';

	}

echo $test_title;echo "<br>";



$test_title = "Test 7: Test raising an exception if not a number, string, string balance.";

$thing1 = new Thing(null);
$uuid1 = $thing1->uuid;
$thing1->Create("null@stackr.ca", "test", "Test: Incorrectly presented balance at " . time());


// $account_uuid, $account_name, $balance
try {
$thing1->newAccount($uuid2, 'badbalance', array("amount"=>0.0, "attribute"=>null, "unit"=>null));
} catch (Exception $e) {
    //echo 'Caught exception: ',  $e->getMessage(), "\n";
}





if ($e->getMessage() == 'Needs development for cases where != numeric, string, string balance.') 
	 {
	echo "Pass  ";
	} else {
	echo "Fail  ";
	echo '<pre> thing_account.php stack balance: '; print_r($same_thing->account['stack']->balance['amount']); echo '</pre>';
	echo '<pre> thing_account.php caught exception: '; print_r($e->getMessage()); echo '</pre>';
	echo '<pre> thing_account.php thing: '; print_r($thing->thing); echo '</pre>';
	}
echo $test_title;echo "<br>";


$test_title = "Test 8: Credit Thing2 on Thing1 to recognize transfer stack credit<br>";
$test_title .= "<br>&nbsp&nbsp by posting a debit to Thing 1 stack and<br>";
$test_title .= "<br>&nbsp&nbsp a credit to Thing 2's account on Thing 1";


$thing1 = new Thing(null);
$uuid1 = $thing1->uuid;
$thing1->Create("null@stackr.ca", "clerk", "Thing 1 and Thing 2 test");




$thing1->account['stack']->Credit(10); // We know 'stack' is a Stack account
//unset($thing1);
// Thing unset



// Fire up Thing 1 again.  Just proves that MySQL saved the settings.
// Only do in test really.
//$thing1 = new Thing($uuid1);



// Get Thing2's uuid.
$thing2 = new Thing(null);
$uuid2 = $thing2->uuid;

//unset($thing2);
// Thing unset but now we have the uuid of both Thing 1 and Thing 2

$pass = false;

try {
// $account_uuid, $account_name, $balance
$thing1 = new Thing($uuid1);
$thing1->newAccount($uuid2, 'stack2', array("amount"=>0.0, "attribute"=>null, "unit"=>null));
} catch(Exception $e) {
$pass = true;
echo "Pass  ";

}

if (!$pass) {echo "Fail";}

echo $test_title;echo "<br>";;

























echo "<br>";
echo "End of test<br>";








?>














