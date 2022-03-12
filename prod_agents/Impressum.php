<?php
/**
 * Impressum.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Impressum extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     */
    function init()
    {
        $this->mail_regulatory =
            $this->thing->container["stack"]["mail_regulatory"];
        $this->node_list = ["start" => ["start", "opt-in"]];
        $this->impressum();
    }

    /**
     *
     */
    public function impressum()
    {
        $this->makeChannel("impressum");
    }

    /**
     *
     */
    public function makeWeb()
    {
        $file = $GLOBALS["stack_path"] . "resources/impressum/impressum.html";
        $contents = "No Impressum found.";
        if (file_exists($file)) {
            $contents = file_get_contents($file);
        }
        $this->thing_report["web"] = $contents;
    }

    /**
     *
     * @return unknown
     */
    public function makeSMS()
    {
        $text = "No SMS response available.";
        if (isset($this->thing_report["sms"])) {
            $text = $this->thing_report["sms"];
        }
        $shortcode_agent = new Shortcode($this->thing, "shortcode");
        $text = $shortcode_agent->filterShortcode($text);

        $sms = "IMPRESSUM | " . $text;

        $this->thing_report["sms"] = $sms;
        $this->sms_message = $sms;
        return $this->sms_message;
    }

    /**
     *
     */
    public function makeEmail()
    {
        if (!isset($this->thing_report["email"])) {
            $thing = new Thing(null);
            $thing->Create(
                "terms-of-use",
                "human",
                "s/ impressum email not found"
            );
            return true;
        }

        $text = $this->thing_report["email"];
        $shortcode_agent = new Shortcode($this->thing, "shortcode");
        $text = $shortcode_agent->filterShortcode($text);

        $this->thing_report["email"] = $text;
    }

    /**
     *
     * @return unknown
     */
    public function respondResponse()
    {
        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        return $this->thing_report;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        $this->thing_report["request"] = "What is your Impressum?";

        return "Message not understood";
    }
}
