<?php
/**
 * Alphanumeric.php
 *
 * @package default
 */


// 4 letters.  Is handy to have.
namespace Nrwtaylor\StackAgentThing;

// Transparency
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Alphanumeric extends Agent
{


    /**
     *
     */
    function init() {
        $this->node_list = array("alphanumeric"=>
            array("alpha","number"));

        $this->aliases = array("learning"=>array("good job"));
        $this->recognize_french = true; // Flag error

$this->filterAlphanumeric("123414sdfas asdfsad 234234 *&*dfg") ;

    }


    /**
     *
     */
    function get() {
//        $this->alphanumeric_agent = new Variables($this->thing, "variables alphanumeric " . $this->from);

//        $this->alphanumeric = $this->alpha_agent->getVariable("alphanumeric");
//        $this->refreshed_at = $this->alpha_agent->getVariable("refreshed_at");
    }


    /**
     *
     */
    function set() {

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("alphanumeric",
                "refreshed_at"),  $this->thing->json->time()
        );

//        $this->alpha_agent->setVariable("alphanumeric", $this->alpha);

//        $this->alpha_agent->setVariable("refreshed_at", $this->current_time);

    }

    public function trimAlphanumeric($text) {
$letters = array();
$new_text = "";
$flag = false;
foreach(range(0, mb_strlen($text)) as $i) {

$letter = substr($text,$i,1);
//if (ctype_alpha($letter)) {$flag = true;}
if (ctype_alnum($letter)) {$flag = true;}


//if ((!ctype_alpha($letter)) and ($flag == false)) {$letter = "";}
if ((!ctype_alnum($letter)) and ($flag == false)) {$letter = "";}

$letters[] = $letter;

}

//$text = $new_text;

$new_text = "";
$flag = false;
foreach(array_reverse($letters) as $i=>$letter) {

//$letter = substr($text,$i,1);
//if (ctype_alpha($letter)) {$flag = true;}
if (ctype_alnum($letter)) {$flag = true;}


//if ((!ctype_alpha($letter)) and ($flag == false)) {$letter = "";}
if ((!ctype_alnum($letter)) and ($flag == false)) {$letter = "";}

$n = count($letters) - $i -1;

$letters[$n] = $letter;

}
$new_text = implode("",$letters);

return $new_text;




    }


    public function filterAlphanumeric($text) {
$letters = array();
//$new_text = "";
//$flag = false;
foreach(range(0, mb_strlen($text)) as $i) {

$letter = substr($text,$i,1);
//if (ctype_alpha($letter)) {$flag = true;}
if (ctype_alnum($letter)) {
$letters[] = $letter;
} else {
$letters[] = " ";
}
//$flag = true;}


//if ((!ctype_alpha($letter)) and ($flag == false)) {$letter = "";}
//if ((!ctype_alnum($letter)) and ($flag == false)) {$letter = "";}

//$letters[] = $letter;

}

//var_dump($letters);
$new_text = implode("" ,$letters);
$this->filter_alphanumeric = $new_text;
return $new_text;




    }



    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractAlphanumerics($input = null) {
        if ($input == null) {
            $input = $this->subject;
        }
        // https://www.regular-expressions.info/floatingpoint.html
        // Life goals regex that does this

        if (!isset($this->alphas)) {
            $this->alphanumerics = array();
        }

        $pieces = explode(" ", $input);
        $this->alphas = [];
        foreach ($pieces as $key=>$piece) {

            if (ctype_alnum($piece)) {
                $this->alphanumerics[] = $piece;
                continue;
            }

            // X - Specify. Z - Available.
            //if ((strtoupper($piece) == "X") or (strtoupper($piece == "Z"))) {
            //    $this->alphas[] = $piece;
            //    continue;
            //}


            //            if (is_numeric(substr($piece,1,-1))) {
            //                if ((substr($piece,0,1) == "(") and (substr($piece,-1,1) == ")")) {
            //                    $this->numbers[] = -1 * substr($piece,1,-1);
            //                    continue;
            //                }

            //                $this->numbers[] = substr($piece,1,-1);
            //                continue;
            //            }

            if (ctype_alnum(str_replace(",", "", $piece))) {
                $this->alphanumerics[] = str_replace(",", "", $piece);
                continue;
            }

            // preg_match_all('!\d+!', $piece, $matches);
            preg_match_all('/^\p{Alphabetic}+$/',  $piece, $matches);

            foreach ($matches[0] as $key=>$match) {
                $this->alphanumerics[] = $match;
            }

        }

        return $this->alphanumerics;
    }


    /**
     *
     */
    function extractAlphanumeric() {
        $this->alpha = false; // No numbers.
        if (!isset($this->alphanumerics)) {$this->extractAlphanumerics();}

        if (isset($this->alphanumerics[0])) {
            $this->alphanumeric = $this->alphanumerics[0];
        }

    }


    /**
     *
     */
    public function respond() {
        // Thing actions

//        $this->thing->json->setField("settings");
//        $this->thing->json->writeVariable(array("alphanumeric",
//                "received_at"),  $this->thing->json->time()
//        );

        $this->thing->flagGreen();

        $from = $this->from;
        $to = $this->to;

        $subject = $this->subject;

        // Now passed by Thing object
        $uuid = $this->uuid;
        $sqlresponse = "yes";

        //$message = "Thank you here is a Number.<p>" . $this->web_prefix . "thing/$uuid\n$sqlresponse \n\n<br> ";
        //$message .= '<img src="' . $this->web_prefix . 'thing/'. $uuid.'/receipt.png" alt="thing:'.$uuid.'" height="92" width="92">';

        $this->makeSMS();

        //$this->thing_report['email'] = array('to'=>$from,
        //   'from'=>'uuid',
        //   'subject'=>$subject,
        //   'message'=>$message,
        //   'choices'=>$choices);

        //$this->makePNG();

        $this->makeChoices();



        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->makeWeb();

        $this->thing_report['thing'] = $this->thing->thing;

        $this->thing_report['help'] = "This extracts alphanumerics from the datagram.";

    }


    /**
     *
     * @return unknown
     */
    public function readSubject() {
        // If the to line is a UUID, then it needs
        // to be sent a receipt.
        if ($this->agent_input == null) {
            $input = $this->subject;
        }

        if ($this->agent_input == "alphanumeric") {
            $input = $this->subject;
        } else {
            $input = $this->agent_input;
        }

        $this->extractAlphanumerics($input);
        $this->extractAlphanumeric();


        if ($this->alphanumeric == false) {
            $this->get();
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            if ($input == 'alphanumeric') {
                $this->getAlphanumeric();
                $this->response = "Last alphanumeric retrieved.";
                return;
            }
        }

        $status = true;

        return $status;
    }


    /**
     *
     */
    function makeWeb() {

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/uuid';

        $this->node_list = array("number"=>array("number", "thing"));

        $web = '<a href="' . $link . '">';
        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/uuid.png">';
        $web .= "</a>";

        $web .= "<br>";
        $web .= '<b>' . ucwords($this->agent_name) . ' Agent</b><br>';
        $web .= $this->subject . "<br>";

        if (!isset($this->numbers[0])) {
            $web .= "No numbers found<br>";
        } else {
            $web .= "First number is ". $this->alphanumerics[0] . "<br>";
            $web .= "Extracted numbers are:<br>";
        }
        foreach ($this->alphanumerics as $key=>$alphanumeric) {
            $web .= $alphanumeric . "<br>";
        }

        if ($this->recognize_french == true) {
            // devstack
        }

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }


    /**
     *
     */
    function makeSMS() {
        $sms = "ALPHANUMERIC | ";
        //foreach ($this->numbers as $key=>$number) {
        //    $this->sms_message .= $number . " | ";
        //}
        $sms .= $this->alphanumeric;
        //$this->sms_message .= 'devstack';

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }


    /**
     *
     */
    function makeChoices() {

        $choices = false;
        $this->thing_report['choices'] = $choices;
        $this->choices = $choices;
    }


    /**
     *
     * @return unknown
     */
/*
    public function makePNG() {
        $text = "thing:".$this->alphas[0];

        ob_clean();

        ob_start();

        QRcode::png($text, false, QR_ECLEVEL_Q, 4);

        $image = ob_get_contents();
        ob_clean();

        $this->thing_report['png'] = $image;
        return $this->thing_report['png'];
    }
*/

}
