<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

// devstack
// teststack
// TODO requires RapidAPI signup for apikey
// TODO Read endpoint and process. Customize eventString as textContextualweb
/*
https://rapidapi.com/auth/login?referral=/contextualwebsearch/api/web-search/pricing
*/

class Contextualweb extends Agent
{
    // This gets web search from Contextualweb.com.

    public $var = "hello";

    public function init()
    {
        $this->keywords = ["contextual", "web", "related"];

        $this->api_key = $this->thing->container["api"]["mashape"];
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "contextualweb" . " " . $this->from
        );
    }

    function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    function get()
    {
        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(
            $this->agent_prefix . "loaded " . $this->counter . ".",
            "DEBUG"
        );

        $this->counter = $this->counter + 1;
    }

    function readContextualweb($type = null)
    {
        if (!isset($this->search_words)) {
            $this->response .= "No search term provided. ";
            return true;
        }

        if ($type == null) {
            $type = null;
        }

        $keywords = "";
        if (isset($this->search_words)) {
            $keywords = $this->search_words;
        }

        $keywords = urlencode($keywords);

        $options = [
            "http" => [
                "method" => "GET",
                "header" =>
                    "Accept-language: application/json\r\n" .
                    "X-Mashape-Key: " .
                    $this->api_key .
                    "\r\n" . // check function.stream-context-cr$
                    "", // i.e. An iPad
            ],
        ];

        $context = stream_context_create($options);

        $keywords = urlencode($this->search_words);

        $data_source =
            "https://contextualwebsearch-websearch-v1.p.mashape.com/api/Search/WebSearchAPI?q=" .
            $keywords .
            "&count=3&autocorrect=true";

        $data = @file_get_contents($data_source, false, $context);

        if ($data == false) {
            $this->response .= "Could not ask Contextual Web.";
            $this->definitions_count = 0;
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, true);

        $related = $json_data["relatedSearch"];

        if (!isset($json_data["value"][0])) {
            return true;
        }

        $url = $json_data["value"][0]["url"];
        $definition = strip_tags($json_data["value"][0]["description"]);

        $this->links[0] = $url;
        $this->definitions[0] = $definition;
        $this->definitions_count = 1;

        return false;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->flag = "green";

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["help"] =
            "This triggers provides currency prices using the 1forge API.";

    }

    public function makeSMS()
    {
        $search_message = "";
        if (isset($this->search_words)) {
            $definitions_count = false;
            if (isset($this->definitions_count)) {
                $definitions_count = $this->definitions_count;
            }

            $sms = strtoupper($this->search_words);
            switch ($definitions_count) {
                case 0:
                    $search_message .= "No definitions found.";
                    break;
                case 1:
                    $search_message .=
                        "" . $this->definitions[0] . " " . $this->links[0];
                    break;
                case false:
                    $search_message =
                        strtoupper($this->search_words) . " No thing found.";
                default:
                    foreach ($this->definitions as $definition) {
                        $search_message .= " / " . $definition;
                    }
            }
        }

        $sms = "CONTEXTUALWEB | " . $search_message . " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeMessage()
    {
        $message = "Contextual Web";

        if (!isset($this->definitions_count)) {
            $this->message = $message . " did not find a thing.";
            return;
        }

        switch ($this->definitions_count) {
            case 0:
                $message .= " did not find any definitions.";
                break;
            case 1:
                $message .= ' found, "' . $this->definitions[0] . '"';
                break;
            default:
                foreach ($this->definitions as $definition) {
                    $message .= " / " . $definition;
                }
        }

        $this->message = $message;
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

    public function readSubject()
    {
        $filtered_input = $this->assert($this->input, "contextualweb", false);
        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
        }
    }
}
