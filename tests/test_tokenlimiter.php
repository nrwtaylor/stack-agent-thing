<?php

namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';

//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_usermanager.php";



echo "<br>";
echo "Test 1: Test create limiter agent<br>";

$token_limiter_thing = new Thing(null);
echo '<pre> thingtest.php $test_thing->thing (at Instantiation): '; print_r($token_limiter_thing->thing); echo '</pre>';


$test_email = $token_limiter_thing->container['stack']['email']; // or


if ($token_limiter_thing->container['stack']['state'] != 'dev') {exit();};


$token_limiter_thing->Create($test_email, "limiter", "Hey limiter at " . date("Y-m-d H:i:s"));
echo '<pre> thingtest.php $test_thing->thing (after Create): '; print_r($token_limiter_thing->thing); echo '</pre>';

// Run the Usermanager agent on this Thing.  This is actually done in a loop by
// agenthandler.php.

echo '<pre> thingtest.php $test_thing->thing (before Usermanager): '; print_r($token_limiter_thing->thing); echo '</pre>';

// 5 things per 8 units.  To a limit of 3.
new TokenLimiter($token_limiter_thing, 5, 8, 3); // five per 8.  Of 3.  The Genie's Deal.

echo '<pre> thingtest.php $test_thing->thing (after Usermanager): '; print_r($token_limiter_thing->thing); echo '</pre>';

echo "DECISION IS BEING OVER-RIDEN WHEN THING STARTS";
echo "INVESTIGATE";

exit();


function thingMessage() {

}




?>
