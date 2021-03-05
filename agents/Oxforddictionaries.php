<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Oxforddictionaries extends Agent
{
    // This gets Forex from an API.

    public $var = 'hello';

    public function init()
    {
        $this->test = "Development code"; // Always

        $this->keywords = [
            'oxford',
            'dictionary',
            'dictionaries',
            'english',
            'spanish',
            'german',
        ];

        $this->application_id =
            $this->thing->container['api']['oxford_dictionaries'][
                'application_id'
            ];
        $this->application_key =
            $this->thing->container['api']['oxford_dictionaries'][
                'application_key'
            ];

        $this->run_time_max = 360; // 5 hours
    }

    public function run()
    {
        $this->getApi("dictionary");
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
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "oxford_dictionaries" . " " . $this->from
        );

        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(
            $this->agent_prefix . 'loaded ' . $this->counter . ".",
            "DEBUG"
        );

        $this->counter = $this->counter + 1;
    }

    function getApi($type = "dictionary")
    {
        if ($type == null) {
            $type = "dictionary";
        }

        $keywords = "";
        if (isset($this->search_words)) {
            $keywords = $this->search_words;
        }

        $keywords = urlencode($keywords);

        $options = [
            'http' => [
                'method' => "GET",
                'header' =>
                    "Accept-language: application/json\r\n" .
                    "app_id: " .
                    $this->application_id .
                    "\r\n" . // check function.stream-context-create on php.net
                    "app_key: " .
                    $this->application_key .
                    "\r\n" .
                    "", // i.e. An iPad
            ],
        ];

        $context = stream_context_create($options);

        $data_source =
            "https://od-api.oxforddictionaries.com:443/api/v1/entries/en/" .
            $keywords;

        //get /entries/{source_lang}/{word_id}/synonyms

        $data = @file_get_contents($data_source, false, $context);
        if ($data === false) {
            $this->response .= "Could not ask Oxford Dictionaries. ";
            $this->definitions_count = 0;
            //$this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, true);

        $definitions =
            $json_data['results'][0]['lexicalEntries'][0]['entries'][0][
                'senses'
            ];

        $count = 0;
        foreach ($definitions as $id => $definition) {
            if (!isset($definition['definitions'][0])) {
                continue;
            }
            $this->definitions[] = $definition['definitions'][0];
            $count += 1;
        }

        $this->definitions_count = $count;

        return false;
    }

    function getLink($ref = null)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.oxforddictionaries.com";
        return $this->link;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report['choices'] = $choices;

        $this->flag = "green";

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $this->thingreportOxforddictionaries();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->thing_report['help'] =
            'This triggers provides currency prices using the 1forge API.';
    }

    public function makeWeb()
    {
        $html = "<b>OXFORD DICTIONARIES</b>";
        $html .= "<p><b>Oxford Dictionaries definitions</b>";

        if (!isset($this->events)) {
            $html .= "<br>No definitions found on Oxford Dictionaries.";
        } else {
            foreach ($this->events as $id => $event) {
                $event_html = $this->eventString($event);

                $link = $event['link'];
                $html_link = '<a href="' . $link . '">';
                //        $web .= $this->html_image;
                $html_link .= "oxford dictionaries";
                $html_link .= "</a>";

                $html .= "<br>" . $event_html . " " . $html_link;
            }
        }

        $this->html_message = $html;
    }

    public function makeSMS()
    {
        //$sms = "OXFORD DICTIONARIES";
        if (!isset($this->search_words)) {
            $sms = "OXFORD DICTIONARIES";
        }

        if (isset($this->search_words)) {
            $sms = strtoupper($this->search_words);
        }

        switch ($this->definitions_count) {
            case 0:
                $sms .= " | No definitions found.";
                break;
            case 1:
                $sms .= " | " . $this->definitions[0];

                break;
            default:
                foreach ($this->definitions as $definition) {
                    $sms .= " / " . $definition;
                }
        }

        $sms .= " | " . $this->response;

        // Really need to refactor this double :/
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function makeMessage()
    {
        $message = "Oxford Dictionaries";

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

    private function thingreportOxforddictionaries()
    {
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['web'] = $this->html_message;
        $this->thing_report['message'] = $this->message;
    }

    public function readSubject()
    {
        $this->num_hits = 0;

        $keywords = $this->keywords;

        $input = $this->input;

        //        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'oxforddictionaries') {
                //$this->search_words = null;
                $this->response .= "Asked Oxford Dicionaries about nothing. ";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        default:
                    }
                }
            }
        }

        $filtered_input = $this->assert($input);

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $t = "words";
            if (count(explode(" ", $this->search_words)) == 1) {
                $t = "word";
            }
            $this->response .=
                "Saw a request about the " .
                $t .
                ": " .
                $this->search_words .
                ". ";
            return false;
        }

        $this->response .= "Message not understood";
        return true;
    }
}
