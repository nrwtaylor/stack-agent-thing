<?php
/**
 * Privacy.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Privacy extends Agent {


    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     */
    function init() {


        $this->mail_regulatory = $this->thing->container['stack']['mail_regulatory'];
        $this->node_list = array("start"=>array("start","opt-in"));
        $this->privacy();

        $this->thing_report['thing']  = $this->thing;
    }


    /**
     *
     */
    public function privacy() {
    }


    /**
     *
     */
    public function makeWeb() {
        $file = $GLOBALS['stack_path'] . 'resources/privacy/privacy.html';
        $contents = file_get_contents($file);
        $this->thing_report['web'] = $contents;
    }


    /**
     *
     * @return unknown
     */
    public function makeSMS() {
        $this->sms_message = "PRIVACY | Records of the subject/chat, originating address and destination agent are retained until they are forgotten.  Records may be forgotten at anytime either by the system or by the Identity. Forgetall will forget all of an Identity's Things. Things may contain nominal key accessible information.";
        $this->sms_message .= " | " . $this->web_prefix ."privacy" ;
        $this->sms_message .= " | " . "TEXT ?" ;

        $this->thing_report['sms'] = $this->sms_message;
        $this->message = $this->sms_message;
        return $this->message;
    }


    /**
     *
     */
    public function makeEmail() {
        $message = "Thank you. Privacy is really important to " . ucwords($this->word) . ". Records deposited with " . ucwords($this->word) . " may be forgotten at any time.\r\n";
        $message .= "The address fields (to:, cc:, and bcc:) are stripped of non-Stackr emails, and the subject line is processed by " . ucwords($this->word) . ".\r\n";
        $message .= "An instruction to Stackr to remove all message records associated with this email address can be sent to forgetall" . $this->mail_postfix . ".\r\n";

        $message .= "<br><br>";

        $message .= 'If you need to discuss our privacy policy please contact ' . $this->email . ".\r\n";
        $message .= 'Our mailing address is ' . trim($this->mail_regulatory) . ".\r\n";

        $message .= 'For a full statement of our privacy policy, please goto to <a href="' . $this->web_prefix . ' privacy">' . $this->web_prefix . 'privacy</a>';

        $this->thing_report['email'] = $message;
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
    public function respond() {
        // Thing actions
        $this->thing->flagGreen();

        $this->makeSMS();
        $this->makeWeb();
        $this->makeEmail();

        $to = $this->thing->from;
        $from = "privacy";

        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        //$this->thing_report['help'] = 'This is the Privacy manager. Email privacy' . $this->mail_postfix .'.';
        $this->thing_report['help'] = 'This is the Privacy manager. Email ' . $this->email .'.';


        return $this->thing_report;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->thing_report['request'] = "What is Privacy?";
        $this->thing_report['message'] = "Request for web privacy policy.";

        return "Message not understood";
    }

}
