<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';


//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


echo "test_db.php";





echo "<br>";
echo "Test 0: Apostrphoe in subject";


$thing1 = new Thing(null);
$thing1->Create("test@stackr.ca", "test", "Test: Embed uuid's in subject - " . $thing1->uuid . " at " . time());









echo "<br>";
echo "Test 1: Test find by UUID";


$thing1 = new Thing(null);
$thing1->Create("test@stackr.ca", "test", "Test: Embed uuid in subject - " . $thing1->uuid . " at " . time());



sleep(1);

$thing = new Thing(null);
$thing->Create("test@stackr.ca", "test", "Embed uuid in subject - " . $thing1->uuid . " at " . time());

//echo '<pre> test_db.php $thing1: '; print_r($thing1->thing->uuid); echo '</pre>';

sleep(1);

$thing = new Thing(null);
$thing->Create("test@stackr.ca", "test", "Embed uuid in subject - " . $thing1->uuid . " at " . time());

//echo '<pre> test_db.php $thing1: '; print_r($thing1->thing->uuid); echo '</pre>';

sleep(1);


$thingreport = $thing1->db->UUids();




// So now want to look at the posterior Thing and see whether it is linking
// forward in 'assocations' to the new Thing.


if(count($thingreport['things']) == 3) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';

echo '<pre> test_db.php $prior_uuid: '; print_r($prior_uuid); echo '</pre>';
echo '<pre> test_db.php $thing3->uuid: '; print_r($thing2->uuid); echo '</pre>';

	echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing2->uuid); echo '</pre>';

	}






//echo "----------------- break in test_db.php -------------------";
//exit();
echo "<br>";
echo "Test 1.1: Test find by keyword";

$keyword = "test";
$thing = new Thing(null);
$thing->Create("test@stackr.ca", "test", "Test: Find by keyword " . $keyword . " at " . time());

$thingreport = $thing1->db->UUids();




// So now want to look at the posterior Thing and see whether it is linking
// forward in 'assocations' to the new Thing.


if(count($thingreport['things']) == 3) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';

echo '<pre> test_db.php $prior_uuid: '; print_r($prior_uuid); echo '</pre>';
echo '<pre> test_db.php $thing3->uuid: '; print_r($thing2->uuid); echo '</pre>';

	echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing2->uuid); echo '</pre>';

	}












echo "<br>";
echo "Test 2: Test priorGet with two items";


$thing1 = new Thing(null);
$thing1->Create("null@stackr.ca", "associator", "test_db test 1 A new thing 1 at" . time());

//echo '<pre> test_db.php $thing1: '; print_r($thing1->thing->uuid); echo '</pre>';

sleep(1);

$thing2 = new Thing(null);
$thing2->Create("null@stackr.ca", "associator", "test_db test 1 A new thing 2 at" . time());


//echo '<pre> test_db.php $thing2: '; print_r($thing2->thing->uuid); echo '</pre>';

$nom_to = "associator";
$nom_from = "null@stackr.ca";

// Thing1 created with new uuid.
// Then Thing2 created, and Thing1 associations should be updated to point
//  forward to Thing2.

$thingreport = $thing2->db->priorGet();
$prior_uuid = $thingreport['thing']->uuid;





// So now want to look at the posterior Thing and see whether it is linking
// forward in 'assocations' to the new Thing.


if($thing1->uuid == $prior_uuid) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';

echo '<pre> test_db.php $prior_uuid: '; print_r($prior_uuid); echo '</pre>';
echo '<pre> test_db.php $thing3->uuid: '; print_r($thing2->uuid); echo '</pre>';

	echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing2->uuid); echo '</pre>';

	}


unset($thing1);
unset($thing2);



echo "<br>";
echo "Test 3: Test priorGet with three items";

$thing1 = new Thing(null);
$thing1->Create("null@stackr.ca", "associator", "test_db test 2 Create a thing at " . time());

sleep(1);

$thing2 = new Thing(null);
$thing2->Create("null@stackr.ca", "associator", "test_db test 2 A new thing 1 at " . time());

sleep(1);

$thing3 = new Thing(null);
$thing3->Create("null@stackr.ca", "associator", "test_db test 2 A new thing 2 at " . time());

sleep(1);

$nom_to = "associatetester";
$nom_from = "null@stackr.ca";

$thingreport = $thing3->db->priorGet();
$prior_uuid = $thingreport['thing']->uuid;





// So now want to look at the posterior Thing and see whether it is linking
// forward in 'assocations' to the new Thing.


if($thing2->uuid == $prior_uuid) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';

echo '<pre> test_db.php $prior_uuid: '; print_r($prior_uuid); echo '</pre>';
echo '<pre> test_db.php $thing3->uuid: '; print_r($thing3->uuid); echo '</pre>';

	echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing2->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing3: '; print_r($thing3->uuid); echo '</pre>';

	}

unset($thing1);
unset($thing2);
unset($thing3);

echo "<br>";
echo "Test 4: Test random3";

$thing = new Thing(null);
$thing->Create("null@stackr.ca", "test", "Test randomN() at " . time());

$thingreport = $thing->db->randomN("null@stackr.ca");




if(count($thingreport['thing']) ==3 ) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';

echo '<pre> test_db.php $thing->uuid: '; print_r($thing->uuid); echo '</pre>';

	echo '<pre> thingtest.php thing: '; print_r($thingreport['thing']); echo '</pre>';

	}





?>
