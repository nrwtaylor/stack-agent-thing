<?php
/**
 * Shortcode.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Shortcode extends Agent {


    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     */
    function init() {

        $this->thing_report['help'] = 'This is the Short Code manager.';

        $this->mail_regulatory = $this->thing->container['stack']['mail_regulatory'];
        $this->web_prefix = $this->thing->container['stack']['web_prefix'];

        $this->node_list = array("start"=>array("start","opt-in"));
        $this->shortcode();
    }


    /**
     *
     */
    public function shortcode() {
//        $this->makeAgent("shortcode");
    }

    /**
     *
     * @return unknown
     */
    public function makeSMS() {
        $sms = "SHORTCODE | No response.";

        $this->thing_report['sms'] = $sms;
        $this->sms_message = $sms;
        return $this->sms_message;
    }


    /**
     *
     */
    public function filterShortcode($text = null) {

        if ($text == null) return true;

        $regex = "/\[(.*?)\]/";
        preg_match_all($regex, $text, $matches);

        for($i = 0; $i < count($matches[1]); $i++)
        {
            $match = $matches[1][$i];

//    $array = explode('~', $match);
//    $newValue = $array[0] . " - " . $array[1] . " - " . $array[2] . " - " . $array[3];
            $newValue = $this->{strtolower($match)};
            $text = str_replace($matches[0][$i], $newValue, $text);
        }

        return $text;
    }


    /**
     *
     * @return unknown
     */
    public function respondResponse() {
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {
        return "Message not understood";
    }

}
