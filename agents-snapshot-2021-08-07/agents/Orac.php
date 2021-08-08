<?php
/**
 * Orac.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Orac extends Agent {

    public $var = 'hello';


    /**
     *
     */
    function init() {

        // So I could call
        if ($this->thing->container['stack']['state'] == 'dev') {$this->test = true;}

        $this->num_hits = 0;

        // Allow for a new state tree to be introduced here.
        $this->node_list = array("start"=>array("useful", "useful?"));
    }


    /**
     *
     */
    function run() {
        $this->text = "";
        $this->findOrac("orac", "orac");
        $this->startOrac();

    }


    /**
     *
     * @param unknown $librex
     * @param unknown $searchfor
     * @return unknown
     */
    public function findOrac($librex, $searchfor) {
        //echo "foo";
        $searchfor="Orac:";
        // Look up the meaning in the dictionary.
        if (($librex == "") or ($librex == " ") or ($librex == null)) {return false;}

        switch ($librex) {
        default:
            $file = $this->resource_path . 'orac/orac.txt';
        }

        $contents = file_get_contents($file);
        // devstack add \b to Word

        $pattern = preg_quote($searchfor, '/');

        // finalise the regular expression, matching the whole line
        $pattern = "/^.*". $pattern. ".*\$/m";

        /*
        if ($librex == "orac") {
            $pattern = "\b" . preg_quote($searchfor, '/'). "\b";
            // finalise the regular expression, matching the whole line
            $pattern = "/^.*". $pattern. ".*\$/m";
        }
*/
        // search, and store all matching occurences in $matches
        $m = false;
        if (preg_match_all($pattern, $contents, $matches)) {
            //echo "Found matches:\n";
            $m = implode("\n", $matches[0]);
            $this->matches = $matches;
        }

        //$this->matches = preg_replace("/<.+>/sU", "", $this->matches);

        $this->matches = $this->matches[0];
        return $m;
    }



    /**
     *
     * @param unknown $type (optional)
     * @return unknown
     */
    public function startOrac($type = null) {

        //$this->findOrac("orac", "orac");

        $key = array_rand($this->matches);
        $value = $this->matches[$key];
        $value = strip_tags($value);
        $value = str_replace("Orac: ", "" , $value);

        $this->message = $value;
        $this->sms_message = $value;

        $this->thing->json->setField("variables");

        $names = $this->thing->json->writeVariable( array("orac", "log"), $this->text );

        $this->thing->json->setField("variables");
        $time_string = $this->thing->time();
        $this->thing->json->writeVariable( array("orac", "refreshed_at"), $time_string );

        return $this->message;
    }


    // -----------------------

    /**
     *
     * @return unknown
     */
    public function respond() {
        // Thing actions
        $this->thing->flagGreen();

        // Generate email response.
        $to = $this->thing->from;
        $from = "orac";

        $this->thing->choice->Create($this->agent_name, $this->node_list, "start");
        $choices = $this->thing->choice->makeLinks('start');
        $this->thing_report['choices'] = $choices;


        $this->sms_message = "ORAC | " . $this->sms_message . " | REPLY HELP";
        $this->thing_report['sms'] = $this->sms_message;

        $this->thing_report['email'] = $this->message;
        $this->thing_report['message'] = $this->message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        $this->thing_report['help'] = "This is Blake 7's robot.";

        return $this->thing_report;
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
        return;
    }


}
