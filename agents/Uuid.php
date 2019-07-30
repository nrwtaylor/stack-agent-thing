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

class Uuid extends Agent
{


    /**
     *
     */
    function init() {
        $this->stack_state = $this->thing->container['stack']['state'];
        $this->short_name = $this->thing->container['stack']['short_name'];

        $this->created_at =  strtotime($this->thing->thing->created_at);

        $this->thing->log('started running on Thing ' . date("Y-m-d H:i:s") . '');

        $this->node_list = array("uuid"=>
            array("uuid", "snowflake"));

        $this->aliases = array("learning"=>array("good job"));

        $this->makePNG();

        $this->thing_report['help'] = "Makes a universally unique identifier. Try NUUID.";

        $this->thing->log('Agent "Uuid" found ' . $this->uuid);
    }


    /**
     *
     */
    function getQuickresponse() {
        $agent = new Qr($this->thing, "qr");
        $this->quick_response_png = $agent->PNG_embed;
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractUuids($input) {
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
     * @param unknown $input
     * @return unknown
     */
    function extractUuid($input) {
        $uuids = $this->extractUuids($input);
        if (!(is_array($uuids))) {return true;}

        if ((is_array($uuids)) and (count($uuids) == 1)) {
            $this->uuid = $uuids[0];
            $this->thing->log('found a uuid (' . $this->uuid . ') in the text.');
            return $this->uuid;
        }

        if  ((is_array($uuids)) and (count($uuids) == 0)) {return false;}
        if  ((is_array($uuids)) and (count($uuids) > 1)) {return true;}

        return true;
    }



    /**
     *
     */
    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/uuid';

        $this->node_list = array("uuid"=>array("uuid", "snowflake"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "uuid");
        $choices = $this->thing->choice->makeLinks('uuid');

        $alt_text = "a QR code with a uuid";

        $web = '<a href="' . $link . '">';
        //$web_prefix = "http://localhost:8080/";
        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/uuid.png" jpg"
                width="100" height="100"
                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/uuid.txt">';

        $web .= "</a>";
        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        //$ago = $this->thing->human_time ( $this->created_at );
        //$web .= "Created about ". $ago . " ago.";
        //$web.= "<b>UUID Agent</b><br>";
        //$web.= "uuid is " . $this->uuid. "<br>";

        $web.= "CREATED AT " . strtoupper(date('Y M d D H:m', $this->created_at)). "<br>";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }


    /**
     *
     */
    public function respond() {
        // Thing actions

        $this->thing->json->setField("settings");
        $this->thing->json->writeVariable(array("uuid",
                "received_at"),  $this->thing->json->time()
        );

        $this->thing->flagGreen();

        $from = $this->from;
        $to = $this->to;

        $subject = $this->subject;

        // Now passed by Thing object
        $uuid = $this->uuid;
        $sqlresponse = "yes";

        $message = "Thank you $from here is a UUID.<p>" . $this->web_prefix . "thing/$uuid\n$sqlresponse \n\n<br> ";
        $message .= '<img src="' . $this->web_prefix . 'thing/'. $uuid.'/receipt.png" alt="thing:'.$uuid.'" height="92" width="92">';

        $this->makeSMS();

        $this->thing_report['email'] = $this->thing_report['sms'];

        $this->makePNG();

        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        //$this->thing_report['thing'] = $this->thing->thing;

        $this->makeWeb();
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {
        // Then look for messages sent to UUIDS
        $this->thing->log('Agent "UUID" looking for UUID in address.');

        $pattern = "|[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}|";

        if (preg_match($pattern, $this->to)) {
            $this->thing->log('Agent "UUID" found a  UUID in address.');
        }

        $status = true;
        return $status;
    }


    /**
     *
     */
    function makeSMS() {
        $this->sms_message = "UUID | ";
        $this->sms_message .= $this->uuid;
        $this->sms_message .= ' | TEXT ?';

        $this->thing_report['sms'] = $this->sms_message;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create("uuid", $this->node_list, "uuid");

        $choices = $this->thing->choice->makeLinks("uuid");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }


    /**
     *
     * @return unknown
     */
    public function makePNG() {
        if (isset($this->PNG)) {return;}

        $codeText = $this->web_prefix . "thing/".$this->uuid;

        $agent = new Qr($this->thing, $codeText);
        $image = $agent->PNG;

        $this->PNG_embed = "data:image/png;base64,".base64_encode($image);
        $this->PNG = $image;

        $this->thing_report['png'] = $image;

        return $this->thing_report['png'];

    }


}
