<?php
namespace Nrwtaylor\StackAgentThing;

class Events extends Agent
{
    // Lots of work needed here.
    // Need to decide how to process events.

    public $var = "hello";

    function init()
    {
        $this->default_calendar_token = null;

        // So I could call
        if (isset($this->thing->container["stack"]["calendar"])) {
            $this->default_calendar_token =
                $this->thing->container["stack"]["calendar"];
        }

        $this->retain_for = 24; // Retain for at least 24 hours.
        $this->state = "dev";
    }

    // Add in code for setting the current distance travelled.
    function set()
    {
        // UK Commonwealth spelling
        $time_string = $this->thing->time();
        $this->thing->Write(
            ["events", "refreshed_at"],
            $time_string
        );
    }

    function get()
    {
        $time_string = $this->thing->Read([
            "events",
            "refreshed_at",
        ]);

        // Keep second level timestamp because I'm not
        // sure Stackr can deal with microtimes (yet).
        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["events", "refreshed_at"],
                $time_string
            );
        }
    }

    public function currentEvents()
    {
        /*
        $events_variable = ['text'=>time(),'refreshed_at'=>$this->current_time,'events'=>[]];

$events_variable['request_flag'] = false;


        $this->setMemory("events",$events_variable);



        $events = $events_variable['events'];

        return $events;
*/
        $events = [];
        // If we know the context we can pull in a useful event.
        // For example. current.
        $event_agent = new Event($this->thing, "event");
        if (
            stripos($this->subject . " " . $this->agent_input, "current") !==
            false
        ) {
            // Saw the word current somewhere.
            $dateline_agent = new Dateline(
                $this->thing,
                "dateline " . $this->subject . " " . $this->agent_input
            );

            $timestamp_agent = new Timestamp($this->thing, "timestamp");
            $start_time = time();
            $paragraphs = $dateline_agent->paragraphsDateline();
            $this->response .=
                "Got some useful paragraphs (" .
                $this->thing->human_time(time() - $start_time) .
                ") .";

            foreach ($paragraphs as $i => $paragraph) {
                $tokens = explode(" ", $paragraph);
                if (count($tokens) == 1) {
                    continue;
                }

                if ($paragraph == "") {
                    continue;
                }

                if ($timestamp_agent->hasTimestamp($paragraph) === false) {
                    continue;
                }

                $time_stamp = $timestamp_agent->extractTimestamp($paragraph);

                $filtered_paragraph = str_replace($time_stamp, "", $paragraph);
                $tokens = explode(" ", $filtered_paragraph);
                if (count($tokens) == 0) {
                    continue;
                }

                $event = $event_agent->parseEvent($paragraph);

                $events[] = $event;
            }
        }

        $events_variable = [
            "cache_request_flag" => false,
            "text" => time(),
            "refreshed_at" => $this->current_time,
            "events" => $events,
        ];

        $this->setMemory("events", $events_variable);

        //$this->setMemory("events", $events);

        return $events;
    }

    // -----------------------

    public function getClocktime()
    {
        $this->clocktime = new Clocktime($this->thing, "clocktime");
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thingreportEvents();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }
    }

    public function deprecate_eventString($event)
    {
        $event_date = date_parse($event["runat"]);
        $month_number = $event_date["month"];
        $month_name = date("F", mktime(0, 0, 0, $month_number, 10)); // March

        $simple_date_text = $month_name . " " . $event_date["day"];
        $event_string = "" . $simple_date_text;
        $event_string .= " " . $event["event"];

        $runat = new Runat($this->thing, "extract " . $event["runat"]);

        $event_string .= " " . $runat->day;
        $event_string .= " " . str_pad($runat->hour, 2, "0", STR_PAD_LEFT);
        $event_string .= ":" . str_pad($runat->minute, 2, "0", STR_PAD_LEFT);

        $run_time = new Runtime($this->thing, "extract " . $event["runtime"]);

        if ($event["runtime"] != "X") {
            $event_string .= " " . $this->thing->human_time($run_time->minutes);
        }

        $event_string .= " " . $event["place"];
        return $event_string;
    }

    function thingreportEvents()
    {
        $this->thing_report["message"] = $this->message;
        $this->thing_report["keyword"] = $this->keyword;
        $this->thing_report["sms"] = $this->sms_message;
        $this->thing_report["email"] = $this->message;
    }

    private function eventsDo()
    {
        // What does this do?
    }

    public function doEvents()
    {
        $this->earliest_event_string =
            "None of the events apis found anything. Which is weird, because there has to be something on.";

        // What is Events.
        $keywords = $this->search_words;

        $this->events = [];

        $calendar_agent = new Calendar($this->thing, "calendar");
        //        $calendar_agent->ics_links = $calendar_agent->icslinksCalendar(
        //            $this->default_calendar_token
        //        );

        //        $calendar_agent->ics_links = $calendar_agent->icslinksCalendar(
        //            $calendar_agent->default_calendar_token
        //        );

        $calendar_agent->doCalendar();

        //$this->calendar = new Calendar($this->thing, "calendar " . $keywords);
        if (isset($calendar_agent->calendar->events)) {
            $this->response .=
                "Counted " .
                count($calendar_agent->calendar->events) .
                " Calendar events. ";
            // TODO Process returned calendar events.
            //foreach ($calendar_agent->calendar->events as &$event) {
            //    $event['source'] = "calendar";
            //}

            //$this->events = array_merge($this->events, $this->calendar->events);
        }

        $this->eventful = new Eventful($this->thing, "eventful " . $keywords);
        if (isset($this->eventful->events)) {
            foreach ($this->eventful->events as &$event) {
                $event["source"] = "eventful";
            }

            $this->events = array_merge($this->events, $this->eventful->events);
        }

        $this->meetup = new Meetup($this->thing, "meetup " . $keywords);

        if (isset($this->meetup->events)) {
            foreach ($this->meetup->events as &$event) {
                $event["source"] = "meetup";
            }
            $this->events = array_merge($this->events, $this->meetup->events);
        }

        $this->brownpapertickets = new Brownpapertickets(
            $this->thing,
            "brownpapertickets " . $keywords
        );

        if (
            isset($this->brownpapertickets->events) and
            $this->brownpapertickets->events != true
        ) {
            foreach ($this->brownpapertickets->events as &$event) {
                $event["source"] = "brownpapertickets";
            }

            $this->events = array_merge(
                $this->events,
                $this->brownpapertickets->events
            );
        }

        $this->ticketmaster = new Ticketmaster(
            $this->thing,
            "ticketmaster " . $keywords
        );

        if (isset($this->ticketmaster->events)) {
            foreach ($this->ticketmaster->events as &$event) {
                $event["source"] = "ticketmaster";
            }

            $this->events = array_merge(
                $this->events,
                $this->ticketmaster->events
            );
        }

        $this->available_events_count = count($this->events);

        $this->thing->log("start sort");

        $runat = [];
        foreach ($this->events as $key => $row) {
            $runat[$key] = $row["runat"];
        }
        array_multisort($runat, SORT_ASC, $this->events);
        $this->thing->log("end sort");

        foreach ($this->events as $eventful_id => $event) {
            $event_name = $event["event"];
            $event_time = $event["runat"];
            $event_place = $event["place"]; // Doesn't presume the Rio

            $time_to_event =
                strtotime($event_time) - strtotime($this->current_time);
            if (!isset($time_to_earliest_event)) {
                $time_to_earliest_event = $time_to_event;
                $event_string = $this->eventful->eventString($event);
                $this->earliest_event_string =
                    $this->thing->human_time($time_to_earliest_event) .
                    " until " .
                    $event_string .
                    ".";
            } else {
                $this->response .= "Got the current Events.";
                if ($time_to_earliest_event > $time_to_event) {
                    $time_to_earliest_event = $time_to_event;
                    //                    $earliest_event_name = $event_name;
                    //                    $earliest_event_time = $event_time;
                    //                    $earliest_event_place = $event_place;
                    $event_string = $this->eventful->eventString($event);

                    $this->earliest_event_string =
                        $this->thing->human_time($time_to_earliest_event) .
                        " until " .
                        $event_string .
                        ".";

                    if ($time_to_event < 0) {
                        $this->earliest_event_string =
                            "About to happen. Happening. Or just happened. " .
                            $event_string .
                            ".";
                    }

                    $this->response .= "Got the next event. ";

                    $this->runat = new Runat(
                        $this->thing,
                        "runat " . $event_time
                    );

                    if ($this->runat->isToday($event_time)) {
                        $this->response .= "Got today's event. ";
                    }
                }
            }
        }

        //      $this->response = "Got the next Geekenders.";
    }

    public function getEvents()
    {
        $this->response .= "Get events. ";

        $events_variable = $this->getMemory("events");

        $age = 1e9;
        $cache_request_flag = false;
        if ($events_variable !== false) {
            if (isset($events_variable["refreshed_at"])) {
                $age =
                    strtotime($this->current_time) -
                    strtotime($events_variable["refreshed_at"]);
                $this->response .= "Age is " . $age . "s. ";
                $this->events_cache_age = $age;
            }

            $events = [];
            if (isset($events_variable["events"])) {
                $events = $events_variable["events"];
                if (isset($this->events) and $this->events != null) {
                    $events = array_merge($this->events, $events);
                }
            }

            $cache_request_flag = false;
            if (isset($events_variable["cache_request_flag"])) {
                $cache_request_flag = $events_variable["cache_request_flag"];
            }
        }

        $this->events_cache_request_flag = $cache_request_flag;
        if ($age > 60 and $cache_request_flag !== true) {
            //if (($events === false) or ($events == []) or (!isset($events['refreshed_at']>

            // How old is the cached item.
            $datagram = [
                "to" => "null" . $this->mail_postfix,
                "from" => "events",
                "subject" => "current events",
                "agent_input" => "current events",
            ];

            $this->thing->spawn($datagram);

            //tets
            //$events_agent = new Events($this->thing, "current events");

            //$events = $events_agent->currentEvents();

            $events_variable = $this->getMemory("events");

            $events_variable["cache_request_flag"] = true;
            $this->setMemory("events", $events_variable);

            if (!isset($events)) {
                $events = [];
            }
            if (isset($events_variable["events"])) {
                $events = array_merge($events_variable["events"], $events);
            }
        }
        return $events;
    }

    public function readSubject()
    {
        $this->response .= "Heard Events. ";
        $this->keyword = "events";

        if ($this->agent_input != null) {
            $input = strtolower($this->agent_input);
        } else {
            $input = strtolower($this->subject);
        }

        if ($input == "events") {
            $this->events = $this->getEvents();
            return;
        }

        if ($input == "current events") {
            $this->events = $this->currentEvents();
            return;
        }

        if (
            stripos(
                $this->subject . " " . $this->agent_input,
                "reset cache"
            ) !== false
        ) {
            $events_variable = $this->getMemory("events");

            $events_variable["cache_request_flag"] = false;

            $this->setMemory("events", $events_variable);

            return;
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "events is")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("events is"));
        } elseif (($pos = strpos(strtolower($input), "events")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("events"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->response .=
                "Asked Events about " . $this->search_words . " events. ";
            //return false;
        }

        $this->doEvents();

        //return $this->response;
    }

    public function makeMessage()
    {
        $message = "No message.";
        if (isset($this->eventful->message)) {
            $message = $this->eventful->message;
        }

        $this->message = $message;
        if (isset($this->earliest_event_string)) {
            $this->message = $this->earliest_event_string; //. ".";
        }

        $this->thing_report["message"] = $message;
    }

    public function makeSMS()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/events";

        $sms = "EVENTS ";

        if (isset($this->earliest_event_string)) {
            $sms .= " | " . $this->earliest_event_string;
        }

        $sms .= " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $this->sms_message;
    }

    public function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/events";

        $html = "<b>EVENTS WATCHER</b>";

        $html .= "<p>";

        $html .= '<br>Events watcher says , "';
        $html .= $this->sms_message . '"';

        $html .= "<p>";
        if (isset($this->events)) {
            $event_agent = new Event($this->thing, "event");
            foreach ($this->events as $id => $event) {
                //            $event_html = $this->eventString($event);
                $event_html = $event_agent->textEvent($event);
                if (isset($event["link"])) {
                    $link = $event["link"];

                    // https://stackoverflow.com/questions/8591623/checking-if-a-url-has-http-at-the-beginning-inserting-if-not
                    $parsed = parse_url($link);
                    if (empty($parsed["scheme"])) {
                        $link = "http://" . ltrim($link, "/");
                    }

                    $html_link = '<a href="' . $link . '">';
                    $event_source = "Unknown source.";
                    if (isset($event["source"])) {
                        $event_source = $event["source"];
                    }
                    $html_link .= $event_source;

                    $html_link .= "</a>";

                    $html .= "<p>" . $event_html . " " . $html_link;
                } else {
                    $html .= "<p>" . "No link found";
                }
            }
        }
        if (isset($this->events_cache_age)) {
            $html .= "<p>" . "Cache age is " . $this->events_cache_age . "s. ";
        }

        if (
            isset($this->events_cache_request_flag) and
            $this->events_cache_request_flag === true
        ) {
            $html .= "<p>" . "Cache request flag is TRUE. ";
        } else {
            $html .= "<p>" . "Cache request flag is NOT SET or NOT TRUE. ";
        }

        $this->web_message = $html;
        $this->thing_report["web"] = $html;
    }
}
