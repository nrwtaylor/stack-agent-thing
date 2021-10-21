<?php
/**
 * Sentence.php
 *
 * @package default
 */

// 4 letters.  Is handy to have.
namespace Nrwtaylor\StackAgentThing;

// Transparency
ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Sentence extends Agent
{
    /**
     *
     */
    function init()
    {
        $this->node_list = ["sentence" => ["sentence"]];
        $this->state = "on";
    }

    /**
     *
     */
    function get()
    {
    }

    /**
     *
     */
    function set()
    {
        $this->thing->Write(
            ["sentence", "refreshed_at"],
            $this->thing->time()
        );

    }

    public function getSentence($text = null)
    {
        if ($text == null) {
            return true;
        }

        $alphanumeric_agent = new Alphanumeric($this->thing, "alphanumeric");
        $slug = $alphanumeric_agent->filterAlphanumeric($text);

        $despaced_slug = preg_replace("/\s+/", " ", $slug);
        $slug = str_replace(" ", "-", $despaced_slug);
        $slug = strtolower($slug);
        $slug = trim($slug, "-");
        $this->slug = $slug;
    }

    /**
     *
     */
    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->makeChoices();

        $message_thing = new Message($this->thing, $this->thing_report);
        $this->thing_report["info"] = $message_thing->thing_report["info"];

        $this->thing_report["thing"] = $this->thing->thing;
        $this->thing_report["help"] = "This gets sentences from the datagram.";
    }

    public function extractSentences($text = null)
    {
        if ($text == null) {
            $text = $this->input;
        }
        $sentences = explode(". ", $text);

        $this->sentences = $sentences;
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
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

        if (!isset($this->sentence) or $this->sentence == false) {
            $this->getSentence($input);
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "sentence") {
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
    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/uuid";

        $this->node_list = ["number" => ["number", "thing"]];

        $web = '<a href="' . $link . '">';
        $web .=
            '<img src= "' .
            $this->web_prefix .
            "thing/" .
            $this->uuid .
            '/uuid.png">';
        $web .= "</a>";

        $web .= "<br>";
        $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br>";
        $web .= $this->subject . "<br>";

        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    /**
     *
     */
    function makeSMS()
    {
        $sms = "SENTENCE";

        $sentence = " | No sentence found.";
        if (isset($this->sentence)) {
            $sentence = " | " . $this->sentence;
        }

        $sms .= $sentence;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */
    function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
        $this->choices = $choices;
    }
}
