<?php
namespace Nrwtaylor\StackAgentThing;
//require '/var/www/stackr.test/vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';


//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//echo "test_db.php";


//echo "<br>";
//echo "Test 1: Test random 3";

//$thing1 = new Thing(null);
//$thing1->makeThing("nick@wildnomad.com", "associator", "Create a thing" . time());

$subject = "Test 1 - PHP mail() function test " . time();
$message = "Daisy daisy";
$to = "test";

$headers = "";



$thing = new Thing(null);

$from = $thing->container['stack']['email'];


$headers = "";


mail($from,$subject,$message, $headers);


$subject = "Test 2 - Thing email test " . time();

$thing->Create($from, "test", $subject);

//$email = new Email($thing->uuid, $from, $to, $subject);
$email = new Email($thing, "email");


$raw_message = 'Test message with <h1>some html</h1> and some <br> line breaks <br> and a link <a href="test">test</a>';

$message = $email->sendGeneric($from,$to,$subject,$raw_message);

//var_dump($message);
//echo "Test done\n";
//exit();

//echo '<pre> dbtest.php $prior_uuid: '; print_r($prior_uuid); echo '</pre>';
//echo '<pre> dbtest.php $thing3->uuid: '; print_r($thing2->uuid); echo '</pre>';


// So now want to look at the posterior Thing and see whether it is linking
// forward in 'assocations' to the new Thing.


if($message == null) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->uuid); echo '</pre>';

	echo '<pre> thingtest.php thing2 %thingreport: '; print_r($message); echo '</pre>';

	}


echo "<br>";
echo "Test 3: Exclude Stack record:";


$subject = "Stack record: Opt-in verification request exclusion test" . date("Y-m-d H:i:s");
$to = "test";

$thing = new Thing(null);
$thing->Create($from, "test", $subject);


//$email = new Email($thing->uuid, $from, $to, $subject);
$email = new Email($thing, "email");


$raw_message = 'Test message with <h1>some html</h1> and some <br> line breaks <br> and a link <a href="test">test</a>';

$message = $email->sendGeneric($from,$to,$subject,$raw_message);

//quoted_printable_decode(

//echo '<pre> dbtest.php $prior_uuid: '; print_r($prior_uuid); echo '</pre>';
//echo '<pre> dbtest.php $thing3->uuid: '; print_r($thing2->uuid); echo '</pre>';


// So now want to look at the posterior Thing and see whether it is linking
// forward in 'assocations' to the new Thing.


if($message == false) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->uuid); echo '</pre>';

	echo '<pre> thingtest.php thing2 %thingreport: '; print_r($message); echo '</pre>';

	}


?>
