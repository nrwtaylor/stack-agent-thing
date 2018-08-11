<?php
namespace Nrwtaylor\StackAgentThing;
require '/var/www/stackr.test/vendor/autoload.php';
//require '/var/www/html/stackr.ca/vendor/autoload.php';

$GLOBALS['stack'] = '/var/www/stackr.test/';
//$GLOBALS['stack'] = '/var/www/html/stackr.ca/';

//use GearmanClient;
//https://stackoverflow.com/questions/36787079/php-class-not-found-when-using-namespace

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);


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

        //$arr = json_encode(array("to"=>$from, "from"=>$stack_agent, "subject"=>$subject));
        //$arr = json_encode(array("uuid"=>$this->thing->uuid));
        //$arr = json_encode(array("to"=>$body['msisdn'], "from"=>$body['to'], "subject"=>$body['text']));

//        $arr = json_encode(array("to"=>null, "from"=>"tick", "subject"=>"s/ tick"));

    //            $client= new GearmanClient();
  //              $client->addServer();
                //$client->doNormal("call_agent", $arr);
//                $client->doHighBackground("call_agent", $arr);


        $tick_agent = new \Nrwtaylor\StackAgentThing\Tick($this->thing);

/*
        $client= new \GearmanClient();
        $client->addServer();
        //$client->doNormal("call_agent", $arr);
        $client->doLowBackground("call_agent", $arr);
*/
        //exit();

        $this->thing_report['sms'] = "CRON | Tick";
        $this->thing_report['help'] = "This Agent connects the computer's clock tick to the stack.";

        return;

    }
}


?>
