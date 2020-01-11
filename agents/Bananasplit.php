<?php
/**
 * Tabletop.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Bananasplit extends Agent {


    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     */
    function init() {

        $this->mail_regulatory = $this->thing->container['stack']['mail_regulatory'];
        $this->web_prefix = $this->thing->container['stack']['web_prefix'];

        $this->node_list = array("start"=>array("start","opt-in"));
        $this->squirrel();


        $this->thing_report['thing']  = $this->thing;
    }


    /**
     *
     */
    public function squirrel() {
        $this->makeAgent("bananasplit");
    }


    /**
     *
     */
    public function makeWeb() {
        $file = $GLOBALS['stack_path'] . 'resources/bananasplit/bananasplit.html';
        $contents = file_get_contents($file);
        $this->thing_report['web'] = $contents;
    }


    /**
     *
     * @return unknown
     */
    public function makeSMS() {
        $sms = "BANANA SPLIT | " . $this->thing_report['sms'];

        $this->thing_report['sms'] = $sms;
        $this->sms_message = $sms;
        return $this->sms_message;

    }


    /**
     *
     */

    public function makeEmail() {
        $text = ($this->thing_report['email']);
        $shortcode_agent = new Shortcode($this->thing, "shortcode");
        $text = $shortcode_agent->filterShortcode($text);

        $this->thing_report['email'] = $text;
    }


    /**
     *
     */
    public function makeChoices() {
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "start");
        $choices = $this->thing->choice->makeLinks('start');
        // $choices = false;
        $this->thing_report['choices'] = $choices;
    }


    /**
     *
     * @return unknown
     */
    public function respondResponse() {

//        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;



        return $this->thing_report;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->thing_report['request'] = "What is Banana Split?";
        return "Message not understood";
    }

}
