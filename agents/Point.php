<?php
namespace Nrwtaylor\StackAgentThing;

ini_set("display_startup_errors", 1);
ini_set("display_errors", 1);
error_reporting(-1);

ini_set("allow_url_fopen", 1);

class Point extends Agent
{
    public $var = "hello";

    function init()
    {
        $this->default_state = "X";

        $this->keyword = "point";

        $this->test = "Development code"; // Always

        $this->resource_path = $GLOBALS["stack_path"] . "resources/";

        // Get the current identities uuid.
        $default_ship_id = new Identity($this->thing, "identity");
        $this->default_point_id = $default_ship_id->uuid;

        // Set up default ship settings
        $this->verbosity = 1;
        $this->requested_state = null;
        //        $this->default_state = "green";
        //        $this->node_list = ["green" => ["red" => ["green"]]];

        $this->show_uuid = "off";

        $this->link = $this->web_prefix . "thing/" . $this->uuid . "/point";

        $this->initPoint();
    }

    public function getPoints()
    {
        $this->allowed_points_resource = "point/points.php";
        $resource_path = $this->resource_path . $this->allowed_points_resource;
        $allowed_endpoints = [];
        if (file_exists($resource_path)) {
            $allowed_endpoints = require $resource_path;
        }

        $this->points = $allowed_endpoints;
        return $this->points;
    }

    public function initPoint()
    {
        $this->coordinate_handler = new Coordinate($this->thing, "coordinate");
    }

    function findPoint($text = null)
    {
        if ($text == null) {
            return null;
        }

        //        if ($this->isIp($text)) {
        //            $selector_array = ["point" => $text];
        //        } else {
        $selector_array = ["Geographical Name" => $text];
        //        }

        $matches = [];

        $this->resource_list = [
            "resources/places_canada/cgn_canada_csv_eng.csv",
        ];

        foreach ($this->resource_list as $i => $resource_list) {
            $m = $this->matchPoint($resource_list, $selector_array);

            $matches = array_merge($m, $matches);
        }

        $this->points_db = $matches;

        $match_array = $this->searchForText(
            strtolower($text),
            $this->points_db
        );
        return $match_array;
    }

    function searchForText($text, $array)
    {
        //$text = "commercial broadway";
        $text = strtolower($text);
        $pieces = explode(" ", $text);
        $match = false;
        $match_array = [];
        $num_words = count($pieces);
        foreach ($array as $key => $val) {
            $count = 0;

            foreach ($pieces as $piece) {
                //                if (preg_match("/\b$piece\b/i", $this->textIp($array))) {
                //$s = $this->textPoint($array);
                if (preg_match("/\b$piece\b/i", $this->textPoint($array))) {
                    $count += 1;
                    $match = true;
                } else {
                    $match = false;
                    continue;
                }

                if ($count == $num_words) {
                    break;
                }
            }

            //            if ($count == $num_words) {

            $match_array[] = [
                "point" => $val,
                "score" => $count,
            ];
            //            }
        }
        return $match_array;
    }

    function nextPoint($file_name, $selector_array = null)
    {
        $split_time = $this->thing->elapsed_runtime();

        $file = $GLOBALS["stack_path"] . "" . $file_name;
        $handle = fopen($file, "r");
        $line_number = 0;

        /*
CGNDB ID,Geographical Name,Language,Syllabic Form,Generic Term,Generic Category,Concise Code,Toponymic Feature ID,Latitude,Longitude,Location,Province - Territory,Relevance at Scale,Decision Date,Source

*/

        $field_names = [
            0 => "start_ip",
            1 => "end_ip",
            2 => "c",
            3 => "jurisdiction",
            4 => "subjurisdiction",
            5 => "place",
            6 => "latitude",
            7 => "longitude",
        ];

        while (!feof($handle)) {
            $line = trim(fgets($handle));

            $line_number += 1;
            // Get headers
            $line1_header = true;
            if ($line_number == 1 and $line1_header == true) {
                $i = 0;
                $field_names = explode(",", $line);
                foreach ($field_names as $field) {
                    $field_names[$i] = preg_replace(
                        '/[\x00-\x1F\x80-\xFF]/',
                        "",
                        $field
                    );
                    $i += 1;
                }
                continue;
            }

            $arr = $this->parseLine($line, $field_names);
            // If there is no selector array, just return it.
            if ($selector_array == null) {
                yield $arr;
                continue;
            }

            if (array_key_exists(0, $selector_array)) {
            } else {
                $selector_array = [$selector_array];
            }

            // Otherwise see if it matches the selector array.
            $match_count = 0;
            $match = true;

            // Look for all items in the selector_array matching
            if ($selector_array == null) {
                continue;
            }

            foreach ($selector_array as $selector) {
                foreach ($selector as $selector_name => $selector_value) {
                    if ($selector_name == "Geographical Name") {
                        $location = $selector_array[0]["Geographical Name"];

                        $haystack = implode(" ", $arr);

                        if (stripos($haystack, $location) !== false) {
                            yield $arr;
                        }
                    }
                }
            }
        }

        fclose($handle);

        $this->thing->log(
            "nextPoint took " .
                number_format($this->thing->elapsed_runtime() - $split_time) .
                "ms."
        );
    }

