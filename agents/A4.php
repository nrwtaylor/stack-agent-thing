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
        $time_string = $this->thing->Read(["a4", "refreshed_at"]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(["a4", "refreshed_at"], $time_string);
        }

        $this->refreshed_at = strtotime($time_string);

        $this->a4 = $this->thing->Read(["a4", "alpha"]);
    }

    /**
     *
     */
    public function set()
    {
        if (is_string($this->a4)) {
            $this->thing->Write(["a4", "alpha"], $this->a4);
        }
    }

    /**
     *
     */
    public function makeA4()
    {
        if (ctype_alpha($this->a4)) {
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
        $this->a4 = $str;

        // https://stackoverflow.com/questions/2257441/random-string-generation-with-upper-case-letters-and-digits
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $this->thing_report["info"] = "This makes four character identities.";
        if (!isset($this->thing_report['help'])) {
            $this->thing_report["help"] =
                'This is about four character words.  Try "n6".';
        }

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }
    }

    /**
     *
     */
    function makeChoices()
    {
        $this->thing->choice->Create($this->agent_name, $this->node_list, "a4");

        $choices = $this->thing->choice->makeLinks('a4');
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
        $web .= "Four letter alpha (A4) is " . strtoupper($this->a4) . ".";
        //$web .= '<br>A4 says, "' . $this->sms_message . '".';

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
        $sms = "A4 | " . $this->a4 . ". " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     */
    function makeMessage()
    {
        $message = "Stackr got this A4 for you.<br>";
        $message .= $this->a4 . ".";

        $this->thing_report['message'] = $message;
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
            $a4 = strtolower($this->a4s[0]);
            return $a4;
        }

        if (count($this->a4s) == 0) {
            $this->response =
                "Did not find any four-character alpha sequences.";
            $a4 = null;
            return $a4;
        }

        $a4 = false;
        return $a4;
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function extractA4s($input)
    {
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

        $input = strtolower($this->input);

        if (is_string($this->a4)) {
            $this->response .= 'Got thing ' . $this->a4 . ". ";
        }

        $a4 = $this->extractA4($input);

        if (is_string($a4)) {
            $this->response .= "Saw A4 is " . $a4 . ". ";
            $this->a4 = $a4;
        }

        if (!is_string($this->a4)) {
            $this->makeA4();
        }
    }
}
