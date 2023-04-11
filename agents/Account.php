<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("allow_url_fopen", 1);

class Account extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->json = new Json(null, ['uuid'=>$this->uuid, 'from'=>'null' . $this->mail_postfix]);
//        $this->json = new Json(null, $this->uuid);

        $settings = require $GLOBALS["stack_path"] . "private/settings.php";

        //    $this->uuid = $uuid;

        $this->container = new \Slim\Container($settings);

        $this->test = "Development code";

        // Provide access to stack settings.
        $this->container["stack"] = function ($c) {
            $db = $c["settings"]["stack"];
            return $db;
        };

        // Because a Thing can only get instantiating messages from agents.

//        $this->account_name = $account_name;

//        $this->account_uuid = $account_uuid;
        //$this->state = true;

        //$this->choice = new \Nrwtaylor\StackAgentThing\Choice($this->uuid);
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

/*
        $this->previous_state = $this->loadChoice($account_name);

        if ($this->previous_state != true) {
            // True is not a valid state for a Thing;
            $this->state = "open";
        } else {
            $this->state = "open";
        }
*/
/*
        $this->createChoice(
            $this->account_name,
            $this->node_list,
            $this->state
        );
*/
        // At this point we have a working accounting agent which we can
        // send choice commands to.
        // Find out what the choices are.