    public function respondResponse()
    {
        $this->makeHelp();
        $this->makeInfo();
        $this->thing->flagGreen();

        $message_thing = new Message($this->thing, $this->thing_report);
        //$thing_report['info'] = $message_thing->thing_report['info'];
    }

    public function set()
    {
        $this->setPoint();
        //return $this->ship_thing->variables->snapshot;
    }

    public function get()
    {
        $this->channel = new Channel($this->thing, "channel");
        $this->channel_name = $this->channel->channel_name;

        /*
        if (is_string($this->channel_name)) {
            $this->response .= "Saw channel is " . $this->channel_name . ". ";
        } else {
            $this->response .= "No channel name. ";
        }
*/
        $this->getPoint();
        $this->getPoints();
    }

    public function placesPoint($text = null)
    {
        /*
resources/places_canada
cgn_canada_csv_eng.csv
*/
    }

    public function run()
    {
    }

    function setPoint($text = null)
    {
        $this->thing->Write(["point", "refreshed_at"], $this->current_time);
    }

    function matchPoint($file_name, $selector_array = null)
    {
        $matches = [];
        $iterator = $this->nextPoint($file_name, $selector_array);

        foreach ($iterator as $iteration) {
            $matches[] = $iteration;
        }

        return $matches;
    }

    public function textPoint($point = null)
    {
        if ($point == null) {
            $point = $this->point;
        }

        $id = "";
        if (isset($point["id"])) {
            $id = strtoupper($point["id"]);
        }

        $geographical_name = "";
        if (isset($point["Geographical Name"])) {
            $geographical_name = $point['Geographical Name'];
        }

        $latitude = "";
        if (isset($point["Latitude"])) {
            $latitude = $point['Latitude'];
        }

        $longitude = "";
        if (isset($point["Longitude"])) {
            $longitude = $point['Longitude'];
        }

        $uuid = "";
        if (isset($point["uuid"])) {
            $uuid = $point["uuid"];
        }

        $state = "";
        if (isset($point["state"])) {
            $state = strtoupper($point["state"]);
        }

        $text = "";
        if (isset($point["text"])) {
            $text = $point["text"];
        }

        $refreshed_at = "";
        if (isset($point["refreshed_at"])) {
            $refreshed_at = $point["refreshed_at"];
        }

        $text =
            $geographical_name .
            " " .
            $id .
            " " .
            //            $uuid .
            //            " " .
            " " .
            $state .
            " " .
            $text .
            " " .
            $latitude .
            " " .
            $longitude .
            " " .
            $refreshed_at .
            "\n";
        return $text;
    }

    public function getPoint($text = null)
    {
    }

    function makeSMS()
    {
        $this->point_message = "POINT | ";
        $this->node_list = ["point" => ["point", "line"]];
        $this->sms_message = "" . $this->point_message . $this->response;
        $this->thing_report['sms'] = $this->sms_message;
    }

