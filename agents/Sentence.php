<?php
/**
 * Sentence.php
 *
 * @package default
 */


// 4 letters.  Is handy to have.
namespace Nrwtaylor\StackAgentThing;

// Transparency
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Sentence extends Agent
{


    /**
     *
     */
    function init() {
        $this->node_list = array("sentence"=>
            array("sentence"));

        //$this->getSlug("123414sdfas asdfsad 234234 *&*dfg") ;
$this->state = "on";
//        $this->state = $this->settings['state'];
    }


    /**
     *
     */
    function get() {
    }


    /**
     *
     */
    function set() {

        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(array("sentence",
                "refreshed_at"),  $this->thing->json->time()
        );

//if (!isset($this->sentence)) {$this->thing_report = true;}

    }

public function getSentence($text = null) {

if ($text == null) {return true;}
//if ($this->state == "off") {$this->slug = ""; return null;}

$alphanumeric_agent = new Alphanumeric($this->thing,"alphanumeric");
$slug = $alphanumeric_agent->filterAlphanumeric($text);
//var_dump($slug);

$despaced_slug = preg_replace('/\s+/', ' ',$slug);
$slug = str_replace(" ","-",$despaced_slug);
$slug = strtolower($slug);
$slug = trim($slug,"-");
$this->slug = $slug;
}

    /**
     *
     */
    public function respondResponse() {
        // Thing actions

        $this->thing->flagGreen();

        $this->makeSMS();
        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->makeWeb();

        $this->thing_report['thing'] = $this->thing->thing;

        $this->thing_report['help'] = "This gets sentences from the datagram.";

    }

public function extractSentences($text = null) {

if ($text == null) {$text = $this->input;}

$sentences = explode(". ", $text);

$this->sentences = $sentences;

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

        if ($this->agent_input == "sentence") {
            $input = $this->subject;
        } else {
            $input = $this->agent_input;
        }

$this->extractSentences();

// dev not needed for now
//        $this->extractSlugs($input);
//        $this->extractSlug();


        if ((!isset($this->sentence)) or ($this->sentence == false)) {
            $this->getSentence($input);
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            if ($input == 'sentence') {
                $this->getSentence();
                $this->response = "Last sentence retrieved.";
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


/*
        if (!isset($this->slugs[0])) {
            $web .= "No slugs found<br>";
        } else {
            $web .= "First slug is ". $this->slugs[0] . "<br>";
            $web .= "Extracted slugs are:<br>";
        }
        foreach ($this->slugs as $key=>$slug) {
            $web .= $slug . "<br>";
        }

        if ($this->recognize_french == true) {
            // devstack
        }
*/
        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }


    /**
     *
     */
    function makeSMS() {
        $sms = "SENTENCE";
        //foreach ($this->numbers as $key=>$number) {
        //    $this->sms_message .= $number . " | ";
        //}

$sentence = "No sentence found.";
if (isset($this->sentence)) {
        $sentence  = " | " . $this->sentence;
        //$this->sms_message .= 'devstack';
}

$sms .= $sentence;

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


}
