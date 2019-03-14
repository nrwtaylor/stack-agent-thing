<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';




ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

echo "test_ant.php<br>";
echo "Functional test of an ant agent.  Updated: 11 May 2017<br>";




$thing = new Thing(null);
$test_email = $thing->container['stack']['email'];

// Provide names it hasn't seen before.  This will generate a ant Thing.
$thing->Create($test_email, "chooser", "spawn");
$thing->choice->Create('a_choice_name');

// Now call the Ant agent on the thing, which will do Ant things.
$ant_agent = new Ant($thing);

// Get the last report back from the ant.
$thing_report = $ant_agent->thing_report;

echo '<pre> test_ant.php $thing_report: '; print_r($thing_report); echo '</pre>';

echo quoted_printable_decode($thing_report['choices']['button']);

?>
