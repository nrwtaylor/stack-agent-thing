<?php
namespace Nrwtaylor\StackAgentThing;

    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(-1);

    // Dispatcher's job is to read the subject and place
    // text in the settings field as to priority.

    // emergency
    // priority
    // routine
    // welfare

    // test is a seperate variable to allow for testing of higher
    // level priorities without action.

    //$this->priority is set at this point.

class Dispatcher extends Agent {

    function init() {
    }

    public function respondResponse() {

        // Thing actions

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("dispatcher", "priority"), $this->priority);

        $this->thing->flagRed();

        // Thing email etc.

        $response = null;

        return $response;
    }

    public function makeSMS() {

        $sms = "DISPATCHER " . $this->priority . " | " . $this->response;
        $this->thing_report['sms'] = $sms;

    }

    public function readSubject() {

        $input = new Input($this->thing, "input");

        $this->priority = "routine";


        $discriminators = array('emergency', 'priority', 'routine', 'welfare');

        $aliases = array(
            "emergency"=>array('e/', 'emergency', 'help', 'urgent', 'assistance', 'sos', 'now', 'immediately'),
            "priority" => array('p/', 'priority'),
            "routine" => array('r/', 'routine', 'normal'),
            "welfare" => array('w/', 'welfare')
        );

        $input->aliases = $aliases;
        $response = $input->discriminateInput($this->input, $discriminators);

        if ($response === false) {
            $this->response .= "Did not see a discriminator. ";
            return;
        }

        $this->priority = $response;

    }

}
