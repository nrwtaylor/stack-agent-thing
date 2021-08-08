<?php
namespace Nrwtaylor\StackAgentThing;

class Damage extends Agent
{
	public $var = 'hello';

    function init()
    {
		$this->test= "Development code";

        $this->default_damage_budget = $this->thing->container['api']['damage']['budget'];

        $this->variables_agent = new Variables($this->thing, "variables " . "damage " . $this->from);
        $this->current_time = $this->thing->json->time();

        $this->damage_budget = $this->default_damage_budget;
        $this->time_budget = $this->default_damage_budget; // because it takes time to destroy things //ms
        $this->shell_impact = 50;
        $this->shell_cost = 50;
	}

    public function run()
    {
        $this->value_destroyed = $this->doDamage();
    }

    public function set()
    {
        $this->variables_agent->setVariable("value_destroyed", $this->value_destroyed);
        $this->variables_agent->setVariable("things_destroyed", $this->things_destroyed);

        // I suppose we often fail to account for the cost of things.
        //$this->thing->setVariable("damage_cost", $this->damage_cost);

        $this->variables_agent->setVariable("refreshed_at", $this->current_time);
    }

	public function respond()
    {
		$this->thing->flagGreen();

		$to = $this->thing->from;
		$from = "damage";

        $this->makeSMS();

        $this->makeWeb();
        $choices = false;

		$this->thing_report[ "choices" ] = $choices;
 		$this->thing_report["info"] = "This damages a Thing's stack value.";
 		$this->thing_report["help"] = "This is about pruning the stack."; // sets operational limit

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
            $web_message .=  str_pad($t['nuuid'], 6 ," ");
            $web_message .= " " . str_pad($t['balance'], 10, " ");
            $web_message .= " " . str_pad($t['destroyed'], 10, " ");
            $web_message .= " " . str_pad($t['created_at'], 10, " ");

            if ($t['balance'] <= 0) {$web_message .= " CRITICAL HIT";}

            $web_message .= "<br>";
        }

        $web_message .=  "<br>";
        $web_message .= "Damage budget was " . $this->damage_budget . ".<br>";
        $web_message .= $this->value_destroyed . " of value destroyed.<br>";
        $web_message .= $this->things_destroyed . " Things destroyed.<br>";


