<?php
/**
 * Active.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Active extends Agent
{
    // This gets Forex from an API.
    // Give this the word active. Until another endpoint is also needed.

    public $var = "hello";

    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    public function init()
    {
        $this->keyword = "mordok";
        $this->keywords = ["active", "event", "show", "happening"];

        $this->api_key = $this->thing->container["api"]["activity_com_v2"];

        $this->run_time_max = 360; // 5 hours
    }

    /**
     *
     */
    public function set()
    {
        $this->variables_agent->setVariable("counter", $this->counter);
        $this->variables_agent->setVariable(
            "refreshed_at",
            $this->current_time
        );
    }

    /**
     *
     */
    public function get()
    {
        $this->variables_agent = new Variables(
            $this->thing,
            "variables " . "active" . " " . $this->from
        );

        $this->counter = $this->variables_agent->getVariable("counter");
        $this->refreshed_at = $this->variables_agent->getVariable(
            "refreshed_at"
        );

        $this->thing->log(" got counter " . $this->counter . ".", "DEBUG");

        $this->counter = $this->counter + 1;
    }

    /**
     *
     * @param unknown $sort_order (optional)
     * @return unknown
     */
    function getApi($sort_order = null)
    {
        $this->thing->log("getApi answered.");

        if (isset($this->events)) {
            return $this->events;
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

        $near = "&near=vancouver,ca";
        $city = "&city=vancouver";
        $country = "&country=canada";
        $query = "&query=" . $keywords;

        //$start_date = "&start_date=2013-07-04..";
        $start_date = "";
        $start_date = "&start_date=2018-09-01..";

        // events, races, tournaments, facilities, classes, leagues
        $category = "&category=event";
        $category = "";

        $per_page = "&per_page=50"; //max 50
        $sort = "&sort=date_desc"; //date_asc, date_desc, distance

        // Custom feed built in developer part of Brown Paper Tickets.
        $api_key = $this->api_key;
        //$data_source = "https://www.eventbriteapi.com/v3/events/search/?token=". $api_key . "&q=vancouver";
        //$data_source = "http://api.amp.active.com/search?{queryString params}&api_key=" . $api_key;
        $data_source =
            "http://api.amp.active.com/v2/search?query=running&category=event&start_date=2013-07-04..&near=San%20Diego,CA,US&radius=50&api_key=" .
            $api_key;
        $data_source =
            "http://api.amp.active.com/v2/search?query=running&category=event&start_date=2013-07-04.." .
            $city .
            $country .
            "&api_key=" .
            $api_key;
        //        $data_source = "http://api.amp.active.com/v2/search?". $query . "&category=event&start_date=2013-07-04.." . $city . $country ."&api_key=" . $api_key;

        $data_source =
            "http://api.amp.active.com/v2/search?" .
            $query .
            $category .
            $start_date .
            $city .
            $country .
            $per_page .
            $sort .
            "&api_key=" .
            $api_key;

        $data = file_get_contents($data_source);
        if ($data == false) {
            $this->response .= "Could not ask Active.com. ";
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
        //$events = json_decode(json_encode($data_xml), TRUE);
        $events = $json_data["results"];
        $this->eventsActivecom($events);

        $this->available_events_count = count($this->events);

        $this->thing->log(
            "getApi got " . $this->available_events_count . " available events."
        );

        $total_items = $json_data["total_results"];
        $page_number = $json_data["start_index"];

        $page_size = $json_data["items_per_page"];

        $this->thing->log(
            "read page " . $page_size . " of " . $total_items . " Event things."
        );

        $this->available_events_count = $total_items;

        return false;
    }

    /**
     *
     * @param array   $array
     * @return unknown
     */
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

    /**
     *
     * @param unknown $events
     */
    function eventsActivecom($events)
    {
        if (!isset($this->events)) {
            $this->events = [];
        }
        if ($events == null) {
            $this->events_count = 0;
            return;
        }

        foreach ($events as $not_used => $event) {
            $id = $event["assetGuid"];

            $event_name = $event["assetName"];
            if (!isset($event["assetDescriptions"][0])) {
                $description = "No description found.";
            } else {
                $description = $event["assetDescriptions"][0]["description"];
            }
            // devstack extract dates from description
            // resolve multi-day events

            $run_at = $event["activityStartDate"]; // local event time
            $end_at = $event["activityEndDate"]; // local event time

            // runtime not available.  Perhaps that is what the full day flag tells people
            $runtime = strtotime($end_at) - strtotime($run_at);
            if ($runtime <= 0) {
                $runtime = "X";
            }

            // Will need to run a venue request.

            if (!isset($event["organization"]["organizationName"])) {
                $organization = "Not provided";
            } else {
                $organization = $event["organization"]["organizationName"];
            }

            $venue_name = $event["place"]["placeName"] . " " . $organization;

            $venue_address = $event["place"]["addressLine1Txt"]; //$event['venue_address'];

            if (is_array($event["urlAdr"])) {
                $link = null;
            } else {
                $link = $event["urlAdr"];
            }

            // Extract activity recurrences
            // $event["activityRecurrences"]

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

    /**
     *
     * @param unknown $ref
     * @return unknown
     */
    function getLink($ref)
    {
        // Give it the message returned from the API service

        $this->link = "https://www.google.com/search?q=" . $ref;
        return $this->link;
    }

    /**
     *
     * @param unknown $event
     */
    public function makeEvent($event)
    {
        throw new Exception("Under construction.");

        // Need to check whether the events exists...
        // This can be post response.

        // devstack this will be an Event function
        // Just needs to pass the source to Event.

        // Load as new event things onto stack
        $thing = new Thing(null);
        $thing->Create(
            "active@stackr.ca",
            "events",
            "s/ event active " . $eventful_id
        );

        // make sure the right fields are directly given

        new Event($thing, "event is " . $event["event"]);
        new Runat($thing, "runat is " . $event["runat"]);
        new Place($thing, "place is " . $event["place"]);
        new link($thing, "link is " . $event["link"]);
    }

    /**
     *
     * @param unknown $variable_name (optional)
     * @param unknown $variable      (optional)
     * @return unknown
     */
    function getVariable($variable_name = null, $variable = null)
    {
        // This function does a minor kind of magic
        // to resolve between $variable, $this->variable,
        // and $this->default_variable.

        if ($variable != null) {
            // Local variable found.
            // Local variable takes precedence.
            return $variable;
        }

        if (isset($this->$variable_name)) {
            // Class variable found.
            // Class variable follows in precedence.
            return $this->$variable_name;
        }

        // Neither a local or class variable was found.
        // So see if the default variable is set.
        if (isset($this->{"default_" . $variable_name})) {
            // Default variable was found.
            // Default variable follows in precedence.
            return $this->{"default_" . $variable_name};
        }

        // Return false ie (false/null) when variable
        // setting is found.
        return false;
    }

    /**
     *
     */
    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();
        // Generate email response.

        $to = $this->thing->from;
        $from = "active";

        $choices = false;
        $this->thing_report["choices"] = $choices;

        $this->flag = "green";

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        }

        $this->thing_report["help"] =
            "This triggers provides currency prices using the 1forge API.";
    }

    /**
     *
     * @param unknown $event
     * @return unknown
     */
    public function eventString($event)
    {
        return $event["event"];
        // TODO This is too slow.
        return $this->textEvent($event);
    }

    public function deprecate_eventString($event)
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

    /**
     *
     */
    public function makeWeb()
    {
        // add registration
        // 'registrationUrlAdr'

        if (!isset($this->search_words)) {
            $s = "";
        } else {
            $s = $this->search_words;
        }

        $html = "<b>ACTIVE.COM " . $s . "</b>";
        $html .= "<p><b>Active.com Events</b>";

        if (!isset($this->events)) {
            $html .= "<br>No events found on Active.com.";
        } else {
            foreach ($this->events as $id => $event) {
                $event_html = $this->eventString($event);

                //$text =             $description = $event['assetDescriptions'][0]['description'];
                if (
                    !isset(
                        $event["datagram"]["assetDescriptions"][0][
                            "description"
                        ]
                    )
                ) {
                    $text = "";
                } else {
                    $text =
                        $event["datagram"]["assetDescriptions"][0][
                            "description"
                        ];
                }
                // https://stackoverflow.com/questions/36564293/extract-urls-from-a-string-using-php
                preg_match_all(
                    "#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#",
                    $text,
                    $match
                );

                $urls = $match[0];

                $html_link_extracted = "";
                if (count($urls) != 0) {
                    // Make a link to the Brown Paper Tickets page
                    $link = "https://www.brownpapertickets.com/event/" . $id;
                    $html_link = '<a href="' . $urls[0] . '">';
                    $html_link .= "link";
                    $html_link .= "</a>";

                    $html_link_extracted = $html_link;
                }
                // Get event link. Normally an artist/performer link.
                $link = $event["link"];

                if ($link != null) {
                    $scheme = parse_url($link, PHP_URL_SCHEME);
                    if (empty($scheme)) {
                        $link = "http://" . ltrim($link, "/");
                    }

                    $html_link_event = '<a href="' . $link . '">';
                    $html_link_event .= "active.com";
                    $html_link_event .= "</a>";
                } else {
                    $html_link_event = "";
                }

                $html .=
                    "<br>" .
                    $event_html .
                    " " .
                    $html_link_event .
                    " " .
                    $html_link_extracted;
            }
        }
        $this->html_message = $html;
    }

    /**
     *
     */
    public function makeSMS()
    {
        $sms = "ACTIVE";

        switch ($this->events_count) {
            case 0:
                $sms .= " | No events found.";
                break;
            case 1:
                /*
                $event = reset($this->events);
                $event_date = date_parse($event['runat']);

                $month_number = $event_date['month'];
                $month_name = date('F', mktime(0, 0, 0, $month_number, 10)); // March

                $simple_date_text = $month_name . " " . $event_date['day'];
*/
                /*
                $sms .= " "  . $simple_date_text;

                $sms .= " "  . $event['event'];

                $runat = new Runat($this->thing, $event['runat']);

                $sms .= " "  . $runat->day;
                $sms .= " "  . $runat->hour;
                $sms .= ":"  . $runat->minute;

                $sms .= " "  . $event['place'];
*/
                $event = reset($this->events);
                $event_html = $this->eventString($event);
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
                $event_html = $this->eventString($event);
                $sms .= " | " . $event_html;
        }

        $sms .= " | " . $this->response;

        // Really need to refactor this double :/
        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    /**
     *
     */
    public function makeMessage()
    {
        $message = "Active.com";

        switch ($this->events_count) {
            case 0:
                $message .= "did not find any events.";
                break;
            case 1:
                $event = reset($this->events);
                $event_html = $this->eventString($event);

                $message .= " found " . $event_html . ".";

                //if ($this->available_events_count != $this->events_count) {
                //    $message .= $this->events_count. " events retrieved.";
                //}

                break;
            default:
                $message .=
                    " found " . $this->available_events_count . " events.";
                //if ($this->available_events_count != $this->events_count) {
                //    $message .= $this->events_count. " retrieved";
                //}

                $event = reset($this->events);
                $event_html = $this->eventString($event);
                $message .= " This was one of them. " . $event_html . ".";
        }

        // $message .= " | " . $this->response;

        // Really need to refactor this double :/

        $this->message = $message;
    }

    /**
     *
     */
    private function thingreportEventful()
    {
        $this->thing_report["sms"] = $this->sms_message;
        $this->thing_report["web"] = $this->html_message;
        $this->thing_report["message"] = $this->message;
    }

    /**
     *
     * @param unknown $input (optional)
     * @return unknown
     */
    /*
    public function extractNumber($input = null) {
        if ($input == null) {$input = $this->subject;}

        $pieces = explode(" ", strtolower($input));

        // Extract number
        $matches = 0;
        foreach ($pieces as $key=>$piece) {

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
*/
    public function readActive()
    {
        $this->getApi();
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        //        $this->response = "Asked Eventful about events.";
        //        $this->response = null;

        $this->num_hits = 0;
        // Extract uuids into

        //$this->number = extractNumber();

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

        $haystack =
            $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == "active") {
                //$this->search_words = null;
                $this->response .= "Asked Active.com about events. ";
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
        if (($pos = strpos(strtolower($input), "active is")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("active is"));
        } elseif (($pos = strpos(strtolower($input), "active")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("active"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        if ($filtered_input != "") {
            $this->search_words = $filtered_input;
            $this->response .=
                "Asked Active.com about " . $this->search_words . " events. ";
            return false;
        }

        $this->response .= "Message not understood. ";
        return true;
    }
}
