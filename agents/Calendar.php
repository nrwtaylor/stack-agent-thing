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

foreach($this->ics_links as $ics_link) {

//$file = $this->file;
$file = $ics_link;
        if (isset($file) and is_string($file)) {
            $this->readCalendar($file);
        }

}

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
        $this->thing_report["help"] = "This is about seeing Calendar Events.";

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

        $web = '</div>No calendar information available.</div>';
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
        $calendar_text = "";
        if (isset($this->events)) {
            $time_agent = new Time($this->thing, "time");

            $calendar_text = "";
            foreach ($this->events as $event) {

                $runtime = $this->thing->human_time(strtotime($event->dtend) - strtotime($event->dtstart));

                $calendar_text .=
                    $time_agent->textTime($event->dtstart) .
                    " " .
                    $runtime .
                    " " .
                    $event->summary .
                    " " .
                    $event->description .
                    " " .
                    $event->location .
                    "\n";
            }

            if (mb_strlen($calendar_text) > 140) {
                $calendar_text = "";
                foreach ($this->events as $event) {
                    $runtime = $this->thing->human_time(strtotime($event->dtend) - strtotime($event->dtstart));

                    $calendar_text .=
                        $time_agent->textTime($event->dtstart) .
                        " " .
                        $runtime .
                        " " .
                        $event->summary .
                        "\n";
                }
            }
        }

        $this->node_list = ["calendar" => ["calendar", "dog"]];
        $this->sms_message =
            "CALENDAR\n" . $calendar_text . "" . $this->response;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeTXT()
    {
        $time_agent = new Time($this->thing, "time");
        $calendar_text = "No calendar information available.";
        if (isset($this->events)) {
            $calendar_text = "";
            foreach ($this->events as $event) {
                $calendar_text .=
                    $time_agent->textTime($event->dtstart) .
                    " " .
                    $time_agent->textTime($event->dtend) .
                    " " .
                    $event->summary .
                    " " .
                    $event->description .
                    " " .
                    $event->location .
                    "\n ";
            }
        }
        //$this->node_list = ["calendar" => ["calendar", "dog"]];
        $txt = "CALENDAR\n\n" . $calendar_text . "\n\n" . $this->response;

        $this->txt = $txt;
        $this->thing_report['txt'] = $txt;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "calendar");
        $choices = $this->thing->choice->makeLinks('calendar');
        $this->thing_report['choices'] = $choices;
    }

    public function eventCalendar($arr = null) {
        // TODO Pass this through Event.
        // And then re-factor.
        $event = $arr;
        return $event;

    }

    public function readCalendar($file)
    {
        try {
            $ical = new ICal($file, [
                'defaultSpan' => 2, // Default value
                'defaultTimeZone' => 'UTC',
                'defaultWeekStart' => 'MO', // Default value
                'disableCharacterReplacement' => false, // Default value
                'filterDaysAfter' => null, // Default value
                'filterDaysBefore' => null, // Default value
                'skipRecurrence' => false, // Default value
            ]);

            $this->response .= 'Read calendar. ';

            // $ical->initFile('ICal.ics');
            // $ical->initUrl('https://raw.githubusercontent.com/u01jmg3/ics-parser/master/examples/ICal.ics>
        } catch (\Exception $e) {
            $this->response .= "Could not read calendar. ";
            return true;
        }

        $events = $ical->eventsFromInterval('1 week');

        if (!isset($this->events)) {$this->events = [];}

        foreach ($events as $event) {
            $this->events[] = $this->eventCalendar($event);
        }

        // Sort events list by start time.
        // https://stackoverflow.com/questions/4282413/sort-array-of-objects-by-object-fields
        usort($this->events,function($first,$second){
            return strtotime($first->dtstart) > strtotime($second->dtstart);
        });

        $time_agent = new Time($this->thing, "time");

        $calendar_text = "";
        foreach ($this->events as $event) {
            $calendar_text .=
                $event->summary .
                " " .
                $time_agent->textTime($event->dtstart) .
                " " .
                $time_agent->textTime($event->dtend) .
                " " .
                $event->description .
                " " .
                $event->location .
                " / ";
        }
        $this->calendar_text = $calendar_text;
    }

    public function readSubject()
    {
        $input = $this->subject;
        if (isset($this->agent_input) and $this->agent_input != "") {
            $input = $this->agent_input;
        }

        if ($input == 'calendar') {
            $input = $this->subject;
        }

        // https://stackoverflow.com/questions/9598665/php-replace-first-occurrence-of-string->
        $string = $input;
        $str_pattern = 'calendar';
        $str_replacement = '';

        if (stripos($string, $str_pattern) !== false) {
            $occurrence = strpos($string, $str_pattern);
            $filtered_input = substr_replace(
                $string,
                $str_replacement,
                strpos($string, $str_pattern),
                strlen($str_pattern)
            );
        }

        $filtered_input = trim($filtered_input);

        if (substr($filtered_input, -4) == '.ics') {
            $this->file = $filtered_input;
            $this->response .= "Saw file " . $this->file . ". ";
            return;
        }

$tokens = explode(" " ,$filtered_input);
$ics_links = [];
foreach($tokens as $i=>$token) {

        // See if Googlecalendar recognizes this.
        $googlecalendar_agent = new Googlecalendar(
            $this->thing,
            "googlecalendar"
        );
        $ics_link = $googlecalendar_agent->icsGooglecalendar($token);
$ics_links[] = $ics_link;
}
$this->ics_links = $ics_links;

//        if (is_string($ics_link)) {
//            $this->file = $ics_link;
//            return;
//        }

        return false;
    }
}
