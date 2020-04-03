<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '/var/www/html/stackr.ca/public/agenthandler.php'; // until the callAgent call can be
// factored to
// call agent 'Agent'

ini_set("allow_url_fopen", 1);

class Read extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test = "Development code"; // Always
        $this->keywords = array('read', 'link', 'date', 'wordlist');

        $this->user_agent = null;
        if (isset($this->thing->container['api']['read']['user_agent'])) {
            $this->user_agent =
                $this->thing->container['api']['read']['user_agent'];
        }

        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "read" . " " . $this->from
        );

        $this->link = $this->web_prefix;
        if ($this->link == false) {
            $this->link = "";
        }
    }

    function run()
    {
        // Now have this->link potentially from reading subject

        echo "prerobot " . $this->link . "\n";
        $this->robot_agent = new Robot($this->thing, $this->link);
        echo "postrobot" . "\n";

if ($this->robot_agent->robots_allowed($this->link, $this->user_agent === false)) {
$this->response .= "Robot not allowed. ";
}
        $this->getUrl($this->link);


    }

    function set()
    {
        $this->variables_agent->setVariable("state", $this->state);

        $this->variables_agent->setVariable("link", $this->link);

        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->refreshed_at = $this->current_time;

        return;
    }

    function get()
    {
        $this->state = $this->variables_agent->getVariable("state");
        $this->link = $this->variables_agent->getVariable("link");
        $this->refreshed_at = $this->variables_agent->getVariables(
            "refreshed_at"
        );
    }

    function getUrl($url = null)
    {
        if ($url == null) {
            $this->link = $this->web_prefix;
            $url = $this->link;
        }
        $data_source = $this->link;

        $options = array(
            'http' => array(
                'method' => "GET",
                'header' => "User-Agent: " . $this->user_agent . "\r\n"
            )
        );

        $context = stream_context_create($options);

        $data = file_get_contents($data_source, false, $context);

        if ($data == false) {
            return true;
            // Invalid return from site..
        }

        // Raw file
        $this->contents = $data;
    }

    function match_all($needles, $haystack)
    {
        if (empty($needles)) {
            return false;
        }

        foreach ($needles as $needle) {
            if (strpos($haystack, $needle) == false) {
                return false;
            }
        }
        return true;
    }

    /*
    function getVariable($variable_name = null, $variable = null) {

        // This function does a minor kind of magic
        // to resolve between $variable, $this->variable,
        // and $this->default_variable.

        if ($variable != null) {
            // Local variable found.
            // Local variable takes precedence.
            return $variable;
        }

        if (isset($this->$variable_name)) {
            // Class variable found.
            // Class variable follows in precedence.
            return $this->$variable_name;
        }

        // Neither a local or class variable was found.
        // So see if the default variable is set.
        if (isset( $this->{"default_" . $variable_name} )) {

            // Default variable was found.
            // Default variable follows in precedence.
            return $this->{"default_" . $variable_name};
        }

        // Return false ie (false/null) when variable
        // setting is found.
        return false;
    }

*/

    public function respond()
    {
        // Thing actions

        $this->thing->flagGreen();
        // Generate email response.

        $to = $this->thing->from;
        $from = "read";

        $choices = false;
        $this->thing_report['choices'] = $choices;

        $this->thing_report['txt'] = implode("/n", $this->yard_sales);

        if (strtolower($this->flag) == "red") {
            $sms_message = "READ DEV = ESTATE FOUND";
        } else {
            $sms_message = "READ DEV";
        }

        if ($this->verbosity >= 2) {
        }

        $a = implode(" | ", $this->addresses);

        $addresses = $a;

        $sms_message .= " | " . $addresses;

        if ($this->verbosity >= 5) {
            $sms_message .= " | wordlist " . $this->wordlist;
        }

        $sms_message .= " | link " . $this->link;

        if ($this->verbosity >= 9) {
            $sms_message .=
                " | nuuid " . substr($this->variables_agent->thing->uuid, 0, 4);
            $sms_message .=
                " | rtime " .
                number_format($this->thing->elapsed_runtime()) .
                'ms';
        }

        $sms_message .= " | TEXT ?";

        $test_message = 'Last thing heard: "' . $this->subject . '"';

        $test_message .= '<br>Train state: ' . $this->state . '<br>';

        $test_message .= '<br>' . $sms_message;

        $this->thing_report['sms'] = $sms_message;
        $this->thing_report['email'] = $sms_message;
        $this->thing_report['message'] = $sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];

        $this->thing_report['help'] = 'This reads a web resource.';

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

        $keywords = $this->keywords;

        $input = $this->assert($this->input);

        $this->url = $input;
        $this->link = $input;

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'read') {
                return;
            }
        }

        return "Message not understood";

        return false;
    }
}
