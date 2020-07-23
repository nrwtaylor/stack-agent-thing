<?php
/**
 * Thankyou.php
 *
 * @package default
 */


namespace Nrwtaylor\StackAgentThing;

class Thankyou extends Agent {

    public $var = 'hello';


    /**
     *
     * @param Thing   $thing
     * @param unknown $text  (optional)
     */
    function init() {
        $this->agent_name = "thankyou";
        $this->test= "Development code";
        $this->thing_report["info"] = "This responds to a thank you.";
        $this->thing_report["help"] = "Why thanks. Glad it was helpful and/or useful.";
    }

    public function makeSnippet() {
    $this->thing_html['snippet'] = $this->message ;

    }

    public function makeWeb() {
    $this->thing_html['web'] = $this->message ;

    }


    /**
     *
     */
    public function makeSMS() {
        $this->node_list = array("thankyou"=>array("thankyou"));
        $m = strtoupper($this->agent_name) . " | " . $this->response;
        $this->sms_message = $m;
        $this->thing_report['sms'] = $m;
    }

    // function query_time_server

    /**
     *
     */
    function makeChoices() {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    function doThankyou($text = null) {

$litany_agent = new Litany($this->thing, "litany thankyou");

// https://www.google.com/search?responses+to+the+word+thank+you
            $array = array("Why shucks. ",
'Glad to help. ',
'Anytime. ',
'No worries. ',
'Thank you! ',
'Sure! ',"It's no problem at all. ", "You're welcome. ");
            $k = array_rand($array);
            $v = $array[$k];


$this->response .= $v;

if (isset($litany_agent->message)) {

$this->response .= $litany_agent->message;
$this->message = $v;
}


        // If we didn't receive the command NTP ...
if (strtolower($this->input) != "thankyou") {
//    $this->thankyou_message = $this->thankyou_response;
//$this->response .= "Quiet. ";
return;
}

    //$this->thankyou_message = $this->response;
//$this->response .= $this->thankyou_response;

    }



    /**
     *
     * @return unknown
     */
    public function readSubject() {

        $this->doThankyou($this->input);

        return false;
    }


}
