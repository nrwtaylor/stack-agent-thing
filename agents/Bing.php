<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Bing extends Agent
{
    // This gets Forex from an API.

    public $var = 'hello';

    public function init()
    {
        $this->keyword = "bing";

        $this->test = "Development code"; // Always

        $this->keywords = ['bing', 'search', 'web'];

        $this->api_key =
            $this->thing->container['api']['microsoft']['bing']['key1'];
    }

    public function run()
    {
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
            "variables " . "bing" . " " . $this->from
        );

        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->counter = $this->counter + 1;
    }

    function getApi($type = null)
    {
        if ($type == null) {
            $type = null;
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
                    "Ocp-Apim-Subscription-Key: " . $this->api_key . "\r\n", // check function.stream-context-create on php.net
            ],
        ];

        $context = stream_context_create($options);

        $keywords = urlencode($this->search_words);

        $data_source =
            "https://api.cognitive.microsoft.com/bing/v7.0/search?q=" .
            $keywords;
        $data = file_get_contents($data_source, false, $context);
        if ($data == false) {
            $this->response .= "Could not ask Bing. ";
            $this->definitions_count = 0;
            //$this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, true);

        $this->response .= "Asked Bing about the word " . $keywords . ". ";

        $definition = $json_data['webPages']['value'][0]['snippet'];

        /*
$count = 0;
foreach ($definitions as $id=>$definition) {
    if (!isset($definition['definitions'][0])) {continue;}
    $this->definitions[] = $definition['definitions'][0];
    $count += 1;
}
*/

        $this->definitions[0] = $definition;
        $this->definitions_count = 1;

        return false;
    }

    function getLink($ref = null)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.bing.com/?q=" . $ref;
        return $this->link;
    }

    public function respondResponse()
    {
        // Thing actions
        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report['choices'] = $choices;

        $this->flag = "green";

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        $this->thingreportEventful();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->thing_report['help'] = 'This interfaces with the Bing service.';
    }

    public function makeWeb()
    {
        $html = "<b>BING</b>";
        $html .= "<p><b>Bing Definitions</b>";

        if (!isset($this->events)) {
            $html .= "<br>No definitions found on Bing.";
        } else {
            foreach ($this->events as $id => $event) {
                $event_html = $this->eventString($event);

                $link = $event['link'];
                $html_link = '<a href="' . $link . '">';
                $html_link .= "eventful";
                $html_link .= "</a>";

                $html .= "<br>" . $event_html . " " . $html_link;
            }
        }
        $this->html_message = $html;
    }

    public function makeSMS()
    {
        $sms = "BING";

        if ((isset($this->search_words)) and ($this->search_words != null)) {
            $sms .= " " . strtoupper($this->search_words);
	}
        $sms .= " | ";


if (isset($this->definitions_count)) {

        switch ($this->definitions_count) {
            case 0:
                $sms .= "No definitions found.";
                break;
            case 1:
                $sms .= $this->definitions[0];

                break;
            default:
                foreach ($this->definitions as $definition) {
                    $sms .= " / " . $definition;
                }
        }
        $sms .= " | ";
}
        $sms .= $this->response;

        // Really need to refactor this double :/
        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function makeMessage()
    {
        $message = "Bing";

$message = "No definition count found.";
if (isset($this->definitions_count)) {
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
}

        // Really need to refactor this double :/

        $this->message = $message;
        $this->thing_report['message'] = $message;
    }

    private function thingreportEventful()
    {
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['web'] = $this->html_message;
        $this->thing_report['message'] = $this->message;
    }

    public function readSubject()
    {
        $this->input = $input;

        $input = $this->input;
        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'bing') {
                $this->response .= "Nothing to asked Bing about. ";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
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
            $this->getApi();
            return false;
        }

    }
}
