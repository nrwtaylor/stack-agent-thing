<?php
namespace Nrwtaylor\StackAgentThing;

use ICal\ICal;

class Calendar extends Agent
{
    public $var = 'hello';

    function init()
    {
    }

    function run()
    {
        $this->doCalendar();
    }

    public function doCalendar()
    {
        if ($this->agent_input == null) {
            $this->calendar_message = $this->calendar_text; // mewsage?
        } else {
            $this->calendar_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is a calendar.";
        $this->thing_report["help"] = "This is about seeings Events.";

        //$this->thing_report['sms'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message;
        $this->thing_report['txt'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeWeb()
    {
        $web = "";
        foreach ($this->events as $event) {
            $web .=
                '<div>' .
                $event->summary .
                " " .
                $event->dtstart .
                " " .
                $event->dtend .
                " " .
                $event->description .
                " " .
                $event->location .
                "</div>";

            $this->web = $web;
            $this->thing_report['web'] = $this->web;
        }
    }

    function makeSMS()
    {
        $this->node_list = ["calendar" => ["calendar", "dog"]];
        $this->sms_message =
            "CALENDAR | " . $this->calendar_message . " " . $this->response;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "calendar");
        $choices = $this->thing->choice->makeLinks('calendar');
        $this->thing_report['choices'] = $choices;
    }

    public function readSubject()
    {
        $input = $this->subject;
        if (isset($this->agent_input) and $this->agent_input != "") {
            $input = $this->agent_input;
        }

        // https://stackoverflow.com/questions/9598665/php-replace-first-occurrence-of-string->
        $string = $input;
        $str_pattern = 'calendar';
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

        $this->response .= "Saw file " . $this->file . ". ";
        //$contents = file_get_contents($this->file);
        //var_dump($contents);

        //$ical = new ICal('ICal.ics', arra
        try {
            $ical = new ICal($this->file, [
                'defaultSpan' => 2, // Default value
                'defaultTimeZone' => 'UTC',
                'defaultWeekStart' => 'MO', // Default value
                'disableCharacterReplacement' => false, // Default value
                'filterDaysAfter' => null, // Default value
                'filterDaysBefore' => null, // Default value
                'skipRecurrence' => false, // Default value
            ]);
            // $ical->initFile('ICal.ics');
            // $ical->initUrl('https://raw.githubusercontent.com/u01jmg3/ics-parser/master/examples/ICal.ics', $username = null, $password = null, $userAgent = null);
        } catch (\Exception $e) {
            $this->response .= "Failed to get calendar. ";
        }

        $this->events = $ical->eventsFromInterval('1 week');

        $calendar_text = "";
        foreach ($this->events as $event) {
            $calendar_text .=
                $event->summary .
                " " .
                $event->dtstart .
                " " .
                $event->dtend .
                " " .
                $event->description .
                " " .
                $event->location .
                " / ";
        }
        $this->calendar_text = $calendar_text;

        return false;
    }
}
