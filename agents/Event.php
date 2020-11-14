<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Event extends Agent
{
    // This is an event.
    // TODO
    // Recognize text with a place and a time. (And optionally a runtime).

    public $var = 'hello';

    public function init()
    {
        $this->keywords = [
            'event',
            'next',
            'accept',
            'clear',
            'drop',
            'add',
            'new',
        ];

        $this->default_event_name =
            $this->thing->container['api']['event']['default_event_name'];
        $this->default_event_code =
            $this->thing->container['api']['event']['default_event_code'];

        $this->default_alias = "Thing";
        $this->current_time = $this->thing->json->time();

        $this->verbosity = 1;

        $this->test = "Development code"; // Always iterative.
        //        $this->initEvent();
    }

    public function currentEvent()
    {
        // If we know the context we can pull in a useful event.
        // For example. current.
        if (
            stripos($this->subject . " " . $this->agent_input, 'current') !==
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
            //exit();
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
            $event['location'] = null;
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
    }

    public function loadEvents()
    {
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
                ' set ' .
                $this->event_code .
                ' and ' .
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
    }

    function lastEvent()
    {
        $this->last_event = new Variables(
            $this->thing,
            "variables event " . $this->from
        );
        $this->last_event_code = $this->last_event->getVariable('event_code');
        $this->last_event_name = $this->last_event->getVariable('event_name');

        // This doesn't work
        $this->last_refreshed_at = $this->last_event->getVariable(
            'refreshed_at'
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
        $next_time = $this->thing->json->time(
            strtotime($this->end_at . " " . $quantity . " minutes")
        );

        $this->get($next_time);

        // So this should create a headcode in the next quantity unit.

        return $this->available;
    }

    function getEvent($selector = null)
    {
        foreach ($this->events as $event) {
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

            if ($event['code'] == $selector or $event['name'] == $selector) {
                $this->refreshed_at = $event['refreshed_at'];
                $this->event_name = $event['name'];
                $this->event_code = $event['code'];

                // Get the most recent value (that isn't X)
                if (!isset($this->day) or $this->day == "X") {
                    $this->day = $event['day'];
                }
                if (!isset($this->minutes) or $this->minutes == "X") {
                    $this->minutes = $event['minutes'];
                }
                if (
                    !isset($this->hour) or
                    !isset($this->minute) or
                    ($this->hour == "X" or $this->minute == "X")
                ) {
                    $this->hour = $event['hour'];
                    $this->minute = $event['minute'];
                }

                $this->event = new Variables(
                    $this->thing,
                    "variables " . $this->event_code . " " . $this->from
                );
                return [$this->event_code, $this->event_name];
            }
        }

        return true;
    }

    function getEvents()
    {
        $this->eventcode_list = [];
        $this->eventname_list = [];
        $this->events = [];

        // See if an event  record exists.
        $things = $this->getThings('event');

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
                if (isset($variables['event'])) {
                    $event_code = $this->default_event_code;
                    $event_name = $this->default_event_name;
                    $refreshed_at = "meep getEvents";

                    if (isset($variables['event']['event_code'])) {
                        $event_code = $variables['event']['event_code'];
                    }
                    if (isset($variables['event']['event_name'])) {
                        $event_name = $variables['event']['event_name'];
                    }
                    if (isset($variables['event']['refreshed_at'])) {
                        $refreshed_at = $variables['event']['refreshed_at'];
                    }

                    if (isset($variables['runtime']['minutes'])) {
                        $minutes = $variables['runtime']['minutes'];
                    } else {
                        $minutes = "X";
                    }
                    if (isset($variables['runat']['day'])) {
                        $day = $variables['runat']['day'];
                    } else {
                        $day = "X";
                    }

                    if (isset($variables['runat']['hour'])) {
                        $hour = $variables['runat']['hour'];
                    } else {
                        $hour = "X";
                    }
                    if (isset($variables['runat']['minute'])) {
                        $minute = $variables['runat']['minute'];
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
                    //                  }
                }
            }
        }

        // Return this-places filtered by latest check-in at each location.

        // Check if the place is already in the the list (this->places)
        $found = false;

        $filtered_events = [];
        foreach (array_reverse($this->events) as $key => $event) {
            $event_name = $event['name'];
            $event_code = $event['code'];

            $minutes = $event['minutes'];
            $day = $event['day'];
            $hour = $event['hour'];
            $minute = $event['minute'];

            if (!isset($event['refreshed_at'])) {
                continue;
            }

            $refreshed_at = $event['refreshed_at'];

            if (isset($filtered_events[$event_name]['refreshed_at'])) {
                if (
                    strtotime($refreshed_at) >
                    strtotime($filtered_events[$event_name]['refreshed_at'])
                ) {
                    $filtered_events[$event_name] = [
                        "name" => $event_name,
                        "code" => $event_code,
                        'refreshed_at' => $refreshed_at,
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
                'refreshed_at' => $refreshed_at,
                "day" => $day,
                "minutes" => $minutes,
                "hour" => $hour,
                "minute" => $minute,
            ];
        }

        $refreshed_at = [];
        foreach ($this->events as $key => $row) {
            $refreshed_at[$key] = $row['refreshed_at'];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->events);

        /*
// Get latest per place
$this->places = array();
foreach($filtered_places as $key=>$filtered_place) {
//var_dump($filtered_place);

        $this->places[] = $filtered_place;
}
*/
        $this->old_events = $this->events;
        $this->events = [];
        foreach ($this->old_events as $key => $row) {
            if (strtotime($row['refreshed_at']) != false) {
                $this->events[] = $row;
            }
        }

        //exit();
        //exit();

        // Add in a set of default places
        $file = $this->resource_path . 'event/events.txt';

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
        //if (isset($this->minutes)) {return;}

        $agent = new Runtime($this->thing, "runtime");

        //$this->minutes = $agent->minutes;
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
        /*
        $event_code_candidate = null;

        foreach ($this->events as $event) {
            $event_code = strtolower($event['code']);
            if (($event_code == $event_code_candidate) or ($event_code_candidate == null)) {
                $event_code_candidate = str_pad(rand(1000000,9999999) , 8, "9", STR_PAD_LEFT);
                $event_code_candidate = str_pad(rand(1000000,9999999) , 8, "9", STR_PAD_LEFT);

            }
        }
*/

        //$event_code = rand(1000000,9999999);
        //if $this->eventExists($event_code);

        $x = 0;
        while ($x <= 50) {
            $event_code = rand(1000000, 9999999);
            if ($this->eventExists($event_code)) {
                continue;
            } else {
                break;
            }

            $x++;
        }

        return $event_code;
    }

    function eventExists($event_candidate)
    {
        foreach ($this->events as $event) {
            $event_code = strtolower($event['code']);
            $event_name = strtolower($event['name']);

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

        // See if the code or name already exists
        foreach ($this->events as $event) {
            if (
                $event_code == $event['code'] or
                $event_name == $event['name']
            ) {
                $this->event_name = $event['name'];
                $event_code = $event['code'];
                $this->last_refreshed_at = $event['refreshed_at'];
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

            $this->minutes = $runtime->minutes;
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

    function eventTime($input = null)
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

        //        $pattern = "|\d[A-Za-z]{1}\d{2}|"; //headcode pattern
        //$pattern = "|\{5}|"; // 5 digits

        $pattern = "|\d{7}$|";

        preg_match_all($pattern, $input, $m);
        $this->event_codes = $m[0];

        if (!isset($this->events)) {
            $this->getEvents();
        }

        foreach ($this->events as $event) {
            $event_name = strtolower($event['name']);
            $event_code = strtolower($event['code']);

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

        //}

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
                    'found a event code (' .
                    $this->event_code .
                    ') in the text.'
            );
            return [$this->event_code, $this->event_name];
        }

        return true;

        // And then extract place names.
        // Take out word 'place' at the start.
        //        $filtered_input = ltrim(strtolower($input), "event");

        //        foreach($this->event_names as $event_name) {

        //           if (strpos($filtered_input, $event_name) !== false) {
        $event_names[] = $event_name;
        //            }

        //        }

        if (count($event_names) == 1) {
            $this->event_name = $this->event_names[0];
        }

        return [$this->event_code, $this->event_name];
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $link_txt = $this->web_prefix . 'thing/' . $this->uuid . '/event.txt';

        $this->node_list = ["event" => ["translink", "job"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "event"
        );
        $choices = $this->thing->choice->makeLinks('event');

        $web = '<b>Event Agent</b><br>';
        $web .=
            "This agent manages a uniquely numbered live event via text message.<br>";

        /*
        $web .= '<a href="' . $link . '">';

// Get aan html image if there is one if (!isset($this->html_image)) {
if (!isset($this->html_image)) {
    if (function_exists("makePNG")) {
        $this->makePNG();
    } else {
        $this->html_image = true;
    }
}

//$this->makePNG();
        $web .= $this->html_image;


$web .= "<br>";

        $web .= "</a>";
        $web .= "<br>";
*/
        $web .= "<br>event_name is " . $this->event_name . "";
        $web .= "<br>event_code is " . $this->event_code . "";

        $web .= "<br>run_time is " . $this->minutes . " minutes";
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

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/event.txt';
        $web .= '<a href="' . $link . '">event.txt</a>';
        $web .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/event.log';
        $web .= '<a href="' . $link . '">event.log</a>';
        $web .= " | ";
        $link =
            $this->web_prefix .
            'thing/' .
            $this->uuid .
            '/' .
            $this->event_name;
        $web .= '<a href="' . $link . '">' . $this->event_name . '</a>';

        $web .= "<br>";
        $web .= "<br>";

        //$received_at = strtotime($this->thing->thing->created_at);
        $ago = $this->thing->human_time(
            strtotime($this->thing->time()) - strtotime($this->refreshed_at)
        );
        $web .= "Last asserted about " . $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }
    /*
    function readEvent()
    {
        $this->thing->log("read");

    }
*/
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
            $txt = 'No events found.';
        } else {
            $txt = 'These are EVENTS for RAILWAY ' . $this->event->nuuid . '. ';
        }
        $txt .= "\n";
        //        $txt .= count($this->placecode_list). ' Place codes and names retrieved.';

        $txt .= "\n";

        //$txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
        $txt .= " " . str_pad("NAME", 40, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("CODE", 8, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        /*

        //$txt = "Test \n";
        foreach ($this->placecode_list as $place_code) {
    
            $txt .= " " . str_pad(" ", 40, " ", STR_PAD_RIGHT);

            $txt .= " " . "  " . str_pad(strtoupper($place_code), 5, "X", STR_PAD_LEFT);
            //$txt .= " " . str_pad($train['alias'], 10, " " , STR_PAD_RIGHT);

            $txt .= "\n";



        }


        foreach ($this->placename_list as $place_name) {

            $txt .= " " . str_pad(strtoupper($place_name), 40, " ", STR_PAD_RIGHT);

            $txt .= "\n";



        }
*/
        // Places must have both a name and a code.  Otherwise it's not a place.
        foreach ($this->events as $key => $event) {
            $txt .=
                " " .
                str_pad(strtoupper($event['name']), 20, " ", STR_PAD_RIGHT);
            $txt .=
                " " .
                "  " .
                str_pad(strtoupper($event['code']), 5, "X", STR_PAD_LEFT);
            $txt .=
                " " .
                "  " .
                str_pad(strtoupper($event['minutes']), 5, "X", STR_PAD_LEFT);
            $txt .=
                " " .
                "  " .
                str_pad(strtoupper($event['day']), 5, "X", STR_PAD_LEFT);
            $txt .=
                " " .
                "  " .
                str_pad(strtoupper($event['hour']), 5, "X", STR_PAD_LEFT);
            $txt .=
                " " .
                "  " .
                str_pad(strtoupper($event['minute']), 5, "X", STR_PAD_LEFT);

            //           $this->minutes = $runtime->minutes;
            //            $this->day = $runat->day;
            //            $this->hour = $runat->hour;
            //            $this->minute = $runat->minute;

            $txt .= "\n";
        }

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    function makeChoices()
    {
        //        $this->thing->choice->Choose($this->state);
        //        $this->thing->choice->save($this->keyword, $this->state);

        $node_list = ["event" => ["going", "meh"]];

        $this->thing->choice->Create($this->agent_name, $node_list, "event");

        $this->choices = $this->thing->choice->makeLinks('event');
        $this->thing_report['choices'] = $this->choices;
    }

    public function makeSMS()
    {
        if (!isset($this->event_name) or $this->event_name == null) {
            $this->event_name = "None found";
            //$this->getEvent();
        }

        $sms_message = "EVENT";
        //$sms_message .= $this->event->nuuid ." ";
        // . strtoupper($this->event_code) ." | " . $s;
        //$sms_message .= " | " . $this->headcodeTime($this->start_at);
        $sms_message .= " ";

        $sms_message .= $this->event_name;

        $event_code_text = "X";
        if (isset($this->event_code) and $this->event_code != false) {
            $event_code_text = trim($this->event_code);
        }
        $sms_message .= " " . $event_code_text;

        $minutes_text = "Set RUNTIME. ";
        if (isset($this->minutes) and $this->minutes != false) {
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

        //        if ( (isset($this->day)) and (isset($this->minute)) ) {
        //            $run_at_text .= " ";
        //            if (isset($this->day)) {$sms_message .= $this->day;}
        //        }

        if ($this->day == "X" or $this->hour == "X" or $this->minute == "X") {
            $run_at_text = "Set RUNAT. ";
        }

        $sms_message .= $run_at_text;

        //        $sms_message .= " | index " . $this->index;

        //        $sms_message .= " | nuuid " . strtoupper($this->event->nuuid);
        //        $sms_message .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

        //       $sms_message .= " | ptime " . number_format($this->event->thing->elapsed_runtime())."ms";

        $this->sms_message = $sms_message;
        $this->thing_report['sms'] = $sms_message;
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        // Allow for indexing.
        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index; //
        }

        $this->makeChoices();

        $this->thing_report['email'] = $this->sms_message;
        $this->thing_report['message'] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        } else {
            $this->thing_report['info'] =
                'Agent input was "' . $this->agent_input . '".';
        }

        $this->thing_report['help'] =
            'This is a Place.  The union of a code and a name.';
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
        $this->response = null;
        $this->num_hits = 0;
        /*
        switch (true) {
            case $this->agent_input == "extract":
                $input = strtolower($this->from . " " . $this->subject);
                break;
            case $this->agent_input != null:
                $input = strtolower($this->agent_input);
                break;
            case true:
                $input = strtolower($this->from . " " . $this->subject);
        }
*/

        $input = $this->input;

        $this->readEvent($input);

        if (stripos($input, "current") !== false) {
            $b = $this->currentEvent();
            return;
        }
        // Haystack doesn't work well here because we want to run the extraction on the cleanest signal.
        // Think about this.
        //$haystack = $this->agent_input . " " . $this->from . " " . $this->subject;

        $prior_uuid = null;

        // Is there a place in the provided datagram
        $this->extractEvent($input);
        if ($this->agent_input == "extract") {
            return;
        }

        if ($this->agent_input == "vancouver pride 2018") {
            $this->makeEvent(500001, "vancouver pride 2018");
            echo $this->event_name;
            return;
        }

        //echo "extracted<br>";
        //var_dump($this->event_name);
        //var_dump($this->event_code);
        //echo "<br>";
        // Return the current place

        $this->last_event = new Variables(
            $this->thing,
            "variables event " . $this->from
        );
        $this->last_event_code = $this->last_event->getVariable('event_code');
        $this->last_event_name = $this->last_event->getVariable('event_name');
        $this->last_refreshed_at = $this->last_event->getVariable(
            'refreshed_at'
        );

        //var_dump($this->last_place->thing['uuid']);
        //exit();
        //echo "last_event_name<br>";
        //var_dump($this->last_event_name);
        //var_dump($this->last_event_code);
        //echo "<br>";

        //echo "event<br>";
        //var_dump($this->event_name);
        //var_dump($this->event_code);
        //echo "<br>";

        //exit();
        // If at this point we get false/false, then the default Place has not been created.
        //        if ( ($this->place_code == false) and ($this->place_name == false) ) {
        //            $this->makePlace($this->default_place_code, $this->default_place_name);
        //        }

        //$this->get();

        //exit();
        $pieces = explode(" ", strtolower($input));

        $prior_uuid = null;

        // Is there a place in the provided datagram
        $this->extractEvent($input);
        if ($this->agent_input == "extract") {
            return;
        }

        if (count($pieces) == 1) {
            if ($input == 'event') {
                $this->getEvent();

                $this->getRunat();
                $this->getRuntime();

                //$this->getRunat();
                //$this->getRuntime();
                $this->response = "Last 'event' retrieved.";
                return;
            }
        }

        // So this is really the 'sms' section
        // Keyword
        /*
        if (count($pieces) == 1) {

            if ($input == 'place') {

                
                $this->read();
                return;
            }

        }
*/

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'next':
                            $this->thing->log("read subject nextheadcode");
                            $this->nextEvent();
                            break;

                        case 'drop':
                            //     //$this->thing->log("read subject nextheadcode");
                            $this->dropEvent();
                            break;

                        case 'make':
                        case 'new':
                        case 'create':
                        case 'add':
                        case 'event':
                            $this->assertEvent(strtolower($input));

                            //$this->refvar_dump($this->minute);
                            //reshed_at = $this->thing->time();
                            $this->getRunat();
                            $this->getRuntime();
                            //var_dump($this->day);
                            //var_dump($this->hour);
                            //var_dump($this->minute);
                            //exit();

                            if (empty($this->event_name)) {
                                $this->event_name = "X";
                            }

                            $this->response =
                                'Asserted Event and found ' .
                                strtoupper($this->event_name) .
                                ".";
                            return;
                            break;

                            $event_type = "4";
                            //$place_code = $place_zone  . str_pad(rand(0,999) + 1,6,  '0', STR_PAD_LEFT);

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
                                    return;
                                }
                            }

                            if ($this->place_name == null) {
                                $this->event_name =
                                    "Foo" . rand(0, 1000000) . "Bar";
                            }

                            //$this->makeheadcode();
                            $this->makeEvent(
                                $this->event_code,
                                $this->event_name
                            );
                            $this->getEvent($this->event_code);
                            //$this->set();
                            return;
                            break;

                        default:
                    }
                }
            }
        }

        // If at this point we get false/false, then the default Place has not been created.
        //        if ( ($this->place_code == false) and ($this->place_name == false) ) {
        //            $this->makePlace($this->default_place_code, $this->default_place_name);
        //        }

        // Check whether headcode saw a run_at and/or run_time
        // Intent at this point is less clear.  But headcode
        // might have extracted information in these variables.

        // $uuids, $head_codes, $this->run_at, $this->run_time

        if ($this->event_code != null) {
            $this->getEvent($this->event_code);
            $this->thing->log(
                $this->agent_prefix .
                    'using extracted event_code ' .
                    $this->event_code .
                    ".",
                "INFORMATION"
            );
            $this->response = $this->event_code . " used to retrieve an Event.";

            return;
        }

        if ($this->event_name != null) {
            $this->getEvent($this->event_name);

            $this->thing->log(
                $this->agent_prefix .
                    'using extracted event_name ' .
                    $this->event_name .
                    ".",
                "INFORMATION"
            );
            $this->response = strtoupper($this->event_name) . " retrieved.";
            $this->assertEvent($this->event_name);
            return;
        }

        if ($this->last_event_code != null) {
            $this->getEvent($this->last_event_code);
            $this->thing->log(
                $this->agent_prefix .
                    'using extracted last_event_code ' .
                    $this->last_event_code .
                    ".",
                "INFORMATION"
            );
            $this->response =
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
            $this->response = $event . " used to retrieve an Event.";

            return;
        }

        $this->makeEvent(null, $event);
        $this->thing->log(
            $this->agent_prefix .
                'using default_event_code ' .
                $this->default_event_code .
                ".",
            "INFORMATION"
        );

        $this->response = "Made an Event called " . $event . ".";
        return;
        $this->last_event_code = $this->last_event->getVariable('event_code');

        if (
            $this->thing->isData($this->event_name) or
            $this->thing->isData($this->event_code)
        ) {
            $this->set();
            return;
        }

        //$this->read();

        return "Message not understood";

        return false;
    }

    function is_positive_integer($str)
    {
        return is_numeric($str) && $str > 0 && $str == round($str);
    }
}
