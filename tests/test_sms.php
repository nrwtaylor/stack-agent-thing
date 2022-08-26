<?php

namespace Nrwtaylor\StackAgentThing;
//require '/var/www/stackr.test/vendor/autoload.php';
require '/var/www/html/stackr.ca/vendor/autoload.php';

echo "test_sms.php";





$thing = new Thing(null);
$thing->Create("17787920847", "17784012130", "test sms");




$sms_agent = new Sms($thing);

$sms_agent->sendSms("17787920847", "hello");


?>
