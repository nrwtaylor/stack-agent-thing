<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Url extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->thing_report["help"] =
            "Text URL < A web link> to add a link to this list.";
    }

    function run()
    {
    }

    public function set()
    {
        $this->thing->Write(["url", "refreshed_at"], $this->thing->time());
    }

    function get()
    {
    }

    public function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
    }

    public function makeTxt()
    {
        $this->thing_report["txt"] = "No text retrieved.";
    }

    function cleanUrl($text = null)
    {
        if ($text == null) {
            return true;
        }

        // Clean search engine provided link.
        $tokens = explode("&url=", $text);
        if (!isset($tokens[1])) {
            return true;
        }

        $tokens = explode("&usg=", $tokens[1]);

        $url = $tokens[0];

        return $url;
    }

    function makeWeb()
    {
        $this->getUrls();

        $web = "<b>URL Agent</b><br><p>";
        $web .= "<p>";

        if (
            isset($this->urls) and
            is_array($this->urls) and
            count($this->urls) > 0
        ) {
            $web .= "<b>COLLECTED URLS</b><br><p>";
            $web .= "<ul>";
            //$urls = array_unique($this->urls);

            $tempArr = array_unique(array_column($this->urls, "url"));
            $urls = array_intersect_key($this->urls, $tempArr);

            foreach ($urls as $i => $url_array) {
                $url = $url_array["url"];

                $url_link =
                    $this->web_prefix .
                    "thing/" .
                    $url_array["uuid"] .
                    "/forget";
                $html_link = "[" . '<a href="' . $url_link . '">forget</a>]';

                if (stripos($url, "://") !== false) {
                    $link = '<a href="' . $url . '">' . $url . "</a>";
                    $web .= "<li>" . $link . " " . $html_link . "<br>";

                    continue;
                } elseif (stripos($url, ":/") !== false) {
                    $link = '<a href="' . $url . '">' . $url . "</a>";
                    $try_link = "[Try " . str_replace(":/", "://", $link) . "]";
                    $web .= "<li>" . $link . " " . $try_link . "<br>";

                    continue;
                } else {
                    $link =
                        'Try <a href="https://' .
                        $url .
                        '">' .
                        "https://" .
                        $url .
                        "</a>";

                    $web .=
                        "<li>" .
                        $url .
                        " [" .
                        $link .
                        "]" .
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
        $web .= $this->thing_report["help"];

        $this->thing_report["web"] = $web;
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

        $this->thing_report["sms"] = $sms_message;
        $this->sms_message = $sms_message;
    }

    public function getUrl()
    {
        $this->getUrls();

        $this->url = "X";
        if (isset($this->urls[0])) {
            $this->url = $this->urls[0]["url"];
        }
    }

    public function getUrls()
    {
        $urls = [];

        $findagent_thing = new Findagent($this->thing, "url");

        if (!is_array($findagent_thing->thing_report["things"])) {
            return;
        }

        $count = count($findagent_thing->thing_report["things"]);

        if ($count > 0) {
            foreach (
                array_reverse($findagent_thing->thing_report["things"])
                as $thing_object
            ) {
                $uuid = $thing_object["uuid"];
                $variables_json = $thing_object["variables"];
                $variables = $this->thing->json->jsontoArray($variables_json);

                $age =
                    strtotime($this->thing->time()) -
                    strtotime($thing_object["created_at"]);

                if (isset($variables["url"])) {
                    $task_urls = $this->extractUrls($thing_object["task"]);
                    if ($task_urls === true) {
                        continue;
                    }
                    if (count($task_urls) == 0) {
                        continue;
                    }
                    $task_urls = $this->filterUrls($task_urls);

                    $url = [
                        "url" => implode(" ", $task_urls),
                        "uuid" => $thing_object["uuid"],
                    ];

                    $urls[] = $url;
                }
            }
        }

        $urls = array_reverse($urls);
        $this->urls = $urls;
    }

    public function restoreUrl($text)
    {
        $urls = $this->extractUrls($text);

        if ($urls === true) {
            return $text;
        }
        foreach ($urls as $i => $url) {
            $link = '<a href="' . $url . '">' . $url . "</a>";
            $text = str_replace($url, $link, $text);
        }
        $restored_text = $text;
        return $restored_text;
    }

    public function bracketUrl($text, $bracket = null)
    {
        //return $text;
        $urls = $this->extractUrls($text);

        if ($urls === true) {
            return $text;
        }
        foreach ($urls as $i => $url) {
            $link = "<" . $url . ">";
            $text = str_replace($url, $link, $text);
        }
        $restored_text = $text;
        return $restored_text;
    }

    public function filterUrls($urls = null)
    {
        if (!is_array($urls)) {
            return true;
        }
        foreach ($urls as $i => $url) {
            $parts = explode(" ", $url);
            if (count($parts) == 1) {
                if (stripos($url, ".") !== false) {
                    $urls[$i] = explode(" ", $url)[0];
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

            // Did this pick up a decimal.
            $tokens = explode(".", $url);
            if (count($tokens) == 2) {
                if (!is_numeric($tokens[0])) {
                    continue;
                }
                if (!is_numeric($tokens[1])) {
                    continue;
                }
                unset($urls[$i]);
            }
        }
        $urls = array_values($urls);
        return $urls;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $message_thing = new Message($this->thing, $this->thing_report);

        $this->thing_report["info"] = $message_thing->thing_report["info"];
        $this->thing_report["help"] = "This reads a web resource.";
    }

    public function patternUrls($text = null)
    {
        if ($text == null) {
            return true;
        }

        /*
        $pattern =
            '/\b(https?|ftp|file:\/\/)?[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i';

        $pattern =
            '/\b(https?|ftp|file:\/\/)?[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i';
*/
        $pattern =
            '/\b(https?|ftp|file:\/\/)?[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i';

        // This is more permissive than RFC3986.
        // It does not pass merp@localhost
        // It does     pass merp@192.168.7.3
        // It does     pass @gmail.com

        // It recognizes strings that have urls in them. Without spaces.

        // https://stackoverflow.com/questions/17105977/can-i-use-an-at-sign-in-the-path-part-of->
        preg_match_all($pattern, $text, $match);
        if (!isset($urls)) {
            $urls = [];
        }
        $urls = array_merge($urls, $match[0]);

        //        $pattern =
        //            '/\b(https?|ftp|file:\/\/)?[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i';

        if (count($urls) == 0) {
            return false;
        }
        return $urls;
    }

    public function extractUrls($text = null)
    {
        if ($text == null) {
            return true;
        }
        /*
        $pattern =
            '/\b(https?|ftp|file:\/\/)?[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i';

        $pattern =
            '/\b(https?|ftp|file:\/\/)?[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i';
*/
        $pattern =
            '/\b(https?|ftp|file:\/\/)?[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i';

        // This is more permissive than RFC3986.
        // It does not pass merp@localhost
        // It does     pass merp@192.168.7.3
        // It does     pass @gmail.com

        // It recognizes strings that have urls in them. Without spaces.
        // Real world urls have these things - ampersands.

        // https://stackoverflow.com/questions/17105977/can-i-use-an-at-sign-in-the-path-part-of-an-url

        preg_match_all($pattern, $text, $match);
        //        if (!isset($urls)) {
        $urls = [];
        //        }

        //var_dump($urls);
        //exit();
        $text_variants = [];
        $text_variants[] = $text;

        //generalDelimsUrl
        $explode_delimiters = ["@"];

        foreach ($explode_delimiters as $i => $delimiter_character) {
            $text_variants = array_merge(
                $text_variants,
                explode($delimiter_character, $text)
            );
        }

        //$urls = [];
        foreach ($text_variants as $i => $text_variant) {
            $pattern_urls = $this->patternUrls($text_variant);
            if ($pattern_urls == null) {
                continue;
            }
            if ($urls == null) {
                $urls = [];
            }
            // test
            if ($pattern_urls === true) {
                continue;
            }
            if ($pattern_urls === false) {
                continue;
            }

            $urls = array_merge($urls, $pattern_urls);

            // Now need to check whether each of these is a Url.
            // Slower. Dev do this in one hit in regex?
        }
        // If there are @ signs.

        // First reduce the job.

        //$urls = array_merge($urls, $match[0]);
        $urls = array_unique($urls);

        // TEST
        foreach ($urls as $i => $url) {
            $p = $this->containsAtsign($url);

            if ($p === true) {
                //            if (($this->patternUrls($url) === false) or ($this->patternUrls($url) === true)) {
                //if (strtotime($url) !== false) {
                unset($urls[$i]);
                continue;
            }

            // 12.000kHz is not a URL
            $tokens = explode(".", $url);
            if (count($tokens) === 4) {
                continue;
            } // expect URL format four tokens

            if (count($tokens) === 2 and is_numeric($tokens[0])) {
                unset($urls[$i]);
                continue;
            }

            if (is_numeric(str_replace(".", "", $url))) {
                unset($urls[$i]);
                continue;
            }
        }

        // Deal with spaces
        $urls = $this->filterUrls($urls);
        // TODO: Test.
        foreach ($urls as $i => $url) {
            if (stripos($url, "&lt;") !== false) {
                $tokens = explode("&lt;", $url);
                $urls[$i] = rtrim($tokens[1], "&gt");
            }
        }
        return $urls;
    }

    public function generalDelimsUrl($url = null)
    {
        // https://www.rfc-editor.org/rfc/rfc3986
        // ":" / "/" / "?" / "#" / "[" / "]" / "@" general delims

        // https://www.rfc-editor.org/rfc/rfc3986#section-2.2
        $text = '":" / "/" / "?" / "#" / "[" / "]" / "@"';
        $parts = explode('"', $text);
        //
        //

        // ugly

        //

        return [":", "/", "?", "#", "[", "]", "@"];
    }

    public function isUrl($text)
    {
        return $this->hasUrl($text);
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

    public function stripUrls($text = null, $replace_text = null)
    {
        if ($text == null) {
            $text = $this->input;
        }
        if ($replace_text == null) {
            $replace_text = "";
        }

        $urls = $this->extractUrls($text);

        foreach ($urls as $i => $url) {
            $text = str_replace($url, $replace_text, $text);
        }
        return $text;
    }

    public function readUrl($text = null)
    {
        // dev below creates a loop
        // develop
        //      if (!$this->isUrl($text)) {return true;}
        //      return $this->urlRead($text);
    }

    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;

        $input = $this->input;

        $string = $input;
        $str_pattern = "url";
        $str_replacement = "";
        $filtered_input = $input;
        if (strpos($string, $str_pattern) !== false) {
            $occurrence = strpos($string, $str_pattern);
            $filtered_input = substr_replace(
                $string,
                $str_replacement,
                strpos($string, $str_pattern),
                strlen($str_pattern)
            );
        }

        if (!isset($this->thing->quote_handler)) {
            $this->thing->quote_handler = new Quote($this->thing, "quote");
        }
        $filtered_input = $this->thing->quote_handler->stripQuotes(
            $filtered_input
        );

        //        $filtered_input = $this->stripQuotes($filtered_input);

        $input_input = trim($filtered_input);
        if ($input == "") {
            $this->getUrl();
            return;
        }

        // Get urls from string
        $this->urls = $this->extractUrls($input_input);
        $this->url = "X";

        if (isset($this->urls[0])) {
            $this->url = $this->urls[0];
        }
        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == "url") {
                $this->getUrl();
                return;
            }

            if ($input == "read") {
                return;
            }
        }

        if (
            stripos($input, "https://www.google.com/url?") !== false and
            stripos($input, "clean") !== false
        ) {
            $this->url = $this->cleanUrl($this->url);
            $this->url = urldecode($this->url);
            return;
        }

        return;
    }
}
