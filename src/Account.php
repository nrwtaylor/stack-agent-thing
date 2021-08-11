<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("allow_url_fopen", 1);

class Account
{
    public $var = "hello";

    function __construct($uuid, $account_uuid, $account_name)
    {
        $this->json = new Json($uuid);

        $settings = require $GLOBALS["stack_path"] . "private/settings.php";

        $this->uuid = $uuid;

        $this->container = new \Slim\Container($settings);

        $this->test = "Development code";

        // Provide access to stack settings.
        $this->container["stack"] = function ($c) {
            $db = $c["settings"]["stack"];
            return $db;
        };

        // Because a Thing can only get instantiating messages from agents.

        $this->account_name = $account_name;
        $this->account_uuid = $account_uuid;
        //$this->state = true;

        $this->choice = new \Nrwtaylor\StackAgentThing\Choice($this->uuid);
        $this->node_list = [
            // choice
            "open" => ["credit", "debit", "close"], // only states available
            [
                // only state available
                "credit" => "open",
            ], // only state available
            ["debit" => "open"], // end state
            [
                // choice
                "close" => "open",
            ],
        ]; // end state // end

        // Behaviour is that the line above needs to be modified first.
        // First state is open because the choice has already been made
        // in calling this stack code.  There is no other first option.

        $this->previous_state = $this->choice->load($account_name);

        if ($this->previous_state != true) {
            // True is not a valid state for a Thing;
            $this->state = "open";
        } else {
            $this->state = "open";
        }

        $this->choice->Create(
            $this->account_name,
            $this->node_list,
            $this->state
        );

        // At this point we have a working accounting agent which we can
        // send choice commands to.
        // Find out what the choices are.

        $test = $this->choice->getChoices();

        $message = [
            "message0" => "",
            "message1" => false,
            "message2" => false,
            "message3" => false,
            "message4" => false,
            "message5" => false,
            "message6" => false,
            "message7" => true,
        ];

        //		echo "meep. okay";

        // Signal Thing is working on it.

        //$this->loadBalance();
        //$this->balance = 0.0;

        //$this->state = true;
        return;
    }

    function Create($balance)
    {
        // Here and credit are where the balance gets saved.

        if ($balance == null) {
            // First check if there is an existing balance.
            $this->balance = $this->loadBalance();

            // Still not found?  Then this really is a new account.
            if ($this->balance == null) {
                $null_balance = [
                    "amount" => 0,
                    "attribute" => "scalar",
                    "unit" => "none",
                ];
                $this->balance = $null_balance;
            }
        } else {
            if (
                is_numeric($balance["amount"]) and
                is_string($balance["attribute"]) and
                is_string($balance["unit"])
            ) {
                $this->balance = $balance;
                unset($this->balance["account_name"]);
            } else {
                throw new Exception(
                    "Needs development for cases where != numeric, string, string balance."
                );
            }
        }

        $this->saveBalance();

        return;
    }

    function Credit(float $credit_amount)
    {
        //echo "Credit";

        //echo "state" . $this->state ."<br>";
        //echo "credit amount". $credit_amount . "<br>";

        if ($this->state == "open") {
            //echo $this->balance['amount'];

            $this->balance["amount"] =
                $this->balance["amount"] + $credit_amount;

            $this->saveBalance();
        } else {
            // Return false in line with Thing commmunication.
            return false;
            //throw new Exception('Account cannot be charged to.');
        }

        return $this->account_name .
            " " .
            $this->balance["amount"] .
            " " .
            $this->balance["unit"];
    }

    function Debit(float $debit_amount)
    {
        $this->Credit(-$debit_amount);
        return;
    }

    function Distribution($distribution_type)
    {
        // Need to think this through.
        // I think the distribution is done by the agent.

        // Distribution types
        switch ($i) {
            case "stack":
                break;
            case "uniform":
                break;
            case "stochastic":
                break;
            default:
            // Anything else
        }
        return;
    }

    function Destroy(float $value)
    {
        echo "Not tested.";
        return $this->Credit(-$value);
    }

    function Close()
    {
        echo "Not implemented";
        $this->state = false;
        return;
    }

    function Open()
    {
        echo "Not implemented";
        $this->state = true;
        return;
    }

    // Following functions conceptualize "LOAD" and "SAVE" to a json file
    // per BASIC.  I will save the get and set language for the JSON<->DB
    //

    // Theis loads and saves a calculated balance that is stored in variables
    // Since each time a Thing is loaded it re-builds it's balance
    // from other Things, these are calculated and NOT stored in settings.

    // Loading from settings would make it an authoritative statement.
    // Which it shouldn't be.

    function loadBalance()
    {
        $this->json->setField("variables");
        $this->balance = $this->json->readVariable([
            "account",
            $this->account_uuid,
            $this->account_name,
        ]);

        return $this->balance;
    }

    function saveBalance()
    {
        $var_path = ["account", $this->account_uuid, $this->account_name];

        $this->json->setField("variables");

        $m = $this->json->writeVariable($var_path, $this->balance);

        return;
    }

    function isOpen()
    {
        return $this->state;
        return;
    }
}
