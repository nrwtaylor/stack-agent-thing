<?php
/**
 * A4.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class A4 extends Agent
{
    public $var = 'hello';

    /**
     *
     */
    function init()
    {
        $this->test = "Development code";

        $this->node_list = ["a4" => ["a4"]];
    }

    /**
     *
     */
    public function get()
    {
        // Borrow this from iching
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable(["a4", "refreshed_at"]);

        if ($time_string == false) {
            //            $this->thing->json->setField("variables");
            $time_string = $this->thing->time();
            $this->thing->json->writeVariable(
                ["a4", "refreshed_at"],
                $time_string
            );
        }

        $this->refreshed_at = strtotime($time_string);

        //        $this->thing->json->setField("variables");
        $this->alpha = $this->thing->json->readVariable(["a4", "alpha"]);
        //        $this->text = $this->thing->json->readVariable( array("a4", "text") ); // Test because this will become A6.
    }

    /**
     *
     */
    public function set()
    {
        if ($this->alpha == false) {
            $this->makeA4();
            $this->thing->json->writeVariable(["a4", "alpha"], $this->alpha);
            //            $this->thing->json->writeVariable( array("a4", "text"), $this->text );
        }
    }

    /**
     *
     */
    public function makeA4()
    {
        if (ctype_alpha($this->alpha)) {
            $this->response = "Read this four-character alpha sequence.";
            return;
        }

        $this->response = "Made this four-character alpha sequence.";

        //        $this->random = new Random($this->thing,"random AAAA ZZZZ");
        //        $this->alpha = $this->random->number;

        // https://www.xeweb.net/2011/02/11/generate-a-random-string-a-z-0-9-in-php/
        // devstack
        $length = 4;
        $str = "";
        $characters = array_merge(range('A', 'Z'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        $this->alpha = $str;

        // https://stackoverflow.com/questions/2257441/random-string-generation-with-upper-case-letters-and-digits
    }

    // -----------------------

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        // This should be the code to handle non-matching responses.

        $to = $this->thing->from;
        $from = "a4";

        $choices = false;

        //$this->makeSMS();
        //$this->makeMessage();

        $this->makeChoices();
        //$this->makeWeb();

        //$this->makeEmail();

        $this->thing_report["info"] = "This makes four character identities.";
        if (!isset($this->thing_report['help'])) {
            $this->thing_report["help"] =
                'This is about four character words.  Try "n6".';
        }

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        //return $this->thing_report;
    }

    /**
     *
     */
    function makeChoices()
    {
        $this->thing->choice->Create($this->agent_name, $this->node_list, "n6");

        $choices = $this->thing->choice->makeLinks('n6');
        $this->thing_report['choices'] = $choices;
    }

    /**
     *
     */
    function makeEmail()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/a4';

        $this->node_list = ["a4" => ["a4"]];
        // Make buttons
        $this->thing->choice->Create($this->agent_name, $this->node_list, "a4");
        $choices = $this->thing->choice->makeLinks('a4');

        $web = '<a href="' . $link . '">';
        //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/roll.png" jpg"
        //                width="100" height="100"
        //                alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/roll.tx$

        //$web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';

        //        if (!isset($this->html_image)) {$this->makePNG();}

        //        $web .= $this->html_image;

        //        $web .= "</a>";
        //        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time(time() - $this->refreshed_at);
        $web .= "This number was made about " . $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['email'] = $web;
    }

    /**
     *
     */
    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $this->node_list = ["a4" => ["a4"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "web"
        );
        $choices = $this->thing->choice->makeLinks('web');

        $web = "";
        $web .= "Four letter alpha (A4) is " . $this->alpha . ".";
        $web .= '<br>A4 says, "' . $this->sms_message . '".';

        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time(time() - $this->refreshed_at);
        $web .= "<p>The alpha group was made about " . $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    /**
     *
     */
    function makeSMS()
    {
        $sms = "A4 | " . $this->alpha . " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     */
    function makeMessage()
    {
        $message = "Stackr got this A4 for you.<br>";
        $message .= $this->alpha . ".";

        $this->thing_report['message'] = $message;

        return;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractA4($input)
    {
        if (!isset($this->a4s)) {
            $this->response = "Found lots of four-character alpha sequences.";
            $this->a4s = $this->extractA4s($input);
        }

        if (count($this->a4s) == 1) {
            $this->response = "Found a four-character alpha sequence.";
            $this->a4 = strtolower($this->a4s[0]);
            return $this->a4;
        }

        if (count($this->a4s) == 0) {
            $this->response =
                "Did not find any four-character alpha sequences.";
            $this->a4 = null;
            return $this->a4;
        }

        $this->a4 = false;
        return false;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractA4s($input)
    {
        // Devstack this just does numbers.
        if (!isset($this->a4s)) {
            $this->a4s = [];
        }

        $pattern = "|\b[a-zA-Z]{4}\b|";
        preg_match_all($pattern, $input, $m);

        $arr = $m[0];
        $this->a4s = $arr;
        return $this->a4s;
    }

    /**
     *
     */
    public function readSubject()
    {
        $this->response = "Read.";

        $input = strtolower($this->subject);

        if ($this->agent_input != null) {
            $input = strtolower($this->agent_input);
        }

        $this->extractA4($input);

        if ($this->a4 == null) {
            $this->a4 = "XXXX";
        }
    }
}
