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

/*
private/settings.php
            'when' => [
                'preferences_location'=>'/home/edna/.when/calendar',
                'calendar_location'=>'/home/edna/.when/calendar',
                'more'=>'on'
            ],



*/

class When extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->initWhen();
    }

    public function initWhen()
    {
        $this->days = ["mon", "tue", "wed", "thu", "fri", "sat", "sun"];
        $this->months = [
            "jan",
            "feb",
            "mar",
            "apr",
            "may",
            "jun",
            "jul",
            "aug",
            "sep",
            "oct",
            "nov",
            "dec",
        ];

        $this->preferences_location = null;
        if (
            isset(
                $this->thing->container["api"]["when"]["preferences_location"]
            )
        ) {
            $preferences =
                $this->thing->container["api"]["when"]["preferences_location"];
        }

        $this->calendar_location = null;
        if (
            isset($this->thing->container["api"]["when"]["calendar_location"])
        ) {
            $this->calendar_location =
                $this->thing->container["api"]["when"]["calendar_location"];
        }

        $this->calendar_list = null;

        $this->more_flag = "off";
        if (isset($this->thing->container["api"]["when"])) {
            if (isset($this->thing->container["api"]["when"]["more"])) {
                $this->more_flag =
                    $this->thing->container["api"]["when"]["more"];
            }
        }

        $file = $this->resource_path . "when/when.txt";

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

        if (!isset($this->thing->calendar_handler)) {
            $this->thing->calendar_handler = new Calendar(
                $this->thing,
                "calendar"
            );
        }
        $this->thing->calendar_handler->span = 10;
        $this->calendar_contents = file_get_contents($this->calendar_location);

        // Pull in the stack timezone.

        if (!isset($this->thing->time_handler)) {
            $this->thing->time_handler = new Time($this->thing, "time");
        }
        $this->time_zone = $this->thing->time_handler->time_zone;
        $this->thing->log("When init completed");
    }

    function run()
    {
    }

    public function calendarWhen($text = null, $name = null)
    {
        if ($text == null) {
            return true;
        }

        // Reset the calendar agent response.
        $this->thing->calendar_handler->response = "";
        // Reset the calendar unique events watch.
        // This variable indicates whether all the events in the calendar have a unique identity.
        $this->calendar_unique_events = false;

        $this->thing->log("When calendarWhen call readCalendar", "DEBUG");
        $this->thing->calendar_handler->readCalendar($text, $name);
        $this->thing->log(
            "When calendarWhen call readCalendar complete",
            "DEBUG"
        );
        $this->response .= $this->thing->calendar_handler->response;

        return $this->thing->calendar_handler->calendar->events;
    }

    public function dateWhen($text)
    {
        //https://stackoverflow.com/questions/2167916/convert-one-date-format-into-another-in-php
        // Format the date as When expects it.
        // $date = new \DateTime($text, new \DateTimeZone($this->time_zone));
        // Events from ICal parser should have timezone applied.
        // TODO: Test

        $date = new \DateTime($text);

        $response = $date->format("Y M d");
        return $response;
    }

    public function timeWhen($text)
    {
        // Events from ICal parser should have timezone applied.
        // TODO: Test
        $date = new \DateTime($text);
        //$date = new \DateTime($text, new \DateTimeZone($this->time_zone));
        $response = $date->format("H:i");

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
        } // Apparently also a possibility :|

        $runtime_text = $this->thing->human_time($runtime);

        return $runtime_text;
    }

    public function textWhen($event)
    {
        //$time_agent = new Time($this->thing, "time");

        $timestamp = $this->thing->calendar_handler->textCalendar($event, [
            "timestamp",
        ]);
        $timestamp = trim($timestamp);

        $runtime_text = $this->runtimeWhen(
            $event->dtstart_tz,
            $event->dtend_tz
        );

        if ($runtime_text != "") {
            $runtime_text = "[" . $runtime_text . "]";
        }

        $summary_text = $event->summary;
        if (
            strtolower($event->summary) == "busy" or
            strtolower($event->summary) == "available"
        ) {
            if (isset($event->calendar_name)) {
                $summary_text = $event->summary . " - " . $event->calendar_name;
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
        $this->thing->log("doWhen called");
        $events = [];
        if ($this->calendar_list === null) {
            $this->response .= "No calendars found. ";
            return true;
        }
        foreach ($this->calendar_list as $i => $calendar) {
            $ics_links = $this->thing->calendar_handler->icslinksCalendar(
                $calendar["ics_link"]
            );
            foreach ($ics_links as $j => $ics_link) {
                $new_events = $this->calendarWhen($ics_link, $calendar["name"]);
            }
        }

        $events = $this->thing->calendar_handler->calendar->events;

        $txt = "";
        foreach ($events as $i => $event) {
            $when_description = $this->thing->calendar_handler->descriptionCalendar(
                $event
            );

            if ($this->description_flag != "on") {
                $when_description = "";
            }
            $when_description = " " . $when_description;

            $txt .= $this->textWhen($event) . $when_description . "\n";
        }
        $this->when_text = $txt;

        if ($this->agent_input == null) {
            $this->when_message = $this->when_text;
        } else {
            $this->when_message = $this->agent_input;
        }

        $count = count($this->thing->calendar_handler->calendar->events);
        $this->response .= "Got " . $count . " events. ";
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is supportive of calendar.";
        $this->thing_report["help"] = "This is about seeing Events.";

        $this->thing_report["message"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];

        return $this->thing_report;
    }

    function makeWeb()
    {
        $web = "<div>No web output. Check the TXT channel.</div>";
        if (isset($this->events)) {
            $web = "";
            foreach ($this->events as $event) {
                $web .=
                    "<div>" .
                    $this->thing->time_handler->textTime($event->dtstart_tz) .
                    " " .
                    $this->thing->time_handler->textTime($event->dtend_tz) .
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
        $this->thing_report["web"] = $this->web;
    }

    function makeSMS()
    {
        $sms = "WHEN";
        if ($this->response != "") {
            $sms .= " | " . $this->response;
        }
        $sms .= "Text TXT. Or WEB.";

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeTXT()
    {
        $text = $this->calendar_contents;
        if (isset($this->when_text)) {
            $text .= $this->when_text;
        }
        $this->txt = $text;
        $this->thing_report["txt"] = $text;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
    }

    public function extractWhen($text)
    {
        $text = str_replace("event", "", strtolower($text));

        $tokens = explode(",", $text);
        if (count($tokens) == 2) {
            $timedate = trim($tokens[0]);
            $description = trim($tokens[1]);
        }
        $when_date = $this->dateWhen($timedate);
        $when_time = $this->timeWhen($timedate);
        $when = [
            "date" => $when_date,
            "time" => $when_time,
            "description" => $description,
        ];

        return $when;
    }

    public function test()
    {
        /*

Full test set from
http://www.lightandmatter.com/when/when.html

* dec 25 , Christmas
1920* aug 29 , Charlie Parker turns \a, born in \y
w=sun , go to church, 10:00
m=jan & w=mon & a=3 , Martin Luther King Day
* feb 14 , Valentine's Day
m=feb & w=mon & a=3 , Washington's Birthday observed
m=may & w=sun & a=2 , Mother's Day
m=may & w=mon & b=1 , Memorial Day
m=jun & w=sun & a=3 , Father's Day
* jul 4 , Independence Day
m=sep & w=mon & a=1 , Labor Day
m=oct & w=mon & a=2 , Columbus Day
m=oct & w=mon & a=2 , Thanksgiving (Canada)
* nov 11 , Armistice Day
m=nov & w=thu & a=4 , Thanksgiving (U.S.)
e=47 , Mardi Gras
e=46 , Ash Wednesday
e=7 , Palm Sunday
e=0 , Easter Sunday
e=0-49 , Pentecost (49 days after easter)
* jul 4 , Independence Day
m=jul & c=4 , Independence Day (observed as a federal holiday)
2010 apr 25 , 7:00 dinner at the anarcho-syndicalist commune
w=sun , 10:00 church
d=1 | d=15 , Pay employees.
w=sat & b=1 , Rehearse with band.
* dec 25 , Christmas
m=dec & d=25 , Christmas
w=fri & !(m=dec & d=25) , poker game

*/
    }

    public function readWhen($text)
    {
        if (file_exists($text)) {
            $this->response .= "Saw a reference to a file. ";
            $this->response .= "Did not do anything with it. ";
            return;
        }
        // http://www.lightandmatter.com/when/when.html
        $tokens = explode(",", $text);
        if (!isset($tokens[1])) {
            return true;
        }

        $date_input = trim($tokens[0]);
        $description = trim($tokens[1]);
        $dateline = $this->extractDateline($text);

        $date = $this->interpretWhen($date_input);
        if ($date === true) {
            $this->response .= "No When interpretation available. ";
        }

        $this->response .= "No When interpretation available. ";
        $this->response .= "Used stack date extractor. ";
        $this->response .=
            "Saw " . $dateline["dateline"] . " " . $description . ". ";
    }

    public function interpretWhen($text)
    {
        // TODO - Lots of work here to parse When parameter based language.
        return true;

        $text = trim($text); // Make sure

        // For now. Pass via At date extractor.
        // See if results are serviceable

        /*
The date has to be in year-month-day format, but you can either spell
       the month or give it as a number. (Month names are case-insensitive,
       and it doesn't matter if you represent February as F, Fe, Feb, Februa,
       or whatever.  It just has to be a unique match. You can give a trailing
       ., which will be ignored. In Czech, "cer" can be used as an
       abbreviation for Cerven, and "cec" for Cervenec.) Extra whitespace is
       ignored until you get into the actual text after the comma. Blank lines
       and lines beginning with a # sign are ignored.
*/

        // Not implemented.
        // Exploratory.
        // Needs to recognize
        /*
               left    %
               left    -
               left    < > <= >=
               left    = !=
               right   !
               left    &
               left    |
*/

        // Below exploratory. Not functional. Yet.
        // Need to reconsider token expansion to allow for symbols.

        // https://stackoverflow.com/questions/19347005/how-can-i-explode-and-trim-whitespace
        $tokens = array_map("trim", explode("&", $text)); //explode and trim

        // TODO - Above line ignores brackets. So needs to be reworked.

        if (!isset($this->parameters)) {
            $this->parameters = [];
        }

        foreach ($tokens as $i => $token) {
            $token = trim($token);
            $this->thing->log("token " . $token . " ", "INFORMATION");

            if (substr($token, 0, 2) === "!(") {
                // recurse.
                if (substr($token, 0, 2) === "!(") {
                    $token = substr($token, 2);
                } elseif (substr($token, 0, 1) === "(") {
                    $token = substr($token, 1);
                }

                $token = substr($token, 0, -1);

                // TODO recognize not

                $this->interpretWhen($token);
                continue;
            }

            $p = array_map("trim", explode("=", $token)); //explode and trim
            $key = $p[0];

            if (!isset($p[1])) {
                $value = true;
            } else {
                $value = $p[1];
            }

            // Consider if this is true --- with not case.
            if (isset($this->parameters[$key])) {
                $value = true;
            } // Can't have the same key overloaded.
            $this->parameters[$key] = $value;
        }
    }

    public function loadWhen($file)
    {
        if ($file == "") {
            return true;
        }
        $contents = file_get_contents($file);
        // TODO - Read When file.
    }

    public function updateWhen($line, $file = null)
    {
        if ($file == null) {
            $file = $this->calendar_location;
        }
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    public function readSubject()
    {
        $input = $this->subject;
        if (isset($this->agent_input) and $this->agent_input != "") {
            $input = $this->agent_input;
        }

        if ($input == "when") {
            $input = $this->subject;
        }

        if ($this->agent_input === "when") {
            return;
        }

        $this->description_flag = "off";
        if (stripos($input, "description") !== false) {
            $this->description_flag = "on";
        }

        // TODO - Read command line provided resource.
        // Pipe via Calendar.
        // https://stackoverflow.com/questions/9598665/php-replace-first-occurrence-of-string->
        $string = $input;
        $str_pattern = "when";
        $str_replacement = "";

        $filtered_input = "";
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

        if ($filtered_input == "") {
            $this->doWhen();

            return;
        }

        $this->readWhen($filtered_input);
        $this->doWhen();
    }
}
