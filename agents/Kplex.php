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
    }

    public function listenKplex()
    {
        $listen_time = 10; //s

        $address = "127.0.0.1";
        $port = "10110";
        $fp = fsockopen($address, $port, $errno, $errstr, 30);

        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
            die();
        }
        echo "Connected to kplex server.\n";

        $ship_handler = new Ship($this->thing, "ship");
        $snapshot = null;
        $datagram_stack = [];
        $unrecognized_sentences = [];
        $start_time = time();
        while (($buffer = fgets($fp, 4096)) !== false) {
            $response = $ship_handler->readShip($buffer);

            $recognized_sentence =
                $ship_handler->ship_thing->variables->snapshot
                    ->recognized_sentence;
            $sentence_identifier =
                $ship_handler->ship_thing->variables->snapshot
                    ->sentence_identifier;

            if ($recognized_sentence === "N") {
                if (!in_array($sentence_identifier, $unrecognized_sentences)) {
                    $unrecognized_sentences[] = $sentence_identifier;
                }
            }

            $snapshot = $ship_handler->ship_thing->variables->snapshot;
            $elapsed_time = time() - $start_time;
            if ($elapsed_time > $listen_time) {
                break;
            }
        }
        $this->snapshot = $snapshot;

    }

    function get()
    {
        $time_string = $this->thing->Read(["kplex", "refreshed_at"]);

        // And if there is no IChing timestamp create one now.

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(["kplex", "refreshed_at"], $time_string);
        }

        $this->kplex = $this->thing->Read(["kplex", "kplex"]);
    }

    /**
     *
     */
    function set()
    {
        $this->thing->Write(["kplex", "kplex"], $this->kplex);
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
        $sms = strtoupper($this->agent_name) . " | ";
if ($this->snapshot != null) {
        $sms .= "latitude " . $this->snapshot->current_latitude_decimal . " ";
        $sms .= "longitude " . $this->snapshot->current_longitude_decimal . " ";
        $sms .= "speed " . $this->snapshot->speed_in_knots . " knots ";
        $sms .= "course " . $this->snapshot->true_course . " degrees ";
        if (isset($this->snapshot->destination_waypoint_id)) {
            $sms .=
                "destination waypoint " .
                $this->snapshot->destination_waypoint_id .
                " ";
        }

        if (isset($this->snapshot->range_to_destination_in_nautical_miles)) {
            $sms .=
                "range " .
                $this->snapshot->range_to_destination_in_nautical_miles .
                " NM ";
        }

        if (isset($this->snapshot->bearing_to_destination_in_degrees_true)) {
            $sms .=
                "bearing " .
                $this->snapshot->bearing_to_destination_in_degrees_true .
                "T degrees ";
        }

        if (isset($this->snapshot->destination_closing_velocity_in_knots)) {
            $sms .=
                "closing velocity " .
                $this->snapshot->destination_closing_velocity_in_knots .
                " knots ";
        }
}

        $sms .= $this->response;
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function extractKplex($text)
    {
        return true;
    }

    public function readSubject()
    {
        $input = $this->input;
        $filtered_input = $this->assert(strtolower($input));
        $kplex = false;
        //if ($filtered_input == 'l') {
        //if (isset($this->snapshot)) {
        //var_dump($this->snapshot);
        //return;}
        $this->listenKplex();

        if (isset($this->snapshot) and $this->snapshot !== false) {
            $this->response .= "Heard data stream from Kplex server. ";
            return;
        }

        if ($this->snapshot == null) {

            $this->response .= "Did not hear data stream from Kplex server. ";

        }
        // return;

        //}

        /*
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
 //   }

*/
    }
}
