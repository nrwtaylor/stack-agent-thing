<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// What is Channel based search?

class Search extends Agent
{
    public $var = 'hello';

    // http://www.helios825.org/url-parameters.php

    public function init()
    {
        // Refactor
        // Consider how to move this to Thing.php

        // Default to ...
        // And show you the data structure.
        $this->search_resources = [
            "google" => [
                "state" => "off",
                "search_prefix" => 'https://google.com/search?q=',
                "search_space" => "+",
            ],
        ];

        $search_settings_resource = '/search/search.php';
        $file = $this->resource_path . $search_settings_resource;
        if (file_exists($file)) {
            $this->search_resources = require $file;
        }

        // https://codereview.stackexchange.com/questions/165263/move-one-element-before-another-in-an-associated-array
        $arr = $this->search_resources;
        while (count($arr) != 0) {
            $ngrams_count_max = 0;
            $longest_token = "";
            foreach ($arr as $search_engine => $search_settings) {
                $tokens = explode(" ", $search_engine);
                $count_tokens = count($tokens);
                if (count($tokens) > $ngrams_count_max) {
                    $longest_token = $search_engine;
                    $ngrams_count_max = $count_tokens;
                    //    $key_order[] = $search_engine;
                    //unset($arr[$search_engine]);
                }

                // Will this work?
            }

            $key_order[] = $longest_token;
            unset($arr[$longest_token]);
        }

        $this->search_engine_order = $key_order;

        $this->search_engines = [];
        foreach ($this->search_engine_order as $i => $search_engine) {
            $this->search_engines[$search_engine] =
                $this->search_resources[$search_engine];
        }
    }

    public function run()
    {
        if (!isset($this->selected_search_resources)) {
            $this->selected_search_resources = $this->search_resources;
        }
        foreach ($this->search_tokens as $search_token => $a) {
            foreach ($this->selected_search_resources as $name => $resource) {
                if (!isset($resource['tokens'])) {
                    continue;
                }

                $resource_tokens = explode(" ", $resource['tokens']);

                foreach ($resource_tokens as $i => $resource_token) {
                    if ($resource_token != $search_token) {
                        continue;
                    }

                    var_dump($resource_token);

                    if (
                        !isset($this->selected_search_resources[$name]['score'])
                    ) {
                        $this->selected_search_resources[$name]['score'] = 1;
                    }

                    $this->selected_search_resources[$name]['score'] += 1;
                }
            }
        }

        $refreshed_at = [];
        foreach ($this->selected_search_resources as $key => $row) {
            $score[$key] = $row['score'];
        }
        array_multisort($score, SORT_DESC, $this->selected_search_resources);
    }

    public function get()
    {
        // For contextual narrowing, some context is needed.
        // It should be this straightforward.

        if (!isset($this->context) or $this->context == null) {
            $context_agent = new Context($this->thing, "context");
            $this->context = $context_agent->context;
        }
        //var_dump($this->context);
        // Refactor.
        // Which can be coded into Agent.
    }

    public function linksSearch($text)
    {
        $flag_first = true;
        $this->search_links = [];

        $search_resources = $this->search_engines;
        if (isset($this->selected_search_resources)) {
            $search_resources = $this->selected_search_resources;
        }
        foreach ($search_resources as $search_resource => $x) {
            if (!$flag_first) {
                //$this->response .= ' - ';
            }
            $link = $this->urlSearch($search_resource, $text);
            $this->search_links[$search_resource] = $link;

            $flag_first = false;
        }

        return $this->search_links;
    }

