<?php
/**
 * Webex.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Webex extends Agent
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
            "WEBEX is a tool for hosting audio-visual conferences.";
        $this->thing_report["help"] = "Click on the image for a PDF.";

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
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        //        return $this->thing_report;
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
        $sms = "WEBEX | ";

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

    public function setWebex()
    {
    }

    /**
     *
     * @return unknown
     */
    public function getWebex()
    {
    }

    /**
     *
     */
    public function initWebex()
    {
    }

    public function readWebex($text = null)
    {
        //       $file = $this->resource_path . 'call/call-test' . '.txt';

        //       if (file_exists($file)) {
        //           $text = file_get_contents($file);
        //       }
        $this->access_code = $this->accesscodeWebex($text);
        $this->password = $this->passwordWebex($text);

        $this->url = $this->urlWebex($text);
        $this->host_url = $this->hosturlWebex($text);

        $this->telephone_numbers = $this->telephonenumberWebex($text);
    }

    public function run()
    {
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/webex.pdf";
        $this->node_list = ["webex" => ["webex"]];
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

    public function urlWebex($text = null)
    {
        //        $url_agent = new Url($this->thing, "url");
        //        $urls = $url_agent->extractUrls($text);

        $urls = $this->extractUrls($text);

        foreach ($urls as $i => $url) {
            if (stripos($url, ".php?MTID") !== false) {
                // Match first instance.
                return $url;
            }
        }

        foreach ($urls as $i => $url) {
            if (stripos($url, ".webex.com") !== false) {
                return $url;
            }
        }

        return false;
    }

    public function hosturlWebex($text = null)
    {
        //        $url_agent = new Url($this->thing, "url");
        //        $urls = $url_agent->extractUrls($text);

        $urls = $this->extractUrls($text);

        foreach ($urls as $i => $url) {
            if (stripos($url, ".php?MTID") !== false) {
                continue;
            }

            if (stripos($url, ".webex.com") !== false) {
                continue;
            }

            unset($urls[$i]);
        }

        // Match last instance.
        return end($urls);
    }

    public function telephonenumberWebex($text = null)
    {
        // TODO: devstack Telephonenumber

        //        $telephonenumber_agent = new Telephonenumber(
        //            $this->thing,
        //            "telephonenumber"
        //        );

        //        $telephone_numbers = $telephonenumber_agent->extractTelephonenumbers(
        //            $text
        //        );

        $telephone_numbers = $this->extractTelephonenumbers($text);

        return $telephone_numbers;
    }

    public function accesscodeWebex($text = null)
    {
        // 124 456 5678

        if ($text == null) {
            return true;
        }

        $pattern = "/\b\d{3} \d{3} \d{4}/i";

        preg_match_all($pattern, $text, $match);
        if (!isset($access_codes)) {
            $access_codes = [];
        }

        $access_codes = array_merge($access_codes, $match[0]);
        $access_codes = array_unique($access_codes);

        if (count($access_codes) == 1) {
            return $access_codes[0];
        }

        return false;
    }

    public function passwordWebex($text)
    {
        // 11 character string. Alphunumeric.
        // 124 456 5678

        if ($text == null) {
            return true;
        }

        $pattern = "/\b[a-zA-Z0-9]{11}\b/i";

        //TODO: Develop regex pattern to match at least one number and one alpha.
        //$pattern = '/\b^(?=.*\d)(?=.*[a-zA-Z]).{11}$\b/';
        //$pattern = '/^.*(?=.{11})(?=.*\d)(?=.*[a-zA-Z]).*$/';
        //$pattern = '/\b^(?=.*\d)(?=.*[a-zA-Z])[a-zA-Z0-9]{11}$\b/';
        //$pattern = '^((?=.*\d)(?=.*[A-Z])(?=.*\W).{11,11})$';

        //$pattern = '/\b^(?=.*\d)(?=.*[a-zA-Z])[a-zA-Z0-9]{11}$\b/i';

        preg_match_all($pattern, $text, $match);
        if (!isset($passwords)) {
            $passwords = [];
        }
        //var_dump($match[0]);
        $passwords = array_merge($passwords, $match[0]);
        $passwords = array_unique($passwords);

        // See TODO above.
        // For now do this.
        foreach ($passwords as $i => $password) {
            if (
                preg_match("/[A-Za-z]/", $password) &&
                preg_match("/[0-9]/", $password)
            ) {
            } else {
                unset($passwords[$i]);
            }
        }

        if (count($passwords) == 1) {
            return $passwords[0];
        }

        // That strategy didn't work.
        // Try seeing if there is a paragraph with the word 'password'.

        // And then see if there is a strange token.

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

    public function isWebex($text)
    {
        if (stripos($text, "webex") !== false) {
            return true;
        }
        // Contains word webex?
        return false;
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $this->readWebex($input);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "webex") {
                $this->getWebex();
                return;
            }
        }

        $this->getWebex();

        return;
    }
}
