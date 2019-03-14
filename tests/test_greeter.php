<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';

//http://project-stack.dev:8080/stackfunctest.php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


echo "test_dispatcher.php";


echo "<br>";
echo "Test 1: Instantiate a class of greeter";

// Dispatcher gets called by the cron job.
// Cronjob calls agenthandler.
// Agenthandler creates a dispatcher agent.

// So production test is to call agenthandler.php.


//$thing1 = new Thing(null);
//$thing1->makeThing("nick@wildnomad.com", "associator", "Create a thing" . time());

$thing = new Thing(null);

$thing->makeThing("nick@wildnomad.com", "greeter", "Please sign me up at" . time());

$greeter = new Greeter($thing);








?>
