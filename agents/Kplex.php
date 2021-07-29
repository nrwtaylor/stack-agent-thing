<?php
/**
 * Kplex.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Kplex extends Agent
{
    public $var = "hello";

    /**
     *
     */
    function init()
    {
        $this->node_list = ["kplex" => ["nmea", "opencpn"]];
        $this->colour_indicators = ["red", "green"];
$socket = new Socket($this->thing, ['session_terminator'=>".", "address"=>"192.168.10.122", "port"=>10110]);
        // TODO develop file of colour names.
    }


    function get()
    {
        // Take a look at this thing for IChing variables.

        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "kplex",
            "refreshed_at",
        ]);

        // And if there is no IChing timestamp create one now.

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["kplex", "refreshed_at"],
                $time_string
            );
        }

        $this->thing->json->setField("variables");
        $this->kplex = $this->thing->json->readVariable([
            "kplex",
            "kplex",
        ]);
    }

    /**
     *
     */
    function set()
    {
        $this->thing->json->writeVariable(
            ["kplex", "kplex"],
            $this->kplex
        );
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function isKplex($text)
    {
        // Is it a "degree".
        return true; // Not yet implemeneted.
    }

/*
    public function makeLink()
    {
        $this->link = false;

        if (isset($this->colour) and isset($this->colour["hex"])) {
            $colour = $this->colour["hex"];
            $hex = ltrim($colour, "#");

            $this->link = "https://htmlcolors.com/hex/" . $hex;
        }
        $this->thing_report["link"] = $this->link;
    }
*/

    public function respondResponse()
    {
        $this->thing->flagGreen();
        $message_thing = new Message($this->thing, $this->thing_report);
    }

    public function makeSMS()
    {
        $sms_message = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    public function extractKplex($text) {
return true;

    }

    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = $this->assert(strtolower($input));
        $kplex = false;

        if ($filtered_input != "") {

            $kplex = $this->extractKplex($filtered_input);

            if ($kplex !== false) {
                $this->response .= "Saw a Kplex of " . $kplex . "°. ";
                $this->kplex = $kplex;
            }
        }

        if ($kplex === false) {
            $this->response .= "Did not hear a kplex. ";
        }
    }
}