/*
        $test = $this->getChoices();

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
*/
    }
    public function getAccount($text = null)
    {
        if ($text != null) {
            $t = $this->getAccountbyId($text);
            return;
        }

if (!isset($this->thing->thing->variables)) {
$this->response .= 'No stack found. ';
return true;}

        if (
            isset(
                $this->thing->json->jsontoArray($this->thing->thing->variables)[
                    'account'
                ]
            )
        ) {
            // First is there a account in this thing.
            $account = $this->thing->json->jsontoArray(
                $this->thing->thing->variables
            )['account'];

            $account_id = "X";
            if (isset($this->account['id'])) {
                $this->account_id = $account['id'];
                $this->response .= "Saw " . $this->account_id . ". ";

                $this->getAccountbyId($this->account_id);
                return;
            }

            $account_id = "X";
            if (isset($account['uuid'])) {
                $this->account_id = $this->idAccount($account['uuid']);
                $this->response .= "Saw " . $this->account_id . ". ";

                $this->getAccountbyUuid($account['uuid']);
                return;
            }

            if (isset($this->account['refreshed_at'])) {
                $this->account_id = $this->thing->uuid;

                $this->response .= "Saw an account in the thing. ";

                $this->getAccountbyId($this->account_id);
                return;
            }

            // Get the most recent signal command.
            //return;
        }
        // Haven't found the signal in the thing.

        if (!isset($this->accounts)) {
            $this->getAccounts();
        }

        foreach ($this->accounts as $i => $account) {

            if (isset($account['uuid'])) {
                $flag = $this->getAccountbyUuid($account['uuid']);
                return;
            }

            if (isset($account['id'])) {
                $flag = $this->getAccountbyId($account['id']);
                return;
            }
        }

        $this->response .= "Did not find an account. ";

        // Can't find a account.
        return false;
    }


    function getAccounts()
    {
        $this->accountid_list = [];
        $this->accounts = [];

        $things = $this->getThings('account');
        if ($things === null) {
            return;
        }
        if ($things === true) {
            return;
        }
        $count = count($things);
        // See if a headcode record exists.
        //$findagent_thing = new Findagent($this->thing, 'signal');
        //$count = count($findagent_thing->thing_report['things']);
        $this->thing->log('Agent "Account" found ' . $count . " account Things.");

        if (!$this->is_positive_integer($count)) {
            // No signals found
        } else {
            foreach (array_reverse($things) as $uuid => $thing) {
                $associations = $thing->associations;

                $account = [];
                $account["associations"] = $associations;

                $variables = $thing->variables;
                if (isset($variables['account'])) {
                    if (isset($variables['account']['refreshed_at'])) {
                        $account['refreshed_at'] =
                            $variables['account']['refreshed_at'];
                    }

                    if (isset($variables['account']['text'])) {
                        $account['text'] = $variables['account']['text'];
                    }

                    if (isset($variables['account']['state'])) {
                        $account['state'] = $variables['account']['state'];
                    }

                    $account["uuid"] = $uuid;
                    $account["id"] = $this->idAccount($uuid);

                    $this->accounts[] = $account;
                    $this->accountid_list[] = $uuid;
                }
            }
        }

        $refreshed_at = [];
        foreach ($this->accounts as $key => $row) {
            $refreshed_at[$key] = $row['refreshed_at'];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->accounts);

        return [$this->accountid_list, $this->accounts];
    }



    public function get()
    {
/*
        $this->channel = new Channel($this->thing, "channel");
        $this->channel_name = $this->channel->channel_name;

        if (is_string($this->channel_name)) {
            $this->response .= "Saw channel is " . $this->channel_name . ". ";
        } else {
            $this->response .= "No channel name. ";
        }
*/
        $this->getAccount();

if (!isset($this->accounts)) {return true;}

        foreach ($this->accounts as $i => $account) {
            if ($account['uuid'] == $this->account_thing->uuid) {

                $this->account_thing->state = $account['state'];
                return;
            }
        }
    }


    public function balanceAccount($account_name = null)
    {
        if ($account_name == null) {
            $account_name = $this->account_name;
        }

        if (!$this->isAccount($account_name)) {return true;}
        return $this->accounts[$account_name]['amount'];
    }


    // Take in a uuid and convert it to a account id (id here).
    function idAccount($text = null)
    {
        $account_id = $text;
        if ($text == null) {
            if (isset($this->account_thing->uuid)) {
                $account_id = $this->account_thing->uuid;
            }
        }

        $t = hash('sha256', $account_id);
        $t = substr($t, 0, 4);
        return $t;
    }


    function getAccountbyUuid($uuid)
    {
        if ($this->channel_name == 'web') {
            $id = $this->idAccount($uuid);
            $this->getAccountbyId($id);
            return;
        }
        $thing = new Thing($uuid);
        if ($thing->thing == false) {
            $this->account_thing = false;
            $this->account_id = null;
            return true;
        }

        $account = $this->thing->json->jsontoArray($thing->thing->variables)[
            'account'
        ];

        $this->account_thing = $thing;
        $this->account_id = $thing->uuid;

        if (isset($account['state'])) {
            $this->account_thing->state = $account['state'];
        }
    }

    function setAccount($text = null)
    {
        if (!isset($this->account_thing)) {
            return true;
        }

        $this->account_thing->Write(
            ["account"],
            $this->account
        );
// ?
        $this->account_thing->Write(
            ["account", "refreshed_at"],
            $this->current_time
        );

        $this->account_thing->associate($this->account_thing->uuid);
    }

    public function getAccountbyName($name)
    {
        if (!isset($this->accounts)) {
            $this->getAccounts();
        }
        $matched_uuids = [];
        foreach ($this->accounts as $i => $account) {
            if ($account['name'] == $name) {
                $matched_uuids[] = $account['uuid'];
                continue;
            }

            //if ($this->idAccount($account['uuid']) == $id) {
            //    $matched_uuids[] = $account['uuid'];
            //    continue;
            //}
        }
        if (count($matched_uuids) != 1) {
            return true;
        }

        $uuid = $matched_uuids[0];

        $this->account_thing = new Thing($uuid);

        $account = $this->thing->json->jsontoArray(
            $this->account_thing->thing->variables
        )['account'];

        //$this->account_id = $this->account_thing->uuid;
        $this->account_name = $account['name'];

        if (isset($account['state'])) {
            $this->account_thing->state = $account['state'];
        }
    }



    public function getAccountbyId($id)
    {
        if (!isset($this->accounts)) {
            $this->getAccounts();
        }
        $matched_uuids = [];
        foreach ($this->accounts as $i => $account) {
            if ($account['id'] == $id) {
                $matched_uuids[] = $account['uuid'];
                continue;
            }

            if ($this->idAccount($account['uuid']) == $id) {
                $matched_uuids[] = $account['uuid'];
                continue;
            }
        }
        if (count($matched_uuids) != 1) {
            return true;
        }

        $uuid = $matched_uuids[0];

        $this->account_thing = new Thing($uuid);

        $account = $this->thing->json->jsontoArray(
            $this->account_thing->thing->variables
        )['account'];

        $this->account_id = $this->account_thing->uuid;

        if (isset($account['state'])) {
            $this->account_thing->state = $account['state'];
        }
    }

    public function set()
    {
        if (!isset($this->account_thing)) {
            //$this->signal_thing = $this->thing;
            // Nothing to set
            //return true;
        }

        if (isset($this->account_thing->state)) {
            $this->account['state'] = $this->account_thing->state;
        }

        if (isset($this->account_thing->name)) {
            $this->account['name'] = $this->account_thing->name;
        }


        if (isset($this->account_thing->uuid)) {
            $this->account['id'] = $this->idAccount($this->account_thing->uuid);
        }

        if (isset($this->account_thing->uuid)) {
            $this->account['uuid'] = $this->account_thing->uuid;
        }
        $this->account['text'] = "account check";

        if (isset($this->account_thing->text)) {
            $this->account['text'] = $this->account_thing->text;
        }

        if (isset($this->account_thing->uuid)) {
            $this->account_thing->associate($this->account_thing->uuid);
        }
/*
        if ($this->channel_name == 'web') {
            $this->response .= "Detected web channel. ";
            // Do not effect a state change for web views.
            return;
        }
*/
        $this->setAccount();
    }


    function newAccount($account)
    {
return;
        $this->response .= "Called for a new account. ";

        $thing = new Thing(null);
        $thing->Create($this->from, 'account', 'account');

        $agent = new Account($thing, "account");

        $this->account_thing = $thing;
        $this->account_thing->state = $account['state'];
        $this->account_thing->text = $account['text'];

        $this->account_thing->name = $account['name'];

        $this->account_id = $this->idAccount($thing->uuid);
    }

