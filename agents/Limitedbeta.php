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

class LimitedBeta extends Agent {


    public $var = 'hello';


    /**
     *
     */
    function init() {

        $this->thing->log($this->agent_prefix . 'running on Thing ' . $this->thing->nuuid . '.');

        $this->node_list = array("limitedbeta"=>array("start", "opt-in"));

    }


    /**
     *
     * @return unknown
     */
    public function limitedbeta() {

        $this->sms_message = 'LIMITED BETA | Your address has been forwarded to the development team.';
        $this->message = $this->word . ' is in limited beta. Your address has been forwarded to the development team.';

        $message = 'The stack received a limited beta request from ' . $this->from .'.';

        $thing = new Thing(null);

        $to = $this->email;

        $thing->Create($to, $thing->uuid , 's/ limited beta ' . $this->from);
        $thing->flagGreen();

        $thing_report['thing'] = $thing;
        $thing_report['message'] = $message;
        $thing_report['sms'] = $message;
        $thing_report['email'] = $message;

        $message_thing = new Message($thing, $thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->message;
    }


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    public function isLimitedbeta($input = null) {

        if ($input === null) {return false;}

        $input_address = trim($input);
        $input_address_array = explode("@", $input_address);
        $input_prefix = $input_address_array[0];
        $input_postfix = $input_address_array[1];

        // Check address against the beta list
        $file = $this->resource_path . 'limitedbeta/limitedbeta.txt';

        if (file_exists($file)) {

        //        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {


                $limitedbeta_address = trim($line);
                //echo $limitedbeta_address . " "  . $input_address . "\n";

                $limitedbeta_address_array = explode("@", $limitedbeta_address);

                $limitedbeta_prefix = $limitedbeta_address_array[0];
                $limitedbeta_postfix = $limitedbeta_address_array[1];

                if ($limitedbeta_prefix == "*") {
                    if (strtolower($limitedbeta_postfix) == strtolower($input_postfix)) {return true;}
                }

                if (strtolower($input_address) == strtolower($limitedbeta_address)) {return true;}


            }
            fclose($handle);
        } else {
            // error opening the file.
        }
        }
        return false;
    }


    /**
     *
     */
    public function readSubject() {
        $this->limitedbeta();
    }



}