    public function extractSearch($text = null)
    {
        // Generate a list of recognized search tokens
        foreach ($this->search_resources as $name => $resource) {
            if (!isset($resource['tokens'])) {
                continue;
            }
            $tokens = explode(" ", $resource['tokens']);
            foreach ($tokens as $i => $token_name) {
                $search_tokens[$token_name] = true;
            }
        }

        if ($text == null) {
            return true;
        }

        if (substr($text, 0, 6) != 'search') {
            $parts = explode("search", strtolower($text));

            if (isset($parts[0])) {
                $text = $parts[0];
            }

            if (isset($parts[1])) {
                $text = $parts[1];
            }
        }

        $tokens = explode(" ", strtolower($text));
        $filtered_parts = [];
        $flag = false;
        foreach ($tokens as $i => $part) {
            $part = trim($part);
            if ($part == "") {
                continue;
            }

            if ($part == "search") {
                continue;
            }

            if ($flag === false) {
                foreach ($this->search_resources as $name => $resource) {
                    if ($this->in($name, $part) !== false) {
                        $this->selected_search_resources[$name] = $resource;

                        continue 2;
                    }
                }
            }

            if ($flag === false) {
                foreach ($search_tokens as $name => $resource) {
                    if ($this->in($name, $part) !== false) {
                        continue 2;
                    }
                }
            }

            $flag = true;

            $filtered_parts[] = $part;
        }

        $search_text = implode(" ", $filtered_parts);

        // Shorten if search is too long.
        if (count($filtered_parts) > 5) {
            $search_text = $this->readSearch($search_text);
        }

        //$this->linksSearch($search_text);

        return $search_text;
        //        $this->url = $url_agent->extractUrl($input);
    }

    public function respondResponse()
    {
        $this->thing_report['info'] = 'Creates url search links.';

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->thing_report['help'] =
            'This is an agent which understands what search is. And will help do one.';
    }

    public function urlSearch($search_engine = null, $raw_search_words = null)
    {
        if ($search_engine == null) {
            return;
        }

        if ($raw_search_words == null) {
            $raw_search_words = $this->search_text;
        }

        $links = "";
        $search_resource = $this->search_resources[$search_engine];
        //$search_words = $this->search_words;
        $search_words = $raw_search_words;
        if (
            isset($search_resource['search_encoding']) and
            $search_resource['search_encoding'] == "url"
        ) {
            $search_words = urlencode($search_words);
        }

        $search_postfix = "";
        if (isset($search_resource['search_postfix'])) {
            $search_postfix = $search_resource['search_postfix'];
        }

        $link =
            $search_resource['search_prefix'] .
            str_replace(
                " ",
                $search_resource['search_space'],
                $search_words . $search_postfix
            );

        $html_link =
            '<div><a href="' .
            $link .
            '">' .
            $search_engine .
            ' search</a></div>';

        //$this->response .= $link;

        return $link;
    }

    public function readSearch($text = null)
    {
        if ($text == null) {
            return false;
        }

        $text = trim($text);

        $brilltagger = new Brilltagger($this->thing, "brilltagger");

        $arr = $brilltagger->tag($text);

        $search_tokens = [];

        foreach ($arr as $i => $tag_array) {
            $token = $tag_array['token'];
            // devstack - fix brilltagger tag name generator
            $tag = trim($tag_array['tag']);

            $allow_tags = ["JJ", "NNS", "NN"];

            if (in_array($tag, $allow_tags)) {
                $search_tokens[] = $token;
            }
        }

        $search_text = trim(implode(" ", $search_tokens));

        return $search_text;
    }

