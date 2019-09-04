<?php
/**
 * MOSS.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class MOSS extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init() {

        if ($this->agent_input == null) {
            $this->requested_agent = "MOSS.";
        } else {
            $this->requested_agent = $this->agent_input;
        }

// Which leads where.
//        $this->api_key = $this->thing->container['api']['translink'];

        $this->retain_for = 4; // Retain for at least 4 hours.

        $this->num_hits = 0;
//        $this->sqlresponse = null;

        // Allow for a new state tree to be introduced here.
        $this->node_list = array("start"=>array("help", "agents"));

        $this->thing_report['info'] = 'Hey';
        $this->thing_report['num_hits'] = $this->num_hits;

        $this->thing_report['help'] = "This is an acronym that is not defined in the movie.";

    }

    function run() {
        $this->startMOSS();
    }


    /**
     *
     * @param unknown $type (optional)
     * @return unknown
     */
    public function startMOSS($type = null) {
        $litany = array("Beijing No. 3 transportation division reminds you that routes are countless but safety is foremost. Unregulated driving will cause loved ones to end up in tears."
        );

        $key = array_rand($litany);
        $value = $litany[$key];

        $this->message = $value;
        $this->sms_message = $value;

        $this->thing->json->setField("variables");
        $names = $this->thing->json->writeVariable( array("moss", "requested_agent"), $this->requested_agent );
        $time_string = $this->thing->time();
        $this->thing->json->writeVariable( array("moss", "refreshed_at"), $time_string );

        return $this->message;
    }


    // -----------------------

    /**
     *
     * @return unknown
     */
    public function respondResponse() {
        // Thing actions
        $this->thing->flagGreen();

        $this->thing->choice->Create($this->agent_name, $this->node_list, "start");
        $choices = $this->thing->choice->makeLinks('start');
        $this->thing_report['choices'] = $choices;

        $this->sms_message = "MOSS | " . $this->sms_message . " | REPLY START";
        $this->thing_report['sms'] = $this->sms_message;

        $this->thing_report['email'] = $this->message;
        $this->thing_report['message'] = $this->message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = "Suggests a practical concern of individuals to self-regulate.";
//        return $this->thing_report;
    }


    /**
     *
     * @return unknown
     */
    public function test() {
        $this->test = false; // good
        return "green";
    }


    /**
     *
     */
    public function readSubject() {
        $this->response = null;
    }


}
