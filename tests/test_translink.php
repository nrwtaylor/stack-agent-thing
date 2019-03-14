<?php

namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';


//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_translink.php";


echo "<br>";
echo "Test 1: Test create transit agent";


$thing = new Thing(null);
$test_email = $thing->container['stack']['email'];
$thing->Create($test_email, "transit", "urgent stop 51380 at " . date("Y-m-d H:i:s"));

$thingreport = new Translink($thing);

exit();

if($thingreport['stop'] == 51380) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2: '; print_r($thing->uuid); echo '</pre>';
	echo '<pre> thingtest.php thing2 %thingreport: '; print_r($thingreport); echo '</pre>';

	}





?>
