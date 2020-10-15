<?php
namespace Nrwtaylor\StackAgentThing;

// TODO
// http://www.lightandmatter.com/when/when.html
// Determine stack when response.

class When extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doWhen();
    }

    public function doWhen()
    {
        if (isset($this->file) and is_string($this->file)) {
            $this->readWhen($this->file);
        }

        if ($this->agent_input == null) {
            $this->when_message = $this->when_text; // mewsage?
        } else {
            $this->when_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is supportive of calendar.";
        $this->thing_report["help"] = "This is about seeing Events.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        //$this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeWeb()
    {
        $time_agent = new Time($this->thing, "time");

        $web = '</div>No calendar information available from when.</div>';
        if (isset($this->events)) {
            $web = "";
            foreach ($this->events as $event) {
                $web .=
                    '<div>' .
                    $time_agent->textTime($event->dtstart) .
                    " " .
                    $time_agent->textTime($event->dtend) .
                    " " .
                    $event->summary .
                    " " .
                    $event->description .
                    " " .
                    $event->location .
                    "</div>";
            }
        }
        $this->web = $web;
        $this->thing_report['web'] = $this->web;
    }

    function makeSMS()
    {
        $this->sms_message =
            "WHEN " . $this->response;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    public function readWhen($file)
    {
        if ($file == "") {return true;}
        $contents = file_get_contents($file);
        // TODO - Read When file.
    }

    public function readSubject()
    {
        $input = $this->subject;
        if (isset($this->agent_input) and $this->agent_input != "") {
            $input = $this->agent_input;
        }

        if ($input == 'when') {
            $input = $this->subject;
        }

        // https://stackoverflow.com/questions/9598665/php-replace-first-occurrence-of-string->
        $string = $input;
        $str_pattern = 'when';
        $str_replacement = '';

        if (strpos($string, $str_pattern) !== false) {
            $occurrence = strpos($string, $str_pattern);
            $filtered_input = substr_replace(
                $string,
                $str_replacement,
                strpos($string, $str_pattern),
                strlen($str_pattern)
            );
        }

        $filtered_input = trim($filtered_input);

        $this->file = $filtered_input;
        return false;
    }
}
