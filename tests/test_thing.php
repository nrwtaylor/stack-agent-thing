<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';

//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


echo "test_thing.php";

// Make a new thing and get a random matching keyword record from the db.
//

echo "<br>";
echo "Test 1: Create a thing class";

$expected_result = "subject ". time();
$subject = $expected_result;

$thing = new Thing(null);
$thing->Create("null@stackr.ca", "jsontest", $subject);


//echo '<pre>'; print_r($thing->thing); echo '</pre>';
//echo "temporary break here thing_test.php";
//exit();

//echo '<pre> thingtest.php '; print_r($thing->thing); echo '</pre>';

if($expected_result == $thing->subject) {
	echo "Pass  ";
	} else {
	echo "Fail  ";
	echo '<pre>'; print_r($thing->thing); echo '</pre>';
	}





echo "<br>";
echo "Test 2: Test db read: ";
// Create a Thing to get a uuid.  This is the only way to do this
// in Stacker.  Forces use of Things uuid generator.
$temp_thing = new Thing(null);
$uuid = $temp_thing->uuid;
//Destroy thing
unset($thing);


$thing = new Thing($uuid);
$thing->Get();

if($thing->thing == false) {
	echo "Pass  ";
	} else {
	echo "Fail  ";
	echo '<pre>'; print_r($thing->thing); echo '</pre>';
	}


echo "<br>";
echo "Test 3: Ignore";

$thing = new Thing(null);
$thing->Create("null@stackr.ca", "test", "Create a thing" . time());

$thing->Get();
$thing->Ignore();

	echo '<pre> thingtest.php $thing->thing: '; print_r($thing->thing); echo '</pre>';



echo "<br>";
echo "Test 4: Test posterior association";

$thing1 = new Thing(null);
$thing1->Create("null@stackr.ca", "associatetester", "Create a thing at " . time());

sleep(1);

$thing2 = new Thing(null);
$thing2->Create("null@stackr.ca", "associatetester", "A new thing 1 at " . time());

sleep(1);

$thing3 = new Thing(null);
$thing3->Create("null@stackr.ca", "associatetester", "A new thing 2 at " . time());



// So now want to look at the posterior Thing and see whether it is linking
// forward in 'assocations' to the new Thing.


// Get latest a_thing.
$thing1->Get();

//	echo '<pre> thingtest.php $thing1->thing: '; print_r($thing1->thing); echo '</pre>';



if((strpos($thing1->thing->associations, $thing3->uuid) !== false) 
	or (strpos($thing2->thing->associations, $thing3->uuid)!== false)) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	echo '<pre> thingtest.php thing1: '; print_r($thing1->thing); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing2->thing); echo '</pre>';
	echo '<pre> thingtest.php thing3: '; print_r($thing3->thing); echo '</pre>';

	}




$input1 = 'Null Nominal null@stackr.ca, Nom Nom nomnom@stackr.ca';

$input2 = '"devstack@stackr.ca" devstack@stackr.ca';
$input3 = 'Nominal Agent nominal@stackr.ca, "devstack@stackr.ca" devstack@stackr.ca';

echo "<br>";
echo "Test 5: Test red, amber, green flags.  ";

$thing = new Thing(null);
$thing->Create("null@stackr.ca", "associatetester", "A new thing 2" . time());

$thing->flagRed();



if($thing->isRed() == true) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	echo '<pre> thingtest.php thing1: '; print_r($thing->thing); echo '</pre>';
	}



$thing->Ignore();


if($thing->isRed() == false) {

	echo "Pass  ";
	} else {
	echo "Fail  ";

	echo '<pre> thingtest.php thing1: '; print_r($thing->thing); echo '</pre>';
	}


$thing->flagRed();
if($thing->isRed() == true) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	echo '<pre> thingtest.php thing1: '; print_r($thing->thing); echo '</pre>';
	}

echo "<br>";

echo "<br>";
echo "Test 6: UUID test.  ";

$thing = new Thing(null);
$thing->Create("null@stackr.ca", "associatetester", "A new thing 2" . time());

echo $thing->uuid;


echo "<br>";
echo "Test 7: Check create, credit, debit account functions on 'stack'<br>";
$thing = new Thing(null);
$thing->Create("null@stackr.ca", "test", "test");


if($thing->account['stack']->balance['amount'] == 100) {
	echo "Pass  ";
	} else {
	echo "Fail  ";
echo '<pre>'; print_r($thing->thing); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->account['stack']->balance); echo '</pre>';

	}

$thing->account['stack']->Credit(10);

if($thing->account['stack']->balance['amount'] == 110) {
	echo "Pass  ";
	} else {
	echo "Fail  ";
echo '<pre>'; print_r($thing->thing); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->account['stack']->balance); echo '</pre>';

	}


$thing->account['stack']->Debit(50);
if($thing->account['stack']->balance['amount'] == 60) {
	echo "Pass  ";
	} else {
	echo "Fail  ";
echo '<pre>'; print_r($thing->thing); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->account['stack']->balance); echo '</pre>';

	}

$thing->account['thing']->Debit(50);
if($thing->account['thing']->balance['amount'] == -150) {
	echo "Pass  ";
	} else {
	echo "Fail  ";
echo '<pre>'; print_r($thing->thing); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->account['thing']->balance); echo '</pre>';

	}




echo "<br>";
echo "Test 8 Create a choice ...<br>";
$thing1 = new Thing(null);

$random = mt_rand(10000000, 99999999);

$thing1->Create($random . "@stackr.ca", "test", "Doing one thing or another test at " . time());

//	echo '<pre> thingtest.php thing: '; print_r($thing1->thing); echo '</pre>';



$current_state = $thing1->getState("test");

if($current_state == 'open') {
	echo "Pass  ";
} else {
	echo "Fail  ";
	echo '<pre> test_thing.php $current_state'; print_r($current_state); echo '</pre>';
	echo '<pre> test_thing.php $thing1->thing: '; print_r($thing1->thing); echo '</pre>';

	}


echo "Test 8.1 Follow up with another e-mail from same id ...<br>";
//$thing = new Thing(null);

//$random = mt_rand(10000000, 99999999);

$thing2->Create($random . "@stackr.ca", "test", "Another email at " . time());

	echo '<pre> thingtest.php thing: '; print_r($thing->thing); echo '</pre>';

$current_state = $thing2->getState("test");

if($current_state == 'open') {
	echo "Pass  ";
} else {
	echo "Fail  ";
	echo '<pre> test_thing.php $current_state'; print_r($current_state); echo '</pre>';
	echo '<pre> test_thing.php $thing1->thing: '; print_r($thing2->thing); echo '</pre>';

	}



echo "Test 8.2 Follow up with another e-mail from same id ...<br>";
//$thing = new Thing(null);

//$random = mt_rand(10000000, 99999999);

$thing3->Create($random . "@stackr.ca", "test", "Another email at " . time());

	echo '<pre> thingtest.php thing: '; print_r($thing3->thing); echo '</pre>';

$current_state = $thing3->getState("test");

if($current_state == 'open') {
	echo "Pass  ";
} else {
	echo "Fail  ";
	echo '<pre> test_thing.php $current_state'; print_r($current_state); echo '</pre>';
	echo '<pre> test_thing.php $thing->thing: '; print_r($thing3->thing); echo '</pre>';

	}


//$thing->receiptPNG();

?>
