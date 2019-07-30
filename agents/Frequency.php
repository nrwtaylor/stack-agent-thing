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

class Frequency extends Agent
{


    /**
     *
     */
    function init() {
        $this->stack_state = $this->thing->container['stack']['state'];
        $this->short_name = $this->thing->container['stack']['short_name'];

        $this->created_at =  strtotime($this->thing->thing->created_at);

        $this->thing->log('started running on Thing ' . date("Y-m-d H:i:s") . '');

        $this->node_list = array("frequency"=>
            array("frequency", "snowflake"));

        $this->aliases = array("learning"=>array("good job"));

//        $this->makePNG();

        $this->thing_report['help'] = "Recognizes frequencies.";

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


function hasFrequency($text) {


$this->extractFrequencies($text);
if ((isset($this->frequencies)) and (count($this->frequencies) > 0)) {return true;}
return false;

}

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractFrequencies($input) {
        if (!isset($this->frequencies)) {
            $this->frequencies = array();
        }

        $pattern = "|[0-9]{3}.[0-9]{3}|";

        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        //array_pop($arr);
        $this->frequencies = $arr;
        return $arr;
    }


    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractFrequency($input) {
        $frequencies = $this->extractFrequencies($input);
        if (!(is_array($frequencies))) {return true;}

        if ((is_array($frequencies)) and (count($frequencies) == 1)) {
            $this->frequency = $frequencies[0];
            $this->thing->log('found a frequency (' . $this->frequency . ') in the text.');
            return $this->frequency;
        }

        if  ((is_array($frequencies)) and (count($frequencies) == 0)) {return false;}
        if  ((is_array($frequencies)) and (count($frequencies) > 1)) {return true;}

        return true;
    }



    /**
     *
     */
    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/frequency';

        $this->node_list = array("frequency"=>array("frequency", "snowflake"));
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "frequency");
        $choices = $this->thing->choice->makeLinks('frequency');

        $alt_text = "a QR code with a frequency";

        $web = '<a href="' . $link . '">';
        //$web_prefix = "http://localhost:8080/";
        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/frequency.png" jpg"
                width="100" height="100"
                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/frequency.txt">';

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
        $this->thing->json->writeVariable(array("frequency",
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
        $this->extractFrequency($this->input);

//            $pieces = explode(" ", strtolower($this->input));


        $input = $this->input;
        $whatIWant = $this->input;
        if (($pos = strpos(strtolower($input), "frequency")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("frequency")); 
        } elseif (($pos = strpos(strtolower($input), "frequency")) !== FALSE) { 
            $whatIWant = substr(strtolower($input), $pos+strlen("frequency")); 
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
if ((isset($this->frequencies)) and (count($this->frequencies) > 0 )) {
$this->response = "";
foreach($this->frequencies as $index=>$frequency) {

    $this->response .= $frequency." ";

}
}

}

    /**
     *
     */
    function makeSMS() {
$this->makeResponse();
        $this->sms_message = "FREQUENCY | ";
        $this->sms_message .= $this->response;
        $this->sms_message .= ' | TEXT CHANNEL';

        $this->thing_report['sms'] = $this->sms_message;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create("frequency", $this->node_list, "frequency");

        $choices = $this->thing->choice->makeLinks("frequency");
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }

function makeImage() {
$this->image = null;
}

}
