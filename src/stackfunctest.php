<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '/home/wildtay3/public_html/stackr/vendor/autoload.php';

require '../vendor/autoload.php';
require 'stack.php';

$from = "nick@wildnomad.com";
$to = "stackr@stackr.co";
$subject = "Hey Watson";


$thing = new Thing(null);


$thing->makeThing($from, $to, $subject);

$email = new emailResponder($thing);

$email->sendKey();
$email->chooseResponse();

echo "Completed";


?>
