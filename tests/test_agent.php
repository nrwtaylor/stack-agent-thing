<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_translink.php";


echo "<br>";
echo "Test 1: Test create transit agent";


$thing = new Thing(null);
$thing->Create("null@stackr.ca", "transit", "urgent stop 51380 at" . time());

$thingreport = new Agent($thing);


echo "<br>Successful test indicated by receipy of Transit email at null@stackr.ca<br>"

//if($thing->uuid == $prior_uuid) {
//	echo "Pass  ";
//	} else {
//	echo "Fail  ";

//	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
//	echo '<pre> thingtest.php thing2: '; print_r($thing->uuid); echo '</pre>';
//	echo '<pre> thingtest.php thing2 %thingreport: '; print_r($thingreport); echo '</pre>';

//	}





?>
