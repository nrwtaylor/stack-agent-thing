<?php
/**
 * Webex.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Webex extends Agent
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
            "WEBEX is a tool for hosting audio-visual conferences.";
        $this->thing_report["help"] = 'Click on the image for a PDF.';

        $this->node_list = ["webex" => ["webex", "uuid"]];

        $this->current_time = $this->thing->json->time();

        $this->initWebex();
    }

    public function set()
    {
        $this->setWebex();
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
        $sms = "WEBEX | ";

        $this->sms_message = $sms; 
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     */

    /**
     *
     */


    public function setWebex() {}


    /**
     *
     * @return unknown
     */
    public function getWebex()
    {
        $this->thing->json->setField("variables");
        $this->decimal_webex = $this->thing->json->readVariable([
            "webex",
            "decimal",
        ]);

        if ($this->decimal_webex == false) {
            $this->thing->log(
                $this->agent_prefix . ' did not find a decimal webex.',
                "INFORMATION"
            );
            return true;
        }

    }

    /**
     *
     */
    public function initWebex()
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
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/webex.pdf';
        $this->node_list = ["webex" => ["webex"]];
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
            "webex",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["webex", "refreshed_at"],
                $time_string
            );
        }
    }

    /**
     *
     * @return unknown
     */

    public function isWebex($text)
    {
        // Contains word webex?
        return false;
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'webex') {
                $this->getWebex();
                return;
            }
        }

        $this->getWebex();

        return;
    }
}
