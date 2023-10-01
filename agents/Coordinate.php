<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Coordinate extends Agent
{
    // This is a coordinate.

    //

    // This is related agent to Place.  They can probaby do a lot for somebody.

    public $var = "hello";

    function init()
    {

        $this->keywords = [
            "coordinate",
            "next",
            "last",
            "nearest",
            "accept",
            "clear",
            "drop",
            "add",
            "new",
            "here",
            "there",
        ];

        $this->default_coordinate =
            $this->thing->container["api"]["coordinate"]["default_coordinate"];

        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        $this->default_alias = "Thing";

        $this->test = "Development code"; // Always iterative.


        $this->state = null; // to avoid error messages
        $this->lastCoordinate();
    }

    function set()
    {
        if ($this->agent_input == "extract") {
            return;
        }

        if (!isset($this->refreshed_at)) {
            $this->refreshed_at = $this->thing->time();
        }
        $string_coordinate = $this->stringCoordinate($this->coordinate);
        if ($this->coordinate == true and !is_array($this->coordinate)) {
            return;
        }

        //$this->refreshed_at = $this->current_time;
        $coordinate_variable = new Variables(
            $this->thing,
            "variables coordinate " . $this->from
        );

        $coordinate_variable->setVariable("coordinate", $string_coordinate);
        $coordinate_variable->setVariable("refreshed_at", $this->refreshed_at);

        $this->thing->log(
            $this->agent_prefix .
                " set " .
                $this->stringCoordinate($this->coordinate) .
                ".",
            "INFORMATION"
        );

        //$coordinate_variable = new Variables($this->thing, "variables " . $string_coordinate . " " . $this->from);
        //$coordinate_variable->setVariable("refreshed_at", $this->refreshed_at);

        return;
    }

    // TODO

    function isCoordinate()
    {
        $place_zone = "05";
        //$place_code = $place_zone  . str_pad(rand(0,999) + 1,6,  '0', STR_PAD_LEFT);

        foreach (range(1, 9999) as $n) {
            foreach ($this->coordinates as $coordinate) {
                $place_code = $place_zone . str_pad($n, 4, "0", STR_PAD_LEFT);
                if ($this->getCoordinate($coordinate)) {
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

    // TODO

    function nextCoordinate()
    {
        $place_code_candidate = null;

        foreach ($this->coordinates as $place) {
            $place_code = strtolower($place["code"]);
            if (
                $place_code == $place_code_candidate or
                $place_code_candidate == null
            ) {
                $place_code_candidate = str_pad(
                    rand(100, 9999),
                    8,
                    " ",
                    STR_PAD_LEFT
                );
            }
        }

        return $place_code;
    }

    function getCoordinate($selector = null)
    {
        if (!isset($this->coordinates)) {
            $this->getCoordinates();
        }

        foreach ($this->coordinates as $coordinate) {
            // Match the first matching place

            if ($selector == null or $selector == "") {
                $this->refreshed_at = $this->last_refreshed_at; // This is resetting the count.

                if (is_array($this->last_coordinate)) {
                    $this->thing->console( "lastcoord" );
                    throw \Exception("Last coordinate");
                }
                $this->coordinate = $this->last_coordinate;

                $this->coordinate_variable = new Variables(
                    $this->thing,
                    "variables " . $this->coordinate . " " . $this->from
                );
                return $this->coordinate;
            }

            // Get closest
            /*
            if (($place['code'] == $selector) or ($place['name'] == $selector)) {

                $this->refreshed_at = $coordinate['refreshed_at'];
                //$this-> = $coordinate['coordinate'];

                $this->coordinate_variable = new Variables($this->thing, "variables " . $this->coordinate . " " . $this->from);

                return array($this->coordinate);
            }
*/
        }

        return true;
    }

    //function makeCoordinate($v)
    //{

    //}

    function getCoordinates()
    {
        $this->coordinate_list = [];
        $this->coordinates = [];

        // See if a headcode record exists.
        $findagent_thing = new Findagent($this->thing, "coordinate");

$things = $findagent_thing->thing_report["things"];


if ($things === true) {
$this->coordinates = [];
$this->coordinate = null;
return true;
}

        $count = count($things);
        $this->thing->log(
            'Agent "Coordinate" found ' .
                count($findagent_thing->thing_report["things"]) .
                " coordinate Things."
        );

        //        if ($findagent_thing->thing_reports['things'] == false) {
        //                $place_code = $this->default_place_code;
        //                $place_name = $this->default_place_name;
        //            return array($this->placecode_list, $this->placename_list, $this->places);
        //        }


        if (!$this->is_positive_integer($count)) {
            // No places found
        } else {
            foreach (
                array_reverse($things)
                as $thing_object
            ) {
                $uuid = $thing_object["uuid"];

                $variables_json = $thing_object["variables"];
                $variables = $this->thing->json->jsontoArray($variables_json);

                if (isset($variables["coordinate"])) {
                    $coordinate = $this->default_coordinate;
                    $refreshed_at = "meep getPlaces";

                    if (isset($variables["coordinate"]["coordinate"])) {
                        $coordinate = $variables["coordinate"]["coordinate"];
                    }
                    if (isset($variables["coordinate"]["refreshed_at"])) {
                        $refreshed_at =
                            $variables["coordinate"]["refreshed_at"];
                    }

                    // If it isn't an array try and convert text to array
                    if (!is_array($coordinate)) {
                        $coordinate = $this->arrayCoordinate($coordinate);
                    }

                    $this->coordinates[] = [
                        "coordinate" => $coordinate,
                        "refreshed_at" => $refreshed_at,
                    ];
                    $this->coordinate_list[] = $coordinate;
                }
            }
        }

        // Return this-places filtered by latest check-in at each location.

        // Check if the place is already in the the list (this->places)
        $found = false;

        $filtered_coordinates = [];
        foreach (array_reverse($this->coordinates) as $key => $coordinate) {
            $coordinate = $coordinate["coordinate"];

            if (!isset($coordinate["refreshed_at"])) {
                continue;
            }

            $refreshed_at = $coordinate["refreshed_at"];

            if (isset($filtered_coordinates[$coordinate]["refreshed_at"])) {
                if (
                    strtotime($refreshed_at) >
                    strtotime($filtered_places[$place_name]["refreshed_at"])
                ) {
                    $filtered_coordinates[$coordinate] = [
                        "coordinate" => $coordinate,
                        "refreshed_at" => $refreshed_at,
                    ];
                }
                continue;
            }

            $filtered_coordinates[$coordinate] = [
                "coordinate" => $coordinate,
                "refreshed_at" => $refreshed_at,
            ];
        }

        $refreshed_at = [];
        foreach ($this->coordinates as $key => $row) {
            $refreshed_at[$key] = $row["refreshed_at"];
        }
        array_multisort($refreshed_at, SORT_DESC, $this->coordinates);

        $this->old_coordinates = $this->coordinates;
        $this->coordinates = [];
        foreach ($this->old_coordinates as $key => $row) {
            if (strtotime($row["refreshed_at"]) != false) {
                $this->coordinates[] = $row;
            }
        }

        // Indexing not implemented
        $this->max_index = 0;

        return [$this->coordinate_list, $this->coordinates];
    }

    function is_positive_integer($str)
    {
        return is_numeric($str) && $str > 0 && $str == round($str);
    }

    public function get($coordinate = null)
    {
        // This is a request to get the Place from the Thing
        // and if that doesn't work then from the Stack.
        //if ($place_code == null) {
        //    $place_code = $this->place_code;
        //}

        $this->variables_coordinate = new Variables(
            $this->thing,
            "variables " . $coordinate . " " . $this->from
        );

        $coordinate = $this->variables_coordinate->getVariable("coordinate");
        $this->coordinate = $this->arrayCoordinate($coordinate);
        $this->refreshed_at = $this->variables_coordinate->getVariable(
            "refreshed_at"
        );
        return $this->coordinate;
    }

    function dropPlace()
    {
        $this->thing->log($this->agent_prefix . "was asked to drop a Place.");

        // If it comes back false we will pick that up with an unset headcode thing.

        if (isset($this->coordinate)) {
            $this->coordinate->Forget();
            $this->coordinate = null;
        }

        $this->get();
    }

    function makeCoordinate($coordinate = null)
    {
        if ($coordinate == null) {
            return true;
        }

        /*
        if (!isset($this->coordinates)) {$this->getCoordinates();}

        // See if the coordination is already tagged
        foreach ($this->coordinates as $coordinate_object) {
            if ($coordinate == $coordinate_object['coordinate']) {return true;}
        }
*/
        //if ($coordinate == null) {$coordinate = $this->nextCoordinate();}
        $this->thing->log(
            'Agent "Coordinate" will make a Coordinate for ' .
                $this->stringCoordinate($coordinate) .
                "."
        );

        $this->current_coordinate = $coordinate;
        $this->coordinate = $coordinate;
        $this->refreshed_at = $this->current_time;

        // This will write the refreshed at.
        $this->set();

        $this->thing->log(
            'Agent "Coordinate" found a Coordinate and pointed to it.'
        );
    }

    function coordinateTime($input = null)
    {
        if ($input == null) {
            $input_time = $this->current_time;
        } else {
            $input_time = $input;
        }

        if ($input == "x") {
            $coordinate_time = "x";
            return $coordinate_time;
        }

        $t = strtotime($input_time);

        $this->hour = date("H", $t);
        $this->minute = date("i", $t);

        $coordinate_time = $this->hour . $this->minute;

        if ($input == null) {
            $this->coordinate_time = $coordinate_time;
        }

        return $coordinate_time;
    }

    // Currently just tuple extraction.
    // With negative numbers.

    public function extractCoordinates($input = null)
    {
        // From a string

        //https://gist.github.com/cgudea/7c558138cb48b36e785b
        //# regular expressions (all the magic)
        //dmsLatRegEx = '^-?((90\/[0]{0,}\/[0]{0,}$)|([1-8]?\d))(\/|\:|\ )(([1-5]?\d))(\/|\:|\ )[1-5]?\d(\.\d{0,})?$'
        //dmsLonRegEx = '^-?((180(\/|\:| )0(\/|\:| )0((\.0{0,})?))|(([1]?[1-7]\d)|\d?\d)(\/|\:| )([1-5]?\d)(\/|\:| )[1-5]?\d(\.\d{0,})?$)'
        //decimalRegEx = "^-?(180((\.0{0,})?)|([1]?[0-7]?\d(\.\d{0,})?))$"
        //mgrsRegEx = "^\d{1,2}[^ABIOYZabioyz][A-Za-z]{2}([0-9][0-9])+$"
        //utmRegEx = "^\d(\/|\:| |)[^aboiyzABOIYZ\d\[-\` -@](\/|\:| |)\d{2,}$"

        // https://blogs.msdn.microsoft.com/raulperez/2011/03/01/regular-expressions-for-float-values-and-coordinates/
        // 2D : (3.1,23,5) =  \((?<X>\d(\.\d*)?),(?<Y>\d(\.\d*)?)\)
        // 3D : (3.1,23,5,90) =  \((?<X>\d(\.\d*)?),(?<Y>\d(\.\d*)?),(?<Z>\d(\.\d*)?)\)

        //        if (!isset($this->coordinates)) {
        //            $this->coordinates = array();
        //        }

        //$input = "(14) (5,6)";
        //$pattern = "|\d[A-Za-z]{1}\d{2}|"; //headcode pattern
        //$pattern = "|\{5}|"; // 5 digits

        //        $pattern = "|\d{6}$|";
        //        $pattern = "|\((?<X>\d(\.\d*)?),(?<Y>\d(\.\d*)?),(?<Z>\d(\.\d*)?)\)|";
        $pattern = "|\((?<X>\d(\.\d*)?),(?<Y>\d(\.\d*)?)\)|";
        $pattern = "|(?<X>\d(\.\d*)?),(?<Y>\d(\.\d*)?)|";
        $pattern = "|(?<X>\d(\.\d*)?),(?<Y>\d(\.\d*)?),(?<Z>\d(\.\d*)?)|";
        $pattern =
            "|(?<X>\-?\d(\.\d*)?),(?<Y>\-?\d(\.\d*)?),(?<Z>\-?\d(\.\d*)?)|"; // include (-) numbers
        $pattern =
            "|(?<X>\-?\\+?\d(\.\d*)?),(?<Y>\-?\+?\d(\.\d*)?),(?<Z>\-?\+?\d(\.\d*)?)|"; // include (-) numbers

        $pattern =
            "|(?<X>\-?\\+?\d+(\.\d*)?),(?<Y>\-?\+?\d+(\.\d*)?),(?<Z>\-?\+?\d+(\.\d*)?)|"; // include (-) numbers

        //$pattern = "|\((?<X>\d(\.\d*)?),(?<Y>\d(\.\d*)?)\)|";

        preg_match_all($pattern, $input, $m);

        if (!isset($m["Z"][0])) {
            // try 2d
            //$pattern = "|\((?<X>\d(\.\d*)?),(?<Y>\d(\.\d*)?),(?<Z>\d(\.\d*)?)\)|";
            //        $pattern = "|(?<X>\d(\.\d*)?),(?<Y>\d(\.\d*)?),(?<Z>\d(\.\d*)?)|";

            $pattern = "|(?<X>\d(\.\d*)?),(?<Y>\d(\.\d*)?)|";
            $pattern = "|(?<X>\-?\\+?\d(\.\d*)?),(?<Y>\-?\+?\d(\.\d*)?)|"; // include (-) numbers
            $pattern = "|(?<X>\-?\\+?\d+(\.\d*)?),(?<Y>\-?\+?\d+(\.\d*)?)|"; // include (-) numbers

            preg_match_all($pattern, $input, $m);
        } else {
            //$x = $m['X'][0];
            //$y = $m['Y'][0];

            //$coordinate = array($x,$y);
        }

        if (!isset($m["Y"][0])) {
            $pattern = "|(?<X>\d(\.\d*)?)|";
            $pattern = "|(?<X>\-?\\+?\d(\.\d*)?)|"; // include (-) numbers
            $pattern = "|(?<X>\-?\\+?\d+(\.\d*)?)|"; // include (-) numbers

            preg_match_all($pattern, $input, $m);
        } else {
            //$x = $m['X'][0];
            //$y = $m['Y'][0];

            //$coordinate = array($x,$y);
        }

        if (!isset($m["X"][0])) {
            $this->coordinate = true;
            return;
        }

        $x = $m["X"][0];
        if (isset($m["Y"][0])) {
            $y = $m["Y"][0];
        }
        if (isset($m["Z"][0])) {
            $z = $m["Z"][0];
        }

        // refactor this.

        $coordinate = [$x];
        if (isset($m["Y"][0])) {
            $coordinate = [$x, $y];
        }
        if (isset($m["Z"][0])) {
            $coordinate = [$x, $y, $z];
        }

        $coordinates[] = $coordinate;

        //$this->coordinates = array_unique($this->coordinates);
        return $coordinates;
    }

    public function extractCoordinate($input)
    {
        $this->coordinate = null;

        if (is_array($input)) {
            $this->coordinate = true;
            return;
        }

        $coordinates = $this->extractCoordinates($input);

        if (is_array($coordinates) and count($coordinates) == 1) {
            //if ( ( count($coordinates) ) == 1) {
            if (isset($coordinates[0])) {
                $this->coordinate = $coordinates[0];
            }

            $this->thing->log(
                $this->agent_prefix .
                    "found a coordinate " .
                    $this->stringCoordinate() .
                    " in the text."
            );
            return $this->coordinate;
        }

        if (is_array($coordinates) and count($coordinates) == 1) {
            //if (count($coordinates) == 1) {
            $this->coordinate = $this->coordinates[0];
        }
        return $this->coordinate;
    }

    // Assert that the string has a coordinate.
    function assertCoordinate($input)
    {
        if (($pos = strpos(strtolower($input), "coordinate is")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("coordinate is")
            );
        } elseif (($pos = strpos(strtolower($input), "coordinate")) !== false) {
            $whatIWant = substr(
                strtolower($input),
                $pos + strlen("coordinate")
            );
        }

        $filtered_input = ltrim(strtolower($whatIWant), " ");

        $coordinate = $this->extractCoordinate($filtered_input);

        if ($coordinate) {
            //true so make a place
            $this->makeCoordinate($this->coordinate);
        }

        //        $this->coordinate = $coordinate;
    }

    public function makeWeb()
    {
        $test_message =
            "<b>COORDINATE " .
            $this->stringCoordinate($this->coordinate) .
            "</b>" .
            "<br>";

        if (!isset($this->refreshed_at)) {
            $test_message .= "<br>Thing just happened.";
        } else {
            $refreshed_at = $this->refreshed_at;

            $test_message .= "<p>";
            $ago = $this->thing->human_time(
                strtotime($this->thing->time()) - strtotime($refreshed_at)
            );
            $test_message .= "<br>Thing happened about " . $ago . " ago.";
        }
        //$test_message .= '<br>' .$this->whatisthis[$this->state] . '<br>';

        //$this->thing_report['sms'] = $this->message['sms'];
        $this->thing_report["web"] = $test_message;
    }

    function makeTXT()
    {
        if (!isset($this->coordinates)) {
            $this->getCoordinates();
        }

        if (!isset($this->coordinate)) {
            $txt = "Not here";
        } else {
            $txt =
                "These are COORDINATEs for RAILWAY " .
                $this->last_coordinate_variable->nuuid .
                ". ";
        }
        $txt .= "\n";
        $txt .= "\n";

        $txt .= " " . str_pad("COORDINATE", 19, " ", STR_PAD_RIGHT);
        $txt .= " " . str_pad("REFRESHED AT", 25, " ", STR_PAD_RIGHT);

        $txt .= "\n";
        $txt .= "\n";

        foreach ($this->coordinates as $key => $coordinate) {
            if (isset($coordinate["refreshed_at"])) {
                $t = $this->stringCoordinate($coordinate["coordinate"]);
                $txt .= " " . "  " . str_pad($t, 15, " ", STR_PAD_LEFT);

                $txt .=
                    " " .
                    "  " .
                    str_pad(
                        strtoupper($coordinate["refreshed_at"]),
                        25,
                        " ",
                        STR_PAD_RIGHT
                    );
            }
            $txt .= "\n";
        }

        $txt .= "\n";
        $txt .=
            "Last place " .
            $this->stringCoordinate($this->last_coordinate) .
            "\n";
        $txt .= "Now at " . $this->stringCoordinate($this->coordinate);

        $this->thing_report["txt"] = $txt;
        $this->txt = $txt;
    }

    // String to array
    function arrayCoordinate($input)
    {

        $coordinates = $this->extractCoordinates($input);
        $coordinate_array = true;

        if (is_array($coordinates) and count($coordinates) == 1) {
            $coordinate_array = $coordinates[0];
        }

        return $coordinate_array;
    }

    function stringCoordinate($coordinate = null)
    {
        if ($coordinate == null) {
            $coordinate = $this->coordinate;
        }

        if (!is_array($coordinate)) {
            $this->coordinate_string = true;
            return $this->coordinate_string;
        }

        $this->coordinate_string = "(" . implode(",", $coordinate) . ")";
        return $this->coordinate_string;
    }

    public function makeSMS()
    {
        $this->inject = null;
        $s = $this->inject;
        $string_coordinate = $this->stringCoordinate($this->coordinate);

        $sms = "COORDINATE " . $string_coordinate;

        if (!empty($this->inject)) {
            $sms .= " | " . $s;
        }

        $sms .= " " . $this->response;

        $this->sms_message = $sms;
        $this->thing_report["sms"] = $sms;
    }

    public function respondResponse()
    {
        // Thing actions

        $this->thing->flagGreen();

        // Generate email response.

        $choices = false;
        $this->thing_report["choices"] = $choices;

        // Get available for place.  This would be an available agent.
        //$available = $this->thing->human_time($this->available);

        // Allow for indexing.
        if (!isset($this->index)) {
            $index = "0";
        } else {
            $index = $this->index; //
        }

        $this->thing_report["email"] = $this->sms_message;
        $this->thing_report["message"] = $this->sms_message; // NRWTaylor 4 Oct - slack can't take html in $test_message;

        if (!$this->thing->isData($this->agent_input)) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report["info"] = $message_thing->thing_report["info"];
        } else {
            $this->thing_report["info"] =
                'Agent input was "' . $this->agent_input . '".';
        }

        //        $this->makeTXT();

        $this->thing_report["help"] =
            "Stores a 1- 2- or 3-dimensional co-ordinate on the stack. Try COORDINATE 2.5,7.3,23.2";

        //        return;
    }

    function isData($variable)
    {
        if ($variable !== false and $variable !== true and $variable != null) {
            return true;
        } else {
            return false;
        }
    }

    function lastCoordinate()
    {
        $this->last_coordinate_variable = new Variables(
            $this->thing,
            "variables coordinate " . $this->from
        );
        // $this->last_coordinate = $this->last_coordinate_variable->getVariable('coordinate');

        // Get a textual representation of a coordinate
        $coordinate = $this->last_coordinate_variable->getVariable(
            "coordinate"
        );

        // Turn the text into an array
        $this->last_coordinate = $this->arrayCoordinate($coordinate);

        // This doesn't work
        $this->last_refreshed_at = $this->last_coordinate_variable->getVariable(
            "refreshed_at"
        );

        return;

        // So do it the hard way

        if (!isset($this->coordinates)) {
            $this->getCoordinates();
        }
        $last_coordinate = $this->coordinates[0];

        $this->last_coordinate = $last_coordinate["coordinate"];
        $this->last_refreshed_at = $last_coordinate["refreshed_at"];

        //        foreach(array_reverse($this->coordinates) as $key=>$coordinate) {

        //            if ($coordinate['coordinate'] == $this->last_coordinate) {
        //                $this->last_refreshed_at = $coordinate['refreshed_at'];
        //                break;
        //            }

        //        }
    }

    public function readSubject()
    {
        $this->num_hits = 0;

        switch (true) {
            case $this->agent_input == "extract":
                //$input = strtolower($this->from . " " . $this->subject);
                $input = strtolower($this->subject);

                break;
            case $this->agent_input != null:
                $input = strtolower($this->agent_input);
                break;
            case true:
                // What is going to be found in from?
                // Allows place name extraction from email address.
                // Allows place code extraction from "from"
                // Internet of Things.  Sometimes all you have is the Thing's address.
                // And that's a Place.
                //$input = strtolower($this->from . " " . $this->subject);
                $input = strtolower($this->subject);
        }

        $prior_uuid = null;

        // Is there a place in the provided datagram
        $this->extractCoordinate($input);

        if ($this->agent_input == "extract") {
            $this->response .= "Extracted coordinate(s). ";
            return;
        }
        $pieces = explode(" ", strtolower($input));

        // So this is really the 'sms' section
        // Keyword
        if (count($pieces) == 1) {
            if ($input == "coordinate") {
                $this->lastCoordinate();

                $this->coordinate = $this->last_coordinate;
                $this->refreshed_at = $this->last_refreshed_at;

                $this->response .= "Last coordinate retrieved. ";
                return;
            }
        }

        foreach ($pieces as $key => $piece) {
            foreach ($this->keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case "next":
                            $this->thing->log("read subject next coordinate");
                            $this->nextCoordinate();
                            break;

                        case "drop":
                            //$this->thing->log("read subject nextheadcode");
                            $this->dropCoordinate();
                            break;
                        case "make":
                        case "new":
                        case "coordinate":
                        case "create":
                        case "add":
                            if (is_array($this->coordinate)) {
                                $this->response .=
                                    "Asserted coordinate and found " .
                                    $this->stringCoordinate($this->coordinate) .
                                    ". ";
                                return;
                            }

                            if ($this->coordinate) {
                                // Coordinate not provided in string
                                $this->lastCoordinate();
                                $this->coordinate = $this->last_coordinate;
                                $this->refreshed_at = $this->last_refreshed_at;
                                $this->response .=
                                    "Asserted coordinate and found last " .
                                    $this->stringCoordinate($this->coordinate) .
                                    ". ";
                                return;
                            }

                            $this->assertCoordinate(strtolower($input));

                            if (empty($this->coordinate)) {
                                $this->coordinate = "X";
                            }

                            $this->response .=
                                "Asserted Coordinate and found " .
                                $this->stringCoordinate($this->coordinate) .
                                ". ";

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

        if ($this->coordinate != null) {
            $this->getCoordinate($this->coordinate);
            $this->thing->log(
                $this->agent_prefix .
                    "using extracted coordinate " .
                    $this->stringCoordinate() .
                    ".",
                "INFORMATION"
            );
            $this->response .=
                $this->stringCoordinate() . " used to retrieve a Coordinate. ";

            return;
        }

        if ($this->last_coordinate != null) {
            $this->getCoordinate($this->last_coordinate);
            $this->thing->log(
                $this->agent_prefix .
                    "using extracted last_coordinate " .
                    $this->last_coordinate .
                    ".",
                "INFORMATION"
            );
            $this->response .=
                "Last coordinate " .
                $this->last_coordinate .
                " used to retrieve a Coordinate. ";

            return;
        }

        // so we get here and this is null placename, null place_id.
        // so perhaps try just loading the place by name

        $coordinate = strtolower($this->subject);

        if (!$this->getCoordinate(strtolower($coordinate))) {
            // Place was found
            // And loaded
            $this->response .= $coordinate . " used to retrieve a Coordinate. ";

            return;
        }

        $this->makeCoordinate($coordinate);

        $this->thing->log(
            $this->agent_prefix .
                "using default_coordinate " .
                implode(" ", $this->default_coordinate) .
                ".",
            "INFORMATION"
        );

        $this->response .= "Made a Coordinate called " . $coordinate . ". ";
        return;

        if (
            $this->isData($this->coordinate) or $this->isData($this->coordinate)
        ) {
            $this->set();
            return;
        }

        return false;
    }
}