    function makeWeb()
    {
        $web = null;
        if (isset($this->ship_thing)) {
            $web = "";
            $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br><p>";
            $web .= "<p>";

            $web .= $this->html_image;

            $web .= "<br>";

            $state_text = "X";
            if (isset($this->state)) {
                $state_text = strtoupper($this->state);
            }

            $web .= "POINT IS " . $state_text;
            $web = "";
            $web .= "<b>" . ucwords($this->agent_name) . " Agent</b><br><p>";
            $web .= "<p>";
            $web .= '<a href="' . $this->link . '">';
            //        $web .= '<img src= "' . $this->web_prefix . 'thing/' . $this->uuid . '/sig>
            $web .= $this->html_image;

            $web .= "</a>";
            $web .= "<br>";

            $state_text = "X";
            if (isset($this->state)) {
                $state_text = strtoupper($this->state);
            }

            $web .= "POINT IS " . $state_text . "<br>";

            $web .= "<p>";
        }

        $this->thing_report["web"] = $web;
    }

    public function snapshotPoint($text = null)
    {
        $matches = [];
        foreach ($this->points as $i => $point) {
            $slugified_text = $this->slugifySlug($text);

            if (stripos($point, $slugified_text) !== false) {
                $matches[] = $point;
            }
        }
        if (count($matches) != 1) {
            return true;
        }
        $data_source = $matches[0];
        $snapshot_handler = new Snapshot($this->thing, $data_source);
        if (isset($snapshot_handler->snapshot)) {
//        if (isset($this->thing->variables->snapshot)) {
            $this->response .= "Saw a snapshot variable. ";

if (isset($snapshot_handler->snapshot['transducers'])) {
$transducers_agent = new Transducers($this->thing, $snapshot_handler->snapshot['transducers']);
$this->response .= $transducers_agent->textTransducers($snapshot_handler->snapshot['transducers']);

}

        } else {
//            $this->response .= "Did not see a snapshot variable. ";
        }
    }

    public function readPoint($text = null)
    {
        // Handle a NMEA string
        if ($text === null) {
            return null;
        }
        if ($text == "null") {
            return null;
        }
    }

    public function makeResponse()
    {
        $matched_points = $this->matched_points;
        if (!isset($this->response)) {
            $this->response = "";
        }
        if (count($matched_points) == 1) {
            $this->response .=
                "Found a point. " .
                $this->textPoint($matched_points[0]['point']) .
                ". ";
        } elseif (count($matched_points) > 1) {
            $this->response .= "Found several points. ";
        } else {
            $this->response .= "Did not find a point. ";
        }
    }

    public function readSubject($input = null)
    {
        $keywords = ["point", "dot", "nexus", "node"];

        usort($keywords, function ($a, $b) {
            $countA = $this->countNgrams($a);
            $countB = $this->countNgrams($b);

            $diff = $countB - $countA;
            return $diff;
            //return $countA < $countB;
        });
        if ($input == null) {
            $input = $this->input;
        }
        $filtered_input = $this->assert($input);
        $prior_uuid = null;

        //        $pieces = explode(" ", strtolower($input));
        //        $input = strtolower($this->subject);

        $ngram_agent = new Ngram($this->thing, "ngram");
        $pieces = [];
        $arr = $ngram_agent->getNgrams(strtolower($input), 4);
        $pieces = array_merge($pieces, $arr);
        $arr = $ngram_agent->getNgrams(strtolower($input), 3);
        $pieces = array_merge($pieces, $arr);
        $arr = $ngram_agent->getNgrams(strtolower($input), 2);
        $pieces = array_merge($pieces, $arr);
        $arr = $ngram_agent->getNgrams(strtolower($input), 1);
        $pieces = array_merge($pieces, $arr);

        if (count($pieces) == 1) {
            if ($input == $this->keyword) {
                //$this->response .= "Got the current ship. ";
                return;
            }

            if ($input == "points") {
                $this->response .= "Got active points. ";
                return;
            }
        }
        $this->matched_points = $this->findPoint($this->input);
        $this->makeResponse();
        $this->snapshotPoint($this->input);
        /*
        if (count($pieces) == 3) {
            if ($input == "ship double yellow") {
                $this->changeShip('double yellow');
                return;
            }
        }
*/

        //$this->readShip();
        $this->response .= "Did not see a command. ";

        // devstack
        //return "Message not understood";
        //return false;
    }
}
