<?php
namespace Nrwtaylor\StackAgentThing;

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class City extends Agent
{
    // This is a city.

    //

    // Search for valid city names.

    public $var = 'hello';

    public function init()
    {
        //    function __construct(Thing $thing, $agent_input = null)
        //    {
        //        $this->agent_input = $agent_input;

        //        $this->thing = $thing;
        //        $this->start_time = $this->thing->elapsed_runtime();
        //        $this->thing_report['thing'] = $this->thing->thing;
        //        $this->agent_name = "city";
        //       $this->agent_prefix = 'Agent "City" ';

        //     $this->thing->log($this->agent_prefix . 'running on Thing '. $this->thing->nuuid . '.',"INFORMATION");

        // $this->node_list = array("start"=>array("stop 1"=>array("stop 2","stop 1"),"stop 3"),"stop 3");

        // Get some stuff from the stack which will be helpful.
        //        $this->web_prefix = $thing->container['stack']['web_prefix'];
        //      $this->mail_postfix = $thing->container['stack']['mail_postfix'];
        //    $this->word = $thing->container['stack']['word'];
        //  $this->email = $thing->container['stack']['email'];

        $this->keywords = [
            'city',
            'next',
            'accept',
            'clear',
            'drop',
            'add',
            'new',
            'here',
            'there',
        ];

        $this->default_city_name =
            $this->thing->container['api']['city']['default_city_name'];
        $this->default_city_code = null;

        //      $this->resource_path = $GLOBALS['stack_path'] . 'resources/';

        $this->default_alias = "Thing";
        //    $this->current_time = $this->thing->time();

        $this->test = "Development code"; // Always iterative.

        // Non-nominal
        //        $this->uuid = $thing->uuid;
        //      $this->to = $thing->to;

        // Potentially nominal
        //  $this->subject = $thing->subject;

        // Treat as nominal
        //      $this->from = $thing->from;

        // Agent variables
        //    $this->sqlresponse = null; // True - error. (Null or False) - no response. Text - response

        $this->state = null; // to avoid error messages

        $this->link = $this->web_prefix . 'thing/' . $this->uuid . '/city';

        $this->lastCity();

        // Read the subject to determine intent.
        $this->railway_city = new Variables(
            $this->thing,
            "variables city " . $this->from
        );
        $this->city_code = $this->railway_city->getVariable("city_code");

        //		$this->readSubject();

        // Generate a response based on that intent.

        //        if ($this->agent_input == null) {
        //		    $this->Respond();
        //        }

        //        if ($this->agent_input != "extract") {
        //            $this->set();
        //        }

        //        $this->thing->log( $this->agent_prefix .' loaded city_name ' . $this->city_name . " and city_code " . $this->city_code . "." );

        //      $this->thing->log( $this->agent_prefix .' ran for ' . number_format($this->thing->elapsed_runtime() - $this->start_time) . 'ms.' );
        //    $this->thing_report['log'] = $this->thing->log;
        //  $this->thing_report['response'] = $this->response;

        //		return;
    }

    function set()
    {
        if ($this->agent_input == "extract") {
            return;
        }

        if (!isset($this->refreshed_at)) {
            $this->refreshed_at = $this->thing->time();
        }

        $city = new Variables($this->thing, "variables city " . $this->from);

        $city->setVariable("city_code", $this->city_code);
        $city->setVariable("city_name", $this->city_name);

        $city->setVariable("refreshed_at", $this->refreshed_at);

        $this->thing->log(
            $this->agent_prefix .
                ' set ' .
                $this->city_code .
                ' and ' .
                $this->city_name .
                ".",
            "INFORMATION"
        );

        $city = new Variables(
            $this->thing,
            "variables " . $this->city_code . " " . $this->from
        );

        $city->setVariable("city_name", $this->city_name);
        $city->setVariable("refreshed_at", $this->refreshed_at);
    }

    function isCode()
    {
        $city_zone = "05";

        foreach (range(1, 9999) as $n) {
            foreach ($this->cities as $city) {
                $city_code = $city_zone . str_pad($n, 4, "0", STR_PAD_LEFT);

                if ($this->getCity($city_code)) {
                    // Code doesn't exist
                    break;
                }
            }
            if ($n >= 9999) {
                $this->thing->log(
                    "No City code available in zone " . $city_zone . ".",
                    "WARNING"
                );
                return;
            }
        }
    }

    function nextCode()
    {
        $city_code_candidate = null;

        foreach ($this->cities as $city) {
            $city_code = strtolower($city['code']);
            if (
                $city_code == $city_code_candidate or
                $city_code_candidate == null
            ) {
                $city_code_candidate = str_pad(
                    rand(100, 9999),
                    8,
                    "9",
                    STR_PAD_LEFT
                );
            }
        }

        return $city_code;
    }

    function nextCity()
    {
        // devstack

        $this->thing->log("next city");
        // Pull up the current headcode
        $this->get();
        // So this should create a headcode in the next quantity unit.
    }

    function getCity($selector = null)
    {
        foreach ($this->cities as $city) {
            // Match the first matching place
            if ($selector == null or $selector == "") {
                $this->refreshed_at = $this->last_refreshed_at; // This is resetting the count.
                $this->city_name = $this->last_city_name;
                $this->city_code = $this->last_city_code;
                $this->city = new Variables(
                    $this->thing,
                    "variables " .
                        "city_" .
                        $this->city_code .
                        " " .
                        $this->from
                );
                return [$this->city_code, $this->city_name];
            }

            if ($city['code'] == $selector or $city['name'] == $selector) {
                $this->refreshed_at = $city['refreshed_at'];
                $this->city_name = $city['name'];
                $this->city_code = $city['code'];
                $this->city = new Variables(
                    $this->thing,
                    "variables " .
                        "city_" .
                        $this->city_code .
                        " " .
                        $this->from
                );
                return [$this->city_code, $this->city_name];
            }
        }

        // Not found on Stack

        $cities = [
            "burnaby",
            "vancouver",
            "north vancouver",
            "new westminster",
        ];
        foreach ($cities as $city_name) {
            if ($city_name == $selector) {
                return [999999, $selector];
            }
        }

        return true;
    }

    function getCities()
    {
        $this->citycode_list = [];
        $this->cityname_list = [];
        $this->cities = [];

        // See if a headcode record exists.
        $findagent_thing = new Findagent($this->thing, 'city');
        $count = count($findagent_thing->thing_report['things']);
        $this->thing->log(
            'Agent "City" found ' .
                count($findagent_thing->thing_report['things']) .
                " city Things."
        );

        if ($findagent_thing->thing_report['things'] == true) {
        }

        if (!$this->is_positive_integer($count)) {
            // No places found
        } else {
            foreach (
                array_reverse($findagent_thing->thing_report['things'])
                as $thing_object
            ) {
                $uuid = $thing_object['uuid'];

                $variables_json = $thing_object['variables'];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if (isset($variables['city'])) {
                    $city_code = $this->default_city_code;
                    $city_name = $this->default_city_name;
                    $refreshed_at = "meep getPlaces";

                    if (isset($variables['city']['city_code'])) {
                        $city_code = $variables['city']['city_code'];
                    }
                    if (isset($variables['city']['city_name'])) {
                        $city_name = $variables['city']['city_name'];
                    }
                    if (isset($variables['city']['refreshed_at'])) {
                        $refreshed_at = $variables['city']['refreshed_at'];
                    }

                    $this->cities[] = [
                        "code" => $city_code,
                        "name" => $city_name,
                        "refreshed_at" => $refreshed_at,
                    ];
                    $this->citycode_list[] = $city_code;
                    $this->cityname_list[] = $city_name;
                }
            }
        }

        // Return this-places filtered by latest check-in at each location.

        // Check if the place is already in the the list (this->places)
        $found = false;

        $filtered_cities = [];
        foreach (array_reverse($this->cities) as $key => $city) {
            $city_name = $city['name'];
            $city_code = $city['code'];

            if (!isset($city['refreshed_at'])) {
                continue;
            }

            $refreshed_at = $city['refreshed_at'];

            if (isset($filtered_cities[$city_name]['refreshed_at'])) {
                if (
                    strtotime($refreshed_at) >
                    strtotime($filtered_cities[$city_name]['refreshed_at'])
                ) {
                    $filtered_cities[$city_name] = [
                        "name" => $city_name,
                        "code" => $city_code,
                        'refreshed_at' => $refreshed_at,
                    ];
                }
                continue;
            }
            $filtered_cities[$city_name] = [
                "name" => $city_name,
                "code" => $city_code,
                'refreshed_at' => $refreshed_at,
            ];
        }

        // Sort by the refreshed at field
        $refreshed_at = [];
        foreach ($this->cities as $key => $row) {
            $refreshed_at[$key] = $row['refreshed_at'];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->cities);

        $this->old_cities = $this->cities;
        $this->cities = [];

        foreach ($this->old_cities as $key => $row) {
            if (strtotime($row['refreshed_at']) != false) {
                $this->cities[] = $row;
            }
        }

        // Add in a set of default cities
        $file = $this->resource_path . 'city/cities.txt';

        if (!file_exists($file)) {
            return true;
        }

        //        $contents = file_get_contents($file);

        $handle = @fopen($file, "r");

        if ($handle === false) {
            return true;
        }

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // process the line read.

                // It's just a list of city names.
                // Common ones.
                $city_name = $line;
                // This is where the city index will be called.
                $city_code = str_pad(RAND(1, 99999), 8, " ", STR_PAD_LEFT);

                $this->citycode_list[] = $city_code;
                $this->cityname_list[] = $city_name;
                $this->cities[] = ["code" => $city_code, "name" => $city_name];
            }

            fclose($handle);
        } else {
            // error opening the file.
        }

        // Indexing not implemented
        $this->max_index = 0;

        return [$this->citycode_list, $this->cityname_list, $this->cities];
    }

    function is_positive_integer($str)
    {
        return is_numeric($str) && $str > 0 && $str == round($str);
    }

    public function get($city_code = null)
    {
        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.
        if ($city_code == null) {
            $city_code = $this->city_code;
        }
        //var_dump($city_code);
        $this->city = new Variables(
            $this->thing,
            "variables " . "city_" . $city_code . " " . $this->from
        );

        $this->city_code = $this->city->getVariable("city_code");
        //var_dump($this->city_code);
        $this->city_name = $this->city->getVariable("city_name");
        $this->refreshed_at = $this->city->getVariable("refreshed_at");

        return [$this->city_code, $this->city_name];
    }

    function dropCity()
    {
        $this->thing->log($this->agent_prefix . "was asked to drop a City.");

        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->city)) {
            $this->city->Forget();
            $this->city = null;
        }

        $this->get();
    }

    function makeCity($city_code = null, $city_name = null)
    {
        // devstack cities defined by province
        return true;
        if ($city_name == null) {
            return true;
        }

        // See if the code or name already exists
        foreach ($this->cities as $city) {
            if ($city_code == $city['code'] or $city_name == $city['name']) {
                $this->city_name = $city['name'];
                $city_code = $city['code'];
                $this->last_refreshed_at = $city['refreshed_at'];
            }
        }
        if ($city_code == null) {
            $city_code = $this->nextCode();
        }

        $this->thing->log('will make a City for ' . $city_code . ".");
        $ad_hoc = true;
        $this->thing->log("is ready to make a City.");

        if ($ad_hoc != false) {
            $this->thing->log($this->agent_prefix . "is making a City.");
            $this->thing->log(
                $this->agent_prefix .
                    "was told the City is Useable but we might get kicked out."
            );

            $this->index = $this->max_index + 1;
            $this->max_index = $this->index;

            if ($city_code == false) {
                $city_code = $this->default_city_code;
                $city_name = $this->default_city_name;
            }

            $this->current_city_code = $city_code;
            $this->city_code = $city_code;

            $this->current_city_name = $city_name;
            $this->city_name = $city_name;
            $this->refreshed_at = $this->current_time;

            // This will write the refreshed at.
            $this->set();

            $this->getCities();
            $this->getCity($this->city_code);

            $this->city_thing = $this->thing;
        }

        $this->thing->log('Agent "City" found a City and pointed to it.');
    }

    function cityTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $city_time = "x";
            return $headcode_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $city_time = $this->hour . $this->minute;

        if ($input == null) {
            $this->city_time = $city_time;
        }

        return $city_time;
    }

    public function extractCities($input = null)
    {
        if (!isset($this->city_codes)) {
            $this->city_codes = [];
        }

        if (!isset($this->city_names)) {
            $this->city_names = [];
        }

        //$pattern = "|\d[A-Za-z]{1}\d{2}|"; //headcode pattern
        //$pattern = "|\{5}|"; // 5 digits

        $pattern = "|\d{6}$|";

        preg_match_all($pattern, $input, $m);
        $this->city_codes = $m[0];

        if (!isset($this->cities)) {
            $this->getCities();
        }

        foreach ($this->cities as $city) {
            $city_name = strtolower($city['name']);
            $city_code = strtolower($city['code']);

            if (empty($city_name)) {
                continue;
            }
            if (empty($city_code)) {
                continue;
            }

            // Thx. https://stackoverflow.com/questions/4366730/how-do-i-check-if-a-string-contains-a-specific-word
            if (strpos($input, $city_code) !== false) {
                $this->city_codes[] = $city_code;
            }

            if (strpos($input, $city_name) !== false) {
                $this->city_names[] = $city_name;
            }
        }

        $this->city_codes = array_unique($this->city_codes);
        $this->city_names = array_unique($this->city_names);

        return [$this->city_codes, $this->city_names];
    }

    public function extractCity($input)
    {
        $this->city_name = null;
        $this->city_code = null;

        list($city_codes, $city_names) = $this->extractCities($input);

        if (count($city_codes) + count($city_names) == 1) {
            if (isset($city_codes[0])) {
                $this->city_code = $city_codes[0];
            }
            if (isset($city_names[0])) {
                $this->city_name = $city_names[0];
            }

            $this->thing->log(
                $this->agent_prefix .
                    'found a city code (' .
                    $this->city_code .
                    ') in the text.'
            );
            return [$this->city_code, $this->city_name];
        }

        if (count($city_names) == 1) {
            $this->city_name = $this->city_names[0];
        }

        return [$this->city_code, $this->city_name];
    }

    function isCity($input = null)
    {
        if ($input == null) {
            return false;
        }

        if (!isset($this->resource)) {
            $this->resource = new Resource($this->thing, "extract");
        }

        $this->resource->findResource("Geographical Name", $input);

        foreach ($this->resource->resources as $name => $resources) {
            foreach ($resources as $resource) {
                $text = implode(" ", $resource);
                if (strpos(strtolower($text), strtolower($input)) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    function assertCity($input)
    {
        $whatIWant = $input;
        if (($pos = strpos(strtolower($input), "city is")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("city is"));
        } elseif (($pos = strpos(strtolower($input), "city")) !== false) {
            $whatIWant = substr(strtolower($input), $pos + strlen("city"));
        }
        $filtered_input = ltrim(strtolower($whatIWant), " ");
        $city = $this->getCity($filtered_input);

        if ($city == true) {
            // refactor
            // Didn't show up on the stack as a city
            // See if it's in the city resource
            $is_city = $this->isCity(ucwords($filtered_input));

            if (!$is_city) {
                return true;
            }

            foreach (
                $this->resource->resources[ucwords($filtered_input)]
                as $id => $resource
            ) {
                if ($resource['code'] == "CITY") {
                    $this->refreshed_at = $this->thing->time();
                    $city_id = $resource['id'];
                    $city_name = $resource['name'];
                    break;
                }
            }
        } else {
            $city_id = $city[0];
            $city_name = $city[1];
        }

        $this->refreshed_at = $this->thing->time();
        $this->city_code = $city[0];
        $this->current_city_name = $city_name;
        $this->city_name = $city_name;
    }

    function readCity()
    {
        $this->thing->log("read");
    }

    function addCity()
    {
        $this->get();
        return;
    }

    public function makeMessage()
    {
        $message = "City is " . ucwords($this->city_name) . ".";
        $this->message = $message;
        $this->thing_report['message'] = $message;
    }

    function makeTXT()
    {
        if (!isset($this->citycode_list)) {
            $this->getCities();
        }

        $this->getCities();

        if (!isset($this->city)) {
            $txt = "Not here";
        } else {
            $txt =
                'These are CITIES for PLACE ' .
                $this->railway_city->nuuid .
                '. ';
        }
        $txt .= "\n";
        $txt .= "\n";

        //$txt .= str_pad("INDEX", 7, ' ', STR_PAD_LEFT);
        $txt .= " " . str_pad("NAME", 40, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("CODE", 8, " ", STR_PAD_LEFT);

        $txt .= "\n";
        $txt .= "\n";

        // Places must have both a name and a code.  Otherwise it's not a place.
        foreach ($this->cities as $key => $city) {
            $txt .=
                " " .
                str_pad(
                    strtoupper(trim($city['name'])),
                    40,
                    " ",
                    STR_PAD_RIGHT
                );
            $txt .=
                " " .
                "  " .
                str_pad(strtoupper(trim($city['code'])), 5, "X", STR_PAD_LEFT);
            if (isset($city['refreshed_at'])) {
                $txt .=
                    " " .
                    "  " .
                    str_pad(
                        strtoupper($city['refreshed_at']),
                        15,
                        "X",
                        STR_PAD_LEFT
                    );
            }
            $txt .= "\n";
        }

        $txt .= "\n";
        $txt .= "Last city " . $this->last_city_name . "\n";
        $txt .= "Now at " . $this->city_name;

        $this->thing_report['txt'] = $txt;
        $this->txt = $txt;
    }

    public function makeSMS()
    {
        $this->inject = null;
        $s = $this->inject;
        $sms = "CITY " . strtoupper($this->city_name);

        if (!empty($this->inject)) {
            $sms .= " | " . $s;
        }

        if (!empty($this->city_code)) {
            $sms .= " | " . trim(strtoupper($this->city_code));
        }

        if (!empty($this->city_code)) {
            $sms .=
                " | " . $this->web_prefix . 'thing/' . $this->uuid . '/city';
        }

        $sms .= " | " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report['sms'] = $sms;
    }

    function makeWeb()
    {
        $link = $this->web_prefix . 'thing/' . $this->uuid . '/agent';

        $link_txt = $this->web_prefix . 'thing/' . $this->uuid . '/city.txt';

        $this->node_list = ["city" => ["translink", "job"]];
        // Make buttons
        $this->thing->choice->Create(
            $this->agent_name,
            $this->node_list,
            "city"
        );
        $choices = $this->thing->choice->makeLinks('city');

        $web = '<a href="' . $link . '">';

        // Get a html image if there is one
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

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/city.txt';
        $web .= '<a href="' . $link . '">city.txt</a>';
        $web .= " | ";

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/city.log';
        $web .= '<a href="' . $link . '">city.log</a>';
        $web .= " | ";

        $link = $this->web_prefix . 'thing/' . $this->uuid . '/' . "city";
        $web .= $this->city_name . '';
        $web .= " | ";
        $web .= '<a href="' . $link . '">' . "city" . '</a>';

        $web .= "<br>";

        $web .= "<br>";

        if (!isset($this->refreshed_at)) {
            $ago = "X";
        } else {
            $ago = $this->thing->human_time(
                strtotime($this->thing->time()) - strtotime($this->refreshed_at)
            );
        }

        $web .= "Last asserted about " . $ago . " ago.";

        $web .= "<br>";

        $this->thing_report['web'] = $web;
    }

    public function makeImage()
    {
        // devstack refactor as Image

        $text = strtoupper($this->city_name);

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

        $font = $this->resource_path . 'roll/KeepCalm-Medium.ttf';

        // Add some shadow to the text
        //imagettftext($image, 40, 0, 0, 75, $grey, $font, $number);
        $sizes_allowed = [72, 36, 24, 12, 6];

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
            $width = imagesx($image);
            $height = imagesy($image);
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
        imagestring(
            $image,
            2,
            $image_width - 75,
            10,
            $this->city_code,
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

    function ImageRectangleWithRoundedCorners(
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
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.
        //		$to = $this->thing->from;
        //		$from = "city";

        //$choices = $this->thing->choice->makeLinks($this->state);
        $choices = false;
        $this->thing_report['choices'] = $choices;

        // Allow for indexing.
        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index; //
        }

        //      $this->makeSMS();

        $this->thing_report['email'] = $this->sms_message;

        //    $this->makeMessage();

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        } else {
            $this->thing_report['info'] =
                'Agent input was "' . $this->agent_input . '".';
        }

        //      $this->makeWeb();
        //    $this->makeTXT();

        $this->thing_report['help'] =
            'This is a City.  The union of a code and a name.';

        //	return;
    }

    function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    function lastCity()
    {
        // devstack

        $this->last_city = new Variables(
            $this->thing,
            "variables city " . $this->from
        );
        $this->last_city_code = $this->last_city->getVariable('city_code');
        $this->last_city_name = $this->last_city->getVariable('city_name');

        // This doesn't work
        $this->last_refreshed_at = $this->last_city->getVariable(
            'refreshed_at'
        );
        return;

        // So do it the hard way

        if (!isset($this->cities)) {
            $this->getCities();
        }

        foreach (array_reverse($this->cities) as $key => $city) {
            if ($city['name'] == $this->last_city_name) {
                $this->last_refreshed_at = $city['refreshed_at'];
                break;
            }
        }
    }

    public function readSubject()
    {
        $this->response = null;
        $this->num_hits = 0;

        switch (true) {
            case $this->agent_input == "extract":
                $input = strtolower($this->from . " " . $this->subject);
                break;
            case isset($this->agent_input):
                $input = strtolower($this->agent_input);
                break;
            case $this->agent_input == null:
                $input = strtolower($this->from . " " . $this->subject);
                break;
        }

        // $prior_uuid = null;

        // Is there a place in the provided datagram
        $this->extractCity($input);
        if ($this->agent_input == "extract") {
            return;
        }

        $pieces = explode(" ", strtolower($input));

        if (count($pieces) == 1) {
            if ($input == 'city') {
                $this->getCity();
                $this->response = "Last 'city' retrieved.";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'next':
                            $this->thing->log("read subject nextheadcode");
                            $this->nextCity();
                            break;

                        case 'drop':
                            $this->dropCity();
                            break;

                        case 'make':
                        case 'new':
                        case 'city':
                        case 'create':
                        case 'add':
                            $this->assertCity(strtolower($input));

                            if (empty($this->city_name)) {
                                $this->city_name = "X";
                            }

                            $this->response =
                                'Asserted City and found ' .
                                strtoupper($this->city_name) .
                                ".";
                            return;
                        // break;

                        default:
                    }
                }
            }
        }

        if ($this->city_code != null) {
            $this->getCity($this->city_code);
            $this->thing->log(
                'using extracted city_code ' . $this->city_code . ".",
                "INFORMATION"
            );
            $this->response = $this->city_code . " used to retrieve a City.";
            return;
        }

        if ($this->city_name != null) {
            $this->getCity($this->city_name);
            $this->thing->log(
                'using extracted city_name ' . $this->city_name . ".",
                "INFORMATION"
            );
            $this->response = strtoupper($this->city_name) . " retrieved.";
            $this->assertCity($this->city_name);
            return;
        }

        if ($this->last_city_code != null) {
            $this->getCity($this->last_city_code);
            $this->thing->log(
                'using extracted last_city_code ' . $this->last_city_code . ".",
                "INFORMATION"
            );
            $this->response =
                "Last city " .
                $this->last_city_code .
                " used to retrieve a City.";
            return;
        }

        // so we get here and this is null cityname, null city_id.
        // so perhaps try just loading the city by name

        $city = strtolower($this->subject);

        if (!$this->getCity(strtolower($city))) {
            // City was found
            // And loaded
            $this->response = $city . " used to retrieve a City.";
            return;
        }

        $this->makeCity(null, $city);
        $this->thing->log(
            'using default_city_code ' . $this->default_city_code . ".",
            "INFORMATION"
        );

        $this->response = "Made a City called " . $city . ".";
        return;

        if (
            $this->isData($this->city_name) or $this->isData($this->city_code)
        ) {
            $this->set();
            return;
        }

        return false;
    }
}