/*
    function createAccount($account_name, $balance = null)
    {
        if (!isset($this->accounts)) {
            $this->accounts = [];
        }

        $this->loadAccount($account_name);

        $this->accounts[$account_name] = null;

        if ($balance == null) {
            $start_balance = [
                "amount" => 0,
                "attribute" => "scalar",
                "unit" => "none",
            ];
        } else {
            if (
                is_numeric($balance["amount"]) and
                is_string($balance["attribute"]) and
                is_string($balance["unit"])
            ) {
                $start_balance = $balance;
            }
        }

        $this->accounts[$account_name] = $start_balance;
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
                //             $this->balance = $null_balance;
            }
        } else {
            if (
                is_numeric($balance["amount"]) and
                is_string($balance["attribute"]) and
                is_string($balance["unit"])
            ) {
                //$this->balance = $balance;
                //unset($this->balance["account_name"]);
            } else {
                return true;
                throw new Exception(
                    "Needs development for cases where != numeric, string, string balance."
                );
            }
        }
        $this->accounts[$this->account_name] = $balance;
        //       $this->saveBalance();

        //       return;
    }
*/

    public function isAccount($account_name = null) {

       if (!isset($this->accounts)) {return false;}

       if (!isset($this->accounts[$account_name])) {return false;}

       return true;

    }

    public function creditAccount(float $credit_amount, $account_name = null)
    {

        if (!$this->isAccount($account_name)) {return true;}

        if ($account_name == null) {
            $account_name = $this->account_name;
        }
        //echo "Credit";

        //echo "state" . $this->state ."<br>";
        //echo "credit amount". $credit_amount . "<br>";

        if ($this->accounts[$account_name]->state == "open") {

            $this->accounts[$account_name]["amount"] =
                $this->accounts[$account_name]["amount"] +
                $credit_amount;

        } else {
            // Return false in line with Thing commmunication.
            return false;
            //throw new Exception('Account cannot be charged to.');
        }
        /*
        return $this->account_name .
            " " .
            $this->balance["amount"] .
            " " .
            $this->balance["unit"];
*/
    }

    public function debitAccount(float $debit_amount, $account_name = null)
    {
        return $this->creditAccount(-1 * $debit_amount, $account_name);
    }
    /*
    public function creditAccount(float $debit_amount, $account_name = null) {

    }
*/
/*
    function Debit(float $debit_amount)
    {
        $this->Credit(-$debit_amount);
        return;
    }
*/
/*
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
*/
    // Following functions conceptualize "LOAD" and "SAVE" to a json file
    // per BASIC.  I will save the get and set language for the JSON<->DB
    //

    // Theis loads and saves a calculated balance that is stored in variables
    // Since each time a Thing is loaded it re-builds it's balance
    // from other Things, these are calculated and NOT stored in settings.

    // Loading from settings would make it an authoritative statement.
    // Which it shouldn't be.
/*
    function loadAccount()
    {
        $this->json->setField("variables");
        $this->balance = $this->json->readVariable([
            "account",
            $this->account_uuid,
            $this->account_name,
        ]);

        return $this->balance;
    }

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
*/

}
