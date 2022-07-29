<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
//error_reporting(-1);


error_reporting(E_ALL ^ E_DEPRECATED);

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];

    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

 // May be needed.
 session_save_path('/tmp');

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../private/settings.php';

$GLOBALS['settings'] = $settings;
$GLOBALS['stack'] = __DIR__ . "/../";
$GLOBALS['stack_path'] = __DIR__ . "/../";


$app = new \Slim\App($settings);

$app->web_prefix = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
$app->stack_path = $GLOBALS['stack_path'];
//define('WEB_PREFIX', $app->web_prefix);
//$GLOBALS['web_prefix'] = $app->web_prefix;


// Set up dependencies

require __DIR__ . '/../vendor/nrwtaylor/stack-agent-thing/src/dependencies.php';

// Register middleware
require __DIR__ . '/../vendor/nrwtaylor/stack-agent-thing/src/middleware.php';

// Register routes
require __DIR__ . '/../vendor/nrwtaylor/stack-agent-thing/src/routes.php';

// Run app
$app->run();

?>
