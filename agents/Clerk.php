<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Clerk extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->api_key = $this->thing->container['api']['clerk'];
    }

    public function run()
    {
        $this->thing->flagGreen();

        $new_number =
            $this->thing->account[$this->account_name]->balance['amount'];
        $this->response .= "new number :" . $new_number;

        $this->message =
            "Thank you for your request.  The following accounting was done: " .
            $old_number .
            " + " .
            $this->amount .
            " = " .
            $new_number;
    }

    public function makeSMS()
    {
        $sms = $this->response;
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function readSubject()
    {
        $command = $this->assert($this->input);

        switch ($command) {
            case 'credit':
                $this->response .= 'identified a Credit transaction.';
                $old_number =
                    $this->thing->account[$this->account_name]->balance[
                        'amount'
                    ];

                $this->thing->account[$this->account_name]->Credit(
                    $this->amount
                );
                echo $this->thing->account[$this->account_name]->balance[
                    'amount'
                ];
                break;
            case 'create':
                $this->response .=
                    'identified an Account creation transaction.';
                $balance = [
                    "amount" => $this->amount,
                    "attribute" => $this->attribute,
                    "unit" => $this->unit,
                ];
                $this->thing->newAccount($this->account_name, $balance);
                $old_number = 0;
                break;

            case 'destroy':
                //etc
                break;

            default:
            //echo 'default';
        }

        // Look for one number in the subject line.
        // If there is more than one, don't use any.
        $this->amount = $this->getAmount();

        if ($this->scoreCredit() > $this->scoreCreate()) {
            // Likely subject is a Credit instruction
            return 'credit';
        } else {
            return 'create';
            //			return $this->thing->scalar->Create($this->amount);
        }

        return false;
    }

    public function getAmount()
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
