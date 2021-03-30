<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Eventbrite extends Agent
{
    public $var = "hello";

    public function init()
    {
        $this->keywords = ["eventbrite", "event", "show", "happening"];

        $this->current_time = $this->thing->json->time();

        $this->api_key = null;
        if (isset($this->thing->container["api"]["eventbrite"]["api_key"])) {
            $this->api_key =
                $this->thing->container["api"]["eventbrite"]["api_key"];
        }

        // TODO
        // Eventbrite API needs to be updated to current API.
        // https://www.eventbrite.com/platform/api
        $this->api_key = null;

        // Functionality currently limited to recognized eventbrite links.

        $this->run_time_max = 360; // 5 hours
    }

    function set()
    {
        $this->thing->log(
            $this->agent_prefix . "set counter  " . $this->counter . ".",
            "DEBUG"
        );

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
            "variables " . "eventbrite" . " " . $this->from
        );

        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(" got counter " . $this->counter . ".", "DEBUG");

        $this->counter = $this->counter + 1;
    }

    function apiEventbrite($sort_order = null)
    {
        $this->thing->log("apiEventbrite answered.");

        if (isset($this->events)) {
            return $this->events;
        }

        if ($this->api_key == null) {
            $this->response .= "Did not ask Eventbrite about events.";
            return true;
        }

        if ($sort_order == null) {
            $sort_order = "popularity";
        }

        $city = "vancouver";
        // "America/Vancouver" apparently

        $keywords = "";
        if (isset($this->search_words)) {
            $keywords = $this->search_words;
        }

        $keywords = str_replace(" ", "%20%", $keywords);
        // https://www.eventbrite.ca/platform/docs/authentication
        $api_key = $this->api_key;
        $data_source =
            "https://www.eventbriteapi.com/v3/events/search/?token=" .
            $api_key .
            "&q=vancouver";
        $data = @file_get_contents($data_source);

        if ($data == false) {
            $this->response .= "Could not ask Eventbrite.";
            $this->available_events_count = 0;
            $this->events = true;
            $this->events_count = 0;
            $this->thing->log("did not get any events.");

            return true;
            // Invalid query of some sort.
        }

        //        $data_xml = simplexml_load_string($data);
        $json_data = json_decode($data, true);

        // devstack
        // https://stackoverflow.com/questions/6167279/converting-a-simplexml-object-to-an-array

        $events = $json_data["events"];

        $this->eventsEventbrite($events);

        $this->available_events_count = count($this->events);

        $this->thing->log(
            "apiEventbrite got " .
                $this->available_events_count .
                " available events."
        );

        $this->response .= "Asked Eventbrite about events.";

        return false;
    }

    // TODO Find a better home for this function

    function array_flatten(array $array)
    {
        $flat = []; // initialize return array
        $stack = array_values($array); // initialize stack
        while ($stack) {
            // process stack until done
            $value = array_shift($stack);
            if (is_array($value)) {
                // a value to further process
                $stack = array_merge(array_values($value), $stack);
            }
            // a value to take
            else {
                $flat[] = $value;
            }
        }
        return $flat;
    }

    function eventsEventbrite($events)
    {
        if (!isset($this->events)) {
            $this->events = [];
        }
        if ($events == null) {
            $this->events_count = 0;
            return;
        }

        foreach ($events as $not_used => $event) {
            $id = $event["id"];

            $event_name = $event["name"]["text"];

            $description = $event["description"]["text"];
            // devstack extract dates from description
            // resolve multi-day events

            $run_at = $event["start"]["local"]; // local event time
            $end_at = $event["end"]["local"]; // local event time

            // runtime not available.  Perhaps that is what the full day flag tells people
            $runtime = strtotime($end_at) - strtotime($run_at);
            if ($runtime <= 0) {
                $runtime = "X";
            }

            //if ($runtime > $this->run_time_max) {echo "meep";continue;}

            // Will need to run a venue request.
            $venue_name = null; //$event['venue_name'];

            $venue_address = null; //$event['venue_address'];

            if (is_array($event["url"])) {
                $link = null;
            } else {
                $link = $event["url"];
            }

            $event_array = [
                "event" => $event_name,
                "runat" => $run_at,
                "runtime" => $runtime,
                "place" => $venue_name,
                "link" => $link,
                "datagram" => $event,
            ];

            $pieces = $this->array_flatten($event_array, " ");

            if (!isset($this->search_words)) {
                $this->events[$id] = $event_array;
            } else {
                $keywords = explode(" ", $this->search_words);

                foreach ($pieces as $key => $phrase) {
                    $words = explode(" ", $phrase);
                    foreach ($words as $piece) {
                        foreach ($keywords as $command) {
                            if (
                                strpos(
                                    strtolower($piece),
                                    strtolower($command)
                                ) !== false
                            ) {
                                // Match found
                                $this->events[$id] = $event_array;
                            }
                        }
                    }
                }
            }
        }

        $this->events_count = count($this->events);
    }

    function getLink($ref = null)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.google.com/search?q=" . $ref;
        return $this->link;
    }

    public function makeEventbrite($event)
    {
        throw new Exception("Under construction.");

        // Need to check whether the events exists...
        // This can be post response.

        // devstack this will be an Event function
        // Just needs to pass the source to Event.

        // Load as new event things onto stack
        $thing = new Thing(null);
        $thing->Create(
            "eventbrite@stackr.ca",
            "events",
            "s/ event eventbrite " . $eventful_id
        );

        // make sure the right fields are directly given

        new Event($thing, "event is " . $event["event"]);
        new Runat($thing, "runat is " . $event["runat"]);
        new Place($thing, "place is " . $event["place"]);
        new Link($thing, "link is " . $event["link"]);
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->flag = "green";

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        //        $this->thingreportEventful();

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["help"] =
            "This handles Eventbrite related requests.";
    }

    public function textEventbrite($event)
    {
        $event_date = date_parse($event["runat"]);

        $month_number = $event_date["month"];
        $month_name = date("F", mktime(0, 0, 0, $month_number, 10)); // March

        $simple_date_text = $month_name . " " . $event_date["day"];
        $event_string = "" . $simple_date_text;
        $event_string .= " " . $event["event"];

        $runat = new Runat($this->thing, "extract " . $event["runtime"]);

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

    public function makeWeb()
    {
        if (!isset($this->search_words)) {
            $s = "";
        } else {
            $s = $this->search_words;
        }

        $html = "<b>EVENTBRITE " . $s . "</b>";
        $html .= "<p><b>Eventbrite Events</b>";

        if (!isset($this->events)) {
            $html .= "<br>No events found on Eventbrite.";
        } else {
            if ($this->events === true) {
                $html .= "No events found.";
            } else {
                foreach ($this->events as $id => $event) {
                    $event_html = $this->textEventbrite($event);
                    /*
            // Make a link to the Eventbrite page
            $link = "https://www.brownpapertickets.com/event/" . $id;
            $html_link = '<a href="' . $link . '">';
            $html_link .= "brown paper tickets";
            $html_link .= "</a>";

            $html_link_brownpapertickets = $html_link;
*/
                    // Get event link. Normally an artist/performer link.
                    $link = $event["link"];

                    if ($link != null) {
                        $scheme = parse_url($link, PHP_URL_SCHEME);
                        if (empty($scheme)) {
                            $link = "http://" . ltrim($link, "/");
                        }

                        $html_link_event = '<a href="' . $link . '">';
                        $html_link_event .= "eventbrite";
                        $html_link_event .= "</a>";
                    } else {
                        $html_link_event = "";
                    }

                    $html .= "<br>" . $event_html . " " . $html_link_event; // . " " . $html_link_brownpapertickets;
                }
            }
        }
        $this->html_message = $html;
        $this->thing_report["web"] = $html;
    }

    public function makeSMS()
    {
        $sms = "EVENTBRITE";
        if (isset($this->events_count)) {
            switch ($this->events_count) {
                case 0:
                    $sms .= " | No events found.";
                    break;
                case 1:
                    $event = reset($this->events);
                    $event_html = $this->textEventbrite($event);
                    $sms .= " | " . $event_html;

                    if ($this->available_events_count != $this->events_count) {
                        $sms .= $this->events_count . " retrieved";
                    }

                    break;
                default:
                    $sms .= " " . $this->available_events_count . " events ";
                    if ($this->available_events_count != $this->events_count) {
                        $sms .= $this->events_count . " retrieved";
                    }

                    $event = reset($this->events);
                    $event_html = $this->textEventbrite($event);
                    $sms .= " | " . $event_html;
            }
        } else {
            $sms .= " | No events found.";
        }
        $sms .= " | " . $this->response;

        // Really need to refactor this double :/
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function makeMessage()
    {
        $message = "Eventbrite";
        if (isset($this->events_count)) {
            switch ($this->events_count) {
                case 0:
                    $message .= "did not find any events.";
                    break;
                case 1:
                    $event = reset($this->events);
                    $event_html = $this->textEventbrite($event);

                    $message .= " found " . $event_html . ".";

                    break;
                default:
                    $message .=
                        " found " . $this->available_events_count . " events.";

                    $event = reset($this->events);
                    $event_html = $this->textEventbrite($event);
                    $message .= " This was one of them. " . $event_html . ".";
            }
        } else {
            $message .= "did not count any events.";
        }

        $this->message = $message;
    }

    public function urlEventbrite($text = null)
    {
        $urls = $this->extractUrls($text);

        foreach ($urls as $i => $url) {
            if (stripos($url, "https://www.eventbrite") !== false) {
                return $url;
            }
        }

        return false;
    }

    public function readEventbrite($text = null)
    {
        $this->url = $this->urlEventbrite($text);
    }

    public function isEventbrite($text)
    {
        if (stripos($text, "eventbrite") !== false) {
            return true;
        }
        // Contains word webex?
        return false;
    }

    public function readSubject()
    {
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

        $this->readEventbrite($input);

        $this->input = $input;

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == "eventbrite") {
                //$this->search_words = null;
                $this->apiEventbrite();

                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "run":
                            //     //$this->thing->log("read subject nextblock");
                            $this->runTrain();
                            break;

                        default:
                    }
                }
            }
        }

        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "eventbrite is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("eventbrite is")
            );
        } elseif (($pos = strpos(strtolower($input), "eventbrite")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("eventbrite")
            );
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->response .=
                "Asked Eventbrite about " . $this->search_words . " events";
            $this->apiEventbrite();

            return false;
        }

        // Empty input of some sort.

        $this->response .= "Message not understood";
        return true;
    }
}
