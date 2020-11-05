<?php
namespace Nrwtaylor\StackAgentThing;

use ICal\ICal;

class Calendar extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->default_span = 2; // Default value
        $this->span = $this->default_span;
        $this->default_time_zone = 'UTC';

        $this->time_zone = $this->default_time_zone;

        $this->time_agent = new Time($this->thing, "time");
        if (is_string($this->time_agent->time_zone)) {
            $this->time_zone = $this->time_agent->time_zone;
        }

        $this->googlecalendar_agent = new Googlecalendar(
            $this->thing,
            "googlecalendar"
        );

        $this->alphanumeric_agent = new Alphanumeric(
            $this->thing,
            "alphanumeric"
        );
    }

    function run()
    {
        $this->doCalendar();
    }

    public function doCalendar()
    {
        if (isset($this->ics_links)) {
            foreach ($this->ics_links as $ics_link) {
                $file = $ics_link;
                if (isset($file) and is_string($file) and $file !== "") {
                    $this->readCalendar($file);
                }
            }
        }

        if ($this->agent_input == null) {
            $this->calendar_message = $this->calendar_text;
        } else {
            $this->calendar_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is a calendar.";
        $this->thing_report["help"] = "This is about seeing Calendar Events.";
        $this->thing_report['message'] = $this->sms_message;

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
                $timestamp = $this->textCalendar($event, ['timestamp']);

                $web .=
                    '<div>' .
                    '<div>' .
                    $time_agent->textTime($timestamp) .
                    " " .
                    $event->summary .
                    ' [' .
                    $event->runtime .
                    '] ' .
                    '</div><div>' .
                    $event->description .
                    "</div><div>" .
                    $event->location .
                    "</div></div>";
            }
        }

        $this->thing_report['info'] =
            "Times are " . $this->time_zone . ". Click refresh to update.";

        $this->web = $web;
        $this->thing_report['web'] = $this->web;
    }

    public function textCalendar($event, $parameters = null)
    {
        if ($parameters == null) {
            $parameters = [
                'timestamp',
                'timezone',
                'runtime',
                'summary',
                'description',
                'location',
            ];
        }

        $event_runtime = $this->thing->human_time(
            strtotime($event->dtend_tz) - strtotime($event->dtstart_tz)
        );

        $start_time = $event->dtstart;
        $event_calendar = $event->calendar_name;
        $event_timezone = $event->calendar_timezone;

        // Send the start time with the known event timezone.
        // Create a datum object.
        $datum = $this->time_agent->datumTime($event->dtstart, $event_timezone);

        // Get a timestamp of the datum.
        // In the specified timezone.
        // The timestamp is a text string which strtotime will recognize.
        // FALSE (default) - Do not include timezone string in the returned timestamp text.
        // TRUE - Include timezone of time in returned timestamp.
        $timestamp = $this->time_agent->timestampTime(
            $datum,
            $this->time_agent->time_zone
        );

        $event->timestamp = $timestamp;
        $event->timezone = $event_timezone;
        $event->runtime = $event_runtime;

        $calendar_text = "";
        foreach ($parameters as $i => $parameter) {
            if (isset($event->{$parameter})) {
                $calendar_text .= $event->{$parameter};
            } else {
                $calendar_text .= $parameter;
            }
        }
        return $calendar_text;
    }

    function makeSMS()
    {
        $calendar_text = "";
        if (isset($this->events)) {
            $time_agent = new Time($this->thing, "time");

            $calendar_text = "";
            foreach ($this->events as $event) {
                $calendar_text .=
                    $this->textCalendar($event, [
                        'timestamp',
                        'runtime',
                        'summary',
                        'description',
                        'location',
                    ]) . "\n";
            }

            if (mb_strlen($calendar_text) > 140) {
                $calendar_text = "";
                foreach ($this->events as $event) {
                    $calendar_text .=
                        $this->textCalendar($event, [
                            'timestamp',
                            ' ',
                            //                           'timezone',
                            //                           ' ',
                            'summary',
                            ' [',
                            'runtime',
                            ']',
                        ]) . "\n";
                }
            }
        }

        $this->node_list = ["calendar" => ["calendar", "dog"]];
        $this->sms_message =
            "CALENDAR " .
            $this->time_agent->time_zone .
            "\n" .
            $calendar_text .
            "" .
            $this->response;
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
                    $this->textCalendar($event, [
                        'timestamp',
                        ' ',
                        //                          'timezone',
                        //                          ' ',
                        'summary',
                        ' [',
                        'runtime',
                        ']',
                    ]) . "\n";
            }
        }
        //$this->node_list = ["calendar" => ["calendar", "dog"]];
        $txt =
            "CALENDAR " .
            $this->time_zone .
            "\n\n" .
            $calendar_text .
            "\n\n" .
            $this->response;

        $this->txt = $txt;
        $this->thing_report['txt'] = $txt;
    }

    function makeChoices()
    {
        $this->thing->choice->Create('channel', $this->node_list, "calendar");
        $choices = $this->thing->choice->makeLinks('calendar');
        $this->thing_report['choices'] = $choices;
    }

    public function eventCalendar($arr = null)
    {
        // TODO Pass this through Event.
        // And then re-factor.
        $event = $arr;
        return $event;
    }

    public function readCalendar($file, $calendar_name = null)
    {
        try {
            // ICal is noisy at the WARNING and NOTICE level.
            // TODO ?
            $old_level = error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
            $ical = new ICal($file, [
                'defaultSpan' => $this->default_span, // Default value
                'defaultTimeZone' => $this->time_zone,
                'defaultWeekStart' => 'MO', // Default value
                'disableCharacterReplacement' => false, // Default value
                'filterDaysAfter' => null, // Default value
                'filterDaysBefore' => null, // Default value
                'skipRecurrence' => false, // Default value
            ]);
            error_reporting($old_level);
            // Test with the GitHub provided ics file.

            // $ical->initUrl('https://raw.githubusercontent.com/u01jmg3/ics-parser/master/examples/ICal.ics>
        } catch (\Exception $e) {
            $this->response .= "Could not read calendar " . $file . ". ";
            return true;
        }
        $events = $ical->eventsFromInterval('1 week');

        if (!isset($this->events)) {
            $this->events = [];
        }

        $calendar_timezone = $ical->calendarTimeZone();

        foreach ($events as $event) {
            $e = $this->eventCalendar($event);
            $e->calendar_name = $calendar_name;
            $e->calendar_timezone = $calendar_timezone;
            //            $this->events[] = $this->eventCalendar($event);
            $this->events[] = $e;
        }

        // Sort events list by start time.
        // https://stackoverflow.com/questions/4282413/sort-array-of-objects-by-object-fields
        usort($this->events, function ($first, $second) {
            return strtotime($first->dtstart_tz) >
                strtotime($second->dtstart_tz);
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

    public function icslinksCalendar($token)
    {
        if (strtolower(substr($token, -4)) == '.ics') {
            $ics_links[] = $token;
            return $ics_links;
        }

        // See if Googlecalendar recognizes this.

        $addresses = $this->googlecalendar_agent->addressesGooglecalendar(
            $token
        );

        if ($addresses !== false) {
            foreach ($addresses as $i => $address) {
                $ics_link = $this->googlecalendar_agent->icsGooglecalendar(
                    $address
                );
                if ($ics_link !== true) {
                    $ics_links[] = $ics_link;
                }
            }
            return $ics_links;
        }

        $ics_link = $this->googlecalendar_agent->icsGooglecalendar($token);

        if ($ics_link !== true) {
            $ics_links[] = $ics_link;
            return $ics_links;
        }

        // Some ics links don't end in .ics
        // TODO Test
        if (strtolower(substr($token, 0, 9)) == "webcal://") {
            $ics_links[] = $token;
            return $ics_links;
        }

        // Assume alphanumeric tokens are calls for @gmail addresses.
        // For now.
        // TODO: Explore Apple and Microsoft calendaring.
        $alphanumeric_agent = new Alphanumeric($this->thing, "alphanumeric");
        if ($alphanumeric_agent->isAlphanumeric($token)) {
            $ics_link = $this->googlecalendar_agent->icsGooglecalendar(
                $token . "@gmail.com"
            );
            if ($ics_link !== true) {
                $ics_links[] = $ics_link;
                return $ics_links;
            }
        }
        $ics_links[] = $token;

        // And some don't have anything distinctive.
        // https://www.officeholidays.com/ics/canada/british-columbia
        // Can not rely on the link having ics in it.
        // TODO Identify and store non ics links.
        // For now add to the list to try and read it as a calendar.
        return $ics_links;
    }

    public function readSubject()
    {
        if (strtolower($this->agent_input) == 'calendar') {
            return;
        }

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

        $tokens = explode(" ", $filtered_input);
        $ics_links = [];

        foreach ($tokens as $i => $token) {
            $new_ics_links = $this->icslinksCalendar($token);
            $ics_links = array_merge($ics_links, $new_ics_links);
        }

        $this->ics_links = array_unique($ics_links);

        return false;
    }
}
