<?php
/**
 * Notword.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Notword extends Agent {

    public $var = 'hello';


    /**
     *
     */
    function init() {
        $this->test= "Development code";

        $this->node_list = array("not word"=>array("not word"));
    }


    /**
     *
     */
    public function get() {

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("notword", "refreshed_at") );

        if ($time_string == false) {
            //            $this->thing->json->setField("variables");
            $time_string = $this->thing->time();
            $this->thing->json->writeVariable( array("notword", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);


        //        $this->thing->json->setField("variables");
        $this->alpha = $this->thing->json->readVariable( array("notword", "alpha"));
    }


    /**
     *
     */
    public function set() {

        if ($this->alpha == false) {

            $this->makeNotword();
            $this->thing->json->writeVariable( array("notword", "alpha"), $this->alpha );

        }



    }


    /**
     *
     */
    public function makeNotword() {
        //if (ctype_alpha($this->alpha)) {
        //    $this->response = "Read this four-character alpha sequence.";
        //    return;
        //}

        $this->response = "Understood.";

        //        $this->random = new Random($this->thing,"random AAAA ZZZZ");
        //        $this->alpha = $this->random->number;

        $this->alpha =  "OK";


    }


    // -----------------------

    /**
     *
     * @return unknown
     */
    public function respond() {

        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        $to = $this->thing->from;
        $from = "notword";

        $choices = false;

        $this->makeSMS();
        $this->makeMessage();

        $this->makeChoices();
        $this->makeWeb();

        $this->makeEmail();

        $this->thing_report["info"] = "This makes four character identities.";
        if (!isset($this->thing_report['help'])) {
            $this->thing_report["help"] = 'This is about four character words.  Try "n6".';
        }

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'] ;
        }

        return $this->thing_report;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create($this->agent_name, $this->node_list, "n6");

        $choices = $this->thing->choice->makeLinks('n6');
        $this->thing_report['choices'] = $choices;
    }



    /**
     *
     */
    function makeSMS() {

        //if (!isset($this->text) or ($this->text == 'Invalid input' ) or ($this->text == null)) {
        //    $sms = "N6 | Request not processed. Check syntax.";
        //} else {

        $sms = "NOT WORD | " . $this->alpha . " | " . $this->response;

        //}


        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;

    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractNotword($input) {
        if (!isset($this->notwords)) {
            $this->response = "Found lots of four-character alpha sequences.";
            $this->notwords = $this->extractNotwords($input);
        }

        if (count($this->notwords) == 1) {
            $this->response = "Found a four-character alpha sequence.";
            $this->notwords = strtolower($this->notwords[0]);
            return $this->notword;
        }

        if (count($this->notwords) == 0) {
            $this->response = "Did not find any four-character alpha sequences.";
            $this->notword = null;
            return $this->notword;
        }

        $this->notword = false;
        //array_pop($arr);
        return false;
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractNotwords($input) {

global $wp;
if (!isset($wp->word_agent)) {
$wp->word_agent = new Word($this->thing, "word");
}
$tokens = explode(" ",$input);

$this->notwords = array();
foreach($tokens as $i=>$token) {

if (strtolower($token) == "notword") {continue;}

if ($wp->word_agent->isWord($token)) {

} else {

$this->notwords[] = $token;
}

}
        return $this->notwords;
    }


    /**
     *
     */
    public function readSubject() {
        $this->response = "Read.";

//        $input = strtolower($this->subject);

//        if ($this->agent_input != null) {
//            $input = strtolower($this->agent_input);
//        }

$input = $this->input;
        $this->extractNotwords($input);

        if ((!isset($this->notword)) or ($this->notword == null)) {
            $this->notword = "X";
        }
    }


}
