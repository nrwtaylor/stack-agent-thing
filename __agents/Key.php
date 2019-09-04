<?php
/**
 * Key.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Key extends Agent {


    /**
     *
     */
    function init() {

        $this->keyword = "key";
        $this->thing_report['help'] = "Creates a uuid link to access your datagram.";

    }

    function set() {

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("key",
                "received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
        );

    }

    /**
     *
     */
    public function respond() {

        // Thing actions

//        $this->thing->json->setField("variables");
//        $this->thing->json->writeVariable(array("key",
//                "received_at"),  gmdate("Y-m-d\TH:i:s\Z", time())
//        );

        $this->thing->flagGreen();

        $from = $this->from;
        $to = $this->to;

        //echo "from",$from,"to",$to;

        $subject = $this->subject;

        // Now passed by Thing object
        $uuid = $this->uuid;
        $sqlresponse = $this->sqlresponse;

        $message = "'keymanager' decided it was about time that you had a new
key to access Stackr. Keep on stacking.\n\n<p>" . $this->web_prefix . "thing/$uuid\n\n\n<br> ";
        $message .= '<img src="' . $this->web_prefix . 'thing/'.$uuid.'/receipt.png" alt="thing:'.$uuid.'" height="92" width="92">';


        $this->sms_message = "KEY | " . $this->web_prefix . "thing/$uuid/agent | TEXT [ FORGETALL | SHUFFLE ]";

        // Assemble a button set.

        $this->node_list = array("key maintenance"=>array("happy", "not happy"=>array("more", "less")));

        //  $this->thing->choice->Create($node_list, "key maintenance");
        $this->thing->choice->Create('key', $this->node_list, "key maintenance");

        $choices = $this->thing->choice->makeLinks();

        $this->thing_report['choices'] = $choices;

        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $message;
        $this->thing_report['email'] = $message;
        $this->thing_report['choices'] = $choices;
        //                        $this->thing_report['info'] = 'SMS sent';



        //echo $quoted_printable_button_set;

        //$db = new Database($this->uuid);
        //  $user_state = $this->thing->currentState();

        //  if ($user_state == "opt-in") {

        //   $this->thing->email->sendGeneric($from,"keymanager",$subject,$message,$html_button_set);


        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;


    }



    /**
     *
     * @return unknown
     */
    public function readSubject() {


        $status = true;
        return $status;
    }


    /**
     *
     */
    public function sendKey() {
    }



}
