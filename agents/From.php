<?php
/**
 * From.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class From extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    function init()
    {
        $this->thing->log(
            $this->agent_prefix .
                'running on Thing ' .
                $this->thing->nuuid .
                '.'
        );
        $this->node_list = ["from" => ["from"]];
        //$this->from();
    }

    public function run() {

        $this->from();

    }

    public function countFrom() {

        $things = $this->thing->db->fromcountDatabase();
        $this->from_count = count($things);
    }

    /**
     *
     * @return unknown
     */
    public function from()
    {
return;
        $this->countFrom();
        return;
        if ($this->isFrom() === true) {
            $this->response .= "Denied. ";
            return;
        }

        $this->response .= "Accepted.";
    }

    public function makeSMS()
    {
        $response_text = $this->response;
        if ($this->response == "") {
            $response_text = "No response found. ";
        }

        $from_text = "Counted " . $this->from_count . " stack address(es).";

        $sms = "FROM | " . $from_text . " " .$response_text;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    //public function make() {}

    public function read($text = null) {}

    public function readFrom($text = null) {}

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function isFrom($input = null)
    {
        if ($input == null) {
            $input = $this->from;
        }
        $file = $this->resource_path . 'from/from.txt';

        if (!file_exists($file)) {
            throw "File does not exist";
        }

        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $from_address = trim($line);

                $hash_from_address = hash('sha256', $from_address);
                if ($hash_from_address == $input) {
                    return true;
                }

                if ($from_address == $input) {
                    return true;
                }


            }
            fclose($handle);
        } else {
            // error opening the file.
        }

        return false;
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report['choices'] = $choices;

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $t>

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);

            $this->thing_report['info'] = $message_thing->thing_report['info'];
        } else {
            $this->thing_report['info'] =
                'Agent input was "' . $this->agent_input . '".';
        }

        $this->thing_report['help'] = 'This is a headcode.';
    }


    /**
     *
     */
    public function readSubject()
    {
        //$this->from();
    }
}
