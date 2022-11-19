<?php
namespace Nrwtaylor\StackAgentThing;

error_reporting(E_ALL);
ini_set("display_errors", 1);

class Balance extends Agent
{
    function init()
    {
        if ($this->thing->container["stack"]["state"] == "dev") {
            $this->test = true;
        }
        $this->stack_uuid = $this->thing->container["stack"]["uuid"];
        $this->node_list = ["start"];
    }

    public function run()
    {
        $this->getBalance();
    }

    public function getBalance()
    {
        $this->thing->db->setUser($this->from);
        $thingreport = $this->thing->db->userSearch(""); // Designed to accept null as $this->uuid.

        $things = $thingreport["thing"];

        // May need to check for empty array.

        $stack_balance = 0;
        $thing_balance = 0;

        $start_time = time();

        if ($things === false) {
            return;
        }

        foreach (array_reverse($things) as $thing_object) {
            $uuid = $thing_object["uuid"];

            $variables_json = $thing_object["variables"];
            $variables = $this->thing->json->jsontoArray($variables_json);

            if (isset($variables["account"])) {
                foreach ($variables["account"] as $uuid => $arr) {
                    if (isset($arr["stack"]) and $uuid == $this->stack_uuid) {
                        $stack_amount =
                            $variables["account"][$this->stack_uuid]["stack"][
                                "amount"
                            ];
                        $stack_balance += $stack_amount;
                    }

                    if (isset($arr["thing"])) {
                        $thing_amount =
                            $variables["account"][$uuid]["thing"]["amount"];
                        $thing_balance += $thing_amount;
                    }
                }
            }
        }

        $end_time = time();

        $this->stack_balance = $stack_balance;
        $this->thing_balance = $thing_balance;
    }

    public function respond()
    {
        // Develop the various messages for each channel.

        // Thing actions
        $this->thing->flagGreen();

        $this->makeSMS();

        $this->message = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];
    }

    public function readSubject()
    {
    }

    public function makeSMS()
    {
        $this->sms_message = "BALANCE | ";
        if (isset($this->stack_balance)) {
            $this->sms_message .=
                number_format($this->stack_balance) . " units";
        } else {
            $this->sms_message .= "Balance not available.";
        }
        $this->sms_message .= " | TEXT AGE";

        $this->thing_report["sms"] = $this->sms_message;
    }
}
