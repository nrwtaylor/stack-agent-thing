<?php
/**
 * Termsofuse.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;


ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Termsofuse extends Agent {


    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     */
    function init() {

        $this->thing_report['help'] = 'This is the Terms of Use manager. Email ' . $this->email .'.';

        $this->mail_regulatory = $this->thing->container['stack']['mail_regulatory'];
        $this->node_list = array("start"=>array("start","opt-in"));
        $this->termsofuse();

//        $this->thing_report['thing']  = $this->thing;
    }


    /**
     *
     */
    public function termsofuse() {
        $this->makeAgent("where");
    }


    /**
     *
     */
    public function makeWeb() {
        $file = $GLOBALS['stack_path'] . 'resources/termsofuse/termsofuse.html';
        $contents = file_get_contents($file);
        $this->thing_report['web'] = $contents;
    }


    /**
     *
     * @return unknown
     */
    public function makeSMS() {
        $sms = "TERMS OF USE | " . $this->thing_report['sms'];
        $sms .= " Please read them carefully at " . $this->web_prefix ."terms-of-use" ;
        $sms .= " | " . "TEXT PRIVACY" ;

        $this->thing_report['sms'] = $sms;
        $this->sms_message = $sms;
        return $this->sms_message;
    }


    /**
     *
     */
    public function makeEmail() {
$text = ($this->thing_report['email']);

//$string = "The people are very nice , [gal~route~100~100] , the people are very nice , [ga2l~route2~150~150]";
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

        $this->thing_report['email'] = $text;
    }


    /**
     *
     */
/*
    public function makeChoices() {
var_dump($this->thing_report['choices']);
exit();
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "start");
        $choices = $this->thing->choice->makeLinks('start');
        // $choices = false;
        $this->thing_report['choices'] = $choices;
    }
*/

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

        $this->thing_report['request'] = "What are the Terms of Use?";

        return "Message not understood";
    }

}
