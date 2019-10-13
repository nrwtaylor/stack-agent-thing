<?php
/**
 * Uuid.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

// Recognizes and handles UUIDS.
// Does not generate them.  That is a Thing function.

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Winlink extends Agent
{


    /**
     *
     */
    function init() {
        $this->stack_state = $this->thing->container['stack']['state'];
        $this->short_name = $this->thing->container['stack']['short_name'];

        $this->created_at =  strtotime($this->thing->thing->created_at);

        $this->thing->log('started running on Thing ' . date("Y-m-d H:i:s") . '');

        $this->node_list = array("winlink"=>
            array("percs", "rocky"));

        $this->response = "";
        $this->thing_report['help'] = "Checks to see if this is a Winlink message.";
    }


    /**
     *
     * @return unknown
     */
    function isWinlink() {
        $searchfor = "@winlink.org";
        // Check address against the beta list

        //        $file = '/var/www/stackr.test/resources/slack/id.txt';
        //        $contents = file_get_contents($file);

        if ( strpos(strtolower($this->from), '@winlink.org') !== false) {
            return true;
        }
        return false;
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractWinlink($input) {
        if (!isset($this->uuids)) {
            $this->uuids = array();
        }

        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);
        $this->uuids = $arr;
        return $arr;
    }




    /**
     *
     */
    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/winlink';

        $this->node_list = array("winlink"=>array("percs", "rocky"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "winlink");
        $choices = $this->thing->choice->makeLinks('winlink');

        $web = "<b>Winlink Agent</b>";
        $web .= "<p>";

        $web .= '<a href="https://winlink.org">https://winlink.org</a>';

        $this->thing_report['web'] = $web;
    }


    /**
     *
     */
    public function set() {

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("winlink",
                "refreshed_at"),  $this->thing->time()
        );


    }


    /**
     *
     */
    public function respondResponse() {
        // Thing actions

        $this->thing->flagGreen();
        $this->thing_report['email'] = $this->thing_report['sms'];

        $message_thing = new Message($this->thing, $this->thing_report);

        $info = "No info available.";
        if (isset($message_thing->thing_report['info'])) {$info = $message_thing->thing_report['info'];}

    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {
        // Then look for messages sent to UUIDS
        $this->thing->log('Agent "Winlink" looking for winlink in address.');

        if (stripos($this->subject, '//wl2k') !== false) {
            $this->response  .= "Saw Winlink in datagram subject.";
        }

    }


    /**
     *
     */
    function makeSMS() {
        $this->sms_message = "WINLINK | ";


        //        $this->sms_message .= $this->uuid;

        if ($this->isWinlink()) {$this->sms_message .= "Recognized a message from a winlink.org address. ";} else {
            $this->sms_message .= 'https://winlink.org/ ';
        }

        $this->sms_message .= $this->response;

        $this->sms_message .= ' | TEXT WINLINK';

        $this->thing_report['sms'] = $this->sms_message;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create("winlink", $this->node_list, "winlink");

        $choices = $this->thing->choice->makeLinks("winlink");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }


}
