<?php
/**
 * Zoom.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

//use QR_Code\QR_Code;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class Zoom extends Agent
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
            "ZOOM is a tool for hosting audio-visual conferences.";
        $this->thing_report["help"] = 'Click on the image for a PDF.';

        $this->node_list = ["zoom" => ["zoom", "uuid"]];

        $this->current_time = $this->thing->json->time();

        $this->initZoom();
    }

    public function set()
    {
        $this->setZoom();
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
        $sms = "ZOOM | ";

        $sms_text =
            $this->password .
            " " .
            $this->access_code .
            " " .
            $this->url .
            " ";
        if ($this->host_url !== true) {
	    $sms_text =
                $this->host_url;
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
        $this->thing_report['sms'] = $sms;
    }

    /**
     *
     */

    /**
     *
     */

    public function setZoom()
    {
    }

    /**
     *
     * @return unknown
     */
    public function getZoom()
    {
    }

    /**
     *
     */
    public function initZoom()
    {
    }

    public function readZoom($text = null)
    {
//        $file = $this->resource_path . 'call/call-zoom-test' . '.txt';

//        if (file_exists($file)) {
//            $text = file_get_contents($file);
//        }
        $this->access_code = $this->accesscodeZoom($text);
        $this->password = $this->passwordZoom($text);

        $this->url = $this->urlZoom($text);
        $this->urls = $this->urlsZoom($text);
        $this->host_url = $this->hosturlZoom($text);

        $this->telephone_numbers = $this->telephonenumberZoom($text);
    }

    public function run()
    {
    }

    /**
     *
     */
    public function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/zoom.pdf';
        $this->node_list = ["zoom" => ["zoom"]];
        $web = "";

        if (isset($this->html_image)) {
            $web .= '<a href="' . $link . '">';
            $web .= $this->html_image;
            $web .= "</a>";
        }
        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    public function get()
    {
        $this->thing->json->setField("variables");
        $time_string = $this->thing->json->readVariable([
            "zoom",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $this->thing->json->setField("variables");
            $time_string = $this->thing->json->time();
            $this->thing->json->writeVariable(
                ["zoom", "refreshed_at"],
                $time_string
            );
        }
    }

    public function urlZoom($text = null)
    {
        $url_agent = new Url($this->thing, "url");
        $urls = $url_agent->extractUrls($text);

        foreach ($urls as $i => $url) {
            if (stripos($url, '.zoom.us/') !== false) {
                // Match first instance.
                return $url;
            }
        }

        return false;
    }

    public function urlsZoom($text = null)
    {
        $url_agent = new Url($this->thing, "url");
        $urls = $url_agent->extractUrls($text);

        foreach ($urls as $i => $url) {
            if (stripos($url, '.zoom.us/') !== false) {
                // Match first instance.
                continue;
            }
            unset($urls[$i]);
        }
        if (count($urls) != 0) {
            return $urls;
        }

        return false;
    }

    public function hosturlZoom($text = null)
    {
        // Undefined at this time.
        return true;

        $url_agent = new Url($this->thing, "url");
        $urls = $url_agent->extractUrls($text);

        foreach ($urls as $i => $url) {
            if (stripos($url, 'j.php?MTID') !== false) {
                continue;
            }
            unset($urls[$i]);
        }

        // Match last instance.
        return end($urls);
    }

    public function telephonenumberZoom($text = null)
    {
        // TODO: devstack Telephonenumber

        $telephonenumber_agent = new Telephonenumber(
            $this->thing,
            "telephonenumber"
        );

        $telephone_numbers = $telephonenumber_agent->extractTelephonenumbers(
            $text
        );
        return $telephone_numbers;
    }

    public function accesscodeZoom($text = null)
    {
        // 124 456 5678

        if ($text == null) {
            return true;
        }

        $pattern = '/\b\d{3} \d{3} \d{4}/i';

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

    public function passwordZoom($text)
    {
        // 11 character string. Alphunumeric.
        // 124 456 5678

        if ($text == null) {
            return true;
        }

        $pattern = '/\b[a-zA-Z0-9]{11}\b/i';

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

        // See TODO above.
        // For now do this.
        foreach ($passwords as $i => $password) {
            if (
                preg_match('/[A-Za-z]/', $password) &&
                preg_match('/[0-9]/', $password)
            ) {
            } else {
                unset($passwords[$i]);
            }
        }

        if (count($passwords) == 1) {
            return $passwords[0];
        }

        return false;
    }

    /**
     *
     * @return unknown
     */

    public function isZoom($text)
    {
        // Contains word zoom?
        return false;
    }

    public function readSubject()
    {
        $input = strtolower($this->subject);

        $this->readZoom($input);

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'zoom') {
                $this->getZoom();
                return;
            }
        }

        $this->getZoom();

        return;
    }
}
