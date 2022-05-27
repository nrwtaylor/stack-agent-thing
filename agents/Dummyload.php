<?php
namespace Nrwtaylor\StackAgentThing;

class Dummyload extends Agent
{
    public $var = "hello";

    public function init()
    {

        $this->default_dummyload_budget =
            $this->thing->container["api"]["dummyload"]["budget"];

        $this->dummyload_budget = $this->default_dummyload_budget;
        $this->time_budget = 10000; //ms
        $this->dummyload_cost = 1;

        $this->value_created = $this->doDummyload();

    }


    public function set()
    {
        $this->thing->Write(
            ["dummyload", "value_created"],
            $this->value_created
        );
        $this->thing->Write(
            ["dummyload", "things_created"],
            $this->things_created
        );
        $this->thing->Write(
            ["dummyload", "refreshed_at"],
            $this->current_time
        );



    }

    public function respondResponse()
    {
        if ($this->agent_input != null) {
            return;
        }

        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["info"] = "This damages a Thing's stack value.";
        $this->thing_report["help"] = "This is about pruning the stack.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeWeb()
    {
        $web_message = '<p class="description">';
        foreach ($this->things as $t) {
            $web_message .= str_pad($t["nuuid"], 6, " ");
            $web_message .= " " . str_pad($t["balance"], 10, " ");
            $web_message .= " " . str_pad($t["created"], 10, " ");
            $web_message .= " " . str_pad($t["created_at"], 10, " ");

            $web_message .= "<br>";
        }

        $this->web_message = $web_message;
        $this->thing_report["web"] = $this->web_message;
    }

    function makeSMS()
    {
        $message = "DUMMYLOAD";
        $message .= " | " . $this->value_created . " of value createed";
        $message .= " | " . $this->things_created . " Things created";

        $this->sms_message = $message;
        $this->thing_report["sms"] = $this->sms_message;
    }

    public function readSubject()
    {
    }

    function doLoad($thing = null)
    {
        $this->thing->log($this->agent_prefix . "made a datagram.");

        $client = new \GearmanClient();

        $arr = json_encode([
            "to" => "test@stackr.ca",
            "from" => "hey",
            "subject" => "hey (test dummyload)",
        ]);

        // Add a server
        $client->addServer(); // by default host/port will be "localhost" & 4730

        $this->thing->log("Dummyload sent to Gearman as doNormal.");

        // Send reverse job
        //        $result = $client->doNormal("call_agent", $arr);
        $result = $client->doLowBackground("call_agent", $arr);

        if ($result) {
        }

        return;
    }

    function doDummyload($dummyload_budget = null)
    {
        $this->things_created = 0;

        $this->split_time = $this->thing->elapsed_runtime();

        if ($dummyload_budget == null) {
            $dummyload_budget = $this->dummyload_budget;
        }
        $remaining_budget = $dummyload_budget;

        $this->things = [];
        do {
            // Acquire a shell.
            // Is there enough remaining of the damage_budget to buy another shell?
            if ($remaining_budget < $this->dummyload_cost) {
                break;
            }

            $remaining_budget -= $this->dummyload_cost;

            // Select a random target and fire a 50 shell at it.
            $created = $this->doLoad();

            if (!isset($thing->account["stack"]->balance["amount"])) {
                $balance = null;
            } else {
                $balance = $thing->account["stack"]->balance["amount"];
            }
            $this->things[] = [
                "nuuid" => "not returned",
                "balance" => $balance,
                "created" => $created,
                "created_at" => "not returned",
            ];

            // So make sure at least one hit runs, then check whether time limit is up.
            // A unit of damage is 1s.  So apply maximum 1s.  (Or one shell.)
        } while (
            $this->thing->elapsed_runtime() - $this->split_time <
            $this->time_budget
        );

        $value_created = $dummyload_budget - $remaining_budget;

        $this->thing->log(
            $this->agent_prefix . " dummyload cost = " . $value_created . "."
        );


        $this->value_created = $value_created;

        return $value_created;
    }
}
