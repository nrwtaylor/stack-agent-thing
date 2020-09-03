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

class Address extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    function init()
    {
        $this->node_list = ["address" => ["n-gram", "address"]];
    }

    /**
     *
     * @return unknown
     */
    public function address()
    {
        $this->sms_message = 'ADDRESS | Found a mapped address.';
        $this->message = 'Found a mapped address.';

        $this->thing_report['message'] = $this->message;
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['email'] = $this->sms_message;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function isAddress($input = null, $address_book = null)
    {
        if ($address_book == null) {
            $address_book = 'address';
        }

        // Check address against the addressbook.
        $file =
            $this->resource_path . $address_book . '/' . $address_book . '.txt';

if (!file_exists($file)) {return true;}

        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $address_string = trim($line);

                $address_array = explode(",", $address_string);

                $ngram = $address_array[0];
                $address = $address_array[1];
                if (strtolower($input) == strtolower($ngram)) {
                    return $address;
                }
            }
            fclose($handle);
        } else {
            return true;
            // error opening the file.
        }

        return false;
    }

    /**
     *
     */
    public function readSubject()
    {
        $input = $this->assert($this->input);
        $response = $this->isAddress($input);
        $this->address = $response;
        if ($response === false) {
            // No address found.
            $this->response .= 'Address not found. ';
            return;
        }

        if ($response === true) {
            // No address found.
            $this->response .= 'Could not lookup that address. ';
            return;
        }

        $this->address();
    }
}
