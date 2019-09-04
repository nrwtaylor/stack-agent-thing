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

class Repeater extends Agent
{


    /**
     *
     */
    function init() {
        $this->stack_state = $this->thing->container['stack']['state'];
        $this->short_name = $this->thing->container['stack']['short_name'];

        $this->created_at =  strtotime($this->thing->thing->created_at);

        $this->thing->log('started running on Thing ' . date("Y-m-d H:i:s") . '');

        $this->node_list = array("repeater"=>
            array("repeater", "snowflake"));

        $this->aliases = array("learning"=>array("good job"));

//        $this->makePNG();

        $this->thing_report['help'] = "Recognizes repeaters.";

    }

//function run() {
//}

    /**
     *
     
    function getQuickresponse() {
        $agent = new Qr($this->thing, "qr");
        $this->quick_response_png = $agent->PNG_embed;
    }
*/


function hasRepeater($text) {


$this->extractRepeaters($text);
if ((isset($this->repeaters)) and (count($this->repeaters) > 0)) {return true;}
return false;

}

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractRepeaters($input) {
        if (!isset($this->repeaters)) {
            $this->repeaters = array();
        }

//        $pattern = "|[a-zA-Z]{1-2}[1-9]{1}[a-zA-Z]{1-3}|";


        $pattern = "|[a-zA-Z]{1,2}[1-9]{1}[a-zA-Z]{1,3}|";


        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);
        $this->repeaters = $arr;
        return $arr;
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractRepeater($input) {
        $repeaters = $this->extractRepeaters($input);
        if (!(is_array($repeaters))) {return true;}

        if ((is_array($repeaters)) and (count($repeaters) == 1)) {
            $this->repeater = $repeaters[0];
            $this->thing->log('found a repeater (' . $this->repeater . ') in the text.');
            return $this->repeater;
        }

        if  ((isset($repeater)) and (is_array($repeater)) and (count($repeater) == 0)) {return false;}
        if  ((isset($repeater)) and (is_array($repeater)) and (count($repeater) > 1)) {return true;}

        return true;
    }



    /**
     *
     */
    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/repeater';

        $this->node_list = array("repeater"=>array("repeater", "frequency"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "repeater");
        $choices = $this->thing->choice->makeLinks('repeater');

        $alt_text = "a QR code with a repeater";

        $web = '<a href="' . $link . '">';
        //$web_prefix = "http://localhost:8080/";
        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/repeater.png" jpg"
                width="100" height="100"
                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/repeater.txt">';

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

function set() {

        $this->thing->json->setField("settings");
        $this->thing->json->writeVariable(array("repeater",
                "received_at"),  $this->thing->json->time()
        );

}

    /**
     *
  */   
    public function respondResponse() {
        // Thing actions

//        $this->thing->json->setField("settings");
//        $this->thing->json->writeVariable(array("frequency",
//                "received_at"),  $this->thing->json->time()
//        );

        $this->thing->flagGreen();

//        $from = $this->from;
//        $to = $this->to;

//        $subject = $this->subject;

        // Now passed by Thing object
//        $uuid = $this->uuid;
//        $sqlresponse = "yes";

//        $message = "Thank you $from here is a UUID.<p>" . $this->web_prefix . "thing/$uuid\n$sqlresponse \n\n<br> ";
//        $message .= '<img src="' . $this->web_prefix . 'thing/'. $uuid.'/receipt.png" alt="thing:'.$uuid.'" height="92" width="92">';

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
        $this->extractRepeater($this->input);

//            $pieces = explode(" ", strtolower($this->input));


        $input = $this->input;
        $whatIWant = $this->input;
        if (($pos = strpos(strtolower($input), "repeater")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("amateur repeater")); 
        } elseif (($pos = strpos(strtolower($input), "repeater")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("amateur repeater")); 
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");



//                if (count($pieces) == 1) {
//echo "merp";

//$a = new Agent($this->thing, "amateurradioservice 156.580");
//var_dump($a->response);
//var_dump($a->sms_message);


//exit();

//$a = new Amateurradioservice($this->thing, $filtered_input);
//$a->doAmateurradioservice($this->input)
//foreach($this->frequencies as $index=>$frequency) {
//var_dump($frequency);
//$a->doAmateurradioservice($frequency);

//$a->input = $frequency;
//    $a->getMatches($frequency, "CSV");
//    var_dump($a->message);
//    var_dump($a->response);


//}
//                }


    }

function makeResponse() {
$this->response = "X";
if ((isset($this->repeaters)) and (count($this->repeaters) > 0 )) {
$this->response = "";
foreach($this->repeaters as $index=>$repeater) {

    $this->response .= $repeater." ";

}
}

}

    /**
     *
     */
    function makeSMS() {
$this->makeResponse();
        $this->sms_message = "REPEATER | ";
        $this->sms_message .= $this->response;
        $this->sms_message .= ' | TEXT CHANNEL';

        $this->thing_report['sms'] = $this->sms_message;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create("repeater", $this->node_list, "repeater");

        $choices = $this->thing->choice->makeLinks("repeater");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }

function makeImage() {
$this->image = null;
}

}
