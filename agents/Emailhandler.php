#!/usr/bin/php -q
<?php
use Nrwtaylor\StackAgentThing;
#echo __DIR__;
#require '../vendor/autoload.php';
#require_once __DIR__ . '/vendor/autoload.php';
require "/var/www/stackr.test/vendor/autoload.php";

// Set $development true to run a standard email through emailhandler.php
// when called from the command line with
// /usr/bin/php -q <path>/src/emailhandler.php

$development = false;
$accepted_domains = ["stackr.ca", "stackr.co"];

if ($development == true) {
    ini_set("display_startup_errors", 1);
    ini_set("display_errors", 1);
    error_reporting(-1);
}

// Copy in dev public has ../src/
// Needs stripping out for public
// and putting in src.

ini_set("allow_url_fopen", 1);

if ($development == true) {
    echo "emailhandler.php " . $development . "<br>";
    //$to = 'redpanda.stack@gmail.com, watson@stackr.co, "devstack@stackr.co" devstack@stackr.co, transit@stackr.co';
    $to = "transit@stackr.ca";
    //$subject = "Stop 12345" . ' Time: ' . date(DATE_RFC2822);
    $subject = "stop 51380 devstack";

    //$from = rand(100000,999999) .  $accepted_domains[0];
    //$from = "322564@stackr.ca";
    $from = "nick@stackr.ca";
    echo "<br>Default values set for test email.  Call agenthandler.php to process.<br>";
    echo "to: " . $to . "<br>";
    echo "from: " . $from . "<br>";
    echo "subject: " . $subject . "<br>";
    goto test;
}

// Stackr takes any 34 character CHAR as an identifier.  It currently creates
// UUIDv4 identifiers.
// Design objective: Provide a number for an object which is
// probably/possibly not also associated with another object by others.
// read from stdin

$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
    $email .= fread($fd, 1024);
}
fclose($fd);

//Assumes $email contains the contents of the e-mail
//When the script is done, $subject, $to, $message, and $from all contain appropriate values

//Parse "subject"
$subject1 = explode("\nSubject: ", $email);
$subject2 = explode("\n", $subject1[1]);
$subject = $subject2[0];

//Parse "to"
$to1 = explode("\nTo: ", $email);
$to2 = explode("\n", $to1[1]);
$to = str_replace(">", "", str_replace("<", "", $to2[0]));

$message1 = explode("\n\n", $email);

$start = count($message1) - 3;

if ($start < 1) {
    $start = 1;
}

//Parse "message"
$message2 = explode("\n\n", $message1[$start]);
$message = $message2[0];

//Parse "from"
$from1 = explode("\nFrom: ", $email);
$from2 = explode("\n", $from1[1]);

if (strpos($from2[0], "<") !== false) {
    $from3 = explode("<", $from2[0]);
    $from4 = explode(">", $from3[1]);
    $from = $from4[0];
} else {
    $from = $from2[0];
}

test:

// So that's some nonsense for parsing the email.

// Build a haystack.
$haystack = strtolower($to . " | " . $subject);

$needles = strtolower("/reject/");
if (preg_match($needles, $haystack) === 1) {
    exit();
}

// Okay.  So no other good reason to reject a message.

if ($development) {
    echo "to: " . $to;
    echo "from: " . $from;
    echo "subject: " . $subject;
}

// Create an agent request for every stack agent listed in To.
// Only To.

$stack_agents = [];
$words = explode(" ", $to);
//	Only words with an @ sign are valid.
foreach ($words as $word) {
    $word = str_replace('"', "", $word);
    //echo $word . '<br>';
    if (strpos($word, "@stackr.") !== false) {
        //	//echo "postfix found";
        $arr = explode("@", $word, 2);

        array_push($stack_agents, $arr[0]);
        //echo $stack_agent . '<br>';
        //	//$stack_agents[] = $stack_agent;
    }
}

$stack_agents = array_unique($stack_agents);

foreach ($stack_agents as $stack_agent) {
    // This now handled by Gearman 23 Jan 2018
    //	$thing = new Thing(null);
    //	$thing->Create($from, $stack_agent, $subject);

    $arr = json_encode([
        "to" => $from,
        "from" => $stack_agent,
        "subject" => $subject,
    ]);
    //$arr = json_encode(array("uuid"=>$thing->uuid));

    $client = new GearmanClient();
    $client->addServer();
    //$client->doNormal("call_agent", $arr);
    $client->doHighBackground("call_agent", $arr);
}

if ($development) {
    print_r("emailhandler.php completed");
}

return;
exit();

fail:
// Fail to this.
// Send response email.

$headers =
    "From: no-reply@stackr.co" .
    "\r\n" .
    "Reply-To: no-reply@stackr.co" .
    "\r\n" .
    "X-Mailer: PHP/" .
    phpversion();

$text = $message;

$processed = ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
$processed = [
    "from" => $from,
    "to" => $to,
    "subject" => $subject,
    "text" => $text,
    "info" => "this is what Stackr processed",
];
$processed_json = json_encode($processed);

$notused = "{\"processed\": 
[
\"from\":\"$from\", 
\"to\":($to), 
\"subject\": \"$subject\", 
\"text\": ($message)]}";

$devreport =
    'Subject: $subject\nTo: $to\nFrom: $from\nMessage: $message\nUUID: $uuid4\n\n$sqlresponse';

//test email to make sure it works

mail(
    $from,
    "[Stackr] $subject",
    "Thank you $from your message to stackr.ca account $to has not been accepted by Stackr.  Keep on stacking.\n\nhttps://stackr.co/api/thing:$uuid\n$sqlresponse
",
    $headers
);


?>
