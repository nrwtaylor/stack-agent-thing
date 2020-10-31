<?php
/**
 * Call.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Call extends Agent
{
    public $var = 'hello';

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->test = "Development code";

        $this->thing_report["info"] =
            "CALL is a tool for understanding audio-visual conference related text.";
        $this->thing_report["help"] = 'Text CALL <text>.';

        $this->node_list = ["call" => ["call", "uuid"]];

        $this->current_time = $this->thing->json->time();

        $this->initCall();
    }

    public function set()
    {
        $this->setCall();
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        return $this->thing_report;
    }

    /**
     *
     */
    public function makeChoices()
    {
        $this->choices = false;
        $this->thing_report['choices'] = $this->choices;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms = "CALL | ";

        $this->sms_message = $sms; 
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     */

    /**
     *
     */


    public function setCall() {}


    /**
     *
     * @return unknown
     */
    public function getCall()
    {
    }

    /**
     *
     */
    public function initCall()
    {
    }

    public function run()
    {
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/call.pdf';
        $this->node_list = ["call" => ["call"]];
        $web = "";
        $web .= '<a href="' . $link . '">';
        $web .= $this->html_image;
        $web .= "</a>";
        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "call",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["call", "refreshed_at"],
                $time_string
            );
        }
    }


    // TODO: Test extraction of telephone numbers
    public function readCall($text = null) {
        $file = $this->resource_path . 'call/call-test'.'.txt';

        if (file_exists($file)) {

            $text = file_get_contents($file);

        }

        $url_agent = new Url($this->thing, "url");

        $this->urls = $url_agent->extractUrls($text);

        $telephonenumber_agent = new Telephonenumber($this->thing, "telephonenumber");
//var_dump($text);
        $this->telephone_numbers = $telephonenumber_agent->extractTelephonenumbers($text);

//var_dump($this->telephone_numbers);
//var_dump($this->urls);

        return;
    }

    /**
     *
     * @return unknown
     */

    public function isCall($text)
    {
        // Contains word call?
        return false;
    }

    public function readSubject()
    {
        //$input = strtolower($this->subject);
        $input = $this->subject;
        if (isset($this->agent_input)) {
            if (strtolower($this->agent_input) == 'call') {return;}
            if (!$this->thing->isEmpty($this->agent_input)) {
                $input = $this->agent_input;
            }

        }

        $this->input = $input;

        $this->readCall($input);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'call') {
                $this->getCall();
                return;
            }
        }

        $this->getCall();

        return;
    }
}
