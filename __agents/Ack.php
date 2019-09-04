<?php
/**
 * Ack.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Ack extends Agent {

    public $var = 'hello';


    /**
     *
     */
    function init() {
        $this->test= "Development code";

        $this->node_list = array("ack"=>array("ack"));
    }


    /**
     *
     */
    public function get() {

        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable( array("ack", "refreshed_at") );

        if ($time_string == false) {
            //            $this->thing->json->setField("variables");
            $time_string = $this->thing->time();
            $this->thing->json->writeVariable( array("ack", "refreshed_at"), $time_string );
        }

        $this->refreshed_at = strtotime($time_string);


        //        $this->thing->json->setField("variables");
        $this->alpha = $this->thing->json->readVariable( array("ack", "alpha"));
        //        $this->text = $this->thing->json->readVariable( array("ack", "text") ); // Test because this will become A6.
    }


    /**
     *
     */
    public function set() {

        if ($this->alpha == false) {

            $this->makeACk();
            $this->thing->json->writeVariable( array("ack", "alpha"), $this->alpha );
            //            $this->thing->json->writeVariable( array("ack", "text"), $this->text );

        }



    }


    /**
     *
     */
    public function makeAck() {
        //if (ctype_alpha($this->alpha)) {
        //    $this->response = "Read this four-character alpha sequence.";
        //    return;
        //}

        $this->response = "Understood.";

        //        $this->random = new Random($this->thing,"random AAAA ZZZZ");
        //        $this->alpha = $this->random->number;

        $this->alpha =  "OK";


        // https://stackoverflow.com/questions/2257441/random-string-generation-with-upper-case-letters-and-digits
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
        $from = "ack";

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
    function makeEmail() {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/ack';

        $this->node_list = array("ack"=>array("ack"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "ack");
        $choices = $this->thing->choice->makeLinks('ack');

        $web = '<a href="' . $link . '">';
        //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/roll.png" jpg"
        //                width="100" height="100"
        //                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/roll.tx$

        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';

        //        if (!isset($this->html_image)) {$this->makePNG();}

        //        $web .= $this->html_image;

        //        $web .= "</a>";
        //        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( time() - $this->refreshed_at );
        $web .= "This number was made about ". $ago . " ago.";

        $web .= "<br>";


        $this->thing_report['email'] = $web;
    }



    /**
     *
     */
    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = array("ack"=>array("ack"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "web");
        $choices = $this->thing->choice->makeLinks('web');

        $web = "Ack is " . $this->alpha .".";
        //        if (!isset($this->html_image)) {$this->makePNG();}

        //        $web = '<a href="' . $link . '">'. $this->html_image . "</a>";
        //        $web .= "<br>";

        $web .= '<br>Ack says, "'  .$this->sms_message . '".';


        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time ( time() - $this->refreshed_at );
        $web .= "<p>The alpha group was made about ". $ago . " ago.";


        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }


    /**
     *
     */
    function makeSMS() {

        //if (!isset($this->text) or ($this->text == 'Invalid input' ) or ($this->text == null)) {
        //    $sms = "N6 | Request not processed. Check syntax.";
        //} else {

        $sms = "Ack | " . $this->alpha . " | " . $this->response;

        //}


        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;

    }


    /**
     *
     */
    function makeMessage() {
        $message = "Stackr got this Ack for you.<br>";
        $message .= $this->alpha .".";

        $this->thing_report['message'] = $message;

        return;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractAck($input) {
        if (!isset($this->acks)) {
            $this->response = "Found lots of four-character alpha sequences.";
            $this->acks = $this->extractAcks($input);
        }

        if (count($this->acks) == 1) {
            $this->response = "Found a four-character alpha sequence.";
            $this->acks = strtolower($this->acks[0]);
            return $this->ack;
        }

        if (count($this->acks) == 0) {
            $this->response = "Did not find any four-character alpha sequences.";
            $this->ack = null;
            return $this->ack;
        }

        $this->ack = false;
        //array_pop($arr);
        return false;
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractAcks($input) {

        // Devstack this just does numbers.
        if (!isset($this->acks)) {
            $this->acks = array();
        }

        $pattern = "|\b[a-zA-Z]{4}\b|";
        //$pattern = "|\b[X]{6}\b|";
        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);
        $this->acks = $arr;
        return $this->acks;
    }


    /**
     *
     */
    public function readSubject() {
        $this->response = "Read.";

        $input = strtolower($this->subject);

        if ($this->agent_input != null) {
            $input = strtolower($this->agent_input);
        }

        $this->extractAcks($input);

        if ((!isset($this->ack)) or ($this->ack == null)) {
            $this->ack = "X";
        }
    }


}
