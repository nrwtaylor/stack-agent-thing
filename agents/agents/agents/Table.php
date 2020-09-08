<?php
/**
 * Table.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Table extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    public function init()
    {
        $this->test = "Development code";

        $this->primary_place = "roost";
        $this->signals = ["on", "off"];

        $this->node_list = [
            "on the table" => [
                "on the table" => [
                    "off the table" => "off the table",
                    "on the table",
                ],
            ],
            "off the table" => "on the table",
        ];

        $this->thing_report['help'] =
            'This is the table agent. Try ON THE TABLE. Or OFF THE TABLE.';

        // devstack
        //$agent = new Entity($this->thing, "table");
        //$this->agent_thing = $agent->thing;
    }

    function run()
    {
        $this->doTable();
    }

    /**
     *
     */
    public function set()
    {
    }

    function doTable()
    {
    }

    function offTable()
    {
        $this->response .= "That is off the table. ";
    }

    function onTable()
    {
        $this->response .= "That is on the table. ";
    }

    public function makeSMS()
    {
        $sms = "TABLE | " . $this->response;
        $this->thing_report['sms'] = $sms;
    }

    function readSubject()
    {
        $input = new Input($this->thing, "input");

        $discriminators = ['on', 'off'];

        $aliases = [];

        $aliases['on'] = ['on', 'on the table'];
        $aliases['off'] = ['off', 'off the table'];

        $input->aliases = $aliases;
        $response = $input->discriminateInput($this->input, $discriminators);

        if ($response === false) {
            $this->response .= "Did not see anything on the table. ";
            return;
        }

        if ($response == 'on') {
            $this->onTable();
        }

        if ($response == 'off') {
            $this->offTable();
        }
    }
}
