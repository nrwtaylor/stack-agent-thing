<?php

namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';

echo "test_abc.php";





$thing = new Thing(null);
$thing->Create("null@stackr.ca", "chooser", "spawn");



$abc_agent = new Abc($thing);


?>