        $this->web_message = $web_message;
        $this->thing_report['web'] = $this->web_message;

    }

    function makeSMS()
    {

        $message = "DAMAGE";
        $message .= " | " . $this->value_destroyed . " of value destroyed";
        $message .= " | " . $this->things_destroyed . " Things destroyed";

        $this->sms_message = $message;
        $this->thing_report['sms'] = $this->sms_message;
    }

	public function readSubject()
    {
        $input = strtolower($this->subject);

        $number_agent = new Number($this->thing,"number");
        $number_agent->extractNumber($input);
        if ($number_agent->number != false) {

            if ($number_agent->number == "Z") {$this->damage_budget = $this->default_damage_budget; return;}
            if ($number_agent->number == "X") {$this->damage_budget = $this->default_damage_budget; return;}

            $this->damage_budget = $number_agent->number;
        }

		return false;
    }

    function getThings($n = 1)
    {
        // Get a list of Things  in one go.  Saves time for database calls.
        $this->thing->log($this->agent_prefix . "start getThings.");

        // meep tries the second way of creating random row
        $thingreport = $this->thing->db->random(null,50); // Leaving this blank selects any record for deletion.
        $this->uuids = array();
        foreach($thingreport['things'] as $thing) {
            $this->uuids[] = $thing['uuid'];
        }
        $this->thing->log($this->agent_prefix . "completed getThings " . count($this->uuids) . ".");
    }

    function getThing()
    {
        if (!isset($this->uuids)) {$this->getThings();}
        $this->thing->log($this->agent_prefix . "start getThing.");

        $uuid = array_pop($this->uuids);

//        $uuid = $thingreport['things']->uuid; // Quest that random only returns one thing and this is misnamed

        $this->thing->log($this->agent_prefix . "got a random UUID ". $uuid .".");

        $thing = new Thing($uuid);
        $this->thing->log($this->agent_prefix . "instantiated Thing.");

        return $thing;

    }

    function doHit($thing = null)
    {
        if ($thing == null) {$thing = $this->getThing();}

        if ( $thing->isRed() ) {
            $this->thing->log($this->agent_prefix . "choose a Red flagged thing.  No action.");
            return;
        } // Don't remove a Thing that is working.

        // Get the stack balance.

        if ( isset($thing->account['stack']) ) {
            $stack_balance = $thing->account['stack']->balance;
            $this->thing->log($this->agent_prefix . "got a stack balance of " . $stack_balance['amount'] . ".");

        } else {
            $this->thing->log($this->agent_prefix . "did not get a stack balance. This is likely to be a legacy condition.");

            //echo "No stack balance";
            // Legacy condition.  Or forager.
            // Flip a coin
            $d2 = rand(1,2);
            if($d2 == 2) {
                $thing->Forget();
                $this->things_destroyed += 1;
                $this->thing->log($this->agent_prefix . " Forgot Thing.");

                return;
            } // Critical success
            return;
        }

        // The stack balance is distributed.  So debiting the
        // stack balance on the Thing destroys stack value.

        // Choice is how to do that.
        // So lets say our OP power is STR = 4.  Skill = 4 * 4 = 16. +1 +2.  19.
        // Roll D20.
        // If less than 19 then pass.  If 19.  Then fail.  If 20 critical success.

        // Fire the shell.
        $modifier = 7;
        $d20 = rand(1,20);

        $this->thing->log($this->agent_prefix . "rolled " . $d20 . " plus a modifier of " . $modifier . ".");


        if($d20 == 20) {
            $thing->Forget();
            $this->things_destroyed +=1;
            $this->thing->log($this->agent_prefix . "got a Critical Hit > Forgot Thing.");
            return;} // Critica$

        if($d20 == 1) {
            $this->thing->log($this->agent_prefix . "got a Critical Fail.  No action.");
            return;
        } // Critical fail

        $hit = round( ($modifier + $d20)/20  * $this->shell_impact);

        $thing->account['stack']->Debit($hit);

        // Get the stack balance of the thing.
        $updated_balance = $thing->account['stack']->balance;

        $this->thing->log($this->agent_prefix . " damage scored = ".  $hit . '. ' . $updated_balance['amount'] . ' units left.');

        if ( $updated_balance['amount'] < 0 ) {
            $thing->Forget();
            $this->things_destroyed += 1;
            $this->thing->log($this->agent_prefix . "scored ".  $hit . '.');
            $this->thing->log($this->agent_prefix . " Forgot Thing " . $thing->uuid.  ".");
        }

        return $hit;
    }

    function doDamage($damage_budget = null)
    {
        $this->things_destroyed = 0;
        $this->split_time = $this->thing->elapsed_runtime();
        if ($damage_budget == null) {$damage_budget = $this->damage_budget;}
        $remaining_budget = $damage_budget;

        $this->things = array();
        do {

            // Acquire a shell.
            // Is there enough remaining of the damage_budget to buy another shell?
            if ($remaining_budget < $this->shell_cost ) {
                // $value_destroyed = $damage_budget - $remaining_budget;
                // echo "Value destroyed: " . $value_destroyed;
                // return $value_destroyed;
                break;
            }

            $remaining_budget -= $this->shell_cost;

            // Select a random target and fire a 50 shell at it.
            $thing = $this->getThing();
            $destroyed = $this->doHit($thing);
            if (!isset($thing->account['stack']->balance['amount'])) {
                $balance = null;
            } else {
                $balance = $thing->account['stack']->balance['amount'];
            }

            if (!isset($thing->thing->created_at)) {$created_at = null;} else {
                $created_at = $thing->thing->created_at;
            }
            // echo $thing->thing->created_at;
            $this->things[] = array("nuuid"=>$thing->nuuid,"balance"=> $balance,
                "destroyed"=>$destroyed,
                "created_at"=>$created_at);
            // So make sure at least one hit runs, then check whether time limit is up.
            // A unit of damage is 1s.  So apply maximum 1s.  (Or one shell.)
        } while ($this->thing->elapsed_runtime() - $this->split_time < $this->time_budget);

        $value_destroyed = $damage_budget - $remaining_budget;

        $this->thing->log($this->agent_prefix . " damage cost = ".  $value_destroyed . '.');

        //echo "Value destroyed: " . $value_destroyed;

        $this->value_destroyed = $value_destroyed;
        //$this->thing_destroyed = $things_destroyed;

        return $value_destroyed;
    }

}
