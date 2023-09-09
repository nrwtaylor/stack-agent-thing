<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Event extends Agent
{
    // This is an event.
    // TODO
    // Recognize text with a place and a time. (And optionally a runtime).

    public $var = "hello";

    public function init()
    {
        $this->keywords = [
            "event",
            "next",
            "accept",
            "clear",
            "drop",
            "add",
            "new",
            "stochastic",
            "random"
        ];

        $this->default_event_name =
            $this->thing->container["api"]["event"]["default_event_name"];
        $this->default_event_code =
            $this->thing->container["api"]["event"]["default_event_code"];

        $this->default_alias = "Thing";
        $this->current_time = $this->thing->time();

        $this->max_index = 0;

        $this->verbosity = 1;

        $this->test = "Development code"; // Always iterative.

        $this->day = "X";
        $this->hour = "X";
        $this->minute = "X";
        $this->minutes = "X";
        $this->event_code = "X";
        $this->refreshed_at = "X";

//$this->event_handler = new Events($this->thing, "events");

    }

    public function isEvent($text = null)
    {
        var_dump($text);
        $dateline = $this->extractDateline($text);
        var_dump("isEvent dateline");
        var_dump($dateline);

        if (
            $dateline["year"] === false &&
            $dateline["month"] === false &&
            $dateline["day"] === false &&
            $dateline["day_number"] === false &&
            $dateline["hour"] === false &&
            $dateline["minute"] === false
        ) {
            return false;
        }

// Expect...
// Not strange to put date at beginning.
   $parts = strtok($contents, ".");



        return true;
    }

    public function currentEvent()
    {
        // If we know the context we can pull in a useful event.
        // For example. current.
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

                $this->makeEvent(null, $paragraph);
                break;
            }
        }
    }

    public function readEvent($text = null)
    {
        // TODO Read event and extract when, place and runtime.
        if ($text == null) {
            return null;
        }

        // When format
        // date or config string, comma, text
        $tokens = explode(",", $text);
        if (count($tokens) == 2) {
            // Possibly a when structured event.
            $when_agent = new When($this->thing, "when");
            $when = $when_agent->extractWhen($text);

            $event = $when;
            $event["location"] = null;
            return $event;
        }

        $this->place_agent = new Place($this->thing, "place");
        $this->at_agent = new At($this->thing, "at");

        $this->place_agent->extractPlace($text);

        $this->at_agent->extractAt($text);
    }

    public function placetimeEvent($event)
    {
    }

    public function textEvent($event)
    {
        $event_string = "No at found. ";
        if (isset($event["runat"])) {
            $event_date = date_parse($event["runat"]);

            $month_number = $event_date["month"];
            $month_name = date("F", mktime(0, 0, 0, $month_number, 10)); // March

            $simple_date_text = $month_name . " " . $event_date["day"];
            $event_string = "" . $simple_date_text;
        }

        if (isset($event["event"])) {
            $event_string .= " " . $event["event"];
        }

        if (isset($event["runat"])) {
            $runat = new Runat($this->thing, "runat");

            $runat->extractRunat($event["runat"]);

            $event_string .= " " . $runat->day;
            $event_string .= " " . str_pad($runat->hour, 2, "0", STR_PAD_LEFT);
            $event_string .=
                ":" . str_pad($runat->minute, 2, "0", STR_PAD_LEFT);
        }

        if (isset($event["runtime"])) {
            //            $run_time = new Runtime(
            //                $this->thing,
            //                "extract " . $event['runtime']
            //            );

            $run_time = new Runtime($this->thing, "runtime");
            $run_time->extractRuntime($event["runtime"]);

            if ($event["runtime"] != "X") {
                $event_string .=
                    " " . $this->thing->human_time($run_time->minutes);
            }
        }
        if (isset($event["place"])) {
            $event_string .= " " . $event["place"];
        }

        return $event_string;
    }

    public function stochasticEvent()
    {
$this->response .= "Called stochasticEvent. ";
// Older pattern for test.
//$this->event_handler = new Events($this->thing, "events");

var_dump("stochasticEvent");
$raw_event_lines = $this->loadEvents();

$is_event = false;
$loop_count = 0;
while (!$is_event || $loop_count > 20) {

$random_raw_event = $raw_event_lines[array_rand($raw_event_lines, 1)];
var_dump($random_raw_event);

//exit();

$is_event = $this->isEvent($random_raw_event);
if ($is_event === false) {$random_raw_event = null;}
$loop_count += 1;
}

var_dump($random_raw_event);
var_dump($is_event);

// Make an event in the system.
if ($is_event) {
$this->makeEvent(null, $random_raw_event);
}

//exit();

//if ($is_event !== false) {

 $dateline = $this->extractDateline($random_raw_event);


var_dump($dateline);
/*
//}
//        $this->pleasant_words_list = array_map(
//            "strtolower",
//            $pleasant_words_list
//        );

$ts= $this->timestampDateline($dateline['dateline']);
var_dump($ts);
var_dump("exit loadEvents");
*/

$this->response .= "Stochastic event. " . $dateline['line'] . "[" . $this->timestampDateline($dateline['dateline']) . "] ";





        // Events
    }

    function set()
    {
        if ($this->agent_input == "extract") {
            return;
        }

        if (!isset($this->refreshed_at)) {
            $this->refreshed_at = $this->thing->time();
        }
        //$this->refreshed_at = $this->current_time;
        $event = new Variables($this->thing, "variables event " . $this->from);

        $event->setVariable("event_code", $this->event_code);
        $event->setVariable("event_name", $this->event_name);

        $event->setVariable("refreshed_at", $this->refreshed_at);

        $this->thing->log(
            $this->agent_prefix .
                " set " .
                $this->event_code .
                " and " .
                $this->event_name .
                ".",
            "INFORMATION"
        );

        $event = new Variables(
            $this->thing,
            "variables " . $this->event_code . " " . $this->from
        );
        $event->setVariable("event_name", $this->event_name);
        $event->setVariable("refreshed_at", $this->refreshed_at);

        $event = new \stdClass();

        if (!isset($this->events)) {
            $this->events = [];
        }

        $event->events = $this->events;
        $event->refreshed_at = $this->current_time;
    }

    function lastEvent()
    {
        $this->last_event = new Variables(
            $this->thing,
            "variables event " . $this->from
        );
        $this->last_event_code = $this->last_event->getVariable("event_code");
        $this->last_event_name = $this->last_event->getVariable("event_name");

        // This doesn't work
        $this->last_refreshed_at = $this->last_event->getVariable(
            "refreshed_at"
        );
    }

    function assertEvent($input)
    {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "event is")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("event is"));
        } elseif (($pos = strpos(strtolower($input), "event")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("event"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $event = $this->getEvent($filtered_input);
        if ($event) {
            //true so make a place
            $this->makeEvent(null, $filtered_input);
        }
    }

    function nextEvent()
    {
        $this->thing->log("next event");
        // Pull up the current headcode
        $this->get();

        // Find the end time of the headcode
        // which is $this->end_at

        // One minute into next headcode
        $quantity = 1;
        $next_time = $this->thing->time(
            strtotime($this->end_at . " " . $quantity . " minutes")
        );

        $this->get($next_time);

        // So this should create a headcode in the next quantity unit.

        return $this->available;
    }

    function getEvent($selector = null)
    {
        $this->response .= "Called getEvent with " . $selector . ". ";
        // TODO Get event by timestamp selector.
        $timestamp_agent = new Timestamp($this->thing, "timestamp");
        if ($timestamp_agent->isTimestamp($selector)) {
            $this->response .= "Timestamp selector provided. ";
        }
        $events_agent = new Events($this->thing, "events");
        $events = $events_agent->events;
        $this->response .= $events_agent->response;
        if (isset($events_agent->events_cache_age)) {
            $this->events_cache_age = $events_agent->events_cache_age;
        }

        if (isset($events_agent->events_cache_request_flag)) {
            $this->events_cache_request_flag =
                $events_agent->events_cache_request_flag;
        }

        $this->response .= "Counted " . count($events) . " events. ";

        foreach ($events as $event) {
            if ($timestamp_agent->isTimestamp($selector)) {
                if (isset($event["dateline"])) {
                    $t = $timestamp_agent->extractTimestamp($event["dateline"]);
                    $time_distance = strtotime($selector) - strtotime($t);
                    if ($time_distance < 0) {
                        continue;
                    }
                    if (
                        !isset($min_time_distance) or
                        $time_distance < $min_time_distance
                    ) {
                        $min_time_distance = $time_distance;
                        $best_event = $event;
                    }
                }
            }
            // Match the first matching place
            if ($selector == null or $selector == "") {
                $this->refreshed_at = $this->last_refreshed_at; // This is resetting the count.
                $this->event_name = $this->last_event_name;
                $this->event_code = $this->last_event_code;

                $this->getRuntime();
                $this->getRunat();

                //$this->day = "X";
                //$this->minutes = "X";
                //$this->hour = "X";
                //$this->minute = "X";

                $this->event = new Variables(
                    $this->thing,
                    "variables " . $this->event_code . " " . $this->from
                );
                return [$this->event_code, $this->event_name];
            }

            if ($event["code"] == $selector or $event["name"] == $selector) {
                $this->refreshed_at = $event["refreshed_at"];
                $this->event_name = $event["name"];
                $this->event_code = $event["code"];

                // Get the most recent value (that isn't X)
                if (!isset($this->day) or $this->day == "X") {
                    $this->day = $event["day"];
                }
                if (!isset($this->minutes) or $this->minutes == "X") {
                    $this->minutes = $event["minutes"];
                }
                if (
                    !isset($this->hour) or
                    !isset($this->minute) or
                    ($this->hour == "X" or $this->minute == "X")
                ) {
                    $this->hour = $event["hour"];
                    $this->minute = $event["minute"];
                }

                $this->event = new Variables(
                    $this->thing,
                    "variables " . $this->event_code . " " . $this->from
                );
                return [$this->event_code, $this->event_name];
            }
        }

        if (isset($best_event)) {
            $this->best_event = $best_event;
        }
        return true;
    }

    function getEvents()
    {
        $this->eventcode_list = [];
        $this->eventname_list = [];
        $this->events = [];

        // See if an event  record exists.
        $things = $this->getThings("event");
        if ($things == null) {
            return false;
        }
        $count = count($things);
        $this->thing->log(
            'Agent "Event" found ' . count($things) . " event Things."
        );

        if (!$this->is_positive_integer($count)) {
            // No places found
        } else {
            foreach (array_reverse($things) as $thing) {
                $uuid = $thing->uuid;
                $variables = $thing->variables;
                if (isset($variables["event"])) {
                    $event_code = $this->default_event_code;
                    $event_name = $this->default_event_name;
                    $refreshed_at = "meep getEvents";

                    if (isset($variables["event"]["event_code"])) {
                        $event_code = $variables["event"]["event_code"];
                    }
                    if (isset($variables["event"]["event_name"])) {
                        $event_name = $variables["event"]["event_name"];
                    }
                    if (isset($variables["event"]["refreshed_at"])) {
                        $refreshed_at = $variables["event"]["refreshed_at"];
                    }

                    if (isset($variables["runtime"]["minutes"])) {
                        $minutes = $variables["runtime"]["minutes"];
                    } else {
                        $minutes = "X";
                    }
                    if (isset($variables["runat"]["day"])) {
                        $day = $variables["runat"]["day"];
                    } else {
                        $day = "X";
                    }

                    if (isset($variables["runat"]["hour"])) {
                        $hour = $variables["runat"]["hour"];
                    } else {
                        $hour = "X";
                    }
                    if (isset($variables["runat"]["minute"])) {
                        $minute = $variables["runat"]["minute"];
                    } else {
                        $minute = "X";
                    }

                    /*
                    // Check if the place is already in the the list (this->places)
                    $found = false;
                    foreach(array_reverse($this->places) as $key=>$place) {

                        if ($place["name"] == $place_name) {
                            // Found place in list.  Don't add again.
                            $found = true;
                            break;
                        }
                    }

                    $found = false;

                    */
                    //                    if ($found == false) {

                    $this->events[] = [
                        "code" => $event_code,
                        "name" => $event_name,
                        "refreshed_at" => $refreshed_at,
                        "minutes" => $minutes,
                        "day" => $day,
                        "hour" => $hour,
                        "minute" => $minute,
                    ];
                    $this->eventcode_list[] = $event_code;
                    $this->eventname_list[] = $event_name;
                }
            }
        }

        // Return this-places filtered by latest check-in at each location.

        // Check if the place is already in the the list (this->places)
        $found = false;

        $filtered_events = [];
        foreach (array_reverse($this->events) as $key => $event) {
            $event_name = $event["name"];
            $event_code = $event["code"];

            $minutes = $event["minutes"];
            $day = $event["day"];
            $hour = $event["hour"];
            $minute = $event["minute"];

            if (!isset($event["refreshed_at"])) {
                continue;
            }

            $refreshed_at = $event["refreshed_at"];

            if (isset($filtered_events[$event_name]["refreshed_at"])) {
                if (
                    strtotime($refreshed_at) >
                    strtotime($filtered_events[$event_name]["refreshed_at"])
                ) {
                    $filtered_events[$event_name] = [
                        "name" => $event_name,
                        "code" => $event_code,
                        "refreshed_at" => $refreshed_at,
                        "minutes" => $minutes,
                        "day" => $day,
                        "hour" => $hour,
                        "minute" => $minute,
                    ];
                }
                continue;
            }

            $filtered_events[$event_name] = [
                "name" => $event_name,
                "code" => $event_code,
                "refreshed_at" => $refreshed_at,
                "day" => $day,
                "minutes" => $minutes,
                "hour" => $hour,
                "minute" => $minute,
            ];
        }

        $refreshed_at = [];
        foreach ($this->events as $key => $row) {
            $refreshed_at[$key] = $row["refreshed_at"];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->events);

        $this->old_events = $this->events;
        $this->events = [];
        foreach ($this->old_events as $key => $row) {
            if (strtotime($row["refreshed_at"]) != false) {
                $this->events[] = $row;
            }
        }

        // Add in a set of default places
        $file = $this->resource_path . "event/events.txt";

        if (file_exists($file) === false) {
            $this->response .= "No events resource found. ";
            return;
        }

        $contents = file_get_contents($file);
        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // process the line read.

                // It's just a list of place names.
                // Common ones.
                $event_name = $line;
                // This is where the place index will be called.
                $place_code = str_pad(RAND(1, 99999), 8, " ", STR_PAD_LEFT);

                $this->eventcode_list[] = $event_code;
                $this->eventname_list[] = $event_name;
                $this->events[] = [
                    "code" => $event_code,
                    "name" => $event_name,
                    "refreshed_at" => $this->start_time,
                    "minutes" => $minutes,
                    "day" => $day,
                    "hour" => $hour,
                    "minute" => $minute,
                ];
            }

            fclose($handle);
        } else {
            // error opening the file.
        }

        // Indexing not implemented
        $this->max_index = 0;

        return [$this->eventcode_list, $this->eventname_list, $this->events];
    }

    public function get($event_code = null)
    {
        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.
        if ($event_code == null) {
            if (isset($this->event_code)) {
                $event_code = $this->event_code;
            }

            if (!isset($this->event_code)) {
                if (!isset($this->last_event_code)) {
                    return true;
                }

                $event_code = $this->last_event_code;
            }
        }
        $this->event = new Variables(
            $this->thing,
            "variables " . $event_code . " " . $this->from
        );

        $this->event_code = $this->event->getVariable("event_code");
        $this->event_name = $this->event->getVariable("event_name");
        $this->refreshed_at = $this->event->getVariable("refreshed_at");

        $this->state = null; // to avoid error messages
        $this->lastEvent();

        return [$this->event_code, $this->event_name];
    }

    private function getRuntime()
    {
        $agent = new Runtime($this->thing, "runtime");

        $this->minutes = $agent->runtime;
    }

    private function getRunat()
    {
        $agent = new Runat($this->thing, "runat");

        if (isset($agent->day)) {
            $this->day = $agent->day;
        }
        if (isset($agent->hour)) {
            $this->hour = $agent->hour;
        }
        if (isset($agent->minute)) {
            $this->minute = $agent->minute;
        }
    }

    function dropEvent()
    {
        $this->thing->log($this->agent_prefix . "was asked to drop an Event.");

        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->event)) {
            $this->event->Forget();
            $this->event = null;
        }

        $this->get();
    }

    function nextCode()
    {
        $x = 0;
        while ($x <= 50) {
            $event_code = rand(1000000, 9999999);
            if ($this->existsEvent($event_code)) {
                continue;
            } else {
                break;
            }

            $x++;
        }

        return $event_code;
    }

    function parseEvent($text)
    {
        $event = [];
        $event["dateline"] = $text;
        $event["refreshed_at"] = $this->current_time;

        $event["name"] = $text;
        $event["code"] = "1212";

        return $event;
    }

    function existsEvent($event_candidate)
    {
        $events = [];
        if (isset($this->events)) {
            $events = $this->events;
        }
        foreach ($events as $event) {
            $event_code = strtolower($event["code"]);
            $event_name = strtolower($event["name"]);

            if (
                $event_code == $event_candidate or
                $event_candidate == null or
                $event_name == $event_candidate
            ) {
                return true;
            }
        }
        return false;
    }

    function makeEvent($event_code = null, $event_name = null)
    {
        if ($event_name == null) {
            return true;
        }

        $events = [];
        if (isset($this->events)) {
            $events = $this->events;
        }

        // See if the code or name already exists
        foreach ($events as $event) {
            if (
                $event_code == $event["code"] or
                $event_name == $event["name"]
            ) {
                $this->event_name = $event["name"];
                $event_code = $event["code"];
                $this->last_refreshed_at = $event["refreshed_at"];
            }
        }
        if ($event_code == null) {
            $event_code = $this->nextCode();
        }

        $this->thing->log(
            'Agent "Event" will make an Event for ' . $event_code . "."
        );

        $ad_hoc = true;
        $this->thing->log($this->agent_prefix . "is ready to make an Event.");
        if ($ad_hoc != false) {
            $this->thing->log($this->agent_prefix . "is making an Event.");
            $this->thing->log(
                $this->agent_prefix .
                    "was told the Event is okay but we might get kicked out."
            );

            // An event needs a runtime and a runat
            $runtime = new Runtime($this->thing, "extract");
            $runat = new Runat($this->thing, "extract");

            $minutes = "X";
            if (isset($runtime->minutes)) {
                $minutes = $runtime->minutes;
            }
            $this->minutes = $minutes;

            $this->day = $runat->day;
            $this->hour = $runat->hour;
            $this->minute = $runat->minute;

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            if ($event_code == false) {
                $event_code = $this->default_event_code;
                $event_name = $this->default_event_name;
            }

            $this->current_event_code = $event_code;
            $this->event_code = $event_code;

            $this->current_event_name = $event_name;
            $this->event_name = $event_name;
            $this->refreshed_at = $this->current_time;

            // This will write the refreshed at.
            $this->set();

            $this->getEvents();
            $this->getEvent($this->event_code);

            $this->event_thing = $this->thing;
        }
        $this->thing->log('Agent "Event" found an Event and pointed to it.');
    }

    function timeEvent($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $event_time = "x";
            return $event_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $event_time = $this->hour . $this->minute;

        if ($input == null) {
            $this->event_time = $event_time;
        }

        return $event_time;
    }

    public function extractEvents($input = null)
    {
        if (!isset($this->event_codes)) {
            $this->event_codes = [];
        }

        if (!isset($this->event_names)) {
            $this->event_names = [];
        }

        $pattern = "|\d{7}$|";

        preg_match_all($pattern, $input, $m);
        $this->event_codes = $m[0];

        if (!isset($this->events)) {
            $this->getEvents();
        }

        foreach ($this->events as $event) {
            $event_name = strtolower($event["name"]);
            $event_code = strtolower($event["code"]);

            if (empty($event_name)) {
                continue;
            }
            if (empty($event_code)) {
                continue;
            }

            if (strpos($input, $event_code) !== false) {
                $this->event_codes[] = $event_code;
            }

            if (strpos($input, $event_name) !== false) {
                $this->event_names[] = $event_name;
            }
        }

        $this->event_codes = array_unique($this->event_codes);
        $this->event_names = array_unique($this->event_names);

        return [$this->event_codes, $this->event_names];
    }

    public function extractEvent($input)
    {
        $this->event_name = null;
        $this->event_code = null;

        list($event_codes, $event_names) = $this->extractEvents($input);

        if (count($event_codes) + count($event_names) == 1) {
            if (isset($event_codes[0])) {
                $this->event_code = $event_codes[0];
            }
            if (isset($event_names[0])) {
                $this->event_name = $event_names[0];
            }

            $this->thing->log(
                $this->agent_prefix .
                    "found a event code (" .
                    $this->event_code .
                    ") in the text."
            );
            return [$this->event_code, $this->event_name];
        }

        return true;

        $event_names[] = $event_name;

        if (count($event_names) == 1) {
            $this->event_name = $this->event_names[0];
        }

        return [$this->event_code, $this->event_name];
    }

    function makeWeb()
    {
        $link = $this->web_prefix . "thing/" . $this->uuid . "/agent";

        $link_txt = $this->web_prefix . "thing/" . $this->uuid . "/event.txt";

        $this->node_list = ["event" => ["translink", "job"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "event"
        );
        $choices = $this->thing->choice->makeLinks("event");

        $web = "<b>Event Agent</b><br>";
        $web .=
            "This agent manages a uniquely numbered live event via text message.<br>";

        if (isset($this->best_event)) {
            $web .= "<br>Nearest event found.";
            $web .= "<br>" . $this->best_event["name"];
            $web .= "<p>";
        }

        $web .= "<br>event_name is " . $this->event_name . "";

        $event_code_text = "X";
        if (isset($this->event_code) and $this->event_code !== false) {
            $event_code_text = $this->event_code;
        }
        $web .= "<br>event_code is " . $event_code_text . "";

        $web .= "<br>" . $this->last_event_code;
        $web .= "<br>" . $this->last_event_name;
if ($this->isText($this->minutes)) {
        $web .= "<br>run_time is " . $this->minutes . " minutes";
}
        $web .=
            "<br>run_at is " .
            $this->day .
            " " .
            $this->hour .
            " " .
            $this->minute;

        $web .= "<br>sms message is ";
        $web .= $this->sms_message;
        $web .= "<br>";

        $link = $this->web_prefix . "thing/" . $this->uuid . "/event.txt";
        $web .= '<a href="' . $link . '">event.txt</a>';
        $web .= " | ";
        $link = $this->web_prefix . "thing/" . $this->uuid . "/event.log";
        $web .= '<a href="' . $link . '">event.log</a>';
        $web .= " | ";
        $link =
            $this->web_prefix .
            "thing/" .
            $this->uuid .
            "/" .
            $this->event_name;
        $web .= '<a href="' . $link . '">' . $this->event_name . "</a>";

        $web .= "<br>";
        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time(
            strtotime($this->thing->time()) - strtotime($this->refreshed_at)
        );
        $web .= "Last asserted about " . $ago . " ago.";

        $web .= "<br>";

        if (isset($this->events_cache_age)) {
            $web .= "<p>" . "Cache age is " . $this->events_cache_age . "s. ";
        }

        if (
            isset($this->events_cache_request_flag) and
            $this->events_cache_request_flag === true
        ) {
            $web .= "<p>" . "Cache request flag is TRUE. ";
        } else {
            $web .= "<p>" . "Cache request flag is NOT SET or NOT TRUE. ";
        }

        $this->thing_report["web"] = $web;
    }

    function addEvent()
    {
        $this->get();
    }

    function makeTXT()
    {
        if (!isset($this->eventcode_list)) {
            $this->getEvents();
        }
        $this->getEvents();

        if (!isset($this->event)) {
            $txt = "No events found.";
        } else {
            $txt = "These are EVENTS for RAILWAY " . $this->event->nuuid . ". ";
        }
        $txt .= "\n";
        //        $txt .= count($this->placecode_list). ' Place codes and names retrieved.';

        $txt .= "\n";

        //$txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
        $txt .= " " . str_pad("NAME", 40, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("CODE", 8, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        // Places must have both a name and a code.  Otherwise it's not a place.
        foreach ($this->events as $key => $event) {
            $txt .=
                " " .
                str_pad(strtoupper($event["name"]), 20, " ", STR_PAD_RIGHT);
            $txt .=
                " " .
                "  " .
                str_pad(strtoupper($event["code"]), 5, "X", STR_PAD_LEFT);
            $txt .=
                " " .
                "  " .
                str_pad(strtoupper($event["minutes"]), 5, "X", STR_PAD_LEFT);
            $txt .=
                " " .
                "  " .
                str_pad(strtoupper($event["day"]), 5, "X", STR_PAD_LEFT);
            $txt .=
                " " .
                "  " .
                str_pad(strtoupper($event["hour"]), 5, "X", STR_PAD_LEFT);
            $txt .=
                " " .
                "  " .
                str_pad(strtoupper($event["minute"]), 5, "X", STR_PAD_LEFT);

            //           $this->minutes = $runtime->minutes;
            //            $this->day = $runat->day;
            //            $this->hour = $runat->hour;
            //            $this->minute = $runat->minute;

            $txt .= "\n";
        }

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    function makeChoices()
    {
        $node_list = ["event" => ["going", "meh"]];

        $this->thing->choice->Create($this->agent_name, $node_list, "event");

        $this->choices = $this->thing->choice->makeLinks("event");
        $this->thing_report["choices"] = $this->choices;
    }

    public function makeSMS()
    {
        if (!isset($this->event_name) or $this->event_name == null) {
            $this->event_name = "None found";
            //$this->getEvent();
        }

        $sms_message = "EVENT";

        $sms_message .= " ";

        $sms_message .= $this->event_name;

        $event_code_text = "X";
        if (isset($this->event_code) and $this->event_code != false) {
            $event_code_text = trim($this->event_code);
        }
        $sms_message .= " " . $event_code_text;

        $minutes_text = "Set RUNTIME. ";
        if (isset($this->minutes) and $this->isText($this->minutes) and $this->minutes != false  ) {
            $minutes_text = "runtime " . $this->minutes . " minutes. ";
        }

        $sms_message .= " | " . $minutes_text;

        $run_at_text = "Set RUNAT. ";

        if (isset($this->day) or isset($this->hour) or isset($this->minute)) {
            if (isset($this->hour) and isset($this->minute)) {
                $run_at_text .= " ";
                $hour_text = str_pad($this->hour, 2, "0", STR_PAD_LEFT);
                $minute_text = str_pad($this->minute, 2, "0", STR_PAD_LEFT);
                $day_text = $this->day;

                $run_at_text =
                    "runat " .
                    $hour_text .
                    ":" .
                    $minute_text .
                    " " .
                    $day_text .
                    " ";
            }
        }

        if ($this->day == "X" or $this->hour == "X" or $this->minute == "X") {
            $run_at_text = "Set RUNAT. ";
        }

        $sms_message .= $run_at_text;
$sms_message .= $this->response;
        $this->sms_message = $sms_message;
        $this->thing_report["sms"] = $sms_message;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        // Allow for indexing.
        //if (!isset($this->index)) {
        //    $index = "0";
        //} else {
        //    $index = $this->index; //
        //}

        $this->makeChoices();

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        } else {
            $this->thing_report["info"] =
                'Agent input was "' . $this->agent_input . '".';
        }

        $this->thing_report["help"] =
            "This is a Event.  Somthing which happens someplace and sometime.";
    }

    function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    public function readSubject()
    {
        $this->num_hits = 0;

        $input = $this->agent_input;
        if ($this->agent_input == null or $this->agent_input == "") {
            $input = $this->subject;
        }

        if ($input == "event") {
            return;
        }

        $this->readEvent($input);

        $timestamp_agent = new Timestamp($this->thing, "timestamp");
        if ($timestamp_agent->isTimestamp($input)) {
            $this->getEvent($input);
            return;
        }

        if (stripos($input, "current") !== false) {
            $b = $this->currentEvent();
            return;
        }

        $prior_uuid = null;
        // Is there a place in the provided datagram

        $this->extractEvent($input);
        if ($this->agent_input == "extract") {
            return;
        }

        if ($this->agent_input == "vancouver pride 2018") {
            $this->makeEvent(500001, "vancouver pride 2018");
            $this->thing->console($this->event_name);
            return;
        }

        $this->last_event = new Variables(
            $this->thing,
            "variables event " . $this->from
        );
        $this->last_event_code = $this->last_event->getVariable("event_code");
        $this->last_event_name = $this->last_event->getVariable("event_name");
        $this->last_refreshed_at = $this->last_event->getVariable(
            "refreshed_at"
        );

        $pieces = explode(" ", strtolower($input));
        $prior_uuid = null;

        // Is there a place in the provided datagram
        $this->extractEvent($input);
        if ($this->agent_input == "extract") {
            return;
        }

        if (count($pieces) == 1) {
            if ($input == "event") {
                $this->getEvent();

                $this->getRunat();
                $this->getRuntime();

                $this->response .= "Last 'event' retrieved.";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
var_dump($command, $piece);
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "next":
                            $this->thing->log("read subject nextheadcode");
                            $this->nextEvent();
                            break;

                        case "drop":
                            $this->dropEvent();
                            break;
case "random":
case "stochastic":
case "s":
//var_dump("heard");
//exit();
$this->stochasticEvent();
//return;
break;
                        case "make":
                        case "new":
                        case "create":
                        case "add":
                        case "event":
                            $this->assertEvent(strtolower($input));

                            $this->getRunat();
                            $this->getRuntime();

                            if (empty($this->event_name)) {
                                $this->event_name = "X";
                            }

                            $this->response .=
                                "Asserted Event and found " .
                                strtoupper($this->event_name) .
                                ".";
      //                      return;
                            break;

                            $event_type = "4";

                            foreach (range(1, 9999999) as $n) {
                                foreach ($this->events as $event) {
                                    $event_code =
                                        $event_type .
                                        str_pad($n, 6, "0", STR_PAD_LEFT);

                                    if ($this->getEvent($event_code)) {
                                        // Code doesn't exist
                                        break;
                                    }
                                }
                                if ($n >= 9999) {
                                    $this->thing->log(
                                        "No Event code available of type " .
                                            $event_type .
                                            ".",
                                        "WARNING"
                                    );
///   break;    
                             return;
                                }
                            }

                            if ($this->place_name == null) {
                                $this->event_name =
                                    "Foo" . rand(0, 1000000) . "Bar";
                            }

                            $this->makeEvent(
                                $this->event_code,
                                $this->event_name
                            );
                            $this->getEvent($this->event_code);
                            return;

                        default:
                    }
                }
            }
        }

        if ($this->event_code != null) {
            $this->getEvent($this->event_code);
            $this->thing->log(
                $this->agent_prefix .
                    "using extracted event_code " .
                    $this->event_code .
                    ".",
                "INFORMATION"
            );
            $this->response .=
                $this->event_code . " used to retrieve an Event.";

            return;
        }

        if ($this->event_name != null) {
            $this->getEvent($this->event_name);

            $this->thing->log(
                $this->agent_prefix .
                    "using extracted event_name " .
                    $this->event_name .
                    ".",
                "INFORMATION"
            );
            $this->response .= strtoupper($this->event_name) . " retrieved.";
            $this->assertEvent($this->event_name);
            return;
        }

        if ($this->last_event_code != null) {
            $this->getEvent($this->last_event_code);
            $this->thing->log(
                $this->agent_prefix .
                    "using extracted last_event_code " .
                    $this->last_event_code .
                    ".",
                "INFORMATION"
            );
            $this->response .=
                "Last event " .
                $this->last_event_code .
                " used to retrieve an Event.";

            return;
        }

        // so we get here and this is null placename, null place_id.
        // so perhaps try just loading the place by name

        $event = strtolower($this->subject);

        if (!$this->getEvent(strtolower($event))) {
            // Event was found
            // And loaded
            $this->response .= $event . " used to retrieve an Event.";

            return;
        }

        $this->makeEvent(null, $event);
        $this->thing->log(
            $this->agent_prefix .
                "using default_event_code " .
                $this->default_event_code .
                ".",
            "INFORMATION"
        );

        $this->response .= "Made an Event called " . $event . ".";
        return;
        $this->last_event_code = $this->last_event->getVariable("event_code");

        if (
            $this->thing->isData($this->event_name) or
            $this->thing->isData($this->event_code)
        ) {
            $this->set();
            return;
        }

        return "Message not understood";

        return false;
    }

    function is_positive_integer($str)
    {
        return is_numeric($str) && $str > 0 && $str == round($str);
    }
}
