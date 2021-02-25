<?php
/**
 * Webinar.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Webinar extends Agent
{
    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->test = "Development code";

        $this->thing_report["info"] =
            "WEBINAR is a tool for hosting audio-visual conferences.";
        $this->thing_report["help"] = "No user response.";

        $this->node_list = ["webinar" => ["webinar", "uuid"]];

        $this->current_time = $this->thing->json->time();

        $this->initWebinar();
    }

    public function set()
    {
        $this->setWebinar();
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
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    /**
     *
     */
    public function makeChoices()
    {
        $this->choices = false;
        $this->thing_report["choices"] = $this->choices;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms = "WEBINAR | ";

        $sms_text =
            $this->password .
            " " .
            $this->access_code .
            " " .
            $this->url .
            " " .
            $this->host_url;

        $telephone_numbers_text = implode(" ", $this->telephone_numbers);

        $sms .= $sms_text . " ";
        $sms .= $telephone_numbers_text . " ";
        $sms .= $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */

    /**
     *
     */

    public function setWebinar()
    {
    }

    /**
     *
     * @return unknown
     */
    public function getWebinar()
    {
    }

    /**
     *
     */
    public function initWebinar()
    {
    }

    public function readWebinar($text = null)
    {
        $this->access_code = $this->accesscodeWebinar($text);
        $this->password = $this->passwordWebinar($text);

        $this->url = $this->urlWebinar($text);
        $this->host_url = $this->hosturlWebinar($text);

        $this->telephone_numbers = $this->telephonenumberWebinar($text);
    }

    public function run()
    {
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/webinar.pdf";
        $this->node_list = ["webinar" => ["webinar"]];
        $web = "";
        if (isset($this->html_image)) {
            $web .= '<a href="' . $link . '">';
            $web .= $this->html_image;
            $web .= "</a>";
        }
        $web .= "<br>";

        $this->thing_report["web"] = $web;
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "webinar",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["webinar", "refreshed_at"],
                $time_string
            );
        }
    }

    public function urlWebinar($text = null)
    {
        $selected_paragraphs = [];
        $paragraph_agent = new Paragraph($this->thing, $text);
        $paragraphs = $paragraph_agent->paragraphs;
        foreach ($paragraphs as $i => $paragraph) {
            if (stripos($paragraph, "join the webinar") !== false) {
                $selected_paragraphs[] = $paragraph;
                continue;
            }

            if (stripos($paragraph, "join webinar") !== false) {
                $selected_paragraphs[] = $paragraph;
                continue;
            }

            if (stripos($paragraph, "webinar link") !== false) {
                $selected_paragraphs[] = $paragraph;
                continue;
            }


        }
$urls = [];
foreach($selected_paragraphs as $s=>$paragraph) {

$urls = array_merge($this->extractUrls($paragraph), $urls);
$urls = array_unique($urls);
}
if (count($urls) === 1) {$url = $urls[0]; return $url;}

        return false;
    }

    public function hosturlWebinar($text = null)
    {
        return false;
    }

    public function telephonenumberWebinar($text = null)
    {
        $telephone_numbers = $this->extractTelephonenumbers($text);
        return $telephone_numbers;
    }

    public function accesscodeWebinar($text = null)
    {
        return false;
    }

    public function passwordWebinar($text)
    {
        $selected_paragraphs = [];
        $paragraph_agent = new Paragraph($this->thing, $text);
        $paragraphs = $paragraph_agent->paragraphs;
        foreach ($paragraphs as $i => $paragraph) {
            if (stripos($paragraph, "password") !== false) {
                $selected_paragraphs[] = $paragraph;
                continue;
            }

            if (stripos($paragraph, "passwd") !== false) {
                $selected_paragraphs[] = $paragraph;
                continue;
            }

            if (stripos($paragraph, "pword") !== false) {
                $selected_paragraphs[] = $paragraph;
                continue;
            }

            if (stripos($paragraph, "pwd") !== false) {
                $selected_paragraphs[] = $paragraph;
                continue;
            }
        }
        $possible_passwords = [];
        //$this->thing->punctuation_handler = new Punctuation($this->thing, "punctuation");
        $this->thing->word_handler = new Word($this->thing, "word");
        foreach ($selected_paragraphs as $i => $paragraph) {
            $strip_nbsp = str_replace("&nbsp", " ", $paragraph);

            $tokens = explode(" ", $strip_nbsp);
            foreach ($tokens as $i => $token) {
                $stripped_token = trim(
                    $this->thing->word_handler->stripPunctuation($token)
                );
                $t = strtolower($stripped_token);
                if ($t === "") {
                    continue;
                }
                $t = $this->isWord($t);
                if ($t === false) {
                    $possible_passwords[] = $stripped_token;
                }
            }
            //$this->thing->word_handler->extractWords($paragraph);
        }
        $possible_passwords = array_unique($possible_passwords);
        if (count($possible_passwords) === 1) {
            return $possible_passwords[0];
        }
        return false;
    }

    /**
     *
     * @return unknown
     */

    public function isWebinar($text)
    {
        if (stripos($text, "webinar") !== false) {
            return true;
        }
        // Contains word webinar?
        return false;
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);
        $this->readWebinar($input);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "webinar") {
                $this->getWebinar();
                return;
            }
        }

        $this->getWebinar();

        return;
    }
}
