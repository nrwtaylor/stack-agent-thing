<?php
/**
 * Slug.php
 *
 * @package default
 */


// 4 letters.  Is handy to have.
namespace Nrwtaylor\StackAgentThing;

// Transparency
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Slug extends Agent
{


    /**
     *
     */
    function init() {
        $this->node_list = array("slug"=>
            array("slug"));

        //$this->getSlug("123414sdfas asdfsad 234234 *&*dfg") ;
        $this->state = $this->settings['state'];
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
        $this->thing->json->writeVariable(array("slug",
                "refreshed_at"),  $this->thing->json->time()
        );

    }

public function getSlug($text = null) {

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

public function extractSlug($text = null) {

if ($text == null) {return true;}
//if ($this->state == "off") {$this->slug = ""; return null;}

$alphanumeric_agent = new Alphanumeric($this->thing,"alphanumeric");
$slug = $alphanumeric_agent->filterAlphanumeric($text);
//var_dump($slug);

$despaced_slug = preg_replace('/\s+/', ' ',$slug);
$slug = str_replace(" ","-",$despaced_slug);
$slug = strtolower($slug);
$slug = trim($slug,"-");
return $slug;
}


    /**
     *
     */
    public function respond() {
        // Thing actions

        $this->thing->flagGreen();

        $this->makeSMS();
        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report['info'] = $message_thing->thing_report['info'] ;

        $this->makeWeb();

        $this->thing_report['thing'] = $this->thing->thing;

        $this->thing_report['help'] = "This makes a slug from the datagram.";

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

        if ($this->agent_input == "slug") {
            $input = $this->subject;
        } else {
            $input = $this->agent_input;
        }

// dev not needed for now
//        $this->extractSlugs($input);
//        $this->extractSlug();


        if ((!isset($this->slug)) or ($this->slug == false)) {
            $this->getSlug($input);
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {

            if ($input == 'slug') {
                $this->getSlug();
                $this->response = "Last slug retrieved.";
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
        $sms = "SLUG";
        //foreach ($this->numbers as $key=>$number) {
        //    $this->sms_message .= $number . " | ";
        //}

if (isset($this->slug)) {
        $sms .= " | " . $this->slug;
        //$this->sms_message .= 'devstack';
}
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
