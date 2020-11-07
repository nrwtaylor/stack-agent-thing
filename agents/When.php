<?php
namespace Nrwtaylor\StackAgentThing;

// TODO
// Determine stack when response.

/**
Integration with "when" - http://www.lightandmatter.com/when/when.html
cd ~/.when
nano preferences
prefilter = agent --channel=txt --meta=off when
**/

/*
Some sample resources
resources/when/when.txt example
webcal://cantonbecker.com/astronomy-calendar/astrocal.ics
https://www.officeholidays.com/ics/canada
https://www.calendarlabs.com/ical-calendar/ics/39/Canada_Holidays.ics
examplename
/home/bob/basic.ics
*/

class When extends Agent
{
    public $var = 'hello';

    function init()
    {
        $this->initWhen();
    }

    public function initWhen()
    {
        $this->preferences_location = null;
        if (
            isset(
                $this->thing->container['api']['when']['preferences_location']
            )
        ) {
            $preferences =
                $this->thing->container['api']['when']['preferences_location'];
        }

        $this->calendar_location = null;
        if (
            isset($this->thing->container['api']['when']['calendar_location'])
        ) {
            $this->calendar_location =
                $this->thing->container['api']['when']['calendar_location'];
        }

        $this->calendar_list = null;
        $file = $this->resource_path . 'when/when.txt';

        if (file_exists($file)) {
            $handle = fopen($file, "r");

            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (substr($line, 0, 1) == "#") {
                        continue;
                    }

                    $tokens = explode(",", $line);
                    $ics_link = trim($line);
                    $name = null;
                    if (count($tokens) == 2) {
                        $name = trim($tokens[0]);
                        $ics_link = trim($tokens[1]);
                    }

                    //$ics_link = trim($line);
                    $calendar = ["ics_link" => $ics_link, "name" => $name];
                    $this->calendar_list[] = $calendar;
                }
                fclose($handle);
            } else {
                // error opening the file.
            }
        }

        $this->calendar_agent = new Calendar($this->thing, "calendar");
        $this->calendar_agent->span = 10;
        $this->calendar_contents = file_get_contents($this->calendar_location);

        // Pull in the stack timezone.
        $this->time_agent = new Time($this->thing, "time");
        $this->time_zone = $this->time_agent->time_zone;
    }

    function run()
    {
        $this->doWhen();
    }

    public function calendarWhen($text = null, $name = null)
    {
        if ($text == null) {
            return true;
        }
        //$this->calendar_agent->events = [];
        $this->calendar_agent->readCalendar($text, $name);
        //echo $text ." ";
        //echo count($this->calendar_agent->events) . "\n";
        return $this->calendar_agent->calendar->events;
    }

    public function dateWhen($text)
    {
        //https://stackoverflow.com/questions/2167916/convert-one-date-format-into-another-in-php
        // Format the date as When expects it.
        // $date = new \DateTime($text, new \DateTimeZone($this->time_zone));
        // Events from ICal parser should have timezone applied.
        // TODO: Test

        $date = new \DateTime($text);

        $response = $date->format('Y M d');
        return $response;
    }

    public function timeWhen($text)
    {
        // Events from ICal parser should have timezone applied.
        // TODO: Test
        $date = new \DateTime($text);
        //$date = new \DateTime($text, new \DateTimeZone($this->time_zone));
        $response = $date->format('H:i');

        return $response;
    }
    public function runtimeWhen($start, $end)
    {
        $runtime = strtotime($end) - strtotime($start);
        if ($runtime == 0) {
            return "";
        }
        if ($runtime < 0) {
            return "";
        } // Apparenly also a possibility :|

        $runtime_text = $this->thing->human_time($runtime);

        return $runtime_text;
    }

    public function textWhen($event)
    {
        $time_agent = new Time($this->thing, "time");


        $timestamp = $this->calendar_agent->textCalendar($event, ['timestamp']);
        $timestamp =trim($timestamp);

        $runtime_text = $this->runtimeWhen($event->dtstart_tz, $event->dtend_tz);

        if ($runtime_text != "") {
            $runtime_text = '[' . $runtime_text . ']';
        }

        $summary_text = $event->summary;
        if (
            strtolower($event->summary) == "busy" or
            strtolower($event->summary) == "available"
        ) {
            if (isset($event->calendar_name)) {
                $summary_text = $event->summary . ' - ' . $event->calendar_name;
            }
        }

        $when_text =
            $this->dateWhen($timestamp) .
            ", " .
            $this->timeWhen($timestamp) .
            " " .
            $summary_text .
            " " .
            $runtime_text;

        return $when_text;
    }


    public function doWhen()
    {
        $events = [];
        foreach ($this->calendar_list as $i => $calendar) {

            $ics_links = $this->calendar_agent->icslinksCalendar(
                $calendar['ics_link']
            );
            foreach ($ics_links as $j => $ics_link) {
                $new_events = $this->calendarWhen($ics_link, $calendar['name']);
            }
        }

        $events = $this->calendar_agent->calendar->events;

        $txt = "";
        foreach ($events as $i => $event) {

            $txt .= $this->textWhen($event) . "\n";
        }
        $this->when_text = $txt;

        if ($this->agent_input == null) {
            $this->when_message = $this->when_text;
        } else {
            $this->when_message = $this->agent_input;
        }

        $count = count($this->calendar_agent->calendar->events);
        $this->response .= "Got " . $count . " events. ";
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is supportive of calendar.";
        $this->thing_report["help"] = "This is about seeing Events.";

        $this->thing_report['message'] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report['info'] = $message_thing->thing_report['info'];

        return $this->thing_report;
    }

    function makeWeb()
    {
        $time_agent = new Time($this->thing, "time");

        $web = '<div>No web output. Check the TXT channel.</div>';
        if (isset($this->events)) {
            $web = "";
            foreach ($this->events as $event) {
                $web .=
                    '<div>' .
                    $time_agent->textTime($event->dtstart_tz) .
                    " " .
                    $time_agent->textTime($event->dtend_tz) .
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
        $sms = "WHEN";
        if ($this->response != "") {
            $sms .= " | " . $this->response;
        }
        $sms .= "Text TXT. Or WEB.";

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function makeTXT()
    {
        $text = $this->calendar_contents;
        $text .= $this->when_text;
        $this->txt = $text;
        $this->thing_report['txt'] = $text;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report['choices'] = $choices;
    }

    public function extractWhen($text) {

        $text = str_replace("event","", strtolower($text));

        $tokens = explode(",", $text);
        if (count($tokens) == 2) {
            $timedate = trim($tokens[0]);
            $description = trim($tokens[1]);

        }
        //$this->when_date = $timedate;
        //$this->when_time = $timedate;


        $when_date = $this->dateWhen($timedate);
        $when_time = $this->timeWhen($timedate);
        $when = ['date'=>$when_date, 'time'=>$when_time, 'description'=>$description];

        return $when;

    }

    public function readWhen($file)
    {
        if ($file == "") {
            return true;
        }
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

/*
        // TODO - Read command line provided resource.
        // Pipe via Calendar.
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

        if (isset($filtered_input)) {
            $filtered_input = trim($filtered_input);
            $this->file = $filtered_input;
        }
*/
        return;
    }
}
