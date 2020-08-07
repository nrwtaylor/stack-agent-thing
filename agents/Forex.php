<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Forex extends Agent
{
    // This gets Forex from an API.

    public $var = 'hello';
    function init()
    {
        $this->test = "Development code"; // Always

        $this->keywords = ['forex'];

        $this->thing_report['help'] =
            'Provides the latest US to Canadian conversion rate from the European Central Bank.';
    }

    function run()
    {
        $this->getForex();
    }

    function set()
    {
        if (!isset($requested_state) or $requested_state == null) {
            if (!isset($this->requested_state)) {
                $this->requested_state = null;
            }
            $requested_state = $this->requested_state;
        }

        $this->variables_agent->setVariable("state", $requested_state);

        $this->variables_agent->setVariable("verbosity", $this->verbosity);

        $this->variables_agent->setVariable(
            "currency_pair",
            $this->currency_pair
        );
        $this->variables_agent->setVariable("bid", $this->bid);
        $this->variables_agent->setVariable("ask", $this->ask);
        $this->variables_agent->setVariable("price", $this->price);

        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->state = $requested_state;
        $this->refreshed_at = $this->current_time;
    }

    function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "forex" . " " . $this->from
        );

        // Loads in variables.

        $this->currency_pair = $this->variables_agent->getVariable(
            "currency_pair"
        );
        $this->bid = $this->variables_agent->getVariable("bid");
        $this->ask = $this->variables_agent->getVariable("ask");
        $this->price = $this->variables_agent->getVariable("price");

        $this->verbosity = $this->variables_agent->getVariable("verbosity");

        if ($this->verbosity == false) {
            $this->verbosity = 2;
        }

        if ($this->currency_pair == false) {
            $this->currency_pair = "USDCAD";
        }

        return;
    }

    function getForex()
    {
        $forex = new Exchangeratesapi($this->thing, $this->currency_pair);

        $this->getLink($this->currency_pair);

        $this->bid = $forex->bid;
        $this->price = $forex->price;
        $this->ask = $forex->ask;

        return $this->price;
    }

    public function getLink($ref = null)
    {
        if ($ref == null) {
            return true;
        }
        // Give it the message returned from the API service

        $this->link = "https://www.google.com/search?q=" . $ref;
        return $this->link;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();
        // Generate email response.

        $to = $this->thing->from;
        $from = "forex";

        $choices = false;
        $this->thing_report['choices'] = $choices;

        //$this->flag = "green";
        //if (strtolower($this->flag) == "red") {
        //    $sms_message = "FOREX = SURF'S UP";
        //} else {
        $sms_message = "FOREX " . $this->currency_pair;
        //}

        if ($this->verbosity >= 2) {
            //            $sms_message .= " | flag " . strtoupper($this->flag);
            $sms_message .=
                " | price " . trim(number_format($this->price, 4)) . "";
            if ($this->bid != null) {
                $sms_message .= " | bid " . $this->bid . " ";
            }
            if ($this->ask != null) {
                $sms_message .= " | ask " . $this->ask . " ";
            }
            $sms_message .= " | source exchangeratesapi ";
        }

        $sms_message .= "| curated link " . $this->link;

        if ($this->verbosity >= 9) {
            $sms_message .=
                " | nuuid " . substr($this->variables_agent->thing->uuid, 0, 4);

            $run_time = microtime(true) - $this->start_time;
            $milliseconds = round($run_time * 1000);

            $sms_message .= " | rtime " . number_format($milliseconds) . 'ms';
        }

        //        $sms_message .=  " | TEXT HELP";

        $test_message =
            'Last thing heard: "' .
            $this->subject .
            '".  Your next choices are [ ' .
            $choices['link'] .
            '].';

        $test_message .= '<br>' . $sms_message;

        $this->thing_report['sms'] = $sms_message;
        $this->thing_report['email'] = $sms_message;
        $this->thing_report['message'] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        return;
    }

    public function extractNumber($input = null)
    {
        if ($input == null) {
            $input = $this->subject;
        }

        $pieces = explode(" ", strtolower($input));

        // Extract number
        $matches = 0;
        foreach ($pieces as $key => $piece) {
            if (is_numeric($piece)) {
                $number = $piece;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            if (is_integer($number)) {
                $this->number = intval($number);
            } else {
                $this->number = floatval($number);
            }
        } else {
            $this->number = true;
        }

        return $this->number;
    }

    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;

        $input = $this->input;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'forex') {
                return;
            }
        }

        // Extract runat signal
        $matches = 0;

        $currencies = [];

        foreach ($pieces as $key => $piece) {
            if (strlen($piece) == 3 and ctype_alpha($piece)) {
                $currencies[] = strtoupper($piece);
                //$run_at = $piece;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            $this->currency_pair = 'USD' . $currencies[0];
        }

        if ($matches == 2) {
            $this->currency_pair = $currencies[0] . $currencies[1];
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'verbosity':
                        case 'mode':
                            $number = $this->extractNumber();
                            if (is_numeric($number)) {
                                $this->verbosity = $number;
                                $this->set();
                            }
                            return;

                        default:
                    }
                }
            }
        }

        $this->requested_state = null;
        return false;
    }
}
