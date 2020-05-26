<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//require '/var/www/html/stackr.ca/public/agenthandler.php'; // until the callAgent call can be
// factored to
// call agent 'Agent'

ini_set("allow_url_fopen", 1);

class Url extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
    }

    function set()
    {
    }

    function get()
    {
    }

    public function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    public function makeTxt()
    {
        //        $this->thing_report['txt'] = implode("/n", $this->yard_sales);
        $this->thing_report['txt'] = "No text retrieved.";
    }

    function makeSMS()
    {
        $sms_message = "URL | ";
        $sms_message .= $this->response;

        if ($this->verbosity >= 2) {
        }

        $sms_message .= " | link " . $this->link;

        $sms_message .= " | TEXT ?";

        $this->thing_report['sms'] = $sms_message;
        $this->sms_message = $sms_message;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report['info'] = $message_thing->thing_report['info'];
        $this->thing_report['help'] = 'This reads a web resource.';
    }

    public function extractUrls($text = null)
    {
        if ($text == null) {
            return true;
        }
        // https://stackoverflow.com/questions/36564293/extract-urls-from-a-string-using-php

        //$string = "The text you want to filter goes here. http://google.com, https://www.youtube.com/watch?v=K_m7NEDMrV0,https://instagram.com/hellow/";
        // Require http...
        //$pattern = '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#';
        $pattern =
            '#^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$#';
        //$pattern == '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';

        preg_match_all($pattern, $text, $match);
        if (!isset($urls)) {
            $urls = [];
        }
        $urls = array_merge($urls, $match[0]);
        $urls = array_unique($urls);
        //$this->slugs = $match[0];

        // Deal with spaces

        foreach ($urls as $i => $url) {
            if (strpos($url, ' ') !== false) {
                $urls[$i] = explode(' ', $url)[0];
            }
        }
        return $urls;
    }

    public function extractUrl($text = null)
    {
        $urls = $this->extractUrls($text);

// No URLS founds.
if ($urls === true) {return false;}

        if (count($urls) == 1) {
            return $urls[0];
        }

        return false;
    }

    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;

        //        $keywords = $this->keywords;

        $input = $this->assert($this->input);

        $this->url = $input;
        $this->link = $input;

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'read') {
                return;
            }
        }

        return "Message not understood";

        return false;
    }
}
