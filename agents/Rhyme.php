<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Rhyme extends Agent
{
    // This gets rhyming words.

    public $var = "hello";

    public function init()
    {
        $this->keywords = ["rhyme", "event", "show", "happening"];
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
            "variables " . "rhyme" . " " . $this->from
        );

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

    function readRhyme($sort_order = null)
    {
        if ($sort_order == null) {
            $sort_order = "popularity";
        }

        $keywords = "";
        if (isset($this->search_words)) {
            $keywords = $this->search_words;
        }

        $keywords = str_replace(" ", "%20%", $keywords);

        $data_source =
            "http://rhymebrain.com/talk?function=getRhymes&word=" . $keywords;

        $data = @file_get_contents($data_source);
        if ($data == false) {
            $this->response .= "Could not ask Rhymebrain. ";
            $this->available_events_count = 0;
            $this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }

        $json_data = json_decode($data, true);

        $this->rhyme_words = $json_data;

        return true;
    }

    function getLink($ref)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.rhymebrain.com";
        return $this->link;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();
        // Generate email response.

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
            "This provides rhymes from Rhymebrain API.";
    }

    public function makeWeb()
    {
        $html = "<b>RHYME</b>";
        $html .= "<p><b>Rhymebrain rhymes</b>";

        if (!isset($this->rhyme_words)) {
            $html .= "<br>No rhymes found on Rhymebrain.";
        } else {
            $html .= "<p>";
            foreach ($this->rhyme_words as $index => $rhyme) {
                $html .= $rhyme["word"];
                $html .= " /  ";
            }
        }
        $this->html_message = $html;
    }

    public function makeSMS()
    {
        $sms = "RHYME ";
        $sms = strtoupper($this->search_words) . " | ";
        $i = 0;
        foreach ($this->rhyme_words as $index => $rhyme) {
            $sms .= $rhyme["word"];
            $i += 1;
            if ($i >= 7) {
                break;
            }
            $sms .= " /  ";
        }

        $sms .= " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function readSubject()
    {
        $this->num_hits = 0;

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

        $pieces = explode(" ", strtolower($input));

        // Keyword
        if (count($pieces) == 1) {
            if ($input == "rhyme") {
                $this->search_words = "limerick";
                //$this->search_words = null;
                $this->response .= "Asked Rhyhmebrain.com about rhymes. ";
                return;
            }
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "rhyme is")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("rhyme is"));
        } elseif (($pos = strpos(strtolower($input), "rhyme")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("rhyme"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->readRhyme();
            $this->response .=
                "Asked Rhymebrain about " .
                $this->search_words .
                " rhyming words. ";
            return false;
        }

        $this->response .= "Message not understood. ";
        return true;
    }
}
