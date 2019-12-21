<?php
/**
 * Alpha.php
 *
 * @package default
 */


// 4 letters.  Is handy to have.
namespace Nrwtaylor\StackAgentThing;

// Transparency
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Alpha extends Agent
{

public function isAlpha($token) {

                    if (ctype_alpha($token)) {
                        return true;
                    }
return false;


}


    /**
     *
     */
    function init() {
        $this->node_list = array("alpha"=>
            array("number"));

        $this->aliases = array("learning"=>array("good job"));
        $this->recognize_french = true; // Flag error
    }


    /**
     *
     */
    function get() {
        $this->alpha_agent = new Variables($this->thing, "variables alpha " . $this->from);

        $this->alpha = $this->alpha_agent->getVariable("alpha");
        $this->refreshed_at = $this->alpha_agent->getVariable("refreshed_at");
    }


    /**
     *
     */
    function set() {
        $this->alpha_agent->setVariable("alpha", $this->alpha);

        $this->alpha_agent->setVariable("refreshed_at", $this->current_time);

    }

    public function trimAlpha($text) {
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


    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    function extractAlphas($input = null) {
        if ($input == null) {
            $input = $this->subject;
        }
        // https://www.regular-expressions.info/floatingpoint.html
        // Life goals regex that does this

        if (!isset($this->alphas)) {
            $this->alphas = array();
        }

        $pieces = explode(" ", $input);
        $this->alphas = [];
        foreach ($pieces as $key=>$piece) {

            if (ctype_alpha($piece)) {
                $this->alphas[] = $piece;
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

            if (ctype_alpha(str_replace(",", "", $piece))) {
                $this->alphas[] = str_replace(",", "", $piece);
                continue;
            }

            // preg_match_all('!\d+!', $piece, $matches);
//var_dump($piece);

if (ctype_alpha($piece)) {

$this->alphas[] = $piece;


}

//            preg_match_all('/^\p{Alphabetic}+$/',  $piece, $matches);

//            foreach ($matches[0] as $key=>$match) {
//                $this->alphas[] = $match;
//            }

        }

        return $this->alphas;
    }


    /**
     *
     */
    function extractAlpha() {
        $this->alpha = false; // No numbers.
        if (!isset($this->alphas)) {$this->extractAlphas();}

        if (isset($this->alphas[0])) {
            $this->alpha = $this->alphas[0];
        }

    }


    /**
     *
     */
    public function respond() {
        // Thing actions

        $this->thing->json->setField("settings");
        $this->thing->json->writeVariable(array("alpha",
                "received_at"),  $this->thing->json->time()
        );

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

        $this->thing_report['help'] = "This extracts alphas from the datagram.";

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

        if ($this->agent_input == "alpha") {
            $input = $this->subject;
        } else {
            $input = $this->agent_input;
        }

        $this->extractAlphas($input);
        $this->extractAlpha();


        if ($this->alpha == false) {
            $this->get();
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            if ($input == 'alpha') {
                $this->getAlpha();
                $this->response = "Last alpha retrieved.";
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
            $web .= "First number is ". $this->alphas[0] . "<br>";
            $web .= "Extracted numbers are:<br>";
        }
        foreach ($this->alphas as $key=>$alpha) {
            $web .= $alpha . "<br>";
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
        $sms = "ALPHA | ";
        //foreach ($this->numbers as $key=>$number) {
        //    $this->sms_message .= $number . " | ";
        //}
        $sms .= $this->alpha;
        //$this->sms_message .= 'devstack';

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }


    /**
     *
     */
    function makeChoices() {
        $this->thing->choice->Create("number", $this->node_list, "number");

        $choices = $this->thing->choice->makeLinks("number");
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
