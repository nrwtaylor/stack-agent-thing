<?php

namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';

//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_receipt.php";


		$settings = require '../src/settings.php';
		$container = new \Slim\Container($settings);


		//$app->test= "Development code";

		// Provide access to stack settings.
		$container['stack'] = function ($c) {
			$db = $c['settings']['stack'];
			return $db;
			};


		$test_email = $container['stack']['email'];







echo "<br>";
echo "Test 1: Test create Receipt agent";


$thing = new Thing(null);
$thing->Create($test_email, "receipt", "Hey receipt at " . date("Y-m-d H:i:s"));
	echo '<pre> Thing '.$thing->uuid.' created by Receipt agent.';


	echo '<pre> new Receipt($thing)'; echo print_r($thing->thing); echo '</pre>';

$receipt = new Receipt($thing);


if($thing->uuid == $receipt->uuid) {
	echo "Pass  ";
	} else {
	echo "Fail  ";

	//echo '<pre> thingtest.php thing1: '; print_r($thing1->uuid); echo '</pre>';
	echo '<pre> test_receipt.php receipt: '; print_r($thing->uuid); echo '</pre>';
	echo '<pre> test_receipt.php receipt %thingreport: '; print_r($thingreport); echo '</pre>';

	}





?>