    public function makeSMS()
    {
        $sms = "SEARCH ";

        if (
            isset($this->search_text) and
            !in_array($this->search_text, ["", null])
        ) {
            $search_message = strtolower($this->search_text) . " ";
            $links = $this->linksSearch($this->search_text);
            $sms .= $search_message;
        }
        //        $sms .= $this->response;

        if (isset($links)) {
            $sms .= "| ";
            $i = 0;
            foreach ($links as $search_engine => $link) {
                if ($i > 3) {
                    break;
                }
                $sms .= $search_engine . ' ' . $link . ' / ';
                $i += 1;
            }
        }

        $sms .= " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function makeWeb()
    {
        $links = $this->linksSearch($this->search_text);
        $web = "<b>Search Agent</b><p>";
        $web .= $this->search_text . "<br>";
        //$web .= $this->subject ."<br>";
        //$web .= $this->thing->subject . "<br>";

        // Devstack
        // Encoding issue?
        // Equals sign renders as question in diamond.

        $web .= "<div>";
        foreach ($this->selected_search_resources as $name => $resource) {
            if (isset($resource['score'])) {
                if ($resource['score'] == 0) {
                    continue;
                }
                $web .= $name;
                $web .= "(" . $resource['score'] . ")";
            }
            $web .= " ";
        }
        $web .= "</div>";

        if ($this->engine_state != 'prod') {
            foreach ($this->search_links as $search_engine => $link) {
                //var_dump($link);
                //$link2 = html_entity_decode($link);
                //$link = utf8_encode($link);
                //echo $link;
                //$link =  html_entity_decode($link, ENT_COMPAT);
                //var_dump($link);

                //var_dump(mb_detect_encoding($link));

                //$link = htmlentities($link);
                //$link = utf8_encode($link);
                //var_dump($link);
                //var_dump(mb_detect_encoding($link));
                $line =
                    $search_engine .
                    ' <a href="' .
                    $link .
                    '">' .
                    $search_engine .
                    " " .
                    $this->search_text .
                    '</a>' .
                    ' or <a href="' .
                    $link .
                    '">' .
                    $link .
                    '</a>' .
                    "<br>";

                $web .= $line;
            }
        }

        $this->thing_report['web'] = $web;
    }

    public function in($piece, $name)
    {
        if (strpos(strtolower($piece), $name) !== false) {
            return true;
        }

        if (strpos(strtolower($name), $piece) !== false) {
            if (strtolower($name) == strtolower($piece)) {
                return true;
            }
        }

        return false;
    }

    public function readSubject()
    {
        $input = $this->input;
        $this->search_text = $this->extractSearch($input);

        //$pieces = explode(" ", $this->search_text);
        $pieces = explode(" ", $input);

        if (count($pieces) == 1) {
            if (strtolower($input) == 'search') {
                return;
            }
        }
        //var_dump($this->search_text);
        //var_dump($pieces);
        //exit();

        $this->keywords = ['product', 'news', 'list', 'random'];
        foreach ($pieces as $key => $piece) {
            if ($piece == 'search') {
                continue;
            }

            // Add to tokens
            $this->search_tokens[strtolower($piece)] = true;

            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    $this->instruct($piece);
                }
            }

            foreach ($this->search_resources as $name => $resource) {
                if (strpos(strtolower($piece), $name) !== false) {
                    $this->selected_search_resources[$name] = $resource;
                    continue;
                }

                if (strpos(strtolower($name), $piece) !== false) {
                    $this->selected_search_resources[$name] = $resource;
                    continue;
                }
            }
        }
    }

    public function build()
    {
        $matches = 0;

        foreach ($this->search_resource_order as $i => $search_engine) {
            if (strpos($filtered_input, $search_engine) !== false) {
                $filtered_input = $this->assert(
                    $search_engine,
                    $filtered_input
                );
                $this->search_resources[$search_engine] =
                    $this->search_resources[$search_engine];
            }
        }

        if ($matches == 1) {
            $this->search_resource = $this->search_resources[0];
        }
    }

    public function instruct($piece = null)
    {
        if ($piece == null) {
            return true;
        }

        switch ($piece) {
            case 'list':
                foreach ($this->search_resources as $name => $resource) {
                    $this->response .= $name . " / ";
                }
                return;

            case 'random':
                //devstack
                return;
                $search_resources = $this->search_resources;
                if (isset($this->selected_search_resources)) {
                    $search_resources = $this->selected_search_resources;
                }

                $selected_search_resource = array_rand($search_resources);
                $selected_search = $search_resources[$selected_search_resource];
                $this->selected_search_resources[
                    $selected_search_resource
                ] = $selected_search;
                return;
            // some thought follow
            case 'make':
            case 'new':
                return;
            case 'next':
                return;
            default:
        }
    }
}
