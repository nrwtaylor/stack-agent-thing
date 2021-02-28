<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Dictionary extends Agent
{
    public $var = 'hello';

    public function init()
    {
        $this->test = "Development code"; // Always

        $this->keyword = "dictionary";
        $this->keywords = [
            'meaning',
            'definition',
            'dictionary',
            'dictionaries',
            'english',
            'spanish',
            'german',
        ];

        $this->run_time_max = 360; // 5 hours
    }

    function run()
    {
        $this->getDictionary();
    }

    function set()
    {
        return;
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );

        //        $this->thing->choice->save('usermanager', $this->state);

        return;
    }

    function get()
    {
        return;
        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(
            $this->agent_prefix . 'loaded ' . $this->counter . ".",
            "DEBUG"
        );

        $this->counter = $this->counter + 1;

        return;
    }

    public function getDictionary()
    {
        if (isset($this->definitions)) {
            return;
        }
        $this->definitions = [];

        $oxford_dictionaries = new Oxforddictionaries(
            $this->thing,
            "oxforddictionaries " . $this->search_words
        );

        if (isset($oxford_dictionaries->definitions)) {
            $this->definitions = $oxford_dictionaries->definitions;
        }

        $this->definitions_count = count($this->definitions);

        if ($this->definitions_count == 0) {
            // No word found. Check wikipedia.

            $wikipedia = new Wikipedia(
                $this->thing,
                "wikipedia ." . $this->search_words
            );
            $this->definitions[] = $wikipedia->text;
        }

        $this->definitions_count = count($this->definitions);

        if ($this->definitions_count == 0) {
            // Still no word found
        }
    }

    public function getLink($ref = null)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.google.com/search?q=" . $ref;
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

        $this->thingreportDictionary();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->thing_report['help'] = 'This provides a dictionary lookup.';
    }

    public function makeWeb()
    {
        if (!isset($this->definitions)) {
            $this->getDictionary();
        }

        $html = "<b>DICTIONARY</b>";
        $html .= "<p><b>Oxford dictionaries definition</b>";

        if (!isset($this->definitions)) {
            $html .= "<br>No definitions found on Oxford Dictionaries.";
        } else {
            foreach ($this->definitions as $id => $definition) {
            }
        }

        $this->html_message = $html;
    }

    public function makeSMS()
    {
        //$sms = "DICTIONARYIES";
        $sms = strtoupper($this->search_words);
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
    }

    public function makeMessage()
    {
        $message = "Dictionary";

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

        // $message .= " | " . $this->response;

        // Really need to refactor this double :/

        $this->message = $message;
    }

    private function thingreportDictionary()
    {
        $this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['web'] = $this->html_message;
        $this->thing_report['message'] = $this->message;
    }

    public function loadDictionary($file_names) {

        if (is_string($file_names)) {$file_names = [$file_names];}

        foreach($file_names as $j=>$file_name) {

        $librex_handler = new Librex($this->thing, "librex");
        $librex_handler->getLibrex($file_name);
        $librex_handler->linesLibrex();

        foreach($librex_handler->lines as $i=>$line) {
            $dictionary = $this->getSlug($file_name);
            $slug = $this->getSlug($line);
            $arr = $this->getMemory( $slug );

            if ($arr == null) {$arr = [];}

            $this->setMemory( $slug, array_merge([$dictionary=>true], $arr) );
        }
        }

    }

    public function deprecate_extractNumber($input = null)
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
        $this->response = null;

        $this->num_hits = 0;
        // Extract uuids into

        //$this->number = extractNumber();

        $keywords = $this->keywords;

        if ($this->agent_input != null) {
            // If agent input has been provided then
            // ignore the subject.
            // Might need to review this.
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        $this->input = $input;

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == 'dictionary') {
                //$this->search_words = null;
                $this->search_words = "dictionary";
                $this->response = "Asked Dictionary about nothing.";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'run':
                            //     //$this->thing->log("read subject nextblock");
                            $this->runTrain();
                            break;

                        default:
                    }
                }
            }
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "dictionary is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("dictionary is")
            );
        } elseif (($pos = strpos(strtolower($input), "dictionary")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("dictionary")
            );
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->response =
                "Asked Dictionary about the word " . $this->search_words . ".";
            return false;
        }

        $this->response = "Message not understood";
        return true;
    }
}
