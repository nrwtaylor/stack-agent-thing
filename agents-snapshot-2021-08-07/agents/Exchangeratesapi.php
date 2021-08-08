<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Exchangeratesapi extends Agent
{
    // This gets Forex from an API.

    public $var = 'hello';

    function init()
    {
        $this->test = "Development code"; // Always

        $this->keywords = ['forex', 'exchange rate', 'USDCAD', 'CADUSA'];

        $this->thing_report['help'] = 'Try FOREX CADUSA.';
        $this->thing_report['info'] =
            'This provides currency prices using the Exchange Rates API.';
    }

    function run()
    {
        $this->getForex();
    }

    function set()
    {
        $this->variables_agent->setVariable(
            "currency_pair",
            $this->currency_pair
        );
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->refreshed_at = $this->current_time;
    }

    function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "forex" . " " . $this->from
        );

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
        $this->getLink($this->currency_pair);

        $base = substr($this->currency_pair, 0, 3);
        $symbol = substr($this->currency_pair, 3, 3);

        $data_source = "https://api.exchangeratesapi.io/latest?base=" . $base;

        //$data = file_get_contents($data_source, NULL, NULL, 0, 4000);

        $data = file_get_contents($data_source);
        if ($data == false) {
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, true);

        $this->bid = null;
        $this->price = $json_data['rates'][$symbol];
        $this->ask = null;

        return $this->price;
    }

    function getLink($ref = null)
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

        $choices = false;
        $this->thing_report['choices'] = $choices;

        $sms_message = "FOREX " . $this->currency_pair;

        if ($this->verbosity >= 2) {
            $sms_message .= " | flag " . strtoupper($this->flag);
            $sms_message .= " | price " . $this->price . " ";
            if (isset($this->bid) or $this->bid != null) {
                $sms_message .= " | bid " . $this->bid . " ";
            }
            if (isset($this->ask) or $this->ask != null) {
                $sms_message .= " | ask " . $this->ask . " ";
            }
            $sms_message .= " | source exchangeratesapi ";
        }

        $sms_message .= " | curated link " . $this->link;

        if ($this->verbosity >= 9) {
            $sms_message .=
                " | nuuid " . substr($this->variables_agent->thing->uuid, 0, 4);

            $run_time = microtime(true) - $this->start_time;
            $milliseconds = round($run_time * 1000);

            $sms_message .= " | rtime " . number_format($milliseconds) . 'ms';
        }

        $sms_message .= " | TEXT ?";

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

        $this->thing_report['help'] =
            'This triggers provides currency prices using the 1forge API.';
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
        // Extract uuids into

        //$this->number = extractNumber();

        //        $keywords = $this->keywords;

        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        $this->input = $input;

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

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
                        //$this->read();                                                    //echo 'default';
                    }
                }
            }
        }

        return false;
    }
}
