<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

/*
print gearman_version() . "\n";

$thing = new Thing();
$t = new Manager($thing);
$s = $t->getStatus();
var_dump($s);
exit();
// Taken from https://stackoverflow.com/questions/2752431/any-way-to-access-gearman-administration
*/
class Manager {

    /**
     * @var string
     */
    public $host = "127.0.0.1";
    /**
     * @var int
     */
    public $port = 4730;

    /**
     * @param string $host
     * @param int $port
     *//*
    public function __construct($host=null,$port=null){
        if( !is_null($host) ){
            $this->host = $host;
        }
        if( !is_null($port) ){
            $this->port = $port;
        }
    }
*/

    public function __construct(Thing $thing, $agent_input = null) {

        $host = "127.0.0.1";
        $port = 4730;

        if( !isset($host) ){
            $this->host = $host;
        }
        if( !isset($port) ){
            $this->port = $port;
        }


        $this->agent_input = $agent_input;

        $this->agent_name = "manager";
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
        $this->test= "Development code";

        $this->thing = $thing;
        $this->thing_report['thing']  = $thing;

        $this->start_time = $this->thing->elapsed_runtime(); 

        $command_line = null;

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        $this->node_list = array("nuuid"=>array("nuuid"));

        // Get some stuff from the stack which will be helpful.
        $this->web_prefix = $thing->container['stack']['web_prefix'];
        $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        $this->word = $thing->container['stack']['word'];
        $this->email = $thing->container['stack']['email'];

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->haystack = $thing->uuid . 
        $thing->to . 
        $thing->subject . 
        $command_line .
        $this->agent_input;

$this->queue_engine_version = gearman_version();

$s = $this->getStatus();
$this->queued_jobs = $s['operations']['call_agent']['total'];
$this->workers_running = $s['operations']['call_agent']['running'];
$this->workers_connected = $s['operations']['call_agent']['connectedWorkers'];


// Fire off a test message via Gearman
        $arr = json_encode(array("to"=>"console", "from"=>"manager", "subject"=>"ping"));
        $client= new \GearmanClient();
        $client->addServer();
//        $client->doNormal("call_agent", $arr);
        $client->doHighBackground("call_agent", $arr);
//        var_dump($client);
$this->response = "Gearman snowflake worker started.";



//echo $this->queued_jobs ." " . $this->workers_running . " of " . $this->workers_connected . " workers (" . $this->queue_engine_version . ")";
//exit();


        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.', "INFORMATION");
        $this->thing->log($this->agent_prefix . 'received this Thing "'.  $this->subject . '".', "DEBUG");


        $this->current_time = $this->thing->time();

        $this->thing->log( $this->agent_prefix .'completed init. Timestamp = ' . number_format($this->thing->elapsed_runtime()) .  'ms.');

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("manager", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable( array("manager", "refreshed_at"), $time_string );
        }

        $this->readSubject();

        //$this->init();

        if ($this->agent_input == null) {$this->respond();}

        $this->set();

        $this->thing->log( $this->agent_prefix .'completed setSignals.');

 //       $this->makePNG();

        $this->thing->log( $this->agent_prefix .'completed setSnowflake.');


        $this->thing->log( $this->agent_prefix .'ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.');


        $this->thing_report['log'] = $this->thing->log;


        return;
    }


    /**
     * @return array | null
     */
    public function getStatus(){
        $status = null;
        $handle = fsockopen($this->host,$this->port,$errorNumber,$errorString,30);
        if($handle!=null){
            fwrite($handle,"status\n");
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                if( $line==".\n"){
                    break;
                }
                if( preg_match("~^(.*)[ \t](\d+)[ \t](\d+)[ \t](\d+)~",$line,$matches) ){
                    $function = $matches[1];
                    $status['operations'][$function] = array(
                        'function' => $function,
                        'total' => $matches[2],
                        'running' => $matches[3],
                        'connectedWorkers' => $matches[4],
                    );
                }
            }
            fwrite($handle,"workers\n");
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                if( $line==".\n"){
                    break;
                }
                // FD IP-ADDRESS CLIENT-ID : FUNCTION
                if( preg_match("~^(\d+)[ \t](.*?)[ \t](.*?) : ?(.*)~",$line,$matches) ){
                    $fd = $matches[1];
                    $status['connections'][$fd] = array(
                        'fd' => $fd,
                        'ip' => $matches[2],
                        'id' => $matches[3],
                        'function' => $matches[4],
                    );
                }
            }
            fclose($handle);
        }

        return $status;
    }

    function getManager() 
    {
$this->queue_engine_version = gearman_version();

$s = $this->getStatus();
$this->queued_jobs = $s['operations']['call_agent']['total'];
$this->workers_running = $s['operations']['call_agent']['running'];
$this->workers_connected = $s['operations']['call_agent']['connectedWorkers'];


    }

    private function set()
    {

        $this->current_time = $this->thing->time();

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("manager", "refreshed_at") );

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->time();
            $this->thing->json->writeVariable( array("manager", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);

        $this->thing->json->setField("variables");
//        $queue_time = $this->thing->json->readVariable( array("manager", "queued_jobs") );
//        $run_time = $this->thing->json->readVariable( array("manager", "workers_running") );


        if ($this->queue_engine_version == false) {
            $this->getManager();

            $this->readSubject();

            $this->thing->json->writeVariable( array("manager", "queued_jobs"), $this->queued_jobs );
            $this->thing->json->writeVariable( array("manager", "workers_running"), $this->workers_running );
            $this->thing->json->writeVariable( array("manager", "workers_connected"), $this->workers_connected );

        }
    }

    function readSubject() {}

    private function respond()
    {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        $to = $this->thing->from;
        $from = "manager";

        //$subject = 's/ manager '. $this->current_state; 
        //$message = 'Latency checker.';

        $received_at = strtotime($this->thing->thing->created_at);

        $ago = $this->thing->human_time ( time() - $received_at );

        $this->makeChoices();
        $this->makeSMS();

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;


        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->thing_report['keyword'] = 'pingback';
        $this->thing_report['help'] = 'Latency is how long the message is in the stack queue.';

        return $this->thing_report;
    }

    function makeSMS()
    {
//        $this->getQueuetime();
//        $rtime = $this->thing->elapsed_runtime() - $this->start_time;

        $this->node_list = array("manager"=>array("managergraph"));

//echo $this->queued_jobs ." " . $this->workers_running . " of " . $this->workers_connected . " workers (" . $this->queue_engine_version . ")";
//exit();


        $this->sms_message = "MANAGER";
        $this->sms_message .= " | queued jobs " . number_format($this->queued_jobs) . "";
        $this->sms_message .= " | workers running " . number_format($this->workers_running). ""; 
        $this->sms_message .= " | workers connected " . number_format($this->workers_connected). ""; 
        $this->sms_message .= " | queue version " . $this->queue_engine_version. ""; 

        $this->sms_message .= " | TEXT LATENCY";
        $this->thing_report['sms'] = $this->sms_message;

    }

    public function makeChoices()
    {

        if ($this->from == "null@stackr.ca") {
            $this->thing->choice->Create($this->agent_name, $this->node_list, "null");
            $choices = $this->thing->choice->makeLinks("null");
        } else {
            $this->thing->choice->Create($this->agent_name, $this->node_list, "manager");
            $choices = $this->thing->choice->makeLinks('manager');
        }

        $this->thing_report['choices'] = $choices;
        return;
    }


}

//print gearman_version() . "\n";


?>
