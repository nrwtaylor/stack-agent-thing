<?php
namespace Nrwtaylor\StackAgentThing;

// Refactor to use GLOBAL variable
//require $GLOBALS['stack_path'] . "vendor/autoload.php";
require '/var/www/stackr.test/vendor/autoload.php';

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

$input = "The quick brown fox jumps over the lazy dog.";
$tagger = new \BrillTagger();
$tagger->tag($input);
echo "foo";
var_dump($tagger);

