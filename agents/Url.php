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
        $this->thing_report['help'] =
            'Text URL < A web link> to add a link to this list.';
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

    function makeWeb()
    {
        $this->getUrls();

        $web = "<b>URL Agent</b><br><p>";
        $web .= "<p>";

        if (count($this->urls) > 0) {
            $web .= "<b>COLLECTED URLS</b><br><p>";
            $web .= "<ul>";
            //$urls = array_unique($this->urls);

            $tempArr = array_unique(array_column($this->urls, 'url'));
            $urls = array_intersect_key($this->urls, $tempArr);

            foreach ($urls as $i => $url_array) {
                $url = $url_array['url'];

                $url_link =
                    $this->web_prefix .
                    "thing/" .
                    $url_array['uuid'] .
                    "/forget";
                $html_link = "[" . '<a href="' . $url_link . '">forget</a>]';

                if (stripos($url, "://") !== false) {
                    $link = '<a href="' . $url . '">' . $url . '</a>';
                    $web .= "<li>" . $link . " " . $html_link . "<br>";

                    continue;
                } elseif (stripos($url, ":/") !== false) {
                    $link = '<a href="' . $url . '">' . $url . '</a>';
                    $try_link = '[Try ' . str_replace(":/", "://", $link) . ']';
                    $web .= "<li>" . $link . " " . $try_link . "<br>";

                    continue;
                } else {
                    $link =
                        'Try <a href="https://' .
                        $url .
                        '">' .
                        "https://" .
                        $url .
                        '</a>';

                    $web .=
                        "<li>" .
                        $url .
                        ' [' .
                        $link .
                        ']' .
                        $html_link .
                        "<br>";
                    continue;
                }

                $web .= "<li>" . $url . $html_link . "<br>";
            }

            $web .= "</ul>";

            $web .= "<p>";
        }
        $web .= "<b>HELP</b><br><p>";
        $web .= $this->thing_report['help'];

        $this->thing_report['web'] = $web;
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

        if ($this->url == "X") {
            $urls_text = "No urls found. ";
        } else {
            $urls_text = $this->url;
        }
        $sms_message .= " | " . $urls_text;

        $this->thing_report['sms'] = $sms_message;
        $this->sms_message = $sms_message;
    }

    public function getUrl()
    {
        $this->getUrls();

        $this->url = "X";
        if (isset($this->urls[0])) {
            $this->url = $this->urls[0]['url'];
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
if ($task_urls === true) {continue;}
                    if (count($task_urls) == 0) {
                        continue;
                    }
                    $task_urls = $this->filterUrls($task_urls);

                    $url = [
                        "url" => implode(" ", $task_urls),
                        "uuid" => $thing_object['uuid'],
                    ];

                    $urls[] = $url;
                }
            }
        }

        $urls = array_reverse($urls);

        $this->urls = $urls;
    }

    public function filterUrls($urls = null)
    {
        if (!is_array($urls)) {
            return true;
        }
        foreach ($urls as $i => $url) {
            $parts = explode(" ", $url);
            if (count($parts) == 1) {
                if (stripos($url, '.') !== false) {
                    $urls[$i] = explode(' ', $url)[0];
                } else {
                    unset($urls[$i]);
                }
            }

            if (stripos($url, "://") !== false) {
                continue;
            }

            if (stripos($url, ":/") !== false) {
                unset($urls[$i]);
            }
        }
        return $urls;
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

        $text = str_replace('url is', '', $text);
        $text = str_replace('url', '', $text);
        $text = trim($text);
        // https://stackoverflow.com/questions/36564293/extract-urls-from-a-string-using-php

        // Require http...
        //$pattern = '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#';
        //        $pattern =
        //            '#^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$#';
        //$pattern == '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';

        //$pattern = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';
        $pattern =
            '#\b(https://)?([^\s()<>]+)?(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';

        // https://stackoverflow.com/questions/6427530/regular-expression-pattern-to-match-url-with-or-without-http-www

        $regex = '((https?|ftp)://)?';
        $regex .= '([a-z0-9+!*(),;?&=$_.-]+(:[a-z0-9+!*(),;?&=$_.-]+)?@)?';
        $regex .=
            "([a-z0-9\-\.]*)\.(([a-z]{2,4})|([0-9]{1,3}\.([0-9]{1,3})\.([0-9]{1,3})))";
        $regex .= '(:[0-9]{2,5})?';
        $regex .= '(/([a-z0-9+$_%-]\.?)+)*/?';
        $regex .= '(\?[a-z+&\$_.-][a-z0-9;:@&%=+/$_.-]*)?';
        $regex .= '(#[a-z_.-][a-z0-9+$%_.-]*)?';

        $pattern = "#" . $regex . "#";

        $pattern =
            '/^(http|https)?:\/\/|(www\.)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/';

        $pattern =
            '/\b(https?|ftp|file:\/\/)?[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i';

        preg_match_all($pattern, $text, $match);
        if (!isset($urls)) {
            $urls = [];
        }
        $urls = array_merge($urls, $match[0]);
        $urls = array_unique($urls);

        // Deal with spaces

        $urls = $this->filterUrls($urls);

        /*
        foreach ($urls as $i => $url) {
            $parts = explode(" ", $url);
            //var_dump($parts);
            if (count($parts) == 1) {
                if (stripos($url, '.') !== false) {
                    $urls[$i] = explode(' ', $url)[0];
                } else {
                    unset($urls[$i]);
                }
            }
        }
*/
        return $urls;
    }

    public function hasUrl($text = null)
    {
        $urls = $this->extractUrls($text);

        // No URLS found.
        if ($urls === true) {
            return false;
        }

        if (count($urls) >= 1) {
            return true;
        }

        return false;
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

        // Get urls from string
        $this->urls = $this->extractUrls($input);
        $this->url = "X";
        if (isset($this->urls[0])) {
            $this->url = $this->urls[0];
        }

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
