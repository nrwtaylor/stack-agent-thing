<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';

$GLOBALS['stack'] = '/var/www/stackr.test/';

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);


// Set-up for command-line/cron job run

        $url = $GLOBALS['stack'] . 'private/settings.php';
        $settings = require $url;

        $container = new \Slim\Container($settings);

        $container['stack'] = function ($c) {
            $db = $c['settings']['stack'];
            return $db;
        };

        $mail_postfix = $container['stack']['mail_postfix'];



$from = "null" . $mail_postfix;
$stack_agent = "watchdog";
$subject = "s/ watchdog beat";


        $thing = new Thing(null);
        $thing->Create($from, $stack_agent,$subject);

        $cron = new Watchdoghandler($thing);


class Watchdoghandler
{
    function __construct(Thing $thing, $agent_input = null)
    {

		$this->stack_idle_mode = 'use'; // Prevents stack generated execution when idle.

        $this->thing = $thing;

		$this->thing->flagGreen(); // Just make sure

        $watchdog_agent = new Watchdog($this->thing);
        $this->thing_report['sms'] = "WATCHDOG | Tick";
        $this->thing_report['help'] = "This Agent connects the computer's clock tick to the stack.";

    }
}
