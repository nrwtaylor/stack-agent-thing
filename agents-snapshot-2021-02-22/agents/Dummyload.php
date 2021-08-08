<?php
namespace Nrwtaylor\StackAgentThing;

class Dummyload
{
	public $var = 'hello';

    function __construct(Thing $thing, $text = null)
    {
        $this->start_time = $thing->elapsed_runtime();

		$this->agent_name = 'dummyload';
        $this->agent_prefix = 'Agent "' . ucwords($this->agent_name) . '" ';
		$this->test= "Development code";

        $this->agent_input = $text;

//      This is how old roll.php is.
//		$thingy = $thing->thing;
		$this->thing = $thing;
        $this->thing_report['thing']  = $thing;

        $this->uuid = $thing->uuid;
        $this->to = $thing->to;
        $this->from = $thing->from;
        $this->subject = $thing->subject;

        $this->default_dummyload_budget = $this->thing->container['api']['dummyload']['budget'];

        $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.');
        $this->thing->log($this->agent_prefix . "received this Thing ".  $this->subject . '".');

        //$this->variables_agent = new Variables($this->thing, "variables " . "damage " . $this->from);
        $this->current_time = $this->thing->time();

        $this->dummyload_budget = $this->default_dummyload_budget;
        $this->time_budget = 10000; //ms
        $this->dummyload_cost = 1;

        $this->value_created = $this->doDummyload();

        $this->Set();

		$this->readSubject();

		if ($this->agent_input == null) {$this->respond();}

        $this->thing->log( $this->agent_prefix .'ran for ' . number_format( $this->thing->elapsed_runtime() - $this->start_time ) . 'ms.' );
        $this->thing_report['log'] = $this->thing->log;

		return;
	}


// -----------------------

    function Set() {

        //$this->variables_agent->setVariable("value_created", $this->value_destroyed);
        //$this->variables_agent->setVariable("things_created", $this->things_destroyed);

        //$this->thing->setVariable("damage_cost", $this->damage_cost);

        //$this->variables_agent->setVariable("refreshed_at", $this->current_time);

/*
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("dummyload", "value_created"), $this->value_created);
        $this->thing->json->writeVariable(array("dummyload", "things_created"), $this->things_created);
        $this->thing->json->writeVariable(array("dummyload", "refreshed_at"), $this->current_time);
*/

        //$this->thing->json->setField("variables");
        $this->thing->variables->writeVariable(array("dummyload", "value_created"), $this->value_created);
        $this->thing->variables->writeVariable(array("dummyload", "things_created"), $this->things_created);
        $this->thing->variables->writeVariable(array("dummyload", "refreshed_at"), $this->current_time);




    }



	private function respond() {


		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "dummyload";

//        $response = $input . "Try " . strtoupper($v) . ".";

        //$this->sms_message = "TIMEOUT";

//        if ($this->agent_input != null) {
//            $this->sms_message = "" . $this->cat_message;
//        }

//        $this->sms_message .= " | " . number_format( $this->thing->elapsed_runtime() ) . "ms.";

        $this->makeSMS();

        $this->makeWeb();
        $choices = false;

		$this->thing_report[ "choices" ] = $choices;
 		$this->thing_report["info"] = "This damages a Thing's stack value."; 
 		$this->thing_report["help"] = "This is about pruning the stack.";

		//$this->thing_report['sms'] = $this->sms_message;
		$this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

		return $this->thing_report;
	}

    function makeWeb()
    {
        $web_message = '<p class="description">';
        foreach ($this->things as $t) {
            //var_dump($t);
            $web_message .=  str_pad($t['nuuid'], 6 ," ");
            $web_message .= " " . str_pad($t['balance'], 10, " ");
            $web_message .= " " . str_pad($t['created'], 10, " ");
            $web_message .= " " . str_pad($t['created_at'], 10, " ");

            $web_message .= "<br>";
        }

        $this->web_message = $web_message;
        $this->thing_report['web'] = $this->web_message;

    }

    function makeSMS()
    {
        $message = "DUMMYLOAD";
        $message .= " | " . $this->value_created . " of value createed";
        $message .= " | " . $this->things_created . " Things created";

        $this->sms_message = $message;
        $this->thing_report['sms'] = $this->sms_message;
    }

	public function readSubject()
    {
        //$input = strtolower($this->subject);
		return false;
    }

/*
    function getThing()
    {

        // meep tries the second way of creating random row
        $thingreport = $this->thing->db->random("meep");
        $uuid = $thingreport['things']->uuid; // Quest that random only returns one thing and this is misnamed
        $thing = new Thing($uuid);

        return $thing;

    }
*/

    function doLoad($thing = null)
    {

        $this->thing->log($this->agent_prefix . "made a datagram.");

        $client = new \GearmanClient();

        $arr = json_encode(array("to"=>"test@stackr.ca", "from"=>"hey", "subject"=>"hey (test dummyload)"));

        // Add a server
        //$client->addServer(); // by default host/port will be "localhost" & 4730
        $client->addServer(); // by default host/port will be "localhost" & 4730

        $this->thing->log( "Dummyload sent to Gearman as doNormal.");

        // Send reverse job
//        $result = $client->doNormal("call_agent", $arr);
        $result = $client->doLowBackground("call_agent", $arr);


        if ($result) {
//            echo "Success: $result\n";
        }




        return;

    }

    function doDummyload($dummyload_budget = null)
    {
        $this->things_created = 0;

        $this->split_time = $this->thing->elapsed_runtime();

        if ($dummyload_budget == null) {$dummyload_budget = $this->dummyload_budget;}
        $remaining_budget = $dummyload_budget;

        $this->things = array();
        do {

            // Acquire a shell.
            // Is there enough remaining of the damage_budget to buy another shell?
            if ($remaining_budget < $this->dummyload_cost ) {
//            $value_destroyed = $damage_budget - $remaining_budget;
//            echo "Value destroyed: " . $value_destroyed;
//            return $value_destroyed;
                break;
            }

            $remaining_budget -= $this->dummyload_cost;

            // Select a random target and fire a 50 shell at it.
//            $thing = $this->getThing();
//            $created = $this->doLoad($thing);
            $created = $this->doLoad();


if (!isset($thing->account['stack']->balance['amount'])) {$balance = null;} else {
$balance = $thing->account['stack']->balance['amount'];
}
//echo $thing->thing->created_at;
            $this->things[] = array("nuuid"=>"not returned","balance"=> $balance,
                 "created"=>$created,
                 "created_at"=>"not returned");

//                 "created_at"=>$thing['created_at']);
            //$this->thing->log($this->agent_prefix . "got a stack balance of " . $stack_balance['amount'] . ".");

            // So make sure at least one hit runs, then check whether time limit is up.
            // A unit of damage is 1s.  So apply maximum 1s.  (Or one shell.)
        } while ($this->thing->elapsed_runtime() - $this->split_time < $this->time_budget);


        $value_created = $dummyload_budget - $remaining_budget;

        $this->thing->log($this->agent_prefix . " dummyload cost = ".  $value_created . '.');

        //echo "Value destroyed: " . $value_destroyed;

        $this->value_created = $value_created;
        //$this->thing_destroyed = $things_destroyed;

        return $value_created;




    }

}

?>
