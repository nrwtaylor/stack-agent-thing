<?php
/**
 * Uuid.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

// Recognizes and handles UUIDS.
// Does not generate them.  That is a Thing function.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Amount extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->agent_name = "AMOUNT";

        $this->stack_state = $this->thing->container['stack']['state'];
        $this->short_name = $this->thing->container['stack']['short_name'];

        $locale = 'en_CA';
        if (isset($this->thing->container['stack']['locale'])) {
            $locale = $this->thing->container['stack']['locale'];
        }
        $this->locale = $locale;

        //        $this->created_at =  strtotime($this->thing->thing->created_at);

        $this->node_list = [
            "amount" => [
                "currency",
                "worth",
                "price",
                "value",
                "fiat",
                "reckoning",
                "cost",
                "charge",
            ],
        ];

        //$this->aliases = array("learning"=>array("good job"));

        $this->thing_report['help'] = "Recognizes text with an amount in it. ";
    }

    public function extractAmount($input = null)
    {
        if (!isset($this->amounts)) {
            $this->extractAmounts($input);
        }
        $this->amount = "X";
        if (isset($this->amounts[0])) {
            $this->amount = $this->amounts[0];
        }
        return $this->amount;
    }

    function extractAmounts($input = null)
    {
        if (is_array($input)) {
            return true;
        }

        $region = $this->locale;
        //$num = '$21.34';
        $a = new \NumberFormatter($region, \NumberFormatter::CURRENCY);

        $tokens = explode(' ', $input);
        $amounts = [];

        foreach ($tokens as $key => $token) {
            $n = $a->parseCurrency($token, $currency);

            if (is_numeric($n)) {
                $amount = ["currency" => $currency, "amount" => $n];
                $amounts[] = $amount;
            }
        }

        foreach ($tokens as $key => $token) {
            $currency_arr = ["dollars", "dollar"];
            //            if (strtolower($token) == "dollars") {
            if (in_array(strtolower($token), $currency_arr)) {
                //$n =  $a->parseCurrency($token, $currency);

                if (!isset($tokens[$key - 1])) {
                    continue;
                }

                if (is_numeric($tokens[$key - 1])) {
                    $n = floatval($tokens[$key - 1]);
                    $amount = ["currency" => "USD", "amount" => $n];
                    $amounts[] = $amount;
                }
            }
        }

        $this->amounts = $amounts;
        return $this->amounts;
    }

    /**
     *
     * @param unknown $text
     * @return unknown
     */
    function hasAmount($text)
    {
        $this->extractAmounts($text);
        if (isset($this->amounts) and count($this->amounts) > 0) {
            return true;
        }
        return false;
    }

    function set()
    {
        $this->thing->Write(
            ["amount", "received_at"],
            $this->thing->time()
        );
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        // Test
        // $this->input = "amount 21.65 54.2 sdfdsaf $21.32 -$543.345345";
        $this->extractAmounts($this->input);
        if (isset($this->amount) and $this->amount != null) {
            $this->response = "Amount spotted.";
            return;
        }

        $input = $this->input;
        $strip_words = ["amount"];

        foreach ($strip_words as $i => $strip_word) {
            $whatIWant = $input;
            if (
                ($pos = strpos(strtolower($input), $strip_word . " is")) !==
                false
            ) {
                $whatIWant = substr(
                    strtolower($input),
                    $pos + strlen($strip_word . " is")
                );
            } elseif (
                ($pos = strpos(strtolower($input), $strip_word)) !== false
            ) {
                $whatIWant = substr(
                    strtolower($input),
                    $pos + strlen($strip_word)
                );
            }

            $input = $whatIWant;
        }

        $filtered_input = ltrim(strtolower($input), " ");

        return false;
    }

    public function filterAmount($text = null)
    {
    }

    /**
     *
     */
    function makeResponse()
    {
        if (isset($this->response)) {
            return;
        }
        $this->response = "X";
        if (isset($this->amounts) and count($this->amounts) > 0) {
            $this->response = "";
            foreach ($this->amounts as $index => $amount) {
                $this->response .= $amount . " ";
            }
        }
    }

    /**
     *
     */
    function makeSMS()
    {
        $this->sms_message = strtoupper($this->agent_name) . " | ";

        $t = "";
        foreach ($this->amounts as $i => $amount) {
            $t .= $amount['amount'] . $amount['currency'] . " ";
        }
        $t = trim($t);
        $this->sms_message .= $t . " ";

        $this->sms_message .= $this->response;
        $this->sms_message .= ' | TEXT CHANNEL';

        $this->thing_report['sms'] = $this->sms_message;
    }

    /**
     *
     */
    function makeChoices()
    {
    }

    /**
     *
     */
    function makeImage()
    {
        $this->image = null;
    }
}
