<?php
namespace Nrwtaylor\StackAgentThing;

/*

$/var/www/stackr.test
$composer require johngrogg/ics-parser

*/

use ICal\ICal;

// John Grogg (?) has built a nice ICal parser.
// Call it here (and only here) so the stack can read ICS.

/*
settings.php
        'stack' => [
...
        'calendar'=>['bob@example.com','mark@example.com'],
...
]

*/

class Calendar extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->default_calendar_token = null;
        $this->default_calendar_tokens = [];
        // So I could call
        if (isset($this->thing->container["stack"]["calendar"])) {
            if (is_string($this->thing->container["stack"]["calendar"])) {
                $this->default_calendar_token =
                    $this->thing->container["stack"]["calendar"];
                $this->default_calendar_tokens = [
                    $this->default_calendar_token
                ];
            }

            if (is_array($this->thing->container["stack"]["calendar"])) {
                $this->default_calendar_tokens =
                    $this->thing->container["stack"]["calendar"];
                $this->default_calendar_token =
                    $this->default_calendar_tokens[0];
            }
        }

        $this->default_interval = "6 week";

        if (isset($this->thing->container["api"]["calendar"])) {
            if (
                isset(
                    $this->thing->container["api"]["calendar"][
                        "default_interval"
                    ]
                )
            ) {
                $this->default_interval =
                    $this->thing->container["api"]["calendar"][
                        "default_interval"
                    ];
            }
        }

        // Looks in private/settings.php
        // Then it should resources/calendar
        // User setting?
        // If all else fails... ?

        $this->default_span = 2; // Default value
        $this->span = $this->default_span;
        $this->default_time_zone = "UTC";

        $this->time_zone = $this->default_time_zone;

        $this->thing->time_handler = new Time($this->thing, "time");
        if (is_string($this->thing->time_handler->time_zone)) {
            $this->time_zone = $this->thing->time_handler->time_zone;
        }

        //        $this->googlecalendar_agent = new Googlecalendar(
        //            $this->thing,
        //            "googlecalendar"
        //        );

        //        $this->alphanumeric_agent = new Alphanumeric(
        //            $this->thing,
        //            "alphanumeric"
        //        );

        $this->calendar_unique_events = false;

        $this->calendar = new \stdClass();
        if (!isset($this->calendar->events)) {
            $this->calendar->events = [];
        }
    }

    function run()
    {
        //        $this->doCalendar();
        //        $this->makeIcal();
    }

    public function descriptionCalendar($event)
    {
        $text = $event->description;

        // TODO read for call details.

        // TODO: Test with tag stripping and html_entity_decoding turned off.
        //

        $preprocess_text = false;
        if ($preprocess_text === true) {
            $description = strip_tags($text);
            $when_description = html_entity_decode($description);
        } else {
            $when_description = html_entity_decode($text);
            //$when_description = $text;
        }

        $when_description = str_replace(
            ["\n", "\t", "\r"],
            " ",
            $when_description
        );

        $troublesome_tokens = [""];

        foreach ($troublesome_tokens as $j => $troublesome_token) {
            if (stripos($when_description, $troublesome_token)) {
                $when_description = "Not readable.";
            }
            break;
        }

        // Protect URLS wrapped in <> from tag stripping.
        /*
$urls = $this->extractUrls($when_description);
        // Strip html tags.
foreach($urls as $u=>$url) {

$when_description = str_replace("<".$url.">", " ".$url." ",$when_description);

}
        // Strip html tags.

       $when_description = strip_tags(
            str_replace("<", " <", $when_description)
        );
*/

        $when_description = $this->stripHtml($when_description);

        // Strip repeating spaces.
        $when_description = preg_replace("/\s+/", " ", $when_description);
        // Strip repeating periods.
        $when_description = preg_replace(
            "/([.])\\1+/",
            "$1",
            $when_description
        );
        return $when_description;
    }

    public function extractCalendar($input)
    {
        // Identifiy UTC.
        $calendar = "Gregorian";

        if (stripos($input, "julian") !== false) {
            $calendar = "julian";
        }

        if (stripos(str_replace(".", "", $input), "BP") !== false) {
            $calendar = "BP";
        }

        return $calendar;
    }

    public function doCalendar()
    {
        $calendar_count = 0;
        if (isset($this->ics_links) and count($this->ics_links) != 0) {
            foreach ($this->ics_links as $ics_link) {
                if ($ics_link === true) {
                    continue;
                }
                if (strtolower($ics_link) === "x") {
                    continue;
                }
                if (strtolower($ics_link) === "z") {
                    continue;
                }
                if ($ics_link === null) {
                    continue;
                }
                if ($ics_link === false) {
                    continue;
                }

                $file = $ics_link;
                if (isset($file) and is_string($file) and $file !== "") {
                    $response = $this->readCalendar($file);
                    if ($response != true) {
                        $calendar_count += 1;
                    }
                }
            }
        }

        //foreach($this->calendar->events as $i=>$event) {

        //}

        usort($this->calendar->events, function ($first, $second) {
            return strtotime($first->start_at) > strtotime($second->end_at);
        });

        if ($calendar_count != 0) {
            $this->response .= "Read " . $calendar_count . " calendar(s). ";
        }

        //        if (isset($this->calendar)) {
        //            $this->calendar_text = $calendar['line'];
        //        }

        if ($this->agent_input == null) {
            if (!isset($this->calendar_text)) {
                $this->calendar_text = "No calendar text found. ";
            }
            $this->calendar_message = $this->calendar_text;
        } else {
            $this->calendar_message = $this->agent_input;
        }

        //        if ($this->agent_input == 'calendar') {
        //            $this->calendar_text = $calendar['line'];
        //        }
    }

    public function respondResponse()
    {
        //        $this->doCalendar();
        $this->makeIcal();

        $this->thing->flagGreen();

        $this->thing_report["info"] = "This is a calendar.";
        $this->thing_report["help"] = "This is about seeing Calendar Events.";
        $this->thing_report["message"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];

        //return $this->thing_report;
    }

    public function makeJson()
    {
        $json = $this->calendar->events;

        $this->thing_report["json"] = $json;
    }

    function makeWeb()
    {
        //$time_agent = new Time($this->thing, "time");

        $web = "</div>No calendar information available.</div>";
        if (isset($this->calendar->events)) {
            $web = "";
            foreach ($this->calendar->events as $event) {
                $timestamp = $this->textCalendar($event, ["timestamp"]);

                $web .=
                    "<div>" .
                    "<div>" .
                    $this->thing->time_handler->textTime($timestamp) .
                    " " .
                    $event->summary .
                    " [" .
                    $event->runtime .
                    "] " .
                    "</div><div>" .
                    $event->description .
                    "</div><div>" .
                    $event->location .
                    "</div></div>";
            }
        }

        $this->thing_report["info"] =
            "Times are " . $this->time_zone . ". Click refresh to update.";

        $this->web = $web;
        $this->thing_report["web"] = $this->web;
    }

    public function textCalendar($event, $parameters = null)
    {
        return $this->icsCalendar($event, $parameters);
    }

    public function icsCalendar($event, $parameters = null)
    {
        $default_parameters = [
            "timestamp",
            "timezone",
            "runtime",
            "summary",
            "description",
            "location",
        ];

        if ($parameters == null) {
            $parameters = $default_parameters;
        }

        $event_runtime = $this->thing->human_time(
            strtotime($event->dtend_tz) - strtotime($event->dtstart_tz)
        );

        $start_time = $event->dtstart;
        $event_calendar = $event->calendar_name;
        $event_timezone = $event->calendar_timezone;

        // Send the start time with the known event timezone.
        // Create a datum object.
        $datum = $this->thing->time_handler->datumTime(
            $event->dtstart,
            $event_timezone
        );

        // Get a timestamp of the datum.
        // In the specified timezone.
        // The timestamp is a text string which strtotime will recognize.
        // FALSE (default) - Do not include timezone string in the returned timestamp text.
        // TRUE - Include timezone of time in returned timestamp.
        $timestamp = $this->thing->time_handler->timestampTime(
            $datum,
            $this->thing->time_handler->time_zone
        );

        $event->timestamp = $timestamp;
        $event->timezone = $event_timezone;
        $event->runtime = $event_runtime;

        $calendar_text = "";
        foreach ($parameters as $i => $parameter) {
            // Allow settings to be passed with a parameter.
            // And recognize non-parameters ie text formatting.
            $setting = null;
            $parameter_tokens = explode(" ", $parameter, 2);

            if (in_array($parameter_tokens[0], $default_parameters)) {
                $parameter = $parameter_tokens[0];

                if (isset($parameter_tokens[1])) {
                    $setting = $parameter_tokens[1];
                }
            }

            if (in_array($parameter, $default_parameters)) {
                if ($event->{$parameter} == null) {
                    continue;
                }
            }
            if (isset($event->{$parameter})) {
                $t = $event->{$parameter};
                // Allow for request based timestamp formatting
                if ($parameter == "timestamp") {
                    if ($setting !== null) {
                        $t = date($setting, strtotime($t));
                    }
                }

                $calendar_text .= $t;
            } else {
                $calendar_text .= $parameter;
            }
        }
        return $calendar_text;
    }

    public function makeIcal()
    {
        $version = "X";
        $calendar_name = "X";
        $timezone = $this->time_zone;

        $arr = [
            "dtstart" => "start_at",
            "dtend" => "end_at",
            "dtstamp",
            "uid",
            "created",
            "description",
            "lastmodified",
            "location",
            "sequence",
            "status",
            "summary",
            "transp",
        ];

        $c = "BEGIN:VCALENDAR" . "\n";
        $c .=
            "PRODID:-//Stackr Interactive Ltd//Calendar " .
            $version .
            "//EN" .
            "\n";
        $c .= "VERSION:2.0" . "\n";

        // https://www.kanzaki.com/docs/ical/calscale.html
        $c .= "CALSCALE:GREGORIAN" . "\n";
        $c .= "METHOD:PUBLIC" . "\n";
        $c .= "X-WR-CALNAME:" . $calendar_name . "\n";
        $c .= "X-WR-TIMEONE:" . $timezone . "\n";

        if (isset($this->calendar->events)) {
            foreach ($this->calendar->events as $event) {
                $c .= "BEGIN:VEVENT" . "\n";

                $c .= "DTSTART:" . $event->start_at . "\n";
                $c .= "DTEND:" . $event->end_at . "\n";

                $dtstamp = "";
                if (isset($event->dtstamp)) {
                    $dtstamp = $event->dtstamp;
                }
                $c .= "DTSTAMP:" . $dtstamp . "\n";

                $uid = "";
                if (isset($event->uid)) {
                    $uid = $event->uid;
                }
                $c .= "UID:" . $uid . "\n";

                $created = "";
                if (isset($event->created)) {
                    $created = $event->created;
                }
                $c .= "CREATED:" . $created . "\n";

                $description = "";
                if (isset($event->description)) {
                    $description = $event->description;
                }
                $c .= "DESCRIPTION:" . $description . "\n";

                $lastmodified = "";
                if (isset($event->lastmodified)) {
                    $lastmodified = $event->lastmodified;
                }
                $c .= "LAST-MODIFIED:" . $lastmodified . "\n";

                $location = "";
                if (isset($event->location)) {
                    $location = $event->location;
                }
                $c .= "LOCATION:" . "\n";

                $c .= "SEQUENCE:0" . "\n";
                $c .= "STATUS:CONFIRMED" . "\n";
                $c .= "SUMMARY:" . $event->summary . "\n";
                $c .= "TRANSP:OPAQUE" . "\n";

                $c .= "END:EVENT" . "\n";
            }

            $c .= "END:VCALENDAR";
        }

        $this->ical = $c;
        $this->thing_report["ical"] = $c;
    }

    function makeSMS()
    {
        $calendar_text = "";

        if (isset($this->calendar->events)) {
            //$time_agent = new Time($this->thing, "time");

            $calendar_text = "";
            foreach ($this->calendar->events as $event) {
                $calendar_text .=
                    $this->textCalendar($event, [
                        "timestamp Y-m-d H:i",
                        " ",
                        "summary",
                        " ",
                        "[",
                        "runtime",
                        "]",
                        //                        "\n",
                        //                        'description',' ',"\n",
                        //                        'location',
                    ]) . "\n";
            }

            if (mb_strlen($calendar_text) > 140) {
                $calendar_text = "";
                foreach ($this->calendar->events as $event) {
                    $calendar_text .=
                        $this->textCalendar($event, [
                            "timestamp Y-m-d H:i",
                            " ",
                            //                           'timezone',
                            //                           ' ',
                            "summary",
                            " [",
                            "runtime",
                            "]",
                        ]) . "\n";
                }
            }
        }

        $this->node_list = ["calendar" => ["calendar", "dog"]];

        $sms =
            "CALENDAR " .
            $this->thing->time_handler->time_zone .
            "\n" .
            $calendar_text .
            "" .
            $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    function makeTXT()
    {
        //$time_agent = new Time($this->thing, "time");
        $calendar_text = "No calendar information available.";
        if (isset($this->calendar->events)) {
            $calendar_text = "";
            foreach ($this->calendar->events as $event) {
                $description_seperator = " - ";
                $description = "description";
                if ($this->description_flag != "on") {
                    $description_seperator = "";
                    $description = "";
                }

                $t =
                    $this->textCalendar($event, [
                        "timestamp Y-m-d H:i",
                        " ",
                        //                          'timezone',
                        //                          ' ',
                        "summary",
                        " [",
                        "runtime",
                        "]",
                        $description_seperator,
                        $description,
                    ]) . "\n";

                $calendar_text .= $t;
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
        $this->thing_report["txt"] = $txt;
    }

    function makeChoices()
    {
        $this->thing->choice->Create("channel", $this->node_list, "calendar");
        $choices = $this->thing->choice->makeLinks("calendar");
        $this->thing_report["choices"] = $choices;
    }

    public function eventCalendar($text = null)
    {
        try {
            // ICal is noisy at the WARNING and NOTICE level.
            // TODO ?

            set_error_handler(
                [$this, "calendar_warning_handler"],
                E_WARNING | E_NOTICE
            );

            // This is ignored because of the custom error handler in Agent.php
            // So use a custom error handler in this agent to handle WARNINGs and NOTICEs from the library.
            // $old_level = error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

            // Pass text to ICAl.

            $this->ical = new ICal($text, [
                "defaultSpan" => $this->default_span, // Default value
                "defaultTimeZone" => $this->time_zone,
                "defaultWeekStart" => "MO", // Default value
                "disableCharacterReplacement" => false, // Default value
                "filterDaysAfter" => null, // Default value
                "filterDaysBefore" => null, // Default value
                "skipRecurrence" => false, // Default value
            ]);
            $calendar_timezone = $this->ical->calendarTimeZone();
            restore_error_handler();

            // See note above.
            // error_reporting($old_level);
            // Test with the GitHub provided ics file.
        } catch (\Exception $e) {
            $this->response .= "Could not read event. ";
            return true;
        }
        $events = $this->ical->events();

        if (count($events) > 1) {
            $this->response .= "More than one event provided. ";
            return true;
        }

        $event = $events[0];

        $e = $event;

        //        $e->start_at = $e->dtstart;

        $t = $this->ical->iCalDateWithTimeZone(
            (array) $e,
            "dtstart",
            \DateTime::ATOM
        );
        $e->start_at = $t;

        //public function iCalDateToDateTime($icalDate)

        //        $e->end_at = $e->dtend;
        $t = $this->ical->iCalDateWithTimeZone(
            (array) $e,
            "dtend",
            \DateTime::ATOM
        );
        $e->end_at = $t;

        //            $e->calendar_name = $calendar_name;
        $e->calendar_timezone = $calendar_timezone;

        // Tidy up description.

        $description = $this->descriptionCalendar($event);
        $e->description = $description;

        $event = $e;

        $calendar_text = "";

        $calendar_text .=
            $event->summary .
            " " .
            $this->thing->time_handler->textTime($event->start_at) .
            " " .
            $this->thing->time_handler->textTime($event->end_at) .
            " " .
            $event->description .
            " " .
            $event->location .
            " / ";

        return $event;
    }

    public function readCalendar($calendar_uri, $calendar_name = null)
    {
        $this->calendar_unique_events === false;

        try {
            // ICal is noisy at the WARNING and NOTICE level.
            // TODO ?

            set_error_handler(
                [$this, "calendar_warning_handler"],
                E_WARNING | E_NOTICE
            );

            // This is ignored because of the custom error handler in Agent.php
            // So use a custom error handler in this agent to handle WARNINGs and NOTICEs from the library.
            // $old_level = error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
            $this->ical = new ICal($calendar_uri, [
                "defaultSpan" => $this->default_span, // Default value
                "defaultTimeZone" => $this->time_zone,
                "defaultWeekStart" => "MO", // Default value
                "disableCharacterReplacement" => false, // Default value
                "filterDaysAfter" => null, // Default value
                "filterDaysBefore" => null, // Default value
                "skipRecurrence" => false, // Default value
            ]);

            $this->thing->log(
                "Calendar readCalendar ICal created for " . $calendar_uri,
                "INFORMATION"
            );

            //$events = $this->ical->eventsFromInterval('6 weeks');

            //$this->default_interval = '10 weeks';
            $events = $this->ical->eventsFromInterval($this->default_interval);
            $calendar_timezone = $this->ical->calendarTimeZone();
            restore_error_handler();

            // See note above.
            // error_reporting($old_level);
            // Test with the GitHub provided ics file.

            // $ical->initUrl('https://raw.githubusercontent.com/u01jmg3/ics-parser/master/examples/ICal.ics>
        } catch (\Exception $e) {
            $this->response .=
                "Could not read calendar " . $calendar_uri . ". ";
            return true;
        }

        if ($this->calendar_unique_events === true) {
            $this->thing->log(
                "Saw an event in " .
                    $calendar_uri .
                    " without a unique reference."
            );
        }

        //$events = $ical->eventsFromInterval('1 week');
        //$calendar_timezone = $ical->calendarTimeZone();
        foreach ($events as $event) {
            // TODO - Recognize event structure.
            // For now assume it is Grogg object.
            //            $e = $this->eventCalendar($event);
            $e = $event;

            $e->start_at = $e->dtstart;
            $e->end_at = $e->dtend;

            $e->calendar_name = $calendar_name;
            $e->calendar_timezone = $calendar_timezone;

            // Tidy up description.

            $description = $this->descriptionCalendar($event);
            $e->description = $description;

            $this->calendar->events[] = $e;
        }

        // Sort events list by start time.
        // https://stackoverflow.com/questions/4282413/sort-array-of-objects-by-object-fields
        usort($this->calendar->events, function ($first, $second) {
            return strtotime($first->start_at) > strtotime($second->end_at);
        });

        //$time_agent = new Time($this->thing, "time");

        $calendar_text = "";
        foreach ($this->calendar->events as $event) {
            $calendar_text .=
                $event->summary .
                " " .
                $this->thing->time_handler->textTime($event->start_at) .
                " " .
                $this->thing->time_handler->textTime($event->end_at) .
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
        if (strtolower(substr($token, -4)) == ".ics") {
            $ics_links[] = $token;
            return $ics_links;
        }

        // See if Googlecalendar recognizes this.

        $addresses = $this->addressesGooglecalendar($token);

        if ($addresses !== false and $addresses !== true) {
            foreach ($addresses as $i => $address) {
                $ics_link = $this->icsGooglecalendar($address);
                if ($ics_link !== true) {
                    $ics_links[] = $ics_link;
                }
            }
            return $ics_links;
        }

        $ics_link = $this->icsGooglecalendar($token);

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
        //        $alphanumeric_agent = new Alphanumeric($this->thing, "alphanumeric");
        //        if ($alphanumeric_agent->isAlphanumeric($token)) {
        if ($this->isAlphanumeric($token)) {
            $ics_link = $this->icsGooglecalendar($token . "@gmail.com");
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
        if (strtolower($this->agent_input) == "calendar") {
            return;
        }

        $input = $this->subject;
        if (isset($this->agent_input) and $this->agent_input != "") {
            $input = $this->agent_input;
        }

        if ($this->agent_input == "dateline") {
            $dateline = $this->memoryAgent("Dateline");
        }

        $this->description_flag = "off";
        if (stripos($input, "description") !== false) {
            $pos = strpos($input, "description");
            if ($pos !== false) {
                $input = substr_replace(
                    $input,
                    " ",
                    $pos,
                    strlen("description")
                );
            }

            $this->description_flag = "on";
        }

        if ($input == "calendar") {
            $ics_links = [];

            $tokens = $this->default_calendar_tokens;
            //$new_ics_links = $this->icslinksCalendar($token);

            foreach ($tokens as $i => $token) {
                $new_ics_links = $this->icslinksCalendar($token);
                $ics_links = array_merge($ics_links, $new_ics_links);
            }

            $this->ics_links = array_unique($ics_links);

            //$this->ics_links = $new_ics_links;

            /*
            $e->dtstart = $this->current_time;
            $e->dtend = $e->dtstart;
            $e->summary = $dateline['line'];

            $this->calendar->events[] = $e;
*/
            $this->doCalendar();

            return;
        }
        // https://stackoverflow.com/questions/9598665/php-replace-first-occurrence-of-string->
        $filtered_input = $input;
        $string = $input;
        $str_pattern = "calendar";
        $str_replacement = "";

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
        $this->doCalendar();

        return false;
    }

    // ICal is a little noisy.
    // So create a custom warning handler here to response appropriately.
    function calendar_warning_handler(
        $errno,
        $errstr,
        $errfile,
        $errline,
        $errContext
    ) {
        //throw new \Exception('Class not found.');
        //trigger_error("Fatal error", E_USER_ERROR);
        $this->thing->log($errno);
        $this->thing->log($errstr);

        $console =
            "Calendar warning seen. " .
            $errline .
            " " .
            $errfile .
            " " .
            $errno .
            " " .
            $errstr .
            ". ";

        // Attempt to extract a useful reference to the problematic calendar.
        $calendar_name = "X";
        if (isset($this->ical->cal["VCALENDAR"]["X-WR-CALNAME"])) {
            $calendar_name = $this->ical->cal["VCALENDAR"]["X-WR-CALNAME"];
        }

        if (isset($errContext->filename)) {
            $calendar_name = $errContext->filename;
        }

        // Some big problem reading the calendar endpoint.
        if ($errno == 2) {
            throw new \Exception("Could not read calendar.");
        }

        if ($errno == 8) {
            // Flag that not all events have a unique id.
            // Might be useful later.
            $this->calendar_unique_events = true;
            return;
        }

        if ($this->stack_engine_state != "prod") {
            echo $console . "\n";
            $this->response .=
                "Unexpected calendar warning seen. " . $errstr . ". ";
        }
        // do something
    }
}
