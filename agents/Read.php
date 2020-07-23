<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Read extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->test = "Development code"; // Always
        $this->keywords = ['read', 'link', 'date', 'wordlist', 'last'];

        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "read" . " " . $this->from
        );

        $this->link = $this->web_prefix;
        if ($this->link == false) {
            $this->link = "";
        }

        $this->do_not_read = false;
        $this->do_not_catalogue = false;

        $this->read_horizon = 6 * 60 * 60; // 6 hours
    }

    function run()
    {
        // Now have this->link potentially from reading subject

        $this->matched_sentences = [];

        $this->robot_agent = new Robot($this->thing, $this->link);

        if (
            $this->robot_agent->robots_allowed(
                $this->link,
                $this->robot_agent->user_agent_short
            )
        ) {
            $this->response .=
                "Robot " . $this->robot_agent->user_agent_short . " allowed. ";

            if (
                substr($this->link, 0, 4) === "http" or
                substr($this->link, 0, 5) === "https"
            ) {
                // Okay.
            } elseif (isset($this->robot_agent->scheme)) {
                $this->link = $this->robot_agent->scheme . '://' . $this->link;
            } else {
                return true;
            }
            // Populate $this->contents
            $this->getUrl($this->link);

            $this->metaRead($this->contents);
            if ($this->noindexRead($this->contents)) {
                // Read as noindex do not set url
                $this->response .= 'Do not index. ';
            }

            if ($this->copyrightRead($this->contents)) {
                $this->response .= 'Saw a copyright notice. ';
                $this->do_not_read = true;
            }

            if ($this->trademarkRead($this->contents)) {
                $this->response .= 'Saw a trademark notice. ';
                $this->do_not_read = true;
            }

            // Okay to read meta. Get description.
            $description = $this->descriptionRead($this->contents);
            $this->response .= 'Read meta ' . $description . ' ';

            //}

            // Get all the URLs in the page.
            $url_agent = new Url($this->thing, "url");
            $this->urls = $url_agent->extractUrls($this->contents);
            $text = strip_tags($this->contents);
            // Remove multiple spaces
            $text = preg_replace('/\s+/', ' ', $text);
            // Remove start and end spaces
            $text = trim($text);

            //https://stackoverflow.com/questions/16377437/split-a-text-into-sentences
            $pattern = '/(?<=[.?!])\s+(?=[a-z])/i';

            //$pattern = '/(?<!\.\.\.)(?<!Dr\.)(?<=[.?!]|\.\.)|\.")\s+(?=[a-zA-Z"\(])/';
            $this->sentences = preg_split($pattern, $text);

            foreach ($this->sentences as $i => $sentence) {
                if (stripos($sentence, $this->search_phrase) !== false) {
                    $this->matched_sentences[] = $sentence;
                }
            }
        } else {
            $this->response .=
                "Robot not allowed. " . $this->robot_agent->response;
            $this->do_not_read = true;
        }
    }

    function copyrightRead($html)
    {
        // devstack

        if (stripos($html, 'copywrite') !== false) {
            return true;
        }

        if (stripos($html, 'copyright') !== false) {
            return true;
        }

        if (stripos($html, '©') !== false) {
            return true;
        }

        if (stripos($html, '(c)') !== false) {
            return true;
        }

        if (stripos($html, 'copr') !== false) {
            return true;
        }

        if (stripos($html, '&copy') !== false) {
            return true;
        }

        return false;
    }

    public function readRead($text)
    {
        if ($text == null) {
            return true;
        }

        if (strtolower($text) == "read") {
            return false;
        }

        $tokens = explode(" ", strtolower($text));

        $first_two_characters = strtolower(substr($text, 0, 2));

        if ($first_two_characters == 's/') {
            return false;
        }

        if (strpos($text, 'read') !== false) {
        } else {
            return false;
        }

        $tokens = explode(" ", $text);
        if (strtolower($tokens[0]) == 'read') {
            array_shift($tokens);
            $text = trim(implode(" ", $tokens));
        }

        $text = preg_replace('/^read _/', '', $text);
        $text = preg_replace('/^read_/', '', $text);

        return $text;
    }

    public function getReads()
    {
        $reads_list = [];

        $this->reads_list = [];
        $this->unique_count = 0;

        $findagent_thing = new Findagent($this->thing, 'read');
        if (!is_array($findagent_thing->thing_report['things'])) {
            return;
        }
        $count = count($findagent_thing->thing_report['things']);
        $this->thing->log(
            'Agent "Read" found ' .
                count($findagent_thing->thing_report['things']) .
                " Read Things."
        );

        //$rule_agent = new Rule($this->thing, "rule");

        if ($count > 0) {
            foreach (
                array_reverse($findagent_thing->thing_report['things'])
                as $thing_object
            ) {
                $uuid = $thing_object['uuid'];

                if ($uuid == $this->uuid) {
                    continue;
                }

                $variables_json = $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                $response = $this->readRead($thing_object['task']);

                // This can be refactered I think with a call to the empty thing function.
                //if ($response == false) {continue;}
                //if ($response == true) {continue;}
                //if ($response == null) {continue;}
                if ($response == "") {
                    continue;
                }

                if ($response === true) {
                    continue;
                }

                $text = $response;

                $age =
                    strtotime($this->thing->time()) -
                    strtotime($thing_object['created_at']);

                if ($age > $this->read_horizon) {
                    continue;
                }

                $read = [
                    "url" => $response,
                    "age" => $age,
                    "uuid" => $thing_object['uuid'],
                ];

                $reads_list[] = $read;
            }
        }
        $this->reads_list = $reads_list;
        $this->unique_count = count($reads_list);
    }

    function trademarkRead($html)
    {
        // devstack

        if (stripos($html, 'trademark') !== false) {
            return true;
        }

        if (stripos($html, ' TM ') !== false) {
            return true;
        }

        if (stripos($html, '(TM)') !== false) {
            return true;
        }

        if (stripos($html, 'TM.') !== false) {
            return true;
        }

        if (stripos($html, '™') !== false) {
            $this->response .= "trademark b";

            return true;
        }

        if (stripos($html, '®') !== false) {
            $this->response .= "trademark c";

            return true;
        }
        /*
        if (stripos($html, 'tradem') !== false) {
            return true;
        }
*/
        return false;
    }

    function metaRead($html)
    {
        $doc = new \DOMDocument();
        //$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        @$doc->loadHTML($html);

        $xpath = new \DOMXpath($doc);
        //$elements = $xpath->query("*/div[@class='yourTagIdHere']");
        $elements = $xpath->query(
            "//*[contains(@class, 'class name goes here')]"
        );
    }

    /* A comment to break the confusion that the above string causes. */

    function noindexRead($html)
    {
        $doc = new \DOMDocument();
        //$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        @$doc->loadHTML($html);

        $xpath = new \DOMXpath($doc);
        //$elements = $xpath->query("*/div[@class='yourTagIdHere']");

        //$nodes = $xpath->query('meta[name="robots"');
        //$contents = $xpath->query('//meta[@name="description"]/@content');

        $contents = $xpath->query('//meta[@name="robots"]/@content');

        $meta = [];

        foreach ($contents as $node) {
            $meta[] = $node->nodeValue;
        }

        $contents = $xpath->query('//meta[@name="ROBOTS"]/@content');

        foreach ($contents as $node) {
            $meta[] = $node->nodeValue;
        }

        foreach ($meta as $i => $tag) {
            if ($tag == "NOINDEX") {
                return true;
            }
            if ($tag == "noindex") {
                return true;
            }
        }
        return false;
    }
    /* A comment to break the confusion that the above string causes. */

    function descriptionRead($html)
    {
        $doc = new \DOMDocument();
        //$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        @$doc->loadHTML($html);

        $xpath = new \DOMXpath($doc);

        $contents = $xpath->query('//meta[@name="description"]/@content');
        $meta = [];

        foreach ($contents as $node) {
            $meta[] = $node->nodeValue;
        }

        $contents = $xpath->query('//meta[@name="DESCRIPTION"]/@content');

        foreach ($contents as $node) {
            $meta[] = $node->nodeValue;
        }

        foreach ($meta as $i => $tag) {
            //echo $i . " " . $tag . "<br>";
        }

        $response = "an empty description.";
        if (isset($meta[0])) {
            $response = $meta[0];
        }

        return $response;
    }

    function set()
    {
        $this->variables_agent->setVariable("state", $this->state);

        $this->variables_agent->setVariable("link", $this->link);

        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        $this->refreshed_at = $this->current_time;
    }

    function get()
    {
        $this->state = $this->variables_agent->getVariable("state");
        $this->link = $this->variables_agent->getVariable("link");
        $this->refreshed_at = $this->variables_agent->getVariables(
            "refreshed_at"
        );

        $this->getReads();
    }

    function getUrl($url = null)
    {
        $this->contents = false;
        if ($url == null) {
            $this->link = $this->web_prefix;
            $url = $this->link;
        }

        $data_source = $this->link;

        // Has this been read recently?
        foreach ($this->reads_list as $i => $read) {
            if ("http://" . $read['url'] == $this->link) {
                if (!isset($last_seen)) {
                    $last_seen = $read['age'];
                }
                if ($read['age'] < $last_seen) {
                    $last_seen = $read['age'];
                }
            }

            if ("https://" . $read['url'] == $this->link) {
                if (!isset($last_seen)) {
                    $last_seen = $read['age'];
                }
                if ($read['age'] < $last_seen) {
                    $last_seen = $read['age'];
                }
            }
        }

        if (isset($last_seen)) {
            $this->response .=
                "Last read " . $this->thing->human_time($last_seen) . " ago. ";
        }

        $options = [
            'http' => [
                'method' => "GET",
                'header' =>
                    "User-Agent: " . $this->robot_agent->useragent . "\r\n",
            ],
        ];

        $context = stream_context_create($options);
        $data = file_get_contents($data_source, false, $context);

        if (isset($http_response_header[0])) {
            $response_string = $http_response_header[0];
        } else {
            $this->thing->log('No response code header found.');
            return true;
        }
        $parts = explode(' ', $response_string);
        $response_code = null;
        if (isset($parts[1])) {
            $response_code = $parts[1];
        }
        $this->response_code = $response_code;
        $allowed_response_codes = [301, 302, 200];

        if (
            $data == false or
            !in_array($response_code, $allowed_response_codes)
        ) {
            $this->thing->log('No response or response code not 200.');
            return true;
            // Invalid return from site..
        }

        // Raw file
        $this->contents = $data;
    }

    function match_all($needles, $haystack)
    {
        if (empty($needles)) {
            return false;
        }

        foreach ($needles as $needle) {
            if (strpos($haystack, $needle) == false) {
                return false;
            }
        }
        return true;
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
        $sms_message = "READ | ";
        $sms_message .= trim($this->response);

        if ($this->verbosity >= 2) {
        }

        if ($this->link !== false) {
            $sms_message .= " | link " . $this->link;
        }
        $sms_message .= " | Do not read flag ";
        if ($this->do_not_read) {
            $sms_message .= 'RED. ';
        } else {
            $sms_message .= 'GREEN. ';
        }

        $this->thing_report['sms'] = $sms_message;
        $this->sms_message = $sms_message;
    }

    public function makeWeb()
    {
        $web = "<b>READ AGENT</b><p>";

        if (isset($this->urls) and $this->urls == true) {
        } else {
            //var_dump($this->urls);
        }

        $web .= "<p><b>URLs read</b><br>";

        if (isset($this->contents)) {
            $link_agent = new Link($this->thing, "link");
            $link_agent->extractLinks($this->contents);

            $links = array_unique($link_agent->links);

            foreach ($links as $i => $link) {
                $unsafe_characters = ['{', '}'];

                if (
                    preg_match(
                        '/[' .
                            preg_quote(implode(',', $unsafe_characters)) .
                            ']+/',
                        $link
                    )
                ) {
                    continue;
                }

                $web .= '<a href="' . $link . '">' . $link . '</a>' . '<br>';
            }

            $sentence = $this->sentences[0];

            $word_agent = new Word($this->thing, "word");
            $words = $word_agent->extractWords($this->contents);

            $unique_words = array_unique($words);

            $web .= "<p><b>Words read</b>" . '<br>';

            foreach ($unique_words as $i => $unique_word) {
                $web .= $unique_word . " ";
            }

            $web .= '<p>';
        }
        $web .= $this->sms_message;
        $web .= '<br>';
        $this->thing_report['web'] = $web;
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

    public function extractNumber($input = null)
    {
        if ($input == null) {
            $input = $this->subject;
        }

        $pieces = explode(" ", strtolower($input));

        // Extract number
        $matches = 0;
        foreach ($pieces as $key => $piece) {
            if (is_numeric($piece)) {
                $number = $piece;
                $matches += 1;
            }
        }

        if ($matches == 1) {
            if (is_integer($number)) {
                $this->number = intval($number);
            } else {
                $this->number = floatval($number);
            }
        } else {
            $this->number = true;
        }
        return $this->number;
    }

    public function readSubject($input = null)
    {
        //$input = null;

        if ($input == null) {
            $input = $this->assert($this->input);
        }

        $pieces = explode(" ", strtolower($input));

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'last':
                            // devstack
                            $this->getPrior();
                            $task = $this->prior_thing['thing']->task;
                            $uuid = $this->prior_thing['thing']->uuid;
                            $nom_from = $this->prior_thing['thing']->nom_from;
                            $nom_to = $this->prior_thing['thing']->nom_to;
                            $created_at =
                                $this->prior_thing['thing']->created_at;

                            $input = $task;
                            if (!isset($this->recursion_count)) {
                                $this->recursion_count = 0;
                            }

                            if ($this->recursion_count < 2) {
                                $this->recursion_count += 1;
                                $this->readSubject($input);
                            }

                            if ($this->recursion_count == 1) {
                                $this->response .= 'Read "' . $input . '" ';
                            } else {
                                $this->response .=
                                    'Then read "' . $input . '" ';
                            }
                    }
                }
            }
        }

        //$this->response = null;
        $this->num_hits = 0;

        $url_agent = new Url($this->thing, "url");

        $this->url = $url_agent->extractUrl($input);

        $this->link = $this->url;

        $input = str_replace($this->url, "", $input);
        $this->search_phrase = trim(strtolower($input));

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
