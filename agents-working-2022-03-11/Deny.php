<?php
/**
 * Limitedbeta.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Deny extends Agent
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

        $this->node_list = ["deny" => ["deny"]];
    }

    /**
     *
     * @return unknown
     */
    public function deny()
    {
        if ($this->isDeny() === true) {
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

        $sms = "DENY | " . $response_text;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function make() {}

    public function read($text = null) {}

    public function readFrom($text = null) {}

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function isDeny($input = null)
    {
        if ($input == null) {
            $input = $this->from;
        }
        $file = $this->resource_path . 'deny/deny.txt';

        if (!file_exists($file)) {
            return null;
            //            throw new \Exception("File does not exist");
        }

        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $deny_address = trim($line);

                $hash_deny_address = hash('sha256', $deny_address);
                if ($hash_deny_address == $input) {
                    return true;
                }

                if ($deny_address == $input) {
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
    public function readSubject()
    {
    }
}
