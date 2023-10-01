<?php
namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// devstack lots of work here

class Consist extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->keywords = ["next", "accept", "clear", "drop", "add", "new"];

        $this->default_consist = "NX";

        $this->default_alias = "Thing";

        $this->test = "Development code"; // Always iterative.

        // Agent variables

        $this->head_code = $this->thing->Read([
            "headcode",
            "head_code",
        ]);

        $flag_variable_name = "_" . $this->head_code;

        // Get the current Identities flag
        $this->variables = new Variables(
            $this->thing,
            "variables consist" . $flag_variable_name . " " . $this->from
        );

        $this->state = null; // to avoid error messages
    }

    public function setConsist($consist = null)
    {
        $consist = null;
        if (isset($this->consist)) {
            $consist = $this->consist;
        }
        $consist["refreshed_at"] = $this->current_time;

        $this->thing->Write(["consist"], $consist);
    }

    function set()
    {
        $this->setConsist();

        $this->variables->setVariable("consist", $this->consist);
        $this->variables->setVariable("head_code", $this->head_code);
        $this->variables->setVariable("refreshed_at", $this->current_time);
    }

    function getConsists()
    {
        $this->consists = [];
        // See if a consist record exists.
        $findagent_thing = new Findagent($this->thing, "consist");
        $consists = $findagent_thing->thing_report["things"];
        if ($consists == true) {
            return;
        }

        foreach (array_reverse($consists) as $thing_object) {
            // While timing is an issue of concern

            $uuid = $thing_object["uuid"];

            $variables_json = $thing_object["variables"];
            $variables = $this->thing->json->jsontoArray($variables_json);

            if (isset($variables["consist"])) {
                if (!$this->isConsist($variables["consist"])) {
                    continue;
                }
                if (!isset($variables["consist"]["refreshed_at"])) {
                    continue;
                }

                $this->consists[] = $variables["consist"];
            }
        }

        $refreshed_at = [];
        foreach ($this->consists as $key => $row) {
            $refreshed_at[$key] = $row["refreshed_at"];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->consists);

        return $this->consists;
    }

    function getConsist($selector = null)
    {
        if (!isset($this->consists)) {
            $this->getConsists();
        }

        foreach ($this->consists as $key => $consist) {
        }

        if (count($this->consists) != 0) {
            $this->consist = $this->consists[0];
        }
    }

    function get($consist = null)
    {
        $time_string = $this->thing->Read([
            "consist",
            "refreshed_at",
        ]);

        // And if there is no IChing timestamp create one now.

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["consist", "refreshed_at"],
                $time_string
            );
        }

        if (!isset($this->consist)) {
            $this->consist = $this->variables->getVariable("consist");
            //$this->head_code = $this->variables->getVariable('head_code');
        }
    }

    function makeConsist($head_code = null)
    {
    }

    function deprecate_headcodeTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $headcode_time = "x";
            return $headcode_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $headcode_time = $this->hour . $this->minute;

        if ($input == null) {
            $this->headcode_time = $headcode_time;
        }

        return $headcode_time;
    }

    function addHeadcode()
    {
        $this->get();
    }

    function makeTXT()
    {
        if (!isset($this->consists)) {
            $this->getConsists();
        }

        $txt = "Test \n";
        foreach ($this->consists as $i => $consist) {
            if (!isset($consist["vehicles"])) {
                continue;
            }
            $txt .= $this->textConsist($consist);
            $txt .= "\n";
        }

        $this->thing_report["txt"] = $txt;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $this->thing_report["email"] = $this->thing_report["sms"];
        $this->thing_report["message"] = $this->thing_report["sms"]; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report["info"] = $message_thing->thing_report["info"];
        } else {
            $this->thing_report["info"] =
                'Agent input was "' . $this->agent_input . '".';
        }

        $this->thing_report["help"] = "This is a consist.";
    }

    public function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
    }

    public function vehiclesConsist($text = null)
    {
        //$tokens = array_map('trim', explode('>', $text));
        $tokens = mb_str_split($text);
        $vehicles = [];
        foreach ($tokens as $i => $token) {
            $vehicles[] = $token;
        }

        return $vehicles;
    }

    public function isConsist($consist = null)
    {
        // Basic validation of consist.
        if ($consist == null) {
            return false;
        }
        if ($consist == []) {
            return false;
        }

        if (isset($consist["vehicles"])) {
            return true;
        }

        return false;
    }

    public function readConsist($text = null)
    {
        $vehicles = $this->vehiclesConsist($text);

        $consist = ["vehicles" => $vehicles];
        return $consist;
    }

    public function textConsist($consist = null)
    {
        $text = "X";
        if ($consist == null) {
            return $text;
        }
        if ($this->isConsist($consist) === false) {
            return $text;
        }

        if (!is_array($consist["vehicles"])) {
            return $text;
        }

        $text = implode("", $consist["vehicles"]);
        return $text;
    }

    public function makeSMS()
    {
        $sms =
            "CONSIST " .
            strtoupper($this->head_code) .
            " " .
            $this->textConsist($this->consist) .
            " " .
            $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    // TODO
    public function nextConsist()
    {
        $this->response .= "Request for the next consist seen. ";
    }

    // TODO
    public function dropConsist()
    {
        $this->response .= "Request to drop consist seen. ";
    }

    // TODO
    public function addConsist()
    {
        $this->response .= "Request to add consist seen. ";
    }

    public function recognizeConsist($consist)
    {
        if ($this->isConsist($consist) === false) {
            return false;
        }

        if (!isset($this->consists)) {
            $this->getConsists();
        }

        foreach ($this->consists as $i => $known_consist) {
            if ($this->isConsist($known_consist) === false) {
                continue;
            }
            if ($known_consist["vehicles"] === $consist["vehicles"]) {
                $this->response .= "Recognized consist. ";
                return $this->consists[$i];
            }
        }

        $this->response .= "New consist seen. ";

        return false;
    }

    public function readSubject()
    {
        $input = $this->input;
        // Bail at this point if only a headcode check is needed.
        if ($this->agent_input == "extract") {
            return;
        }

        if ($input == "consist" or $this->agent_input == "consist") {
            $this->response .= "Saw a request for the current consist. ";
            return;
        }

        $pos = stripos($input, "consist");
        if ($pos === 0) {
            $input = trim(substr_replace($input, "", 0, strlen("consist")));
        }

        $consist = $this->readConsist($input);

        if ($this->isConsist($consist) === true) {
            $this->recognizeConsist($consist);

            $this->consist = $consist;
            $this->response .= "Saw and extracted a consist. ";
        }

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == "consist") {
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "next":
                            $this->thing->log("read subject nextheadcode");
                            $this->nextConsist();
                            break;

                        case "drop":
                            $this->dropConsist();
                            break;

                        case "add":
                            $this->addConsist();
                            break;

                        default:
                    }
                }
            }
        }
    }
}
