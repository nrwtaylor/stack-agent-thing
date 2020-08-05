<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// devstack

class Clerk extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->api_key = $this->thing->container['api']['clerk'];
        $this->account_name = 'thing';
        $this->thing_report['help'] = 'Try CLERK CREDIT THING 10.';
        $this->amount = 0;
    }

    public function run()
    {
        $this->thing->flagGreen();

        $this->new_number =
            $this->thing->account[$this->account_name]->balance['amount'];

        $this->response .= "new number :" . $this->new_number;

        $this->response .= 'Account name ' . $this->account_name . '. ';

        $this->message =
            "Thank you for your request.  The following accounting was done: " .
            $this->prior_balance .
            " + " .
            $this->amount .
            " = " .
            $this->balance;
    }

    public function makeSMS()
    {
        $sms = "CLERK | " . $this->message . " " . $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function creditClerk()
    {
        $this->response .= 'identified a Credit transaction.';
        $this->old_number =
            $this->thing->account[$this->account_name]->balance['amount'];

        if (!isset($this->amount)) {
            $this->response .= "An amount is needed. ";
            return true;
        }

        $this->thing->account[$this->account_name]->Credit($this->amount);
        $this->amount =
            $this->thing->account[$this->account_name]->balance['amount'];
    }

    public function getBalance()
    {
        $this->balance =
            $this->thing->account[$this->account_name]->balance['amount'];
    }

    public function createClerk()
    {
        $this->response .= 'identified an Account creation transaction.';
        $balance = [
            "amount" => $this->amount,
            "attribute" => $this->attribute,
            "unit" => $this->unit,
        ];
        $this->thing->newAccount($this->account_name, $balance);
        $this->old_number = 0;
    }
    public function readSubject()
    {
        $command = $this->assert($this->input);
        $this->prior_balance = $this->getBalance();
        $this->amount = $this->extractAmount();

        $input_agent = new Input($this->thing, "input");
        $discriminators = ["statement", "create", "credit", "destroy"];
        $input_agent->aliases['statement'] = ['statement'];
        $input_agent->aliases['create'] = ['create'];
        $input_agent->aliases['credit'] = ['credit'];
        $input_agent->aliases['destroy'] = ['destroy'];

        $response = $input_agent->discriminateInput($command, $discriminators);

        switch ($response) {
            case 'credit':
                $this->creditClerk();
                break;
            case 'create':
                $this->createClerk();
                break;
            case 'destroy':
                //etc
                break;

            case false:
                $this->response .= "Noted. ";
            default:
            //echo 'default';
        }

        // Look for one number in the subject line.
        // If there is more than one, don't use any.
        //        $this->amount = $this->extractAmount();
        $this->getBalance();

        if ($this->scoreCredit() > $this->scoreCreate()) {
            // Likely subject is a Credit instruction
            return 'credit';
        } else {
            return 'create';
            //			return $this->thing->scalar->Create($this->amount);
        }

        return false;
    }

    public function extractAmount()
    {
        //$this->subject = "1 2 -3 -4.5,56 90 123.01 -80.01 100,23 -34, 100,000,000";

        //preg_match_all('/((?:[0-9]+,)*[0-9]+(?:\.[0-9]+)?)/', $this->subject, $numbers);
        preg_match_all(
            '/(\+|-)?((?:[0-9]+,)*[0-9]+(?:\.[0-9]+)?)/',
            $this->subject,
            $numbers
        );
        $numbers = $numbers[0]; // Take first element of three part array.

        // http://stackoverflow.com/questions/15814592/how-do-i-include-negative-decimal-numbers-in-this-regular-expression

        //	echo '<pre> $numbers: '; print_r($numbers); echo '</pre>';
        //	echo '<pre> $count(numbers): '; print_r(count($numbers)); echo '</pre>';

        if (count($numbers) == 1) {
            $this->amount = $numbers[0];
            $this->response .= "Got " . $this->amount . " amount. ";
            return $numbers[0];
        }
        if (count($numbers) > 1) {
            $this->amount = false;
            return false;
        }

        return implode(",", $numbers);
    }

    public function scoreCredit()
    {
        // Score the likelihood this is a request to credit an account.
        $confidence = 0.0;
        $this->response = null;

        $keywords = ['credit', 'debit'];

        $input = strtolower($this->subject);
        $pieces = explode(" ", strtolower($input));
        foreach ($keywords as $command) {
            foreach ($pieces as $key => $piece) {
                if (strpos(strtolower($piece), $command) !== false) {
                    try {
                        // is either debit or credit
                        $confidence = 0.0;
                        $this->action = $pieces[$key];
                        $this->account_name = $pieces[$key + 1];
                        if (isset($pieces[$key + 1])) {
                            $this->amount = $pieces[$key + 2];
                            if (is_numeric($this->amount)) {
                                //echo "numeric";
                                $confidence = $confidence + 0.6;
                            } else {
                                $confidence = 0.0;
                            }
                        }

                        if (isset($pieces[$key + 3])) {
                            $this->attribute = $pieces[$key + 3];
                            $confidence = $confidence + 0.3;
                        }

                        if (isset($pieces[$key + 4])) {
                            $this->unit = $pieces[$key + 4];
                            $confidence = $confidence + 0.3;
                        }
                    } catch (Exception $e) {
                    }
                    echo $confidence;
                }
            }
        }

        return $confidence;
    }

    function scoreCreate()
    {
        $confidence = 0.0;
        $this->response = null;

        $keywords = ['create', 'make', 'log', 'track', 'new', 'open'];

        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        foreach ($keywords as $command) {
            foreach ($pieces as $key => $piece) {
                if (strpos(strtolower($piece), $command) !== false) {
                    try {
                        // is either debit or credit
                        $confidence = 0.0;
                        $this->action = $pieces[$key];
                        $confidence = 0.2;
                        $this->account_name = $pieces[$key + 1];
                        $this->amount = $pieces[$key + 2];

                        if (is_numeric($this->amount)) {
                            //echo "numeric";
                            $confidence = 0.8;
                        }

                        $this->attribute = $pieces[$key + 3];
                        if (isset($pieces[$key + 4])) {
                            $this->unit = $pieces[$key + 4];
                            $confidence = 0.95;
                        }
                    } catch (Exception $e) {
                    }
                }
            }

            return $confidence;
        }
    }
}
