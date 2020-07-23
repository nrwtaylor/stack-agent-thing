<?php
/**
 * Quantity.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Quantity extends Agent
{
    // This is a quantity.

    // This is an agent of a quantity.  With one quantity per headcode ie train.
    // That is a lot of quantities to manage.

    public $var = 'hello';

    /**
     *
     */
    function init()
    {
        $this->keywords = [
            'quantity',
            'next',
            'last',
            'nearest',
            'accept',
            'clear',
            'drop',
            'add',
            'new',
            'here',
            'there',
        ];

        $this->default_quantity = "Z";
        if (
            isset(
                $this->thing->container['api']['quantity']['default_quantity']
            )
        ) {
            $this->default_quantity =
                $this->thing->container['api']['quantity']['default_quantity'];
        }

        $this->thing_report['help'] = 'This is a Quantity.';
        $this->thing_report['info'] =
            'Keeps track of quantities on a headcode basis.';

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/quantity';

        $this->test = "Development code"; // Always iterative.
    }

    /**
     *
     * @return unknown
     */
    /*
    public function set()
    {
        if ($this->input == "quantity") {
            return true;
        }

        if ($this->agent_input == "extract") {
            return true;
        }

        if (!isset($this->refreshed_at)) {
            $this->refreshed_at = $this->thing->time();
        }
        $quantity = $this->quantity;
        if ($this->quantity == true and !is_numeric($this->quantity)) {
            return;
        }

        $this->refreshed_at = $this->current_time;

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["quantity", "quantity"],
            $this->quantity
        );
        $this->thing->json->writeVariable(
            ["quantity", "refreshed_at"],
            $this->refreshed_at
        );

        $this->thing->log(
            $this->agent_prefix . ' set ' . $this->quantity . ".",
            "INFORMATION"
        );
    }
*/

    function set($requested_quantity = null)
    {
        if ($requested_quantity == null) {
            if (!isset($this->requested_quantity)) {
                $this->requested_quantity = "X"; // If not sure, show X.

                if (isset($this->quantity)) {
                    $this->requested_quantity = $this->quantity;
                }
                // Set default behaviour.
                // $this->requested_state = "green";
                // $this->requested_state = "red";
                //                $this->requested_state = "green"; // If not sure, show green.
            }

            $requested_quantity = $this->requested_quantity;
        }

        $this->quantity = $requested_quantity;
        $this->refreshed_at = $this->current_time;

        $this->quantity_agent->setVariable("quantity", $this->quantity);

        //$this->nuuid = substr($this->variables_thing->variables_thing->uuid,0,4);
        //$this->variables_thing->setVariable("flag_id", $this->nuuid);

        $this->quantity_agent->setVariable("refreshed_at", $this->current_time);

        $this->thing->log(
            $this->agent_prefix . 'set Quantity to ' . $this->quantity,
            "INFORMATION"
        );
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'];
    }

    /**
     *
     * @param unknown $selector (optional)
     * @return unknown
     */
    function getQuantity()
    {
        if (!isset($this->quantities)) {
            $this->getQuantities();
        }

        $this->quantity = "X";
        $consist = [];
        foreach ($this->quantities as $i => $quantity) {
            if (
                strtolower($quantity['head_code']) ==
                strtolower($this->headcode_agent->head_code)
            ) {
                $consist[] = $quantity;
            }
        }

        if (isset($consist[0]['quantity'])) {
            $this->quantity = $consist[0]['quantity'];
        }

        return $this->quantity;
    }

    /**
     *
     * @return unknown
     */
    function getQuantities()
    {
        $this->quantity_list = [];
        $this->quantities = [];

        // See if a headcode record exists.
        $findagent_thing = new Findagent($this->thing, 'quantity');
        $count = count($findagent_thing->thing_report['things']);
        $this->thing->log(
            'Agent "Quantity" found ' .
                count($findagent_thing->thing_report['things']) .
                " quantity Things."
        );

        if ($findagent_thing->thing_report['things'] == true) {
        }

        if (!$this->is_positive_integer($count)) {
        } else {
            foreach (
                array_reverse($findagent_thing->thing_report['things'])
                as $thing_object
            ) {
                $uuid = $thing_object['uuid'];

                $variables_json = $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if (
                    isset($variables['quantity']) and
                    isset($variables['headcode'])
                ) {
                    $quantity = "X";
                    $headcode = "X";

                    if (isset($variables['quantity']['quantity'])) {
                        $quantity = $variables['quantity']['quantity'];
                    }
                    if (isset($variables['headcode']['head_code'])) {
                        $headcode = $variables['headcode']['head_code'];
                    }
                    if (isset($variables['quantity']['refreshed_at'])) {
                        $refreshed_at = $variables['quantity']['refreshed_at'];
                    }

                    $this->quantities[] = [
                        "quantity" => $quantity,
                        "refreshed_at" => $refreshed_at,
                        "head_code" => $headcode,
                    ];
                    $this->quantity_list[] = $quantity;
                }
            }
        }

        $refreshed_at = [];
        foreach ($this->quantities as $key => $row) {
            $refreshed_at[$key] = $row['refreshed_at'];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->quantities);

        return [$this->quantity_list, $this->quantities];
    }

    /**
     *
     * @param unknown $str
     * @return unknown
     */
    function is_positive_integer($str)
    {
        return is_numeric($str) && $str > 0 && $str == round($str);
    }

    /**
     *
     * @return unknown
     */
    public function get()
    {
        $flag_variable_name = "";
        // Get the current Identities flag
        $this->quantity_agent = new Variables(
            $this->thing,
            "variables quantity" . $flag_variable_name . " " . $this->from
        );

        // get gets the state of the Flag the last time
        // it was saved into the stack (serialized).
        $this->previous_quantity = $this->quantity_agent->getVariable(
            "quantity"
        );
        $this->refreshed_at = $this->quantity_agent->getVariable(
            "refreshed_at"
        );

        // If it is a valid previous_state, then
        // load it into the current state variable.
        if ($this->isQuantity($this->previous_quantity)) {
            $this->quantity = $this->previous_quantity;
        } else {
            $this->quantity = $this->default_quantity;
        }
    }

    public function isQuantity($quantity = null)
    {
        // Validates whether the Flag is green or red.
        // Nothing else is allowed.

        if ($quantity == null) {
            if (!isset($this->quantity)) {
                $this->quantity = "X";
            }

            $quantity = $this->quantity;
        }

        if (is_numeric($quantity)) {
            return true;
        }

        if (strtolower($quantity) == "x") {
            return true;
        }
        if (strtolower($quantity) == "z") {
            return true;
        }

        return false;
    }

    /**
     *
     */
    function dropQuantity()
    {
        $this->thing->log(
            $this->agent_prefix . "was asked to drop a Quantity."
        );

        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->quantity)) {
            $this->quantity->Forget();
            $this->quantity = null;
        }

        $this->get();
    }

    /**
     *
     * @param unknown $quantity (optional)
     * @return unknown
     */
    function makeQuantity($quantity = null)
    {
        if ($quantity == null) {
            return true;
        }
        if (!is_numeric($quantity)) {
            return true;
        }

        $this->thing->log(
            'Agent "Quantity" will make a Quantity for ' .
                $this->stringQuantity($quantity) .
                "."
        );

        $this->current_quantity = $quantity;

        $this->requested_quantity = $quantity;

        $this->quantity = $quantity;
        $this->refreshed_at = $this->current_time;

        // This will write the refreshed at.
        $this->set();

        $this->thing->log(
            'Agent "Quantity" found a Quantity and pointed to it.'
        );
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function quantityTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $quantity_time = "x";
            return $quantity_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $quantity_time = $this->hour . $this->minute;

        if ($input == null) {
            $this->quantity_time = $quantity_time;
        }

        return $quantity_time;
    }

    // Currently just tuple extraction.
    // With negative numbers.

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function extractQuantities($input = null)
    {
        $number = new Number($this->thing, "number");
        $numbers = $number->numbers;

        return $numbers;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    public function extractQuantity($input)
    {
        $this->quantity = null;

        if (is_array($input)) {
            $this->quantity = true;
            return;
        }

        $quantities = $this->extractQuantities($input);

        if (is_array($quantities) and count($quantities) == 1) {
            if (isset($quantities[0])) {
                $this->quantity = $quantities[0];
            }

            $this->thing->log(
                $this->agent_prefix .
                    'found a quantity ' .
                    $this->quantity .
                    ' in the text.'
            );
            return $this->quantity;
        }

        if (is_array($quantities) and count($quantities) == 1) {
            //if (count($coordinates) == 1) {
            $this->quantity = $this->quantities[0];
        }
        return $this->quantity;
    }

    /**
     * Assert that the string has a coordinate.
     *
     * @param unknown $input
     */
    function assertQuantity($input)
    {
        if (($pos = strpos(strtolower($input), "quantity is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("quantity is")
            );
        } elseif (($pos = strpos(strtolower($input), "quantity")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("quantity"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        $quantity = $this->extractQuantity($filtered_input);
        if ($quantity) {
            //true so make a place
            $this->makeQuantity($quantity);
        }
    }

    /**
     *
     */
    function addQuantity()
    {
        $this->get();
        return;
    }

    /**
     *
     */
    public function makeWeb()
    {
        if (!isset($this->quantities)) {
            $this->getQuantities();
        }

        $test_message = "<b>Quantity Agent</b><p>";

        $test_message .=
            "Headcode " .
            strtoupper($this->quantity_agent->head_code) .
            " " .
            $this->quantity .
            ' units<p>';
        $c = [];
        foreach ($this->quantities as $i => $quantity) {
            $h = strtoupper($quantity['head_code']);
            if (isset($c[$h])) {
                continue;
            }

            $c[$h] = $quantity;
        }

        ksort($c);
        foreach ($c as $i => $quantity) {
            $test_message .=
                strtoupper($quantity['head_code']) .
                " " .
                $quantity['quantity'] .
                " units<br>";
        }
        if (!isset($this->refreshed_at)) {
            $test_message .= "<br>Thing just happened.";
        } else {
            $refreshed_at = $this->refreshed_at;

            $test_message .= "<p>";
            $ago = $this->thing->human_time(
                strtotime($this->thing->time()) - strtotime($refreshed_at)
            );
            $test_message .= "<br>Thing happened about " . $ago . " ago.";
        }

        $this->thing_report['web'] = $test_message;
    }

    /**
     *
     */
    function makeTXT()
    {
        $txt = 'These are QUANTITIES for RAILWAY. ';

        $txt .= "\n";
        $txt .= "\n";

        $txt .= " " . str_pad("HEADCODE", 9, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("QUANTITY", 19, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("REFRESHED AT", 25, " ", STR_PAD_RIGHT);

        $txt .= "\n";
        $txt .= "\n";

        // Places must have both a name and a code.  Otherwise it's not a place.
        foreach ($this->quantities as $key => $quantity) {
            if (isset($quantity['refreshed_at'])) {
                $h = strtoupper($quantity['head_code']);
                $txt .= " " . "  " . str_pad($h, 9, " ", STR_PAD_LEFT);

                $t = $quantity['quantity'];
                $txt .= " " . "  " . str_pad($t, 15, " ", STR_PAD_LEFT);

                $txt .=
                    " " .
                    "  " .
                    str_pad(
                        strtoupper($quantity['refreshed_at']),
                        25,
                        " ",
                        STR_PAD_RIGHT
                    );
            }
            $txt .= "\n";
        }

        $txt .= "\n";
        //        $txt .= "Last place " . $this->last_quantity . "\n";
        $txt .= "Now at " . $this->quantity . " units.";
        $txt .= "\n";
        $txt .= $this->response;

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    /**
     * String to array
     *
     * @param unknown $input
     * @return unknown
     */
    function arrayQuantity($input)
    {
        $quantities = $this->extractQuantities($input);

        $quantity_array = true;

        if (is_array($quantities) and count($quantities) == 1) {
            $quantity_array = $quantities[0];
        }

        return $quantity_array;
    }

    /**
     *
     * @param unknown $quantity (optional)
     * @return unknown
     */
    function stringQuantity($quantity = null)
    {
        if ($quantity == null) {
            $quantity = $this->quantity;
        }

        if ($quantity === false) {
            $quantity = $this->default_quantity;
        }

        if (is_array($quantity)) {
            $this->quantity_string = true;
            return $this->quantity_string;
        }

        $this->quantity_string = "" . $quantity . " units ";
        return $this->quantity_string;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $this->inject = null;
        $s = $this->inject;
        $string_quantity = $this->stringQuantity($this->quantity);
        $headcode = "X";
        /*
        if (isset($this->headcode_agent->head_code)) {
            $headcode = strtoupper($this->headcode_agent->head_code);
        }
*/
        /*
        if (isset($this->headcode_agent->head_code)) {
            $headcode = strtoupper($this->headcode_agent->head_code);
        }
*/
        if (isset($this->quantity_agent->head_code)) {
            $headcode = strtoupper($this->quantity_agent->head_code);
        }

        $sms = "QUANTITY " . $headcode . " " . $string_quantity;

        if (!empty($this->inject)) {
            $sms .= " | " . $s;
        }

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     * @param unknown $variable
     * @return unknown
     */
    function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;

        $input = $this->input;
        $pieces = explode(" ", strtolower($input));

        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'quantity') {
                $this->response = "Last quantity retrieved.";
                return;
            }
            return;
        }

        $this->extractQuantity($input);

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'next':
                            $this->thing->log("read subject next quantity");
                            $this->nextQuantity();
                            break;

                        case 'drop':
                            $this->dropQuantity();
                            break;
                        case 'make':
                        case 'new':
                        case 'quantity':
                        case 'create':
                        case 'add':
                            if (is_numeric($this->quantity)) {
                                $this->response =
                                    'Asserted quantity and found ' .
                                    $this->stringQuantity($this->quantity) .
                                    ".";
                                return;
                            }

                            return;
                            break;

                        default:
                    }
                }
            }
        }
        return true;
    }
}
