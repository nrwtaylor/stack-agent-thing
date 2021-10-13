<?php

namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Place extends Agent
{
    // Timing 11 July 2018
    // 4,232ms, 4,009ms, 3,057ms

    // This is a place.

    //

    // This is an agent of a place.  They can probaby do a lot for somebody.
    // With the right questions.

    public $var = 'hello';

    function init()
    {
        $this->keywords = [
            'place',
            'next',
            'accept',
            'clear',
            'drop',
            'add',
            'new',
            'here',
            'there',
        ];

        $this->default_place_name = "Here";
        $this->default_place_code = "BMYK";

        if (isset($this->thing->container['api']['place'])) {
            if (
                isset(
                    $this->thing->container['api']['place'][
                        'default_place_name'
                    ]
                )
            ) {
                $this->default_place_name =
                    $this->thing->container['api']['place'][
                        'default_place_name'
                    ];
            }

            if (
                isset(
                    $this->thing->container['api']['place'][
                        'default_place_code'
                    ]
                )
            ) {
                $this->default_place_code =
                    $this->thing->container['api']['place'][
                        'default_place_code'
                    ];
            }
        }

        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->default_alias = "Thing";
        $this->current_time = $this->thing->time();

        $this->test = "Development code"; // Always iterative.

        $this->state = null; // to avoid error messages

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/place';

        $this->lastPlace();

        // Read the subject to determine intent.
        $this->railway_place = new Variables(
            $this->thing,
            "variables place " . $this->from
        );

    }

    function set()
    {
var_dump("place set");

        if (!isset($this->refreshed_at)) {
            $this->refreshed_at = $this->thing->time();
        }

        $place = new Variables($this->thing, "variables place " . $this->from);

        $place->setVariable("place_code", $this->place_code);
        $place->setVariable("place_name", $this->place_name);

        $place->setVariable("refreshed_at", $this->refreshed_at);

        $this->thing->log(
            $this->agent_prefix .
                ' set ' .
                $this->place_code .
                ' and ' .
                $this->place_name .
                ".",
            "INFORMATION"
        );

        $place = new Variables(
            $this->thing,
            "variables " . $this->place_code . " " . $this->from
        );
        $place->setVariable("place_name", $this->place_name);
        $place->setVariable("refreshed_at", $this->refreshed_at);
    }

    function isCode()
    {
        $place_zone = "05";

        foreach (range(1, 9999) as $n) {
            foreach ($this->places as $place) {
                $place_code = $place_zone . str_pad($n, 4, "0", STR_PAD_LEFT);

                if ($this->getPlace($place_code)) {
                    // Code doesn't exist
                    break;
                }
            }
            if ($n >= 9999) {
                $this->thing->log(
                    "No Place code available in zone " . $place_zone . ".",
                    "WARNING"
                );
                return;
            }
        }
    }

    function nextCode()
    {
        $place_code_candidate = null;

        $alpha_agent = new A4($this->thing, "a4");
        //        $place_code_candidate = $this->thing->nuuid;
        $place_code_candidate = $alpha_agent->alpha;

        foreach ($this->places as $place) {
            $existing_place_code = strtolower($place['code']);
            if (
                $existing_place_code == $place_code_candidate or
                $place_code_candidate == null
            ) {
                //$place_code_candidate = str_pad(rand(100,9999) , 8, "9", STR_PAD_LEFT);

                $alpha_agent = new A4($this->thing, "a4");
                //       $place_code_candidate = $this->thing->nuuid;
                $place_code_candidate = $alpha_agent->alpha;

                //                $place_code_candidate = $this->thing->nuuid;
            }
        }
        $place_code = $place_code_candidate;
        return $place_code;
    }

    function nextPlace()
    {
        $this->thing->log("next place");

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

    public function parsePlace($place_text)
    {
        $arr = explode("|", $place_text);

        $feature_id = $arr[0];
        $feature_name = strtolower($arr[1]);
        $feature_class = $arr[2];
        $state_alpha = strtolower($arr[3]);
        $state_numeric = $arr[4];
        $county_name = strtolower($arr[5]);
        $count_numeric = $arr[6];
        // etc
        //array(20) { [0]=> string(13) "﻿FEATURE_ID" [1]=> string(12) "FEATURE_NAME"
        // [2]=> string(13) "FEATURE_CLASS" [3]=> string(11) "STATE_ALPHA"
        // [4]=> string(13) "STATE_NUMERIC" [5]=> string(11) "COUNTY_NAME"
        // [6]=> string(14) "COUNTY_NUMERIC" [7]=> string(15)
        // "PRIMARY_LAT_DMS" [8]=> string(13) "PRIM_LONG_DMS"
        // [9]=> string(12) "PRIM_LAT_DEC" [10]=> string(13) "PRIM_LONG_DEC"
        // [11]=> string(14) "SOURCE_LAT_DMS" [12]=> string(15) "SOURCE_LONG_DMS"
        // [13]=> string(14) "SOURCE_LAT_DEC" [14]=> string(15) "SOURCE_LONG_DEC"
        // [15]=> string(9) "ELEV_IN_M" [16]=> string(10) "ELEV_IN_FT"
        // [17]=> string(8) "MAP_NAME" [18]=> string(12) "DATE_CREATED" [19]=> string(13) "DATE_EDITED " }';

        $place = [
            "feature_id" => $feature_id,
            "feature_name" => $feature_name,
            "state_alpha" => $state_alpha,
            "county_name" => $county_name,
        ];

        return $place;
    }

    function getPlace($selector = null)
    {
        foreach ($this->places as $place) {
            // Match the first matching place
            if ($selector == null or $selector == "") {
                $this->refreshed_at = $this->last_refreshed_at; // This is resetting the count.
                $this->place_name = $this->last_place_name;
                $this->place_code = $this->last_place_code;
                $this->place = new Variables(
                    $this->thing,
                    "variables " . $this->place_code . " " . $this->from
                );
                return [$this->place_code, $this->place_name];
            }

            if (
                strtolower($place['code']) == strtolower($selector) or
                $place['name'] == $selector
            ) {
                $this->refreshed_at = $place['refreshed_at'];
                $this->place_name = $place['name'];
                $this->place_code = $place['code'];
                $this->place = new Variables(
                    $this->thing,
                    "variables " . $this->place_code . " " . $this->from
                );
                return [$this->place_code, $this->place_name];
            }
        }
        return true;
    }

    // Factor a self-naming function in Agent to perform this magic.
    function getQuantity()
    {
        $quantity_agent = new Quantity($this->thing, "quantity");
        $this->quantity = $quantity_agent->quantity;
    }

    function isPlace($requested_place_identifier = null)
    {
        if ($requested_place_identifier == "") {
            return false;
        }

        if (!isset($this->places)) {
            $this->getPlaces();
        }
        foreach ($this->places as $key => $place) {
            if (
                strtolower($requested_place_identifier) ==
                strtolower($place['code'])
            ) {
                return true;
            }
            if (
                strtolower($requested_place_identifier) ==
                strtolower($place['name'])
            ) {
                return true;
            }

            $words = explode(" ", $requested_place_identifier);
            foreach ($words as $index => $word) {
                $word = trim($word);
                if (strtolower($word) == strtolower($place['code'])) {
                    return true;
                }
                if (strtolower($word) == strtolower($place['name'])) {
                    return true;
                }
            }
        }

        return false;
    }

    // very much dev
    function getPlaces()
    {
        $this->placecode_list = [];
        $this->placename_list = [];
        $this->places = [];

        // See if a headcode record exists.
//        $findagent_thing = new Findagent($this->thing, 'place');
//        $count = count($findagent_thing->thing_report['things']);
$things = $this->getThings('place');

        $this->max_index = 0;

if ($things === true) {return;}
if ($things === null) {return;}

$count = count($things);

        $this->thing->log(
            'found ' .
                $count .
                " place Things."
        );

        //        if ($findagent_thing->thing_reports['things'] == false) {
        //                $place_code = $this->default_place_code;
        //                $place_name = $this->default_place_name;
        //            return array($this->placecode_list, $this->placename_list, $this->places);
        //        }

        if ($things == true) {
        }

        if (!$this->is_positive_integer($count)) {
            // No places found
        } else {
            foreach (
                array_reverse($things)
                as $i=>$thing
            ) {
                //$uuid = $thing_object['uuid'];
$uuid = $thing->uuid;
                //$variables_json = $thing_object['variables'];
                //$variables = $this->thing->json->jsontoArray($variables_json);
$variables = $thing->variables;
                if (isset($variables['place'])) {
                    $place_code = $this->default_place_code;
                    $place_name = $this->default_place_name;
                    $refreshed_at = "meep getPlaces";

                    if (isset($variables['place']['place_code'])) {
                        $place_code = $variables['place']['place_code'];
                    }
                    if (isset($variables['place']['place_name'])) {
                        $place_name = $variables['place']['place_name'];
                    }
                    if (isset($variables['place']['refreshed_at'])) {
                        $refreshed_at = $variables['place']['refreshed_at'];
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

                    $this->places[] = [
                        "code" => $place_code,
                        "name" => $place_name,
                        "refreshed_at" => $refreshed_at,
                    ];
                    $this->placecode_list[] = $place_code;
                    $this->placename_list[] = $place_name;
                    //                  }
                }
            }
        }

        // Return this-places filtered by latest check-in at each location.

        // Check if the place is already in the the list (this->places)
        $found = false;

        $filtered_places = [];
        foreach (array_reverse($this->places) as $key => $place) {
            $place_name = $place['name'];
            $place_code = $place['code'];

            if (!isset($place['refreshed_at'])) {
                continue;
            }

            $refreshed_at = $place['refreshed_at'];

            if (isset($filtered_places[$place_name]['refreshed_at'])) {
                if (
                    strtotime($refreshed_at) >
                    strtotime($filtered_places[$place_name]['refreshed_at'])
                ) {
                    $filtered_places[$place_name] = [
                        "name" => $place_name,
                        "code" => $place_code,
                        'refreshed_at' => $refreshed_at,
                    ];
                }
                continue;
            }

            $filtered_places[$place_name] = [
                "name" => $place_name,
                "code" => $place_code,
                'refreshed_at' => $refreshed_at,
            ];
        }

        $refreshed_at = [];
        foreach ($this->places as $key => $row) {
            $refreshed_at[$key] = $row['refreshed_at'];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->places);

        $this->old_places = $this->places;
        $this->places = [];
        foreach ($this->old_places as $key => $row) {
            if (strtotime($row['refreshed_at']) != false) {
                $this->places[] = $row;
            }
        }


        // Add in a set of default places
        $file = $this->resource_path . 'place/places.txt';

if (file_exists($file)) {
        $contents = file_get_contents($file);

        $handle = fopen($file, "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // process the line read.

                // It's just a list of place names.
                // Common ones.
                $place_name = $line;
                // This is where the place index will be called.
                // $place_code = str_pad(RAND(1,99999), 8, " ", STR_PAD_LEFT);
                $place_code = $this->thing->nuuid;

                $this->placecode_list[] = $place_code;
                $this->placename_list[] = $place_name;
                $this->places[] = [
                    "code" => $place_code,
                    "name" => $place_name,
                ];
            }
            fclose($handle);
        } else {
            // error opening the file.
        }
}
        // Indexing not implemented
        $this->max_index = 0;

        return [$this->placecode_list, $this->placename_list, $this->places];
    }

    function is_positive_integer($str)
    {
        return is_numeric($str) && $str > 0 && $str == round($str);
    }

    public function get($place_code = null)
    {
        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.
        if ($place_code == null) {
            if (isset($this->default_place_code)) {
                $place_code = $this->default_place_code;
            }

            if (isset($this->last_place_code)) {
                $place_code = $this->last_place_code;
            }

            if (isset($this->place_code)) {
                $place_code = $this->place_code;
            }

            if (isset($this->last_place_code)) {
                $place_code = $this->last_place_code;
                //exit();
            }
        }

        //$place_code = $this->place_code;

        $this->place = new Variables(
            $this->thing,
            "variables " . $place_code . " " . $this->from
        );

        $this->place_code = $this->place->getVariable("place_code");
        $this->place_name = $this->place->getVariable("place_name");
        $this->refreshed_at = $this->place->getVariable("refreshed_at");

        return [$this->place_code, $this->place_name];
    }

    function dropPlace()
    {
        $this->thing->log($this->agent_prefix . "was asked to drop a Place.");

        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->place)) {
            // devstack in Variables
            $this->place->resetVariable();
            // $this->place->Forget();
            // $this->place = null;
        }

        $this->get();
    }

    function makePlace($place_code = null, $place_name = null)
    {
        if ($place_name == null) {
            return true;
        }

        // See if the code or name already exists
        foreach ($this->places as $place) {
            if (
                $place_code == $place['code'] or
                $place_name == $place['name']
            ) {
                $this->place_name = $place['name'];
                $place_code = $place['code'];

                if (isset($place['refreshed_at'])) {
                    $this->last_refreshed_at = $place['refreshed_at'];
                } else {
                    $this->last_refreshed_at = "X";
                }
            }
        }
        if ($place_code == null) {
            $place_code = $this->nextCode();
        }

        $this->thing->log(
            'Agent "Place" will make a Place for ' . $place_code . "."
        );

        $ad_hoc = true;
        $this->thing->log($this->agent_prefix . "is ready to make a Place.");
        if ($ad_hoc != false) {
            $this->thing->log($this->agent_prefix . "is making a Place.");
            $this->thing->log(
                $this->agent_prefix .
                    "was told the Place is Useable but we might get kicked out."
            );

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            if ($place_code == false) {
                $place_code = $this->default_place_code;
                $place_name = $this->default_place_name;
            }

            $this->current_place_code = $place_code;
            $this->place_code = $place_code;

            $this->current_place_name = $place_name;
            $this->place_name = $place_name;
            $this->refreshed_at = $this->current_time;

            // This will write the refreshed at.
            $this->set();

            $this->getPlaces();
            $this->getPlace($this->place_code);

            $this->place_thing = $this->thing;
        }
        $this->thing->log('found a Place and pointed to it.');
    }

    function placeTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $place_time = "x";
            return $headcode_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $place_time = $this->hour . $this->minute;

        if ($input == null) {
            $this->place_time = $place_time;
        }

        return $place_time;
    }

    public function extractPlaces($input = null)
    {
        if (!isset($this->place_codes)) {
            $this->place_codes = [];
        }

        if (!isset($this->place_names)) {
            $this->place_names = [];
        }

        //$pattern = "|\d[A-Za-z]{1}\d{2}|"; //headcode pattern
        //$pattern = "|\{5}|"; // 5 digits

        $pattern = "|\d{6}$|";

        preg_match_all($pattern, $input, $m);
        $this->place_codes = $m[0];

        // Look for an established list of places.
        //$default_placename_list = array("Eton", "Gilmore", "Hastings", "Vine", "Downtown", "Metrotown", "Triumph", "Main and Hastings", "Commercial and Broadway", "Granville Street", "Burrard Skytrain");

        //if (!isset($this->place_name_list)) {$this->get();}

        if (!isset($this->places)) {
            $this->getPlaces();
        }

        foreach ($this->places as $place) {
            $place_name = strtolower($place['name']);
            $place_code = strtolower($place['code']);

            if (empty($place_name)) {
                continue;
            }
            if (empty($place_code)) {
                continue;
            }

            // Thx. https://stackoverflow.com/questions/4366730/how-do-i-check-if-a-string-contains-a-specific-word
            if (strpos($input, $place_code) !== false) {
                $this->place_codes[] = $place_code;
            }

            if (strpos($input, $place_name) !== false) {
                $this->place_names[] = $place_name;
            }
        }

        $this->place_codes = array_unique($this->place_codes);
        $this->place_names = array_unique($this->place_names);

        return [$this->place_codes, $this->place_names];
    }

    public function extractPlace($input)
    {
        $this->place_name = null;
        $this->place_code = null;

        list($place_codes, $place_names) = $this->extractPlaces($input);

        if (count($place_codes) + count($place_names) == 1) {
            if (isset($place_codes[0])) {
                $this->place_code = $place_codes[0];
            }
            if (isset($place_names[0])) {
                $this->place_name = $place_names[0];
            }

            $this->thing->log(
                $this->agent_prefix .
                    'found a place code (' .
                    $this->place_code .
                    ') in the text.'
            );
            return [$this->place_code, $this->place_name];
        }

        if (count($place_names) == 1) {
            $this->place_name = $this->place_names[0];
        }
        return [$this->place_code, $this->place_name];
    }

    function assertPlace($input)
    {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "place is")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("place is"));
        } elseif (($pos = strpos(strtolower($input), "place")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("place"));
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $place = $this->getPlace($filtered_input);
        if ($place) {
            //true so make a place
            $this->makePlace(null, $filtered_input);
        }
    }

    function readPlace()
    {
        $this->thing->log("read");
    }

    function addPlace()
    {
        $this->get();
    }

    public function makeMessage()
    {
        $message = "Place is " . ucwords($this->place_name) . ".";
        $this->message = $message;
        $this->thing_report['message'] = $message;
    }

    // dev
    function makeTXT()
    {
        if (!isset($this->placecode_list)) {
            $this->getPlaces();
        }

        $this->getPlaces();

        if (!isset($this->place)) {
            $txt = "Not here";
        } else {
            $txt =
                'These are PLACES for PLACE ' .
                $this->railway_place->nuuid .
                '. ';
        }
        $txt .= "\n";
        //        $txt .= count($this->placecode_list). ' Place codes and names retrieved.';

        $txt .= "\n";

        //$txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
        $txt .= " " . str_pad("NAME", 40, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("CODE", 6, " ", STR_PAD_LEFT);
        $txt .= " " . str_pad("FRESHNESS", 15, " ", STR_PAD_LEFT);

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
        foreach ($this->places as $key => $place) {
            $txt .=
                " " .
                str_pad(
                    strtoupper(trim($place['name'])),
                    40,
                    " ",
                    STR_PAD_RIGHT
                );
            $txt .=
                " " .
                "  " .
                str_pad(strtoupper(trim($place['code'])), 6, " ", STR_PAD_LEFT);
            if (isset($place['refreshed_at'])) {
                //if ($place['refreshed_at'] == $last_refreshed_at) {continue;}
                $last_refreshed_at = $place['refreshed_at'];
                $txt .=
                    " " .
                    "  " .
                    str_pad(
                        strtoupper($place['refreshed_at']),
                        15,
                        "X",
                        STR_PAD_LEFT
                    );
            }
            $txt .= "\n";
        }

        $txt .= "\n";
        $txt .= "Last place " . strtoupper($this->last_place_name) . "\n";
        $txt .= "Now at " . strtoupper($this->place_name);

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    // prod
    public function makeSMS()
    {
        // Get available for place.  This would be an available agent.
        $this->getQuantity();

        $this->inject = null;
        $s = $this->inject;
        $sms = "PLACE " . strtoupper($this->place_name);

        if (!empty($this->inject)) {
            $sms .= " | " . $s;
        }

        // Not there yet.
        // if ((!empty($this->quantity))) {
        //    $sms .= " | " . "quantity " . $this->quantity;
        // }

        //if ((!empty($this->place_code))) {
        //    $sms .= " | " . trim(strtoupper($this->place_code));
        //}

        if (!empty($this->place_code)) {
            //$sms .= " | " . $this->web_prefix . 'thing/' . $this->uuid . '/place';
            $sms .= " | " . $this->link;
            $sms .= " ";
        }

        if (isset($this->response)) {
            $sms .= $this->response;
        }

        if (!empty($this->place_code)) {
            $sms .= " | " . "TEXT " . trim(strtoupper($this->place_code));
            //        } else {
            //            $sms .= " | " . "TEXT " . "AGENT";
        }


        /* dev

//        if (!isset($this->last_refreshed_at) {$this->lastPlace();}

        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($this->last_refreshed_at) );
        $sms .= " | (Last) Place set about ". $ago . " ago.";

        $ago = $this->thing->human_time ( strtotime($this->thing->time()) - strtotime($this->refreshed_at) );
        $sms .= " | Place set about ". $ago . " ago.";

        $time = $this->last_refreshed_at;
        $sms .= " | (Last) Place set " . $this->refreshed_at . " " . $this->last_refreshed_at . ".";

        $time = $this->last_refreshed_at;
        $sms .= " | Place set " . $this->refreshed_at . " " . $this->refreshed_at . ".";


        if (isset($this->response)) {
            $sms .= " | " . $this->response;
        }
        //$sms .= " | ~rtime " . number_format($this->thing->elapsed_runtime())."ms";

*/

        $sms = str_replace(" | ", "\n", $sms);

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    public function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $link_txt = $this->web_prefix . 'thing/' . $this->uuid . '/place.txt';

        //        $this->node_list = array("place"=>array("translink", "job"));
        // Make buttons
        //        $this->thing->choice->Create($this->agent_name, $this->node_list, "place");
        //        $choices = $this->thing->choice->makeLinks('place');

        $choices = false;

        $web = '<a href="' . $link . '">';

        // dev
        // Insert other agents images...
        // $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/roll.png" jpg"
        //      width="100" height="100"
        //      alt="' . $alt_text . '" longdesc = "' . $this->web_prefix . 'thing/' .$this->uuid . '/roll.txt">';
        // or
        // $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/snowflake.png">';

        // Get an html image if there is one
        if (!isset($this->html_image)) {
            if (function_exists("makePNG")) {
                $this->makePNG();
            } else {
                $this->html_image = true;
            }
        }

        $this->makePNG();
        $web .= $this->html_image;
        $web .= "<br>";

        $web .= "</a>";
        $web .= "<br>";
        $web .= $this->sms_message;
        $web .= "<br>";

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/place.txt';
        $web .= '<a href="' . $link . '">place.txt</a>';
        $web .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/place.log';
        $web .= '<a href="' . $link . '">place.log</a>';

        $web .= " | ";

        $link =
            $this->web_prefix .
            'thing/' .
            $this->uuid .
            '/' .
            $this->place_name;
        $web .= '<a href="' . $link . '">' . $this->place_name . '</a>';

        $web .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/' . "place";
        $web .= '<a href="' . $link . '">' . "place" . '</a>';

        $web .= " | ";
        $link =
            $this->web_prefix .
            'thing/' .
            $this->uuid .
            '/' .
            urlencode($this->place_code);

        $web .= '<a href="' . $link . '">' . $this->place_code . '</a>';

        /*
        $web .= " | ";
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/'. "place";
        $web .= $this->place_name. '';
        $web .= " | ";
        $web .= '<a href="' . $link . '">'. "place" . '</a>';
*/
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

    public function makeImage()
    {
        $text = strtoupper($this->place_name);

        $image_height = 125;
        $image_width = 125 * 4;

        $image = imagecreatetruecolor($image_width, $image_height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 255, 0, 0);
        $green = imagecolorallocate($image, 0, 255, 0);
        $grey = imagecolorallocate($image, 128, 128, 128);

        imagefilledrectangle($image, 0, 0, $image_width, $image_height, $white);
        $textcolor = imagecolorallocate($image, 0, 0, 0);

        $this->ImageRectangleWithRoundedCorners(
            $image,
            0,
            0,
            $image_width,
            $image_height,
            12,
            $black
        );
        $this->ImageRectangleWithRoundedCorners(
            $image,
            6,
            6,
            $image_width - 6,
            $image_height - 6,
            12 - 6,
            $white
        );

        $font = $this->default_font;

        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);
        $sizes_allowed = [72, 36, 24, 12, 6];

            $width = imagesx($image);
            $height = imagesy($image);

if (file_exists($font)) {

        foreach ($sizes_allowed as $size) {
            $angle = 0;
            $bbox = imagettfbbox($size, $angle, $font, $text);
            $bbox["left"] = 0 - min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["top"] = 0 - min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            $bbox["width"] =
                max($bbox[0], $bbox[2], $bbox[4], $bbox[6]) -
                min($bbox[0], $bbox[2], $bbox[4], $bbox[6]);
            $bbox["height"] =
                max($bbox[1], $bbox[3], $bbox[5], $bbox[7]) -
                min($bbox[1], $bbox[3], $bbox[5], $bbox[7]);
            extract($bbox, EXTR_PREFIX_ALL, 'bb');

            //check width of the image
//            $width = imagesx($image);
//            $height = imagesy($image);
            if ($bbox['width'] < $image_width - 50) {
                break;
            }
        }

        $pad = 0;
        imagettftext(
            $image,
            $size,
            $angle,
            $width / 2 - $bb_width / 2,
            $height / 2 + $bb_height / 2,
            $grey,
            $font,
            $text
        );
}

        imagestring(
            $image,
            2,
            $image_width - 75,
            10,
            $this->place_code,
            $textcolor
        );

        $this->image = $image;
    }

    public function makePNG()
    {
        $agent = new Png($this->thing, "png"); // long run

        $this->makeImage();

        $agent->makePNG($this->image);

        $this->html_image = $agent->html_image;
        $this->image = $agent->image;
        $this->PNG = $agent->PNG;
        $this->PNG_embed = $agent->PNG_embed;
        $this->thing_report['png'] = $agent->image_string;
    }

    // Must be able to factor this out with Image. Eventually.
    public function ImageRectangleWithRoundedCorners(
        &$im,
        $x1,
        $y1,
        $x2,
        $y2,
        $radius,
        $color
    ) {
        // draw rectangle without corners
        imagefilledrectangle(
            $im,
            $x1 + $radius,
            $y1,
            $x2 - $radius,
            $y2,
            $color
        );
        imagefilledrectangle(
            $im,
            $x1,
            $y1 + $radius,
            $x2,
            $y2 - $radius,
            $color
        );

        // draw circled corners
        imagefilledellipse(
            $im,
            $x1 + $radius,
            $y1 + $radius,
            $radius * 2,
            $radius * 2,
            $color
        );
        imagefilledellipse(
            $im,
            $x2 - $radius,
            $y1 + $radius,
            $radius * 2,
            $radius * 2,
            $color
        );
        imagefilledellipse(
            $im,
            $x1 + $radius,
            $y2 - $radius,
            $radius * 2,
            $radius * 2,
            $color
        );
        imagefilledellipse(
            $im,
            $x2 - $radius,
            $y2 - $radius,
            $radius * 2,
            $radius * 2,
            $color
        );
    }

    public function respondResponse()
    {
var_dump("adfas");

        // Thing actions

        $this->thing->flagGreen();

        $choices = false;
        $this->thing_report['choices'] = $choices;

        // Allow for indexing.
        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index; //
        }

        $this->thing_report['email'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }

        $this->thing_report['help'] =
            'This is a Place.  The union of a code and a name.';

        if ($this->agent_input != "extract") {
            $this->set();
        }
    }

    function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    function lastPlace()
    {
        $this->last_place = new Variables(
            $this->thing,
            "variables place " . $this->from
        );
        $this->last_place_code = $this->last_place->getVariable('place_code');
        $this->last_place_name = $this->last_place->getVariable('place_name');

        // This doesn't work
        $this->last_refreshed_at = $this->last_place->getVariable(
            'refreshed_at'
        );
        return;

        // So do it the hard way

        if (!isset($this->places)) {
            $this->getPlaces();
        }

        foreach (array_reverse($this->places) as $key => $place) {
            if ($place['name'] == $this->last_place_name) {
                $this->last_refreshed_at = $place['refreshed_at'];
                break;
            }
        }
    }

    public function textPlaces()
    {
        // Need to work on places list generation.
        // Find three 'best' places.
        // But for now find the last three places.
        $places = [];
        $count = 0;
        foreach ($this->places as $i => $place) {
            $place_name = $place['name'];
            if ($count > 2) {
                break;
            }
            if ((isset($last_place_name)) and ($place_name == $last_place_name)) {
                continue;
            }

            if (in_array($place_name, $places)) {continue;}

            $last_place_name = $place_name;
            $places[] = $place['name'];
            $count += 1;
        }

        $places_text = implode(" / ", $places);

        return $places_text;
    }

    public function readSubject()
    {
        $this->num_hits = 0;

        $input = strtolower($this->input);

        $prior_uuid = null;

        // Is there a place in the provided datagram
        $this->extractPlace($input);
        if ($this->agent_input == "extract") {
            return;
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'place') {
                $this->getPlace();
                $this->response .= "Last 'place' retrieved.";
                return;
            }

            if ($input == 'places') {
                $this->getPlace();
                $places_text = $this->textPlaces();

                $this->response .=
                    "Known 'places' retrieved. " . $places_text . ". ";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'next':
                            $this->thing->log("read subject nextheadcode");
                            $this->nextPlace();
                            break;

                        case 'drop':
                            //     //$this->thing->log("read subject nextheadcode");
                            $this->dropPlace();
                            break;

                        case 'make':
                        case 'new':
                        case 'place':
                        case 'create':
                        case 'add':
                            $this->assertPlace(strtolower($input));

                            //$this->refreshed_at = $this->thing->time();

                            if (empty($this->place_name)) {
                                $this->place_name = "X";
                            }

                            $this->response .=
                                'Asserted Place and found ' .
                                strtoupper($this->place_name) .
                                ".";
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

        if ($this->place_code != null) {
            $this->getPlace($this->place_code);
            $this->thing->log(
                $this->agent_prefix .
                    'using extracted place_code ' .
                    $this->place_code .
                    ".",
                "INFORMATION"
            );
            $this->response .= $this->place_code . " used to retrieve a Place.";
            return;
        }

        if ($this->place_name != null) {
            $this->getPlace($this->place_name);

            $this->thing->log(
                $this->agent_prefix .
                    'using extracted place_name ' .
                    $this->place_name .
                    ".",
                "INFORMATION"
            );
            $this->response .= strtoupper($this->place_name) . " retrieved.";
            $this->assertPlace($this->place_name);
            return;
        }

        if ($this->last_place_code != null) {
            $this->getPlace($this->last_place_code);
            $this->thing->log(
                $this->agent_prefix .
                    'using extracted last_place_code ' .
                    $this->last_place_code .
                    ".",
                "INFORMATION"
            );
            $this->response .=
                "Last place " .
                $this->last_place_code .
                " used to retrieve a Place.";
            return;
        }

        // so we get here and this is null placename, null place_id.
        // so perhaps try just loading the place by name

        $place = strtolower($this->subject);

        if (!$this->getPlace(strtolower($place))) {
            // Place was found
            // And loaded
            $this->response .= $place . " used to retrieve a Place.";
            return;
        }

        $this->makePlace(null, $place);
        $this->thing->log(
            $this->agent_prefix .
                'using default_place_code ' .
                $this->default_place_code .
                ".",
            "INFORMATION"
        );

        $this->response .= "Made a Place called " . $place . ".";

        return;

        if (
            $this->isData($this->place_name) or $this->isData($this->place_code)
        ) {
            $this->set();
            return;
        }

        return false;
    }

}

/* More on places

Lots of different ways to number places.
I choose four letters and numbers case-insentitive

*/
