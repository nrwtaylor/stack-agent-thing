<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Oneforge extends Agent
{
    // This gets Forex from an API.

    public $var = "hello";

    public function init()
    {
        $this->keywords = ["forex"];

        $this->api_key = $this->thing->container["api"]["1forge"];

        if (!isset($this->verbosity)) {
            $this->verbosity = 2;
        }

        //if ($this->currency_pair == false) {
            $this->currency_pair = "USDCAD";
        //}
    }

    public function run()
    {
        $this->getOneforge();
    }

    function set()
    {
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


        $this->refreshed_at = $this->current_time;
    }

    function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "1forge" . " " . $this->from
        );

        //$this->variables_thing->getVariables();

        $this->currency_pair = $this->variables_agent->getVariable(
            "currency_pair"
        );
        $this->bid = $this->variables_agent->getVariable("bid");
        $this->ask = $this->variables_agent->getVariable("ask");
        $this->price = $this->variables_agent->getVariable("price");

        $this->verbosity = $this->variables_agent->getVariable("verbosity");
    }

    function getOneforge()
    {
        $this->getLink($this->currency_pair);
/*
        $data_source =
            "https://forex.1forge.com/1.0.3/quotes?pairs=" .
            $this->currency_pair .
            "&api_key=" .
            $this->api_key;
*/
$data_source = "https://api.1forge.com/quotes?pairs=EUR/USD,GBP/JPY,AUD/USD&api_key=". $this->api_key;

        $data = file_get_contents($data_source);

        if ($data == false) {
            $this->response .= "Could not access 1Forge API. ";
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, true);

        if ($json_data['error'] == true) {
            $this->response .= "Could not access 1Forge API. ";
            return true;
            // Invalid query of some sort.
        }


        $this->bid = $json_data[0]["bid"];
        $this->price = $json_data[0]["price"];
        $this->ask = $json_data[0]["ask"];

        return $this->price;
    }

    function getLink($ref = null)
    {
        // Give it the message returned from the API service

        $this->link = "https://1forge.com/";
        return $this->link;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["help"] =
            "This triggers provides currency prices using the 1forge API.";

    }

    public function makeSMS() {

       $this->flag = "green";

        if (strtolower($this->flag) == "red") {
            $sms_message = "FOREX = SURF'S UP";
        } else {
            $sms_message = "FOREX " . $this->currency_pair;
        }

        $sms_message = trim($sms_message);

        if ($this->verbosity >= 2) {
            $sms_message .= " | flag " . strtoupper($this->flag);
            $sms_message .= " | price " . $this->price . " ";
            $sms_message .= " | bid " . $this->bid . " ";
            $sms_message .= " | ask " . $this->ask . " ";
            $sms_message .= " | source 1forge ";
        }

        $sms_message .= " | curated link " . $this->link;

        if ($this->verbosity >= 9) {
            $sms_message .=
                " | nuuid " . substr($this->variables_agent->thing->uuid, 0, 4);

            $run_time = microtime(true) - $this->start_time;
            $milliseconds = round($run_time * 1000);

            $sms_message .= " | rtime " . number_format($milliseconds) . "ms";
        }

        $sms_message .= " | TEXT ?";

        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;


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

        $keywords = $this->keywords;

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
            if ($input == "forex") {
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
            $this->currency_pair = "USD" . $currencies[0];
        }

        if ($matches == 2) {
            $this->currency_pair = $currencies[0] . $currencies[1];
        }
    }
}
