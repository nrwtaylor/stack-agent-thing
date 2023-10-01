<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Wikipedia extends Agent
{
    // This gets Forex from an API.

    public $var = "hello";
    function init()
    {
        $this->keyword = "know";

        $this->keywords = ["wikipedia", "definition"];

        $this->application_id = null;
        $this->application_key = null;

        $this->run_time_max = 360; // 5 hours

        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "wikipedia" . " " . $this->from
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

    function apiWikipedia($keywords = null, $sort_order = null)
    {
        if ($sort_order == null) {
            $sort_order = "popularity";
        }

        $city = "vancouver";
        // "America/Vancouver" apparently
        if ($keywords === "" or $keywords === null) {
            $keywords = "";
            if (isset($this->search_words)) {
                $keywords = $this->search_words;
            }
        }
        $keywords = urlencode($keywords);

        //$titles = "&titles=New_York_Yankees";
        $titles = "&titles=" . $keywords;

        $format = "&format=json";

        $rvprop = "&rvprop=timestamp|user|comment|content";
        $rvprop = "";

        $prop = "&prop=revisions";
        $prop = "&prop=extracts";

        //if we just want the intro, we can use exintro. Otherwise it shows all sections
        $exintro = "&exintro=1";
        $list = "&list=search";

        //$srsearch = "&srsearch=皮皮果";
        $srsearch = "&srsearch=" . $keywords;

        // Experiments
        // $data_source = "http://en.wikipedia.org/w/api.php?action=query" . $prop . $exintro . $format . $prop . $titles . $rvprop;
        // $data_source = "http://en.wikipedia.org/w/api.php?action=query&list=search&srsearch=皮皮果&utf8=&format=json";
        // $data_source = "http://en.wikipedia.org/w/api.php?action=query" . $srsearch . $prop . $exintro . $format . $rvprop;
        // $data_source = "http://en.wikipedia.org/w/api.php?action=query" . $srsearch . $prop . $exintro . $format . $rvprop;

        // Gets a list of matches
        $data_source =
            "http://en.wikipedia.org/w/api.php?action=query" .
            $list .
            $srsearch .
            "&utf8=&format=json";

        $data = file_get_contents($data_source);

        if ($data == false) {
            $this->response = "Could not ask Wikipedia.";
            $this->available_events_count = 0;
            $this->events_count = 0;
            return true;
            // Invalid query of some sort.
        }
        $json_data = json_decode($data, true);

        if (!isset($json_data["query"]["search"][0]["snippet"])) {
            $this->text = "Wikipedia did not find anything.";
            return true;
        }

        $snippet = strip_tags($json_data["query"]["search"][0]["snippet"]);
        $this->text = html_entity_decode($snippet);
        return false;
    }

    function getLink($ref = null)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.google.com/search?q=" . $ref;
        return $this->link;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["help"] =
            "This asks Wikipedia about the words provided.";
    }

    public function makeWeb()
    {
        $html = "<b>WIKIPEDIA</b>";
        $html .= "<p><b>Wikipedia Text</b>";

        if (!isset($this->text)) {
            $html .= "<br>Nothing found on Wikipedia.";
        } else {
            $html .= "<br>" . $this->text;
        }
        $this->html_message = $html;
    }

    function truncate($string, $length = 100, $append = "[...]")
    {
        $string = trim($string);

        if (strlen($string) > $length) {
            $string = wordwrap($string, $length);
            $string = explode("\n", $string, 2);
            $string = $string[0] . $append;
        }
        return $string;
    }

    public function makeSMS()
    {
        $sms = "WIKIPEDIA | ";

        if ((!isset($this->text)) or ($this->text == "")) {
            $text = "Nothing found.";
        } else {
            $text = $this->truncate($this->text, 100);
        }

        $sms .= $text;
        $sms .= " | " . $this->response;
        $this->thing_report["sms"] = $sms;
        $this->sms_message = $sms;
    }

    public function makeMessage()
    {
        if ((!isset($this->text)) or ($this->text == "")) {
            $text = "Nothing found.";
        } else {
            $text = $this->truncate($this->text, 100);
        }

        if (substr_count($text, '"') > 0) {
            $quotation_mark = "'";
        } else {
            $quotation_mark = '"';
        }

        $message = "Wikipedia said, " . $quotation_mark;

        $message .= $text;
        $message .= $quotation_mark;

        $this->thing_report["message"] = $message;
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
        $this->response = null;

        $this->num_hits = 0;
        // Extract uuids into

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

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == "wikipedia") {
                //$this->search_words = null;
                $this->response = "Asked Wikipedia about everything.";
                return;
            }
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "wikipedia is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("wikipedia is")
            );
        } elseif (($pos = strpos(strtolower($input), "wikipedia")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("wikipedia"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->apiWikipedia();

            $this->response =
                "Asked Wikipedia about " . $this->search_words . ".";
            return false;
        }

        $this->response = "Message not understood";
        return true;
    }
}
