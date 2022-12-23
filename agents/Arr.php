<?php
namespace Nrwtaylor\StackAgentThing;

// dev not tested
// Array is a reserved PHP name

class Arr extends Agent
{
    public $var = "hello";

    function init()
    {
    }

    function run()
    {
        $this->doArr();
    }

    public function doArr()
    {
        if ($this->agent_input == null) {
            $array = ["miao", "miaou", "hiss", "prrr", "grrr"];
            $k = array_rand($array);
            $v = $array[$k];

            $response = "ARRAY | " . strtolower($v) . ".";

            $this->html_message = $response; // mewsage?
        } else {
            $this->html_message = $this->agent_input;
        }
    }

    public function respondResponse()
    {
        $this->thing->flagGreen();

        $this->thing_report["info"] = "This agent handles Arrays.";
        $this->thing_report["help"] =
            "This is about recognizing and processing Arrays.";

        $this->thing_report["message"] = $this->sms_message;
        $this->thing_report["txt"] = $this->sms_message;

        $message_thing = new Message($this->thing, $this->thing_report);
        $thing_report["info"] = $message_thing->thing_report["info"];
    }

    function makeSMS()
    {
        $this->node_list = ["array" => ["array"]];
        $this->sms_message = "" . $this->html_message;
        $this->thing_report["sms"] = $this->sms_message;
    }

    function makeChoices()
    {
        $choices = false;
        $this->thing_report["choices"] = $choices;
    }

    function textArr($arr)
    {
        $flattened_array = $this->flattenArray($arr);
        $text = implode(" ", $flattened_array);
        return $text;
    }

    public function searchArr(array $array, $search_key, $id)
    {
        foreach ($array as $key => $val) {
            if ($val[$search_key] == $id) {
                return $key;
            }
        }
        return null;
    }

    // https://stackoverflow.com/questions/262891/is-there-a-way-to-find-out-how-deep-a-php-array-is
    public function depthArr($array)
    {
        $max_indentation = 1;

        $array_str = print_r($array, true);
        $lines = explode("\n", $array_str);

        foreach ($lines as $line) {
            $indentation = (strlen($line) - strlen(ltrim($line))) / 4;

            if ($indentation > $max_indentation) {
                $max_indentation = $indentation;
            }
        }

        return ceil(($max_indentation - 1) / 2) + 1;
    }

    public function variableArr(array $array, $variable_name)
    {
        //        if (!isset($this->{$variable_name})) {$this->{$variable_name} = new \stdClass();}
        $temp_variable = new \stdClass();
        foreach (
            $variable_array
            as $variable_variable_name => $variable_value
        ) {
            $temp_variable->{$variable_variable_name} = $variable_value;
        }
        return $temp_variable;
    }

    public function snippetArr(array $array)
    {
        $web = "";
        if (isset($array) and count($array) !== 0) {
            $web .= "<ul>";
            foreach ($array as $index => $array_element) {
                $web .=
                    "<li><div>" .
                    implode(" ", $this->flattenArr($array_element)) .
                    "</div>";
            }
            $web .= "</ul>";
            $web .= "<p>";
        }

        return $web;
    }

    public function filterFieldsArr($climate_data_points, $filter_array = null)
    {
        $filtered_data_points = array_filter($climate_data_points, function (
            $value
        ) use ($filter_array) {
            $filter_month = $filter_array["month"];
            $filter_day = $filter_array["day"];

            $month = str_replace('"', "", $value["Month"]);
            $day = str_replace('"', "", $value["Day"]);
            $time_stamp = str_replace('"', "", $value["Time (LST)"]);

            $filter_time_stamp = $filter_array["time_stamp"];

            return $month == $filter_month and
                $day == $filter_day and
                $time_stamp == $filter_time_stamp;
        });

        return $filtered_data_points;
    }
    public function filterArr(array $array, $search_words)
    {
        $filtered_array = [];
        foreach ($array as $i => $array_element) {
            $haystack = implode(" ", $this->flattenArr($array_element));

            if (stripos($haystack, $search_words) !== false) {
                $filtered_array[] = $array_element;
            }
        }

        return $filtered_array;
    }

    /**
     *
     * @param array   $array
     * @return unknown
     */
    function flattenArr(array $array)
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
     * @param unknown $json_data (optional)
     * @return unknown
     */
    public function jsonArr($json_data = null)
    {
        var_dump("Arr jsonArr");
        $array_data = json_decode($json_data, true);

        if ($array_data == false) {
            return false;
        }

        if (is_string($array_data)) {
            $array_data = ["text" => $array_data];
        }

        /*
        foreach ($array_data as $key => $value) {
            if ($key != "") {
                $this->{$key} = $value;
            }
        }
*/
        return $array_data;
    }

    private function setPathValueArr(&$arr, $path, $value)
    {
        if (!is_array($arr)) {
            return true;
        }
        // we need references as we will modify the first parameter
        $dest = &$arr;
        if ($dest == null) {
            $dest = [];
        }
        //var_dump($dest);
        //return null;}
        $finalKey = array_pop($path);
        foreach ($path as $key) {
            $dest = &$dest[$key];
        }

        if (is_array($finalKey)) {
            throw new Exception("Array received as path.");
            return true;
        }
        if (is_string($dest)) {
            return true;
            // dev 5 November 2021
        }
        $dest[$finalKey] = $value;
    }

    public function readSubject()
    {
        return false;
    }
}
