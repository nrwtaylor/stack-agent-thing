<?php
namespace Nrwtaylor\StackAgentThing;

if ($_SERVER['DOCUMENT_ROOT'] == "") {
    // Is not being run by apache.    require '/var/www/stackr.test/vendor/autoload.php';

    require '/var/www/stackr.test/vendor/autoload.php';
}
//require '/var/www/html/stackr.ca/vendor/autoload.php';

//var_dump($_SERVER['DOCUMENT_ROOT']);

//$GLOBALS['stack'] = '/var/www/stackr.test/';
//$GLOBALS['stack'] = '/var/www/html/stackr.ca/';

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);


// Set-up for command-line/cron job run

$from = "null@stackr.ca";
$stack_agent = "cron";
$subject = "s/ cron 60s tick";

$thing = new Thing(null);
$thing->Create($from, $stack_agent,$subject);

$cron = new Cron($thing);


class Cron
{
    function __construct(Thing $thing, $agent_input = null)
    {

		echo '<pre> cronhandler started running ';echo date("Y-m-d H:i:s");echo'</pre>';
		echo '<pre> cronhandler version redpanda 6 April 2018';echo'</pre>';

		$this->stack_idle_mode = 'use'; // Prevents stack generated execution when idle.

        $this->thing = $thing;

		$this->thing->flagGreen(); // Just make sure

        $arr = json_encode(array("to"=>null, "from"=>"tick", "subject"=>"s/ tick"));

        // use GearmanClient;
        // https://stackoverflow.com/questions/36787079/php-class-not-found-when-using-namespace

        $client= new \GearmanClient();
        $client->addServer();
        $client->doNormal("call_agent", $arr);

        // Capture the tick as quickly as possible. Alternatives below.

        // $client->doHighBackground("call_agent", $arr);

        // $tick_agent = new \Nrwtaylor\StackAgentThing\Tick($this->thing);
        // $tick_agent = new Tick($this->thing);

        $this->thing_report['sms'] = "CRON | Tick";
        $this->thing_report['help'] = "This Agent connects the computer's clock tick to the stack.";

        return;
    }
}


?>
