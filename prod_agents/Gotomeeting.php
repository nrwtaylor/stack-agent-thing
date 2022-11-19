<?php
/**
 * Gotomeeting.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

class Gotomeeting extends Agent
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
            "GOTOMEETING is a tool for hosting audio-visual conferences.";
        $this->thing_report["help"] = "Click on the image for a PDF.";

        $this->node_list = ["gotomeeting" => ["gotomeeting", "uuid"]];

        $this->current_time = $this->thing->time();

        $this->initGotomeeting();
    }

    public function set()
    {
        $this->setGotomeeting();
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
        $sms = "GOTOMEETING | ";

        $sms_text =
            $this->password . " " . $this->access_code . " " . $this->url . " ";
        if ($this->host_url !== true) {
            $sms_text = $this->host_url;
        }
        $telephone_numbers_text = implode(" / ", $this->telephone_numbers);

        if ($this->urls !== false) {
            $urls_text = implode(" ", $this->urls);
            $sms .= $urls_text . " ";
        }
        $sms .= $sms_text . " ";
        $sms .= $telephone_numbers_text . " ";

        $sms = trim($sms) . " ";

        $response_text = "No response.";
        if ($this->response != "") {
            $response_text = $this->response;
        }

        $sms .= $response_text;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */

    /**
     *
     */

    public function setGotomeeting()
    {
    }

    /**
     *
     * @return unknown
     */
    public function getGotomeeting()
    {
    }

    /**
     *
     */
    public function initGotomeeting()
    {
    }

    public function readGotomeeting($text = null)
    {
        $this->access_code = $this->accesscodeGotomeeting($text);
        $this->password = $this->passwordGotomeeting($text);
        $this->url = $this->urlGotomeeting($text);
        $this->urls = $this->urlsGotomeeting($text);
        $this->host_url = $this->hosturlGotomeeting($text);

        $this->telephone_numbers = $this->telephonenumberGotomeeting($text);
    }

    public function run()
    {
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/gotomeeting.pdf";
        $this->node_list = ["gotomeeting" => ["gotomeeting"]];
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
        $time_string = $this->thing->Read([
            "gotomeeting",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["gotomeeting", "refreshed_at"],
                $time_string
            );
        }
    }

    public function urlGotomeeting($text = null)
    {
        $urls = $this->extractUrls($text);
        foreach ($urls as $i => $url) {
            if (stripos($url, ".gotomeeting.com/") !== false) {
                // Match first instance.
                return $url;
            }

            if (stripos($url, ".gotowebinar.com/") !== false) {
                // Match first instance.
                return $url;
            }


/*
            if (stripos($url, "/zoom.us/") !== false) {
                // Match first instance.
                return $url;
            }
*/
        }

        return false;
    }

    public function urlsGotomeeting($text = null)
    {
        //        $url_agent = new Url($this->thing, "url");
        //        $urls = $url_agent->extractUrls($text);

        $urls = $this->extractUrls($text);

        foreach ($urls as $i => $url) {
            if (stripos($url, ".gotomeeting.com/") !== false) {
                // Match first instance.
                continue;
            }

            if (stripos($url, ".gotowebinar.com/") !== false) {
                // Match first instance.
                continue;
            }

/*
            if (stripos($url, "/zoom.us/") !== false) {
                // Match first instance.
                continue;
            }
*/
            unset($urls[$i]);
        }
        if (count($urls) != 0) {
            return $urls;
        }

        return false;
    }

    public function hosturlGotomeeting($text = null)
    {
        // Undefined at this time.
        return true;

        //        $url_agent = new Url($this->thing, "url");
        //        $urls = $url_agent->extractUrls($text);

        $urls = $this->extractUrls($text);

        foreach ($urls as $i => $url) {
            if (stripos($url, "j.php?MTID") !== false) {
                continue;
            }
            unset($urls[$i]);
        }

        // Match last instance.
        return end($urls);
    }

    public function telephonenumberGotomeeting($text = null)
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

    // This is the room number.
    public function accesscodeGotomeeting($text = null)
    {
        // 124 456 5678

        if ($text == null) {
            return true;
        }
        //         $pattern = "/\b\d{6}\b/i";

        $pattern = "/\b\d{3}[\-]\d{3}[\-]\d{3}\b/i";

        preg_match_all($pattern, $text, $match);
        if (!isset($access_codes)) {
            $access_codes = [];
        }

        $access_codes = array_merge($access_codes, $match[0]);

        $access_codes = array_unique($access_codes);

        // No access codes found.
        if (count($access_codes) === 0) {
            return false;
        }

        if (count($access_codes) == 1) {
            return $access_codes[0];
        }


        // Extract urls and see which codes are also in Url.
        $urls = $this->extractUrls($text);

        $validated_access_codes = [];
        foreach ($access_codes as $i => $access_code) {
            $filtered_access_code = str_replace(" ", "", $access_code);

            foreach ($urls as $j => $url) {
                if (strpos($url, $filtered_access_code) !== false) {
                    $validated_access_codes[] = $access_code;
                }
            }
        }

        if (count($validated_access_codes) == 1) {
            return $validated_access_codes[0];
        }

        // Okay this is getting harder.
        // Now read and see if any of the access codes
        // is preceeded by the two alpha tokens meeting id.

        // AND with the words meeting id preceeding the access code.
        $ngrams = $this->extractNgrams($text, 5);

        $validated_access_codes = [];
        foreach ($access_codes as $i => $access_code) {
            foreach ($ngrams as $j => $ngram) {
                if (
                    strpos($ngram, $access_code) !== false and
                    stripos($ngram, "meeting id") !== false
                ) {
                    if (
                        strpos($text, $access_code) >
                        stripos($text, "meeting id")
                    ) {
                        $validated_access_codes[] = $access_code;
                    }
                }
            }
        }

        if (count($validated_access_codes) == 1) {
            return $validated_access_codes[0];
        }

        return false;
    }

    public function passwordGotomeeting($text)
    {
        // TODO - Check if gotomeeting has passwords.

        return false;
        // 11 character string. Alphunumeric.
        // 124 456 5678

        if ($text == null) {
            return true;
        }

        //        $pattern = '/\b[a-zA-Z0-9]{11}\b/i';
        $pattern = "/\b\d{6}\b/i";

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
        $passwords = array_merge($passwords, $match[0]);
        $passwords = array_unique($passwords);

        if (count($passwords) == 1) {
            return $passwords[0];
        }

        return false;
    }

    /**
     *
     * @return unknown
     */

    public function isGotomeeting($text)
    {
            if (stripos($text, "gotomeeting") !== false) {
                // Match first instance.
                return true;
            }

            if (stripos($text, "gotowebinar") !== false) {
                // Match first instance.
                return true;
            }



        // Contains word gotomeeting?
        return false;
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $this->readGotomeeting($input);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "gotomeeting") {
                $this->getGotomeeting();
                return;
            }
        }

        $this->getGotomeeting();

        return;
    }
}
