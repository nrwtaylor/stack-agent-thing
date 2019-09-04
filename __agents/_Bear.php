<?php
/**
 * Cat.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Bear extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
//        $this->agent_name = "bear";
        $this->test= "Development code";
        $this->thing_report["info"] = "This is a ranger who keeps an eye open for picnickers. And bears.";
        $this->thing_report["help"] = "Find the bears. However you can. Text BEAR.";
        $this->game_name = "pic-a-nic";

        $this->contact = "VE7RVF control";
        $this->primary_channel = "146.580";

        if ($this->game_name == "pic-a-nic") {
            $this->contact = "146.580 CONTROL";
            $this->primary_channel = "146.565";
        }


    }

    /**
     *
     */
    private function getNegativetime() {

        // And example of using another agent to get information the cat needs.
        $agent = new Negativetime($this->thing, "bear");
        $this->negative_time = $agent->negative_time; //negative time is asking

    }


    /**
     *
     * @return unknown
     */
/*
    public function respond() {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "bear";

        $this->makeSMS();
        $this->makeChoices();

        $this->thing_report["info"] = "This is a bear in a park.";
        $this->thing_report["help"] = "This is finding picnics. And getting your friends to join you. Text BEAR.";

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }
*/

    /**
     *
     */
    function makeSMS() {
        $this->node_list = array("bear"=>array("bear", "ranger"));
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }

    public function respond() {
        $this->thing->flagGreen();

        $to = $this->thing->from;
        $from = "bear";

        $this->makeSMS();
        $this->makeChoices();

//        $this->thing_report["info"] = "This is a camper in a park with a picnic basket.";
//        $this->thing_report["help"] = "This is finding picnics. And getting your friends to join you. Text B$

        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'] ;

        return $this->thing_report;
    }

    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create('channel', $this->node_list, "bear");
        $choices = $this->thing->choice->makeLinks('bear');
        $this->thing_report['choices'] = $choices;
    }

    function doBear($text = null) {
        // Yawn.

        // Wake up. It's not as cold.

//        $whatIWant = $this->input;
//        if (($pos = strpos(strtolower($this->input), "\@ednabot")) !== FALSE) { 
//            $whatIWant = substr(strtolower($this->input), $pos+strlen("\@ednabot")); 
//        } elseif (($pos = strpos(strtolower($this->input), "\@ednabot")) !== FALSE) { 
//            $whatIWant = substr(strtolower($this->input), $pos+strlen("\@ednabot")); 
//        }
//$this->input = $whatIWant;

/*
if ( ( strtolower($text) != "bear") and (strtolower($text) != "bear bear") ) {
    $this->bear_message = $this->bear_response;
$this->response = $this->bear_response;

return;
}
*/
        if ($this->agent_input == null) {

// Simple.
//            $array = array('Find the picanic(s). There is at least one picnic basket broadcasting on 146.580. Contact VE7RVF control for help and support.');

// But bears like variety.

$contact = $this->contact;
$primary_channel = $this->primary_channel;

            $array = array('Find the picanic(s). There is at least one camper broadcasting on 146.580. Contact VE7RVF control for help and support.',
'Find the picanic(s). There is at least one camper broadcasting on ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). There is at least one camper broadcasting on ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). There is at least one camper broadcasting on ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). There is at least one camper broadcasting on ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). A picanic has been heard on ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). A picnicker is broadcasting on ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). The camper is broadcasting on ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). Amateur call tagged CAMPER is broadcasting on ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). There has been a picnic beaconing on ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). Use the proword NO PLAY during games for real things. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). A pic-a-nic has been heard on ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). A picnic has been heard on Simplex ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). A picnic has been heard on Amateur Radio Service Simplex ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). A picnic has been heard on Amateur Radio Service Simplex ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). Listen to ' . $primary_channel . '. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). Bear query Ginko Yurishiro. I SPELL. GOLF INDIA ETC. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). Bear query Kozlov Leifonovich Grebnev. I SPELL. KILO OSCAR ZULU ETC. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). Bear query Vladimir Goudenov Grizzlikof. I SPELL. VICTOR LIMA ETC. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). Bear query Herbert Percival. I SPELL. HOTEL ECHO ROMEO ETC. Contact ' . $contact . ' for help and support.',
'Find the picanic(s). Let control know that you might be Iorek Byrnison. I SPELL. INDIA OSCAR ROMEO ETC. Contact ' . $contact . ' for help and support.'
);

// Please help me understand how to use these words.
// Halkomelem
// s.pέ:θ
// x̌əyƛ̕έls
// k̕ʷí:cəl
// s.péʔeθ
// Moksgm'ol

            $k = array_rand($array);
            $v = $array[$k];


            // Say what the bear picked.

            $this->response = $v;

            // Bear goes back to sleep.
            $this->bear_message = $this->response;
        } else {
            $this->bear_message = $this->agent_input;
        }

    }



    /**
     *
     * @return unknown
     */
    public function readSubject() {

//var_dump($this->input);
//        $this->doBear($this->input);

$t = new Compression($this->thing, "compression bear");


$i = $this->input;
var_dump($i);
        $whatIWant = $this->input;
        if (($pos = strpos(strtolower($this->input), "@ednabot")) !== FALSE) { 
            $whatIWant = substr(strtolower($this->input), $pos+strlen("@ednabot")); 
        } elseif (($pos = strpos(strtolower($this->input), "@ednabot")) !== FALSE) { 
            $whatIWant = substr(strtolower($this->input), $pos+strlen("@ednabot")); 
        }
$i = trim($whatIWant);
//var_dump($i);
$bear_name = "ted";
$bear_response = "Quiet.";
$min_lev = 1e99;
foreach($t->agent->matches as $type=>$bears) {
shuffle($bears);
foreach($bears as $key=>$value) {

$bear_text = $value['proword']. " " .$value['words'] . "\n";

$lev = levenshtein($i, $bear_text);
//$lev = levenshtein($this->input, $bear_text);
if ($lev < $min_lev) {$min_lev = $lev;
$bear_name = $value['words'];
$bear_response = ucwords($value['words']) . " is a " . $value['proword'] . ".";
}

}
}

//if (strtolower($bear_name) == strtolower($this->input)) {
if (stripos($i, $bear_name) !== false) {
//if (stripos($this->input, $bear_name) !== false) {
$bear_response = "Found bear. ". $bear_response;

} else {

//$bear_response = "Did not find bear. ". $bear_response;


}

$this->bear_response = $bear_response;
//var_dump($this->bear_response);
//var_dump($bear_response);

//        $this->doBear($this->input);
        $this->doBear($i);
        return false;
    }


}

