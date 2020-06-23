<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

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

    //    function set()
    //    {
    //    }

    public function set()
    {
        $this->thing->json->setField("variables");
        $this->thing->json->writeVariable(
            ["url", "refreshed_at"],
            $this->thing->json->time()
        );
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
        $this->thing_report['txt'] = "No text retrieved.";
    }

    function makeSMS()
    {
        $sms_message = "URL";

        if (isset($this->response) and $this->response != "") {
            $sms_message .= " | ";
            $sms_message .= $this->response;
        }

        if ($this->verbosity >= 2) {
        }

        $sms_message .= " | " . $this->url;

        $this->thing_report['sms'] = $sms_message;
        $this->sms_message = $sms_message;
    }

    public function getUrl()
    {
        $this->getUrls();

        $this->url = "X";
        if (isset($this->urls[0])) {
            $this->url = $this->urls[0];
        }
    }

    public function getUrls()
    {
        $urls = [];

        $findagent_thing = new Findagent($this->thing, 'url');

        if (!is_array($findagent_thing->thing_report['things'])) {
            return;
        }

        $count = count($findagent_thing->thing_report['things']);

        if ($count > 0) {
            foreach (
                array_reverse($findagent_thing->thing_report['things'])
                as $thing_object
            ) {
                $uuid = $thing_object['uuid'];
                $variables_json = $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                $age =
                    strtotime($this->thing->time()) -
                    strtotime($thing_object['created_at']);

                if (isset($variables['url'])) {
                    $task_urls = $this->extractUrls($thing_object['task']);
                    if (count($task_urls) == 0) {
                        continue;
                    }

                    $urls[] = implode(" ", $task_urls);
                }
            }
        }

        $urls = array_reverse($urls);

        $this->urls = $urls;
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

        // Require http...
        //$pattern = '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#';
        //        $pattern =
        //            '#^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$#';
        //$pattern == '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';

        $pattern = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';

        preg_match_all($pattern, $text, $match);
        if (!isset($urls)) {
            $urls = [];
        }
        $urls = array_merge($urls, $match[0]);
        $urls = array_unique($urls);

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

        // No URLS found.
        if ($urls === true) {
            return false;
        }

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
        if ($input == '') {
            $this->getUrl();
            return;
        }

        $this->url = $input;

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'url') {
                $this->getUrl();
                return;
            }

            if ($input == 'read') {
                return;
            }
        }

        return;
    }
}
